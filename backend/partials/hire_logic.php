<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/fragment_helpers.php';

define('SESSION_TIMEOUT_HIRE', 60);

function hire_getBackendWebBase() {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
}

function hire_requireAuthenticatedSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $hasRemember = (
        isset($_COOKIE['user_id'], $_COOKIE['username'], $_COOKIE['remember_token']) &&
        $_COOKIE['remember_token'] === '1'
    );

    if (isset($_SESSION['user_id'])) {
        if (
            isset($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_HIRE) &&
            !$hasRemember
        ) {
            session_unset();
            session_destroy();
            kunzz_send_fragment_unauthorized();
        }
        $_SESSION['last_activity'] = time();
        return;
    }

    if ($hasRemember) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'];
        $_SESSION['position'] = $_COOKIE['position'] ?? null;
        $_SESSION['last_activity'] = time();
        return;
    }

    kunzz_send_fragment_unauthorized();
}

function hire_prepareViewData() {
    return [
        'backendWebBase' => hire_getBackendWebBase(),
    ];
}
