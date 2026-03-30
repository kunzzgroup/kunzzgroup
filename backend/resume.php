<?php
/**
 * resume.php — 安全简历查看端点
 * 用法：/backend/resume.php?id={application_id}
 *       /backend/resume.php?id={application_id}&token={hmac}
 * 登录用户可直接访问；邮件里的链接带 token 有效即可访问
 */

// 固定嫺签密钥（请勿对外泄露）
define('RESUME_SIGN_KEY', 'kunzz_resume_2024_xK9!mP#vL');

session_start();

// ── 参数校验 ─────────────────────────────────────────────────────────────────
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('400 Bad Request: Missing id.');
}

// ── 登录检查：session 或有效 HMAC token 二选一 ──────────────────────────────
$hasSession = !empty($_SESSION['user_id']);
$tokenOk    = false;
if (!$hasSession) {
    $token    = $_GET['token'] ?? '';
    $expected = substr(hash_hmac('sha256', (string)$id, RESUME_SIGN_KEY), 0, 32);
    $tokenOk  = ($token !== '' && hash_equals($expected, $token));
}
if (!$hasSession && !$tokenOk) {
    http_response_code(403);
    exit('403 Forbidden: Login required or invalid token.');
}

// ── 数据库查询 ───────────────────────────────────────────────────────────────
$host   = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT resume_file_url FROM job_applications WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['resume_file_url'])) {
        http_response_code(404);
        exit('404 Not Found: No resume on record.');
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('500 DB Error.');
}

// ── 路径安全检查（防路径穿越）───────────────────────────────────────────────
$relUrl = ltrim($row['resume_file_url'], '/');

// 兼容：有没有 /backend/ 前缀
if (strpos($relUrl, 'backend/') === 0) {
    $relUrl = substr($relUrl, strlen('backend/'));
}

$filePath = realpath(__DIR__ . '/' . $relUrl);
$allowedBase = realpath(__DIR__ . '/uploads/resumes/');

if ($filePath === false || strpos($filePath, $allowedBase) !== 0) {
    http_response_code(403);
    exit('403 Forbidden: Invalid path.');
}

if (!file_exists($filePath)) {
    http_response_code(404);
    exit('404 Not Found: File missing on server.');
}

// ── 输出文件 ─────────────────────────────────────────────────────────────────
$ext      = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeMap  = ['pdf' => 'application/pdf', 'doc' => 'application/msword',
             'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$mime     = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;
