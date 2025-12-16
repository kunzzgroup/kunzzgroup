<?php
// 测试碗碟破损记录系统
require_once 'dishware_api.php';

echo "<h2>碗碟破损记录系统测试</h2>";

// 测试数据库连接
try {
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ 数据库连接成功</p>";
    
    // 检查表是否存在
    $tables = ['dishware_info', 'dishware_stock', 'dishware_break_records'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ 表 $table 存在</p>";
        } else {
            echo "<p style='color: red;'>✗ 表 $table 不存在</p>";
        }
    }
    
    // 测试API端点
    echo "<h3>API 端点测试</h3>";
    
    // 测试获取库存列表
    $_GET['action'] = 'stock';
    ob_start();
    handleGet();
    $output = ob_get_clean();
    $result = json_decode($output, true);
    
    if ($result && $result['success']) {
        echo "<p style='color: green;'>✓ 获取库存列表 API 正常</p>";
    } else {
        echo "<p style='color: red;'>✗ 获取库存列表 API 异常</p>";
    }
    
    // 测试获取分类
    $_GET['action'] = 'categories';
    ob_start();
    handleGet();
    $output = ob_get_clean();
    $result = json_decode($output, true);
    
    if ($result && $result['success']) {
        echo "<p style='color: green;'>✓ 获取分类 API 正常</p>";
    } else {
        echo "<p style='color: red;'>✗ 获取分类 API 异常</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ 数据库连接失败: " . $e->getMessage() . "</p>";
}

echo "<h3>使用说明</h3>";
echo "<ol>";
echo "<li>首先运行 <code>create_dishware_break_records_table.sql</code> 创建破损记录表</li>";
echo "<li>访问 <code>dishware_stock.php</code> 查看新的页面切换功能</li>";
echo "<li>在库存管理页面可以添加和管理碗碟信息</li>";
echo "<li>切换到 J1/J2/J3 页面可以记录各店铺的破损数量</li>";
echo "<li>破损记录会自动扣减对应店铺的库存数量</li>";
echo "</ol>";

echo "<h3>功能特性</h3>";
echo "<ul>";
echo "<li>✓ 页面切换功能（类似 stockeditall.php）</li>";
echo "<li>✓ 库存管理页面</li>";
echo "<li>✓ J1/J2/J3 破损记录页面</li>";
echo "<li>✓ 自动库存扣减</li>";
echo "<li>✓ 破损记录增删改查</li>";
echo "<li>✓ 实时数据更新</li>";
echo "</ul>";
?>
