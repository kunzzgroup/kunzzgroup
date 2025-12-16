<?php
ob_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 数据库配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $e->getMessage(), "error_details" => $e->getMessage()]);
    exit;
}

// 调试信息
error_log("数据库连接成功 - stockeditapi");
error_log("请求方法: " . $_SERVER['REQUEST_METHOD']);

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

function sendResponse($success, $message = "", $data = null) {
    ob_end_clean();
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

function saveToJ1Table($pdo, $data, $mainRecordId = null) {
    try {
        // 保存到 j1stockinout_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j1stockinout_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        
        // 将主表的出库数量作为J1表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        // 使用更精确的计算方法，避免浮点数精度问题
        $totalValue = round($outQuantity * $price, 2);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $outQuantity, // 作为入库数量
            0, // 出库数量为0
            $data['specification'] ?? null,
            $price,
            $totalValue,
            'AUTO_INBOUND', // 改为入库类型
            $data['receiver'],
            $data['remark'] ?? null,
            $mainRecordId,
            'from_main' // 标记来源
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J1表失败: " . $e->getMessage());
        return false;
    }
}

function saveToJ2Table($pdo, $data, $mainRecordId = null) {
    try {
        // 保存到 j2stockinout_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j2stockinout_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        
        // 将主表的出库数量作为J2表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        // 使用更精确的计算方法，避免浮点数精度问题
        $totalValue = round($outQuantity * $price, 2);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $outQuantity, // 作为入库数量
            0, // 出库数量为0
            $data['specification'] ?? null,
            $price,
            $totalValue,
            'AUTO_INBOUND', // 改为入库类型
            $data['receiver'],
            $data['remark'] ?? null,
            $mainRecordId,
            'from_main' // 标记来源
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J2表失败: " . $e->getMessage());
        return false;
    }
}

function saveToJ1EditTable($pdo, $data, $mainRecordId = null) {
    try {
        // 保存到 j1stockedit_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j1stockedit_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        
        // 将主表的出库数量作为J1Edit表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $outQuantity, // 作为入库数量
            0, // 出库数量为0
            $data['specification'] ?? null,
            $price,
            $data['receiver'],
            $data['remark'] ?? null,
            'j1' // 设置为j1
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J1Edit表失败: " . $e->getMessage());
        return false;
    }
}

function saveToJ2EditTable($pdo, $data, $mainRecordId = null) {
    try {
        // 保存到 j2stockedit_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j2stockedit_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        
        // 将主表的出库数量作为J2Edit表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $outQuantity, // 作为入库数量
            0, // 出库数量为0
            $data['specification'] ?? null,
            $price,
            $data['receiver'],
            $data['remark'] ?? null,
            'j2' // 设置为j2
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J2Edit表失败: " . $e->getMessage());
        return false;
    }
}

function saveToJ3Table($pdo, $data, $mainRecordId = null) {
    try {
        // 保存到 j3stockinout_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j3stockinout_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        
        // 将主表的出库数量作为J3表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        // 使用更精确的计算方法，避免浮点数精度问题
        $totalValue = round($outQuantity * $price, 2);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $outQuantity, // 作为入库数量
            0, // 出库数量为0
            $data['specification'] ?? null,
            $price,
            $totalValue,
            'AUTO_INBOUND', // 改为入库类型
            $data['receiver'],
            $data['remark'] ?? null,
            $mainRecordId,
            'from_main' // 标记来源
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J3表失败: " . $e->getMessage());
        return false;
    }
}

function saveToJ3EditTable($pdo, $data, $mainRecordId = null) {
    try {
        // 保存到 j3stockedit_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j3stockedit_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        
        // 将主表的出库数量作为J3Edit表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $outQuantity, // 作为入库数量
            0, // 出库数量为0
            $data['specification'] ?? null,
            $price,
            $data['receiver'],
            $data['remark'] ?? null,
            'j3' // 设置为j3
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J3Edit表失败: " . $e->getMessage());
        return false;
    }
}

// 路由处理
switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    case 'PATCH':
        handlePatch();
        break;
    default:
        sendResponse(false, "不支持的请求方法");
}

// 处理 GET 请求 - 获取数据
function handleGet() {
    global $pdo;
    
    $action = $_GET['action'] ?? 'list';

    if ($action === 'approve') {
        handleApprove();
        return;
    }
    
    switch ($action) {
        case 'list':
            // 获取所有进出库数据
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $searchDate = $_GET['search_date'] ?? null;
            $receiver = $_GET['receiver'] ?? null;
            $productCode = $_GET['product_code'] ?? null;  // 这行已存在，保持不变
            $productName = $_GET['product_name'] ?? null;

            // 如果没有提供日期范围，默认显示一年内的数据
            if (!$startDate && !$endDate && !$searchDate) {
                $startDate = date('Y-m-d', strtotime('-1 year')); // 一年前的今天
                $endDate = date('Y-m-d'); // 今天
            }

            $sql = "SELECT * FROM stockinout_data WHERE 1=1";
            $params = [];
            
            if ($searchDate) {
                $sql .= " AND date = ?";
                $params[] = $searchDate;
            } elseif ($startDate && $endDate) {
                $sql .= " AND date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            if ($receiver) {
                $sql .= " AND receiver LIKE ?";
                $params[] = "%$receiver%";
            }

            if ($productCode) {
                $sql .= " AND code_number LIKE ?";  // 修改这里：从product_code改为code_number
                $params[] = "%$productCode%";
            }

            if ($productName) {
                $sql .= " AND product_name LIKE ?";
                $params[] = "%$productName%";
            }
            
            $sql .= " ORDER BY date ASC, time ASC";
            $sql .= " LIMIT 5000";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute($params);
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 为每条记录添加计算字段
                foreach ($records as &$record) {
                    // 计算库存余额
                    $inQty = floatval($record['in_quantity'] ?? 0);
                    $outQty = floatval($record['out_quantity'] ?? 0);
                    $record['balance_quantity'] = $inQty - $outQty;
                    
                    // 计算总价值
                    $price = floatval($record['price'] ?? 0);
                    $record['in_value'] = $inQty * $price;
                    $record['out_value'] = $outQty * $price;
                    $record['balance_value'] = $record['balance_quantity'] * $price;
                    
                    // 格式化数字
                    $record['in_quantity'] = $inQty;
                    $record['out_quantity'] = $outQty;
                    $record['balance_quantity'] = $record['balance_quantity'];
                    $record['price'] = $price;
                    $record['in_value'] = $record['in_value'];
                    $record['out_value'] = $record['out_value'];
                    $record['balance_value'] = $record['balance_value'];
                }

                $record['in_quantity'] = $inQty;
                    $record['out_quantity'] = $outQty;
                    $record['balance_quantity'] = $record['balance_quantity'];
                    $record['price'] = $price;
                    $record['in_value'] = $record['in_value'];
                    $record['out_value'] = $record['out_value'];
                    $record['balance_value'] = $record['balance_value'];
                
                sendResponse(true, "进出库数据获取成功，共找到 " . count($records) . " 条记录", $records);
            } catch (PDOException $e) {
                sendResponse(false, "查询数据失败：" . $e->getMessage());
            }
            break;
            
        case 'summary':
            // 获取汇总数据
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            
            $sql = "SELECT 
                        COUNT(*) as total_records,
                        COUNT(DISTINCT product_code) as total_products,
                        COUNT(DISTINCT supplier) as total_suppliers,
                        SUM(in_quantity * price) as total_in_value,
                        SUM(out_quantity * price) as total_out_value,
                        SUM((in_quantity - out_quantity) * price) as total_balance_value,
                        SUM(in_quantity) as total_in_quantity,
                        SUM(out_quantity) as total_out_quantity,
                        SUM(in_quantity - out_quantity) as total_balance_quantity
                    FROM stockinout_data WHERE 1=1";
            $params = [];
            
            if ($startDate && $endDate) {
                $sql .= " AND date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 格式化数据
            foreach (['total_in_value', 'total_out_value', 'total_balance_value', 'total_in_quantity', 'total_out_quantity', 'total_balance_quantity'] as $field) {
                $summary[$field] = floatval($summary[$field] ?? 0);
            }
            
            sendResponse(true, "汇总数据获取成功", $summary);
            break;
            
        case 'single':
            // 获取单条记录
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, "缺少记录ID");
            }
            
            $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                sendResponse(true, "记录获取成功", $record);
            } else {
                sendResponse(false, "记录不存在");
            }
            break;
            
        case 'suppliers':
            // 获取所有供应商列表
            $stmt = $pdo->prepare("SELECT DISTINCT supplier FROM stockinout_data ORDER BY supplier");
            $stmt->execute();
            $suppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendResponse(true, "供应商列表获取成功", $suppliers);
            break;
            
        case 'products':
            // 获取所有产品列表
            $stmt = $pdo->prepare("SELECT DISTINCT product_code, product_name FROM stockinout_data ORDER BY product_code");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "产品列表获取成功", $products);
            break;

        case 'codenumbers':
            // 获取所有唯一的code_number和对应的product_name列表
            $stmt = $pdo->prepare("SELECT DISTINCT product_code as code_number, product_name FROM stock_data WHERE product_code IS NOT NULL AND product_code != '' ORDER BY product_code");
            $stmt->execute();
            $codeNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "编号列表获取成功", $codeNumbers);
            break;

        case 'product_by_code':
            // 根据code_number获取对应的product_name和specification
            $codeNumber = $_GET['code_number'] ?? null;
            if (!$codeNumber) {
                sendResponse(false, "缺少编号参数");
            }
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, specification FROM stock_data WHERE product_code = ? LIMIT 1");
            $stmt->execute([$codeNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                sendResponse(true, "产品名称获取成功", [
                    'product_name' => $result['product_name'],
                    'specification' => $result['specification']
                ]);
            } else {
                sendResponse(false, "未找到对应的产品名称");
            }
            break;
        
        case 'products_list':
            // 获取所有唯一的产品名称和对应的product_code列表
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, product_code FROM stock_data WHERE product_name IS NOT NULL AND product_name != '' ORDER BY product_name");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "产品列表获取成功", $products);
            break;

        case 'code_by_product':
            // 根据product_name获取对应的product_code和specification
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_code, specification FROM stock_data WHERE product_name = ? LIMIT 1");
            $stmt->execute([$productName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                sendResponse(true, "产品编号获取成功", [
                    'product_code' => $result['product_code'],
                    'specification' => $result['specification']
                ]);
            } else {
                sendResponse(false, "未找到对应的产品编号");
            }
            break;
            
        case 'product_prices':
            // 获取指定产品的所有进货价格
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            $sql = "SELECT DISTINCT price 
                    FROM stockinout_data 
                    WHERE product_name = ? AND in_quantity > 0
                    ORDER BY price DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName]);
            $prices = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendResponse(true, "产品价格列表获取成功", $prices);
            break;

        case 'product_stock':
            // 获取指定产品的库存信息
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            $sql = "SELECT 
                        SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                        (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as available_stock
                    FROM stockinout_data 
                    WHERE product_name = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName]);
            $stockData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stockData) {
                $result = [
                    'total_in' => floatval($stockData['total_in'] ?? 0),
                    'total_out' => floatval($stockData['total_out'] ?? 0),
                    'available_stock' => floatval($stockData['available_stock'] ?? 0),
                    'current_stock' => floatval($stockData['available_stock'] ?? 0) // 别名
                ];
                sendResponse(true, "产品库存信息获取成功", $result);
            } else {
                sendResponse(true, "产品库存信息获取成功", [
                    'total_in' => 0,
                    'total_out' => 0, 
                    'available_stock' => 0,
                    'current_stock' => 0
                ]);
            }
            break;

        case 'product_stock_by_price':
            // 获取指定产品和价格的库存信息
            $productName = $_GET['product_name'] ?? null;
            $price = $_GET['price'] ?? null;
            
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            if ($price === null || $price === '') {
                sendResponse(false, "缺少价格参数");
            }
            
            $sql = "SELECT 
                        SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                        (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as available_stock
                    FROM stockinout_data 
                    WHERE product_name = ? AND price = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName, $price]);
            $stockData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stockData) {
                $result = [
                    'total_in' => floatval($stockData['total_in'] ?? 0),
                    'total_out' => floatval($stockData['total_out'] ?? 0),
                    'available_stock' => floatval($stockData['available_stock'] ?? 0),
                    'current_stock' => floatval($stockData['available_stock'] ?? 0)
                ];
                sendResponse(true, "产品价格库存信息获取成功", $result);
            } else {
                sendResponse(true, "产品价格库存信息获取成功", [
                    'total_in' => 0,
                    'total_out' => 0, 
                    'available_stock' => 0,
                    'current_stock' => 0
                ]);
            }
            break;

        case 'product_prices_with_stock':
            // 获取指定产品的价格列表，并检查每个价格对应的库存是否足够
            $productName = $_GET['product_name'] ?? null;
            $requiredQty = floatval($_GET['required_qty'] ?? 0);
            
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            if ($requiredQty <= 0) {
                sendResponse(false, "出库数量必须大于0");
            }
            
            try {
                // 获取该产品所有不同价格的库存情况
                $sql = "SELECT 
                            price,
                            SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                            SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                            (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                            SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as available_stock
                        FROM stockinout_data 
                        WHERE product_name = ? AND price > 0
                        GROUP BY price
                        HAVING available_stock > 0
                        ORDER BY price DESC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$productName]);
                $priceStockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 处理结果，确保数据格式正确
                $result = [];
                foreach ($priceStockData as $row) {
                    $availableStock = floatval($row['available_stock']);
                    // 保留原始价格精度，不进行格式化
                    $price = $row['price'];
                    
                    $result[] = [
                        'price' => $price,
                        'available_stock' => $availableStock,
                        'total_in' => floatval($row['total_in']),
                        'total_out' => floatval($row['total_out']),
                        'is_sufficient' => $availableStock >= $requiredQty
                    ];
                }
                
                sendResponse(true, "产品价格库存信息获取成功", $result);
                
            } catch (PDOException $e) {
                sendResponse(false, "查询价格库存信息失败：" . $e->getMessage());
            }
            break;

        case 'export':
            // 先清除之前的输出缓冲
            ob_end_clean();
            
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';
            $includeIn = $_GET['include_in'] ?? '1';
            $includeOut = $_GET['include_out'] ?? '1';
            
            // 构建查询条件
            $conditions = ["1=1"];
            $params = [];
            
            if ($startDate) {
                $conditions[] = "date >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $conditions[] = "date <= ?";
                $params[] = $endDate;
            }
            
            // 根据选择的数据类型添加条件
            $typeConditions = [];
            if ($includeIn === '1') {
                $typeConditions[] = "in_quantity > 0";
            }
            if ($includeOut === '1') {
                $typeConditions[] = "out_quantity > 0";
            }
            
            if (!empty($typeConditions)) {
                $conditions[] = "(" . implode(" OR ", $typeConditions) . ")";
            }
            
            // 执行查询
            $sql = "SELECT * FROM stockinout_data WHERE " . implode(" AND ", $conditions) . " ORDER BY date ASC, time ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 生成CSV格式的Excel文件
            $filename = 'stock_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            // 设置响应头
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            // 输出BOM以支持中文
            echo "\xEF\xBB\xBF";
            
            // 打开输出流
            $output = fopen('php://output', 'w');
            
            // 写入表头
            $headers = [
                '日期', '时间', '产品编号', '产品名称', '入库数量', '出库数量', 
                '目标系统', '规格单位', '价格', '总价值', '收货人', '备注'
            ];
            fputcsv($output, $headers);
            
            // 写入数据
            foreach ($records as $record) {
                $inQty = floatval($record['in_quantity'] ?? 0);
                $outQty = floatval($record['out_quantity'] ?? 0);
                $price = floatval($record['price'] ?? 0);
                $netQty = $inQty - $outQty;
                // 使用更精确的计算方法，避免浮点数精度问题
                $totalValue = round($netQty * $price, 2);
                
                $row = [
                    $record['date'],
                    $record['time'],
                    $record['code_number'] ?? '',
                    $record['product_name'],
                    number_format($inQty, 2),
                    number_format($outQty, 2),
                    strtoupper($record['target_system'] ?? ''),
                    $record['specification'] ?? '',
                    'RM ' . number_format($price, 2),
                    'RM ' . number_format($totalValue, 2),
                    $record['receiver'],
                    $record['remark'] ?? ''
                ];
                fputcsv($output, $row);
            }
            
            fclose($output);
            exit; // 重要：退出脚本，避免额外输出
            break;

        case 'remark_numbers':
            // 获取所有唯一的备注编号
            $stmt = $pdo->prepare("SELECT DISTINCT remark_number FROM stockinout_data WHERE remark_number IS NOT NULL AND remark_number != '' ORDER BY remark_number");
            $stmt->execute();
            $remarkNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendResponse(true, "备注编号列表获取成功", $remarkNumbers);
            break;
            
        default:
            sendResponse(false, "无效的操作");
    }
}

// 处理 POST 请求 - 添加新记录（修改版支持双重保存）
function handlePost() {
    global $pdo, $data;
    
    if (!$data) {
        sendResponse(false, "无效的数据格式");
    }
    
    // 验证必填字段
    $required_fields = ['date', 'time', 'product_name', 'receiver'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }

    // 验证产品名称是否存在于数据库中
    if (!empty($data['product_name'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_data WHERE product_name = ?");
        $stmt->execute([$data['product_name']]);
        if ($stmt->fetchColumn() == 0) {
            sendResponse(false, "产品名称不存在，请选择有效的产品");
        }
    }

    // 验证产品编号是否存在于数据库中
    if (!empty($data['code_number'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_data WHERE product_code = ?");
        $stmt->execute([$data['code_number']]);
        if ($stmt->fetchColumn() == 0) {
            sendResponse(false, "产品编号不存在，请选择有效的编号");
        }
    }

    // 验证 target_system 字段
    if (!empty($data['target_system']) && !in_array($data['target_system'], ['j1', 'j2', 'j3', 'central'])) {
        sendResponse(false, "目标系统只能是 j1、j2、j3 或 central");
    }

    // 验证数量字段
    $inQuantity = floatval($data['in_quantity'] ?? 0);
    $outQuantity = floatval($data['out_quantity'] ?? 0);

    if ($inQuantity < 0 || $outQuantity < 0) {
        sendResponse(false, "数量不能为负数");
    }

    if ($inQuantity == 0 && $outQuantity == 0) {
        sendResponse(false, "入库数量和出库数量不能同时为0");
    }

    if ($inQuantity > 0 && $outQuantity > 0) {
        sendResponse(false, "入库数量和出库数量不能同时大于0");
    }
    
    try {
        // 开始事务
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO stockinout_data 
                (date, time, product_name, receiver, in_quantity, out_quantity, 
                specification, price, code_number, remark, target_system, product_remark_checked, remark_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['product_name'],
            $data['receiver'],
            $data['in_quantity'] ?? 0,
            $data['out_quantity'] ?? 0,
            $data['specification'] ?? null,
            $data['price'] ?? 0,
            $data['code_number'] ?? null,
            $data['remark'] ?? null,
            $data['target_system'] ?? null,
            $data['product_remark_checked'] ?? 0,
            $data['remark_number'] ?? ''
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // 检查是否为出库记录（出库数量大于0）
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        // 位置1：POST 请求处理中的出库逻辑 (大约第180行附近)
        if ($outQuantity > 0) {
            $targetSystem = $data['target_system'] ?? 'j1'; // 默认j1
            
            if ($targetSystem === 'j1') {
                // 保存到J1表
                $j1Id = saveToJ1Table($pdo, $data, $newId);
                if (!$j1Id) {
                    $pdo->rollBack();
                    sendResponse(false, "保存到J1表失败，操作已回滚");
                }
                
                // 同时保存到J1Edit表
                $j1EditId = saveToJ1EditTable($pdo, $data, $newId);
                if (!$j1EditId) {
                    $pdo->rollBack();
                    sendResponse(false, "保存到J1Edit表失败，操作已回滚");
                }
                
                error_log("出库记录已保存到J1表，J1记录ID: " . $j1Id . "，J1Edit记录ID: " . $j1EditId);
            } elseif ($targetSystem === 'j2') {
                // 保存到J2表
                $j2Id = saveToJ2Table($pdo, $data, $newId);
                if (!$j2Id) {
                    $pdo->rollBack();
                    sendResponse(false, "保存到J2表失败，操作已回滚");
                }
                
                // 同时保存到J2Edit表
                $j2EditId = saveToJ2EditTable($pdo, $data, $newId);
                if (!$j2EditId) {
                    $pdo->rollBack();
                    sendResponse(false, "保存到J2Edit表失败，操作已回滚");
                }
                
                error_log("出库记录已保存到J2表，J2记录ID: " . $j2Id . "，J2Edit记录ID: " . $j2EditId);
            } elseif ($targetSystem === 'j3') {
                // 保存到J3表
                $j3Id = saveToJ3Table($pdo, $data, $newId);
                if (!$j3Id) {
                    $pdo->rollBack();
                    sendResponse(false, "保存到J3表失败，操作已回滚");
                }
                
                // 同时保存到J3Edit表
                $j3EditId = saveToJ3EditTable($pdo, $data, $newId);
                if (!$j3EditId) {
                    $pdo->rollBack();
                    sendResponse(false, "保存到J3Edit表失败，操作已回滚");
                }
                
                error_log("出库记录已保存到J3表，J3记录ID: " . $j3Id . "，J3Edit记录ID: " . $j3EditId);
            } elseif ($targetSystem === 'central') {
                // Central 选项：不保存到其他表，只保存在主表
                error_log("出库记录仅保存在主表 (Central)");
            }
        }
        
        // 提交事务
        $pdo->commit();
        
        // 获取新插入的记录
        $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        $newRecord['approval_status'] = (!empty($newRecord['approver'])) ? 'approved' : 'pending';
        
        $message = "进出库记录添加成功";
        if ($outQuantity > 0) {
            if ($targetSystem === 'central') {
                $message .= "，已保存到Central系统";
            } elseif ($targetSystem === 'j1') {
                $message .= "，已同时保存到J1入库表";
            } elseif ($targetSystem === 'j2') {
                $message .= "，已同时保存到J2入库表";
            } elseif ($targetSystem === 'j3') {
                $message .= "，已同时保存到J3入库表";
            } else {
                $message .= "，已同时保存到" . strtoupper($targetSystem) . "出库表";
            }
        }
        
        sendResponse(true, $message, $newRecord);
        
    } catch (PDOException $e) {
        // 回滚事务
        $pdo->rollBack();
        error_log("数据库错误: " . $e->getMessage());
        error_log("错误代码: " . $e->getCode());
        error_log("错误文件: " . $e->getFile() . " 行: " . $e->getLine());
        sendResponse(false, "添加记录失败：" . $e->getMessage());
    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollBack();
        error_log("一般错误: " . $e->getMessage());
        sendResponse(false, "添加记录失败：" . $e->getMessage());
    }
}

// 处理批准请求
function handleApprove() {
    global $pdo, $data;
    
    // 检查用户权限
    session_start();
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, "用户未登录");
    }
    
    // 检查用户是否使用了允许的注册码
    $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userCode = $stmt->fetchColumn();

    if (!$userCode || !in_array($userCode, $allowedCodes)) {
        sendResponse(false, "您没有权限执行此操作");
    }
    
    if (!$data || !isset($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }
    
    $id = $data['id'];
    $approver = $_SESSION['username'] ?? 'System';
    
    try {
        $sql = "UPDATE stockinout_data SET approver = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$approver, $id]);
        
        if ($stmt->rowCount() > 0) {
            // 获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ?");
            $stmt->execute([$id]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            $updatedRecord['approval_status'] = 'approved';
            
            sendResponse(true, "记录批准成功", $updatedRecord);
        } else {
            sendResponse(false, "记录不存在");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "批准失败：" . $e->getMessage());
    }
}

// 处理 PUT 请求 - 更新记录
function handlePut() {
    global $pdo, $data;
    
    if (!$data || !isset($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }
    
    // 验证必填字段
    $required_fields = ['date', 'time', 'product_name', 'receiver'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }

    // 验证产品名称是否存在于数据库中
    if (!empty($data['product_name'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_data WHERE product_name = ?");
        $stmt->execute([$data['product_name']]);
        if ($stmt->fetchColumn() == 0) {
            sendResponse(false, "产品名称不存在，请选择有效的产品");
        }
    }

    // 验证产品编号是否存在于数据库中
    if (!empty($data['code_number'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_data WHERE product_code = ?");
        $stmt->execute([$data['code_number']]);
        if ($stmt->fetchColumn() == 0) {
            sendResponse(false, "产品编号不存在，请选择有效的编号");
        }
    }
    
    try {
        // 先获取原始记录，用于比较target_system是否发生变化
        $originalStmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ?");
        $originalStmt->execute([$data['id']]);
        $originalRecord = $originalStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$originalRecord) {
            sendResponse(false, "记录不存在");
        }
        
        $originalTargetSystem = $originalRecord['target_system'] ?? 'j1';
        $newTargetSystem = $data['target_system'] ?? 'j1';
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        
        // 开始事务
        $pdo->beginTransaction();
        
        // 更新主表记录
        $sql = "UPDATE stockinout_data 
                SET date = ?, time = ?, product_name = ?, receiver = ?,
                    in_quantity = ?, out_quantity = ?, 
                    specification = ?, price = ?, code_number = ?, remark = ?, 
                    target_system = ?, product_remark_checked = ?, remark_number = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['date'],
            $data['time'],
            $data['product_name'],
            $data['receiver'],
            $data['in_quantity'] ?? 0,
            $data['out_quantity'] ?? 0,
            $data['specification'] ?? null,
            $data['price'] ?? 0,
            $data['code_number'] ?? null,
            $data['remark'] ?? null,
            $data['target_system'] ?? null,
            $data['product_remark_checked'] ?? 0,
            $data['remark_number'] ?? '',
            $data['id']
        ]);
        
        // 如果是出库记录，需要处理J1/J2表的同步
        if ($outQuantity > 0) {
            // 使用更精确的计算方法，避免浮点数精度问题
            $totalValue = round($outQuantity * floatval($data['price'] ?? 0), 2);
            
            // 如果target_system发生了变化，需要先清理旧记录，再创建新记录
            if ($originalTargetSystem !== $newTargetSystem) {
                error_log("Target system changed from $originalTargetSystem to $newTargetSystem");
                
                // 清理旧的记录
                if ($originalTargetSystem === 'j1') {
                    // 删除J1表中的记录
                    $j1DeleteSql = "DELETE FROM j1stockinout_data WHERE main_record_id = ?";
                    $j1DelStmt = $pdo->prepare($j1DeleteSql);
                    $j1DelStmt->execute([$data['id']]);
                    
                    // 删除J1Edit表中的记录
                    $j1EditDeleteSql = "DELETE FROM j1stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j1'";
                    $j1EditDelStmt = $pdo->prepare($j1EditDeleteSql);
                    $j1EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                    
                    error_log("已清理J1表和J1Edit表中的旧记录");
                } elseif ($originalTargetSystem === 'j2') {
                    // 删除J2表中的记录
                    $j2DeleteSql = "DELETE FROM j2stockinout_data WHERE main_record_id = ?";
                    $j2DelStmt = $pdo->prepare($j2DeleteSql);
                    $j2DelStmt->execute([$data['id']]);
                    
                    // 删除J2Edit表中的记录
                    $j2EditDeleteSql = "DELETE FROM j2stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j2'";
                    $j2EditDelStmt = $pdo->prepare($j2EditDeleteSql);
                    $j2EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                    
                    error_log("已清理J2表和J2Edit表中的旧记录");
                } elseif ($originalTargetSystem === 'j3') {
                    // 删除J3表中的记录
                    $j3DeleteSql = "DELETE FROM j3stockinout_data WHERE main_record_id = ?";
                    $j3DelStmt = $pdo->prepare($j3DeleteSql);
                    $j3DelStmt->execute([$data['id']]);
                    
                    // 删除J3Edit表中的记录
                    $j3EditDeleteSql = "DELETE FROM j3stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j3'";
                    $j3EditDelStmt = $pdo->prepare($j3EditDeleteSql);
                    $j3EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                    
                    error_log("已清理J3表和J3Edit表中的旧记录");
                }
            }
            
            // 根据新的target_system创建或更新记录
            if ($newTargetSystem === 'j1') {
                // 检查J1表中是否已存在记录
                $j1CheckSql = "SELECT COUNT(*) FROM j1stockinout_data WHERE main_record_id = ?";
                $j1CheckStmt = $pdo->prepare($j1CheckSql);
                $j1CheckStmt->execute([$data['id']]);
                $j1Exists = $j1CheckStmt->fetchColumn() > 0;
                
                if ($j1Exists) {
                    // 更新J1stockinout_data表
                    $j1UpdateSql = "UPDATE j1stockinout_data 
                                    SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                        in_quantity = ?, out_quantity = ?, specification = ?, price = ?, total_value = ?, receiver = ?, remark = ?, target_system = ?
                                    WHERE main_record_id = ?";
                    
                    $j1Stmt = $pdo->prepare($j1UpdateSql);
                    $j1Stmt->execute([
                        $data['date'], 
                        $data['time'], 
                        $data['code_number'] ?? null, 
                        $data['product_name'],
                        $outQuantity, 
                        0, 
                        $data['specification'] ?? null, 
                        floatval($data['price'] ?? 0), 
                        $totalValue,
                        $data['receiver'] ?? null, 
                        $data['remark'] ?? null,
                        'from_main',
                        $data['id']
                    ]);
                } else {
                    // 创建新的J1stockinout_data记录
                    $j1InsertSql = "INSERT INTO j1stockinout_data 
                                    (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $j1Stmt = $pdo->prepare($j1InsertSql);
                    $j1Stmt->execute([
                        $data['date'],
                        $data['time'],
                        $data['code_number'] ?? null,
                        $data['product_name'],
                        $outQuantity, // 作为入库数量
                        0, // 出库数量为0
                        $data['specification'] ?? null,
                        floatval($data['price'] ?? 0),
                        $totalValue,
                        'AUTO_INBOUND', // 改为入库类型
                        $data['receiver'],
                        $data['remark'] ?? null,
                        $data['id'],
                        'from_main' // 标记来源
                    ]);
                }
                
                // 处理J1stockedit_data表
                $j1EditCheckSql = "SELECT COUNT(*) FROM j1stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j1'";
                $j1EditCheckStmt = $pdo->prepare($j1EditCheckSql);
                $j1EditCheckStmt->execute([$data['product_name'], $data['receiver']]);
                $j1EditExists = $j1EditCheckStmt->fetchColumn() > 0;
                
                if ($j1EditExists) {
                    // 更新J1stockedit_data表
                    $j1EditUpdateSql = "UPDATE j1stockedit_data 
                                        SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                            in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                        WHERE product_name = ? AND receiver = ? AND target_system = 'j1'";
                    
                    $j1EditStmt = $pdo->prepare($j1EditUpdateSql);
                    $j1EditStmt->execute([
                        $data['date'], 
                        $data['time'], 
                        $data['code_number'] ?? null, 
                        $data['product_name'],
                        $outQuantity, 
                        0, 
                        $data['specification'] ?? null, 
                        floatval($data['price'] ?? 0), 
                        $data['receiver'] ?? null, 
                        $data['remark'] ?? null,
                        'j1',
                        $data['product_name'], // 用于WHERE条件
                        $data['receiver'] ?? null  // 用于WHERE条件
                    ]);
                } else {
                    // 创建新的J1stockedit_data记录
                    $j1EditInsertSql = "INSERT INTO j1stockedit_data 
                                        (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $j1EditStmt = $pdo->prepare($j1EditInsertSql);
                    $j1EditStmt->execute([
                        $data['date'],
                        $data['time'],
                        $data['code_number'] ?? null,
                        $data['product_name'],
                        $outQuantity, // 作为入库数量
                        0, // 出库数量为0
                        $data['specification'] ?? null,
                        floatval($data['price'] ?? 0),
                        $data['receiver'],
                        $data['remark'] ?? null,
                        'j1'
                    ]);
                }
                
                error_log("已同步更新J1表和J1Edit表记录");
                
            } elseif ($newTargetSystem === 'j2') {
                // 检查J2表中是否已存在记录
                $j2CheckSql = "SELECT COUNT(*) FROM j2stockinout_data WHERE main_record_id = ?";
                $j2CheckStmt = $pdo->prepare($j2CheckSql);
                $j2CheckStmt->execute([$data['id']]);
                $j2Exists = $j2CheckStmt->fetchColumn() > 0;
                
                if ($j2Exists) {
                    // 更新J2stockinout_data表
                    $j2UpdateSql = "UPDATE j2stockinout_data 
                                    SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                        in_quantity = ?, out_quantity = ?, specification = ?, price = ?, total_value = ?, receiver = ?, remark = ?, target_system = ?
                                    WHERE main_record_id = ?";
                    
                    $j2Stmt = $pdo->prepare($j2UpdateSql);
                    $j2Stmt->execute([
                        $data['date'], 
                        $data['time'], 
                        $data['code_number'] ?? null, 
                        $data['product_name'],
                        $outQuantity, 
                        0, 
                        $data['specification'] ?? null, 
                        floatval($data['price'] ?? 0), 
                        $totalValue,
                        $data['receiver'] ?? null, 
                        $data['remark'] ?? null,
                        'from_main',
                        $data['id']
                    ]);
                } else {
                    // 创建新的J2stockinout_data记录
                    $j2InsertSql = "INSERT INTO j2stockinout_data 
                                    (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $j2Stmt = $pdo->prepare($j2InsertSql);
                    $j2Stmt->execute([
                        $data['date'],
                        $data['time'],
                        $data['code_number'] ?? null,
                        $data['product_name'],
                        $outQuantity, // 作为入库数量
                        0, // 出库数量为0
                        $data['specification'] ?? null,
                        floatval($data['price'] ?? 0),
                        $totalValue,
                        'AUTO_INBOUND', // 改为入库类型
                        $data['receiver'],
                        $data['remark'] ?? null,
                        $data['id'],
                        'from_main' // 标记来源
                    ]);
                }
                
                // 处理J2stockedit_data表
                $j2EditCheckSql = "SELECT COUNT(*) FROM j2stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j2'";
                $j2EditCheckStmt = $pdo->prepare($j2EditCheckSql);
                $j2EditCheckStmt->execute([$data['product_name'], $data['receiver']]);
                $j2EditExists = $j2EditCheckStmt->fetchColumn() > 0;
                
                if ($j2EditExists) {
                    // 更新J2stockedit_data表
                    $j2EditUpdateSql = "UPDATE j2stockedit_data 
                                        SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                            in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                        WHERE product_name = ? AND receiver = ? AND target_system = 'j2'";

                    $j2EditStmt = $pdo->prepare($j2EditUpdateSql);
                    $j2EditStmt->execute([
                        $data['date'], 
                        $data['time'], 
                        $data['code_number'] ?? null, 
                        $data['product_name'],
                        $outQuantity, 
                        0, 
                        $data['specification'] ?? null, 
                        floatval($data['price'] ?? 0), 
                        $data['receiver'] ?? null, 
                        $data['remark'] ?? null,
                        'j2',
                        $data['product_name'], // 用于WHERE条件
                        $data['receiver'] ?? null  // 用于WHERE条件
                    ]);
                } else {
                    // 创建新的J2stockedit_data记录
                    $j2EditInsertSql = "INSERT INTO j2stockedit_data 
                                        (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $j2EditStmt = $pdo->prepare($j2EditInsertSql);
                    $j2EditStmt->execute([
                        $data['date'],
                        $data['time'],
                        $data['code_number'] ?? null,
                        $data['product_name'],
                        $outQuantity, // 作为入库数量
                        0, // 出库数量为0
                        $data['specification'] ?? null,
                        floatval($data['price'] ?? 0),
                        $data['receiver'],
                        $data['remark'] ?? null,
                        'j2'
                    ]);
                }
                
                error_log("已同步更新J2表和J2Edit表记录");
                
            } elseif ($newTargetSystem === 'j3') {
                // 检查J3表中是否已存在记录
                $j3CheckSql = "SELECT COUNT(*) FROM j3stockinout_data WHERE main_record_id = ?";
                $j3CheckStmt = $pdo->prepare($j3CheckSql);
                $j3CheckStmt->execute([$data['id']]);
                $j3Exists = $j3CheckStmt->fetchColumn() > 0;
                
                if ($j3Exists) {
                    // 更新J3stockinout_data表
                    $j3UpdateSql = "UPDATE j3stockinout_data 
                                    SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                        in_quantity = ?, out_quantity = ?, specification = ?, price = ?, total_value = ?, receiver = ?, remark = ?, target_system = ?
                                    WHERE main_record_id = ?";
                    
                    $j3Stmt = $pdo->prepare($j3UpdateSql);
                    $j3Stmt->execute([
                        $data['date'], 
                        $data['time'], 
                        $data['code_number'] ?? null, 
                        $data['product_name'],
                        $outQuantity, 
                        0, 
                        $data['specification'] ?? null, 
                        floatval($data['price'] ?? 0), 
                        $totalValue,
                        $data['receiver'] ?? null, 
                        $data['remark'] ?? null,
                        'from_main',
                        $data['id']
                    ]);
                } else {
                    // 创建新的J3stockinout_data记录
                    $j3InsertSql = "INSERT INTO j3stockinout_data 
                                    (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $j3Stmt = $pdo->prepare($j3InsertSql);
                    $j3Stmt->execute([
                        $data['date'],
                        $data['time'],
                        $data['code_number'] ?? null,
                        $data['product_name'],
                        $outQuantity, // 作为入库数量
                        0, // 出库数量为0
                        $data['specification'] ?? null,
                        floatval($data['price'] ?? 0),
                        $totalValue,
                        'AUTO_INBOUND', // 改为入库类型
                        $data['receiver'],
                        $data['remark'] ?? null,
                        $data['id'],
                        'from_main' // 标记来源
                    ]);
                }
                
                // 处理J3stockedit_data表
                $j3EditCheckSql = "SELECT COUNT(*) FROM j3stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j3'";
                $j3EditCheckStmt = $pdo->prepare($j3EditCheckSql);
                $j3EditCheckStmt->execute([$data['product_name'], $data['receiver']]);
                $j3EditExists = $j3EditCheckStmt->fetchColumn() > 0;
                
                if ($j3EditExists) {
                    // 更新J3stockedit_data表
                    $j3EditUpdateSql = "UPDATE j3stockedit_data 
                                        SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                            in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                        WHERE product_name = ? AND receiver = ? AND target_system = 'j3'";

                    $j3EditStmt = $pdo->prepare($j3EditUpdateSql);
                    $j3EditStmt->execute([
                        $data['date'], 
                        $data['time'], 
                        $data['code_number'] ?? null, 
                        $data['product_name'],
                        $outQuantity, 
                        0, 
                        $data['specification'] ?? null, 
                        floatval($data['price'] ?? 0), 
                        $data['receiver'] ?? null, 
                        $data['remark'] ?? null,
                        'j3',
                        $data['product_name'], // 用于WHERE条件
                        $data['receiver'] ?? null  // 用于WHERE条件
                    ]);
                } else {
                    // 创建新的J3stockedit_data记录
                    $j3EditInsertSql = "INSERT INTO j3stockedit_data 
                                        (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $j3EditStmt = $pdo->prepare($j3EditInsertSql);
                    $j3EditStmt->execute([
                        $data['date'],
                        $data['time'],
                        $data['code_number'] ?? null,
                        $data['product_name'],
                        $outQuantity, // 作为入库数量
                        0, // 出库数量为0
                        $data['specification'] ?? null,
                        floatval($data['price'] ?? 0),
                        $data['receiver'],
                        $data['remark'] ?? null,
                        'j3'
                    ]);
                }
                
                error_log("已同步更新J3表和J3Edit表记录");
                
            } elseif ($newTargetSystem === 'central') {
                // 如果是central，需要清理J1、J2和J3表中的记录
                if ($originalTargetSystem === 'j1') {
                    $j1DeleteSql = "DELETE FROM j1stockinout_data WHERE main_record_id = ?";
                    $j1DelStmt = $pdo->prepare($j1DeleteSql);
                    $j1DelStmt->execute([$data['id']]);
                    
                    $j1EditDeleteSql = "DELETE FROM j1stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j1'";
                    $j1EditDelStmt = $pdo->prepare($j1EditDeleteSql);
                    $j1EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                } elseif ($originalTargetSystem === 'j2') {
                    $j2DeleteSql = "DELETE FROM j2stockinout_data WHERE main_record_id = ?";
                    $j2DelStmt = $pdo->prepare($j2DeleteSql);
                    $j2DelStmt->execute([$data['id']]);
                    
                    $j2EditDeleteSql = "DELETE FROM j2stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j2'";
                    $j2EditDelStmt = $pdo->prepare($j2EditDeleteSql);
                    $j2EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                } elseif ($originalTargetSystem === 'j3') {
                    $j3DeleteSql = "DELETE FROM j3stockinout_data WHERE main_record_id = ?";
                    $j3DelStmt = $pdo->prepare($j3DeleteSql);
                    $j3DelStmt->execute([$data['id']]);
                    
                    $j3EditDeleteSql = "DELETE FROM j3stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j3'";
                    $j3EditDelStmt = $pdo->prepare($j3EditDeleteSql);
                    $j3EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                }
                
                error_log("Central记录更新：仅更新主表，已清理J1/J2/J3表记录");
            }
        }
        
        // 提交事务
        $pdo->commit();
        
        // 获取更新后的记录
        $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ?");
        $stmt->execute([$data['id']]);
        $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, "进出库记录更新成功", $updatedRecord);
        
    } catch (PDOException $e) {
        // 回滚事务
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("数据库错误: " . $e->getMessage());
        sendResponse(false, "更新记录失败：" . $e->getMessage());
    } catch (Exception $e) {
        // 回滚事务
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("一般错误: " . $e->getMessage());
        sendResponse(false, "更新记录失败：" . $e->getMessage());
    }
}

function handleDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        // 先获取要删除的记录信息
        $getRecordSql = "SELECT * FROM stockinout_data WHERE id = ?";
        $getStmt = $pdo->prepare($getRecordSql);
        $getStmt->execute([$id]);
        $recordToDelete = $getStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$recordToDelete) {
            sendResponse(false, "记录不存在");
        }
        
        // 执行删除主表记录
        $stmt = $pdo->prepare("DELETE FROM stockinout_data WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            // 如果是出库记录，根据target_system同步删除相应的表记录
            if (floatval($recordToDelete['out_quantity'] ?? 0) > 0) {
                $targetSystem = $recordToDelete['target_system'] ?? 'j1'; // 默认j1
                
                if ($targetSystem === 'j1') {
                    // 删除J1stockinout_data表记录
                    $j1DeleteSql = "DELETE FROM j1stockinout_data WHERE main_record_id = ?";
                    $j1DelStmt = $pdo->prepare($j1DeleteSql);
                    $j1DelStmt->execute([$id]);
                    
                    // 同时删除J1stockedit_data表记录 - 通过产品名称和接收者匹配最新记录
                    $getJ1EditRecordSql = "SELECT id FROM j1stockedit_data WHERE product_name = ? AND receiver = ? ORDER BY created_at DESC LIMIT 1";
                    $getJ1EditStmt = $pdo->prepare($getJ1EditRecordSql);
                    $getJ1EditStmt->execute([$recordToDelete['product_name'], $recordToDelete['receiver']]);
                    $j1EditRecordId = $getJ1EditStmt->fetchColumn();

                    if ($j1EditRecordId) {
                        $j1EditDeleteSql = "DELETE FROM j1stockedit_data WHERE id = ?";
                        $j1EditDelStmt = $pdo->prepare($j1EditDeleteSql);
                        $j1EditDelStmt->execute([$j1EditRecordId]);
                        error_log("已同步删除J1表和J1Edit表记录");
                    } else {
                        error_log("未找到对应的J1Edit记录进行删除");
                    }
                } elseif ($targetSystem === 'j2') {
                    // 删除J2stockinout_data表记录
                    $j2DeleteSql = "DELETE FROM j2stockinout_data WHERE main_record_id = ?";
                    $j2DelStmt = $pdo->prepare($j2DeleteSql);
                    $j2DelStmt->execute([$id]);
                    
                    // 同时删除J2stockedit_data表记录 - 通过产品名称和接收者匹配最新记录
                    $getJ2EditRecordSql = "SELECT id FROM j2stockedit_data WHERE product_name = ? AND receiver = ? ORDER BY created_at DESC LIMIT 1";
                    $getJ2EditStmt = $pdo->prepare($getJ2EditRecordSql);
                    $getJ2EditStmt->execute([$recordToDelete['product_name'], $recordToDelete['receiver']]);
                    $j2EditRecordId = $getJ2EditStmt->fetchColumn();

                    if ($j2EditRecordId) {
                        $j2EditDeleteSql = "DELETE FROM j2stockedit_data WHERE id = ?";
                        $j2EditDelStmt = $pdo->prepare($j2EditDeleteSql);
                        $j2EditDelStmt->execute([$j2EditRecordId]);
                    }
                    
                    error_log("已同步删除J2表和J2Edit表记录");
                } elseif ($targetSystem === 'j3') {
                    // 删除J3stockinout_data表记录
                    $j3DeleteSql = "DELETE FROM j3stockinout_data WHERE main_record_id = ?";
                    $j3DelStmt = $pdo->prepare($j3DeleteSql);
                    $j3DelStmt->execute([$id]);
                    
                    // 同时删除J3stockedit_data表记录 - 通过产品名称和接收者匹配最新记录
                    $getJ3EditRecordSql = "SELECT id FROM j3stockedit_data WHERE product_name = ? AND receiver = ? ORDER BY created_at DESC LIMIT 1";
                    $getJ3EditStmt = $pdo->prepare($getJ3EditRecordSql);
                    $getJ3EditStmt->execute([$recordToDelete['product_name'], $recordToDelete['receiver']]);
                    $j3EditRecordId = $getJ3EditStmt->fetchColumn();

                    if ($j3EditRecordId) {
                        $j3EditDeleteSql = "DELETE FROM j3stockedit_data WHERE id = ?";
                        $j3EditDelStmt = $pdo->prepare($j3EditDeleteSql);
                        $j3EditDelStmt->execute([$j3EditRecordId]);
                        error_log("已同步删除J3表和J3Edit表记录");
                    } else {
                        error_log("未找到对应的J3Edit记录进行删除");
                    }
                } elseif ($targetSystem === 'central') {
                    error_log("Central记录删除：仅删除主表记录");
                }
            }
            sendResponse(true, "进出库记录删除成功");
        } else {
            sendResponse(false, "删除失败");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}

// 处理 PATCH 请求 - 更新单个字段
function handlePatch() {
    global $pdo, $data;
    
    if (!$data || !isset($data['id']) || !isset($data['field']) || !isset($data['value'])) {
        sendResponse(false, "缺少必要参数：id、field、value");
    }
    
    $id = intval($data['id']);
    $field = $data['field'];
    $value = $data['value'];
    
    // 验证字段名是否安全
    $allowedFields = ['product_remark_checked', 'remark_number', 'remark'];
    if (!in_array($field, $allowedFields)) {
        sendResponse(false, "不允许更新字段: " . $field);
    }
    
    try {
        // 开始事务
        $pdo->beginTransaction();
        
        // 更新单个字段
        $sql = "UPDATE stockinout_data SET {$field} = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$value, $id]);
        
        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            sendResponse(true, "字段更新成功", ['id' => $id, 'field' => $field, 'value' => $value]);
        } else {
            $pdo->rollBack();
            sendResponse(false, "记录不存在或无变化");
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新字段失败：" . $e->getMessage());
    }
}
?>
