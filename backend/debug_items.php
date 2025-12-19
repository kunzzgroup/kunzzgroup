<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("需要登录");
}

$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');
$configFileName = $isEnglish ? 'timeline_config_en.json' : 'timeline_config.json';

// 使用与 aboutpage4upload.php 相同的路径查找逻辑
$possiblePaths = [
    __DIR__ . '/../' . $configFileName,
    '../' . $configFileName,
    '../../' . $configFileName,
    $configFileName,
];

$configFile = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $configFile = $path;
        break;
    }
}

if (!$configFile) {
    $configFile = __DIR__ . '/../' . $configFileName;
}

echo "<h1>调试 items 变量</h1>";
echo "<p>配置文件: <strong>$configFile</strong></p>";
echo "<p>文件存在: " . (file_exists($configFile) ? "✓ 是" : "✗ 否") . "</p>";
echo "<p>文件可读: " . (is_readable($configFile) ? "✓ 是" : "✗ 否") . "</p>";

// 模拟 aboutpage4upload.php 的读取逻辑
$items = [];
if (file_exists($configFile) && is_readable($configFile)) {
    $content = @file_get_contents($configFile);
    if ($content !== false) {
        $raw = json_decode($content, true);
        if ($raw === null && json_last_error() !== JSON_ERROR_NONE) {
            echo "<p style='color: red;'>JSON 解析失败: " . json_last_error_msg() . "</p>";
            $raw = [];
        }
        $raw = $raw ?: [];
        
        echo "<h2>1. 原始数据</h2>";
        echo "<p>原始数据数量: " . count($raw) . "</p>";
        echo "<p>数组类型: " . (is_array($raw) && array_keys($raw) === range(0, count($raw) - 1) ? "扁平数组" : "关联数组") . "</p>";
        
        // 检查数组结构
        if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
            echo "<p style='color: blue;'>检测到：按年份分组的结构</p>";
            foreach ($raw as $yearKey => $entries) {
                if (is_array($entries)) {
                    if (array_keys($entries) === range(0, count($entries) - 1)) {
                        foreach ($entries as $entry) {
                            $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                            $items[] = array_merge($entryArray, [ 
                                'year' => (string)$yearKey, 
                                'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 
                            ]);
                        }
                    } else {
                        $entryArray = $entries;
                        $items[] = array_merge($entryArray, [ 
                            'year' => (string)$yearKey, 
                            'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 
                        ]);
                    }
                } else {
                    $items[] = [
                        'title' => (string)$entries,
                        'year' => (string)$yearKey,
                        'month' => 0
                    ];
                }
            }
        } else {
            echo "<p style='color: green;'>检测到：扁平数组结构</p>";
            $items = $raw;
        }
        
        echo "<h2>2. 处理后的 items</h2>";
        echo "<p>items 数量: " . count($items) . "</p>";
        
        if (empty($items)) {
            echo "<p style='color: red;'>✗ items 为空！</p>";
        } else {
            echo "<p style='color: green;'>✓ items 有 " . count($items) . " 条记录</p>";
            echo "<h3>第一条记录:</h3>";
            echo "<pre>" . print_r($items[0], true) . "</pre>";
            
            // 提取年份
            $years = array_values(array_unique(array_map(function($it){ 
                return (string)($it['year'] ?? ''); 
            }, $items)));
            $years = array_filter($years, function($y) { return !empty($y); });
            $years = array_values($years);
            sort($years, SORT_NUMERIC);
            
            echo "<h2>3. 提取的年份</h2>";
            if (empty($years)) {
                echo "<p style='color: red;'>✗ 无法提取年份！</p>";
                echo "<p>第一条记录的 year 字段: " . ($items[0]['year'] ?? '不存在') . "</p>";
            } else {
                echo "<p style='color: green;'>✓ 提取到年份: " . implode(', ', $years) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>无法读取文件内容</p>";
    }
} else {
    echo "<p style='color: red;'>文件不存在或不可读</p>";
}

echo "<hr>";
echo "<p><a href='aboutpage4upload.php'>返回 aboutpage4upload.php</a></p>";
?>

