<?php
require_once __DIR__ . '/../../backend/xss_protect.php';
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === 自动登录检查 ===
// 优先用 session
if (isset($_SESSION['user_id'])) {
    $redirect = $_GET['redirect'] ?? 'stocklistj1.php';
    header("Location: " . $redirect);
    exit();
}
// 如果 session 没有，再用 cookie
elseif (isset($_COOKIE['user_login'])) {
    $_SESSION['user_id'] = $_COOKIE['user_login'];
    // 注意：这里建议在正式页面（auth_check.php）恢复更完整的 session 资料
    $redirect = $_GET['redirect'] ?? 'stocklistj1.php';
    header("Location: " . $redirect);
    exit();
}
// ===================

// 数据库连接信息
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

// 创建连接
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// ================= 登录处理 =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (verify_secure_password($password, $user['password'])) {
            // ✅ 保存 session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['account_type'] = $user['account_type'];
            $_SESSION['last_activity'] = time();

            // 判断用户是否勾选“记住我”
            $cookie_options = [
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || 
                           isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https',
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            if ($remember) {
                // 设置长期 cookie（30天）
                $cookie_options['expires'] = time() + 86400 * 30;
                setcookie("user_login", $user['id'], $cookie_options);
                
                // 为了兼容 login.html 的 JavaScript 检测自动跳转，
                // 我们还是需要一个脚本可读的 remember_token
                $cookie_options['httponly'] = false;
                setcookie("remember_token", "1", $cookie_options);
            } else {
                // 会话 cookie（关闭浏览器就过期）
                $cookie_options['expires'] = 0;
                setcookie("user_login", $user['id'], $cookie_options);
                
                // 清除残留的记住我标记
                setcookie("remember_token", "", time() - 3600, "/");
            }

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
