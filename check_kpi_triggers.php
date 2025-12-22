<?php
// 检查 KPI 表是否有触发器会自动创建 cost 记录

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>KPI 表触发器检查</h2>\n\n";
    
    $kpiTables = ['j1data', 'j2data', 'j3data'];
    
    foreach ($kpiTables as $table) {
        echo "<h3>表: {$table}</h3>\n";
        
        // 检查触发器
        $stmt = $pdo->query("SHOW TRIGGERS WHERE `Table` = '{$table}'");
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($triggers) > 0) {
            echo "<h4 style='color: red;'>发现触发器:</h4>\n";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>\n";
            echo "<tr><th>触发器名</th><th>事件</th><th>时机</th><th>定义</th></tr>\n";
            
            foreach ($triggers as $trigger) {
                echo "<tr>";
                echo "<td><strong>{$trigger['Trigger']}</strong></td>";
                echo "<td>{$trigger['Event']}</td>";
                echo "<td>{$trigger['Timing']}</td>";
                echo "<td style='font-family: monospace; font-size: 12px;'>" . htmlspecialchars($trigger['Statement']) . "</td>";
                echo "</tr>\n";
                
                // 检查是否包含 sales 字段
                if (stripos($trigger['Statement'], 'sales') !== false) {
                    echo "<tr><td colspan='4' style='color: red; background: #ffeeee;'><strong>⚠️ 警告：此触发器包含 'sales' 字段！</strong></td></tr>\n";
                }
            }
            echo "</table>\n";
        } else {
            echo "<p style='color: green;'>✓ 没有触发器</p>\n";
        }
        
        echo "<hr>\n\n";
    }
    
    echo "<h3>建议</h3>\n";
    echo "<p>如果发现触发器尝试插入 sales 字段到 cost 表，需要：</p>\n";
    echo "<ol>\n";
    echo "<li>删除或修改这些触发器</li>\n";
    echo "<li>移除触发器中对 sales 字段的引用</li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>错误: " . $e->getMessage() . "</p>\n";
}
?>

