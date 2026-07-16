<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/partials/hire_logic.php';

hire_requireAuthenticatedSession();

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

extract(hire_prepareViewData());
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 招聘列表</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/hire.css?v=<?php echo time(); ?>">
</head>
<body class="hire-page">
    <script>
        window.__KUNZZ_BACKEND_BASE__ = <?php echo json_encode($backendWebBase); ?>;
    </script>
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/hire_content.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/zh.js"></script>
    <script src="js/hire.js?v=<?php echo time(); ?>"></script>
</body>
</html>
