<?php
/**
 * Dashboard Shell
 * Acts as a controller to serve the dashboard UI shell.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '../core/session_check.php';
require_login();

// User Info for Sidebar (sidebar.php handles its own session check/vars now or we pass them? 
// sidebar.php (Step 853) has: $username = isset($username) ? $username : (isset($_SESSION['username']) ? ...
// So we don't strictly need to define them here if session is active, but keeping them for compatibility doesn't hurt.
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
        window.BASE_URL = "/backendtest/"; // Adjusted to project root if needed, user said "/backendtest/"
        window.API_BASE = "/backendtest/api/";
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
    
    <!-- Sidebar (Keep outside #app so it persists) -->
    <?php include '../core/sidebar.php'; ?>

    <!-- Main Content Container (Refreshed by JS) -->
    <div id="app"></div>

    <!-- Application Script -->
    <script src="js/dashboard.js"></script>
</body>
</html>
