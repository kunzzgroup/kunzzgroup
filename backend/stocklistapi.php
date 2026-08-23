<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/permission_guard.php';
requirePermissionApi('resource', 'stock_inventory');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
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

try {
    $pdo = get_pdo_connection();
}
catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $e->getMessage()]);
    exit;
}

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

// 获取库存汇总数据
function getStockSummary($system = 'central', $startDate = null, $endDate = null)
{
    global $pdo;

    try {
        $tableMap = [
            'central' => 'stockinout_data',
            'j1' => 'j1stockedit_data',
            'j2' => 'j2stockedit_data',
            'j3' => 'j3stockedit_data'
        ];

        $tableName = $tableMap[$system] ?? 'stockinout_data';
        $isBranch = ($system !== 'central');

        // 构建基础查询
        $sql = "SELECT 
                    REPLACE(product_name, '&amp;', '&') as product_name,
                    specification,
                    price,
                    code_number";

        if ($isBranch) {
            $sql .= ", MAX(type) as type";
        }

        $sql .= ", SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                   SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out,
                   (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - 
                    SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) as current_stock
                FROM $tableName 
                WHERE product_name IS NOT NULL AND product_name != ''
                AND deleted_at IS NULL";

        $queryParams = [];
        // 库存是累积的：必须从最早记录计算到 end_date 才能得到正确余额
        // start_date 不参与SQL查询，仅用于PDF标注日期范围
        // 例如：查看3月数据时，SQL 只限制 date <= '2026-03-31'
        // 这样才能正确计算截至3月31日的库存余额（不会包含4月数据）
        if ($endDate) {
            $sql .= " AND date <= ?";
            $queryParams[] = $endDate;
        }

        $groupBy = "REPLACE(product_name, '&amp;', '&'), specification, price, code_number";

        $sql .= " GROUP BY $groupBy
                  HAVING current_stock != 0
                  ORDER BY product_name ASC, price ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($queryParams);
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
                    'total_price' => 0,
                    'type' => ''
                ];
                if ($isBranch) {
                    $type = $row['type'] ?? '';
                    if ($type === 'Drinks')
                        $type = 'Service Line';
                    $merged[$key]['type'] = $type;
                }
            }
            $merged[$key]['stock'] += $currentStock;
            $merged[$key]['total_price'] += $rowTotalPrice;
        }

        uasort($merged, function ($a, $b) {
            $cmp = strcasecmp($a['product_name'], $b['product_name']);
            if ($cmp !== 0)
                return $cmp;
            return $a['price'] <=> $b['price'];
        });

        $totalValue = 0;
        $summaryData = [];
        $counter = 1;

        // 分支机构特有的类型统计
        $typeStats = $isBranch ? [
            'Kitchen' => 0,
            'Sushi Bar' => 0,
            'Service Line' => 0,
            'Sake' => 0
        ] : null;

        foreach ($merged as $v) {
            $currentStock = $v['stock'];
            if ($currentStock == 0)
                continue;

            $specification = $v['specification'];
            $stockDecimals = (strtolower($specification) === 'kilo') ? 3 : 2;
            $price = $v['price'];
            $totalPrice = $v['total_price'];
            $totalValue += $totalPrice;

            $item = [
                'no' => $counter++,
                'product_name' => $v['product_name'],
                'code_number' => $v['code_number'],
                'total_stock' => $currentStock,
                'specification' => $specification,
                'price' => $price,
                'total_price' => $totalPrice,
                'formatted_stock' => number_format($currentStock, $stockDecimals),
                'formatted_price' => $v['formatted_price'],
                'formatted_total_price' => number_format($totalPrice, 2)
            ];

            if ($isBranch) {
                $type = $v['type'];
                $item['type'] = $type;

                if (!empty($type) && isset($typeStats[$type])) {
                    $typeStats[$type] += $totalPrice;
                }
            }

            $summaryData[] = $item;
        }

        $result = [
            'summary' => $summaryData,
            'total_value' => $totalValue,
            'formatted_total_value' => number_format($totalValue, 2),
            'total_products' => count($summaryData)
        ];

        if ($isBranch) {
            $result['type_stats'] = [
                'kitchen' => $typeStats['Kitchen'],
                'sushi_bar' => $typeStats['Sushi Bar'],
                'service_line' => $typeStats['Service Line'],
                'sake' => $typeStats['Sake'],
                'formatted_kitchen' => number_format($typeStats['Kitchen'], 2),
                'formatted_sushi_bar' => number_format($typeStats['Sushi Bar'], 2),
                'formatted_service_line' => number_format($typeStats['Service Line'], 2),
                'formatted_sake' => number_format($typeStats['Sake'], 2)
            ];
        }
        else {
        // 中央库存特有的逻辑（可选供前端扩展，目前前端是通过 loadData 里的特定逻辑处理供货统计）
        }

        return $result;

    }
    catch (PDOException $e) {
        throw new Exception("查询库存数据失败：" . $e->getMessage());
    }
}

// 修改现有的 getLowStockAlerts() 函数
function getLowStockAlerts($system = 'central')
{
    global $pdo;

    try {
        $tableMap = [
            'central' => 'stockinout_data',
            'j1' => 'j1stockedit_data',
            'j2' => 'j2stockedit_data',
            'j3' => 'j3stockedit_data'
        ];

        $tableName = $tableMap[$system] ?? 'stockinout_data';

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
                               SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)), 
                               CASE WHEN LOWER(TRIM(specification)) = 'kilo' THEN 3 ELSE 2 END) as formatted_stock
                    FROM $tableName 
                    WHERE product_name IS NOT NULL AND product_name != ''
                    AND deleted_at IS NULL
                    GROUP BY product_name, code_number, specification
                ) s
                INNER JOIN stock_minimum_settings m ON TRIM(s.product_name) = TRIM(m.product_name)
                WHERE m.minimum_quantity > 0 AND s.current_stock <= m.minimum_quantity
                ORDER BY s.product_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $lowStockData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $lowStockData;

    }
    catch (PDOException $e) {
        throw new Exception("查询低库存数据失败：" . $e->getMessage());
    }
}

// 获取供货总额（当前月份）
function getSupplyTotal($system = 'central')
{
    global $pdo;

    try {
        $tableMap = [
            'central' => 'stockinout_data',
            'j1' => 'j1stockinout_data',
            'j2' => 'j2stockinout_data',
            'j3' => 'j3stockinout_data'
        ];

        $tableName = $tableMap[$system] ?? 'stockinout_data';

        // 获取当前月份的第一天和最后一天
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');

        $sql = "SELECT SUM(in_quantity * price) as total_supply_value 
                FROM $tableName 
                WHERE in_quantity > 0 
                AND deleted_at IS NULL
                AND date >= ? AND date <= ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$firstDayOfMonth, $lastDayOfMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalSupplyValue = floatval($result['total_supply_value'] ?? 0);

        return [
            'total_supply_value' => $totalSupplyValue,
            'formatted_total_value' => number_format($totalSupplyValue, 2),
            'month' => date('Y-m')
        ];
    }
    catch (PDOException $e) {
        throw new Exception("查询供货总额失败：" . $e->getMessage());
    }
}

// 主要路由处理
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'summary';

    switch ($action) {
        case 'summary':
            try {
                $system = $_GET['system'] ?? 'central';
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $result = getStockSummary($system, $startDate, $endDate);
                sendResponse(true, ucfirst($system) . "库存汇总数据获取成功", $result);
            }
            catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        case 'low_stock_alerts':
            try {
                $system = $_GET['system'] ?? 'central';
                $result = getLowStockAlerts($system);
                sendResponse(true, ucfirst($system) . "低库存预警数据获取成功", ['alerts' => $result, 'count' => count($result)]);
            }
            catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        case 'supply_total':
            try {
                $system = $_GET['system'] ?? 'central';
                $result = getSupplyTotal($system);
                sendResponse(true, ucfirst($system) . "供货总值获取成功", $result);
            }
            catch (Exception $e) {
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
                fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

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

            }
            catch (Exception $e) {
                sendResponse(false, "导出失败：" . $e->getMessage());
            }
            break;

        default:
            sendResponse(false, "无效的操作");
    }
}
else {
    sendResponse(false, "不支持的请求方法");
}
?>
