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

// 数据库配置（与 j1 同一库，使用 j2 表）
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 确保所需数据表存在（静默失败以避免无权限导致500）
    try { ensureTables($pdo); } catch (Throwable $ignore) {}
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $e->getMessage()]);
    exit;
}

function sendResponse($success, $message = "", $data = null) {
    ob_end_clean();
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

// 如果缺少表则自动创建（J2 表）
function ensureTables(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `j2stockeditmobile_data` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` date NOT NULL,
      `time` time NOT NULL,
      `product_name` varchar(255) NOT NULL,
      `code_number` varchar(100) DEFAULT NULL,
      `in_quantity` decimal(10,3) DEFAULT 0.000,
      `out_quantity` decimal(10,3) DEFAULT 0.000,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_date` (`date`),
      KEY `idx_product_name` (`product_name`),
      KEY `idx_code_number` (`code_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `j2stocklist_total` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `product_name` varchar(255) NOT NULL,
      `code_number` varchar(100) DEFAULT NULL,
      `total_qty` decimal(10,3) DEFAULT 0.000,
      `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_product` (`product_name`, `code_number`),
      KEY `idx_product_name` (`product_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? null;

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

// 处理 GET 请求
function handleGet() {
    global $pdo, $dbname, $dbuser;
    
    $action = $_GET['action'] ?? 'list';

    // 健康检查
    if ($action === 'ping') {
        sendResponse(true, 'ok', [
            'db' => $dbname,
            'user' => $dbuser,
        ]);
    }

    // 手动初始化（当权限允许但尚未建表时可调用一次）
    if ($action === 'init') {
        try { ensureTables($pdo); } catch (Throwable $e) { sendResponse(false, $e->getMessage()); }
        sendResponse(true, 'tables ensured');
    }
    
    switch ($action) {
        case 'list':
            // 获取所有进出库数据
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $searchDate = $_GET['search_date'] ?? null;
            $productCode = $_GET['product_code'] ?? null;
            $productName = $_GET['product_name'] ?? null;
            $limit = $_GET['limit'] ?? 5000;

            // 不设置默认日期范围：未提供日期参数时返回全部记录

            $sql = "SELECT * FROM j2stockeditmobile_data WHERE 1=1";
            $params = [];
            
            if ($searchDate) {
                $sql .= " AND date = ?";
                $params[] = $searchDate;
            } elseif ($startDate && $endDate) {
                $sql .= " AND date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            if ($productCode) {
                $sql .= " AND code_number LIKE ?";
                $params[] = "%$productCode%";
            }

            if ($productName) {
                $sql .= " AND product_name LIKE ?";
                $params[] = "%$productName%";
            }
            
            // 按顺序显示：日期升序 → 时间升序 → 进货在前（in_quantity>0）→ 最后按id
            $sql .= " ORDER BY date ASC, time ASC, CASE WHEN in_quantity>0 THEN 0 ELSE 1 END ASC, id ASC";
            $sql .= " LIMIT " . intval($limit);
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 为每条记录添加计算字段
                foreach ($records as &$record) {
                    $inQty = floatval($record['in_quantity'] ?? 0);
                    $outQty = floatval($record['out_quantity'] ?? 0);
                    $record['balance_quantity'] = $inQty - $outQty;
                }
                
                sendResponse(true, "数据获取成功，共找到 " . count($records) . " 条记录", $records);
            } catch (PDOException $e) {
                // 表不存在：创建表并返回空数组，避免500
                if ($e->getCode() === '42S02' || strpos($e->getMessage(), '1146') !== false) {
                    try { ensureTables($pdo); } catch (Throwable $ignore) {}
                    sendResponse(true, "首次初始化，表已创建", []);
                }
                sendResponse(false, "查询数据失败：" . $e->getMessage());
            }
            break;
            
        case 'single':
            // 获取单条记录
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, "缺少记录ID");
            }
            
            $stmt = $pdo->prepare("SELECT * FROM j2stockeditmobile_data WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                sendResponse(true, "记录获取成功", $record);
            } else {
                sendResponse(false, "记录不存在");
            }
            break;
            
        case 'codenumbers':
            // 获取所有唯一的code_number和对应的product_name列表（J2 分配）
            $stmt = $pdo->prepare("SELECT DISTINCT product_code as code_number, product_name FROM stock_data WHERE product_code IS NOT NULL AND product_code != '' AND (system_assign = 'J2' OR system_assign LIKE '%J2%') ORDER BY product_code");
            $stmt->execute();
            $codeNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "编号列表获取成功", $codeNumbers);
            break;
        
        case 'products_list':
            // 获取所有唯一的产品名称和对应的product_code列表（J2 分配）
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, product_code FROM stock_data WHERE product_name IS NOT NULL AND product_name != '' AND (system_assign = 'J2' OR system_assign LIKE '%J2%') ORDER BY product_name");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "产品列表获取成功", $products);
            break;
            
        case 'product_by_code':
            // 根据code_number获取对应的product_name
            $codeNumber = $_GET['code_number'] ?? null;
            if (!$codeNumber) {
                sendResponse(false, "缺少编号参数");
            }
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, product_code FROM stock_data WHERE product_code = ? AND (system_assign = 'J2' OR system_assign LIKE '%J2%') LIMIT 1");
            $stmt->execute([$codeNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                sendResponse(true, "产品信息获取成功", [
                    'product_name' => $result['product_name'],
                    'product_code' => $result['product_code']
                ]);
            } else {
                sendResponse(false, "未找到对应的产品");
            }
            break;
            
        case 'code_by_product':
            // 根据product_name获取对应的product_code
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_code, product_name FROM stock_data WHERE product_name = ? AND (system_assign = 'J2' OR system_assign LIKE '%J2%') LIMIT 1");
            $stmt->execute([$productName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                sendResponse(true, "产品编号获取成功", [
                    'product_code' => $result['product_code'],
                    'product_name' => $result['product_name']
                ]);
            } else {
                sendResponse(false, "未找到对应的产品编号");
            }
            break;
            
        case 'stocklist_total':
            // 合并 j2stockeditmobile_data 和 j2stockedit_data 两个表的数据来计算库存总数
            try {
                $sql = "SELECT 
                            product_name,
                            code_number,
                            SUM(in_quantity) as total_in,
                            SUM(out_quantity) as total_out,
                            SUM(in_quantity) - SUM(out_quantity) as total_qty
                        FROM (
                            SELECT product_name, code_number, in_quantity, out_quantity
                            FROM j2stockeditmobile_data
                            WHERE product_name IS NOT NULL AND product_name != ''
                            UNION ALL
                            SELECT product_name, code_number, in_quantity, out_quantity
                            FROM j2stockedit_data
                            WHERE product_name IS NOT NULL AND product_name != ''
                        ) AS combined_data
                        GROUP BY product_name, code_number
                        ORDER BY product_name";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $totals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $items = [];
                $totalQty = 0;
                foreach ($totals as $item) {
                    $qty = floatval($item['total_qty'] ?? 0);
                    $totalQty += $qty;
                    $items[] = [
                        'product_name' => $item['product_name'],
                        'code_number' => $item['code_number'] ?? null,
                        'total_qty' => number_format($qty, 3, '.', '')
                    ];
                }
                
                sendResponse(true, "库存总数获取成功", [
                    'items' => $items,
                    'total_records' => count($items),
                    'total_qty' => number_format($totalQty, 3, '.', '')
                ]);
            } catch (PDOException $e) {
                if ($e->getCode() === '42S02' || strpos($e->getMessage(), '1146') !== false) {
                    try { ensureTables($pdo); } catch (Throwable $ignore) {}
                    sendResponse(true, "表已创建，暂无数据", [
                        'items' => [],
                        'total_records' => 0,
                        'total_qty' => '0.000'
                    ]);
                } else {
                    sendResponse(false, "计算库存总数失败：" . $e->getMessage());
                }
            }
            break;
            
        default:
            sendResponse(false, "未知的action参数");
    }
}

// 处理 POST 请求 - 创建新记录
function handlePost() {
    global $pdo;
    
    global $data;
    
    if (empty($data['date']) || empty($data['time']) || empty($data['product_name'])) {
        sendResponse(false, "日期、时间和产品名称是必填字段");
    }
    
    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO j2stockeditmobile_data 
                (date, time, product_name, code_number, in_quantity, out_quantity) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['product_name'],
            $data['code_number'] ?? null,
            floatval($data['in_quantity'] ?? 0),
            floatval($data['out_quantity'] ?? 0)
        ]);
        
        $newId = $pdo->lastInsertId();
        
        updateStocklistTotal($data['product_name'], $data['code_number'] ?? null, floatval($data['in_quantity'] ?? 0), floatval($data['out_quantity'] ?? 0), true);
        
        syncToJ2StockEditData($pdo, $data, 'insert');
        
        $pdo->commit();
        
        $stmt = $pdo->prepare("SELECT * FROM j2stockeditmobile_data WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, "记录创建成功", $newRecord);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "创建记录失败：" . $e->getMessage());
    }
}

// 处理 PUT 请求 - 更新记录
function handlePut() {
    global $pdo;
    global $data;
    
    if (empty($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $oldStmt = $pdo->prepare("SELECT * FROM j2stockeditmobile_data WHERE id = ?");
        $oldStmt->execute([$data['id']]);
        $oldRecord = $oldStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$oldRecord) {
            sendResponse(false, "记录不存在");
        }
        
        $pdo->beginTransaction();
        
        $sql = "UPDATE j2stockeditmobile_data 
                SET date = ?, time = ?, product_name = ?, code_number = ?, 
                    in_quantity = ?, out_quantity = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $data['date'] ?? $oldRecord['date'],
            $data['time'] ?? $oldRecord['time'],
            $data['product_name'] ?? $oldRecord['product_name'],
            $data['code_number'] ?? $oldRecord['code_number'],
            floatval($data['in_quantity'] ?? $oldRecord['in_quantity']),
            floatval($data['out_quantity'] ?? $oldRecord['out_quantity']),
            $data['id']
        ]);
        
        $oldInQty = floatval($oldRecord['in_quantity'] ?? 0);
        $oldOutQty = floatval($oldRecord['out_quantity'] ?? 0);
        $newInQty = floatval($data['in_quantity'] ?? $oldInQty);
        $newOutQty = floatval($data['out_quantity'] ?? $oldOutQty);
        
        $diffInQty = $newInQty - $oldInQty;
        $diffOutQty = $newOutQty - $oldOutQty;
        
        updateStocklistTotal(
            $data['product_name'] ?? $oldRecord['product_name'],
            $data['code_number'] ?? $oldRecord['code_number'],
            $diffInQty,
            $diffOutQty,
            true
        );
        
        $updateData = [
            'date' => $data['date'] ?? $oldRecord['date'],
            'time' => $data['time'] ?? $oldRecord['time'],
            'product_name' => $data['product_name'] ?? $oldRecord['product_name'],
            'code_number' => $data['code_number'] ?? $oldRecord['code_number'],
            'in_quantity' => floatval($data['in_quantity'] ?? $oldRecord['in_quantity']),
            'out_quantity' => floatval($data['out_quantity'] ?? $oldRecord['out_quantity']),
            'old_date' => $oldRecord['date'],
            'old_time' => $oldRecord['time']
        ];
        syncToJ2StockEditData($pdo, $updateData, 'update');
        
        $pdo->commit();
        
        $stmt = $pdo->prepare("SELECT * FROM j2stockeditmobile_data WHERE id = ?");
        $stmt->execute([$data['id']]);
        $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, "记录更新成功", $updatedRecord);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新记录失败：" . $e->getMessage());
    }
}

// 处理 DELETE 请求
function handleDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $getStmt = $pdo->prepare("SELECT * FROM j2stockeditmobile_data WHERE id = ?");
        $getStmt->execute([$id]);
        $record = $getStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            sendResponse(false, "记录不存在");
        }
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM j2stockeditmobile_data WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($record) {
            $inQty = floatval($record['in_quantity'] ?? 0);
            $outQty = floatval($record['out_quantity'] ?? 0);
            
            updateStocklistTotal(
                $record['product_name'],
                $record['code_number'],
                -$inQty,
                -$outQty,
                true
            );
            
            syncToJ2StockEditData($pdo, $record, 'delete');
        }
        
        $pdo->commit();
        
        sendResponse(true, "记录删除成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}

// 同步数据到 j2stockedit_data 表
function syncToJ2StockEditData($pdo, $data, $operation = 'insert') {
    try {
        $productInfo = null;
        if (!empty($data['product_name'])) {
            $infoStmt = $pdo->prepare("SELECT specification, price, category FROM stock_data WHERE product_name = ? LIMIT 1");
            $infoStmt->execute([$data['product_name']]);
            $productInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($data['code_number'])) {
            $infoStmt = $pdo->prepare("SELECT specification, price, category FROM stock_data WHERE product_code = ? LIMIT 1");
            $infoStmt->execute([$data['code_number']]);
            $productInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $specification = $productInfo['specification'] ?? null;
        $price = floatval($productInfo['price'] ?? 0);
        $type = $productInfo['category'] ?? null;
        
        if ($operation === 'insert') {
            $sql = "INSERT INTO j2stockedit_data 
                    (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system, type) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['date'],
                $data['time'],
                $data['code_number'] ?? null,
                $data['product_name'],
                floatval($data['in_quantity'] ?? 0),
                floatval($data['out_quantity'] ?? 0),
                $specification,
                $price,
                'Mobile',
                null,
                'j2',
                $type
            ]);
            
            return $pdo->lastInsertId();
        } elseif ($operation === 'update') {
            $oldDate = $data['old_date'] ?? $data['date'];
            $oldTime = $data['old_time'] ?? $data['time'];
            
            $sql = "UPDATE j2stockedit_data 
                    SET date = ?, time = ?, code_number = ?, product_name = ?, 
                        in_quantity = ?, out_quantity = ?, specification = ?, price = ?, type = ?
                    WHERE product_name = ? AND date = ? AND time = ? AND receiver = 'Mobile' AND target_system = 'j2'
                    LIMIT 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['date'],
                $data['time'],
                $data['code_number'] ?? null,
                $data['product_name'],
                floatval($data['in_quantity'] ?? 0),
                floatval($data['out_quantity'] ?? 0),
                $specification,
                $price,
                $type,
                $data['product_name'],
                $oldDate,
                $oldTime
            ]);
            
            if ($stmt->rowCount() === 0) {
                $insertSql = "INSERT INTO j2stockedit_data 
                        (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system, type) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->execute([
                    $data['date'],
                    $data['time'],
                    $data['code_number'] ?? null,
                    $data['product_name'],
                    floatval($data['in_quantity'] ?? 0),
                    floatval($data['out_quantity'] ?? 0),
                    $specification,
                    $price,
                    'Mobile',
                    null,
                    'j2',
                    $type
                ]);
                
                return $pdo->lastInsertId();
            }
            
            return true;
        } elseif ($operation === 'delete') {
            $sql = "DELETE FROM j2stockedit_data 
                    WHERE product_name = ? AND date = ? AND time = ? AND receiver = 'Mobile' AND target_system = 'j2'
                    LIMIT 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['product_name'],
                $data['date'],
                $data['time']
            ]);
            
            return $stmt->rowCount() > 0;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("同步到j2stockedit_data失败: " . $e->getMessage());
        return false;
    }
}

// 更新库存总数表
function updateStocklistTotal($productName, $codeNumber, $inQty, $outQty, $isAdd = true) {
    global $pdo;
    
    if (empty($productName)) {
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM j2stocklist_total WHERE product_name = ? AND code_number = ?");
        $stmt->execute([$productName, $codeNumber]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $netQty = $inQty - $outQty;
        
        if ($existing) {
            if ($isAdd) {
                $newTotal = floatval($existing['total_qty']) + $netQty;
            } else {
                $newTotal = floatval($existing['total_qty']) - $netQty;
            }
            
            if ($newTotal <= 0) {
                $deleteStmt = $pdo->prepare("DELETE FROM j2stocklist_total WHERE id = ?");
                $deleteStmt->execute([$existing['id']]);
            } else {
                $updateStmt = $pdo->prepare("UPDATE j2stocklist_total SET total_qty = ?, last_updated = NOW() WHERE id = ?");
                $updateStmt->execute([$newTotal, $existing['id']]);
            }
        } else {
            if ($netQty > 0 || $isAdd) {
                $insertStmt = $pdo->prepare("INSERT INTO j2stocklist_total (product_name, code_number, total_qty) VALUES (?, ?, ?)");
                $insertStmt->execute([$productName, $codeNumber, $netQty > 0 ? $netQty : 0]);
            }
        }
    } catch (PDOException $e) {
        error_log("更新库存总数失败: " . $e->getMessage());
    }
}

?>
