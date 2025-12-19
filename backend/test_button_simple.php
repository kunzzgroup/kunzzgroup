<?php
// 简单测试 - 直接输出按钮
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("需要登录");
}

include_once __DIR__ . '/../media_config.php';

$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>按钮测试</title>
    <style>
        .year-management {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px;
            padding: 20px;
            border: 2px solid red;
        }
        .year-tabs {
            display: flex;
            gap: 10px;
            border: 2px solid blue;
            padding: 10px;
        }
        .year-actions {
            display: flex !important;
            gap: 10px;
            align-items: center;
            border: 2px solid green;
            padding: 10px;
        }
        .btn-add {
            background: #28a745 !important;
            color: white !important;
            padding: 10px 20px !important;
            border: none !important;
            border-radius: 6px !important;
            font-size: 16px !important;
            cursor: pointer !important;
        }
    </style>
</head>
<body>
    <h1>简单按钮测试</h1>
    
    <div class="year-management">
        <div class="year-tabs">
            <span>暂无记录</span>
        </div>
        
        <div class="year-actions">
            <button type="button" class="btn btn-add" onclick="alert('按钮点击成功！')">+ <?php echo $isEnglish ? 'Add Record' : '新增记录'; ?></button>
        </div>
    </div>
    
    <hr>
    <h2>测试结果</h2>
    <p>如果你能看到上面的绿色按钮，说明HTML和CSS都正常。</p>
    <p>如果看不到，可能是浏览器或CSS的问题。</p>
    
    <hr>
    <h2>对比 aboutpage4upload.php</h2>
    <p><a href="aboutpage4upload.php">返回 aboutpage4upload.php</a></p>
</body>
</html>

