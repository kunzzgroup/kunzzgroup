<?php
// 此脚本用于修复数据库中因为系统 bug 而导致商品名字里带有 &amp; 的错误数据
// 修复后，所有的 &amp; 都会被转换回正常的 &，例如 A&amp;W 会变回 A&W，从而解决库存列表出现两条重复记录的问题。

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<html><head><meta charset='utf-8'><title>修复数据</title></head><body>";
echo "<h2>自动修复数据库中的 &amp; 乱码问题</h2>";

try {
    // 连接数据库
    $pdo = new PDO('mysql:host=localhost;dbname=u690174784_kunzz;charset=utf8mb4', 'u690174784_kunzz', 'Kunzz1688');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 需要清理的表列表
    $tables = [
        'stock_data',
        'stockinout_data',
        'j1stockedit_data',
        'j2stockedit_data',
        'j3stockedit_data',
        'j1stockeditmobile_data',
        'j2stockeditmobile_data',
        'j3stockeditmobile_data'
    ];

    $totalUpdated = 0;

    echo "<ul>";
    foreach ($tables as $table) {
        try {
            // 查找带有 &amp; 的名字，并替换为 &
            $sql = "UPDATE $table SET product_name = REPLACE(product_name, '&amp;', '&') WHERE product_name LIKE '%&amp;%'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $affected = $stmt->rowCount();
            $totalUpdated += $affected;
            
            if ($affected > 0) {
                echo "<li style='color: green;'>成功更新表 <b>$table</b>: 修复了 $affected 条记录。</li>";
            } else {
                echo "<li style='color: gray;'>表 <b>$table</b> 中没有发现需要修复的记录。</li>";
            }
        } catch (PDOException $e) {
            echo "<li style='color: red;'>检查表 <b>$table</b> 失败: " . $e->getMessage() . "</li>";
        }
    }
    echo "</ul>";

    echo "<h3>修复完成！总共修复了 <span style='color: blue;'>$totalUpdated</span> 条错乱数据。</h3>";
    echo "<p>现在请您返回 J2 手机端，刷新页面（或者重新进入），您应该会看到 <b>A&amp;W</b> 和 <b>A&W</b> 已经成功合并成了一项！</p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>数据库连接错误: </h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "</body></html>";
