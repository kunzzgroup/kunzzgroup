<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'code' => 'SESSION_EXPIRED']);
} else {
    echo json_encode(['success' => true]);
}
?>
