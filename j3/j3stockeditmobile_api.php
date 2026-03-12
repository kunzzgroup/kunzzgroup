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

// 如果缺少表则自动创建
function ensureTables(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `j3stockeditmobile_data` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` date NOT NULL,
      `time` time NOT NULL,
      `product_name` varchar(255) NOT NULL,
      `code_number` varchar(100) DEFAULT NULL,
      `in_quantity` decimal(10,3) DEFAULT 0.000,
      `out_quantity` decimal(10,3) DEFAULT 0.000,
      `receiver` varchar(100) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_date` (`date`),
      KEY `idx_product_name` (`product_name`),
      KEY `idx_code_number` (`code_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    
    // 如果表已存在但缺少receiver字段，则添加该字段
    try {
        $pdo->exec("ALTER TABLE `j3stockeditmobile_data` ADD COLUMN `receiver` varchar(100) DEFAULT NULL AFTER `out_quantity`");
    } catch (PDOException $e) {
        // 字段已存在，忽略错误
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS `j3stocklist_total` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `product_name` varchar(255) NOT NULL,
      `code_number` varchar(100) DEFAULT NULL,
      `total_qty` decimal(10,3) DEFAULT 0.000,
      `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_product` (`product_name`, `code_number`),
      KEY `idx_product_name` (`product_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 添加 mobile_ref_id 列
    try {
        $pdo->exec("ALTER TABLE `j3stockedit_data` ADD COLUMN `mobile_ref_id` int(11) DEFAULT NULL");
        $pdo->exec("ALTER TABLE `j3stockedit_data` ADD INDEX `idx_mobile_ref_id` (`mobile_ref_id`)");
    } catch (PDOException $e) {
        // 列已存在则忽略
    }
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
        if (($data['action'] ?? '') === 'batch_save') {
             handleBatchSave();
             return;
        }
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

            $sql = "SELECT * FROM j3stockeditmobile_data WHERE 1=1";
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
            
            $stmt = $pdo->prepare("SELECT * FROM j3stockeditmobile_data WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                sendResponse(true, "记录获取成功", $record);
            } else {
                sendResponse(false, "记录不存在");
            }
            break;
            
        case 'codenumbers':
            // 获取所有唯一的code_number和对应的product_name列表（J3 分配，包含多系统分配，且不过滤 approver）
            $stmt = $pdo->prepare("SELECT DISTINCT product_code as code_number, product_name FROM stock_data WHERE product_code IS NOT NULL AND product_code != '' AND (system_assign = 'J3' OR system_assign LIKE '%J3%') ORDER BY product_code");
            $stmt->execute();
            $codeNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "编号列表获取成功", $codeNumbers);
            break;
        
        case 'products_list':
            // 获取所有唯一的产品名称和对应的product_code列表（J3 分配，包含多系统分配，且不过滤 approver）
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, product_code FROM stock_data WHERE product_name IS NOT NULL AND product_name != '' AND (system_assign = 'J3' OR system_assign LIKE '%J3%') ORDER BY product_name");
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
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, product_code FROM stock_data WHERE product_code = ? AND (system_assign = 'J3' OR system_assign LIKE '%J3%') LIMIT 1");
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
            
            $stmt = $pdo->prepare("SELECT DISTINCT product_code, product_name FROM stock_data WHERE product_name = ? AND (system_assign = 'J3' OR system_assign LIKE '%J3%') LIMIT 1");
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
            // 手机记录已经通过 sync 写入 j3stockedit_data，直接查该表即可，避免双重计算
            try {
                $sql = "SELECT 
                            product_name,
                            code_number,
                            SUM(in_quantity) as total_in,
                            SUM(out_quantity) as total_out,
                            SUM(in_quantity) - SUM(out_quantity) as total_qty
                        FROM j3stockedit_data
                        WHERE product_name IS NOT NULL AND product_name != ''
                        GROUP BY product_name, code_number
                        ORDER BY product_name";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $totals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 格式化数据，匹配原来的格式
                $items = [];
                $totalQty = 0;
                foreach ($totals as $item) {
                    $qty = floatval($item['total_qty'] ?? 0);
                    $totalQty += $qty;
                    $items[] = [
                        'product_name' => $item['product_name'],
                        'code_number' => $item['code_number'],
                        'total_qty' => number_format($qty, 3, '.', '')
                    ];
                }
                
                sendResponse(true, "库存总数获取成功", [
                    'items' => $items,
                    'total_records' => count($items),
                    'total_qty' => number_format($totalQty, 3, '.', '')
                ]);
            } catch (PDOException $e) {
                // 表不存在：返回空数组
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
            
        case 'product_stock_by_price':
            // 获取指定产品的按价格分组的库存记录（用于按价格从高到低扣除）
            $productName = $_GET['product_name'] ?? null;
            $codeNumber = $_GET['code_number'] ?? null;
            
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }
            
            try {
                // 从 j3stockedit_data 表获取该产品的所有不同价格的库存情况
                // 注意：需要计算每个价格的净库存（in - out），包括负数的情况
                // 过滤掉 price 为 NULL 的记录，并按价格从高到低排序
                $sql = "SELECT 
                            COALESCE(price, 0) as price,
                            SUM(in_quantity) as total_in,
                            SUM(out_quantity) as total_out,
                            (SUM(in_quantity) - SUM(out_quantity)) as available_stock
                        FROM j3stockedit_data 
                        WHERE product_name = ? AND price IS NOT NULL";
                $params = [$productName];
                
                if (!empty($codeNumber)) {
                    $sql .= " AND code_number = ?";
                    $params[] = $codeNumber;
                }
                
                $sql .= " GROUP BY price ORDER BY price DESC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $priceStockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $result = [];
                foreach ($priceStockData as $row) {
                    $price = floatval($row['price'] ?? 0);
                    $availableStock = floatval($row['available_stock'] ?? 0);
                    
                    // 只返回有库存的价格（包括负数，因为负数表示已经超扣了）
                    $result[] = [
                        'price' => $price,
                        'available_stock' => $availableStock,
                        'total_in' => floatval($row['total_in'] ?? 0),
                        'total_out' => floatval($row['total_out'] ?? 0)
                    ];
                }
                
                sendResponse(true, "产品按价格分组的库存获取成功", $result);
            } catch (PDOException $e) {
                sendResponse(false, "查询失败：" . $e->getMessage());
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
    
    // 验证必填字段
    if (empty($data['date']) || empty($data['time']) || empty($data['product_name'])) {
        sendResponse(false, "日期、时间和产品名称是必填字段");
    }
    
    try {
        // 开始事务
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO j3stockeditmobile_data 
                (date, time, product_name, code_number, in_quantity, out_quantity, receiver) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['product_name'],
            $data['code_number'] ?? null,
            floatval($data['in_quantity'] ?? 0),
            floatval($data['out_quantity'] ?? 0),
            $data['receiver'] ?? null
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // 更新库存总数表
        updateStocklistTotal($data['product_name'], $data['code_number'] ?? null, floatval($data['in_quantity'] ?? 0), floatval($data['out_quantity'] ?? 0), true);
        
        // 同步到 j3stockedit_data 表（传入 mobile_ref_id）
        $data['mobile_ref_id'] = $newId;
        syncToJ3StockEditData($pdo, $data, 'insert');
        
        $pdo->commit();
        
        // 获取新创建的记录
        $stmt = $pdo->prepare("SELECT * FROM j3stockeditmobile_data WHERE id = ?");
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
        // 获取旧记录用于计算差值
        $oldStmt = $pdo->prepare("SELECT * FROM j3stockeditmobile_data WHERE id = ?");
        $oldStmt->execute([$data['id']]);
        $oldRecord = $oldStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$oldRecord) {
            sendResponse(false, "记录不存在");
        }
        
        $pdo->beginTransaction();
        
        $sql = "UPDATE j3stockeditmobile_data 
                SET date = ?, time = ?, product_name = ?, code_number = ?, 
                    in_quantity = ?, out_quantity = ?, receiver = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $data['date'] ?? $oldRecord['date'],
            $data['time'] ?? $oldRecord['time'],
            $data['product_name'] ?? $oldRecord['product_name'],
            $data['code_number'] ?? $oldRecord['code_number'],
            floatval($data['in_quantity'] ?? $oldRecord['in_quantity']),
            floatval($data['out_quantity'] ?? $oldRecord['out_quantity']),
            $data['receiver'] ?? $oldRecord['receiver'] ?? null,
            $data['id']
        ]);
        
        // 计算差值并更新库存总数
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
        
        // 同步更新到 j3stockedit_data：先删除旧扣货行，再重新智能分层扣货
        $mobileId = $data['id'];
        $delStmt = $pdo->prepare("DELETE FROM j3stockedit_data WHERE mobile_ref_id = ? AND receiver = 'Mobile' AND target_system = 'j3'");
        $delStmt->execute([$mobileId]);

        $updateData = [
            'date'          => $data['date'] ?? $oldRecord['date'],
            'time'          => $data['time'] ?? $oldRecord['time'],
            'product_name'  => $data['product_name'] ?? $oldRecord['product_name'],
            'code_number'   => $data['code_number'] ?? $oldRecord['code_number'],
            'in_quantity'   => floatval($data['in_quantity'] ?? $oldRecord['in_quantity']),
            'out_quantity'  => floatval($data['out_quantity'] ?? $oldRecord['out_quantity']),
            'price'         => $data['price'] ?? null,
            'mobile_ref_id' => $mobileId,
        ];
        syncToJ3StockEditData($pdo, $updateData, 'insert');
        
        $pdo->commit();
        
        // 获取更新后的记录
        $stmt = $pdo->prepare("SELECT * FROM j3stockeditmobile_data WHERE id = ?");
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
        // 获取要删除的记录用于更新库存总数
        $getStmt = $pdo->prepare("SELECT * FROM j3stockeditmobile_data WHERE id = ?");
        $getStmt->execute([$id]);
        $record = $getStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            sendResponse(false, "记录不存在");
        }
        
        $pdo->beginTransaction();
        
        // 删除记录
        $stmt = $pdo->prepare("DELETE FROM j3stockeditmobile_data WHERE id = ?");
        $stmt->execute([$id]);
        
        // 更新库存总数（撤销被删除记录的影响）
        if ($record) {
            $inQty = floatval($record['in_quantity'] ?? 0);
            $outQty = floatval($record['out_quantity'] ?? 0);
            
            // 删除记录时，需要撤销之前的影响
            // 如果原来是出库(out_quantity)，删除后应该加回去
            // 如果原来是入库(in_quantity)，删除后应该减回去
            updateStocklistTotal(
                $record['product_name'],
                $record['code_number'],
                -$inQty,  // 撤销入库
                -$outQty, // 撤销出库（负数出库 = 加回库存）
                true
            );
            
            // 同步删除 j3stockedit_data 表中的记录
            syncToJ3StockEditData($pdo, ['mobile_ref_id' => $id], 'delete');
        }
        
        $pdo->commit();
        
        sendResponse(true, "记录删除成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}

// 同步数据到 j3stockedit_data 表（智能按价格从高到低分层扣货）
function syncToJ3StockEditData($pdo, $data, $operation = 'insert') {
    try {
        if ($operation === 'delete') {
            $mobileRefId = $data['mobile_ref_id'] ?? null;
            if ($mobileRefId) {
                $stmt = $pdo->prepare("DELETE FROM j3stockedit_data WHERE mobile_ref_id = ? AND receiver = 'Mobile' AND target_system = 'j3'");
                $stmt->execute([$mobileRefId]);
                return $stmt->rowCount() > 0;
            }
            return false;
        }

        if ($operation !== 'insert') return false;

        $outQty      = floatval($data['out_quantity'] ?? 0);
        $inQty       = floatval($data['in_quantity'] ?? 0);
        $mobileRefId = $data['mobile_ref_id'] ?? null;
        $codeNumber  = $data['code_number'] ?? null;
        $productName = $data['product_name'] ?? '';

        // 入库或无出货：单行插入
        if ($outQty <= 0) {
            $matchInfo = null;
            $qStmt = $pdo->prepare("SELECT specification, price, type FROM j3stockedit_data
                WHERE product_name = ? AND (receiver IS NULL OR receiver NOT IN ('Mobile','mobile'))
                AND price IS NOT NULL AND price > 0 ORDER BY id DESC LIMIT 1");
            $qStmt->execute([$productName]);
            $matchInfo = $qStmt->fetch(PDO::FETCH_ASSOC);

            if (!$matchInfo) {
                $infoStmt = $pdo->prepare("SELECT specification, price, category as type FROM stock_data WHERE product_name = ? LIMIT 1");
                $infoStmt->execute([$productName]);
                $matchInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
            }

            $price = isset($data['price']) && $data['price'] !== null && $data['price'] !== ''
                ? floatval($data['price']) : floatval($matchInfo['price'] ?? 0);

            $stmt = $pdo->prepare(
                "INSERT INTO j3stockedit_data
                 (date, time, code_number, product_name, in_quantity, out_quantity,
                  specification, price, receiver, remark, target_system, type, mobile_ref_id)
                 VALUES (?,?,?,?,?,?,?,?,'Mobile',NULL,'j3',?,?)"
            );
            $stmt->execute([
                $data['date'], $data['time'], $codeNumber, $productName,
                $inQty, 0,
                $matchInfo['specification'] ?? null, $price,
                $matchInfo['type'] ?? null, $mobileRefId,
            ]);
            return $pdo->lastInsertId();
        }

        // 出库：智能分层扣货
        $tierParams = [$productName];
        $codeFilter = '';
        if ($codeNumber !== null && $codeNumber !== '') {
            $codeFilter = ' AND code_number = ?';
            $tierParams[] = $codeNumber;
        }

        $tierStmt = $pdo->prepare(
            "SELECT specification, price, type,
                    (SUM(in_quantity) - SUM(out_quantity)) AS available
             FROM j3stockedit_data
             WHERE product_name = ? {$codeFilter}
             AND price IS NOT NULL AND price > 0
             GROUP BY specification, price, type
             HAVING available > 0
             ORDER BY price DESC
             FOR UPDATE"
        );
        $tierStmt->execute($tierParams);
        $tiers = $tierStmt->fetchAll(PDO::FETCH_ASSOC);

        $remaining   = $outQty;
        $insertStmt  = $pdo->prepare(
            "INSERT INTO j3stockedit_data
             (date, time, code_number, product_name, in_quantity, out_quantity,
              specification, price, receiver, remark, target_system, type, mobile_ref_id)
             VALUES (?,?,?,?,0,?,?,?,'Mobile',NULL,'j3',?,?)"
        );

        foreach ($tiers as $tier) {
            if ($remaining <= 0) break;
            $deduct = min(floatval($tier['available']), $remaining);
            $insertStmt->execute([
                $data['date'], $data['time'], $codeNumber, $productName,
                $deduct, $tier['specification'], floatval($tier['price']),
                $tier['type'], $mobileRefId,
            ]);
            $remaining -= $deduct;
        }

        if ($remaining > 0.0001) {
            $lastTier = !empty($tiers) ? end($tiers) : [
                'specification' => null, 'price' => floatval($data['price'] ?? 0), 'type' => null
            ];
            $insertStmt->execute([
                $data['date'], $data['time'], $codeNumber, $productName,
                $remaining, $lastTier['specification'], floatval($lastTier['price']),
                $lastTier['type'], $mobileRefId,
            ]);
        }

        error_log("syncToJ3StockEditData: product={$productName}, out={$outQty}, tiers=" . count($tiers));
        return true;

    } catch (PDOException $e) {
        error_log("同步到j3stockedit_data失败: " . $e->getMessage());
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
        // 查找或创建库存总数记录
        $stmt = $pdo->prepare("SELECT * FROM j3stocklist_total WHERE product_name = ? AND code_number = ?");
        $stmt->execute([$productName, $codeNumber]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $netQty = $inQty - $outQty;
        
        if ($existing) {
            // 更新现有记录
            if ($isAdd) {
                $newTotal = floatval($existing['total_qty']) + $netQty;
            } else {
                $newTotal = floatval($existing['total_qty']) - $netQty;
            }
            
            // 如果总数小于等于0，删除记录而不是保留为0
            if ($newTotal <= 0) {
                $deleteStmt = $pdo->prepare("DELETE FROM j3stocklist_total WHERE id = ?");
                $deleteStmt->execute([$existing['id']]);
            } else {
                $updateStmt = $pdo->prepare("UPDATE j3stocklist_total SET total_qty = ?, last_updated = NOW() WHERE id = ?");
                $updateStmt->execute([$newTotal, $existing['id']]);
            }
        } else {
            // 创建新记录
            if ($netQty > 0 || $isAdd) {
                $insertStmt = $pdo->prepare("INSERT INTO j3stocklist_total (product_name, code_number, total_qty) VALUES (?, ?, ?)");
                $insertStmt->execute([$productName, $codeNumber, $netQty > 0 ? $netQty : 0]);
            }
        }
    } catch (PDOException $e) {
        error_log("更新库存总数失败: " . $e->getMessage());
        // 不抛出异常，避免影响主流程
    }
}

/**
 * 处理移动端批量保存请求 (J3)
 */
function handleBatchSave() {
    global $pdo, $data;
    
    $documentDate = $data['document_date'] ?? null;
    $rows = $data['rows'] ?? [];
    
    if (!$documentDate) {
        sendResponse(false, "缺少文件日期 (document_date)");
    }
    
    if (empty($rows)) {
        sendResponse(false, "没有需要保存的数据行");
    }
    
    try {
        $pdo->beginTransaction();
        
        $successCount = 0;
        foreach ($rows as $index => $row) {
            if (empty($row['time']) || empty($row['product_name'])) {
                throw new Exception("第 " . ($index + 1) . " 行缺少必填字段");
            }

            $sql = "INSERT INTO j3stockeditmobile_data 
                    (date, time, product_name, code_number, in_quantity, out_quantity, receiver) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $documentDate,
                $row['time'],
                $row['product_name'],
                $row['code_number'] ?? null,
                floatval($row['in_quantity'] ?? 0),
                floatval($row['out_quantity'] ?? 0),
                $row['receiver'] ?? 'Mobile'
            ]);
            
            $newMobileId = $pdo->lastInsertId();
            updateStocklistTotal($row['product_name'], $row['code_number'] ?? null, floatval($row['in_quantity'] ?? 0), floatval($row['out_quantity'] ?? 0), true);
            
            $syncData = $row;
            $syncData['date'] = $documentDate;
            $syncData['mobile_ref_id'] = $newMobileId;
            syncToJ3StockEditData($pdo, $syncData, 'insert');
            
            $successCount++;
        }
        
        $pdo->commit();
        sendResponse(true, "J3 移动端批量保存成功，共保存 {$successCount} 条记录");
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, "J3 移动端批量保存失败：" . $e->getMessage());
    }
}
?>
