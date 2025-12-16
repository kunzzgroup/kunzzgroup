<?php
header("Content-Type: application/json");

$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone_number'] ?? '');
$password = $data['password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
    echo json_encode(["success" => false, "message" => "请填写所有字段"]);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(["success" => false, "message" => "两次输入的密码不一致"]);
    exit;
}

// 检查 email 是否已注册
$stmt = $conn->prepare("SELECT id FROM users_member WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "该邮箱已被注册，请使用其他 Gmail"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// 密码加密
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 插入用户数据
$stmt = $conn->prepare("INSERT INTO users_member (username, email, phone_number, password, created_at)
                        VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "注册成功！"]);
} else {
    echo json_encode(["success" => false, "message" => "注册失败：" . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
