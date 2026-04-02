<?php
require_once __DIR__ . '/permission_guard.php';
requirePermissionApi('resource', 'stock_inventory');

require_once __DIR__ . '/xss_protect.php';
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
$data = get_safe_json_input();

function sendResponse($success, $message = "", $data = null)
{
    ob_end_clean();
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

/**
 * 批量将记录中的 created_by（存储的是 username）替换为对应的 nickname 用于显示
 * 优先级：nickname > username_cn > username（原值）
 */
function resolveCreatedByNicknames(PDO $pdo, array $records): array
{
    // 收集所有唯一的 created_by 值
    $usernames = [];
    foreach ($records as $record) {
        $cb = trim((string) ($record['created_by'] ?? ''));
        if ($cb !== '' && $cb !== 'System') {
            $usernames[$cb] = true;
        }
    }
    if (empty($usernames)) {
        return $records;
    }

    // 批量查询 nickname
    $placeholders = implode(',', array_fill(0, count($usernames), '?'));
    $stmt = $pdo->prepare("SELECT username, nickname, username_cn FROM users WHERE username IN ($placeholders)");
    $stmt->execute(array_keys($usernames));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 构建映射表
    $nicknameMap = [];
    foreach ($rows as $row) {
        $nick = trim((string) ($row['nickname'] ?? ''));
        if ($nick !== '') {
            $nicknameMap[$row['username']] = $nick;
        } else {
            $cn = trim((string) ($row['username_cn'] ?? ''));
            if ($cn !== '') {
                $nicknameMap[$row['username']] = $cn;
            }
        }
    }

    // 替换 created_by 为 nickname
    foreach ($records as &$record) {
        $cb = trim((string) ($record['created_by'] ?? ''));
        if (isset($nicknameMap[$cb])) {
            $record['created_by'] = $nicknameMap[$cb];
        }
    }

    return $records;
}

function saveToJ1Table($pdo, $data, $mainRecordId = null)
{
    try {
        // 从stock_data获取该货品的category
        $category = null;
        if (!empty($data['product_name'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_name = ? LIMIT 1");
            $categoryStmt->execute([$data['product_name']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        } elseif (!empty($data['code_number'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_code = ? LIMIT 1");
            $categoryStmt->execute([$data['code_number']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        }

        // 保存到 j1stockinout_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j1stockinout_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // 将主表的出库数量作为J1表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        $totalValue = $outQuantity * $price;

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
            $category, // 使用从stock_data获取的category
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

function saveToJ2Table($pdo, $data, $mainRecordId = null)
{
    try {
        // 从stock_data获取该货品的category
        $category = null;
        if (!empty($data['product_name'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_name = ? LIMIT 1");
            $categoryStmt->execute([$data['product_name']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        } elseif (!empty($data['code_number'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_code = ? LIMIT 1");
            $categoryStmt->execute([$data['code_number']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        }

        // 保存到 j2stockinout_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j2stockinout_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // 将主表的出库数量作为J2表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        $totalValue = $outQuantity * $price;

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
            $category, // 使用从stock_data获取的category
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

function saveToJ1EditTable($pdo, $data, $mainRecordId = null)
{
    try {
        // 从stock_data获取该货品的category
        $category = null;
        if (!empty($data['product_name'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_name = ? LIMIT 1");
            $categoryStmt->execute([$data['product_name']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        } elseif (!empty($data['code_number'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_code = ? LIMIT 1");
            $categoryStmt->execute([$data['code_number']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        }

        // 保存到 j1stockedit_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j1stockedit_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system, type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
            'j1', // 设置为j1
            $category // 使用从stock_data获取的category
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J1Edit表失败: " . $e->getMessage());
        return false;
    }
}

function saveToJ2EditTable($pdo, $data, $mainRecordId = null)
{
    try {
        // 从stock_data获取该货品的category
        $category = null;
        if (!empty($data['product_name'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_name = ? LIMIT 1");
            $categoryStmt->execute([$data['product_name']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        } elseif (!empty($data['code_number'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_code = ? LIMIT 1");
            $categoryStmt->execute([$data['code_number']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        }

        // 保存到 j2stockedit_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j2stockedit_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system, type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
            'j2', // 设置为j2
            $category // 使用从stock_data获取的category
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("保存到J2Edit表失败: " . $e->getMessage());
        return false;
    }
}

function saveToJ3Table($pdo, $data, $mainRecordId = null)
{
    try {
        // 从stock_data获取该货品的category
        $category = null;
        if (!empty($data['product_name'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_name = ? LIMIT 1");
            $categoryStmt->execute([$data['product_name']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        } elseif (!empty($data['code_number'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_code = ? LIMIT 1");
            $categoryStmt->execute([$data['code_number']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        }

        // 保存到 j3stockinout_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j3stockinout_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // 将主表的出库数量作为J3表的入库数量
        $outQuantity = floatval($data['out_quantity'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        $totalValue = $outQuantity * $price;

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
            $category, // 使用从stock_data获取的category
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

function saveToJ3EditTable($pdo, $data, $mainRecordId = null)
{
    try {
        // 从stock_data获取该货品的category
        $category = null;
        if (!empty($data['product_name'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_name = ? LIMIT 1");
            $categoryStmt->execute([$data['product_name']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        } elseif (!empty($data['code_number'])) {
            $categoryStmt = $pdo->prepare("SELECT category FROM stock_data WHERE product_code = ? LIMIT 1");
            $categoryStmt->execute([$data['code_number']]);
            $categoryResult = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $category = $categoryResult['category'];
            }
        }

        // 保存到 j3stockedit_data 表 - 出库记录转为入库记录
        $sql = "INSERT INTO j3stockedit_data 
                (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system, type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
            'j3', // 设置为j3
            $category // 使用从stock_data获取的category
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
function handleGet()
{
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
            $productCode = $_GET['product_code'] ?? null; // 这行已存在，保持不变
            $productName = $_GET['product_name'] ?? null;

            // 如果没有提供日期范围，默认显示一年内的数据
            if (!$startDate && !$endDate && !$searchDate) {
                $startDate = date('Y-m-d', strtotime('-1 year')); // 一年前的今天
                $endDate = date('Y-m-d'); // 今天
            }

            $sql = "SELECT * FROM stockinout_data WHERE deleted_at IS NULL";
            $params = [];

            // 排除货品异常（SOT）的记录，因为它们在 stocksot.php 中管理
            $sql .= " AND (target_system IS NULL OR target_system != 'SOT')";

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
                $sql .= " AND code_number LIKE ?"; // 修改这里：从product_code改为code_number
                $params[] = "%$productCode%";
            }

            if ($productName) {
                $sql .= " AND product_name LIKE ?";
                $params[] = "%$productName%";
            }

            $sql .= " ORDER BY date ASC, id ASC";

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
                    $price = floatval($record['price'] ?? 0);
                    $record['in_value'] = $inQty * $price;
                    $record['out_value'] = $outQty * $price;
                    $record['balance_value'] = $record['balance_quantity'] * $price;

                    // 格式化数字
                    $record['in_quantity'] = $inQty;
                    $record['out_quantity'] = $outQty;
                    $record['price'] = $price;
                }

                sendResponse(true, "进出库数据获取成功，共找到 " . count($records) . " 条记录", resolveCreatedByNicknames($pdo, $records));
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
                        COALESCE(SUM(in_quantity * price), 0) as total_in_value,
                        COALESCE(SUM(out_quantity * price), 0) as total_out_value,
                        COALESCE(SUM((in_quantity - out_quantity) * price), 0) as total_balance_value,
                        COALESCE(SUM(in_quantity), 0) as total_in_quantity,
                        COALESCE(SUM(out_quantity), 0) as total_out_quantity,
                        COALESCE(SUM(in_quantity - out_quantity), 0) as total_balance_quantity
                    FROM stockinout_data WHERE deleted_at IS NULL";
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

            $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ? AND deleted_at IS NULL");
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
            $stmt = $pdo->prepare("SELECT DISTINCT supplier FROM stockinout_data WHERE deleted_at IS NULL ORDER BY supplier");
            $stmt->execute();
            $suppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            sendResponse(true, "供应商列表获取成功", $suppliers);
            break;

        case 'products':
            // 获取所有产品列表
            $stmt = $pdo->prepare("SELECT DISTINCT product_code, product_name FROM stockinout_data WHERE deleted_at IS NULL ORDER BY product_code");
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
                $category = trim((string) ($result['category'] ?? ''));
                // 兼容旧数据与大小写：Drinks / service line / Service line 等统一为 Service Line
                if (strtolower($category) === 'service line' || $category === 'Drinks') {
                    $category = 'Service Line';
                }
                sendResponse(true, "产品名称获取成功", [
                    'product_name' => $result['product_name'],
                    'specification' => $result['specification'],
                    'supplier' => $result['supplier'],
                    'category' => $category
                ]);
            } else {
                sendResponse(false, "未找到对应的产品名称");
            }
            break;

        case 'products_list':
            // 获取所有唯一的产品名称和对应的product_code列表（只显示已批准的货品）
            $stmt = $pdo->prepare("SELECT DISTINCT product_name, product_code, supplier FROM stock_data WHERE product_name IS NOT NULL AND product_name != '' AND approver IS NOT NULL AND approver != '' ORDER BY product_name, product_code");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendResponse(true, "产品列表获取成功", $products);
            break;

        case 'get_shippers':
            // 获取所有被赋予「出货人」权限的用户昵称
            // 存储位置: user_page_permissions.stock_inventory.is_shipper = true
            try {
                $shipperStmt = $pdo->prepare("
                    SELECT u.nickname, u.username
                    FROM user_page_permissions p
                    JOIN users u ON u.id = p.user_id
                    WHERE p.page_key = 'stock_inventory'
                      AND JSON_UNQUOTE(JSON_EXTRACT(p.permissions_json, '$.is_shipper')) = 'true'
                ");
                $shipperStmt->execute();
                $rows = $shipperStmt->fetchAll(PDO::FETCH_ASSOC);
                $shippers = ['中央'];
                foreach ($rows as $row) {
                    $name = !empty(trim($row['nickname'])) ? trim($row['nickname']) : trim($row['username']);
                    if ($name && !in_array($name, $shippers)) {
                        $shippers[] = $name;
                    }
                }
                sendResponse(true, "出货人列表获取成功", $shippers);
            } catch (PDOException $e) {
                sendResponse(true, "ok", ['中央']);
            }
            break;


        case 'code_by_product':
            // 根据product_name获取对应的product_code、specification、supplier和category
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }

            $stmt = $pdo->prepare("SELECT DISTINCT product_code, specification, supplier, category FROM stock_data WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) LIMIT 1");
            $stmt->execute([$productName, $productName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $category = trim((string) ($result['category'] ?? ''));
                // 兼容旧数据与大小写：Drinks / service line / Service line 等统一为 Service Line
                if (strtolower($category) === 'service line' || $category === 'Drinks') {
                    $category = 'Service Line';
                }
                sendResponse(true, "产品编号获取成功", [
                    'product_code' => $result['product_code'],
                    'specification' => $result['specification'],
                    'supplier' => $result['supplier'],
                    'category' => $category
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
                    WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) AND in_quantity > 0 AND deleted_at IS NULL
                    ORDER BY price DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName, $productName]);
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
                        COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) as total_in,
                        COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0) as total_out,
                        (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) - 
                        COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) as available_stock
                    FROM stockinout_data 
                    WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) AND deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName, $productName]);
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

            if ($price === null || $price === "") {
                sendResponse(false, "缺少价格参数");
            }

            $sql = "SELECT 
                        COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) as total_in,
                        COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0) as total_out,
                        (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) - 
                        COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) as available_stock
                    FROM stockinout_data 
                    WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) AND price = ? AND deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName, $productName, $price]);
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
            // 当同一货品名有多个供应商时，可传 code_number 只显示该编号（该供应商）的价格
            $productName = $_GET['product_name'] ?? null;
            $codeNumber = $_GET['code_number'] ?? null;
            $requiredQty = floatval($_GET['required_qty'] ?? 0);

            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }

            if ($requiredQty < 0) {
                sendResponse(false, "出库数量不能为负数");
            }

            try {
                // 获取该产品所有不同价格的库存情况（包括价格为0的记录）
                $sql = "SELECT 
                            price,
                            COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) as total_in,
                            COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0) as total_out,
                            (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) - 
                            COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) as available_stock
                        FROM stockinout_data 
                        WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) AND deleted_at IS NULL";
                $params = [$productName, $productName];
                if (!empty($codeNumber)) {
                    $sql .= " AND code_number = ?";
                    $params[] = $codeNumber;
                }
                $sql .= "
                        GROUP BY price
                        HAVING available_stock > 0
                        ORDER BY price DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
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
            $conditions = ["deleted_at IS NULL"];
            $params = [];

            // 排除货品异常（SOT）的记录
            $conditions[] = "(target_system IS NULL OR target_system != 'SOT')";

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
                '日期',
                '时间',
                '产品编号',
                '产品名称',
                '入库数量',
                '出库数量',
                '目标系统',
                '规格单位',
                '价格',
                '总价值',
                '收货人',
                '备注'
            ];
            fputcsv($output, $headers);

            // 写入数据
            foreach ($records as $record) {
                $inQty = floatval($record['in_quantity'] ?? 0);
                $outQty = floatval($record['out_quantity'] ?? 0);
                $price = floatval($record['price'] ?? 0);
                $netQty = $inQty - $outQty;
                $totalValue = $netQty * $price;

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

        case 'remark_numbers':
            // 获取所有唯一的备注编号
            $stmt = $pdo->prepare("SELECT DISTINCT remark_number FROM stockinout_data WHERE remark_number IS NOT NULL AND remark_number != '' AND deleted_at IS NULL ORDER BY remark_number");
            $stmt->execute();
            $remarkNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            sendResponse(true, "备注编号列表获取成功", $remarkNumbers);
            break;

        case 'product_remark_codes':
            // 获取指定产品在库的备注编号列表
            $productName = $_GET['product_name'] ?? '';
            if (!$productName) {
                sendResponse(false, '缺少产品名称参数');
            }

            $stmt = $pdo->prepare("
                SELECT remark_number
                FROM stockinout_data
                WHERE product_name = ?
                  AND product_remark_checked = 1
                  AND remark_number IS NOT NULL
                  AND remark_number != ''
                  AND deleted_at IS NULL
                GROUP BY remark_number
                HAVING (SUM(in_quantity) - SUM(out_quantity)) > 0
                ORDER BY remark_number ASC
            ");
            $stmt->execute([$productName]);
            $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            sendResponse(true, '在库备注编号列表获取成功', $codes);
            break;

        case 'deleted':
            // 获取回收站数据 (已删除记录)
            try {
                $results = [];
                $tables = [
                    'j1' => 'j1stockedit_data',
                    'j2' => 'j2stockedit_data',
                    'j3' => 'j3stockedit_data',
                    'central' => 'stockinout_data'
                ];

                foreach ($tables as $system => $table) {
                    $sql = "SELECT id, product_name, in_quantity, out_quantity, specification, receiver, date, deleted_at, deleted_by 
                            FROM $table 
                            WHERE deleted_at IS NOT NULL 
                            ORDER BY deleted_at DESC";
                    $stmt = $pdo->query($sql);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $row['system'] = $system;
                        $results[] = $row;
                    }
                }

                // 按删除时间排序
                usort($results, function ($a, $b) {
                    return strtotime($b['deleted_at'] ?? '') - strtotime($a['deleted_at'] ?? '');
                });

                sendResponse(true, "回收站数据获取成功", $results);
            } catch (PDOException $e) {
                sendResponse(false, "获取回收站数据失败: " . $e->getMessage());
            }
            break;

        case 'fix_branch_types':
            // 修复所有分店表中 type 为 NULL 的记录，从 stock_data 表的 category 字段获取
            try {
                $tables = [
                    'j1stockedit_data',
                    'j2stockedit_data',
                    'j3stockedit_data',
                    'j1stockinout_data',
                    'j2stockinout_data',
                    'j3stockinout_data',
                    'stockinout_data'
                ];

                $totalUpdated = 0;
                $details = [];

                foreach ($tables as $table) {
                    // 通过 product_name 匹配更新
                    $sql = "UPDATE $table t
                            INNER JOIN stock_data sd ON t.product_name = sd.product_name
                            SET t.type = CASE 
                                WHEN sd.category = 'Drinks' THEN 'Service Line'
                                WHEN LOWER(sd.category) = 'service line' THEN 'Service Line'
                                ELSE sd.category 
                            END
                            WHERE (t.type IS NULL OR t.type = '')
                            AND sd.category IS NOT NULL AND sd.category != ''
                            AND t.deleted_at IS NULL";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $updatedByName = $stmt->rowCount();

                    // 通过 code_number / product_code 匹配更新（补充上一步未匹配到的）
                    $sql2 = "UPDATE $table t
                             INNER JOIN stock_data sd ON t.code_number = sd.product_code
                             SET t.type = CASE 
                                 WHEN sd.category = 'Drinks' THEN 'Service Line'
                                 WHEN LOWER(sd.category) = 'service line' THEN 'Service Line'
                                 ELSE sd.category 
                             END
                             WHERE (t.type IS NULL OR t.type = '')
                             AND sd.category IS NOT NULL AND sd.category != ''
                             AND t.deleted_at IS NULL";

                    $stmt2 = $pdo->prepare($sql2);
                    $stmt2->execute();
                    $updatedByCode = $stmt2->rowCount();

                    $tableTotal = $updatedByName + $updatedByCode;
                    $totalUpdated += $tableTotal;

                    if ($tableTotal > 0) {
                        $details[] = "$table: 更新了 $tableTotal 条记录";
                    }
                }

                $message = "修复完成，共更新了 $totalUpdated 条记录";
                if (!empty($details)) {
                    $message .= "。详情：" . implode('；', $details);
                }

                sendResponse(true, $message, [
                    'total_updated' => $totalUpdated,
                    'details' => $details
                ]);

            } catch (PDOException $e) {
                sendResponse(false, "修复失败：" . $e->getMessage());
            }
            break;

        case 'product_batches_for_hifo':
            // HIFO 拆行专用：按 remark_number + price 分组返回可用批次（价格降序）
            $productName = $_GET['product_name'] ?? null;
            $codeNumber  = $_GET['code_number']  ?? null;

            if (!$productName) {
                sendResponse(false, "缺少产品名称参数");
            }

            try {
                // 查询带有 remark_number 的批次（按 remark_number 级别分组）
                $sql = "SELECT
                            price,
                            remark_number,
                            product_remark_checked,
                            (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) -
                             COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) AS available_stock
                        FROM stockinout_data
                        WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&'))
                          AND deleted_at IS NULL
                          AND (target_system IS NULL OR target_system != 'SOT')";
                $params = [$productName, $productName];

                if (!empty($codeNumber)) {
                    $sql .= " AND code_number = ?";
                    $params[] = $codeNumber;
                }

                $sql .= " GROUP BY price, remark_number, product_remark_checked
                          HAVING available_stock > 0
                          ORDER BY price DESC, remark_number ASC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $result = [];
                foreach ($rows as $row) {
                    $result[] = [
                        'price'                  => $row['price'],
                        'remark_number'          => $row['remark_number'] ?? '',
                        'product_remark_checked' => intval($row['product_remark_checked'] ?? 0),
                        'available_stock'        => round(floatval($row['available_stock']), 3)
                    ];
                }

                sendResponse(true, "HIFO批次数据获取成功", $result);

            } catch (PDOException $e) {
                sendResponse(false, "查询HIFO批次数据失败：" . $e->getMessage());
            }
            break;

        default:
            sendResponse(false, "无效的操作");
    }
}

/**
 * 为单个前缀分配下一个可用备注编号
 * @param PDO    $pdo
 * @param string $prefix   如 'SH'
 * @param array  $alreadyAssigned 本次事务内已占用的数字（避免同批次重复）
 * @return string  如 'SH-015'
 */
function generateRemarkCode(PDO $pdo, string $prefix, array &$alreadyAssigned): string
{
    // 一次查询：历史编号 MAX + 在库冲突集合
    $stmt = $pdo->prepare("
        SELECT remark_number,
               SUM(in_quantity)  AS total_in,
               SUM(out_quantity) AS total_out
        FROM stockinout_data
        WHERE remark_number LIKE ? AND deleted_at IS NULL
        GROUP BY remark_number
    ");
    $stmt->execute([$prefix . '-%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lastVal = 0;
    $inStockSet = [];
    $pattern = '/^' . preg_quote($prefix, '/') . '-(\d{1,3})$/';

    foreach ($rows as $h) {
        if (preg_match($pattern, $h['remark_number'], $m)) {
            $num = intval($m[1]);
            $lastVal = max($lastVal, $num);
            $net = floatval($h['total_in']) - floatval($h['total_out']);
            if ($net > 0)
                $inStockSet[$num] = true;
        }
    }

    // 999 循环递增 + 避让在库 + 避让本批次已占用
    $current = $lastVal;
    $tries = 0;
    do {
        $current = ($current % 999) + 1;
        $tries++;
        if ($tries > 999) {
            throw new Exception("前缀[$prefix]无可用编号（所有999个编号均在库中）");
        }
    } while (isset($inStockSet[$current]) || in_array($current, $alreadyAssigned[$prefix] ?? []));

    $alreadyAssigned[$prefix][] = $current;
    return $prefix . '-' . str_pad($current, 3, '0', STR_PAD_LEFT);
}

/**
 * 计算货品名称前缀（最多取前两个单词的首字母）
 */
function computePrefix(string $productName): string
{
    $clean_name = strtoupper(trim($productName));
    $words = preg_split('/\s+/', $clean_name, -1, PREG_SPLIT_NO_EMPTY);

    if (empty($words))
        return '';

    if (count($words) == 1) {
        $lettersOnly = preg_replace('/[^\p{L}\p{N}]/u', '', $words[0]);
        return mb_substr($lettersOnly, 0, 2);
    } else {
        $firstLetter = mb_substr(preg_replace('/[^\p{L}\p{N}]/u', '', $words[0]), 0, 1);
        $secondLetter = mb_substr(preg_replace('/[^\p{L}\p{N}]/u', '', $words[1]), 0, 1);
        return $firstLetter . $secondLetter;
    }
}

// 处理 POST 请求 - 添加新记录（修改版支持双重保存）
function handlePost()
{
    global $pdo, $data;

    if (!$data) {
        sendResponse(false, "无效的数据格式");
    }

    // 优先处理批量保存
    if (($data['action'] ?? '') === 'batch_save') {
        handleBatchSave();
        return;
    }

    // 处理恢复操作
    if (($data['action'] ?? '') === 'restore') {
        $ids = $data['ids'] ?? [];
        $system = $data['system'] ?? '';
        if (empty($ids)) {
            sendResponse(false, "缺少要恢复的记录ID");
        }

        try {
            $pdo->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            if ($system === 'central' || empty($system)) {
                // 1. 恢复主表
                $sql = "UPDATE stockinout_data SET deleted_at = NULL, deleted_by = NULL WHERE id IN ($placeholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($ids);

                // 2. 对于 Central 记录，同步恢复分支系统记录
                foreach ($ids as $currentId) {
                    $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ?");
                    $stmt->execute([$currentId]);
                    $record = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($record) {
                        $targetSystem = strtolower($record['target_system'] ?? '');
                        if (in_array($targetSystem, ['j1', 'j2', 'j3'])) {
                            // 恢复分支编辑表和进出库表
                            $pdo->prepare("UPDATE {$targetSystem}stockedit_data SET deleted_at = NULL, deleted_by = NULL 
                                           WHERE product_name = ? AND date = ? AND receiver = ? AND deleted_at IS NOT NULL")
                                ->execute([$record['product_name'], $record['date'], $record['receiver']]);

                            $pdo->prepare("UPDATE {$targetSystem}stockinout_data SET deleted_at = NULL, deleted_by = NULL 
                                           WHERE main_record_id = ? AND deleted_at IS NOT NULL")
                                ->execute([$currentId]);
                        }
                    }
                }
            } else {
                // 恢复特定的子系统记录 (J1, J2, J3)
                $tables = [];
                if ($system === 'j1')
                    $tables = ['j1stockedit_data', 'j1stockinout_data', 'j1stockeditmobile_data'];
                if ($system === 'j2')
                    $tables = ['j2stockedit_data', 'j2stockinout_data', 'j2stockeditmobile_data'];
                if ($system === 'j3')
                    $tables = ['j3stockedit_data', 'j3stockinout_data', 'j3stockeditmobile_data'];

                foreach ($tables as $table) {
                    try {
                        $idField = (strpos($table, 'inout') !== false) ? 'main_record_id' : 'id';
                        $sql = "UPDATE $table SET deleted_at = NULL, deleted_by = NULL WHERE $idField IN ($placeholders)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($ids);

                        // 如果是主表恢复，同步恢复关联的中心库记录
                        if ($table === "{$system}stockedit_data") {
                            foreach ($ids as $currentId) {
                                $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
                                $stmt->execute([$currentId]);
                                $record = $stmt->fetch(PDO::FETCH_ASSOC);

                                if ($record && strtolower($record['target_system'] ?? '') === 'central') {
                                    $pdo->prepare("UPDATE stockinout_data SET deleted_at = NULL, deleted_by = NULL 
                                                   WHERE product_name = ? AND date = ? AND receiver = ? AND target_system = 'central' AND deleted_at IS NOT NULL
                                                   ORDER BY id DESC LIMIT 1")
                                        ->execute([$record['product_name'], $record['date'], $record['receiver']]);
                                }
                            }
                        }
                    } catch (PDOException $e) {
                        error_log("恢复表 $table 失败: " . $e->getMessage());
                    }
                }
            }

            $pdo->commit();
            sendResponse(true, "记录已成功恢复");
        } catch (PDOException $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            sendResponse(false, "恢复失败: " . $e->getMessage());
        }
        return;
    }

    // 验证必填字段
    $required_fields = ['date', 'time', 'product_name', 'receiver', 'price'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            sendResponse(false, "缺少必填字段：$field");
        }
    }

    // 验证进出货互斥
    $in_qty = isset($data['in_quantity']) ? floatval($data['in_quantity']) : 0;
    $out_qty = isset($data['out_quantity']) ? floatval($data['out_quantity']) : 0;
    if ($in_qty > 0 && $out_qty > 0) {
        sendResponse(false, "进货和出货数量不能同时大于0");
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

        // ========== 库存校验（出库时检查） ==========
        if ($outQuantity > 0) {
            $stockSql = "SELECT 
                            (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) - 
                            COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) as available_stock
                        FROM stockinout_data 
                        WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) 
                        AND CAST(price AS DECIMAL(15,6)) = CAST(? AS DECIMAL(15,6)) AND deleted_at IS NULL
                        AND (target_system IS NULL OR target_system != 'SOT')";
            $stockStmt = $pdo->prepare($stockSql);
            $stockStmt->execute([$data['product_name'], $data['product_name'], $data['price']]);
            $stockRow = $stockStmt->fetch(PDO::FETCH_ASSOC);
            $availableStock = floatval($stockRow['available_stock'] ?? 0);

            if ($outQuantity > $availableStock) {
                $pdo->rollBack();
                sendResponse(false, "产品 [{$data['product_name']}] (价格 RM{$data['price']}) 库存不足！可用库存: {$availableStock}，请求出库: {$outQuantity}");
            }
        }
        // ========== 库存校验结束 ==========

        // ====== 进货自动生码 / 出货备注校验 ======
        $isIncoming = floatval($data['in_quantity'] ?? 0) > 0;
        $isOutgoing = floatval($data['out_quantity'] ?? 0) > 0;

        if ($isIncoming && !empty($data['needGenerateCode'])) {
            $prefix = strtoupper(trim($data['prefix'] ?? computePrefix($data['product_name'])));
            if (!$prefix)
                throw new Exception('无法计算前缀，请确认货品名称不为空');
            $alreadyAssigned = [];
            $data['remark_number'] = generateRemarkCode($pdo, $prefix, $alreadyAssigned);
            $data['product_remark_checked'] = 1;
        }

        if ($isOutgoing) {
            $rmStmt = $pdo->prepare("
                SELECT remark_number FROM stockinout_data
                WHERE product_name = ? AND product_remark_checked = 1
                  AND remark_number IS NOT NULL AND remark_number != ''
                  AND deleted_at IS NULL
                GROUP BY remark_number
                HAVING (SUM(in_quantity) - SUM(out_quantity)) > 0 LIMIT 1
            ");
            $rmStmt->execute([$data['product_name']]);
            if ($rmStmt->fetchColumn() !== false) {
                $outRemark = trim($data['remark_number'] ?? '');
                if (!$outRemark) {
                    throw new Exception("货品 [{$data['product_name']}] 有备注编码在库，出货时必须填写备注编号");
                }
                $validStmt = $pdo->prepare("
                    SELECT 1 FROM stockinout_data
                    WHERE product_name = ? AND remark_number = ? AND product_remark_checked = 1 AND deleted_at IS NULL
                    GROUP BY remark_number HAVING (SUM(in_quantity) - SUM(out_quantity)) > 0
                ");
                $validStmt->execute([$data['product_name'], $outRemark]);
                if (!$validStmt->fetchColumn()) {
                    throw new Exception("备注编号 [{$outRemark}] 不在库中");
                }
            }
        }

        $sql = "INSERT INTO stockinout_data 
                (date, time, product_name, receiver, in_quantity, out_quantity, 
                specification, price, code_number, remark, target_system, product_remark_checked, remark_number, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // 获取当前用户名
        $createdBy = $_SESSION['username'] ?? 'System';

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
            $data['remark_number'] ?? '',
            $createdBy
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
        $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        $newRecord['approval_status'] = (!empty($newRecord['approver'])) ? 'approved' : 'pending';
        // 解析 created_by 为 nickname 再返回给前端
        $resolved = resolveCreatedByNicknames($pdo, [$newRecord]);
        $newRecord = $resolved[0];

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

/**
 * 处理批量保存请求
 * 预期负载结构: { "action": "batch_save", "document_date": "YYYY-MM-DD", "rows": [...] }
 */
function handleBatchSave()
{
    global $pdo, $data;

    $rows = $data['rows'] ?? [];

    if (empty($rows)) {
        sendResponse(false, "没有需要保存的数据行");
    }

    try {
        $pdo->beginTransaction();

        // ========== 库存校验 ==========
        // 汇总每个 product_name + price 组合的总出库量
        $outSummary = [];
        foreach ($rows as $row) {
            $outQty = floatval($row['out_quantity'] ?? 0);
            if ($outQty > 0) {
                $key = ($row['product_name'] ?? '') . '||' . ($row['price'] ?? 0);
                if (!isset($outSummary[$key])) {
                    $outSummary[$key] = [
                        'product_name' => $row['product_name'],
                        'price' => $row['price'] ?? 0,
                        'total_out' => 0
                    ];
                }
                $outSummary[$key]['total_out'] += $outQty;
            }
        }

        // 检查每个组合的库存是否足够
        foreach ($outSummary as $item) {
            $stockSql = "SELECT 
                            (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) - 
                            COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) as available_stock
                        FROM stockinout_data 
                        WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) 
                        AND CAST(price AS DECIMAL(15,6)) = CAST(? AS DECIMAL(15,6)) AND deleted_at IS NULL
                        AND (target_system IS NULL OR target_system != 'SOT')";
            $stockStmt = $pdo->prepare($stockSql);
            $stockStmt->execute([$item['product_name'], $item['product_name'], $item['price']]);
            $stockRow = $stockStmt->fetch(PDO::FETCH_ASSOC);
            $availableStock = floatval($stockRow['available_stock'] ?? 0);

            if ($item['total_out'] > $availableStock) {
                throw new Exception("产品 [{$item['product_name']}] (价格 RM{$item['price']}) 库存不足！可用库存: {$availableStock}，请求出库: {$item['total_out']}");
            }
        }
        // ========== 库存校验结束 ==========

        // ====== 进货自动生码 ======
        $alreadyAssigned = [];
        foreach ($rows as $idx => &$row) {
            $isIncoming = floatval($row['in_quantity'] ?? 0) > 0;
            if ($isIncoming && !empty($row['needGenerateCode'])) {
                $prefix = strtoupper(trim($row['prefix'] ?? computePrefix($row['product_name'])));
                if (!$prefix)
                    throw new Exception("第" . ($idx + 1) . "行无法计算前缀");
                $row['remark_number'] = generateRemarkCode($pdo, $prefix, $alreadyAssigned);
                $row['product_remark_checked'] = 1;
            }
        }
        unset($row);

        // ====== 出货备注编号校验 ======
        foreach ($rows as $idx => $row) {
            $isOutgoing = floatval($row['out_quantity'] ?? 0) > 0;
            if (!$isOutgoing)
                continue;

            $rmStmt = $pdo->prepare("
                SELECT remark_number FROM stockinout_data
                WHERE product_name = ? AND product_remark_checked = 1
                  AND remark_number IS NOT NULL AND remark_number != ''
                  AND deleted_at IS NULL
                GROUP BY remark_number
                HAVING (SUM(in_quantity) - SUM(out_quantity)) > 0 LIMIT 1
            ");
            $rmStmt->execute([$row['product_name']]);
            if ($rmStmt->fetchColumn() !== false) {
                $outRemark = trim($row['remark_number'] ?? '');
                if (!$outRemark) {
                    throw new Exception("第" . ($idx + 1) . "行：货品 [{$row['product_name']}] 有备注编码在库，出货时必须填写备注编号");
                }
                $validStmt = $pdo->prepare("
                    SELECT 1 FROM stockinout_data
                    WHERE product_name = ? AND remark_number = ? AND product_remark_checked = 1 AND deleted_at IS NULL
                    GROUP BY remark_number HAVING (SUM(in_quantity) - SUM(out_quantity)) > 0
                ");
                $validStmt->execute([$row['product_name'], $outRemark]);
                if (!$validStmt->fetchColumn()) {
                    throw new Exception("第" . ($idx + 1) . "行：备注编号 [{$outRemark}] 不在库中");
                }
            }
        }

        $sql = "INSERT INTO stockinout_data 
                (date, time, product_name, receiver, in_quantity, out_quantity, 
                specification, price, code_number, remark, target_system, product_remark_checked, remark_number, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // 获取当前用户名
        $createdBy = $_SESSION['username'] ?? 'System';

        $successCount = 0;
        foreach ($rows as $index => $row) {
            $rowNum = $index + 1;

            // 验证每行的必填字段 (包含日期和价格)
            $required = ['date', 'time', 'product_name', 'receiver', 'price'];
            foreach ($required as $field) {
                if (!isset($row[$field]) || $row[$field] === '') {
                    throw new Exception("第 {$rowNum} 行缺少必填字段：{$field}");
                }
            }

            $rowDate = $row['date'];

            $stmt->execute([
                $rowDate, // 使用每行自带的日期
                $row['time'],
                $row['product_name'],
                $row['receiver'],
                $row['in_quantity'] ?? 0,
                $row['out_quantity'] ?? 0,
                $row['specification'] ?? null,
                $row['price'] ?? 0,
                $row['code_number'] ?? null,
                $row['remark'] ?? null,
                $row['target_system'] ?? null,
                $row['product_remark_checked'] ?? 0,
                $row['remark_number'] ?? '',
                $createdBy
            ]);

            $newId = $pdo->lastInsertId();
            $successCount++;

            // 处理出库记录同步到子系统逻辑
            $outQty = floatval($row['out_quantity'] ?? 0);
            if ($outQty > 0) {
                $targetSystem = $row['target_system'] ?? 'j1';
                // 使用单行数据进行同步
                $syncData = $row;

                if ($targetSystem === 'j1') {
                    saveToJ1Table($pdo, $syncData, $newId);
                    saveToJ1EditTable($pdo, $syncData, $newId);
                } elseif ($targetSystem === 'j2') {
                    saveToJ2Table($pdo, $syncData, $newId);
                    saveToJ2EditTable($pdo, $syncData, $newId);
                } elseif ($targetSystem === 'j3') {
                    saveToJ3Table($pdo, $syncData, $newId);
                    saveToJ3EditTable($pdo, $syncData, $newId);
                }
            }
        }

        $pdo->commit();
        sendResponse(true, "批量保存成功，共保存 {$successCount} 条记录");

    } catch (Exception $e) {
        $pdo->rollBack();
        sendResponse(false, "批量保存失败：" . $e->getMessage());
    }
}

// 处理批准请求
function handleApprove()
{
    global $pdo, $data;

    // 检查用户权限
    session_start();
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, "用户未登录");
    }

    // 检查用户是否使用了允许的注册码
    $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP', 'IT7890'];
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
        $sql = "UPDATE stockinout_data SET approver = ? WHERE id = ? AND deleted_at IS NULL";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$approver, $id]);

        if ($stmt->rowCount() > 0) {
            // 获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ? AND deleted_at IS NULL");
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
function handlePut()
{
    global $pdo, $data;

    if (!$data || !isset($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }

    // 验证必填字段
    $required_fields = ['date', 'time', 'product_name', 'receiver', 'price'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            sendResponse(false, "缺少必填字段：$field");
        }
    }

    // 验证进出货互斥
    $in_qty = isset($data['in_quantity']) ? floatval($data['in_quantity']) : 0;
    $out_qty = isset($data['out_quantity']) ? floatval($data['out_quantity']) : 0;
    if ($in_qty > 0 && $out_qty > 0) {
        sendResponse(false, "进货和出货数量不能同时大于0");
    }



    try {
        // 先获取原始记录，用于比较target_system是否发生变化
        $originalStmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ? AND deleted_at IS NULL");
        $originalStmt->execute([$data['id']]);
        $originalRecord = $originalStmt->fetch(PDO::FETCH_ASSOC);

        if (!$originalRecord) {
            sendResponse(false, "记录不存在");
        }

        $originalTargetSystem = $originalRecord['target_system'] ?? 'j1';
        $newTargetSystem = $data['target_system'] ?? 'j1';
        $outQuantity = floatval($data['out_quantity'] ?? 0);

        // 检测货品是否被更换（检查货品名称、编号或规格）
        $productChanged = (
            $originalRecord['product_name'] !== $data['product_name'] ||
            $originalRecord['code_number'] !== ($data['code_number'] ?? null) ||
            $originalRecord['specification'] !== ($data['specification'] ?? null)
        );

        // 开始事务
        $pdo->beginTransaction();

        // 如果货品被更换，需要先清理原货品在目标系统的记录
        if ($productChanged) {
            error_log("检测到货品更换: {$originalRecord['product_name']} -> {$data['product_name']}");

            $originalOutQty = floatval($originalRecord['out_quantity'] ?? 0);

            // 如果原记录是出库，需要清理目标系统的记录
            // 因为货品更换后，这些记录将关联到新货品
            if ($originalOutQty > 0) {
                if ($originalTargetSystem === 'j1') {
                    $j1DeleteSql = "DELETE FROM j1stockinout_data WHERE main_record_id = ?";
                    $j1DelStmt = $pdo->prepare($j1DeleteSql);
                    $j1DelStmt->execute([$data['id']]);

                    $j1EditDeleteSql = "DELETE FROM j1stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j1'";
                    $j1EditDelStmt = $pdo->prepare($j1EditDeleteSql);
                    $j1EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                    error_log("已清理原货品在J1表中的记录");
                } elseif ($originalTargetSystem === 'j2') {
                    $j2DeleteSql = "DELETE FROM j2stockinout_data WHERE main_record_id = ?";
                    $j2DelStmt = $pdo->prepare($j2DeleteSql);
                    $j2DelStmt->execute([$data['id']]);

                    $j2EditDeleteSql = "DELETE FROM j2stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j2'";
                    $j2EditDelStmt = $pdo->prepare($j2EditDeleteSql);
                    $j2EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                    error_log("已清理原货品在J2表中的记录");
                } elseif ($originalTargetSystem === 'j3') {
                    $j3DeleteSql = "DELETE FROM j3stockinout_data WHERE main_record_id = ?";
                    $j3DelStmt = $pdo->prepare($j3DeleteSql);
                    $j3DelStmt->execute([$data['id']]);

                    $j3EditDeleteSql = "DELETE FROM j3stockedit_data WHERE product_name = ? AND receiver = ? AND target_system = 'j3'";
                    $j3EditDelStmt = $pdo->prepare($j3EditDeleteSql);
                    $j3EditDelStmt->execute([$originalRecord['product_name'], $originalRecord['receiver']]);
                    error_log("已清理原货品在J3表中的记录");
                }
            }
        }

        // 更新主表记录
        $sql = "UPDATE stockinout_data 
                SET date = ?, time = ?, product_name = ?, receiver = ?,
                    in_quantity = ?, out_quantity = ?, 
                    specification = ?, price = ?, code_number = ?, remark = ?, 
                    target_system = ?, product_remark_checked = ?, remark_number = ?
                WHERE id = ? AND deleted_at IS NULL";

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
            $totalValue = $outQuantity * floatval($data['price'] ?? 0);

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
                $j1CheckSql = "SELECT COUNT(*) FROM j1stockinout_data WHERE main_record_id = ? AND deleted_at IS NULL";
                $j1CheckStmt = $pdo->prepare($j1CheckSql);
                $j1CheckStmt->execute([$data['id']]);
                $j1Exists = $j1CheckStmt->fetchColumn() > 0;

                if ($j1Exists) {
                    // 更新J1stockinout_data表
                    $j1UpdateSql = "UPDATE j1stockinout_data 
                                    SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                        in_quantity = ?, out_quantity = ?, specification = ?, price = ?, total_value = ?, receiver = ?, remark = ?, target_system = ?
                                    WHERE main_record_id = ? AND deleted_at IS NULL";

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
                // 使用日期、时间、产品名称和旧的receiver来精确匹配记录
                $originalReceiver = $originalRecord['receiver'] ?? null;
                $newReceiver = $data['receiver'] ?? null;
                $receiverChanged = ($originalReceiver !== $newReceiver);

                // 先尝试用日期、时间和旧的receiver值查找记录（如果receiver改变了）
                $j1EditCheckSql = "SELECT COUNT(*) FROM j1stockedit_data 
                                    WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j1' AND deleted_at IS NULL";
                $j1EditCheckStmt = $pdo->prepare($j1EditCheckSql);
                $j1EditCheckStmt->execute([
                    $data['product_name'],
                    $data['date'],
                    $data['time'],
                    $receiverChanged ? $originalReceiver : $newReceiver
                ]);
                $j1EditExists = $j1EditCheckStmt->fetchColumn() > 0;

                if ($j1EditExists) {
                    // 更新J1stockedit_data表（使用日期、时间和旧的receiver作为WHERE条件）
                    $j1EditUpdateSql = "UPDATE j1stockedit_data 
                                        SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                            in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                        WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j1' AND deleted_at IS NULL
                                        LIMIT 1";

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
                        $data['date'], // 用于WHERE条件
                        $data['time'], // 用于WHERE条件
                        $receiverChanged ? $originalReceiver : $newReceiver // 使用旧的receiver作为WHERE条件
                    ]);
                } else {
                    // 如果没找到，再尝试用日期、时间和新的receiver值查找
                    $j1EditCheckSql2 = "SELECT COUNT(*) FROM j1stockedit_data 
                                        WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j1' AND deleted_at IS NULL";
                    $j1EditCheckStmt2 = $pdo->prepare($j1EditCheckSql2);
                    $j1EditCheckStmt2->execute([$data['product_name'], $data['date'], $data['time'], $newReceiver]);
                    $j1EditExists2 = $j1EditCheckStmt2->fetchColumn() > 0;

                    if ($j1EditExists2) {
                        // 更新J1stockedit_data表（使用日期、时间和新的receiver作为WHERE条件）
                        $j1EditUpdateSql2 = "UPDATE j1stockedit_data 
                                            SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                                in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                            WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j1' AND deleted_at IS NULL
                                            LIMIT 1";

                        $j1EditStmt2 = $pdo->prepare($j1EditUpdateSql2);
                        $j1EditStmt2->execute([
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
                            $data['date'], // 用于WHERE条件
                            $data['time'], // 用于WHERE条件
                            $newReceiver // 使用新的receiver作为WHERE条件
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
                }

                error_log("已同步更新J1表和J1Edit表记录");

            } elseif ($newTargetSystem === 'j2') {
                // 检查J2表中是否已存在记录
                $j2CheckSql = "SELECT COUNT(*) FROM j2stockinout_data WHERE main_record_id = ? AND deleted_at IS NULL";
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
                // 使用日期、时间、产品名称和旧的receiver来精确匹配记录
                $originalReceiver = $originalRecord['receiver'] ?? null;
                $newReceiver = $data['receiver'] ?? null;
                $receiverChanged = ($originalReceiver !== $newReceiver);

                // 先尝试用日期、时间和旧的receiver值查找记录（如果receiver改变了）
                $j2EditCheckSql = "SELECT COUNT(*) FROM j2stockedit_data 
                                    WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j2' AND deleted_at IS NULL";
                $j2EditCheckStmt = $pdo->prepare($j2EditCheckSql);
                $j2EditCheckStmt->execute([
                    $data['product_name'],
                    $data['date'],
                    $data['time'],
                    $receiverChanged ? $originalReceiver : $newReceiver
                ]);
                $j2EditExists = $j2EditCheckStmt->fetchColumn() > 0;

                if ($j2EditExists) {
                    // 更新J2stockedit_data表（使用日期、时间和旧的receiver作为WHERE条件）
                    $j2EditUpdateSql = "UPDATE j2stockedit_data 
                                        SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                            in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                        WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j2' AND deleted_at IS NULL
                                        LIMIT 1";

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
                        $data['date'], // 用于WHERE条件
                        $data['time'], // 用于WHERE条件
                        $receiverChanged ? $originalReceiver : $newReceiver // 使用旧的receiver作为WHERE条件
                    ]);
                } else {
                    // 如果没找到，再尝试用日期、时间和新的receiver值查找
                    $j2EditCheckSql2 = "SELECT COUNT(*) FROM j2stockedit_data 
                                        WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j2' AND deleted_at IS NULL";
                    $j2EditCheckStmt2 = $pdo->prepare($j2EditCheckSql2);
                    $j2EditCheckStmt2->execute([$data['product_name'], $data['date'], $data['time'], $newReceiver]);
                    $j2EditExists2 = $j2EditCheckStmt2->fetchColumn() > 0;

                    if ($j2EditExists2) {
                        // 更新J2stockedit_data表（使用日期、时间和新的receiver作为WHERE条件）
                        $j2EditUpdateSql2 = "UPDATE j2stockedit_data 
                                            SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                                in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                            WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j2' AND deleted_at IS NULL
                                            LIMIT 1";

                        $j2EditStmt2 = $pdo->prepare($j2EditUpdateSql2);
                        $j2EditStmt2->execute([
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
                            $data['date'], // 用于WHERE条件
                            $data['time'], // 用于WHERE条件
                            $newReceiver // 使用新的receiver作为WHERE条件
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
                }

                error_log("已同步更新J2表和J2Edit表记录");

            } elseif ($newTargetSystem === 'j3') {
                // 检查J3表中是否已存在记录
                $j3CheckSql = "SELECT COUNT(*) FROM j3stockinout_data WHERE main_record_id = ? AND deleted_at IS NULL";
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
                // 使用日期、时间、产品名称和旧的receiver来精确匹配记录
                $originalReceiver = $originalRecord['receiver'] ?? null;
                $newReceiver = $data['receiver'] ?? null;
                $receiverChanged = ($originalReceiver !== $newReceiver);

                // 先尝试用日期、时间和旧的receiver值查找记录（如果receiver改变了）
                $j3EditCheckSql = "SELECT COUNT(*) FROM j3stockedit_data 
                                    WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j3' AND deleted_at IS NULL";
                $j3EditCheckStmt = $pdo->prepare($j3EditCheckSql);
                $j3EditCheckStmt->execute([
                    $data['product_name'],
                    $data['date'],
                    $data['time'],
                    $receiverChanged ? $originalReceiver : $newReceiver
                ]);
                $j3EditExists = $j3EditCheckStmt->fetchColumn() > 0;

                if ($j3EditExists) {
                    // 更新J3stockedit_data表（使用日期、时间和旧的receiver作为WHERE条件）
                    $j3EditUpdateSql = "UPDATE j3stockedit_data 
                                        SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                            in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                        WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j3' AND deleted_at IS NULL
                                        LIMIT 1";

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
                        $data['date'], // 用于WHERE条件
                        $data['time'], // 用于WHERE条件
                        $receiverChanged ? $originalReceiver : $newReceiver // 使用旧的receiver作为WHERE条件
                    ]);
                } else {
                    // 如果没找到，再尝试用日期、时间和新的receiver值查找
                    $j3EditCheckSql2 = "SELECT COUNT(*) FROM j3stockedit_data 
                                        WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j3' AND deleted_at IS NULL";
                    $j3EditCheckStmt2 = $pdo->prepare($j3EditCheckSql2);
                    $j3EditCheckStmt2->execute([$data['product_name'], $data['date'], $data['time'], $newReceiver]);
                    $j3EditExists2 = $j3EditCheckStmt2->fetchColumn() > 0;

                    if ($j3EditExists2) {
                        // 更新J3stockedit_data表（使用日期、时间和新的receiver作为WHERE条件）
                        $j3EditUpdateSql2 = "UPDATE j3stockedit_data 
                                            SET date = ?, time = ?, code_number = ?, product_name = ?, 
                                                in_quantity = ?, out_quantity = ?, specification = ?, price = ?, receiver = ?, remark = ?, target_system = ?
                                            WHERE product_name = ? AND date = ? AND time = ? AND receiver = ? AND target_system = 'j3' AND deleted_at IS NULL
                                            LIMIT 1";

                        $j3EditStmt2 = $pdo->prepare($j3EditUpdateSql2);
                        $j3EditStmt2->execute([
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
                            $data['date'], // 用于WHERE条件
                            $data['time'], // 用于WHERE条件
                            $newReceiver // 使用新的receiver作为WHERE条件
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
        $stmt = $pdo->prepare("SELECT * FROM stockinout_data WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$data['id']]);
        $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        // 解析 created_by 为 nickname 再返回给前端
        $resolvedUpd = resolveCreatedByNicknames($pdo, [$updatedRecord]);
        $updatedRecord = $resolvedUpd[0];

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

function updateStocklistTotal($pdo, $branch, $productName, $codeNumber, $inQty, $outQty, $specification = null)
{
    if (empty($productName) || empty($branch)) {
        return;
    }

    $tableName = $branch . 'stocklist_total';

    try {
        $specification = ($specification === "none" || $specification === "") ? null : $specification;

        $sql = "SELECT * FROM {$tableName} WHERE product_name = ? AND code_number = ? ";
        $params = [$productName, $codeNumber];

        if ($specification === null) {
            $sql .= " AND (specification IS NULL OR specification = '' OR specification = 'none') ";
        } else {
            $sql .= " AND specification = ? ";
            $params[] = $specification;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        // Reverse the quantities for deletion
        $netQty = -($inQty - $outQty);

        if ($existing) {
            $newTotal = floatval($existing['total_qty']) + $netQty;

            if ($newTotal <= 0.0001 && $newTotal >= -0.0001) {
                $deleteStmt = $pdo->prepare("DELETE FROM {$tableName} WHERE id = ?");
                $deleteStmt->execute([$existing['id']]);
            } else {
                $updateStmt = $pdo->prepare("UPDATE {$tableName} SET total_qty = ?, last_updated = NOW() WHERE id = ?");
                $updateStmt->execute([$newTotal, $existing['id']]);
            }
        }
    } catch (PDOException $e) {
        error_log("更新库存总数失败 (sync delete): " . $e->getMessage());
    }
}

function handleDelete()
{
    global $pdo;
    session_start();
    $username = $_SESSION['username'] ?? 'System';

    $id = $_GET['id'] ?? null;
    $ids = $_GET['ids'] ?? $_GET['id'] ?? null; // 支持批量删除
    $action = $_GET['action'] ?? '';

    if (!$ids) {
        sendResponse(false, "缺少记录ID");
    }

    $targetIds = explode(',', $ids);

    // 如果 action 是 permanent，执行物理删除
    if ($action === 'permanent') {
        $system = $_GET['system'] ?? '';
        try {
            $pdo->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($targetIds), '?'));

            $tables = [];
            if ($system === 'central') {
                $tables = [
                    'stockinout_data',
                    'j1stockinout_data',
                    'j2stockinout_data',
                    'j3stockinout_data'
                ];
            } elseif ($system === 'j1') {
                $tables = ['j1stockedit_data', 'j1stockinout_data', 'j1stockeditmobile_data'];
            } elseif ($system === 'j2') {
                $tables = ['j2stockedit_data', 'j2stockinout_data', 'j2stockeditmobile_data'];
            } elseif ($system === 'j3') {
                $tables = ['j3stockedit_data', 'j3stockinout_data', 'j3stockeditmobile_data'];
            } else {
                // 回退逻辑，如果没传 system
                $tables = ['j1stockedit_data', 'j2stockedit_data', 'j3stockedit_data', 'stockinout_data', 'j1stockinout_data', 'j2stockinout_data', 'j3stockinout_data'];
            }

            foreach ($tables as $table) {
                try {
                    $idField = 'id';
                    // 中心库彻底删除时，分支系统的 inout 表使用 main_record_id 来关联中心库 ID
                    $branchInoutTables = ['j1stockinout_data', 'j2stockinout_data', 'j3stockinout_data'];
                    if ($system === 'central' && in_array($table, $branchInoutTables)) {
                        $idField = 'main_record_id';
                    }

                    if ($system !== 'central' && in_array($table, ['j1stockedit_data', 'j2stockedit_data', 'j3stockedit_data', 'j1stockeditmobile_data', 'j2stockeditmobile_data', 'j3stockeditmobile_data'])) {
                        // For the branch tables, we use date, time, product_name, and receiver
                        // Since we just have the IDs, we need to fetch the record first to get the exact matching details
                        foreach ($targetIds as $id) {
                            $fetchStmt = $pdo->prepare("SELECT date, time, product_name, receiver FROM $table WHERE id = ?");
                            $fetchStmt->execute([$id]);
                            $record = $fetchStmt->fetch(PDO::FETCH_ASSOC);

                            if ($record) {
                                $sql = "DELETE FROM $table WHERE date = ? AND time = ? AND product_name = ? AND (receiver = ? OR receiver = 'Mobile' OR receiver = 'mobile') AND deleted_at IS NOT NULL";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$record['date'], $record['time'], $record['product_name'], $record['receiver']]);
                            }
                        }
                    } else {
                        $sql = "DELETE FROM $table WHERE $idField IN ($placeholders) AND deleted_at IS NOT NULL";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($targetIds);
                    }
                } catch (PDOException $e) {
                    // 忽略错误
                }
            }

            $pdo->commit();
            sendResponse(true, "记录已彻底删除");
        } catch (PDOException $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            sendResponse(false, "彻底删除失败: " . $e->getMessage());
        }
        return;
    }

    try {
        $pdo->beginTransaction();

        foreach ($targetIds as $currentId) {
            // 先获取要删除的记录信息
            $getRecordSql = "SELECT * FROM stockinout_data WHERE id = ? AND deleted_at IS NULL";
            $getStmt = $pdo->prepare($getRecordSql);
            $getStmt->execute([$currentId]);
            $recordToDelete = $getStmt->fetch(PDO::FETCH_ASSOC);

            if (!$recordToDelete) {
                continue;
            }

            // 执行软删除主表记录
            $stmt = $pdo->prepare("UPDATE stockinout_data SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
            $result = $stmt->execute([$username, $currentId]);

            if ($stmt->rowCount() > 0) {
                // 如果是出库记录，根据target_system同步软删除相应的表记录
                if (floatval($recordToDelete['out_quantity'] ?? 0) > 0) {
                    $targetSystem = $recordToDelete['target_system'] ?? 'j1';

                    if ($targetSystem === 'j1') {
                        // 软删除J1stockinout_data表记录
                        $pdo->prepare("UPDATE j1stockinout_data SET deleted_at = NOW(), deleted_by = ? WHERE main_record_id = ? AND deleted_at IS NULL")
                            ->execute([$username, $currentId]);

                        // 软删除J1stockedit_data表记录
                        $pdo->prepare("UPDATE j1stockedit_data SET deleted_at = NOW(), deleted_by = ? 
                                       WHERE date = ? AND time = ? AND product_name = ? AND (receiver = ? OR receiver = 'Mobile' OR receiver = 'mobile') AND target_system = 'j1' AND deleted_at IS NULL")
                            ->execute([$username, $recordToDelete['date'], $recordToDelete['time'], $recordToDelete['product_name'], $recordToDelete['receiver']]);

                        // 软删除J1stockeditmobile_data表记录
                        $pdo->prepare("UPDATE j1stockeditmobile_data SET deleted_at = NOW(), deleted_by = ? 
                                       WHERE date = ? AND time = ? AND product_name = ? AND receiver = ? AND deleted_at IS NULL")
                            ->execute([$username, $recordToDelete['date'], $recordToDelete['time'], $recordToDelete['product_name'], $recordToDelete['receiver']]);

                        updateStocklistTotal($pdo, 'j1', $recordToDelete['product_name'], $recordToDelete['code_number'], floatval($recordToDelete['in_quantity'] ?? 0), floatval($recordToDelete['out_quantity'] ?? 0), $recordToDelete['specification'] ?? null);
                    } elseif ($targetSystem === 'j2') {
                        // 软删除J2stockinout_data表记录
                        $pdo->prepare("UPDATE j2stockinout_data SET deleted_at = NOW(), deleted_by = ? WHERE main_record_id = ? AND deleted_at IS NULL")
                            ->execute([$username, $currentId]);

                        // 软删除J2stockedit_data表记录
                        $pdo->prepare("UPDATE j2stockedit_data SET deleted_at = NOW(), deleted_by = ? 
                                       WHERE date = ? AND time = ? AND product_name = ? AND (receiver = ? OR receiver = 'Mobile' OR receiver = 'mobile') AND target_system = 'j2' AND deleted_at IS NULL")
                            ->execute([$username, $recordToDelete['date'], $recordToDelete['time'], $recordToDelete['product_name'], $recordToDelete['receiver']]);

                        // 软删除J2stockeditmobile_data表记录
                        $pdo->prepare("UPDATE j2stockeditmobile_data SET deleted_at = NOW(), deleted_by = ? 
                                       WHERE date = ? AND time = ? AND product_name = ? AND receiver = ? AND deleted_at IS NULL")
                            ->execute([$username, $recordToDelete['date'], $recordToDelete['time'], $recordToDelete['product_name'], $recordToDelete['receiver']]);

                        updateStocklistTotal($pdo, 'j2', $recordToDelete['product_name'], $recordToDelete['code_number'], floatval($recordToDelete['in_quantity'] ?? 0), floatval($recordToDelete['out_quantity'] ?? 0), $recordToDelete['specification'] ?? null);
                    } elseif ($targetSystem === 'j3') {
                        // 软删除J3stockinout_data表记录
                        $pdo->prepare("UPDATE j3stockinout_data SET deleted_at = NOW(), deleted_by = ? WHERE main_record_id = ? AND deleted_at IS NULL")
                            ->execute([$username, $currentId]);

                        // 软删除J3stockedit_data表记录
                        $pdo->prepare("UPDATE j3stockedit_data SET deleted_at = NOW(), deleted_by = ? 
                                       WHERE date = ? AND time = ? AND product_name = ? AND (receiver = ? OR receiver = 'Mobile' OR receiver = 'mobile') AND target_system = 'j3' AND deleted_at IS NULL")
                            ->execute([$username, $recordToDelete['date'], $recordToDelete['time'], $recordToDelete['product_name'], $recordToDelete['receiver']]);

                        // 软删除J3stockeditmobile_data表记录
                        $pdo->prepare("UPDATE j3stockeditmobile_data SET deleted_at = NOW(), deleted_by = ? 
                                       WHERE date = ? AND time = ? AND product_name = ? AND receiver = ? AND deleted_at IS NULL")
                            ->execute([$username, $recordToDelete['date'], $recordToDelete['time'], $recordToDelete['product_name'], $recordToDelete['receiver']]);

                        updateStocklistTotal($pdo, 'j3', $recordToDelete['product_name'], $recordToDelete['code_number'], floatval($recordToDelete['in_quantity'] ?? 0), floatval($recordToDelete['out_quantity'] ?? 0), $recordToDelete['specification'] ?? null);
                    }
                }
            }
        }

        $pdo->commit();
        sendResponse(true, "记录已移至回收站");

    } catch (PDOException $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}

// 处理 PATCH 请求 - 更新单个字段
function handlePatch()
{
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
        $sql = "UPDATE stockinout_data SET {$field} = ? WHERE id = ? AND deleted_at IS NULL";
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