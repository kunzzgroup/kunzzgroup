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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host   = 'localhost';
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

function sendResponse($success, $message = "", $data = null)
{
    ob_end_clean();
    echo json_encode(["success" => $success, "message" => $message, "data" => $data]);
    exit;
}

// 智能格式化数量
function formatQuantity($number)
{
    $num = floatval($number);
    if (floor($num) == $num) return number_format($num, 0);
    $d = $num - floor($num);
    if (round($d, 1) == round($d, 3)) return number_format($num, 1);
    if (round($d, 2) == round($d, 3)) return number_format($num, 2);
    return number_format($num, 3);
}

// 自然排序比较函数（备注编号 ASC）
function naturalCompareRemark($a, $b)
{
    $rA = $a['remark_number'] ?? '';
    $rB = $b['remark_number'] ?? '';
    $partsA = preg_split('/(\d+)/', $rA, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $partsB = preg_split('/(\d+)/', $rB, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $len = max(count($partsA), count($partsB));
    for ($i = 0; $i < $len; $i++) {
        $pA = $partsA[$i] ?? '';
        $pB = $partsB[$i] ?? '';
        if (ctype_digit($pA) && ctype_digit($pB)) {
            $diff = (int)$pA - (int)$pB;
            if ($diff !== 0) return $diff;
        } else {
            $diff = strcmp($pA, $pB);
            if ($diff !== 0) return $diff;
        }
    }
    return 0;
}

/**
 * 获取货品备注数据
 *
 * 逻辑说明：
 * 1. 查询一：抓取 product_remark_checked=1 且有 remark_number 的进出货记录，按备注编号分组计算净库存
 * 2. 查询二：获取每个货品的真实净库存（与 stocklistall 相同逻辑，所有进出货汇总）
 * 3. 若 remark 合计 > 真实净库存，用 LIFO 从最大备注号往前扣减差额
 *    （只扣超出部分，不影响数据已对齐的货品如 Salmon 等日常消耗品）
 */
function getMultiPriceAnalysis($system = 'central')
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
        // ── 查询一：抓取有备注编号的进出货记录 ──
        $sql = "SELECT
                    product_name,
                    specification,
                    price,
                    code_number,
                    remark_number,
                    in_quantity,
                    out_quantity
                FROM $tableName
                WHERE product_remark_checked = 1
                AND product_name IS NOT NULL
                AND product_name != ''
                AND remark_number IS NOT NULL
                AND remark_number != ''
                AND deleted_at IS NULL
                ORDER BY product_name ASC, remark_number ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $remarkData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 按货品名称 + 备注编号分组，累计进出量
        $productGroups = [];
        foreach ($remarkData as $row) {
            $productName  = $row['product_name'];
            $remarkNumber = $row['remark_number'];

            if (!isset($productGroups[$productName])) {
                $productGroups[$productName] = [
                    'product_name' => $productName,
                    'variants'     => []
                ];
            }

            if (!isset($productGroups[$productName]['variants'][$remarkNumber])) {
                $productGroups[$productName]['variants'][$remarkNumber] = [
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
            if ($inQty  > 0) $productGroups[$productName]['variants'][$remarkNumber]['in_quantity']  += $inQty;
            if ($outQty > 0) $productGroups[$productName]['variants'][$remarkNumber]['out_quantity'] += $outQty;
        }

        if (empty($productGroups)) {
            return ['products' => []];
        }

        // ── 查询二：获取每个货品的真实净库存（与 stocklistall 完全相同的逻辑）──
        // 只比对 remark 合计 vs 真实净库存的差值，仅扣减超出部分
        $productNames = array_keys($productGroups);
        $placeholders = implode(',', array_fill(0, count($productNames), '?'));
        $realStockSql = "SELECT
                             product_name,
                             (SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) -
                              SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) AS real_stock
                         FROM $tableName
                         WHERE product_name IN ($placeholders)
                         AND deleted_at IS NULL
                         GROUP BY product_name";
        $stmtR = $pdo->prepare($realStockSql);
        $stmtR->execute($productNames);
        $realStockMap = [];
        foreach ($stmtR->fetchAll(PDO::FETCH_ASSOC) as $rRow) {
            $realStockMap[$rRow['product_name']] = floatval($rRow['real_stock']);
        }

        // ── 转换为最终格式 ──
        $remarkProducts = [];
        foreach ($productGroups as $group) {

            // 构建有库存的变种列表
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

            if (empty($variants)) continue;

            // 自然排序 ASC（SH-304 → SH-329）
            usort($variants, 'naturalCompareRemark');

            // ── LIFO 扣减：仅扣 remark 合计超出真实库存的部分 ──
            // 若 Salmon 的 remark 总量 = 真实库存，excess = 0，不做任何扣减
            // 若 Salmon Head 的 remark 总量(26) > 真实库存(25)，excess = 1，移除最后 1 个备注
            $remarkTotal = array_sum(array_column($variants, 'current_stock'));
            $realStock   = $realStockMap[$group['product_name']] ?? $remarkTotal;
            $excess      = $remarkTotal - $realStock;

            if ($excess > 0.001) {
                $idx = count($variants) - 1;
                while ($excess > 0.001 && $idx >= 0) {
                    $stock = $variants[$idx]['current_stock'];
                    if ($excess >= $stock - 0.001) {
                        // 整个 variant 已消耗，移除
                        array_splice($variants, $idx, 1);
                        $excess -= $stock;
                    } else {
                        // 部分消耗
                        $variants[$idx]['current_stock']      -= $excess;
                        $variants[$idx]['formatted_quantity']  = formatQuantity($variants[$idx]['current_stock']);
                        $excess = 0;
                    }
                    $idx--;
                }
            }

            if (!empty($variants)) {
                $totalQuantity = array_sum(array_column($variants, 'current_stock'));
                $remarkProducts[] = [
                    'product_name'   => $group['product_name'],
                    'variants'       => array_values($variants),
                    'total_quantity' => formatQuantity($totalQuantity)
                ];
            }
        }

        return ['products' => $remarkProducts];

    } catch (PDOException $e) {
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

    } catch (PDOException $e) {
        throw new Exception("查询产品详细信息失败：" . $e->getMessage());
    }
}

// 导出CSV
function exportMultiPriceData($system = 'central')
{
    try {
        $result = getMultiPriceAnalysis($system);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="remark_analysis_' . date('Y-m-d') . '.csv"');
        ob_end_clean();

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Product Name', 'Remark Number', 'Code Number', 'Stock Quantity', 'Unit Price (RM)']);

        foreach ($result['products'] as $product) {
            foreach ($product['variants'] as $variant) {
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

    } catch (Exception $e) {
        ob_end_clean();
        sendResponse(false, "导出失败：" . $e->getMessage());
    }
}

// ── 路由 ──
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'analysis';
    $system = $_GET['system'] ?? 'central';

    switch ($action) {
        case 'analysis':
            try {
                $result = getMultiPriceAnalysis($system);
                sendResponse(true, ucfirst($system) . " 货品备注数据获取成功", $result);
            } catch (Exception $e) {
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
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        case 'export':
            exportMultiPriceData($system);
            break;

        default:
            sendResponse(false, "无效的操作");
    }
} else {
    sendResponse(false, "不支持的请求方法");
}
?>
