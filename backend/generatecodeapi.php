<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
// 引入全局防护脚本
require_once __DIR__ . '/xss_protect.php';
require_once __DIR__ . '/mailer_config.php';
require_once VENDOR_AUTOLOAD;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    // 创建PDO连接
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 设置时区为马来西亚时间 (UTC+8)
    $pdo->exec("SET time_zone = '+08:00'");
}
catch (PDOException $e) {
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
}
else if ($method === 'POST') {
    // 使用 xss_protect.php 中定义的安全获取 JSON 输入函数
    $input = get_safe_json_input();
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
}
catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage()
    ]);
}

/**
 * 生成随机密码
 */
function generateRandomPassword($length = 10)
{
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
 * 发送欢迎邮件（PHPMailer SMTP 版）
 */
function sendWelcomeEmail($email, $username, $password, $accountType)
{

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

    // 登录地址（根据实际服务器地址修改）
    $loginUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

    $htmlBody = "
    <html>
    <head>
        <meta charset='utf-8'>
        <title>欢迎加入 Kunzz Group</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
            .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .header { background: #f97316; color: white; padding: 28px 32px; text-align: center; }
            .header h1 { margin: 0; font-size: 22px; }
            .content { padding: 32px; }
            .credentials { background: #fff8f0; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #f97316; }
            .credentials p { margin: 8px 0; }
            .password { font-family: monospace; font-size: 20px; font-weight: bold; color: #f97316; background: #fdebd0; padding: 10px 16px; border-radius: 6px; letter-spacing: 2px; display: inline-block; margin-top: 6px; }
            .login-btn { display: inline-block; margin-top: 20px; padding: 12px 28px; background: #f97316; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; }
            .footer { background: #f9f9f9; padding: 20px 32px; font-size: 12px; color: #999; border-top: 1px solid #eee; text-align: center; }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='header'>
                <h1>🎉 欢迎加入 Kunzz Group!</h1>
            </div>
            <div class='content'>
                <h2>亲爱的 {$username}，</h2>
                <p>您的账户已成功创建。以下是您的登录信息：</p>

                <div class='credentials'>
                    <p><strong>📧 邮箱：</strong> {$email}</p>
                    <p><strong>🏷️ 账户类型：</strong> {$accountTypeName}</p>
                    <p><strong>🔒 临时密码：</strong></p>
                    <div class='password'>{$password}</div>
                </div>

                <a href='{$loginUrl}' class='login-btn'>前往登录系统</a>

                <p style='margin-top:24px;'><strong style='color:#f97316;'>重要提醒：</strong></p>
                <ul>
                    <li>请妥善保管您的登录信息，切勿转发此邮件</li>
                    <li>建议您首次登录后立即修改密码</li>
                    <li>如有任何问题，请联系管理员</li>
                </ul>
            </div>
            <div class='footer'>
                <p>此邮件由系统自动发送，请勿回复。</p>
                <p>&copy; " . date('Y') . " Kunzz Group. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    try {
        $mail = new PHPMailer(true);

        // SMTP 设置
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // 发件人 & 收件人
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $username);
        $mail->addReplyTo(SMTP_FROM, SMTP_FROM_NAME);

        // 内容
        $mail->isHTML(true);
        $mail->Subject = '欢迎加入 Kunzz Group - 您的登录信息';
        $mail->Body = $htmlBody;
        $mail->AltBody = "亲爱的 {$username}，\n\n您的账户已创建。\n邮箱：{$email}\n账户类型：{$accountTypeName}\n临时密码：{$password}\n\n请登录：{$loginUrl}\n\n请勿回复此邮件。";

        $mail->send();
        return true;

    }
    catch (Exception $e) {
        // 记录错误到日志（不暴露给前端）
        error_log('[sendWelcomeEmail] SMTP Error: ' . $e->getMessage());
        return false;
    }
}


/**
 * 生成新的应用代码
 */
function generateCode($pdo, $input)
{
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
        }
        else {
            echo json_encode([
                'success' => false,
                'message' => '代码生成失败，请重试'
            ]);
        }

    }
    catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => '数据库操作失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 获取代码和用户列表
 */
function getCodesAndUsers($pdo)
{
    try {
        // 从 session 读取当前用户的 branch
        // 从 session 读取当前用户的 branch
        if (!isset($_SESSION))
            @session_start();
        $sessionBranch = $_SESSION['branch'] ?? 'kunzz'; // 默认总部

        // 解析可能包含多个分支的字符串
        $user_branches = explode(',', strtoupper($sessionBranch));

        $baseSelect = "
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
                u.branch,
                u.created_at
            FROM users u
        ";

        // 如果拥有 'KUNZZ' 或 'KH' 分支权限，则视为总部，查看所有职员
        if (in_array('KUNZZ', $user_branches) || in_array('KH', $user_branches)) {
            // 总部：查看所有职员
            $sql = $baseSelect . " ORDER BY u.created_at DESC, u.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        else {
            // 分店：只看本店职员（branch 匹配或未设置）
            // 我们需要构建一个动态的 SQL 来匹配这名用户的任意一个分店，或者为空的情况
            $branchConditions = [];
            $params = [];

            foreach ($user_branches as $index => $br) {
                $br = trim($br);
                if (empty($br))
                    continue;
                // 如果数据库里存的可能是单分支，也可能是逗号分隔的多分支
                // 为了严谨，这里使用 FIND_IN_SET，或者简单的 LIKE，或者直接等于。
                // 因为 u.branch 大部分情况下是一个单分支，但如果是多分支例如 'J1,J2'，普通 = 会不匹配
                // 这里我们简化处理，由于原逻辑是 =:branch，我们扩展为支持多个
                $branchConditions[] = "(u.branch LIKE :br_$index)";
                $params[":br_$index"] = "%$br%";
            }

            if (empty($branchConditions)) {
                // 回退逻辑防出错
                $whereClause = "u.branch = :branch OR u.branch IS NULL OR u.branch = ''";
                $params = [':branch' => $sessionBranch];
            }
            else {
                $whereClause = implode(" OR ", $branchConditions) . " OR u.branch IS NULL OR u.branch = ''";
            }

            $sql = $baseSelect . " WHERE " . $whereClause . " ORDER BY u.created_at DESC, u.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        $results = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'message' => '数据获取成功',
            'data' => $results
        ]);

    }
    catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => '数据查询失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 验证代码格式
 */
function validateCodeFormat($code)
{
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
function logOperation($pdo, $action, $details)
{
    try {
    // 如果你有日志表，可以在这里记录操作
    // $logSql = "INSERT INTO operation_logs (action, details, ip_address, created_at) VALUES (:action, :details, :ip, NOW())";
    // $logStmt = $pdo->prepare($logSql);
    // $logStmt->bindParam(':action', $action);
    // $logStmt->bindParam(':details', $details);
    // $logStmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
    // $logStmt->execute();
    }
    catch (Exception $e) {
        // 日志记录失败不影响主要功能
        error_log("日志记录失败: " . $e->getMessage());
    }
}

/**
 * 获取统计信息（扩展功能）
 */
function getStatistics($pdo)
{
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

    }
    catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => '统计数据获取失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 生成6位随机代码并确保唯一性
 */
function generateRandomCode($pdo)
{
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
function updateCodeAndUser($pdo, $input)
{
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
    $branch = !empty($input['branch']) ? trim($input['branch']) : null;

    // 验证账户类型
    $valid_types = ['special', 'hr', 'account', 'media', 'marketing', 'support', 'production', 'r&d', 'technical', 'design', 'operation', 'service', 'sushi', 'kitchen'];
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
            bank_account_holder_en = :bank_account_holder_en,
            branch = :branch
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
            ':branch' => $branch,
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

    }
    catch (PDOException $e) {
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
function deleteCode($pdo, $input)
{
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

    }
    catch (PDOException $e) {
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
function addNewUser($pdo, $input)
{
    // 验证必填字段
    if (empty($input['username']) || empty($input['email']) || empty($input['account_type'])) {
        echo json_encode([
            'success' => false,
            'message' => '英文姓名、邮箱和账号类型为必填项'
        ]);
        return;
    }

    // 验证账户类型
    $valid_types = ['special', 'hr', 'account', 'media', 'marketing', 'support', 'production', 'r&d', 'technical', 'design', 'operation', 'service', 'sushi', 'kitchen'];
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
        // ======= 确保权限表存在 (必须放在事务开始前，否则DDL会隐式提交事务) =======
        ensurePermissionsTable($pdo);

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
        $hashedPassword = secure_hash_password($defaultPassword);

        // 处理日期格式
        $dateOfBirth = !empty($input['date_of_birth']) ? $input['date_of_birth'] : null;

        // 插入用户数据 - 只插入数据库中存在的字段
        $insertUserSql = "INSERT INTO users (
            username, username_cn, nickname, email, password, ic_number, 
            position, bank_name, bank_account, phone_number, 
            home_address, current_address, city, state, postcode,
            date_of_birth, gender, nationality, race, 
            emergency_contact_name, emergency_phone_number, 
            bank_account_holder_en, account_type, registration_code, branch, is_first_login, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, 1, NOW()
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
            $code,
            !empty($input['branch']) ? trim($input['branch']) : null
        ];

        if (!$insertUserStmt->execute($userData)) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => '用户创建失败，请检查数据格式'
            ]);
            return;
        }

        $newUserId = $pdo->lastInsertId();

        // ======= 保存初始权限数据 =======

        $perms = isset($input['permissions']) && is_array($input['permissions']) ? $input['permissions'] : [];
        $pagePerms = isset($input['page_permissions']) && is_array($input['page_permissions']) ? $input['page_permissions'] : [];
        $submenuPerms = isset($input['submenu_permissions']) && is_array($input['submenu_permissions']) ? $input['submenu_permissions'] : [];
        $reportPerms = isset($input['report_permissions']) && is_array($input['report_permissions']) ? $input['report_permissions'] : [];
        $restaurantPerms = isset($input['restaurant_permissions']) && is_array($input['restaurant_permissions']) ? $input['restaurant_permissions'] : [];
        $brandPerms = isset($input['brand_permissions']) && is_array($input['brand_permissions']) ? $input['brand_permissions'] : [];

        // 将权限数据直接插入 user_sidebar_permissions 表
        $insertPermsSql = "INSERT INTO user_sidebar_permissions (
            user_id, 
            permissions_json, 
            page_permissions_json, 
            submenu_permissions_json, 
            report_permissions_json, 
            restaurant_permissions_json, 
            brand_permissions_json,
            upload_permissions_json,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NOW())";

        $insertPermsStmt = $pdo->prepare($insertPermsSql);
        $insertPermsStmt->execute([
            $newUserId,
            empty($perms) ? NULL : json_encode($perms, JSON_UNESCAPED_UNICODE),
            empty($pagePerms) ? NULL : json_encode($pagePerms, JSON_UNESCAPED_UNICODE),
            empty($submenuPerms) ? NULL : json_encode($submenuPerms, JSON_UNESCAPED_UNICODE),
            empty($reportPerms) ? NULL : json_encode($reportPerms, JSON_UNESCAPED_UNICODE),
            empty($restaurantPerms) ? NULL : json_encode($restaurantPerms, JSON_UNESCAPED_UNICODE),
            empty($brandPerms) ? NULL : json_encode($brandPerms, JSON_UNESCAPED_UNICODE)
        ]);

        // 尝试写入 user_page_permissions（如果使用了新表结构）
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'user_page_permissions'");
            if ($checkStmt->rowCount() > 0) {
                // 如果用户有传 page_permissions，遍历并写入
                if (!empty($pagePerms)) {
                    $insertPagePermsStmt = $pdo->prepare("INSERT INTO user_page_permissions (user_id, page_key, permissions_json, updated_at) VALUES (?, ?, ?, NOW())");
                    foreach ($pagePerms as $pageKey => $pagePermData) {
                        $insertPagePermsStmt->execute([
                            $newUserId,
                            $pageKey,
                            json_encode($pagePermData, JSON_UNESCAPED_UNICODE)
                        ]);
                    }
                }
            }
        }
        catch (\Throwable $e) {
        // 忽略表不存在等错误
        }
        // ================================

        // 提交事务
        $pdo->commit();

        // 发送欢迎邮件
        $emailSent = sendWelcomeEmail($input['email'], $input['username'], $defaultPassword, $input['account_type']);

        $message = '用户添加成功！';
        if ($emailSent) {
            $message .= ' 登录信息已发送到用户邮箱。';
        }
        else {
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

    }
    catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => '数据库操作失败: ' . $e->getMessage()
        ]);
    }
    catch (\Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => '操作失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 确保权限表结构正确
 */
function ensurePermissionsTable($pdo)
{
    // 基础表
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sidebar_permissions (
        user_id INT(11) PRIMARY KEY,
        permissions_json TEXT,
        updated_at DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 尝试添加新列
    $columns = [
        'page_permissions_json',
        'submenu_permissions_json',
        'report_permissions_json',
        'restaurant_permissions_json',
        'brand_permissions_json',
        'upload_permissions_json'
    ];

    foreach ($columns as $col) {
        try {
            $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN $col TEXT NULL");
        }
        catch (Throwable $e) { /* 忽略已存在列 */
        }
    }
}

/**
 * 获取用户的侧边栏及页面权限
 */
function getUserSidebarPermissions($pdo, $input)
{
    if (!isset($_SESSION))
        @session_start();
    $userId = !empty($input['user_id']) ? intval($input['user_id']) : ($_SESSION['user_id'] ?? null);

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => '缺少用户ID']);
        return;
    }

    try {
        $userId = intval($userId);
        $perms = [];

        $pagePerms = [];
        $submenuPerms = [];
        $reportPerms = ['kpi', 'cost'];
        $restaurantPerms = ['j1', 'j2', 'j3'];

        // 检查新表结构
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'user_page_permissions'");
            $tableExists = $checkStmt->rowCount() > 0;
        }
        catch (Throwable $e) {
        }

        if ($tableExists) {
            $stmt = $pdo->prepare("SELECT page_key, permissions_json FROM user_page_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                if ($row['page_key'] === 'stock_inventory') {
                    $decoded = json_decode($row['permissions_json'], true);
                    $pagePerms['stock_inventory'] = [
                        'system' => $decoded['systems'] ?? [],
                        'view' => $decoded['views'] ?? []
                    ];
                }
                elseif ($row['page_key'] === 'kpi_upload') {
                    $decoded = json_decode($row['permissions_json'], true);
                    $pagePerms['kpi_upload'] = [
                        'system' => $decoded['systems'] ?? [],
                        'type' => $decoded['types'] ?? []
                    ];
                }
            }
        }

        // 获取基础权限
        ensurePermissionsTable($pdo);
        $stmt = $pdo->prepare("SELECT * FROM user_sidebar_permissions WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row) {
            $perms = json_decode($row['permissions_json'] ?? '[]', true);
            $submenuPerms = json_decode($row['submenu_permissions_json'] ?? '[]', true);

            if (!$tableExists) {
                $rawPage = json_decode($row['page_permissions_json'] ?? '[]', true);
                if (isset($rawPage['stock_inventory']))
                    $pagePerms['stock_inventory'] = $rawPage['stock_inventory'];
                if (isset($rawPage['kpi_upload']))
                    $pagePerms['kpi_upload'] = $rawPage['kpi_upload'];
            }

            $rep = json_decode($row['report_permissions_json'] ?? '[]', true);
            if (!empty($rep))
                $reportPerms = array_values(array_intersect($rep, ['kpi', 'cost']));

            $res = json_decode($row['restaurant_permissions_json'] ?? '[]', true);
            if (!empty($res))
                $restaurantPerms = array_values(array_intersect($res, ['j1', 'j2', 'j3']));
        }

        echo json_encode([
            'success' => true,
            'permissions' => $perms,
            'page_permissions' => $pagePerms,
            'submenu_permissions' => $submenuPerms,
            'report_permissions' => $reportPerms,
            'restaurant_permissions' => $restaurantPerms,
            'brand_permissions' => json_decode($row['brand_permissions_json'] ?? '[]', true),
            'upload_permissions' => json_decode($row['upload_permissions_json'] ?? '[]', true)
        ]);

    }
    catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '获取失败: ' . $e->getMessage()]);
    }
}

/**
 * 保存用户权限
 */
function saveUserSidebarPermissions($pdo, $input)
{
    if (empty($input['user_id'])) {
        echo json_encode(['success' => false, 'message' => '参数不完整']);
        return;
    }

    try {
        $userId = intval($input['user_id']);
<<<<<<< HEAD
        
        // ======= 确保表结构存在 (必须在事务外，否则会触发隐式提交) =======
        ensurePermissionsTable($pdo);
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_page_permissions (
                user_id INT(11) NOT NULL,
                page_key VARCHAR(50) NOT NULL,
                permissions_json TEXT DEFAULT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, page_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {
            // 忽略创建表失败
        }
        // =========================================================

=======
>>>>>>> parent of 6e0f5c7 (885)
        $pdo->beginTransaction();

        $perms = $input['permissions'] ?? [];
        $pagePerms = $input['page_permissions'] ?? [];
        $submenuPerms = $input['submenu_permissions'] ?? [];
        $reportPerms = $input['report_permissions'] ?? ['kpi', 'cost'];
        $restaurantPerms = $input['restaurant_permissions'] ?? ['j1', 'j2', 'j3'];

<<<<<<< HEAD
        // 保存库存权限
        if (isset($pagePerms['stock_inventory'])) {
            $json = json_encode([
                'systems' => $pagePerms['stock_inventory']['system'] ?? [],
                'views' => $pagePerms['stock_inventory']['view'] ?? []
            ], JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare("INSERT INTO user_page_permissions (user_id, page_key, permissions_json) VALUES (?, 'stock_inventory', ?) ON DUPLICATE KEY UPDATE permissions_json = VALUES(permissions_json)");
            $stmt->execute([$userId, $json]);
        }

        // 保存KPI上传权限
        if (isset($pagePerms['kpi_upload'])) {
            $json = json_encode([
                'systems' => $pagePerms['kpi_upload']['system'] ?? [],
                'types' => $pagePerms['kpi_upload']['type'] ?? []
            ], JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare("INSERT INTO user_page_permissions (user_id, page_key, permissions_json) VALUES (?, 'kpi_upload', ?) ON DUPLICATE KEY UPDATE permissions_json = VALUES(permissions_json)");
            $stmt->execute([$userId, $json]);
        }
=======
        // 维护新表
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_page_permissions (
                user_id INT(11) NOT NULL,
                page_key VARCHAR(50) NOT NULL,
                permissions_json TEXT DEFAULT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, page_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            // 保存库存权限
            if (isset($pagePerms['stock_inventory'])) {
                $json = json_encode([
                    'systems' => $pagePerms['stock_inventory']['system'] ?? [],
                    'views' => $pagePerms['stock_inventory']['view'] ?? []
                ], JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO user_page_permissions (user_id, page_key, permissions_json) VALUES (?, 'stock_inventory', ?) ON DUPLICATE KEY UPDATE permissions_json = VALUES(permissions_json)");
                $stmt->execute([$userId, $json]);
            }
            
            // 保存KPI上传权限
            if (isset($pagePerms['kpi_upload'])) {
                $json = json_encode([
                    'systems' => $pagePerms['kpi_upload']['system'] ?? [],
                    'types' => $pagePerms['kpi_upload']['type'] ?? []
                ], JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO user_page_permissions (user_id, page_key, permissions_json) VALUES (?, 'kpi_upload', ?) ON DUPLICATE KEY UPDATE permissions_json = VALUES(permissions_json)");
                $stmt->execute([$userId, $json]);
            }
        } catch (Throwable $e) {}
>>>>>>> parent of 6e0f5c7 (885)

        // 维护旧表兼容
        ensurePermissionsTable($pdo);
        $sql = "INSERT INTO user_sidebar_permissions 
                (user_id, permissions_json, page_permissions_json, submenu_permissions_json, report_permissions_json, restaurant_permissions_json, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                permissions_json = VALUES(permissions_json),
                page_permissions_json = VALUES(page_permissions_json),
                submenu_permissions_json = VALUES(submenu_permissions_json),
                report_permissions_json = VALUES(report_permissions_json),
                restaurant_permissions_json = VALUES(restaurant_permissions_json),
                updated_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId,
            json_encode($perms, JSON_UNESCAPED_UNICODE),
            json_encode($pagePerms, JSON_UNESCAPED_UNICODE),
            json_encode($submenuPerms, JSON_UNESCAPED_UNICODE),
            json_encode($reportPerms, JSON_UNESCAPED_UNICODE),
            json_encode($restaurantPerms, JSON_UNESCAPED_UNICODE)
        ]);

        $pdo->commit();
        echo json_encode(['success' => true]);

    }
    catch (Throwable $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => '保存失败: ' . $e->getMessage()]);
    }
}

/**
 * 获取单页面权限
 */
function getUserPagePermissions($pdo, $input)
{
    getUserSidebarPermissions($pdo, $input); // 直接复用
}

/**
 * 保存单页面权限
 */
function saveUserPagePermissions($pdo, $input)
{
    if (empty($input['user_id'])) {
        echo json_encode(['success' => false, 'message' => '参数不完整']);
        return;
    }
}
?>
