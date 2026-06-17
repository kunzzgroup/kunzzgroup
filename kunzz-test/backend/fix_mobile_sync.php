<?php
require_once __DIR__ . '/../config.php';
// 诊断脚本：查看J3手机记录和对应的桌面记录状态
require_once __DIR__ . '/session_check.php';

$pdo = get_pdo_connection();
$action = $_GET['action'] ?? 'diagnose';

if ($action === 'fix') {
    // ===== 执行修复 =====
    // 1. mobile_ref_id 匹配的孤儿（桌面已删，手机未删）
    $s1 = $pdo->prepare("
        UPDATE j3stockeditmobile_data m
        SET m.deleted_at = NOW(), m.deleted_by = 'system_sync'
        WHERE m.deleted_at IS NULL
          AND EXISTS (SELECT 1 FROM j3stockedit_data d WHERE d.mobile_ref_id = m.id AND d.deleted_at IS NOT NULL)
          AND NOT EXISTS (SELECT 1 FROM j3stockedit_data d2 WHERE d2.mobile_ref_id = m.id AND d2.deleted_at IS NULL)
    ");
    $s1->execute();
    $c1 = $s1->rowCount();

    // 2. 在 j3stockeditmobile_data 存在但完全没有对应 j3stockedit_data 行的记录（孤儿）
    $s2 = $pdo->prepare("
        UPDATE j3stockeditmobile_data m
        SET m.deleted_at = NOW(), m.deleted_by = 'system_sync'
        WHERE m.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM j3stockedit_data d WHERE d.mobile_ref_id = m.id)
          AND m.out_quantity > 0
    ");
    $s2->execute();
    $c2 = $s2->rowCount();

    echo "<p>方法1（桌面已删）清理：<strong>$c1</strong> 条</p>";
    echo "<p>方法2（完全无对应桌面行）清理：<strong>$c2</strong> 条</p>";
    echo "<p style='color:green'><strong>完成！刷新手机页面确认。</strong></p>";
    echo "<p><a href='fix_mobile_sync.php'>← 返回诊断</a></p>";
    exit;
}

// ===== 诊断模式 =====
// 手机端还在显示的记录
$mobileStmt = $pdo->query("
    SELECT m.id as mobile_id, m.date, m.product_name, m.out_quantity, m.receiver,
           (SELECT COUNT(*) FROM j3stockedit_data d WHERE d.mobile_ref_id = m.id) as desktop_total,
           (SELECT COUNT(*) FROM j3stockedit_data d WHERE d.mobile_ref_id = m.id AND d.deleted_at IS NULL) as desktop_active,
           (SELECT COUNT(*) FROM j3stockedit_data d WHERE d.mobile_ref_id = m.id AND d.deleted_at IS NOT NULL) as desktop_deleted
    FROM j3stockeditmobile_data m
    WHERE m.deleted_at IS NULL
    ORDER BY m.date DESC, m.id DESC
    LIMIT 30
");
$rows = $mobileStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
body { font-family: sans-serif; padding: 20px; }
table { border-collapse: collapse; width: 100%; font-size: 13px; }
th, td { border: 1px solid #ccc; padding: 6px 10px; }
th { background: #333; color: white; }
.orphan { background: #fee2e2; }
.ok { background: #d1fae5; }
</style>
<h2>J3 手机记录诊断（当前显示中，共 <?= count($rows) ?> 条）</h2>
<table>
<tr><th>手机ID</th><th>日期</th><th>货品</th><th>出货量</th><th>出货人</th><th>桌面总行</th><th>桌面活跃</th><th>桌面已删</th><th>状态</th></tr>
<?php foreach ($rows as $r):
    $isOrphan = ($r['desktop_total'] == 0) || ($r['desktop_active'] == 0 && $r['desktop_deleted'] > 0);
    $class = $isOrphan ? 'orphan' : 'ok';
    $status = $isOrphan ? '⚠️ 孤儿（应删除）' : '✅ 正常';
?>
<tr class="<?= $class ?>">
    <td><?= $r['mobile_id'] ?></td>
    <td><?= $r['date'] ?></td>
    <td><?= htmlspecialchars($r['product_name']) ?></td>
    <td><?= $r['out_quantity'] ?></td>
    <td><?= htmlspecialchars($r['receiver'] ?? '-') ?></td>
    <td><?= $r['desktop_total'] ?></td>
    <td><?= $r['desktop_active'] ?></td>
    <td><?= $r['desktop_deleted'] ?></td>
    <td><?= $status ?></td>
</tr>
<?php endforeach; ?>
</table>
<br>
<a href="fix_mobile_sync.php?action=fix" style="background:#ef4444;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">
    ▶ 立即清理所有孤儿记录
</a>
