<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
session_start();

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    // 根据文件类型决定上传目录
    $allowedVideo = ['mp4', 'webm', 'mov', 'avi'];
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
    $isVideo = in_array($fileExtension, $allowedVideo);
    $uploadDir = $isVideo ? '../video/video/' : '../images/images/';
    $configFile = '../media_config.json';  // 根目录 media_config.json（前端读取的）
    
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['media_file'];
    $mediaType = $_POST['media_type'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    $allowedTypes = array_merge($allowedVideo, $allowedImage);
    
    if (in_array($fileExtension, $allowedTypes)) {
        // 生成新文件名
        $newFileName = $mediaType . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // 更新配置文件
            $config = [];
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            $webPath = $uploadDir . $newFileName;
            
            $config[$mediaType] = [
                'file' => $webPath,
                'type' => $isVideo ? 'video' : 'image',
                'updated' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
            $success = "文件上传成功！";
        } else {
            $error = "文件上传失败！";
        }
    } else {
        $error = "不支持的文件类型！";
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
    <link rel="stylesheet" href="css/homepage1upload.css?v=<?php echo time(); ?>">
    <title>首页媒体管理 - KUNZZ HOLDINGS</title>
    
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>首页媒体管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>首页媒体</span>
        </div>
        
        <div class="content">           
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="media-section">
                <h2>首页第一页背景视频</h2>
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="media_type" value="home_background">
                    
                    <div class="form-group">
                        <label>上传背景视频/图片</label>
                        <div class="file-input" onclick="document.getElementById('home-page1-file').click()">
                            <input type="file" id="home-page1-file" name="media_file" accept="video/*,image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>支持 MP4, WebM, MOV, AVI, JPG, PNG, WebP 格式 (1920x1080)</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['home_background'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['home_background']['file']); ?><br>
                                <small>类型: <?php echo $config['home_background']['type']; ?> | 更新时间: <?php echo $config['home_background']['updated']; ?></small>
                                
                                <div class="preview-container">
                                    <?php if ($config['home_background']['type'] === 'video'): ?>
                                        <video class="preview-video" controls>
                                            <source src="<?php echo $config['home_background']['file']; ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img class="preview-image" src="<?php echo $config['home_background']['file']; ?>" alt="当前背景">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn">上传文件</button>
                </form>
            </div>
        </div>
    </div>
    <script src="js/homepage1upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
