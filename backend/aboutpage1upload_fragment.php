<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/aboutpage1upload_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$success = null;
$error = null;
$config = aboutpage1upload_loadConfig();
$returnTo = 'v2';
$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
$uploadActionUrl = $backendWebBase . '/aboutpage1upload.php';

include __DIR__ . '/partials/aboutpage1upload_content.php';
