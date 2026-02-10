<?php
require_once dirname(__DIR__) . '/core/init.php';
ob_start();
header("Content-Type: application/json; charset=utf-8");
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
error_reporting(E_ALL);

// 设置错误处理，确保错误时也返回JSON
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "服务器错误：$message (在 $file 的第 $line 行)"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}, E_ALL);

// 设置异常处理
set_exception_handler(function($exception) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "未捕获的异常：" . $exception->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

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
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $e->getMessage()]);
    exit;
}

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$action = '';

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
} else if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}

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
switch ($action) {
    case 'get_criteria':
        getCriteria($pdo);
        break;

    case 'get_standards':
        getStandards($pdo);
        break;
    
    case 'save_standards':
        saveStandards($pdo);
        break;
    
    case 'list_forms':
        listForms($pdo);
        break;
    
    case 'get_form':
        getForm($pdo);
        break;
    
    case 'create_form':
        createForm($pdo);
        break;
    
    case 'update_form':
        updateForm($pdo);
        break;
    
    case 'delete_form':
        deleteForm($pdo);
        break;
    
    default:
        sendResponse(false, "无效的操作请求");
        break;
}

/**
 * 获取考核指标配置
 */
function getCriteria($pdo) {
    try {
        $department = $_GET['department'] ?? '';
        
        if (!$department) {
            sendResponse(false, "请提供部门参数");
            return;
        }
        
        // 检查表是否存在
        try {
            $checkTable = $pdo->query("SHOW TABLES LIKE 'evaluation_criteria_config'");
            if ($checkTable->rowCount() == 0) {
                sendResponse(false, "考核指标配置表不存在，请先执行SQL文件创建表结构");
                return;
            }
        } catch (PDOException $e) {
            sendResponse(false, "检查表失败：" . $e->getMessage());
            return;
        }
        
        $sql = "SELECT * FROM evaluation_criteria_config 
                WHERE department = :department AND is_active = 1 
                ORDER BY criteria_order ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['department' => $department]);
        $criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendResponse(true, "获取成功", $criteria);
    } catch (PDOException $e) {
        sendResponse(false, "获取指标失败：" . $e->getMessage());
    } catch (Exception $e) {
        sendResponse(false, "获取指标失败：" . $e->getMessage());
    }
}

/**
 * 列出表单
 */
function listForms($pdo) {
    $restaurant = $_GET['restaurant'] ?? '';
    $department = $_GET['department'] ?? '';
    
    try {
        $sql = "SELECT * FROM evaluation_forms WHERE 1=1";
        $params = [];
        
        if ($restaurant) {
            $sql .= " AND restaurant = :restaurant";
            $params['restaurant'] = $restaurant;
        }
        
        if ($department) {
            $sql .= " AND department = :department";
            $params['department'] = $department;
        }
        
        $sql .= " ORDER BY evaluation_date DESC, created_at DESC LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendResponse(true, "获取成功", $forms);
    } catch (PDOException $e) {
        sendResponse(false, "获取表单列表失败：" . $e->getMessage());
    }
}

/**
 * 获取单个表单详情
 */
function getForm($pdo) {
    $formId = $_GET['form_id'] ?? '';
    
    if (!$formId) {
        sendResponse(false, "请提供表单ID");
    }
    
    try {
        // 获取表单基本信息
        $sql = "SELECT * FROM evaluation_forms WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $formId]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$form) {
            sendResponse(false, "表单不存在");
        }
        
        // 获取表单详情
        $sql = "SELECT * FROM evaluation_form_details WHERE form_id = :form_id ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['form_id' => $formId]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $form['details'] = $details;
        
        sendResponse(true, "获取成功", $form);
    } catch (PDOException $e) {
        sendResponse(false, "获取表单失败：" . $e->getMessage());
    }
}

/**
 * 创建新表单
 */
function createForm($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $formName = $input['form_name'] ?? '';
    $department = $input['department'] ?? '';
    $restaurant = $input['restaurant'] ?? 'J1';
    $evaluatorName = $input['evaluator_name'] ?? '';
    $evaluationDate = $input['evaluation_date'] ?? '';
    $details = $input['details'] ?? [];
    
    if (!$department || !$evaluatorName || !$evaluationDate) {
        sendResponse(false, "请填写所有必填字段");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 插入表单基本信息
        $sql = "INSERT INTO evaluation_forms 
                (form_name, department, restaurant, evaluator_name, evaluation_date) 
                VALUES (:form_name, :department, :restaurant, :evaluator_name, :evaluation_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'form_name' => $formName ?: ($department . ' - ' . $evaluationDate),
            'department' => $department,
            'restaurant' => $restaurant,
            'evaluator_name' => $evaluatorName,
            'evaluation_date' => $evaluationDate
        ]);
        
        $formId = $pdo->lastInsertId();
        
        // 插入表单详情
        if (!empty($details)) {
            $sql = "INSERT INTO evaluation_form_details 
                    (form_id, employee_id, employee_name, criteria_1, criteria_2, criteria_3, criteria_4, criteria_5, criteria_6, criteria_7, notes) 
                    VALUES (:form_id, :employee_id, :employee_name, :criteria_1, :criteria_2, :criteria_3, :criteria_4, :criteria_5, :criteria_6, :criteria_7, :notes)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($details as $detail) {
                $stmt->execute([
                    'form_id' => $formId,
                    'employee_id' => $detail['employee_id'] ?? null,
                    'employee_name' => $detail['employee_name'] ?? '',
                    'criteria_1' => $detail['criteria_1'] ?? '',
                    'criteria_2' => $detail['criteria_2'] ?? '',
                    'criteria_3' => $detail['criteria_3'] ?? '',
                    'criteria_4' => $detail['criteria_4'] ?? '',
                    'criteria_5' => $detail['criteria_5'] ?? '',
                    'criteria_6' => $detail['criteria_6'] ?? '',
                    'criteria_7' => $detail['criteria_7'] ?? '',
                    'notes' => $detail['notes'] ?? ''
                ]);
            }
        }
        
        $pdo->commit();
        sendResponse(true, "表单创建成功", ['form_id' => $formId, 'id' => $formId]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "创建表单失败：" . $e->getMessage());
    }
}

/**
 * 更新表单
 */
function updateForm($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $formId = $input['form_id'] ?? '';
    $formName = $input['form_name'] ?? '';
    $department = $input['department'] ?? '';
    $restaurant = $input['restaurant'] ?? 'J1';
    $evaluatorName = $input['evaluator_name'] ?? '';
    $evaluationDate = $input['evaluation_date'] ?? '';
    $details = $input['details'] ?? [];
    
    if (!$formId || !$department || !$evaluatorName || !$evaluationDate) {
        sendResponse(false, "请填写所有必填字段");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 更新表单基本信息
        $sql = "UPDATE evaluation_forms 
                SET form_name = :form_name, department = :department, restaurant = :restaurant, 
                    evaluator_name = :evaluator_name, evaluation_date = :evaluation_date 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'form_name' => $formName ?: ($department . ' - ' . $evaluationDate),
            'department' => $department,
            'restaurant' => $restaurant,
            'evaluator_name' => $evaluatorName,
            'evaluation_date' => $evaluationDate,
            'id' => $formId
        ]);
        
        // 删除旧的详情
        $sql = "DELETE FROM evaluation_form_details WHERE form_id = :form_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['form_id' => $formId]);
        
        // 插入新的详情
        if (!empty($details)) {
            $sql = "INSERT INTO evaluation_form_details 
                    (form_id, employee_id, employee_name, criteria_1, criteria_2, criteria_3, criteria_4, criteria_5, criteria_6, criteria_7, notes) 
                    VALUES (:form_id, :employee_id, :employee_name, :criteria_1, :criteria_2, :criteria_3, :criteria_4, :criteria_5, :criteria_6, :criteria_7, :notes)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($details as $detail) {
                $stmt->execute([
                    'form_id' => $formId,
                    'employee_id' => $detail['employee_id'] ?? null,
                    'employee_name' => $detail['employee_name'] ?? '',
                    'criteria_1' => $detail['criteria_1'] ?? '',
                    'criteria_2' => $detail['criteria_2'] ?? '',
                    'criteria_3' => $detail['criteria_3'] ?? '',
                    'criteria_4' => $detail['criteria_4'] ?? '',
                    'criteria_5' => $detail['criteria_5'] ?? '',
                    'criteria_6' => $detail['criteria_6'] ?? '',
                    'criteria_7' => $detail['criteria_7'] ?? '',
                    'notes' => $detail['notes'] ?? ''
                ]);
            }
        }
        
        $pdo->commit();
        sendResponse(true, "表单更新成功", ['form_id' => $formId]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "更新表单失败：" . $e->getMessage());
    }
}

/**
 * 删除表单
 */
function deleteForm($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $formId = $input['form_id'] ?? '';
    
    if (!$formId) {
        sendResponse(false, "请提供表单ID");
    }
    
    try {
        $pdo->beginTransaction();
        
        // 删除详情（外键会自动处理）
        $sql = "DELETE FROM evaluation_form_details WHERE form_id = :form_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['form_id' => $formId]);
        
        // 删除表单
        $sql = "DELETE FROM evaluation_forms WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $formId]);
        
        $pdo->commit();
        sendResponse(true, "表单删除成功");
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse(false, "删除表单失败：" . $e->getMessage());
    }
}

/**
 * 获取考核标准（可按department过滤）
 * GET: action=get_standards&department=service_line|sushi_bar|kitchen(可选)
 */
function getStandards($pdo) {
    try {
        $department = $_GET['department'] ?? '';
        
        // 检查表是否存在，如果不存在返回空数组
        try {
            $checkTable = $pdo->query("SHOW TABLES LIKE 'evaluation_criteria_standards'");
            if ($checkTable->rowCount() == 0) {
                sendResponse(true, "获取成功", []);
                return;
            }
        } catch (PDOException $e) {
            // 检查失败时也返回空数组，不阻断流程
            sendResponse(true, "获取成功", []);
            return;
        }
        
        $sql = "SELECT department, criteria_order, score, description_text
                FROM evaluation_criteria_standards";
        $params = [];
        if ($department) {
            $sql .= " WHERE department = :department";
            $params['department'] = $department;
        }
        $sql .= " ORDER BY department, criteria_order, score";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(true, "获取成功", $rows);
    } catch (PDOException $e) {
        sendResponse(false, "获取考核标准失败：" . $e->getMessage());
    } catch (Exception $e) {
        sendResponse(false, "获取考核标准失败：" . $e->getMessage());
    }
}

/**
 * 保存考核标准（批量upsert）
 * POST JSON:
 * {
 *   action: "save_standards",
 *   items: [{department, criteria_order, score, description_text}, ...]
 * }
 */
function saveStandards($pdo) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $items = $input['items'] ?? [];
        if (!is_array($items) || count($items) === 0) {
            sendResponse(false, "没有要保存的内容");
            return;
        }

        // 检查表是否存在
        try {
            $checkTable = $pdo->query("SHOW TABLES LIKE 'evaluation_criteria_standards'");
            if ($checkTable->rowCount() == 0) {
                sendResponse(false, "考核标准表不存在，请先执行SQL文件创建表结构");
                return;
            }
        } catch (PDOException $e) {
            sendResponse(false, "检查表失败：" . $e->getMessage());
            return;
        }

        $pdo->beginTransaction();

        $sql = "INSERT INTO evaluation_criteria_standards
                    (department, criteria_order, score, description_text)
                VALUES
                    (:department, :criteria_order, :score, :description_text)
                ON DUPLICATE KEY UPDATE
                    description_text = VALUES(description_text),
                    updated_at = CURRENT_TIMESTAMP";
        $stmt = $pdo->prepare($sql);

        foreach ($items as $it) {
            $dept = $it['department'] ?? '';
            $criteriaOrder = (int)($it['criteria_order'] ?? 0);
            $score = (int)($it['score'] ?? 0);
            $text = $it['description_text'] ?? '';

            if (!$dept || $criteriaOrder < 1 || $criteriaOrder > 7 || $score < 1 || $score > 5) {
                continue;
            }

            $stmt->execute([
                ':department' => $dept,
                ':criteria_order' => $criteriaOrder,
                ':score' => $score,
                ':description_text' => $text
            ]);
        }

        $pdo->commit();
        sendResponse(true, "保存成功");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, "保存考核标准失败：" . $e->getMessage());
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, "保存考核标准失败：" . $e->getMessage());
    }
}
