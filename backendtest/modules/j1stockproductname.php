<?php
require_once dirname(__DIR__) . '/core/init.php';

// 包含会话验证
require_once CORE_PATH . '/session_check.php';

// 获取用户权限 - 直接检查注册码
$canApprove = false;
if (isset($_SESSION['user_id'])) {
    // 这里需要连接数据库检查用户的注册码
    // 使用全局 $pdo 对象
    
    try {
        $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP','IT7890'];
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userCode = $stmt->fetchColumn();
        
        $canApprove = $userCode && in_array($userCode, $allowedCodes);
    } catch (PDOException $e) {
        $canApprove = false;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存产品管理后台 - J1</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/j1stockproductname.css">
    <script src="../modules/js/j1stockproductname.js"></script>
    <?php include __DIR__ . '/templates/j1stockproductname.php'; ?>


</head>
 
</html>
