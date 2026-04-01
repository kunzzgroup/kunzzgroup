<?php
require_once __DIR__ . '/permission_guard.php';
require_once __DIR__ . '/heic_convert.php';
requirePermissionApi('resource', 'dishware');

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

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$data = get_safe_json_input();

function sendResponse($success, $message = "", $data = null) {
    ob_end_clean();
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

// 分类配置
$categories = [
    'AG' => '',
    'CU' => '',
    'DN' => '',
    'DR' => '',
    'IP' => '',
    'MA' => '',
    'ME' => '',
    'MU' => '',
    'OM' => '',
    'OT' => '',
    'SA' => '',
    'SU' => '',
    'SAR' => '',
    'SER' => '',
    'SET' => '',
    'TA' => '',
    'TE' => '',
    'WAN' => '',
    'YA' => ''
];

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
    global $pdo, $categories;
    
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            getDishwareList();
            break;
        case 'stock':
            getStockList();
            break;
        case 'categories':
            sendResponse(true, "获取分类成功", $categories);
            break;
        case 'detail':
            getDishwareDetail();
            break;
        case 'damage_records':
            getBreakRecords();
            break;
        case 'monthly_reset_break_check':
            monthlyResetBreakCheck();
            break;
        case 'sets':
            getDishwareSets();
            break;
        case 'set_detail':
            getDishwareSetDetail();
            break;
        case 'dishware_set_info':
            getDishwareSetInfo();
            break;
        case 'set_stock':
            getSetStockList();
            break;
        case 'set_damage_records':
            getSetBreakRecords();
            break;
        case 'restaurants':
            getRestaurants();
            break;
        case 'transfer_records':
            getTransferRecords();
            break;
        default:
            sendResponse(false, "无效的操作");
    }
}

// 处理 POST 请求
function handlePost() {
    global $pdo, $data;
    
    $action = $_POST['action'] ?? ($data['action'] ?? '');
    
    switch ($action) {
        case 'add':
            addDishware();
            break;
        case 'update':
            updateDishware();
            break;
        case 'upload_photo':
            uploadPhoto();
            break;
        case 'update_stock':
            updateStock();
            break;
        case 'delete':
            deleteDishware();
            break;
        case 'add_damage_record':
            addBreakRecord();
            break;
        case 'update_damage_record':
            updateBreakRecord();
            break;
        case 'delete_damage_record':
            deleteBreakRecord();
            break;
        case 'add_transfer_record':
            addTransferRecord();
            break;
        case 'update_transfer_record':
            updateTransferRecord();
            break;
        case 'delete_transfer_record':
            deleteTransferRecord();
            break;
        case 'add_set':
            addDishwareSet();
            break;
        case 'update_set':
            updateDishwareSet();
            break;
        case 'delete_set':
            deleteDishwareSet();
            break;
        case 'update_set_stock':
            updateSetStock();
            break;
        case 'add_set_damage_record':
            addSetBreakRecord();
            break;
        case 'update_set_damage_record':
            updateSetBreakRecord();
            break;
        case 'delete_set_damage_record':
            deleteSetBreakRecord();
            break;
        case 'remove_item_from_set':
            removeItemFromSet();
            break;
        case 'update_dishware_set_relation':
            updateDishwareSetRelation();
            break;
        case 'add_restaurant':
            addRestaurant();
            break;
        case 'update_restaurant':
            updateRestaurant();
            break;
        case 'delete_restaurant':
            deleteRestaurant();
            break;
        case 'update_restaurant_order':
            updateRestaurantOrder();
            break;
        default:
            sendResponse(false, "无效的操作");
    }
}

// 处理 PUT 请求
function handlePut() {
    global $pdo, $data;
    
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'update':
            updateDishware();
            break;
        case 'update_stock':
            updateStock();
            break;
        default:
            sendResponse(false, "无效的操作");
    }
}

// 处理 DELETE 请求
function handleDelete() {
    global $pdo, $data;
    
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            deleteDishware();
            break;
        default:
            sendResponse(false, "无效的操作");
    }
}

// 获取碗碟列表
function getDishwareList() {
    global $pdo;
    
    try {
        $sql = "SELECT di.*, ds.wenhua_quantity, ds.central_quantity, ds.j1_quantity, ds.j2_quantity, ds.j3_quantity, ds.total_quantity
                FROM dishware_info di
                LEFT JOIN dishware_stock ds ON di.id = ds.dishware_id
                ORDER BY di.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 格式化数据
        foreach ($results as &$item) {
            $item['formatted_price'] = number_format($item['unit_price'], 2);
        }
        
        sendResponse(true, "获取碗碟列表成功", $results);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取碗碟列表失败：" . $e->getMessage());
    }
}

// 获取库存列表
function getStockList() {
    global $pdo;
    
    try {
        // 首先获取所有活跃的餐厅店面
        $restaurants_sql = "SELECT id, name, display_order FROM dishware_restaurant_locations WHERE is_active = 1 ORDER BY display_order";
        $restaurants_stmt = $pdo->prepare($restaurants_sql);
        $restaurants_stmt->execute();
        $restaurants = $restaurants_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 获取碗碟基本信息
        $sql = "SELECT di.id, di.product_name, di.code_number, di.category, di.size, di.unit_price, di.photo_path,
                       MAX(CASE 
                           WHEN dsets.id IS NOT NULL THEN 1 
                           ELSE 0 
                       END) as is_in_set
                FROM dishware_info di
                LEFT JOIN dishware_set_items dsi ON di.id = dsi.dishware_id
                LEFT JOIN dishware_sets dsets ON dsi.set_id = dsets.id AND dsets.is_active = 1
                GROUP BY di.id, di.product_name, di.code_number, di.category, di.size, di.unit_price, di.photo_path
                ORDER BY di.product_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 为每个碗碟获取各餐厅店面的库存
        foreach ($results as &$item) {
            $item['formatted_price'] = number_format($item['unit_price'], 2);
            $item['restaurant_stocks'] = [];
            $total_quantity = 0;
            
            // 获取每个餐厅店面的库存
            foreach ($restaurants as $restaurant) {
                $stock_sql = "SELECT quantity FROM dishware_stock_by_restaurant 
                             WHERE dishware_id = ? AND restaurant_id = ?";
                $stock_stmt = $pdo->prepare($stock_sql);
                $stock_stmt->execute([$item['id'], $restaurant['id']]);
                $stock = $stock_stmt->fetch(PDO::FETCH_ASSOC);
                
                $quantity = $stock ? (int)$stock['quantity'] : 0;
                // 使用餐厅ID作为key，而不是code
                $item['restaurant_stocks'][$restaurant['id']] = $quantity;
                $total_quantity += $quantity;
            }
            
            $item['total_quantity'] = $total_quantity;
            
            // 为了向后兼容，也按顺序添加字段（使用索引）
            $index = 0;
            foreach ($restaurants as $restaurant) {
                $quantity = $item['restaurant_stocks'][$restaurant['id']] ?? 0;
                // 使用索引来创建字段名，保持兼容性
                $item['restaurant_' . $index . '_quantity'] = $quantity;
                $index++;
            }
        }
        
        $data = [
            'items' => $results,
            'restaurants' => $restaurants
        ];
        
        sendResponse(true, "获取库存列表成功", $data);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取库存列表失败：" . $e->getMessage());
    }
}

// 获取碗碟详情
function getDishwareDetail() {
    global $pdo;
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少碗碟ID");
    }
    
    try {
        // 获取碗碟基本信息
        $sql = "SELECT di.*
                FROM dishware_info di
                WHERE di.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            sendResponse(false, "碗碟不存在");
        }
        
        // 获取所有活跃的餐厅店面
        $restaurants_sql = "SELECT id, name, display_order FROM dishware_restaurant_locations WHERE is_active = 1 ORDER BY display_order";
        $restaurants_stmt = $pdo->prepare($restaurants_sql);
        $restaurants_stmt->execute();
        $restaurants = $restaurants_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 获取各餐厅店面的库存（使用新的关联表结构）
        $result['restaurant_stocks'] = [];
        $total_quantity = 0;
        
        // 先查询所有库存记录（一次性查询，提高效率）
        $all_stocks_sql = "SELECT restaurant_id, quantity FROM dishware_stock_by_restaurant 
                          WHERE dishware_id = ?";
        $all_stocks_stmt = $pdo->prepare($all_stocks_sql);
        $all_stocks_stmt->execute([$id]);
        $all_stocks = $all_stocks_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 创建快速查找映射
        $stock_map = [];
        foreach ($all_stocks as $stock) {
            $stock_map[(int)$stock['restaurant_id']] = (int)$stock['quantity'];
        }
        
        foreach ($restaurants as $restaurant) {
            $restaurant_id = (int)$restaurant['id'];
            $quantity = isset($stock_map[$restaurant_id]) ? $stock_map[$restaurant_id] : 0;
            
            // 同时使用字符串和数字键，确保前端能正确读取（JSON中数字键会变成字符串）
            $result['restaurant_stocks'][$restaurant_id] = $quantity;
            $result['restaurant_stocks'][(string)$restaurant_id] = $quantity;
            $total_quantity += $quantity;
        }
        
        $result['total_quantity'] = $total_quantity;
        
        // 调试日志
        error_log("getDishwareDetail - dishware_id: $id");
        error_log("getDishwareDetail - found " . count($all_stocks) . " stock records");
        error_log("getDishwareDetail - restaurant_stocks: " . json_encode($result['restaurant_stocks']));
        error_log("getDishwareDetail - total_quantity: " . $total_quantity);
        
        // 为了向后兼容，也添加按索引的字段
        $index = 0;
        foreach ($restaurants as $restaurant) {
            $quantity = $result['restaurant_stocks'][$restaurant['id']] ?? 0;
            $result['restaurant_' . $index . '_quantity'] = $quantity;
            $index++;
        }
        
        // 为了向后兼容，也添加旧字段（从旧表中获取，如果存在）
        try {
            $old_sql = "SELECT wenhua_quantity, central_quantity, j1_quantity, j2_quantity, j3_quantity 
                       FROM dishware_stock 
                       WHERE dishware_id = ?";
            $old_stmt = $pdo->prepare($old_sql);
            $old_stmt->execute([$id]);
            $old_stock = $old_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($old_stock) {
                $result['wenhua_quantity'] = $old_stock['wenhua_quantity'] ?? 0;
                $result['central_quantity'] = $old_stock['central_quantity'] ?? 0;
                $result['j1_quantity'] = $old_stock['j1_quantity'] ?? 0;
                $result['j2_quantity'] = $old_stock['j2_quantity'] ?? 0;
                $result['j3_quantity'] = $old_stock['j3_quantity'] ?? 0;
            }
        } catch (PDOException $e) {
            // 如果旧表不存在，忽略错误
        }
        
        $result['formatted_price'] = number_format($result['unit_price'], 2);
        
        sendResponse(true, "获取碗碟详情成功", $result);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取碗碟详情失败：" . $e->getMessage());
    }
}

// 添加碗碟
function addDishware() {
    global $pdo, $data;
    
    // 获取POST数据
    $postData = $_POST;
    
    $required_fields = ['product_name', 'category'];
    foreach ($required_fields as $field) {
        if (empty($postData[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // 插入碗碟信息
        $sql = "INSERT INTO dishware_info (product_name, code_number, category, size, unit_price, photo_path) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $postData['product_name'],
            $postData['code_number'] ?? '',
            $postData['category'],
            $postData['size'] ?? '',
            !empty($postData['unit_price']) ? $postData['unit_price'] : null,
            $postData['photo_path'] ?? ''
        ]);
        
        $dishware_id = $pdo->lastInsertId();
        
        // 创建对应的库存记录（使用新的关联表结构）
        // 获取所有活跃的餐厅店面
        $restaurants_sql = "SELECT id FROM dishware_restaurant_locations WHERE is_active = 1";
        $restaurants_stmt = $pdo->prepare($restaurants_sql);
        $restaurants_stmt->execute();
        $restaurants = $restaurants_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 为每个餐厅店面创建初始库存记录
        foreach ($restaurants as $restaurant) {
            $stock_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                         VALUES (?, ?, 0)";
            $stock_stmt = $pdo->prepare($stock_sql);
            $stock_stmt->execute([$dishware_id, $restaurant['id']]);
        }
        
        // 为了向后兼容，同时创建旧表记录
        try {
            $old_sql = "INSERT INTO dishware_stock (dishware_id, wenhua_quantity, central_quantity, j1_quantity, j2_quantity, j3_quantity) 
                        VALUES (?, 0, 0, 0, 0, 0)";
            $old_stmt = $pdo->prepare($old_sql);
            $old_stmt->execute([$dishware_id]);
        } catch (PDOException $e) {
            // 如果旧表不存在，忽略
        }
        
        $pdo->commit();
        sendResponse(true, "添加碗碟成功", ['id' => $dishware_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "添加碗碟失败：" . $e->getMessage());
    }
}

// 更新碗碟信息
function updateDishware() {
    global $pdo, $data;
    
    // 支持从POST和PUT请求中获取数据
    $id = $data['id'] ?? $_POST['id'] ?? '';
    $product_name = $data['product_name'] ?? $_POST['product_name'] ?? '';
    $code_number = $data['code_number'] ?? $_POST['code_number'] ?? '';
    $category = $data['category'] ?? $_POST['category'] ?? '';
    $size = $data['size'] ?? $_POST['size'] ?? '';
    $unit_price = $data['unit_price'] ?? $_POST['unit_price'] ?? '';
    $photo_path = $data['photo_path'] ?? $_POST['photo_path'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少碗碟ID");
    }
    
    if (empty($product_name)) {
        sendResponse(false, "缺少产品名称");
    }
    
    if (empty($category)) {
        sendResponse(false, "缺少分类");
    }
    
    try {
        $sql = "UPDATE dishware_info SET 
                product_name = ?, code_number = ?, category = ?, size = ?, unit_price = ?, 
                photo_path = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $product_name,
            $code_number,
            $category,
            $size,
            $unit_price,
            $photo_path,
            $id
        ]);
        
        sendResponse(true, "更新碗碟信息成功");
        
    } catch (PDOException $e) {
        sendResponse(false, "更新碗碟信息失败：" . $e->getMessage());
    }
}

// 更新库存
function updateStock() {
    global $pdo, $data;
    
    // 支持从POST和PUT请求中获取数据
    // 如果$data为空（可能是JSON解析失败），尝试从php://input重新解析
    if (empty($data) || !is_array($data)) {
        $data = get_safe_json_input();
    }
    
    $dishware_id = $data['dishware_id'] ?? $_POST['dishware_id'] ?? '';
    
    // 调试日志
    error_log("updateStock - dishware_id: " . $dishware_id);
    error_log("updateStock - data type: " . gettype($data));
    error_log("updateStock - data: " . json_encode($data));
    error_log("updateStock - POST: " . json_encode($_POST));
    error_log("updateStock - Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    
    if (empty($dishware_id)) {
        sendResponse(false, "缺少碗碟ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取所有活跃的餐厅店面
        $restaurants_sql = "SELECT id FROM dishware_restaurant_locations WHERE is_active = 1";
        $restaurants_stmt = $pdo->prepare($restaurants_sql);
        $restaurants_stmt->execute();
        $restaurants = $restaurants_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 更新每个餐厅店面的库存
        // 获取所有餐厅店面，按显示顺序
        $restaurants_list = $pdo->query("SELECT id FROM dishware_restaurant_locations WHERE is_active = 1 ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("updateStock - restaurants_list count: " . count($restaurants_list));
        error_log("updateStock - restaurant_quantities: " . (isset($data['restaurant_quantities']) ? json_encode($data['restaurant_quantities']) : 'not set'));
        
        // 如果提供了按顺序的数组
        if (isset($data['restaurant_quantities']) && is_array($data['restaurant_quantities'])) {
            $update_count = 0;
            foreach ($restaurants_list as $index => $restaurant) {
                $quantity = isset($data['restaurant_quantities'][$index]) ? (int)$data['restaurant_quantities'][$index] : 0;
                
                $sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        quantity = VALUES(quantity),
                        last_updated = CURRENT_TIMESTAMP";
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$dishware_id, $restaurant['id'], $quantity]);
                
                if ($result) {
                    $update_count++;
                }
                
                error_log("updateStock - dishware_id=$dishware_id, restaurant_id={$restaurant['id']}, index=$index, quantity=$quantity, result=" . ($result ? 'success' : 'failed'));
            }
            error_log("updateStock - 总共更新了 $update_count / " . count($restaurants_list) . " 个餐厅的库存");
        } else {
            // 向后兼容：支持按餐厅ID的格式
            foreach ($restaurants_list as $restaurant) {
                $restaurant_id = $restaurant['id'];
                $quantity = $data['restaurant_' . $restaurant_id . '_quantity'] ?? $_POST['restaurant_' . $restaurant_id . '_quantity'] ?? 0;
                
                $sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        quantity = VALUES(quantity),
                        last_updated = CURRENT_TIMESTAMP";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$dishware_id, $restaurant_id, $quantity]);
            }
        }
        
        // 为了向后兼容，同时更新旧表（如果存在）
        $wenhua_quantity = $data['wenhua_quantity'] ?? $_POST['wenhua_quantity'] ?? 0;
        $central_quantity = $data['central_quantity'] ?? $_POST['central_quantity'] ?? 0;
        $j1_quantity = $data['j1_quantity'] ?? $_POST['j1_quantity'] ?? 0;
        $j2_quantity = $data['j2_quantity'] ?? $_POST['j2_quantity'] ?? 0;
        $j3_quantity = $data['j3_quantity'] ?? $_POST['j3_quantity'] ?? 0;
        
        try {
            $old_sql = "INSERT INTO dishware_stock (dishware_id, wenhua_quantity, central_quantity, j1_quantity, j2_quantity, j3_quantity) 
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        wenhua_quantity = VALUES(wenhua_quantity),
                        central_quantity = VALUES(central_quantity),
                        j1_quantity = VALUES(j1_quantity),
                        j2_quantity = VALUES(j2_quantity),
                        j3_quantity = VALUES(j3_quantity),
                        last_updated = CURRENT_TIMESTAMP";
            $old_stmt = $pdo->prepare($old_sql);
            $old_stmt->execute([$dishware_id, $wenhua_quantity, $central_quantity, $j1_quantity, $j2_quantity, $j3_quantity]);
        } catch (PDOException $e) {
            // 如果旧表不存在或出错，忽略（向后兼容）
        }
        
        $pdo->commit();
        sendResponse(true, "更新库存成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新库存失败：" . $e->getMessage());
    }
}

// 删除碗碟
function deleteDishware() {
    global $pdo, $data;
    
    // 支持从POST和DELETE请求中获取ID
    $id = $data['id'] ?? $_POST['id'] ?? '';
    
    // 调试信息
    error_log("删除请求 - ID: " . $id);
    error_log("删除请求 - data: " . json_encode($data));
    error_log("删除请求 - POST: " . json_encode($_POST));
    
    if (empty($id)) {
        sendResponse(false, "缺少碗碟ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 先检查记录是否存在
        $check_sql = "SELECT id FROM dishware_info WHERE id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$id]);
        $exists = $check_stmt->fetch();
        
        if (!$exists) {
            $pdo->rollBack();
            sendResponse(false, "碗碟记录不存在");
        }
        
        // 删除库存记录（外键约束会自动处理）
        $sql = "DELETE FROM dishware_stock WHERE dishware_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // 删除碗碟信息
        $sql = "DELETE FROM dishware_info WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $pdo->commit();
        sendResponse(true, "删除碗碟成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("删除碗碟数据库错误: " . $e->getMessage());
        sendResponse(false, "删除碗碟失败：" . $e->getMessage());
    }
}

// 上传照片
function uploadPhoto() {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, "照片上传失败");
    }
    
    $upload_dir = 'uploads/dishware/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'heic', 'heif'];
    
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        sendResponse(false, "不支持的文件格式");
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
        // HEIC/HEIF 自动转换为 JPG
        $converted = convertHeicToJpg($file_path, $file_extension);
        if ($converted['converted']) {
            $file_path = $converted['path'];
        }
        sendResponse(true, "照片上传成功", ['photo_path' => $file_path]);
    } else {
        sendResponse(false, "照片保存失败");
    }
}

// 获取打破记录
function getBreakRecords() {
    global $pdo;
    
    $shop_type = $_GET['shop_type'] ?? '';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    
    if (empty($shop_type)) {
        sendResponse(false, "缺少店铺类型参数");
    }
    
    try {
        // 首先根据shop_type找到对应的restaurant_id
        $restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
        $restaurant_stmt = $pdo->prepare($restaurant_sql);
        $restaurant_stmt->execute([$shop_type]);
        $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);
        $restaurant_id = $restaurant ? $restaurant['id'] : null;
        
        // 判断归档表是否存在
        $has_archive = false;
        try {
            $pdo->query("SELECT 1 FROM dishware_break_records_archive LIMIT 1");
            $has_archive = true;
        } catch (PDOException $e) {
            $has_archive = false;
        }
        
        if ($has_archive) {
            $base_sql = "
                SELECT id, dishware_id, shop_type, break_quantity, chargeable_quantity, unit_price, total_price, break_date, recorded_by, created_at, updated_at, NULL as archive_ym
                FROM dishware_break_records
                UNION ALL
                SELECT id, dishware_id, shop_type, break_quantity, chargeable_quantity, unit_price, total_price, break_date, recorded_by, created_at, updated_at, archive_ym
                FROM dishware_break_records_archive
            ";
        } else {
            $base_sql = "
                SELECT id, dishware_id, shop_type, break_quantity, chargeable_quantity, unit_price, total_price, break_date, recorded_by, created_at, updated_at, NULL as archive_ym
                FROM dishware_break_records
            ";
        }

        $sql = "SELECT dbr.*, di.product_name, di.code_number, di.category, di.size, di.photo_path, di.unit_price as current_unit_price
                FROM ($base_sql) AS dbr
                LEFT JOIN dishware_info di ON dbr.dishware_id = di.id
                WHERE dbr.shop_type = ?";
        $params = [$shop_type];
        if (!empty($start_date) && !empty($end_date)) {
            $sql .= " AND dbr.break_date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        $sql .= " ORDER BY dbr.break_date ASC, dbr.created_at ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 添加当前库存字段和计算总价
        foreach ($results as &$result) {
            // 从新的动态餐厅结构中获取当前库存
            if ($restaurant_id) {
                $stock_sql = "SELECT quantity FROM dishware_stock_by_restaurant 
                             WHERE dishware_id = ? AND restaurant_id = ?";
                $stock_stmt = $pdo->prepare($stock_sql);
                $stock_stmt->execute([$result['dishware_id'], $restaurant_id]);
                $stock = $stock_stmt->fetch(PDO::FETCH_ASSOC);
                $result['current_stock'] = $stock ? (int)$stock['quantity'] : 0;
            } else {
                // 向后兼容：尝试从旧表获取
                $stock_field = $shop_type . '_quantity';
                $old_stock_sql = "SELECT $stock_field FROM dishware_stock WHERE dishware_id = ?";
                try {
                    $old_stock_stmt = $pdo->prepare($old_stock_sql);
                    $old_stock_stmt->execute([$result['dishware_id']]);
                    $old_stock = $old_stock_stmt->fetch(PDO::FETCH_ASSOC);
                    $result['current_stock'] = $old_stock ? (int)$old_stock[$stock_field] : 0;
                } catch (PDOException $e) {
                    $result['current_stock'] = 0;
                }
            }
            
            // 降级处理：仅在数据库储存为空时，回退到原本（现有）商品价格
            if (empty($result['unit_price']) && $result['unit_price'] !== '0.00' && $result['unit_price'] !== 0) {
                $result['unit_price'] = $result['current_unit_price'] ?? 0;
            }

            // 仅当原本的 total_price 真的是空时，才被动执行计算。否则无脑使用当初破损时记录存下来的总价！
            if (empty($result['total_price']) && $result['total_price'] !== '0.00' && $result['total_price'] !== 0) {
                $chargeable = isset($result['chargeable_quantity']) && $result['chargeable_quantity'] !== null
                    ? (int) $result['chargeable_quantity'] : null;
                $result['total_price'] = $chargeable !== null
                    ? $result['unit_price'] * $chargeable
                    : $result['unit_price'] * $result['break_quantity'];
            }
        }
        
        sendResponse(true, "获取破损记录成功", $results);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取破损记录失败：" . $e->getMessage());
    }
}

// 每月1号自动归档破损记录：把上月记录移入归档表后清空主表，当月仅执行一次
function monthlyResetBreakCheck() {
    global $pdo;
    $file = __DIR__ . '/last_break_reset_ym.txt';
    $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
    $ym  = $now->format('Y-m');
    $day = (int) $now->format('d');

    // 非1号直接返回
    if ($day !== 1) {
        sendResponse(true, "非每月1号，无需归档", ['reset_done' => false]);
        return;
    }

    // 本月已归档过则跳过
    $last = @file_get_contents($file);
    $last = $last ? trim($last) : '';
    if ($last === $ym) {
        sendResponse(true, "本月已归档", ['reset_done' => false]);
        return;
    }

    // 归档目标月份 = 上个月（例如在4月1号归档3月数据）
    $prevDate  = new DateTime('first day of last month', new DateTimeZone('Asia/Kuala_Lumpur'));
    $archiveYm = $prevDate->format('Y-m');

    try {
        $pdo->beginTransaction();

        // ── 创建破损记录归档表（如不存在）──────────────────────────────
        $pdo->exec("CREATE TABLE IF NOT EXISTS dishware_break_records_archive (
            archive_id      INT          NOT NULL AUTO_INCREMENT,
            archive_ym      VARCHAR(7)   NOT NULL COMMENT '归档年月, 如 2026-03',
            id              INT          NOT NULL COMMENT '原记录 ID',
            dishware_id     INT,
            shop_type       VARCHAR(100),
            break_quantity  INT,
            chargeable_quantity INT,
            unit_price      DECIMAL(10,2),
            total_price     DECIMAL(10,2),
            break_date      DATE,
            recorded_by     VARCHAR(255),
            created_at      TIMESTAMP    NULL DEFAULT NULL,
            updated_at      TIMESTAMP    NULL DEFAULT NULL,
            PRIMARY KEY (archive_id),
            INDEX idx_archive_ym  (archive_ym),
            INDEX idx_dishware_id (dishware_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='破损记录月度归档'");

        // ── 把主表当前所有记录复制进归档表 ────────────────────────────
        $pdo->prepare("
            INSERT INTO dishware_break_records_archive
                (archive_ym, id, dishware_id, shop_type,
                 break_quantity, chargeable_quantity,
                 unit_price, total_price,
                 break_date, recorded_by, created_at, updated_at)
            SELECT ?, id, dishware_id, shop_type,
                   break_quantity, chargeable_quantity,
                   unit_price, total_price,
                   break_date, recorded_by, created_at, updated_at
            FROM dishware_break_records
        ")->execute([$archiveYm]);

        $archivedCount = $pdo->query("SELECT ROW_COUNT()")->fetchColumn();

        // ── 清空主表 ────────────────────────────────────────────────────
        $pdo->exec("DELETE FROM dishware_break_records");

        // ── 同样处理 set 破损记录表（若有数据） ────────────────────────
        $setCount = (int) $pdo->query("SELECT COUNT(*) FROM dishware_set_break_records")->fetchColumn();
        if ($setCount > 0) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS dishware_set_break_records_archive (
                archive_id  INT        NOT NULL AUTO_INCREMENT,
                archive_ym  VARCHAR(7) NOT NULL,
                id          INT        NOT NULL COMMENT '原记录 ID',
                set_id      INT,
                shop_type   VARCHAR(100),
                break_quantity INT,
                unit_price  DECIMAL(10,2),
                total_price DECIMAL(10,2),
                break_date  DATE,
                recorded_by VARCHAR(255),
                created_at  TIMESTAMP  NULL DEFAULT NULL,
                updated_at  TIMESTAMP  NULL DEFAULT NULL,
                PRIMARY KEY (archive_id),
                INDEX idx_archive_ym (archive_ym),
                INDEX idx_set_id (set_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            $pdo->prepare("
                INSERT INTO dishware_set_break_records_archive
                    (archive_ym, id, set_id, shop_type, 
                     break_quantity, unit_price, total_price, 
                     break_date, recorded_by, created_at, updated_at)
                SELECT ?, id, set_id, shop_type, 
                       break_quantity, unit_price, total_price,
                       break_date, recorded_by, created_at, updated_at
                FROM dishware_set_break_records
            ")->execute([$archiveYm]);

            $pdo->exec("DELETE FROM dishware_set_break_records");
        }

        // ── 写入标记文件，确保本月不再重复归档 ─────────────────────────
        if (@file_put_contents($file, $ym) === false) {
            error_log("monthlyResetBreakCheck: 无法写入标记文件 " . $file);
        }

        $pdo->commit();

        sendResponse(true, "已将 {$archiveYm} 破损记录归档（共 {$archivedCount} 条），主表已清空", [
            'reset_done'    => true,
            'archive_ym'    => $archiveYm,
            'archived_count'=> (int) $archivedCount,
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        sendResponse(false, "归档破损记录失败：" . $e->getMessage());
    }
}

// 添加破损记录
function addBreakRecord() {
    global $pdo, $data;
    
    // 支持从POST和JSON数据中获取数据
    $postData = !empty($data) ? $data : $_POST;
    
    // 调试信息
    error_log("addBreakRecord - 接收到的数据: " . json_encode($postData));
    error_log("addBreakRecord - $_POST: " . json_encode($_POST));
    error_log("addBreakRecord - $data: " . json_encode($data));
    
    $required_fields = ['dishware_id', 'shop_type', 'break_quantity'];
    foreach ($required_fields as $field) {
        if (empty($postData[$field])) {
            error_log("缺少必填字段: $field, 值: " . ($postData[$field] ?? 'null'));
            sendResponse(false, "缺少必填字段：$field");
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        $unit_price = $postData['unit_price'] ?? 0;
        $chargeable = isset($postData['chargeable_quantity']) ? (int) $postData['chargeable_quantity'] : null;
        $chargeable = $chargeable !== null ? $chargeable : (int) $postData['break_quantity'];
        $total_price = $unit_price * $chargeable;
        $break_date = $postData['break_date'] ?? null;
        if (empty($break_date)) {
            throw new Exception("缺少破损日期 (break_date)");
        }
        
        $sql = "INSERT INTO dishware_break_records (dishware_id, shop_type, break_quantity, chargeable_quantity, unit_price, total_price, break_date, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $postData['dishware_id'],
            $postData['shop_type'],
            $postData['break_quantity'],
            $chargeable,
            $unit_price,
            $total_price,
            $break_date,
            $postData['recorded_by'] ?? 'system'
        ]);
        
        $record_id = $pdo->lastInsertId();
        
        // 更新对应店铺的库存（减少库存）- 使用新的动态餐厅结构
        // 首先根据shop_type找到对应的restaurant_id
        $restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
        $restaurant_stmt = $pdo->prepare($restaurant_sql);
        $restaurant_stmt->execute([$postData['shop_type']]);
        $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($restaurant) {
            // 使用新的动态餐厅结构更新库存
            try {
                $update_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                              VALUES (?, ?, GREATEST(0, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) - ?))
                              ON DUPLICATE KEY UPDATE 
                              quantity = GREATEST(0, quantity - ?),
                              last_updated = CURRENT_TIMESTAMP";
                
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    $postData['dishware_id'],
                    $restaurant['id'],
                    $postData['dishware_id'],
                    $restaurant['id'],
                    $postData['break_quantity'],
                    $postData['break_quantity']
                ]);
            } catch (PDOException $e) {
                // 记录错误但不回滚事务，确保破损记录能保存
                error_log("更新库存失败 (新表): " . $e->getMessage() . " - shop_type: " . $postData['shop_type'] . ", restaurant_id: " . ($restaurant['id'] ?? 'null'));
            }
        } else {
            // 如果找不到restaurant_id，记录警告
            error_log("警告: 找不到对应的restaurant_id - shop_type: " . $postData['shop_type']);
        }
        
        // 为了向后兼容，同时更新旧表（如果存在）- 仅对j1, j2, j3有效
        $stock_field = $postData['shop_type'] . '_quantity';
        if (in_array(strtolower($postData['shop_type']), ['j1', 'j2', 'j3'])) {
            try {
                $old_update_sql = "UPDATE dishware_stock SET 
                                 $stock_field = GREATEST(0, $stock_field - ?),
                                 last_updated = CURRENT_TIMESTAMP
                                 WHERE dishware_id = ?";
                $old_update_stmt = $pdo->prepare($old_update_sql);
                $old_update_stmt->execute([
                    $postData['break_quantity'],
                    $postData['dishware_id']
                ]);
            } catch (PDOException $e) {
                // 如果旧表不存在或字段不存在，忽略错误
                error_log("更新库存失败 (旧表): " . $e->getMessage());
            }
        }
        
        $pdo->commit();
        
        // 记录成功日志
        error_log("添加破损记录成功 - ID: $record_id, shop_type: " . $postData['shop_type'] . ", dishware_id: " . $postData['dishware_id']);
        
        sendResponse(true, "添加破损记录成功", ['id' => $record_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("添加破损记录失败: " . $e->getMessage() . " - shop_type: " . ($postData['shop_type'] ?? 'null') . ", dishware_id: " . ($postData['dishware_id'] ?? 'null'));
        sendResponse(false, "添加破损记录失败：" . $e->getMessage());
    }
}

// 更新破损记录
function updateBreakRecord() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    $break_quantity = $data['break_quantity'] ?? $_POST['break_quantity'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取原记录信息
        $old_sql = "SELECT * FROM dishware_break_records WHERE id = ?";
        $old_stmt = $pdo->prepare($old_sql);
        $old_stmt->execute([$id]);
        $old_record = $old_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$old_record) {
            sendResponse(false, "记录不存在");
        }
        
        $unit_price = $data['unit_price'] ?? $_POST['unit_price'] ?? $old_record['unit_price'];
        $chargeable = isset($data['chargeable_quantity']) ? (int) $data['chargeable_quantity'] : (isset($_POST['chargeable_quantity']) ? (int) $_POST['chargeable_quantity'] : null);
        $chargeable = $chargeable !== null ? $chargeable : (int) $break_quantity;
        $total_price = $unit_price * $chargeable;
        
        $new_dishware_id = $data['dishware_id'] ?? $_POST['dishware_id'] ?? $old_record['dishware_id'];
        $dishware_id_changed = ($new_dishware_id != $old_record['dishware_id']);
        
        $sql = "UPDATE dishware_break_records SET 
                break_quantity = ?, chargeable_quantity = ?, unit_price = ?, total_price = ?";
        
        if ($dishware_id_changed) {
            $sql .= ", dishware_id = ?";
        }
        
        $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        $params = [$break_quantity, $chargeable, $unit_price, $total_price];
        if ($dishware_id_changed) {
            $params[] = $new_dishware_id;
        }
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // 如果产品ID改变了，需要调整两个产品的库存
        if ($dishware_id_changed) {
            // 首先根据shop_type找到对应的restaurant_id
            $restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
            $restaurant_stmt = $pdo->prepare($restaurant_sql);
            $restaurant_stmt->execute([$old_record['shop_type']]);
            $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($restaurant) {
                // 恢复旧产品的库存（增加破损数量）
                $restore_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                              VALUES (?, ?, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) + ?)
                              ON DUPLICATE KEY UPDATE 
                              quantity = quantity + ?,
                              last_updated = CURRENT_TIMESTAMP";
                $restore_stmt = $pdo->prepare($restore_sql);
                $restore_stmt->execute([
                    $old_record['dishware_id'],
                    $restaurant['id'],
                    $old_record['dishware_id'],
                    $restaurant['id'],
                    $old_record['break_quantity'],
                    $old_record['break_quantity']
                ]);
                
                // 减少新产品的库存（减少新破损数量）
                $deduct_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                              VALUES (?, ?, GREATEST(0, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) - ?))
                              ON DUPLICATE KEY UPDATE 
                              quantity = GREATEST(0, quantity - ?),
                              last_updated = CURRENT_TIMESTAMP";
                $deduct_stmt = $pdo->prepare($deduct_sql);
                $deduct_stmt->execute([
                    $new_dishware_id,
                    $restaurant['id'],
                    $new_dishware_id,
                    $restaurant['id'],
                    $break_quantity,
                    $break_quantity
                ]);
            }
        } else {
            // 更新库存（调整差异）- 使用新的动态餐厅结构
            $quantity_diff = $break_quantity - $old_record['break_quantity'];
            if ($quantity_diff != 0) {
            // 首先根据shop_type找到对应的restaurant_id
            $restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
            $restaurant_stmt = $pdo->prepare($restaurant_sql);
            $restaurant_stmt->execute([$old_record['shop_type']]);
            $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($restaurant) {
                // 使用新的动态餐厅结构更新库存
                if ($quantity_diff > 0) {
                    // 增加破损数量，减少库存
                    $update_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                                  VALUES (?, ?, GREATEST(0, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) - ?))
                                  ON DUPLICATE KEY UPDATE 
                                  quantity = GREATEST(0, quantity - ?),
                                  last_updated = CURRENT_TIMESTAMP";
                } else {
                    // 减少破损数量，增加库存
                    $update_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                                  VALUES (?, ?, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) + ?)
                                  ON DUPLICATE KEY UPDATE 
                                  quantity = quantity + ?,
                                  last_updated = CURRENT_TIMESTAMP";
                }
                
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    $old_record['dishware_id'],
                    $restaurant['id'],
                    $old_record['dishware_id'],
                    $restaurant['id'],
                    abs($quantity_diff),
                    abs($quantity_diff)
                ]);
            }
            
            // 为了向后兼容，同时更新旧表（如果存在）
            $stock_field = $old_record['shop_type'] . '_quantity';
            try {
                if ($quantity_diff > 0) {
                    $old_update_sql = "UPDATE dishware_stock SET 
                                     $stock_field = GREATEST(0, $stock_field - ?),
                                     last_updated = CURRENT_TIMESTAMP
                                     WHERE dishware_id = ?";
                } else {
                    $old_update_sql = "UPDATE dishware_stock SET 
                                     $stock_field = $stock_field + ?,
                                     last_updated = CURRENT_TIMESTAMP
                                     WHERE dishware_id = ?";
                }
                $old_update_stmt = $pdo->prepare($old_update_sql);
                $old_update_stmt->execute([
                    abs($quantity_diff),
                    $old_record['dishware_id']
                ]);
            } catch (PDOException $e) {
                // 如果旧表不存在，忽略错误
            }
            }
        }
        
        $pdo->commit();
        sendResponse(true, "更新破损记录成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新破损记录失败：" . $e->getMessage());
    }
}

// 删除破损记录
function deleteBreakRecord() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取记录信息
        $select_sql = "SELECT * FROM dishware_break_records WHERE id = ?";
        $select_stmt = $pdo->prepare($select_sql);
        $select_stmt->execute([$id]);
        $record = $select_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            sendResponse(false, "记录不存在");
        }
        
        // 删除记录
        $delete_sql = "DELETE FROM dishware_break_records WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id]);
        
        // 恢复库存（增加库存）- 使用新的动态餐厅结构
        // 首先根据shop_type找到对应的restaurant_id
        $restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
        $restaurant_stmt = $pdo->prepare($restaurant_sql);
        $restaurant_stmt->execute([$record['shop_type']]);
        $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($restaurant) {
            // 使用新的动态餐厅结构更新库存
            $update_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                          VALUES (?, ?, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) + ?)
                          ON DUPLICATE KEY UPDATE 
                          quantity = quantity + ?,
                          last_updated = CURRENT_TIMESTAMP";
            
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                $record['dishware_id'],
                $restaurant['id'],
                $record['dishware_id'],
                $restaurant['id'],
                $record['break_quantity'],
                $record['break_quantity']
            ]);
        }
        
        // 为了向后兼容，同时更新旧表（如果存在）
        $stock_field = $record['shop_type'] . '_quantity';
        try {
            $old_update_sql = "UPDATE dishware_stock SET 
                             $stock_field = $stock_field + ?,
                             last_updated = CURRENT_TIMESTAMP
                             WHERE dishware_id = ?";
            $old_update_stmt = $pdo->prepare($old_update_sql);
            $old_update_stmt->execute([
                $record['break_quantity'],
                $record['dishware_id']
            ]);
        } catch (PDOException $e) {
            // 如果旧表不存在，忽略错误
        }
        
        $pdo->commit();
        sendResponse(true, "删除破损记录成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "删除破损记录失败：" . $e->getMessage());
    }
}

// 获取套装列表
function getDishwareSets() {
    global $pdo;
    
    try {
        $sql = "SELECT ds.*, 
                       GROUP_CONCAT(
                           CONCAT(di.product_name, ' (', di.code_number, ')') 
                           ORDER BY dsi.sort_order 
                           SEPARATOR ', '
                       ) as items_list,
                       COUNT(dsi.dishware_id) as items_count
                FROM dishware_sets ds
                LEFT JOIN dishware_set_items dsi ON ds.id = dsi.set_id
                LEFT JOIN dishware_info di ON dsi.dishware_id = di.id
                WHERE ds.is_active = 1
                GROUP BY ds.id
                ORDER BY ds.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 格式化数据
        foreach ($results as &$item) {
            $item['formatted_price'] = number_format($item['set_price'], 2);
        }
        
        sendResponse(true, "获取套装列表成功", $results);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取套装列表失败：" . $e->getMessage());
    }
}

// 获取套装详情
function getDishwareSetDetail() {
    global $pdo;
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少套装ID");
    }
    
    try {
        // 获取套装基本信息
        $set_sql = "SELECT * FROM dishware_sets WHERE id = ? AND is_active = 1";
        $set_stmt = $pdo->prepare($set_sql);
        $set_stmt->execute([$id]);
        $set = $set_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$set) {
            sendResponse(false, "套装不存在");
        }
        
        // 获取套装中的碗碟
        $items_sql = "SELECT di.*, dsi.quantity_in_set, dsi.sort_order
                      FROM dishware_set_items dsi
                      LEFT JOIN dishware_info di ON dsi.dishware_id = di.id
                      WHERE dsi.set_id = ?
                      ORDER BY dsi.sort_order";
        
        $items_stmt = $pdo->prepare($items_sql);
        $items_stmt->execute([$id]);
        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $set['items'] = $items;
        $set['formatted_price'] = number_format($set['set_price'], 2);
        
        sendResponse(true, "获取套装详情成功", $set);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取套装详情失败：" . $e->getMessage());
    }
}

// 获取套装库存列表
function getSetStockList() {
    global $pdo;
    
    try {
        // 首先获取所有活跃的餐厅店面
        $restaurants_sql = "SELECT id, name, display_order FROM dishware_restaurant_locations WHERE is_active = 1 ORDER BY display_order";
        $restaurants_stmt = $pdo->prepare($restaurants_sql);
        $restaurants_stmt->execute();
        $restaurants = $restaurants_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 获取套装基本信息（包含 items_list）
        $sql = "SELECT ds.id, ds.set_name, ds.set_code, ds.set_price,
                       GROUP_CONCAT(
                           CONCAT(di.product_name, ' (', di.code_number, ')') 
                           ORDER BY dsi.sort_order 
                           SEPARATOR ', '
                       ) as items_list,
                       COUNT(dsi.dishware_id) as items_count
                FROM dishware_sets ds
                LEFT JOIN dishware_set_items dsi ON ds.id = dsi.set_id
                LEFT JOIN dishware_info di ON dsi.dishware_id = di.id
                WHERE ds.is_active = 1
                GROUP BY ds.id
                ORDER BY ds.set_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 为每个套装获取各餐厅店面的库存
        foreach ($results as &$item) {
            $item['formatted_price'] = number_format($item['set_price'], 2);
            // 如果 items_list 为空，设置为 null
            if (empty($item['items_list'])) {
                $item['items_list'] = null;
            }
            $item['restaurant_stocks'] = [];
            $total_quantity = 0;
            
            // 获取每个餐厅店面的库存
            foreach ($restaurants as $restaurant) {
                $stock_sql = "SELECT quantity FROM dishware_set_stock_by_restaurant 
                             WHERE set_id = ? AND restaurant_id = ?";
                $stock_stmt = $pdo->prepare($stock_sql);
                $stock_stmt->execute([$item['id'], $restaurant['id']]);
                $stock = $stock_stmt->fetch(PDO::FETCH_ASSOC);
                
                $quantity = $stock ? (int)$stock['quantity'] : 0;
                // 使用餐厅ID作为key
                $item['restaurant_stocks'][$restaurant['id']] = $quantity;
                $total_quantity += $quantity;
            }
            
            $item['total_quantity'] = $total_quantity;
            
            // 为了向后兼容，也按顺序添加字段（使用索引）
            $index = 0;
            foreach ($restaurants as $restaurant) {
                $quantity = $item['restaurant_stocks'][$restaurant['id']] ?? 0;
                $item['restaurant_' . $index . '_quantity'] = $quantity;
                $index++;
            }
        }
        
        $data = [
            'items' => $results,
            'restaurants' => $restaurants
        ];
        
        sendResponse(true, "获取套装库存列表成功", $data);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取套装库存列表失败：" . $e->getMessage());
    }
}

// 添加套装
function addDishwareSet() {
    global $pdo, $data;
    
    // 获取POST数据
    $postData = !empty($data) ? $data : $_POST;
    
    $required_fields = ['set_name', 'set_code', 'set_price'];
    foreach ($required_fields as $field) {
        if (empty($postData[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // 插入套装信息
        $sql = "INSERT INTO dishware_sets (set_name, set_code, set_size, set_price, description) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $postData['set_name'],
            $postData['set_code'],
            $postData['set_size'] ?? '',
            $postData['set_price'],
            $postData['description'] ?? ''
        ]);
        
        $set_id = $pdo->lastInsertId();
        
        // 添加套装项目
        if (!empty($postData['items']) && is_array($postData['items'])) {
            foreach ($postData['items'] as $index => $item) {
                $item_sql = "INSERT INTO dishware_set_items (set_id, dishware_id, quantity_in_set, sort_order) 
                             VALUES (?, ?, ?, ?)";
                $item_stmt = $pdo->prepare($item_sql);
                $item_stmt->execute([
                    $set_id,
                    $item['dishware_id'],
                    $item['quantity_in_set'] ?? 1,
                    $index + 1
                ]);
            }
        }
        
        // 创建对应的库存记录（使用新的关联表结构）
        // 获取所有活跃的餐厅店面
        $restaurants_sql = "SELECT id FROM dishware_restaurant_locations WHERE is_active = 1";
        $restaurants_stmt = $pdo->prepare($restaurants_sql);
        $restaurants_stmt->execute();
        $restaurants = $restaurants_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 为每个餐厅店面创建初始库存记录
        foreach ($restaurants as $restaurant) {
            $stock_sql = "INSERT INTO dishware_set_stock_by_restaurant (set_id, restaurant_id, quantity) 
                         VALUES (?, ?, 0)";
            $stock_stmt = $pdo->prepare($stock_sql);
            $stock_stmt->execute([$set_id, $restaurant['id']]);
        }
        
        // 为了向后兼容，同时创建旧表记录
        try {
            $old_stock_sql = "INSERT INTO dishware_set_stock (set_id, wenhua_quantity, central_quantity, j1_quantity, j2_quantity, j3_quantity) 
                              VALUES (?, 0, 0, 0, 0, 0)";
            $old_stock_stmt = $pdo->prepare($old_stock_sql);
            $old_stock_stmt->execute([$set_id]);
        } catch (PDOException $e) {
            // 如果旧表不存在，忽略
        }
        
        $pdo->commit();
        sendResponse(true, "添加套装成功", ['id' => $set_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "添加套装失败：" . $e->getMessage());
    }
}

// 更新套装信息
function updateDishwareSet() {
    global $pdo, $data;
    
    // 支持从POST和PUT请求中获取数据
    $id = $data['id'] ?? $_POST['id'] ?? '';
    $set_name = $data['set_name'] ?? $_POST['set_name'] ?? '';
    $set_code = $data['set_code'] ?? $_POST['set_code'] ?? '';
    $set_size = $data['set_size'] ?? $_POST['set_size'] ?? '';
    $set_price = $data['set_price'] ?? $_POST['set_price'] ?? '';
    $description = $data['description'] ?? $_POST['description'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少套装ID");
    }
    
    if (empty($set_name)) {
        sendResponse(false, "缺少套装名称");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 更新套装基本信息
        $sql = "UPDATE dishware_sets SET 
                set_name = ?, set_code = ?, set_size = ?, set_price = ?, description = ?, 
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $set_name,
            $set_code,
            $set_size,
            $set_price,
            $description,
            $id
        ]);
        
        // 更新套装项目（如果提供了items）
        if (isset($data['items']) || isset($_POST['items'])) {
            $items = $data['items'] ?? $_POST['items'];
            
            // 删除现有项目
            $delete_sql = "DELETE FROM dishware_set_items WHERE set_id = ?";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([$id]);
            
            // 添加新项目
            if (is_array($items)) {
                foreach ($items as $index => $item) {
                    $item_sql = "INSERT INTO dishware_set_items (set_id, dishware_id, quantity_in_set, sort_order) 
                                 VALUES (?, ?, ?, ?)";
                    $item_stmt = $pdo->prepare($item_sql);
                    $item_stmt->execute([
                        $id,
                        $item['dishware_id'],
                        $item['quantity_in_set'] ?? 1,
                        $index + 1
                    ]);
                }
            }
        }
        
        $pdo->commit();
        sendResponse(true, "更新套装信息成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新套装信息失败：" . $e->getMessage());
    }
}

// 删除套装
function deleteDishwareSet() {
    global $pdo, $data;
    
    // 支持从POST和DELETE请求中获取ID
    $id = $data['id'] ?? $_POST['id'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少套装ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 软删除套装（设置为不活跃）
        $sql = "UPDATE dishware_sets SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $pdo->commit();
        sendResponse(true, "删除套装成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "删除套装失败：" . $e->getMessage());
    }
}

// 更新套装库存
function updateSetStock() {
    global $pdo, $data;
    
    // 支持从POST和PUT请求中获取数据
    $set_id = $data['set_id'] ?? $_POST['set_id'] ?? '';
    
    if (empty($set_id)) {
        sendResponse(false, "缺少套装ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取所有活跃的餐厅店面
        $restaurants_sql = "SELECT id FROM dishware_restaurant_locations WHERE is_active = 1";
        $restaurants_stmt = $pdo->prepare($restaurants_sql);
        $restaurants_stmt->execute();
        $restaurants = $restaurants_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 更新每个餐厅店面的库存
        // 获取所有餐厅店面，按显示顺序
        $restaurants_list = $pdo->query("SELECT id FROM dishware_restaurant_locations WHERE is_active = 1 ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
        
        // 如果提供了按顺序的数组
        if (isset($data['restaurant_quantities']) && is_array($data['restaurant_quantities'])) {
            foreach ($restaurants_list as $index => $restaurant) {
                $quantity = $data['restaurant_quantities'][$index] ?? 0;
                
                $sql = "INSERT INTO dishware_set_stock_by_restaurant (set_id, restaurant_id, quantity) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        quantity = VALUES(quantity),
                        last_updated = CURRENT_TIMESTAMP";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$set_id, $restaurant['id'], $quantity]);
            }
        } else {
            // 向后兼容：支持按餐厅ID的格式
            foreach ($restaurants_list as $restaurant) {
                $restaurant_id = $restaurant['id'];
                $quantity = $data['restaurant_' . $restaurant_id . '_quantity'] ?? $_POST['restaurant_' . $restaurant_id . '_quantity'] ?? 0;
                
                $sql = "INSERT INTO dishware_set_stock_by_restaurant (set_id, restaurant_id, quantity) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        quantity = VALUES(quantity),
                        last_updated = CURRENT_TIMESTAMP";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$set_id, $restaurant_id, $quantity]);
            }
        }
        
        // 为了向后兼容，同时更新旧表
        $wenhua_quantity = $data['wenhua_quantity'] ?? $_POST['wenhua_quantity'] ?? 0;
        $central_quantity = $data['central_quantity'] ?? $_POST['central_quantity'] ?? 0;
        $j1_quantity = $data['j1_quantity'] ?? $_POST['j1_quantity'] ?? 0;
        $j2_quantity = $data['j2_quantity'] ?? $_POST['j2_quantity'] ?? 0;
        $j3_quantity = $data['j3_quantity'] ?? $_POST['j3_quantity'] ?? 0;
        
        try {
            $old_sql = "INSERT INTO dishware_set_stock (set_id, wenhua_quantity, central_quantity, j1_quantity, j2_quantity, j3_quantity) 
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        wenhua_quantity = VALUES(wenhua_quantity),
                        central_quantity = VALUES(central_quantity),
                        j1_quantity = VALUES(j1_quantity),
                        j2_quantity = VALUES(j2_quantity),
                        j3_quantity = VALUES(j3_quantity),
                        last_updated = CURRENT_TIMESTAMP";
            $old_stmt = $pdo->prepare($old_sql);
            $old_stmt->execute([$set_id, $wenhua_quantity, $central_quantity, $j1_quantity, $j2_quantity, $j3_quantity]);
        } catch (PDOException $e) {
            // 如果旧表不存在，忽略
        }
        
        $pdo->commit();
        sendResponse(true, "更新套装库存成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新套装库存失败：" . $e->getMessage());
    }
}

// 获取套装破损记录
function getSetBreakRecords() {
    global $pdo;
    
    $shop_type = $_GET['shop_type'] ?? '';
    
    if (empty($shop_type)) {
        sendResponse(false, "缺少店铺类型参数");
    }
    
    try {
        $has_set_archive = false;
        try {
            $pdo->query("SELECT 1 FROM dishware_set_break_records_archive LIMIT 1");
            $has_set_archive = true;
        } catch (PDOException $e) {
            $has_set_archive = false;
        }

        if ($has_set_archive) {
            $base_sql = "
                SELECT id, set_id, shop_type, break_quantity, unit_price, total_price, break_date, recorded_by, created_at, updated_at, NULL as archive_ym
                FROM dishware_set_break_records
                UNION ALL
                SELECT id, set_id, shop_type, break_quantity, unit_price, total_price, break_date, recorded_by, created_at, updated_at, archive_ym
                FROM dishware_set_break_records_archive
            ";
        } else {
            $base_sql = "
                SELECT id, set_id, shop_type, break_quantity, unit_price, total_price, break_date, recorded_by, created_at, updated_at, NULL as archive_ym
                FROM dishware_set_break_records
            ";
        }

        $sql = "SELECT dsbr.*, ds.set_name, ds.set_code
                FROM ($base_sql) AS dsbr
                LEFT JOIN dishware_sets ds ON dsbr.set_id = ds.id
                WHERE dsbr.shop_type = ? AND ds.is_active = 1
                ORDER BY dsbr.break_date DESC, dsbr.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$shop_type]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 添加当前库存字段和计算总价
        foreach ($results as &$result) {
            // 获取套装库存信息
            $stock_sql = "SELECT * FROM dishware_set_stock WHERE set_id = ?";
            $stock_stmt = $pdo->prepare($stock_sql);
            $stock_stmt->execute([$result['set_id']]);
            $stock = $stock_stmt->fetch(PDO::FETCH_ASSOC);
            
            $stock_field = $shop_type . '_quantity';
            $result['current_stock'] = $stock[$stock_field] ?? 0;
            
            // 如果没有存储的单价，使用套装单价
            // 如果历史存储单价是空，使用套装实时单价
            if (empty($result['unit_price']) && $result['unit_price'] !== '0.00' && $result['unit_price'] !== 0) {
                $result['unit_price'] = $result['set_price'] ?? 0;
            }
            
            // 如果历史 total_price 是空才进行计算，否则相信当初存下来的数据
            if (empty($result['total_price']) && $result['total_price'] !== '0.00' && $result['total_price'] !== 0) {
                $result['total_price'] = $result['unit_price'] * $result['break_quantity'];
            }
        }
        
        sendResponse(true, "获取套装破损记录成功", $results);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取套装破损记录失败：" . $e->getMessage());
    }
}

// 添加套装破损记录
function addSetBreakRecord() {
    global $pdo, $data;
    
    // 支持从POST和JSON数据中获取数据
    $postData = !empty($data) ? $data : $_POST;
    
    $required_fields = ['set_id', 'shop_type', 'break_quantity'];
    foreach ($required_fields as $field) {
        if (empty($postData[$field])) {
            sendResponse(false, "缺少必填字段：$field");
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取套装价格
        $set_sql = "SELECT set_price FROM dishware_sets WHERE id = ? AND is_active = 1";
        $set_stmt = $pdo->prepare($set_sql);
        $set_stmt->execute([$postData['set_id']]);
        $set = $set_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$set) {
            sendResponse(false, "套装不存在");
        }
        
        // 插入破损记录
        $unit_price = $postData['unit_price'] ?? $set['set_price'];
        $total_price = $unit_price * $postData['break_quantity'];
        $break_date = $postData['break_date'] ?? null;
        if (empty($break_date)) {
            throw new Exception("缺少日期参数 (break_date)");
        }
        
        $sql = "INSERT INTO dishware_set_break_records (set_id, shop_type, break_quantity, unit_price, total_price, break_date, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $postData['set_id'],
            $postData['shop_type'],
            $postData['break_quantity'],
            $unit_price,
            $total_price,
            $break_date,
            $postData['recorded_by'] ?? 'system'
        ]);
        
        $record_id = $pdo->lastInsertId();
        
        // 更新对应店铺的库存（减少库存）
        $stock_field = $postData['shop_type'] . '_quantity';
        $update_sql = "UPDATE dishware_set_stock SET 
                       $stock_field = GREATEST(0, $stock_field - ?),
                       last_updated = CURRENT_TIMESTAMP
                       WHERE set_id = ?";
        
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            $postData['break_quantity'],
            $postData['set_id']
        ]);
        
        $pdo->commit();
        sendResponse(true, "添加套装破损记录成功", ['id' => $record_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "添加套装破损记录失败：" . $e->getMessage());
    }
}

// 更新套装破损记录
function updateSetBreakRecord() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    $break_quantity = $data['break_quantity'] ?? $_POST['break_quantity'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取原记录信息
        $old_sql = "SELECT * FROM dishware_set_break_records WHERE id = ?";
        $old_stmt = $pdo->prepare($old_sql);
        $old_stmt->execute([$id]);
        $old_record = $old_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$old_record) {
            sendResponse(false, "记录不存在");
        }
        
        // 更新记录
        $unit_price = $data['unit_price'] ?? $_POST['unit_price'] ?? $old_record['unit_price'];
        $total_price = $unit_price * $break_quantity;
        
        $sql = "UPDATE dishware_set_break_records SET 
                break_quantity = ?, unit_price = ?, total_price = ?, 
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $break_quantity,
            $unit_price,
            $total_price,
            $id
        ]);
        
        // 更新库存（调整差异）
        $quantity_diff = $break_quantity - $old_record['break_quantity'];
        if ($quantity_diff != 0) {
            $stock_field = $old_record['shop_type'] . '_quantity';
            $update_sql = "UPDATE dishware_set_stock SET 
                           $stock_field = GREATEST(0, $stock_field - ?),
                           last_updated = CURRENT_TIMESTAMP
                           WHERE set_id = ?";
            
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                $quantity_diff,
                $old_record['set_id']
            ]);
        }
        
        $pdo->commit();
        sendResponse(true, "更新套装破损记录成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新套装破损记录失败：" . $e->getMessage());
    }
}

// 删除套装破损记录
function deleteSetBreakRecord() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取记录信息
        $select_sql = "SELECT * FROM dishware_set_break_records WHERE id = ?";
        $select_stmt = $pdo->prepare($select_sql);
        $select_stmt->execute([$id]);
        $record = $select_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            sendResponse(false, "记录不存在");
        }
        
        // 删除记录
        $delete_sql = "DELETE FROM dishware_set_break_records WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id]);
        
        // 恢复库存（增加库存）
        $stock_field = $record['shop_type'] . '_quantity';
        $update_sql = "UPDATE dishware_set_stock SET 
                       $stock_field = $stock_field + ?,
                       last_updated = CURRENT_TIMESTAMP
                       WHERE set_id = ?";
        
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            $record['break_quantity'],
            $record['set_id']
        ]);
        
        $pdo->commit();
        sendResponse(true, "删除套装破损记录成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "删除套装破损记录失败：" . $e->getMessage());
    }
}

// 从套装中移除碗碟
function removeItemFromSet() {
    global $pdo, $data;
    
    // 支持从POST和JSON数据中获取数据
    $postData = !empty($data) ? $data : $_POST;
    
    $set_id = $postData['set_id'] ?? '';
    $dishware_id = $postData['dishware_id'] ?? '';
    
    if (empty($set_id)) {
        sendResponse(false, "缺少套装ID");
    }
    
    if (empty($dishware_id)) {
        sendResponse(false, "缺少碗碟ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 检查套装是否存在
        $check_set_sql = "SELECT id FROM dishware_sets WHERE id = ? AND is_active = 1";
        $check_set_stmt = $pdo->prepare($check_set_sql);
        $check_set_stmt->execute([$set_id]);
        $set_exists = $check_set_stmt->fetch();
        
        if (!$set_exists) {
            $pdo->rollBack();
            sendResponse(false, "套装不存在");
        }
        
        // 从套装中移除碗碟
        $remove_sql = "DELETE FROM dishware_set_items WHERE set_id = ? AND dishware_id = ?";
        $remove_stmt = $pdo->prepare($remove_sql);
        $remove_stmt->execute([$set_id, $dishware_id]);
        
        // 检查套装中是否还有其他碗碟
        $count_sql = "SELECT COUNT(*) as item_count FROM dishware_set_items WHERE set_id = ?";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute([$set_id]);
        $item_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['item_count'];
        
        // 如果套装中没有碗碟了，删除套装
        if ($item_count == 0) {
            $delete_set_sql = "UPDATE dishware_sets SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $delete_set_stmt = $pdo->prepare($delete_set_sql);
            $delete_set_stmt->execute([$set_id]);
        }
        
        $pdo->commit();
        sendResponse(true, "已从套装中移除碗碟" . ($item_count == 0 ? "，套装已自动删除" : ""));
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "从套装中移除碗碟失败：" . $e->getMessage());
    }
}

// 获取所有餐厅店面
function getRestaurants() {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM dishware_restaurant_locations WHERE is_active = 1 ORDER BY display_order, id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendResponse(true, "获取餐厅店面列表成功", $results);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取餐厅店面列表失败：" . $e->getMessage());
    }
}

// 添加餐厅店面
function addRestaurant() {
    global $pdo, $data;
    
    $postData = !empty($data) ? $data : $_POST;
    
    $name = $postData['name'] ?? '';
    
    if (empty($name)) {
        sendResponse(false, "缺少餐厅店面名称");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取当前最大的 display_order，新添加的放在最后
        $max_order_sql = "SELECT COALESCE(MAX(display_order), 0) as max_order FROM dishware_restaurant_locations";
        $max_order_stmt = $pdo->prepare($max_order_sql);
        $max_order_stmt->execute();
        $max_order_result = $max_order_stmt->fetch(PDO::FETCH_ASSOC);
        $new_display_order = ($max_order_result['max_order'] ?? 0) + 1;
        
        // 插入餐厅店面（不再需要 code）
        $sql = "INSERT INTO dishware_restaurant_locations (name, display_order) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $new_display_order]);
        
        $restaurant_id = $pdo->lastInsertId();
        
        // 为所有现有的碗碟创建该餐厅店面的库存记录
        $dishware_sql = "SELECT id FROM dishware_info";
        $dishware_stmt = $pdo->prepare($dishware_sql);
        $dishware_stmt->execute();
        $dishwares = $dishware_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($dishwares as $dishware) {
            $stock_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                         VALUES (?, ?, 0)";
            $stock_stmt = $pdo->prepare($stock_sql);
            $stock_stmt->execute([$dishware['id'], $restaurant_id]);
        }
        
        // 为所有现有的套装创建该餐厅店面的库存记录
        $set_sql = "SELECT id FROM dishware_sets WHERE is_active = 1";
        $set_stmt = $pdo->prepare($set_sql);
        $set_stmt->execute();
        $sets = $set_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sets as $set) {
            $set_stock_sql = "INSERT INTO dishware_set_stock_by_restaurant (set_id, restaurant_id, quantity) 
                             VALUES (?, ?, 0)";
            $set_stock_stmt = $pdo->prepare($set_stock_sql);
            $set_stock_stmt->execute([$set['id'], $restaurant_id]);
        }
        
        $pdo->commit();
        sendResponse(true, "添加餐厅店面成功", ['id' => $restaurant_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "添加餐厅店面失败：" . $e->getMessage());
    }
}

// 更新餐厅店面
function updateRestaurant() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    $name = $data['name'] ?? $_POST['name'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少餐厅店面ID");
    }
    
    if (empty($name)) {
        sendResponse(false, "缺少餐厅店面名称");
    }
    
    try {
        $sql = "UPDATE dishware_restaurant_locations SET 
                name = ?, 
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $id]);
        
        sendResponse(true, "更新餐厅店面成功");
        
    } catch (PDOException $e) {
        sendResponse(false, "更新餐厅店面失败：" . $e->getMessage());
    }
}

// 更新餐厅店面显示顺序（用于拖拽排序）
function updateRestaurantOrder() {
    global $pdo, $data;
    
    $postData = !empty($data) ? $data : $_POST;
    $orders = $postData['orders'] ?? [];
    
    if (empty($orders) || !is_array($orders)) {
        sendResponse(false, "缺少顺序数据");
    }
    
    try {
        $pdo->beginTransaction();
        
        foreach ($orders as $index => $restaurant_id) {
            $display_order = $index + 1; // 从1开始
            $sql = "UPDATE dishware_restaurant_locations SET 
                    display_order = ?, 
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$display_order, $restaurant_id]);
        }
        
        $pdo->commit();
        sendResponse(true, "更新餐厅店面顺序成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新餐厅店面顺序失败：" . $e->getMessage());
    }
}

// 删除餐厅店面（软删除）
function deleteRestaurant() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少餐厅店面ID");
    }
    
    try {
        // 检查是否有库存数据
        $stock_check = "SELECT COUNT(*) as count FROM dishware_stock_by_restaurant WHERE restaurant_id = ?";
        $stock_stmt = $pdo->prepare($stock_check);
        $stock_stmt->execute([$id]);
        $stock_result = $stock_stmt->fetch(PDO::FETCH_ASSOC);
        
        $set_stock_check = "SELECT COUNT(*) as count FROM dishware_set_stock_by_restaurant WHERE restaurant_id = ?";
        $set_stock_stmt = $pdo->prepare($set_stock_check);
        $set_stock_stmt->execute([$id]);
        $set_stock_result = $set_stock_stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_stock = ($stock_result['count'] ?? 0) + ($set_stock_result['count'] ?? 0);
        
        if ($total_stock > 0) {
            // 软删除（设置为不活跃）
            $sql = "UPDATE dishware_restaurant_locations SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            sendResponse(true, "餐厅店面已停用（存在库存数据）");
        } else {
            // 如果没有库存数据，可以硬删除
            $pdo->beginTransaction();
            
            // 删除关联的库存记录
            $delete_stock_sql = "DELETE FROM dishware_stock_by_restaurant WHERE restaurant_id = ?";
            $delete_stock_stmt = $pdo->prepare($delete_stock_sql);
            $delete_stock_stmt->execute([$id]);
            
            $delete_set_stock_sql = "DELETE FROM dishware_set_stock_by_restaurant WHERE restaurant_id = ?";
            $delete_set_stock_stmt = $pdo->prepare($delete_set_stock_sql);
            $delete_set_stock_stmt->execute([$id]);
            
            // 删除餐厅店面
            $delete_sql = "DELETE FROM dishware_restaurant_locations WHERE id = ?";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([$id]);
            
            $pdo->commit();
            sendResponse(true, "删除餐厅店面成功");
        }
        
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, "删除餐厅店面失败：" . $e->getMessage());
    }
}

// 获取转卖记录
function getTransferRecords() {
    global $pdo;
    
    $shop_type = $_GET['shop_type'] ?? '';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    
    if (empty($shop_type)) {
        sendResponse(false, "缺少店铺类型参数");
    }
    
    try {
        // 首先根据shop_type找到对应的restaurant_id
        $restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
        $restaurant_stmt = $pdo->prepare($restaurant_sql);
        $restaurant_stmt->execute([$shop_type]);
        $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);
        $restaurant_id = $restaurant ? $restaurant['id'] : null;
        
        $sql = "SELECT dtr.*, di.product_name, di.code_number, di.category, di.size, di.photo_path, di.unit_price,
                       from_r.name as from_restaurant_name, to_r.name as to_restaurant_name
                FROM dishware_transfer_records dtr
                LEFT JOIN dishware_info di ON dtr.dishware_id = di.id
                LEFT JOIN dishware_restaurant_locations from_r ON dtr.from_restaurant_id = from_r.id
                LEFT JOIN dishware_restaurant_locations to_r ON dtr.to_restaurant_id = to_r.id
                WHERE (
                    (dtr.from_shop_type = ? AND dtr.record_type = 'out') OR 
                    (dtr.to_shop_type = ? AND dtr.record_type = 'in')
                )";
        $params = [$shop_type, $shop_type];
        if (!empty($start_date) && !empty($end_date)) {
            $sql .= " AND dtr.transfer_date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        $sql .= " ORDER BY dtr.transfer_date ASC, dtr.created_at ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 添加当前库存字段
        foreach ($results as &$result) {
            if ($restaurant_id) {
                $stock_sql = "SELECT quantity FROM dishware_stock_by_restaurant 
                             WHERE dishware_id = ? AND restaurant_id = ?";
                $stock_stmt = $pdo->prepare($stock_sql);
                $stock_stmt->execute([$result['dishware_id'], $restaurant_id]);
                $stock = $stock_stmt->fetch(PDO::FETCH_ASSOC);
                $result['current_stock'] = $stock ? $stock['quantity'] : 0;
            } else {
                $result['current_stock'] = 0;
            }
        }
        
        sendResponse(true, "获取转卖记录成功", $results);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取转卖记录失败：" . $e->getMessage());
    }
}

// 添加转卖记录
function addTransferRecord() {
    global $pdo, $data;
    
    $postData = $data ?? $_POST;
    
    $dishware_id = $postData['dishware_id'] ?? '';
    $from_shop_type = $postData['from_shop_type'] ?? '';
    $to_shop_type = $postData['to_shop_type'] ?? '';
    $quantity = $postData['quantity'] ?? 0;
    $unit_price = $postData['unit_price'] ?? 0;
    $transfer_date = $postData['transfer_date'] ?? null;
    if (empty($transfer_date)) {
        sendResponse(false, "缺少转移日期 (transfer_date)");
    }
    
    if (empty($dishware_id) || empty($from_shop_type) || empty($to_shop_type) || $quantity <= 0) {
        sendResponse(false, "缺少必要参数");
    }
    
    if ($from_shop_type === $to_shop_type) {
        sendResponse(false, "转出和转入餐厅不能相同");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取餐厅ID
        $from_restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
        $from_restaurant_stmt = $pdo->prepare($from_restaurant_sql);
        $from_restaurant_stmt->execute([$from_shop_type]);
        $from_restaurant = $from_restaurant_stmt->fetch(PDO::FETCH_ASSOC);
        
        $to_restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
        $to_restaurant_stmt = $pdo->prepare($to_restaurant_sql);
        $to_restaurant_stmt->execute([$to_shop_type]);
        $to_restaurant = $to_restaurant_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$from_restaurant || !$to_restaurant) {
            sendResponse(false, "找不到指定的餐厅");
        }
        
        $total_price = $quantity * $unit_price;
        
        // 插入出货记录
        $out_sql = "INSERT INTO dishware_transfer_records 
                   (dishware_id, from_restaurant_id, to_restaurant_id, from_shop_type, to_shop_type, 
                    quantity, unit_price, total_price, transfer_date, record_type, recorded_by)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'out', ?)";
        $out_stmt = $pdo->prepare($out_sql);
        $out_stmt->execute([
            $dishware_id,
            $from_restaurant['id'],
            $to_restaurant['id'],
            $from_shop_type,
            $to_shop_type,
            $quantity,
            $unit_price,
            $total_price,
            $transfer_date,
            $postData['recorded_by'] ?? 'system'
        ]);
        
        $out_record_id = $pdo->lastInsertId();
        
        // 插入进货记录（关联出货记录）
        $in_sql = "INSERT INTO dishware_transfer_records 
                  (dishware_id, from_restaurant_id, to_restaurant_id, from_shop_type, to_shop_type, 
                   quantity, unit_price, total_price, transfer_date, record_type, related_record_id, recorded_by)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'in', ?, ?)";
        $in_stmt = $pdo->prepare($in_sql);
        $in_stmt->execute([
            $dishware_id,
            $from_restaurant['id'],
            $to_restaurant['id'],
            $from_shop_type,
            $to_shop_type,
            $quantity,
            $unit_price,
            $total_price,
            $transfer_date,
            $out_record_id,
            $postData['recorded_by'] ?? 'system'
        ]);
        
        $in_record_id = $pdo->lastInsertId();
        
        // 更新库存：转出餐厅减少，转入餐厅增加
        // 转出餐厅减少
        $update_from_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                           VALUES (?, ?, GREATEST(0, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) - ?))
                           ON DUPLICATE KEY UPDATE 
                           quantity = GREATEST(0, quantity - ?),
                           last_updated = CURRENT_TIMESTAMP";
        $update_from_stmt = $pdo->prepare($update_from_sql);
        $update_from_stmt->execute([
            $dishware_id,
            $from_restaurant['id'],
            $dishware_id,
            $from_restaurant['id'],
            $quantity,
            $quantity
        ]);
        
        // 转入餐厅增加
        $update_to_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                         VALUES (?, ?, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) + ?)
                         ON DUPLICATE KEY UPDATE 
                         quantity = quantity + ?,
                         last_updated = CURRENT_TIMESTAMP";
        $update_to_stmt = $pdo->prepare($update_to_sql);
        $update_to_stmt->execute([
            $dishware_id,
            $to_restaurant['id'],
            $dishware_id,
            $to_restaurant['id'],
            $quantity,
            $quantity
        ]);
        
        $pdo->commit();
        sendResponse(true, "添加转卖记录成功", ['out_record_id' => $out_record_id, 'in_record_id' => $in_record_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "添加转卖记录失败：" . $e->getMessage());
    }
}

// 更新转卖记录（只允许更新出货记录）
function updateTransferRecord() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    $quantity = $data['quantity'] ?? $_POST['quantity'] ?? '';
    $unit_price = $data['unit_price'] ?? $_POST['unit_price'] ?? '';
    $to_shop_type = $data['to_shop_type'] ?? $_POST['to_shop_type'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取原记录信息
        $old_sql = "SELECT * FROM dishware_transfer_records WHERE id = ? AND record_type = 'out'";
        $old_stmt = $pdo->prepare($old_sql);
        $old_stmt->execute([$id]);
        $old_record = $old_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$old_record) {
            sendResponse(false, "记录不存在或不允许编辑");
        }
        
        // 如果改变了转入餐厅，需要更新
        $new_to_shop_type = $to_shop_type ?: $old_record['to_shop_type'];
        $new_quantity = $quantity ?: $old_record['quantity'];
        $new_unit_price = $unit_price ?: $old_record['unit_price'];
        $new_total_price = $new_quantity * $new_unit_price;
        
        // 获取新的转入餐厅ID（如果改变了）
        $to_restaurant_sql = "SELECT id FROM dishware_restaurant_locations WHERE LOWER(name) = LOWER(?) AND is_active = 1";
        $to_restaurant_stmt = $pdo->prepare($to_restaurant_sql);
        $to_restaurant_stmt->execute([$new_to_shop_type]);
        $new_to_restaurant = $to_restaurant_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$new_to_restaurant) {
            sendResponse(false, "找不到指定的转入餐厅");
        }
        
        // 恢复原库存
        $quantity_diff = $old_record['quantity'] - $new_quantity;
        $to_restaurant_changed = ($new_to_shop_type !== $old_record['to_shop_type']);
        
        // 恢复转出餐厅库存（增加）
        $restore_from_sql = "UPDATE dishware_stock_by_restaurant 
                            SET quantity = quantity + ?,
                                last_updated = CURRENT_TIMESTAMP
                            WHERE dishware_id = ? AND restaurant_id = ?";
        $restore_from_stmt = $pdo->prepare($restore_from_sql);
        $restore_from_stmt->execute([$old_record['quantity'], $old_record['dishware_id'], $old_record['from_restaurant_id']]);
        
        // 恢复原转入餐厅库存（减少）
        $restore_to_sql = "UPDATE dishware_stock_by_restaurant 
                          SET quantity = GREATEST(0, quantity - ?),
                              last_updated = CURRENT_TIMESTAMP
                          WHERE dishware_id = ? AND restaurant_id = ?";
        $restore_to_stmt = $pdo->prepare($restore_to_sql);
        $restore_to_stmt->execute([$old_record['quantity'], $old_record['dishware_id'], $old_record['to_restaurant_id']]);
        
        // 更新出货记录
        $update_out_sql = "UPDATE dishware_transfer_records SET 
                          to_restaurant_id = ?,
                          to_shop_type = ?,
                          quantity = ?,
                          unit_price = ?,
                          total_price = ?,
                          updated_at = CURRENT_TIMESTAMP
                          WHERE id = ?";
        $update_out_stmt = $pdo->prepare($update_out_sql);
        $update_out_stmt->execute([
            $new_to_restaurant['id'],
            $new_to_shop_type,
            $new_quantity,
            $new_unit_price,
            $new_total_price,
            $id
        ]);
        
        // 更新关联的进货记录
        $in_record_sql = "SELECT id FROM dishware_transfer_records WHERE related_record_id = ?";
        $in_record_stmt = $pdo->prepare($in_record_sql);
        $in_record_stmt->execute([$id]);
        $in_record = $in_record_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($in_record) {
            $update_in_sql = "UPDATE dishware_transfer_records SET 
                            to_restaurant_id = ?,
                            to_shop_type = ?,
                            quantity = ?,
                            unit_price = ?,
                            total_price = ?,
                            updated_at = CURRENT_TIMESTAMP
                            WHERE id = ?";
            $update_in_stmt = $pdo->prepare($update_in_sql);
            $update_in_stmt->execute([
                $new_to_restaurant['id'],
                $new_to_shop_type,
                $new_quantity,
                $new_unit_price,
                $new_total_price,
                $in_record['id']
            ]);
        }
        
        // 更新新库存
        // 转出餐厅减少
        $update_from_sql = "UPDATE dishware_stock_by_restaurant 
                          SET quantity = GREATEST(0, quantity - ?),
                              last_updated = CURRENT_TIMESTAMP
                          WHERE dishware_id = ? AND restaurant_id = ?";
        $update_from_stmt = $pdo->prepare($update_from_sql);
        $update_from_stmt->execute([$new_quantity, $old_record['dishware_id'], $old_record['from_restaurant_id']]);
        
        // 新转入餐厅增加
        $update_to_sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                         VALUES (?, ?, COALESCE((SELECT quantity FROM dishware_stock_by_restaurant WHERE dishware_id = ? AND restaurant_id = ?), 0) + ?)
                         ON DUPLICATE KEY UPDATE 
                         quantity = quantity + ?,
                         last_updated = CURRENT_TIMESTAMP";
        $update_to_stmt = $pdo->prepare($update_to_sql);
        $update_to_stmt->execute([
            $old_record['dishware_id'],
            $new_to_restaurant['id'],
            $old_record['dishware_id'],
            $new_to_restaurant['id'],
            $new_quantity,
            $new_quantity
        ]);
        
        $pdo->commit();
        sendResponse(true, "更新转卖记录成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新转卖记录失败：" . $e->getMessage());
    }
}

// 删除转卖记录（只允许删除出货记录，会自动删除关联的进货记录）
function deleteTransferRecord() {
    global $pdo, $data;
    
    $id = $data['id'] ?? $_POST['id'] ?? '';
    
    if (empty($id)) {
        sendResponse(false, "缺少记录ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 获取记录信息
        $record_sql = "SELECT * FROM dishware_transfer_records WHERE id = ?";
        $record_stmt = $pdo->prepare($record_sql);
        $record_stmt->execute([$id]);
        $record = $record_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            sendResponse(false, "记录不存在");
        }
        
        // 只允许删除出货记录
        if ($record['record_type'] !== 'out') {
            sendResponse(false, "只能删除出货记录");
        }
        
        // 恢复库存
        // 转出餐厅增加
        $restore_from_sql = "UPDATE dishware_stock_by_restaurant 
                            SET quantity = quantity + ?,
                                last_updated = CURRENT_TIMESTAMP
                            WHERE dishware_id = ? AND restaurant_id = ?";
        $restore_from_stmt = $pdo->prepare($restore_from_sql);
        $restore_from_stmt->execute([$record['quantity'], $record['dishware_id'], $record['from_restaurant_id']]);
        
        // 转入餐厅减少
        $restore_to_sql = "UPDATE dishware_stock_by_restaurant 
                          SET quantity = GREATEST(0, quantity - ?),
                              last_updated = CURRENT_TIMESTAMP
                          WHERE dishware_id = ? AND restaurant_id = ?";
        $restore_to_stmt = $pdo->prepare($restore_to_sql);
        $restore_to_stmt->execute([$record['quantity'], $record['dishware_id'], $record['to_restaurant_id']]);
        
        // 删除关联的进货记录
        $delete_in_sql = "DELETE FROM dishware_transfer_records WHERE related_record_id = ?";
        $delete_in_stmt = $pdo->prepare($delete_in_sql);
        $delete_in_stmt->execute([$id]);
        
        // 删除出货记录
        $delete_out_sql = "DELETE FROM dishware_transfer_records WHERE id = ?";
        $delete_out_stmt = $pdo->prepare($delete_out_sql);
        $delete_out_stmt->execute([$id]);
        
        $pdo->commit();
        sendResponse(true, "删除转卖记录成功");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "删除转卖记录失败：" . $e->getMessage());
    }
}

// 获取碗碟的套装信息
function getDishwareSetInfo() {
    global $pdo;
    
    $dishware_id = $_GET['dishware_id'] ?? '';
    
    if (empty($dishware_id)) {
        sendResponse(false, "缺少碗碟ID");
        return;
    }
    
    try {
        // 查找该碗碟所属的套装
        $sql = "SELECT ds.id as set_id, ds.set_name, ds.set_code, 
                       GROUP_CONCAT(
                           CONCAT(di.id, ':', di.product_name, ' (', di.code_number, ')') 
                           ORDER BY dsi.sort_order 
                           SEPARATOR '|'
                       ) as set_members
                FROM dishware_set_items dsi
                INNER JOIN dishware_sets ds ON dsi.set_id = ds.id
                INNER JOIN dishware_set_items dsi2 ON ds.id = dsi2.set_id
                INNER JOIN dishware_info di ON dsi2.dishware_id = di.id
                WHERE dsi.dishware_id = ? AND ds.is_active = 1
                GROUP BY ds.id, ds.set_name, ds.set_code";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dishware_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // 解析套装成员
            $members = [];
            if ($result['set_members']) {
                $memberStrings = explode('|', $result['set_members']);
                foreach ($memberStrings as $memberStr) {
                    $parts = explode(':', $memberStr, 2);
                    if (count($parts) === 2) {
                        $members[] = [
                            'id' => $parts[0],
                            'display' => $parts[1]
                        ];
                    }
                }
            }
            $result['members'] = $members;
            unset($result['set_members']);
        }
        
        sendResponse(true, "获取套装信息成功", $result);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取套装信息失败：" . $e->getMessage());
    }
}

// 更新碗碟的套装关系
function updateDishwareSetRelation() {
    global $pdo, $data;
    
    $postData = !empty($data) ? $data : $_POST;
    
    $dishware_id = $postData['dishware_id'] ?? '';
    $member_ids = $postData['member_ids'] ?? []; // 要组成套装的碗碟ID数组
    
    if (empty($dishware_id)) {
        sendResponse(false, "缺少碗碟ID");
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // 如果member_ids为空，表示从套装中移除
        if (empty($member_ids) || !is_array($member_ids)) {
            // 查找该碗碟所属的套装
            $findSetSql = "SELECT set_id FROM dishware_set_items WHERE dishware_id = ?";
            $findSetStmt = $pdo->prepare($findSetSql);
            $findSetStmt->execute([$dishware_id]);
            $setItems = $findSetStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 从所有套装中移除该碗碟
            foreach ($setItems as $item) {
                $deleteSql = "DELETE FROM dishware_set_items WHERE set_id = ? AND dishware_id = ?";
                $deleteStmt = $pdo->prepare($deleteSql);
                $deleteStmt->execute([$item['set_id'], $dishware_id]);
                
                // 检查套装是否还有其他成员，如果没有则删除套装
                $checkSql = "SELECT COUNT(*) as count FROM dishware_set_items WHERE set_id = ?";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([$item['set_id']]);
                $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($count == 0) {
                    $deleteSetSql = "DELETE FROM dishware_sets WHERE id = ?";
                    $deleteSetStmt = $pdo->prepare($deleteSetSql);
                    $deleteSetStmt->execute([$item['set_id']]);
                }
            }
            
            $pdo->commit();
            sendResponse(true, "已从套装中移除");
            return;
        }
        
        // 确保当前碗碟也在member_ids中
        if (!in_array($dishware_id, $member_ids)) {
            $member_ids[] = $dishware_id;
        }
        
        // 去重并排序
        $member_ids = array_unique(array_map('intval', $member_ids));
        sort($member_ids);
        
        // 检查是否只剩下当前碗碟（只有一个成员且是当前碗碟）
        if (count($member_ids) === 1 && $member_ids[0] == $dishware_id) {
            // 查找该碗碟所属的套装并删除
            $findSetSql = "SELECT set_id FROM dishware_set_items WHERE dishware_id = ?";
            $findSetStmt = $pdo->prepare($findSetSql);
            $findSetStmt->execute([$dishware_id]);
            $setItems = $findSetStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($setItems as $item) {
                // 删除套装中的所有成员
                $deleteSql = "DELETE FROM dishware_set_items WHERE set_id = ?";
                $deleteStmt = $pdo->prepare($deleteSql);
                $deleteStmt->execute([$item['set_id']]);
                
                // 删除套装
                $deleteSetSql = "DELETE FROM dishware_sets WHERE id = ?";
                $deleteSetStmt = $pdo->prepare($deleteSetSql);
                $deleteSetStmt->execute([$item['set_id']]);
            }
            
            $pdo->commit();
            sendResponse(true, "套装已自动删除（只剩一个成员）", ['set_id' => null, 'deleted' => true]);
            return;
        }
        
        // 检查这些碗碟是否已经属于其他套装
        $checkSql = "SELECT DISTINCT set_id FROM dishware_set_items WHERE dishware_id IN (" . 
                    implode(',', array_fill(0, count($member_ids), '?')) . ")";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute($member_ids);
        $existingSets = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
        
        $set_id = null;
        
        if (!empty($existingSets)) {
            // 如果所有碗碟都属于同一个套装，使用该套装
            if (count($existingSets) === 1) {
                $set_id = $existingSets[0];
            } else {
                // 如果属于多个套装，合并到第一个套装
                $set_id = $existingSets[0];
                // 从其他套装中移除这些碗碟
                for ($i = 1; $i < count($existingSets); $i++) {
                    $deleteSql = "DELETE FROM dishware_set_items WHERE set_id = ? AND dishware_id IN (" . 
                                implode(',', array_fill(0, count($member_ids), '?')) . ")";
                    $deleteStmt = $pdo->prepare($deleteSql);
                    $deleteStmt->execute(array_merge([$existingSets[$i]], $member_ids));
                }
            }
        }
        
        // 如果没有套装，创建新套装
        if (!$set_id) {
            // 生成套装编号
            $setCode = 'SET' . time();
            
            // 获取第一个碗碟的信息作为套装名称
            $firstDishwareSql = "SELECT product_name, code_number FROM dishware_info WHERE id = ?";
            $firstDishwareStmt = $pdo->prepare($firstDishwareSql);
            $firstDishwareStmt->execute([$member_ids[0]]);
            $firstDishware = $firstDishwareStmt->fetch(PDO::FETCH_ASSOC);
            
            $setName = ($firstDishware['product_name'] ?? '套装') . ' 套装';
            
            // 计算套装总价
            $priceSql = "SELECT SUM(unit_price) as total_price FROM dishware_info WHERE id IN (" . 
                       implode(',', array_fill(0, count($member_ids), '?')) . ")";
            $priceStmt = $pdo->prepare($priceSql);
            $priceStmt->execute($member_ids);
            $priceResult = $priceStmt->fetch(PDO::FETCH_ASSOC);
            $setPrice = $priceResult['total_price'] ?? 0;
            
            $insertSetSql = "INSERT INTO dishware_sets (set_name, set_code, set_price, description) VALUES (?, ?, ?, ?)";
            $insertSetStmt = $pdo->prepare($insertSetSql);
            $insertSetStmt->execute([$setName, $setCode, $setPrice, '']);
            $set_id = $pdo->lastInsertId();
        }
        
        // 删除该套装中现有的所有成员
        $deleteSql = "DELETE FROM dishware_set_items WHERE set_id = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$set_id]);
        
        // 添加所有成员到套装
        foreach ($member_ids as $index => $member_id) {
            $insertSql = "INSERT INTO dishware_set_items (set_id, dishware_id, quantity_in_set, sort_order) VALUES (?, ?, 1, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$set_id, $member_id, $index + 1]);
        }
        
        // 更新套装总价
        $updatePriceSql = "UPDATE dishware_sets SET set_price = (
            SELECT SUM(unit_price) FROM dishware_info WHERE id IN (
                SELECT dishware_id FROM dishware_set_items WHERE set_id = ?
            )
        ) WHERE id = ?";
        $updatePriceStmt = $pdo->prepare($updatePriceSql);
        $updatePriceStmt->execute([$set_id, $set_id]);
        
        $pdo->commit();
        sendResponse(true, "套装关系更新成功", ['set_id' => $set_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新套装关系失败：" . $e->getMessage());
    }
}
?>
