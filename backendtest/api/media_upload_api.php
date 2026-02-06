<?php
/**
 * Unified Media Upload API
 * Handles file uploads and updates the media_config.json
 */
session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    if (basename($_SERVER['PHP_SELF']) === 'media_upload_api.php') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '未登录']);
        exit();
    }
    return ['success' => false, 'message' => '未登录'];
}

function handleMediaUpload($postData, $fileData) {
    if (!isset($fileData['media_file'])) {
        return ['success' => false, 'message' => '未发现上传文件'];
    }

    $mediaType = $postData['media_type'] ?? 'unknown';
    $uploadDir = $postData['upload_dir'] ?? '../video/video/';
    $configFile = $postData['config_file'] ?? '../media_config.json';
    
    // 确保上传目录存在 (相对于 API 文件的路径)
    // 注意：如果从 shell 包含此文件，路径可能不同。建议使用基于 __DIR__ 的路径。
    $absoluteUploadDir = dirname(__DIR__) . '/' . ltrim($uploadDir, './');
    $absoluteConfigFile = dirname(__DIR__) . '/' . ltrim($configFile, './');

    if (!file_exists($absoluteUploadDir)) {
        mkdir($absoluteUploadDir, 0777, true);
    }
    
    $file = $fileData['media_file'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 允许的文件类型
    $allowedVideo = ['mp4', 'webm', 'mov', 'avi'];
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedAudio = ['mp3', 'wav', 'ogg'];
    $allowedTypes = array_merge($allowedVideo, $allowedImage, $allowedAudio);
    
    if (in_array($fileExtension, $allowedTypes)) {
        // 生成新文件名
        $newFileName = $mediaType . '.' . $fileExtension;
        $targetPath = $absoluteUploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // 更新配置文件
            $config = [];
            if (file_exists($absoluteConfigFile)) {
                $config = json_decode(file_get_contents($absoluteConfigFile), true) ?: [];
            }
            
            // Web 访问路径 (保持相对结构)
            $webPath = $uploadDir . $newFileName;
            
            $config[$mediaType] = [
                'file' => $webPath,
                'type' => in_array($fileExtension, $allowedVideo) ? 'video' : (in_array($fileExtension, $allowedAudio) ? 'audio' : 'image'),
                'updated' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($absoluteConfigFile, json_encode($config, JSON_PRETTY_PRINT));
            return ['success' => true, 'message' => '文件上传成功！', 'webPath' => $webPath];
        } else {
            return ['success' => false, 'message' => '文件上传失败！无法移动临时文件。'];
        }
    } else {
        return ['success' => false, 'message' => '不支持的文件类型！'];
    }
}

// 如果直接访问 API
if (basename($_SERVER['PHP_SELF']) === 'media_upload_api.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $result = handleMediaUpload($_POST, $_FILES);
    echo json_encode($result);
    exit();
}
?>
