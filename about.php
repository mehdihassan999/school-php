<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$leaders  = fetch_all("SELECT * FROM faculty WHERE published = 1 AND LOWER(department) = 'leadership' ORDER BY sort_order, id LIMIT 4");
$values   = text_pairs(setting('core_values'));

$pageTitle = 'About Us';
$pageDesc  = 'Learn about ' . setting('school_name') . ' — our history, mission, vision, core values and leadership.';
$pageKicker = 'About Us';
$pageHeading = 'A School Built on Purpose';
$pageSub     = setting('about_short');
$pageCrumb   = 'About';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap split">
    <div class="split-img reveal">
      <img src="<?= e(setting_asset('about_image')) ?>" alt="Students at <?= e(setting('school_name')) ?>" loading="lazy">
      <div class="badge-float"><b><?= e(setting('stat_years')) ?>+</b><span>Years of Excellence</span></div>
    </div>
    <div class="reveal">
      <div class="section-head left">
        <p class="kicker">Our History</p>
        <h2><?= e(setting('about_title')) ?></h2>
      </div>
      <div class="prose">
        <?php foreach (text_paragraphs(setting('about_content')) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section section-cream">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Mission &amp; Vision</p>
      <h2>What Drives Us Every Day</h2>
    </div>
    <div class="grid g2">
      <div class="card card-pad reveal" style="border-top:4px solid var(--accent)">
        <p class="kicker">Our Mission</p>
        <p style="font-size:15px;margin-top:14px"><?= e(setting('mission')) ?></p>
      </div>
      <div class="card card-pad reveal" style="border-top:4px solid var(--gold)">
        <p class="kicker">Our Vision</p>
        <p style="font-size:15px;margin-top:14px"><?= e(setting('vision')) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Core Values</p>
      <h2>The Principles We Live By</h2>
      <p>Values are not posters on our walls — they are habits our students practice daily.</p>
    </div>
    <div class="grid g3">
      <?php foreach ($values as $i => $v): ?>
      <div class="card card-pad card-hover reveal">
        <span class="avatar" style="width:44px;height:44px;border-radius:12px"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
        <h3 style="margin-top:15px"><?= e($v['title']) ?></h3>
        <p><?= e($v['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-cream">
  <div class="wrap">
    <div class="card principal reveal">
      <div class="principal-img"><img src="<?= e(setting_asset('principal_photo')) ?>" alt="<?= e(setting('principal_name')) ?>" loading="lazy"></div>
      <div class="principal-body">
        <span class="quote-mark" aria-hidden="true">&ldquo;</span>
        <p class="kicker">Principal&rsquo;s Message</p>
        <div class="prose" style="margin-top:16px">
          <?php foreach (text_paragraphs(setting('principal_message')) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
        </div>
        <div class="principal-sign">
          <span class="rule" aria-hidden="true"></span>
          <div><b><?= e(setting('principal_name')) ?></b><span><?= e(setting('principal_designation')) ?>, <?= e(setting('school_short')) ?></span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($leaders): ?>
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Leadership</p>
      <h2>Meet Our Leadership Team</h2>
    </div>
    <div class="grid g4">
      <?php foreach ($leaders as $p): ?>
      <div class="card card-hover reveal" style="text-align:center;padding:26px 22px">
        <?php if ($p['photo']): ?>
          <img class="avatar" style="width:120px;height:120px;border:4px solid color-mix(in srgb,var(--gold) 40%,#fff);margin:0 auto" src="<?= e(asset_url($p['photo'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        <?php else: ?>
          <span class="avatar" style="width:120px;height:120px;font-size:38px;margin:0 auto"><?= e(mb_substr($p['name'], 0, 1)) ?></span>
        <?php endif; ?>
        <h3 style="margin-top:18px"><?= e($p['name']) ?></h3>
        <p class="kicker" style="margin-top:6px;font-size:10.5px"><?= e($p['position']) ?></p>
        <p style="font-size:12.5px"><?= e($p['qualification']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:38px"><a class="btn btn-outline" href="<?= e(BASE_URL) ?>faculty.php">Meet All Faculty &amp; Staff</a></p>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
