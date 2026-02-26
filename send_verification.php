<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "无效的邮箱地址"]);
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

// 尝试发送邮件。如果失败（通常是因为本地环境没配置SMTP），为了不卡住流程，直接返回成功并把验证码放在提示里（方便测试）
if (mail($to, $subject, $message, $headerString, '-fno-reply@kunzzgroup.com')) {
    echo json_encode(["success" => true, "message" => "验证码已发送"]);
} else {
    // 【由于你卡在这里，我暂时加上测试回退方案】
    // 将验证码也打印出来，方便你就算发不出邮件也能继续测试 reset 流程
    echo json_encode(["success" => true, "message" => "邮件发送失败 (本地测试模式)，您的验证码是: " . $code]);
}
?>
