<?php
session_start();
include_once '../media_config.php';

// 禁用页面缓存
header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/joinus.css','../public_en/css/components/header.css','../public_en/css/components/social.css','../public_en/css/components/footer.css']; // footer.css 放最后，确保样式优先级
$showPageIndicator = true;
$totalSlides = 5;

// 包含header
include '../public_en/header.php';
?>

<!-- Include Content Template -->
<?php include 'templates/joinus-content.php'; ?>

<?php include '../public_en/social.php'; ?>
    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../app.js"></script>
<script src="../public_en/header.js"></script>
<script src="../public_en/footer.js"></script>
<script src="../public_en/social.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Inject PHP Data for JS -->
<script>
    // 我们的足迹 - 照片轮播
    <?php
    // 使用 media_config.php 中的 getCompanyPhotos() 函数获取照片
    $photos = getCompanyPhotos();
    
    // 如果 getCompanyPhotos() 返回的照片数量不够，从 comphoto/comphoto/ 目录补充
    if (count($photos) < 30) { // 最多获取30张照片
        $comphotoDir = '../comphoto/comphoto/';
        if (is_dir($comphotoDir)) {
            $files = glob($comphotoDir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
            if ($files) {
                // 按文件名排序
                sort($files);
                $addedCount = 0;
                foreach ($files as $file) {
                    // 限制最多30张照片
                    if (count($photos) >= 30) {
                        break;
                    }
                    
                    $photoPath = '/' . str_replace('../', '', $file);
                    // 避免重复添加
                    if (!in_array($photoPath, $photos)) {
                        $photos[] = $photoPath;
                        $addedCount++;
                    }
                }
            }
        }
    }
    
    // 强制限制最多30张照片
    if (count($photos) > 30) {
        $photos = array_slice($photos, 0, 30);
    }
    
    echo "window.comphotoImages = " . json_encode($photos) . ";";
    ?>

    // 背景图 loading Logic variable
    <?php 
    $media = getMediaConfig('joinus_background');
    $filePath = $media['file'];
    if (strpos($filePath, '/') !== 0 && strpos($filePath, 'http') !== 0) {
        $filePath = '../' . $filePath;
    }
    $timestamp = file_exists($filePath) ? '?v=' . filemtime($filePath) : '?v=' . time();
    echo "window.bgImgSrc = \"$filePath$timestamp\";";
    ?>
</script>

<!-- Include JS logic -->
<script src="js/joinus.js"></script>

</body>
</html>
