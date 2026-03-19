<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/mailer_config.php';

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    echo "--- Users Table Audit ---\n";
    $stmt = $pdo->query("SELECT id, username, account_type, created_at FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $u) {
        echo "ID: " . str_pad($u['id'], 5) . " | Name: " . str_pad($u['username'], 30) . " | Type: " . str_pad($u['account_type'], 15) . " | Created: " . $u['created_at'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
