<?php
/**
 * Session hardening, CSRF protection, authentication, rate limiting
 * and secure file uploads.
 */

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

/* ----------------------------- output escaping --------------------------- */

/** Escape for HTML output. Use for EVERYTHING printed into markup. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ------------------------------- sessions -------------------------------- */

function boot_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();

    // Enforce inactivity timeout.
    $now = time();
    if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > SESSION_LIFETIME) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = $now;
}

/* ---------------------------------- CSRF --------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Abort the request if the posted token is missing or wrong. */
function csrf_verify(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Security token expired. Please go back and try again.');
    }
}

/* ------------------------------ input helpers ---------------------------- */

/** Trimmed, length-limited POST string. */
function post(string $key, int $max = 5000): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim(mb_substr($v, 0, $max)) : '';
}

function post_int(string $key, int $default = 0): int
{
    $v = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    return $v === false || $v === null ? $default : $v;
}

function post_bool(string $key): bool
{
    $v = $_POST[$key] ?? '';
    return $v === '1' || $v === 'on' || $v === 'true';
}

function is_email(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function client_ip(): string
{
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return mb_substr(trim(explode(',', $ip)[0]), 0, 45);
}

/* ------------------------------ authentication --------------------------- */

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return fetch_one('SELECT * FROM admins WHERE id = ? AND role IN ("admin","editor") LIMIT 1', [$_SESSION['admin_id']]);
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        header('Location: ' . BASE_URL . 'admin/login.php?error=auth');
        exit;
    }
    return $admin;
}

/* --------------------------- login rate limiting -------------------------- */

function login_attempts_recent(string $ip): int
{
    try {
        return (int) fetch_val(
            'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND success = 0 AND created_at > (NOW() - INTERVAL ' . (int) LOGIN_WINDOW . ' SECOND)',
            [$ip]
        );
    } catch (Throwable $e) {
        return 0;
    }
}

function is_rate_limited(string $ip): bool
{
    return login_attempts_recent($ip) >= LOGIN_MAX_ATTEMPTS;
}

function record_login_attempt(string $ip, string $username, bool $success): void
{
    try {
        q('INSERT INTO login_attempts (ip, username, success) VALUES (?,?,?)', [$ip, mb_substr($username, 0, 80), $success ? 1 : 0]);
        if ($success) {
            q('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
        }
    } catch (Throwable $e) {
        error_log('login attempt log failed: ' . $e->getMessage());
    }
}

/* ------------------------------ file uploads ----------------------------- */

/**
 * Validate and store an uploaded file.
 *
 * @param string $field  name of the <input type="file">
 * @param string $kind   'image' | 'document' | 'any'
 * @return string|null   relative URL such as /uploads/1699-abc123.jpg, or null
 * @throws RuntimeException with a safe message on validation failure
 */
function save_upload(string $field, string $kind = 'any'): ?string
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }
    $f = $_FILES[$field];

    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (code ' . (int) $f['error'] . ').');
    }
    if (!is_uploaded_file($f['tmp_name'])) {
        throw new RuntimeException('Invalid upload source.');
    }

    $ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = $kind === 'image' ? ALLOWED_IMAGE_EXT
        : ($kind === 'document' ? ALLOWED_DOC_EXT : array_merge(ALLOWED_IMAGE_EXT, ALLOWED_DOC_EXT));

    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('File type .' . $ext . ' is not allowed. Allowed: ' . implode(', ', $allowed) . '.');
    }

    $max = $kind === 'image' ? UPLOAD_MAX_IMAGE_BYTES : UPLOAD_MAX_DOC_BYTES;
    if (($f['size'] ?? 0) <= 0 || $f['size'] > $max) {
        throw new RuntimeException('File is too large. Maximum ' . ($kind === 'image' ? '4 MB' : '10 MB') . '.');
    }

    // Verify real content, not just the file name.
    if ($kind === 'image' || in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        if (function_exists('finfo_open')) {
            $fi     = finfo_open(FILEINFO_MIME_TYPE);
            $mime   = finfo_file($fi, $f['tmp_name']);
            finfo_close($fi);
            $imgMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
            if (!in_array($mime, $imgMime, true)) {
                throw new RuntimeException('File is not a valid image.');
            }
        }
        if (@getimagesize($f['tmp_name']) === false) {
            throw new RuntimeException('File is not a valid image.');
        }
    }

    if (!is_dir(UPLOAD_DIR)) {
        if (!@mkdir(UPLOAD_DIR, 0755, true)) {
            throw new RuntimeException('Upload directory is not writable.');
        }
    }

    $name = time() . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }
    @chmod(UPLOAD_DIR . '/' . $name, 0644);

    return UPLOAD_URL . '/' . $name;
}

/** Redirect helper (relative to the app). */
function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}
