<?php
require_once __DIR__ . '/../../config.php';
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
require_once __DIR__ . '/../../backend/auth.php';
ob_start();
// ★ Edge 修复：PHPSESSID 也需要 SameSite，否则 Edge 会静默拦截
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================

// 创建连接
$conn = get_mysqli_connection();
// ================= 登录处理 =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = find_user_by_login_identifier($conn, $_POST['username'] ?? '');

    if ($user) {
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (verify_secure_password($password, $user['password'])) {
            // ✅ 保存 session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['account_type'] = $user['account_type'];
            $_SESSION['branch'] = strtoupper($user['branch'] ?? ''); // Added branch (Force Uppercase)
            $_SESSION['nickname'] = $user['nickname'] ?? '';
            $_SESSION['username_cn'] = $user['username_cn'] ?? '';
            $_SESSION['last_activity'] = time();

            // 判断用户是否勾选"记住我"
            if ($remember) {
                // ✅ 勾选了"记住我"，设置 cookie（30天）
                $expire = time() + (86400 * 30);
                $opts = [
                    'expires'  => $expire,
                    'path'     => '/',
                    'httponly' => false,
                    'samesite' => 'Lax',
                ];

                setcookie('mobile_user_id', $user['id'], $opts);
                setcookie('mobile_username', $user['username'], $opts);
                setcookie('mobile_position', $user['position'], $opts);
                setcookie('mobile_account_type', $user['account_type'], $opts);
                setcookie('mobile_branch', strtoupper($user['branch'] ?? ''), $opts);
                setcookie('mobile_nickname', $user['nickname'] ?? '', $opts);
                setcookie('mobile_username_cn', $user['username_cn'] ?? '', $opts);
                setcookie('mobile_remember_token', '1', $opts);
            } else {
                // ❌ 没勾选记住我，清除残留 cookie
                $expire_past = time() - 3600;
                $opts = [
                    'expires'  => $expire_past,
                    'path'     => '/',
                    'samesite' => 'Lax',
                ];
                setcookie('mobile_user_id', '', $opts);
                setcookie('mobile_username', '', $opts);
                setcookie('mobile_position', '', $opts);
                setcookie('mobile_account_type', '', $opts);
                setcookie('mobile_branch', '', $opts);
                setcookie('mobile_nickname', '', $opts);
                setcookie('mobile_username_cn', '', $opts);
                setcookie('mobile_remember_token', '', $opts);
            }

            session_write_close(); // 确保强制写入

            // 检查是否为首次登录
            if ($user['is_first_login'] == 1) {
                header("Location: reset_password.html");
                exit();
            }

            $redirect = $_GET['redirect'] ?? 'stocklistj1.php';
            header("Location: " . $redirect);
            exit();
        } else {
            echo "<script>alert('密码错误'); window.location.href='login.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('用户不存在'); window.location.href='login.html';</script>";
        exit();
    }
} else {
    // 如果不是 POST 请求且没自动登录成功，就去登录页
    header("Location: login.html");
    exit();
}
?>
