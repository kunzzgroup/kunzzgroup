<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
session_start();
header("Content-Type: application/json");

// 数据库配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["canApprove" => false]);
    exit;
}

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
            // $decoded 包含的是 {"systems": [...], "views": [...]}
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

    // 第二层验证（Fallback兼容）：针对系统原有管理员代码
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
    "canApprove" => $canApprove,
    "canApply" => $canApply
]);
?>
