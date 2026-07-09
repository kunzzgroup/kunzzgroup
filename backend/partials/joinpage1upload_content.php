<?php
$uploadActionUrl = isset($uploadActionUrl) ? $uploadActionUrl : 'joinpage1upload.php';
$returnTo = isset($returnTo) ? $returnTo : '';
$mediaEntry = $config['joinus_background'] ?? null;
?>
<div class="container" data-joinpage1-content-root>
    <div class="header">
        <h1>加入我们页面管理</h1>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">仪表板</a> >
        <a href="media_manager.php">媒体管理</a> >
        <span>加入我们页面</span>
    </div>

    <div class="content">
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="alert alert-success">文件上传成功！</div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['debug'])): ?>
            <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
                <strong>调试信息：</strong><br>
                配置文件路径: <?php echo htmlspecialchars(realpath(__DIR__ . '/../../media_config.json') ?: '文件不存在', ENT_QUOTES, 'UTF-8'); ?><br>
                当前配置: <?php echo htmlspecialchars(json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?><br>
                文件是否存在: <?php echo ($mediaEntry && joinpage1upload_diskPath($mediaEntry['file']) && file_exists(joinpage1upload_diskPath($mediaEntry['file']))) ? '是' : '否'; ?>
            </div>
        <?php endif; ?>

        <div class="media-section">
            <h2>加入我们页面封面背景图片</h2>
            <form id="joinpage1-upload-form" method="post" enctype="multipart/form-data" class="upload-form" action="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="media_type" value="joinus_background">
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label>上传背景图片</label>
                    <div class="file-input" onclick="document.getElementById('joinus-page1-file').click()">
                        <input type="file" id="joinus-page1-file" name="media_file" accept="video/*,image/*">
                        <div class="file-input-text">
                            点击选择文件或拖拽到此处<br>
                            <small>支持 MP4, WebM, MOV, AVI, JPG, PNG, WebP 格式（HEIC 自动转换）(推荐尺寸: 1920x600)</small>
                        </div>
                    </div>

                    <?php if ($mediaEntry): ?>
                        <?php
                        $mediaSrc = joinpage1upload_resolveMediaSrc($mediaEntry['file']);
                        $mediaVersion = joinpage1upload_mediaVersion($mediaEntry['file']);
                        ?>
                        <div class="current-file">
                            <strong>当前文件:</strong> <?php echo htmlspecialchars(basename($mediaEntry['file']), ENT_QUOTES, 'UTF-8'); ?><br>
                            <small>类型: <?php echo htmlspecialchars($mediaEntry['type'], ENT_QUOTES, 'UTF-8'); ?> | 更新时间: <?php echo htmlspecialchars($mediaEntry['updated'], ENT_QUOTES, 'UTF-8'); ?></small>

                            <div class="preview-container">
                                <?php if ($mediaEntry['type'] === 'video'): ?>
                                    <video class="preview-video" controls style="width: 100%; max-height: 300px; object-fit: cover;">
                                        <source src="<?php echo htmlspecialchars($mediaSrc, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo $mediaVersion; ?>" type="video/mp4">
                                    </video>
                                <?php else: ?>
                                    <img class="preview-image" src="<?php echo htmlspecialchars($mediaSrc, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo $mediaVersion; ?>" alt="当前背景">
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
