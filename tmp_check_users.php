<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT id, username, position, branch FROM users ORDER BY created_at DESC, id DESC LIMIT 15");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
