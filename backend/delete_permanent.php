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
$system = $data['system'] ?? ''; // 可选，指定系统

if (empty($ids)) {
    echo json_encode(["success" => false, "message" => "缺少记录ID"]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // 映射系统到对应的表
    $tableMap = [
        'j1' => ['j1stockedit_data', 'j1stockeditmobile_data'],
        'j2' => ['j2stockedit_data', 'j2stockeditmobile_data'],
        'j3' => ['j3stockedit_data', 'j3stockeditmobile_data'],
        'central' => ['stockinout_data']
    ];
    
    if ($system && isset($tableMap[$system])) {
        $tables = $tableMap[$system];
    } else {
        // 如果未指定，尝试在所有表中删除 (仅针对已在 soft delete 状态的记录)
        $tables = ['j1stockedit_data', 'j2stockedit_data', 'j3stockedit_data', 'stockinout_data', 'j1stockeditmobile_data', 'j2stockeditmobile_data', 'j3stockeditmobile_data'];
    }
    
    foreach ($tables as $table) {
        $sql = "DELETE FROM $table WHERE id IN ($placeholders) AND deleted_at IS NOT NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
    }
    
    $pdo->commit();
    echo json_encode(["success" => true, "message" => "记录已永久删除"]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "永久删除失败: " . $e->getMessage()]);
}
?>
