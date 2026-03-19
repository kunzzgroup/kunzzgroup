<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/mailer_config.php';

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    echo "--- Users Table Detailed Audit ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total = $stmt->fetchColumn();
    echo "Total Users: $total\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM application_codes");
    $totalCodes = $stmt->fetchColumn();
    echo "Total Application Codes: $totalCodes\n\n";

    $fields = ['username', 'username_cn', 'position', 'branch', 'email', 'phone_number', 'ic_number'];
    foreach ($fields as $field) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE `$field` IS NULL OR `$field` = '' OR `$field` = '-' ");
        $emptyCount = $stmt->fetchColumn();
        echo "Field: " . str_pad($field, 20) . " | Empty/Null/Dash: $emptyCount\n";
    }

    echo "\n--- Tables in Database ---\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table` ")->fetchColumn();
        echo "$table: $count lines\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
