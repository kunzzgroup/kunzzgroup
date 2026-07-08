<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/aboutpage4upload_logic.php';

$language = aboutpage4upload_getLanguage();
$language = ($language === 'en') ? 'en' : 'zh';
$isEnglish = ($language === 'en');

$success = null;
$error = null;
aboutpage4upload_handlePost($success, $error, $language);

$returnTo = aboutpage4upload_getReturnTo();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $returnTo === 'v2' && !empty($error)) {
    aboutpage4upload_redirectToV2WithError($error, $language);
}

$configFile = aboutpage4upload_resolveConfigFile($language);
$items = aboutpage4upload_loadItems($configFile);

$uploadActionUrl = 'aboutpage4upload.php?lang=' . urlencode($language);

$backendWebBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/aboutpage4upload.css?v=<?php echo time(); ?>">
    <title><?php echo $isEnglish ? 'Timeline Management' : '发展历史管理'; ?> - KUNZZ HOLDINGS</title>
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/aboutpage4upload_content.php'; ?>
    <script src="js/toast.js?v=<?php echo time(); ?>"></script>
    <script src="js/aboutpage4upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
