<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 数据库连接配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '数据库连接失败: ' . $e->getMessage()
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        case 'PUT':
            handlePut($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            throw new Exception('不支持的请求方法');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 处理 GET 请求 - 获取货品异常记录列表
function handleGet($pdo) {
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5000;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    $sql = "SELECT * FROM stock_sot WHERE 1=1";
    $params = [];
    
    // 日期范围过滤
    if ($startDate && $endDate) {
        $sql .= " AND date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }
    
    // 排序：按日期降序
    $sql .= " ORDER BY date DESC, id DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    // 绑定参数
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    // 格式化数据
    foreach ($data as &$item) {
        $item['quantity'] = number_format((float)$item['quantity'], 2, '.', '');
        $item['price'] = number_format((float)$item['price'], 2, '.', '');
        $item['total_price'] = number_format((float)$item['total_price'], 2, '.', '');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => count($data)
    ]);
}

// 处理 POST 请求 - 创建新的货品异常记录
function handlePost($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // 验证必填字段
    if (empty($input['date']) || empty($input['product_name'])) {
        throw new Exception('日期和货品名称为必填项');
    }
    
    // 确保数量为正数
    $quantity = isset($input['quantity']) ? abs(floatval($input['quantity'])) : 0;
    if ($quantity <= 0) {
        throw new Exception('数量必须大于0');
    }
    
    $price = isset($input['price']) ? floatval($input['price']) : 0;
    $totalPrice = $quantity * $price;
    
    // 开始事务
    $pdo->beginTransaction();
    
    try {
        // 1. 插入到 stock_sot 表
        $sql = "INSERT INTO stock_sot (
            date, product_code, product_name, quantity, 
            specification, price, total_price, category
        ) VALUES (
            :date, :product_code, :product_name, :quantity, 
            :specification, :price, :total_price, :category
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':date' => $input['date'],
            ':product_code' => $input['product_code'] ?? '',
            ':product_name' => $input['product_name'],
            ':quantity' => $quantity,
            ':specification' => $input['specification'] ?? '',
            ':price' => $price,
            ':total_price' => $totalPrice,
            ':category' => $input['category'] ?? ''
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // 2. 同步到 stockinout_data 表（作为出货记录）
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
            ':date' => $input['date'],
            ':time' => date('H:i:s'),
            ':code_number' => $input['product_code'] ?? '',
            ':product_name' => $input['product_name'],
            ':out_quantity' => $quantity,
            ':specification' => $input['specification'] ?? '',
            ':price' => $price,
            ':remark' => '货品异常 #' . $newId
        ]);
        
        // 提交事务
        $pdo->commit();
        
        // 获取新创建的记录
        $stmt = $pdo->prepare("SELECT * FROM stock_sot WHERE id = :id");
        $stmt->execute([':id' => $newId]);
        $newRecord = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => '记录创建成功，库存已更新',
            'data' => $newRecord
        ]);
        
    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollBack();
        throw $e;
    }
}

// 处理 PUT 请求 - 更新货品异常记录
function handlePut($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        throw new Exception('记录ID为必填项');
    }
    
    // 开始事务
    $pdo->beginTransaction();
    
    try {
        // 获取旧记录
        $stmt = $pdo->prepare("SELECT * FROM stock_sot WHERE id = :id");
        $stmt->execute([':id' => $input['id']]);
        $oldRecord = $stmt->fetch();
        
        if (!$oldRecord) {
            throw new Exception('记录不存在');
        }
        
        // 确保数量为正数
        if (isset($input['quantity'])) {
            $quantity = abs(floatval($input['quantity']));
            if ($quantity <= 0) {
                throw new Exception('数量必须大于0');
            }
            $input['quantity'] = $quantity;
        }
        
        // 重新计算总价
        if (isset($input['quantity']) || isset($input['price'])) {
            $quantity = isset($input['quantity']) ? $input['quantity'] : $oldRecord['quantity'];
            $price = isset($input['price']) ? floatval($input['price']) : $oldRecord['price'];
            $input['total_price'] = $quantity * $price;
        }
        
        // 1. 更新 stock_sot 表
        $updateFields = [];
        $params = [':id' => $input['id']];
        
        $allowedFields = ['date', 'product_code', 'product_name', 'quantity', 'specification', 'price', 'total_price', 'category'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $input[$field];
            }
        }
        
        if (!empty($updateFields)) {
            $sql = "UPDATE stock_sot SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        // 获取更新后的记录
        $stmt = $pdo->prepare("SELECT * FROM stock_sot WHERE id = :id");
        $stmt->execute([':id' => $input['id']]);
        $updatedRecord = $stmt->fetch();
        
        // 2. 更新 stockinout_data 表中对应的记录
        // 先删除旧的出货记录
        $stmt = $pdo->prepare("DELETE FROM stockinout_data WHERE remark = :remark");
        $stmt->execute([':remark' => '货品异常 #' . $input['id']]);
        
        // 重新插入出货记录
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
            ':date' => $updatedRecord['date'],
            ':time' => date('H:i:s'),
            ':code_number' => $updatedRecord['product_code'] ?? '',
            ':product_name' => $updatedRecord['product_name'],
            ':out_quantity' => $updatedRecord['quantity'],
            ':specification' => $updatedRecord['specification'] ?? '',
            ':price' => $updatedRecord['price'],
            ':remark' => '货品异常 #' . $input['id']
        ]);
        
        // 提交事务
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => '记录更新成功，库存已更新',
            'data' => $updatedRecord
        ]);
        
    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollBack();
        throw $e;
    }
}

// 处理 DELETE 请求 - 删除货品异常记录
function handleDelete($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        throw new Exception('记录ID为必填项');
    }
    
    // 开始事务
    $pdo->beginTransaction();
    
    try {
        // 获取要删除的记录信息
        $stmt = $pdo->prepare("SELECT * FROM stock_sot WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();
        
        if (!$record) {
            throw new Exception('记录不存在');
        }
        
        // 1. 删除 stock_sot 表中的记录
        $stmt = $pdo->prepare("DELETE FROM stock_sot WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // 2. 删除 stockinout_data 表中对应的出货记录
        $stmt = $pdo->prepare("DELETE FROM stockinout_data WHERE remark = :remark");
        $stmt->execute([':remark' => '货品异常 #' . $id]);
        
        // 提交事务
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => '记录删除成功，相关库存已恢复',
            'data' => $record
        ]);
        
    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollBack();
        throw $e;
    }
}
?>

