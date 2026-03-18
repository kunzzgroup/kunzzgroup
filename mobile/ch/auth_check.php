<?php
/**
 * Mobile 身份验证检查（镜像 Frontend 成功模式）
 * 逻辑：优先 Session -> 次选 Cookie 直接恢复（一致性高） -> 最后跳转 Login
 */

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. 优先从 Session 获取
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $current_user_id = $_SESSION['user_id'];
} 
// 2. 如果 Session 没有，且有自动登录标记，模仿 backend/session_check.php 直接从 Cookie 恢复
elseif (
    isset($_COOKIE['mobile_user_id']) && 
    !empty($_COOKIE['mobile_user_id']) &&
    isset($_COOKIE['mobile_remember_token']) && 
    $_COOKIE['mobile_remember_token'] === '1'
) {
    $_SESSION['user_id'] = $_COOKIE['mobile_user_id'];
    $_SESSION['username'] = $_COOKIE['mobile_username'] ?? '';
    $_SESSION['position'] = $_COOKIE['mobile_position'] ?? '';
    $_SESSION['account_type'] = $_COOKIE['mobile_account_type'] ?? '';
    $_SESSION['branch'] = $_COOKIE['mobile_branch'] ?? ''; // Added branch restoration
    $_SESSION['nickname'] = $_COOKIE['mobile_nickname'] ?? '';
    $_SESSION['username_cn'] = $_COOKIE['mobile_username_cn'] ?? '';
    $_SESSION['last_activity'] = time();
    $current_user_id = $_SESSION['user_id'];
} 
// 3. 都没有，跳转登录
else {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'login.html') {
        header("Location: login.html?redirect=" . urlencode($current_page));
        exit();
    }
}

// 统一提供当前用户名变量
$currentUsername = '';
if (isset($_SESSION['user_id'])) {
    $nickname = trim((string)($_SESSION['nickname'] ?? ''));
    $usernameCn = trim((string)($_SESSION['username_cn'] ?? ''));
    $username = trim((string)($_SESSION['username'] ?? ''));
    $currentUsername = ($nickname !== '') ? $nickname : (($usernameCn !== '') ? $usernameCn : $username);
}
?>