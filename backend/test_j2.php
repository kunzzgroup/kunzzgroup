<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u690174784_kunzz;charset=utf8mb4', 'u690174784_kunzz', 'Kunzz1688');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT 
                REPLACE(product_name, '&amp;', '&') as product_name,
                code_number,
                specification,
                SUM(in_quantity) as total_in,
                SUM(out_quantity) as total_out,
                SUM(in_quantity) - SUM(out_quantity) as total_qty
            FROM j2stockedit_data
            WHERE product_name LIKE '%A&%'
            GROUP BY REPLACE(product_name, '&amp;', '&'), code_number, specification
            ORDER BY REPLACE(product_name, '&amp;', '&')";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $totals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>J2 stocklist_total Group By Test:\n";
    print_r($totals);
    echo "</pre>";

    $sql2 = "SELECT id, product_code, product_name FROM stock_data WHERE product_name LIKE '%A&%'";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $sd = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>stock_data list:\n";
    print_r($sd);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
