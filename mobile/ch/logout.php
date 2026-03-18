<?php
/**
 * Mobile 退出登录（优化版）
 */
session_start();

// 清除所有 Session
session_unset();
session_destroy();

// 彻底清除所有相关的 Cookie
$options = [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || 
               isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https',
    'httponly' => true,
    'samesite' => 'Lax'
];

setcookie("mobile_user_id", "", $options);

// mobile_remember_token 可能是非 httponly，单独清除
$options['httponly'] = false;
setcookie("mobile_remember_token", "", $options);

// 跳转回登录页
header("Location: login.html");
exit();
?>