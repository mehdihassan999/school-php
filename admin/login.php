<?php
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

if (current_admin()) {
    redirect(BASE_URL . 'admin/index.php');
}

$errors = [
    'missing' => 'Please enter your username/email and password.',
    'invalid' => 'Invalid credentials. Please try again.',
    'rate'    => 'Too many failed attempts. Please wait 15 minutes and try again.',
    'auth'    => 'Your session has expired. Please sign in again.',
    'db'      => 'Database is unreachable. Please try again shortly.',
];
$error  = isset($_GET['error']) ? (string) $_GET['error'] : '';
$msg    = isset($_GET['msg']) ? (string) $_GET['msg'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = post('username', 160);
    $password = post('password', 200);
    $ip       = client_ip();

    if ($username === '' || $password === '') {
        redirect(BASE_URL . 'admin/login.php?error=missing');
    }
    if (is_rate_limited($ip)) {
        redirect(BASE_URL . 'admin/login.php?error=rate');
    }

    try {
        $admin = fetch_one(
            'SELECT * FROM admins WHERE (username = ? OR email = ?) LIMIT 1',
            [$username, $username]
        );
    } catch (Throwable $ex) {
        error_log('admin login query failed: ' . $ex->getMessage());
        redirect(BASE_URL . 'admin/login.php?error=db');
    }

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        record_login_attempt($ip, $username, false);
        redirect(BASE_URL . 'admin/login.php?error=invalid');
    }

    record_login_attempt($ip, $username, true);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    redirect(BASE_URL . 'admin/index.php');
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login | <?= e(setting('school_name')) ?></title>
<link rel="icon" href="<?= e(setting_asset('favicon', BASE_URL . 'assets/images/favicon.svg')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/style.css">
<style>:root{--accent:<?= e(setting('accent_color', '#1B3A6B')) ?>;}</style>
</head>
<body class="admin-body">
<div class="login-wrap">
  <div class="login-card">
    <div class="login-brand">
      <?php if (setting('logo')): ?>
        <img src="<?= e(setting_asset('logo')) ?>" alt="<?= e(setting('school_name')) ?> logo" style="width:56px;height:56px;border-radius:13px;object-fit:contain">
      <?php else: ?>
        <span class="brand-mark" style="width:56px;height:56px;flex:0 0 56px"><svg viewBox="0 0 24 24" width="29" height="29" fill="currentColor"><path d="M12 3 1 9l11 6 9-4.9V15h2V9L12 3zM5.5 13.2V17c0 2.2 2.9 4 6.5 4s6.5-1.8 6.5-4v-3.8L12 16.7l-6.5-3.5z"/></svg></span>
      <?php endif; ?>
      <div>
        <strong style="font-family:Fraunces,Georgia,serif;font-size:20px;display:block"><?= e(setting('school_name')) ?></strong>
        <small style="font-size:10.5px;font-weight:700;letter-spacing:.19em;text-transform:uppercase;color:var(--gold-deep)"><?= e(setting('tagline')) ?></small>
      </div>
      <h1 style="font-family:Fraunces,Georgia,serif;font-size:22px">Administrator Login</h1>
      <p style="font-size:13.5px;color:var(--ink-soft);margin:0">Sign in to manage website content</p>
    </div>

    <?php if ($error !== ''): ?>
      <div class="alert alert-err"><?= e($errors[$error] ?? 'Something went wrong. Please try again.') ?></div>
    <?php elseif ($msg === 'out'): ?>
      <div class="alert alert-ok">You have been securely logged out.</div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label for="username">Username or Email</label>
        <input id="username" name="username" type="text" required autocomplete="username" autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
      </div>
      <button class="btn btn-accent btn-block" type="submit">Sign In</button>
    </form>

    <p style="text-align:center;margin-top:22px;font-size:12.5px">
      <a href="<?= e(BASE_URL) ?>index.php" style="color:var(--accent);font-weight:600">&larr; Back to website</a>
    </p>
  </div>
</div>
</body>
</html>
