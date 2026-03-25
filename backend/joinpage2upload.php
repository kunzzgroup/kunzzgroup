<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    // 子域名存储配置
    $subdomainUrl = 'https://media.kunzzgroup.com/comphotos/';
    $uploadDir = '../images/images/';
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
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $error = "请先选择要上传的照片！";
        }
        elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "文件上传错误，错误代码：" . $file['error'];
        }
        elseif ($file['size'] > 10 * 1024 * 1024) { // 限制10MB
            $error = "文件大小超过10MB限制！";
        }
        elseif (!in_array($fileExtension, $allowedImage)) {
            $error = "只支持图片格式（JPG, PNG, WebP）！";
        }
        else {
            // MIME类型验证
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                $error = "文件MIME类型验证失败！";
            }
            else {
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
                        'file' => $targetPath, // 物理路径，用于后端验证
                        'type' => 'image',
                        'updated' => date('Y-m-d H:i:s')
                    ];

                    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $success = "照片 #{$photoNumber} 上传成功！";

                    // 页面重定向，清除缓存
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = window.location.href + '?updated=' + Date.now();
                        }, 1500);
                    </script>";
                }
                else {
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
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我们的足迹照片管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/joinpage2upload.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>我们的足迹照片管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>我们的足迹照片</span>
        </div>
        
        <div class="content">            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php
endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php
endif; ?>

            <?php
// 统计已上传的照片（检查本地和子域名路径）
$uploadedCount = 0;
$subdomainPhysicalPath = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';

for ($i = 1; $i <= 30; $i++) {
    $photoKey = 'comphoto_' . $i;
    if (isset($config[$photoKey])) {
        // 优先检查子域名路径
        $subdomainPath = $subdomainPhysicalPath . basename($config[$photoKey]['file']);
        if (file_exists($subdomainPath)) {
            $uploadedCount++;
        }
        elseif (file_exists($config[$photoKey]['file'])) {
            // 检查本地文件
            $uploadedCount++;
        }
    }
}
?>
            
            <div class="stats-bar">
                <div class="stats-item">
                    总照片数: <span class="stats-number">30</span>
                </div>
                <div class="stats-item">
                    已上传: <span class="stats-number"><?php echo $uploadedCount; ?></span>
                </div>
                <div class="stats-item">
                    待上传: <span class="stats-number"><?php echo 30 - $uploadedCount; ?></span>
                </div>
            </div>
            
            <h2 class="section-title">照片上传管理</h2>
            
            <div class="photos-grid">
                <?php for ($i = 1; $i <= 30; $i++): ?>
                    <div class="photo-card">
                        <div class="photo-header">
                            <div class="photo-number"><?php echo $i; ?></div>
                            <div class="photo-title">照片 #<?php echo $i; ?></div>
                        </div>
                        
                        <?php
    // 检查文件是否存在（优先检查子域名路径）
    $photoKey = 'comphoto_' . $i;
    $fileExists = false;
    $displayUrl = '';
    $subdomainPhysicalPath = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';

    if (isset($config[$photoKey])) {
        // 优先检查子域名路径
        $subdomainPath = $subdomainPhysicalPath . basename($config[$photoKey]['file']);
        if (file_exists($subdomainPath)) {
            $fileExists = true;
        }
        elseif (file_exists($config[$photoKey]['file'])) {
            $fileExists = true;
        }

        // 使用子域名URL显示图片
        if ($fileExists && isset($config[$photoKey]['url'])) {
            $displayUrl = $config[$photoKey]['url'];
        }
        elseif ($fileExists) {
            $displayUrl = $config[$photoKey]['file'];
        }
    }

    if ($fileExists && $displayUrl): ?>
                            <div class="current-image">
                                <img src="<?php echo $displayUrl; ?>?v=<?php echo time(); ?>" alt="照片 <?php echo $i; ?>">
                                <div class="image-info">
                                    <strong>已上传</strong><br>
                                    <small>更新: <?php echo $config[$photoKey]['updated']; ?></small>
                                    <?php if (isset($config[$photoKey]['url'])): ?>
                                        <br><small>URL: <?php echo htmlspecialchars($config[$photoKey]['url']); ?></small>
                                    <?php
        endif; ?>
                                </div>
                            </div>
                        <?php
    endif; ?>
                        
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="photo_number" value="<?php echo $i; ?>">
                            
                            <div class="file-input" onclick="document.getElementById('file-<?php echo $i; ?>').click()">
                                <input type="file" id="file-<?php echo $i; ?>" name="media_file" accept="image/*">
                                <div class="file-input-text">
                                    点击选择图片<br>
                                    <small>支持 JPG, PNG, WebP</small>
                                </div>
                            </div>
                            
                            <button type="submit" class="upload-btn">
                                <?php echo isset($config['comphoto_' . $i]) ? '更新照片' : '上传照片'; ?>
                            </button>
                        </form>
                    </div>
                <?php
endfor; ?>
            </div>
        </div>
    </div>
    
   <script src="js/joinpage2upload.js?v=<?php echo time(); ?>"></script>    
</body>
</html>
