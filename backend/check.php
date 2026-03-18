<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u690174784_kunzz;charset=utf8mb4', 'u690174784_kunzz', 'Kunzz1688');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id, product_code, product_name, system_assign FROM stock_data WHERE product_name LIKE '%A&%' OR product_name LIKE '%GOLD FLAKE%'");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($rows);
    echo "</pre>";

    $stmt2 = $pdo->prepare("SELECT id, code_number, product_name FROM j2stockedit_data WHERE product_name LIKE '%A&%'");
    $stmt2->execute();
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>J2 Stock Edit Data:\n";
    print_r($rows2);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
