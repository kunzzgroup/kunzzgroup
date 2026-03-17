<?php
/**
 * Mobile 身份验证检查（优化版）
 * 逻辑：优先 Session -> 次选 Cookie -> 最后跳转 Login
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. 优先从 Session 获取当前用户
if (isset($_SESSION['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
} 
// 2. 如果 Session 没有，尝试从 Cookie 恢复
elseif (isset($_COOKIE['user_login'])) {
    
    $user_id = intval($_COOKIE['user_login']);
    
    // 连接数据库以获取完整用户信息并校验
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
    
    $conn_auth = new mysqli($host, $dbuser, $dbpass, $dbname);
    if (!$conn_auth->connect_error) {
        $stmt_auth = $conn_auth->prepare("SELECT * FROM users WHERE id = ?");
        $stmt_auth->bind_param("i", $user_id);
        $stmt_auth->execute();
        $user_res = $stmt_auth->get_result();
        
        if ($user = $user_res->fetch_assoc()) {
            // 恢复 Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['account_type'] = $user['account_type'];
            $_SESSION['last_activity'] = time();
            
            $current_user_id = $user['id'];
        } else {
            // Cookie 无效（用户不存在），清理后跳转
            setcookie("user_login", "", time() - 3600, "/");
            setcookie("remember_token", "", time() - 3600, "/");
            header("Location: login.html");
            exit();
        }
        $stmt_auth->close();
        $conn_auth->close();
    }
} 
// 3. 都没有，跳转登录
else {
    $current_page = basename($_SERVER['PHP_SELF']);
    header("Location: login.html?redirect=" . urlencode($current_page));
    exit();
}

// 统一提供给页面的当前用户名变量
$currentUsername = '';
if (isset($_SESSION['username'])) {
    $currentUsername = $_SESSION['username'];
}
?>