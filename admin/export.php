<?php
/**
 * CSV export of admission applications (admin only).
 */
define('APP_RUNNING', true);
require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/database.php';
require dirname(__DIR__) . '/includes/security.php';
require dirname(__DIR__) . '/includes/functions.php';
boot_session();

if (!current_admin()) {
    http_response_code(401);
    exit('Unauthorized');
}

if (($_GET['type'] ?? '') !== 'admissions') {
    http_response_code(400);
    exit('Unknown export type');
}

$rows = fetch_all('SELECT * FROM admissions ORDER BY created_at DESC');

$cells = function (array $v): string {
    return '"' . str_replace('"', '""', (string) $v) . '"';
};

$out = fopen('php://temp', 'r+');
fputcsv($out, ['ID', 'Student Name', 'Date of Birth', 'Gender', 'Grade', 'Previous School',
               'Parent/Guardian', 'Relationship', 'Phone', 'Email', 'Address', 'Emergency Contact',
               'Additional Info', 'Document', 'Status', 'Submitted At']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'], $r['student_name'], $r['dob'], $r['gender'], $r['grade'], $r['previous_school'],
        $r['parent_name'], $r['relationship'], $r['phone'], $r['email'], $r['address'],
        $r['emergency_contact'], $r['additional_info'], $r['document'], $r['status'], $r['created_at'],
    ]);
}
rewind($out);
$csv = (string) stream_get_contents($out);
fclose($out);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="admission-applications-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

// BOM so Excel opens UTF-8 correctly
echo "\xEF\xBB\xBF" . $csv;
