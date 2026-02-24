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

// 获取J1库存汇总数据
// 真实货物数量 = 手机版现有数量，即 j1stocklist_total.total_qty（手机端进出时更新）
function getJ1StockSummary($startDate = null, $endDate = null) {
    global $pdo;

    try {
        // 真实显示数量 = j1stocklist_total.total_qty（现有货物数量），关联桌面取单价/规格/类型
        $sql = "SELECT t.product_name, t.code_number, t.total_qty,
                je.specification, je.price, je.type
                FROM j1stocklist_total t
                LEFT JOIN (
                    SELECT je1.product_name, je1.code_number, je1.specification, je1.price, je1.type
                    FROM j1stockedit_data je1
                    INNER JOIN (
                        SELECT product_name, code_number, MAX(id) as max_id
                        FROM j1stockedit_data WHERE price > 0
                        GROUP BY product_name, code_number
                    ) je2 ON je1.id = je2.max_id
                ) je ON je.product_name = t.product_name
                    AND (je.code_number = t.code_number OR (je.code_number IS NULL AND t.code_number IS NULL))
                WHERE t.total_qty > 0
                ORDER BY t.product_name ASC, COALESCE(je.price, 0) ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if ($e->getCode() === '42S02' || strpos($e->getMessage(), '1146') !== false) {
            $stockData = [];
        } else {
            throw new Exception("查询J1库存数据失败：" . $e->getMessage());
        }
    }

    $totalValue = 0;
    $summaryData = [];
    $counter = 1;
    $typeStats = [
        'Kitchen' => 0,
        'Sushi Bar' => 0,
        'Service Line' => 0,
        'Sake' => 0
    ];
    foreach ($stockData as $row) {
        $currentStock = floatval($row['total_qty'] ?? 0);
        $price = floatval($row['price'] ?? 0);
        $type = $row['type'] ?? '';
        if ($type === 'Drinks') $type = 'Service Line';
                    HAVING current_stock != 0
        $totalPrice = $currentStock * $price;
        $totalValue += $totalPrice;
        if (!empty($type) && isset($typeStats[$type])) {
            $typeStats[$type] += $totalPrice;
        }
        $summaryData[] = [
            'no' => $counter++,
            'product_name' => $row['product_name'],
            'code_number' => $row['code_number'] ?? '',
            'total_stock' => $currentStock,
            'specification' => $row['specification'] ?? '',
            'price' => $price,
            'total_price' => $totalPrice,
            'type' => $type,
            'formatted_stock' => number_format($currentStock, 2),
            'formatted_price' => number_format($price, 2),
            'formatted_total_price' => number_format($totalPrice, 2)
        
        foreach ($stockData as $row) {
            $currentStock = floatval($row['current_stock']);
            $price = floatval($row['price']);
            $totalPrice = $currentStock * $price;
            $type = $row['type'] ?? '';
            // Drinks 与 Service Line 合并统计
            if ($type === 'Drinks') $type = 'Service Line';
            
            // 使用原始数值累加，不进行四舍五入
            $totalValue += $totalPrice;
            
            // 按类型累计库存价值
            if (!empty($type) && isset($typeStats[$type])) {
                $typeStats[$type] += $totalPrice;
            }
            
            $summaryData[] = [
                'no' => $counter++,
                'product_name' => $row['product_name'],
                'code_number' => $row['code_number'] ?? '',
                'total_stock' => $currentStock,
                'specification' => $row['specification'] ?? '',
                'price' => $price,
                'total_price' => $totalPrice, // 使用原始计算值
                'type' => $type,
                'formatted_stock' => number_format($currentStock, 2),
                'formatted_price' => number_format($price, 2),
                'formatted_total_price' => number_format($totalPrice, 2) // 显示时格式化为两位小数
            ];
        }
        
        return [
            'summary' => $summaryData,
            'total_value' => $totalValue,
                    HAVING current_stock != 0
            'formatted_total_value' => number_format($totalValue, 2),
            'total_products' => count($summaryData),
            'type_stats' => [
                'kitchen' => $typeStats['Kitchen'],
                'sushi_bar' => $typeStats['Sushi Bar'],
                'service_line' => $typeStats['Service Line'],
                'sake' => $typeStats['Sake'],
                'formatted_kitchen' => number_format($typeStats['Kitchen'], 2),
                'formatted_sushi_bar' => number_format($typeStats['Sushi Bar'], 2),
                'formatted_service_line' => number_format($typeStats['Service Line'], 2),
                'formatted_sake' => number_format($typeStats['Sake'], 2)
            ]
            $key = ($row['product_name'] ?? '') . '|' . ($row['code_number'] ?? '') . '|' . ($row['specification'] ?? '');
            $currentStock = floatval($row['current_stock']);
        ];
        
    } catch (PDOException $e) {
        throw new Exception("查询J1库存数据失败：" . $e->getMessage());
        ];
    }

    return [
        'summary' => $summaryData,
        'total_value' => $totalValue,
        'formatted_total_value' => number_format($totalValue, 2),
        'total_products' => count($summaryData),
        'type_stats' => [
            'kitchen' => $typeStats['Kitchen'],
            'sushi_bar' => $typeStats['Sushi Bar'],
            'service_line' => $typeStats['Service Line'],
            'sake' => $typeStats['Sake'],
            'formatted_kitchen' => number_format($typeStats['Kitchen'], 2),
            'formatted_sushi_bar' => number_format($typeStats['Sushi Bar'], 2),
            'formatted_service_line' => number_format($typeStats['Service Line'], 2),
            'formatted_sake' => number_format($typeStats['Sake'], 2)
        
        // 初始化类型统计
        ]
    ];
}

// 主要路由处理
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'summary';
    
    switch ($action) {
        case 'summary':
            try {
                $endDate = $_GET['end_date'] ?? null;
                $result = getJ1StockSummary(null, $endDate);
                sendResponse(true, "J1库存汇总数据获取成功", $result);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;
            
        case 'supply_total':
            // 获取J1入库总值（从j1stockinout_data表，仅当前月份）
            try {
                // 获取当前月份的第一天和最后一天
                $firstDayOfMonth = date('Y-m-01');
                $lastDayOfMonth = date('Y-m-t');
                
                $sql = "SELECT SUM(in_quantity * price) as total_supply_value 
                        FROM j1stockinout_data 
                        WHERE in_quantity > 0 
                        AND date >= ? AND date <= ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$firstDayOfMonth, $lastDayOfMonth]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $totalSupplyValue = floatval($result['total_supply_value'] ?? 0);
                
                sendResponse(true, "J1供应总值获取成功", [
                    'total_supply_value' => $totalSupplyValue,
                    'formatted_total_value' => number_format($totalSupplyValue, 2),
                    'month' => date('Y-m')
                ]);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;
            
        case 'export':
            // 导出功能（可选实现）
            try {
                $result = getJ1StockSummary();
                
                // 设置CSV头信息
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="j1_stock_summary_' . date('Y-m-d') . '.csv"');
                
                ob_end_clean();
                
                // 创建CSV输出
                $output = fopen('php://output', 'w');
                
                // 写入BOM以支持中文
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // 写入表头
                fputcsv($output, ['No', 'Product Name', 'Code Number', 'Total Stock', 'Specification', 'Unit Price (RM)', 'Total Price (RM)']);
                
                // 写入数据
                foreach ($result['summary'] as $row) {
                    fputcsv($output, [
                        $row['no'],
                        $row['product_name'],
                        $row['code_number'],
                        $row['formatted_stock'],
                        $row['specification'],
                        $row['formatted_price'],
                        $row['formatted_total_price']
                    ]);
                }
                
                // 写入总计
                fputcsv($output, ['', '', '', '', '', 'Total Value:', $result['formatted_total_value']]);
                
                fclose($output);
                exit;
                
            } catch (Exception $e) {
                sendResponse(false, "导出失败：" . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, "无效的操作");
    }
} else {
    sendResponse(false, "不支持的请求方法");
}
?>