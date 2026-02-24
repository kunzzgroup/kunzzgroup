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

// 获取J2库存汇总数据
function getJ2StockSummary($startDate = null, $endDate = null) {
    global $pdo;

    try {
        // 1) 桌面端库存：按 (名称, 编号, 规格, 单价) 分组
        $sqlDesktop = "SELECT product_name, specification, price, code_number, type,
            SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
            SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
            (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) -
             SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock
            FROM j2stockedit_data
            WHERE product_name IS NOT NULL AND product_name != ''";
        $params = [];
        if ($endDate) {
            $sqlDesktop .= " AND date <= ?";
            $params[] = $endDate;
        }
        $sqlDesktop .= " GROUP BY product_name, specification, price, code_number, type ORDER BY product_name ASC, price ASC";
        $stmt = $pdo->prepare($sqlDesktop);
        $stmt->execute($params);
        $desktopRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2) 手机端出货合计：按 (名称, 编号) 汇总，不区分单价
        $sqlMobile = "SELECT product_name, code_number, SUM(out_quantity) as mobile_out
            FROM j2stockeditmobile_data
            WHERE product_name IS NOT NULL AND product_name != ''";
        $paramsM = [];
        if ($endDate) {
            $sqlMobile .= " AND date <= ?";
            $paramsM[] = $endDate;
        }
        $sqlMobile .= " GROUP BY product_name, code_number";
        $stmtM = $pdo->prepare($sqlMobile);
        $stmtM->execute($paramsM);
        $mobileOutList = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        $mobileOutMap = [];
        $normKey = function ($name, $code) {
            return preg_replace('/\s+/', '', trim($name ?? '')) . '|' . preg_replace('/\s+/', '', trim($code ?? ''));
        };
        foreach ($mobileOutList as $r) {
            $mobileOutMap[$normKey($r['product_name'], $r['code_number'])] = floatval($r['mobile_out']);
        }

        // 3) 按 (product_name, code_number) 分组桌面行，手机出货按「先扣高价」从各单价档扣减
        $byProduct = [];
        foreach ($desktopRows as $row) {
            $name = $row['product_name'] ?? '';
            $code = $row['code_number'] ?? '';
            $key = $normKey($name, $code);
            if (!isset($byProduct[$key])) {
                $byProduct[$key] = [];
            }
            $stock = floatval($row['current_stock']);
            $price = floatval($row['price']);
            $type = $row['type'] ?? '';
            if ($type === 'Drinks') $type = 'Service Line';
            $byProduct[$key][] = [
                'product_name' => $name,
                'code_number' => $code,
                'specification' => $row['specification'] ?? '',
                'price' => $price,
                'type' => $type,
                'stock' => $stock
            ];
        }
        foreach ($mobileOutMap as $pkey => $mobileOut) {
            if ($mobileOut <= 0 || !isset($byProduct[$pkey])) continue;
            $rows = &$byProduct[$pkey];
            usort($rows, function ($a, $b) { return $b['price'] <=> $a['price']; });
            $remain = $mobileOut;
            foreach ($rows as &$r) {
                if ($remain <= 0) break;
                $deduct = min($r['stock'], $remain);
                $r['stock'] -= $deduct;
                $remain -= $deduct;
            }
            unset($r);
        }

        // 4) 展平为 stock > 0 的行，按名称、单价排序
        $merged = [];
        foreach ($byProduct as $rows) {
            foreach ($rows as $r) {
                if ($r['stock'] <= 0) continue;
                $merged[] = $r;
            }
        }
        usort($merged, function ($a, $b) {
            $c = strcmp($a['product_name'], $b['product_name']);
            return $c !== 0 ? $c : ($a['price'] <=> $b['price']);
        });

        $totalValue = 0;
        $summaryData = [];
        $counter = 1;
        $typeStats = [
            'Kitchen' => 0,
            'Sushi Bar' => 0,
            'Service Line' => 0,
            'Sake' => 0
        ];
        foreach ($merged as $v) {
            $currentStock = $v['stock'];
            $price = $v['price'];
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
        throw new Exception("查询J2库存数据失败：" . $e->getMessage());
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
                $result = getJ2StockSummary(null, $endDate);
                sendResponse(true, "J2库存汇总数据获取成功", $result);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;
            
        case 'supply_total':
            // 获取J2入库总值（从j2stockinout_data表，仅当前月份）
            try {
                // 获取当前月份的第一天和最后一天
                $firstDayOfMonth = date('Y-m-01');
                $lastDayOfMonth = date('Y-m-t');
                
                $sql = "SELECT SUM(in_quantity * price) as total_supply_value 
                        FROM j2stockinout_data 
                        WHERE in_quantity > 0 
                        AND date >= ? AND date <= ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$firstDayOfMonth, $lastDayOfMonth]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $totalSupplyValue = floatval($result['total_supply_value'] ?? 0);
                
                sendResponse(true, "J2供应总值获取成功", [
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
                $result = getJ2StockSummary();
                
                // 设置CSV头信息
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="j2_stock_summary_' . date('Y-m-d') . '.csv"');
                
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