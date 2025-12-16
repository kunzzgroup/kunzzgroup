<?php
header("Content-Type: text/html; charset=utf-8");

// 数据库配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败：" . $e->getMessage());
}

echo "<h2>检查 freezer_category 数据</h2>";

// 1. 检查所有记录的 freezer_category 值
echo "<h3>1. 所有记录的 freezer_category 值统计：</h3>";
$sql = "SELECT 
    CASE 
        WHEN freezer_category IS NULL THEN 'NULL'
        WHEN freezer_category = '' THEN 'EMPTY'
        ELSE freezer_category
    END as category_status,
    COUNT(*) as count
FROM stock_data
GROUP BY category_status
ORDER BY count DESC";
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>freezer_category 状态</th><th>记录数量</th></tr>";
foreach ($results as $row) {
    echo "<tr><td>" . htmlspecialchars($row['category_status']) . "</td><td>" . $row['count'] . "</td></tr>";
}
echo "</table>";

// 2. 检查 J3 系统分配的记录的 freezer_category
echo "<h3>2. J3 系统分配的记录的 freezer_category 值：</h3>";
$sql = "SELECT 
    CASE 
        WHEN freezer_category IS NULL THEN 'NULL'
        WHEN freezer_category = '' THEN 'EMPTY'
        ELSE freezer_category
    END as category_status,
    COUNT(*) as count
FROM stock_data
WHERE (FIND_IN_SET('J3', system_assign) > 0 OR system_assign = 'J3')
GROUP BY category_status
ORDER BY count DESC";
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>freezer_category 状态</th><th>记录数量</th></tr>";
foreach ($results as $row) {
    echo "<tr><td>" . htmlspecialchars($row['category_status']) . "</td><td>" . $row['count'] . "</td></tr>";
}
echo "</table>";

// 3. 显示所有非空的 freezer_category 值
echo "<h3>3. 所有非空的 freezer_category 值（前50条）：</h3>";
$sql = "SELECT id, product_name, system_assign, freezer_category
FROM stock_data
WHERE freezer_category IS NOT NULL AND freezer_category != ''
ORDER BY id DESC
LIMIT 50";
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($results) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>产品名称</th><th>系统分配</th><th>冰箱分类</th></tr>";
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['system_assign'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['freezer_category']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'><strong>没有找到任何设置了 freezer_category 的记录！</strong></p>";
}

// 4. 检查是否有 J3 系统分配且设置了 freezer_category 的记录
echo "<h3>4. J3 系统分配且设置了 freezer_category 的记录：</h3>";
$sql = "SELECT id, product_name, system_assign, freezer_category
FROM stock_data
WHERE (FIND_IN_SET('J3', system_assign) > 0 OR system_assign = 'J3')
AND freezer_category IS NOT NULL AND freezer_category != ''
ORDER BY id DESC
LIMIT 50";
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($results) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>产品名称</th><th>系统分配</th><th>冰箱分类</th></tr>";
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['system_assign'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['freezer_category']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'><strong>没有找到任何 J3 系统分配且设置了 freezer_category 的记录！</strong></p>";
    echo "<p>这就是为什么过滤不起作用的原因。</p>";
}

// 5. 检查 freezer_category 字段是否存在
echo "<h3>5. 检查 freezer_category 字段结构：</h3>";
$sql = "DESCRIBE stock_data";
$stmt = $pdo->query($sql);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hasFreezerCategory = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'freezer_category') {
        $hasFreezerCategory = true;
        echo "<p>字段存在：</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>字段名</th><th>类型</th><th>允许NULL</th><th>默认值</th><th>注释</th></tr>";
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($col['Comment'] ?? '') . "</td>";
        echo "</tr>";
        echo "</table>";
        break;
    }
}
if (!$hasFreezerCategory) {
    echo "<p style='color: red;'><strong>错误：freezer_category 字段不存在！</strong></p>";
}
?>
