<?php
session_start();

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    $uploadDir = 'audio/audio/';
    $configFile = 'music_config.json';
    
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['music_file'];
    
    // 检查上传错误
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "文件上传失败，错误代码：" . $file['error'];
    } else {
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 允许的音频文件类型
        $allowedAudio = ['mp3', 'wav', 'ogg', 'm4a'];

        if (in_array($fileExtension, $allowedAudio)) {
            // 读取旧配置并删除所有旧文件
            $oldConfig = [];
            if (file_exists($configFile)) {
                $oldConfig = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            // 删除所有可能存在的旧音乐文件
            $possibleExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
            foreach ($possibleExtensions as $ext) {
                $oldFile = $uploadDir . 'music.' . $ext;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            // 如果配置中有旧文件路径，也删除
            if (isset($oldConfig['background_music']['file']) && file_exists($oldConfig['background_music']['file'])) {
                unlink($oldConfig['background_music']['file']);
            }
            
            // 生成新文件名并上传
            $newFileName = 'music.' . $fileExtension;
            $targetPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // 更新配置文件
            $config = [];
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            $config['background_music'] = [
                'file' => $targetPath,
                'type' => 'audio',
                'format' => $fileExtension,
                'updated' => date('Y-m-d H:i:s'),
                'filesize' => filesize($targetPath),
                'original_name' => $file['name']
            ];
            
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // 使用HTTP重定向而不是JavaScript
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&t=" . time());
            exit();
            
        } else {
                $error = "文件移动失败！请检查目录权限。";
            }
        } else {
            $error = "不支持的文件类型！请上传 MP3、WAV、OGG 或 M4A 格式的音频文件。";
        }
    }
}

// 处理音乐删除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $configFile = 'music_config.json';
    
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true) ?: [];
        
        if (isset($config['background_music']['file']) && file_exists($config['background_music']['file'])) {
            // 直接删除文件
            if (unlink($config['background_music']['file'])) {
                unset($config['background_music']);
                file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $success = "音乐文件已删除！";
            } else {
                $error = "删除文件时出错！";
            }
        } else {
            $error = "文件不存在！";
        }
    }
}

// 读取当前配置
$config = [];
if (file_exists('music_config.json')) {
    $config = json_decode(file_get_contents('music_config.json'), true) ?: [];
}

// 获取音频文件信息
function getAudioInfo($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    
    $info = [];
    $info['size'] = filesize($filePath);
    $info['size_formatted'] = formatFileSize($info['size']);
    $info['modified'] = date('Y-m-d H:i:s', filemtime($filePath));
    
    return $info;
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>背景音乐管理 - KUNZZ HOLDINGS</title>
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
        
        .music-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: clamp(18px, 1.56vw, 30px);
            margin-bottom: 30px;
            border-left: 5px solid #000000ff;
            box-shadow: 0px 5px 15px rgb(0 0 0 / 60%);
        }
        
        .music-section h2 {
            color: #333;
            margin-bottom: clamp(10px, 1.04vw, 20px);
            font-size: clamp(16px, 1.5vw, 28px);
            display: flex;
            align-items: center;
            gap: 0px;
        }
        
        .music-icon {
            font-size: 1.2em;
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
        
        .current-music {
            font-size: clamp(10px, 0.84vw, 16px);
            margin-top: clamp(5px, 0.78vw, 15px);
            padding: 20px;
            background: #e8f4f8;
            border-radius: 8px;
            border-left: 4px solid #000000ff;
        }
        
        .current-music strong {
            font-size: clamp(10px, 0.84vw, 16px);
            color: #155724;
        }
        
        .music-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: clamp(10px, 0.78vw, 15px) 0;
        }
        
        .info-item {
            background: white;
            padding: clamp(8px, 0.63vw, 12px);
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        
        .info-item .label {
            font-size: clamp(8px, 0.74vw, 14px);
            color: #666;
            margin-bottom: 4px;
        }
        
        .info-item .value {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            color: #333;
        }
        
        .audio-player {
            margin: clamp(0px, 0.78vw, 15px) 0;
            width: 100%;
        }
        
        .audio-player audio {
            width: 100%;
            max-width: 500px;
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background: #f98500ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 0, 0.3);
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            background: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: clamp(0px, 1.04vw, 20px);
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
        
        .tips {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .tips h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .tips ul {
            color: #856404;
            margin-left: 20px;
        }
        
        .tips li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .music-section {
                padding: 20px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .music-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
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

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="music-section">
                <h2>
                    <span class="music-icon"></span>
                    网站背景音乐设置
                </h2>
                
                <form method="post" enctype="multipart/form-data" class="upload-form">
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
                            <div class="current-music">
                                <strong>当前音乐文件:</strong> <?php echo $config['background_music']['original_name'] ?? basename($config['background_music']['file']); ?>
                                
                                <?php 
                                $audioInfo = getAudioInfo($config['background_music']['file']);
                                if ($audioInfo): 
                                ?>
                                <div class="music-info">
                                    <div class="info-item">
                                        <div class="label">格式</div>
                                        <div class="value"><?php echo strtoupper($config['background_music']['format']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">文件大小</div>
                                        <div class="value"><?php echo $audioInfo['size_formatted']; ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">上传时间</div>
                                        <div class="value"><?php echo $config['background_music']['updated']; ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">最后修改</div>
                                        <div class="value"><?php echo $audioInfo['modified']; ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="audio-player">
                                    <audio controls preload="metadata">
                                        <source src="<?php echo $config['background_music']['file']; ?>?v=<?php echo filemtime($config['background_music']['file']); ?>" type="audio/<?php echo $config['background_music']['format']; ?>">
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
                        
                        <?php if (isset($config['background_music'])): ?>
                        <form method="post" style="display: inline;" onsubmit="return confirm('确定要删除当前音乐文件吗？文件将被永久删除。');">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger">
                                删除当前音乐
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // 文件拖拽功能
        const fileInput = document.querySelector('.file-input');
        const fileInputElement = document.getElementById('music-file');
        
        fileInput.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileInput.style.borderColor = '#e54a00';
            fileInput.style.background = '#fff5f0';
        });
        
        fileInput.addEventListener('dragleave', (e) => {
            e.preventDefault();
            fileInput.style.borderColor = '#FF5C00';
            fileInput.style.background = '#fff9f5';
        });
        
        fileInput.addEventListener('drop', (e) => {
            e.preventDefault();
            const files = e.dataTransfer.files;
            
            if (files.length > 0) {
                const file = files[0];
                
                // 验证文件类型
                const allowedTypes = ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a', 'audio/mpeg'];
                if (!allowedTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|ogg|m4a)$/i)) {
                    alert('请选择有效的音频文件（MP3, WAV, OGG, M4A）');
                    return;
                }
                
                // 验证文件大小（10MB）
                if (file.size > 10 * 1024 * 1024) {
                    alert('文件大小不能超过 10MB');
                    return;
                }
                
                fileInputElement.files = files;
                updateFileInputText(file.name);
            }
            
            fileInput.style.borderColor = '#FF5C00';
            fileInput.style.background = '#fff9f5';
        });
        
        // 文件选择功能
        fileInputElement.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                
                // 验证文件大小
                if (file.size > 10 * 1024 * 1024) {
                    alert('文件大小不能超过 10MB');
                    this.value = '';
                    return;
                }
                
                updateFileInputText(file.name);
            }
        });
        
        function updateFileInputText(fileName) {
            const textDiv = document.querySelector('.file-input-text');
            textDiv.innerHTML = `🎵 已选择: ${fileName}<br><small>点击"上传新音乐"按钮完成上传</small>`;
        }
        
        // 表单提交验证
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!fileInputElement.files.length) {
                e.preventDefault();
                alert('请先选择要上传的音乐文件');
            }
        });
    </script>
</body>
</html>