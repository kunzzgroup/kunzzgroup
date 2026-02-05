<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页媒体管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="../css/homepage1upload.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
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
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="media-section">
                <h2>首页第一页背景视频</h2>
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="media_type" value="home_background">
                    <input type="hidden" name="upload_dir" value="../video/video/">
                    
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
                                <strong>当前文件:</strong> <?php echo htmlspecialchars(basename($config['home_background']['file'])); ?><br>
                                <small>类型: <?php echo htmlspecialchars($config['home_background']['type']); ?> | 更新时间: <?php echo htmlspecialchars($config['home_background']['updated']); ?></small>
                                
                                <div class="preview-container">
                                    <?php if ($config['home_background']['type'] === 'video'): ?>
                                        <video class="preview-video" controls>
                                            <source src="<?php echo htmlspecialchars($config['home_background']['file']); ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img class="preview-image" src="<?php echo htmlspecialchars($config['home_background']['file']); ?>" alt="当前背景">
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
    <script src="../js/homepage1upload.js"></script>
</body>
</html>
