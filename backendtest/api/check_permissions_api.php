<?php
session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/core/init.php';

$canApprove = false;
$canApply = false;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // 第一层验证：检查 user_page_permissions 表里的动态权限
    try {
        $permStmt = $pdo->prepare("SELECT permissions_json FROM user_page_permissions WHERE user_id = ? AND page_key = 'stock_inventory'");
        $permStmt->execute([$userId]);
        $permData = $permStmt->fetchColumn();

        if ($permData) {
            $decoded = json_decode($permData, true);
            if (isset($decoded['views']) && is_array($decoded['views'])) {
                if (in_array('approve', $decoded['views'])) {
                    $canApprove = true;
                }
                if (in_array('apply', $decoded['views'])) {
                    $canApply = true;
                }
            }
        }
    } catch (PDOException $e) {
        error_log("获取用户权限失败：" . $e->getMessage());
    }

    // 第二层验证（Fallback兼容）
    if (!$permData) {
        $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP', 'HR2025','AZGQOY','IT7890'];
        $stmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userCode = $stmt->fetchColumn();

        if ($userCode && in_array($userCode, $allowedCodes)) {
            $canApprove = true;
            $canApply = true;
        }
    }
}

echo json_encode([
    "success" => true,
    "canApprove" => $canApprove,
    "canApply" => $canApply
]);
?>
