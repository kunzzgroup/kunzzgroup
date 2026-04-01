<?php
require 'db_connect.php';
$stmt = $pdo->query("SELECT id, product_name, code_number FROM dishware_info WHERE code_number LIKE 'se%' OR code_number LIKE 'SET%' LIMIT 20");
echo "=== Single Items ===\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $pdo->query("SELECT id, set_name as product_name, set_code as code_number FROM dishware_sets WHERE set_code LIKE 'se%' OR set_code LIKE 'SET%' LIMIT 20");
echo "=== Sets ===\n";
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
