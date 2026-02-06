<?php
if (session_status() === PHP_SESSION_NONE) {

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/backendtest/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'NOT_LOGIN']);
        exit;
    }
}

// User Info Variables for compatibility with existing code
$username = $_SESSION['username'] ?? 'User';
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';
$account_type = (!empty($_SESSION['account_type'])) ? $_SESSION['account_type'] : 'user';
$avatarLetter = strtoupper($username[0] ?? 'U');
?>
