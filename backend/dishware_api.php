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

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

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
        case 'sets':
            getDishwareSets();
            break;
        case 'set_detail':
            getDishwareSetDetail();
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
                       CASE 
                           WHEN dsi.dishware_id IS NOT NULL THEN 1 
                           ELSE 0 
                       END as is_in_set
                FROM dishware_info di
                LEFT JOIN dishware_set_items dsi ON di.id = dsi.dishware_id
                LEFT JOIN dishware_sets dsets ON dsi.set_id = dsets.id AND dsets.is_active = 1
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
        $sql = "SELECT di.*, ds.wenhua_quantity, ds.central_quantity, ds.j1_quantity, ds.j2_quantity, ds.j3_quantity, ds.total_quantity
                FROM dishware_info di
                LEFT JOIN dishware_stock ds ON di.id = ds.dishware_id
                WHERE di.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            sendResponse(false, "碗碟不存在");
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
    $dishware_id = $data['dishware_id'] ?? $_POST['dishware_id'] ?? '';
    
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
        
        // 如果提供了按顺序的数组
        if (isset($data['restaurant_quantities']) && is_array($data['restaurant_quantities'])) {
            foreach ($restaurants_list as $index => $restaurant) {
                $quantity = $data['restaurant_quantities'][$index] ?? 0;
                
                $sql = "INSERT INTO dishware_stock_by_restaurant (dishware_id, restaurant_id, quantity) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        quantity = VALUES(quantity),
                        last_updated = CURRENT_TIMESTAMP";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$dishware_id, $restaurant['id'], $quantity]);
            }
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
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        sendResponse(false, "不支持的文件格式");
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
        sendResponse(true, "照片上传成功", ['photo_path' => $file_path]);
    } else {
        sendResponse(false, "照片保存失败");
    }
}

// 获取打破记录
function getBreakRecords() {
    global $pdo;
    
    $shop_type = $_GET['shop_type'] ?? '';
    
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
        
        $sql = "SELECT dbr.*, di.product_name, di.code_number, di.category, di.size, di.photo_path, di.unit_price
                FROM dishware_break_records dbr
                LEFT JOIN dishware_info di ON dbr.dishware_id = di.id
                WHERE dbr.shop_type = ?
                ORDER BY dbr.break_date DESC, dbr.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$shop_type]);
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
            
            // 如果没有存储的单价，使用碗碟信息中的单价
            if (empty($result['unit_price'])) {
                $result['unit_price'] = $result['unit_price'] ?? 0;
            }
            
            // 计算总价
            $result['total_price'] = $result['unit_price'] * $result['break_quantity'];
        }
        
        sendResponse(true, "获取破损记录成功", $results);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取破损记录失败：" . $e->getMessage());
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
        
        // 插入破损记录
        $unit_price = $postData['unit_price'] ?? 0;
        $total_price = $unit_price * $postData['break_quantity'];
        $break_date = $postData['break_date'] ?? date('Y-m-d');
        
        $sql = "INSERT INTO dishware_break_records (dishware_id, shop_type, break_quantity, unit_price, total_price, break_date, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $postData['dishware_id'],
            $postData['shop_type'],
            $postData['break_quantity'],
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
        }
        
        // 为了向后兼容，同时更新旧表（如果存在）
        $stock_field = $postData['shop_type'] . '_quantity';
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
            // 如果旧表不存在，忽略错误
        }
        
        $pdo->commit();
        sendResponse(true, "添加破损记录成功", ['id' => $record_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
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
        
        // 更新记录
        $unit_price = $data['unit_price'] ?? $_POST['unit_price'] ?? $old_record['unit_price'];
        $total_price = $unit_price * $break_quantity;
        
        // 检查是否更新了产品ID（dishware_id）
        $new_dishware_id = $data['dishware_id'] ?? $_POST['dishware_id'] ?? $old_record['dishware_id'];
        $dishware_id_changed = ($new_dishware_id != $old_record['dishware_id']);
        
        $sql = "UPDATE dishware_break_records SET 
                break_quantity = ?, unit_price = ?, total_price = ?";
        
        // 如果产品ID改变了，也更新它
        if ($dishware_id_changed) {
            $sql .= ", dishware_id = ?";
        }
        
        $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        $params = [$break_quantity, $unit_price, $total_price];
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
        $sql = "SELECT dsbr.*, ds.set_name, ds.set_code
                FROM dishware_set_break_records dsbr
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
            if (empty($result['unit_price'])) {
                $result['unit_price'] = $result['set_price'] ?? 0;
            }
            
            // 计算总价
            $result['total_price'] = $result['unit_price'] * $result['break_quantity'];
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
        $break_date = $postData['break_date'] ?? date('Y-m-d');
        
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
?>
