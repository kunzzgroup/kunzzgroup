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
// 真实货物数量 = 有手机记录时用 j2stocklist_total.total_qty（现有），否则用桌面该货品库存合计
// 货品全集 = 桌面 j2stockedit_data 所有货品 + j2stocklist_total 所有货品，保证「其余资料」都显示
function getJ2StockSummary($startDate = null, $endDate = null) {
    global $pdo;

    // 1) 桌面按货品汇总：每货品一行，库存合计 + 最新单价/规格/类型
    $sqlDesktop = "SELECT d.product_name, d.code_number,
            SUM(CASE WHEN d.in_quantity > 0 THEN d.in_quantity ELSE 0 END) - SUM(CASE WHEN d.out_quantity > 0 THEN d.out_quantity ELSE 0 END) as desktop_stock
            FROM j2stockedit_data d
            WHERE d.product_name IS NOT NULL AND d.product_name != ''
            GROUP BY d.product_name, d.code_number";
    $stmt = $pdo->prepare($sqlDesktop);
    $stmt->execute();
    $desktopStock = [];
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $k = ($r['product_name'] ?? '') . '|' . ($r['code_number'] ?? '');
        $desktopStock[$k] = ['stock' => floatval($r['desktop_stock'] ?? 0), 'product_name' => $r['product_name'] ?? '', 'code_number' => $r['code_number'] ?? ''];
    }
    $sqlLatest = "SELECT je1.product_name, je1.code_number, je1.specification, je1.price, je1.type
            FROM j2stockedit_data je1
            INNER JOIN (SELECT product_name, code_number, MAX(id) as max_id FROM j2stockedit_data WHERE price > 0 GROUP BY product_name, code_number) je2 ON je1.id = je2.max_id
            ORDER BY je1.product_name, je1.price";
    $stmt2 = $pdo->query($sqlLatest);
    $desktopByProduct = [];
    while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $k = ($r['product_name'] ?? '') . '|' . ($r['code_number'] ?? '');
        $desktopByProduct[$k] = [
            'product_name' => $r['product_name'],
            'code_number' => $r['code_number'] ?? '',
            'desktop_stock' => isset($desktopStock[$k]) ? $desktopStock[$k]['stock'] : 0,
            'specification' => $r['specification'] ?? '',
            'price' => floatval($r['price'] ?? 0),
            'type' => $r['type'] ?? ''
        ];
    }
    foreach ($desktopStock as $k => $v) {
        if (!isset($desktopByProduct[$k])) {
            $desktopByProduct[$k] = [
                'product_name' => $v['product_name'],
                'code_number' => $v['code_number'],
                'desktop_stock' => $v['stock'],
                'specification' => '',
                    HAVING current_stock != 0
                'price' => 0,
                'type' => ''
            ];
        }
    }

    // 2) 手机现有数量（有则优先用）
    $mobileMap = [];
    try {
        $stmtM = $pdo->query("SELECT product_name, code_number, total_qty FROM j2stocklist_total");
        while ($r = $stmtM->fetch(PDO::FETCH_ASSOC)) {
            $k = ($r['product_name'] ?? '') . '|' . ($r['code_number'] ?? '');
            $mobileMap[$k] = ['qty' => floatval($r['total_qty'] ?? 0), 'product_name' => $r['product_name'] ?? '', 'code_number' => $r['code_number'] ?? ''];
        }
    } catch (PDOException $e) {
        if ($e->getCode() !== '42S02' && strpos($e->getMessage(), '1146') === false) {
            throw new Exception("查询J2库存数据失败：" . $e->getMessage());
        }
    }

    // 3) 合并：所有桌面货品 + 仅在手机出现的货品；数量 = 有手机用 total_qty，否则用桌面合计
    $rows = [];
    foreach ($desktopByProduct as $k => $v) {
        $qty = isset($mobileMap[$k]) ? $mobileMap[$k]['qty'] : $v['desktop_stock'];
        $rows[] = [
            'product_name' => $v['product_name'],
            'code_number' => $v['code_number'],
            'total_qty' => $qty,
            'specification' => $v['specification'],
            'price' => $v['price'],
            'type' => $v['type']
        ];
    }
    foreach ($mobileMap as $k => $m) {
        if (!isset($desktopByProduct[$k]) && $m['qty'] > 0) {
            $rows[] = [
                'product_name' => $m['product_name'],
                'code_number' => $m['code_number'],
                'total_qty' => $m['qty'],
                'specification' => '',
                'price' => 0,
                'type' => ''
                    HAVING current_stock != 0
            ];
        }
    }
    usort($rows, function ($a, $b) {
        $c = strcmp($a['product_name'], $b['product_name']);
        return $c !== 0 ? $c : ($a['price'] <=> $b['price']);
    });

    $totalValue = 0;
    $summaryData = [];
    $counter = 1;
    $typeStats = ['Kitchen' => 0, 'Sushi Bar' => 0, 'Service Line' => 0, 'Sake' => 0];
    foreach ($rows as $row) {
        $currentStock = floatval($row['total_qty'] ?? 0);
        if ($currentStock <= 0) continue;
        $price = floatval($row['price'] ?? 0);
        $type = $row['type'] ?? '';
        if ($type === 'Drinks') $type = 'Service Line';
        $totalPrice = $currentStock * $price;
        $totalValue += $totalPrice;
        if (!empty($type) && isset($typeStats[$type])) $typeStats[$type] += $totalPrice;
        $summaryData[] = [
            'no' => $counter++,
            'product_name' => $row['product_name'],
            'code_number' => $row['code_number'],
            'total_stock' => $currentStock,
            'specification' => $row['specification'],
            'price' => $price,
            'total_price' => $totalPrice,
            'type' => $type,
            'formatted_stock' => number_format($currentStock, 2),
            'formatted_price' => number_format($price, 2),
            'formatted_total_price' => number_format($totalPrice, 2)
        ];
    }

        
        // 初始化类型统计
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