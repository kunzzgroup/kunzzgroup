<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// 数据库配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

// 获取语言参数，默认为中文
$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 根据语言获取职位
    $stmt = $pdo->prepare("SELECT * FROM job_positions WHERE language = ? ORDER BY publish_date DESC, id DESC");
    $stmt->execute([$language]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 按公司分组职位数据
    $companies = [
        'KUNZZ HOLDINGS' => [
            'name' => 'KUNZZ HOLDINGS',
            'jobs' => []
        ],
        'TOKYO JAPANESE CUISINE' => [
            'name' => 'TOKYO JAPANESE CUISINE',
            'jobs' => []
        ]
    ];
    
    // 处理每个职位
    foreach ($jobs as $job) {
        $company = $job['company_category'] ?? 'KUNZZ HOLDINGS';
        
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
            'address' => $job['company_location'] ?? '待定',
            'department' => $job['company_department'] ?? '',
            'salary' => $job['salary'] ?? ''
        ];
        
        $companies[$company]['jobs'][] = $jobData;
    }
    
    // 返回结构化的数据
    $response = [
        'success' => true,
        'companies' => $companies
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => '获取职位数据失败: ' . $e->getMessage()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
