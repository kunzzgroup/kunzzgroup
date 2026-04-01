<?php
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

// 数据库配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
        "data"    => $data
    ]);
    exit;
}

// 智能格式化数量函数
function formatQuantity($number)
{
    $num = floatval($number);

    if (floor($num) == $num) {
        return number_format($num, 0);
    }

    $decimalPart = $num - floor($num);

    if (round($decimalPart, 1) == round($decimalPart, 3)) {
        return number_format($num, 1);
    }
    elseif (round($decimalPart, 2) == round($decimalPart, 3)) {
        return number_format($num, 2);
    }
    else {
        return number_format($num, 3);
    }
}

// 获取货品备注数据（支持 system 参数，与 stocklistapi.php 保持一致）
function getMultiPriceAnalysis($system = 'central')
{
    global $pdo;

    // 根据 system 切换数据库表
    $tableMap = [
        'central' => 'stockinout_data',
        'j1'      => 'j1stockedit_data',
        'j2'      => 'j2stockedit_data',
        'j3'      => 'j3stockedit_data'
    ];
    $tableName = $tableMap[$system] ?? 'stockinout_data';

    try {
        $sql = "SELECT 
                    product_name,
                    specification,
                    price,
                    code_number,
                    remark_number,
                    in_quantity,
                    out_quantity,
                    date,
                    time,
                    receiver
                FROM $tableName 
                WHERE product_remark_checked = 1 
                AND product_name IS NOT NULL 
                AND product_name != ''
                AND remark_number IS NOT NULL 
                AND remark_number != ''
                AND deleted_at IS NULL
                ORDER BY product_name ASC, remark_number ASC, date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $remarkData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 按产品名称分组
        $productGroups = [];

        foreach ($remarkData as $row) {
            $productName  = $row['product_name'];
            $remarkNumber = $row['remark_number'];
            $groupKey     = $productName;

            if (!isset($productGroups[$groupKey])) {
                $productGroups[$groupKey] = [
                    'product_name' => $productName,
                    'variants'     => []
                ];
            }

            $remarkKey = $remarkNumber;

            if (!isset($productGroups[$groupKey]['variants'][$remarkKey])) {
                $productGroups[$groupKey]['variants'][$remarkKey] = [
                    'code_number'   => $row['code_number'] ?? '',
                    'specification' => $row['specification'] ?? '',
                    'remark_number' => $remarkNumber,
                    'in_quantity'   => 0,
                    'out_quantity'  => 0,
                    'price'         => floatval($row['price'])
                ];
            }

            $inQty  = floatval($row['in_quantity']);
            $outQty = floatval($row['out_quantity']);

            if ($inQty > 0) {
                $productGroups[$groupKey]['variants'][$remarkKey]['in_quantity'] += $inQty;
            }
            if ($outQty > 0) {
                $productGroups[$groupKey]['variants'][$remarkKey]['out_quantity'] += $outQty;
            }
        }

        // 转换为最终格式
        $remarkProducts = [];
        foreach ($productGroups as $group) {
            $variants = [];
            foreach ($group['variants'] as $variant) {
                $currentStock = $variant['in_quantity'] - $variant['out_quantity'];

                if (round($currentStock, 3) > 0) {
                    $variants[] = [
                        'code_number'        => $variant['code_number'],
                        'specification'      => $variant['specification'],
                        'in_quantity'        => $variant['in_quantity'],
                        'out_quantity'       => $variant['out_quantity'],
                        'current_stock'      => $currentStock,
                        'formatted_quantity' => formatQuantity($currentStock),
                        'price'              => $variant['price'],
                        'formatted_price'    => number_format($variant['price'], 2),
                        'remark_number'      => $variant['remark_number']
                    ];
                }
            }

            if (!empty($variants)) {
                $totalQuantity  = array_sum(array_column($variants, 'current_stock'));
                $remarkProducts[] = [
                    'product_name'   => $group['product_name'],
                    'variants'       => $variants,
                    'total_quantity' => formatQuantity($totalQuantity)
                ];
            }
        }

        return ['products' => $remarkProducts];

    }
    catch (PDOException $e) {
        throw new Exception("查询货品备注数据失败：" . $e->getMessage());
    }
}

// 获取产品详细信息
function getProductDetails($productName, $system = 'central')
{
    global $pdo;

    $tableMap = [
        'central' => 'stockinout_data',
        'j1'      => 'j1stockedit_data',
        'j2'      => 'j2stockedit_data',
        'j3'      => 'j3stockedit_data'
    ];
    $tableName = $tableMap[$system] ?? 'stockinout_data';

    try {
        $sql = "SELECT 
                    product_name,
                    specification,
                    price,
                    code_number,
                    in_quantity,
                    out_quantity,
                    SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as total_out
                FROM $tableName 
                WHERE product_name = :product_name
                AND deleted_at IS NULL
                GROUP BY product_name, specification, price, code_number
                ORDER BY price DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':product_name', $productName, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    catch (PDOException $e) {
        throw new Exception("查询产品详细信息失败：" . $e->getMessage());
    }
}

// 导出CSV数据
function exportMultiPriceData($system = 'central')
{
    try {
        $result = getMultiPriceAnalysis($system);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="multi_price_analysis_' . date('Y-m-d') . '.csv"');

        ob_end_clean();

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Product Name', 'Remark Number', 'Code Number', 'Stock Quantity', 'Unit Price (RM)']);

        foreach ($result['products'] as $product) {
            foreach ($product['variants'] as $index => $variant) {
                fputcsv($output, [
                    $product['product_name'],
                    $variant['remark_number'],
                    $variant['code_number'],
                    $variant['formatted_quantity'],
                    $variant['formatted_price']
                ]);
            }
        }

        fclose($output);
        exit;

    }
    catch (Exception $e) {
        ob_end_clean();
        sendResponse(false, "导出失败：" . $e->getMessage());
    }
}

// 主要路由处理
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'analysis';
    $system = $_GET['system'] ?? 'central';

    switch ($action) {
        case 'analysis':
            try {
                $result = getMultiPriceAnalysis($system);
                sendResponse(true, ucfirst($system) . "货品备注数据获取成功", $result);
            }
            catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        case 'details':
            $productName = $_GET['product'] ?? '';
            if (empty($productName)) {
                sendResponse(false, "产品名称不能为空");
            }

            try {
                $result = getProductDetails($productName, $system);
                sendResponse(true, "产品详细信息获取成功", $result);
            }
            catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        case 'export':
            exportMultiPriceData($system);
            break;

        default:
            sendResponse(false, "无效的操作");
    }
}
else {
    sendResponse(false, "不支持的请求方法");
}
?>
