<?php
require_once __DIR__ . '/../config.php';

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

$loginPage = app_url('frontend_en/login.html');
$dashboardPage = app_url('backend/dashboard');
$resetPasswordPage = app_url('frontend_en/reset_password');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $loginPage);
    exit();
}

require_once __DIR__ . '/../backend/auth.php';
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $conn = get_mysqli_connection();
} catch (RuntimeException $e) {
    error_log('Login connection failed: ' . $e->getMessage());
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
