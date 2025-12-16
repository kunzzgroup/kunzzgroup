<?php
// 修复comphoto显示问题的脚本

echo "<h2>Comphoto 修复工具</h2>";

// 1. 创建media_config.json文件（如果不存在）
echo "<h3>1. 创建配置文件</h3>";

$configFile = 'media_config.json';
$config = [];

// 检查是否已存在配置文件
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
    echo "配置文件已存在，读取现有配置<br>";
} else {
    echo "配置文件不存在，创建新的配置文件<br>";
}

// 2. 检查comphoto目录并创建示例照片配置
echo "<h3>2. 检查comphoto目录</h3>";

$comphotoDir = 'comphoto/comphoto/';
if (!is_dir($comphotoDir)) {
    mkdir($comphotoDir, 0777, true);
    echo "创建comphoto目录: $comphotoDir<br>";
} else {
    echo "comphoto目录已存在: $comphotoDir<br>";
}

// 3. 扫描现有照片文件
$existingFiles = glob($comphotoDir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
echo "找到现有照片文件: " . count($existingFiles) . " 个<br>";

// 4. 更新配置文件，添加现有照片
$photoIndex = 1;
foreach ($existingFiles as $file) {
    $config['comphoto_' . $photoIndex] = [
        'file' => $file,
        'type' => 'image',
        'updated' => date('Y-m-d H:i:s')
    ];
    echo "添加照片配置: comphoto_$photoIndex -> $file<br>";
    $photoIndex++;
}

// 5. 如果没有照片，创建一些示例配置
if (count($existingFiles) == 0) {
    echo "<h3>3. 创建示例照片配置</h3>";
    echo "没有找到现有照片，创建示例配置...<br>";
    
    // 创建一些示例配置，指向不存在的文件（用于测试）
    for ($i = 1; $i <= 5; $i++) {
        $config['comphoto_' . $i] = [
            'file' => $comphotoDir . 'sample_' . $i . '.jpg',
            'type' => 'image',
            'updated' => date('Y-m-d H:i:s')
        ];
        echo "创建示例配置: comphoto_$i<br>";
    }
}

// 6. 保存配置文件
file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "<br>配置文件已保存: $configFile<br>";

// 7. 测试getCompanyPhotos函数
echo "<h3>4. 测试照片获取功能</h3>";
include_once 'media_config.php';

$photos = getCompanyPhotos();
echo "getCompanyPhotos()返回的照片数量: " . count($photos) . "<br>";

if ($photos) {
    echo "照片路径列表:<br>";
    foreach ($photos as $i => $photo) {
        echo ($i + 1) . ". $photo<br>";
    }
} else {
    echo "没有找到任何照片<br>";
}

// 8. 提供上传照片的说明
echo "<h3>5. 下一步操作</h3>";
echo "请按照以下步骤上传照片：<br>";
echo "1. 访问后台管理页面<br>";
echo "2. 上传照片到 comphoto/comphoto/ 目录<br>";
echo "3. 或者直接通过FTP上传照片到该目录<br>";
echo "4. 照片文件名格式：1.jpg, 2.jpg, 3.jpg 等<br>";

echo "<br><strong>修复完成！</strong>";
?>
