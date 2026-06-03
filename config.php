<?php
$host = 'localhost';
$dbname = 'kunzz';
$dbuser = 'root';
$dbpass = '';
 
$pdo = null;
try {
 
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4;connect_timeout=5",
        $dbuser,
        $dbpass,
        [PDO::ATTR_TIMEOUT => 5]
    );
 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
 
 
    // 设置MySQL连接的时区
 
    $pdo->exec("SET time_zone = '+08:00'");
 
} catch (PDOException $e) {
 
    error_log('Database connection failed: ' . $e->getMessage());
 
    $pdo = null;
 
}
?>
 