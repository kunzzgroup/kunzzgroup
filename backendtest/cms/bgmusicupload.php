<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    $uploadDir = '../audio/audio/';
    $configFile = '../music_config.json';
    
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['music_file'];
    
    // 检查上传错误
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "文件上传失败，错误代码：" . $file['error'];
    } else {
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 允许的音频文件类型
        $allowedAudio = ['mp3', 'wav', 'ogg', 'm4a'];

        if (in_array($fileExtension, $allowedAudio)) {
            // 读取旧配置并删除所有旧文件
            $oldConfig = [];
            if (file_exists($configFile)) {
                $oldConfig = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            // 删除所有可能存在的旧音乐文件
            $possibleExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
            foreach ($possibleExtensions as $ext) {
                $oldFile = $uploadDir . 'music.' . $ext;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            // 如果配置中有旧文件路径，也删除
            if (isset($oldConfig['background_music']['file']) && file_exists($oldConfig['background_music']['file'])) {
                unlink($oldConfig['background_music']['file']);
            }
            
            // 生成新文件名并上传
            $newFileName = 'music.' . $fileExtension;
            $targetPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // 更新配置文件
            $config = [];
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            $config['background_music'] = [
                'file' => $targetPath,
                'type' => 'audio',
                'format' => $fileExtension,
                'updated' => date('Y-m-d H:i:s'),
                'filesize' => filesize($targetPath),
                'original_name' => $file['name']
            ];
            
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // 使用HTTP重定向而不是JavaScript
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&t=" . time());
            exit();
            
        } else {
                $error = "文件移动失败！请检查目录权限。";
            }
        } else {
            $error = "不支持的文件类型！请上传 MP3、WAV、OGG 或 M4A 格式的音频文件。";
        }
    }
}

// 处理音乐删除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $configFile = '../music_config.json';
    
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true) ?: [];
        
        if (isset($config['background_music']['file']) && file_exists($config['background_music']['file'])) {
            // 直接删除文件
            if (unlink($config['background_music']['file'])) {
                unset($config['background_music']);
                file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $success = "音乐文件已删除！";
            } else {
                $error = "删除文件时出错！";
            }
        } else {
            $error = "文件不存在！";
        }
    }
}

// 读取当前配置
$config = [];
if (file_exists('../music_config.json')) {
    $config = json_decode(file_get_contents('../music_config.json'), true) ?: [];
}

// 获取音频文件信息
function getAudioInfo($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    
    $info = [];
    $info['size'] = filesize($filePath);
    $info['size_formatted'] = formatFileSize($info['size']);
    $info['modified'] = date('Y-m-d H:i:s', filemtime($filePath));
    
    return $info;
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

require __DIR__ . '/templates/bgmusicupload.php';
