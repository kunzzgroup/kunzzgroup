<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- Users ---\n";
    $stmt = $pdo->query("SELECT id, username, position FROM users LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }

    echo "\n--- J1 Data for March 2026 ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM j1data WHERE date BETWEEN '2026-03-01' AND '2026-03-31'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Count: " . $row['count'] . "\n";

    echo "\n--- Recent J1 Data ---\n";
    $stmt = $pdo->query("SELECT * FROM j1data ORDER BY date DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }

    echo "\n--- User Permissions (last 5) ---\n";
    $stmt = $pdo->query("SELECT user_id, page_permissions_json, restaurant_permissions_json FROM user_sidebar_permissions ORDER BY updated_at DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
