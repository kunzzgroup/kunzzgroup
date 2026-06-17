<?php
ob_start();
require_once __DIR__ . '/backend/menu_api.php';

try {
    $pdo = getDB();
    
    echo "--- Category IDs ---\n";
    $stmt = $pdo->query("SELECT id, category_name, menu_type FROM menu_categories");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Name: {$row['category_name']} | Type: {$row['menu_type']}\n";
    }

    echo "\n--- All Items ---\n";
    $stmt = $pdo->query("SELECT id, category_id, item_name, status, menu_type FROM menus");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | CatID: {$row['category_id']} | Name: {$row['item_name']} | Status: {$row['status']} | Type: {$row['menu_type']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$output = ob_get_clean();
file_put_contents(__DIR__ . '/db_dump.txt', $output);
echo "Dumped to db_dump.txt";
