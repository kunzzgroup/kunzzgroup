<?php
require_once __DIR__ . '/../../backend/xss_protect.php';
ob_start();
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ================= 数据库 =================
$conn = new mysqli('localhost', 'u690174784_kunzz', 'Kunzz1688', 'u690174784_kunzz');
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// ================= 自动登录（Remember Me）=================
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE remember_token=? 
        AND remember_expiry > NOW()
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['position'] = $user['position'];
        $_SESSION['account_type'] = $user['account_type'];
        $_SESSION['last_activity'] = time();

        header("Location: stocklistj1.php");
        exit();
    }
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

            // ✅ Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['account_type'] = $user['account_type'];
            $_SESSION['last_activity'] = time();

            // ✅ 首次登录
            if ($user['is_first_login'] == 1) {
                header("Location: reset_password.html");
                exit();
            }

            // ================= Remember Me =================
            if ($remember) {

                $token = bin2hex(random_bytes(32));

                $stmt = $conn->prepare("
                    UPDATE users 
                    SET remember_token=?, 
                        remember_expiry=DATE_ADD(NOW(), INTERVAL 30 DAY)
                    WHERE id=?
                ");
                $stmt->bind_param("si", $token, $user['id']);
                $stmt->execute();

                setcookie("remember_token", $token, [
                    'expires' => time() + 86400 * 30,
                    'path' => '/',
                    'secure' => false, // 有 HTTPS 改 true
                    'httponly' => false, // ✅ 改为 false，让 login.html 的 JS 能检测到
                    'samesite' => 'Lax'
                ]);

            }
            else {

                $stmt = $conn->prepare("
                    UPDATE users 
                    SET remember_token=NULL, remember_expiry=NULL 
                    WHERE id=?
                ");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();

                setcookie("remember_token", "", time() - 3600, "/");
            }

            $redirect = $_GET['redirect'] ?? 'stocklistj1.php';
            header("Location: " . $redirect);
            exit();

        }
        else {
            echo "<script>alert('密码错误');window.location.href='login.html';</script>";
            exit();
        }

    }
    else {
        echo "<script>alert('用户不存在');window.location.href='login.html';</script>";
        exit();
    }
} else {
    // 如果不是 POST 请求，且没有自动登录成功，就呆在登录页
    header("Location: login.html");
    exit();
}
?>