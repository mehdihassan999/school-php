<?php
/**
 * Dynamic sitemap.xml (news + events + static pages).
 */
define('APP_RUNNING', true);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$base = rtrim($host . BASE_URL, '/');

$static = ['index.php', 'about.php', 'academics.php', 'admissions.php', 'apply.php',
           'faculty.php', 'facilities.php', 'student-life.php', 'news.php', 'events.php',
           'gallery.php', 'notices.php', 'downloads.php', 'contact.php'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($static as $p) {
    echo '  <url><loc>' . e($base . '/' . $p) . '</loc><changefreq>monthly</changefreq>'
       . '<priority>' . ($p === 'index.php' ? '1.0' : '0.7') . '</priority></url>' . "\n";
}

try {
    foreach (fetch_all('SELECT slug FROM news WHERE published = 1') as $n) {
        echo '  <url><loc>' . e($base . '/news.php?slug=' . $n['slug']) . '</loc><priority>0.6</priority></url>' . "\n";
    }
    foreach (fetch_all('SELECT slug FROM events WHERE published = 1') as $ev) {
        echo '  <url><loc>' . e($base . '/events.php?slug=' . $ev['slug']) . '</loc><priority>0.6</priority></url>' . "\n";
    }
} catch (Throwable $e) {
    // fresh install — static routes only
}

echo '</urlset>' . "\n";
