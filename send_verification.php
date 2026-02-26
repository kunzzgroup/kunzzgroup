<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "无效的邮箱地址"]);
    exit;
}

// ✅ 安全检查：仅允许 status = 'active' 的用户接收验证码
// 若用户不存在或已被删除/禁用，返回 success:true（防止邮箱探测攻击）
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "数据库连接失败"]);
    exit;
}

$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND status = 'active' LIMIT 1");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    // 用户不存在或已删除 — 静默返回成功，防止邮箱探测
    $checkStmt->close();
    $conn->close();
    echo json_encode(["success" => true, "message" => "验证码已发送"]);
    exit;
}

$checkStmt->close();
$conn->close();

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
