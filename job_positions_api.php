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
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 获取请求方法和数据
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

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

// 处理 GET 请求 - 获取职位数据
function handleGet() {
    global $pdo;
    
    try {
        // 获取语言参数，默认为中文
        $language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
        
        $stmt = $pdo->prepare("SELECT * FROM job_positions WHERE status = 'active' AND language = ? ORDER BY publish_date DESC, created_at DESC");
        $stmt->execute([$language]);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 按公司分组职位数据
        $companies = [
            'KUNZZHOLDINGS' => [
                'name' => 'KUNZZHOLDINGS',
                'jobs' => []
            ],
            'TOKYO CUISINE' => [
                'name' => 'TOKYO CUISINE',
                'jobs' => []
            ]
        ];
        
        foreach ($jobs as $job) {
            $company = $job['company_category'] ?? 'KUNZZHOLDINGS';
            
            // 确保公司存在
            if (!isset($companies[$company])) {
                $companies[$company] = [
                    'name' => $company,
                    'jobs' => []
                ];
            }
            
            // 添加职位到对应公司
            $jobData = [
                'id' => $job['id'],
                'title' => $job['job_title'],
                'count' => $job['recruitment_count'],
                'experience' => $job['work_experience'],
                'publish_date' => $job['publish_date'],
                'description' => $job['job_description'],
                'location' => $job['company_location'] ?? ''
            ];
            
            $companies[$company]['jobs'][] = $jobData;
        }
        
        sendResponse(true, "职位数据获取成功", ['companies' => $companies]);
        
    } catch (PDOException $e) {
        sendResponse(false, "获取职位数据失败：" . $e->getMessage());
    }
}

// 处理 POST 请求 - 添加职位
function handlePost() {
    global $pdo, $data;
    
    try {
        // 验证必需字段
        $requiredFields = ['job_title', 'work_experience', 'recruitment_count', 'publish_date', 'company_category', 'job_description'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                sendResponse(false, "缺少必需字段：$field");
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO job_positions 
            (job_title, work_experience, recruitment_count, publish_date, company_category, job_description, company_location, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        
        $result = $stmt->execute([
            $data['job_title'],
            $data['work_experience'],
            $data['recruitment_count'],
            $data['publish_date'],
            $data['company_category'],
            $data['job_description'],
            $data['company_location'] ?? ''
        ]);
        
        if ($result) {
            $jobId = $pdo->lastInsertId();
            sendResponse(true, "职位添加成功", ['job_id' => $jobId]);
        } else {
            sendResponse(false, "职位添加失败");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "添加职位失败：" . $e->getMessage());
    }
}

// 处理 PUT 请求 - 更新职位
function handlePut() {
    global $pdo, $data;
    
    try {
        if (empty($data['id'])) {
            sendResponse(false, "缺少职位ID");
        }
        
        // 验证必需字段
        $requiredFields = ['job_title', 'work_experience', 'recruitment_count', 'publish_date', 'company_category', 'job_description'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                sendResponse(false, "缺少必需字段：$field");
            }
        }
        
        $stmt = $pdo->prepare("
            UPDATE job_positions 
            SET job_title = ?, work_experience = ?, recruitment_count = ?, publish_date = ?, 
                company_category = ?, job_description = ?, company_location = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $data['job_title'],
            $data['work_experience'],
            $data['recruitment_count'],
            $data['publish_date'],
            $data['company_category'],
            $data['job_description'],
            $data['company_location'] ?? '',
            $data['id']
        ]);
        
        if ($result) {
            sendResponse(true, "职位更新成功");
        } else {
            sendResponse(false, "职位更新失败");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "更新职位失败：" . $e->getMessage());
    }
}

// 处理 DELETE 请求 - 删除职位
function handleDelete() {
    global $pdo, $data;
    
    try {
        if (empty($data['id'])) {
            sendResponse(false, "缺少职位ID");
        }
        
        // 软删除 - 将状态改为 inactive
        $stmt = $pdo->prepare("UPDATE job_positions SET status = 'inactive', updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$data['id']]);
        
        if ($result) {
            sendResponse(true, "职位删除成功");
        } else {
            sendResponse(false, "职位删除失败");
        }
        
    } catch (PDOException $e) {
        sendResponse(false, "删除职位失败：" . $e->getMessage());
    }
}
?>
