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
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

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
        'data_table' => 'j1cost',
        'name' => 'J1分店'
    ],
    'j2' => [
        'data_table' => 'j2cost',
        'name' => 'J2分店'
    ],
    'j3' => [
        'data_table' => 'j3cost',
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

// 获取库存供应数据
function getStockSupplyData($restaurant, $startDate = null, $endDate = null) {
    global $pdo;
    
    try {
        // 如果没有提供日期，使用当前月份
        if (!$startDate || !$endDate) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $startDate = "$currentYear-$currentMonth-01";
            $endDate = date('Y-m-t');
        }
        
        // 计算上个月的最后一天（基于查询开始日期）
        $startDateTime = new DateTime($startDate);
        $lastMonthEnd = clone $startDateTime;
        $lastMonthEnd->modify('first day of this month');
        $lastMonthEnd->modify('-1 day');
        $lastMonthEndDate = $lastMonthEnd->format('Y-m-d');
        
        // 确定要查询的表和餐厅列表
        $tables = [];
        if ($restaurant === 'j1') {
            $tables = ['j1' => 'j1stockinout_data'];
        } elseif ($restaurant === 'j2') {
            $tables = ['j2' => 'j2stockinout_data'];
        } elseif ($restaurant === 'j3') {
            $tables = ['j3' => 'j3stockinout_data'];
        } else {
            // total - 查询所有三个餐厅
            $tables = [
                'j1' => 'j1stockinout_data',
                'j2' => 'j2stockinout_data',
                'j3' => 'j3stockinout_data'
            ];
        }
        
        $lastStock = 0;
        $currentStock = 0;
        
        // 计算每个表的供应值
        foreach ($tables as $key => $tableName) {
            // 获取上个月最后一天的累计供应值
            $sqlLast = "SELECT SUM(in_quantity * price) as total_supply 
                       FROM " . $tableName . " 
                       WHERE in_quantity > 0 AND date <= ?";
            $stmtLast = $pdo->prepare($sqlLast);
            $stmtLast->execute([$lastMonthEndDate]);
            $resultLast = $stmtLast->fetch(PDO::FETCH_ASSOC);
            $lastStock += floatval($resultLast['total_supply'] ?? 0);
            
            // 获取选择的日期范围内的累计供应值（从开始日期到结束日期）
            $sqlCurrent = "SELECT SUM(in_quantity * price) as total_supply 
                          FROM " . $tableName . " 
                          WHERE in_quantity > 0 
                          AND date >= ? AND date <= ?";
            $stmtCurrent = $pdo->prepare($sqlCurrent);
            $stmtCurrent->execute([$startDate, $endDate]);
            $resultCurrent = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
            $currentStock += floatval($resultCurrent['total_supply'] ?? 0);
        }
        
        return [
            'last_stock' => $lastStock,
            'current_stock' => $currentStock
        ];
        
    } catch (PDOException $e) {
        // 如果出错，返回0值
        return [
            'last_stock' => 0,
            'current_stock' => 0
        ];
    }
}

// 获取请求参数
$action = isset($_GET['action']) ? $_GET['action'] : '';
$restaurant = isset($_GET['restaurant']) ? $_GET['restaurant'] : 'j1';

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
                $endDate = date('Y-m-t');
            }

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
            
            // 处理 total 情况
            if ($restaurant === 'total') {
                // 对于 total，需要合并所有三个餐厅的数据
                $summary = [
                    'total_days' => 0,
                    'total_sales' => 0,
                    'total_beverage_cost' => 0,
                    'total_kitchen_cost' => 0,
                    'total_cost' => 0,
                    'total_profit' => 0,
                    'avg_cost_percent' => 0
                ];
                
                // 汇总所有餐厅的数据
                foreach (['j1', 'j2', 'j3'] as $r) {
                    $rConfig = getRestaurantConfig($r);
                    $sql = "SELECT 
                                COUNT(*) as total_days,
                                SUM(sales) as total_sales,
                                SUM(c_beverage) as total_beverage_cost,
                                SUM(c_kitchen) as total_kitchen_cost,
                                SUM(c_total) as total_cost,
                                SUM(gross_total) as total_profit
                            FROM " . $rConfig['data_table'] . " WHERE 1=1";
                    $params = [];
                    
                    if ($startDate && $endDate) {
                        $sql .= " AND date BETWEEN ? AND ?";
                        $params[] = $startDate;
                        $params[] = $endDate;
                    }
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $rSummary = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($rSummary) {
                        $summary['total_days'] += intval($rSummary['total_days'] ?? 0);
                        $summary['total_sales'] += floatval($rSummary['total_sales'] ?? 0);
                        $summary['total_beverage_cost'] += floatval($rSummary['total_beverage_cost'] ?? 0);
                        $summary['total_kitchen_cost'] += floatval($rSummary['total_kitchen_cost'] ?? 0);
                        $summary['total_cost'] += floatval($rSummary['total_cost'] ?? 0);
                        $summary['total_profit'] += floatval($rSummary['total_profit'] ?? 0);
                    }
                }
                
                // 计算平均成本率
                if ($summary['total_sales'] > 0) {
                    $summary['avg_cost_percent'] = ($summary['total_cost'] / $summary['total_sales']) * 100;
                }
            } else {
                $sql = "SELECT 
                            COUNT(*) as total_days,
                            SUM(sales) as total_sales,
                            SUM(c_beverage) as total_beverage_cost,
                            SUM(c_kitchen) as total_kitchen_cost,
                            SUM(c_total) as total_cost,
                            SUM(gross_total) as total_profit,
                            AVG(cost_percent) as avg_cost_percent
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
            }
            
            // 获取库存数据
            $stockData = getStockSupplyData($restaurant, $startDate, $endDate);
            $summary['last_stock'] = $stockData['last_stock'];
            $summary['current_stock'] = $stockData['current_stock'];
            
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
            
        case 'get_month_stock':
            // 获取月度库存数据
            $yearMonth = $_GET['year_month'] ?? null;
            if (!$yearMonth) {
                sendResponse(false, "缺少年月参数");
            }
            
            try {
                // 创建表（如果不存在）
                $createTableSql = "CREATE TABLE IF NOT EXISTS cost_month_stock (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    restaurant VARCHAR(10) NOT NULL,
                    `year_month` VARCHAR(7) NOT NULL,
                    current_stock DECIMAL(10, 2) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_restaurant_month (restaurant, `year_month`)
                )";
                $pdo->exec($createTableSql);
                
                // 获取数据
                $stmt = $pdo->prepare("SELECT * FROM cost_month_stock WHERE restaurant = ? AND `year_month` = ?");
                $stmt->execute([$restaurant, $yearMonth]);
                $record = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($record) {
                    sendResponse(true, "库存数据获取成功", $record);
                } else {
                    sendResponse(true, "暂无库存数据", null);
                }
            } catch (PDOException $e) {
                sendResponse(false, "获取库存数据失败：" . $e->getMessage());
            }
            break;

        case 'get_supply':
            // 获取 J1 供应给 J2 和 J3 的数据（仅适用于 J1 餐厅）
            // 算法与发票导出完全一致：使用分（cents）进行累加避免浮点精度问题
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            
            if ($restaurant !== 'j1') {
                sendResponse(false, "供应数据仅适用于 J1 餐厅");
            }
            
            try {
                // 辅助函数：按分进行舍入（与 JavaScript 的 roundCurrencyValue 一致）
                function roundCurrencyValue($value) {
                    if ($value === null || $value === '' || $value === 0) return 0;
                    $num = floatval($value);
                    if (!is_finite($num)) return 0;
                    
                    $sign = $num >= 0 ? 1 : -1;
                    // PHP_FLOAT_EPSILON 相当于 JavaScript 的 Number.EPSILON
                    $correction = PHP_FLOAT_EPSILON * max(1, abs($num));
                    $absRoundedCents = round((abs($num) + $correction) * 100);
                    return $sign * ($absRoundedCents / 100);
                }
                
                $supply_to_j2_cents = 0;
                $supply_to_j3_cents = 0;
                
                // 从中央 stockinout_data 表获取 J1 供应给 J2 的所有出库记录
                $sql_j2 = "SELECT out_quantity, price
                          FROM stockinout_data 
                          WHERE target_system = 'j2' AND out_quantity > 0";
                $params_j2 = [];
                
                if ($startDate && $endDate) {
                    $sql_j2 .= " AND date BETWEEN ? AND ?";
                    $params_j2[] = $startDate;
                    $params_j2[] = $endDate;
                }
                
                $stmt_j2 = $pdo->prepare($sql_j2);
                $stmt_j2->execute($params_j2);
                $records_j2 = $stmt_j2->fetchAll(PDO::FETCH_ASSOC);
                
                // 按记录逐条计算并累加到分（与 JavaScript 一致）
                foreach ($records_j2 as $record) {
                    $outQty = floatval($record['out_quantity']);
                    $price = floatval($record['price']);
                    $totalRaw = $outQty * $price;
                    $totalRounded = roundCurrencyValue($totalRaw);
                    $supply_to_j2_cents += round($totalRounded * 100);
                }
                
                // 从中央 stockinout_data 表获取 J1 供应给 J3 的所有出库记录
                $sql_j3 = "SELECT out_quantity, price
                          FROM stockinout_data 
                          WHERE target_system = 'j3' AND out_quantity > 0";
                $params_j3 = [];
                
                if ($startDate && $endDate) {
                    $sql_j3 .= " AND date BETWEEN ? AND ?";
                    $params_j3[] = $startDate;
                    $params_j3[] = $endDate;
                }
                
                $stmt_j3 = $pdo->prepare($sql_j3);
                $stmt_j3->execute($params_j3);
                $records_j3 = $stmt_j3->fetchAll(PDO::FETCH_ASSOC);
                
                // 按记录逐条计算并累加到分（与 JavaScript 一致）
                foreach ($records_j3 as $record) {
                    $outQty = floatval($record['out_quantity']);
                    $price = floatval($record['price']);
                    $totalRaw = $outQty * $price;
                    $totalRounded = roundCurrencyValue($totalRaw);
                    $supply_to_j3_cents += round($totalRounded * 100);
                }
                
                // 转换回元（与 JavaScript 的 formatCentsToCurrency 一致）
                $supply_to_j2 = $supply_to_j2_cents / 100;
                $supply_to_j3 = $supply_to_j3_cents / 100;
                
                sendResponse(true, "供应数据获取成功", [
                    'supply_to_j2' => $supply_to_j2,
                    'supply_to_j3' => $supply_to_j3
                ]);
                
            } catch (PDOException $e) {
                sendResponse(false, "获取供应数据失败：" . $e->getMessage());
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
    
    $action = $_GET['action'] ?? '';
    
    // 处理保存月度库存
    if ($action === 'save_month_stock') {
        $restaurant = $data['restaurant'] ?? 'j1';
        $yearMonth = $data['year_month'] ?? null;
        $currentStock = $data['current_stock'] ?? 0;
        
        if (!$yearMonth) {
            sendResponse(false, "缺少年月参数");
        }
        
        try {
            // 创建表（如果不存在）
            $createTableSql = "CREATE TABLE IF NOT EXISTS cost_month_stock (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant VARCHAR(10) NOT NULL,
                `year_month` VARCHAR(7) NOT NULL,
                current_stock DECIMAL(10, 2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_restaurant_month (restaurant, `year_month`)
            )";
            $pdo->exec($createTableSql);
            
            // 使用 INSERT ... ON DUPLICATE KEY UPDATE
            $sql = "INSERT INTO cost_month_stock (restaurant, `year_month`, current_stock) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE current_stock = ?, updated_at = CURRENT_TIMESTAMP";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$restaurant, $yearMonth, $currentStock, $currentStock]);
            
            // 获取保存后的记录
            $stmt = $pdo->prepare("SELECT * FROM cost_month_stock WHERE restaurant = ? AND `year_month` = ?");
            $stmt->execute([$restaurant, $yearMonth]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, "库存数据保存成功", $record);
            
        } catch (PDOException $e) {
            sendResponse(false, "保存库存数据失败：" . $e->getMessage());
        }
    }
    
    // 处理添加成本记录
    $restaurant = $data['restaurant'] ?? 'j1';
    $config = getRestaurantConfig($restaurant);
    
    // 验证必填字段
    if (empty($data['date'])) {
        sendResponse(false, "缺少必填字段：日期");
    }
    
    try {
        $sql = "INSERT INTO " . $config['data_table'] . " 
                (date, day_name, sales, c_beverage, c_kitchen) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        $dayName = date('l', strtotime($data['date']));
        $stmt->execute([
            $data['date'],
            $dayName,
            $data['sales'] ?? 0,
            $data['c_beverage'] ?? 0,
            $data['c_kitchen'] ?? 0
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
    if (empty($data['date'])) {
        sendResponse(false, "缺少必填字段：日期");
    }
    
    try {
        // 先读取数据库现有记录，用于“缺省字段保持原值”（避免前端清空成本时误把 sales 覆盖成 0）
        $stmt = $pdo->prepare("SELECT * FROM " . $config['data_table'] . " WHERE id = ?");
        $stmt->execute([$data['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            sendResponse(false, "记录不存在");
        }

        // 若字段未提供（或为空/null），则保持数据库原值
        $salesValue = (array_key_exists('sales', $data) && $data['sales'] !== null && $data['sales'] !== '')
            ? $data['sales']
            : ($existing['sales'] ?? 0);
        $beverageValue = (array_key_exists('c_beverage', $data) && $data['c_beverage'] !== null && $data['c_beverage'] !== '')
            ? $data['c_beverage']
            : ($existing['c_beverage'] ?? 0);
        $kitchenValue = (array_key_exists('c_kitchen', $data) && $data['c_kitchen'] !== null && $data['c_kitchen'] !== '')
            ? $data['c_kitchen']
            : ($existing['c_kitchen'] ?? 0);

        // 添加日志记录
        error_log("=== PUT 请求调试 ===");
        error_log("餐厅: " . $restaurant);
        error_log("表名: " . $config['data_table']);
        error_log("记录ID: " . $data['id']);
        error_log("日期: " . $data['date']);
        error_log("销售额: " . $salesValue);
        error_log("饮料成本: " . $beverageValue);
        error_log("厨房成本: " . $kitchenValue);
        
        $sql = "UPDATE " . $config['data_table'] . " 
                SET date = ?, day_name = ?, sales = ?, c_beverage = ?, c_kitchen = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        $dayName = date('l', strtotime($data['date']));
        $result = $stmt->execute([
            $data['date'],
            $dayName,
            $salesValue,
            $beverageValue,
            $kitchenValue,
            $data['id']
        ]);
        
        error_log("执行的 SQL: " . $sql);
        error_log("影响行数: " . $stmt->rowCount());
        
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
?>

