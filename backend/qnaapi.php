<?php
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

session_start();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "未登录"]);
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
    
    // 确保表存在
    $pdo->exec("CREATE TABLE IF NOT EXISTS `qna_responses` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) NOT NULL,
      `question1` TEXT NULL COMMENT '如果不考虑现实限制,你希望自己在3-5年后成为什么样的人?',
      `question2` TEXT NULL COMMENT '你目前最重要的个人目标或梦想是什么?',
      `question3` TEXT NULL COMMENT '如果公司为你提供机会,你是否愿意承担更高的责任与压力?你认为这些责任具体体现在哪些方面?',
      `question4` TEXT NULL COMMENT '在实现的目标过程中,你目前遇到最大的困难或挑战是什么?',
      `question5` TEXT NULL COMMENT '如果公司可以提供支持,你最希望公司在哪些方面给予帮助?',
      `question6` TEXT NULL COMMENT '在目前的公司中,有没有你特别希望尝试或发展的方向?为什么?',
      `question7` TEXT NULL COMMENT '你认为哪些能力或经验,是你未来1-2年最需要重点提升的?',
      `question8` TEXT NULL COMMENT '问题8（预留）',
      `question9` TEXT NULL COMMENT '问题9（预留）',
      `question10` TEXT NULL COMMENT '问题10（预留）',
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_user_response` (`user_id`),
      KEY `idx_user_id` (`user_id`),
      KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='问卷回答表';");
    
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "数据库连接失败：" . $e->getMessage()]);
    exit;
}

$userId = $_SESSION['user_id'];
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
        handleGet($pdo, $userId);
        break;
    case 'POST':
        handlePost($pdo, $userId, $data);
        break;
    default:
        sendResponse(false, "不支持的请求方法");
}

// 处理 GET 请求 - 获取用户的问卷回答
function handleGet($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM qna_responses WHERE user_id = ?");
        $stmt->execute([$userId]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($response) {
            sendResponse(true, "获取成功", $response);
        } else {
            sendResponse(true, "暂无回答", null);
        }
    } catch (PDOException $e) {
        sendResponse(false, "查询失败：" . $e->getMessage());
    }
}

// 处理 POST 请求 - 提交问卷回答
function handlePost($pdo, $userId, $data) {
    try {
        // 检查用户是否已经提交过
        $checkStmt = $pdo->prepare("SELECT id FROM qna_responses WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            sendResponse(false, "您已经提交过问卷，每个用户只能提交一次");
        }
        
        // 验证必填字段
        $requiredFields = ['question1', 'question2', 'question3', 'question4', 'question5', 'question6', 'question7'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                sendResponse(false, "请填写所有必填问题");
            }
        }
        
        // 插入数据
        $stmt = $pdo->prepare("INSERT INTO qna_responses (
            user_id, question1, question2, question3, question4, question5, 
            question6, question7, question8, question9, question10
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $userId,
            trim($data['question1'] ?? ''),
            trim($data['question2'] ?? ''),
            trim($data['question3'] ?? ''),
            trim($data['question4'] ?? ''),
            trim($data['question5'] ?? ''),
            trim($data['question6'] ?? ''),
            trim($data['question7'] ?? ''),
            trim($data['question8'] ?? ''),
            trim($data['question9'] ?? ''),
            trim($data['question10'] ?? '')
        ]);
        
        sendResponse(true, "问卷提交成功");
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // 唯一约束违反
            sendResponse(false, "您已经提交过问卷，每个用户只能提交一次");
        } else {
            sendResponse(false, "提交失败：" . $e->getMessage());
        }
    }
}

