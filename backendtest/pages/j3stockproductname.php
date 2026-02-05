<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user permission - check registration code directly
$canApprove = false;
if (isset($_SESSION['user_id'])) {
    // Connect to database to check user's registration code
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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

// Include template
include '../templates/j3stockproductname_template.php';
?>
