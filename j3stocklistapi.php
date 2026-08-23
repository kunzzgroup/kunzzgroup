<?php
require_once __DIR__ . '/config.php';
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

try {
    $pdo = get_pdo_connection();
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

// 获取J3库存汇总数据
function getJ3StockSummary() {
    global $pdo;
    
    try {
        // 查询j3stockedit_data表的汇总数据：按产品名称、规格、价格分组计算库存
        $sql = "SELECT 
                    product_name,
                    specification,
                    price,
                    code_number,
                    SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                    (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                     SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock
                FROM j3stockedit_data 
                WHERE product_name IS NOT NULL AND product_name != ''
                GROUP BY product_name, specification, price, code_number
                HAVING current_stock > 0
                ORDER BY product_name ASC, price ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 同一货品（名称+编号+规格+显示单价）合并为一行
        $merged = [];
        foreach ($stockData as $row) {
            $productName = trim($row['product_name'] ?? '');
            $codeNumber = trim($row['code_number'] ?? '');
            $specification = trim($row['specification'] ?? '');
            $price = floatval($row['price']);
            $formattedPrice = number_format($price, 2);
            $key = $productName . '|' . $codeNumber . '|' . $specification . '|' . $formattedPrice;

            $currentStock = floatval($row['current_stock']);
            $rowTotalPrice = $currentStock * $formattedPrice;

            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'product_name' => $productName,
                    'code_number' => $codeNumber,
                    'specification' => $specification,
                    'formatted_price' => $formattedPrice,
                    'price' => floatval($formattedPrice),
                    'stock' => 0,
                    'total_price' => 0
                ];
            }
            $merged[$key]['stock'] += $currentStock;
            $merged[$key]['total_price'] += $rowTotalPrice;
        }

        uasort($merged, function ($a, $b) {
            $cmp = strcasecmp($a['product_name'], $b['product_name']);
            if ($cmp !== 0) return $cmp;
            return $a['price'] <=> $b['price'];
        });

        $totalValue = 0;
        $summaryData = [];
        $counter = 1;

        foreach ($merged as $v) {
            $currentStock = $v['stock'];
            if ($currentStock == 0) continue;

            $totalPrice = $v['total_price'];
            $totalValue += $totalPrice;

            $summaryData[] = [
                'no' => $counter++,
                'product_name' => $v['product_name'],
                'code_number' => $v['code_number'],
                'total_stock' => $currentStock,
                'specification' => $v['specification'],
                'price' => $v['price'],
                'total_price' => $totalPrice,
                'formatted_stock' => number_format($currentStock, 2),
                'formatted_price' => $v['formatted_price'],
                'formatted_total_price' => number_format($totalPrice, 2)
            ];
        }
        
        return [
            'summary' => $summaryData,
            'total_value' => $totalValue,
            'formatted_total_value' => number_format($totalValue, 2),
            'total_products' => count($summaryData)
        ];
        
    } catch (PDOException $e) {
        throw new Exception("查询J3库存数据失败：" . $e->getMessage());
    }
}

// 主要路由处理
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'summary';
    
    switch ($action) {
        case 'summary':
            try {
                $result = getJ3StockSummary();
                sendResponse(true, "J3库存汇总数据获取成功", $result);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;
            
        case 'export':
            // 导出功能（可选实现）
            try {
                $result = getJ3StockSummary();
                
                // 设置CSV头信息
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="j3_stock_summary_' . date('Y-m-d') . '.csv"');
                
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
