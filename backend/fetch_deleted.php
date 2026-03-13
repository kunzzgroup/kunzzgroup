<?php
// fetch_deleted.php
require_once 'session_check.php';
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    $results = [];
    $tables = [
        'j1' => 'j1stockedit_data',
        'j2' => 'j2stockedit_data',
        'j3' => 'j3stockedit_data',
        'central' => 'stockinout_data'
    ];

    foreach ($tables as $system => $table) {
        $sql = "SELECT id, product_name, in_quantity, out_quantity, specification, receiver, date, deleted_at, deleted_by 
                FROM $table 
                WHERE deleted_at IS NOT NULL 
                ORDER BY deleted_at DESC";
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['system'] = $system;
            $results[] = $row;
        }
    }

    // 按删除时间排序
    usort($results, function($a, $b) {
        return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
    });

    echo json_encode(["success" => true, "data" => $results]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
