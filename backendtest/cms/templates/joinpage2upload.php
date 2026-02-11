<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我们的足迹照片管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/joinpage2upload.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
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
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

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
                    } elseif (file_exists($config[$photoKey]['file'])) {
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
                            } elseif (file_exists($config[$photoKey]['file'])) {
                                $fileExists = true;
                            }

                            // 使用子域名URL显示图片
                            if ($fileExists && isset($config[$photoKey]['url'])) {
                                $displayUrl = $config[$photoKey]['url'];
                            } elseif ($fileExists) {
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
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
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
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <script src="js/joinpage2upload.js"></script>
</body>
</html>
