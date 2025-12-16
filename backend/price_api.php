<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理 OPTIONS 请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 数据库连接配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$username = 'u857194726_kunzzgroup';
$password = 'Kholdings1688@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '数据库连接失败: ' . $e->getMessage()
    ]);
    exit();
}

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

function normalizePriceValue($value) {
    if ($value === '' || $value === null) {
        return null;
    }
    if (!is_numeric($value)) {
        return null;
    }
    return floatval($value);
}

function normalizeFoodName($name) {
    if ($name === null) {
        return '';
    }
    return strtoupper(trim($name));
}

// 路由处理
try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            $action = $_GET['action'] ?? 'food';
            if ($action === 'batch-save') {
                handleBatchSave($pdo, $input);
            } else {
                handlePost($pdo, $input);
            }
            break;
        case 'PUT':
            handlePut($pdo, $input);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            throw new Exception('不支持的请求方法');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 处理 GET 请求
function handleGet($pdo) {
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'restaurants') {
        // 获取所有餐厅列表（包含中文名和英文名）
        $stmt = $pdo->query("SELECT id, name_cn, name_en, COALESCE(name_cn, name_en) as name FROM restaurants ORDER BY id ASC");
        $restaurants = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $restaurants
        ]);
    } elseif ($action === 'list') {
        // 获取价格对比列表 - 使用新的表结构
        // 首先获取所有餐厅（包含中文名和英文名）
        $restaurantsStmt = $pdo->query("SELECT id, name_cn, name_en, COALESCE(name_cn, name_en) as name FROM restaurants ORDER BY id ASC");
        $restaurants = $restaurantsStmt->fetchAll();
        
        // 获取所有唯一的食品（通过 food_name, food_type 组合）
        // 使用 MIN(id) 来获取最早的记录ID，用于排序
        $sql = "SELECT food_name, food_type, MIN(id) as min_id
                FROM restaurant_foods 
                WHERE 1=1";
        $params = [];
        
        // 搜索过滤
        if (!empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $sql .= " AND food_name LIKE ?";
            $params[] = $search;
        }
        
        // 类型过滤
        if (!empty($_GET['type'])) {
            $sql .= " AND food_type = ?";
            $params[] = $_GET['type'];
        }
        
        $sql .= " GROUP BY food_name, food_type";
        $sql .= " ORDER BY food_type ASC, min_id ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $foods = $stmt->fetchAll();
        
        // 构建价格对比数据
        $result = [];
        foreach ($foods as $food) {
            $item = [
                'food_name' => $food['food_name'],
                'food_type' => $food['food_type'] ?? '',
                'prices' => []
            ];
            
            // 获取每个餐厅的价格和记录ID
            foreach ($restaurants as $restaurant) {
                $priceStmt = $pdo->prepare("
                    SELECT id, price
                    FROM restaurant_foods 
                    WHERE restaurant_id = ? 
                    AND food_name = ? 
                    AND COALESCE(food_type, '') = COALESCE(?, '')
                    LIMIT 1
                ");
                $priceStmt->execute([
                    $restaurant['id'],
                    $food['food_name'],
                    $food['food_type'] ?? null
                ]);
                $priceData = $priceStmt->fetch();
                if ($priceData) {
                    $item['prices'][$restaurant['id']] = [
                        'id' => $priceData['id'],
                        'price' => $priceData['price']
                    ];
                } else {
                    $item['prices'][$restaurant['id']] = null;
                }
            }
            
            $result[] = $item;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'restaurants' => $restaurants,
            'count' => count($result)
        ]);
    } elseif ($action === 'categories') {
        // 获取所有类别
        $stmt = $pdo->query("SELECT DISTINCT category FROM restaurant_foods WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
    } elseif ($action === 'types') {
        // 获取所有类型
        $stmt = $pdo->query("SELECT DISTINCT food_type FROM restaurant_foods WHERE food_type IS NOT NULL AND food_type != '' ORDER BY food_type");
        $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'data' => $types
        ]);
    }
}

// 处理 POST 请求
function handlePost($pdo, $input) {
    $action = $_GET['action'] ?? 'food';
    
    if ($action === 'restaurant') {
        // 新增餐厅
        if (empty($input['name_cn'])) {
            throw new Exception('餐厅中文名称不能为空');
        }
        
        if (empty($input['name_en'])) {
            throw new Exception('餐厅英文名称不能为空');
        }
        
        // 转换为大写
        $nameCn = strtoupper(trim($input['name_cn']));
        $nameEn = strtoupper(trim($input['name_en']));
        
        // 检查餐厅中文名称是否已存在
        $checkStmt = $pdo->prepare("SELECT id FROM restaurants WHERE name_cn = ?");
        $checkStmt->execute([$nameCn]);
        if ($checkStmt->fetch()) {
            throw new Exception('餐厅中文名称已存在');
        }
        
        // 检查餐厅英文名称是否已存在
        $checkStmt = $pdo->prepare("SELECT id FROM restaurants WHERE name_en = ?");
        $checkStmt->execute([$nameEn]);
        if ($checkStmt->fetch()) {
            throw new Exception('餐厅英文名称已存在');
        }
        
        // 插入新餐厅（保存为大写）
        $stmt = $pdo->prepare("INSERT INTO restaurants (name_cn, name_en) VALUES (?, ?)");
        $stmt->execute([$nameCn, $nameEn]);
        
        $newId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT id, name_cn, name_en, COALESCE(name_cn, name_en) as name FROM restaurants WHERE id = ?");
        $stmt->execute([$newId]);
        $newRestaurant = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => '餐厅添加成功',
            'data' => $newRestaurant
        ]);
    } else {
        // 新增食品记录
        if (empty($input['food_name'])) {
            throw new Exception('货品名称不能为空');
        }
        
        $foodName = normalizeFoodName($input['food_name']);
        if ($foodName === '') {
            throw new Exception('货品名称不能为空');
        }
        $input['food_name'] = $foodName;
        
        if (empty($input['restaurant_id'])) {
            throw new Exception('餐厅ID不能为空');
        }
        
        // 检查是否已存在相同的记录
        $checkStmt = $pdo->prepare("
            SELECT id FROM restaurant_foods 
            WHERE restaurant_id = ? 
            AND food_name = ? 
            AND COALESCE(food_type, '') = COALESCE(?, '')
        ");
        $checkStmt->execute([
            $input['restaurant_id'],
            $foodName,
            $input['food_type'] ?? null
        ]);
        
        $existing = $checkStmt->fetch();
        if ($existing) {
            // 更新现有记录
            $updateStmt = $pdo->prepare("
                UPDATE restaurant_foods 
                SET price = ? 
                WHERE id = ?
            ");
            $price = normalizePriceValue($input['price'] ?? null);
            $updateStmt->execute([$price, $existing['id']]);
            
            $stmt = $pdo->prepare("SELECT * FROM restaurant_foods WHERE id = ?");
            $stmt->execute([$existing['id']]);
            $updatedRecord = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => '记录更新成功',
                'data' => $updatedRecord
            ]);
        } else {
            // 插入新记录
            $stmt = $pdo->prepare("
                INSERT INTO restaurant_foods (restaurant_id, food_name, food_type, price) 
                VALUES (?, ?, ?, ?)
            ");
            $price = normalizePriceValue($input['price'] ?? null);
            $stmt->execute([
                $input['restaurant_id'],
                $foodName,
                $input['food_type'] ?? null,
                $price
            ]);
            
            $newId = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("SELECT * FROM restaurant_foods WHERE id = ?");
            $stmt->execute([$newId]);
            $newRecord = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => '记录添加成功',
                'data' => $newRecord
            ]);
        }
    }
}

function handleRestaurantUpdate($pdo, $input) {
    $id = $_GET['id'] ?? null;
    if (empty($id)) {
        throw new Exception('餐厅ID不能为空');
    }
    
    if (empty($input['name_cn'])) {
        throw new Exception('餐厅中文名称不能为空');
    }
    
    if (empty($input['name_en'])) {
        throw new Exception('餐厅英文名称不能为空');
    }
    
    $nameCn = strtoupper(trim($input['name_cn']));
    $nameEn = strtoupper(trim($input['name_en']));
    
    if ($nameCn === '' || $nameEn === '') {
        throw new Exception('餐厅名称不能为空');
    }
    
    // 检查中文名是否重复
    $checkStmt = $pdo->prepare("SELECT id FROM restaurants WHERE name_cn = ? AND id != ?");
    $checkStmt->execute([$nameCn, $id]);
    if ($checkStmt->fetch()) {
        throw new Exception('餐厅中文名称已存在');
    }
    
    // 检查英文名是否重复
    $checkStmt = $pdo->prepare("SELECT id FROM restaurants WHERE name_en = ? AND id != ?");
    $checkStmt->execute([$nameEn, $id]);
    if ($checkStmt->fetch()) {
        throw new Exception('餐厅英文名称已存在');
    }
    
    $stmt = $pdo->prepare("UPDATE restaurants SET name_cn = ?, name_en = ? WHERE id = ?");
    $stmt->execute([$nameCn, $nameEn, $id]);
    
    $stmt = $pdo->prepare("SELECT id, name_cn, name_en, COALESCE(name_cn, name_en) as name FROM restaurants WHERE id = ?");
    $stmt->execute([$id]);
    $updated = $stmt->fetch();
    
    if (!$updated) {
        throw new Exception('餐厅不存在');
    }
    
    echo json_encode([
        'success' => true,
        'message' => '餐厅更新成功',
        'data' => $updated
    ]);
}

// 处理 PUT 请求 - 更新价格
function handlePut($pdo, $input) {
    $action = $_GET['action'] ?? 'food';
    if ($action === 'restaurant') {
        handleRestaurantUpdate($pdo, $input);
        return;
    }
    
    if (empty($input['food_name'])) {
        throw new Exception('货品名称不能为空');
    }
    
    $foodName = normalizeFoodName($input['food_name']);
    if ($foodName === '') {
        throw new Exception('货品名称不能为空');
    }
    
    if (empty($input['restaurant_id'])) {
        throw new Exception('餐厅ID不能为空');
    }
    
    // 查找或创建记录
    $checkStmt = $pdo->prepare("
        SELECT id FROM restaurant_foods 
        WHERE restaurant_id = ? 
        AND food_name = ? 
        AND COALESCE(food_type, '') = COALESCE(?, '')
    ");
    $checkStmt->execute([
        $input['restaurant_id'],
        $foodName,
        $input['food_type'] ?? null
    ]);
    
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // 更新现有记录
        $price = normalizePriceValue($input['price'] ?? null);
        $updateStmt = $pdo->prepare("UPDATE restaurant_foods SET price = ? WHERE id = ?");
        $updateStmt->execute([$price, $existing['id']]);
        
        $stmt = $pdo->prepare("SELECT * FROM restaurant_foods WHERE id = ?");
        $stmt->execute([$existing['id']]);
        $updatedRecord = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => '记录更新成功',
            'data' => $updatedRecord
        ]);
    } else {
        // 创建新记录
        $stmt = $pdo->prepare("
            INSERT INTO restaurant_foods (restaurant_id, food_name, food_type, price) 
            VALUES (?, ?, ?, ?)
        ");
        $price = normalizePriceValue($input['price'] ?? null);
        $stmt->execute([
            $input['restaurant_id'],
            $foodName,
            $input['food_type'] ?? null,
            $price
        ]);
        
        $newId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT * FROM restaurant_foods WHERE id = ?");
        $stmt->execute([$newId]);
        $newRecord = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => '记录创建成功',
            'data' => $newRecord
        ]);
    }
}

// 处理 DELETE 请求
function handleDelete($pdo) {
    $action = $_GET['action'] ?? 'food';
    $id = $_GET['id'] ?? null;
    
    if (empty($id)) {
        throw new Exception('ID不能为空');
    }
    
    if ($action === 'restaurant') {
        // 删除餐厅（同时删除相关的食品记录）
        $pdo->beginTransaction();
        try {
            // 先删除相关的食品记录
            $stmt = $pdo->prepare("DELETE FROM restaurant_foods WHERE restaurant_id = ?");
            $stmt->execute([$id]);
            
            // 删除餐厅
            $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => '餐厅删除成功'
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } else {
        // 删除食品记录
        $stmt = $pdo->prepare("SELECT id FROM restaurant_foods WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            throw new Exception('记录不存在');
        }
        
        $stmt = $pdo->prepare("DELETE FROM restaurant_foods WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => '记录删除成功'
        ]);
    }
}

// 处理批量保存请求
function handleBatchSave($pdo, $input) {
    if (empty($input['records']) || !is_array($input['records'])) {
        throw new Exception('记录数据不能为空');
    }
    
    $results = [];
    $errors = [];
    
    foreach ($input['records'] as $record) {
        try {
            if (empty($record['food_name'])) {
                $errors[] = '货品名称不能为空';
                continue;
            }
            
            $foodName = normalizeFoodName($record['food_name']);
            if ($foodName === '') {
                $errors[] = '货品名称不能为空';
                continue;
            }
            
            if (empty($record['restaurant_id'])) {
                $errors[] = '餐厅ID不能为空';
                continue;
            }
            
            // 检查是否已存在相同的记录
            $checkStmt = $pdo->prepare("
                SELECT id FROM restaurant_foods 
                WHERE restaurant_id = ? 
                AND food_name = ? 
                AND COALESCE(food_type, '') = COALESCE(?, '')
            ");
            $checkStmt->execute([
                $record['restaurant_id'],
                $foodName,
                $record['food_type'] ?? null
            ]);
            
            $existing = $checkStmt->fetch();
            $price = normalizePriceValue($record['price'] ?? null);
            
            if ($existing) {
                // 更新现有记录
                $updateStmt = $pdo->prepare("UPDATE restaurant_foods SET price = ? WHERE id = ?");
                $updateStmt->execute([$price, $existing['id']]);
                $results[] = ['id' => $existing['id'], 'action' => 'updated'];
            } else {
                // 插入新记录
                $insertStmt = $pdo->prepare("
                    INSERT INTO restaurant_foods (restaurant_id, food_name, food_type, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $record['restaurant_id'],
                    $foodName,
                    $record['food_type'] ?? null,
                    $price
                ]);
                $results[] = ['id' => $pdo->lastInsertId(), 'action' => 'created'];
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => count($errors) === 0,
        'message' => count($errors) > 0 ? '部分记录保存失败' : '批量保存成功',
        'saved_count' => count($results),
        'error_count' => count($errors),
        'errors' => $errors
    ]);
}
