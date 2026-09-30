<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$category = trim((string) ($_GET['category'] ?? ''));
$where    = 'published = 1';
$params   = [];
if ($category !== '') {
    $where .= ' AND category = ?';
    $params[] = $category;
}
$notices = fetch_all("SELECT * FROM notices WHERE $where ORDER BY notice_date DESC", $params);
$cats    = fetch_all('SELECT DISTINCT category FROM notices WHERE published = 1 ORDER BY category');

$badgeColors = [
    'Holiday' => 'badge-amber', 'Examination' => 'badge-red', 'Admission' => 'badge-green',
    'Announcement' => 'badge-sky', 'Circular' => 'badge-violet', 'General' => 'badge-grey',
];

$pageTitle = 'Notice Board';
$pageDesc  = 'Official notices and circulars from ' . setting('school_name') . ' — holidays, examinations, admissions and announcements.';
$pageKicker = 'Notice Board';
$pageHeading = 'Official Notices & Circulars';
$pageSub = 'Stay informed — holidays, examinations, admissions and important announcements.';
$pageCrumb = 'Notice Board';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <?php if ($cats): ?>
    <div class="filters">
      <a class="<?= $category === '' ? 'is-current' : '' ?>" href="<?= e(BASE_URL) ?>notices.php">All Notices</a>
      <?php foreach ($cats as $c): ?>
        <a class="<?= $category === $c['category'] ? 'is-current' : '' ?>" href="<?= e(BASE_URL) ?>notices.php?category=<?= e(rawurlencode($c['category'])) ?>"><?= e($c['category']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div style="max-width:860px;margin:0 auto;display:grid;gap:17px">
      <?php foreach ($notices as $n): ?>
      <article class="card card-hover notice reveal">
        <div class="notice-head">
          <span class="notice-ico">&#128276;</span>
          <div>
            <span class="badge <?= e($badgeColors[$n['category']] ?? 'badge-grey') ?>"><?= e($n['category']) ?></span>
            <span class="when" style="margin-left:9px"><?= e(fmt_date($n['notice_date'])) ?></span>
            <h3><?= e($n['title']) ?></h3>
            <p><?= e($n['description']) ?></p>
            <?php if ($n['document']): ?>
              <a class="btn btn-outline btn-sm" style="margin-top:14px" href="<?= e(asset_url($n['document'])) ?>" download>&#11015; Download Attachment</a>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (!$notices): ?>
        <div class="card card-pad" style="text-align:center">
          <p style="font-size:30px">&#128444;</p>
          <p>No notices published yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
