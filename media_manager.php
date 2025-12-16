<?php
session_start();

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $uploadDir = 'video/video/';
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>媒体管理 - KUNZZ HOLDINGS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #faf7f2;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            width: clamp(600px, 52.08vw, 1000px);
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0px 5px 15px rgb(0 0 0 / 60%);
            overflow: hidden;
        }
        
        .header {
            background: #f99e00;
            color: black;
            padding: clamp(16px, 1.56vw, 30px);
            text-align: left;
        }
        
        .header h1 {
            font-size: clamp(20px, 2.6vw, 50px);
            margin-bottom: 0px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: clamp(10px, 0.84vw, 16px);
        }
        
        .content {
            padding: 20px 40px;
        }
        
        .media-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: clamp(10px, 1.04vw, 20px) clamp(20px, 1.56vw, 30px);
            margin-bottom: clamp(20px, 1.56vw, 30px);
            border-left: 5px solid #000000ff;
        }
        
        .media-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: clamp(18px, 1.5vw, 28px);
        }
        
        .upload-form {
            display: grid;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        
        .file-input {
            border: 2px dashed #FF5C00;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #fff9f5;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input:hover {
            border-color: #e54a00;
            background: #fff5f0;
        }
        
        .file-input input {
            display: none;
        }
        
        .file-input-text {
            color: #FF5C00;
            font-size: 1.1em;
            font-weight: 500;
        }
        
        .current-file {
            margin-top: 15px;
            padding: 15px;
            background: #e8f4f8;
            border-radius: 8px;
            border-left: 4px solid #FF5C00;
        }
        
        .current-file strong {
            color: #155724;
        }
        
        .btn {
            background: linear-gradient(135deg, #FF5C00 0%, #ff7a33 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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
            font-size: clamp(8px, 0.74vw, 14px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.32vw, 6px);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .page-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .page-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: clamp(15px, 1.3vw, 25px);
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .page-card:hover {
            border-color: #FF5C00;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
            text-decoration: none;
            color: #333;
        }
        
        .page-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
            display: block;
        }
        
        .page-card h3 {
            font-size: clamp(12px, 1.04vw, 20px);
            margin-bottom: 10px;
            color: #333;
        }
        
        .page-card p {
            color: #666;
            font-size: clamp(10px, 0.84vw, 16px);
            margin-bottom: 15px;
        }
        
        .page-arrow {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: clamp(18px, 1.25vw, 24px);
            color: #f99e00;
            transition: transform 0.3s ease;
        }
        
        .page-card:hover .page-arrow {
            transform: translateX(5px);
        }
        
        @media (max-width: 768px) {
            .page-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .page-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>媒体管理中心</h1>
        </div>
        
        <div class="content">
            <a href="dashboard.php" class="back-btn">← 返回仪表板</a>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 页面分类管理 -->
            <div class="media-section">
                <h2>背景音乐管理</h2>
                <div class="page-grid">
                    <a href="bgmusicupload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>管理网站所有页面的背景音乐</h3>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>

            <div class="media-section">
                <h2>首页管理</h2>
                <div class="page-grid">
                    <a href="homepage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>首页第一页</h3>
                        <p>管理首页背景视频/图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <div class="media-section">
                <h2>关于我们管理</h2>
                <div class="page-grid">
                    <a href="aboutpage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>关于我们第一页</h3>
                        <p>管理封面背景图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="aboutpage4upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>关于我们第四页</h3>
                        <p>管理发展历史图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <!-- 页面分类管理 -->
            <div class="media-section">
                <h2>旗下品牌管理</h2>
                <div class="page-grid">
                    <a href="tokyopage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>Tokyo 首页背景</h3>
                        <p>管理品牌页面首页背景图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="tokyopage5upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>Tokyo 位置信息</h3>
                        <p>管理总店分店地址电话信息</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <div class="media-section">
                <h2>加入我们管理</h2>
                <div class="page-grid">
                    <a href="joinpage1upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>加入我们页面</h3>
                        <p>管理招聘页面图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="joinpage2upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>我们的足迹照片</h3>
                        <p>管理34张公司活动照片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="joinpage3upload.php" class="page-card">
                        <div class="page-icon"></div>
                        <h3>招聘资料</h3>
                        <p>管理招聘职位</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 文件拖拽功能
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('dragover', (e) => {
                e.preventDefault();
                input.style.borderColor = '#5a6fd8';
                input.style.background = '#f0f2ff';
            });
            
            input.addEventListener('dragleave', (e) => {
                e.preventDefault();
                input.style.borderColor = '#667eea';
                input.style.background = '#f8f9ff';
            });
            
            input.addEventListener('drop', (e) => {
                e.preventDefault();
                const files = e.dataTransfer.files;
                const fileInput = input.querySelector('input[type="file"]');
                fileInput.files = files;
                
                input.style.borderColor = '#667eea';
                input.style.background = '#f8f9ff';
                
                // 显示文件名
                if (files.length > 0) {
                    const textDiv = input.querySelector('.file-input-text');
                    textDiv.innerHTML = `已选择: ${files[0].name}`;
                }
            });
        });
        
        // 文件选择时显示文件名
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