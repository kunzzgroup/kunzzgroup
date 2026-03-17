<?php
/**
 * Mobile 统一身份验证脚本
 * 参考 backend/session_check.php 实现
 */

// 1. 设置安全响应头（防止缓存）
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}

// 2. 引入防 XSS 脚本 (包含安全头设置与输入过滤)
require_once __DIR__ . '/../../backend/xss_protect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 超时时间（秒）- 与后端保持一致 (1分钟)
// 注意：如果勾选了“记住我”，则不受此 1 分钟限制
define('SESSION_TIMEOUT', 60);

$current_page = basename($_SERVER['PHP_SELF']);
$login_url = 'login.html?redirect=' . urlencode($current_page);

// 3. 验证逻辑
if (isset($_SESSION['user_id'])) {
    
    // 检查是否过期 (1分钟无活动)
    // 如果勾选了“记住我” (cookies 存在且有效)，则不执行自动登出
    $is_remembered = (isset($_COOKIE['remember_token']) && $_COOKIE['remember_token'] === '1');
    
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) && 
        !$is_remembered) {
        
        // 过期且没记住我：清除 session 后跳转
        session_unset();
        session_destroy();
        
        // 清除 cookie (双重保证)
        setcookie('user_id', '', time() - 3600, "/");
        setcookie('username', '', time() - 3600, "/");
        setcookie('position', '', time() - 3600, "/");
        setcookie('account_type', '', time() - 3600, "/");
        setcookie('remember_token', '', time() - 3600, "/");

        header("Location: " . $login_url);
        exit();
    }

    // 更新活动时间戳
    $_SESSION['last_activity'] = time();

} elseif (
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    // ✅ 4. “记住我” 自动恢复 session
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['account_type'] = isset($_COOKIE['account_type']) ? $_COOKIE['account_type'] : null;
    $_SESSION['last_activity'] = time();
} else {
    // 5. 无有效 session/cookie 凭证，重定向至登录页
    header("Location: " . $login_url);
    exit();
}

// 供页面使用的用户信息变量
$currentUsername = '';
if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
    
    try {
        $conn_auth = new mysqli($host, $dbuser, $dbpass, $dbname);
        if (!$conn_auth->connect_error) {
            $stmt_auth = $conn_auth->prepare("SELECT nickname, username_cn, username FROM users WHERE id = ?");
            $stmt_auth->bind_param("i", $_SESSION['user_id']);
            $stmt_auth->execute();
            $user_res = $stmt_auth->get_result();
            if ($userRow = $user_res->fetch_assoc()) {
                $nickname = trim((string)($userRow['nickname'] ?? ''));
                $usernameCn = trim((string)($userRow['username_cn'] ?? ''));
                $username = trim((string)($userRow['username'] ?? ''));
                $currentUsername = $nickname !== '' ? $nickname : ($usernameCn !== '' ? $usernameCn : $username);
            }
            $stmt_auth->close();
            $conn_auth->close();
        }
    } catch (Exception $e) {
        $currentUsername = $_SESSION['username'] ?? '';
    }
}
?>
