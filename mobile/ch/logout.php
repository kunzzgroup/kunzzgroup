<?php
/**
 * Mobile 退出登录（优化版）
 */
session_start();

// 清除所有 Session
session_unset();
session_destroy();

// 彻底清除所有相关的 Cookie
$p = "/";
$t = time() - 3600;
setcookie("mobile_user_id", "", $t, $p);
setcookie("mobile_username", "", $t, $p);
setcookie("mobile_position", "", $t, $p);
setcookie("mobile_account_type", "", $t, $p);
setcookie("mobile_nickname", "", $t, $p);
setcookie("mobile_username_cn", "", $t, $p);
setcookie("mobile_remember_token", "", $t, $p);

// 跳转回登录页
header("Location: login.html");
exit();
?>