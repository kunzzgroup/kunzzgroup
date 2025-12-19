<?php
// 简单测试页面 - 检查 aboutpage4upload.php 是否能正常加载
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>测试 aboutpage4upload.php</h1>";

// 测试 1: Session
echo "<h2>测试 1: Session</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✓ Session 存在: user_id = " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Session 不存在 - 需要登录</p>";
    echo "<p><a href='../login.html'>前往登录</a></p>";
    exit;
}

// 测试 2: 文件包含
echo "<h2>测试 2: 文件包含</h2>";
$mediaConfigPath = __DIR__ . '/../media_config.php';
if (file_exists($mediaConfigPath)) {
    echo "<p style='color: green;'>✓ media_config.php 存在</p>";
    try {
        include_once $mediaConfigPath;
        echo "<p style='color: green;'>✓ media_config.php 包含成功</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ 包含失败: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ media_config.php 不存在</p>";
}

$sidebarPath = __DIR__ . '/sidebar.php';
if (file_exists($sidebarPath)) {
    echo "<p style='color: green;'>✓ sidebar.php 存在</p>";
} else {
    echo "<p style='color: red;'>✗ sidebar.php 不存在</p>";
}

// 测试 3: 配置文件路径
echo "<h2>测试 3: 配置文件路径</h2>";
$configFile = __DIR__ . '/../timeline_config.json';
echo "<p>配置文件路径: $configFile</p>";
if (file_exists($configFile)) {
    echo "<p style='color: green;'>✓ timeline_config.json 存在</p>";
    if (is_readable($configFile)) {
        echo "<p style='color: green;'>✓ 可读</p>";
    } else {
        echo "<p style='color: red;'>✗ 不可读</p>";
    }
    if (is_writable($configFile)) {
        echo "<p style='color: green;'>✓ 可写</p>";
    } else {
        echo "<p style='color: orange;'>⚠ 不可写（首次创建时可能需要）</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ timeline_config.json 不存在（首次使用时会创建）</p>";
    $configDir = dirname($configFile);
    if (is_writable($configDir)) {
        echo "<p style='color: green;'>✓ 配置目录可写，可以创建文件</p>";
    } else {
        echo "<p style='color: red;'>✗ 配置目录不可写: $configDir</p>";
    }
}

// 测试 4: 上传目录
echo "<h2>测试 4: 上传目录</h2>";
$uploadDir = __DIR__ . '/../images/images/';
echo "<p>上传目录路径: $uploadDir</p>";
if (file_exists($uploadDir)) {
    echo "<p style='color: green;'>✓ 上传目录存在</p>";
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✓ 可写</p>";
    } else {
        echo "<p style='color: red;'>✗ 不可写</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ 上传目录不存在，尝试创建...</p>";
    if (mkdir($uploadDir, 0777, true)) {
        echo "<p style='color: green;'>✓ 创建成功</p>";
    } else {
        echo "<p style='color: red;'>✗ 创建失败</p>";
    }
}

// 测试 5: 尝试包含 sidebar
echo "<h2>测试 5: 尝试包含 sidebar.php</h2>";
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<p><strong>注意：</strong>如果下面出现侧边栏，说明包含成功。如果出现错误，会显示错误信息。</p>";
try {
    ob_start();
    $sidebarPath = __DIR__ . '/sidebar.php';
    if (file_exists($sidebarPath)) {
        include $sidebarPath;
        $sidebarOutput = ob_get_clean();
        if (!empty($sidebarOutput)) {
            echo "<p style='color: green;'>✓ sidebar.php 包含成功，输出了 " . strlen($sidebarOutput) . " 字节</p>";
            // 不直接输出 sidebar，因为可能会影响页面布局
            echo "<p style='color: blue;'>（侧边栏内容已加载，但未在此页面显示）</p>";
        } else {
            echo "<p style='color: orange;'>⚠ sidebar.php 包含成功，但没有输出内容</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ sidebar.php 不存在</p>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>✗ 包含 sidebar.php 时出错: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    ob_end_clean();
    echo "<p style='color: red;'>✗ 包含 sidebar.php 时发生致命错误: " . $e->getMessage() . "</p>";
}

// 测试 6: PHP 错误检查
echo "<h2>测试 6: PHP 错误检查</h2>";
$errorLog = ini_get('error_log');
echo "<p>错误日志路径: " . ($errorLog ?: '默认位置') . "</p>";
echo "<p>错误显示: " . (ini_get('display_errors') ? '开启' : '关闭') . "</p>";
echo "<p>错误报告级别: " . error_reporting() . "</p>";

// 测试 7: 尝试访问实际页面
echo "<h2>测试 7: 访问实际页面</h2>";
echo "<p><a href='aboutpage4upload.php' target='_blank'>点击这里访问 aboutpage4upload.php</a></p>";
echo "<p><strong>如果页面是空白的：</strong></p>";
echo "<ul>";
echo "<li>检查浏览器开发者工具（F12）的 Console 和 Network 标签</li>";
echo "<li>查看服务器错误日志</li>";
echo "<li>检查是否有 JavaScript 错误</li>";
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<h2>总结</h2>";
echo "<p>如果以上所有测试都通过，但 aboutpage4upload.php 仍然无法显示，可能的原因：</p>";
echo "<ol>";
echo "<li><strong>JavaScript 错误：</strong>打开浏览器开发者工具（F12）检查 Console</li>";
echo "<li><strong>CSS 问题：</strong>页面加载了但样式丢失</li>";
echo "<li><strong>输出缓冲问题：</strong>某些内容在 header 发送后输出</li>";
echo "<li><strong>重定向循环：</strong>检查是否有无限重定向</li>";
echo "</ol>";
?>

