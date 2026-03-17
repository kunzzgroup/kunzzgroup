<?php
session_start();

$conn = new mysqli('localhost', 'u690174784_kunzz', 'Kunzz1688', 'u690174784_kunzz');

if (isset($_SESSION['user_id'])) {

    $stmt = $conn->prepare("
        UPDATE users 
        SET remember_token=NULL, remember_expiry=NULL 
        WHERE id=?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// 删除 cookie
setcookie("remember_token", "", time() - 3600, "/");

session_destroy();

header("Location: login.html");
exit();