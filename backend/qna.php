<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('hr', 'staff_management');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/qna_logic.php';

extract(qna_prepareViewData());
$backendWebBase = qna_getBackendWebBase();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>问卷回答 - KUNZZ HOLDINGS</title>
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib/dist/pdf-lib.min.js"></script>
    <script src="get_fontkit.php"></script>
    <link rel="stylesheet" href="css/qna.css?v=<?php echo time(); ?>">
</head>
<body>
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/qna_content.php'; ?>
    <script src="js/qna.js?v=<?php echo time(); ?>"></script>
</body>
</html>
