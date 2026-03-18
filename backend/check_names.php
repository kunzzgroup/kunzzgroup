<?php
$pdo = new PDO('mysql:host=localhost;dbname=u690174784_kunzz;charset=utf8mb4', 'u690174784_kunzz', 'Kunzz1688');

$stmt = $pdo->prepare("SELECT id, product_code, product_name, system_assign FROM stock_data WHERE product_name LIKE '%A&%' OR product_name LIKE '%GOLD FLAKE%'");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($rows);
