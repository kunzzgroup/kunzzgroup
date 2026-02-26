<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "无效的邮箱地址"]);
    exit;
}

// ✅ 健壮性检查：仅在 status 字段存在时进行安全校验
// 防止用户由于未运行 SQL 迁移而导致脚本报错（点不到验证码）
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "数据库连接失败"]);
    exit;
}

// 检查 users 表是否已有 status 字段
$hasStatus = false;
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($res && $res->num_rows > 0) {
    $hasStatus = true;
}

$query = $hasStatus 
    ? "SELECT id FROM users WHERE email = ? AND status = 'active' LIMIT 1"
    : "SELECT id FROM users WHERE email = ? LIMIT 1";

$checkStmt = $conn->prepare($query);
if ($checkStmt) {
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        // 用户非 active 或不存在 — 返回错误提示（用户要求明确提示）
        $checkStmt->close();
        $conn->close();
        echo json_encode(["success" => false, "message" => "该用户邮件并不在数据库，无法发送验证码"]);
        exit;
    }
    $checkStmt->close();
}
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
