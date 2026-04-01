<?php
require_once __DIR__ . '/permission_guard.php';
requirePermissionApi('resource', 'stock_inventory');

header("Content-Type: application/json; charset=utf-8");

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$product = $_GET['product'] ?? '100 PLUS';
$system = $_GET['system'] ?? 'j1';

$tableMap = [
    'central' => 'stockinout_data',
    'j1' => 'j1stockedit_data',
    'j2' => 'j2stockedit_data',
    'j3' => 'j3stockedit_data'
];

$table = $tableMap[$system] ?? 'j1stockedit_data';

// 1. 查询该产品的所有明细记录
$stmt = $pdo->prepare("SELECT id, date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system, deleted_at 
    FROM $table 
    WHERE product_name LIKE ? 
    ORDER BY date ASC, id ASC");
$stmt->execute(["%$product%"]);
$allRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. 分析：截至3月31日 vs 全部
$marchTotal = ['in' => 0, 'out' => 0];
$allTotal = ['in' => 0, 'out' => 0];
$aprilRecords = [];

foreach ($allRecords as $record) {
    if ($record['deleted_at'] !== null) continue; // 跳过已删除
    
    $inQty = floatval($record['in_quantity']);
    $outQty = floatval($record['out_quantity']);
    
    $allTotal['in'] += $inQty;
    $allTotal['out'] += $outQty;
    
    if ($record['date'] <= '2026-03-31') {
        $marchTotal['in'] += $inQty;
        $marchTotal['out'] += $outQty;
    } else {
        $aprilRecords[] = $record;
    }
}

echo json_encode([
    "product" => $product,
    "system" => $system,
    "table" => $table,
    "total_records" => count($allRecords),
    "active_records" => count(array_filter($allRecords, fn($r) => $r['deleted_at'] === null)),
    "march_summary" => [
        "total_in" => $marchTotal['in'],
        "total_out" => $marchTotal['out'],
        "balance" => $marchTotal['in'] - $marchTotal['out']
    ],
    "all_time_summary" => [
        "total_in" => $allTotal['in'],
        "total_out" => $allTotal['out'],
        "balance" => $allTotal['in'] - $allTotal['out']
    ],
    "april_records" => $aprilRecords,
    "all_records" => $allRecords
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
