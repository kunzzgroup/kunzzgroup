<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
// 包含全局防 XSS 脚本 (包括安全响应头设置与输入过滤)
require_once __DIR__ . '/xss_protect.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/fragment_helpers.php';

$loginPage = app_url('frontend/login.html');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 超时时间（秒） - 增加到 1 小时以避免频繁退出
define('SESSION_TIMEOUT', 3600);

// 如果 session 存在，检查是否过期
if (isset($_SESSION['user_id'])) {

    // 如果超过 1 分钟没活动，并且没有记住我
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) &&
        (!isset($_COOKIE['remember_token']) || $_COOKIE['remember_token'] !== '1')
    ) {
        // 清除 session
        session_unset();
        session_destroy();

        // 清除 cookie（可选）
        setcookie('user_id', '', time() - 60, "/");
        setcookie('username', '', time() - 60, "/");
        setcookie('position', '', time() - 60, "/");
        setcookie('account_type', '', time() - 60, "/");
        setcookie('branch', '', time() - 60, "/");
        setcookie('remember_token', '', time() - 60, "/");

        // 跳转登录页
        kunzz_send_fragment_unauthorized();
    }

    // 更新活动时间戳
    $_SESSION['last_activity'] = time();

} elseif (
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    // 记住我逻辑（恢复 session）
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['account_type'] = isset($_COOKIE['account_type']) ? $_COOKIE['account_type'] : null;
    $_SESSION['branch'] = isset($_COOKIE['branch']) ? strtoupper($_COOKIE['branch']) : (isset($_COOKIE['mobile_branch']) ? strtoupper($_COOKIE['mobile_branch']) : null);
    $_SESSION['last_activity'] = time();
} else {
    // 没有 session，也没有有效 cookie
    kunzz_send_fragment_unauthorized();
}

// 设置用户信息变量
$username = $_SESSION['username'];
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';
$account_type = (!empty($_SESSION['account_type'])) ? $_SESSION['account_type'] : 'user';
$avatarLetter = strtoupper($username[0]);
?>
