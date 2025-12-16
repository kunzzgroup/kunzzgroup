<?php
// 诊断成本表问题的脚本

$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>成本表诊断报告</h2>\n\n";
    
    // 检查每个表
    $tables = ['j1cost', 'j2cost', 'j3cost'];
    
    foreach ($tables as $table) {
        echo "<h3>检查表: {$table}</h3>\n";
        
        // 1. 检查表结构
        echo "<h4>表结构:</h4>\n";
        $stmt = $pdo->query("SHOW CREATE TABLE {$table}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>" . htmlspecialchars($result['Create Table']) . "</pre>\n\n";
        
        // 2. 检查触发器
        echo "<h4>触发器:</h4>\n";
        $stmt = $pdo->query("SHOW TRIGGERS WHERE `Table` = '{$table}'");
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($triggers) > 0) {
            echo "<pre>" . print_r($triggers, true) . "</pre>\n";
        } else {
            echo "<p>没有触发器</p>\n";
        }
        
        // 3. 检查示例数据
        echo "<h4>最近5条记录:</h4>\n";
        $stmt = $pdo->query("SELECT * FROM {$table} ORDER BY date DESC LIMIT 5");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($records, true) . "</pre>\n\n";
        
        echo "<hr>\n\n";
    }
    
    // 测试更新操作
    echo "<h3>测试更新操作</h3>\n";
    echo "<p>请手动测试：</p>\n";
    echo "<ol>\n";
    echo "<li>找到一条现有记录，记下它的 sales 值（例如：100）</li>\n";
    echo "<li>在前端更新这条记录，将 sales 改为其他值（例如：200）</li>\n";
    echo "<li>保存后，检查数据库中的实际值</li>\n";
    echo "<li>如果值变成了 300（100+200），说明有触发器在累加</li>\n";
    echo "<li>如果值是 200，说明更新正常，问题在前端显示</li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage();
}
?>

