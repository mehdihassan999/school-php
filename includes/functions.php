<?php
/**
 * Settings access, formatting and small view helpers.
 */

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

/* --------------------------------- settings ------------------------------- */

function all_settings(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = DEFAULT_SETTINGS;
    try {
        foreach (fetch_all('SELECT `key`, value FROM settings') as $row) {
            if ($row['value'] !== null && $row['value'] !== '') {
                $cache[$row['key']] = $row['value'];
            }
        }
    } catch (Throwable $e) {
        // fresh install — fall back to defaults
    }
    return $cache;
}

function setting(string $key, string $fallback = ''): string
{
    $all = all_settings();
    return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $fallback;
}

function save_setting(string $key, string $value): void
{
    q(
        'INSERT INTO settings (`key`, value) VALUES (?,?)
         ON DUPLICATE KEY UPDATE value = VALUES(value)',
        [$key, $value]
    );
}

/* -------------------------------- formatting ------------------------------ */

function slugify(string $text): string
{
    $s = strtolower(trim($text));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    if (strlen($s) > 120) {
        $s = substr($s, 0, 120);
    }
    return $s !== '' ? $s : 'item-' . time();
}

function unique_slug(string $table, string $title, int $ignoreId = 0): string
{
    $base = slugify($title);
    $slug = $base;
    $i    = 1;
    while (true) {
        $row = fetch_one("SELECT id FROM `$table` WHERE slug = ? AND id <> ? LIMIT 1", [$slug, $ignoreId]);
        if (!$row) {
            return $slug;
        }
        $slug = $base . '-' . (++$i);
    }
}

function fmt_date($value, string $format = 'j F Y'): string
{
    if (!$value) {
        return '—';
    }
    $ts = is_numeric($value) ? (int) $value : strtotime($value);
    return $ts ? date($format, $ts) : '—';
}

function fmt_datetime($value): string
{
    return fmt_date($value, 'j M Y, g:i a');
}

function excerpt(string $text, int $len = 150): string
{
    $t = trim(preg_replace('/\s+/', ' ', $text));
    return mb_strlen($t) <= $len ? $t : rtrim(mb_substr($t, 0, $len)) . '…';
}

/** Split text on blank lines into paragraphs. */
function text_paragraphs(string $text): array
{
    $out = [];
    foreach (preg_split('/\n{2,}/', $text) as $p) {
        $p = trim(preg_replace('/\s+/', ' ', $p));
        if ($p !== '') {
            $out[] = $p;
        }
    }
    return $out;
}

/** Split text into non-empty lines. */
function text_lines(string $text): array
{
    $out = [];
    foreach (explode("\n", $text) as $l) {
        $l = trim($l);
        if ($l !== '') {
            $out[] = $l;
        }
    }
    return $out;
}

/** Turn "Title: description" lines into [title, desc] pairs. */
function text_pairs(string $text): array
{
    $out = [];
    foreach (text_lines($text) as $l) {
        $pos = strpos($l, ':');
        if ($pos === false) {
            $out[] = ['title' => $l, 'desc' => ''];
        } else {
            $out[] = ['title' => trim(substr($l, 0, $pos)), 'desc' => trim(substr($l, $pos + 1))];
        }
    }
    return $out;
}

/* ------------------------------- view helpers ----------------------------- */

function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^+\d]/', '', $phone);
}

function whatsapp_href(string $number): string
{
    return 'https://wa.me/' . preg_replace('/[^0-9]/', '', $number);
}

function map_src(string $address): string
{
    return 'https://www.google.com/maps?q=' . rawurlencode($address) . '&output=embed';
}

/**
 * Resolve a stored path to a URL.
 * Paths saved in the database may be absolute URLs (https://…), app-root
 * absolute (/school-php/uploads/x.jpg) or document-relative
 * (assets/images/hero.jpg). Relative ones are prefixed with BASE_URL so they
 * resolve correctly on every page, including /admin/.
 */
function asset_url(?string $path): string
{
    $p = is_string($path) ? trim($path) : '';
    if ($p === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $p) || $p[0] === '/') {
        return $p;
    }
    return BASE_URL . $p;
}

/** Default image when a record has none. */
function img(?string $src, string $fallback = 'hero'): string
{
    $resolved = asset_url($src);
    if ($resolved !== '') {
        return $resolved;
    }
    return BASE_URL . 'assets/images/' . $fallback . '.jpg';
}

/** A setting that holds an image/document path. */
function setting_asset(string $key, string $fallback = ''): string
{
    $v = asset_url(setting($key));
    return $v !== '' ? $v : $fallback;
}

function status_badge(string $status): string
{
    $map = [
        'new'          => ['New', 'badge-sky'],
        'under_review' => ['Under Review', 'badge-amber'],
        'contacted'    => ['Contacted', 'badge-violet'],
        'accepted'     => ['Accepted', 'badge-green'],
        'rejected'     => ['Rejected', 'badge-red'],
    ];
    [$label, $class] = $map[$status] ?? [ucwords(str_replace('_', ' ', $status)), 'badge-grey'];
    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}
