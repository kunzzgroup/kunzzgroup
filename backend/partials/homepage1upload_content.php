<?php
$uploadActionUrl = isset($uploadActionUrl) ? $uploadActionUrl : 'homepage1upload.php';
$returnTo = isset($returnTo) ? $returnTo : '';
$mediaEntry = $config['home_background'] ?? null;
?>
<div class="container" data-homepage1-content-root>
    <div class="header">
        <h1>首页媒体管理</h1>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">仪表板</a> >
        <a href="media_manager.php">媒体管理</a> >
        <span>首页媒体</span>
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

        <div class="media-section">
            <h2>首页第一页背景视频</h2>
            <form id="homepage1-upload-form" method="post" enctype="multipart/form-data" class="upload-form" action="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="media_type" value="home_background">
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label>上传背景视频/图片</label>
                    <div class="file-input" onclick="document.getElementById('home-page1-file').click()">
                        <input type="file" id="home-page1-file" name="media_file" accept="video/*,image/*">
                        <div class="file-input-text">
                            点击选择文件或拖拽到此处<br>
                            <small>支持 MP4, WebM, MOV, AVI, JPG, PNG, WebP 格式（HEIC 自动转换）(1920x1080)</small>
                        </div>
                    </div>

                    <?php if ($mediaEntry): ?>
                        <?php
                        $mediaSrc = homepage1upload_resolveMediaSrc($mediaEntry['file']);
                        $mediaVersion = homepage1upload_mediaVersion($mediaEntry['file']);
                        ?>
                        <div class="current-file">
                            <strong>当前文件:</strong> <?php echo htmlspecialchars(basename($mediaEntry['file']), ENT_QUOTES, 'UTF-8'); ?><br>
                            <small>类型: <?php echo htmlspecialchars($mediaEntry['type'], ENT_QUOTES, 'UTF-8'); ?> | 更新时间: <?php echo htmlspecialchars($mediaEntry['updated'], ENT_QUOTES, 'UTF-8'); ?></small>

                            <div class="preview-container">
                                <?php if ($mediaEntry['type'] === 'video'): ?>
                                    <video class="preview-video" controls>
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
