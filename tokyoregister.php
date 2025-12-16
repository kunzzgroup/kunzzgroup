<?php
// 设置响应为 JSON
header("Content-Type: application/json");

// 1. 数据库连接配置
$host = 'localhost';
$dbname = 'u857194726_tokyo';
$dbuser = 'u857194726_tokyo';
$dbpass = 'Kholdings1688@';

// 2. 建立数据库连接
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// 3. 检查连接是否成功
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $conn->connect_error]);
    exit;
}

// 4. 解析 JSON 请求数据
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

// 5. 检查必填字段
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    echo json_encode(["success" => false, "message" => "请填写所有字段"]);
    exit;
}

// 6. 检查两次密码是否一致
if ($password !== $confirm_password) {
    echo json_encode(["success" => false, "message" => "两次输入的密码不一致，请返回修改。"]);
    exit;
}

// 7. 检查邮箱是否已经注册
$stmt = $conn->prepare("SELECT id FROM tokyo_users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "该邮箱已被注册，请使用其他 Gmail。"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// 8. 加密密码
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 9. 插入用户信息
$stmt = $conn->prepare("INSERT INTO tokyo_users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "注册成功！现在你可以登录了。"]);
} else {
    echo json_encode(["success" => false, "message" => "注册失败，请稍后重试。"]);
}

// 10. 关闭连接
$stmt->close();
$conn->close();
?>
