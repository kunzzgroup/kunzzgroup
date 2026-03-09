<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}

session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$mediaType = 'sushi_menu';
$pageTitle = 'Sushi Menu 页面管理';

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $uploadDir = '../images/images/';
    $configFile = 'media_config.json';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['media_file'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 允许的文件类型：图片和PDF
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedPdf = ['pdf'];
    $allowedTypes = array_merge($allowedImage, $allowedPdf);

    if (in_array($fileExtension, $allowedTypes)) {
        $newFileName = $mediaType . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $config = [];
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            $config[$mediaType] = [
                'file' => $targetPath,
                'type' => in_array($fileExtension, $allowedPdf) ? 'pdf' : 'image',
                'updated' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
            $success = "Sushi Menu 上传成功！";

            echo "<script>
                setTimeout(function() {
                    window.location.href = window.location.href + '?updated=' + Date.now();
                }, 2000);
            </script>";
        } else {
            $error = "文件上传失败！";
        }
    } else {
        $error = "不支持的文件类型！仅支持 JPG, PNG, WEBP 和 PDF。";
    }
}

// 读取当前配置
$config = [];
if (file_exists('media_config.json')) {
    $config = json_decode(file_get_contents('media_config.json'), true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/menu_upload.css?v=<?php echo time(); ?>">
    <title><?php echo $pageTitle; ?> - KUNZZ HOLDINGS</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1><?php echo $pageTitle; ?></h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard">仪表板</a> > 
            <span><?php echo $pageTitle; ?></span>
        </div>
        
        <div class="content">              
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="media-section">
                <h2>上传 Sushi Menu (图片或 PDF)</h2>
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label>选择菜单文件</label>
                        <div class="file-input" onclick="document.getElementById('menu-file').click()">
                            <input type="file" id="menu-file" name="media_file" accept=".pdf,image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>支持 PDF, JPG, PNG, WebP 格式</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config[$mediaType])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config[$mediaType]['file']); ?><br>
                                <small>类型: <?php echo strtoupper($config[$mediaType]['type']); ?> | 更新时间: <?php echo $config[$mediaType]['updated']; ?></small>
                                
                                <div class="preview-container">
                                    <?php if ($config[$mediaType]['type'] === 'pdf'): ?>
                                        <embed src="<?php echo $config[$mediaType]['file']; ?>?v=<?php echo time(); ?>" type="application/pdf" class="preview-pdf" />
                                    <?php else: ?>
                                        <img class="preview-image" src="<?php echo $config[$mediaType]['file']; ?>?v=<?php echo time(); ?>" alt="当前菜单">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn">上传菜单</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/menu_upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
