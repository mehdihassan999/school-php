<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$programs = fetch_all('SELECT * FROM academic_programs WHERE published = 1 ORDER BY sort_order, id');
$subjects = fetch_all('SELECT * FROM subjects WHERE published = 1 ORDER BY sort_order, id');
$events   = fetch_all('SELECT * FROM events WHERE published = 1 AND event_date >= NOW() ORDER BY event_date ASC LIMIT 6');

$levels = [];
foreach ($subjects as $s) {
    $levels[$s['level']][] = $s;
}

$pageTitle = 'Academics';
$pageDesc  = 'Explore academic programs at ' . setting('school_name') . ' — Early Years, Primary, Middle and Secondary school, curriculum, examinations and calendar.';
$pageKicker = 'Academics';
$pageHeading = 'Learning That Grows With Your Child';
$pageSub = 'From the first day of Early Years to the last exam of Grade 12 — one coherent, world-class journey.';
$pageCrumb = 'Academics';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap" style="display:grid;gap:80px">
    <?php foreach ($programs as $i => $p): ?>
    <article class="split reveal" id="<?= e($p['slug']) ?>" style="scroll-margin-top:110px">
      <div class="split-img" style="--pos:<?= $i % 2 ?>">
        <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
      </div>
      <div>
        <span class="badge" style="background:var(--accent-soft);color:var(--accent)"><?= e($p['level']) ?></span>
        <h2 class="display" style="font-size:29px;margin-top:13px"><?= e($p['title']) ?></h2>
        <p style="color:var(--ink-soft);margin:15px 0 0"><?= e($p['summary']) ?></p>
        <div class="prose" style="margin-top:14px">
          <?php foreach (array_slice(text_paragraphs($p['content']), 0, 4) as $para): ?>
            <p style="font-size:14px"><?= e($para) ?></p>
          <?php endforeach; ?>
        </div>
        <a class="btn btn-outline btn-sm" style="margin-top:18px" href="<?= e(BASE_URL) ?>apply.php">Apply for <?= e($p['level']) ?></a>
      </div>
    </article>
    <?php endforeach; ?>
    <?php if (!$programs): ?>
      <p>Academic programs will appear here once published in the admin panel.</p>
    <?php endif; ?>
  </div>
</section>

<?php if ($levels): ?>
<section class="section section-cream">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Subjects</p>
      <h2>A Broad, Balanced Curriculum</h2>
    </div>
    <div style="display:grid;gap:38px;margin-top:16px">
      <?php foreach ($levels as $level => $subs): ?>
      <div class="reveal">
        <h3 class="display" style="font-size:20px"><?= e($level) ?></h3>
        <div class="filters" style="justify-content:flex-start;margin-top:15px">
          <?php foreach ($subs as $sub): ?>
            <span title="<?= e($sub['description']) ?>"><?= e($sub['name']) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="wrap grid g2">
    <div class="card card-pad reveal">
      <h3>Our Curriculum</h3>
      <div class="prose" style="margin-top:16px">
        <?php foreach (text_paragraphs(setting('curriculum')) as $p): ?><p style="font-size:14px"><?= e($p) ?></p><?php endforeach; ?>
      </div>
    </div>
    <div class="card card-pad reveal">
      <h3>Examination System</h3>
      <div class="prose" style="margin-top:16px">
        <?php foreach (text_paragraphs(setting('exam_system')) as $p): ?><p style="font-size:14px"><?= e($p) ?></p><?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section section-cream">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Academic Calendar</p>
      <h2>Key Dates This Year</h2>
      <p>Full calendars are available in Downloads.</p>
    </div>
    <div style="max-width:720px;margin:0 auto;display:grid;gap:15px">
      <?php foreach ($events as $ev): $d = strtotime($ev['event_date']); ?>
      <a class="card card-hover event-row reveal" style="padding:19px" href="<?= e(BASE_URL) ?>events.php?slug=<?= e($ev['slug']) ?>">
        <span class="date-chip" style="background:var(--accent);color:#fff"><b><?= e(date('j', $d)) ?></b><span><?= e(date('M Y', $d)) ?></span></span>
        <span>
          <h3 class="display" style="font-size:18px"><?= e($ev['title']) ?></h3>
          <p><?= e(fmt_date($ev['event_date'])) ?> · <?= e($ev['location']) ?></p>
        </span>
      </a>
      <?php endforeach; ?>
      <?php if (!$events): ?><p>Calendar dates will appear here.</p><?php endif; ?>
    </div>
    <p style="text-align:center;margin-top:34px"><a class="btn btn-accent" href="<?= e(BASE_URL) ?>downloads.php">Download Full Academic Calendar</a></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
