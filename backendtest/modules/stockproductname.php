<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';

// 获取用户权限 - 直接检查注册码
$canApprove = false;
$currentApplicant = '';
if (isset($_SESSION['user_id'])) {
    
    try {
        // Global $pdo is available from init.php -> db.php
        
        $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP', 'HR2025','AZGQOY','IT7890'];
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT registration_code, nickname, username_cn, username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $userCode = $userRow['registration_code'] ?? null;
        
        // 申请人：优先昵称，其次中文名，最后英文名
        $nickname = trim((string)($userRow['nickname'] ?? ''));
        $usernameCn = trim((string)($userRow['username_cn'] ?? ''));
        $username = trim((string)($userRow['username'] ?? ''));
        $currentApplicant = $nickname !== '' ? $nickname : ($usernameCn !== '' ? $usernameCn : $username);
        
        $canApprove = $userCode && in_array($userCode, $allowedCodes);
    } catch (PDOException $e) {
        $canApprove = false;
        $currentApplicant = '';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存货品管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/stockproductname.css">
    <?php include __DIR__ . '/templates/stockproductname.php'; ?>
    <script src="../modules/js/stockproductname.js"></script>

</head>

</html>
