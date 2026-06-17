<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = get_pdo_connection();
    $stmt = $pdo->query("SELECT * FROM j1stockedit_data LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "First 5 records from j1stockedit_data:\n";
    print_r($data);
    
    $stmt2 = $pdo->query("SHOW COLUMNS FROM j1stockedit_data");
    echo "\nColumns in j1stockedit_data:\n";
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
