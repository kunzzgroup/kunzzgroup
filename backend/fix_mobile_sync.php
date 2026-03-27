<?php
// 一次性清理脚本：将已在桌面端被删除的手机记录同步软删除
// 执行后请立即删除此文件
require_once __DIR__ . '/session_check.php';

$host   = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// J3
$stmt = $pdo->prepare("
    UPDATE j3stockeditmobile_data m
    SET m.deleted_at = NOW(), m.deleted_by = 'system_sync'
    WHERE m.deleted_at IS NULL
      AND EXISTS (
          SELECT 1 FROM j3stockedit_data d
          WHERE d.mobile_ref_id = m.id AND d.deleted_at IS NOT NULL
      )
      AND NOT EXISTS (
          SELECT 1 FROM j3stockedit_data d2
          WHERE d2.mobile_ref_id = m.id AND d2.deleted_at IS NULL
      )
");
$stmt->execute();
$j3Count = $stmt->rowCount();

echo "<p>J3 手机记录已同步删除：<strong>{$j3Count}</strong> 条</p>";

// J2
try {
    $stmt2 = $pdo->prepare("
        UPDATE j2stockeditmobile_data m
        SET m.deleted_at = NOW(), m.deleted_by = 'system_sync'
        WHERE m.deleted_at IS NULL
          AND EXISTS (
              SELECT 1 FROM j2stockedit_data d
              WHERE d.mobile_ref_id = m.id AND d.deleted_at IS NOT NULL
          )
          AND NOT EXISTS (
              SELECT 1 FROM j2stockedit_data d2
              WHERE d2.mobile_ref_id = m.id AND d2.deleted_at IS NULL
          )
    ");
    $stmt2->execute();
    echo "<p>J2 手机记录已同步删除：<strong>{$stmt2->rowCount()}</strong> 条</p>";
} catch (Exception $e) {
    echo "<p>J2 跳过（表不存在或无 mobile_ref_id）</p>";
}

// J1
try {
    $stmt3 = $pdo->prepare("
        UPDATE j1stockeditmobile_data m
        SET m.deleted_at = NOW(), m.deleted_by = 'system_sync'
        WHERE m.deleted_at IS NULL
          AND EXISTS (
              SELECT 1 FROM j1stockedit_data d
              WHERE d.mobile_ref_id = m.id AND d.deleted_at IS NOT NULL
          )
          AND NOT EXISTS (
              SELECT 1 FROM j1stockedit_data d2
              WHERE d2.mobile_ref_id = m.id AND d2.deleted_at IS NULL
          )
    ");
    $stmt3->execute();
    echo "<p>J1 手机记录已同步删除：<strong>{$stmt3->rowCount()}</strong> 条</p>";
} catch (Exception $e) {
    echo "<p>J1 跳过（表不存在或无 mobile_ref_id）</p>";
}

echo "<p style='color:green'><strong>完成！请立即删除此文件：fix_mobile_sync.php</strong></p>";
