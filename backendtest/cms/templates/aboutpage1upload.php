<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关于我们页面管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/aboutpage1upload.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>关于我们页面管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>关于我们页面</span>
        </div>
        
        <div class="content">           
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="media-section">
                <h2>关于我们页面封面背景视频/图片</h2>
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="media_type" value="about_background">
                    
                    <div class="form-group">
                        <label>上传背景视频/图片</label>
                        <div class="file-input" onclick="document.getElementById('about-page1-file').click()">
                            <input type="file" id="about-page1-file" name="media_file" accept="video/*,image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>支持 MP4, WebM, MOV, AVI, JPG, PNG, WebP 格式 (1920x600)</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['about_background'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['about_background']['file']); ?><br>
                                <small>类型: <?php echo $config['about_background']['type']; ?> | 更新时间: <?php echo $config['about_background']['updated']; ?></small>
                                
                                <div class="preview-container">
                                    <?php if ($config['about_background']['type'] === 'video'): ?>
                                        <video class="preview-video" controls>
                                            <source src="<?php echo $config['about_background']['file']; ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img class="preview-image" src="<?php echo $config['about_background']['file']; ?>" alt="当前背景">
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
    
    <script src="js/aboutpage1upload.js"></script>
</body>
</html>
