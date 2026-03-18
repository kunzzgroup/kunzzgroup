<?php
/**
 * Branch Access Control logic
 * Sets restrictions based on $page_branch variable.
 */

if (!isset($_SESSION['branch'])) {
    // If branch is not set, we assume restricted access unless authenticated
    if (isset($_SESSION['user_id'])) {
        // Logged in but no branch info? This shouldn't happen with new login logic.
        // For safety, we can allow 'KH' as default if they are admin, but better to deny.
        http_response_code(403);
        echo "Access denied: Missing branch information in session.";
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
