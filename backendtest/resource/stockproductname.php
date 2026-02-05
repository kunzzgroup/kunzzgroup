<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // 重定向到登录页面
    exit;
}

// 获取用户权限 - 直接检查注册码
$canApprove = false;
$currentApplicant = '';
if (isset($_SESSION['user_id'])) {
    // 这里需要连接数据库检查用户的注册码
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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

// Include the template
include '../templates/stockproductname_template.php';
?>
