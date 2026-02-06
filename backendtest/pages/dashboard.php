<?php
/**
 * Dashboard Shell
 * Acts as a controller to serve the dashboard UI shell.
 */

require_once __DIR__ . '/../core/session_check.php';
require_login();

// User Info for Sidebar
$username = $_SESSION['username'] ?? 'User';
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KUNZZ HOLDINGS</title>
    <link rel="icon" type="image/png" href="../../images/images/logo.png">
    
    <!-- Base Configuration -->
    <script>
        window.BASE_URL = "/backendtest/";
        window.API_BASE = "/backendtest/backend/api/";
        window.PAGE_NAME = "dashboard";
    </script>
    
    <!-- External Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="../../animation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    
    <!-- Required Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body class="dashboard-page">
    <div id="app">
        <!-- Sidebar -->
        <?php include '../core/sidebar.php'; ?>
        
        <!-- Loading Placeholder -->
        <div class="main-container">
            <div style="display: flex; justify-content: center; align-items: center; height: 80vh; color: #6b7280;">
                <div class="loader-spinner">加载中...</div>
            </div>
        </div>
    </div>

    <!-- Application Script -->
    <script src="js/dashboard.js"></script>
</body>
</html>
