<?php
session_start();

// 清除所有 Session
session_unset();
session_destroy();

// 彻底清除所有相关的 Cookie（使用 SameSite=Lax 与 login.php 保持一致）
$opts = [
    'expires'  => time() - 3600,
    'path'     => '/',
    'samesite' => 'Lax',
];
setcookie("mobile_user_id", "", $opts);
setcookie("mobile_username", "", $opts);
setcookie("mobile_position", "", $opts);
setcookie("mobile_account_type", "", $opts);
setcookie("mobile_branch", "", $opts);
setcookie("mobile_nickname", "", $opts);
setcookie("mobile_username_cn", "", $opts);
setcookie("mobile_remember_token", "", $opts);

// 跳转回登录页
header("Location: login.html");
exit();
?>