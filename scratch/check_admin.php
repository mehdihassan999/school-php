<?php
define('APP_RUNNING', true);
require __DIR__ . '/../includes/config.php';

echo "Testing DB connection...\n";
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "DB Connection SUCCESS!\n";

    $stmt = $pdo->query("SELECT * FROM admins");
    $admins = $stmt->fetchAll();
    echo "Admins found: " . count($admins) . "\n";
    foreach ($admins as $a) {
        echo "ID: {$a['id']}, Username: {$a['username']}, Email: {$a['email']}\n";
        echo "Verifying password 'Admin@1234': " . (password_verify('Admin@1234', $a['password_hash']) ? "MATCH!" : "NO MATCH") . "\n";
    }

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM login_attempts");
    $attempts = $stmt->fetch()['cnt'];
    echo "Total login attempts logged: $attempts\n";

} catch (Exception $e) {
    echo "DB Connection/Query Error: " . $e->getMessage() . "\n";
}
