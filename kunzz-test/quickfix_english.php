<?php
/**
 * 英文版上传问题快速修复脚本
 * 
 * 使用方法：
 * 1. 将此文件上传到网站根目录
 * 2. 在浏览器访问: yoursite.com/quickfix_english.php
 * 3. 按照提示操作
 */

session_start();

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查是否是POST请求
$action = $_POST['action'] ?? '';
$message = '';
$error = '';

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>英文版上传问题修复工具</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .status-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .status-label {
            font-weight: 500;
            color: #495057;
        }
        
        .status-value {
            font-size: 14px;
        }
        
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .log-box {
            background: #2d3436;
            color: #dfe6e9;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 20px;
        }
        
        .log-success {
            color: #00b894;
        }
        
        .log-error {
            color: #ff7675;
        }
        
        .log-warning {
            color: #fdcb6e;
        }
        
        .log-info {
            color: #74b9ff;
        }
        
        .config-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .config-preview pre {
            margin: 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 英文版上传问题修复工具</h1>
        <p class="subtitle">自动诊断和修复英文版时间线配置问题</p>
        
        <?php
        // 查找配置文件
        $configPaths = [
            'timeline_config_en.json',
            '../timeline_config_en.json',
            'frontend_en/timeline_config_en.json',
            'frontend/timeline_config_en.json'
        ];
        
        $configFile = null;
        foreach ($configPaths as $path) {
            if (file_exists($path)) {
                $configFile = $path;
                break;
            }
        }
        
        // 执行操作
        if ($action === 'backup') {
            if ($configFile) {
                $backupDir = 'backups';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                
                $backupFile = $backupDir . '/timeline_config_en_' . date('Y-m-d_H-i-s') . '.json';
                if (copy($configFile, $backupFile)) {
                    $message = "✅ 备份成功：$backupFile";
                } else {
                    $error = "❌ 备份失败";
                }
            } else {
                $error = "❌ 找不到配置文件";
            }
        }
        
        if ($action === 'clean') {
            if ($configFile) {
                $content = file_get_contents($configFile);
                $data = json_decode($content, true);
                
                if (is_array($data)) {
                    $originalCount = count($data);
                    
                    // 清理空记录
                    $cleaned = array_filter($data, function($item) {
                        if (!is_array($item)) return false;
                        
                        // 过滤占位内容
                        if (isset($item['title']) && 
                            ($item['title'] === 'New Milestone ✨' || 
                             $item['title'] === 'New Development ✨' ||
                             trim($item['title']) === '')) {
                            return false;
                        }
                        
                        if (isset($item['description1']) && 
                            ($item['description1'] === 'Please fill in the first description here...' ||
                             trim($item['description1']) === '')) {
                            return false;
                        }
                        
                        // 至少要有一些实际内容
                        return (
                            (!empty($item['title']) && trim($item['title']) !== '') ||
                            (!empty($item['description1']) && trim($item['description1']) !== '')
                        );
                    });
                    
                    $cleaned = array_values($cleaned);
                    $removedCount = $originalCount - count($cleaned);
                    
                    if (file_put_contents($configFile, json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
                        $message = "✅ 清理成功：移除了 $removedCount 条空记录";
                    } else {
                        $error = "❌ 保存失败";
                    }
                } else {
                    $error = "❌ 配置文件格式错误";
                }
            } else {
                $error = "❌ 找不到配置文件";
            }
        }
        
        if ($action === 'reset') {
            if ($configFile || !empty($_POST['create_new'])) {
                // 如果文件不存在，创建新文件
                if (!$configFile) {
                    $configFile = 'timeline_config_en.json';
                }
                
                $emptyConfig = [];
                if (file_put_contents($configFile, json_encode($emptyConfig, JSON_PRETTY_PRINT)) !== false) {
                    $message = "✅ 配置文件已重置为空";
                } else {
                    $error = "❌ 重置失败";
                }
            } else {
                $error = "❌ 找不到配置文件";
            }
        }
        
        // 显示消息
        if ($message) {
            echo '<div class="message success">' . $message . '</div>';
        }
        if ($error) {
            echo '<div class="message error">' . $error . '</div>';
        }
        ?>
        
        <div class="section">
            <h2 class="section-title">📊 系统状态</h2>
            <div class="status-box">
                <?php
                // 检查配置文件状态
                $configStatus = '未找到';
                $configClass = 'status-error';
                $recordCount = 0;
                $emptyCount = 0;
                
                if ($configFile) {
                    $configStatus = $configFile;
                    $configClass = 'status-ok';
                    
                    $content = file_get_contents($configFile);
                    $data = json_decode($content, true);
                    
                    if (is_array($data)) {
                        $recordCount = count($data);
                        
                        // 统计空记录
                        foreach ($data as $item) {
                            if (is_array($item)) {
                                if ((isset($item['title']) && $item['title'] === 'New Milestone ✨') ||
                                    (isset($item['description1']) && $item['description1'] === 'Please fill in the first description here...')) {
                                    $emptyCount++;
                                }
                            }
                        }
                    }
                }
                ?>
                
                <div class="status-item">
                    <span class="status-label">配置文件位置：</span>
                    <span class="status-value <?php echo $configClass; ?>"><?php echo htmlspecialchars($configStatus); ?></span>
                </div>
                
                <div class="status-item">
                    <span class="status-label">总记录数：</span>
                    <span class="status-value"><?php echo $recordCount; ?> 条</span>
                </div>
                
                <div class="status-item">
                    <span class="status-label">空记录数：</span>
                    <span class="status-value <?php echo $emptyCount > 0 ? 'status-warning' : 'status-ok'; ?>">
                        <?php echo $emptyCount; ?> 条
                    </span>
                </div>
                
                <div class="status-item">
                    <span class="status-label">文件权限：</span>
                    <span class="status-value <?php echo $configFile && is_writable($configFile) ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $configFile && is_writable($configFile) ? '可写' : '不可写'; ?>
                    </span>
                </div>
                
                <div class="status-item">
                    <span class="status-label">JSON格式：</span>
                    <span class="status-value <?php echo $configFile && json_last_error() === JSON_ERROR_NONE ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $configFile && json_last_error() === JSON_ERROR_NONE ? '正确' : '错误'; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">🛠️ 修复操作</h2>
            
            <form method="post" style="display: inline;">
                <div class="button-group">
                    <button type="submit" name="action" value="backup" class="btn-secondary">
                        💾 备份配置文件
                    </button>
                    
                    <button type="submit" name="action" value="clean" class="btn-primary" 
                            <?php echo $emptyCount === 0 ? 'disabled' : ''; ?>
                            onclick="return confirm('确定要清理所有空记录吗？');">
                        🧹 清理空记录
                    </button>
                    
                    <button type="submit" name="action" value="reset" class="btn-danger"
                            onclick="return confirm('警告：这将删除所有英文版记录！确定要继续吗？');">
                        🔄 重置配置文件
                    </button>
                    
                    <?php if (!$configFile): ?>
                    <input type="hidden" name="create_new" value="1">
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <?php if ($configFile && $recordCount > 0): ?>
        <div class="section">
            <h2 class="section-title">📋 当前配置内容</h2>
            <div class="config-preview">
                <pre><?php
                    $data = json_decode(file_get_contents($configFile), true);
                    echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                ?></pre>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2 class="section-title">📝 操作日志</h2>
            <div class="log-box">
                <div class="log-info">系统初始化完成...</div>
                <?php if ($configFile): ?>
                <div class="log-success">✓ 找到配置文件: <?php echo $configFile; ?></div>
                <div class="log-info">  记录数: <?php echo $recordCount; ?></div>
                <?php if ($emptyCount > 0): ?>
                <div class="log-warning">⚠ 发现 <?php echo $emptyCount; ?> 条空记录</div>
                <?php endif; ?>
                <?php else: ?>
                <div class="log-error">✗ 未找到配置文件</div>
                <div class="log-warning">→ 可以使用"重置配置文件"创建新文件</div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                <div class="log-success"><?php echo strip_tags($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="log-error"><?php echo strip_tags($error); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">💡 建议</h2>
            <ol style="line-height: 1.8; color: #666;">
                <li>首先备份当前配置文件</li>
                <li>如果发现空记录，使用"清理空记录"功能</li>
                <li>如果问题严重，可以选择"重置配置文件"重新开始</li>
                <li>修复后，重新添加您的实际内容</li>
                <li>定期备份配置文件以防数据丢失</li>
            </ol>
        </div>
    </div>
</body>
</html>
