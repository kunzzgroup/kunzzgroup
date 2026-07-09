<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/joinpage2upload_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

date_default_timezone_set('Asia/Kuala_Lumpur');

$success = null;
$error = null;
$config = joinpage2upload_loadConfig();
$returnTo = 'v2';
$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
$uploadActionUrl = $backendWebBase . '/joinpage2upload.php';

include __DIR__ . '/partials/joinpage2upload_content.php';
