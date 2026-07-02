<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/permission_guard.php';
requireStockView('sot');
require_once __DIR__ . '/session_check.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

echo json_encode([
    'success' => true,
    'data' => [
        'pageTitle' => '货品异常 - 库存管理系统',
    ],
], JSON_UNESCAPED_UNICODE);
