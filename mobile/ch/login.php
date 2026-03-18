if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
require_once __DIR__ . '/../../backend/xss_protect.php';
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
            $_SESSION['nickname'] = $user['nickname'] ?? '';
            $_SESSION['username_cn'] = $user['username_cn'] ?? '';
            $_SESSION['last_activity'] = time();

            // 判断用户是否勾选“记住我”
            if ($remember) {
                // ✅ 勾选了"记住我"，设置 cookie（30天）
                $expire = time() + (86400 * 30);
                // 采用 frontend/login.php 的简单风格，增加可靠性
                setcookie('mobile_user_id', $user['id'], $expire, "/");
                setcookie('mobile_username', $user['username'], $expire, "/");
                setcookie('mobile_position', $user['position'], $expire, "/");
                setcookie('mobile_account_type', $user['account_type'], $expire, "/");
                setcookie('mobile_nickname', $user['nickname'], $expire, "/");
                setcookie('mobile_username_cn', $user['username_cn'], $expire, "/");
                setcookie('mobile_remember_token', '1', $expire, "/");
            } else {
                // ❌ 没勾选记住我，清除残留 cookie (会话级别不需要再设置，依靠 session)
                $expire_past = time() - 3600;
                setcookie('mobile_user_id', '', $expire_past, "/");
                setcookie('mobile_username', '', $expire_past, "/");
                setcookie('mobile_position', '', $expire_past, "/");
                setcookie('mobile_account_type', '', $expire_past, "/");
                setcookie('mobile_nickname', '', $expire_past, "/");
                setcookie('mobile_username_cn', '', $expire_past, "/");
                setcookie('mobile_remember_token', '', $expire_past, "/");
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
