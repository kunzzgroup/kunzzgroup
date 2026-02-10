<?php
require_once __DIR__ . '/path.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once CORE_PATH . '/check_permissions.php';
require_once CORE_PATH . '/session_check.php';
require_once CORE_PATH . '/sidebar.php';
require_once CORE_PATH . '/check_restaurant_setup.php';
