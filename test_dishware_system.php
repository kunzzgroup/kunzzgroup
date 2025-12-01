<?php
// 测试碗碟库存系统
// 这个文件用于测试系统是否正常工作

// 数据库配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

echo "<h1>碗碟库存系统测试</h1>";

// 测试数据库连接
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ 数据库连接成功</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ 数据库连接失败: " . $e->getMessage() . "</p>";
    exit;
}

// 测试表是否存在
$tables = ['dishware_info', 'dishware_stock'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ 表 $table 存在</p>";
        } else {
            echo "<p style='color: red;'>❌ 表 $table 不存在，请先执行 create_dishware_tables.sql</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ 检查表 $table 时出错: " . $e->getMessage() . "</p>";
    }
}

// 测试上传目录
$upload_dir = 'uploads/dishware/';
if (is_dir($upload_dir)) {
    if (is_writable($upload_dir)) {
        echo "<p style='color: green;'>✅ 上传目录存在且可写</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ 上传目录存在但不可写，请设置权限</p>";
    }
} else {
    if (mkdir($upload_dir, 0755, true)) {
        echo "<p style='color: green;'>✅ 上传目录创建成功</p>";
    } else {
        echo "<p style='color: red;'>❌ 无法创建上传目录</p>";
    }
}

// 测试API文件
$api_files = ['dishware_api.php', 'dishware_upload.php', 'dishware_stock.php', 'dishware_index.php'];
foreach ($api_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ 文件 $file 存在</p>";
    } else {
        echo "<p style='color: red;'>❌ 文件 $file 不存在</p>";
    }
}

// 测试PHP扩展
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ PHP扩展 $ext 已加载</p>";
    } else {
        echo "<p style='color: red;'>❌ PHP扩展 $ext 未加载</p>";
    }
}

echo "<hr>";
echo "<h2>系统状态</h2>";

// 检查数据
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dishware_info");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>碗碟信息记录数: " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dishware_stock");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>库存记录数: " . $result['count'] . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ 查询数据时出错: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>快速链接</h2>";
echo "<p><a href='dishware_index.php' style='color: blue;'>🏠 系统首页</a></p>";
echo "<p><a href='dishware_upload.php' style='color: blue;'>📤 上传碗碟信息</a></p>";
echo "<p><a href='dishware_stock.php' style='color: blue;'>📊 库存管理</a></p>";

echo "<hr>";
echo "<p><small>测试完成时间: " . date('Y-m-d H:i:s') . "</small></p>";
?>
