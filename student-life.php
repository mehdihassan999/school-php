<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$activities = fetch_all('SELECT * FROM activities WHERE published = 1 ORDER BY sort_order, id');
$icons = ['Sports' => '&#127942;', 'Clubs' => '&#128101;', 'Competitions' => '&#129351;',
          'Field Trips' => '&#9992;', 'Cultural' => '&#127917;', 'Achievements' => '&#10024;'];

$order = array_keys($icons);
$found = array_unique(array_column($activities, 'category'));
$cats  = array_merge(
    array_values(array_filter($order, fn ($c) => in_array($c, $found, true))),
    array_values(array_diff($found, $order))
);

$pageTitle = 'Student Life';
$pageDesc  = 'Sports, clubs, competitions, field trips, cultural activities and student achievements at ' . setting('school_name') . '.';
$pageKicker = 'Student Life';
$pageHeading = 'Beyond the Classroom';
$pageSub = 'From the sports field to the debate stage — ' . setting('school_short') . ' students discover talents they never knew they had.';
$pageCrumb = 'Student Life';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap" style="display:grid;gap:74px">
    <?php foreach ($cats as $cat): ?>
    <div>
      <div class="reveal" style="display:flex;align-items:center;gap:17px">
        <span class="step-ico" style="background:var(--accent);color:#fff;width:52px;height:52px;font-size:23px"><?= $icons[$cat] ?? '&#128101;' ?></span>
        <div>
          <p class="kicker"><?= e($cat) ?></p>
          <h2 class="display" style="font-size:28px;margin-top:4px"><?= e($cat === 'Achievements' ? 'Proud Moments' : $cat . ' at ' . setting('school_short')) ?></h2>
        </div>
      </div>
      <div class="grid g3" style="margin-top:30px">
        <?php foreach (array_filter($activities, fn ($a) => $a['category'] === $cat) as $a): ?>
        <article class="card card-hover media-card reveal">
          <div class="thumb"><img src="<?= e(img($a['image'])) ?>" alt="<?= e($a['title']) ?>" loading="lazy"></div>
          <div class="body">
            <h3><?= e($a['title']) ?></h3>
            <p><?= e($a['description']) ?></p>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$activities): ?>
      <p>Student life activities will appear here once added in the admin panel.</p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
