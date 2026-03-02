<?php
// ============================================================
// KUNZZ 媒体诊断页面 - 检查所有媒体文件是否存在于服务器
// 访问: https://kunzzgroup.com/diagnose_media.php
// 用完后请删除此文件！
// ============================================================

// 安全保护 - 只允许登录用户访问
session_start();
// 注释掉下面的检查以在未登录时也能访问（调试用）
// if (!isset($_SESSION['user_id'])) { die('需要登录'); }

$root = $_SERVER['DOCUMENT_ROOT'];
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>媒体诊断</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#111;color:#0f0;font-size:13px}";
echo "h2{color:#ff0}.ok{color:#0f0}.fail{color:#f44}.warn{color:#fa0}pre{background:#000;padding:10px;border:1px solid #333;white-space:pre-wrap;word-break:break-all}</style></head><body>";

echo "<h2>🛠 KUNZZ 媒体诊断工具</h2>";
echo "<p style='color:#888'>Document Root: <span style='color:#fff'>" . $root . "</span></p>";
echo "<p style='color:#888'>当前 CWD: <span style='color:#fff'>" . getcwd() . "</span></p>";
echo "<p style='color:#888'>当前文件: <span style='color:#fff'>" . __FILE__ . "</span></p>";

echo "<hr style='border-color:#333'>";

// ---- 1. 检查 media_config.json ----
echo "<h2>① media_config.json 内容</h2>";
$configPaths = [
    'media_config.json',
    $root . '/media_config.json',
];
$foundConfig = false;
foreach ($configPaths as $cp) {
    if (file_exists($cp)) {
        echo "<p class='ok'>✅ 找到: $cp</p>";
        $json = json_decode(file_get_contents($cp), true);
        if ($json) {
            echo "<pre>";
            foreach ($json as $key => $val) {
                if (is_array($val) && isset($val['file'])) {
                    $exists = file_exists($val['file']) || file_exists($root . '/' . ltrim($val['file'], '/'));
                    $status = $exists ? "<span class='ok'>✅ 文件存在</span>" : "<span class='fail'>❌ 文件不存在</span>";
                    echo htmlspecialchars("[$key] file: " . $val['file'] . " | type: " . ($val['type'] ?? '?')) . " $status\n";
                }
            }
            echo "</pre>";
        } else {
            echo "<p class='fail'>❌ JSON 解析失败</p>";
        }
        $foundConfig = true;
        break;
    }
}
if (!$foundConfig) {
    echo "<p class='fail'>❌ media_config.json 不存在！（使用默认配置）</p>";
}

echo "<hr style='border-color:#333'>";

// ---- 2. 检查关键目录 ----
echo "<h2>② 关键目录检查</h2>";
$dirs = [
    'images/images/',
    'video/video/',
    'audio/audio/',
    'backend/video/video/',
    'backend/images/images/',
];
foreach ($dirs as $d) {
    $full = $root . '/' . $d;
    if (is_dir($full)) {
        $files = scandir($full);
        $count = count($files) - 2; // remove . and ..
        echo "<p class='ok'>✅ $d → $count 个文件</p>";
        if ($count > 0 && $count <= 20) {
            echo "<pre style='margin:0 0 5px 20px'>";
            foreach ($files as $f) {
                if ($f !== '.' && $f !== '..') echo htmlspecialchars($f) . "\n";
            }
            echo "</pre>";
        }
    } else {
        echo "<p class='fail'>❌ 目录不存在: $d</p>";
    }
}

echo "<hr style='border-color:#333'>";

// ---- 3. 检查硬编码图片是否存在 ----
echo "<h2>③ 前端硬编码图片检查</h2>";
$hardcoded = [
    'images/images/logo.png',
    'images/images/KUNZZ.png',
    'images/images/积极向上 (1).png',
    'images/images/高效执行 (1).png',
    'images/images/灵活应变 (1).png',
    'images/images/诚信待人 (1).png',
    'images/images/目标导向.png',
    'images/images/理念一致.png',
    'images/images/追求卓越.png',
    'images/images/创新精神.png',
    'images/images/带薪假期.png',
    'images/images/旅游奖励.png',
    'images/images/汽车奖励.png',
    'images/images/房子奖励.png',
    'images/images/年度绩效奖励.png',
    'images/images/专业培训与学习机会.png',
    'video/video/home_background.webm',
    'video/video/home_background.mp4',
];
foreach ($hardcoded as $f) {
    $full = $root . '/' . $f;
    if (file_exists($full)) {
        $size = round(filesize($full) / 1024, 1);
        echo "<p class='ok'>✅ " . htmlspecialchars($f) . " ({$size} KB)</p>";
    } else {
        echo "<p class='fail'>❌ " . htmlspecialchars($f) . "</p>";
    }
}

echo "<hr style='border-color:#333'>";

// ---- 4. 检查侧边栏SVG图标 ----
echo "<h2>④ 侧边栏 SVG 图标</h2>";
$svgs = [
    'images/images/网页照片上传.svg',
    'images/images/运营分析与报表.svg',
    'images/images/人事与资源管理.svg',
    'images/images/资源库管理.svg',
];
foreach ($svgs as $f) {
    $full = $root . '/' . $f;
    if (file_exists($full)) {
        echo "<p class='ok'>✅ " . htmlspecialchars($f) . "</p>";
    } else {
        echo "<p class='fail'>❌ " . htmlspecialchars($f) . " （需要上传）</p>";
    }
}

echo "<hr style='border-color:#333'>";
echo "<p class='warn'>⚠️ 诊断完毕！请在服务器文件管理器中删除此文件 (diagnose_media.php)</p>";
echo "</body></html>";
