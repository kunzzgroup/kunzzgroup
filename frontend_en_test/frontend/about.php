<?php
// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
include_once '../media_config.php';

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/about.css','../public/css/components/header.css','../public/css/components/footer.css','../public/css/components/social.css'];
$showPageIndicator = true;
$totalSlides = 5;

// 包含header
include '../public/header.php';

// 在header之后获取时间线数据（扁平记录，允许同一年多条）
$timelineItems = getTimelineItems('zh');
?>

<!-- Include Content Template -->
<?php include 'templates/about-content.php'; ?>

<?php include '../public/social.php'; ?>
  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../app.js"></script>
<script src="../public/header.js"></script>
<script src="../public/footer.js"></script>
<script src="../public/social.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Pass PHP Data to JS -->
<script>
    window.timelineYearsData = <?php echo json_encode(getTimelineYearsFlat('zh')); ?>;
</script>

<!-- Include About JS -->
<script src="js/about.js"></script>
    
<!-- 背景音乐 -->
<?php echo getBgMusicHtml(); ?>
</body>
</html>