<?php
/**
 * Join us Page 2 Upload Shell (Footprints)
 */
require_once '../system/session_check.php';

// Initialize variables
$success = "";
$error = "";
$configFile = '../media_config.json';
$subdomainUrl = 'https://media.kunzzgroup.com/comphotos/';
$uploadDir = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $photoNumber = $_POST['photo_number'];
    $file = $_FILES['media_file'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validation
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "上传错误 (" . $file['error'] . ")";
    } elseif ($file['size'] > 10 * 1024 * 1024) {
        $error = "文件太大 (Max 10MB)";
    } elseif (!in_array($fileExtension, $allowedImage)) {
        $error = "仅支持图片格式";
    } else {
        // Ensure directory exists (optional, as per original)
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $newFileName = $photoNumber . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            chmod($targetPath, 0644);
            $config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
            $config['comphoto_' . $photoNumber] = [
                'file' => $targetPath,
                'url' => $subdomainUrl . $newFileName,
                'type' => 'image',
                'updated' => date('Y-m-d H:i:s')
            ];
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = "照片 #{$photoNumber} 上传成功！";
        } else {
            $error = "无法保存文件到服务器";
        }
    }
}

// Read current config and stats
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
}

$uploadedCount = 0;
for ($i = 1; $i <= 30; $i++) {
    if (isset($config['comphoto_' . $i])) {
        // Simple check: if metadata exists, consider it uploaded (or we could check file_exists if local)
        $uploadedCount++;
    }
}

// Include the template
include '../templates/joinpage2upload_template.php';
?>
