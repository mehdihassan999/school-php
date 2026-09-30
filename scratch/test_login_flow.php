<?php
define('APP_RUNNING', true);
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/database.php';
require __DIR__ . '/../includes/security.php';

boot_session();

$username = 'admin@example.edu';
$password = 'Hacker123@@';

$admin = fetch_one(
    'SELECT * FROM admins WHERE (username = ? OR email = ?) LIMIT 1',
    [$username, $username]
);

if ($admin && password_verify($password, $admin['password_hash'])) {
    $_SESSION['admin_id'] = (int) $admin['id'];
    echo "LOGIN VERIFICATION SUCCESSFUL!\n";
    echo "Admin ID: " . $_SESSION['admin_id'] . "\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
} else {
    echo "LOGIN VERIFICATION FAILED!\n";
}
