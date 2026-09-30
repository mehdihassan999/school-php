<?php
/** Admin panel shell. Requires an authenticated admin. */
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

$admin      = require_admin();
$unreadMsgs = (int) fetch_val('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
$newApps    = (int) fetch_val("SELECT COUNT(*) FROM admissions WHERE status = 'new'");
$accent     = setting('accent_color', '#1B3A6B');
$current    = basename($_SERVER['SCRIPT_NAME'] ?? '');

$menu = [
    'Overview' => [['index.php', 'Dashboard', '&#9636;']],
    'Website Management' => [['settings.php', 'General Settings', '&#9881;']],
    'Academic Management' => [
        ['content.php?entity=programs', 'Programs', '&#127891;'],
        ['content.php?entity=subjects', 'Subjects', '&#128196;'],
    ],
    'People' => [
        ['content.php?entity=faculty', 'Faculty & Staff', '&#128101;'],
        ['content.php?entity=testimonials', 'Testimonials', '&#10024;'],
    ],
    'Admissions' => [['admissions.php', 'Applications', '&#128100;']],
    'Content' => [
        ['content.php?entity=news', 'News', '&#128240;'],
        ['content.php?entity=events', 'Events', '&#128197;'],
        ['content.php?entity=notices', 'Notices', '&#128276;'],
        ['content.php?entity=activities', 'Student Life', '&#127942;'],
        ['content.php?entity=downloads', 'Downloads', '&#11015;'],
    ],
    'Media' => [['gallery.php', 'Gallery', '&#128247;']],
    'Communication' => [['messages.php', 'Contact Messages', '&#9993;']],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($adminTitle ?? 'Admin') ?> | <?= e(setting('school_name')) ?></title>
<link rel="icon" href="<?= e(setting_asset('favicon', BASE_URL . 'assets/images/favicon.svg')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/style.css">
<style>:root{--accent:<?= e($accent) ?>;}</style>
</head>
<body class="admin-body">
<div class="admin">
  <aside class="sidebar">
    <div class="sidebar-head">
      <?php if (setting('logo')): ?>
        <img class="brand-logo" style="width:38px;height:38px;border-radius:9px" src="<?= e(setting_asset('logo')) ?>" alt="">
      <?php else: ?>
        <span class="brand-mark" style="width:38px;height:38px;flex:0 0 38px"><svg viewBox="0 0 24 24" width="21" height="21" fill="currentColor"><path d="M12 3 1 9l11 6 9-4.9V15h2V9L12 3zM5.5 13.2V17c0 2.2 2.9 4 6.5 4s6.5-1.8 6.5-4v-3.8L12 16.7l-6.5-3.5z"/></svg></span>
      <?php endif; ?>
      <span class="brand-text">
        <strong><?= e(setting('school_short')) ?></strong>
        <small>Admin Panel</small>
      </span>
    </div>

    <nav class="sidebar-nav">
      <?php foreach ($menu as $label => $items): ?>
        <p class="nav-label"><?= e($label) ?></p>
        <?php foreach ($items as [$href, $text, $icon]): ?>
          <?php $isActive = $current === basename($href) && (strpos($href, '?') === false || strpos($_SERVER['REQUEST_URI'] ?? '', $href) !== false); ?>
          <a class="<?= $isActive ? 'is-current' : '' ?>" href="<?= e(BASE_URL . 'admin/' . $href) ?>">
            <span aria-hidden="true"><?= $icon ?></span> <?= e($text) ?>
            <?php if ($href === 'admissions.php' && $newApps > 0): ?>
              <span class="badge badge-sky" style="margin-left:auto"><?= (int) $newApps ?></span>
            <?php elseif ($href === 'messages.php' && $unreadMsgs > 0): ?>
              <span class="badge badge-amber" style="margin-left:auto"><?= (int) $unreadMsgs ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>

      <p class="nav-label" style="margin-top:14px">Account</p>
      <a href="<?= e(BASE_URL) ?>" target="_blank" rel="noopener"><span aria-hidden="true">&#8599;</span> View Website</a>
      <a href="<?= e(BASE_URL) ?>admin/logout.php"><span aria-hidden="true">&#8618;</span> Logout</a>
      <div style="display:flex;align-items:center;gap:11px;margin-top:16px;background:rgba(255,255,255,.06);border-radius:11px;padding:11px 12px">
        <span class="avatar" style="background:rgba(200,162,75,.25);color:var(--gold)"><?= e(mb_substr($admin['name'], 0, 1)) ?></span>
        <span style="min-width:0">
          <strong style="display:block;font-size:13px;color:#fff"><?= e($admin['name']) ?></strong>
          <small style="font-size:11px;color:rgba(255,255,255,.5)"><?= e(ucfirst($admin['role'])) ?></small>
        </span>
      </div>
    </nav>
  </aside>

  <div class="admin-main">
    <div class="admin-head">
      <div>
        <h1><?= e($adminTitle ?? 'Admin') ?></h1>
        <?php if (!empty($adminDesc)): ?><p><?= e($adminDesc) ?></p><?php endif; ?>
      </div>
      <?php if (!empty($adminActions)): ?><div class="actions"><?= $adminActions ?></div><?php endif; ?>
    </div>

    <?php if ($m = flash('ok')): ?><div class="alert alert-ok" data-toast="<?= e($m) ?>"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('err')): ?><div class="alert alert-err" data-toast="<?= e($m) ?>" data-toast-kind="error"><?= e($m) ?></div><?php endif; ?>
    <?php if (!empty($adminBanner)): ?>
      <div class="alert <?= e($adminBanner[1]) ?>"><?= $adminBanner[0] ?></div>
    <?php endif; ?>
