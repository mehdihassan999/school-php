<?php
define('APP_RUNNING', true);
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/database.php';

$hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
echo "New hash for 'Admin@1234': " . $hash . "\n";

q('UPDATE admins SET password_hash = ? WHERE username = ? OR email = ?', [$hash, 'admin', 'admin@example.edu']);
echo "Updated admins table in DB!\n";

q('DELETE FROM login_attempts');
echo "Cleared login_attempts table!\n";

$admin = fetch_one('SELECT * FROM admins WHERE username = ?', ['admin']);
if ($admin && password_verify('Admin@1234', $admin['password_hash'])) {
    echo "VERIFICATION SUCCESSFUL! Password 'Admin@1234' works for user 'admin' or 'admin@example.edu'\n";
} else {
    echo "VERIFICATION FAILED!\n";
}
