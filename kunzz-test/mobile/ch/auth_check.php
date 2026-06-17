<?php
/**
 * Mobile 身份验证检查（与 Backend session_check.php 保持一致）
 * 逻辑：
 * 1. 如果 Session 存在 → 检查是否超时（勾选了"记住我"则不超时）
 * 2. 如果 Session 不存在但有 Cookie → 从 Cookie 恢复 Session
 * 3. 都没有 → 跳转登录页
 */

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}

if (session_status() === PHP_SESSION_NONE) {
    // ★ Edge 修复：为 PHPSESSID 设置 SameSite=Lax
    // Edge 对缺少 SameSite 属性的 cookie 特别严格，会静默拦截
    $cookie_lifetime = 0; // 默认：浏览器关闭即失效
    // 如果有 "记住我" cookie，将 PHPSESSID 也延长为 30 天
    if (isset($_COOKIE['mobile_remember_token']) && $_COOKIE['mobile_remember_token'] === '1') {
        $cookie_lifetime = 86400 * 30;
    }
    session_set_cookie_params([
        'lifetime' => $cookie_lifetime,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// 超时时间（秒）— 与 backend 一致：1 小时无操作自动登出
define('MOBILE_SESSION_TIMEOUT', 3600);

// 1. 优先从 Session 获取
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {

    // 检查是否超时（如果勾选了"记住我"则跳过超时检查）
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > MOBILE_SESSION_TIMEOUT) &&
        (!isset($_COOKIE['mobile_remember_token']) || $_COOKIE['mobile_remember_token'] !== '1')
    ) {
        // 超时且没有记住我 → 清除 session 并跳转登录
        session_unset();
        session_destroy();

        $p = "/";
        $t = time() - 3600;
        setcookie('mobile_user_id', '', $t, $p);
        setcookie('mobile_username', '', $t, $p);
        setcookie('mobile_position', '', $t, $p);
        setcookie('mobile_account_type', '', $t, $p);
        setcookie('mobile_branch', '', $t, $p);
        setcookie('mobile_nickname', '', $t, $p);
        setcookie('mobile_username_cn', '', $t, $p);
        setcookie('mobile_remember_token', '', $t, $p);

        $current_page = basename($_SERVER['PHP_SELF']);
        header("Location: login.html?redirect=" . urlencode($current_page) . "&msg=session_expired");
        exit();
    }

    // 如果 Session 中缺少分支信息（可能是旧会话），尝试从 Cookie 恢复
    if (!isset($_SESSION['branch']) && isset($_COOKIE['mobile_branch'])) {
        $_SESSION['branch'] = strtoupper($_COOKIE['mobile_branch']);
    } elseif (isset($_SESSION['branch'])) {
        $_SESSION['branch'] = strtoupper($_SESSION['branch']);
    }

    // 更新活动时间戳
    $_SESSION['last_activity'] = time();

    $current_user_id = $_SESSION['user_id'];
}
// 2. 如果 Session 没有，且有"记住我"Cookie → 从 Cookie 恢复
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
    $_SESSION['branch'] = strtoupper($_COOKIE['mobile_branch'] ?? '');
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