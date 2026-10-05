<?php
define('APP_RUNNING', true);
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/database.php';

$new_email = 'm.qazim1997@gmail.com';
$new_password = 'B4lgh4ri786@#$%';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "Updating admin credentials...\n";
q('UPDATE admins SET email = ?, password_hash = ? WHERE username = ? OR id = 1', [$new_email, $hash, 'admin']);
echo "Updated admins table in DB!\n";

q('DELETE FROM login_attempts');
echo "Cleared login_attempts table!\n";

$admin = fetch_one('SELECT * FROM admins WHERE username = ? OR email = ?', ['admin', $new_email]);
if ($admin && password_verify($new_password, $admin['password_hash'])) {
    echo "VERIFICATION SUCCESSFUL!\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Password verified successfully!\n";
} else {
    echo "VERIFICATION FAILED!\n";
}
