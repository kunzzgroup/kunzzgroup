<?php
session_start();
header("Content-Type: application/json");

// 数据库配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["canApprove" => false]);
    exit;
}

$canApprove = false;

if (isset($_SESSION['user_id'])) {
    $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userCode = $stmt->fetchColumn();

    if ($userCode && in_array($userCode, $allowedCodes)) {
        $canApprove = true;
    }
}

echo json_encode(["canApprove" => $canApprove]);
?>