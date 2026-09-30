<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

if ($slug !== '') {
    $item = fetch_one('SELECT * FROM events WHERE slug = ? AND published = 1 LIMIT 1', [$slug]);
    if (!$item) {
        http_response_code(404);
        $pageTitle = 'Not Found';
        require __DIR__ . '/includes/header.php';
        echo '<section class="section"><div class="wrap" style="text-align:center">'
           . '<h2 class="display" style="font-size:56px;color:var(--gold)">404</h2>'
           . '<p>The event you are looking for could not be found.</p>'
           . '<p style="margin-top:22px"><a class="btn btn-outline" href="' . e(BASE_URL) . 'events.php">Back to Events</a></p>'
           . '</div></section>';
        require __DIR__ . '/includes/footer.php';
        exit;
    }

    $others = fetch_all('SELECT * FROM events WHERE published = 1 AND id <> ? ORDER BY event_date DESC LIMIT 3', [$item['id']]);
    $d = strtotime($item['event_date']);

    $pageTitle = $item['title'];
    $pageDesc  = $item['excerpt'];
    $canonical = BASE_URL . 'events.php?slug=' . rawurlencode($item['slug']);
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="page-hero">
      <div class="wrap">
        <nav class="crumbs"><a href="<?= e(BASE_URL) ?>index.php">Home</a> <span>/</span>
          <a href="<?= e(BASE_URL) ?>events.php">Events</a> <span>/</span> <em><?= e(excerpt($item['title'], 40)) ?></em></nav>
        <div style="display:flex;gap:19px;align-items:center;flex-wrap:wrap;margin-top:8px">
          <div class="date-chip" style="background:var(--gold);color:#fff;width:76px;height:76px;border-radius:14px">
            <b style="font-size:27px"><?= e(date('j', $d)) ?></b>
            <span><?= e(date('M Y', $d)) ?></span>
          </div>
          <div>
            <h1 class="display" style="font-size:clamp(26px,4vw,38px)"><?= e($item['title']) ?></h1>
            <p class="page-sub" style="margin-top:9px"><?= e(fmt_date($item['event_date'])) ?><?= $item['location'] ? ' · ' . e($item['location']) : '' ?></p>
          </div>
        </div>
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
        <aside class="card card-pad reveal" style="height:fit-content">
          <h3>More Events</h3>
          <ul style="margin-top:17px;display:grid;gap:17px">
            <?php foreach ($others as $o): $od = strtotime($o['event_date']); ?>
            <li><a class="event-row" href="<?= e(BASE_URL) ?>events.php?slug=<?= e($o['slug']) ?>">
              <span class="date-chip"><b><?= e(date('j', $od)) ?></b><span><?= e(date('M', $od)) ?></span></span>
              <span><b style="font-size:13.5px;display:block"><?= e(excerpt($o['title'], 42)) ?></b>
              <span style="font-size:11.5px;color:var(--ink-soft)"><?= e($o['location']) ?></span></span>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </aside>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$all     = fetch_all('SELECT * FROM events WHERE published = 1 ORDER BY event_date DESC');
$upcoming = array_values(array_filter($all, fn ($e) => strtotime($e['event_date']) >= time()));
usort($upcoming, fn ($a, $b) => strtotime($a['event_date']) <=> strtotime($b['event_date']));
$past = array_values(array_filter($all, fn ($e) => strtotime($e['event_date']) < time()));

$card = function ($e, $muted = false) {
    $d = strtotime($e['event_date']); ?>
    <a class="card card-hover media-card reveal" style="flex-direction:row;<?= $muted ? 'opacity:.72' : '' ?>" href="<?= e(BASE_URL) ?>events.php?slug=<?= e($e['slug']) ?>">
      <div class="thumb" style="flex:0 0 200px;aspect-ratio:auto;min-height:150px"><img src="<?= e(img($e['image'])) ?>" alt="<?= e($e['title']) ?>" loading="lazy"></div>
      <div class="body">
        <div class="event-row" style="margin-bottom:11px">
          <span class="date-chip"><b><?= e(date('j', $d)) ?></b><span><?= e(date('M', $d)) ?></span></span>
          <span><b class="display" style="font-size:17px"><?= e($e['title']) ?></b>
          <span style="display:block;font-size:12px;color:var(--ink-soft)"><?= e(fmt_date($e['event_date'])) ?><?= $e['location'] ? ' · ' . e($e['location']) : '' ?></span></span>
        </div>
        <p><?= e(excerpt($e['excerpt'], 130)) ?></p>
      </div>
    </a>
    <?php
};

$pageTitle = 'Events';
$pageDesc  = setting('school_name') . ' events calendar — celebrations, performances, exhibitions and important dates.';
$pageKicker = 'Events';
$pageHeading = 'School Events Calendar';
$pageSub = 'Performances, exhibitions, tournaments and celebrations — mark your calendar.';
$pageCrumb = 'Events';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap" style="display:grid;gap:62px">
    <div>
      <div class="section-head left reveal"><p class="kicker">Coming Up</p><h2>Upcoming Events</h2></div>
      <div class="grid" style="gap:20px">
        <?php foreach ($upcoming as $e) { $card($e); } ?>
        <?php if (!$upcoming): ?><p>No upcoming events scheduled — check back soon.</p><?php endif; ?>
      </div>
    </div>
    <?php if ($past): ?>
    <div>
      <div class="section-head left reveal"><p class="kicker">Archive</p><h2>Past Events</h2></div>
      <div class="grid" style="gap:20px">
        <?php foreach (array_slice($past, 0, 6) as $e) { $card($e, true); } ?>
      </div>
    </div>
    <?php endif; ?>
    <p style="text-align:center"><a class="btn btn-outline" href="<?= e(BASE_URL) ?>news.php">Read Latest News</a></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
