<?php
require_once __DIR__ . '/backend/menu_api.php';

try {
    $pdo = getDB();
    
    echo "--- Category Links ---\n";
    $stmt = $pdo->query("SELECT id, category_name, menu_type FROM menu_categories");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cats as $cat) {
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM menus WHERE category_id = ?");
        $stmt2->execute([$cat['id']]);
        $count = $stmt2->fetchColumn();
        echo "Cat ID: {$cat['id']} | Name: {$cat['category_name']} | Type: {$cat['menu_type']} | Items: {$count}\n";
    }

    echo "\n--- Floating Items (No category) ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM menus WHERE category_id IS NULL OR category_id = 0");
    echo "Count: " . $stmt->fetchColumn() . "\n";

    echo "\n--- Item sample ---\n";
    $stmt = $pdo->query("SELECT * FROM menus LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
