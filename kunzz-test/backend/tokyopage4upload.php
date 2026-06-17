<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: /frontend/login.html");
    exit();
}

$uploadDir = '../images/images/';
$configFile = '../media_config.json';

// 读取当前配置
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $success_items = [];
    $errors = [];

    // 定义要处理的两个特色卡片键
    $cards = [
        '1' => ['key_base' => 'tokyo_featured_1', 'label' => '特色推荐 1 (Sushi & Sashimi)'],
        '2' => ['key_base' => 'tokyo_featured_2', 'label' => '特色推荐 2 (Grand Menu)']
    ];

    foreach ($cards as $id => $card) {
        $key = $card['key_base'];
        
        // 处理文本更新
        $title = $_POST[$key . '_title'] ?? '';
        $desc = $_POST[$key . '_desc'] ?? '';
        
        if (!isset($config[$key])) {
            $config[$key] = ['type' => 'image', 'file' => ''];
        }
        
        $config[$key]['title'] = $title;
        $config[$key]['desc'] = $desc;

        // 处理文件上传
        if (isset($_FILES[$key . '_file']) && $_FILES[$key . '_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$key . '_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedImage = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];

            if (in_array($fileExtension, $allowedImage)) {
                $newFileName = $key . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // HEIC/HEIF 自动转换为 JPG
                    $converted = convertHeicToJpg($targetPath, $fileExtension);
                    if ($converted['converted']) {
                        $targetPath = $converted['path'];
                        $newFileName = basename($converted['path']);
                        $fileExtension = 'jpg';
                    }
                    $config[$key]['file'] = $targetPath;
                    $config[$key]['updated'] = date('Y-m-d H:i:s');
                } else {
                    $errors[] = $card['label'] . " 图片上传失败！";
                }
            } else {
                $errors[] = $card['label'] . " 不支持的文件类型！";
            }
        }
    }

    if (empty($errors)) {
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        $success = "设置已成功更新！";
        
        // 强制刷新
        echo "<script>
            setTimeout(function() {
                window.location.href = window.location.href + (window.location.href.indexOf('?') > -1 ? '&' : '?') + 'updated=' + Date.now();
            }, 1000);
        </script>";
    } else {
        $error = implode('<br>', $errors);
    }
    
    // 重新从保存后的配置加载
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true) ?: [];
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/tokyopage1upload.css?v=<?php echo time(); ?>">
    <title>Tokyo Japanese Cuisine 第四页管理 - KUNZZ HOLDINGS</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Tokyo Japanese Cuisine 特色推荐管理 (Section 4)</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard">仪表板</a> > 
            <a href="media_manager">媒体管理</a> > 
            <span>Tokyo Japanese Cuisine 第四页</span>
        </div>
        
        <div class="content">              
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" class="upload-form">
                
                <!-- FEATURED 1 -->
                <div class="media-section">
                    <h2>特色推荐 1 (左侧卡片)</h2>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>标题文字</label>
                        <input type="text" name="tokyo_featured_1_title" value="<?php echo htmlspecialchars($config['tokyo_featured_1']['title'] ?? 'Sushi & Sashimi'); ?>" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>描述文字</label>
                        <textarea name="tokyo_featured_1_desc" class="form-control" rows="2"><?php echo htmlspecialchars($config['tokyo_featured_1']['desc'] ?? '探索各种新鲜寿司与刺身，每一口都是深海的馈赠。'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>上传图片</label>
                        <div class="file-input" onclick="document.getElementById('tokyo-f1-file').click()">
                            <input type="file" id="tokyo-f1-file" name="tokyo_featured_1_file" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 800x600 (4:3)</small>
                            </div>
                        </div>
                        <?php if (!empty($config['tokyo_featured_1']['file'])): ?>
                            <div class="current-file">
                                <strong>当前图片:</strong><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_featured_1']['file']; ?>?v=<?php echo time(); ?>" alt="Featured 1 预览">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FEATURED 2 -->
                <div class="media-section" style="margin-top: 40px;">
                    <h2>特色推荐 2 (右侧卡片)</h2>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>标题文字</label>
                        <input type="text" name="tokyo_featured_2_title" value="<?php echo htmlspecialchars($config['tokyo_featured_2']['title'] ?? 'Grand Menu'); ?>" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>描述文字</label>
                        <textarea name="tokyo_featured_2_desc" class="form-control" rows="2"><?php echo htmlspecialchars($config['tokyo_featured_2']['desc'] ?? '不仅是品味，更是对正宗日本料理的致敬。'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>上传图片</label>
                        <div class="file-input" onclick="document.getElementById('tokyo-f2-file').click()">
                            <input type="file" id="tokyo-f2-file" name="tokyo_featured_2_file" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 800x600 (4:3)</small>
                            </div>
                        </div>
                        <?php if (!empty($config['tokyo_featured_2']['file'])): ?>
                            <div class="current-file">
                                <strong>当前图片:</strong><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_featured_2']['file']; ?>?v=<?php echo time(); ?>" alt="Featured 2 预览">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn" style="margin-top: 30px;">保存全部设置</button>
            </form>
        </div>
    </div>

    <script src="js/tokyopage1upload.js?v=<?php echo time(); ?>"></script>
    <style>
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            background: #fff;
        }
        .media-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid #c9a96e;
        }
    </style>
</body>
</html>
