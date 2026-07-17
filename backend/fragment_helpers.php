<?php

require_once __DIR__ . '/../config.php';

/**
 * Detect React v2 fragment fetches (must not return full login HTML).
 */
function kunzz_is_backend_fragment_request() {
    $flag = $_SERVER['HTTP_X_KUNZZ_BACKEND_FRAGMENT'] ?? '';
    return $flag === '1' || strcasecmp($flag, 'true') === 0;
}

function kunzz_fragment_login_url($returnTo = null) {
    $loginPage = app_url('frontend/login.html');
    if ($returnTo === null || $returnTo === '') {
        return $loginPage;
    }

    $returnTo = ltrim($returnTo, '/');
    return $loginPage . '?redirect=' . rawurlencode($returnTo);
}

function kunzz_fragment_current_return_to() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri === '') {
        return '';
    }

    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '';
    }

    // Map legacy fragment paths back to their v2 routes when possible.
    if (preg_match('#/backend/(.+)_fragment\.php$#i', $path, $matches)) {
        return 'backend/' . $matches[1] . '-v2';
    }

    return ltrim($path, '/');
}

function kunzz_send_fragment_unauthorized($message = '登录已过期，请重新登录') {
    if (!kunzz_is_backend_fragment_request()) {
        header('Location: ' . kunzz_fragment_login_url(kunzz_fragment_current_return_to()));
        exit;
    }

    if (!headers_sent()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    echo json_encode([
        'success' => false,
        'code' => 'UNAUTHORIZED',
        'message' => $message,
        'redirect' => kunzz_fragment_login_url(kunzz_fragment_current_return_to()),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function kunzz_send_fragment_forbidden($message = '无权限访问此功能') {
    if (!kunzz_is_backend_fragment_request()) {
        http_response_code(403);
        $username = $_SESSION['username'] ?? '未知用户';
        echo '<!DOCTYPE html><html lang="zh"><head><meta charset="UTF-8"><title>403</title></head><body><h1>403</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><p>用户: ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
        exit;
    }

    if (!headers_sent()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    echo json_encode([
        'success' => false,
        'code' => 'FORBIDDEN',
        'message' => $message,
        'redirect' => kunzz_fragment_login_url(kunzz_fragment_current_return_to()),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
