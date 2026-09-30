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
$files = fetch_all("SELECT * FROM downloads WHERE $where ORDER BY created_at DESC", $params);
$cats  = fetch_all('SELECT DISTINCT category FROM downloads WHERE published = 1 ORDER BY category');

$pageTitle = 'Downloads';
$pageDesc  = 'Download admission forms, prospectus, fee structure, academic calendar and other documents from ' . setting('school_name') . '.';
$pageKicker = 'Downloads';
$pageHeading = 'Forms & Documents';
$pageSub = 'Everything you need — admission forms, prospectus, calendars and schedules.';
$pageCrumb = 'Downloads';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <?php if ($cats): ?>
    <div class="filters">
      <a class="<?= $category === '' ? 'is-current' : '' ?>" href="<?= e(BASE_URL) ?>downloads.php">All Documents</a>
      <?php foreach ($cats as $c): ?>
        <a class="<?= $category === $c['category'] ? 'is-current' : '' ?>" href="<?= e(BASE_URL) ?>downloads.php?category=<?= e(rawurlencode($c['category'])) ?>"><?= e($c['category']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="grid g3">
      <?php foreach ($files as $f): ?>
      <article class="card card-hover card-pad reveal" style="display:flex;flex-direction:column">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span class="notice-ico">&#128196;</span>
          <span class="badge badge-grey"><?= e($f['category']) ?></span>
        </div>
        <h3 style="margin-top:16px"><?= e($f['title']) ?></h3>
        <p style="flex:1"><?= e($f['description']) ?></p>
        <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--line);margin-top:18px;padding-top:15px;gap:11px;flex-wrap:wrap">
          <span style="font-size:12px;color:var(--ink-soft)">Added <?= e(fmt_date($f['created_at'])) ?></span>
          <?php if ($f['file_path']): ?>
            <a class="btn btn-accent btn-sm" href="<?= e(asset_url($f['file_path'])) ?>" download="<?= e($f['file_name'] ?: '') ?>">&#11015; Download</a>
          <?php else: ?>
            <span style="font-size:12px;color:var(--ink-soft)">Unavailable</span>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if (!$files): ?>
      <div class="card card-pad" style="max-width:420px;margin:0 auto;text-align:center">
        <p style="font-size:30px">&#128230;</p>
        <p>No documents published yet.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
