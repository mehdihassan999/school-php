<?php
/**
 * Silver Oak International School — configuration
 * Pure PHP 8 + MySQL. No frameworks.
 *
 * SECURITY: this file must never be requested directly. It is loaded only via
 * the application entry points, and /includes is blocked by .htaccess.
 */

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

/* ----------------------------------------------------------------------
 |  Database credentials
 |  On cPanel, create the DB + user in the MySQL Databases tool, then paste
 |  the values here (or better, export them as environment variables).
 * -------------------------------------------------------------------- */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'school_website');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/* ----------------------------------------------------------------------
 |  Application paths & URLs
 * -------------------------------------------------------------------- */
define('APP_ROOT', dirname(__DIR__));

$_docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$_appDir  = str_replace('\\', '/', APP_ROOT);
$_rel     = ($_docRoot !== '' && strpos($_appDir, $_docRoot) === 0)
    ? substr($_appDir, strlen($_docRoot))
    : '';
define('BASE_URL', $_rel === '' ? '/' : $_rel . '/');

define('UPLOAD_DIR', APP_ROOT . '/uploads');
define('UPLOAD_URL', BASE_URL . 'uploads');

/* ----------------------------------------------------------------------
 |  Upload rules
 * -------------------------------------------------------------------- */
const UPLOAD_MAX_IMAGE_BYTES = 4 * 1024 * 1024;    // 4 MB
const UPLOAD_MAX_DOC_BYTES   = 10 * 1024 * 1024;   // 10 MB

const ALLOWED_IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];
const ALLOWED_DOC_EXT   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];

/* ----------------------------------------------------------------------
 |  Sessions
 * -------------------------------------------------------------------- */
const SESSION_NAME       = 'so_school_session';
const SESSION_LIFETIME   = 7200;   // 2 hours of inactivity
const LOGIN_MAX_ATTEMPTS = 5;     // per IP …
const LOGIN_WINDOW       = 900;   // … within 15 minutes

/* ----------------------------------------------------------------------
 |  School defaults (used only when a setting is missing from the DB)
 * -------------------------------------------------------------------- */
const DEFAULT_SETTINGS = [
    'school_name'       => 'Silver Oak International School',
    'school_short'      => 'Silver Oak',
    'tagline'           => 'Nurturing Minds. Building Character.',
    'accent_color'      => '#1B3A6B',
    'logo'              => '',
    'favicon'           => '',
    'phone'             => '+1 (555) 014-2450',
    'email'             => 'info@silveroak.example.edu',
    'admissions_email'  => 'admissions@silveroak.example.edu',
    'address'           => '14 Knowledge Parkway, Greenfield, CA 94105',
    'whatsapp'          => '15550142450',
    'office_hours'      => 'Monday – Friday: 7:30 AM – 3:30 PM',
    'facebook'          => '',
    'instagram'         => '',
    'twitter'           => '',
    'youtube'           => '',
    'linkedin'          => '',
    'map_query'         => '',
    'hero_image'        => BASE_URL . 'assets/images/hero.jpg',
    'hero_kicker'       => 'Admissions Open · 2025–26',
    'hero_title'        => 'Where Curiosity Becomes Confidence',
    'hero_subtitle'     => 'A private day school offering an enriched, globally-minded education from Early Years through Grade 12 — small classes, caring teachers, and a campus built for discovery.',
    'stat_students'     => '1250',
    'stat_teachers'     => '85',
    'stat_classes'      => '48',
    'stat_years'        => '25',
    'about_title'       => 'A Legacy of Learning Since 2000',
    'about_short'       => 'For 25 years we have blended academic rigour with warmth, creativity and character. (Demo content)',
    'about_content'     => "Founded in 2000, our school began with 12 classrooms, 9 teachers and a simple belief: children learn best when they are known, challenged and cared for.\n\nToday our green campus serves students from Early Years to Grade 12 with a curriculum that unites the sciences, humanities, arts and athletics.\n\nOur graduates carry with them curiosity, integrity and the confidence to lead.",
    'about_image'       => BASE_URL . 'assets/images/about.jpg',
    'mission'           => 'To provide a world-class education that inspires every student to think critically, act compassionately and lead with integrity.',
    'vision'            => 'A school where every child is known, every talent is discovered, and every graduate steps into the world ready to shape it for the better.',
    'core_values'       => "Academic Excellence: High expectations and a lifelong love of learning.\nIntegrity: We do the right thing — even when no one is watching.\nCompassion: Kindness and service are practiced daily.\nCuriosity: Questions are celebrated as much as answers.\nLeadership: Every student learns to lead themselves first.\nGlobal Citizenship: We prepare students for an interconnected world.",
    'principal_name'        => 'Dr. Eleanor A. Whitfield',
    'principal_designation' => 'Principal',
    'principal_photo'       => BASE_URL . 'assets/images/principal.jpg',
    'principal_message'     => "Thank you for considering our school for your child's education.\n\nA school is its people — and ours are extraordinary. Walk our corridors and you will find robotics beside poetry, debate beside chemistry, and laughter beside serious study.\n\nMy door is always open to parents and students alike.",
    'admission_open'        => '1',
    'admission_note'        => 'Applications for the 2025–26 academic year are now open. Limited seats remain in select grades.',
    'admission_requirements' => "Completed application form\nBirth certificate (copy)\nPrevious 2 years of school reports\nTransfer certificate (if applicable)\nPassport-size photographs\nCopy of parent / guardian ID\nImmunisation record",
    'fee_structure'         => "Early Years (Pre-KG – KG): 1,200 per term\nPrimary (Grades 1 – 5): 1,500 per term\nMiddle School (Grades 6 – 8): 1,800 per term\nSecondary (Grades 9 – 12): 2,200 per term\nOne-time admission fee: 500\nSibling discount: 10% for the second child",
    'admission_dates'       => "Applications open — October 1, 2025\nPriority deadline — January 31, 2026\nEntrance assessments — February 10 – 21, 2026\nFamily interviews — March 2 – 13, 2026\nDecisions released — March 20, 2026\nEnrollment & orientation — April 6 – 17, 2026",
    'curriculum'            => "Our curriculum blends a strong academic core with inquiry-based learning, digital literacy and the arts.\n\nEarly Years follows a play-based framework building literacy, numeracy and social confidence.\n\nSecondary students pursue a college-preparatory program with electives and university counselling from Grade 9.",
    'exam_system'           => "The year has two semesters. Continuous assessment carries 40%; midterm and final examinations 60%.\n\nProgress reports are issued each quarter, followed by parent–teacher conferences.\n\nGrades 10 and 12 sit pre-board examinations each January.",
    'seo_title'             => 'Silver Oak International School | Private Day School',
    'seo_description'       => 'A private day school offering Early Years to Grade 12 education with small classes, expert faculty and a world-class campus. Demo website.',
    'seo_keywords'          => 'private school, international school, admissions, academics',
    'og_image'              => BASE_URL . 'assets/images/hero.jpg',
];

date_default_timezone_set('UTC');
