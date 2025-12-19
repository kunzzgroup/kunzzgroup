<?php
ob_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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

            // 如果没有提供日期范围，默认使用当月
            if (!$startDate && !$endDate && !$searchDate) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $startDate = "$currentYear-$currentMonth-01";
                $endDate = date('Y-m-t');
            }

            $sql = "SELECT * FROM j1stockedit_data WHERE 1=1";
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
            
            // 从请求参数中获取limit，如果没有则默认使用10000
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10000;
            $sql .= " LIMIT " . $limit;
            
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
                    $originalPrice = $record['price'];
                    $price = floatval($record['price'] ?? 0);
                    $record['in_value'] = $inQty * $price;
                    $record['out_value'] = $outQty * $price;
                    $record['balance_value'] = $record['balance_quantity'] * $price;
                    
                    // 格式化数字
                    $record['in_quantity'] = number_format($inQty, 2, '.', '');
                    $record['out_quantity'] = number_format($outQty, 2, '.', '');
                    $record['balance_quantity'] = number_format($record['balance_quantity'], 2, '.', '');
                    // 保留原始数据库精度供编辑使用，同时提供两位小数用于展示
                    $record['price_raw'] = $originalPrice;
                    $record['price'] = number_format($price, 2, '.', '');
                    $record['in_value'] = number_format($record['in_value'], 2, '.', '');
                    $record['out_value'] = number_format($record['out_value'], 2, '.', '');
                    $record['balance_value'] = number_format($record['balance_value'], 2, '.', '');
                }
                
                sendResponse(true, "进出库数据获取成功，共找到 " . count($records) . " 条记录", $records);
            } catch (PDOException $e) {
                sendResponse(false, "查询数据失败：" . $e->getMessage());
            }
            break;
            
        case 'summary'://1
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
                    FROM j1stockedit_data WHERE 1=1";
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
            
            $stmt = $pdo->prepare("SELECT * FROM j1stockedit_data WHERE id = ?");
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
            $stmt = $pdo->prepare("SELECT DISTINCT receiver FROM j1stockedit_data ORDER BY receiver");
            $stmt->execute();
            $suppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendResponse(true, "供应商列表获取成功", $suppliers);
            break;
            
        case 'products':
            // 获取所有产品列表
            $stmt = $pdo->prepare("SELECT DISTINCT code_number, product_name FROM j1stockedit_data ORDER BY code_number");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "产品列表获取成功", $products);
            break;

        case 'codenumbers':
            // 获取所有唯一的code_number和对应的product_name列表（只显示已批准的货品）
            $stmt = $pdo->prepare("SELECT DISTINCT product_code as code_number, product_name FROM stock_data WHERE product_code IS NOT NULL AND product_code != '' AND approver IS NOT NULL AND approver != '' ORDER BY product_code");
            $stmt->execute();
            $codeNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "编号列表获取成功", $codeNumbers);
            break;

        case 'product_by_code':
            // 根据code_number获取对应的product_name、specification、supplier和category
            $codeNumber = $_GET['code_number'] ?? null;
            if (!$codeNumber) {
                sendResponse(false, "缺少编号参数");
            }
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, specification, supplier, category FROM stock_data WHERE product_code = ? LIMIT 1");
            $stmt->execute([$codeNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                sendResponse(true, "产品名称获取成功", [
                    'product_name' => $result['product_name'],
                    'specification' => $result['specification'],
                    'supplier' => $result['supplier'],
                    'category' => $result['category']
                ]);
            } else {
                sendResponse(false, "未找到对应的产品名称");
            }
            break;
        
        case 'products_list':
            // 获取所有唯一的产品名称和对应的product_code列表（只显示已批准的货品）
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, product_code FROM stock_data WHERE product_name IS NOT NULL AND product_name != '' AND approver IS NOT NULL AND approver != '' ORDER BY product_name");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "产品列表获取成功", $products);
            break;

        case 'code_by_product':
            // 根据product_name获取对应的product_code、specification、supplier和category
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_code, specification, supplier, category FROM stock_data WHERE product_name = ? LIMIT 1");
            $stmt->execute([$productName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                sendResponse(true, "产品编号获取成功", [
                    'product_code' => $result['product_code'],
                    'specification' => $result['specification'],
                    'supplier' => $result['supplier'],
                    'category' => $result['category']
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
                FROM j1stockedit_data 
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
                    FROM j1stockedit_data 
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
                    FROM j1stockedit_data 
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
                // 获取该产品所有不同价格的库存情况（包括价格为0的记录）
                $sql = "SELECT 
                            price,
                            SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                            SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                            (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                            SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as available_stock
                        FROM j1stockedit_data 
                        WHERE product_name = ?
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

    // 验证 target_system 枚举值
    if (!empty($data['target_system']) && !in_array($data['target_system'], ['j1', 'Central', 'central'])) {
        sendResponse(false, "target_system 只能选择 j1 或 Central");
    }
    
    try {
        // 开始事务
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO j1stockedit_data 
                (date, time, product_name, 
                in_quantity, out_quantity, specification, price, code_number, remark, receiver, target_system, type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['product_name'],
            $data['in_quantity'] ?? 0,
            $data['out_quantity'] ?? 0,
            $data['specification'] ?? null,
            $data['price'] ?? 0,
            $data['code_number'] ?? null,
            $data['remark'] ?? null,
            $data['receiver'] ?? null,
            $data['target_system'] ?? null,
            $data['type'] ?? null
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // 检查target_system，如果是Central则同时保存到stockinout_data表
        $targetSystem = $data['target_system'] ?? 'j1'; // 默认j1

        if (strtolower($targetSystem) === 'central') {
            // 如果选择Central，同时保存到stockinout_data表
            $centralSql = "INSERT INTO stockinout_data 
                        (date, time, product_name, 
                        in_quantity, out_quantity, specification, price, code_number, remark, receiver, target_system) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $centralStmt = $pdo->prepare($centralSql);
            $centralResult = $centralStmt->execute([
                $data['date'],
                $data['time'],
                $data['product_name'],
                floatval($data['out_quantity'] ?? 0), // J1的出库数量作为Central的入库数量
                0, // Central的出库数量设为0
                $data['specification'] ?? null,
                floatval($data['price'] ?? 0),
                $data['code_number'] ?? null,
                $data['remark'] ?? null,
                $data['receiver'] ?? null,
                'central'
            ]);
            
            if (!$centralResult) {
                $pdo->rollBack();
                $error = $centralStmt->errorInfo();
                sendResponse(false, "保存到Central表失败：" . $error[2]);
            }
            
            $centralId = $pdo->lastInsertId();
            if (!$centralId) {
                $pdo->rollBack();
                sendResponse(false, "获取Central表记录ID失败，操作已回滚");
            }
            error_log("记录已同时保存到Central表，J1记录ID: " . $newId . ", Central记录ID: " . $centralId);
            
            if (!$centralResult) {
                $pdo->rollBack();
                $error = $centralStmt->errorInfo();
                sendResponse(false, "保存到Central表失败：" . $error[2]);
            }
            
            $centralId = $pdo->lastInsertId();
            error_log("记录已同时保存到Central表，J1记录ID: " . $newId . ", Central记录ID: " . $centralId);
            
            if (!$centralResult) {
                $pdo->rollBack();
                sendResponse(false, "保存到Central表失败，操作已回滚");
            }
            
            // 还需要检查 lastInsertId 是否有效
            $centralId = $pdo->lastInsertId();
            if (!$centralId) {
                $pdo->rollBack();
                sendResponse(false, "获取Central表记录ID失败，操作已回滚");
            }
            error_log("记录已同时保存到Central表，Central记录ID: " . $centralId);
        } elseif ($targetSystem === 'j1') {
            // 如果选择j1，只保存在j1stockedit_data表（当前表）
            error_log("记录仅保存在J1编辑表");
        }
        
        // 提交事务
        $pdo->commit();
        
        // 获取新插入的记录
        $stmt = $pdo->prepare("SELECT * FROM j1stockedit_data WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        $newRecord['approval_status'] = (!empty($newRecord['approver'])) ? 'approved' : 'pending';
        
        $message = "进出库记录添加成功";
        if (strtolower($targetSystem) === 'central') {
            $message .= "，已同时保存到Central系统";
        } elseif ($targetSystem === 'j1') {
            $message .= "，已保存到J1系统";
        }
        
        sendResponse(true, $message, $newRecord);
        
    } catch (PDOException $e) {
        // 回滚事务
        $pdo->rollBack();
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
    $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP','IT7890'];
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
        $sql = "UPDATE j1stockedit_data SET approver = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$approver, $id]);
        
        if ($stmt->rowCount() > 0) {
            // 获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM j1stockedit_data WHERE id = ?");
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
        $sql = "UPDATE j1stockedit_data 
                SET date = ?, time = ?, product_name = ?, 
                    in_quantity = ?, out_quantity = ?, 
                    specification = ?, price = ?, code_number = ?, remark = ?, receiver = ?, target_system = ?, type = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            $data['date'],
            $data['time'],
            $data['product_name'],
            $data['in_quantity'] ?? 0,
            $data['out_quantity'] ?? 0,
            $data['specification'] ?? null,
            $data['price'] ?? 0,
            $data['code_number'] ?? null,
            $data['remark'] ?? null,
            $data['receiver'] ?? null,
            $data['target_system'] ?? null,
            $data['type'] ?? null,
            $data['id']
        ]);
        
        // 检查记录是否存在
        $checkStmt = $pdo->prepare("SELECT * FROM j1stockedit_data WHERE id = ?");
        $checkStmt->execute([$data['id']]);
        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            // 记录存在，获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM j1stockedit_data WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            // 检查target_system，如果是Central则同步更新stockinout_data表
            $targetSystem = $data['target_system'] ?? 'j1'; // 默认j1

            if (strtolower($targetSystem) === 'central') {
                // 更新对应的stockinout_data记录 - 通过匹配字段找到对应记录
                $centralUpdateSql = "UPDATE stockinout_data 
                                    SET date = ?, time = ?, product_name = ?, 
                                        in_quantity = ?, out_quantity = ?, 
                                        specification = ?, price = ?, code_number = ?, remark = ?, receiver = ?
                                    WHERE product_name = ? AND date = ? AND receiver = ? AND target_system = 'Central'
                                    ORDER BY id DESC LIMIT 1";
                
                $centralStmt = $pdo->prepare($centralUpdateSql);
                $centralStmt->execute([
                    $data['date'],
                    $data['time'], 
                    $data['product_name'],
                    floatval($data['out_quantity'] ?? 0), // J1的出库数量作为Central的入库数量
                    0, // Central的出库数量设为0
                    $data['specification'] ?? null,
                    floatval($data['price'] ?? 0),
                    $data['code_number'] ?? null,
                    $data['remark'] ?? null,
                    $data['receiver'] ?? null,
                    $existingRecord['product_name'], // WHERE 条件
                    $existingRecord['date'],         // WHERE 条件  
                    $existingRecord['receiver']      // WHERE 条件
                ]);
                
                if ($centralResult && $centralStmt->rowCount() > 0) {
                    error_log("已同步更新Central表记录");
                } else {
                    error_log("未找到对应的Central表记录进行更新");
                }
                error_log("已同步更新Central表记录");
            } elseif ($targetSystem === 'j1') {
                error_log("J1记录更新：仅更新J1编辑表");
            }
            
            sendResponse(true, "进出库记录更新成功", $updatedRecord);
        } else {
            sendResponse(false, "记录不存在");
        }
        
    } catch (PDOException $e) {
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
        $getRecordSql = "SELECT * FROM j1stockedit_data WHERE id = ?";
        $getStmt = $pdo->prepare($getRecordSql);
        $getStmt->execute([$id]);
        $recordToDelete = $getStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$recordToDelete) {
            sendResponse(false, "记录不存在");
        }
        
        // 执行删除主表记录
        $stmt = $pdo->prepare("DELETE FROM j1stockedit_data WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            // 如果是Central记录，同步删除stockinout_data表记录
                $targetSystem = $recordToDelete['target_system'] ?? 'j1'; // 默认j1

                if (strtolower($targetSystem) === 'central') {
                    // 删除对应的stockinout_data记录
                    $centralDeleteSql = "DELETE FROM stockinout_data 
                                        WHERE product_name = ? AND date = ? AND receiver = ? AND target_system = 'central'
                                        ORDER BY created_at DESC LIMIT 1";
                    
                    $centralDelStmt = $pdo->prepare($centralDeleteSql);
                    $centralDelStmt->execute([
                        $recordToDelete['product_name'],
                        $recordToDelete['date'],
                        $recordToDelete['receiver']
                    ]);
                    error_log("已同步删除Central表记录");
                } elseif ($targetSystem === 'j1') {
                    error_log("J1记录删除：仅删除J1编辑表记录");
                }
            sendResponse(true, "进出库记录删除成功");
        } else {
            sendResponse(false, "删除失败");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}
?>