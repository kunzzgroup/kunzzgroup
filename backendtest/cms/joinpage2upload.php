<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    // 子域名存储配置
    $subdomainUrl = 'https://media.kunzzgroup.com/comphotos/';
    $uploadDir = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';
    $configFile = '../media_config.json';
    
    // 确保上传目录存在
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $error = "无法创建上传目录：" . $uploadDir . " (请检查服务器路径配置)";
        }
    }
    
    // 验证目录是否可写
    if (!isset($error) && !is_writable($uploadDir)) {
        $error = "上传目录不可写：" . $uploadDir . " (请检查文件夹权限)";
    }
    
    if (!isset($error)) {
        $file = $_FILES['media_file'];
        $photoNumber = $_POST['photo_number'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 允许的文件类型
        $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];

        // 验证文件安全性
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "文件上传错误，错误代码：" . $file['error'];
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 限制10MB
            $error = "文件大小超过10MB限制！";
        } elseif (!in_array($fileExtension, $allowedImage)) {
            $error = "只支持图片格式（JPG, PNG, WebP）！";
        } else {
            // MIME类型验证
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                $error = "文件MIME类型验证失败！";
            } else {
                // 生成新文件名
                $newFileName = $photoNumber . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // 设置文件权限
                    chmod($targetPath, 0644);

                    // 更新配置文件
                    $config = [];
                    if (file_exists($configFile)) {
                        $config = json_decode(file_get_contents($configFile), true) ?: [];
                    }
                    
                    $config['comphoto_' . $photoNumber] = [
                        'file' => $targetPath,  // 物理路径，用于后端验证
                        'url' => $subdomainUrl . $newFileName,  // 子域名URL，用于前端访问
                        'type' => 'image',
                        'updated' => date('Y-m-d H:i:s')
                    ];
                    
                    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $success = "照片 #{$photoNumber} 上传成功！已保存到子域名存储：" . $subdomainUrl . $newFileName;

                    // 页面重定向，清除缓存
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = window.location.href + '?updated=' + Date.now();
                        }, 1500);
                    </script>";
                } else {
                    $error = "照片上传失败！无法移动文件到：" . $targetPath;
                }
            }
        }
    }
}

// 读取当前配置
$config = [];
if (file_exists('../media_config.json')) {
    $config = json_decode(file_get_contents('../media_config.json'), true) ?: [];
}

require __DIR__ . '/templates/joinpage2upload.php';
