<?php
session_start();

// 1. 数据库连接配置
$host = 'localhost';
$dbname = 'u857194726_tokyo'; // 改成你的数据库名
$user = 'u857194726_tokyo';   // 改成你的数据库用户名
$pass = 'Kholdings1688@';       // 改成你的数据库密码

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("连接失败: " . $e->getMessage());
}

// 2. 获取表单数据
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 3. 查找用户
    $stmt = $conn->prepare("SELECT * FROM tokyo_users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. 验证用户和密码
    if ($user && password_verify($password, $user['password'])) {
        // 登录成功，保存登录状态
        $_SESSION['user_email'] = $user['email'];
        header("Location: tokyo-japanese-cuisine.html"); // 登录成功后跳转页面
        exit();
    } else {
        // 登录失败
        echo "<script>alert('账号不存在或密码错误');window.location.href='tokyologin.html';</script>";
        exit();
    }
}
?>
