<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "无效的邮箱地址"]);
    exit;
}

// ✅ 安全检查：
// 1. 用户必须存在
// 2. 用户必须已激活账户（is_first_login = 0）
//    → 如果 is_first_login = 1（被删除后重新添加），必须走"临时密码"登录流程
//    → 不能用 forgot password 绕过
$dbHost = 'localhost'; $dbName = 'u690174784_kunzz';
$dbUser = 'u690174784_kunzz'; $dbPass = 'Kunzz1688';
$dbConn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if (!$dbConn->connect_error) {
    $chk = $dbConn->prepare("SELECT id FROM users WHERE email = ? AND is_first_login = 0 LIMIT 1");
    $chk->bind_param("s", $email);
    $chk->execute();
    $activated = $chk->get_result()->num_rows > 0;
    $chk->close();
    $dbConn->close();

    if (!$activated) {
        // 统一返回成功，不泄露用户是否存在或是否已激活
        echo json_encode(["success" => true, "message" => "验证码已发送"]);
        exit;
    }
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
