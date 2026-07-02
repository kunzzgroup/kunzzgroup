<?php
require_once __DIR__ . '/permission_guard.php';
requireStockView('sot');
require_once __DIR__ . '/session_check.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$system = isset($_GET['system']) ? $_GET['system'] : 'central';

include __DIR__ . '/partials/stocksot_content.php';
