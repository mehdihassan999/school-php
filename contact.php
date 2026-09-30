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

    $name    = post('name', 160);
    $email   = post('email', 160);
    $phone   = post('phone', 40);
    $subject = post('subject', 200);
    $message = post('message', 3000);

    if ($name === '' || !is_email($email) || $message === '') {
        $errors[] = 'Please provide your name, a valid email address and a message.';
    }

    if (!$errors) {
        try {
            insert_row('contact_messages', [
                'name' => $name, 'email' => $email, 'phone' => $phone,
                'subject' => $subject, 'message' => $message, 'is_read' => 0,
            ]);
            $sent = true;
        } catch (Throwable $ex) {
            error_log('contact insert failed: ' . $ex->getMessage());
            $errors[] = 'Something went wrong while sending your message. Please try again.';
        }
    }
}

$pageTitle = 'Contact Us';
$pageDesc  = 'Contact ' . setting('school_name') . ' — address, phone, email, WhatsApp, office hours and contact form.';
$pageKicker = 'Contact Us';
$pageHeading = 'We&rsquo;d Love to Hear From You';
$pageSub = 'Questions about admissions, campus tours or anything else — reach out and we\'ll respond within one working day.';
$pageCrumb = 'Contact';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="grid g4">
      <?php
      $cards = [
        ['&#128205;', 'Visit Us', setting('address'), 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(setting('address'))],
        ['&#9742;', 'Call Us', setting('phone'), tel_href(setting('phone'))],
        ['&#9993;', 'Email Us', setting('email'), 'mailto:' . setting('email')],
        ['&#128172;', 'WhatsApp', 'Message our front office', whatsapp_href(setting('whatsapp'))],
      ];
      foreach ($cards as $c): ?>
      <a class="card card-hover contact-card reveal" href="<?= e($c[3]) ?>" <?= strpos($c[3], 'http') === 0 ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
        <span class="ico"><?= $c[0] ?></span>
        <span><b><?= e($c[1]) ?></b><span><?= e($c[2]) ?></span></span>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="grid" style="grid-template-columns:1.3fr 1fr;gap:38px;margin-top:60px">
      <div class="card card-pad reveal" id="form">
        <div class="section-head left"><p class="kicker">Send a Message</p><h2>Contact Form</h2></div>

        <?php if ($sent): ?>
          <div class="alert alert-ok" style="margin-top:22px">
            <strong>Message sent successfully.</strong> Our office will reply within one working day.
          </div>
        <?php endif; ?>
        <?php if ($errors): ?>
          <div class="alert alert-err" style="margin-top:22px">
            <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <?= csrf_field() ?>
          <div class="grid g2" style="gap:18px;margin-top:22px">
            <div class="field"><label for="name">Your Name *</label>
              <input id="name" name="name" type="text" required value="<?= e(post('name', 160)) ?>"></div>
            <div class="field"><label for="cemail">Email *</label>
              <input id="cemail" name="email" type="email" required value="<?= e(post('email', 160)) ?>"></div>
            <div class="field"><label for="cphone">Phone</label>
              <input id="cphone" name="phone" type="tel" value="<?= e(post('phone', 40)) ?>"></div>
            <div class="field"><label for="subject">Subject</label>
              <input id="subject" name="subject" type="text" value="<?= e(post('subject', 200)) ?>"></div>
            <div class="field" style="grid-column:1/-1"><label for="message">Message *</label>
              <textarea id="message" name="message" required style="min-height:140px"><?= e(post('message', 3000)) ?></textarea></div>
          </div>
          <button class="btn btn-accent" type="submit">Send Message</button>
        </form>
      </div>

      <div style="display:grid;gap:22px;align-content:start">
        <div class="map-frame reveal">
          <iframe title="<?= e(setting('school_name')) ?> map" src="<?= e(map_src(setting('address'))) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
        <div class="card card-pad reveal">
          <h3 style="display:flex;align-items:center;gap:9px">&#128337; Office Hours</h3>
          <p style="font-size:14px"><?= e(setting('office_hours')) ?></p>
          <p style="font-size:12.5px;color:var(--ink-soft)">Weekend visits available by appointment for campus tours.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
