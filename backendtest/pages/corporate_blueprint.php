<?php
session_start();
ob_start();

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');

// 包含会话验证
// Note: The original file had session_start at the top, but session_check.php also does checks.
// However, the original file did NOT include session_check.php. It just did session_start().
// I should probably preserve the original logic or upgrade it.
// The task is refactoring, so I should try to preserve behavior.
// Original: session_start(); ... load JSON ... HTML.
// Also checked logic for $jsonFile.

// 加载JSON数据 - 文件在backend目录中
$jsonFile = __DIR__ . '/corporate_strategy.json';
$strategyData = null;

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $strategyData = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $strategyData = null;
    }
}

include '../templates/corporate_blueprint_template.php';
?>
