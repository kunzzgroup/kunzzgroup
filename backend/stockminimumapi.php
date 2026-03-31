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
require_once __DIR__ . '/xss_protect.php';
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
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data"    => $data
    ]);
    exit;
}

/**
 * 根据系统获取对应的库存表名
 * 只列出实际存在库存记录的货品，并关联 stock_minimum_settings
 */
function getProductsForSystem($system)
{
    global $pdo;

    $tableMap = [
        'central' => 'stockinout_data',
        'j1'      => 'j1stockedit_data',
        'j2'      => 'j2stockedit_data',
        'j3'      => 'j3stockedit_data',
    ];

    if (!isset($tableMap[$system])) {
        throw new Exception("无效的系统：" . $system);
    }

    $tableName = $tableMap[$system];

    try {
        /*
         * 完全对照 stocklistapi.php 的逻辑：
         *   1. 按 (product_name, specification, price, code_number) 分组
         *   2. 计算 current_stock = 入库 - 出库
         *   3. HAVING current_stock != 0  ← 只保留有当前库存的货品
         *   4. 在外层按 product_name 去重（唯一货品名） 
         *   5. LEFT JOIN stock_minimum_settings 取最低库存设置
         */
        $sql = "
            SELECT
                @row_num := @row_num + 1 AS no,
                sub.product_name,
                sub.product_code,
                COALESCE(m.minimum_quantity, 0) AS minimum_quantity
            FROM (
                SELECT
                    TRIM(REPLACE(product_name, '&amp;', '&')) AS product_name,
                    -- 取该货品下第一个非空的货品编号
                    MIN(CASE WHEN TRIM(code_number) != '' THEN TRIM(code_number) END) AS product_code
                FROM (
                    -- 第一层：按 stocklistapi 同样的分组逻辑计算当前库存
                    SELECT
                        TRIM(REPLACE(product_name, '&amp;', '&')) AS product_name,
                        TRIM(code_number) AS code_number,
                        (SUM(CASE WHEN in_quantity  > 0 THEN in_quantity  ELSE 0 END) -
                         SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END)) AS current_stock
                    FROM `$tableName`
                    WHERE product_name IS NOT NULL
                      AND TRIM(product_name) != ''
                      AND deleted_at IS NULL
                    GROUP BY
                        TRIM(REPLACE(product_name, '&amp;', '&')),
                        specification,
                        price,
                        TRIM(code_number)
                    HAVING current_stock != 0
                ) AS inner_stock
                GROUP BY TRIM(REPLACE(product_name, '&amp;', '&'))
            ) AS sub
            JOIN (SELECT @row_num := 0) AS init
            LEFT JOIN stock_minimum_settings m
                ON TRIM(m.product_name) = sub.product_name
            ORDER BY sub.product_name ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'no'               => intval($row['no']),
                'product_name'     => $row['product_name'],
                'product_code'     => $row['product_code'] ?: '-',
                'minimum_quantity' => floatval($row['minimum_quantity']),
            ];
        }

        return $result;

    } catch (PDOException $e) {
        throw new Exception("查询货品数据失败：" . $e->getMessage());
    }
}

/**
 * 保存单条最低库存设置
 */
function saveSingleSetting($productName, $minimumQuantity)
{
    global $pdo;

    try {
        $trimmedName = trim($productName);
        if (empty($trimmedName)) {
            throw new Exception("产品名称不能为空");
        }

        $sql = "INSERT INTO stock_minimum_settings (product_name, minimum_quantity)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                minimum_quantity = VALUES(minimum_quantity),
                updated_at = CURRENT_TIMESTAMP";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$trimmedName, floatval($minimumQuantity)]);

        return true;

    } catch (PDOException $e) {
        throw new Exception("保存设置失败：" . $e->getMessage());
    }
}

/**
 * 批量保存最低库存设置
 */
function saveBatchSettings($products)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO stock_minimum_settings (product_name, minimum_quantity)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                minimum_quantity = VALUES(minimum_quantity),
                updated_at = CURRENT_TIMESTAMP";

        $stmt = $pdo->prepare($sql);

        foreach ($products as $product) {
            $trimmedName = trim($product['product_name']);
            if (empty($trimmedName)) continue;
            $stmt->execute([$trimmedName, floatval($product['minimum_quantity'])]);
        }

        $pdo->commit();
        return true;

    } catch (PDOException $e) {
        $pdo->rollback();
        throw new Exception("批量保存失败：" . $e->getMessage());
    }
}

// ─── 路由 ───────────────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            try {
                $system = $_GET['system'] ?? 'central';
                $result = getProductsForSystem($system);
                sendResponse(true, "货品设置数据获取成功", $result);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        default:
            sendResponse(false, "无效的操作");
    }

} elseif ($method === 'POST') {
    $input = get_safe_json_input();

    if (!$input) {
        sendResponse(false, "无效的JSON数据");
    }

    $action = $input['action'] ?? '';

    switch ($action) {
        case 'save_single':
            try {
                $productName     = $input['product_name'] ?? '';
                $minimumQuantity = floatval($input['minimum_quantity'] ?? 0);

                if (empty($productName)) {
                    sendResponse(false, "货品名称不能为空");
                }

                saveSingleSetting($productName, $minimumQuantity);
                sendResponse(true, "设置保存成功");

            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        case 'save_batch':
            try {
                $products = $input['products'] ?? [];

                if (empty($products)) {
                    sendResponse(false, "没有要保存的数据");
                }

                foreach ($products as $product) {
                    if (empty($product['product_name'])) {
                        sendResponse(false, "货品名称不能为空");
                    }
                }

                saveBatchSettings($products);
                sendResponse(true, "批量保存成功");

            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;

        default:
            sendResponse(false, "无效的操作");
    }

} else {
    sendResponse(false, "不支持的请求方法");
}
?>
