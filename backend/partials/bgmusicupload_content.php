<?php
$uploadActionUrl = isset($uploadActionUrl) ? $uploadActionUrl : 'bgmusicupload.php';
$returnTo = isset($returnTo) ? $returnTo : '';
?>
<div class="container" data-bgmusic-content-root>
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

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
            <div class="alert alert-success">音乐文件已删除！</div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="music-section">
            <h2>
                <span class="music-icon"></span>
                网站背景音乐设置
            </h2>

            <form id="bgmusic-upload-form" method="post" enctype="multipart/form-data" class="upload-form" action="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
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
                        <?php
                        $audioWebPath = bgmusicupload_resolveAudioSrc($config['background_music']['file']);
                        $audioInfo = bgmusicupload_getAudioInfo(
                            strpos($config['background_music']['file'], '../') === 0
                                ? __DIR__ . '/../../' . substr($config['background_music']['file'], 3)
                                : $config['background_music']['file']
                        );
                        ?>
                        <div class="current-music">
                            <strong>当前音乐文件:</strong>
                            <?php echo htmlspecialchars($config['background_music']['original_name'] ?? basename($config['background_music']['file']), ENT_QUOTES, 'UTF-8'); ?>

                            <?php if ($audioInfo): ?>
                            <div class="music-info">
                                <div class="info-item">
                                    <div class="label">格式</div>
                                    <div class="value"><?php echo strtoupper(htmlspecialchars($config['background_music']['format'], ENT_QUOTES, 'UTF-8')); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">文件大小</div>
                                    <div class="value"><?php echo htmlspecialchars($audioInfo['size_formatted'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">上传时间</div>
                                    <div class="value"><?php echo htmlspecialchars($config['background_music']['updated'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">最后修改</div>
                                    <div class="value"><?php echo htmlspecialchars($audioInfo['modified'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="audio-player">
                                <audio controls preload="metadata">
                                    <source src="<?php echo htmlspecialchars($audioWebPath, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo bgmusicupload_audioVersion($config['background_music']['file']); ?>" type="audio/<?php echo htmlspecialchars($config['background_music']['format'], ENT_QUOTES, 'UTF-8'); ?>">
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
            <form id="bgmusic-delete-form" method="post" class="delete-form" action="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('确定要删除当前音乐文件吗？文件将被永久删除。');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="btn-group">
                    <button type="submit" class="btn btn-danger">
                        删除当前音乐
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
