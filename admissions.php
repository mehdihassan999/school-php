<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$forms = fetch_all("SELECT * FROM downloads WHERE published = 1 AND (category LIKE '%admission%' OR title LIKE '%form%') ORDER BY created_at DESC");
$reqs  = text_lines(setting('admission_requirements'));
$fees  = text_pairs(setting('fee_structure'));
$dates = text_pairs(setting('admission_dates'));

$steps = [
  ['&#128203;', 'Submit Application', 'Complete the online application form and upload the required documents.'],
  ['&#128269;', 'Application Review', 'Our admissions team reviews every application within 5 working days.'],
  ['&#128196;', 'Entrance Assessment', 'Age-appropriate assessment in English and mathematics, plus a classroom visit.'],
  ['&#128101;', 'Interview', 'A friendly conversation with the student and parents.'],
  ['&#9989;', 'Admission Confirmation', 'Offer letters are issued; enrollment is confirmed on fee payment.'],
];

$pageTitle = 'Admissions';
$pageDesc  = 'Admissions at ' . setting('school_name') . ' — process, requirements, fee structure, important dates and online application.';
$pageKicker = setting('admission_open') === '1' ? 'Admissions Open' : 'Admissions';
$pageHeading = 'Join the ' . setting('school_short') . ' Family';
$pageSub = setting('admission_note');
$pageCrumb = 'Admissions';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Admission Process</p>
      <h2>Five Simple Steps to Enrollment</h2>
    </div>
    <div class="grid g4 steps">
      <?php foreach ($steps as $s): ?>
      <div class="card step card-hover reveal">
        <span class="step-ico"><?= $s[0] ?></span>
        <h3><?= e($s[1]) ?></h3>
        <p><?= e($s[2]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:40px" class="reveal">
      <a class="btn btn-accent" href="<?= e(BASE_URL) ?>apply.php">Start Online Application</a>
    </p>
  </div>
</section>

<section class="section section-cream">
  <div class="wrap grid g2" style="align-items:start">
    <div class="reveal">
      <div class="section-head left"><p class="kicker">Admission Requirements</p><h2>What You&rsquo;ll Need</h2></div>
      <ul class="grid" style="gap:12px;margin-top:16px">
        <?php foreach ($reqs as $r): ?>
        <li class="card contact-card"><span class="ico">&#128196;</span><span style="font-size:14px;font-weight:500"><?= e($r) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="reveal">
      <div class="section-head left"><p class="kicker">Important Dates</p><h2>Key Admission Dates</h2></div>
      <ol class="timeline" style="margin-top:22px">
        <?php foreach ($dates as $d): ?>
        <li><b><?= e($d['title']) ?></b><span><?= e($d['desc']) ?></span></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Fee Structure</p>
      <h2>Transparent, Value-Driven Fees</h2>
      <p>Indicative fees. Contact the admissions office for the detailed schedule and payment plans.</p>
    </div>
    <div class="table-wrap reveal" style="max-width:720px;margin:0 auto">
      <table>
        <thead><tr><th>Grade Level</th><th class="t-right">Tuition (per term)</th></tr></thead>
        <tbody>
        <?php foreach ($fees as $f): ?>
          <tr><td style="font-weight:500"><?= e($f['title']) ?></td><td class="t-right" style="font-weight:600;color:var(--accent)"><?= e($f['desc']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="text-align:center;font-size:12.5px;color:var(--ink-soft);margin-top:14px">Fees shown are demo placeholder values.</p>
  </div>
</section>

<section class="section section-cream">
  <div class="wrap grid g2" style="align-items:center">
    <div class="reveal">
      <div class="section-head left"><p class="kicker">Download Forms</p><h2>Prefer Paper? Start Here.</h2></div>
      <ul class="grid" style="gap:12px;margin-top:16px">
        <?php foreach ($forms as $f): ?>
        <li>
          <a class="card card-hover contact-card" href="<?= e(asset_url($f['file_path'])) ?>" download>
            <span class="ico">&#11015;</span>
            <span><b style="text-transform:none;letter-spacing:0;font-size:14px"><?= e($f['title']) ?></b>
            <span style="font-weight:400;font-size:12.5px;color:var(--ink-soft)"><?= e($f['description']) ?></span></span>
          </a>
        </li>
        <?php endforeach; ?>
        <?php if (!$forms): ?><li style="font-size:14px;color:var(--ink-soft)">Upload admission forms in the admin panel (Content → Downloads).</li><?php endif; ?>
      </ul>
    </div>
    <div class="card card-pad reveal" style="background:var(--accent-deep);color:#fff;border:0">
      <h3 style="color:#fff">Questions About Admissions?</h3>
      <p style="color:rgba(255,255,255,.7);font-size:14px;margin-top:12px">Our admissions office is happy to help — call, email or WhatsApp us for a campus tour.</p>
      <div style="display:grid;gap:11px;margin-top:22px;font-size:14px">
        <a href="<?= e(tel_href(setting('phone'))) ?>">&#9742; <?= e(setting('phone')) ?></a>
        <a href="mailto:<?= e(setting('admissions_email')) ?>">&#9993; <?= e(setting('admissions_email')) ?></a>
      </div>
      <div style="display:flex;gap:11px;margin-top:24px;flex-wrap:wrap">
        <a class="btn btn-gold btn-sm" href="<?= e(BASE_URL) ?>apply.php">Apply Online</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(BASE_URL) ?>contact.php">Contact Us</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
