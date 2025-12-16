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

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

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
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $e->getMessage()]);
    exit;
}

// 餐厅配置
$restaurantConfig = [
    'j1' => [
        'data_table' => 'j1data',
        'view_table' => 'j1data_view',
        'name' => 'J1分店'
    ],
    'j2' => [
        'data_table' => 'j2data',
        'view_table' => 'j2data_view',
        'name' => 'J2分店'
    ],
    'j3' => [
        'data_table' => 'j3data',
        'view_table' => 'j3data_view',
        'name' => 'J3分店'
    ]
];

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

function getRestaurantConfig($restaurant) {
    global $restaurantConfig;
    
    if (!isset($restaurantConfig[$restaurant])) {
        sendResponse(false, "无效的餐厅标识：" . $restaurant);
    }
    
    return $restaurantConfig[$restaurant];
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
    $restaurant = $_GET['restaurant'] ?? 'j1';
    $config = getRestaurantConfig($restaurant);
    
    switch ($action) {
        case 'list':
            // 获取所有数据
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $searchDate = $_GET['search_date'] ?? null;

            // 如果没有提供日期范围，默认使用当月
            if (!$startDate && !$endDate && !$searchDate) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $startDate = "$currentYear-$currentMonth-01";
                $endDate = date('Y-m-t'); // 当月最后一天
            }

            // 使用数据表而不是视图表，确保能获取到 adj_amount 字段
            $sql = "SELECT * FROM " . $config['data_table'] . " WHERE 1=1";
            $params = [];
            
            if ($searchDate) {
                $sql .= " AND date = ?";
                $params[] = $searchDate;
            } elseif ($startDate && $endDate) {
                $sql .= " AND date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $sql .= " ORDER BY date DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "数据获取成功", $records);
            break;
            
        case 'summary':
            // 获取汇总数据
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            
            $sql = "SELECT 
                        COUNT(*) as total_days,
                        SUM(gross_sales) as total_gross_sales,
                        SUM(gross_sales - discounts - service_fee - tax + adj_amount) as total_net_sales,
                        SUM(service_fee) as total_service_fee,
                        SUM(tax) as total_tax,
                        SUM(adj_amount) as total_adj_amount,
                        SUM(tender_amount) as total_tender_amount,
                        SUM(diners) as total_diners,
                        SUM(tables_used) as total_tables,
                        SUM(returning_customers) as total_returning_customers,
                        SUM(new_customers) as total_new_customers,
                        AVG(CASE WHEN diners > 0 THEN gross_sales / diners ELSE 0 END) as avg_per_diner
                    FROM " . $config['data_table'] . " WHERE 1=1";
            $params = [];
            
            if ($startDate && $endDate) {
                $sql .= " AND date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, "汇总数据获取成功", $summary);
            break;
            
        case 'single':
            // 获取单条记录
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, "缺少记录ID");
            }
            
            $stmt = $pdo->prepare("SELECT * FROM " . $config['data_table'] . " WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                sendResponse(true, "记录获取成功", $record);
            } else {
                sendResponse(false, "记录不存在");
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
    
    $restaurant = $data['restaurant'] ?? 'j1';
    $config = getRestaurantConfig($restaurant);
    
    // 验证必填字段
    if (empty($data['date']) || !isset($data['gross_sales']) || !isset($data['diners'])) {
        sendResponse(false, "缺少必填字段：日期、总销售额、用餐人数");
    }
    
    try {
        // 更新SQL语句，包含 adj_amount 字段
        $sql = "INSERT INTO " . $config['data_table'] . " 
                (date, gross_sales, discounts, service_fee, tax, adj_amount, tender_amount, diners, tables_used, returning_customers, new_customers) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $data['date'],
            $data['gross_sales'] ?? 0,
            $data['discounts'] ?? 0,
            $data['service_fee'] ?? 0,
            $data['tax'] ?? 0,
            $data['adj_amount'] ?? 0,  // 添加四舍五入金额字段
            $data['tender_amount'] ?? 0,
            $data['diners'] ?? 0,
            $data['tables_used'] ?? 0,
            $data['returning_customers'] ?? 0,
            $data['new_customers'] ?? 0
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // 获取新插入的记录
        $stmt = $pdo->prepare("SELECT * FROM " . $config['data_table'] . " WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, $config['name'] . "记录添加成功", $newRecord);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            sendResponse(false, "该日期在" . $config['name'] . "的记录已存在");
        } else {
            sendResponse(false, "添加记录失败：" . $e->getMessage());
        }
    }
}

// 处理 PUT 请求 - 更新记录
function handlePut() {
    global $pdo, $data;
    
    if (!$data || !isset($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }
    
    $restaurant = $data['restaurant'] ?? 'j1';
    $config = getRestaurantConfig($restaurant);
    
    // 验证必填字段
    if (empty($data['date']) || !isset($data['gross_sales']) || !isset($data['diners'])) {
        sendResponse(false, "缺少必填字段：日期、总销售额、用餐人数");
    }
    
    try {
        // 更新SQL语句，包含 adj_amount 字段
        $sql = "UPDATE " . $config['data_table'] . " 
                SET date = ?, gross_sales = ?, discounts = ?, service_fee = ?, tax = ?, adj_amount = ?, tender_amount = ?, diners = ?, 
                    tables_used = ?, returning_customers = ?, new_customers = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            $data['date'],
            $data['gross_sales'] ?? 0,
            $data['discounts'] ?? 0,
            $data['service_fee'] ?? 0,
            $data['tax'] ?? 0,
            $data['adj_amount'] ?? 0,  // 添加四舍五入金额字段
            $data['tender_amount'] ?? 0,
            $data['diners'] ?? 0,
            $data['tables_used'] ?? 0,
            $data['returning_customers'] ?? 0,
            $data['new_customers'] ?? 0,
            $data['id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            // 获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM " . $config['data_table'] . " WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, $config['name'] . "记录更新成功", $updatedRecord);
        } else {
            sendResponse(false, "记录不存在或无变化");
        }
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            sendResponse(false, "该日期在" . $config['name'] . "的记录已存在");
        } else {
            sendResponse(false, "更新记录失败：" . $e->getMessage());
        }
    }
}

// 处理 DELETE 请求 - 删除记录
function handleDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    $restaurant = $_GET['restaurant'] ?? 'j1';
    $config = getRestaurantConfig($restaurant);
    
    if (!$id) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM " . $config['data_table'] . " WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            sendResponse(true, $config['name'] . "记录删除成功");
        } else {
            sendResponse(false, "记录不存在");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}

// 处理清理测试数据请求
function handleClearTestData() {
    global $pdo, $restaurantConfig;
    
    try {
        $deletedCount = 0;
        
        // 清理所有餐厅的测试数据
        foreach ($restaurantConfig as $restaurant => $config) {
            $sql = "DELETE FROM " . $config['data_table'] . " WHERE product_name LIKE '%TEST PRODUCT%' OR product_name LIKE '%test product%' OR product_name LIKE '%Test Product%'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $deletedCount += $stmt->rowCount();
        }
        
        sendResponse(true, "清理完成", ["deleted_count" => $deletedCount]);
        
    } catch (PDOException $e) {
        sendResponse(false, "清理测试数据失败：" . $e->getMessage());
    }
}

// 根据请求方法处理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'clear_test_data':
            handleClearTestData();
            break;
        default:
            sendResponse(false, "未知的POST操作");
    }
}
?>