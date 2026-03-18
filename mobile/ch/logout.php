<?php
/**
 * Mobile 退出登录（优化版）
 */
session_name('MOBILE_SESSION');
session_start();

// 清除所有 Session
session_unset();
session_destroy();

// 彻底清除所有相关的 Cookie
$p = "/";
$d = "kunzzgroup.com";
$t = time() - 3600;
setcookie("mobile_user_id", "", $t, $p, $d);
setcookie("mobile_username", "", $t, $p, $d);
setcookie("mobile_position", "", $t, $p, $d);
setcookie("mobile_account_type", "", $t, $p, $d);
setcookie("mobile_nickname", "", $t, $p, $d);
setcookie("mobile_username_cn", "", $t, $p, $d);
setcookie("mobile_remember_token", "", $t, $p, $d);

// 跳转回登录页
header("Location: login.html");
exit();
?>