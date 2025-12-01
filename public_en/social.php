<?php
/**
 * 社交侧边栏组件
 * 可在任何页面中通过 include 引入
 * 使用方式: <?php include 'public/social.php'; ?>
 */

// 检查是否在正确的目录结构中
$imagePath = '';
if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
    $imagePath = '../images/images/';
} elseif (strpos($_SERVER['PHP_SELF'], '/public/') !== false) {
    $imagePath = '../images/images/';
} else {
    $imagePath = '../images/images/';
}
?>

<!-- 社交侧边栏 -->
<div class="social-sidebar">
    <!-- Facebook -->
    <a href="https://www.facebook.com/share/16ZihY9RN6/" target="_blank" class="social-icon facebook" title="进入 Facebook 世界">
        <img src="<?php echo $imagePath; ?>fbicon.png" alt="Facebook">
    </a>

    <!-- Instagram -->
    <a href="https://www.instagram.com" target="_blank" class="social-icon instagram" title="探索 Instagram 精彩">
        <img src="<?php echo $imagePath; ?>igicon.png" alt="Instagram">
    </a>

    <!-- WhatsApp -->
    <a href="https://wa.me/60135535355" target="_blank" class="social-icon whatsapp" title="通过 WhatsApp 联系我们">
        <img src="<?php echo $imagePath; ?>wsicon.png" alt="WhatsApp">
    </a>
</div>
