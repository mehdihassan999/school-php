<?php
define('APP_RUNNING', true);
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/database.php';

$password = 'Hacker123@@';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Generated Hash for '$password': $hash\n";

q('UPDATE admins SET password_hash = ? WHERE username = ? OR email = ?', [$hash, 'admin', 'admin@example.edu']);
echo "Updated admins table in DB!\n";

q('DELETE FROM login_attempts');
echo "Cleared login_attempts table!\n";

$admin = fetch_one('SELECT * FROM admins WHERE username = ?', ['admin']);
if ($admin && password_verify($password, $admin['password_hash'])) {
    echo "VERIFICATION SUCCESSFUL! Password '$password' works for user 'admin' / 'admin@example.edu'\n";
} else {
    echo "VERIFICATION FAILED!\n";
}
