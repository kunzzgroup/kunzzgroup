<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/mailer_config.php';

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- Database Diagnosis ---\n";
    
    // 1. Total User Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total = $stmt->fetchColumn();
    echo "Total Users in 'users' table: $total\n";
    
    // 2. Table Status (Auto Increment)
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'users'");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Auto_increment value: " . ($status['Auto_increment'] ?? 'Unknown') . "\n";
    echo "Create_time: " . ($status['Create_time'] ?? 'Unknown') . "\n";
    
    // 3. First 5 users (Earliest)
    echo "\n--- Earliest 5 Users ---\n";
    $stmt = $pdo->query("SELECT id, username, branch, created_at FROM users ORDER BY id ASC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Name: {$row['username']} | Branch: {$row['branch']} | Created: {$row['created_at']}\n";
    }
    
    // 4. Last 5 users (Most Recent)
    echo "\n--- Latest 5 Users ---\n";
    $stmt = $pdo->query("SELECT id, username, branch, created_at FROM users ORDER BY id DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Name: {$row['username']} | Branch: {$row['branch']} | Created: {$row['created_at']}\n";
    }
    
    // 5. Check current session
    session_start();
    echo "\n--- Current Session ---\n";
    echo "User ID: " . ($_SESSION['user_id'] ?? 'None') . "\n";
    echo "Session Branch: " . ($_SESSION['branch'] ?? 'None') . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
