<?php
// 上传示例照片的脚本

echo "<h2>上传示例照片</h2>";

// 创建comphoto目录（如果不存在）
$comphotoDir = 'comphoto/comphoto/';
if (!is_dir($comphotoDir)) {
    mkdir($comphotoDir, 0777, true);
    echo "创建目录: $comphotoDir<br>";
}

// 创建一些示例照片文件（使用base64编码的小图片）
$sampleImages = [
    1 => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A',
    2 => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A',
    3 => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A'
];

$uploadedCount = 0;

foreach ($sampleImages as $index => $base64Data) {
    // 解码base64数据
    $imageData = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
    
    // 创建文件路径
    $filename = $index . '.jpg';
    $filepath = $comphotoDir . $filename;
    
    // 写入文件
    if (file_put_contents($filepath, $imageData)) {
        echo "创建示例照片: $filename<br>";
        $uploadedCount++;
    } else {
        echo "创建示例照片失败: $filename<br>";
    }
}

echo "<br>成功创建 $uploadedCount 个示例照片<br>";

// 更新media_config.json
echo "<h3>更新配置文件</h3>";

$configFile = 'media_config.json';
$config = [];

// 读取现有配置
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
}

// 添加照片配置
for ($i = 1; $i <= $uploadedCount; $i++) {
    $config['comphoto_' . $i] = [
        'file' => $comphotoDir . $i . '.jpg',
        'type' => 'image',
        'updated' => date('Y-m-d H:i:s')
    ];
}

// 保存配置
file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "配置文件已更新: $configFile<br>";

// 测试getCompanyPhotos函数
echo "<h3>测试照片获取</h3>";
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

echo "<br><strong>示例照片上传完成！</strong>";
?>
