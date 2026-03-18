<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 数据库配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "正在检查 users 表结构...<br>";
    
    // 增加 branch 列的长度
    $sql = "ALTER TABLE users MODIFY COLUMN branch VARCHAR(255) DEFAULT NULL";
    $pdo->exec($sql);
    
    echo "成功！已将 branch 列长度扩展到 255 字符。<br>";
    
} catch(PDOException $e) {
    echo "错误: " . $e->getMessage() . "<br>";
}
?>
