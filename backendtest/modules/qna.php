<?php
// 引入核心初始化文件
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>问卷回答 - KUNZZ HOLDINGS</title>
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib/dist/pdf-lib.min.js"></script>
    <script src="get_fontkit.php"></script>
    <link rel="stylesheet" href="../css/qna.css">
    <?php include __DIR__ . '/templates/qna.php'; ?>
    <script src="../modules/js/qna.js"></script>

</head>
</html>

