<?php
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}
$socials = [
    'facebook'  => 'Facebook',
    'instagram' => 'Instagram',
    'twitter'   => 'Twitter / X',
    'youtube'   => 'YouTube',
    'linkedin'  => 'LinkedIn',
];
?>
</main>

<footer class="site-footer">
  <div class="wrap footer-grid">
    <div>
      <div class="brand brand-light">
        <?php if (setting('logo')): ?>
          <img class="brand-logo" src="<?= e(setting_asset('logo')) ?>" alt="<?= e(setting('school_name')) ?> logo">
        <?php else: ?>
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor"><path d="M12 3 1 9l11 6 9-4.9V15h2V9L12 3zM5.5 13.2V17c0 2.2 2.9 4 6.5 4s6.5-1.8 6.5-4v-3.8L12 16.7l-6.5-3.5z"/></svg>
          </span>
        <?php endif; ?>
        <span class="brand-text">
          <strong><?= e(setting('school_name')) ?></strong>
          <small><?= e(setting('tagline')) ?></small>
        </span>
      </div>
      <p class="footer-about"><?= e(setting('about_short')) ?></p>
      <?php $anySocial = array_filter(array_map(fn ($k) => setting($k), array_keys($socials))); ?>
      <?php if ($anySocial): ?>
        <div class="socials">
          <?php foreach ($socials as $key => $label): if (!setting($key)) continue; ?>
            <a href="<?= e(setting($key)) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($label) ?>"><?= e(substr($label, 0, 1)) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <nav aria-label="Explore">
      <h3>Explore</h3>
      <ul>
        <li><a href="<?= e(BASE_URL) ?>about.php">About Us</a></li>
        <li><a href="<?= e(BASE_URL) ?>academics.php">Academics</a></li>
        <li><a href="<?= e(BASE_URL) ?>admissions.php">Admissions</a></li>
        <li><a href="<?= e(BASE_URL) ?>faculty.php">Faculty &amp; Staff</a></li>
        <li><a href="<?= e(BASE_URL) ?>facilities.php">Campus &amp; Facilities</a></li>
        <li><a href="<?= e(BASE_URL) ?>student-life.php">Student Life</a></li>
      </ul>
    </nav>

    <nav aria-label="Resources">
      <h3>Resources</h3>
      <ul>
        <li><a href="<?= e(BASE_URL) ?>news.php">News &amp; Events</a></li>
        <li><a href="<?= e(BASE_URL) ?>notices.php">Notice Board</a></li>
        <li><a href="<?= e(BASE_URL) ?>gallery.php">Photo Gallery</a></li>
        <li><a href="<?= e(BASE_URL) ?>downloads.php">Downloads</a></li>
        <li><a href="<?= e(BASE_URL) ?>apply.php">Apply Online</a></li>
        <li><a href="<?= e(BASE_URL) ?>contact.php">Contact Us</a></li>
      </ul>
    </nav>

    <div>
      <h3>Contact</h3>
      <ul class="contact-list">
        <li><?= e(setting('address')) ?></li>
        <li><a href="<?= e(tel_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
        <li><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
        <li><?= e(setting('office_hours')) ?></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="wrap footer-bottom-in">
      <p>&copy; <?= date('Y') ?> <?= e(setting('school_name')) ?>. All rights reserved.</p>
      <p>Demo website — all names and details are placeholder content.</p>
    </div>
  </div>
</footer>

<div class="lightbox" id="lightbox" hidden>
  <button class="lb-close" type="button" aria-label="Close">&times;</button>
  <button class="lb-prev" type="button" aria-label="Previous">&#8249;</button>
  <button class="lb-next" type="button" aria-label="Next">&#8250;</button>
  <figure>
    <img src="" alt="" id="lightboxImg">
    <figcaption id="lightboxCap"></figcaption>
  </figure>
</div>

<script src="<?= e(BASE_URL) ?>assets/js/main.js" defer></script>
</body>
</html>
