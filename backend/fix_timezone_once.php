<?php
/**
 * fix_timezone_once.php — 一次性时区修正脚本
 * 将 job_applications 表所有旧记录的 created_at / updated_at 往前加 8 小时
 * （把 UTC 时间修正为 UTC+8 马来西亚时间）
 *
 * 使用方法：浏览器访问 https://kunzzgroup.com/backend/fix_timezone_once.php
 * 执行完成后请立即删除此文件！
 */

// 简单安全校验：只允许登录用户访问
session_start();
if (empty($_SESSION['user_id'])) {
    die('<h2 style="color:red">❌ 未登录，拒绝访问</h2>');
}

$host   = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '+08:00'");

    // 先查看当前记录
    $before = $pdo->query("SELECT id, created_at, updated_at FROM job_applications ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    // 执行修正：+8小时
    // 只修正那些时间明显偏早的记录（created_at < 当前MYT时间减去合理范围）
    // 安全做法：给所有记录 +8 小时（假设之前全部是 UTC 存储的）
    $stmt = $pdo->exec("UPDATE job_applications SET 
        created_at = DATE_ADD(created_at, INTERVAL 8 HOUR),
        updated_at = DATE_ADD(updated_at, INTERVAL 8 HOUR)
    ");

    // 查看修正后的记录
    $after = $pdo->query("SELECT id, created_at, updated_at FROM job_applications ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <style>body{font-family:sans-serif;padding:30px;background:#faf7f2;}
    table{border-collapse:collapse;width:100%;margin-top:16px;}
    th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;font-size:13px;}
    th{background:#636363;color:#fff;}
    tr:nth-child(even){background:#f3f4f6;}
    .success{color:#059669;font-weight:bold;font-size:18px;}
    .section{margin-top:32px;font-weight:bold;font-size:15px;color:#333;}
    .warn{background:#fff3cd;border:1px solid #fbbf24;padding:14px 18px;border-radius:8px;margin-top:24px;font-size:14px;}
    </style></head><body>';

    echo '<h1>⏰ 时区修正完成</h1>';
    echo '<p class="success">✅ 所有记录的 created_at / updated_at 已 +8 小时（UTC → UTC+8）</p>';

    echo '<div class="section">📋 修正前：</div>';
    echo '<table><tr><th>ID</th><th>created_at (修正前)</th><th>updated_at (修正前)</th></tr>';
    foreach ($before as $row) {
        echo "<tr><td>{$row['id']}</td><td>{$row['created_at']}</td><td>{$row['updated_at']}</td></tr>";
    }
    echo '</table>';

    echo '<div class="section">✅ 修正后：</div>';
    echo '<table><tr><th>ID</th><th>created_at (修正后)</th><th>updated_at (修正后)</th></tr>';
    foreach ($after as $row) {
        echo "<tr><td>{$row['id']}</td><td>{$row['created_at']}</td><td>{$row['updated_at']}</td></tr>";
    }
    echo '</table>';

    echo '<div class="warn">⚠️ <strong>请立即删除此文件！</strong><br>
    路径：<code>/backend/fix_timezone_once.php</code><br>
    此文件仅供一次性使用，保留会有安全风险。</div>';

    echo '</body></html>';

} catch (PDOException $e) {
    die('<h2 style="color:red">❌ 数据库错误：' . htmlspecialchars($e->getMessage()) . '</h2>');
}
