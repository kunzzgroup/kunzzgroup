<?php
// 检查输出问题
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>输出测试</h1>";

// 测试1: 基本输出
echo "<p>测试1: 基本输出正常</p>";

// 测试2: PHP变量输出
$testVar = "测试变量";
echo "<p>测试2: 变量输出 - $testVar</p>";

// 测试3: HTML结构
echo "<div class='year-management'>";
echo "<div class='year-tabs'>";
echo "<span>暂无记录</span>";
echo "</div>";
echo "<div class='year-actions' style='display: flex; gap: 10px;'>";
echo "<button type='button' class='btn btn-add' style='background: #28a745; color: white; padding: 10px 20px;'>+ 新增记录</button>";
echo "</div>";
echo "</div>";

// 测试4: 检查是否有输出缓冲
if (ob_get_level() > 0) {
    echo "<p>测试4: 输出缓冲级别: " . ob_get_level() . "</p>";
    echo "<p>测试4: 输出缓冲长度: " . ob_get_length() . " 字节</p>";
} else {
    echo "<p>测试4: 没有输出缓冲</p>";
}

// 测试5: 检查PHP错误
$errors = error_get_last();
if ($errors) {
    echo "<p style='color: red;'>测试5: 发现PHP错误:</p>";
    echo "<pre>" . print_r($errors, true) . "</pre>";
} else {
    echo "<p style='color: green;'>测试5: 没有PHP错误</p>";
}

echo "<hr>";
echo "<p><strong>如果上面的测试都正常显示，说明输出没有问题。</strong></p>";
echo "<p><strong>如果 year-actions div 没有显示，可能是 aboutpage4upload.php 中的PHP代码有问题。</strong></p>";
?>

