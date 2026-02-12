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
    <title>菜单成本数据管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menucostdata.css">
    <script src="js/menucostdata.js"></script>
</head>
<?php include __DIR__ . '/templates/menucostdata.php'; ?>
</html>

