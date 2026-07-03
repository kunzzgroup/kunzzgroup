<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'dishware');
require_once __DIR__ . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟库存管理 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/smartSearch.css">
    <link rel="stylesheet" href="css/dishware_stock.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    $backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
    ?>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/dishware_stock_content.php'; ?>
    <script src="js/toast.js"></script>
    <script src="js/smartSearch.js?v=<?php echo time(); ?>"></script>
    <script src="js/dishware_stock.js?v=<?php echo time(); ?>"></script>
</body>

</html>
