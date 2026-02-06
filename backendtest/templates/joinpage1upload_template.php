<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>加入我们页面管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="../css/joinpage1upload.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>加入我们页面管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard">仪表板</a> > 
            <a href="media_manager">媒体管理</a> > 
            <span>加入我们页面</span>
        </div>
        
        <div class="content">          
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
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
                                <strong>当前文件:</strong> <?php echo htmlspecialchars(basename($config['joinus_background']['file'])); ?><br>
                                <small>类型: <?php echo htmlspecialchars($config['joinus_background']['type']); ?> | 更新时间: <?php echo htmlspecialchars($config['joinus_background']['updated']); ?></small>
                                
                                <div class="preview-container">
                                    <?php if ($config['joinus_background']['type'] === 'video'): ?>
                                        <video class="preview-video" controls style="width: 100%; max-height: 300px; object-fit: cover;">
                                            <source src="../<?php echo htmlspecialchars(ltrim($config['joinus_background']['file'], '../')); ?>?v=<?php echo time(); ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img class="preview-image" src="../<?php echo htmlspecialchars(ltrim($config['joinus_background']['file'], '../')); ?>?v=<?php echo time(); ?>" alt="当前背景">
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
    <script src="../js/joinpage1upload.js"></script>
</body>
</html>
