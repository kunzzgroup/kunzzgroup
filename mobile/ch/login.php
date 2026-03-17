<?php
require_once __DIR__ . '/../../backend/xss_protect.php';
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === 自动登录检查 ===
// 如果已经有 Session，或者有有效的“记住我” Cookie，直接跳转
$is_logged_in = isset($_SESSION['user_id']);
$is_remembered = (isset($_COOKIE['user_id']) && isset($_COOKIE['remember_token']) && $_COOKIE['remember_token'] === '1');

if ($is_logged_in || $is_remembered) {
    // 恢复 Session 如果是通过 Cookie 记录的
    if (!$is_logged_in && $is_remembered) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'] ?? '';
        $_SESSION['position'] = $_COOKIE['position'] ?? '';
        $_SESSION['account_type'] = $_COOKIE['account_type'] ?? '';
        $_SESSION['last_activity'] = time();
    }
    
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

// 获取提交数据
$email = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']); // true/false

// 查询用户
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (verify_secure_password($password, $user['password'])) {
        // ✅ 登录成功，设置 Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['position'] = $user['position'];
        $_SESSION['account_type'] = $user['account_type']; // ⭐ 添加这行 - 关键！
        $_SESSION['last_activity'] = time(); // ➤ 当前登录时间（用于 1 分钟自动登出）

        // 检查是否为首次登录
        if ($user['is_first_login'] == 1) {
            // 首次登录，跳转到密码重置页面
            header("Location: reset_password.html");
            exit();
        }

        if ($remember) {
            error_log("Setting remember cookies for user: " . $user['id']);
            // ✅ 勾选了"记住我"，设置 cookie（30天）
            $expire = time() + (86400 * 30);
            $options = [
                'expires' => $expire,
                'path' => '/',
                'domain' => '', // Default to current domain
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            setcookie('user_id', $user['id'], $options);
            setcookie('username', $user['username'], $options);
            setcookie('position', $user['position'] ?? '', $options);
            setcookie('account_type', $user['account_type'] ?? '', $options);
            setcookie('remember_token', '1', $options);
        } else {
            error_log("Clearing remember cookies for user: " . $user['id']);
            // ❌ 没勾选记住我，清除残留 cookie
            $expire = time() - 3600;
            $options = ['expires' => $expire, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax'];
            setcookie('user_id', '', $options);
            setcookie('username', '', $options);
            setcookie('position', '', $options);
            setcookie('account_type', '', $options);
            setcookie('remember_token', '', $options);
        }

        $redirect_page = $_GET['redirect'] ?? 'stocklistj1.php';
        error_log("Login successful, redirecting to: " . $redirect_page);
        session_write_close();
        header("Location: " . $redirect_page);
        exit();

    } else {
        error_log("Password mismatch for email: " . $email);
        echo "<script>alert('密码错误'); window.location.href='login.html';</script>";
        exit();
    }

} else {
    error_log("User not found for email: " . $email);
    exit();
}
?>
