<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/joinpage2upload_logic.php';

$success = null;
$error = null;
joinpage2upload_handlePost($success, $error);
$config = joinpage2upload_loadConfig();
$returnTo = joinpage2upload_getReturnTo();
$uploadActionUrl = 'joinpage2upload.php';

$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/joinpage2upload.css?v=<?php echo time(); ?>">
    <title>我们的足迹照片管理 - KUNZZ HOLDINGS</title>
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/joinpage2upload_content.php'; ?>
    <script src="js/joinpage2upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
