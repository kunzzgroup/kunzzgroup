<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/aboutpage4upload_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$language = aboutpage4upload_getLanguage();
$language = ($language === 'en') ? 'en' : 'zh';
$isEnglish = ($language === 'en');

$success = null;
$error = null;
$configFile = aboutpage4upload_resolveConfigFile($language);
$items = aboutpage4upload_loadItems($configFile);

$returnTo = 'v2';
$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
$uploadActionUrl = $backendWebBase . '/aboutpage4upload.php?lang=' . urlencode($language);

include __DIR__ . '/partials/aboutpage4upload_content.php';
