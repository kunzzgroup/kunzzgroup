<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/aboutpage1upload_logic.php';

$success = null;
$error = null;
aboutpage1upload_handlePost($success, $error);
$config = aboutpage1upload_loadConfig();
$returnTo = aboutpage1upload_getReturnTo();
$uploadActionUrl = 'aboutpage1upload.php';

$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/aboutpage1upload.css?v=<?php echo time(); ?>">
    <title>关于我们页面管理 - KUNZZ HOLDINGS</title>
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/aboutpage1upload_content.php'; ?>
    <script src="js/toast.js?v=<?php echo time(); ?>"></script>
    <script src="js/aboutpage1upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
