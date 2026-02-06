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
$additionalCSS = ['css/index.css','../public_en/css/components/header.css','../public_en/css/components/footer.css','../public_en/css/components/social.css'];
$showPageIndicator = true;
$totalSlides = 4;

// 包含header
include '../public_en/header.php';
?>

<!-- Include Content Template -->
<?php 
// 1. 获取动态内容
$homeBackgroundHtml = getMediaHtml('home_background');

// 2. 获取 Footer 内容 (通过输出缓冲)
ob_start();
include '../public_en/footer.php';
$footerHtml = ob_get_clean();

// 3. 读取 HTML 模板
$templatePath = 'templates/index-content.html';
if (file_exists($templatePath)) {
    $templateContent = file_get_contents($templatePath);

    // 4. 替换占位符
    $templateContent = str_replace('<!-- DYNAMIC_HOME_BACKGROUND -->', $homeBackgroundHtml, $templateContent);
    $templateContent = str_replace('<!-- DYNAMIC_FOOTER -->', $footerHtml, $templateContent);

    // 5. 输出最终内容
    echo $templateContent;
} else {
    echo "<!-- Error: Template file not found: $templatePath -->";
}
?>

<?php include '../public_en/social.php'; ?>
  
<script src="../app.js" defer></script>
<script src="../public_en/header.js" defer></script>
<script src="../public_en/footer.js" defer></script>
<script src="../public_en/social.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Background Image Loading Logic -->
<script>
window.addEventListener('load', () => {
  const video = document.querySelector('.background-video');
  const bgImage = document.querySelector('.background-image');
  
  // 触发动画的通用函数
  function triggerAnimations() {
    const home = document.querySelector('.home');
    if (home) home.classList.add('gradient-loaded');
    
    const homeContent = document.querySelector('.home-content');
    if (homeContent) {
        homeContent.classList.remove('hidden');
        // 强制触发重绘，重新开始动画
        void homeContent.offsetWidth;
        // 添加动画类
        const h1 = homeContent.querySelector('h1');
        const p = homeContent.querySelector('p');
        if (h1) h1.classList.add('scale-fade-in');
        if (p) p.classList.add('scale-fade-in');
    }
  }
  
  // 处理视频背景
  if (video) {
    video.addEventListener('canplay', function() {
      triggerAnimations();
    });
  }
  
  // 处理图片背景
  if (bgImage) {
    bgImage.addEventListener('load', function() {
      triggerAnimations();
    });
    if (bgImage.complete) {
      bgImage.dispatchEvent(new Event('load'));
    }
  }

  // 备用方案：如果视频/图片加载失败或很慢
  setTimeout(() => {
    const home = document.querySelector('.home');
    if (home && !home.classList.contains('gradient-loaded')) {
      triggerAnimations();
    }
  }, 500);
});
</script>

<!-- Include Index JS -->
<script src="js/index.js"></script>
    
<!-- 背景音乐 -->
<?php echo getBgMusicHtml(); ?>
</body>
</html>