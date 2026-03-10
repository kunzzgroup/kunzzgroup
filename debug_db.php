<?php
require_once __DIR__ . '/backend/menu_api.php';

try {
    $pdo = getDB();
    
    echo "--- Category Stats ---\n";
    $stmt = $pdo->query("SELECT menu_type, COUNT(*) as count FROM menu_categories GROUP BY menu_type");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Type: {$row['menu_type']}, Categories: {$row['count']}\n";
    }

    echo "\n--- Item Stats (Overall) ---\n";
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM menus GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Status: {$row['status']}, Count: {$row['count']}\n";
    }

    echo "\n--- Items per Category ---\n";
    $stmt = $pdo->query("SELECT mc.category_name, mc.menu_type, COUNT(m.id) as item_count 
                         FROM menu_categories mc 
                         LEFT JOIN menus m ON mc.id = m.category_id 
                         GROUP BY mc.id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Category: {$row['category_name']} ({$row['menu_type']}), Items: {$row['item_count']}\n";
    }

    echo "\n--- Detailed check for first 5 items ---\n";
    $stmt = $pdo->query("SELECT id, category_id, item_name, status, menu_type FROM menus LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, CatID: {$row['category_id']}, Name: {$row['item_name']}, Status: {$row['status']}, Type: {$row['menu_type']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
