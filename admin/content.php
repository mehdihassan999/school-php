<?php
/**
 * Generic content editor. One controller drives every entity defined in
 * admin/includes/entities.php: list, create, edit, delete, publish/unpublish.
 */
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

$entities = require __DIR__ . '/includes/entities.php';
$entity   = (string) ($_GET['entity'] ?? '');

if (!isset($entities[$entity])) {
    http_response_code(404);
    exit('Unknown content type.');
}

$def      = $entities[$entity];
$table    = $def['table'];
$editId   = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$selfBase = 'content.php?entity=' . $entity;
$err      = null;

/* ------------------------------ handle posts ----------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = post('action', 20);

    try {
        if ($action === 'delete') {
            delete_row($table, 'id = ?', [post_int('id')]);
            flash('ok', $def['singular'] . ' deleted.');
            redirect(BASE_URL . 'admin/' . $selfBase);
        }

        if ($action === 'save') {
            $id      = post_int('id');
            $data    = [];
            $missing = [];

            foreach ($def['fields'] as $f) {
                [$col, $label, $type, $required] = [$f[0], $f[1], $f[2], !empty($f[3])];
                $val = post($col, 40000);

                if ($type === 'image' || $type === 'document') {
                    try {
                        $saved = save_upload($col, $type === 'image' ? 'image' : 'document');
                        if ($saved !== null) {
                            $data[$col] = $saved;
                        }
                    } catch (RuntimeException $ex) {
                        $err = $ex->getMessage();
                        break;
                    }
                    continue;
                }

                if ($type === 'number') {
                    $data[$col] = (int) $val;
                    continue;
                }
                if ($type === 'email') {
                    if ($val !== '' && !is_email($val)) {
                        $err = $label . ' must be a valid email address.';
                        break;
                    }
                    $data[$col] = $val;
                    continue;
                }
                if ($type === 'datetime') {
                    $data[$col] = $val !== '' ? date('Y-m-d H:i:s', strtotime($val)) : date('Y-m-d H:i:s');
                    continue;
                }

                if ($required && $val === '') {
                    $missing[] = $label;
                }
                $data[$col] = $val;
            }

            if (!$err && $missing) {
                $err = 'Please fill in: ' . implode(', ', $missing) . '.';
            }

            if (!$err) {
                if (!empty($def['has_publish'])) {
                    $data['published'] = post_bool('published') ? 1 : 0;
                }

                if (!empty($def['has_slug'])) {
                    $titleCol = isset($data['title']) ? 'title' : 'name';
                    $base     = isset($data['title']) ? $data['title'] : ($data[$titleCol] ?? '');
                    if ($id > 0) {
                        $slug = unique_slug($table, $base, $id);
                        update_row($table, ['slug' => $slug], 'id = ?', [$id]);
                    } else {
                        $data['slug'] = unique_slug($table, $base);
                    }
                }

                if ($id > 0) {
                    update_row($table, $data, 'id = ?', [$id]);
                    flash('ok', $def['singular'] . ' updated.');
                } else {
                    insert_row($table, $data);
                    flash('ok', $def['singular'] . ' created.');
                }
                redirect(BASE_URL . 'admin/' . $selfBase);
            }
        }
    } catch (Throwable $ex) {
        error_log('content save failed: ' . $ex->getMessage());
        $err = 'Could not save. Please check your input and try again.';
    }
}

/* ------------------------------- load data ------------------------------- */
$rows = fetch_all("SELECT * FROM `$table` ORDER BY {$def['order']}");
$editing = null;
foreach ($rows as $r) {
    if ((int) $r['id'] === $editId) {
        $editing = $r;
        break;
    }
}
if (!$editing && $editId > 0) {
    redirect(BASE_URL . 'admin/' . $selfBase);
}

/** Render one field for the form. */
function render_field(array $f, ?array $row, string $entity): void
{
    [$col, $label, $type] = [$f[0], $f[1], $f[2]];
    $required = !empty($f[3]);
    $options  = $f[4] ?? null;
    $hint     = $f[5] ?? null;
    $val      = $row[$col] ?? '';
    $star     = $required ? ' *' : '';

    echo '<div class="field">';
    echo '<label for="f_' . e($col) . '">' . e($label) . $star . '</label>';

    if ($type === 'textarea') {
        echo '<textarea id="f_' . e($col) . '" name="' . e($col) . '" ' . ($required ? 'required' : '')
           . ' style="min-height:' . ($col === 'content' ? '170px' : '100px') . '">' . e($val) . '</textarea>';
    } elseif ($type === 'select') {
        echo '<select id="f_' . e($col) . '" name="' . e($col) . '" ' . ($required ? 'required' : '') . '>';
        foreach ((array) $options as $opt) {
            echo '<option value="' . e($opt) . '" ' . ((string) $val === (string) $opt ? 'selected' : '') . '>' . e($opt) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'image' || $type === 'document') {
        $accept = $type === 'image' ? '.jpg,.jpeg,.png,.webp,.gif,.avif' : '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv';
        echo '<div class="img-preview">';
        if ($type === 'image' && $val) {
            $round = ($col === 'photo') ? ' round' : '';
            echo '<img class="' . trim($round) . '" src="' . e($val) . '" alt="" data-prev-target>';
        } elseif ($val) {
            echo '<a href="' . e($val) . '" target="_blank" rel="noreferrer">View current file</a>';
        }
        echo '<div style="flex:1;min-width:220px">';
        echo '<input type="hidden" name="' . e($col) . '" value="' . e($val) . '">';
        echo '<input id="f_' . e($col) . '" name="' . e($col) . '" type="file" accept="' . $accept . '"'
           . ' data-preview="' . ($type === 'image' ? '#prev_' . e($col) : '') . '">';
        echo '</div></div>';
        if ($type === 'image') {
            echo '<img id="prev_' . e($col) . '" alt="" hidden style="width:90px;height:62px;object-fit:cover;border-radius:8px;border:1px solid var(--line);margin-top:9px">';
        }
    } elseif ($type === 'datetime') {
        $v = $val ? date('Y-m-d\TH:i', strtotime($val)) : date('Y-m-d\TH:i');
        echo '<input id="f_' . e($col) . '" name="' . e($col) . '" type="datetime-local" value="' . e($v) . '" ' . ($required ? 'required' : '') . '>';
    } elseif ($type === 'number') {
        echo '<input id="f_' . e($col) . '" name="' . e($col) . '" type="number" value="' . e((int) $val) . '">';
    } elseif ($type === 'email') {
        echo '<input id="f_' . e($col) . '" name="' . e($col) . '" type="email" value="' . e($val) . '">';
    } else {
        echo '<input id="f_' . e($col) . '" name="' . e($col) . '" type="text" value="' . e($val) . '" ' . ($required ? 'required' : '') . '>';
    }

    if ($hint) {
        echo '<span class="hint">' . e($hint) . '</span>';
    }
    echo '</div>';
}

$adminTitle = $def['title'];
$adminDesc  = $def['desc'];
$adminBanner = $err ? [$err, 'alert-err'] : null;
require __DIR__ . '/includes/header.php';
?>

<div class="card card-pad" style="margin-bottom:24px">
  <details <?= $editing ? 'open' : '' ?>>
    <summary style="cursor:pointer;font-family:Fraunces,Georgia,serif;font-size:18px;font-weight:600">
      <?= $editing ? 'Edit: ' . e($editing['title'] ?? $editing['name'] ?? 'record') : 'Add New ' . e($def['singular']) ?>
    </summary>
    <form method="post" enctype="multipart/form-data" style="margin-top:22px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">

      <div class="grid g2" style="gap:18px">
        <?php foreach ($def['fields'] as $f): ?>
          <?php if (in_array($f[2], ['textarea', 'image', 'document'], true) || $f[0] === 'content'): ?>
            <div style="grid-column:1/-1"><?php render_field($f, $editing, $entity); ?></div>
          <?php else: ?>
            <?php render_field($f, $editing, $entity); ?>
          <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!empty($def['has_publish'])): ?>
          <div class="field" style="grid-column:1/-1">
            <label class="check"><input type="hidden" name="published" value="0">
              <input type="checkbox" name="published" value="1" <?= (!$editing || !empty($editing['published'])) ? 'checked' : '' ?>>
              Published (visible on the website)</label>
          </div>
        <?php endif; ?>
      </div>

      <div style="display:flex;gap:11px;flex-wrap:wrap">
        <button class="btn btn-accent" type="submit"><?= $editing ? 'Update' : 'Create' ?></button>
        <?php if ($editing): ?>
          <a class="btn btn-outline" href="<?= e(BASE_URL . 'admin/' . $selfBase) ?>">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </details>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Item</th><th>Details</th><?php if (!empty($def['has_publish'])): ?><th>Status</th><?php endif; ?><th class="t-right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <?php $imgCol = in_array($entity, ['faculty', 'testimonials'], true) ? 'photo' : 'image'; ?>
          <?php if (!empty($r[$imgCol])): ?>
            <img src="<?= e(asset_url($r[$imgCol])) ?>" alt="" style="width:56px;height:38px;object-fit:cover;border-radius:7px;display:block;margin-bottom:7px">
          <?php endif; ?>
          <strong><?= e($r['title'] ?? $r['name'] ?? ('#' . $r['id'])) ?></strong>
          <?php if (!empty($r['slug'])): ?>
            <span style="display:block;font-size:11.5px;color:var(--ink-soft)">/<?= e($r['slug']) ?></span>
          <?php endif; ?>
        </td>
        <td style="max-width:340px">
          <?php if (!empty($r['category'])): ?><span class="badge badge-grey"><?= e($r['category']) ?></span><?php endif; ?>
          <?php if (!empty($r['level'])): ?><span class="badge badge-grey"><?= e($r['level']) ?></span><?php endif; ?>
          <?php if (!empty($r['position'])): ?><span style="display:block;font-size:12.5px"><?= e($r['position']) ?></span><?php endif; ?>
          <?php if (!empty($r['location'])): ?><span style="display:block;font-size:12.5px"><?= e($r['location']) ?></span><?php endif; ?>
          <?php foreach (['published_at', 'event_date', 'notice_date', 'created_at'] as $dateCol): ?>
            <?php if (!empty($r[$dateCol])): ?>
              <span style="display:block;font-size:11.5px;color:var(--ink-soft)"><?= e(fmt_date($r[$dateCol])) ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if (!empty($r['excerpt'])): ?>
            <span style="display:block;font-size:12.5px;color:var(--ink-soft)"><?= e(excerpt($r['excerpt'], 70)) ?></span>
          <?php endif; ?>
        </td>
        <?php if (!empty($def['has_publish'])): ?>
          <td><span class="badge <?= !empty($r['published']) ? 'badge-green' : 'badge-grey' ?>"><?= !empty($r['published']) ? 'Published' : 'Draft' ?></span></td>
        <?php endif; ?>
        <td>
          <div class="actions">
            <a class="btn btn-outline btn-icon" href="<?= e(BASE_URL . 'admin/' . $selfBase . '&edit=' . (int) $r['id']) ?>">Edit</a>
            <form method="post" data-confirm="Delete &ldquo;<?= e($r['title'] ?? $r['name'] ?? 'this item') ?>&rdquo; permanently? This cannot be undone.">
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
      <tr><td colspan="4" style="text-align:center;padding:38px">Nothing here yet — add your first <?= e(strtolower($def['singular'])) ?> above.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
