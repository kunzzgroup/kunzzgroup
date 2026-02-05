<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我们的足迹照片管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="../css/joinpage2upload.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
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
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
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
                        $photoKey = 'comphoto_' . $i;
                        $displayUrl = '';
                        if (isset($config[$photoKey])) {
                            $displayUrl = $config[$photoKey]['url'] ?? $config[$photoKey]['file'];
                            // Simple heuristic for relative paths
                            if (strpos($displayUrl, 'http') !== 0 && strpos($displayUrl, '/') !== 0) {
                                $displayUrl = '../' . $displayUrl;
                            }
                        }

                        if ($displayUrl): ?>
                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($displayUrl); ?>?v=<?php echo time(); ?>" alt="照片 <?php echo $i; ?>">
                                <div class="image-info">
                                    <strong>已上传</strong><br>
                                    <small>更新: <?php echo htmlspecialchars($config[$photoKey]['updated'] ?? 'N/A'); ?></small>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="photo_number" value="<?php echo $i; ?>">
                            
                            <div class="file-input">
                                <input type="file" name="media_file" accept="image/*">
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
    <script src="../js/joinpage2upload.js"></script>
</body>
</html>
