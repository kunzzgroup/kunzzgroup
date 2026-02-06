<?php
/**
 * Corporate Blueprint Shell
 * Acts as a controller to load strategy data and serve the UI shell.
 */

require_once __DIR__ . '/../core/session_check.php';
require_login();

// Strategy Data logic
$strategyFile = __DIR__ . '/../../backend/corporate_strategy.json';
$strategyData = null;

if (file_exists($strategyFile)) {
    $json = file_get_contents($strategyFile);
    $strategyData = json_decode($json, true);
}

// User Info for Sidebar (if needed by shell directly)
$username = $_SESSION['username'] ?? 'User';
$position = $_SESSION['position'] ?? 'Member';

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图 - KUNZZ HOLDINGS</title>
    <link rel="icon" type="image/png" href="../../images/images/logo.png">
    
    <!-- Base Configuration -->
    <script>
        window.BASE_URL = "/backendtest/";
        window.API_BASE = "/backendtest/backend/api/";
        window.PAGE_NAME = "corporate_blueprint";
        window.STRATEGY_DATA = <?php echo json_encode($strategyData, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    
    <!-- External Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@3.1.1/dist/css/jquery.orgchart.min.css">
    <link rel="stylesheet" href="css/corporate_blueprint.css">
    
    <!-- Required Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/orgchart@3.1.1/dist/js/jquery.orgchart.min.js"></script>
</head>
<body class="blueprint-page">
    <div id="app">
        <!-- Sidebar is included via loadView in corporate_blueprint.js or directly here -->
        <?php include '../core/sidebar.php'; ?>
        
        <!-- Loading Placeholder -->
        <div class="main-container">
            <div style="display: flex; justify-content: center; align-items: center; height: 80vh; color: #6b7280;">
                <div class="loader-spinner">加载中...</div>
            </div>
        </div>
    </div>

    <!-- Application Script -->
    <script src="js/corporate_blueprint.js"></script>
</body>
</html>
