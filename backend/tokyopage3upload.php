<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

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
    header("Location: /frontend/login");
    exit();
}

$uploadDir = '../images/images/';
$configFile = '../media_config.json';

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $success_files = [];
    $errors = [];

    // 定义要处理的四个媒体键
    $mediaTypes = ['tokyo_mission', 'tokyo_vision', 'tokyo_values', 'tokyo_character'];
    $mediaLabels = [
        'tokyo_mission' => '使命 Mission',
        'tokyo_vision' => '愿景 Vision',
        'tokyo_values' => '价值观 Values',
        'tokyo_character' => '人品 Character'
    ];

    // 读取当前配置
    $config = [];
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true) ?: [];
    }

    foreach ($mediaTypes as $type) {
        if (isset($_FILES[$type]) && $_FILES[$type]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$type];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // 允许的文件类型
            $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedImage)) {
                // 生成新文件名
                $newFileName = $type . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $config[$type] = [
                        'file' => $targetPath,
                        'type' => 'image',
                        'updated' => date('Y-m-d H:i:s')
                    ];
                    $success_files[] = $mediaLabels[$type];
                } else {
                    $errors[] = $mediaLabels[$type] . "上传失败！";
                }
            } else {
                $errors[] = $mediaLabels[$type] . "不支持的文件类型！";
            }
        }
    }

    if (!empty($success_files)) {
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        $success = implode('、', $success_files) . " 上传成功！";

        // 清除缓存重定向
        echo "<script>
            setTimeout(function() {
                window.location.href = window.location.href + (window.location.href.indexOf('?') > -1 ? '&' : '?') + 'updated=' + Date.now();
            }, 2000);
        </script>";
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}

// 再次读取最新配置用于显示
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/tokyopage1upload.css?v=<?php echo time(); ?>">
    <title>Tokyo Japanese Cuisine 第三页管理 - KUNZZ HOLDINGS</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Tokyo Japanese Cuisine 文化墙管理 (Section 3)</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard">仪表板</a> > 
            <a href="media_manager">媒体管理</a> > 
            <span>Tokyo Japanese Cuisine 第三页</span>
        </div>
        
        <div class="content">              
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" class="upload-form">
                <div class="media-section">
                    <h2>使命 Mission</h2>
                    <div class="form-group">
                        <div class="file-input" onclick="document.getElementById('tokyo-mission-file').click()">
                            <input type="file" id="tokyo-mission-file" name="tokyo_mission" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 800x800</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['tokyo_mission'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['tokyo_mission']['file']); ?><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_mission']['file']; ?>?v=<?php echo time(); ?>" alt="Mission 预览">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="media-section" style="margin-top: 30px;">
                    <h2>愿景 Vision</h2>
                    <div class="form-group">
                        <div class="file-input" onclick="document.getElementById('tokyo-vision-file').click()">
                            <input type="file" id="tokyo-vision-file" name="tokyo_vision" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 800x800</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['tokyo_vision'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['tokyo_vision']['file']); ?><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_vision']['file']; ?>?v=<?php echo time(); ?>" alt="Vision 预览">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="media-section" style="margin-top: 30px;">
                    <h2>价值观 Values</h2>
                    <div class="form-group">
                        <div class="file-input" onclick="document.getElementById('tokyo-values-file').click()">
                            <input type="file" id="tokyo-values-file" name="tokyo_values" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 800x800</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['tokyo_values'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['tokyo_values']['file']); ?><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_values']['file']; ?>?v=<?php echo time(); ?>" alt="Values 预览">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="media-section" style="margin-top: 30px;">
                    <h2>人品 Character</h2>
                    <div class="form-group">
                        <div class="file-input" onclick="document.getElementById('tokyo-character-file').click()">
                            <input type="file" id="tokyo-character-file" name="tokyo_character" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 800x800</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['tokyo_character'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['tokyo_character']['file']); ?><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_character']['file']; ?>?v=<?php echo time(); ?>" alt="Character 预览">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn" style="margin-top: 20px;">上传全部更新</button>
            </form>
        </div>
    </div>

    <script src="js/tokyopage1upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
