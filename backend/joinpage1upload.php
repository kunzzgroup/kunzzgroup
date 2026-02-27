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
    $uploadDir = '../images/images/';
    $configFile = '../media_config.json';
    
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['media_file'];
    $mediaType = $_POST['media_type'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 允许的文件类型
    $allowedVideo = ['mp4', 'webm', 'mov', 'avi'];
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
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
            
            // 使用绝对路径（从网站根目录开始）
            $webPath = 'images/images/' . $newFileName;
            
            $config[$mediaType] = [
                'file' => $webPath,
                'type' => in_array($fileExtension, $allowedVideo) ? 'video' : 'image',
                'updated' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
            $success = "文件上传成功！";

            // 添加页面重定向，清除缓存
            echo "<script>
                setTimeout(function() {
                    window.location.href = window.location.href + '?updated=' + Date.now();
                }, 2000);
            </script>";
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
    <title>加入我们页面管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/joinpage1upload.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>加入我们页面管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>加入我们页面</span>
        </div>
        
        <div class="content">          
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- 调试信息 -->
            <?php if (isset($_GET['debug'])): ?>
                <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
                    <strong>调试信息：</strong><br>
                    配置文件路径: <?php echo realpath('../media_config.json') ?: '文件不存在'; ?><br>
                    当前配置: <?php echo json_encode($config, JSON_PRETTY_PRINT); ?><br>
                    文件是否存在: <?php echo isset($config['joinus_background']) && file_exists($config['joinus_background']['file']) ? '是' : '否'; ?>
                </div>
            <?php endif; ?>
            
            <div class="media-section">
                <h2>加入我们页面封面背景图片</h2>
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="media_type" value="joinus_background">
                    
                    <div class="form-group">
                        <label>上传背景图片</label>
                        <div class="file-input" onclick="document.getElementById('joinus-page1-file').click()">
                            <input type="file" id="joinus-page1-file" name="media_file" accept="video/*,image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>支持 MP4, WebM, MOV, AVI, JPG, PNG, WebP 格式 (推荐尺寸: 1920x600)</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['joinus_background'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['joinus_background']['file']); ?><br>
                                <small>类型: <?php echo $config['joinus_background']['type']; ?> | 更新时间: <?php echo $config['joinus_background']['updated']; ?></small>
                                
                                <div class="preview-container">
                                    <?php if ($config['joinus_background']['type'] === 'video'): ?>
                                        <video class="preview-video" controls style="width: 100%; max-height: 300px; object-fit: cover;">
                                            <source src="<?php echo $config['joinus_background']['file']; ?>?v=<?php echo time(); ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img class="preview-image" src="<?php echo $config['joinus_background']['file']; ?>?v=<?php echo time(); ?>" alt="当前背景">
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
    
    <script src="js/joinpage1upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
