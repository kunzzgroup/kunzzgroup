<?php
/**
 * Branch Access Control logic
 * Sets restrictions based on $page_branch variable.
 */

if (!isset($_SESSION['branch'])) {
    // 如果 Session 中没有分支信息，尝试从 Cookie 恢复
    if (isset($_COOKIE['mobile_branch'])) {
        $_SESSION['branch'] = strtoupper($_COOKIE['mobile_branch']);
    } elseif (isset($_SESSION['user_id'])) {
        // 如果已登录但完全没有分支信息（旧会话），强制重新登录以刷新权限
        $current_page = basename($_SERVER['PHP_SELF']);
        header("Location: login.html?redirect=" . urlencode($current_page) . "&msg=refresh_required");
        exit;
    }
}

$user_branch = strtoupper($_SESSION['branch'] ?? '');

// HQ (KH) users can access everything
if ($user_branch === 'KH') {
    return;
}

// Check if page_branch is set, otherwise default to "KH" (locked down)
$required_branch = strtoupper($page_branch ?? 'KH');

if ($user_branch !== $required_branch) {
    http_response_code(403);
    echo "Access denied: Unauthorized branch access. (User: $user_branch, Required: $required_branch)";
    exit;
}
?>
