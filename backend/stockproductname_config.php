<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/permission_guard.php';
requireStockView('product');
require_once __DIR__ . '/session_check.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

echo json_encode([
    'success' => true,
    'data' => [
        'pageTitle' => '库存货品管理后台 - Excel模式',
    ],
], JSON_UNESCAPED_UNICODE);
