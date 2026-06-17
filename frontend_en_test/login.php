<?php
require_once __DIR__ . '/../config.php';

$loginPage = app_url('frontend_en_test/login.php');
$dashboardPage = app_url('backend/dashboard');
$resetPasswordPage = app_url('frontend_en_test/reset_password');

ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../backend/auth.php';

    try {
        $conn = get_mysqli_connection();
    } catch (RuntimeException $e) {
        redirect_with_login_alert('无法连接数据库，请确认 XAMPP MySQL 已启动并已导入用户数据', $loginPage);
    }

    process_web_login(
        $conn,
        $_POST['username'] ?? '',
        $_POST['password'] ?? '',
        isset($_POST['remember']),
        $loginPage,
        $dashboardPage,
        $resetPasswordPage
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/login.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'templates/login-content.php'; ?>

<script src="../app.js"></script>
<script src="js/login.js"></script>

</body>
</html>
<?php ob_end_flush(); ?>
