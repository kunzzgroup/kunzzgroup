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
$subject = "KUNZZ HOLDINGS - 邮箱验证码";
$message = "您的验证码是：$code\n\n有效时间为5分钟。";

$headers = array(
    'MIME-Version' => '1.0',
    'Content-type' => 'text/plain; charset=utf-8',
    'From' => 'no-reply@kunzzgroup.com',
    'Reply-To' => 'support@kunzzgroup.com',
    'Return-Path' => 'no-reply@kunzzgroup.com'
);

$headerString = '';
foreach ($headers as $key => $value) {
    if ($key !== 'Content-type') {
        $headerString .= $key . ': ' . $value . "\r\n";
    } else {
        $headerString .= $key . ': ' . $value . "\r\n";
    }
}

if (mail($to, $subject, $message, $headerString, '-fno-reply@kunzzgroup.com')) {
    echo json_encode(["success" => true, "message" => "验证码已发送"]);
} else {
    // 【测试回退方案】本地发不出邮件时，直接将验证码显示在页面上供测试
    echo json_encode(["success" => true, "message" => "邮件发送失败。内部测试验证码: " . $code]);
}

?>
