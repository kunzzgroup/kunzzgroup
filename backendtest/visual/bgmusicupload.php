<?php
/**
 * Background Music Upload Shell
 */
require_once '../system/session_check.php';

// Initialize variables
$success = "";
$error = "";
$configFile = '../music_config.json';
$uploadDir = '../audio/audio/';

// Helper: Format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Handle POST request: Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    require_once '../api/media_upload_api.php';
    
    // Preparation: Delete old files if they exist (business logic for single bg music)
    $possibleExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
    foreach ($possibleExtensions as $ext) {
        if (file_exists($uploadDir . 'music.' . $ext)) {
            unlink($uploadDir . 'music.' . $ext);
        }
    }

    $_POST['media_type'] = 'background_music';
    $_POST['upload_dir'] = $uploadDir;
    $_POST['config_file'] = $configFile;
    
    $result = handleMediaUpload($_POST, $_FILES);
    if ($result['success']) {
        $success = "背景音乐上传成功！";
    } else {
        $error = $result['message'];
    }
}

// Handle POST request: Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true) ?: [];
        if (isset($config['background_music']['file']) && file_exists($config['background_music']['file'])) {
            if (unlink($config['background_music']['file'])) {
                unset($config['background_music']);
                file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $success = "音乐文件已删除！";
            } else {
                $error = "删除文件失败";
            }
        }
    }
}

// Read current config
$config = [];
$fileSizeFormatted = "N/A";
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
    if (isset($config['background_music']['file']) && file_exists($config['background_music']['file'])) {
        $fileSizeFormatted = formatFileSize(filesize($config['background_music']['file']));
    }
}

// Include the template
include '../templates/bgmusicupload_template.php';
?>
