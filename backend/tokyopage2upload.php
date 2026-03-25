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
    header("Location: /frontend/login.html");
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

    // 定义要处理的两个媒体键
    $mediaTypes = ['tokyo_about_image1', 'tokyo_about_image2'];

    // 读取当前配置
    $config = [];
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true) ?: [];
    }

    // 处理文本更新
    $section_title = $_POST['tokyo_about_title'] ?? '关于我们';
    $section_desc = $_POST['tokyo_about_desc'] ?? '';

    if (!isset($config['tokyo_about_text'])) {
        $config['tokyo_about_text'] = [];
    }
    $config['tokyo_about_text']['title'] = $section_title;
    $config['tokyo_about_text']['desc'] = $section_desc;

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
                    if (!isset($config[$type])) {
                        $config[$type] = ['type' => 'image'];
                    }
                    $config[$type]['file'] = $targetPath;
                    $config[$type]['updated'] = date('Y-m-d H:i:s');
                    $success_files[] = ($type === 'tokyo_about_image1' ? "左边小图" : "右边背景图");
                } else {
                    $errors[] = ($type === 'tokyo_about_image1' ? "左边小图" : "右边背景图") . "上传失败！";
                }
            } else {
                $errors[] = ($type === 'tokyo_about_image1' ? "左边小图" : "右边背景图") . "不支持的文件类型！";
            }
        }
    }

    // 始终保存文本部分（即使没有文件上传）
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    
    if (!empty($success_files)) {
        $success = implode('、', $success_files) . " 上传成功！且文字内容已保存。";
    } else {
        $success = "设置已成功更新！";
    }

    // 清除缓存重定向
    echo "<script>
        setTimeout(function() {
            window.location.href = window.location.href + (window.location.href.indexOf('?') > -1 ? '&' : '?') + 'updated=' + Date.now();
        }, 1500);
    </script>";
    
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
    <title>Tokyo Japanese Cuisine 第二页管理 - KUNZZ HOLDINGS</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Tokyo Japanese Cuisine 介绍页管理 (Section 2)</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard">仪表板</a> > 
            <a href="media_manager">媒体管理</a> > 
            <span>Tokyo Japanese Cuisine 第二页</span>
        </div>
        
        <div class="content">              
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" class="upload-form">
                <!-- 文字内容 section -->
                <div class="media-section" style="margin-bottom: 30px;">
                    <h2>关于我们 - 文字内容</h2>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>标题文字</label>
                        <input type="text" name="tokyo_about_title" value="<?php echo htmlspecialchars($config['tokyo_about_text']['title'] ?? '关于我们'); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>描述文字</label>
                        <textarea name="tokyo_about_desc" class="form-control" rows="6"><?php echo htmlspecialchars($config['tokyo_about_text']['desc'] ?? "我们是一家致力于提供精致料理与品越服务的日式料理餐厅。\n以极致的匠心打造美食。严选当季新鲜食材,融合传统与创意,呈现日本料理美。\n餐厅环境清雅舒适,充满日式格调。宾客在此不仅能品味精妙料理,更能感受到细致入微的服务与文化魅力。\n我们立志将每一次用餐变成难忘的美食之旅, 以品越的服务和精致的料理成为世界级日料品牌。"); ?></textarea>
                    </div>
                </div>

                <div class="media-section">
                    <h2>关于我们 - 左侧悬浮图 (Image 1)</h2>
                    <div class="form-group">
                        <div class="file-input" onclick="document.getElementById('tokyo-img1-file').click()">
                            <input type="file" id="tokyo-img1-file" name="tokyo_about_image1" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 500x500 (1:1)</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['tokyo_about_image1'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['tokyo_about_image1']['file']); ?><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_about_image1']['file']; ?>?v=<?php echo time(); ?>" alt="Image 1 预览">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="media-section" style="margin-top: 30px;">
                    <h2>关于我们 - 右侧背景图 (Image 2)</h2>
                    <div class="form-group">
                        <div class="file-input" onclick="document.getElementById('tokyo-img2-file').click()">
                            <input type="file" id="tokyo-img2-file" name="tokyo_about_image2" accept="image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>推荐尺寸: 800x1000 (或大图比例)</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['tokyo_about_image2'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['tokyo_about_image2']['file']); ?><br>
                                <div class="preview-container">
                                    <img class="preview-image" src="<?php echo $config['tokyo_about_image2']['file']; ?>?v=<?php echo time(); ?>" alt="Image 2 预览">
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
    <style>
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            background: #fff;
            font-family: inherit;
        }
        .media-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid #a68a64;
        }
        textarea.form-control {
            resize: vertical;
        }
    </style>
</body>
</html>
