<?php
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';
boot_session();

$programs      = fetch_all('SELECT * FROM academic_programs WHERE published = 1 ORDER BY sort_order, id');
$facilities    = fetch_all('SELECT * FROM facilities WHERE published = 1 ORDER BY sort_order, id LIMIT 6');
$newsItems     = fetch_all('SELECT * FROM news WHERE published = 1 ORDER BY published_at DESC LIMIT 3');
$events        = fetch_all('SELECT * FROM events WHERE published = 1 AND event_date >= NOW() ORDER BY event_date ASC LIMIT 3');
$images        = fetch_all('SELECT i.src, i.caption, a.title AS album FROM gallery_images i JOIN gallery_albums a ON a.id = i.album_id WHERE a.published = 1 ORDER BY i.id DESC LIMIT 8');
$testimonials  = fetch_all('SELECT * FROM testimonials WHERE published = 1 ORDER BY sort_order, id LIMIT 6');
$values        = text_pairs(setting('core_values'));

$pageTitle = '';
$pageDesc  = setting('seo_description');

require __DIR__ . '/includes/header.php';
?>

<!-- ================================ HERO ================================ -->
<section class="hero">
  <img class="hero-bg" src="<?= e(setting_asset('hero_image')) ?>" alt="<?= e(setting('school_name')) ?> campus">
  <div class="hero-veil" aria-hidden="true"></div>
  <div class="wrap">
    <p class="hero-kicker"><?= e(setting('hero_kicker')) ?></p>
    <h1 class="display"><?= e(setting('hero_title')) ?></h1>
    <p class="lead"><?= e(setting('hero_subtitle')) ?></p>
    <div class="hero-actions">
      <a class="btn btn-gold" href="<?= e(BASE_URL) ?>apply.php">Apply for Admission</a>
      <a class="btn btn-ghost" href="<?= e(BASE_URL) ?>about.php">Explore Our School</a>
    </div>
  </div>
  <div class="stats">
    <div class="wrap stats-in">
      <div class="stat"><b><span data-count="<?= e(setting('stat_students')) ?>">0</span></b><span>Students</span></div>
      <div class="stat"><b><span data-count="<?= e(setting('stat_teachers')) ?>">0</span></b><span>Teachers</span></div>
      <div class="stat"><b><span data-count="<?= e(setting('stat_classes')) ?>">0</span></b><span>Classrooms</span></div>
      <div class="stat"><b><span data-count="<?= e(setting('stat_years')) ?>">0</span></b><span>Years of Excellence</span></div>
    </div>
  </div>
</section>

<!-- ============================ ABOUT PREVIEW =========================== -->
<section class="section">
  <div class="wrap split">
    <div class="split-img reveal">
      <img src="<?= e(setting_asset('about_image')) ?>" alt="Students at <?= e(setting('school_name')) ?>" loading="lazy">
      <div class="badge-float">
        <b><?= e(setting('stat_years')) ?>+</b>
        <span>Years of Excellence</span>
      </div>
    </div>
    <div class="reveal">
      <div class="section-head left">
        <p class="kicker">About Our School</p>
        <h2><?= e(setting('about_title')) ?></h2>
      </div>
      <div class="prose">
        <?php foreach (array_slice(text_paragraphs(setting('about_content')), 0, 3) as $p): ?>
          <p><?= e($p) ?></p>
        <?php endforeach; ?>
      </div>
      <div class="ticks">
        <?php foreach (array_slice($values, 0, 4) as $v): ?>
          <div class="tick"><?= e($v['title']) ?></div>
        <?php endforeach; ?>
      </div>
      <a class="btn btn-outline" href="<?= e(BASE_URL) ?>about.php">Read Our Full Story</a>
    </div>
  </div>
</section>

<!-- ========================= PRINCIPAL'S MESSAGE ======================== -->
<section class="section section-cream">
  <div class="wrap">
    <div class="card principal reveal">
      <div class="principal-img">
        <img src="<?= e(setting_asset('principal_photo')) ?>" alt="<?= e(setting('principal_name')) ?>" loading="lazy">
      </div>
      <div class="principal-body">
        <span class="quote-mark" aria-hidden="true">&ldquo;</span>
        <p class="kicker">Principal&rsquo;s Message</p>
        <div class="prose" style="margin-top:16px">
          <?php foreach (array_slice(text_paragraphs(setting('principal_message')), 0, 2) as $p): ?>
            <p><?= e($p) ?></p>
          <?php endforeach; ?>
        </div>
        <div class="principal-sign">
          <span class="rule" aria-hidden="true"></span>
          <div>
            <b><?= e(setting('principal_name')) ?></b>
            <span><?= e(setting('principal_designation')) ?>, <?= e(setting('school_short')) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================== ACADEMIC PROGRAMS ========================= -->
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Academic Programs</p>
      <h2>A Journey From Early Years to Graduation</h2>
      <p>Four stages, one promise — an education that grows with your child.</p>
    </div>
    <div class="grid g4">
      <?php foreach ($programs as $p): ?>
      <article class="card card-hover media-card reveal">
        <div class="thumb">
          <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
        </div>
        <div class="body">
          <h3><?= e($p['title']) ?></h3>
          <p><?= e($p['summary']) ?></p>
          <a class="more" href="<?= e(BASE_URL) ?>academics.php#<?= e($p['slug']) ?>">Learn More &rarr;</a>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (!$programs): ?>
        <p class="g4">Programs will appear here once added in the admin panel.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================ WHY CHOOSE US =========================== -->
<section class="section features">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker kicker-light center">Why Choose Us</p>
      <h2>An Education Built Around Your Child</h2>
      <p>Six reasons families choose — and stay with — <?= e(setting('school_short')) ?>.</p>
    </div>
    <div class="grid g3">
      <?php
      $icons = ['&#9733;', '&#9728;', '&#9878;', '&#9824;', '&#9737;', '&#9884;'];
      foreach ($values as $i => $v): ?>
      <div class="feature reveal">
        <span class="feature-ico"><?= $icons[$i % count($icons)] ?></span>
        <h3><?= e($v['title']) ?></h3>
        <p><?= e($v['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================== FACILITIES ============================ -->
<section class="section">
  <div class="wrap">
    <div class="section-head left reveal" style="max-width:none">
      <p class="kicker">Campus &amp; Facilities</p>
      <h2>Spaces Designed for Discovery</h2>
    </div>
    <div class="grid g3">
      <?php foreach ($facilities as $f): ?>
      <a class="card card-hover media-card reveal" href="<?= e(BASE_URL) ?>facilities.php">
        <div class="thumb"><img src="<?= e(img($f['image'])) ?>" alt="<?= e($f['title']) ?>" loading="lazy"></div>
        <div class="body">
          <h3><?= e($f['title']) ?></h3>
          <p><?= e($f['description']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================== NEWS AND EVENTS ========================== -->
<section class="section section-cream">
  <div class="wrap">
    <div class="section-head left reveal" style="max-width:none">
      <p class="kicker">Latest Updates</p>
      <h2>News &amp; Upcoming Events</h2>
    </div>
    <div class="grid" style="grid-template-columns:1fr;gap:26px">
      <div class="grid" style="gap:22px">
        <?php foreach ($newsItems as $n): ?>
        <a class="card card-hover media-card reveal" style="flex-direction:row" href="<?= e(BASE_URL) ?>news.php?slug=<?= e($n['slug']) ?>">
          <div class="thumb" style="flex:0 0 220px"><img src="<?= e(img($n['image'])) ?>" alt="<?= e($n['title']) ?>" loading="lazy"></div>
          <div class="body">
            <p class="meta"><b><?= e($n['category']) ?></b> <span><?= e(fmt_date($n['published_at'])) ?></span></p>
            <h3><?= e($n['title']) ?></h3>
            <p><?= e(excerpt($n['excerpt'], 120)) ?></p>
          </div>
        </a>
        <?php endforeach; ?>
        <?php if (!$newsItems): ?><p>No news published yet.</p><?php endif; ?>
      </div>

      <aside class="card card-pad reveal">
        <h3 style="display:flex;align-items:center;gap:9px">&#128197; Upcoming Events</h3>
        <ul style="margin-top:20px;display:grid;gap:20px">
          <?php foreach ($events as $ev): $d = strtotime($ev['event_date']); ?>
          <li>
            <a class="event-row" href="<?= e(BASE_URL) ?>events.php?slug=<?= e($ev['slug']) ?>">
              <span class="date-chip"><b><?= e(date('j', $d)) ?></b><span><?= e(date('M', $d)) ?></span></span>
              <span>
                <h3 style="font-size:15px"><?= e($ev['title']) ?></h3>
                <p><?= e($ev['location']) ?></p>
              </span>
            </a>
          </li>
          <?php endforeach; ?>
          <?php if (!$events): ?><li>No upcoming events scheduled.</li><?php endif; ?>
        </ul>
        <a class="more" style="display:inline-block;margin-top:20px;color:var(--accent);font-weight:600;font-size:13.5px" href="<?= e(BASE_URL) ?>events.php">Full Calendar &rarr;</a>
      </aside>
    </div>
  </div>
</section>

<!-- ========================== GALLERY PREVIEW ========================== -->
<?php if ($images): ?>
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Gallery</p>
      <h2>Life at <?= e(setting('school_short')) ?></h2>
      <p>Moments from our classrooms, stages, fields and trips.</p>
    </div>
    <div class="grid g4" data-lightbox-group>
      <?php foreach ($images as $im): ?>
        <a class="gal" href="<?= e(BASE_URL) ?>gallery.php" data-lightbox data-full="<?= e(asset_url($im['src'])) ?>" data-caption="<?= e($im['caption'] ?: $im['album']) ?>">
          <img src="<?= e(asset_url($im['src'])) ?>" alt="<?= e($im['caption'] ?: 'School life') ?>" style="aspect-ratio:1;object-fit:cover;border-radius:11px" loading="lazy">
          <figcaption><?= e($im['caption'] ?: $im['album']) ?></figcaption>
        </a>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:34px">
      <a class="btn btn-outline" href="<?= e(BASE_URL) ?>gallery.php">Open Full Gallery</a>
    </p>
  </div>
</section>
<?php endif; ?>

<!-- ============================ TESTIMONIALS =========================== -->
<?php if ($testimonials): ?>
<section class="section section-cream">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Testimonials</p>
      <h2>What Our Community Says</h2>
    </div>
    <div class="grid g3">
      <?php foreach ($testimonials as $t): ?>
      <figure class="quote reveal">
        <span class="q" aria-hidden="true">&ldquo;</span>
        <blockquote><?= e($t['quote']) ?></blockquote>
        <footer>
          <?php if ($t['photo']): ?>
            <img class="avatar" style="object-fit:cover" src="<?= e(asset_url($t['photo'])) ?>" alt="<?= e($t['name']) ?>" loading="lazy">
          <?php else: ?>
            <span class="avatar"><?= e(mb_substr($t['name'], 0, 1)) ?></span>
          <?php endif; ?>
          <div><b><?= e($t['name']) ?></b><span><?= e($t['designation']) ?></span></div>
        </footer>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================ ADMISSION CTA ========================== -->
<section class="cta">
  <div class="wrap reveal">
    <p class="hero-kicker" style="border-color:rgba(255,255,255,.45)"><?= e(setting('admission_open') === '1' ? 'Admissions Are Open' : 'Admission Enquiries') ?></p>
    <h2 class="display">Give Your Child the <?= e(setting('school_short')) ?> Advantage</h2>
    <p><?= e(setting('admission_note')) ?></p>
    <div class="cta-actions">
      <a class="btn btn-accent" href="<?= e(BASE_URL) ?>apply.php">Apply Now</a>
      <a class="btn btn-ghost" style="border-color:#fff;color:#fff" href="<?= e(BASE_URL) ?>admissions.php">Admission Information</a>
    </div>
  </div>
</section>

<!-- ========================== CONTACT PREVIEW ========================== -->
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="kicker center">Get in Touch</p>
      <h2>Visit Our Campus</h2>
      <p>We would love to meet your family. Call, message, or drop in during office hours.</p>
    </div>
    <div class="grid g2">
      <div class="grid" style="gap:15px">
        <?php
        $cards = [
          ['&#128205;', 'Address', setting('address'), 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(setting('address'))],
          ['&#9742;', 'Phone', setting('phone'), tel_href(setting('phone'))],
          ['&#9993;', 'Email', setting('email'), 'mailto:' . setting('email')],
          ['&#128172;', 'WhatsApp', 'Chat with our admissions team', whatsapp_href(setting('whatsapp'))],
        ];
        foreach ($cards as $c): ?>
        <a class="card card-hover contact-card reveal" href="<?= e($c[3]) ?>" <?= strpos($c[3], 'http') === 0 ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
          <span class="ico"><?= $c[0] ?></span>
          <span><b><?= e($c[1]) ?></b><span><?= e($c[2]) ?></span></span>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="map-frame reveal">
        <iframe title="<?= e(setting('school_name')) ?> location map" src="<?= e(map_src(setting('address'))) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
