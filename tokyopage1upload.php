<?php
session_start();

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $uploadDir = 'images/images/';
    $configFile = 'media_config.json';
    
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['media_file'];
    $mediaType = $_POST['media_type'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 允许的文件类型
    $allowedVideo = ['mp4', 'webm', 'mov', 'avi'];
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedTypes = array_merge($allowedVideo, $allowedImage);

    if (in_array($fileExtension, $allowedTypes)) {
        // 生成新文件名
        $newFileName = $mediaType . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // 更新配置文件
            $config = [];
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            $config[$mediaType] = [
                'file' => $targetPath,
                'type' => in_array($fileExtension, $allowedVideo) ? 'video' : 'image',
                'updated' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
            $success = "文件上传成功！";

            // 添加页面重定向，清除缓存
            echo "<script>
                setTimeout(function() {
                    window.location.href = window.location.href + '?updated=' + Date.now();
                }, 2000);
            </script>";
        } else {
            $error = "文件上传失败！";
        }
    } else {
        $error = "不支持的文件类型！";
    }
}

// 读取当前配置
$config = [];
if (file_exists('media_config.json')) {
    $config = json_decode(file_get_contents('media_config.json'), true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokyo Japanese Cuisine页面管理 - KUNZZ HOLDINGS</title>
    <style>
        * {
            font-size: clamp(8px, 0.74vw, 14px);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #faf7f2;
            min-height: 100vh;
            padding: 0px;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            background: #faf7f2;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .header {
            background: transparent;
            color: #000000ff;
            text-align: center;
        }
        
        .header h1 {
            font-size: clamp(20px, 2.6vw, 50px);
            margin-bottom: 10px;
            text-align: left;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .breadcrumb {
            padding: clamp(2px, 1.04vw, 20px) 0px clamp(10px, 1.04vw, 20px);
            background: transparent;
        }
        
        .breadcrumb a {
            font-size: clamp(8px, 0.74vw, 14px);
            color: #f99e00;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .content {
            padding: 0;
        }
        
        .media-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: clamp(18px, 1.56vw, 30px);
            margin-bottom: 30px;
            border-left: 5px solid #000000ff;
            box-shadow: 0px 5px 15px rgb(0 0 0 / 60%);
        }
        
        .media-section h2 {
            color: #333;
            margin-bottom: clamp(10px, 1.04vw, 20px);
            font-size: clamp(16px, 1.5vw, 28px);
        }
        
        .upload-form {
            display: grid;
            gap: clamp(10px, 1.04vw, 20px);
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-size: clamp(10px, 0.84vw, 16px) !important;
            font-weight: 600;
            color: #555;
        }
        
        .file-input {
            border: 2px dashed #000000ff;
            border-radius: 10px;
            padding: clamp(20px, 2.08vw, 40px);
            text-align: center;
            background: #fff9f5;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input:hover {
            border-color: #000000ff;
            background: #fff5f0;
        }
        
        .file-input input {
            display: none;
        }
        
        .file-input-text {
            color: #000000ff;
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 500;
        }
        
        .current-file {
            font-size: clamp(10px, 0.84vw, 16px);
            margin-top: clamp(5px, 0.78vw, 15px);
            padding: 15px;
            background: #e8f4f8;
            border-radius: 8px;
            border-left: 4px solid #000000ff;
        }
        
        .current-file strong {
            font-size: clamp(10px, 0.84vw, 16px);
            color: #155724;
        }
        
        .btn {
            background: #f99e00;
            color: white;
            border: none;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #f98500ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 0, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            font-size: clamp(8px, 0.74vw, 14px);
            border-radius: clamp(4px, 0.32vw, 6px);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .preview-container {
            margin-top: clamp(10px, 1.04vw, 20px);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .preview-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .media-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Tokyo Japanese Cuisine 页面管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>Tokyo Japanese Cuisine 页面</span>
        </div>
        
        <div class="content">              
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- 调试信息 -->
            <?php if (isset($_GET['debug'])): ?>
                <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
                    <strong>调试信息：</strong><br>
                    配置文件路径: <?php echo realpath('media_config.json') ?: '文件不存在'; ?><br>
                    当前配置: <?php echo json_encode($config, JSON_PRETTY_PRINT); ?><br>
                    文件是否存在: <?php echo isset($config['tokyo_background']) && file_exists($config['tokyo_background']['file']) ? '是' : '否'; ?>
                </div>
            <?php endif; ?>
            
            <div class="media-section">
                <h2>Tokyo Japanese Cuisine 首页背景图片</h2>
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="media_type" value="tokyo_background">
                    
                    <div class="form-group">
                        <label>上传背景图片</label>
                        <div class="file-input" onclick="document.getElementById('tokyo-page1-file').click()">
                            <input type="file" id="tokyo-page1-file" name="media_file" accept="video/*,image/*">
                            <div class="file-input-text">
                                点击选择文件或拖拽到此处<br>
                                <small>支持 MP4, WebM, MOV, AVI, JPG, PNG, WebP 格式 (推荐尺寸: 1920x1080)</small>
                            </div>
                        </div>
                        
                        <?php if (isset($config['tokyo_background'])): ?>
                            <div class="current-file">
                                <strong>当前文件:</strong> <?php echo basename($config['tokyo_background']['file']); ?><br>
                                <small>类型: <?php echo $config['tokyo_background']['type']; ?> | 更新时间: <?php echo $config['tokyo_background']['updated']; ?></small>
                                
                                <div class="preview-container">
                                    <?php if ($config['tokyo_background']['type'] === 'video'): ?>
                                        <video class="preview-video" controls style="width: 100%; max-height: 300px; object-fit: cover;">
                                            <source src="<?php echo $config['tokyo_background']['file']; ?>?v=<?php echo time(); ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img class="preview-image" src="<?php echo $config['tokyo_background']['file']; ?>?v=<?php echo time(); ?>" alt="当前背景">
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
    
    <script>
        // 文件拖拽和选择功能
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('dragover', (e) => {
                e.preventDefault();
                input.style.borderColor = '#0ea5e9';
                input.style.background = '#e0f2fe';
            });
            
            input.addEventListener('dragleave', (e) => {
                e.preventDefault();
                input.style.borderColor = '#FF5C00';
                input.style.background = '#fff9f5';
            });
            
            input.addEventListener('drop', (e) => {
                e.preventDefault();
                const files = e.dataTransfer.files;
                const fileInput = input.querySelector('input[type="file"]');
                fileInput.files = files;
                
                input.style.borderColor = '#FF5C00';
                input.style.background = '#fff9f5';
                
                if (files.length > 0) {
                    const textDiv = input.querySelector('.file-input-text');
                    textDiv.innerHTML = `已选择: ${files[0].name}`;
                }
            });
        });
        
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const textDiv = this.parentElement.querySelector('.file-input-text');
                if (this.files.length > 0) {
                    textDiv.innerHTML = `已选择: ${this.files[0].name}`;
                }
            });
        });
    </script>
</body>
</html>