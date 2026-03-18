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
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>无权访问 | Access Denied</title>
    <style>
        :root {
            /* Backend theme colors */
            --primary-bg: #faf7f2;
            --card-bg: #ffffff;
            --text-main: #000000;
            --text-muted: #6b7280;
            --danger: #ef4444;       
            --danger-bg: #fef2f2;     
            --danger-border: #fecaca; 
            /* Incorporating backend accent colors */
            --accent-orange: #ff5c00;
            --accent-yellow: #f99e00;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            background-color: var(--primary-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .error-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-container {
            background-color: var(--danger-bg);
            color: var(--danger);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            border: 4px solid var(--danger-border);
        }
        .icon-container svg {
            width: 40px;
            height: 40px;
        }
        h1 {
            color: var(--text-main);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        h2 {
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 24px;
        }
        .details-box {
            background-color: var(--primary-bg);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 32px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
        }
        .detail-row:not(:last-child) {
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-label {
            color: var(--text-muted);
        }
        .detail-value {
            color: var(--text-main);
            font-weight: 600;
            background: #e5e7eb;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: monospace;
        }
        .btn-back {
            display: block;
            background-color: var(--accent-yellow);
            color: #ffffff;
            text-decoration: none;
            padding: 14px 24px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .btn-back:hover {
            background-color: var(--accent-orange);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
        }
        .btn-back:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-container">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h1>抱歉，您无权访问该分支页面</h1>
        <h2>Access Denied</h2>
        
        <div class="details-box">
            <div class="detail-row">
                <span class="detail-label">当前账户分支</span>
                <span class="detail-value">' . htmlspecialchars(empty($user_branch) ? '未分配' : $user_branch) . '</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">页面要求分支</span>
                <span class="detail-value">' . htmlspecialchars($required_branch) . '</span>
            </div>
        </div>
        
        <a href="javascript:history.length > 1 ? history.back() : window.location.href=\'login.html\';" class="btn-back">
            返回上一页 / Go Back
        </a>
    </div>
</body>
</html>';
    exit;
}
?>
