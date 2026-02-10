<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// require_once __DIR__ . '/check_permissions.php'; // Outputs JSON, removed
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/sidebar.php';
// require_once __DIR__ . '/check_restaurant_setup.php'; // Outputs HTML diagnostic, removed
?>
