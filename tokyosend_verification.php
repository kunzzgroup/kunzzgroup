<?php
require_once __DIR__ . '/backend/xss_protect.php';
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/backend/mailer_config.php';
require_once VENDOR_AUTOLOAD;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// 2. 获取请求数据
$data = get_safe_json_input();
$email = trim($data["email"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "无效的邮箱地址"]);
    exit;
}

// 检查邮箱是否在数据库中
$host = 'localhost';
$dbname = 'u857194726_tokyo'; 
$dbuser = 'u857194726_tokyo';   
$dbpass = 'Kholdings1688@';       

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "数据库连接失败: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM tokyo_users WHERE email = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "该邮箱不存在，无法发送验证码"]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
}
$conn->close();

// 生成6位验证码
$code = rand(100000, 999999);

// 保存验证码到 session，设置5分钟过期
$_SESSION["verification_code"]  = $code;
$_SESSION["verification_email"] = $email;
$_SESSION["code_expire_time"]   = time() + 300;

// 发送邮件（PHPMailer SMTP）
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'TOKYO JAPANESE CUISINE - 邮箱验证码';
    $mail->Body    = "
        <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:30px;background:#fff;border-radius:10px;border:1px solid #eee;'>
            <h2 style='color:#f97316;text-align:center;'>邮箱验证码</h2>
            <p>请使用以下验证码完成操作：</p>
            <div style='font-size:36px;font-weight:bold;letter-spacing:8px;text-align:center;color:#f97316;background:#fff8f0;padding:20px;border-radius:8px;margin:20px 0;'>{$code}</div>
            <p style='color:#999;font-size:13px;'>验证码有效期为 <strong>5 分钟</strong>，请勿泄露给他人。</p>
            <p style='color:#ccc;font-size:12px;text-align:center;margin-top:30px;'>此邮件由系统自动发送，请勿回复。</p>
        </div>
    ";
    $mail->AltBody = "您的验证码是：{$code}，有效时间为5分钟。";

    $mail->send();
    echo json_encode(["success" => true, "message" => "验证码已发送到邮箱，请查收"]);

} catch (\Exception $e) {
    error_log('[tokyosend_verification] SMTP Error: ' . $e->getMessage());
    echo json_encode(["success" => false, "message" => "邮件发送失败，请稍后重试"]);
}
?>

