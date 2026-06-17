<?php
require_once __DIR__ . '/../../config.php';

$loginPage = app_url('frontend_en_test/frontend/login.php');
$dashboardPage = app_url('backend/dashboard');
$resetPasswordPage = app_url('frontend_en_test/frontend/reset_password');

ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../backend/auth.php';

    try {
        $conn = get_mysqli_connection();
    } catch (RuntimeException $e) {
        header('Location: login.php?error=db_connection');
        exit();
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
// Render Page (GET)
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="。。/images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入 - KUNZZ HOLDINGS</title>
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
