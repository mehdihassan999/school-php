<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$errors = [];
$sent   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $studentName = post('studentName', 160);
    $dob         = post('dob', 20);
    $gender      = post('gender', 20);
    $grade       = post('grade', 20);
    $prevSchool  = post('previousSchool', 200);
    $parentName  = post('parentName', 160);
    $relation    = post('relationship', 40);
    $phone       = post('phone', 40);
    $email       = post('email', 160);
    $address     = post('address', 1000);
    $emergency   = post('emergencyContact', 200);
    $extra       = post('additionalInfo', 2000);

    if ($studentName === '' || $dob === '' || $gender === '' || $grade === '' || $parentName === '' || $relation === '') {
        $errors[] = 'Please fill in all required student and parent fields.';
    }
    if ($phone === '') {
        $errors[] = 'A phone number is required.';
    }
    if (!is_email($email)) {
        $errors[] = 'Please provide a valid email address.';
    }

    $document = '';
    if (!$errors) {
        try {
            $document = (string) save_upload('document', 'any');
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    if (!$errors) {
        try {
            insert_row('admissions', [
                'student_name'      => $studentName,
                'dob'               => $dob,
                'gender'            => $gender,
                'grade'             => $grade,
                'previous_school'   => $prevSchool,
                'parent_name'       => $parentName,
                'relationship'      => $relation,
                'phone'             => $phone,
                'email'             => $email,
                'address'           => $address,
                'emergency_contact' => $emergency,
                'additional_info'   => $extra,
                'document'          => $document,
                'status'            => 'new',
            ]);
            $sent = true;
        } catch (Throwable $ex) {
            error_log('admission insert failed: ' . $ex->getMessage());
            $errors[] = 'Something went wrong while saving your application. Please try again.';
        }
    }
}

$grades = ['Pre-KG', 'KG 1', 'KG 2'];
for ($i = 1; $i <= 12; $i++) {
    $grades[] = 'Grade ' . $i;
}

$pageTitle = 'Apply Online';
$pageDesc  = 'Submit an online admission application to ' . setting('school_name') . '.';
$pageKicker = 'Online Application';
$pageHeading = 'Apply for Admission';
$pageSub = 'Complete the form below — our admissions team will contact you within 5 working days.';
$pageCrumb = 'Apply';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap grid" style="grid-template-columns:1fr;gap:38px">
    <aside style="display:grid;gap:16px">
      <div class="card card-pad">
        <h3>Before You Begin</h3>
        <ul class="ticks" style="margin-top:16px">
          <li class="tick">Fields marked * are required.</li>
          <li class="tick">Upload a PDF or image of prior school reports (max 10 MB).</li>
          <li class="tick">You will receive a confirmation call after review.</li>
        </ul>
      </div>
      <div class="card card-pad" style="background:var(--accent-deep);color:#fff;border:0">
        <h3 style="color:#fff">Need Help?</h3>
        <p style="color:rgba(255,255,255,.72);font-size:14px;margin-top:11px"><?= e(setting('admission_note')) ?></p>
        <p style="margin:15px 0 0;font-size:14px;color:var(--gold);font-weight:600"><?= e(setting('admissions_email')) ?></p>
        <p style="margin:4px 0 0;font-size:14px;color:rgba(255,255,255,.72)"><?= e(setting('phone')) ?></p>
      </div>
    </aside>

    <div class="card card-pad">
      <?php if ($sent): ?>
        <div class="alert alert-ok">
          <strong>Application submitted successfully.</strong><br>
          Our admissions team will contact you shortly at the email and phone number provided.
        </div>
        <a class="btn btn-outline" href="<?= e(BASE_URL) ?>index.php">Back to Homepage</a>
      <?php else: ?>
        <?php if ($errors): ?>
          <div class="alert alert-err">
            <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
          <?= csrf_field() ?>

          <fieldset>
            <legend>Student Information</legend>
            <div class="grid g2" style="gap:18px">
              <div class="field"><label for="studentName">Student Full Name *</label>
                <input id="studentName" name="studentName" type="text" required value="<?= e(post('studentName', 160)) ?>"></div>
              <div class="field"><label for="dob">Date of Birth *</label>
                <input id="dob" name="dob" type="date" required value="<?= e(post('dob', 20)) ?>"></div>
              <div class="field"><label for="gender">Gender *</label>
                <select id="gender" name="gender" required>
                  <option value="">Select gender</option>
                  <?php foreach (['Female', 'Male', 'Other'] as $g): ?>
                    <option <?= post('gender', 20) === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                  <?php endforeach; ?>
                </select></div>
              <div class="field"><label for="grade">Applying for Grade *</label>
                <select id="grade" name="grade" required>
                  <option value="">Select grade</option>
                  <?php foreach ($grades as $g): ?>
                    <option <?= post('grade', 20) === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                  <?php endforeach; ?>
                </select></div>
              <div class="field" style="grid-column:1/-1"><label for="previousSchool">Previous School (if any)</label>
                <input id="previousSchool" name="previousSchool" type="text" value="<?= e(post('previousSchool', 200)) ?>"></div>
            </div>
          </fieldset>

          <fieldset>
            <legend>Parent / Guardian</legend>
            <div class="grid g2" style="gap:18px">
              <div class="field"><label for="parentName">Parent / Guardian Name *</label>
                <input id="parentName" name="parentName" type="text" required value="<?= e(post('parentName', 160)) ?>"></div>
              <div class="field"><label for="relationship">Relationship to Student *</label>
                <select id="relationship" name="relationship" required>
                  <option value="">Select relationship</option>
                  <?php foreach (['Mother', 'Father', 'Guardian', 'Other'] as $r): ?>
                    <option <?= post('relationship', 40) === $r ? 'selected' : '' ?>><?= e($r) ?></option>
                  <?php endforeach; ?>
                </select></div>
              <div class="field"><label for="phone">Phone *</label>
                <input id="phone" name="phone" type="tel" required value="<?= e(post('phone', 40)) ?>"></div>
              <div class="field"><label for="email">Email *</label>
                <input id="email" name="email" type="email" required value="<?= e(post('email', 160)) ?>"></div>
              <div class="field" style="grid-column:1/-1"><label for="address">Home Address</label>
                <textarea id="address" name="address"><?= e(post('address', 1000)) ?></textarea></div>
              <div class="field" style="grid-column:1/-1"><label for="emergencyContact">Emergency Contact (name &amp; phone)</label>
                <input id="emergencyContact" name="emergencyContact" type="text" value="<?= e(post('emergencyContact', 200)) ?>"></div>
            </div>
          </fieldset>

          <fieldset>
            <legend>Additional Information</legend>
            <div class="field"><label for="additionalInfo">Anything else we should know?</label>
              <textarea id="additionalInfo" name="additionalInfo" placeholder="Learning needs, interests, sibling applications…"><?= e(post('additionalInfo', 2000)) ?></textarea></div>
            <div class="field">
              <label for="document">Supporting Documents</label>
              <input id="document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
              <span class="hint">Previous report cards, transfer certificate or birth certificate. PDF or image, max 10 MB.</span>
            </div>
          </fieldset>

          <div style="display:flex;justify-content:space-between;gap:18px;align-items:center;flex-wrap:wrap">
            <p style="font-size:12px;color:var(--ink-soft);margin:0">By submitting you confirm the information provided is accurate. Demo site — data is stored for demonstration only.</p>
            <button class="btn btn-accent" type="submit">Submit Application</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
