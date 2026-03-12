<?php
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

// 启动 session（用于获取当前登录用户）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$data = get_safe_json_input();

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
error_log("数据库连接成功");
error_log("请求方法: " . $method);

function sendResponse($success, $message = "", $data = null) {
    ob_end_clean();
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

function getCurrentApplicantName(PDO $pdo): string {
    if (!isset($_SESSION['user_id'])) {
        return '';
    }
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT nickname, username_cn, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $nickname = trim((string)($row['nickname'] ?? ''));
    if ($nickname !== '') return $nickname;
    $usernameCn = trim((string)($row['username_cn'] ?? ''));
    if ($usernameCn !== '') return $usernameCn;
    return trim((string)($row['username'] ?? ''));
}

/**
 * 检查用户是否拥有特定权限
 * @param PDO $pdo 数据库连接
 * @param int|string $userId 用户ID
 * @param string $permission 权限名称 ('apply' 或 'approve')
 * @return bool
 */
function checkUserPermission(PDO $pdo, $userId, $permission): bool {
    $hasPermission = false;
    
    try {
        // 第一层验证：检查 user_page_permissions 表里的动态权限
        $permStmt = $pdo->prepare("SELECT permissions_json FROM user_page_permissions WHERE user_id = ? AND page_key = 'stock_inventory'");
        $permStmt->execute([$userId]);
        $permData = $permStmt->fetchColumn();

        if ($permData) {
            $decoded = json_decode($permData, true);
            // 注意：数据库中存储的键是 'views'
            if (isset($decoded['views']) && is_array($decoded['views']) && in_array($permission, $decoded['views'])) {
                $hasPermission = true;
            }
        }

        // 第二层验证（Fallback兼容）：针对系统原有管理员代码
        if (!$hasPermission && !$permData) {
            $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP', 'HR2025', 'AZGQOY', 'IT7890'];
            $codeStmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
            $codeStmt->execute([$userId]);
            $userCode = $codeStmt->fetchColumn();
            
            if ($userCode && in_array($userCode, $allowedCodes)) {
                $hasPermission = true;
            }
        }
    } catch (PDOException $e) {
        error_log("检查用户权限失败：" . $e->getMessage());
    }
    
    return $hasPermission;
}

// 路由处理
switch ($method) {
    case 'GET':
        // 检查是否是批准请求
        if (($_GET['action'] ?? '') === 'approve') {
            handleApprove();
        } else {
            handleGet();
        }
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        // 检查是否是批准请求
        if (($_GET['action'] ?? '') === 'approve') {
            handleApprove();
        } else {
            handlePut();
        }
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
        // 这是PUT请求，重定向到批准处理
        handleApprove();
        return;
    }
    
    switch ($action) {
        case 'list':
            // 获取所有库存数据
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $searchDate = $_GET['search_date'] ?? null;
            $supplier = $_GET['supplier'] ?? null;
            $approvalStatus = $_GET['approval_status'] ?? null;
            $productSearch = $_GET['product_search'] ?? null;
            $systemAssign = $_GET['system_assign'] ?? null;
            $freezerCategory = isset($_GET['freezer_category']) && $_GET['freezer_category'] !== '' ? $_GET['freezer_category'] : null;

            // 调试日志
            error_log("stockapi.php - freezer_category parameter: " . var_export($freezerCategory, true));

            // 如果查询系统分配的数据，不应用日期范围过滤
            // 如果没有提供日期范围且没有指定系统分配，默认显示一年内的数据
            if (!$systemAssign && !$startDate && !$endDate && !$searchDate) {
                $startDate = date('Y-m-d', strtotime('-1 year')); // 一年前的今天
                $endDate = date('Y-m-d'); // 今天
            }

            $sql = "SELECT * FROM stock_data WHERE 1=1";
            $params = [];
            
            // 只有在没有指定系统分配时才应用日期过滤
            if (!$systemAssign) {
                if ($searchDate) {
                    $sql .= " AND date = ?";
                    $params[] = $searchDate;
                } elseif ($startDate && $endDate) {
                    $sql .= " AND date BETWEEN ? AND ?";
                    $params[] = $startDate;
                    $params[] = $endDate;
                }
            }
            
            if ($supplier) {
                $sql .= " AND supplier LIKE ?";
                $params[] = "%$supplier%";
            }

            // 产品搜索（搜索所有相关字段：货品编号、货品名字、规格、货品类型、供应商、申请人、系统分配）
            // Drinks 与 Service Line 等效：搜索 service/line/ser 时也匹配 category='Drinks'
            if ($productSearch) {
                $productSearchLower = strtolower($productSearch);
                $searchMatchesServiceLine = (strpos($productSearchLower, 'service') !== false || strpos($productSearchLower, 'line') !== false || strpos($productSearchLower, 'ser') !== false);
                $categoryCondition = $searchMatchesServiceLine ? "(category LIKE ? OR category = 'Drinks')" : "category LIKE ?";
                $sql .= " AND (product_code LIKE ? OR product_name LIKE ? OR specification LIKE ? OR $categoryCondition OR supplier LIKE ? OR applicant LIKE ? OR system_assign LIKE ?)";
                $params[] = "%$productSearch%";
                $params[] = "%$productSearch%";
                $params[] = "%$productSearch%";
                $params[] = "%$productSearch%";
                $params[] = "%$productSearch%";
                $params[] = "%$productSearch%";
                $params[] = "%$productSearch%";
            }
            
            // 系统分配过滤（支持多选，使用FIND_IN_SET或LIKE）
            if ($systemAssign) {
                $sql .= " AND (FIND_IN_SET(?, system_assign) > 0 OR system_assign = ?)";
                $params[] = $systemAssign;
                $params[] = $systemAssign;
            }
            
            // 冰箱分类过滤
            if ($freezerCategory !== null && $freezerCategory !== '') {
                $sql .= " AND freezer_category = ?";
                $params[] = $freezerCategory;
                error_log("stockapi.php - Applying freezer_category filter: " . $freezerCategory);
                error_log("stockapi.php - SQL before execution: " . $sql);
                error_log("stockapi.php - Parameters: " . json_encode($params));
            }
            
            if ($approvalStatus) {
                if ($approvalStatus === 'approved') {
                    $sql .= " AND approver IS NOT NULL AND approver != ''";
                } elseif ($approvalStatus === 'pending') {
                    $sql .= " AND (approver IS NULL OR approver = '')";
                }
            }
            
            $sql .= " ORDER BY date DESC, time DESC";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute($params);
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 调试日志
                error_log("stockapi.php - Records found: " . count($records));
                if ($freezerCategory !== null && $freezerCategory !== '' && count($records) > 0) {
                    error_log("stockapi.php - First record freezer_category: " . ($records[0]['freezer_category'] ?? 'NULL'));
                }
                
                // 为每条记录添加批准状态
                foreach ($records as &$record) {
                    $record['approval_status'] = (!empty($record['approver'])) ? 'approved' : 'pending';
                }
                
                sendResponse(true, "库存数据获取成功", $records);
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
                        COUNT(CASE WHEN approver IS NOT NULL AND approver != '' THEN 1 END) as approved_count,
                        COUNT(CASE WHEN approver IS NULL OR approver = '' THEN 1 END) as pending_count,
                        SUM(CASE WHEN in_quantity > 0 THEN (in_quantity * price) ELSE 0 END) as total_in_value,
                        SUM(CASE WHEN out_quantity > 0 THEN (out_quantity * price) ELSE 0 END) as total_out_value,
                        SUM((in_quantity - out_quantity) * price) as net_value
                    FROM stock_data WHERE 1=1";
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
            foreach (['total_in_value', 'total_out_value', 'net_value'] as $field) {
                $summary[$field] = floatval($summary[$field] ?? 0);
            }
            
            sendResponse(true, "汇总数据获取成功", $summary);
            
        case 'single':
            // 获取单条记录
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, "缺少记录ID");
            }
            
            $stmt = $pdo->prepare("SELECT * FROM stock_data WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                $record['approval_status'] = (!empty($record['approver'])) ? 'approved' : 'pending';
                sendResponse(true, "记录获取成功", $record);
            } else {
                sendResponse(false, "记录不存在");
            }
            
        case 'suppliers':
            // 获取所有供应商列表
            $stmt = $pdo->prepare("SELECT DISTINCT supplier FROM stock_data ORDER BY supplier");
            $stmt->execute();
            $suppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendResponse(true, "供应商列表获取成功", $suppliers);
            
        case 'products':
            // 获取所有产品列表
            $stmt = $pdo->prepare("SELECT DISTINCT product_code, product_name FROM stock_data ORDER BY product_code");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "产品列表获取成功", $products);
            
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

    // 新增记录必须是已登录用户（用于自动写入申请人昵称）
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, "用户未登录");
    }
    
    // 权限检查：必须要有 apply 权限才能添加记录
    if (!checkUserPermission($pdo, $_SESSION['user_id'], 'apply')) {
        sendResponse(false, "您没有权限执行此操作 (缺少[申请权限])");
    }
    
    // 验证必填字段
    // applicant 由系统根据登录账号自动写入（不信任前端传值）
    $required_fields = ['product_code', 'product_name', 'specification', 'supplier'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }
    
    try {
        // 自动设置date和time字段
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $applicant = getCurrentApplicantName($pdo);
        if ($applicant === '') {
            sendResponse(false, "无法获取当前账号昵称（申请人）");
        }
        
        $sql = "INSERT INTO stock_data 
                (date, time, product_code, product_name, specification, category, supplier, applicant, system_assign, freezer_category, approver) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $currentDate,
            $currentTime,
            $data['product_code'],
            $data['product_name'],
            $data['specification'],
            $data['category'] ?? null,  // 添加 category 字段
            $data['supplier'],
            $applicant,
            $data['system_assign'] ?? null,  // 添加 system_assign 字段
            $data['freezer_category'] ?? null,  // 添加 freezer_category 字段
            $data['approver'] ?? null
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // 获取新插入的记录
        $stmt = $pdo->prepare("SELECT * FROM stock_data WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        $newRecord['approval_status'] = (!empty($newRecord['approver'])) ? 'approved' : 'pending';
        
        sendResponse(true, "库存记录添加成功", $newRecord);
        
    } catch (PDOException $e) {
        sendResponse(false, "添加记录失败：" . $e->getMessage());
    }
}

// 处理批准请求
function handleApprove() {
    global $pdo, $data;
    
    // 检查用户权限
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, "用户未登录");
    }
    
    $userId = $_SESSION['user_id'];
    if (!checkUserPermission($pdo, $userId, 'approve')) {
        sendResponse(false, "您没有权限执行此操作 (缺少[货品批准]权限)");
    }
    
    if (!$data || !isset($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }
    
    $id = $data['id'];
    $approver = $_SESSION['username'] ?? 'System'; // 使用登录用户的用户名
    
    try {
        $sql = "UPDATE stock_data SET approver = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$approver, $id]);
        
        if ($stmt->rowCount() > 0) {
            // 获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM stock_data WHERE id = ?");
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

    // 权限检查：必须要有 apply 权限才能更新记录
    if (!checkUserPermission($pdo, $_SESSION['user_id'], 'apply')) {
        sendResponse(false, "您没有权限执行此操作 (缺少[申请权限])");
    }
    
    // 验证必填字段
    $required_fields = ['product_code', 'product_name', 'specification', 'supplier', 'applicant'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }
    
    try {
        // 更新时申请人始终改为当前登录用户的昵称（谁编辑保存就显示谁）
        $applicant = getCurrentApplicantName($pdo);
        if ($applicant === '') {
            sendResponse(false, "无法获取当前账号昵称（申请人）");
        }

        $sql = "UPDATE stock_data 
                SET product_code = ?, product_name = ?, specification = ?, category = ?, supplier = ?, 
                    applicant = ?, system_assign = ?, freezer_category = ?, approver = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            $data['product_code'],
            $data['product_name'],
            $data['specification'],
            $data['category'] ?? null,  // 添加 category 字段
            $data['supplier'],
            $applicant,
            $data['system_assign'] ?? null,  // 添加 system_assign 字段
            $data['freezer_category'] ?? null,  // 添加 freezer_category 字段
            $data['approver'] ?? null,
            $data['id']
        ]);
        
        // 检查记录是否存在
        $checkStmt = $pdo->prepare("SELECT * FROM stock_data WHERE id = ?");
        $checkStmt->execute([$data['id']]);
        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            // 记录存在，获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM stock_data WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            $updatedRecord['approval_status'] = (!empty($updatedRecord['approver'])) ? 'approved' : 'pending';
            
            sendResponse(true, "库存记录更新成功", $updatedRecord);
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

    // 权限检查：必须要有 apply 权限才能删除记录
    if (!checkUserPermission($pdo, $_SESSION['user_id'], 'apply')) {
        sendResponse(false, "您没有权限执行此操作 (缺少[申请权限])");
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM stock_data WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            sendResponse(true, "库存记录删除成功");
        } else {
            sendResponse(false, "记录不存在");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}

// 批量批准功能（可选）
function handleBatchApprove() {
    global $pdo, $data;
    
    // 检查用户权限
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, "用户未登录");
    }
    
    $userId = $_SESSION['user_id'];
    if (!checkUserPermission($pdo, $userId, 'approve')) {
        sendResponse(false, "您没有权限执行此操作 (缺少[货品批准]权限)");
    }
    
    if (!$data || !isset($data['ids']) || !isset($data['approver'])) {
        sendResponse(false, "缺少必要参数");
    }
    
    $ids = $data['ids'];
    $approver = $data['approver'];
    
    if (!is_array($ids) || empty($ids)) {
        sendResponse(false, "无效的ID列表");
    }
    
    try {
        $pdo->beginTransaction();
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "UPDATE stock_data SET approver = ? WHERE id IN ($placeholders)";
        
        $params = array_merge([$approver], $ids);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $pdo->commit();
        
        sendResponse(true, "批量批准成功", ["affected_rows" => $stmt->rowCount()]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        sendResponse(false, "批量批准失败：" . $e->getMessage());
    }
}
?>
