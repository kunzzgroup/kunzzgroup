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
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

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
error_log("数据库连接成功 - j1stockeditapi");
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

    switch ($action) {
        case 'list':
            // 获取所有J1出库数据
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $searchDate = $_GET['search_date'] ?? null;
            $receiver = $_GET['receiver'] ?? null;
            $productCode = $_GET['product_code'] ?? null;
            $productName = $_GET['product_name'] ?? null;
            $type = $_GET['type'] ?? null;

            // 如果没有提供日期范围，默认使用当月
            if (!$startDate && !$endDate && !$searchDate) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $startDate = "$currentYear-$currentMonth-01";
                $endDate = date('Y-m-t');
            }

            $sql = "SELECT * FROM j1stockinout_data WHERE 1=1";
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
                $sql .= " AND code_number LIKE ?";
                $params[] = "%$productCode%";
            }

            if ($productName) {
                $sql .= " AND product_name LIKE ?";
                $params[] = "%$productName%";
            }

            if ($type) {
                $sql .= " AND type LIKE ?";
                $params[] = "%$type%";
            }

            $sql .= " ORDER BY date ASC, time ASC";

            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute($params);
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 为每条记录格式化数字和计算总价值
                foreach ($records as &$record) {
                    $inQty = floatval($record['in_quantity'] ?? 0);
                    $outQty = floatval($record['out_quantity'] ?? 0);
                    $price = floatval($record['price'] ?? 0);
                    
                    // 计算库存余额和价值
                    $record['balance_quantity'] = $inQty - $outQty;
                    $record['in_value'] = $inQty * $price;
                    $record['out_value'] = $outQty * $price;
                    $record['balance_value'] = $record['balance_quantity'] * $price;
                    $record['total_value'] = $outQty * $price; // 保持原有逻辑
                    
                    // 格式化数字
                    $record['in_quantity'] = number_format($inQty, 2);
                    $record['out_quantity'] = number_format($outQty, 2);
                    $record['balance_quantity'] = number_format($record['balance_quantity'], 2);
                    $record['price'] = number_format($price, 2);
                    $record['in_value'] = number_format($record['in_value'], 2);
                    $record['out_value'] = number_format($record['out_value'], 2);
                    $record['balance_value'] = number_format($record['balance_value'], 2);
                    $record['total_value'] = number_format($record['total_value'], 2);
                }

                sendResponse(true, "J1出库数据获取成功，共找到 " . count($records) . " 条记录", $records);
            } catch (PDOException $e) {
                sendResponse(false, "查询数据失败：" . $e->getMessage());
            }
            break;

        case 'codenumbers':
            // 获取所有唯一的code_number和对应的product_name列表
            $stmt = $pdo->prepare("SELECT DISTINCT product_code as code_number, product_name FROM stock_data WHERE product_code IS NOT NULL AND product_code != '' ORDER BY product_code");
            $stmt->execute();
            $codeNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendResponse(true, "编号列表获取成功", $codeNumbers);
            break;

        case 'product_by_code':
            // 根据code_number获取对应的product_name
            $codeNumber = $_GET['code_number'] ?? null;
            if (!$codeNumber) {
                sendResponse(false, "缺少编号参数");
            }

            $stmt = $pdo->prepare("SELECT DISTINCT product_name FROM stock_data WHERE product_code = ? LIMIT 1");
            $stmt->execute([$codeNumber]);
            $productName = $stmt->fetchColumn();

            if ($productName) {
                sendResponse(true, "产品名称获取成功", ['product_name' => $productName]);
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
            // 根据product_name获取对应的product_code
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }

            $stmt = $pdo->prepare("SELECT DISTINCT product_code FROM stock_data WHERE product_name = ? LIMIT 1");
            $stmt->execute([$productName]);
            $productCode = $stmt->fetchColumn();

            if ($productCode) {
                sendResponse(true, "产品编号获取成功", ['product_code' => $productCode]);
            } else {
                sendResponse(false, "未找到对应的产品编号");
            }
            break;

        case 'types':
            // 获取所有唯一的类型列表
            $stmt = $pdo->prepare("SELECT DISTINCT type FROM j1stockinout_data WHERE type IS NOT NULL AND type != '' ORDER BY type");
            $stmt->execute();
            $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

            sendResponse(true, "类型列表获取成功", $types);
            break;

        case 'migrate_history':
            // 一次性迁移所有历史出库数据
            try {
                // 查找所有有出库数量的历史记录，且不在J1表中的
                $sql = "INSERT INTO j1stockinout_data 
                        (date, time, code_number, product_name, out_quantity, specification, price, total_value, type, receiver, remark)
                        SELECT 
                            s.date, s.time, s.code_number, s.product_name, s.out_quantity, s.specification, s.price, 
                            (s.out_quantity * s.price) as total_value,
                            'MIGRATED_OUTBOUND' as type,
                            s.receiver, s.remark
                        FROM stockinout_data s
                        WHERE s.out_quantity > 0 
                        AND NOT EXISTS (
                            SELECT 1 FROM j1stockinout_data j1 
                            WHERE j1.product_name = s.product_name 
                            AND j1.date = s.date 
                            AND j1.time = s.time 
                            AND j1.out_quantity = s.out_quantity
                        )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $migratedCount = $stmt->rowCount();
                
                sendResponse(true, "历史数据迁移完成，共迁移 $migratedCount 条出库记录", ['migrated_count' => $migratedCount]);
                
            } catch (PDOException $e) {
                sendResponse(false, "数据迁移失败：" . $e->getMessage());
            }
            break;

        default:
            sendResponse(false, "无效的操作");
    }
}

// 处理 POST 请求 - 添加新记录
function handlePost() {
    global $pdo, $data;

    if (!$data) {
        sendResponse(false, "无效的数据格式");
    }

    // 验证必填字段
    $required_fields = ['date', 'time', 'product_name'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }

    // 验证至少有入库或出库数量
    $inQty = floatval($data['in_quantity'] ?? 0);
    $outQty = floatval($data['out_quantity'] ?? 0);
    if ($inQty <= 0 && $outQty <= 0) {
        sendResponse(false, "入库数量或出库数量至少填写一项且大于0");
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
        // 计算总价值
        $inQty = floatval($data['in_quantity'] ?? 0);
        $outQty = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        $totalValue = ($inQty + $outQty) * $price;

        $sql = "INSERT INTO j1stockinout_data 
                (date, time, code_number, product_name, 
                in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $inQty,
            $outQty,
            $data['specification'] ?? null,
            $price,
            $totalValue,
            $data['type'] ?? null,
            $data['receiver'] ?? null,
            $data['remark'] ?? null,
            $data['target_system'] ?? null
        ]);

        $newId = $pdo->lastInsertId();

        // 获取新插入的记录
        $stmt = $pdo->prepare("SELECT * FROM j1stockinout_data WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        sendResponse(true, "J1出库记录添加成功", $newRecord);

    } catch (PDOException $e) {
        sendResponse(false, "添加记录失败：" . $e->getMessage());
    }
}

// 处理 PUT 请求 - 更新记录
function handlePut() {
    global $pdo, $data;

    if (!$data || !isset($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }

    // 验证必填字段
    $required_fields = ['date', 'time', 'product_name'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }

    // 验证至少有入库或出库数量
    $inQty = floatval($data['in_quantity'] ?? 0);
    $outQty = floatval($data['out_quantity'] ?? 0);
    if ($inQty <= 0 && $outQty <= 0) {
        sendResponse(false, "入库数量或出库数量至少填写一项且大于0");
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
        // 计算总价值
        $inQty = floatval($data['in_quantity'] ?? 0);
        $outQty = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        $totalValue = ($inQty + $outQty) * $price;

        $sql = "UPDATE j1stockinout_data 
                SET date = ?, time = ?, code_number = ?, product_name = ?, 
                    in_quantity = ?, out_quantity = ?, specification = ?, price = ?, total_value = ?,
                    type = ?, receiver = ?, remark = ?, target_system = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            $data['date'],
            $data['time'],
            $data['code_number'] ?? null,
            $data['product_name'],
            $inQty,
            $outQty,
            $data['specification'] ?? null,
            $price,
            $totalValue,
            $data['type'] ?? null,
            $data['receiver'] ?? null,
            $data['remark'] ?? null,
            $data['target_system'] ?? null,
            $data['id']
        ]);

        // 检查记录是否存在
        $checkStmt = $pdo->prepare("SELECT * FROM j1stockinout_data WHERE id = ?");
        $checkStmt->execute([$data['id']]);
        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            // 记录存在，获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM j1stockinout_data WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            sendResponse(true, "J1出库记录更新成功", $updatedRecord);
        } else {
            sendResponse(false, "记录不存在");
        }

    } catch (PDOException $e) {
        sendResponse(false, "更新记录失败：" . $e->getMessage());
    }
}

// 处理 DELETE 请求 - 删除记录
function handleDelete() {
    global $pdo;

    $id = $_GET['id'] ?? null;

    if (!$id) {
        sendResponse(false, "缺少记录ID");
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM j1stockinout_data WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            sendResponse(true, "J1出库记录删除成功");
        } else {
            sendResponse(false, "记录不存在");
        }

    } catch (PDOException $e) {
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}
?>