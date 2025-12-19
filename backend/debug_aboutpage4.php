<?php
// 调试页面 - 检查 aboutpage4upload.php 的输出问题
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>调试 aboutpage4upload.php 输出问题</h1>";

// 模拟 aboutpage4upload.php 的输出缓冲设置
if (!ob_get_level()) {
    ob_start();
    echo "<p>输出缓冲已启动</p>";
}

// 检查 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>需要登录</p>";
    echo "<p><a href='../login.html'>前往登录</a></p>";
    exit;
}

echo "<p style='color: green;'>✓ Session 正常</p>";

// 测试包含文件
$mediaConfigPath = __DIR__ . '/../media_config.php';
if (file_exists($mediaConfigPath)) {
    include_once $mediaConfigPath;
    echo "<p style='color: green;'>✓ media_config.php 包含成功</p>";
}

// 测试输出缓冲
echo "<h2>输出缓冲测试</h2>";
$bufferLevel = ob_get_level();
$bufferLength = ob_get_length();
echo "<p>当前缓冲级别: $bufferLevel</p>";
echo "<p>当前缓冲长度: $bufferLength 字节</p>";

// 测试实际页面
echo "<h2>测试实际页面</h2>";
echo "<p>尝试直接包含 aboutpage4upload.php 的内容...</p>";
echo "<div style='border: 2px solid #ccc; padding: 10px; margin: 10px 0;'>";

// 检查文件是否存在
if (file_exists(__DIR__ . '/aboutpage4upload.php')) {
    echo "<p style='color: green;'>✓ aboutpage4upload.php 文件存在</p>";
    
    // 读取文件内容检查
    $content = file_get_contents(__DIR__ . '/aboutpage4upload.php');
    $contentLength = strlen($content);
    echo "<p>文件大小: $contentLength 字节</p>";
    
    // 检查 PHP 语法（不使用 shell_exec，因为可能被禁用）
    // 尝试包含文件来检查语法错误
    ob_start();
    $syntaxError = false;
    try {
        // 使用 token_get_all 来检查基本语法
        $tokens = @token_get_all($content);
        if ($tokens === false) {
            echo "<p style='color: orange;'>⚠ 无法进行语法检查（token_get_all 失败）</p>";
        } else {
            echo "<p style='color: green;'>✓ 基本语法检查通过（使用 token_get_all）</p>";
        }
    } catch (ParseError $e) {
        $syntaxError = true;
        echo "<p style='color: red;'>✗ PHP 语法错误:</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    } catch (Error $e) {
        // 忽略其他错误，因为这只是检查语法
    }
    ob_end_clean();
    
    // 检查文件末尾
    $lastLines = array_slice(explode("\n", $content), -5);
    echo "<p><strong>文件最后 5 行:</strong></p>";
    echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
    
    // 检查是否有 ob_end_flush
    if (strpos($content, 'ob_end_flush') !== false) {
        echo "<p style='color: green;'>✓ 文件包含 ob_end_flush()</p>";
    } else {
        echo "<p style='color: red;'>✗ 文件不包含 ob_end_flush() - 这可能是问题所在！</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ aboutpage4upload.php 文件不存在</p>";
}

echo "</div>";

// 刷新输出缓冲
echo "<h2>输出缓冲处理</h2>";
if (ob_get_level()) {
    $finalLength = ob_get_length();
    echo "<p>最终缓冲长度: $finalLength 字节</p>";
    echo "<p style='color: green;'>正在刷新输出缓冲...</p>";
    ob_end_flush();
    echo "<p style='color: green;'>✓ 输出缓冲已刷新</p>";
} else {
    echo "<p>没有活动的输出缓冲</p>";
}

echo "<hr>";
echo "<h2>建议</h2>";
echo "<ol>";
echo "<li>如果看到 '文件不包含 ob_end_flush()'，说明修复可能没有正确应用</li>";
echo "<li>检查浏览器开发者工具（F12）的 Network 标签，查看页面响应</li>";
echo "<li>查看响应头，确认 Content-Length 是否正确</li>";
echo "<li>检查是否有任何 JavaScript 错误阻止页面显示</li>";
echo "</ol>";

echo "<p><a href='aboutpage4upload.php'>现在尝试访问 aboutpage4upload.php</a></p>";
?>

