<?php

require_once __DIR__ . '/xss_protect.php';

function find_user_by_login_identifier(mysqli $conn, string $identifier): ?array
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return null;
    }

    if (ctype_digit($identifier)) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $id = (int) $identifier;
        $stmt->bind_param('i', $id);
    } else {
        $normalized = strtolower($identifier);
        $stmt = $conn->prepare(
            'SELECT * FROM users WHERE LOWER(email) = ? OR LOWER(username) = ? LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('ss', $normalized, $normalized);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows === 1 ? $result->fetch_assoc() : null;
}

function login_set_session_and_cookies(array $user, bool $remember): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['position'] = $user['position'];
    $_SESSION['account_type'] = $user['account_type'];
    $_SESSION['branch'] = strtoupper($user['branch'] ?? '');
    $_SESSION['last_activity'] = time();

    if ($remember) {
        $expire = time() + (86400 * 30);
        setcookie('user_id', $user['id'], $expire, '/');
        setcookie('username', $user['username'], $expire, '/');
        setcookie('position', $user['position'], $expire, '/');
        setcookie('account_type', $user['account_type'], $expire, '/');
        setcookie('branch', strtoupper($user['branch'] ?? ''), $expire, '/');
        setcookie('remember_token', '1', $expire, '/');
        return;
    }

    setcookie('user_id', '', time() - 3600, '/');
    setcookie('username', '', time() - 3600, '/');
    setcookie('position', '', time() - 3600, '/');
    setcookie('account_type', '', time() - 3600, '/');
    setcookie('branch', '', time() - 3600, '/');
    setcookie('remember_token', '', time() - 3600, '/');
}

function redirect_with_login_alert(string $message, string $loginPageUrl): void
{
    echo '<script>alert(' . json_encode($message, JSON_UNESCAPED_UNICODE) . '); window.location.href='
        . json_encode($loginPageUrl) . ';</script>';
    exit();
}

function process_web_login(
    mysqli $conn,
    string $identifier,
    string $password,
    bool $remember,
    string $loginPageUrl,
    string $dashboardUrl,
    string $resetPasswordUrl
): void {
    $user = find_user_by_login_identifier($conn, $identifier);
    if (!$user) {
        redirect_with_login_alert('该账号不存在', $loginPageUrl);
    }

    if (!verify_secure_password($password, $user['password'])) {
        redirect_with_login_alert('密码错误', $loginPageUrl);
    }

    login_set_session_and_cookies($user, $remember);

    if ((int) ($user['is_first_login'] ?? 0) === 1) {
        header('Location: ' . $resetPasswordUrl);
        exit();
    }

    header('Location: ' . $dashboardUrl);
    exit();
}
