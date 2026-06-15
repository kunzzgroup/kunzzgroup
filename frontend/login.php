<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
require_once __DIR__ . '/../config.php';

$loginPage = app_url('frontend/login.html');
$dashboardPage = app_url('backend/dashboard');
$resetPasswordPage = app_url('frontend/reset_password');

// 如果不是 POST 请求（如直接访问 /frontend/login），跳转到登录表单页
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $loginPage);
    exit();
}

require_once __DIR__ . '/../backend/xss_protect.php';
ob_start();
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $conn = get_mysqli_connection();
} catch (RuntimeException $e) {
    error_log('Login connection failed: ' . $e->getMessage());
    echo "<script>alert('无法连接数据库，请稍后再试'); window.location.href=" . json_encode($loginPage) . ";</script>";
    exit();
}

$email = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

$sql = 'SELECT * FROM users WHERE LOWER(email) = ?';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Login prepare failed: ' . $conn->error);
    echo "<script>alert('登录服务暂时不可用，请联系管理员'); window.location.href=" . json_encode($loginPage) . ";</script>";
    exit();
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (verify_secure_password($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['position'] = $user['position'];
        $_SESSION['account_type'] = $user['account_type'];
        $_SESSION['branch'] = strtoupper($user['branch'] ?? '');
        $_SESSION['last_activity'] = time();

        if ((int) ($user['is_first_login'] ?? 0) === 1) {
            header('Location: ' . $resetPasswordPage);
            exit();
        }

        if ($remember) {
            $expire = time() + (86400 * 30);
            setcookie('user_id', $user['id'], $expire, '/');
            setcookie('username', $user['username'], $expire, '/');
            setcookie('position', $user['position'], $expire, '/');
            setcookie('account_type', $user['account_type'], $expire, '/');
            setcookie('branch', strtoupper($user['branch'] ?? ''), $expire, '/');
            setcookie('remember_token', '1', $expire, '/');
        } else {
            setcookie('user_id', '', time() - 3600, '/');
            setcookie('username', '', time() - 3600, '/');
            setcookie('position', '', time() - 3600, '/');
            setcookie('account_type', '', time() - 3600, '/');
            setcookie('branch', '', time() - 3600, '/');
            setcookie('remember_token', '', time() - 3600, '/');
        }

        header('Location: ' . $dashboardPage);
        exit();
    }

    echo "<script>alert('密码错误'); window.location.href=" . json_encode($loginPage) . ";</script>";
    exit();
}

echo "<script>alert('该账号不存在'); window.location.href=" . json_encode($loginPage) . ";</script>";
exit();
