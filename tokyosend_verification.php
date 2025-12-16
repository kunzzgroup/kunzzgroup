<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "无效的邮箱地址"]);
    exit;
}

// 只允许 Gmail
if (!preg_match('/@gmail\.com$/', $email)) {
    echo json_encode(["success" => false, "message" => "目前仅支持 Gmail"]);
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
$subject = "TOKYO JAPANESE CUISINE - 邮箱验证码";
$message = "您的验证码是：$code\n\n有效时间为5分钟。";
$headers = "From: no-reply@kunzzgroup.com\r\nContent-Type: text/plain; charset=UTF-8";

if (mail($to, $subject, $message, $headers)) {
    echo json_encode(["success" => true, "message" => "验证码已发送"]);
} else {
    echo json_encode(["success" => false, "message" => "邮件发送失败，请稍后重试"]);
}
?>
