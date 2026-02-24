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
function getJ1StockSummary($startDate = null, $endDate = null) {
    global $pdo;
    
    try {
        // 如果提供了结束日期，计算到该日期为止的所有库存（包括历史累计）
        if ($endDate) {
            // 计算到结束日期为止的所有库存
            // 合并 j1stockedit_data 和 j1stockeditmobile_data 两个表的数据
            $sql = "SELECT 
                        product_name,
                        specification,
                        price,
                        code_number,
                        type,
                        SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                        (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                         SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock
                    FROM (
                        SELECT product_name, specification, price, code_number, type, in_quantity, out_quantity
                        FROM j1stockedit_data 
                        WHERE product_name IS NOT NULL AND product_name != ''
                        AND date <= ?
                        UNION ALL
                        SELECT 
                            m.product_name,
                            COALESCE(sd.specification, je.specification, NULL) as specification,
                            COALESCE(je.price, 0) as price,
                            m.code_number,
                            COALESCE(sd.category, je.type, NULL) as type,
                            m.in_quantity,
                            m.out_quantity
                        FROM j1stockeditmobile_data m
                        LEFT JOIN stock_data sd ON (sd.product_name = m.product_name OR sd.product_code = m.code_number)
                        LEFT JOIN (
                            SELECT je1.product_name, je1.code_number, je1.price, je1.specification, je1.type
                            FROM j1stockedit_data je1
                            INNER JOIN (
                                SELECT product_name, code_number, MAX(id) as max_id
                                FROM j1stockedit_data
                                WHERE price > 0
                                GROUP BY product_name, code_number
                            ) je2 ON je1.id = je2.max_id
                        ) je ON je.product_name = m.product_name 
                            AND (je.code_number = m.code_number OR (je.code_number IS NULL AND m.code_number IS NULL))
                        WHERE m.product_name IS NOT NULL AND m.product_name != ''
                        AND m.date <= ?
                    ) AS combined_data
                    GROUP BY product_name, specification, price, code_number, type
                    ORDER BY product_name ASC, price ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$endDate, $endDate]);
        } else {
            // 没有日期范围，返回所有库存
            // 合并 j1stockedit_data 和 j1stockeditmobile_data 两个表的数据
            $sql = "SELECT 
                        product_name,
                        specification,
                        price,
                        code_number,
                        type,
                        SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                        (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                         SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock
                    FROM (
                        SELECT product_name, specification, price, code_number, type, in_quantity, out_quantity
                        FROM j1stockedit_data 
                        WHERE product_name IS NOT NULL AND product_name != ''
                        UNION ALL
                        SELECT 
                            m.product_name,
                            COALESCE(sd.specification, je.specification, NULL) as specification,
                            COALESCE(je.price, 0) as price,
                            m.code_number,
                            COALESCE(sd.category, je.type, NULL) as type,
                            m.in_quantity,
                            m.out_quantity
                        FROM j1stockeditmobile_data m
                        LEFT JOIN stock_data sd ON (sd.product_name = m.product_name OR sd.product_code = m.code_number)
                        LEFT JOIN (
                            SELECT je1.product_name, je1.code_number, je1.price, je1.specification, je1.type
                            FROM j1stockedit_data je1
                            INNER JOIN (
                                SELECT product_name, code_number, MAX(id) as max_id
                                FROM j1stockedit_data
                                WHERE price > 0
                                GROUP BY product_name, code_number
                            ) je2 ON je1.id = je2.max_id
                        ) je ON je.product_name = m.product_name 
                            AND (je.code_number = m.code_number OR (je.code_number IS NULL AND m.code_number IS NULL))
                        WHERE m.product_name IS NOT NULL AND m.product_name != ''
                    ) AS combined_data
                    GROUP BY product_name, specification, price, code_number, type
                    ORDER BY product_name ASC, price ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 同一货品（名称+编号+规格+单价）合并为一行；同品名不同单价不合并，各占一行
        $merged = [];
        foreach ($stockData as $row) {
            $price = floatval($row['price']);
            $key = ($row['product_name'] ?? '') . '|' . ($row['code_number'] ?? '') . '|' . ($row['specification'] ?? '') . '|' . round($price, 4);
            $currentStock = floatval($row['current_stock']);
            $type = $row['type'] ?? '';
            if ($type === 'Drinks') $type = 'Service Line';
            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'product_name' => $row['product_name'],
                    'code_number' => $row['code_number'] ?? '',
                    'specification' => $row['specification'] ?? '',
                    'stock' => 0,
                    'value_sum' => 0,
                    'type' => $type
                ];
            }
            $merged[$key]['stock'] += $currentStock;
            $merged[$key]['value_sum'] += $price * $currentStock;
            if ($currentStock > ($merged[$key]['_max_stock'] ?? 0)) {
                $merged[$key]['_max_stock'] = $currentStock;
                $merged[$key]['type'] = $type;
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
        foreach ($merged as $k => $v) {
            $currentStock = $v['stock'];
            if ($currentStock == 0) continue;
            $price = $currentStock != 0 ? $v['value_sum'] / $currentStock : 0;
            $totalPrice = $currentStock * $price;
            $type = $v['type'] ?? '';
            $totalValue += $totalPrice;
            if (!empty($type) && isset($typeStats[$type])) {
                $typeStats[$type] += $totalPrice;
            }
            $summaryData[] = [
                'no' => $counter++,
                'product_name' => $v['product_name'],
                'code_number' => $v['code_number'],
                'total_stock' => $currentStock,
                'specification' => $v['specification'],
                'price' => $price,
                'total_price' => $totalPrice,
                'type' => $type,
                'formatted_stock' => number_format($currentStock, 2),
                'formatted_price' => number_format($price, 2),
                'formatted_total_price' => number_format($totalPrice, 2)
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
            ]
        ];
        
    } catch (PDOException $e) {
        throw new Exception("查询J1库存数据失败：" . $e->getMessage());
    }
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