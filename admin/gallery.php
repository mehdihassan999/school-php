<?php
/**
 * Gallery admin: albums + image uploads.
 */
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

$err      = null;
$editId   = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$categories = ['Campus', 'Events', 'Sports', 'Activities', 'Trips', 'Students', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = post('action', 20);

    try {
        if ($action === 'delete_album') {
            delete_row('gallery_albums', 'id = ?', [post_int('id')]);   // images cascade
            flash('ok', 'Album deleted.');
            redirect(BASE_URL . 'admin/gallery.php');
        }

        if ($action === 'delete_image') {
            delete_row('gallery_images', 'id = ?', [post_int('id')]);
            flash('ok', 'Image deleted.');
            redirect(BASE_URL . 'admin/gallery.php');
        }

        if ($action === 'save_album') {
            $id    = post_int('id');
            $title = post('title', 160);
            if ($title === '') {
                $err = 'Album title is required.';
            } else {
                $cover = save_upload('coverImage', 'image');
                $data  = [
                    'title'       => $title,
                    'category'    => post('category', 40) ?: 'Campus',
                    'description' => post('description', 2000),
                    'published'   => post_bool('published') ? 1 : 0,
                ];
                if ($id > 0) {
                    if ($cover !== null) {
                        $data['cover_image'] = $cover;
                    }
                    update_row('gallery_albums', $data, 'id = ?', [$id]);
                    flash('ok', 'Album updated.');
                } else {
                    $data['cover_image'] = (string) $cover;
                    insert_row('gallery_albums', $data);
                    flash('ok', 'Album created.');
                }
                redirect(BASE_URL . 'admin/gallery.php');
            }
        }

        if ($action === 'upload') {
            $albumId = post_int('albumId');
            if (!$albumId) {
                $err = 'Choose an album first.';
            } else {
                $saved = 0;
                if (!empty($_FILES['files']) && is_array($_FILES['files']['name'])) {
                    $count = count($_FILES['files']['name']);
                    for ($i = 0; $i < $count; $i++) {
                        if ((int) $_FILES['files']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                            continue;
                        }
                        $_FILES['single'] = [
                            'name'     => $_FILES['files']['name'][$i],
                            'type'     => $_FILES['files']['type'][$i],
                            'tmp_name' => $_FILES['files']['tmp_name'][$i],
                            'error'    => $_FILES['files']['error'][$i],
                            'size'     => $_FILES['files']['size'][$i],
                        ];
                        try {
                            $src = save_upload('single', 'image');
                            if ($src !== null) {
                                insert_row('gallery_images', ['album_id' => $albumId, 'src' => $src, 'caption' => '', 'sort_order' => 0]);
                                $saved++;
                            }
                        } catch (RuntimeException $ex) {
                            $err = $ex->getMessage();
                        }
                    }
                }
                if ($saved > 0) {
                    flash('ok', $saved . ' image' . ($saved === 1 ? '' : 's') . ' uploaded.');
                    redirect(BASE_URL . 'admin/gallery.php');
                }
                if (!$err) {
                    $err = 'Select at least one image to upload.';
                }
            }
        }
    } catch (Throwable $ex) {
        error_log('gallery admin failed: ' . $ex->getMessage());
        $err = 'Could not complete the action. Please try again.';
    }
}

$albums = fetch_all('SELECT * FROM gallery_albums ORDER BY created_at DESC');
$images = fetch_all('SELECT * FROM gallery_images ORDER BY sort_order, id');
$editing = null;
foreach ($albums as $a) {
    if ((int) $a['id'] === $editId) {
        $editing = $a;
        break;
    }
}

$adminTitle = 'Gallery';
$adminDesc  = 'Create albums and upload photos. Images appear in the public lightbox gallery.';
$adminBanner = $err ? [$err, 'alert-err'] : null;
require __DIR__ . '/includes/header.php';
?>

<div class="grid g2">
  <div class="card card-pad">
    <details <?= $editing ? 'open' : '' ?>>
      <summary style="cursor:pointer;font-family:Fraunces,Georgia,serif;font-size:18px;font-weight:600">
        <?= $editing ? 'Edit Album: ' . e($editing['title']) : 'Create New Album' ?>
      </summary>
      <form method="post" enctype="multipart/form-data" style="margin-top:20px">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_album">
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <div class="field"><label for="al_title">Album Title *</label>
          <input id="al_title" name="title" type="text" required value="<?= e($editing['title'] ?? '') ?>"></div>
        <div class="field"><label for="al_cat">Category</label>
          <select id="al_cat" name="category">
            <?php foreach ($categories as $c): ?>
              <option <?= ($editing['category'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field"><label for="al_desc">Description</label>
          <textarea id="al_desc" name="description" style="min-height:80px"><?= e($editing['description'] ?? '') ?></textarea></div>
        <div class="field"><label for="al_cover">Cover Image</label>
          <div class="img-preview">
            <?php if (!empty($editing['cover_image'])): ?>
              <img src="<?= e(asset_url($editing['cover_image'])) ?>" alt="">
            <?php endif; ?>
            <div style="flex:1;min-width:220px">
              <input type="hidden" name="coverImage" value="<?= e($editing['cover_image'] ?? '') ?>">
              <input id="al_cover" name="coverImage" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.avif" data-drop>
            </div>
          </div></div>
        <div class="field"><label class="check"><input type="hidden" name="published" value="0">
          <input type="checkbox" name="published" value="1" <?= (!$editing || !empty($editing['published'])) ? 'checked' : '' ?>>
          Published</label></div>
        <button class="btn btn-accent" type="submit"><?= $editing ? 'Update Album' : 'Create Album' ?></button>
      </form>
    </details>
  </div>

  <div class="card card-pad">
    <details>
      <summary style="cursor:pointer;font-family:Fraunces,Georgia,serif;font-size:18px;font-weight:600">Upload Images</summary>
      <form method="post" enctype="multipart/form-data" style="margin-top:20px">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload">
        <div class="field"><label for="up_album">Target Album *</label>
          <select id="up_album" name="albumId" required>
            <option value="">Select an album…</option>
            <?php foreach ($albums as $a): ?>
              <option value="<?= (int) $a['id'] ?>"><?= e($a['title']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field">
          <label class="dropzone" for="up_files">
            <span style="font-size:22px">&#11014;</span>
            <strong style="display:block">Drop images here or click to select</strong>
            <span>Multiple selection supported · JPG, PNG, WEBP, GIF · max 4 MB each</span>
            <input id="up_files" name="files[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.avif" data-drop style="margin-top:11px">
          </label>
        </div>
        <button class="btn btn-accent" type="submit">Upload Images</button>
      </form>
    </details>
  </div>
</div>

<div class="table-wrap" style="margin-top:24px">
  <table>
    <thead><tr><th>Album</th><th>Category</th><th>Images</th><th>Status</th><th class="t-right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($albums as $a): $count = count(array_filter($images, fn ($i) => (int) $i['album_id'] === (int) $a['id'])); ?>
      <tr>
        <td>
          <?php $cover = $a['cover_image']; ?>
          <?php if (!$cover) { foreach ($images as $i) { if ((int) $i['album_id'] === (int) $a['id']) { $cover = $i['src']; break; } } } ?>
          <img src="<?= e(img($cover, 'hero')) ?>" alt="" style="width:56px;height:38px;object-fit:cover;border-radius:7px;display:block;margin-bottom:7px">
          <strong><?= e($a['title']) ?></strong>
          <span style="display:block;font-size:11.5px;color:var(--ink-soft)"><?= e($a['description']) ?></span>
        </td>
        <td><?= e($a['category']) ?></td>
        <td><?= (int) $count ?></td>
        <td><span class="badge <?= !empty($a['published']) ? 'badge-green' : 'badge-grey' ?>"><?= !empty($a['published']) ? 'Published' : 'Draft' ?></span></td>
        <td>
          <div class="actions">
            <a class="btn btn-outline btn-icon" href="<?= e(BASE_URL) ?>admin/gallery.php?edit=<?= (int) $a['id'] ?>">Edit</a>
            <form method="post" data-confirm="Delete album &ldquo;<?= e($a['title']) ?>&rdquo; and all its images?">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_album">
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
              <button class="btn btn-danger btn-icon" type="submit">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$albums): ?>
      <tr><td colspan="5" style="text-align:center;padding:38px">No albums yet — create your first album above.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php foreach ($albums as $a): ?>
  <?php $list = array_filter($images, fn ($i) => (int) $i['album_id'] === (int) $a['id']); ?>
  <?php if (!$list) { continue; } ?>
  <div style="margin-top:32px">
    <h3 style="font-family:Fraunces,Georgia,serif;font-size:19px">
      <?= e($a['title']) ?> <span style="font-weight:400;font-size:14px;color:var(--ink-soft)">(<?= count($list) ?> photos)</span>
    </h3>
    <div class="gallery-admin" style="margin-top:15px">
      <?php foreach ($list as $img): ?>
      <div class="item">
        <img src="<?= e(asset_url($img['src'])) ?>" alt="<?= e($img['caption']) ?>" loading="lazy">
        <form method="post" data-confirm="Remove this image from the album?">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete_image">
          <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
          <button class="del" type="submit" aria-label="Delete image">&times;</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
