<?php
// 防止浏览器/代理缓存，确保修改能立刻生效
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';

// define('SESSION_TIMEOUT_DISHWARE', 60); // Not needed if session_check handles it

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟库存管理 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../modules/css/dishware_stock.css">

    <?php include __DIR__ . '/templates/dishware_stock.php'; ?>
    <script src="js/dishware_stock.js"></script>
</head>
    
</html>
