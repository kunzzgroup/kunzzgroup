<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'stock_inventory');
require_once __DIR__ . '/session_check.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

include __DIR__ . '/partials/supply_content.php';
