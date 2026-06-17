<?php
require_once __DIR__ . '/../config.php';
/**
 * check_permissions.php
 * 返回当前登录用户在"货品种类"(stock_inventory)页面的
 * canApply（申请权限）和 canApprove（批准权限）
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Cache-Control: no-cache');

// 未登录
if (empty($_SESSION['user_id'])) {
    echo json_encode(['canApply' => false, 'canApprove' => false]);
    exit;
}

$canApply   = false;
$canApprove = false;

try {
    $pdo = get_pdo_connection();
    $userId = intval($_SESSION['user_id']);

    // ── 第一优先：user_page_permissions 新表 ──────────────────────
    try {
        $stmt = $pdo->prepare(
            "SELECT permissions_json FROM user_page_permissions
             WHERE user_id = ? AND page_key = 'stock_inventory'"
        );
        $stmt->execute([$userId]);
        $json = $stmt->fetchColumn();
        if ($json) {
            $decoded = json_decode($json, true);
            $views   = $decoded['views'] ?? ($decoded['view'] ?? []);
            if (in_array('apply',   $views, true)) $canApply   = true;
            if (in_array('approve', $views, true)) $canApprove = true;
        }
    } catch (Throwable $e) { /* 表不存在时跳过 */ }

    // ── 第二优先(兼容旧结构)：user_sidebar_permissions.page_permissions_json ──
    if (!$canApply && !$canApprove) {
        $stmt = $pdo->prepare(
            "SELECT page_permissions_json FROM user_sidebar_permissions WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $decoded = json_decode($raw, true);
            $views   = $decoded['stock_inventory']['views']
                    ?? ($decoded['stock_inventory']['view'] ?? []);
            if (in_array('apply',   $views, true)) $canApply   = true;
            if (in_array('approve', $views, true)) $canApprove = true;
        }
    }

    // ── 第三优先(兜底)：旧注册码方式 ─────────────────────────────
    if (!$canApply && !$canApprove) {
        $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP', 'HR2025', 'AZGQOY', 'IT7890'];
        $codeStmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
        $codeStmt->execute([$userId]);
        $userCode = $codeStmt->fetchColumn();
        if ($userCode && in_array($userCode, $allowedCodes, true)) {
            $canApply   = true;
            $canApprove = true;
        }
    }

} catch (Throwable $e) {
    // 数据库错误时保持 false
}

echo json_encode([
    'canApply'   => $canApply,
    'canApprove' => $canApprove,
]);
