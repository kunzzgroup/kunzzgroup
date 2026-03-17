<?php
session_start();

// 已登录直接通过
if (isset($_SESSION['user_id'])) {
    return;
}

// 自动登录（Remember Me）
if (isset($_COOKIE['remember_token'])) {

    $conn = new mysqli('localhost', 'u690174784_kunzz', 'Kunzz1688', 'u690174784_kunzz');

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

        return;
    }
}

// ❌ 没登录
header("Location: login.html");
exit();