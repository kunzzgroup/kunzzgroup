<?php
// SMTP 测试 - 测试完后删除此文件
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/backend/mailer_config.php';
require_once VENDOR_AUTOLOAD;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 改成您要测试的收件邮箱
$testTo = 'kunzzit01@gmail.com';

echo "<pre>";
try {
    $mail = new PHPMailer(true);
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = 'echo';
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($testTo);
    $mail->isHTML(false);
    $mail->Subject = 'SMTP 测试';
    $mail->Body    = '如果收到此邮件，说明 SMTP 正常！';

    $mail->send();
    echo "</pre><br><b style='color:green'>✅ 发送成功！请检查收件箱和垃圾邮件。</b>";

} catch (\Exception $e) {
    echo "</pre><br><b style='color:red'>❌ 错误：" . htmlspecialchars($mail->ErrorInfo) . "</b>";
}
?>
