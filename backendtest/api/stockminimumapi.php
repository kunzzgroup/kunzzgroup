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

// 直接从 stock_minimum_settings 表获取所有最低库存设置
function getProductsWithSettings() {
    global $pdo;
    
    try {
        // 直接从 stock_minimum_settings 表获取所有数据，确保与数据库完全一致
        $sql = "SELECT 
                    TRIM(product_name) as product_name,
                    minimum_quantity
                FROM stock_minimum_settings
                WHERE product_name IS NOT NULL AND product_name != ''
                ORDER BY product_name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $settingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 获取所有货品名称和编号（用于显示 product_code）
        $productsSQL = "SELECT DISTINCT 
                            TRIM(product_name) as product_name, 
                            TRIM(product_code) as product_code 
                        FROM stock_data 
                        WHERE product_name IS NOT NULL AND product_name != ''
                        ORDER BY product_name ASC";
        
        $productsStmt = $pdo->prepare($productsSQL);
        $productsStmt->execute();
        $productsData = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 创建产品编号映射
        $productCodeMap = [];
        foreach ($productsData as $product) {
            $productName = trim($product['product_name']);
            if ($productName) {
                $productCodeMap[$productName] = trim($product['product_code'] ?? '');
            }
        }
        
        // 组合数据 - 确保数据与数据库完全一致
        // 如果同一个产品名称有多个记录，只保留最新的（按ID或更新时间）
        $resultMap = [];
        foreach ($settingsData as $setting) {
            $productName = trim($setting['product_name']);
            if ($productName) {
                // 如果已存在，保留较大的 minimum_quantity 值（确保显示正确的设置）
                if (!isset($resultMap[$productName]) || floatval($setting['minimum_quantity']) > $resultMap[$productName]['minimum_quantity']) {
                    $resultMap[$productName] = [
                        'product_name' => $productName,
                        'product_code' => $productCodeMap[$productName] ?? '',
                        'minimum_quantity' => floatval($setting['minimum_quantity'])
                    ];
                }
            }
        }
        
        // 转换为数组
        $result = array_values($resultMap);
        
        return $result;
        
    } catch (PDOException $e) {
        throw new Exception("查询货品数据失败：" . $e->getMessage());
    }
}

// 修改保存函数，移除 is_active 参数
function saveSingleSetting($productName, $minimumQuantity) {
    global $pdo;
    
    try {
        // 去除产品名称首尾空格，确保数据一致性
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
        $stmt->execute([$trimmedName, $minimumQuantity]);
        
        return true;
        
    } catch (PDOException $e) {
        throw new Exception("保存设置失败：" . $e->getMessage());
    }
}

function saveBatchSettings($products) {
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
            // 去除产品名称首尾空格，确保数据一致性
            $trimmedName = trim($product['product_name']);
            if (empty($trimmedName)) {
                continue; // 跳过无效的产品名称
            }
            
            $stmt->execute([
                $trimmedName,
                floatval($product['minimum_quantity'])
            ]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (PDOException $e) {
        $pdo->rollback();
        throw new Exception("批量保存失败：" . $e->getMessage());
    }
}

// 主要路由处理
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            try {
                $result = getProductsWithSettings();
                sendResponse(true, "货品设置数据获取成功", $result);
            } catch (Exception $e) {
                sendResponse(false, $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, "无效的操作");
    }
    
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendResponse(false, "无效的JSON数据");
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'save_single':
            try {
                $productName = $input['product_name'] ?? '';
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
                
                // 验证数据格式
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