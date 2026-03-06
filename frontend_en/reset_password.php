<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
require_once __DIR__ . '/../backend/xss_protect.php';
session_start();

// GET request: serve the reset password HTML page
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include 'reset_password.html';
    exit;
}

// POST request: handle password reset API
header("Content-Type: application/json");

// 1. Database connection
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

// 2. Connect
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get JSON input
$data = get_safe_json_input();
$email = $data["email"] ?? "";
$newPassword = $data["new_password"] ?? "";

// Validate fields
if (!$email || !$newPassword) {
    echo json_encode(["success" => false, "message" => "Email or new password is missing"]);
    exit;
}

// Validate session verification code
if (
    !isset($_SESSION["verification_code"]) ||
    !isset($_SESSION["verification_email"]) ||
    !isset($_SESSION["code_expire_time"]) ||
    $_SESSION["verification_email"] !== $email
) {
    echo json_encode(["success" => false, "message" => "Please complete verification first"]);
    exit;
}

// Check if code has expired
if (time() > $_SESSION["code_expire_time"]) {
    echo json_encode(["success" => false, "message" => "Verification code has expired"]);
    exit;
}

// Hash and update password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ?, is_first_login = 0 WHERE email = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Password updated successfully"]);

    // Clear verification session
    unset($_SESSION["verification_code"]);
    unset($_SESSION["verification_email"]);
    unset($_SESSION["code_expire_time"]);
} else {
    echo json_encode(["success" => false, "message" => "Password update failed"]);
}

$stmt->close();
$conn->close();
?>
