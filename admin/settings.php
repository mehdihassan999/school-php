<?php
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

$admin = require_admin();

$tabs = [
    'general'    => ['School Information', '&#127970;'],
    'homepage'   => ['Homepage & Stats', '&#127968;'],
    'about'      => ['About / Mission / Principal', '&#8505;'],
    'admissions' => ['Admissions Content', '&#127891;'],
    'academics'  => ['Curriculum & Exams', '&#127758;'],
    'seo'        => ['SEO & Social', '&#128269;'],
    'account'    => ['Admin Profile', '&#128100;'],
];
$tab  = isset($_GET['tab']) && isset($tabs[$_GET['tab']]) ? $_GET['tab'] : 'general';
$err  = null;

/* ------------------------------- handlers -------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = post('action', 20);

    try {
        if ($action === 'profile') {
            $name  = post('name', 120);
            $email = post('email', 160);
            if ($name === '' || !is_email($email)) {
                $err = 'A valid name and email address are required.';
            } else {
                update_row('admins', ['name' => $name, 'email' => $email], 'id = ?', [$admin['id']]);
                flash('ok', 'Profile updated.');
                redirect(BASE_URL . 'admin/settings.php?tab=account');
            }
        } elseif ($action === 'password') {
            $current = post('currentPassword', 200);
            $next    = post('newPassword', 200);
            $confirm = post('confirmPassword', 200);
            if (!password_verify($current, $admin['password_hash'])) {
                $err = 'Your current password is incorrect.';
            } elseif (strlen($next) < 8) {
                $err = 'The new password must be at least 8 characters.';
            } elseif ($next !== $confirm) {
                $err = 'The new passwords do not match.';
            } else {
                update_row('admins', ['password_hash' => password_hash($next, PASSWORD_DEFAULT)], 'id = ?', [$admin['id']]);
                flash('ok', 'Password changed successfully.');
                redirect(BASE_URL . 'admin/settings.php?tab=account');
            }
        } elseif ($action === 'settings') {
            $keys = [
                'general'    => ['school_name', 'school_short', 'tagline', 'accent_color', 'phone', 'whatsapp', 'email',
                                 'admissions_email', 'address', 'office_hours'],
                'homepage'   => ['hero_kicker', 'hero_title', 'hero_subtitle', 'stat_students', 'stat_teachers',
                                 'stat_classes', 'stat_years'],
                'about'      => ['about_title', 'about_short', 'about_content', 'mission', 'vision', 'core_values',
                                 'principal_name', 'principal_designation', 'principal_message'],
                'admissions' => ['admission_note', 'admission_requirements', 'fee_structure', 'admission_dates'],
                'academics'  => ['curriculum', 'exam_system'],
                'seo'        => ['seo_title', 'seo_description', 'seo_keywords', 'facebook', 'instagram',
                                 'twitter', 'youtube', 'linkedin'],
            ];
            $fields = $keys[$tab] ?? [];

            // Validate the accent colour before saving anything.
            if ($tab === 'general') {
                $colour = post('accent_color', 20);
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $colour)) {
                    $err = 'Accent colour must be a 6-digit hex value such as #1B3A6B.';
                }
                if (post('admission_open', 5) !== '') {
                    $fields[] = 'admission_open';
                }
            }

            if (!$err) {
                $uploadedKeys = [];
                // Handle image uploads first so a bad file aborts before saving.
                foreach (['logo', 'favicon', 'hero_image', 'about_image', 'principal_photo', 'og_image'] as $img) {
                    try {
                        $saved = save_upload('file_' . $img, 'image');
                        if ($saved !== null) {
                            save_setting($img, $saved);
                            $uploadedKeys[$img] = true;
                        }
                    } catch (RuntimeException $ex) {
                        $err = $ex->getMessage();
                        break;
                    }
                }
            }

            if (!$err) {
                foreach ($fields as $key) {
                    save_setting($key, post($key, 8000));
                }
                foreach (['logo', 'favicon', 'hero_image', 'about_image', 'principal_photo', 'og_image'] as $img) {
                    if (!isset($uploadedKeys[$img]) && isset($_POST[$img])) {
                        save_setting($img, post($img, 2000));
                    }
                }
                flash('ok', 'Settings saved — the website has been updated.');
                redirect(BASE_URL . 'admin/settings.php?tab=' . $tab);
            }
        }
    } catch (Throwable $ex) {
        error_log('settings save failed: ' . $ex->getMessage());
        $err = 'Could not save your settings. Please try again.';
    }
}

/** A setting field, with an optional image upload alongside. */
function setting_field(string $key, string $label, string $type = 'text', ?string $hint = null, bool $withImage = false): void
{
    $rawVal = setting($key);
    $val    = in_array($key, ['logo', 'favicon', 'hero_image', 'about_image', 'principal_photo', 'og_image'], true)
        ? setting_asset($key)
        : setting($key);

    echo '<div class="field" style="grid-column:1/-1">';
    echo '<label for="s_' . e($key) . '">' . e($label) . '</label>';

    if ($withImage) {
        echo '<div class="img-preview" style="display:flex;gap:14px;align-items:center;margin-top:4px">';
        if ($val) {
            echo '<img src="' . e($val) . '" alt="" style="width:70px;height:50px;object-fit:contain;border-radius:8px;border:1px solid var(--line);background:#fff;padding:3px;flex-shrink:0">';
        }
        echo '<div style="flex:1;min-width:240px;display:flex;flex-direction:column;gap:6px">';
        echo '<input id="s_' . e($key) . '" name="' . e($key) . '" type="text" value="' . e($rawVal) . '" placeholder="Image path or URL (e.g. assets/images/logo.png)">';
        echo '<input type="file" name="file_' . e($key) . '" accept=".jpg,.jpeg,.png,.webp,.gif,.avif">';
        echo '<span class="hint">Upload a file (max 4 MB) or enter an image URL/path above.</span>';
        echo '</div></div>';
        if ($hint) {
            echo '<span class="hint" style="margin-top:4px;display:block">' . e($hint) . '</span>';
        }
        echo '</div>';
        return;
    }

    if ($type === 'textarea') {
        $h = in_array($key, ['about_content', 'core_values', 'admission_requirements', 'fee_structure',
                             'admission_dates', 'curriculum', 'exam_system'], true) ? '190px' : '110px';
        echo '<textarea id="s_' . e($key) . '" name="' . e($key) . '" style="min-height:' . $h . '">' . e($val) . '</textarea>';
    } elseif ($type === 'color') {
        echo '<div style="display:flex;gap:12px;align-items:center">'
           . '<span style="width:40px;height:38px;border-radius:8px;border:1px solid var(--line);background:' . e($val) . '"></span>'
           . '<input id="s_' . e($key) . '" name="' . e($key) . '" type="text" value="' . e($val) . '" placeholder="#1B3A6B"></div>';
    } else {
        echo '<input id="s_' . e($key) . '" name="' . e($key) . '" type="text" value="' . e($val) . '">';
    }
    if ($hint) {
        echo '<span class="hint">' . e($hint) . '</span>';
    }
    echo '</div>';
}

$adminTitle = 'General Settings';
$adminDesc  = 'All website content is managed here — no code required.';
$adminBanner = $err ? [$err, 'alert-err'] : null;
require __DIR__ . '/includes/header.php';
?>

<div class="tabs">
  <?php foreach ($tabs as $id => [$label, $icon]): ?>
    <a class="<?= $id === $tab ? 'is-current' : '' ?>" href="<?= e(BASE_URL) ?>admin/settings.php?tab=<?= e($id) ?>">
      <span aria-hidden="true"><?= $icon ?></span> <?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($tab === 'account'): ?>
<div class="grid g2">
  <div class="card card-pad">
    <h3>Profile</h3>
    <form method="post" style="margin-top:20px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="profile">
      <div class="field"><label for="p_name">Display Name</label>
        <input id="p_name" name="name" type="text" required value="<?= e($admin['name']) ?>"></div>
      <div class="field"><label for="p_email">Email</label>
        <input id="p_email" name="email" type="email" required value="<?= e($admin['email']) ?>"></div>
      <div class="field"><label>Username</label>
        <input type="text" value="<?= e($admin['username']) ?>" disabled>
        <span class="hint">Usernames are managed directly in the database.</span></div>
      <button class="btn btn-accent" type="submit">Update Profile</button>
    </form>
  </div>

  <div class="card card-pad">
    <h3>Change Password</h3>
    <form method="post" style="margin-top:20px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div class="field"><label for="cp">Current Password</label>
        <input id="cp" name="currentPassword" type="password" required autocomplete="current-password"></div>
      <div class="field"><label for="np">New Password</label>
        <input id="np" name="newPassword" type="password" required minlength="8" autocomplete="new-password">
        <span class="hint">Minimum 8 characters. Stored as a bcrypt hash — never in plain text.</span></div>
      <div class="field"><label for="np2">Confirm New Password</label>
        <input id="np2" name="confirmPassword" type="password" required minlength="8" autocomplete="new-password"></div>
      <button class="btn btn-accent" type="submit">Change Password</button>
    </form>
  </div>
</div>

<?php else: ?>
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="settings">

  <?php if ($tab === 'general'): ?>
  <div class="grid g2">
    <div class="card card-pad">
      <h3>Identity</h3>
      <p style="font-size:12px;color:var(--ink-soft);background:var(--cream);border-radius:9px;padding:11px;margin-top:11px">
        Changing the name here updates the header, footer, page titles, metadata, image alt text, the admin panel
        and the 404 page automatically. Prose that names the school (hero introduction, About text, Principal&rsquo;s
        message, SEO description and testimonials) is edited on the other tabs.
      </p>
      <div class="grid g2" style="gap:18px;margin-top:18px">
        <?php setting_field('school_name', 'School Name'); ?>
        <?php setting_field('school_short', 'Short Name', 'text', 'Used in the admin sidebar and compact headings.'); ?>
        <?php setting_field('tagline', 'Tagline'); ?>
        <?php setting_field('accent_color', 'Accent Colour', 'color', 'Hex code such as #1B3A6B. Applies site-wide instantly.'); ?>
        <?php setting_field('logo', 'School Logo', 'text', 'Shown in the header and admin panel.', true); ?>
        <?php setting_field('favicon', 'Favicon', 'text', 'Square icon shown in the browser tab.', true); ?>
      </div>
    </div>

    <div class="card card-pad">
      <h3>Contact Information</h3>
      <div class="grid g2" style="gap:18px;margin-top:18px">
        <?php setting_field('phone', 'Phone'); ?>
        <?php setting_field('whatsapp', 'WhatsApp Number', 'text', 'Digits only with country code, e.g. 15550142450'); ?>
        <?php setting_field('email', 'General Email'); ?>
        <?php setting_field('admissions_email', 'Admissions Email'); ?>
        <?php setting_field('address', 'Address', 'textarea'); ?>
        <?php setting_field('office_hours', 'Office Hours'); ?>
        <div class="field" style="grid-column:1/-1">
          <label class="check"><input type="hidden" name="admission_open" value="0">
            <input type="checkbox" name="admission_open" value="1" <?= setting('admission_open') === '1' ? 'checked' : '' ?>>
            Admissions are open (shows the &ldquo;Admissions Are Open&rdquo; banner)</label>
        </div>
      </div>
    </div>
  </div>

  <?php elseif ($tab === 'homepage'): ?>
  <div class="grid g2">
    <div class="card card-pad">
      <h3>Hero Section</h3>
      <div class="grid" style="gap:18px;margin-top:18px">
        <?php setting_field('hero_image', 'Hero Background', 'text', 'Large image behind the homepage headline.', true); ?>
        <?php setting_field('hero_kicker', 'Kicker / Badge Text'); ?>
        <?php setting_field('hero_title', 'Headline'); ?>
        <?php setting_field('hero_subtitle', 'Introduction', 'textarea'); ?>
      </div>
    </div>
    <div class="card card-pad">
      <h3>Statistics (animated counters)</h3>
      <div class="grid g2" style="gap:18px;margin-top:18px">
        <?php setting_field('stat_students', 'Students'); ?>
        <?php setting_field('stat_teachers', 'Teachers'); ?>
        <?php setting_field('stat_classes', 'Classes'); ?>
        <?php setting_field('stat_years', 'Years of Excellence'); ?>
      </div>
      <p class="hint">Numbers animate on the homepage. Non-numeric characters are ignored by the counter.</p>
    </div>
  </div>

  <?php elseif ($tab === 'about'): ?>
  <div class="grid" style="gap:24px">
    <div class="card card-pad">
      <h3>About Preview</h3>
      <div class="grid" style="gap:18px;margin-top:18px">
        <?php setting_field('about_title', 'Title'); ?>
        <?php setting_field('about_short', 'Short Intro (footer &amp; meta)', 'textarea'); ?>
        <?php setting_field('about_content', 'Full Story', 'textarea', 'Separate paragraphs with a blank line.'); ?>
        <?php setting_field('about_image', 'About Image', 'text', null, true); ?>
      </div>
    </div>
    <div class="grid g2">
      <div class="card card-pad">
        <h3>Mission &amp; Vision</h3>
        <div class="grid" style="gap:18px;margin-top:18px">
          <?php setting_field('mission', 'Mission', 'textarea'); ?>
          <?php setting_field('vision', 'Vision', 'textarea'); ?>
          <?php setting_field('core_values', 'Core Values', 'textarea', 'One per line as &ldquo;Title: Description&rdquo;.'); ?>
        </div>
      </div>
      <div class="card card-pad">
        <h3>Principal&rsquo;s Message</h3>
        <div class="grid" style="gap:18px;margin-top:18px">
          <?php setting_field('principal_name', 'Principal Name'); ?>
          <?php setting_field('principal_designation', 'Designation'); ?>
          <?php setting_field('principal_message', 'Message', 'textarea', 'Separate paragraphs with a blank line.'); ?>
          <?php setting_field('principal_photo', 'Principal Photo', 'text', null, true); ?>
        </div>
      </div>
    </div>
  </div>

  <?php elseif ($tab === 'admissions'): ?>
  <div class="grid" style="gap:24px">
    <div class="card card-pad">
      <h3>Admission Banner</h3>
      <div class="grid" style="gap:18px;margin-top:18px">
        <?php setting_field('admission_note', 'Admission Note', 'textarea'); ?>
      </div>
    </div>
    <div class="grid g2">
      <div class="card card-pad">
        <h3>Requirements &amp; Fees</h3>
        <div class="grid" style="gap:18px;margin-top:18px">
          <?php setting_field('admission_requirements', 'Requirements', 'textarea', 'One requirement per line.'); ?>
          <?php setting_field('fee_structure', 'Fee Structure', 'textarea', 'One per line as &ldquo;Grade level: Fee&rdquo;.'); ?>
        </div>
      </div>
      <div class="card card-pad">
        <h3>Important Dates</h3>
        <?php setting_field('admission_dates', 'Dates', 'textarea', 'One per line as &ldquo;Milestone &mdash; Date&rdquo;.'); ?>
      </div>
    </div>
  </div>

  <?php elseif ($tab === 'academics'): ?>
  <div class="card card-pad">
    <h3>Curriculum &amp; Examination System</h3>
    <div class="grid g2" style="gap:18px;margin-top:18px">
      <?php setting_field('curriculum', 'Curriculum', 'textarea', 'Shown on the Academics page. Blank line = new paragraph.'); ?>
      <?php setting_field('exam_system', 'Examination System', 'textarea', 'Shown on the Academics page.'); ?>
    </div>
  </div>

  <?php elseif ($tab === 'seo'): ?>
  <div class="grid g2">
    <div class="card card-pad">
      <h3>SEO</h3>
      <div class="grid" style="gap:18px;margin-top:18px">
        <?php setting_field('seo_title', 'Default Meta Title'); ?>
        <?php setting_field('seo_description', 'Meta Description', 'textarea'); ?>
        <?php setting_field('seo_keywords', 'Keywords', 'text', 'Comma separated.'); ?>
        <?php setting_field('og_image', 'Open Graph Image', 'text', 'Shared preview image, 1200×630 recommended.', true); ?>
      </div>
    </div>
    <div class="card card-pad">
      <h3>Social Media</h3>
      <div class="grid" style="gap:18px;margin-top:18px">
        <?php foreach (['facebook' => 'Facebook URL', 'instagram' => 'Instagram URL', 'twitter' => 'Twitter / X URL',
                        'youtube' => 'YouTube URL', 'linkedin' => 'LinkedIn URL'] as $k => $lbl): ?>
          <?php setting_field($k, $lbl, 'text', 'Leave blank to hide this icon.'); ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div style="display:flex;justify-content:flex-end;margin-top:22px">
    <button class="btn btn-accent" type="submit">Save All Changes</button>
  </div>
</form>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
