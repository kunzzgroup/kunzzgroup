<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>背景音乐管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/bgmusicupload.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>背景音乐管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>背景音乐</span>
        </div>
        
        <div class="content">  
            
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="alert alert-success">音乐文件上传成功！</div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="music-section">
                <h2>
                    <span class="music-icon"></span>
                    网站背景音乐设置
                </h2>
                
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label>上传音乐文件</label>
                        <div class="file-input" onclick="document.getElementById('music-file').click()">
                            <input type="file" id="music-file" name="music_file" accept="audio/*">
                            <div class="file-input-text">
                                🎵 点击选择音乐文件或拖拽到此处<br>
                                <small>支持 MP3, WAV, OGG, M4A 格式 | 建议文件大小不超过 10MB</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['background_music'])): ?>
                            <div class="current-music">
                                <strong>当前音乐文件:</strong> <?php echo $config['background_music']['original_name'] ?? basename($config['background_music']['file']); ?>
                                
                                <?php 
                                $audioInfo = getAudioInfo($config['background_music']['file']);
                                if ($audioInfo): 
                                ?>
                                <div class="music-info">
                                    <div class="info-item">
                                        <div class="label">格式</div>
                                        <div class="value"><?php echo strtoupper($config['background_music']['format']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">文件大小</div>
                                        <div class="value"><?php echo $audioInfo['size_formatted']; ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">上传时间</div>
                                        <div class="value"><?php echo $config['background_music']['updated']; ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">最后修改</div>
                                        <div class="value"><?php echo $audioInfo['modified']; ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="audio-player">
                                    <audio controls preload="metadata">
                                        <source src="<?php echo $config['background_music']['file']; ?>?v=<?php echo filemtime($config['background_music']['file']); ?>" type="audio/<?php echo $config['background_music']['format']; ?>">
                                        您的浏览器不支持音频播放器。
                                    </audio>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="current-music">
                                <strong>状态:</strong> 暂未上传背景音乐文件
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn">
                            上传新音乐
                        </button>
                        
                        <?php if (isset($config['background_music'])): ?>
                        <form method="post" style="display: inline;" onsubmit="return confirm('确定要删除当前音乐文件吗？文件将被永久删除。');">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger">
                                删除当前音乐
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/bgmusicupload.js"></script>
</body>
</html>
