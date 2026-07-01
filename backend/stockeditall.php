<?php
require_once __DIR__ . '/permission_guard.php';
requireStockView('records');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
// 包含会话验证
require_once 'session_check.php';

// 服务器端获取系统参数
$system = isset($_GET['system']) ? $_GET['system'] : 'central';
$system_names = [
    'central' => '中央',
    'j1' => 'J1',
    'j2' => 'J2',
    'j3' => 'J3'
];
$display_name = isset($system_names[$system]) ? $system_names[$system] : '中央';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理系统</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../animation.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="css/stockeditall.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="css/sidebar.css?v=<?php echo time(); ?>" />
    
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include __DIR__ . '/partials/stockeditall_content.php'; ?>
    <script src="js/stockeditall.js?v=<?php echo time(); ?>"></script>
    <script>
    (function() {
        const hoverBox = document.getElementById("userHoverBox");
        if (!hoverBox) return;
        document.addEventListener("mouseover", function(e) {
            if (e.target.classList.contains("created-user")) {
                const user = e.target.getAttribute("data-user") || "-";
                const time = e.target.getAttribute("data-time") || "-";
                hoverBox.innerHTML = '<div style="font-weight:600;">' + user + '</div>' + '<div style="margin-top:4px;color:#666;">操作时间：' + time + '</div>';
                hoverBox.style.display = "block";
            }
        });
        document.addEventListener("mousemove", function(e) {
            if (hoverBox.style.display === "block") {
                hoverBox.style.top = (e.pageY - hoverBox.offsetHeight - 12) + "px";
                hoverBox.style.left = (e.pageX - hoverBox.offsetWidth - 12) + "px";
            }
        });
        document.addEventListener("mouseout", function(e) {
            if (e.target.classList.contains("created-user")) {
                hoverBox.style.display = "none";
            }
        });
    })();
    </script>
</body>
<script>
// 页面权限检查已在 applyPagePermissions 函数中处理，这里不需要重复代码
</html>
