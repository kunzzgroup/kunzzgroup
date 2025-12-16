<?php
header("Content-Type: application/json");
session_start();

// 1. 数据库连接配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

// 2. 建立数据库连接
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "数据库连接失败: " . $conn->connect_error]);
    exit;
}

// 获取 JSON 数据
$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$newPassword = $data["new_password"] ?? "";

// 检查字段
if (!$email || !$newPassword) {
    echo json_encode(["success" => false, "message" => "邮箱或新密码缺失"]);
    exit;
}

// 验证 session 中验证码
if (
    !isset($_SESSION["verification_code"]) ||
    !isset($_SESSION["verification_email"]) ||
    !isset($_SESSION["code_expire_time"]) ||
    $_SESSION["verification_email"] !== $email
) {
    echo json_encode(["success" => false, "message" => "请先完成验证码验证"]);
    exit;
}

// 检查验证码是否过期
if (time() > $_SESSION["code_expire_time"]) {
    echo json_encode(["success" => false, "message" => "验证码已过期"]);
    exit;
}

// 加密密码
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// 更新数据库 - 同时更新密码和首次登录状态
$stmt = $conn->prepare("UPDATE users SET password = ?, is_first_login = 0 WHERE email = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "密码更新成功"]);

    // 可选：清除验证码 session
    unset($_SESSION["verification_code"]);
    unset($_SESSION["verification_email"]);
    unset($_SESSION["code_expire_time"]);
} else {
    echo json_encode(["success" => false, "message" => "密码更新失败"]);
}

$stmt->close();
$conn->close();
?>
