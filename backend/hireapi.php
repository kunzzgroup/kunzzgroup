<?php
/**
 * hireapi.php — 招聘申请 API
 *
 * GET    ?action=list   → 获取申请列表（后台 hire.php 使用）
 * GET    ?action=stats  → 获取各状态数量统计
 * POST   (multipart)    → 前端提交申请（含简历上传）
 * PUT    (json)         → 后台更新状态 / HR 备注
 * DELETE ?id=xxx        → 删除申请记录
 *
 * 响应格式：{"code": 200|400|500, "msg": "...", "data": ...}
 */

ob_start();

// ─── CORS & Content-Type ────────────────────────────────────────────────────
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// OPTIONS 预检请求直接放行
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

// ─── 数据库连接 ──────────────────────────────────────────────────────────────
$host   = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "code" => 500,
        "msg"  => "数据库连接失败：" . $e->getMessage(),
        "data" => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── 统一响应函数 ────────────────────────────────────────────────────────────
function sendResponse(int $code, string $msg, $data = null): void
{
    ob_end_clean();
    $httpStatus = $code >= 500 ? 500 : ($code !== 200 ? 400 : 200);
    http_response_code($httpStatus);
    echo json_encode(
        ["code" => $code, "msg" => $msg, "data" => $data],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

// ─── 路由分发 ────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];

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
        sendResponse(400, "不支持的请求方法");
}

// ════════════════════════════════════════════════════════════════════════════
// GET — 查询申请列表 / 统计
// ════════════════════════════════════════════════════════════════════════════
function handleGet(): void
{
    global $pdo;

    $action = $_GET['action'] ?? 'list';

    if ($action === 'stats') {
        handleStats();
        return;
    }

    // ── 筛选参数 ──────────────────────────────────────────────────────────
    $status      = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;
    $company     = trim($_GET['company']    ?? '');
    $jobTitle    = trim($_GET['job_title']  ?? '');
    $keyword     = trim($_GET['keyword']    ?? '');   // 搜索姓名 / 邮箱 / 手机
    $dateStart   = trim($_GET['date_start'] ?? '');
    $dateEnd     = trim($_GET['date_end']   ?? '');

    // ── 分页参数 ──────────────────────────────────────────────────────────
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $pageSize = max(1, min(100, (int)($_GET['page_size'] ?? 20)));
    $offset   = ($page - 1) * $pageSize;

    // ── 构建 SQL ──────────────────────────────────────────────────────────
    $where  = ["1=1"];
    $params = [];

    if ($status !== null) {
        $where[]  = "status = ?";
        $params[] = $status;
    }
    if ($company !== '') {
        $where[]  = "company_name LIKE ?";
        $params[] = "%$company%";
    }
    if ($jobTitle !== '') {
        $where[]  = "job_title LIKE ?";
        $params[] = "%$jobTitle%";
    }
    if ($keyword !== '') {
        $where[]  = "(chinese_name LIKE ? OR english_name LIKE ? OR email LIKE ? OR phone_number LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }
    if ($dateStart !== '') {
        $where[]  = "DATE(created_at) >= ?";
        $params[] = $dateStart;
    }
    if ($dateEnd !== '') {
        $where[]  = "DATE(created_at) <= ?";
        $params[] = $dateEnd;
    }

    $whereStr = implode(" AND ", $where);

    try {
        // 总记录数
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM job_applications WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // 分页数据 — LIMIT/OFFSET 直接嵌入（已 int 强转，无注入风险）
        $sql  = "SELECT id, company_name, job_title, chinese_name, english_name,
                        gender, email, phone_code, phone_number, resume_file_url,
                        status, hr_remarks, created_at, updated_at
                 FROM job_applications
                 WHERE $whereStr
                 ORDER BY created_at DESC
                 LIMIT $pageSize OFFSET $offset";

        $stmt         = $pdo->prepare($sql);
        $stmt->execute($params);
        $applications = $stmt->fetchAll();

        sendResponse(200, "获取成功", [
            "total"     => $total,
            "page"      => $page,
            "page_size" => $pageSize,
            "list"      => $applications
        ]);
    } catch (PDOException $e) {
        sendResponse(500, "查询失败：" . $e->getMessage());
    }
}

// ─── 统计各状态数量 ──────────────────────────────────────────────────────────
function handleStats(): void
{
    global $pdo;

    try {
        $stmt = $pdo->query(
            "SELECT status, COUNT(*) AS cnt
             FROM job_applications
             GROUP BY status"
        );
        $rows = $stmt->fetchAll();

        // 初始化全部状态为 0
        $stats = ["total" => 0, "0" => 0, "1" => 0, "2" => 0, "3" => 0];
        foreach ($rows as $row) {
            $stats[(string)$row['status']] = (int)$row['cnt'];
            $stats['total'] += (int)$row['cnt'];
        }

        sendResponse(200, "统计获取成功", $stats);
    } catch (PDOException $e) {
        sendResponse(500, "统计查询失败：" . $e->getMessage());
    }
}

// ════════════════════════════════════════════════════════════════════════════
// POST — 前端提交申请（multipart/form-data，含简历上传）
// ════════════════════════════════════════════════════════════════════════════
function handlePost(): void
{
    global $pdo;

    // ── 必填字段校验 ──────────────────────────────────────────────────────
    $required = ['position', 'chinese_name', 'english_name', 'gender', 'email', 'country_code', 'phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            sendResponse(400, "缺少必填字段：$field");
        }
    }

    // ── 简历文件校验 ──────────────────────────────────────────────────────
    if (empty($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $uploadError = $_FILES['resume']['error'] ?? -1;
        sendResponse(400, "简历上传失败，错误码：$uploadError");
    }

    $file     = $_FILES['resume'];
    $fileTmp  = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileMime = mime_content_type($fileTmp);

    // 限制：PDF 格式，不超过 3MB
    if ($fileMime !== 'application/pdf') {
        sendResponse(400, "简历格式错误，仅支持 PDF 文件");
    }
    if ($fileSize > 3 * 1024 * 1024) {
        sendResponse(400, "简历文件过大，请上传不超过 3MB 的 PDF");
    }

    // ── 保存文件 ──────────────────────────────────────────────────────────
    $uploadDir = __DIR__ . '/uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 文件名：时间戳_随机6位_原文件名（去除特殊字符）
    $originalName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
    $uniqueName   = date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6) . '_' . $originalName;
    $savePath     = $uploadDir . $uniqueName;

    if (!move_uploaded_file($fileTmp, $savePath)) {
        sendResponse(500, "简历文件保存失败，请稍后重试");
    }

    // 存储到数据库的相对 URL（供下载/预览使用）
    $resumeUrl = 'uploads/resumes/' . $uniqueName;

    // ── 写入数据库 ────────────────────────────────────────────────────────
    $companyName = trim($_POST['company_name'] ?? '');   // 前端可传 hidden 字段
    $jobTitle    = trim($_POST['position']);
    $chineseName = trim($_POST['chinese_name']);
    $englishName = trim($_POST['english_name']);
    $gender      = trim($_POST['gender']);
    $email       = trim($_POST['email']);
    $phoneCode   = trim($_POST['country_code']);
    $phoneNumber = trim($_POST['phone']);

    // 简单 Email 格式校验
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(400, "电子邮箱格式不正确");
    }

    try {
        $sql = "INSERT INTO job_applications
                    (company_name, job_title, chinese_name, english_name,
                     gender, email, phone_code, phone_number, resume_file_url, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $companyName,
            $jobTitle,
            $chineseName,
            $englishName,
            $gender,
            $email,
            $phoneCode,
            $phoneNumber,
            $resumeUrl
        ]);

        $newId = $pdo->lastInsertId();

        sendResponse(200, "申请提交成功！我们会尽快与您联系。", ["id" => (int)$newId]);
    } catch (PDOException $e) {
        // 数据库写入失败时删除已上传的文件，保持一致性
        @unlink($savePath);
        sendResponse(500, "申请提交失败，请稍后重试：" . $e->getMessage());
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PUT — 后台更新状态 & HR 备注（application/json）
// ════════════════════════════════════════════════════════════════════════════
function handlePut(): void
{
    global $pdo;

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (empty($data) || !isset($data['id'])) {
        sendResponse(400, "缺少申请记录 ID");
    }

    $id = (int)$data['id'];

    // 动态构建更新字段（只更新传入的字段）
    $fields = [];
    $params = [];

    if (isset($data['status'])) {
        $status = (int)$data['status'];
        if (!in_array($status, [0, 1, 2, 3], true)) {
            sendResponse(400, "状态值无效，请传入 0/1/2/3");
        }
        $fields[] = "status = ?";
        $params[] = $status;
    }

    if (array_key_exists('hr_remarks', $data)) {
        $fields[] = "hr_remarks = ?";
        $params[] = $data['hr_remarks'];    // 允许传 null 或空字符串
    }

    if (empty($fields)) {
        sendResponse(400, "未提供任何需要更新的字段");
    }

    $params[] = $id;   // WHERE id = ?

    try {
        $sql  = "UPDATE job_applications SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            // rowCount 为 0 可能是记录不存在，也可能是值没有变化
            // 查一下确认记录是否存在
            $check = $pdo->prepare("SELECT id FROM job_applications WHERE id = ?");
            $check->execute([$id]);
            if (!$check->fetch()) {
                sendResponse(404, "申请记录不存在 (id=$id)");
            }
        }

        // 返回更新后的完整记录
        $fetch = $pdo->prepare("SELECT * FROM job_applications WHERE id = ?");
        $fetch->execute([$id]);
        $updated = $fetch->fetch();

        sendResponse(200, "更新成功", $updated);
    } catch (PDOException $e) {
        sendResponse(500, "更新失败：" . $e->getMessage());
    }
}

// ════════════════════════════════════════════════════════════════════════════
// DELETE — 删除申请记录
// ════════════════════════════════════════════════════════════════════════════
function handleDelete(): void
{
    global $pdo;

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        sendResponse(400, "缺少有效的申请记录 ID");
    }

    try {
        // 先查出简历文件路径，删除记录后同步删除文件
        $fetch = $pdo->prepare("SELECT resume_file_url FROM job_applications WHERE id = ?");
        $fetch->execute([$id]);
        $row = $fetch->fetch();

        if (!$row) {
            sendResponse(404, "申请记录不存在 (id=$id)");
        }

        $stmt = $pdo->prepare("DELETE FROM job_applications WHERE id = ?");
        $stmt->execute([$id]);

        // 删除对应的简历文件（忽略失败，不影响业务）
        if (!empty($row['resume_file_url'])) {
            $filePath = __DIR__ . '/' . $row['resume_file_url'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        sendResponse(200, "申请记录已删除", ["id" => $id]);
    } catch (PDOException $e) {
        sendResponse(500, "删除失败：" . $e->getMessage());
    }
}
