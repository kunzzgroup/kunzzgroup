<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    // 子域名存储配置
    $subdomainUrl = 'https://media.kunzzgroup.com/comphotos/';
    $uploadDir = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';
    $configFile = '../media_config.json';
    
    // 确保上传目录存在
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $error = "无法创建上传目录：" . $uploadDir . " (请检查服务器路径配置)";
        }
    }
    
    // 验证目录是否可写
    if (!isset($error) && !is_writable($uploadDir)) {
        $error = "上传目录不可写：" . $uploadDir . " (请检查文件夹权限)";
    }
    
    if (!isset($error)) {
        $file = $_FILES['media_file'];
        $photoNumber = $_POST['photo_number'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 允许的文件类型
        $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];

        // 验证文件安全性
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "文件上传错误，错误代码：" . $file['error'];
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 限制10MB
            $error = "文件大小超过10MB限制！";
        } elseif (!in_array($fileExtension, $allowedImage)) {
            $error = "只支持图片格式（JPG, PNG, WebP）！";
        } else {
            // MIME类型验证
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                $error = "文件MIME类型验证失败！";
            } else {
                // 生成新文件名
                $newFileName = $photoNumber . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // 设置文件权限
                    chmod($targetPath, 0644);

                    // 更新配置文件
                    $config = [];
                    if (file_exists($configFile)) {
                        $config = json_decode(file_get_contents($configFile), true) ?: [];
                    }
                    
                    $config['comphoto_' . $photoNumber] = [
                        'file' => $targetPath,  // 物理路径，用于后端验证
                        'url' => $subdomainUrl . $newFileName,  // 子域名URL，用于前端访问
                        'type' => 'image',
                        'updated' => date('Y-m-d H:i:s')
                    ];
                    
                    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $success = "照片 #{$photoNumber} 上传成功！已保存到子域名存储：" . $subdomainUrl . $newFileName;

                    // 页面重定向，清除缓存
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = window.location.href + '?updated=' + Date.now();
                        }, 1500);
                    </script>";
                } else {
                    $error = "照片上传失败！无法移动文件到：" . $targetPath;
                }
            }
        }
    }
}

// 读取当前配置
$config = [];
if (file_exists('../media_config.json')) {
    $config = json_decode(file_get_contents('../media_config.json'), true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我们的足迹照片管理 - KUNZZ HOLDINGS</title>
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
            margin-bottom: 8px;
            text-align: left;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.05em;
        }
        
        .breadcrumb {
            padding: clamp(2px, 1.04vw, 20px) 0px clamp(10px, 1.04vw, 20px);
            background: transparent;
            font-size: 0.9em;
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
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 0.9em;
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
        
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: clamp(10px, 1.04vw, 20px);
            margin-top: clamp(10px, 1.04vw, 20px);
        }
        
        .photo-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #000000ff;
            transition: all 0.3s ease;
            box-shadow: 0px 5px 15px rgb(0 0 0 / 60%);
        }
        
        .photo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .photo-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .photo-number {
            background: #000000ff;
            color: white;
            width: clamp(20px, 1.82vw, 35px);
            height: clamp(20px, 1.82vw, 35px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
            font-size: clamp(8px, 0.74vw, 14px);
        }
        
        .photo-title {
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 600;
            color: #333;
        }
        
        .file-input {
            border: 2px dashed #000000ff;
            border-radius: 8px;
            padding: clamp(10px, 1.04vw, 20px);
            text-align: center;
            background: #fff9f5;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: clamp(8px, 0.78vw, 15px);
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
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
        }
        
        .current-image {
            margin-bottom: 15px;
        }
        
        .current-image img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .image-info {
            margin-top: 8px;
            padding: 8px;
            background: #e8f4f8;
            border-radius: 6px;
            font-size: clamp(6px, 0.63vw, 12px);
            color: #155724;
        }
        
        .upload-btn {
            background: #f99e00;
            color: white;
            border: none;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.32vw, 6px);
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .upload-btn:hover {
            background: #f98500ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 0, 0.3);
        }
        
        .section-title {
            color: #333;
            font-size: clamp(16px, 1.5vw, 28px);
            margin-bottom: clamp(10px, 1.04vw, 20px);
            text-align: center;
            border-bottom: 2px solid #000000ff;
            padding-bottom: 8px;
        }
        
        .stats-bar {
            background: #f8f9fa;
            padding: clamp(6px, 0.78vw, 15px);
            border-radius: 8px;
            margin-bottom: clamp(10px, 1.3vw, 25px);
            text-align: center;
            border: 1px solid #dee2e6;
        }
        
        .stats-item {
            font-size: clamp(10px, 0.84vw, 16px);
            display: inline-block;
            margin: 0 20px;
            color: #666;
        }
        
        .stats-number {
            font-size: clamp(12px, 1.04vw, 20px);
            font-weight: 600;
            color: #000000ff;
        }
        
        @media (max-width: 768px) {
            .photos-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .content {
                padding: 20px;
            }
            
            .stats-item {
                display: block;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>我们的足迹照片管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>我们的足迹照片</span>
        </div>
        
        <div class="content">            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php
            // 统计已上传的照片（检查本地和子域名路径）
            $uploadedCount = 0;
            $subdomainPhysicalPath = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';

            for ($i = 1; $i <= 30; $i++) {
                $photoKey = 'comphoto_' . $i;
                if (isset($config[$photoKey])) {
                    // 优先检查子域名路径
                    $subdomainPath = $subdomainPhysicalPath . basename($config[$photoKey]['file']);
                    if (file_exists($subdomainPath)) {
                        $uploadedCount++;
                    } elseif (file_exists($config[$photoKey]['file'])) {
                        // 检查本地文件
                        $uploadedCount++;
                    }
                }
            }
            ?>
            
            <div class="stats-bar">
                <div class="stats-item">
                    总照片数: <span class="stats-number">30</span>
                </div>
                <div class="stats-item">
                    已上传: <span class="stats-number"><?php echo $uploadedCount; ?></span>
                </div>
                <div class="stats-item">
                    待上传: <span class="stats-number"><?php echo 30 - $uploadedCount; ?></span>
                </div>
            </div>
            
            <h2 class="section-title">照片上传管理</h2>
            
            <div class="photos-grid">
                <?php for ($i = 1; $i <= 30; $i++): ?>
                    <div class="photo-card">
                        <div class="photo-header">
                            <div class="photo-number"><?php echo $i; ?></div>
                            <div class="photo-title">照片 #<?php echo $i; ?></div>
                        </div>
                        
                        <?php
                        // 检查文件是否存在（优先检查子域名路径）
                        $photoKey = 'comphoto_' . $i;
                        $fileExists = false;
                        $displayUrl = '';
                        $subdomainPhysicalPath = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';

                        if (isset($config[$photoKey])) {
                            // 优先检查子域名路径
                            $subdomainPath = $subdomainPhysicalPath . basename($config[$photoKey]['file']);
                            if (file_exists($subdomainPath)) {
                                $fileExists = true;
                            } elseif (file_exists($config[$photoKey]['file'])) {
                                $fileExists = true;
                            }

                            // 使用子域名URL显示图片
                            if ($fileExists && isset($config[$photoKey]['url'])) {
                                $displayUrl = $config[$photoKey]['url'];
                            } elseif ($fileExists) {
                                $displayUrl = $config[$photoKey]['file'];
                            }
                        }

                        if ($fileExists && $displayUrl): ?>
                            <div class="current-image">
                                <img src="<?php echo $displayUrl; ?>?v=<?php echo time(); ?>" alt="照片 <?php echo $i; ?>">
                                <div class="image-info">
                                    <strong>已上传</strong><br>
                                    <small>更新: <?php echo $config[$photoKey]['updated']; ?></small>
                                    <?php if (isset($config[$photoKey]['url'])): ?>
                                        <br><small>URL: <?php echo htmlspecialchars($config[$photoKey]['url']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="photo_number" value="<?php echo $i; ?>">
                            
                            <div class="file-input" onclick="document.getElementById('file-<?php echo $i; ?>').click()">
                                <input type="file" id="file-<?php echo $i; ?>" name="media_file" accept="image/*">
                                <div class="file-input-text">
                                    点击选择图片<br>
                                    <small>支持 JPG, PNG, WebP</small>
                                </div>
                            </div>
                            
                            <button type="submit" class="upload-btn">
                                <?php echo isset($config['comphoto_' . $i]) ? '更新照片' : '上传照片'; ?>
                            </button>
                        </form>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <script>
        // 文件选择时显示文件名
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const textDiv = this.parentElement.querySelector('.file-input-text');
                if (this.files.length > 0) {
                    textDiv.innerHTML = `已选择: ${this.files[0].name}<br><small>点击上传按钮完成上传</small>`;
                }
            });
        });
        
        // 拖拽功能
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('dragover', (e) => {
                e.preventDefault();
                input.style.borderColor = '#e54a00';
                input.style.background = '#fff5f0';
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
                    textDiv.innerHTML = `已选择: ${files[0].name}<br><small>点击上传按钮完成上传</small>`;
                }
            });
        });
    </script>
</body>
</html>