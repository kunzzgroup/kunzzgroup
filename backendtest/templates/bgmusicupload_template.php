<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>背景音乐管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="../css/bgmusicupload.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
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

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="music-section">
                <h2>
                    <span class="music-icon">🎵</span>
                    网站背景音乐设置
                </h2>
                
                <form method="post" enctype="multipart/form-data" class="upload-form" id="upload-form">
                    <div class="form-group">
                        <label>上传音乐文件</label>
                        <div class="file-input">
                            <input type="file" id="music-file" name="music_file" accept="audio/*">
                            <div class="file-input-text">
                                🎵 点击选择音乐文件或拖拽到此处<br>
                                <small>支持 MP3, WAV, OGG, M4A 格式 | 建议文件大小不超过 10MB</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['background_music'])): ?>
                            <div class="current-music">
                                <strong>当前音乐文件:</strong> <?php echo htmlspecialchars($config['background_music']['original_name'] ?? basename($config['background_music']['file'])); ?>
                                
                                <div class="music-info">
                                    <div class="info-item">
                                        <div class="label">格式</div>
                                        <div class="value"><?php echo strtoupper(htmlspecialchars($config['background_music']['format'] ?? 'unkown')); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">文件大小</div>
                                        <div class="value"><?php echo htmlspecialchars($fileSizeFormatted); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">上传时间</div>
                                        <div class="value"><?php echo htmlspecialchars($config['background_music']['updated'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                
                                <div class="audio-player">
                                    <audio controls preload="metadata">
                                        <source src="<?php echo htmlspecialchars($config['background_music']['file']); ?>?v=<?php echo time(); ?>" type="audio/<?php echo htmlspecialchars($config['background_music']['format'] ?? 'mpeg'); ?>">
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
                    </div>
                </form>

                <?php if (isset($config['background_music'])): ?>
                <div style="margin-top: 15px;">
                    <form method="post" onsubmit="return confirm('确定要删除当前音乐文件吗？文件将被永久删除。');">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">
                            删除当前音乐
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="tips">
                    <h4>使用提示：</h4>
                    <ul>
                        <li>上传新音乐会自动替换当前正在播放的背景音乐。</li>
                        <li>建议使用 MP3 格式，以获得最佳的浏览器兼容性。</li>
                        <li>背景音乐在网站加载时默认静音，用户需手动开启或交互后自动播放（浏览器策略限制）。</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/bgmusicupload.js"></script>
</body>
</html>
