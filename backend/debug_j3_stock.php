<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- j3stockedit_data Sample (Last 5) ---\n";
    $stmt = $pdo->query("SELECT * FROM j3stockedit_data ORDER BY id DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }

    echo "\n--- j3stockedit_data Summary Statistics ---\n";
    $stmt = $pdo->query("SELECT 
                            COUNT(*) as total_rows,
                            SUM(CASE WHEN in_quantity > 0 THEN 1 ELSE 0 END) as in_rows,
                            SUM(CASE WHEN out_quantity > 0 THEN 1 ELSE 0 END) as out_rows,
                            SUM(in_quantity) as total_in,
                            SUM(out_quantity) as total_out
                         FROM j3stockedit_data 
                         WHERE deleted_at IS NULL");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row) . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
