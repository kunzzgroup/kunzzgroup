<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>菜单成本管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/menucost.css">
    <script src="../js/menucost.js"></script>
</head>
<?php include __DIR__ . '/templates/menucost.php'; ?>
</html>

