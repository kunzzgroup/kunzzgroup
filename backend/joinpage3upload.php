<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/joinpage3upload_logic.php';

$language = joinpage3upload_getLanguage();
$isEnglish = ($language === 'en');
$returnTo = joinpage3upload_getReturnTo();
$pageUrl = joinpage3upload_getPageUrl($returnTo);
$uploadActionUrl = joinpage3upload_getUploadActionUrl($returnTo);
$backendWebBase = joinpage3upload_getBackendWebBase();

try {
    $pdo = get_pdo_connection();
} catch (PDOException $e) {
    die(($isEnglish ? 'Database connection failed: ' : '数据库连接失败：') . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$success = null;
$error = null;
joinpage3upload_handlePost($pdo, $success, $error);

try {
    $jobs = joinpage3upload_loadJobs($pdo, $language);
} catch (PDOException $e) {
    $jobs = [];
    $error = $isEnglish ? 'Failed to read job data: ' . $e->getMessage() : '读取职位数据失败：' . $e->getMessage();
}

if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

$editJob = null;
if (isset($_GET['edit'])) {
    $editJob = joinpage3upload_findEditJob($jobs, $_GET['edit'], $language);
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/joinpage3upload.css?v=<?php echo time(); ?>">
    <title><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?> - KUNZZ HOLDINGS</title>
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/joinpage3upload_content.php'; ?>
    <script src="js/toast.js?v=<?php echo time(); ?>"></script>
    <script src="js/joinpage3upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
