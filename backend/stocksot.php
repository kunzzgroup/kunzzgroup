<?php
require_once __DIR__ . '/permission_guard.php';
requireStockView('sot');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';

$system = isset($_GET['system']) ? $_GET['system'] : 'central';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>货品异常 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/stocksot.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/stocksot_content.php'; ?>
    <script src="js/stocksot.js?v=<?php echo time(); ?>"></script>
</body>
</html>
