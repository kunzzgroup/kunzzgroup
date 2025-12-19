<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("需要登录");
}

$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');
$configFile = $isEnglish ? '../timeline_config_en.json' : '../timeline_config.json';

echo "<h1>检查 Timeline 配置文件</h1>";
echo "<p>当前语言: " . ($isEnglish ? 'English' : '中文') . "</p>";
echo "<p>配置文件路径: <strong>$configFile</strong></p>";

// 检查多个可能的路径
$possiblePaths = [
    $configFile,
    __DIR__ . '/../' . basename($configFile),
    '../' . basename($configFile),
    '../../' . basename($configFile),
    basename($configFile),
];

echo "<h2>1. 检查文件是否存在</h2>";
$foundFile = null;
foreach ($possiblePaths as $path) {
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    $writable = $exists ? is_writable($path) : false;
    
    echo "<p><strong>$path:</strong> ";
    echo $exists ? "✓ 存在" : "✗ 不存在";
    if ($exists) {
        echo " | " . ($readable ? "✓ 可读" : "✗ 不可读");
        echo " | " . ($writable ? "✓ 可写" : "✗ 不可写");
        $size = filesize($path);
        echo " | 大小: $size 字节";
        if (!$foundFile) {
            $foundFile = $path;
        }
    }
    echo "</p>";
}

if (!$foundFile) {
    echo "<p style='color: red; font-weight: bold;'>✗ 找不到配置文件！请检查文件路径。</p>";
    echo "<p>建议：确保文件在项目根目录，文件名为 <code>timeline_config.json</code> 或 <code>timeline_config_en.json</code></p>";
    exit;
}

echo "<h2>2. 读取文件内容</h2>";
$content = file_get_contents($foundFile);
echo "<p>文件内容长度: " . strlen($content) . " 字符</p>";

if (empty($content)) {
    echo "<p style='color: red;'>✗ 文件为空！</p>";
    exit;
}

echo "<h3>原始内容（前500字符）:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow: auto;'>";
echo htmlspecialchars(substr($content, 0, 500));
echo "</pre>";

echo "<h2>3. 解析 JSON</h2>";
$raw = json_decode($content, true);
if ($raw === null) {
    $error = json_last_error_msg();
    echo "<p style='color: red;'>✗ JSON 解析失败: $error</p>";
    echo "<p>JSON 错误代码: " . json_last_error() . "</p>";
    exit;
}

echo "<p style='color: green;'>✓ JSON 解析成功</p>";
echo "<p>数据类型: " . gettype($raw) . "</p>";

if (is_array($raw)) {
    echo "<p>数组键数量: " . count($raw) . "</p>";
    
    // 检查数组结构
    $keys = array_keys($raw);
    $isNumeric = array_keys($keys) === range(0, count($keys) - 1);
    $isAssoc = !$isNumeric;
    
    echo "<p>数组类型: " . ($isNumeric ? "索引数组（扁平结构）" : "关联数组（按年份分组）") . "</p>";
    
    echo "<h3>数组结构预览:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars(print_r(array_slice($raw, 0, 5, true), true));
    echo "</pre>";
    
    echo "<h2>4. 处理数据（模拟 aboutpage4upload.php 的逻辑）</h2>";
    $items = [];
    
    if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
        // 按年份分组的结构
        echo "<p>检测到：按年份分组的结构</p>";
        foreach ($raw as $yearKey => $entries) {
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                    $items[] = array_merge($entryArray, [ 
                        'year' => (string)$yearKey, 
                        'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 
                    ]);
                }
            } else {
                // 单个条目
                $entryArray = is_array($entries) ? $entries : [ 'title' => (string)$entries ];
                $items[] = array_merge($entryArray, [ 
                    'year' => (string)$yearKey, 
                    'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 
                ]);
            }
        }
    } else {
        // 扁平数组结构
        echo "<p>检测到：扁平数组结构</p>";
        $items = $raw;
    }
    
    echo "<p>处理后的记录数量: " . count($items) . "</p>";
    
    if (empty($items)) {
        echo "<p style='color: orange;'>⚠ 处理后没有记录！</p>";
        echo "<p>可能的原因：</p>";
        echo "<ul>";
        echo "<li>文件格式不正确</li>";
        echo "<li>数据结构不符合预期</li>";
        echo "<li>所有记录都被过滤掉了</li>";
        echo "</ul>";
    } else {
        echo "<h3>处理后的记录（前3条）:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow: auto;'>";
        echo htmlspecialchars(print_r(array_slice($items, 0, 3, true), true));
        echo "</pre>";
        
        // 提取年份
        $years = array_values(array_unique(array_map(function($it){ 
            return (string)($it['year'] ?? ''); 
        }, $items)));
        $years = array_filter($years, function($y) { return !empty($y); });
        $years = array_values($years);
        sort($years, SORT_NUMERIC);
        
        echo "<h3>提取的年份:</h3>";
        if (empty($years)) {
            echo "<p style='color: orange;'>⚠ 没有找到有效的年份！</p>";
        } else {
            echo "<p>年份列表: " . implode(', ', $years) . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ 数据不是数组格式</p>";
}

echo "<hr>";
echo "<h2>建议</h2>";
echo "<ul>";
if (!$foundFile) {
    echo "<li>确保配置文件在项目根目录</li>";
    echo "<li>检查文件名是否正确（timeline_config.json 或 timeline_config_en.json）</li>";
}
if (empty($items)) {
    echo "<li>检查文件内容格式是否正确</li>";
    echo "<li>确保文件包含有效的 JSON 数据</li>";
}
echo "<li>如果文件格式是按年份分组的，代码会自动转换</li>";
echo "<li>如果文件格式是扁平数组，确保每条记录都有 'year' 字段</li>";
echo "</ul>";

echo "<p><a href='aboutpage4upload.php'>返回 aboutpage4upload.php</a></p>";
?>

