<?php
session_start();
include_once '../media_config.php';

// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 如果已登录或记住我，跳转到 dashboard
if (isset($_SESSION['user_id']) || (isset($_COOKIE['user_id']) && isset($_COOKIE['username']))) {
    header("Location: ../backend/dashboard.php");
    exit();
}

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/index.css','../public/css/components/header.css','../public/css/components/footer.css','../public/css/components/social.css'];
$showPageIndicator = true;
$totalSlides = 4;

// 包含header
include '../public/header.php';
?>

<!-- 包含主要内容 HTML -->
<?php include 'templates/index-content.php'; ?>

<?php include '../public/social.php'; ?>
  
<script src="../app.js" defer></script>
<script src="../public/header.js" defer></script>
<script src="../public/footer.js" defer></script>
<script src="../public/social.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- 引入分离后的 JS 文件 -->
<script src="js/index.js" defer></script>
    
<!-- 背景音乐 -->
<?php echo getBgMusicHtml(); ?>
</body>
</html>