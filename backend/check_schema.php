<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/mailer_config.php';

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    echo "--- Users Table Schema ---\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "Field: " . str_pad($col['Field'], 20) . " | Type: " . str_pad($col['Type'], 20) . " | Null: " . $col['Null'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
