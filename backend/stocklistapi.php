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

// 获取库存汇总数据
function getStockSummary($startDate = null, $endDate = null) {
    global $pdo;
    
    try {
        // 如果提供了日期范围，计算到结束日期为止的所有库存（包括历史累计）
        if ($startDate && $endDate) {
            // 计算到结束日期为止的所有库存
            $sql = "SELECT 
                        product_name,
                        specification,
                        price,
                        code_number,
                        SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                        (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                         SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock
                    FROM stockinout_data 
                    WHERE product_name IS NOT NULL AND product_name != ''
                    AND date <= ?
                    GROUP BY product_name, specification, price, code_number
                    HAVING current_stock > 0
                    ORDER BY product_name ASC, price ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$endDate]);
        } else {
            // 没有日期范围，返回所有库存
            $sql = "SELECT 
                        product_name,
                        specification,
                        price,
                        code_number,
                        SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                        (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                         SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock
                    FROM stockinout_data 
                    WHERE product_name IS NOT NULL AND product_name != ''
                    GROUP BY product_name, specification, price, code_number
                    HAVING current_stock > 0
                    ORDER BY product_name ASC, price ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 计算总价值 - 使用原始数值计算，只在显示时格式化
        $totalValue = 0;
        $summaryData = [];
        $counter = 1;
        
        foreach ($stockData as $row) {
            $currentStock = floatval($row['current_stock']);
            $price = floatval($row['price']);
            $totalPrice = $currentStock * $price;
            
            // 使用原始数值累加，不进行四舍五入
            $totalValue += $totalPrice;
            
            $summaryData[] = [
                'no' => $counter++,
                'product_name' => $row['product_name'],
                'code_number' => $row['code_number'] ?? '',
                'total_stock' => $currentStock,
                'specification' => $row['specification'] ?? '',
                'price' => $price,
                'total_price' => $totalPrice, // 使用原始计算值
                'formatted_stock' => number_format($currentStock, 2),
                'formatted_price' => number_format($price, 2),
                'formatted_total_price' => number_format($totalPrice, 2) // 显示时格式化为两位小数
            ];
        }
        
        return [
            'summary' => $summaryData,
            'total_value' => $totalValue,
            'formatted_total_value' => number_format($totalValue, 2),
            'total_products' => count($summaryData)
        ];
        
    } catch (PDOException $e) {
        throw new Exception("查询库存数据失败：" . $e->getMessage());
    }
}

// 修改现有的 getLowStockAlerts() 函数
function getLowStockAlerts() {
    global $pdo;
    
    try {
        // 获取当前库存和最低库存设置，只显示有设置且库存不足的货品
        $sql = "SELECT 
                    TRIM(s.product_name) as product_name,
                    s.code_number,
                    s.specification,
                    s.current_stock,
                    s.formatted_stock,
                    m.minimum_quantity
                FROM (
                    SELECT 
                        product_name,
                        code_number,
                        specification,
                        (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                         SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock,
                        FORMAT((SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                               SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)), 2) as formatted_stock
                    FROM stockinout_data 
                    WHERE product_name IS NOT NULL AND product_name != ''
                    GROUP BY product_name, code_number, specification
                ) s
                INNER JOIN stock_minimum_settings m ON TRIM(s.product_name) = TRIM(m.product_name)
                WHERE m.minimum_quantity > 0 AND s.current_stock <= m.minimum_quantity
                ORDER BY s.product_name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $lowStockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $lowStockData;
        
    } catch (PDOException $e) {
        throw new Exception("查询低库存数据失败：" . $e->getMessage());
    }
}

// 主要路由处理
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'summary';
    
    switch ($action) {
        case 'summary':
            try {
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $result = getStockSummary($startDate, $endDate);
                sendResponse(true, "库存汇总数据获取成功", $result);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        case 'low_stock_alerts':
            try {
                $result = getLowStockAlerts();
                sendResponse(true, "低库存预警数据获取成功", ['alerts' => $result, 'count' => count($result)]);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;
            
        case 'export':
            // 导出功能（可选实现）
            try {
                $result = getStockSummary();
                
                // 设置CSV头信息
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="stock_summary_' . date('Y-m-d') . '.csv"');
                
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