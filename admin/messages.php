<?php
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id     = post_int('id');
    $action = post('action', 20);

    if ($action === 'read' && $id) {
        update_row('contact_messages', ['is_read' => post_bool('read') ? 1 : 0], 'id = ?', [$id]);
        flash('ok', 'Message updated.');
    } elseif ($action === 'delete' && $id) {
        delete_row('contact_messages', 'id = ?', [$id]);
        flash('ok', 'Message deleted.');
    }
    redirect(BASE_URL . 'admin/messages.php');
}

$rows  = fetch_all('SELECT * FROM contact_messages ORDER BY created_at DESC');
$unread = count(array_filter($rows, fn ($r) => !$r['is_read']));

$adminTitle = 'Contact Messages';
$adminDesc  = count($rows) . ' message' . (count($rows) === 1 ? '' : 's') . ' · ' . $unread . ' unread';
require __DIR__ . '/includes/header.php';
?>

<div style="display:grid;gap:15px">
  <?php foreach ($rows as $m): ?>
  <div class="card card-pad" style="<?= $m['is_read'] ? '' : 'border-left:4px solid var(--gold)' ?>">
    <details>
      <summary style="display:flex;gap:15px;align-items:center;flex-wrap:wrap;cursor:pointer">
        <span class="badge <?= $m['is_read'] ? 'badge-grey' : 'badge-amber' ?>"><?= $m['is_read'] ? 'Read' : 'Unread' ?></span>
        <span style="min-width:0;flex:1">
          <strong style="font-size:14px"><?= e($m['name']) ?></strong>
          <span style="font-size:12.5px;color:var(--ink-soft)"> <?= e($m['email']) ?><?= $m['phone'] ? ' · ' . e($m['phone']) : '' ?></span>
          <span style="display:block;font-size:12.5px;color:var(--ink-soft);margin-top:2px">
            <strong style="color:var(--ink)"><?= e($m['subject'] ?: 'General enquiry') ?></strong> — <?= e(excerpt($m['message'], 90)) ?>
          </span>
        </span>
        <span style="font-size:11.5px;color:var(--ink-soft);white-space:nowrap"><?= e(fmt_datetime($m['created_at'])) ?></span>
      </summary>

      <div style="border-top:1px solid var(--line);margin-top:19px;padding-top:19px">
        <p style="white-space:pre-line;font-size:14px;margin:0"><?= e($m['message']) ?></p>
        <div class="actions" style="justify-content:flex-start;margin-top:19px">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="read">
            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <input type="hidden" name="read" value="<?= $m['is_read'] ? '0' : '1' ?>">
            <button class="btn btn-outline btn-sm" type="submit">Mark as <?= $m['is_read'] ? 'Unread' : 'Read' ?></button>
          </form>
          <a class="btn btn-outline btn-sm" href="mailto:<?= e($m['email']) ?>?subject=<?= e(rawurlencode('Re: ' . ($m['subject'] ?: 'Your enquiry'))) ?>">Reply by Email</a>
          <form method="post" data-confirm="Delete the message from <?= e($m['name']) ?>?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
          </form>
        </div>
      </div>
    </details>
  </div>
  <?php endforeach; ?>

  <?php if (!$rows): ?>
  <div class="card card-pad" style="text-align:center;padding:56px">
    <p style="font-size:30px;margin:0">&#9993;</p>
    <p style="margin-top:15px">No messages yet — submissions from the contact form appear here.</p>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
