<?php
// 诊断脚本：检查 aboutpage4upload.php 的问题
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>诊断报告：aboutpage4upload.php</h2>";

// 1. 检查文件是否存在
echo "<h3>1. 文件检查</h3>";
$files = [
    'aboutpage4upload.php' => __DIR__ . '/aboutpage4upload.php',
    'sidebar.php' => __DIR__ . '/sidebar.php',
    'media_config.php' => __DIR__ . '/../media_config.php',
    'timeline_config.json' => __DIR__ . '/../timeline_config.json',
    'timeline_config_en.json' => __DIR__ . '/../timeline_config_en.json',
    'images/images/' => __DIR__ . '/../images/images/',
];

foreach ($files as $name => $path) {
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    $writable = $exists ? is_writable($path) : false;
    
    echo "<p><strong>$name:</strong> ";
    echo $exists ? "✓ 存在" : "✗ 不存在";
    if ($exists) {
        echo " | " . ($readable ? "✓ 可读" : "✗ 不可读");
        if (is_dir($path)) {
            echo " | " . ($writable ? "✓ 可写" : "✗ 不可写");
        }
    }
    echo " | 路径: $path</p>";
}

// 2. 检查 session
echo "<h3>2. Session 检查</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p>✓ Session 存在: user_id = " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p>✗ Session 不存在 - 这会导致重定向到登录页</p>";
}

// 3. 检查目录权限
echo "<h3>3. 目录权限检查</h3>";
$dirs = [
    '上传目录 (images/images/)' => __DIR__ . '/../images/images/',
    '配置文件目录' => __DIR__ . '/../',
];

foreach ($dirs as $name => $dir) {
    if (!file_exists($dir)) {
        echo "<p><strong>$name:</strong> ✗ 不存在";
        if (mkdir($dir, 0777, true)) {
            echo " → 已创建";
        } else {
            echo " → 创建失败";
        }
        echo "</p>";
    } else {
        $writable = is_writable($dir);
        $readable = is_readable($dir);
        echo "<p><strong>$name:</strong> ";
        echo $readable ? "✓ 可读" : "✗ 不可读";
        echo " | " . ($writable ? "✓ 可写" : "✗ 不可写");
        echo " | 路径: $dir</p>";
    }
}

// 4. 检查 include 路径
echo "<h3>4. Include 路径检查</h3>";
$includePaths = [
    'sidebar.php' => __DIR__ . '/sidebar.php',
    '../media_config.php' => __DIR__ . '/../media_config.php',
];

foreach ($includePaths as $include => $path) {
    if (file_exists($path)) {
        echo "<p>✓ $include 可以正常包含 (路径: $path)</p>";
    } else {
        echo "<p>✗ $include 不存在 (路径: $path)</p>";
    }
}

// 5. 测试配置文件读取
echo "<h3>5. 配置文件读取测试</h3>";
$configFile = __DIR__ . '/../timeline_config.json';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    $json = json_decode($content, true);
    if ($json !== null) {
        echo "<p>✓ timeline_config.json 可以正常读取和解析</p>";
    } else {
        echo "<p>✗ timeline_config.json 存在但无法解析 JSON</p>";
    }
} else {
    echo "<p>⚠ timeline_config.json 不存在（这是正常的，会在首次使用时创建）</p>";
}

// 6. 检查 PHP 错误
echo "<h3>6. PHP 配置</h3>";
echo "<p>PHP 版本: " . phpversion() . "</p>";
echo "<p>错误显示: " . (ini_get('display_errors') ? '开启' : '关闭') . "</p>";
echo "<p>错误报告级别: " . error_reporting() . "</p>";

// 7. 测试实际包含
echo "<h3>7. 实际包含测试</h3>";
try {
    if (file_exists(__DIR__ . '/../media_config.php')) {
        include_once __DIR__ . '/../media_config.php';
        echo "<p>✓ media_config.php 包含成功</p>";
    } else {
        echo "<p>✗ media_config.php 不存在</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ 包含 media_config.php 时出错: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>建议：</strong></p>";
echo "<ul>";
if (!isset($_SESSION['user_id'])) {
    echo "<li>需要先登录才能访问 aboutpage4upload.php</li>";
}
if (!file_exists(__DIR__ . '/../images/images/')) {
    echo "<li>上传目录不存在，需要创建或检查权限</li>";
}
echo "<li>检查服务器错误日志以获取更多信息</li>";
echo "</ul>";
?>

