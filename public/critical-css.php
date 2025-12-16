<?php
// Critical CSS inlining system for performance optimization
// This file contains the critical above-the-fold CSS that should be inlined

function getCriticalCSS() {
    return '
/* Critical CSS - Above the fold styles */
.navbar, .header-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.logo-section, .header-logo-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo, .header-logo {
    height: 40px;
    width: auto;
    transition: transform 0.3s ease;
}

.nav-links, .header-nav-links {
    display: flex;
    gap: 30px;
    align-items: center;
}

.nav-item, .header-nav-item {
    position: relative;
}

.nav-item a, .header-nav-item a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: color 0.3s ease;
}

/* Critical layout for home section */
.home {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.home-content {
    text-align: center;
    z-index: 2;
    position: relative;
}

.home-content h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

/* Critical Swiper styles */
.swiper {
    height: 100vh;
    overflow: hidden;
}

.swiper-slide {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Critical responsive styles */
@media (max-width: 768px) {
    .nav-links, .header-nav-links {
        display: none;
    }
    
    .hamburger, .header-hamburger {
        display: block;
    }
    
    .home-content h1 {
        font-size: 2rem;
    }
}

/* Critical loading states */
.hidden {
    opacity: 0;
    transform: translateY(20px);
}

.visible {
    opacity: 1;
    transform: translateY(0);
    transition: all 0.6s ease;
}

/* Critical animation performance */
.scale-fade-in {
    will-change: transform, opacity;
    transform: translateY(20px);
    opacity: 0;
}

.scale-fade-in.animate {
    transform: translateY(0);
    opacity: 1;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
';
}

function getNonCriticalCSS() {
    return [
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap',
        'https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap'
    ];
}

function loadNonCriticalCSS() {
    $cssFiles = getNonCriticalCSS();
    $output = '';
    
    foreach ($cssFiles as $css) {
        $output .= '<link rel="preload" href="' . $css . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        $output .= '<noscript><link rel="stylesheet" href="' . $css . '"></noscript>' . "\n";
    }
    
    return $output;
}
?>
