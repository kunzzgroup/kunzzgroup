<?php
// 包含会话验证
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>货品异常 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/stocksot.css">
    <link rel="stylesheet" href="../css/stockout.css">
    <?php include __DIR__ . '/templates/stockout.php'; ?>
    <script src="../modules/js/stockout.js"></script>
</head>

</body>
</html>


