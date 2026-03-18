<?php
// 数据库连接
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT id, username, email, branch FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "--- User Branch Data ---\n";
foreach ($users as $user) {
    echo "ID: " . str_pad($user['id'], 5) . " | Name: " . str_pad($user['username'], 15) . " | Email: " . str_pad($user['email'], 25) . " | Branch: [" . ($user['branch'] ?? 'NULL') . "]\n";
}
?>
