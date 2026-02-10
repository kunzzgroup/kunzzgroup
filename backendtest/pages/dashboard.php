<?php
/**
 * Dashboard (Monolithic)
 * Serves the dashboard UI with inline HTML/CSS/JS.
 */

require_once __DIR__ . '/../core/session_check.php';
require_login();

// User Info for Sidebar
$username = $_SESSION['username'] ?? 'User';
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KUNZZ HOLDINGS</title>
    <link rel="icon" type="image/png" href="../../images/images/logo.png">
    
    <!-- Base Configuration -->
    <script>
        window.BASE_URL = "/backendtest/";
        window.API_BASE = "/backendtest/backend/api/";
        window.PAGE_NAME = "dashboard";
    </script>
    
    <!-- External Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="../../animation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Extract from css/dashboard.css if any specific styles existed there */
        /* Currently using external style.css and animation.css */
        
        /* Inline Dashboard Styles */
        body.dashboard-page {
            margin: 0;
            padding: 0;
            background-color: #faf7f2;
            overflow: hidden; /* For Swiper */
            height: 100vh;
        }

        #app {
            height: 100%;
            display: flex;
        }

        .main-container {
            flex: 1;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .swiper {
            width: 100%;
            height: 100vh;
        }

        .swiper-slide {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        /* Page Indicator Styles */
        .page-indicator {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .page-dot {
            width: 10px;
            height: 10px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .page-dot.active {
            background-color: #ff5c00;
            transform: scale(1.3);
        }
        
        /* Home Section */
        .home {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        
        .background-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        
        .home-content {
            background: rgba(0, 0, 0, 0.5);
            padding: 40px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
        
        .home-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .decor-line {
            width: 100px;
            height: 4px;
            background-color: #ff5c00;
            margin: 20px auto;
        }
        
        /* About Section */
        .about-section {
            width: 100%;
            height: 100%;
            display: flex;
            background-color: white;
        }
        
        .comprofile-section {
            display: flex;
            width: 100%;
            height: 100%;
        }
        
        .comprofile-text {
            flex: 1;
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .comprofile-image {
            flex: 1;
            background-color: #fcece4;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .comprofile-image img {
            max-width: 80%;
            max-height: 80%;
        }
        
        /* Culture Section */
        .culture-section {
            width: 100%;
            height: 100%;
            display: flex;
            background-color: #f9f9f9;
        }
        
        .culture-left {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 60px;
            align-content: center;
        }
        
        .culture-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .culture-card:hover {
            transform: translateY(-10px);
        }
        
        .culture-icon {
            height: 60px;
            margin-bottom: 20px;
        }
        
        .culture-right {
            flex: 1;
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #1a1a1a;
            color: white;
        }
        
        .culture-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background-color: #ff5c00;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            width: fit-content;
        }
        
        /* Stats Section */
        .stats-section {
            position: absolute;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            background: white;
            padding: 20px 40px;
            border-radius: 50px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .stat-box {
            text-align: center;
            padding: 0 30px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ff5c00;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .divider {
            width: 1px;
            background-color: #eee;
        }

        /* Footer Slide */
        .footer-slide {
            background-color: #111;
            color: white;
        }
        
        .footer-scroll-buffer {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .footer {
            display: flex;
            justify-content: space-around;
            padding: 50px;
        }
        
        .footer-section h4 {
            color: #ff5c00;
            margin-bottom: 20px;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-section li {
            margin-bottom: 15px;
        }
        
        .footer-section a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #333;
            color: #666;
        }
    </style>
    
    <!-- Required Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body class="dashboard-page">
    <div id="app">
        <!-- Sidebar -->
        <?php include '../core/sidebar.php'; ?>
        
        <div class="main-container">
            <div class="page-indicator">
                <div class="page-dot active" data-slide="0"></div>
                <div class="page-dot" data-slide="1"></div>
                <div class="page-dot" data-slide="2"></div>
                <div class="page-dot" data-slide="3"></div>
            </div>

            <div class="swiper">
                <div class="swiper-wrapper">
                    <!-- Slide 0: Home -->
                    <div class="swiper-slide">
                        <section class="home">
                            <video class="background-video" autoplay muted loop playsinline>
                                <source src="../../video/video/home_background.webm" type="video/webm" />
                            </video>
                            <div class="home-content hidden animate-on-scroll">
                                <h1 class="scale-fade-in">让空间温暖 <span style="font-size: 1.5em;">.</span> 让团队闪光</h1>
                                <div class="decor-line scale-fade-in"></div>
                                <p class="scale-fade-in">
                                    我们用细节构建舒适的氛围，在积极的文化中滋养每一份热情与专注。<br />
                                    我们相信，高效源于信任，创新源于自由。一支有温度的团队，<br />
                                    才能创造持续的价值，向着行业标杆的方向，稳步前行。
                                </p>
                            </div>
                        </section>
                    </div>

                    <!-- Slide 1: About -->
                    <div class="swiper-slide">
                        <section class="about-section" id="comprofile">
                            <div class="comprofile-section">
                                <div class="comprofile-text">
                                    <p class="comprofile-subtitle animate-on-scroll slide-in-left delay-1">
                                        <span class="circle"></span>公司简介
                                    </p>
                                    <h2 class="comprofile-title animate-on-scroll slide-in-left delay-2">KUNZZ HOLDINGS</h2>
                                    <p class="comprofile-description animate-on-scroll slide-in-left delay-3">
                                        Kunzz Holdings 成立于2023年，初衷是为旗下业务建立统一的管理平台，提升资源整合效率。我们坚守“塑造积极向上和舒适的工作环境”为使命，持续推动组织氛围建设，成就更有温度的企业文化。我们信奉积极、高效、灵活、诚信的核心精神，始终以目标导向、理念一致为准则，追求卓越，勇于创新。
                                    </p>
                                </div>
                                <div class="comprofile-image animate-on-scroll rotate-3d-full">
                                    <img src="../../images/images/logo.png" alt="公司介绍图" />
                                </div>
                            </div>

                            <div class="stats-section animate-on-scroll">
                                <div class="stat-box">
                                    <div class="stat-number">2023</div>
                                    <div class="stat-label">成立年份</div>
                                </div>
                                <div class="divider"></div>
                                <div class="stat-box">
                                    <div class="stat-number">3</div>
                                    <div class="stat-label">子公司数量</div>
                                </div>
                                <div class="divider"></div>
                                <div class="stat-box">
                                    <div class="stat-number">70+</div>
                                    <div class="stat-label">员工数量</div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Slide 2: Culture -->
                    <div class="swiper-slide">
                        <section id="culture" class="culture-section">
                            <div class="culture-left animate-on-scroll card-tilt-in-left">
                                <div class="culture-card">
                                    <img src="../../images/images/积极向上 (1).png" alt="icon" class="culture-icon">
                                    <h3>积极向上</h3>
                                    <p>始终以正面心态面对挑战<br>在变化中寻找成长机会</p>
                                </div>
                                <div class="culture-card">
                                    <img src="../../images/images/高效执行 (1).png" alt="icon" class="culture-icon">
                                    <h3>高效执行</h3>
                                    <p>说到做到，快速响应<br>追求结果导向与行动力</p>
                                </div>
                                <div class="culture-card">
                                    <img src="../../images/images/灵活应变 (1).png" alt="icon" class="culture-icon">
                                    <h3>灵活应变</h3>
                                    <p>面对市场变化和问题<br>保持开放思维，快速调整策略</p>
                                </div>
                                <div class="culture-card">
                                    <img src="../../images/images/诚信待人 (1).png" alt="icon" class="culture-icon">
                                    <h3>诚信待人</h3>
                                    <p>以真诚与责任建立合作与信任<br>是我们最基本的做人原则</p>
                                </div>
                            </div>

                            <div class="culture-right animate-on-scroll">
                                <h2 class="culture-title animate-on-scroll culture-scale-fade delay-6">我们的核心价值<br>公司文化</h2>
                                <p class="culture-description animate-on-scroll culture-scale-fade delay-7">
                                    在 Kunzz Holdings，我们相信文化决定高度。我们以目标为导向，理念为基石，打造一支具备高效执行力与高度协同精神的团队。
                                    我们提倡扁平沟通，尊重每一位成员的成长节奏，鼓励分享、学习与共创。在这里，每一份努力都能被看见，每一次突破都值得被鼓励。
                                </p>
                                <a href="about.php" class="culture-button animate-on-scroll culture-scale-fade delay-8">了解更多 &gt;&gt;</a>
                            </div>
                        </section>
                    </div>

                    <!-- Slide 3: Footer -->
                    <div class="swiper-slide footer-slide">
                        <section class="footer-scroll-buffer">
                            <footer class="footer">
                                <div class="footer-section">
                                    <h4><a href="../frontend/index.php">首页</a></h4>
                                    <ul>
                                        <li><a href="javascript:void(0)" onclick="window.goToSlide(1)">公司简介</a></li>
                                        <li><a href="javascript:void(0)" onclick="window.goToSlide(2)">公司文化</a></li>
                                    </ul>
                                </div>

                                <div class="footer-section">
                                    <h4><a href="about.php">关于我们</a></h4>
                                    <ul>
                                        <li><a href="about.php">集团简介</a></li>
                                        <li><a href="about.php">信念与方向</a></li>
                                        <li><a href="about.php">核心价值观</a></li>
                                        <li><a href="about.php">发展历史</a></li>
                                    </ul>
                                </div>

                                <div class="footer-section">
                                    <h4>旗下品牌</h4>
                                    <ul>
                                        <li><a href="tokyo-japanese-cuisine.php">TOKYO JAPANESE </br>CUISINE</a></li>
                                    </ul>
                                </div>

                                <div class="footer-section">
                                    <h4><a href="joinus.php">加入我们</a></h4>
                                    <ul>
                                        <li><a href="joinus.php">公司福利</a></li>
                                        <li><a href="joinus.php">我们的足迹</a></li>
                                        <li><a href="joinus.php">招聘的职位</a></li>
                                        <li><a href="joinus.php">联系我们</a></li>
                                    </ul>
                                </div>
                            </footer>

                            <div class="footer-bottom">
                                © 2025 Kunzz Holdings Sdn. Bhd. 版权所有。
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Script -->
    <script>
        function initializeComponents() {
            initSwiper();
            initAnimations();
            initVideo();
            initPageIndicators();
        }

        function initSwiper() {
            window.swiper = new Swiper('.swiper', {
                direction: 'vertical',
                mousewheel: true,
                speed: 800,
                simulateTouch: false,
                slidesPerView: 'auto',
                spaceBetween: 0,
                on: {
                    slideChange: function() {
                        updatePageIndicator(this.activeIndex);
                    },
                    setTransition: function(duration) {
                        setTimeout(() => {
                            if (this.progress > 0.95) {
                                updatePageIndicator(3);
                            } else {
                                updatePageIndicator(this.activeIndex);
                            }
                        }, duration + 50);
                    }
                }
            });
        }

        function initAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const container = entry.target;
                    if (entry.isIntersecting) {
                        container.classList.add('visible');
                        container.querySelectorAll('.animate-on-scroll').forEach(el => {
                            el.style.animationPlayState = 'running';
                        });
                    } else {
                        container.classList.remove('visible');
                    }
                });
            }, { threshold: 0.2 });

            document.querySelectorAll('section').forEach(section => observer.observe(section));
        }

        function initVideo() {
            const video = document.querySelector('.background-video');
            if (video) {
                video.play().catch(e => console.log("Video autoplay blocked", e));
            }
        }

        function initPageIndicators() {
            const pageDots = document.querySelectorAll('.page-dot');
            pageDots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    if (window.swiper) window.swiper.slideTo(index);
                });
            });
        }

        function updatePageIndicator(activeIndex) {
            const pageDots = document.querySelectorAll('.page-dot');
            pageDots.forEach((dot, index) => {
                if (index === activeIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        window.goToSlide = function(index) {
            if (window.swiper) window.swiper.slideTo(index);
        };

        // Start application
        document.addEventListener('DOMContentLoaded', initializeComponents);
    </script>
</body>
</html>
