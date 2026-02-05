<?php
// Sidebar Shell Page
// Handles session check and includes the template

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Timeout Logic (Preserved from original)
define('SESSION_TIMEOUT', 60);

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) &&
        (!isset($_COOKIE['remember_token']) || $_COOKIE['remember_token'] !== '1')) {
        
        session_unset();
        session_destroy();
        setcookie('user_id', '', time() - 60, "/");
        setcookie('username', '', time() - 60, "/");
        setcookie('position', '', time() - 60, "/");
        setcookie('remember_token', '', time() - 60, "/");
        
        // Use $basePath if available to find correct relative path to frontend
        $redirectPath = isset($basePath) ? $basePath . '../frontend/index.php' : '../../frontend/index.php';
        header("Location: $redirectPath");
        exit();
    }
    $_SESSION['last_activity'] = time();

} elseif (isset($_COOKIE['user_id']) && isset($_COOKIE['username']) && 
          isset($_COOKIE['remember_token']) && $_COOKIE['remember_token'] === '1') {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['last_activity'] = time();
} else {
    $redirectPath = isset($basePath) ? $basePath . '../frontend/index.php' : '../../frontend/index.php';
    header("Location: $redirectPath");
    exit();
}

// Include the template
include __DIR__ . '/../templates/sidebar_template.php'; 
?>
