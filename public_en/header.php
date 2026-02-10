<?php
// 检查是否已经启动了session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 包含媒体配置
if (!isset($mediaConfigIncluded)) {
    include_once '../media_config.php';
    $mediaConfigIncluded = true;
}

// 动态生成语言切换链接的函数
function getLanguageSwitchUrl($targetLang) {
    $currentUrl = $_SERVER['REQUEST_URI'];
    $currentPath = parse_url($currentUrl, PHP_URL_PATH);
    
    // 移除开头的斜杠
    $currentPath = ltrim($currentPath, '/');
    
    // 如果是根目录下的页面
    if (empty($currentPath) || $currentPath === 'index.php') {
        if ($targetLang === 'en') {
            return '/frontend_en/';
        } else {
            return '/frontend/';
        }
    }
    
    // 处理不同的路径格式
    if (strpos($currentPath, 'frontend/') === 0) {
        // 当前在frontend目录
        $relativePath = substr($currentPath, 9); // 移除 'frontend/'
        if ($targetLang === 'en') {
            return '/frontend_en/' . $relativePath;
        } else {
            return '/frontend/' . $relativePath;
        }
    } elseif (strpos($currentPath, 'frontend_en/') === 0) {
        // 当前在frontend_en目录
        $relativePath = substr($currentPath, 12); // 移除 'frontend_en/'
        if ($targetLang === 'en') {
            return '/frontend_en/' . $relativePath;
        } else {
            return '/frontend/' . $relativePath;
        }
    } else {
        // 当前在根目录
        if ($targetLang === 'en') {
            return '/frontend_en/' . $currentPath;
        } else {
            return '/frontend/' . $currentPath;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <style><?php include_once 'critical-css.php'; echo getCriticalCSS(); ?></style> -->
    <title><?php echo isset($pageTitle) ? $pageTitle : 'KUNZZ HOLDINGS'; ?></title>
    <link rel="stylesheet" href="css/header.css" />
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>" />
        <?php endforeach; ?>
    <?php endif; ?>
    <?php echo loadNonCriticalCSS(); ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
</head>
<body>
    <?php echo getBgMusicHtml(); ?>
    <header class="header-navbar">
        <!-- 左侧 logo 和公司名 -->
        <div class="header-logo-section">
            <a href="index.php">
                <img src="../images/images/KUNZZ.png" alt="Logo" class="header-logo">
            </a>
        </div>

        <!-- 中间导航（默认显示，大屏） -->
        <nav class="header-nav-links" id="navMenu">
            <div class="header-nav-item"><a href="index.php">Home</a></div>
            <div class="header-nav-item"><a href="about.php">About Us</a></div>
            <div class="header-nav-item header-nav-dropdown">
                <span class="header-nav-dropdown-trigger">Our Brands</span>
                <div class="header-nav-dropdown-menu" id="brandsNavDropdownMenu">
                    <a href="tokyo-japanese-cuisine.html" class="header-nav-dropdown-item">Tokyo Japanese Cuisine</a>
                    <a href="tokyo-izakaya.html" class="header-nav-dropdown-item">Tokyo Izakaya Japanese Cuisine</a>
                </div>
            </div>
            <div class="header-nav-item"><a href="joinus.php">Join Us</a></div>
        </nav>

        <!-- 右侧区域 -->
        <div class="header-right-section">
            <!-- 移动端隐藏 login，仅大屏显示 -->
            <div class="header-login-dropdown">
                <button class="header-login-btn" id="loginBtn">LOGIN</button>
                <div class="header-login-dropdown-menu" id="loginDropdownMenu">
                    <a href="../frontend_en/login.html" class="header-login-dropdown-item">Staff Login</a>
                </div>
            </div>

            <!-- 翻译按钮始终显示 -->
            <div class="header-language-switch">
                <button class="header-lang" id="languageBtn">English</button>
                <div class="header-language-dropdown-menu" id="languageDropdownMenu">
                    <a href="<?php echo getLanguageSwitchUrl('cn'); ?>" class="header-language-dropdown-item" data-lang="cn">中文</a>
                    <a href="<?php echo getLanguageSwitchUrl('en'); ?>" class="header-language-dropdown-item" data-lang="en">English</a>
                </div>
            </div>

            <!-- hamburger 仅在小屏显示 -->
            <button class="header-hamburger" id="hamburger">&#9776;</button>
        </div>
    </header>

    <?php if (isset($showPageIndicator) && $showPageIndicator): ?>
    <div class="header-page-indicator">
        <?php 
        $totalSlides = isset($totalSlides) ? $totalSlides : 4;
        for ($i = 0; $i < $totalSlides; $i++): 
        ?>
            <div class="header-page-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></div>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
