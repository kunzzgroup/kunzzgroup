<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('brand');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/corporate_blueprint_edit_logic.php';

$returnTo = corporate_blueprint_edit_getReturnTo();
$success = '';
$error = '';

corporate_blueprint_edit_handlePost($success, $error);

if (isset($_GET['success']) && $_GET['success'] !== '') {
    $success = (string)$_GET['success'];
}

extract(corporate_blueprint_edit_prepareViewData($returnTo));
$backendWebBase = corporate_blueprint_edit_getBackendWebBase();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/corporate_blueprint_edit.css?v=<?php echo time(); ?>">
    <title>企业蓝图管理 - KUNZZ HOLDINGS</title>
</head>
<body class="has-sidebar">
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/corporate_blueprint_edit_content.php'; ?>
    <script src="js/corporate_blueprint_edit.js?v=<?php echo time(); ?>"></script>
</body>
</html>
