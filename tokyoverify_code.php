<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$code = $data["code"] ?? "";

if (!$email || !$code) {
    echo json_encode(["success" => false, "message" => "邮箱或验证码缺失"]);
    exit;
}

// 验证 session 中是否有验证码
if (
    !isset($_SESSION["verification_code"]) ||
    !isset($_SESSION["verification_email"]) ||
    !isset($_SESSION["code_expire_time"])
) {
    echo json_encode(["success" => false, "message" => "请先获取验证码"]);
    exit;
}

// 检查是否匹配（都转换为字符串）
if (
    (string)$_SESSION["verification_email"] !== (string)$email ||
    (string)$_SESSION["verification_code"] !== (string)$code
) {
    echo json_encode(["success" => false, "message" => "验证码错误"]);
    exit;
}

// 检查是否过期
if (time() > $_SESSION["code_expire_time"]) {
    echo json_encode(["success" => false, "message" => "验证码已过期"]);
    exit;
}

echo json_encode(["success" => true, "message" => "验证成功"]);
