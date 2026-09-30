<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$faculty = fetch_all('SELECT * FROM faculty WHERE published = 1 ORDER BY sort_order, id');
$byDept  = [];
foreach ($faculty as $f) {
    $byDept[$f['department']][] = $f;
}

$pageTitle = 'Faculty & Staff';
$pageDesc  = 'Meet the experienced faculty and staff of ' . setting('school_name') . '.';
$pageKicker = 'Faculty & Staff';
$pageHeading = 'Teachers Who Inspire';
$pageSub = 'Qualified, caring educators — the heart of everything that happens at ' . setting('school_short') . '.';
$pageCrumb = 'Faculty';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap" style="display:grid;gap:62px">
    <?php foreach ($byDept as $dept => $people): ?>
    <div>
      <div class="section-head left reveal">
        <p class="kicker"><?= e($dept) ?></p>
        <h2><?= e($dept === 'General' ? 'Our Team' : $dept . ' Department') ?></h2>
      </div>
      <div class="grid g4">
        <?php foreach ($people as $p): ?>
        <article class="card card-hover media-card reveal">
          <div class="thumb" style="aspect-ratio:1">
            <?php if ($p['photo']): ?>
              <img src="<?= e(asset_url($p['photo'])) ?>" alt="<?= e($p['name']) ?>, <?= e($p['position']) ?>" loading="lazy">
            <?php else: ?>
              <span class="avatar" style="width:100%;height:100%;border-radius:0;font-size:56px"><?= e(mb_substr($p['name'], 0, 1)) ?></span>
            <?php endif; ?>
          </div>
          <div class="body">
            <h3><?= e($p['name']) ?></h3>
            <p class="kicker" style="font-size:10.5px;margin-top:5px"><?= e($p['position']) ?></p>
            <p style="font-size:12.5px"><?= e($p['qualification']) ?></p>
            <p style="font-size:13px"><?= e($p['bio']) ?></p>
            <?php if ($p['email']): ?>
              <a class="more" href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$faculty): ?>
      <p>Faculty profiles will appear here once added in the admin panel.</p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
