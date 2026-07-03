<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'stock_inventory');
require_once __DIR__ . '/session_check.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$system = isset($_GET['system']) ? $_GET['system'] : 'central';
require_once __DIR__ . '/partials/stockminimum_context.php';

include __DIR__ . '/partials/stockminimum_content.php';
