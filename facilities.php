<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$facilities = fetch_all('SELECT * FROM facilities WHERE published = 1 ORDER BY sort_order, id');

$pageTitle = 'Campus & Facilities';
$pageDesc  = 'Explore the ' . setting('school_name') . ' campus — smart classrooms, laboratories, library, sports facilities, transport and more.';
$pageKicker = 'Campus & Facilities';
$pageHeading = 'A Campus Built for Curiosity';
$pageSub = 'Every space — from laboratories to playing fields — is designed to make learning active, safe and joyful.';
$pageCrumb = 'Campus';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap grid g2">
    <?php foreach ($facilities as $f): ?>
    <article class="card card-hover media-card reveal">
      <div class="thumb" style="flex:0 0 220px;aspect-ratio:auto;min-height:180px">
        <img src="<?= e(img($f['image'])) ?>" alt="<?= e($f['title']) ?>" loading="lazy">
      </div>
      <div class="body">
        <h3><?= e($f['title']) ?></h3>
        <p><?= e($f['description']) ?></p>
      </div>
    </article>
    <?php endforeach; ?>
    <?php if (!$facilities): ?>
      <p>Facilities will appear here once added in the admin panel.</p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
