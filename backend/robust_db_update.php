<?php
/**
 * robust_db_update.php
 * A resilient script to ensure all necessary columns for soft delete are present.
 */
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

header('Content-Type: text/plain');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = [
        'j1stockedit_data', 'j2stockedit_data', 'j3stockedit_data',
        'stockinout_data',
        'j1stockinout_data', 'j2stockinout_data', 'j3stockinout_data',
        'j1stockeditmobile_data', 'j2stockeditmobile_data', 'j3stockeditmobile_data'
    ];
    
    foreach ($tables as $table) {
        echo "Processing table: $table\n";
        try {
            // Ensure deleted_at exists
            $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_at'");
            if (!$check->fetch()) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN deleted_at DATETIME NULL");
                echo "  [SUCCESS] Added deleted_at\n";
            } else {
                echo "  [SKIP] deleted_at already exists\n";
            }
            
            // Ensure deleted_by exists
            $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_by'");
            if (!$check->fetch()) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN deleted_by VARCHAR(50) NULL");
                echo "  [SUCCESS] Added deleted_by\n";
            } else {
                echo "  [SKIP] deleted_by already exists\n";
            }
        } catch (PDOException $e) {
            echo "  [ERROR] " . $e->getMessage() . "\n";
        }
        echo "-----------------------------------\n";
    }
    echo "Database update process finished.\n";
} catch (PDOException $e) {
    echo "CRITICAL ERROR: Connection failed: " . $e->getMessage() . "\n";
}
?>
