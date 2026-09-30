<?php
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

$statuses = ['new' => 'New', 'under_review' => 'Under Review', 'contacted' => 'Contacted',
             'accepted' => 'Accepted', 'rejected' => 'Rejected'];

/* --------------------------- status / delete posts ------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id     = post_int('id');
    $action = post('action', 20);

    if ($action === 'status' && $id && isset($statuses[post('status', 20)])) {
        update_row('admissions', ['status' => post('status', 20)], 'id = ?', [$id]);
        flash('ok', 'Application status updated.');
        $back = post('back', 200);
        redirect(strpos($back, BASE_URL . 'admin/') === 0 ? $back : BASE_URL . 'admin/admissions.php');
    }
    if ($action === 'delete' && $id) {
        delete_row('admissions', 'id = ?', [$id]);
        flash('ok', 'Application deleted.');
        redirect(BASE_URL . 'admin/admissions.php');
    }
}

$search  = trim((string) ($_GET['q'] ?? ''));
$status  = trim((string) ($_GET['status'] ?? ''));
$view    = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

/* ------------------------------ detail view ------------------------------- */
if ($view > 0) {
    $app = fetch_one('SELECT * FROM admissions WHERE id = ? LIMIT 1', [$view]);
    if (!$app) {
        redirect(BASE_URL . 'admin/admissions.php');
    }

    $info = [
        'Student Name'      => $app['student_name'],
        'Date of Birth'     => $app['dob'],
        'Gender'            => $app['gender'],
        'Applying for Grade' => $app['grade'],
        'Previous School'   => $app['previous_school'] ?: '—',
        'Parent / Guardian' => $app['parent_name'],
        'Relationship'      => $app['relationship'],
        'Phone'             => $app['phone'],
        'Email'             => $app['email'],
        'Address'           => $app['address'] ?: '—',
        'Emergency Contact' => $app['emergency_contact'] ?: '—',
        'Submitted'         => fmt_datetime($app['created_at']),
    ];

    $adminTitle = 'Application — ' . $app['student_name'];
    $adminDesc  = 'Reference #' . str_pad((string) $app['id'], 5, '0', STR_PAD_LEFT);
    $adminActions = '<a class="btn btn-outline btn-sm" href="' . e(BASE_URL) . 'admin/admissions.php">&larr; All Applications</a>';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="grid" style="grid-template-columns:1.5fr 1fr;gap:24px">
      <div class="card card-pad">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:11px;flex-wrap:wrap;margin-bottom:20px">
          <h3>Application Details</h3><?= status_badge($app['status']) ?>
        </div>
        <dl class="grid g2" style="gap:17px 30px">
          <?php foreach ($info as $label => $value): ?>
          <div>
            <dt style="font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-soft)"><?= e($label) ?></dt>
            <dd style="margin:3px 0 0;font-size:14px;font-weight:500"><?= e($value) ?></dd>
          </div>
          <?php endforeach; ?>
        </dl>
        <?php if ($app['additional_info']): ?>
        <div style="background:var(--cream);border-radius:11px;padding:16px;margin-top:22px">
          <p style="font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-soft);margin:0 0 6px">Additional Information</p>
          <p style="white-space:pre-line;font-size:14px;margin:0"><?= e($app['additional_info']) ?></p>
        </div>
        <?php endif; ?>
      </div>

      <div style="display:grid;gap:20px;align-content:start">
        <div class="card card-pad">
          <h3>Change Status</h3>
          <form method="post" style="display:flex;gap:9px;margin-top:15px">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="id" value="<?= (int) $app['id'] ?>">
            <input type="hidden" name="back" value="<?= e(BASE_URL . 'admin/admissions.php?view=' . (int) $app['id']) ?>">
            <select name="status"><?php foreach ($statuses as $k => $v): ?>
              <option value="<?= e($k) ?>" <?= $app['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?></select>
            <button class="btn btn-accent btn-sm" type="submit">Update</button>
          </form>
        </div>

        <div class="card card-pad">
          <h3>Document</h3>
          <?php if ($app['document']): ?>
            <a class="btn btn-outline btn-sm btn-block" style="margin-top:15px" href="<?= e(asset_url($app['document'])) ?>" download>Open Submitted Document</a>
          <?php else: ?>
            <p style="font-size:14px;margin-top:11px">No document was uploaded with this application.</p>
          <?php endif; ?>
        </div>

        <div class="card card-pad">
          <h3>Export</h3>
          <a class="btn btn-accent btn-sm btn-block" style="margin-top:15px" href="<?= e(BASE_URL) ?>admin/export.php?type=admissions">Download All as CSV</a>
        </div>

        <div class="card card-pad">
          <h3>Danger Zone</h3>
          <p style="font-size:12.5px;color:var(--ink-soft)">Deleting removes this application permanently.</p>
          <form method="post" data-confirm="Delete the application for <?= e($app['student_name']) ?>? This cannot be undone." style="margin-top:15px">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $app['id'] ?>">
            <button class="btn btn-danger btn-block" type="submit">Delete Application</button>
          </form>
        </div>
      </div>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* -------------------------------- list view ------------------------------- */
$where  = ['1=1'];
$params = [];
if ($status !== '' && isset($statuses[$status])) {
    $where[]  = 'status = ?';
    $params[] = $status;
}
if ($search !== '') {
    $where[]  = '(student_name LIKE ? OR parent_name LIKE ? OR email LIKE ? OR grade LIKE ?)';
    $like     = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = implode(' AND ', $where);

$total = (int) fetch_val("SELECT COUNT(*) FROM admissions WHERE $whereSql", $params);
$pages = max(1, (int) ceil($total / $perPage));
$page  = min($page, $pages);
$rows  = fetch_all("SELECT * FROM admissions WHERE $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);

$qs = function (array $over = []) use ($search, $status) {
    $p = array_filter(['q' => $search, 'status' => $status], fn ($v) => $v !== '');
    foreach ($over as $k => $v) {
        if ($v === '' || $v === null) { unset($p[$k]); } else { $p[$k] = $v; }
    }
    return BASE_URL . 'admin/admissions.php' . ($p ? '?' . http_build_query($p) : '');
};

$adminTitle = 'Admission Applications';
$adminDesc  = $total . ' application' . ($total === 1 ? '' : 's') . ' received.';
$adminActions = '<a class="btn btn-accent btn-sm" href="' . e(BASE_URL) . 'admin/export.php?type=admissions">Export CSV</a>';
require __DIR__ . '/includes/header.php';
?>

<div class="filters-bar">
  <form class="search-form" method="get" action="<?= e(BASE_URL) ?>admin/admissions.php">
    <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search student, parent, email…">
    <button class="btn btn-accent btn-sm" type="submit">Search</button>
  </form>
  <div class="filters">
    <a class="<?= $status === '' ? 'is-current' : '' ?>" href="<?= e($qs(['status' => null, 'page' => null])) ?>">All</a>
    <?php foreach ($statuses as $k => $v): ?>
      <a class="<?= $status === $k ? 'is-current' : '' ?>" href="<?= e($qs(['status' => $k, 'page' => null])) ?>"><?= e($v) ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Student</th><th>Grade</th><th>Parent / Contact</th><th>Applied</th><th>Status</th><th class="t-right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <a style="color:var(--accent);font-weight:600" href="<?= e($qs(['view' => $r['id']])) ?>"><?= e($r['student_name']) ?></a>
          <span style="display:block;font-size:11.5px;color:var(--ink-soft)"><?= e($r['gender']) ?> · DOB <?= e($r['dob']) ?></span>
        </td>
        <td><?= e($r['grade']) ?></td>
        <td>
          <span style="display:block;font-size:13.5px;font-weight:500"><?= e($r['parent_name']) ?></span>
          <span style="display:block;font-size:11.5px;color:var(--ink-soft)"><?= e($r['phone']) ?> · <?= e($r['email']) ?></span>
        </td>
        <td style="white-space:nowrap;font-size:12px;color:var(--ink-soft)"><?= e(fmt_datetime($r['created_at'])) ?></td>
        <td>
          <form method="post" style="display:flex;gap:6px;align-items:center">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <input type="hidden" name="back" value="<?= e($qs()) ?>">
            <select name="status" style="padding:6px 8px;font-size:12px;width:auto">
              <?php foreach ($statuses as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-outline btn-icon" type="submit">Set</button>
          </form>
        </td>
        <td>
          <div class="actions">
            <a class="btn btn-outline btn-icon" href="<?= e($qs(['view' => $r['id']])) ?>">View</a>
            <form method="post" data-confirm="Delete the application for <?= e($r['student_name']) ?>? This cannot be undone.">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn-danger btn-icon" type="submit">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="6" style="text-align:center;padding:38px">No applications found<?= $search || $status ? ' with these filters' : '' ?>.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($pages > 1): ?>
<nav class="pager" aria-label="Pagination">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if (abs($i - $page) < 3 || $i === 1 || $i === $pages): ?>
      <a class="<?= $i === $page ? 'is-current' : '' ?>" href="<?= e($qs(['page' => $i])) ?>"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>
</nav>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
