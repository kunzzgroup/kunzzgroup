<?php
// 防止浏览器/代理缓存，确保修改能立刻生效
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// 在输出任何内容前完成 session 与认证检查，避免未勾选「记住我」时白屏
session_start();
define('SESSION_TIMEOUT_DISHWARE', 60);

if (isset($_SESSION['user_id'])) {
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_DISHWARE) &&
        (!isset($_COOKIE['remember_token']) || $_COOKIE['remember_token'] !== '1')
    ) {
        session_unset();
        session_destroy();
        setcookie('user_id', '', time() - 60, "/");
        setcookie('username', '', time() - 60, "/");
        setcookie('position', '', time() - 60, "/");
        setcookie('remember_token', '', time() - 60, "/");
        header("Location: ../frontend/login.html");
        exit();
    }
    $_SESSION['last_activity'] = time();
} elseif (
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['account_type'] = isset($_COOKIE['account_type']) ? $_COOKIE['account_type'] : null;
    $_SESSION['last_activity'] = time();
} else {
    header("Location: ../frontend/login.html");
    exit();
}

include '../templates/dishware_stock_template.php';
?>
