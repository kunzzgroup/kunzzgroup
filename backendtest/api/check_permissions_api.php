<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["allowed" => false]);
    exit;
}

echo json_encode([
    "allowed" => true,
    "role" => $_SESSION['role'] ?? 'user'
]);
?>
