<?php
// 禁用页面缓存
header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
session_start();
include_once '../media_config.php';

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/about.css','../public_en/css/components/header.css','../public_en/css/components/footer.css','../public_en/css/components/social.css'];
$showPageIndicator = true;
$totalSlides = 5;

// 包含header
include '../public_en/header.php';

// 获取时间线数据（扁平记录，允许同一年多条）
$timelineItems = getTimelineItems('en');
?>
    
<!-- Include Content Template -->
<?php include 'templates/about-content.php'; ?>

<?php include '../public_en/social.php'; ?>
  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../app.js"></script>
<script src="../public_en/header.js"></script>
<script src="../public_en/footer.js"></script>
<script src="../public_en/social.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Inject Timeline Data for JS -->
<script>
    let years = <?php echo json_encode(getTimelineYearsFlat('en')); ?>;
</script>

<!-- Include About JS -->
<script src="js/about.js"></script>
    
<!-- 背景音乐 -->
<?php echo getBgMusicHtml(); ?>
</body>
</html>
