<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('brand');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/corporate_blueprint_edit_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$returnTo = 'v2';
$success = isset($_GET['success']) ? (string)$_GET['success'] : '';
$error = '';

extract(corporate_blueprint_edit_prepareViewData($returnTo));

include __DIR__ . '/partials/corporate_blueprint_edit_content.php';
