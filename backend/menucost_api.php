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
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建菜单项表（如果不存在）
    $createMenuItemsTable = "CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_code VARCHAR(50) NOT NULL COMMENT '菜单编号',
        menu_name VARCHAR(255) NOT NULL COMMENT '菜单名称',
        menu_name_cn VARCHAR(255) DEFAULT '' COMMENT '菜单中文名称',
        portion_size VARCHAR(50) DEFAULT '' COMMENT '份量',
        selling_price DECIMAL(10, 2) DEFAULT 0 COMMENT '售价',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_menu_code (menu_code),
        INDEX idx_menu_name (menu_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($createMenuItemsTable);
    
    // 创建菜单配料表（如果不存在）
    $createIngredientsTable = "CREATE TABLE IF NOT EXISTS menu_item_ingredients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_item_id INT NOT NULL COMMENT '菜单项ID',
        ingredient_id INT NOT NULL COMMENT '原材料ID（关联menu_cost_data）',
        ingredient_name VARCHAR(255) NOT NULL COMMENT '原材料名称',
        rm_price DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT '单价',
        unit DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT '单位',
        measurement DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT '用量',
        cost DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT '成本',
        sort_order INT DEFAULT 0 COMMENT '排序',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
        INDEX idx_menu_item_id (menu_item_id),
        INDEX idx_ingredient_id (ingredient_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($createIngredientsTable);
    
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

// 处理 GET 请求
function handleGet() {
    global $pdo;
    
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            // 获取所有菜单项及其配料
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        mi.*,
                        (SELECT SUM(cost) FROM menu_item_ingredients WHERE menu_item_id = mi.id) as total_cost
                    FROM menu_items mi
                    ORDER BY mi.menu_code ASC
                ");
                $stmt->execute();
                $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 获取每个菜单项的配料
                foreach ($menuItems as &$item) {
                    $stmt = $pdo->prepare("
                        SELECT 
                            mii.*,
                            mcd.product_name,
                            mcd.price as ingredient_price,
                            mcd.unit as ingredient_unit
                        FROM menu_item_ingredients mii
                        LEFT JOIN menu_cost_data mcd ON mii.ingredient_id = mcd.id
                        WHERE mii.menu_item_id = ?
                        ORDER BY mii.sort_order ASC, mii.id ASC
                    ");
                    $stmt->execute([$item['id']]);
                    $item['ingredients'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                
                sendResponse(true, "数据获取成功", $menuItems);
            } catch (PDOException $e) {
                sendResponse(false, "获取数据失败：" . $e->getMessage());
            }
            break;
            
        case 'ingredients':
            // 获取所有原材料列表（用于下拉选择）
            try {
                $stmt = $pdo->prepare("SELECT id, product_name, price, unit FROM menu_cost_data ORDER BY product_name ASC");
                $stmt->execute();
                $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                sendResponse(true, "原材料列表获取成功", $ingredients);
            } catch (PDOException $e) {
                sendResponse(false, "获取原材料列表失败：" . $e->getMessage());
            }
            break;
            
        case 'single':
            // 获取单个菜单项
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, "缺少菜单项ID");
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
                $stmt->execute([$id]);
                $menuItem = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($menuItem) {
                    // 获取配料
                    $stmt = $pdo->prepare("
                        SELECT 
                            mii.*,
                            mcd.product_name,
                            mcd.price as ingredient_price,
                            mcd.unit as ingredient_unit
                        FROM menu_item_ingredients mii
                        LEFT JOIN menu_cost_data mcd ON mii.ingredient_id = mcd.id
                        WHERE mii.menu_item_id = ?
                        ORDER BY mii.sort_order ASC, mii.id ASC
                    ");
                    $stmt->execute([$id]);
                    $menuItem['ingredients'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    sendResponse(true, "菜单项获取成功", $menuItem);
                } else {
                    sendResponse(false, "菜单项不存在");
                }
            } catch (PDOException $e) {
                sendResponse(false, "获取菜单项失败：" . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, "无效的操作");
    }
}

// 处理 POST 请求
function handlePost() {
    global $pdo, $data;
    
    if (!$data) {
        sendResponse(false, "无效的数据格式");
    }
    
    $action = $_GET['action'] ?? '';
    
    if ($action === 'ingredient') {
        // 添加配料
        if (!isset($data['menu_item_id']) || !isset($data['ingredient_id'])) {
            sendResponse(false, "缺少必填字段");
        }
        
        try {
            // 获取原材料信息
            $stmt = $pdo->prepare("SELECT product_name, price, unit FROM menu_cost_data WHERE id = ?");
            $stmt->execute([$data['ingredient_id']]);
            $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ingredient) {
                sendResponse(false, "原材料不存在");
            }
            
            $rmPrice = floatval($data['rm_price'] ?? $ingredient['price']);
            $unit = floatval($data['unit'] ?? $ingredient['unit']);
            $measurement = floatval($data['measurement'] ?? 0);
            
            // 计算成本
            $cost = 0;
            if ($unit > 0 && $measurement > 0) {
                $cost = ($rmPrice / $unit) * $measurement;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO menu_item_ingredients 
                (menu_item_id, ingredient_id, ingredient_name, rm_price, unit, measurement, cost, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $sortOrder = intval($data['sort_order'] ?? 0);
            $stmt->execute([
                $data['menu_item_id'],
                $data['ingredient_id'],
                $ingredient['product_name'],
                $rmPrice,
                $unit,
                $measurement,
                $cost,
                $sortOrder
            ]);
            
            $newId = $pdo->lastInsertId();
            
            // 获取新插入的配料
            $stmt = $pdo->prepare("
                SELECT 
                    mii.*,
                    mcd.product_name,
                    mcd.price as ingredient_price,
                    mcd.unit as ingredient_unit
                FROM menu_item_ingredients mii
                LEFT JOIN menu_cost_data mcd ON mii.ingredient_id = mcd.id
                WHERE mii.id = ?
            ");
            $stmt->execute([$newId]);
            $newIngredient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, "配料添加成功", $newIngredient);
        } catch (PDOException $e) {
            sendResponse(false, "添加配料失败：" . $e->getMessage());
        }
    } else {
        // 添加菜单项
        if (empty($data['menu_code']) || empty($data['menu_name'])) {
            sendResponse(false, "菜单编号和名称不能为空");
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO menu_items (menu_code, menu_name)
                VALUES (?, ?)
            ");
            
            $stmt->execute([
                trim($data['menu_code']),
                trim($data['menu_name'])
            ]);
            
            $newId = $pdo->lastInsertId();
            
            // 获取新插入的菜单项
            $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
            $stmt->execute([$newId]);
            $newMenuItem = $stmt->fetch(PDO::FETCH_ASSOC);
            $newMenuItem['ingredients'] = [];
            
            sendResponse(true, "菜单项添加成功", $newMenuItem);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                sendResponse(false, "菜单编号已存在");
            } else {
                sendResponse(false, "添加菜单项失败：" . $e->getMessage());
            }
        }
    }
}

// 处理 PUT 请求
function handlePut() {
    global $pdo, $data;
    
    if (!$data) {
        sendResponse(false, "无效的数据格式");
    }
    
    $action = $_GET['action'] ?? '';
    
    if ($action === 'ingredient') {
        // 更新配料
        if (!isset($data['id'])) {
            sendResponse(false, "缺少配料ID");
        }
        
        try {
            $rmPrice = floatval($data['rm_price'] ?? 0);
            $unit = floatval($data['unit'] ?? 0);
            $measurement = floatval($data['measurement'] ?? 0);
            
            // 计算成本
            $cost = 0;
            if ($unit > 0 && $measurement > 0) {
                $cost = ($rmPrice / $unit) * $measurement;
            }
            
            $stmt = $pdo->prepare("
                UPDATE menu_item_ingredients 
                SET rm_price = ?, unit = ?, measurement = ?, cost = ?, sort_order = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $rmPrice,
                $unit,
                $measurement,
                $cost,
                intval($data['sort_order'] ?? 0),
                $data['id']
            ]);
            
            // 获取更新后的配料
            $stmt = $pdo->prepare("
                SELECT 
                    mii.*,
                    mcd.product_name,
                    mcd.price as ingredient_price,
                    mcd.unit as ingredient_unit
                FROM menu_item_ingredients mii
                LEFT JOIN menu_cost_data mcd ON mii.ingredient_id = mcd.id
                WHERE mii.id = ?
            ");
            $stmt->execute([$data['id']]);
            $updatedIngredient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, "配料更新成功", $updatedIngredient);
        } catch (PDOException $e) {
            sendResponse(false, "更新配料失败：" . $e->getMessage());
        }
    } else {
        // 更新菜单项
        if (!isset($data['id'])) {
            sendResponse(false, "缺少菜单项ID");
        }
        
        try {
            $stmt = $pdo->prepare("
                UPDATE menu_items 
                SET menu_code = ?, menu_name = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                trim($data['menu_code']),
                trim($data['menu_name']),
                $data['id']
            ]);
            
            // 获取更新后的菜单项
            $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedMenuItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, "菜单项更新成功", $updatedMenuItem);
        } catch (PDOException $e) {
            sendResponse(false, "更新菜单项失败：" . $e->getMessage());
        }
    }
}

// 处理 DELETE 请求
function handleDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    $action = $_GET['action'] ?? '';
    
    if (!$id) {
        sendResponse(false, "缺少ID");
    }
    
    try {
        if ($action === 'ingredient') {
            // 删除配料
            $stmt = $pdo->prepare("DELETE FROM menu_item_ingredients WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                sendResponse(true, "配料删除成功");
            } else {
                sendResponse(false, "配料不存在");
            }
        } else {
            // 删除菜单项（会自动删除关联的配料）
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                sendResponse(true, "菜单项删除成功");
            } else {
                sendResponse(false, "菜单项不存在");
            }
        }
    } catch (PDOException $e) {
        sendResponse(false, "删除失败：" . $e->getMessage());
    }
}
?>

