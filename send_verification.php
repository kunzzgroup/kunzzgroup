<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "无效的邮箱地址"]);
    exit;
}

// 数据库连接配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "数据库连接失败"]);
    exit;
}

// 检查邮箱是否存在，且账户已激活（不是首次登录状态）
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND is_first_login = 0 LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userExists = $result->num_rows > 0;
$stmt->close();
$conn->close();

// 安全性：无论用户是否存在，都返回统一消息，防止扫描邮箱
if (!$userExists) {
    echo json_encode(["success" => true, "message" => "如账户存在，验证邮件将发送至您的邮箱"]);
    exit;
}

// 生成6位验证码
$code = rand(100000, 999999);

// 保存验证码到 session，设置5分钟过期
$_SESSION["verification_code"] = $code;
$_SESSION["verification_email"] = $email;
$_SESSION["code_expire_time"] = time() + 300;  // 5分钟

// 发送邮件
$to = $email;
$subject = "KUNZZ HOLDINGS - 邮箱验证码";
$message = "您的验证码是：$code\n\n有效时间为5分钟。";
$headers = "From: no-reply@kunzzgroup.com\r\nContent-Type: text/plain; charset=UTF-8";

if (mail($to, $subject, $message, $headers)) {
    echo json_encode(["success" => true, "message" => "验证码已发送"]);
} else {
    echo json_encode(["success" => false, "message" => "邮件发送失败，请稍后重试"]);
}
?>
