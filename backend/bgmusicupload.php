<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/bgmusicupload_logic.php';

$success = null;
$error = null;
bgmusicupload_handlePost($success, $error);
$config = bgmusicupload_loadConfig();
$returnTo = bgmusicupload_getReturnTo();
$uploadActionUrl = 'bgmusicupload.php';

$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>背景音乐管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/bgmusicupload.css?v=<?php echo time(); ?>">
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/bgmusicupload_content.php'; ?>
    <script src="js/toast.js?v=<?php echo time(); ?>"></script>
    <script src="js/bgmusicupload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
