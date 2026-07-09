<?php
$uploadActionUrl = isset($uploadActionUrl) ? $uploadActionUrl : 'joinpage2upload.php';
$returnTo = isset($returnTo) ? $returnTo : '';
$photoCount = 30;
$uploadedCount = joinpage2upload_countUploaded($config, $photoCount);
?>
<div
    class="container"
    data-joinpage2-content-root
    data-action-url="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>"
    data-return-to="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>"
>
    <div class="header">
        <h1>我们的足迹照片管理</h1>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">仪表板</a> >
        <a href="media_manager.php">媒体管理</a> >
        <span>我们的足迹照片</span>
    </div>

    <div class="content">
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="alert alert-success">照片上传成功！</div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="stats-bar">
            <div class="stats-item">
                总照片数: <span class="stats-number"><?php echo $photoCount; ?></span>
            </div>
            <div class="stats-item">
                已上传: <span class="stats-number"><?php echo $uploadedCount; ?></span>
            </div>
            <div class="stats-item">
                待上传: <span class="stats-number"><?php echo $photoCount - $uploadedCount; ?></span>
            </div>
        </div>

        <h2 class="section-title">照片上传管理</h2>

        <div class="photos-grid">
            <?php for ($i = 1; $i <= $photoCount; $i++): ?>
                <?php
                $photoKey = 'comphoto_' . $i;
                $fileExists = joinpage2upload_photoExists($config, $i);
                $displayUrl = joinpage2upload_getDisplayUrl($config, $i);
                ?>
                <div class="photo-card">
                    <div class="photo-header">
                        <div class="photo-number"><?php echo $i; ?></div>
                        <div class="photo-title">照片 #<?php echo $i; ?></div>
                    </div>

                    <?php if ($fileExists && $displayUrl): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($displayUrl, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>" alt="照片 <?php echo $i; ?>">
                            <form method="post" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="photo_number" value="<?php echo $i; ?>">
                                <button type="submit" class="delete-btn" title="删除照片">✕</button>
                            </form>
                            <div class="image-info">
                                <strong>已上传</strong><br>
                                <small>更新: <?php echo htmlspecialchars($config[$photoKey]['updated'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php if (!empty($config[$photoKey]['url'])): ?>
                                    <br><small>URL: <?php echo htmlspecialchars($config[$photoKey]['url'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="photo_number" value="<?php echo $i; ?>">
                        <?php if ($returnTo !== ''): ?>
                            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>

                        <div class="file-input" onclick="document.getElementById('file-<?php echo $i; ?>').click()">
                            <input type="file" id="file-<?php echo $i; ?>" name="media_file" accept="image/*">
                            <div class="file-input-text">
                                点击选择图片<br>
                                <small>支持 JPG, PNG, WebP（HEIC 自动转换）</small>
                            </div>
                        </div>

                        <button type="submit" class="upload-btn">
                            <?php echo isset($config[$photoKey]) ? '更新照片' : '上传照片'; ?>
                        </button>
                    </form>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div id="photo-lightbox">
    <span id="photo-lightbox-close">&#x2715;</span>
    <img id="photo-lightbox-img" src="" alt="查看照片">
</div>
