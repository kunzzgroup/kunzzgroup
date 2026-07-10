<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/joinpage3upload_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$language = joinpage3upload_getLanguage();
$isEnglish = ($language === 'en');
$returnTo = 'v2';
$pageUrl = joinpage3upload_getPageUrl($returnTo);
$uploadActionUrl = joinpage3upload_getUploadActionUrl($returnTo);
$backendWebBase = joinpage3upload_getBackendWebBase();

$success = null;
$error = null;
$editJob = null;

try {
    $pdo = get_pdo_connection();
    $jobs = joinpage3upload_loadJobs($pdo, $language);
} catch (PDOException $e) {
    $jobs = [];
    $error = $isEnglish ? 'Failed to read job data: ' . $e->getMessage() : '读取职位数据失败：' . $e->getMessage();
}

if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

if (isset($_GET['edit']) && !empty($jobs)) {
    $editJob = joinpage3upload_findEditJob($jobs, $_GET['edit'], $language);
}

include __DIR__ . '/partials/joinpage3upload_content.php';
