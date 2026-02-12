<?php
// 包含初始化文件
require_once dirname(__DIR__) . '/core/init.php';
// 包含会话验证
require_once CORE_PATH . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>J1库存汇总 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/j1stocklist.css">
    <script src="../modules/js/j1stocklist.js"></script>
    <?php include __DIR__ . '/templates/j1stocklist.php'; ?>



</head>

</html>