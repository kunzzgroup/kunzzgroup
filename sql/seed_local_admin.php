<?php
require dirname(__DIR__) . '/backend/xss_protect.php';

$conn = new mysqli('localhost', 'root', '', 'kunzz');
if ($conn->connect_error) {
    die('connect failed');
}

$email = 'admin@kunzzgroup.com';
$password = 'Admin@12345';
$hash = secure_hash_password($password);

$stmt = $conn->prepare(
    'INSERT INTO users (username, email, password, position, account_type, branch, is_first_login, registration_code)
     VALUES (?, ?, ?, ?, ?, ?, 0, ?)
     ON DUPLICATE KEY UPDATE password = VALUES(password), username = VALUES(username)'
);
$username = 'Local Admin';
$position = 'Admin';
$accountType = 'special';
$branch = 'J1';
$code = 'LOCALDEV001';
$stmt->bind_param('sssssss', $username, $email, $hash, $position, $accountType, $branch, $code);
$stmt->execute();

echo verify_secure_password($password, $hash) ? "seed_ok\n" : "seed_fail\n";
