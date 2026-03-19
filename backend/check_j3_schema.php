<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- j3stockedit_data Schema ---\n";
    $stmt = $pdo->query("DESCRIBE j3stockedit_data");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
