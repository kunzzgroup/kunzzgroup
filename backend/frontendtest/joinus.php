<?php
session_start();
include_once '../media_config.php';

// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/joinus.css','../public/css/components/header.css','../public/css/components/social.css','../public/css/components/footer.css'];
$showPageIndicator = true;
$totalSlides = 6;

// 包含header
include '../public/header.php';
?>

<!-- Include Content Template -->
<?php include 'templates/joinus-content.php'; ?>

<?php include '../public/social.php'; ?>
    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../app.js"></script>
<script src="../public/header.js"></script>
<script src="../public/footer.js"></script>
<script src="../public/social.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Background Image Loading Logic -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const homeContent = document.querySelector('.home-content'); // Might not exist on joinus, but kept for compatibility
        
        const bgImg = new Image();
        <?php 
        $media = getMediaConfig('joinus_background');
        $filePath = $media['file'];
        if (strpos($filePath, '/') !== 0 && strpos($filePath, 'http') !== 0) {
            $filePath = '../' . $filePath;
        }
        $timestamp = file_exists($filePath) ? '?v=' . filemtime($filePath) : '?v=' . time();
        echo "bgImg.src = \"$filePath$timestamp\";";
        ?>

        bgImg.onload = function () {
            const homeSection = document.querySelector('.home'); // JoinUs usually uses .joinus-section, but check CSS
            if (homeSection) homeSection.classList.add('gradient-loaded');
            
            // Show content
            const banners = document.querySelectorAll('.joinus-banner');
            banners.forEach(b => b.classList.add('joinus-loaded'));
        };
    });
</script>

<!-- Pass PHP Data for Comphoto -->
<script>
    <?php
    // 使用 media_config.php 中的 getCompanyPhotos() 函数获取照片
    $photos = getCompanyPhotos();
    
    // 如果数量不够，从 comphoto 目录补充
    if (count($photos) < 30) {
        $comphotoDir = '../comphoto/comphoto/';
        if (is_dir($comphotoDir)) {
            $files = glob($comphotoDir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
            if ($files) {
                sort($files);
                foreach ($files as $file) {
                    if (count($photos) >= 30) break;
                    $photoPath = '/' . str_replace('../', '', $file);
                    if (!in_array($photoPath, $photos)) $photos[] = $photoPath;
                }
            }
        }
    }
    
    if (count($photos) > 30) $photos = array_slice($photos, 0, 30);
    echo "window.comphotoImages = " . json_encode($photos) . ";";
    ?>
</script>

<!-- Include Join Us JS -->
<script src="js/joinus.js"></script>

<!-- 背景音乐 -->
<?php echo getBgMusicHtml(); ?>
</body>
</html>