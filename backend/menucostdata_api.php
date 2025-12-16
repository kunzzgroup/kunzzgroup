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

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// 数据库配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建表（如果不存在）
    $createTableSql = "CREATE TABLE IF NOT EXISTS menu_cost_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(255) NOT NULL COMMENT '原材料名称',
        price DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT '价格',
        unit DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT '单位',
        specification VARCHAR(255) DEFAULT '' COMMENT '规格',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_product_name (product_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($createTableSql);
    
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $e->getMessage()]);
    exit;
}

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

function sendResponse($success, $message = "", $data = null) {
    ob_end_clean();
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 路由处理
switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        sendResponse(false, "不支持的请求方法");
}

// 处理 GET 请求 - 获取数据
function handleGet() {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM menu_cost_data ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendResponse(true, "数据获取成功", $records);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取数据失败：" . $e->getMessage());
    }
}

// 处理 POST 请求 - 添加新记录
function handlePost() {
    global $pdo, $data;
    
    if (!$data) {
        sendResponse(false, "无效的数据格式");
    }
    
    // 验证必填字段
    if (empty($data['product_name'])) {
        sendResponse(false, "原材料名称不能为空");
    }
    
    if (!isset($data['price']) || $data['price'] === '') {
        sendResponse(false, "价格不能为空");
    }
    
    if (!isset($data['unit']) || $data['unit'] === '' || floatval($data['unit']) < 0) {
        sendResponse(false, "单位必须是有效的数字");
    }
    
    try {
        // 检查并添加规格字段（如果不存在）
        $checkColumn = $pdo->query("SHOW COLUMNS FROM menu_cost_data LIKE 'specification'");
        if ($checkColumn->rowCount() == 0) {
            $pdo->exec("ALTER TABLE menu_cost_data ADD COLUMN specification VARCHAR(255) DEFAULT '' COMMENT '规格' AFTER unit");
        }
        
        $sql = "INSERT INTO menu_cost_data (product_name, price, unit, specification) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($data['product_name']),
            floatval($data['price']),
            floatval($data['unit']),
            trim($data['specification'] ?? '')
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // 获取新插入的记录
        $stmt = $pdo->prepare("SELECT * FROM menu_cost_data WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, "记录添加成功", $newRecord);
        
    } catch (PDOException $e) {
        sendResponse(false, "添加记录失败：" . $e->getMessage());
    }
}

// 处理 PUT 请求 - 更新记录
function handlePut() {
    global $pdo, $data;
    
    if (!$data || !isset($data['id'])) {
        sendResponse(false, "缺少记录ID");
    }
    
    // 验证必填字段
    if (empty($data['product_name'])) {
        sendResponse(false, "原材料名称不能为空");
    }
    
    if (!isset($data['price']) || $data['price'] === '') {
        sendResponse(false, "价格不能为空");
    }
    
    if (!isset($data['unit']) || $data['unit'] === '' || floatval($data['unit']) < 0) {
        sendResponse(false, "单位必须是有效的数字");
    }
    
    try {
        // 检查并添加规格字段（如果不存在）
        $checkColumn = $pdo->query("SHOW COLUMNS FROM menu_cost_data LIKE 'specification'");
        if ($checkColumn->rowCount() == 0) {
            $pdo->exec("ALTER TABLE menu_cost_data ADD COLUMN specification VARCHAR(255) DEFAULT '' COMMENT '规格' AFTER unit");
        }
        
        $sql = "UPDATE menu_cost_data 
                SET product_name = ?, price = ?, unit = ?, specification = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            trim($data['product_name']),
            floatval($data['price']),
            floatval($data['unit']),
            trim($data['specification'] ?? ''),
            $data['id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            // 获取更新后的记录
            $stmt = $pdo->prepare("SELECT * FROM menu_cost_data WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, "记录更新成功", $updatedRecord);
        } else {
            sendResponse(false, "记录不存在或无变化");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "更新记录失败：" . $e->getMessage());
    }
}

// 处理 DELETE 请求 - 删除记录
function handleDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM menu_cost_data WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            sendResponse(true, "记录删除成功");
        } else {
            sendResponse(false, "记录不存在");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "删除记录失败：" . $e->getMessage());
    }
}
?>

