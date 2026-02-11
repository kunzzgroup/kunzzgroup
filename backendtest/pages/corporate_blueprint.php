<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');

// 加载JSON数据 - 文件在backend目录中
$jsonFile = dirname(dirname(__DIR__)) . '/backend/corporate_strategy.json';
$strategyData = null;

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $strategyData = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $strategyData = null;
    }
}

require __DIR__ . '/templates/corporate_blueprint.php';
?>
