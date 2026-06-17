<?php
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
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

// 跳转回登录页，带上来源页以便重登后自动跳回
$redirect_page = '';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $referer_path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    $referer_file = basename($referer_path);
    // 只允许白名单页面，防止开放重定向
    $allowed = ['stocklistj1.php', 'stocklistj2.php', 'stocklistj3.php'];
    if (in_array($referer_file, $allowed, true)) {
        $redirect_page = '?redirect=' . urlencode($referer_file);
    }
}
header("Location: login.html" . $redirect_page);
exit();
?>