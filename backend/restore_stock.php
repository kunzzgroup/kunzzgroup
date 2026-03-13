<?php
require_once __DIR__ . '/xss_protect.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "数据库连接失败"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$ids = $data['ids'] ?? [];

if (empty($ids)) {
    echo json_encode(["success" => false, "message" => "缺少记录ID"]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // 如果是 Central 系统记录，需要同步恢复关联的 J1/J2/J3 记录
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // 1. 获取要恢复的 records 信息 (如果是 stockinout_data)
    $getSql = "SELECT * FROM stockinout_data WHERE id IN ($placeholders)";
    $getStmt = $pdo->prepare($getSql);
    $getStmt->execute($ids);
    $records = $getStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. 恢复主表记录
    $updateSql = "UPDATE stockinout_data SET deleted_at = NULL, deleted_by = NULL WHERE id IN ($placeholders)";
    $pdo->prepare($updateSql)->execute($ids);

    // 3. 同步恢复关联表记录
    foreach ($records as $record) {
        if (floatval($record['out_quantity'] ?? 0) > 0) {
            $targetSystem = $record['target_system'] ?? 'j1';
            $mainRecordId = $record['id'];
            
            if ($targetSystem === 'j1') {
                $pdo->prepare("UPDATE j1stockinout_data SET deleted_at = NULL, deleted_by = NULL WHERE main_record_id = ?")->execute([$mainRecordId]);
                $pdo->prepare("UPDATE j1stockedit_data SET deleted_at = NULL, deleted_by = NULL WHERE product_name = ? AND receiver = ? AND target_system = 'j1' AND deleted_at IS NOT NULL LIMIT 1")
                    ->execute([$record['product_name'], $record['receiver']]);
            } elseif ($targetSystem === 'j2') {
                $pdo->prepare("UPDATE j2stockinout_data SET deleted_at = NULL, deleted_by = NULL WHERE main_record_id = ?")->execute([$mainRecordId]);
                $pdo->prepare("UPDATE j2stockedit_data SET deleted_at = NULL, deleted_by = NULL WHERE product_name = ? AND receiver = ? AND target_system = 'j2' AND deleted_at IS NOT NULL LIMIT 1")
                    ->execute([$record['product_name'], $record['receiver']]);
            } elseif ($targetSystem === 'j3') {
                $pdo->prepare("UPDATE j3stockinout_data SET deleted_at = NULL, deleted_by = NULL WHERE main_record_id = ?")->execute([$mainRecordId]);
                $pdo->prepare("UPDATE j3stockedit_data SET deleted_at = NULL, deleted_by = NULL WHERE product_name = ? AND receiver = ? AND target_system = 'j3' AND deleted_at IS NOT NULL LIMIT 1")
                    ->execute([$record['product_name'], $record['receiver']]);
            }
        }
    }

    // 4. 同时也恢复其他可能的表 (如果是直接在分店表删除的情况)
    $otherTables = ['j1stockedit_data', 'j2stockedit_data', 'j3stockedit_data', 'j1stockeditmobile_data', 'j2stockeditmobile_data', 'j3stockeditmobile_data'];
    foreach ($otherTables as $table) {
        try {
            $sql = "UPDATE $table SET deleted_at = NULL, deleted_by = NULL WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ids);
        } catch (PDOException $e) {
            // 如果是字段不存在错误，记录日志但继续执行
            if ($e->getCode() == '42S22') {
                error_log("Restore sync skipped for table $table: " . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }
    
    $pdo->commit();
    echo json_encode(["success" => true, "message" => "记录已成功恢复"]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "恢复失败: " . $e->getMessage()]);
}
?>
