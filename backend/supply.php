<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'stock_inventory');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';

$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>供应商价格对比</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../animation.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="css/supply.css?v=<?php echo time(); ?>" />
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/supply_content.php'; ?>
    <script src="js/supply.js?v=<?php echo time(); ?>"></script>
</body>
</html>
