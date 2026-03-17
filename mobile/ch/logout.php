<?php
/**
 * Mobile 退出登录脚本
 */
session_start();
session_unset();
session_destroy();

// 清除所有相关的 Cookie
$expire = time() - 3600;
setcookie('user_id', '', $expire, "/");
setcookie('username', '', $expire, "/");
setcookie('position', '', $expire, "/");
setcookie('account_type', '', $expire, "/");
setcookie('remember_token', '', $expire, "/");

// 跳转到登录页面
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'stocklistj1.php';
header("Location: login.html?redirect=" . urlencode($redirect));
exit();
?>
