<?php
// 测试成本表更新是否累加的脚本

$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>成本表更新测试</h2>\n\n";
    
    $tables = ['j1cost', 'j2cost', 'j3cost'];
    
    foreach ($tables as $table) {
        echo "<h3>测试表: {$table}</h3>\n";
        
        // 1. 检查是否有触发器
        $stmt = $pdo->query("SHOW TRIGGERS WHERE `Table` = '{$table}'");
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($triggers) > 0) {
            echo "<p style='color: red;'><strong>警告！发现触发器:</strong></p>\n";
            echo "<pre>" . print_r($triggers, true) . "</pre>\n";
        } else {
            echo "<p style='color: green;'>✓ 没有触发器</p>\n";
        }
        
        // 2. 检查表结构
        $stmt = $pdo->query("DESCRIBE {$table}");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>字段定义:</h4>\n";
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>字段</th><th>类型</th><th>Extra</th></tr>\n";
        foreach ($columns as $col) {
            $isGenerated = strpos($col['Extra'], 'GENERATED') !== false || strpos($col['Extra'], 'VIRTUAL') !== false || strpos($col['Extra'], 'STORED') !== false;
            $color = $isGenerated ? 'blue' : 'black';
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td style='color: {$color};'>{$col['Extra']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n\n";
        
        // 3. 测试更新操作
        echo "<h4>测试更新操作:</h4>\n";
        
        // 查找或创建一条测试记录
        $testDate = '2025-11-04';
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE date = ?");
        $stmt->execute([$testDate]);
        $testRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($testRecord) {
            echo "<p>找到测试记录 (日期: {$testDate}):</p>\n";
            echo "<p>当前值 - Sales: {$testRecord['sales']}, C_Beverage: {$testRecord['c_beverage']}, C_Kitchen: {$testRecord['c_kitchen']}</p>\n";
            
            // 尝试更新为固定值
            $newSales = 999.99;
            $newBeverage = 111.11;
            $newKitchen = 222.22;
            
            echo "<p>尝试更新为 - Sales: {$newSales}, C_Beverage: {$newBeverage}, C_Kitchen: {$newKitchen}</p>\n";
            
            $updateSql = "UPDATE {$table} SET sales = ?, c_beverage = ?, c_kitchen = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$newSales, $newBeverage, $newKitchen, $testRecord['id']]);
            
            // 重新查询
            $stmt->execute([$testDate]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>更新后的值 - Sales: {$updatedRecord['sales']}, C_Beverage: {$updatedRecord['c_beverage']}, C_Kitchen: {$updatedRecord['c_kitchen']}</p>\n";
            
            if ($updatedRecord['sales'] == $newSales) {
                echo "<p style='color: green;'><strong>✓ 更新正常 - 值被正确替换</strong></p>\n";
            } else {
                echo "<p style='color: red;'><strong>✗ 更新异常 - 值: {$updatedRecord['sales']} (预期: {$newSales})</strong></p>\n";
            }
            
            // 恢复原值
            $restoreSql = "UPDATE {$table} SET sales = ?, c_beverage = ?, c_kitchen = ? WHERE id = ?";
            $restoreStmt = $pdo->prepare($restoreSql);
            $restoreStmt->execute([
                $testRecord['sales'],
                $testRecord['c_beverage'],
                $testRecord['c_kitchen'],
                $testRecord['id']
            ]);
            echo "<p>已恢复原值</p>\n";
            
        } else {
            echo "<p>没有找到测试记录 (日期: {$testDate})，跳过测试</p>\n";
        }
        
        echo "<hr>\n\n";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>错误: " . $e->getMessage() . "</p>";
}
?>

