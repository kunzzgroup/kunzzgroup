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
        'boss' => '老板',
        'admin' => '管理员',
        'hr' => '人事部',
        'design' => '设计部',
        'support' => '支援部',
        'IT' => '技术部',
        'photograph' => '摄影部'
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
    $valid_types = ['admin', 'hr', 'design', 'support', 'IT', 'boss','photograph'];
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
    $valid_types = ['admin', 'hr', 'design', 'support', 'IT', 'boss', 'photograph'];
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
    $valid_types = ['admin', 'hr', 'design', 'support', 'IT', 'boss', 'photograph'];
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
?>