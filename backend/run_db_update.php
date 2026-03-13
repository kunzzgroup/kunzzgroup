<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/add_deleted_at_column.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if ($stmt && strpos($stmt, '--') !== 0) {
            try {
                $pdo->exec($stmt);
                echo "Executed: " . substr($stmt, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "Skipped/Error: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Database update completed.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
