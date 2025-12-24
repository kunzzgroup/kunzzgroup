<?php
// 提供 fontkit.umd.js 文件
// 如果本地有文件，直接提供；否则尝试从 CDN 下载并缓存

// 优先查找压缩版，如果没有则查找普通版
$fontkitFile = __DIR__ . '/../fonts/fontkit.umd.min.js';
$fontkitFileAlt = __DIR__ . '/../fonts/fontkit.umd.js';

// 如果本地文件存在，直接提供（优先使用 .min.js）
if (file_exists($fontkitFile)) {
    header('Content-Type: application/javascript');
    header('Cache-Control: public, max-age=31536000'); // 缓存1年
    readfile($fontkitFile);
    exit;
} elseif (file_exists($fontkitFileAlt)) {
    header('Content-Type: application/javascript');
    header('Cache-Control: public, max-age=31536000');
    readfile($fontkitFileAlt);
    exit;
}

// 如果本地文件不存在，尝试从 CDN 下载
$cdnUrls = [
    'https://unpkg.com/fontkit/dist/fontkit.umd.js',
    'https://cdn.jsdelivr.net/npm/fontkit/dist/fontkit.umd.js'
];

foreach ($cdnUrls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $content) {
        // 确保 fonts 目录存在
        $fontsDir = __DIR__ . '/../fonts';
        if (!is_dir($fontsDir)) {
            mkdir($fontsDir, 0755, true);
        }
        
        // 保存到本地
        file_put_contents($fontkitFile, $content);
        
        // 提供文件
        header('Content-Type: application/javascript');
        header('Cache-Control: public, max-age=31536000');
        echo $content;
        exit;
    }
}

// 如果所有 CDN 都失败，返回错误
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => '无法加载 fontkit，请手动下载 fontkit.umd.js 到 fonts 文件夹']);
?>

