<?php
require_once __DIR__ . '/permission_guard.php';
requireStockView('records');
require_once __DIR__ . '/session_check.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$system = isset($_GET['system']) ? $_GET['system'] : 'central';
$system_names = [
    'central' => '中央',
    'j1' => 'J1',
    'j2' => 'J2',
    'j3' => 'J3',
];
$display_name = isset($system_names[$system]) ? $system_names[$system] : '中央';

include __DIR__ . '/partials/stockeditall_content.php';
