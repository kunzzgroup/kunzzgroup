<?php
require_once __DIR__ . '/backend/session_check.php';

// 数据库连接
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 0;

echo "--- Session Info ---\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NULL') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'NULL') . "\n";
echo "Branch (Session): [" . ($_SESSION['branch'] ?? 'NULL') . "]\n";
echo "Branch (Cookie): [" . ($_COOKIE['branch'] ?? 'NULL') . "]\n";
echo "Mobile Branch (Cookie): [" . ($_COOKIE['mobile_branch'] ?? 'NULL') . "]\n";

if ($user_id > 0) {
    $sql = "SELECT username, email, branch FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        echo "\n--- Database Info ---\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Branch (DB): [" . ($user['branch'] ?? 'NULL') . "]\n";
    } else {
        echo "\nUser not found in DB.\n";
    }
}
?>
