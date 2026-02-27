<?php
if (!headers_sent()) {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
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
    $uploadDir = '../video/video/';
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
            
            $config[$mediaType] = [
                'file' => $targetPath,
                'type' => in_array($fileExtension, $allowedVideo) ? 'video' : 'image',
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>媒体管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/media_manager.css?v=2026">
    
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>媒体管理中心</h1>
        </div>
        
        <div class="content">
            <a href="dashboard.php" class="back-btn">← 返回仪表板</a>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 页面分类管理 -->
            <div class="media-section">
                <h2>背景音乐管理</h2>
                <div class="page-grid">
                    <a href="bgmusicupload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>管理网站所有页面的背景音乐</h3>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>

            <div class="media-section">
                <h2>首页管理</h2>
                <div class="page-grid">
                    <a href="homepage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>首页第一页</h3>
                        <p>管理首页背景视频/图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <div class="media-section">
                <h2>关于我们管理</h2>
                <div class="page-grid">
                    <a href="aboutpage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>关于我们第一页</h3>
                        <p>管理封面背景图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="aboutpage4upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>关于我们第四页</h3>
                        <p>管理发展历史图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 页面分类管理 -->
            <div class="media-section">
                <h2>旗下品牌管理</h2>
                <div class="page-grid">
                    <a href="tokyopage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>Tokyo 首页背景</h3>
                        <p>管理品牌页面首页背景图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="tokyopage5upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>Tokyo 位置信息</h3>
                        <p>管理总店分店地址电话信息</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <div class="media-section">
                <h2>加入我们管理</h2>
                <div class="page-grid">
                    <a href="joinpage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>加入我们页面</h3>
                        <p>管理招聘页面图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="joinpage2upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>我们的足迹照片</h3>
                        <p>管理34张公司活动照片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="joinpage3upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>招聘资料</h3>
                        <p>管理招聘职位</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="js/media_manager.js?v=2026"></script>
</body>
</html>