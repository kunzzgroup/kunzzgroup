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

function normalizeMaterialName($name) {
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
            $action = $_GET['action'] ?? 'material';
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
    
    if ($action === 'supplies') {
        // 获取所有供应商列表
        $stmt = $pdo->query("SELECT id, name FROM supply ORDER BY id ASC");
        $supplies = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $supplies
        ]);
    } elseif ($action === 'list') {
        // 获取价格对比列表 - 使用新的表结构
        // 首先获取所有供应商
        $suppliesStmt = $pdo->query("SELECT id, name FROM supply ORDER BY id ASC");
        $supplies = $suppliesStmt->fetchAll();
        
        // 获取所有唯一的材料（通过 material_name, material_type 组合）
        // 使用 MIN(id) 来获取最早的记录ID，用于排序
        $sql = "SELECT material_name, material_type, MIN(id) as min_id
                FROM supply_material 
                WHERE 1=1";
        $params = [];
        
        // 搜索过滤
        if (!empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $sql .= " AND material_name LIKE ?";
            $params[] = $search;
        }
        
        // 类型过滤
        if (!empty($_GET['type'])) {
            $sql .= " AND material_type = ?";
            $params[] = $_GET['type'];
        }
        
        $sql .= " GROUP BY material_name, material_type";
        // 按照预定义的类型顺序排序
        $sql .= " ORDER BY CASE material_type
            WHEN '肉类' THEN 1
            WHEN '干货日本' THEN 2
            WHEN '冷冻' THEN 3
            WHEN '生鱼片' THEN 4
            WHEN '蔬菜' THEN 5
            WHEN '水果' THEN 6
            WHEN '干货' THEN 7
            WHEN '冰淇淋' THEN 8
            WHEN '甜品' THEN 9
            ELSE 999
        END ASC, min_id ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $materials = $stmt->fetchAll();
        
        // 构建价格对比数据
        $result = [];
        foreach ($materials as $material) {
            $item = [
                'material_name' => $material['material_name'],
                'material_type' => $material['material_type'] ?? '',
                'prices' => []
            ];
            
            // 获取每个供应商的价格和记录ID
            foreach ($supplies as $supply) {
                $priceStmt = $pdo->prepare("
                    SELECT id, price
                    FROM supply_material 
                    WHERE supply_id = ? 
                    AND material_name = ? 
                    AND COALESCE(material_type, '') = COALESCE(?, '')
                    LIMIT 1
                ");
                $priceStmt->execute([
                    $supply['id'],
                    $material['material_name'],
                    $material['material_type'] ?? null
                ]);
                $priceData = $priceStmt->fetch();
                if ($priceData) {
                    $item['prices'][$supply['id']] = [
                        'id' => $priceData['id'],
                        'price' => $priceData['price']
                    ];
                } else {
                    $item['prices'][$supply['id']] = null;
                }
            }
            
            $result[] = $item;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'supplies' => $supplies,
            'count' => count($result)
        ]);
    } elseif ($action === 'categories') {
        // 获取所有类别
        $stmt = $pdo->query("SELECT DISTINCT category FROM supply_material WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
    } elseif ($action === 'types') {
        // 获取所有类型
        $stmt = $pdo->query("SELECT DISTINCT material_type FROM supply_material WHERE material_type IS NOT NULL AND material_type != '' ORDER BY material_type");
        $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'data' => $types
        ]);
    }
}

// 处理 POST 请求
function handlePost($pdo, $input) {
    $action = $_GET['action'] ?? 'material';
    
    if ($action === 'supply') {
        // 新增供应商
        if (empty($input['name'])) {
            throw new Exception('供应商名称不能为空');
        }
        
        // 转换为大写
        $name = strtoupper(trim($input['name']));
        
        // 检查供应商名称是否已存在
        $checkStmt = $pdo->prepare("SELECT id FROM supply WHERE name = ?");
        $checkStmt->execute([$name]);
        if ($checkStmt->fetch()) {
            throw new Exception('供应商名称已存在');
        }
        
        // 插入新供应商（保存为大写）
        $stmt = $pdo->prepare("INSERT INTO supply (name) VALUES (?)");
        $stmt->execute([$name]);
        
        $newId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT id, name FROM supply WHERE id = ?");
        $stmt->execute([$newId]);
        $newSupply = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => '供应商添加成功',
            'data' => $newSupply
        ]);
    } else {
        // 新增材料记录
        if (empty($input['material_name'])) {
            throw new Exception('材料名称不能为空');
        }
        
        $materialName = normalizeMaterialName($input['material_name']);
        if ($materialName === '') {
            throw new Exception('材料名称不能为空');
        }
        $input['material_name'] = $materialName;
        
        if (empty($input['supply_id'])) {
            throw new Exception('供应商ID不能为空');
        }
        
        // 检查是否已存在相同的记录
        $checkStmt = $pdo->prepare("
            SELECT id FROM supply_material 
            WHERE supply_id = ? 
            AND material_name = ? 
            AND COALESCE(material_type, '') = COALESCE(?, '')
        ");
        $checkStmt->execute([
            $input['supply_id'],
            $materialName,
            $input['material_type'] ?? null
        ]);
        
        $existing = $checkStmt->fetch();
        if ($existing) {
            // 更新现有记录
            $updateStmt = $pdo->prepare("
                UPDATE supply_material 
                SET price = ? 
                WHERE id = ?
            ");
            $price = normalizePriceValue($input['price'] ?? null);
            $updateStmt->execute([$price, $existing['id']]);
            
            $stmt = $pdo->prepare("SELECT * FROM supply_material WHERE id = ?");
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
                INSERT INTO supply_material (supply_id, material_name, material_type, price) 
                VALUES (?, ?, ?, ?)
            ");
            $price = normalizePriceValue($input['price'] ?? null);
            $stmt->execute([
                $input['supply_id'],
                $materialName,
                $input['material_type'] ?? null,
                $price
            ]);
            
            $newId = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("SELECT * FROM supply_material WHERE id = ?");
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

function handleSupplyUpdate($pdo, $input) {
    $id = $_GET['id'] ?? null;
    if (empty($id)) {
        throw new Exception('供应商ID不能为空');
    }
    
    if (empty($input['name'])) {
        throw new Exception('供应商名称不能为空');
    }
    
    $name = strtoupper(trim($input['name']));
    
    if ($name === '') {
        throw new Exception('供应商名称不能为空');
    }
    
    // 检查名称是否重复
    $checkStmt = $pdo->prepare("SELECT id FROM supply WHERE name = ? AND id != ?");
    $checkStmt->execute([$name, $id]);
    if ($checkStmt->fetch()) {
        throw new Exception('供应商名称已存在');
    }
    
    $stmt = $pdo->prepare("UPDATE supply SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    
    $stmt = $pdo->prepare("SELECT id, name FROM supply WHERE id = ?");
    $stmt->execute([$id]);
    $updated = $stmt->fetch();
    
    if (!$updated) {
        throw new Exception('供应商不存在');
    }
    
    echo json_encode([
        'success' => true,
        'message' => '供应商更新成功',
        'data' => $updated
    ]);
}

// 处理 PUT 请求 - 更新价格
function handlePut($pdo, $input) {
    $action = $_GET['action'] ?? 'material';
    if ($action === 'supply') {
        handleSupplyUpdate($pdo, $input);
        return;
    }
    
    if (empty($input['material_name'])) {
        throw new Exception('材料名称不能为空');
    }
    
    $materialName = normalizeMaterialName($input['material_name']);
    if ($materialName === '') {
        throw new Exception('材料名称不能为空');
    }
    
    if (empty($input['supply_id'])) {
        throw new Exception('供应商ID不能为空');
    }
    
    // 查找或创建记录
    $checkStmt = $pdo->prepare("
        SELECT id FROM supply_material 
        WHERE supply_id = ? 
        AND material_name = ? 
        AND COALESCE(material_type, '') = COALESCE(?, '')
    ");
    $checkStmt->execute([
        $input['supply_id'],
        $materialName,
        $input['material_type'] ?? null
    ]);
    
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // 更新现有记录
        $price = normalizePriceValue($input['price'] ?? null);
        $updateStmt = $pdo->prepare("UPDATE supply_material SET price = ? WHERE id = ?");
        $updateStmt->execute([$price, $existing['id']]);
        
        $stmt = $pdo->prepare("SELECT * FROM supply_material WHERE id = ?");
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
            INSERT INTO supply_material (supply_id, material_name, material_type, price) 
            VALUES (?, ?, ?, ?)
        ");
        $price = normalizePriceValue($input['price'] ?? null);
        $stmt->execute([
            $input['supply_id'],
            $materialName,
            $input['material_type'] ?? null,
            $price
        ]);
        
        $newId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT * FROM supply_material WHERE id = ?");
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
    $action = $_GET['action'] ?? 'material';
    $id = $_GET['id'] ?? null;
    
    if (empty($id)) {
        throw new Exception('ID不能为空');
    }
    
    if ($action === 'supply') {
        // 删除供应商（同时删除相关的材料记录）
        $pdo->beginTransaction();
        try {
            // 先删除相关的材料记录
            $stmt = $pdo->prepare("DELETE FROM supply_material WHERE supply_id = ?");
            $stmt->execute([$id]);
            
            // 删除供应商
            $stmt = $pdo->prepare("DELETE FROM supply WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => '供应商删除成功'
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } else {
        // 删除材料记录
        $stmt = $pdo->prepare("SELECT id FROM supply_material WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            throw new Exception('记录不存在');
        }
        
        $stmt = $pdo->prepare("DELETE FROM supply_material WHERE id = ?");
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
            if (empty($record['material_name'])) {
                $errors[] = '材料名称不能为空';
                continue;
            }
            
            $materialName = normalizeMaterialName($record['material_name']);
            if ($materialName === '') {
                $errors[] = '材料名称不能为空';
                continue;
            }
            
            if (empty($record['supply_id'])) {
                $errors[] = '供应商ID不能为空';
                continue;
            }
            
            // 检查是否已存在相同的记录
            $checkStmt = $pdo->prepare("
                SELECT id FROM supply_material 
                WHERE supply_id = ? 
                AND material_name = ? 
                AND COALESCE(material_type, '') = COALESCE(?, '')
            ");
            $checkStmt->execute([
                $record['supply_id'],
                $materialName,
                $record['material_type'] ?? null
            ]);
            
            $existing = $checkStmt->fetch();
            $price = normalizePriceValue($record['price'] ?? null);
            
            if ($existing) {
                // 更新现有记录
                $updateStmt = $pdo->prepare("UPDATE supply_material SET price = ? WHERE id = ?");
                $updateStmt->execute([$price, $existing['id']]);
                $results[] = ['id' => $existing['id'], 'action' => 'updated'];
            } else {
                // 插入新记录
                $insertStmt = $pdo->prepare("
                    INSERT INTO supply_material (supply_id, material_name, material_type, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $record['supply_id'],
                    $materialName,
                    $record['material_type'] ?? null,
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

