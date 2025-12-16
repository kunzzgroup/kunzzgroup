<?php

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
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
$username = 'u857194726_kunzzgroup';
$password = 'Kholdings1688@';

try {
    // 创建PDO连接
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 设置时区为马来西亚时间 (UTC+8)
    $pdo->exec("SET time_zone = '+08:00'");
} catch(PDOException $e) {
    // 数据库连接失败
    echo json_encode([
        'success' => false,
        'message' => '数据库连接失败: ' . $e->getMessage()
    ]);
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

try {
    switch ($action) {
        case 'generate':
            // 生成新代码
            generateCode($pdo, $input);
            break;
            
        case 'list':
            // 获取代码和用户列表
            getCodesAndUsers($pdo);
            break;
            
        case 'update':
            // 更新代码和用户信息
            updateCodeAndUser($pdo, $input);
            break;
            
        case 'delete':
            // 删除代码
            deleteCode($pdo, $input);
            break;

        case 'add_user':
            // 添加新用户
            addNewUser($pdo, $input);
            break;
        
        case 'get_permissions':
            getUserSidebarPermissions($pdo, $input);
            break;

        case 'save_permissions':
            saveUserSidebarPermissions($pdo, $input);
            break;

        case 'get_page_permissions':
            getUserPagePermissions($pdo, $input);
            break;

        case 'save_page_permissions':
            saveUserPagePermissions($pdo, $input);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => '无效的操作请求'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage()
    ]);
}

/**
 * 生成随机密码
 */
function generateRandomPassword($length = 10) {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $symbols = '!@#$%&*';
    
    $password = '';
    
    // 确保密码包含每种类型的字符
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $symbols[rand(0, strlen($symbols) - 1)];
    
    // 填充剩余长度
    $allChars = $uppercase . $lowercase . $numbers . $symbols;
    for ($i = 4; $i < $length; $i++) {
        $password .= $allChars[rand(0, strlen($allChars) - 1)];
    }
    
    // 打乱密码字符顺序
    return str_shuffle($password);
}

/**
 * 发送欢迎邮件
 */
function sendWelcomeEmail($email, $username, $password, $accountType) {
    $to = $email;
    $subject = "欢迎加入 Kunzz Group - 您的登录信息";
    
    // 格式化账户类型
    $typeNames = [
        'special' => '特殊',
        'hr' => '人事部',
        'account' => '会计部',
        'media' => '媒体制作部',
        'marketing' => '推广部',
        'support' => '支援部',
        'production' => '生产部',
        'r&d' => '研发部',
        'technical' => '科技部',
        'design' => '设计部',
        'operation' => 'Operation',
        'service' => '前台',
        'sushi' => 'Sushi Bar',
        'kitchen' => '厨房'
    ];
    
    $accountTypeName = $typeNames[$accountType] ?? $accountType;
    
    $message = "
    <html>
    <head>
        <title>欢迎加入 Kunzz Group</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                max-width: 600px; 
                margin: 0 auto; 
                padding: 20px; 
            }
            .header { 
                background: #ff5c00; 
                color: white; 
                padding: 20px; 
                text-align: center; 
                border-radius: 8px 8px 0 0; 
            }
            .content { 
                background: #f9f9f9; 
                padding: 30px; 
                border-radius: 0 0 8px 8px; 
                border: 1px solid #ddd; 
            }
            .credentials { 
                background: white; 
                padding: 20px; 
                margin: 20px 0; 
                border-radius: 5px; 
                border-left: 4px solid #ff5c00; 
            }
            .password { 
                font-family: monospace; 
                font-size: 18px; 
                font-weight: bold; 
                color: #ff5c00; 
                background: #f0f0f0; 
                padding: 10px; 
                border-radius: 4px; 
                letter-spacing: 1px; 
            }
            .footer { 
                margin-top: 30px; 
                padding-top: 20px; 
                border-top: 1px solid #ddd; 
                font-size: 12px; 
                color: #666; 
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>欢迎加入 Kunzz Group!</h1>
        </div>
        <div class='content'>
            <h2>亲爱的 {$username}，</h2>
            <p>欢迎您加入我们的团队！您的账户已成功创建。</p>
            
            <div class='credentials'>
                <h3>您的登录信息：</h3>
                <p><strong>邮箱：</strong> {$email}</p>
                <p><strong>账户类型：</strong> {$accountTypeName}</p>
                <p><strong>临时密码：</strong></p>
                <div class='password'>{$password}</div>
            </div>
            
            <p><strong style='color: #ff5c00;'>重要提醒：</strong></p>
            <ul>
                <li>请妥善保管您的登录信息</li>
                <li>建议您首次登录后立即修改密码</li>
                <li>如有任何问题，请联系管理员</li>
            </ul>
            
            <p>感谢您成为我们团队的一员！</p>
            
            <div class='footer'>
                <p>此邮件由系统自动发送，请勿回复。</p>
                <p>&copy; " . date('Y') . " Kunzz Group. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // 设置邮件头
    $headers = array(
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=utf-8',
        'From' => 'noreply@kunzzgroup.com',
        'Reply-To' => 'support@kunzzgroup.com',
        'X-Mailer' => 'PHP/' . phpversion()
    );
    
    // 将数组转换为字符串格式
    $headerString = '';
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }
    
    // 发送邮件
    return mail($to, $subject, $message, $headerString);
}

/**
 * 生成新的应用代码
 */
function generateCode($pdo, $input) {
    // 验证输入数据
    if (empty($input['account_type'])) {
        echo json_encode([
            'success' => false,
            'message' => '账户类型不能为空'
        ]);
        return;
    }

    $account_type = trim($input['account_type']);
    
    // 生成6位随机代码
    $code = generateRandomCode($pdo);

    // 验证账户类型
    $valid_types = ['special', 'hr', 'account', 'media', 'marketing', 'support', 'production', 'r&d', 'technical', 'design', 'operation', 'service', 'sushi', 'kitchen'];
    if (!in_array($account_type, $valid_types)) {
        echo json_encode([
            'success' => false,
            'message' => '无效的账户类型'
        ]);
        return;
    }

    // 验证代码格式（只允许字母、数字和特定符号）
    if (!preg_match('/^[A-Z0-9_-]+$/', $code)) {
        echo json_encode([
            'success' => false,
            'message' => '代码格式无效，只能包含大写字母、数字、下划线和连字符'
        ]);
        return;
    }

    try {
        // 检查代码是否已存在
        $checkSql = "SELECT id FROM application_codes WHERE code = :code";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':code', $code);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => '代码已存在，请使用其他代码'
            ]);
            return;
        }

        // 插入新代码
        $insertSql = "INSERT INTO application_codes (code, account_type, used, created_at) VALUES (:code, :account_type, 0, NOW())";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->bindParam(':code', $code);
        $insertStmt->bindParam(':account_type', $account_type);
        
        if ($insertStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => '代码生成成功',
                'data' => [
                    'code' => $code,
                    'account_type' => $account_type
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '代码生成失败，请重试'
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => '数据库操作失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 获取代码和用户列表
 */
function getCodesAndUsers($pdo) {
    try {
        // 查询所有代码和对应的用户信息
        $sql = "
            SELECT 
                u.id,
                u.username,
                u.username_cn,
                u.nickname,
                u.email,
                u.gender,
                u.race,
                u.phone_number,
                u.ic_number,
                u.date_of_birth,
                u.nationality,
                u.home_address,
                u.position,
                u.emergency_contact_name,
                u.emergency_phone_number,
                u.bank_name,
                u.bank_account,
                u.bank_account_holder_en,
                u.registration_code,
                u.account_type,
                u.created_at
            FROM users u
            ORDER BY u.created_at DESC, u.id DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'message' => '数据获取成功',
            'data' => $results
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => '数据查询失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 验证代码格式
 */
function validateCodeFormat($code) {
    // 代码长度限制：3-50个字符
    if (strlen($code) < 3 || strlen($code) > 50) {
        return false;
    }
    
    // 只允许大写字母、数字、下划线和连字符
    return preg_match('/^[A-Z0-9_-]+$/', $code);
}

/**
 * 记录操作日志（可选功能）
 */
function logOperation($pdo, $action, $details) {
    try {
        // 如果你有日志表，可以在这里记录操作
        // $logSql = "INSERT INTO operation_logs (action, details, ip_address, created_at) VALUES (:action, :details, :ip, NOW())";
        // $logStmt = $pdo->prepare($logSql);
        // $logStmt->bindParam(':action', $action);
        // $logStmt->bindParam(':details', $details);
        // $logStmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
        // $logStmt->execute();
    } catch (Exception $e) {
        // 日志记录失败不影响主要功能
        error_log("日志记录失败: " . $e->getMessage());
    }
}

/**
 * 获取统计信息（扩展功能）
 */
function getStatistics($pdo) {
    try {
        $stats = [];
        
        // 总代码数
        $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM application_codes");
        $stats['total_codes'] = $totalStmt->fetch()['total'];
        
        // 已使用代码数
        $usedStmt = $pdo->query("SELECT COUNT(*) as used FROM application_codes WHERE used = 1");
        $stats['used_codes'] = $usedStmt->fetch()['used'];
        
        // 未使用代码数
        $stats['unused_codes'] = $stats['total_codes'] - $stats['used_codes'];
        
        // 各类型账户统计
        $typeStmt = $pdo->query("
            SELECT account_type, COUNT(*) as count 
            FROM application_codes 
            GROUP BY account_type
        ");
        $stats['by_type'] = $typeStmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => '统计数据获取失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 生成6位随机代码并确保唯一性
 */
function generateRandomCode($pdo) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $maxAttempts = 100; // 最大尝试次数，避免无限循环
    
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        // 检查代码是否已存在
        $checkSql = "SELECT id FROM application_codes WHERE code = :code";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':code', $code);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            return $code; // 返回唯一的代码
        }
    }
    
    // 如果尝试次数过多仍未找到唯一代码，抛出异常
    throw new Exception('无法生成唯一的申请码，请稍后重试');
}

/**
 * 更新申请码和用户信息
 */
function updateCodeAndUser($pdo, $input) {
    // 验证输入数据
    if (empty($input['id']) || empty($input['account_type'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID和账户类型不能为空'
        ]);
        return;
    }

    $id = intval($input['id']);
    $account_type = trim($input['account_type']);
    $username = trim($input['username'] ?? '');
    $username_cn = trim($input['username_cn'] ?? '');
    $nickname = trim($input['nickname'] ?? '');
    $email = trim($input['email'] ?? '');
    $gender = trim($input['gender'] ?? '');
    $race = trim($input['race'] ?? '');
    $phone_number = trim($input['phone_number'] ?? '');
    $ic_number = trim($input['ic_number'] ?? '');
    $date_of_birth = !empty($input['date_of_birth']) ? $input['date_of_birth'] : null;
    $nationality = trim($input['nationality'] ?? '');
    $home_address = trim($input['home_address'] ?? '');
    $position = trim($input['position'] ?? '');
    $emergency_contact_name = trim($input['emergency_contact_name'] ?? '');
    $emergency_phone_number = trim($input['emergency_phone_number'] ?? '');
    $bank_name = trim($input['bank_name'] ?? '');
    $bank_account = trim($input['bank_account'] ?? '');
    $bank_account_holder_en = trim($input['bank_account_holder_en'] ?? '');
    $registration_code = trim($input['registration_code'] ?? '');

    // 验证账户类型
    $valid_types = ['special', 'hr', 'account', 'media', 'marketing', 'support', 'production', 'r&d', 'technical', 'design','operation','service','sushi','kitchen'];
    if (!in_array($account_type, $valid_types)) {
        echo json_encode([
            'success' => false,
            'message' => '无效的账户类型'
        ]);
        return;
    }

    // 验证邮箱格式
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => '邮箱格式不正确'
        ]);
        return;
    }

    // 验证性别
    if (!empty($gender) && !in_array($gender, ['male', 'female', 'other'])) {
        echo json_encode([
            'success' => false,
            'message' => '无效的性别选项'
        ]);
        return;
    }

    try {
        // 开始事务
        $pdo->beginTransaction();

        // 检查邮箱是否被其他用户使用
        if (!empty($email)) {
            $checkEmailSql = "SELECT id FROM users WHERE email = :email AND id != :id";
            $checkEmailStmt = $pdo->prepare($checkEmailSql);
            $checkEmailStmt->bindParam(':email', $email);
            $checkEmailStmt->bindParam(':id', $id);
            $checkEmailStmt->execute();

            if ($checkEmailStmt->rowCount() > 0) {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => '邮箱已被其他用户使用'
                ]);
                return;
            }
        }

        // 直接更新用户表
        $updateUserSql = "UPDATE users SET 
            account_type = :account_type,
            username = :username,
            username_cn = :username_cn,
            nickname = :nickname,
            email = :email,
            gender = :gender,
            race = :race,
            phone_number = :phone_number,
            ic_number = :ic_number,
            date_of_birth = :date_of_birth,
            nationality = :nationality,
            home_address = :home_address,
            position = :position,
            emergency_contact_name = :emergency_contact_name,
            emergency_phone_number = :emergency_phone_number,
            bank_name = :bank_name,
            bank_account = :bank_account,
            bank_account_holder_en = :bank_account_holder_en
            WHERE id = :id";

        $params = [
            ':account_type' => $account_type,
            ':username' => $username,
            ':username_cn' => $username_cn,
            ':nickname' => $nickname,
            ':email' => $email,
            ':gender' => $gender,
            ':race' => $race,
            ':phone_number' => $phone_number,
            ':ic_number' => $ic_number,
            ':date_of_birth' => $date_of_birth,
            ':nationality' => $nationality,
            ':home_address' => $home_address,
            ':position' => $position,
            ':emergency_contact_name' => $emergency_contact_name,
            ':emergency_phone_number' => $emergency_phone_number,
            ':bank_name' => $bank_name,
            ':bank_account' => $bank_account,
            ':bank_account_holder_en' => $bank_account_holder_en,
            ':id' => $id
        ];

        $updateUserStmt = $pdo->prepare($updateUserSql);

        if (!$updateUserStmt->execute($params)) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => '更新用户信息失败'
            ]);
            return;
        }

        // 提交事务
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => '更新成功'
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => '数据库操作失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 删除申请码
 */
function deleteCode($pdo, $input) {
    // 验证输入数据
    if (empty($input['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID不能为空'
        ]);
        return;
    }

    $id = intval($input['id']);

    try {
        // 开始事务
        $pdo->beginTransaction();

        // 获取用户信息
        $checkSql = "SELECT username, registration_code FROM users WHERE id = :id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        
        $result = $checkStmt->fetch();
        if (!$result) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => '用户不存在'
            ]);
            return;
        }

        $username = $result['username'];
        $registration_code = $result['registration_code'];

        // 直接删除用户
        $deleteSql = "DELETE FROM users WHERE id = :id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindParam(':id', $id);
        
        if (!$deleteStmt->execute()) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => '删除失败'
            ]);
            return;
        }

        // 提交事务
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => '删除成功',
            'data' => [
                'id' => $id,
                'username' => $username,
                'registration_code' => $registration_code
            ]
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => '数据库操作失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 添加新用户
 */
function addNewUser($pdo, $input) {
    // 验证必填字段
    if (empty($input['username']) || empty($input['email']) || empty($input['account_type'])) {
        echo json_encode([
            'success' => false,
            'message' => '英文姓名、邮箱和账号类型为必填项'
        ]);
        return;
    }

    // 验证账户类型
    $valid_types = ['special', 'hr', 'account', 'media', 'marketing', 'support', 'production', 'r&d', 'technical', 'design','operation','service','sushi','kitchen'];
    if (!in_array($input['account_type'], $valid_types)) {
        echo json_encode([
            'success' => false,
            'message' => '无效的账户类型'
        ]);
        return;
    }

    // 验证邮箱格式
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => '邮箱格式不正确'
        ]);
        return;
    }

    // 验证性别
    if (!empty($input['gender']) && !in_array($input['gender'], ['male', 'female', 'other'])) {
        echo json_encode([
            'success' => false,
            'message' => '无效的性别选项'
        ]);
        return;
    }

    try {
        // 开始事务
        $pdo->beginTransaction();

        // 检查邮箱是否已存在
        $checkEmailSql = "SELECT id FROM users WHERE email = ?";
        $checkEmailStmt = $pdo->prepare($checkEmailSql);
        $checkEmailStmt->execute([$input['email']]);

        if ($checkEmailStmt->rowCount() > 0) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => '该邮箱已被注册'
            ]);
            return;
        }

        // 生成唯一的申请码
        $code = generateRandomCode($pdo);

        // 插入申请码
        $insertCodeSql = "INSERT INTO application_codes (code, account_type, used, created_at) VALUES (?, ?, 1, NOW())";
        $insertCodeStmt = $pdo->prepare($insertCodeSql);
        
        if (!$insertCodeStmt->execute([$code, $input['account_type']])) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => '申请码生成失败'
            ]);
            return;
        }

        // 生成随机密码
        $defaultPassword = generateRandomPassword();
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        // 处理日期格式
        $dateOfBirth = !empty($input['date_of_birth']) ? $input['date_of_birth'] : null;
        
        // 插入用户数据 - 只插入数据库中存在的字段
        $insertUserSql = "INSERT INTO users (
            username, username_cn, nickname, email, password, ic_number, 
            position, bank_name, bank_account, phone_number, 
            home_address, current_address, city, state, postcode,
            date_of_birth, gender, nationality, race, 
            emergency_contact_name, emergency_phone_number, 
            bank_account_holder_en, account_type, registration_code, is_first_login, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, 1, NOW()
        )";

        $insertUserStmt = $pdo->prepare($insertUserSql);
        $userData = [
            trim($input['username']),
            !empty($input['username_cn']) ? trim($input['username_cn']) : null,
            !empty($input['nickname']) ? trim($input['nickname']) : null,
            trim($input['email']),
            $hashedPassword,
            !empty($input['ic_number']) ? trim($input['ic_number']) : null,
            !empty($input['position']) ? trim($input['position']) : null,
            !empty($input['bank_name']) ? trim($input['bank_name']) : null,
            !empty($input['bank_account']) ? trim($input['bank_account']) : null,
            !empty($input['phone_number']) ? trim($input['phone_number']) : null,
            !empty($input['home_address']) ? trim($input['home_address']) : null,
            null, // current_address
            null, // city  
            null, // state
            null, // postcode
            $dateOfBirth,
            !empty($input['gender']) ? $input['gender'] : null,
            !empty($input['nationality']) ? trim($input['nationality']) : null,
            !empty($input['race']) ? trim($input['race']) : null,
            !empty($input['emergency_contact_name']) ? trim($input['emergency_contact_name']) : null,
            !empty($input['emergency_phone_number']) ? trim($input['emergency_phone_number']) : null,
            !empty($input['bank_account_holder_en']) ? trim($input['bank_account_holder_en']) : null,
            $input['account_type'],
            $code
        ];

        if (!$insertUserStmt->execute($userData)) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => '用户创建失败，请检查数据格式'
            ]);
            return;
        }

        // 提交事务
        $pdo->commit();

        // 发送欢迎邮件
        $emailSent = sendWelcomeEmail($input['email'], $input['username'], $defaultPassword, $input['account_type']);

        $message = '用户添加成功！';
        if ($emailSent) {
            $message .= ' 登录信息已发送到用户邮箱。';
        } else {
            $message .= ' 但邮件发送失败，请手动告知用户登录信息。';
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => [
                'username' => $input['username'],
                'email' => $input['email'],
                'code' => $code,
                'account_type' => $input['account_type'],
                'default_password' => $defaultPassword,
                'email_sent' => $emailSent
            ]
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => '数据库操作失败: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => '操作失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 确保权限表存在
 */
function ensurePermissionsTable($pdo) {
    // 主表（若不存在则创建）
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sidebar_permissions (
        user_id INT PRIMARY KEY,
        permissions_json TEXT NULL,
        page_permissions_json TEXT NULL,
        submenu_permissions_json TEXT NULL,
        report_permissions_json TEXT NULL,
        restaurant_permissions_json TEXT NULL,
        brand_permissions_json TEXT NULL,
        upload_permissions_json TEXT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    // 确保新增列存在（向后兼容）
    try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN page_permissions_json TEXT NULL"); } catch (Throwable $e) { /* 已存在则忽略 */ }
    try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN submenu_permissions_json TEXT NULL"); } catch (Throwable $e) { /* 已存在则忽略 */ }
    try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN report_permissions_json TEXT NULL"); } catch (Throwable $e) { /* 已存在则忽略 */ }
    try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN restaurant_permissions_json TEXT NULL"); } catch (Throwable $e) { /* 已存在则忽略 */ }
    try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN brand_permissions_json TEXT NULL"); } catch (Throwable $e) { /* 已存在则忽略 */ }
    try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN upload_permissions_json TEXT NULL"); } catch (Throwable $e) { /* 已存在则忽略 */ }
}

/**
 * 获取用户的侧边栏权限
 */
function getUserSidebarPermissions($pdo, $input) {
    if (empty($input['user_id'])) {
        echo json_encode(['success' => false, 'message' => '缺少用户ID']);
        return;
    }
    try {
        $userId = intval($input['user_id']);
        $perms = [];
        $pagePerms = [];
        $submenuPerms = [];
        $reportPerms = ['kpi','cost'];
        $restaurantPerms = ['j1','j2','j3'];
        
        // 检查是否使用 user_page_permissions 表（新表结构）
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'user_page_permissions'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (Throwable $e) {
            // 表不存在
        }
        
        if ($tableExists) {
            // 使用 user_page_permissions 表（每个页面单独记录）
            $stmt = $pdo->prepare("SELECT page_key, permissions_json FROM user_page_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stockSystems = [];
            $stockViews = [];
            $legacyKeys = ['stocklistall','stockeditall','stockproductname','stockremark','stocksot'];
            
            foreach ($rows as $row) {
                $pageKey = $row['page_key'];
                $permData = [];
                if (!empty($row['permissions_json'])) {
                    $decoded = json_decode($row['permissions_json'], true);
                    if (is_array($decoded)) {
                        $permData = $decoded;
                    }
                }
                $systems = $permData['systems'] ?? $permData['system'] ?? [];
                $views = $permData['views'] ?? $permData['view'] ?? [];
                if ($pageKey === 'stock_inventory') {
                    $stockSystems = is_array($systems) ? array_values(array_intersect($systems, ['central','j1','j2','j3'])) : [];
                    $stockViews = is_array($views) ? array_values(array_intersect($views, ['list','records','remark','product','sot'])) : [];
                } elseif (in_array($pageKey, $legacyKeys, true)) {
                    if (is_array($systems)) {
                        $stockSystems = array_merge($stockSystems, array_values(array_intersect($systems, ['central','j1','j2','j3'])));
                    }
                    if (is_array($views)) {
                        $stockViews = array_merge($stockViews, array_values(array_intersect($views, ['list','records','remark','product','sot'])));
                    }
                }
            }
            $stockSystems = array_values(array_unique($stockSystems));
            $stockViews = array_values(array_unique($stockViews));
            $pagePerms['stock_inventory'] = [
                'system' => $stockSystems,
                'view' => $stockViews
            ];
        }
        
        $brandPerms = [];
        $uploadPerms = [];
        
        // 尝试从 user_sidebar_permissions 表获取（如果存在）
        try {
            ensurePermissionsTable($pdo);
            $stmt = $pdo->prepare("SELECT permissions_json, page_permissions_json, submenu_permissions_json, report_permissions_json, restaurant_permissions_json, brand_permissions_json, upload_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            if ($row && !empty($row['permissions_json'])) {
                $decoded = json_decode($row['permissions_json'], true);
                if (is_array($decoded)) { $perms = $decoded; }
            }
            // 如果 user_page_permissions 表不存在，才使用 user_sidebar_permissions 的页面权限
            if (!$tableExists && $row && !empty($row['page_permissions_json'])) {
                $decoded2 = json_decode($row['page_permissions_json'], true);
                if (is_array($decoded2)) {
                    $stockSystems = [];
                    $stockViews = [];
                    if (isset($decoded2['stock_inventory'])) {
                        $stockSystems = array_values(array_intersect($decoded2['stock_inventory']['system'] ?? [], ['central','j1','j2','j3']));
                        $stockViews = array_values(array_intersect($decoded2['stock_inventory']['view'] ?? [], ['list','records','remark','product','sot']));
                    } else {
                        $legacyKeys = ['stocklistall','stockeditall','stockproductname','stockremark','stocksot'];
                        foreach ($legacyKeys as $legacyKey) {
                            if (!empty($decoded2[$legacyKey]['system']) && is_array($decoded2[$legacyKey]['system'])) {
                                $stockSystems = array_merge($stockSystems, array_values(array_intersect($decoded2[$legacyKey]['system'], ['central','j1','j2','j3'])));
                            }
                            if (!empty($decoded2[$legacyKey]['view']) && is_array($decoded2[$legacyKey]['view'])) {
                                $stockViews = array_merge($stockViews, array_values(array_intersect($decoded2[$legacyKey]['view'], ['list','records','remark','product','sot'])));
                            }
                        }
                        $stockSystems = array_values(array_unique($stockSystems));
                        $stockViews = array_values(array_unique($stockViews));
                    }
                    $pagePerms['stock_inventory'] = [
                        'system' => $stockSystems,
                        'view' => $stockViews
                    ];
                }
            }
            if ($row && !empty($row['submenu_permissions_json'])) {
                $decoded3 = json_decode($row['submenu_permissions_json'], true);
                if (is_array($decoded3)) { $submenuPerms = $decoded3; }
            }
            if ($row && !empty($row['report_permissions_json'])) {
                $decoded4 = json_decode($row['report_permissions_json'], true);
                if (is_array($decoded4) && !empty($decoded4)) {
                    $filtered = array_values(array_intersect($decoded4, ['kpi','cost']));
                    if (!empty($filtered)) {
                        $reportPerms = $filtered;
                    }
                }
            }
            if ($row && !empty($row['restaurant_permissions_json'])) {
                $decoded5 = json_decode($row['restaurant_permissions_json'], true);
                if (is_array($decoded5) && !empty($decoded5)) {
                    $filtered = array_values(array_intersect($decoded5, ['j1','j2','j3']));
                    if (!empty($filtered)) {
                        $restaurantPerms = $filtered;
                    }
                }
            }
            if ($row && !empty($row['brand_permissions_json'])) {
                $decoded6 = json_decode($row['brand_permissions_json'], true);
                if (is_array($decoded6)) { $brandPerms = $decoded6; }
            }
            if ($row && !empty($row['upload_permissions_json'])) {
                $decoded7 = json_decode($row['upload_permissions_json'], true);
                if (is_array($decoded7)) { $uploadPerms = $decoded7; }
            }
        } catch (PDOException $e) {
            // 如果表不存在，忽略错误
        }
        if (!isset($pagePerms['stock_inventory'])) {
            $pagePerms['stock_inventory'] = [
                'system' => [],
                'view' => []
            ];
        }
        
        echo json_encode([
            'success' => true,
            'permissions' => $perms,
            'page_permissions' => $pagePerms,
            'submenu_permissions' => $submenuPerms,
            'report_permissions' => $reportPerms,
            'restaurant_permissions' => $restaurantPerms,
            'brand_permissions' => $brandPerms,
            'upload_permissions' => $uploadPerms
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '获取失败: '.$e->getMessage()]);
    }
}

/**
 * 保存用户的侧边栏权限
 */
function saveUserSidebarPermissions($pdo, $input) {
    if (empty($input['user_id']) || !isset($input['permissions'])) {
        echo json_encode(['success' => false, 'message' => '参数不完整']);
        return;
    }
    $userId = intval($input['user_id']);
    $perms = $input['permissions'];
    if (!is_array($perms)) { $perms = []; }
    // 仅允许这些键
    $allowedKeys = ['analytics','hr','resource','visual','brand'];
    $perms = array_values(array_intersect($perms, $allowedKeys));
    // 页面权限（可选）
    $pagePerms = isset($input['page_permissions']) && is_array($input['page_permissions']) ? $input['page_permissions'] : [];
    // 规范化：仅允许已知键
    $normalize = function($arr, $allow) {
        return array_values(array_intersect(is_array($arr) ? $arr : [], $allow));
    };
    $stockSystemsAllowed = ['central','j1','j2','j3'];
    $stockViewsAllowed = ['list','records','remark','product','sot'];
    $pagePermsNorm = [
        'stock_inventory' => [
            'system' => $normalize($pagePerms['stock_inventory']['system'] ?? [], $stockSystemsAllowed),
            'view'   => $normalize($pagePerms['stock_inventory']['view'] ?? [], $stockViewsAllowed)
        ]
    ];
    $submenuInput = isset($input['submenu_permissions']) && is_array($input['submenu_permissions']) ? $input['submenu_permissions'] : [];
    $submenuAllowed = [
        'analytics' => ['kpi_report', 'kpi_upload'],
        'hr' => ['staff_management', 'schedule'],
        'resource' => ['stock_inventory', 'dishware', 'price_comparison'],
        'visual' => ['bgmusic', 'homepage1', 'about1', 'about4', 'tokyo1', 'tokyo5', 'join1', 'join2', 'join3'],
        'brand' => ['kunzz_holdings', 'tokyo_cuisine', 'tokyo_izakaya']
    ];
    $submenuPermsNorm = [];
    foreach ($submenuAllowed as $parent => $allowedList) {
        $requested = isset($submenuInput[$parent]) && is_array($submenuInput[$parent]) ? $submenuInput[$parent] : [];
        $submenuPermsNorm[$parent] = array_values(array_intersect($requested, $allowedList));
    }
    foreach ($submenuPermsNorm as $parent => $list) {
        if (!in_array($parent, $perms, true)) {
            $submenuPermsNorm[$parent] = [];
        }
    }
    $reportAllowed = ['kpi','cost'];
    $restaurantAllowed = ['j1','j2','j3'];
    $reportPerms = isset($input['report_permissions']) && is_array($input['report_permissions'])
        ? array_values(array_intersect($input['report_permissions'], $reportAllowed))
        : $reportAllowed;
    if (empty($reportPerms)) {
        $reportPerms = $reportAllowed;
    }
    $restaurantPerms = isset($input['restaurant_permissions']) && is_array($input['restaurant_permissions'])
        ? array_values(array_intersect($input['restaurant_permissions'], $restaurantAllowed))
        : $restaurantAllowed;
    if (empty($restaurantPerms)) {
        $restaurantPerms = $restaurantAllowed;
    }
    // 品牌权限（三级和四级）
    $brandPerms = isset($input['brand_permissions']) && is_array($input['brand_permissions']) ? $input['brand_permissions'] : [];
    // 上传权限
    $uploadPerms = isset($input['upload_permissions']) && is_array($input['upload_permissions']) ? $input['upload_permissions'] : [];
    try {
        // 检查是否使用 user_page_permissions 表（新表结构）
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'user_page_permissions'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (Throwable $e) {
            // 表不存在
        }
        
        if ($tableExists && !empty($pagePermsNorm)) {
            // 使用 user_page_permissions 表（每个页面单独记录）
            // 确保表存在
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_page_permissions (
                user_id INT(11) NOT NULL,
                page_key VARCHAR(50) NOT NULL,
                permissions_json TEXT DEFAULT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, page_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci");
            // 清理旧的库存页面记录
            $del = $pdo->prepare("DELETE FROM user_page_permissions WHERE user_id = :uid AND page_key IN ('stocklistall','stockeditall','stockproductname','stockremark','stocksot')");
            $del->execute([':uid' => $userId]);
            
            // 保存统一库存权限
            $stockPermData = $pagePermsNorm['stock_inventory'] ?? ['system'=>[], 'view'=>[]];
            $permJson = json_encode([
                'systems' => $stockPermData['system'] ?? [],
                'views' => $stockPermData['view'] ?? []
            ], JSON_UNESCAPED_UNICODE);
            
            $up = $pdo->prepare("INSERT INTO user_page_permissions (user_id, page_key, permissions_json, updated_at)
                VALUES (:uid, 'stock_inventory', :permJson, NOW())
                ON DUPLICATE KEY UPDATE permissions_json = VALUES(permissions_json), updated_at = NOW()");
            $ok = $up->execute([':uid'=>$userId, ':permJson'=>$permJson]);
            if (!$ok) {
                throw new Exception("保存页面权限失败: stock_inventory");
            }
        }
        
        // 同时保存到 user_sidebar_permissions 表（如果存在，用于侧边栏权限）
        try {
            ensurePermissionsTable($pdo);
            $json = json_encode($perms, JSON_UNESCAPED_UNICODE);
            $jsonPages = json_encode($pagePermsNorm, JSON_UNESCAPED_UNICODE);
            $jsonSubmenu = json_encode($submenuPermsNorm, JSON_UNESCAPED_UNICODE);
            $jsonReport = json_encode($reportPerms, JSON_UNESCAPED_UNICODE);
            $jsonRestaurant = json_encode($restaurantPerms, JSON_UNESCAPED_UNICODE);
            $jsonBrand = json_encode($brandPerms, JSON_UNESCAPED_UNICODE);
            $jsonUpload = json_encode($uploadPerms, JSON_UNESCAPED_UNICODE);
            $up = $pdo->prepare("INSERT INTO user_sidebar_permissions (user_id, permissions_json, page_permissions_json, submenu_permissions_json, report_permissions_json, restaurant_permissions_json, brand_permissions_json, upload_permissions_json, updated_at)
                VALUES (:uid, :json, :jsonPages, :jsonSub, :jsonReport, :jsonRestaurant, :jsonBrand, :jsonUpload, NOW())
                ON DUPLICATE KEY UPDATE permissions_json = VALUES(permissions_json), page_permissions_json = VALUES(page_permissions_json), submenu_permissions_json = VALUES(submenu_permissions_json), report_permissions_json = VALUES(report_permissions_json), restaurant_permissions_json = VALUES(restaurant_permissions_json), brand_permissions_json = VALUES(brand_permissions_json), upload_permissions_json = VALUES(upload_permissions_json), updated_at = NOW()");
            $up->execute([
                ':uid'=>$userId,
                ':json'=>$json,
                ':jsonPages'=>$jsonPages,
                ':jsonSub'=>$jsonSubmenu,
                ':jsonReport'=>$jsonReport,
                ':jsonRestaurant'=>$jsonRestaurant,
                ':jsonBrand'=>$jsonBrand,
                ':jsonUpload'=>$jsonUpload
            ]);
        } catch (PDOException $e) {
            // 如果表不存在，忽略错误（只使用 user_page_permissions）
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                throw $e;
            }
        }
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '保存失败: '.$e->getMessage()]);
    }
}

// 单独的获取/保存页面权限接口（可供其他页面直接调用）
function getUserPagePermissions($pdo, $input) {
    if (!isset($_SESSION)) { @session_start(); }
    $userId = isset($input['user_id']) ? intval($input['user_id']) : (isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0);
    if ($userId <= 0) { echo json_encode(['success'=>false, 'message'=>'未登录']); return; }
    try {
        // 检查是否使用 user_page_permissions 表（新表结构）
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'user_page_permissions'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (Throwable $e) {
            // 表不存在
        }
        
        $pagePerms = [];
        $reportPerms = ['kpi','cost'];
        $restaurantPerms = ['j1','j2','j3'];
        
        if ($tableExists) {
            // 使用 user_page_permissions 表（每个页面单独记录）
            $stmt = $pdo->prepare("SELECT page_key, permissions_json FROM user_page_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stockSystems = [];
            $stockViews = [];
            $hasUnifiedStockEntry = false;
            $legacyKeys = ['stocklistall','stockeditall','stockproductname','stockremark','stocksot'];
            
            foreach ($rows as $row) {
                $pageKey = $row['page_key'];
                $permData = [];
                if (!empty($row['permissions_json'])) {
                    $decoded = json_decode($row['permissions_json'], true);
                    if (is_array($decoded)) {
                        $permData = $decoded;
                    }
                }
                $systems = $permData['systems'] ?? $permData['system'] ?? [];
                $views = $permData['views'] ?? $permData['view'] ?? [];
                if ($pageKey === 'stock_inventory') {
                    $hasUnifiedStockEntry = true;
                    $stockSystems = is_array($systems) ? array_values(array_intersect($systems, ['central','j1','j2','j3'])) : [];
                    $stockViews = is_array($views) ? array_values(array_intersect($views, ['list','records','remark','product','sot'])) : [];
                } elseif (in_array($pageKey, $legacyKeys, true)) {
                    if (is_array($systems)) {
                        $stockSystems = array_merge($stockSystems, array_values(array_intersect($systems, ['central','j1','j2','j3'])));
                    }
                    if (is_array($views)) {
                        $stockViews = array_merge($stockViews, array_values(array_intersect($views, ['list','records','remark','product','sot'])));
                    }
                }
            }
            $stockSystems = array_values(array_unique($stockSystems));
            $stockViews = array_values(array_unique($stockViews));
            $pagePerms['stock_inventory'] = [
                'system' => $stockSystems,
                'view' => $stockViews
            ];
            // 同时尝试从 user_sidebar_permissions 读取报表/餐厅权限
            try {
                ensurePermissionsTable($pdo);
                $stmt = $pdo->prepare("SELECT report_permissions_json, restaurant_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
                $stmt->execute([$userId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['report_permissions_json'])) {
                    $decoded = json_decode($row['report_permissions_json'], true);
                    if (is_array($decoded) && !empty($decoded)) {
                        $filtered = array_values(array_intersect($decoded, ['kpi','cost']));
                        if (!empty($filtered)) {
                            $reportPerms = $filtered;
                        }
                    }
                }
                if ($row && !empty($row['restaurant_permissions_json'])) {
                    $decoded = json_decode($row['restaurant_permissions_json'], true);
                    if (is_array($decoded) && !empty($decoded)) {
                        $filtered = array_values(array_intersect($decoded, ['j1','j2','j3']));
                        if (!empty($filtered)) {
                            $restaurantPerms = $filtered;
                        }
                    }
                }
            } catch (Throwable $e) {
                // ignore
            }
        } else {
            // 使用 user_sidebar_permissions 表（旧表结构）
            ensurePermissionsTable($pdo);
            $stmt = $pdo->prepare("SELECT page_permissions_json, report_permissions_json, restaurant_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            $stockSystems = [];
            $stockViews = [];
            if ($row && !empty($row['page_permissions_json'])) {
                $tmp = json_decode($row['page_permissions_json'], true);
                if (is_array($tmp)) {
                    if (isset($tmp['stock_inventory'])) {
                        $stockSystems = array_values(array_intersect($tmp['stock_inventory']['system'] ?? [], ['central','j1','j2','j3']));
                        $stockViews = array_values(array_intersect($tmp['stock_inventory']['view'] ?? [], ['list','records','remark','product','sot']));
                    } else {
                        $legacyKeys = ['stocklistall','stockeditall','stockproductname','stockremark','stocksot'];
                        foreach ($legacyKeys as $legacyKey) {
                            if (!empty($tmp[$legacyKey]['system']) && is_array($tmp[$legacyKey]['system'])) {
                                $stockSystems = array_merge($stockSystems, array_values(array_intersect($tmp[$legacyKey]['system'], ['central','j1','j2','j3'])));
                            }
                            if (!empty($tmp[$legacyKey]['view']) && is_array($tmp[$legacyKey]['view'])) {
                                $stockViews = array_merge($stockViews, array_values(array_intersect($tmp[$legacyKey]['view'], ['list','records','remark','product','sot'])));
                            }
                        }
                        $stockSystems = array_values(array_unique($stockSystems));
                        $stockViews = array_values(array_unique($stockViews));
                    }
                }
            }
            $pagePerms['stock_inventory'] = [
                'system' => $stockSystems,
                'view' => $stockViews
            ];
            if ($row && !empty($row['report_permissions_json'])) {
                $decoded = json_decode($row['report_permissions_json'], true);
                if (is_array($decoded) && !empty($decoded)) {
                    $filtered = array_values(array_intersect($decoded, ['kpi','cost']));
                    if (!empty($filtered)) {
                        $reportPerms = $filtered;
                    }
                }
            }
            if ($row && !empty($row['restaurant_permissions_json'])) {
                $decoded = json_decode($row['restaurant_permissions_json'], true);
                if (is_array($decoded) && !empty($decoded)) {
                    $filtered = array_values(array_intersect($decoded, ['j1','j2','j3']));
                    if (!empty($filtered)) {
                        $restaurantPerms = $filtered;
                    }
                }
            }
        }

        echo json_encode([
            'success'=>true,
            'page_permissions'=>$pagePerms,
            'report_permissions'=>$reportPerms,
            'restaurant_permissions'=>$restaurantPerms
        ]);
    } catch (Throwable $e) {
        echo json_encode(['success'=>false, 'message'=>'获取失败: '.$e->getMessage()]);
    }
}

function saveUserPagePermissions($pdo, $input) {
    if (empty($input['user_id']) || !isset($input['page_permissions'])) {
        echo json_encode(['success'=>false, 'message'=>'参数不完整']);
        return;
    }
    $userId = intval($input['user_id']);
    $pagePerms = is_array($input['page_permissions']) ? $input['page_permissions'] : [];
    $normalize = function($arr, $allow) { return array_values(array_intersect(is_array($arr)?$arr:[], $allow)); };
    $stockSystemsAllowed = ['central','j1','j2','j3'];
    $stockViewsAllowed = ['list','records','remark','product','sot'];
    $pagePermsNorm = [
        'stock_inventory' => [
            'system' => $normalize($pagePerms['stock_inventory']['system'] ?? [], $stockSystemsAllowed),
            'view'   => $normalize($pagePerms['stock_inventory']['view'] ?? [], $stockViewsAllowed)
        ]
    ];
    try {
        // 检查是否使用 user_page_permissions 表（新表结构）
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'user_page_permissions'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (Throwable $e) {
            // 表不存在
        }
        
        if ($tableExists) {
            // 使用 user_page_permissions 表（每个页面单独记录）
            // 确保表存在
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_page_permissions (
                user_id INT(11) NOT NULL,
                page_key VARCHAR(50) NOT NULL,
                permissions_json TEXT DEFAULT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, page_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci");
            
            // 清理旧的库存页面记录
            $del = $pdo->prepare("DELETE FROM user_page_permissions WHERE user_id = :uid AND page_key IN ('stocklistall','stockeditall','stockproductname','stockremark','stocksot')");
            $del->execute([':uid' => $userId]);
            $permData = $pagePermsNorm['stock_inventory'] ?? ['system'=>[], 'view'=>[]];
            $permJson = json_encode([
                'systems' => $permData['system'] ?? [],
                'views' => $permData['view'] ?? []
            ], JSON_UNESCAPED_UNICODE);
            $up = $pdo->prepare("INSERT INTO user_page_permissions (user_id, page_key, permissions_json, updated_at)
                VALUES (:uid, 'stock_inventory', :permJson, NOW())
                ON DUPLICATE KEY UPDATE permissions_json = VALUES(permissions_json), updated_at = NOW()");
            $ok = $up->execute([':uid'=>$userId, ':permJson'=>$permJson]);
            if (!$ok) {
                throw new Exception("保存页面权限失败: stock_inventory");
            }
            echo json_encode(['success'=>true]);
        } else {
            // 使用 user_sidebar_permissions 表（旧表结构）
            ensurePermissionsTable($pdo);
            $jsonPages = json_encode($pagePermsNorm, JSON_UNESCAPED_UNICODE);
            $up = $pdo->prepare("INSERT INTO user_sidebar_permissions (user_id, page_permissions_json, updated_at)
                VALUES (:uid, :jsonPages, NOW())
                ON DUPLICATE KEY UPDATE page_permissions_json = VALUES(page_permissions_json), updated_at = NOW()");
            $ok = $up->execute([':uid'=>$userId, ':jsonPages'=>$jsonPages]);
            echo json_encode(['success'=>(bool)$ok]);
        }
    } catch (Throwable $e) {
        echo json_encode(['success'=>false, 'message'=>'保存失败: '.$e->getMessage()]);
    }
}
?>