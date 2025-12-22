<?php
// 检查 cost 表结构，找出依赖 sales 字段的计算列
// 运行此脚本后，根据输出结果调整 remove_sales_from_cost_tables.sql

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Cost 表结构检查</h2>\n\n";
    
    $tables = ['j1cost', 'j2cost', 'j3cost'];
    
    foreach ($tables as $table) {
        echo "<h3>表: {$table}</h3>\n";
        
        // 获取表结构
        $stmt = $pdo->query("SHOW CREATE TABLE {$table}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $createTable = $result['Create Table'];
        
        // 查找所有字段定义
        $stmt = $pdo->query("DESCRIBE {$table}");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>完整的 CREATE TABLE 语句:</h4>\n";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($createTable) . "</pre>\n";
        
        echo "<h4>字段列表:</h4>\n";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>\n";
        echo "<tr><th>字段名</th><th>类型</th><th>Extra</th><th>是否依赖 sales</th><th>计算表达式</th></tr>\n";
        
        $columnsToDrop = [];
        
        foreach ($columns as $col) {
            $fieldName = $col['Field'];
            $extra = $col['Extra'];
            $type = $col['Type'];
            
            // 检查是否是计算列
            $isGenerated = strpos($extra, 'GENERATED') !== false || 
                          strpos($extra, 'VIRTUAL') !== false || 
                          strpos($extra, 'STORED') !== false;
            
            // 检查是否依赖 sales 字段
            $dependsOnSales = false;
            if ($isGenerated) {
                // 从 CREATE TABLE 语句中查找该字段的定义
                // 尝试多种匹配模式
                $patterns = [
                    "/`{$fieldName}`[^,)]+AS\s*\(([^)]+)\)/i",
                    "/`{$fieldName}`\s+[^,)]+GENERATED\s+ALWAYS\s+AS\s*\(([^)]+)\)/i",
                    "/`{$fieldName}`[^`]+\(([^)]+)\)/i"
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $createTable, $matches)) {
                        $expression = $matches[1];
                        // 检查表达式中是否包含 sales（可能是 `sales` 或 sales）
                        if (preg_match('/\b(sales|`sales`)\b/i', $expression)) {
                            $dependsOnSales = true;
                            $columnsToDrop[] = $fieldName;
                            break;
                        }
                    }
                }
                
                // 如果正则匹配失败，直接检查 CREATE TABLE 中该字段附近的内容
                if (!$dependsOnSales && $isGenerated) {
                    // 提取该字段的完整定义
                    if (preg_match("/`{$fieldName}`[^,`]+/i", $createTable, $fieldMatch)) {
                        $fieldDef = $fieldMatch[0];
                        if (stripos($fieldDef, 'sales') !== false) {
                            $dependsOnSales = true;
                            $columnsToDrop[] = $fieldName;
                        }
                    }
                }
            }
            
            $color = $dependsOnSales ? 'red' : ($isGenerated ? 'blue' : 'black');
            $dependsText = $dependsOnSales ? '是（需要删除）' : ($isGenerated ? '否' : 'N/A');
            
            // 提取计算表达式
            $expression = '';
            if ($isGenerated) {
                if (preg_match("/`{$fieldName}`[^,`]+AS\s*\(([^)]+)\)/i", $createTable, $exprMatches)) {
                    $expression = $exprMatches[1];
                } elseif (preg_match("/`{$fieldName}`[^,`]+\(([^)]+)\)/i", $createTable, $exprMatches)) {
                    $expression = $exprMatches[1];
                }
            }
            
            echo "<tr>";
            echo "<td><strong>{$fieldName}</strong></td>";
            echo "<td>{$type}</td>";
            echo "<td style='color: {$color};'>{$extra}</td>";
            echo "<td style='color: {$color};'>{$dependsText}</td>";
            echo "<td style='font-family: monospace; font-size: 12px;'>{$expression}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        if (!empty($columnsToDrop)) {
            echo "<h4 style='color: red;'>需要删除的列（依赖 sales 字段）:</h4>\n";
            echo "<ul>\n";
            foreach ($columnsToDrop as $col) {
                echo "<li><code>ALTER TABLE {$table} DROP COLUMN {$col};</code></li>\n";
            }
            echo "</ul>\n";
        } else {
            echo "<p style='color: green;'>✓ 没有发现依赖 sales 字段的计算列</p>\n";
        }
        
        echo "<hr>\n\n";
    }
    
    echo "<h3>建议的删除顺序</h3>\n";
    echo "<ol>\n";
    echo "<li>先删除所有依赖 sales 的计算列（gross_total, cost_percent 等）</li>\n";
    echo "<li>然后删除 sales 字段本身</li>\n";
    echo "<li>如果需要，可以重新创建不依赖 sales 的计算列（如 c_total = c_beverage + c_kitchen）</li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>错误: " . $e->getMessage() . "</p>\n";
}
?>

