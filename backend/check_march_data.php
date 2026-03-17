<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach (['j1data', 'j2data', 'j3data'] as $table) {
        echo "Table: $table\n";
        $stmt = $pdo->query("SELECT date, gross_sales, tender_amount, updated_at FROM $table WHERE date >= '2026-03-01' ORDER BY date ASC LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            echo "No records for March 2026.\n";
        } else {
            foreach ($rows as $row) {
                echo "  Date: {$row['date']} | Gross: {$row['gross_sales']} | Tender: {$row['tender_amount']} | Updated: {$row['updated_at']}\n";
            }
        }
        echo "------------------\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
