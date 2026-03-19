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

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    $uploadDir = '../audio/audio/';
    $configFile = '../music_config.json';
    
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
    $configFile = '../music_config.json';
    
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
if (file_exists('../music_config.json')) {
    $config = json_decode(file_get_contents('../music_config.json'), true) ?: [];
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
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>背景音乐管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="/backend/css/bgmusicupload.css?v=<?php echo time(); ?>">
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
    <script src="/backend/js/bgmusicupload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
