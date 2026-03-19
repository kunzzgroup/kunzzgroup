<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = [
        'stockinout_data', 
        'j1stockedit_data', 
        'j2stockedit_data', 
        'j3stockedit_data',
        'j1stockinout_data',
        'j2stockinout_data',
        'j3stockinout_data'
    ];
    
    foreach ($tables as $table) {
        echo "Updating table: $table... ";
        try {
            // This command modifies the column to DATE and removes the default if it was CURRENT_DATE
            $pdo->exec("ALTER TABLE $table MODIFY COLUMN date DATE NOT NULL");
            echo "Success.\n";
        } catch (PDOException $e) {
            echo "Error or already updated: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nDatabase schema update complete.\n";

} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
}
