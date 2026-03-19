<?php
/**
 * 诊断工具：检查 Edge 的 Cookie 和 Session 状态
 * 部署后访问：/mobile/ch/debug_session.php
 * ⚠️ 用完请删除！
 */
header('Content-Type: application/json; charset=utf-8');

// 先启动 session（与 auth_check.php 一致的参数）
if (session_status() === PHP_SESSION_NONE) {
    $cookie_lifetime = 0;
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

$result = [
    'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'is_https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'session_id' => session_id(),
    'session_cookie_params' => session_get_cookie_params(),
    'php_version' => PHP_VERSION,

    'session_data' => [
        'user_id'   => $_SESSION['user_id'] ?? '❌ 不存在',
        'username'  => $_SESSION['username'] ?? '❌ 不存在',
        'branch'    => $_SESSION['branch'] ?? '❌ 不存在',
        'last_activity' => isset($_SESSION['last_activity'])
            ? date('Y-m-d H:i:s', $_SESSION['last_activity']) . ' (' . (time() - $_SESSION['last_activity']) . '秒前)'
            : '❌ 不存在',
    ],

    'cookies_received' => [
        'PHPSESSID'              => $_COOKIE['PHPSESSID'] ?? '❌ 不存在',
        'mobile_user_id'         => $_COOKIE['mobile_user_id'] ?? '❌ 不存在',
        'mobile_username'        => $_COOKIE['mobile_username'] ?? '❌ 不存在',
        'mobile_branch'          => $_COOKIE['mobile_branch'] ?? '❌ 不存在',
        'mobile_remember_token'  => $_COOKIE['mobile_remember_token'] ?? '❌ 不存在',
    ],

    'all_cookies_raw' => $_COOKIE,

    'diagnosis' => '',
];

// 诊断
if (empty($_COOKIE)) {
    $result['diagnosis'] = '🔴 Edge 没有发送任何 Cookie！可能原因：(1) 用户设置了关闭时清除 Cookie (2) 站点被 Edge 追踪防护拦截 (3) HTTPS 证书问题';
} elseif (!isset($_COOKIE['mobile_remember_token'])) {
    $result['diagnosis'] = '🟡 有 PHPSESSID 但没有 mobile_remember_token。用户可能没有勾选"记住我"，或者旧 Cookie 已过期。';
} elseif (!isset($_SESSION['user_id'])) {
    $result['diagnosis'] = '🟡 有 Cookie 但 Session 中没有 user_id。Cookie 恢复逻辑可能未触发。';
} else {
    $result['diagnosis'] = '🟢 一切正常：Session 和 Cookie 都在。';
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
