<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking j1data table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM j1data");
    $count = $stmt->fetchColumn();
    echo "Total records in j1data: " . $count . "\n";

    if ($count > 0) {
        echo "Last 5 records:\n";
        $stmt = $pdo->query("SELECT * FROM j1data ORDER BY date DESC LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }

    echo "\nChecking j2data and j3data for comparison...\n";
    echo "Total records in j2data: " . $pdo->query("SELECT COUNT(*) FROM j2data")->fetchColumn() . "\n";
    echo "Total records in j3data: " . $pdo->query("SELECT COUNT(*) FROM j3data")->fetchColumn() . "\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
