<?php
// 检查餐厅功能设置

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>餐厅排班系统诊断</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;max-width:1200px;margin:0 auto;}table{border-collapse:collapse;width:100%;}th,td{padding:8px;text-align:left;border:1px solid #ddd;}th{background:#f0f0f0;}</style>";
echo "</head><body>";

echo "<h1>餐厅排班系统诊断</h1>";
echo "<hr>";

// 0. 先检查表是否存在
echo "<h2>0. 检查数据表是否存在</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'schedule_employees'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<div style='background: #fee; padding: 15px; border: 2px solid #f00; border-radius: 5px;'>";
        echo "<h3 style='color: #c00;'>❌ schedule_employees 表不存在！</h3>";
        echo "<p>请先创建员工排班表。执行以下SQL：</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
        echo "CREATE TABLE schedule_employees (\n";
        echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        echo "    name VARCHAR(100) NOT NULL,\n";
        echo "    phone VARCHAR(20) NOT NULL,\n";
        echo "    position VARCHAR(100) NOT NULL,\n";
        echo "    work_area VARCHAR(50) NOT NULL,\n";
        echo "    restaurant VARCHAR(10) DEFAULT 'J1',\n";
        echo "    is_active TINYINT(1) DEFAULT 1,\n";
        echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        echo ");\n";
        echo "</pre>";
        echo "</div>";
        echo "</body></html>";
        exit;
    } else {
        echo "<p style='color: green;'>✅ schedule_employees 表存在</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>错误: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 1. 检查表结构
echo "<h2>1. 检查 schedule_employees 表结构</h2>";
try {
    $stmt = $pdo->query("DESCRIBE schedule_employees");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasRestaurantColumn = false;
    echo "<table>";
    echo "<tr><th>字段</th><th>类型</th><th>默认值</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'restaurant') {
            $hasRestaurantColumn = true;
        }
    }
    echo "</table>";
    
    if ($hasRestaurantColumn) {
        echo "<p style='color: green; font-weight: bold;'>✅ restaurant 字段已存在</p>";
    } else {
        echo "<div style='background: #ffe; padding: 15px; border: 2px solid #fc0; border-radius: 5px;'>";
        echo "<p style='color: #c60; font-weight: bold;'>❌ restaurant 字段不存在！</p>";
        echo "<p>请执行以下SQL添加字段：</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
        echo "ALTER TABLE schedule_employees \n";
        echo "ADD COLUMN restaurant VARCHAR(10) DEFAULT 'J1' AFTER work_area;\n\n";
        echo "CREATE INDEX idx_restaurant ON schedule_employees(restaurant);";
        echo "</pre>";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div style='background: #fee; padding: 15px; border: 2px solid #f00; border-radius: 5px;'>";
    echo "<p style='color: red; font-weight: bold;'>❌ 查询失败！</p>";
    echo "<p>错误信息: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";

// 2. 查看所有员工及其餐厅分配
echo "<h2>2. 当前员工列表</h2>";
try {
    $stmt = $pdo->query("SELECT id, name, phone, position, work_area, restaurant, is_active 
                         FROM schedule_employees 
                         ORDER BY restaurant, work_area, name");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($employees) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>餐厅</th><th>部门</th><th>姓名</th><th>电话</th><th>职位</th><th>状态</th></tr>";
        foreach ($employees as $emp) {
            $restaurant = htmlspecialchars($emp['restaurant'] ?? '未设置');
            $status = $emp['is_active'] ? '✅' : '❌';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($emp['id']) . "</td>";
            echo "<td><strong>{$restaurant}</strong></td>";
            echo "<td>" . htmlspecialchars($emp['work_area']) . "</td>";
            echo "<td>" . htmlspecialchars($emp['name']) . "</td>";
            echo "<td>" . htmlspecialchars($emp['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($emp['position']) . "</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: #999;'>暂无员工数据。请在排班管理页面添加员工。</p>";
    }
} catch (PDOException $e) {
    echo "<div style='background: #fee; padding: 15px; border: 2px solid #f00; border-radius: 5px;'>";
    echo "<p style='color: red; font-weight: bold;'>❌ 查询员工失败！</p>";
    echo "<p>错误信息: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";

// 3. 统计每间餐厅的员工数
echo "<h2>3. 各餐厅员工统计</h2>";
try {
    $stmt = $pdo->query("SELECT 
                            COALESCE(restaurant, '未设置') as restaurant,
                            work_area,
                            COUNT(*) as count
                         FROM schedule_employees
                         WHERE is_active = 1
                         GROUP BY restaurant, work_area
                         ORDER BY restaurant, work_area");
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($stats) > 0) {
        echo "<table>";
        echo "<tr><th>餐厅</th><th>部门</th><th>员工数</th></tr>";
        foreach ($stats as $stat) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($stat['restaurant']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($stat['work_area']) . "</td>";
            echo "<td>" . htmlspecialchars($stat['count']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: #999;'>暂无统计数据</p>";
    }
} catch (PDOException $e) {
    echo "<div style='background: #fee; padding: 15px; border: 2px solid #f00; border-radius: 5px;'>";
    echo "<p style='color: red; font-weight: bold;'>❌ 统计查询失败！</p>";
    echo "<p>错误信息: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";

// 4. 提供快速修复方案
echo "<h2>4. 快速修复方案</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px;'>";
echo "<h3>如果所有员工都显示为J1：</h3>";
echo "<p>这是正常的，因为默认值是J1。你可以：</p>";
echo "<ol>";
echo "<li><strong>方案1：手动分配现有员工</strong>";
echo "<pre style='background: #f0f0f0; padding: 10px;'>";
echo "-- 将ID为1,2,3的员工分配到J2\n";
echo "UPDATE schedule_employees SET restaurant = 'J2' WHERE id IN (1, 2, 3);\n\n";
echo "-- 将ID为4,5,6的员工分配到J3\n";
echo "UPDATE schedule_employees SET restaurant = 'J3' WHERE id IN (4, 5, 6);";
echo "</pre>";
echo "</li>";
echo "<li><strong>方案2：为J2、J3重新添加员工</strong><br>";
echo "在页面上选择J2餐厅，然后通过"员工管理"添加新员工，这些员工会自动属于J2。";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><a href='schedule_manager.php' style='color: #f99e00; font-weight: bold;'>← 返回排班管理</a></p>";
echo "</body></html>";
?>
