<?php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "正在检查 users 表结构...\n";

    // 检查是否有 branch 列
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'branch'");
    if (!$stmt->fetch()) {
        echo "添加 branch 列...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN branch VARCHAR(50) DEFAULT NULL AFTER email");
    } else {
        echo "branch 列已存在。\n";
    }

    // 检查是否有 account_type 列
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'account_type'");
    if (!$stmt->fetch()) {
        echo "添加 account_type 列...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN account_type VARCHAR(50) DEFAULT 'staff' AFTER branch");
    } else {
        echo "account_type 列已存在。\n";
    }

    echo "数据库更新完成。\n";

} catch (PDOException $e) {
    die("数据库错误: " . $e->getMessage());
}
?>
