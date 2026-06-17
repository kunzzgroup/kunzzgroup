<?php
require_once __DIR__ . '/../config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once __DIR__ . '/permission_guard.php';
requirePermissionApi('resource', 'stock_inventory');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
require_once __DIR__ . '/xss_protect.php';
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

ini_set('display_errors', 0);
error_reporting(0);

try {
    $pdo = get_pdo_connection();
}
catch (PDOException $e) {
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

function normalizeStockDate($value)
{
    if (!is_string($value)) return null;
    $value = trim($value);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return null;

    $date = DateTime::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Kuala_Lumpur'));
    $errors = DateTime::getLastErrors();
    $hasErrors = is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

    if (!$date || $hasErrors || $date->format('Y-m-d') !== $value) return null;
    return $value;
}

/**
 * 批量将记录中的 created_by（存储的是 username）替换为对应的 nickname 用于显示
 */
function resolveCreatedByNicknames(PDO $pdo, array $records): array
{
    $usernames = [];
    foreach ($records as $record) {
        $cb = trim((string)($record['created_by'] ?? ''));
        if ($cb !== '' && $cb !== 'System') {
            $usernames[$cb] = true;
        }
    }
    if (empty($usernames)) return $records;

    $placeholders = implode(',', array_fill(0, count($usernames), '?'));
    $stmt = $pdo->prepare("SELECT username, nickname, username_cn FROM users WHERE username IN ($placeholders)");
    $stmt->execute(array_keys($usernames));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nicknameMap = [];
    foreach ($rows as $row) {
        $nick = trim((string)($row['nickname'] ?? ''));
        if ($nick !== '') { $nicknameMap[$row['username']] = $nick; }
        else {
            $cn = trim((string)($row['username_cn'] ?? ''));
            if ($cn !== '') { $nicknameMap[$row['username']] = $cn; }
        }
    }

    foreach ($records as &$record) {
        $cb = trim((string)($record['created_by'] ?? ''));
        if (isset($nicknameMap[$cb])) { $record['created_by'] = $nicknameMap[$cb]; }
    }
    return $records;
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
function handleGet()
{
    global $pdo;
    $productName = '';
    $price = 0;

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

            if ($searchDate !== null && $searchDate !== '') {
                $searchDate = normalizeStockDate($searchDate);
                if ($searchDate === null) sendResponse(false, "日期格式无效，请使用 YYYY-MM-DD");
            }
            if ($startDate !== null && $startDate !== '') {
                $startDate = normalizeStockDate($startDate);
                if ($startDate === null) sendResponse(false, "开始日期格式无效，请使用 YYYY-MM-DD");
            }
            if ($endDate !== null && $endDate !== '') {
                $endDate = normalizeStockDate($endDate);
                if ($endDate === null) sendResponse(false, "结束日期格式无效，请使用 YYYY-MM-DD");
            }

            // 如果没有提供日期范围，默认使用当月
            if (!$startDate && !$endDate && !$searchDate) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $startDate = "$currentYear-$currentMonth-01";
                $endDate = date('Y-m-t');
            }

            $sql = "SELECT * FROM j2stockedit_data WHERE deleted_at IS NULL";
            $params = [];

            if ($searchDate) {
                $sql .= " AND date = ?";
                $params[] = $searchDate;
            }
            elseif ($startDate && $endDate) {
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

            $countSql = preg_replace('/^SELECT \* FROM/', 'SELECT COUNT(*) as cnt FROM', $sql);
            $countSql = preg_replace('/ ORDER BY .+$/', '', $countSql);

            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $dataSql = $sql;
            if ($limit > 0) {
                $dataSql .= " LIMIT " . $limit;
            }

            $stmt = $pdo->prepare($dataSql);
            try {
                $countStmt = $pdo->prepare($countSql);
                $countStmt->execute($params);
                $totalCount = (int)$countStmt->fetchColumn();

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

                sendResponse(true, "进出库数据获取成功，共找到 " . $totalCount . " 条记录", [
                    'records' => resolveCreatedByNicknames($pdo, $records),
                    'total_count' => $totalCount
                ]);
            }
            catch (PDOException $e) {
                sendResponse(false, "查询数据失败：" . $e->getMessage());
            }

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
                    FROM j2stockedit_data WHERE deleted_at IS NULL";
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

        case 'single':
            // 获取单条记录
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, "缺少记录ID");
            }
            $stmt = $pdo->prepare("SELECT * FROM j2stockedit_data WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                sendResponse(true, "记录获取成功", $record);
            }
            else {
                sendResponse(false, "记录不存在");
            }

        case 'suppliers':
            // 获取所有供应商列表
            $stmt = $pdo->prepare("SELECT DISTINCT receiver FROM j2stockedit_data WHERE deleted_at IS NULL ORDER BY receiver");
            $stmt->execute();
            $suppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            sendResponse(true, "供应商列表获取成功", $suppliers);

        case 'products':
            // 获取所有产品列表
            $stmt = $pdo->prepare("SELECT DISTINCT code_number, product_name FROM j2stockedit_data WHERE deleted_at IS NULL ORDER BY code_number");
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
                $category = $result['category'] ?? '';
                if ($category === 'Drinks') {
                    $category = 'Service Line';
                }
                sendResponse(true, "产品名称获取成功", [
                    'product_name' => $result['product_name'],
                    'specification' => $result['specification'],
                    'supplier' => $result['supplier'],
                    'category' => $category
                ]);
            }
            else {
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
                $category = $result['category'] ?? '';
                if ($category === 'Drinks') {
                    $category = 'Service Line';
                }
                sendResponse(true, "产品编号获取成功", [
                    'product_code' => $result['product_code'],
                    'specification' => $result['specification'],
                    'supplier' => $result['supplier'],
                    'category' => $category
                ]);
            }
            else {
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
                FROM j2stockedit_data 
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
                    FROM j2stockedit_data
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
            }
            else {
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
                    FROM j2stockedit_data
                    WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) AND price = ? AND deleted_at IS NULL";

            error_log("J2 DEBUG stock by price: [productName=" . ($productName ?? 'null') . "] [price=" . ($price ?? 'null') . "]");
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
            }
            else {
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
                        FROM j2stockedit_data
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

            }
            catch (PDOException $e) {
                sendResponse(false, "查询价格库存信息失败：" . $e->getMessage());
            }
            break;

        default:
            sendResponse(false, "无效的操作");
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

    // 验证必填字段
    $required_fields = ['date', 'time', 'product_name', 'receiver'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }

    $normalizedDate = normalizeStockDate($data['date']);
    if ($normalizedDate === null) sendResponse(false, "日期格式无效，请使用 YYYY-MM-DD");
    $data['date'] = $normalizedDate;



    // 验证 target_system 枚举值
    if (!empty($data['target_system']) && !in_array($data['target_system'], ['j2', 'Central', 'central'])) {
        sendResponse(false, "target_system 只能选择 j2 或 Central");
    }

    // 验证数量：不能为负数
    $inQty = floatval($data['in_quantity'] ?? 0);
    $outQty = floatval($data['out_quantity'] ?? 0);
    if ($inQty < 0 || $outQty < 0) {
        sendResponse(false, "数量不能为负数");
    }
    // 验证数量：进货和出货不能同时为 0
    if ($inQty <= 0 && $outQty <= 0) {
        sendResponse(false, "进货或出货数量必须大于 0");
    }

    try {

        // 开始事务
        $pdo->beginTransaction();

        // ========== 库存校验（出库时检查） ==========
        if ($outQty > 0) {
            $stockSql = "SELECT 
                            (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) - 
                            COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) as available_stock
                        FROM j2stockedit_data 
                        WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) 
                        AND CAST(price AS DECIMAL(15,6)) = CAST(? AS DECIMAL(15,6)) AND deleted_at IS NULL";
            $stockStmt = $pdo->prepare($stockSql);
            $stockStmt->execute([$data['product_name'], $data['product_name'], $data['price'] ?? 0]);
            $stockRow = $stockStmt->fetch(PDO::FETCH_ASSOC);
            $availableStock = floatval($stockRow['available_stock'] ?? 0);

            if ($outQty > $availableStock) {
                $pdo->rollBack();
                sendResponse(false, "产品 [{$data['product_name']}] (价格 RM" . ($data['price'] ?? 0) . ") 库存不足！可用库存: {$availableStock}，请求出库: {$outQty}");
            }
        }
        // ========== 库存校验结束 ==========

        // 将 Drinks 转换为 Service Line
        $type = $data['type'] ?? null;
        if ($type === 'Drinks' || strtolower($type) === 'drinks') {
            $type = 'Service Line';
        }

        $sql = "INSERT INTO j2stockedit_data 
                (date, time, product_name, 
                in_quantity, out_quantity, specification, price, code_number, remark, receiver, target_system, type, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // 获取当前用户名
        $createdBy = $_SESSION['username'] ?? 'System';

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
            'j2', // 强制使用 j2，防止前端笺改
            $type,
            $createdBy
        ]);

        $newId = $pdo->lastInsertId();

        // 当前 system=j2页面，收货单位始终是 j2
        $targetSystem = 'j2'; // 强制锁定为 j2

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
            error_log("记录已同时保存到Central表，J2记录ID: " . $newId . ", Central记录ID: " . $centralId);

            if (!$centralResult) {
                $pdo->rollBack();
                $error = $centralStmt->errorInfo();
                sendResponse(false, "保存到Central表失败：" . $error[2]);
            }

            $centralId = $pdo->lastInsertId();
            error_log("记录已同时保存到Central表，J2记录ID: " . $newId . ", Central记录ID: " . $centralId);

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
        }
        elseif ($targetSystem === 'j2') {
            // 如果选择j2，只保存在j2stockedit_data表（当前表）
            error_log("记录仅保存在J2编辑表");
        }

        // 提交事务
        $pdo->commit();

        // 获取新插入的记录
        $stmt = $pdo->prepare("SELECT * FROM j2stockedit_data WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        $newRecord['approval_status'] = (!empty($newRecord['approver'])) ? 'approved' : 'pending';

        // 将 created_by 从 username 解析为 nickname（与 GET list 保持一致）
        $resolved = resolveCreatedByNicknames($pdo, [$newRecord]);
        $newRecord = $resolved[0];

        $message = "进出库记录添加成功";
        if (strtolower($targetSystem) === 'central') {
            $message .= "，已同时保存到Central系统";
        }
        elseif ($targetSystem === 'j2') {
            $message .= "，已保存到J2系统";
        }

        sendResponse(true, $message, $newRecord);

    }
    catch (PDOException $e) {
        // 回滚事务
        $pdo->rollBack();
        sendResponse(false, "添加记录失败：" . $e->getMessage());
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
        $sql = "UPDATE j2stockedit_data SET approver = ? WHERE id = ? AND deleted_at IS NULL";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$approver, $id]);

        if ($stmt->rowCount() > 0) {
            // 获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM j2stockedit_data WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            $updatedRecord['approval_status'] = 'approved';

            sendResponse(true, "记录批准成功", $updatedRecord);
        }
        else {
            sendResponse(false, "记录不存在");
        }

    }
    catch (PDOException $e) {
        sendResponse(false, "批准失败：" . $e->getMessage());
    }
}

// 处理 PUT 请求 - 更新记录
function handlePut()
{
    global $pdo, $data;
    $centralResult = false;

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

    $normalizedDate = normalizeStockDate($data['date']);
    if ($normalizedDate === null) sendResponse(false, "日期格式无效，请使用 YYYY-MM-DD");
    $data['date'] = $normalizedDate;



    // 验证数量：不能为负数
    $inQty = floatval($data['in_quantity'] ?? 0);
    $outQty = floatval($data['out_quantity'] ?? 0);
    if ($inQty < 0 || $outQty < 0) {
        sendResponse(false, "数量不能为负数");
    }
    // 验证数量：进货和出货不能同时为 0
    if ($inQty <= 0 && $outQty <= 0) {
        sendResponse(false, "进货或出货数量必须大于 0");
    }

    try {
        // 将 Drinks 转换为 Service Line
        $type = $data['type'] ?? null;
        if ($type === 'Drinks' || strtolower($type) === 'drinks') {
            $type = 'Service Line';
        }

        $sql = "UPDATE j2stockedit_data 
                SET date = ?, time = ?, product_name = ?, 
                    in_quantity = ?, out_quantity = ?, 
                    specification = ?, price = ?, code_number = ?, remark = ?, receiver = ?, target_system = ?, type = ?
                WHERE id = ? AND deleted_at IS NULL";

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
            'j2', // 强制使用 j2，防止前端笺改
            $type,
            $data['id']
        ]);

        // 检查记录是否存在
        $checkStmt = $pdo->prepare("SELECT * FROM j2stockedit_data WHERE id = ? AND deleted_at IS NULL");
        $checkStmt->execute([$data['id']]);
        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            // 记录存在，获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM j2stockedit_data WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$data['id']]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            // 将 created_by 从 username 解析为 nickname（与 GET list 保持一致）
            $resolved = resolveCreatedByNicknames($pdo, [$updatedRecord]);
            $updatedRecord = $resolved[0];

            // 当前 system=j2页面，收货单位始终是 j2
            $targetSystem = 'j2'; // 强制锁定

            if (strtolower($targetSystem) === 'central') {
                // 更新对应的stockinout_data记录 - 通过匹配字段找到对应记录
                $centralUpdateSql = "UPDATE stockinout_data 
                                    SET date = ?, time = ?, product_name = ?, 
                                        in_quantity = ?, out_quantity = ?, 
                                        specification = ?, price = ?, code_number = ?, remark = ?, receiver = ?
                                    WHERE product_name = ? AND date = ? AND receiver = ? AND target_system = 'Central' AND deleted_at IS NULL
                                    ORDER BY id DESC LIMIT 1";

                $centralStmt = $pdo->prepare($centralUpdateSql);
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
                    $existingRecord['product_name'], // WHERE 条件
                    $existingRecord['date'], // WHERE 条件  
                    $existingRecord['receiver'] // WHERE 条件
                ]);

                if ($centralResult && $centralStmt->rowCount() > 0) {
                    error_log("已同步更新Central表记录");
                }
                else {
                    error_log("未找到对应的Central表记录进行更新");
                }

                error_log("已同步更新Central表记录");
            }
            elseif ($targetSystem === 'j2') {
                error_log("J2记录更新：仅更新J2编辑表");
            }

            sendResponse(true, "进出库记录更新成功", $updatedRecord);
        }
        else {
            sendResponse(false, "记录不存在");
        }

    }
    catch (PDOException $e) {
        sendResponse(false, "更新记录失败：" . $e->getMessage());
    }
}

function handleDelete()
{
    global $pdo;
    session_start();
    $username = $_SESSION['username'] ?? 'System';
    $id = $_GET['id'] ?? null;
    $ids = $_GET['ids'] ?? null; // 支持批量删除

    if (!$id && !$ids) {
        sendResponse(false, "缺少记录ID");
    }

    $targetIds = $id ? [$id] : explode(',', $ids);

    try {
        $pdo->beginTransaction();

        foreach ($targetIds as $currentId) {
            // 先获取要删除的记录信息
            $getRecordSql = "SELECT * FROM j2stockedit_data WHERE id = ? AND deleted_at IS NULL";
            $getStmt = $pdo->prepare($getRecordSql);
            $getStmt->execute([$currentId]);
            $recordToDelete = $getStmt->fetch(PDO::FETCH_ASSOC);

            if (!$recordToDelete) {
                continue; // 如果记录不存在或已删除，跳过
            }

            // 执行软删除主表记录
            $stmt = $pdo->prepare("UPDATE j2stockedit_data SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
            $result = $stmt->execute([$username, $currentId]);

            if ($stmt->rowCount() > 0) {
                // 如果有移动端关联ID，同步软删除移动端记录表
                if (!empty($recordToDelete['mobile_ref_id'])) {
                    try {
                        $mobileDeleteSql = "UPDATE j2stockeditmobile_data SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
                        $mobileStmt = $pdo->prepare($mobileDeleteSql);
                        $mobileStmt->execute([$username, $recordToDelete['mobile_ref_id']]);
                    }
                    catch (PDOException $e) {
                        error_log("同步软删除J2移动端历史记录失败: " . $e->getMessage());
                    }
                }

                // 如果是Central记录，同步软删除stockinout_data表记录
                $targetSystem = $recordToDelete['target_system'] ?? 'j2';
                if (strtolower($targetSystem) === 'central') {
                    $centralDeleteSql = "UPDATE stockinout_data SET deleted_at = NOW(), deleted_by = ? 
                                        WHERE product_name = ? AND date = ? AND receiver = ? AND target_system = 'central' AND deleted_at IS NULL
                                        ORDER BY created_at DESC LIMIT 1";
                    $centralDelStmt = $pdo->prepare($centralDeleteSql);
                    $centralDelStmt->execute([
                        $username,
                        $recordToDelete['product_name'],
                        $recordToDelete['date'],
                        $recordToDelete['receiver']
                    ]);
                }
            }
        }

        $pdo->commit();
        sendResponse(true, "记录已成功移至回收站");
    }
    catch (PDOException $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        sendResponse(false, "删除失败：" . $e->getMessage());
    }
}

/**
 * 处理批量保存请求 (J2)
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

        foreach ($outSummary as $item) {
            $stockSql = "SELECT 
                            (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) - 
                            COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) as available_stock
                        FROM j2stockedit_data 
                        WHERE (product_name = ? OR product_name = REPLACE(?, '&amp;', '&')) 
                        AND CAST(price AS DECIMAL(15,6)) = CAST(? AS DECIMAL(15,6)) AND deleted_at IS NULL";
            $stockStmt = $pdo->prepare($stockSql);
            $stockStmt->execute([$item['product_name'], $item['product_name'], $item['price']]);
            $stockRow = $stockStmt->fetch(PDO::FETCH_ASSOC);
            $availableStock = floatval($stockRow['available_stock'] ?? 0);

            if ($item['total_out'] > $availableStock) {
                throw new Exception("产品 [{$item['product_name']}] (价格 RM{$item['price']}) 库存不足！可用库存: {$availableStock}，请求出库: {$item['total_out']}");
            }
        }
        // ========== 库存校验结束 ==========

        $sql = "INSERT INTO j2stockedit_data 
                (date, time, product_name, in_quantity, out_quantity, specification, price, code_number, remark, receiver, target_system, type, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        $centralSql = "INSERT INTO stockinout_data 
                      (date, time, product_name, in_quantity, out_quantity, specification, price, code_number, remark, receiver, target_system, created_by) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $centralStmt = $pdo->prepare($centralSql);

        // 获取当前用户名
        $createdBy = $_SESSION['username'] ?? 'System';

        $successCount = 0;
        foreach ($rows as $index => $row) {
            $rowNum = $index + 1;

            // 验证每行的必填字段 (包含日期)
            $required = ['date', 'time', 'product_name', 'receiver'];
            foreach ($required as $field) {
                if (empty($row[$field])) {
                    throw new Exception("第 {$rowNum} 行缺少必填字段：{$field}");
                }
            }

            // 处理类型转换
            $type = $row['type'] ?? null;
            if ($type === 'Drinks' || strtolower($type) === 'drinks') {
                $type = 'Service Line';
            }

            $rowDate = normalizeStockDate($row['date']);
            if ($rowDate === null) {
                throw new Exception("第 {$rowNum} 行日期格式无效，请使用 YYYY-MM-DD");
            }
            $row['date'] = $rowDate;

            // 写入 J2 数据库表
            $stmt->execute([
                $rowDate,
                $row['time'],
                $row['product_name'],
                $row['in_quantity'] ?? 0,
                $row['out_quantity'] ?? 0,
                $row['specification'] ?? null,
                $row['price'] ?? 0,
                $row['code_number'] ?? null,
                $row['remark'] ?? null,
                $row['receiver'] ?? null,
                'j2',
                $type,
                $createdBy
            ]);

            $newId = $pdo->lastInsertId();
            $successCount++;

            // 如果 target_system 是 Central，则同步保存
            $targetSystem = $row['target_system'] ?? 'j2';
            if (strtolower($targetSystem) === 'central') {
                $centralStmt->execute([
                    $rowDate,
                    $row['time'],
                    $row['product_name'],
                    floatval($row['out_quantity'] ?? 0),
                    0,
                    $row['specification'] ?? null,
                    floatval($row['price'] ?? 0),
                    $row['code_number'] ?? null,
                    $row['remark'] ?? null,
                    $row['receiver'] ?? null,
                    'central',
                    $createdBy
                ]);
            }
        }

        $pdo->commit();
        sendResponse(true, "J2 批量保存成功，共保存 {$successCount} 条记录");

    }
    catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, "J2 批量保存失败：" . $e->getMessage());
    }
}
?>
