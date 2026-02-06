<?php
/**
 * Media Manager Shell
 */
require_once '../system/session_check.php';

// Initialize variables
$success = "";
$error = "";

// Handle POST (if any generic upload is added in the future)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    require_once '../api/media_upload_api.php';
    $result = handleMediaUpload($_POST, $_FILES);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

// Include the template
include '../templates/media_manager_template.php';
?>