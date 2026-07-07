<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/homepage1upload_logic.php';

$success = null;
$error = null;
homepage1upload_handlePost($success, $error);
$config = homepage1upload_loadConfig();
$returnTo = homepage1upload_getReturnTo();
$uploadActionUrl = 'homepage1upload.php';

$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/homepage1upload.css?v=<?php echo time(); ?>">
    <title>首页媒体管理 - KUNZZ HOLDINGS</title>
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/homepage1upload_content.php'; ?>
    <script src="js/toast.js?v=<?php echo time(); ?>"></script>
    <script src="js/homepage1upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
