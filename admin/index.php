<?php
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

$stat = fn ($sql) => (int) fetch_val($sql);

$stats = [
    'Faculty Members'      => [$stat('SELECT COUNT(*) FROM faculty'), 'content.php?entity=faculty', 'bg-sky'],
    'Applications'         => [$stat('SELECT COUNT(*) FROM admissions'), 'admissions.php', 'bg-violet'],
    'New Applications'     => [$stat("SELECT COUNT(*) FROM admissions WHERE status='new'"), 'admissions.php?status=new', 'bg-green'],
    'Unread Messages'      => [$stat('SELECT COUNT(*) FROM contact_messages WHERE is_read=0'), 'messages.php', 'bg-amber'],
    'Published News'       => [$stat('SELECT COUNT(*) FROM news WHERE published=1'), 'content.php?entity=news', 'bg-red'],
    'Upcoming Events'      => [$stat('SELECT COUNT(*) FROM events WHERE published=1 AND event_date >= NOW()'), 'content.php?entity=events', 'bg-indigo'],
    'Active Notices'       => [$stat('SELECT COUNT(*) FROM notices WHERE published=1'), 'content.php?entity=notices', 'bg-cyan'],
    'Gallery Images'       => [$stat('SELECT COUNT(*) FROM gallery_images'), 'gallery.php', 'bg-lime'],
    'Public Downloads'     => [$stat('SELECT COUNT(*) FROM downloads WHERE published=1'), 'content.php?entity=downloads', 'bg-orange'],
];

/* applications per month, last 6 months */
$dates = fetch_all('SELECT created_at FROM admissions ORDER BY created_at DESC LIMIT 1000');
$bars  = [];
for ($i = 5; $i >= 0; $i--) {
    $m      = mktime(0, 0, 0, (int) date('n') - $i, 1, (int) date('Y'));
    $count  = 0;
    foreach ($dates as $d) {
        $t = strtotime($d['created_at']);
        if ($t && date('n', $t) === date('n', $m) && date('Y', $t) === date('Y', $m)) {
            $count++;
        }
    }
    $bars[] = ['label' => date('M', $m), 'count' => $count];
}
$maxBar = max(1, max(array_column($bars, 'count')));

$statusRows = [];
foreach (['new', 'under_review', 'contacted', 'accepted', 'rejected'] as $s) {
    $statusRows[$s] = $stat("SELECT COUNT(*) FROM admissions WHERE status='" . $s . "'");
}
$totalStatus = max(1, array_sum($statusRows));

$recentApps = fetch_all('SELECT * FROM admissions ORDER BY created_at DESC LIMIT 5');
$recentMsgs = fetch_all('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5');

$adminTitle = 'Dashboard';
$adminDesc  = 'Everything happening across your website at a glance.';
$adminActions = '<a class="btn btn-accent btn-sm" href="' . e(BASE_URL) . 'admin/admissions.php">Review Applications</a>';
require __DIR__ . '/includes/header.php';
?>

<div class="stat-cards">
  <?php foreach ($stats as $label => [$value, $href, $color]): ?>
  <a class="stat-card card-hover" href="<?= e(BASE_URL . 'admin/' . $href) ?>">
    <span class="stat-ico" style="background:var(--accent-soft);color:var(--accent)">&#9636;</span>
    <b><?= (int) $value ?></b>
    <span><?= e($label) ?></span>
  </a>
  <?php endforeach; ?>
</div>

<div class="grid g2" style="margin-top:24px">
  <div class="card card-pad">
    <h3>Applications — Last 6 Months</h3>
    <div class="chart-bars">
      <?php foreach ($bars as $b): ?>
      <div class="chart-col">
        <b><?= (int) $b['count'] ?></b>
        <i style="height:<?= max(4, (int) round(($b['count'] / $maxBar) * 130)) ?>px"></i>
        <span><?= e($b['label']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card card-pad">
    <h3>Applications by Status</h3>
    <div style="display:grid;gap:15px;margin-top:22px">
      <?php foreach ($statusRows as $status => $count): ?>
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <?= status_badge($status) ?><span style="font-size:12.5px;font-weight:600;color:var(--ink-soft)"><?= (int) $count ?></span>
        </div>
        <div class="meter"><i style="width:<?= (int) round(($count / $totalStatus) * 100) ?>%"></i></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="grid g2" style="margin-top:24px">
  <div class="card card-pad">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h3>Latest Applications</h3>
      <a class="btn btn-outline btn-icon" href="<?= e(BASE_URL) ?>admin/admissions.php">View all</a>
    </div>
    <div class="table-wrap" style="border:0;margin-top:16px">
      <table style="min-width:0">
        <thead><tr><th>Student</th><th>Grade</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentApps as $a): ?>
          <tr>
            <td><a style="color:var(--accent);font-weight:600" href="<?= e(BASE_URL) ?>admin/admissions.php?view=<?= (int) $a['id'] ?>"><?= e($a['student_name']) ?></a></td>
            <td><?= e($a['grade']) ?></td>
            <td><?= status_badge($a['status']) ?></td>
            <td style="white-space:nowrap;font-size:12px;color:var(--ink-soft)"><?= e(fmt_datetime($a['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentApps): ?><tr><td colspan="4" style="text-align:center">No applications yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card card-pad">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h3>Recent Messages</h3>
      <a class="btn btn-outline btn-icon" href="<?= e(BASE_URL) ?>admin/messages.php">View all</a>
    </div>
    <ul style="margin-top:16px;display:grid;gap:2px">
      <?php foreach ($recentMsgs as $m): ?>
      <li style="display:flex;gap:11px;padding:11px 0;border-bottom:1px solid var(--line)">
        <span style="width:8px;height:8px;border-radius:99px;background:<?= $m['is_read'] ? 'var(--line)' : 'var(--gold)' ?>;margin-top:7px;flex:0 0 8px"></span>
        <span style="min-width:0">
          <strong style="font-size:13.5px"><?= e($m['name']) ?></strong>
          <span style="font-size:13.5px;color:var(--ink-soft)"> · <?= e($m['subject'] ?: 'General enquiry') ?></span>
          <span style="display:block;font-size:12px;color:var(--ink-soft);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($m['message']) ?></span>
        </span>
      </li>
      <?php endforeach; ?>
      <?php if (!$recentMsgs): ?><li style="text-align:center;padding:26px 0">No messages yet.</li><?php endif; ?>
    </ul>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
