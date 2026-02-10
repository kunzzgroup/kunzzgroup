<?php
// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('CORE_PATH', ROOT_PATH . '/core');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('PAGES_PATH', ROOT_PATH . '/pages');

// Start session safely
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Require core files
require_once CORE_PATH . '/config.php';
require_once CORE_PATH . '/input_clean.php';
require_once CORE_PATH . '/db.php';
