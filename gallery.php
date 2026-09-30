<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$albums = fetch_all('SELECT * FROM gallery_albums WHERE published = 1 ORDER BY created_at DESC');
$category = trim((string) ($_GET['category'] ?? ''));

$shown = $category === ''
    ? $albums
    : array_values(array_filter($albums, fn ($a) => $a['category'] === $category));

$images = [];
foreach ($shown as $a) {
    foreach (fetch_all('SELECT src, caption FROM gallery_images WHERE album_id = ? ORDER BY sort_order, id', [$a['id']]) as $img) {
        $images[] = ['src' => $img['src'], 'caption' => $img['caption'], 'album' => $a['title'], 'category' => $a['category']];
    }
}
$cats = fetch_all('SELECT DISTINCT category FROM gallery_albums WHERE published = 1 ORDER BY category');

$pageTitle = 'Photo Gallery';
$pageDesc  = 'Photo galleries from ' . setting('school_name') . ' — campus life, events, sports, trips and student moments.';
$pageKicker = 'Gallery';
$pageHeading = 'Moments & Memories';
$pageSub = 'Browse albums from campus life, celebrations, sport and adventure.';
$pageCrumb = 'Gallery';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <?php if ($cats): ?>
    <div class="filters">
      <a class="<?= $category === '' ? 'is-current' : '' ?>" href="<?= e(BASE_URL) ?>gallery.php">All</a>
      <?php foreach ($cats as $c): ?>
        <a class="<?= $category === $c['category'] ? 'is-current' : '' ?>" href="<?= e(BASE_URL) ?>gallery.php?category=<?= e(rawurlencode($c['category'])) ?>"><?= e($c['category']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="masonry" data-lightbox-group>
      <?php foreach ($images as $im): ?>
      <button class="gal" type="button" data-lightbox data-full="<?= e(asset_url($im['src'])) ?>" data-caption="<?= e($im['caption'] ?: $im['album']) ?>">
        <img src="<?= e(asset_url($im['src'])) ?>" alt="<?= e($im['caption'] ?: $im['album']) ?>" loading="lazy">
        <figcaption><?= e($im['caption'] ?: $im['album']) ?><span style="display:block;font-size:10px;font-weight:500;opacity:.75"><?= e($im['category']) ?></span></figcaption>
      </button>
      <?php endforeach; ?>
    </div>
    <?php if (!$images): ?>
      <p style="text-align:center;padding:56px 0">No images in this album yet.</p>
    <?php endif; ?>

    <?php if ($albums): ?>
    <div style="margin-top:64px">
      <h2 class="display" style="font-size:24px">Albums</h2>
      <div class="grid g4" style="margin-top:22px">
        <?php foreach ($albums as $a): $count = (int) fetch_val('SELECT COUNT(*) FROM gallery_images WHERE album_id = ?', [$a['id']]); ?>
        <div class="card card-hover reveal">
          <div class="thumb" style="aspect-ratio:4/3;overflow:hidden">
            <img src="<?= e(img($a['cover_image'] ?: '', 'hero')) ?>" alt="<?= e($a['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
          </div>
          <div class="body" style="padding:17px">
            <span class="badge" style="background:var(--accent-soft);color:var(--accent)"><?= e($a['category']) ?></span>
            <h3 style="font-size:16px;margin-top:9px"><?= e($a['title']) ?></h3>
            <p style="font-size:12.5px"><?= (int) $count ?> photos</p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
