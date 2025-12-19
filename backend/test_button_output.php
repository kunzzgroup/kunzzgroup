<?php
// 测试按钮输出
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("需要登录");
}

include_once __DIR__ . '/../media_config.php';

$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');
$configFile = $isEnglish ? __DIR__ . '/../timeline_config_en.json' : __DIR__ . '/../timeline_config.json';

// 读取配置
$items = [];
if (file_exists($configFile)) {
    $content = @file_get_contents($configFile);
    if ($content !== false) {
        $raw = json_decode($content, true);
        $raw = $raw ?: [];
        if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
            foreach ($raw as $yearKey => $entries) {
                foreach ($entries as $entry) {
                    $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                    $items[] = array_merge($entryArray, [ 'year' => (string)$yearKey, 'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 ]);
                }
            }
        } else {
            $items = $raw;
        }
    }
}

echo "<h1>测试按钮输出</h1>";
echo "<h2>Items 数据:</h2>";
echo "<pre>" . print_r($items, true) . "</pre>";

echo "<h2>年份提取:</h2>";
$years = array_values(array_unique(array_map(function($it){ return (string)($it['year'] ?? ''); }, $items)));
sort($years, SORT_NUMERIC);
echo "<pre>" . print_r($years, true) . "</pre>";

echo "<h2>HTML 输出测试:</h2>";
echo "<div class='year-management' style='border: 2px solid red; padding: 10px;'>";
echo "<div class='year-tabs' style='border: 2px solid blue; padding: 10px; margin-bottom: 10px;'>";
if (empty($years)) {
    echo '<span style="color: #666;">暂无记录</span>';
} else {
    foreach ($years as $index => $year) {
        echo "<button class='year-tab'>" . htmlspecialchars($year) . "年</button>";
    }
}
echo "</div>";

echo "<div class='year-actions' style='border: 2px solid green; padding: 10px; background: yellow;'>";
echo "<button type='button' class='btn btn-add' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-size: 16px;'>+ 新增记录</button>";
echo "</div>";
echo "</div>";

echo "<h2>检查 PHP 错误:</h2>";
$errors = error_get_last();
if ($errors) {
    echo "<pre style='color: red;'>" . print_r($errors, true) . "</pre>";
} else {
    echo "<p style='color: green;'>没有 PHP 错误</p>";
}
?>

