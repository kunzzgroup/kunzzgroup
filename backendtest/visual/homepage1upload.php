<?php
/**
 * Home Page 1 Upload Shell
 */
require_once '../system/session_check.php';

// Initialize variables
$success = "";
$error = "";
$configFile = '../media_config.json';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    require_once '../api/media_upload_api.php';
    $result = handleMediaUpload($_POST, $_FILES);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

// Read current config for the template
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
}

// Include the template
include '../templates/homepage1upload_template.php';
?>
