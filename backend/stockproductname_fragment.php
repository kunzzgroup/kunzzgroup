<?php
require_once __DIR__ . '/permission_guard.php';
requireStockView('product');
require_once __DIR__ . '/session_check.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$system = isset($_GET['system']) ? $_GET['system'] : 'central';
require_once __DIR__ . '/partials/stockproductname_context.php';

include __DIR__ . '/partials/stockproductname_content.php';
