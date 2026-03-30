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
    $currentPath = ltrim($currentPath, '/');

    if (empty($currentPath) || $currentPath === 'index.php') {
        return $targetLang === 'en' ? '/frontend_en/' : '/frontend/';
    }

    if (strpos($currentPath, 'frontend/') === 0) {
        $relativePath = substr($currentPath, 9);
        return $targetLang === 'en' ? '/frontend_en/' . $relativePath : '/frontend/' . $relativePath;
    } elseif (strpos($currentPath, 'frontend_en/') === 0) {
        $relativePath = substr($currentPath, 12);
        return $targetLang === 'en' ? '/frontend_en/' . $relativePath : '/frontend/' . $relativePath;
    } else {
        return $targetLang === 'en' ? '/frontend_en/' . $currentPath : '/frontend/' . $currentPath;
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css" />
    <!-- 手机端专属覆盖（Swiper 禁用 + 自然滚动）-->
    <link rel="stylesheet" href="../public/mobile.css" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'KUNZZ HOLDINGS'; ?></title>
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>" />
        <?php endforeach; ?>
    <?php endif; ?>
    <?php echo loadNonCriticalCSS(); ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <!-- 手机端 JS（sidebar、CTA 注入、Swiper 禁用）-->
    <script src="../public/mobile.js" defer></script>
</head>
<body>
    <?php echo getBgMusicHtml(); ?>

    <!-- ★ 手机端 Sidebar Overlay（毛玻璃）-->
    <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>

    <!-- ★ 手机端 Sidebar（从右侧滑入，参照 Tokyo）-->
    <aside class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <a href="index.php">
                <img src="../images/images/KUNZZ.png" alt="KUNZZ Logo" class="mobile-sidebar-logo" loading="lazy">
            </a>
            <button class="mobile-sidebar-close" id="mobileSidebarClose" aria-label="关闭菜单">✕</button>
        </div>

        <nav class="mobile-sidebar-content">
            <a href="index.php" class="mobile-sidebar-link">首页</a>
            <a href="about.php" class="mobile-sidebar-link">关于我们</a>

            <!-- 旗下品牌 accordion -->
            <div class="mobile-sidebar-dropdown" id="brandsSidebarDropdown">
                <div class="mobile-sidebar-dropdown-trigger" id="brandsSidebarTrigger">旗下品牌</div>
                <div class="mobile-sidebar-submenu">
                    <a href="https://tokyo.kunzzgroup.com/tokyo">Tokyo Japanese Cuisine</a>
                    <a href="https://izakaya.kunzzgroup.com">Tokyo Izakaya Cuisine</a>
                </div>
            </div>

            <a href="joinus.php" class="mobile-sidebar-link">加入我们</a>
        </nav>

        <div class="mobile-sidebar-footer">
            <a href="/frontend/login.html" class="mobile-sidebar-login-btn">员工登入</a>
        </div>
    </aside>

    <!-- Navbar -->
    <header class="header-navbar">
        <!-- 左侧 Logo -->
        <div class="header-logo-section">
            <a href="index.php">
                <img src="../images/images/KUNZZ.png" alt="Logo" class="header-logo">
            </a>
        </div>

        <!-- 中间导航（桌面端显示）-->
        <nav class="header-nav-links" id="navMenu">
            <div class="header-nav-item"><a href="index.php">首页</a></div>
            <div class="header-nav-item"><a href="about.php">关于我们</a></div>
            <div class="header-nav-item header-nav-dropdown">
                <span class="header-nav-dropdown-trigger">旗下品牌</span>
                <div class="header-nav-dropdown-menu" id="brandsNavDropdownMenu">
                    <a href="https://tokyo.kunzzgroup.com/tokyo" class="header-nav-dropdown-item">Tokyo Japanese Cuisine</a>
                    <a href="https://izakaya.kunzzgroup.com" class="header-nav-dropdown-item">Tokyo Izakaya Cuisine</a>
                </div>
            </div>
            <div class="header-nav-item"><a href="joinus.php">加入我们</a></div>
        </nav>

        <!-- 右侧区域 -->
        <div class="header-right-section">
            <!-- 登入按钮（桌面端）-->
            <div class="header-login-dropdown">
                <button class="header-login-btn" id="loginBtn">登入</button>
                <div class="header-login-dropdown-menu" id="loginDropdownMenu">
                    <a href="/frontend/login.html" class="header-login-dropdown-item">员工登入</a>
                </div>
            </div>

            <!-- 语言切换（始终显示）-->
            <div class="header-language-switch">
                <button class="header-lang" id="languageBtn">中文</button>
                <div class="header-language-dropdown-menu" id="languageDropdownMenu">
                    <a href="<?php echo getLanguageSwitchUrl('cn'); ?>" class="header-language-dropdown-item" data-lang="cn">中文</a>
                    <a href="<?php echo getLanguageSwitchUrl('en'); ?>" class="header-language-dropdown-item" data-lang="en">English</a>
                </div>
            </div>

            <!-- 汉堡菜单（手机端，三条线）-->
            <button class="header-hamburger" id="hamburger" aria-label="打开菜单">
                <span></span>
                <span></span>
                <span></span>
            </button>
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
