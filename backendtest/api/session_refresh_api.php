<?php
require_once __DIR__ . '/../backend/core/api_guard.php';
require_login();
header('Content-Type: application/json');

// session_start(); replaced by api_guard
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'code' => 'SESSION_EXPIRED']);
} else {
    echo json_encode(['success' => true]);
}
?>

