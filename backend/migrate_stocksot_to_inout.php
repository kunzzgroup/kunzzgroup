<?php
/**
 * 迁移脚本：将现有的 stock_sot 记录同步到 stockinout_data 表
 * 
 * 这个脚本会：
 * 1. 查找所有 stock_sot 记录
 * 2. 检查是否已在 stockinout_data 中存在
 * 3. 如果不存在，创建对应的出货记录
 */

// 数据库连接配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "开始迁移 stock_sot 数据到 stockinout_data...\n\n";
    
    // 获取所有 stock_sot 记录
    $stmt = $pdo->query("SELECT * FROM stock_sot ORDER BY id");
    $sotRecords = $stmt->fetchAll();
    
    echo "找到 " . count($sotRecords) . " 条 stock_sot 记录\n\n";
    
    $migratedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    foreach ($sotRecords as $record) {
        $sotId = $record['id'];
        $remark = '货品异常 #' . $sotId;
        
        // 检查是否已存在对应的 stockinout_data 记录
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM stockinout_data WHERE remark = :remark");
        $stmt->execute([':remark' => $remark]);
        $exists = $stmt->fetchColumn();
        
        if ($exists > 0) {
            echo "ID {$sotId}: 已存在，跳过\n";
            $skippedCount++;
            continue;
        }
        
        try {
            // 插入到 stockinout_data 表
            $sqlInout = "INSERT INTO stockinout_data (
                date, time, code_number, product_name, in_quantity, out_quantity, 
                target_system, specification, price, 
                product_remark_checked, remark_number, receiver, remark
            ) VALUES (
                :date, :time, :code_number, :product_name, 0, :out_quantity,
                'SOT', :specification, :price,
                0, NULL, 'SOT-货品异常', :remark
            )";
            
            $stmtInout = $pdo->prepare($sqlInout);
            $stmtInout->execute([
                ':date' => $record['date'],
                ':time' => '00:00:00', // 历史数据使用默认时间
                ':code_number' => $record['product_code'] ?? '',
                ':product_name' => $record['product_name'],
                ':out_quantity' => $record['quantity'],
                ':specification' => $record['specification'] ?? '',
                ':price' => $record['price'],
                ':remark' => $remark
            ]);
            
            echo "ID {$sotId}: 成功迁移 - {$record['product_name']} (数量: {$record['quantity']})\n";
            $migratedCount++;
            
        } catch (Exception $e) {
            echo "ID {$sotId}: 迁移失败 - " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
    echo "\n迁移完成！\n";
    echo "成功迁移: {$migratedCount} 条\n";
    echo "已存在跳过: {$skippedCount} 条\n";
    echo "失败: {$errorCount} 条\n";
    
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
    exit(1);
}
?>

