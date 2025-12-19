<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("需要登录");
}

// 错误日志路径
$errorLogPath = '/home/u690174784/.logs/error_log_kunzzgroup_com';

echo "<h1>查看错误日志</h1>";
echo "<p>日志路径: <code>$errorLogPath</code></p>";

// 检查文件是否存在
if (file_exists($errorLogPath)) {
    echo "<p style='color: green;'>✓ 文件存在</p>";
    echo "<p>文件大小: " . filesize($errorLogPath) . " 字节</p>";
    echo "<p>最后修改时间: " . date('Y-m-d H:i:s', filemtime($errorLogPath)) . "</p>";
    
    // 检查是否可读
    if (is_readable($errorLogPath)) {
        echo "<p style='color: green;'>✓ 文件可读</p>";
        
        // 读取最后100行
        $lines = file($errorLogPath);
        $lastLines = array_slice($lines, -100);
        
        echo "<h2>最后 100 行日志（最新的在底部）</h2>";
        echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 5px; max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 12px;'>";
        echo "<pre style='margin: 0; white-space: pre-wrap; word-wrap: break-word;'>";
        echo htmlspecialchars(implode('', $lastLines));
        echo "</pre>";
        echo "</div>";
        
        // 搜索关于 aboutpage4upload.php 的日志
        echo "<h2>关于 aboutpage4upload.php 的日志</h2>";
        $aboutpage4Logs = array_filter($lines, function($line) {
            return stripos($line, 'aboutpage4') !== false || 
                   stripos($line, 'timeline_config') !== false ||
                   stripos($line, 'items') !== false ||
                   stripos($line, 'year') !== false;
        });
        
        if (!empty($aboutpage4Logs)) {
            echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px;'>";
            echo "<pre style='margin: 0; white-space: pre-wrap; word-wrap: break-word;'>";
            echo htmlspecialchars(implode('', array_slice($aboutpage4Logs, -50)));
            echo "</pre>";
            echo "</div>";
        } else {
            echo "<p style='color: orange;'>没有找到相关的日志</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ 文件不可读（权限问题）</p>";
    }
} else {
    echo "<p style='color: red;'>✗ 文件不存在</p>";
    echo "<p>可能的原因：</p>";
    echo "<ul>";
    echo "<li>日志文件路径不正确</li>";
    echo "<li>日志文件还没有创建（没有错误发生）</li>";
    echo "<li>日志文件在其他位置</li>";
    echo "</ul>";
    
    // 尝试查找其他可能的日志位置
    echo "<h2>尝试查找其他日志位置</h2>";
    $possiblePaths = [
        ini_get('error_log'),
        '/home/u690174784/.logs/error_log',
        '/home/u690174784/logs/error_log',
        __DIR__ . '/../error_log',
        sys_get_temp_dir() . '/php_errors.log',
    ];
    
    foreach ($possiblePaths as $path) {
        if ($path && file_exists($path)) {
            echo "<p style='color: green;'>找到日志文件: <code>$path</code></p>";
        }
    }
}

echo "<hr>";
echo "<p><a href='aboutpage4upload.php'>返回 aboutpage4upload.php</a></p>";
echo "<p><a href='debug_items.php'>查看调试信息</a></p>";
?>

