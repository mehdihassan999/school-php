<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

/* --------------------------- single news article -------------------------- */
if ($slug !== '') {
    $item = fetch_one('SELECT * FROM news WHERE slug = ? AND published = 1 LIMIT 1', [$slug]);
    if (!$item) {
        http_response_code(404);
        $pageTitle = 'Not Found';
        require __DIR__ . '/includes/header.php';
        echo '<section class="section"><div class="wrap" style="text-align:center">'
           . '<h2 class="display" style="font-size:56px;color:var(--gold)">404</h2>'
           . '<p>The news story you are looking for could not be found.</p>'
           . '<p style="margin-top:22px"><a class="btn btn-outline" href="' . e(BASE_URL) . 'news.php">Back to News</a></p>'
           . '</div></section>';
        require __DIR__ . '/includes/footer.php';
        exit;
    }

    $related = fetch_all('SELECT * FROM news WHERE published = 1 AND id <> ? ORDER BY published_at DESC LIMIT 3', [$item['id']]);

    $pageTitle = $item['title'];
    $pageDesc  = $item['excerpt'];
    $canonical = BASE_URL . 'news.php?slug=' . rawurlencode($item['slug']);
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="page-hero" style="background:var(--accent-deep)">
      <div class="wrap">
        <nav class="crumbs"><a href="<?= e(BASE_URL) ?>index.php">Home</a> <span>/</span>
          <a href="<?= e(BASE_URL) ?>news.php">News</a> <span>/</span> <em><?= e(excerpt($item['title'], 40)) ?></em></nav>
        <p class="kicker kicker-light"><?= e($item['category']) ?> · <?= e(fmt_date($item['published_at'])) ?></p>
        <h1 class="display" style="max-width:820px"><?= e($item['title']) ?></h1>
        <p class="page-sub"><?= e($item['excerpt']) ?></p>
      </div>
    </section>

    <section class="section">
      <div class="wrap grid" style="grid-template-columns:1.6fr 1fr;gap:44px">
        <article class="reveal">
          <?php if ($item['image']): ?>
            <img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" style="border-radius:var(--radius);aspect-ratio:16/8;object-fit:cover">
          <?php endif; ?>
          <div class="prose" style="margin-top:28px">
            <?php foreach (text_paragraphs($item['content'] ?: $item['excerpt']) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
          </div>
        </article>
        <aside class="reveal" style="display:grid;gap:22px;align-content:start">
          <div class="card card-pad">
            <h3>Filed Under</h3>
            <a class="badge" style="background:var(--accent-soft);color:var(--accent);margin-top:14px" href="<?= e(BASE_URL) ?>news.php?category=<?= e(rawurlencode($item['category'])) ?>"><?= e($item['category']) ?></a>
          </div>
          <div class="card card-pad">
            <h3>More Stories</h3>
            <ul style="margin-top:17px;display:grid;gap:16px">
              <?php foreach ($related as $r): ?>
              <li><a class="event-row" href="<?= e(BASE_URL) ?>news.php?slug=<?= e($r['slug']) ?>">
                <img src="<?= e(img($r['image'])) ?>" alt="" style="width:66px;height:56px;object-fit:cover;border-radius:8px" loading="lazy">
                <span><b style="font-size:13.5px;display:block"><?= e(excerpt($r['title'], 46)) ?></b>
                <span style="font-size:11.5px;color:var(--ink-soft)"><?= e(fmt_date($r['published_at'])) ?></span></span>
              </a></li>
              <?php endforeach; ?>
            </ul>
            <a class="more" style="display:inline-block;margin-top:18px;color:var(--accent);font-weight:600;font-size:13.5px" href="<?= e(BASE_URL) ?>news.php">All News &rarr;</a>
          </div>
        </aside>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* ------------------------------ news listing ------------------------------ */
$search   = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 9;

$where  = ['published = 1'];
$params = [];
if ($search !== '') {
    $where[]  = '(title LIKE ? OR excerpt LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($category !== '') {
    $where[]  = 'category = ?';
    $params[] = $category;
}
$whereSql = implode(' AND ', $where);

$total    = (int) fetch_val("SELECT COUNT(*) FROM news WHERE $whereSql", $params);
$pages    = max(1, (int) ceil($total / $perPage));
$page     = min($page, $pages);
$rows     = fetch_all("SELECT * FROM news WHERE $whereSql ORDER BY published_at DESC LIMIT " . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $params);
$cats     = fetch_all('SELECT DISTINCT category FROM news WHERE published = 1 ORDER BY category');

$qs = function (array $over = []) use ($search, $category) {
    $p = array_filter(['q' => $search, 'category' => $category], fn ($v) => $v !== '');
    foreach ($over as $k => $v) {
        if ($v === '' || $v === null) { unset($p[$k]); } else { $p[$k] = $v; }
    }
    return BASE_URL . 'news.php' . ($p ? '?' . http_build_query($p) : '');
};

$pageTitle = 'News & Events';
$pageDesc  = 'Latest news, announcements and stories from ' . setting('school_name') . '.';
$pageKicker = 'News & Events';
$pageHeading = 'School News & Stories';
$pageSub = 'Announcements, achievements and happenings around campus.';
$pageCrumb = 'News';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="filters-bar">
      <form class="search-form" method="get" action="<?= e(BASE_URL) ?>news.php" role="search">
        <?php if ($category): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search news…" aria-label="Search news">
        <button class="btn btn-accent btn-sm" type="submit">Search</button>
      </form>
      <div class="filters">
        <a class="<?= $category === '' ? 'is-current' : '' ?>" href="<?= e($qs(['category' => null, 'page' => null])) ?>">All</a>
        <?php foreach ($cats as $c): ?>
          <a class="<?= $category === $c['category'] ? 'is-current' : '' ?>" href="<?= e($qs(['category' => $c['category'], 'page' => null])) ?>"><?= e($c['category']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="grid g3">
      <?php foreach ($rows as $n): ?>
      <a class="card card-hover media-card reveal" href="<?= e(BASE_URL) ?>news.php?slug=<?= e($n['slug']) ?>">
        <div class="thumb"><img src="<?= e(img($n['image'])) ?>" alt="<?= e($n['title']) ?>" loading="lazy"></div>
        <div class="body">
          <p class="meta"><b><?= e($n['category']) ?></b> <span><?= e(fmt_date($n['published_at'])) ?></span></p>
          <h3><?= e($n['title']) ?></h3>
          <p><?= e(excerpt($n['excerpt'], 105)) ?></p>
          <span class="more">Read Story &rarr;</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if (!$rows): ?>
      <div class="card card-pad" style="max-width:420px;margin:0 auto;text-align:center">
        <p style="font-size:32px">&#128240;</p>
        <p>No news found<?= $search ? ' for &ldquo;' . e($search) . '&rdquo;' : '' ?>.</p>
      </div>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Pagination">
      <?php if ($page > 1): ?><a href="<?= e($qs(['page' => $page - 1])) ?>">&lsaquo; Prev</a><?php endif; ?>
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if (abs($i - $page) < 3 || $i === 1 || $i === $pages): ?>
          <a class="<?= $i === $page ? 'is-current' : '' ?>" href="<?= e($qs(['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $pages): ?><a href="<?= e($qs(['page' => $page + 1])) ?>">Next &rsaquo;</a><?php endif; ?>
    </nav>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
