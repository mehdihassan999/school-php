<?php
/**
 * Public site header. Pages set these before including this file:
 *   $pageTitle, $pageDesc, $pageKicker, $pageHeading, $pageCrumb, $pageSub
 */
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

$S          = all_settings();
$pageTitle  = $pageTitle ?? '';
$pageDesc   = $pageDesc ?? setting('seo_description');
$canonical  = $canonical ?? BASE_URL . ltrim($_SERVER['SCRIPT_NAME'] ?? '', '/');
$accent     = setting('accent_color', '#1B3A6B');
$navCurrent = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

$nav = [
    'index.php'         => 'Home',
    'about.php'         => 'About',
    'academics.php'     => 'Academics',
    'admissions.php'    => 'Admissions',
    'facilities.php'    => 'Campus',
    'student-life.php'  => 'Student Life',
];
$navMore = [
    'news.php'      => 'News',
    'events.php'    => 'Events',
    'notices.php'   => 'Notice Board',
    'downloads.php' => 'Downloads',
];
$navFlat = [
    'gallery.php' => 'Gallery',
    'contact.php' => 'Contact',
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ? $pageTitle . ' | ' . setting('school_name') : setting('seo_title')) ?></title>
<meta name="description" content="<?= e(mb_substr($pageDesc, 0, 300)) ?>">
<?php if (setting('seo_keywords')): ?>
<meta name="keywords" content="<?= e(setting('seo_keywords')) ?>">
<?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(setting('school_name')) ?>">
<meta property="og:title" content="<?= e($pageTitle ? $pageTitle . ' | ' . setting('school_name') : setting('seo_title')) ?>">
<meta property="og:description" content="<?= e(mb_substr($pageDesc, 0, 300)) ?>">
<?php if (setting('og_image')): ?>
<meta property="og:image" content="<?= e(setting_asset('og_image')) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(setting_asset('favicon', BASE_URL . 'assets/images/favicon.svg')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/style.css">
<style>:root{--accent:<?= e($accent) ?>;}</style>
</head>
<body>

<a class="skip" href="#main">Skip to content</a>

<header class="site-header" id="siteHeader">
  <div class="topbar">
    <div class="wrap topbar-in">
      <div class="topbar-contacts">
        <a href="<?= e(tel_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>
        <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a>
      </div>
      <a class="portal-link" href="<?= e(BASE_URL) ?>admin/login.php">Portal Login</a>
    </div>
  </div>

  <div class="navbar">
    <div class="wrap navbar-in">
      <a class="brand" href="<?= e(BASE_URL) ?>index.php">
        <?php if (setting('logo')): ?>
          <img class="brand-logo" src="<?= e(setting_asset('logo')) ?>" alt="<?= e(setting('school_name')) ?> logo">
        <?php else: ?>
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 3 1 9l11 6 9-4.9V15h2V9L12 3zM5.5 13.2V17c0 2.2 2.9 4 6.5 4s6.5-1.8 6.5-4v-3.8L12 16.7l-6.5-3.5z"/></svg>
          </span>
        <?php endif; ?>
        <span class="brand-text">
          <strong><?= e(setting('school_name')) ?></strong>
          <small><?= e(setting('tagline')) ?></small>
        </span>
      </a>

      <nav class="nav" id="nav" aria-label="Primary">
        <?php foreach ($nav as $file => $label): ?>
          <a class="nav-link<?= $navCurrent === $file ? ' is-active' : '' ?>" href="<?= e(BASE_URL . $file) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <div class="nav-group">
          <button class="nav-link nav-drop" type="button" aria-expanded="false">News &amp; Events <span class="caret">&#9662;</span></button>
          <div class="nav-menu">
            <?php foreach ($navMore as $file => $label): ?>
              <a href="<?= e(BASE_URL . $file) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php foreach ($navFlat as $file => $label): ?>
          <a class="nav-link<?= $navCurrent === $file ? ' is-active' : '' ?>" href="<?= e(BASE_URL . $file) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <div class="nav-cta">
          <a class="btn btn-gold btn-sm" href="<?= e(BASE_URL) ?>apply.php">Apply Now</a>
          <a class="btn btn-ghost btn-sm" href="<?= e(BASE_URL) ?>admin/login.php">Portal Login</a>
        </div>
      </nav>

      <button class="burger" id="burger" type="button" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<?php if (!empty($pageHeading)): ?>
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>index.php">Home</a> <span>/</span> <em><?= e($pageCrumb ?? '') ?></em>
    </nav>
    <p class="kicker kicker-light"><?= e($pageKicker ?? '') ?></p>
    <h1 class="display"><?= e($pageHeading) ?></h1>
    <?php if (!empty($pageSub)): ?>
      <p class="page-sub"><?= e($pageSub) ?></p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<main id="main">
