<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'stock_inventory');

if (!headers_sent()) {
    header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}

require_once __DIR__ . '/session_check.php';

$system = isset($_GET['system']) ? $_GET['system'] : 'central';
require_once __DIR__ . '/partials/stockminimum_context.php';

if (isset($_GET['system']) && $_GET['system'] !== $system) {
    header('Location: stockminimum.php?system=' . urlencode($system));
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>最低库存设置 - 库存管理系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/toast.css">
    <link rel="stylesheet" href="css/smartSearch.css">
    <link rel="stylesheet" href="css/stockminimum.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include __DIR__ . '/partials/stockminimum_content.php'; ?>
    <script>
        const INITIAL_SYSTEM = <?php echo json_encode($system); ?>;
        const ALLOWED_SYSTEMS = <?php echo json_encode($allowed_systems); ?>;
    </script>
    <script src="js/toast.js"></script>
    <script src="js/stockminimum.js?v=<?php echo time(); ?>"></script>
</body>
</html>
