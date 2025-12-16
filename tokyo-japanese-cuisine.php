<?php
session_start();
include_once 'media_config.php';

// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="images/images/tokyo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOKYO JAPANESE CUISINE</title>
    <link rel="stylesheet" href="tokyo.css" />
    <link rel="stylesheet" href="tokyoanimation.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="navbar">
  <!-- 左侧 logo 和公司名 -->
  <div class="logo-section">
    <a href="index.php">
    <img src="images/images/KUNZZ.png" alt="Logo" class="logo">
    </a>
  </div>

  <!-- 中间导航（默认显示，大屏） -->
  <nav class="nav-links" id="navMenu">
    <div class="nav-item"><a href="index.php">首页</a></div>
    <div class="nav-item"><a href="about.php">关于我们</a></div>
    <div class="nav-item nav-dropdown">
      <span class="nav-dropdown-trigger">旗下品牌</span>
      <div class="nav-dropdown-menu" id="brandsNavDropdownMenu">
        <a href="tokyo-japanese-cuisine.php" class="nav-dropdown-item">Tokyo Japanese Cuisine</a>
        <a href="tokyo-izakaya.php" class="nav-dropdown-item">Tokyo Izakaya Japanese Cuisine</a>
      </div>
     </div>
    <div class="nav-item"><a href="joinus.php">加入我们</a></div>
  </nav>

  <!-- 右侧区域 -->
  <div class="right-section">
    <!-- 移动端隐藏 login，仅大屏显示 -->
    <div class="login-dropdown">
      <button class="login-btn" id="loginBtn">LOGIN</button>
        <div class="login-dropdown-menu" id="loginDropdownMenu">
          <a href="login.html" class="login-dropdown-item">员工登入</a>
          <a href="login.html" class="login-dropdown-item">会员登入</a>
        </div>
      </div>

    <!-- 翻译按钮始终显示 -->
    <div class="language-switch">
      <button class="lang" id="languageBtn">EN | CN</button>
        <div class="language-dropdown-menu" id="languageDropdownMenu">
          <a href="/en/" class="language-dropdown-item" data-lang="en">英文</a>
          <a href="/" class="language-dropdown-item" data-lang="cn">中文</a>
        </div>
      </div>

    <!-- hamburger 仅在小屏显示 -->
    <button class="hamburger" id="hamburger">&#9776;</button>
  </div>
</header>

<div class="page-indicator">
    <div class="page-dot active" data-slide="0"></div>
    <div class="page-dot" data-slide="1"></div>
    <div class="page-dot" data-slide="2"></div>
    <div class="page-dot" data-slide="3"></div>
    <div class="page-dot" data-slide="4"></div>
    <div class="page-dot" data-slide="5"></div>
  </div>

  <div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
  <section class="home">
    <?php echo getMediaHtml('tokyo_background', ['style' => 'width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;']); ?>
    <div class="home-content hidden animate-on-scroll">
      <div class="home-logo-container scale-fade-in">
      <img src="images/images/tokyo.png" alt="餐厅Logo" class="home-logo scale-fade-in">
      <h1 class="scale-fade-in">TOKYO JAPANESE<br />
        CUISINE
      </h1>
      </div>
      <p class="scale-fade-in">
        精致美食，卓越服务，成就世界级日料品牌
      </p>
    </div>
  </section>
  </div>

  <div class="swiper-slide">
  <section class="about-section">
  <div id="tokyoabout" class="tokyoabout-section">
    <div class="tokyoabout-text">
      <h2 class="tokyoabout-title animate-on-scroll slide-in-left delay-1">关于我们</h2>
      <p class="tokyoabout-description animate-on-scroll slide-in-left delay-2">
        我们是一家致力于提供精致料理与卓越服务的日式<br>料理餐厅，以极致的匠心打造美食。严选当即新鲜食材，融合传统与创意，呈现日本料理之美。餐厅环境清雅舒适，充满日式格调，宾客在此不仅能品味精妙料理，更能感受到细致入微的服务与文化魅力。我们立志将每一次用餐变成难忘的美食之旅，以卓越的服务和精致的料理成为世界级日料品牌。
      </p>
    </div>
  </div>
  </section>
  </div>  
  
  <div class="swiper-slide">
  <section class="values-section">
    <div class="overlay"></div>
    <div class="values-container">
      <!-- 选项按钮 -->
      <div class="values-tab-buttons">
        <div class="tab-container">
          <button class="values-tab-btn values-selected" onclick="showContent('values')" data-tab="values">使命</button>
          <button class="values-tab-btn" onclick="showContent('mission')" data-tab="mission">愿景</button>
          <div class="tab-slider"></div>
        </div>
      </div>

        <!-- 内容区域 -->
        <div class="values-content-area">
          <!-- 价值观内容 -->
            <div id="values" class="content-panel active">
              <div class="values-image-section">
                <img class="values-content-image" src="images/images/fujibg.jpg" alt="使命图片">
              </div>
              <div class="values-text-section">
                <h1 class="values-content-title">使命</h1>
                  <p class="values-content-subtitle">热情改变命运；</p>
                  <p class="values-content-description">以热情的服务让顾客享受预约的用餐体验</p>
                </div>
              </div>

              <!-- 海底捞使命内容 -->
              <div id="mission" class="content-panel">
                <div class="values-image-section">
                  <img class="values-content-image" src="images/images/sushi.jpg" alt="愿景图片">
                </div>
              <div class="values-text-section">
                <h1 class="values-content-title">愿景</h1>
                  <p class="values-content-subtitle">让顾客品尝到精致的日料；</p>
                    <p class="values-content-description">通过卓越的服务和精致的美食，<br>成为全球领先的日式料理品牌</p>
                  </div>
              </div>
          </div>
      </div>
    </section>
  </div>

  <div class="swiper-slide">
  <section class="tokyomenu-section">
  <div class="tokyomenu-container">
    <div class="tokyomenu-book" id="tokyomenu-book">

      <!-- 封面左页 -->
      <div class="tokyomenu-page tokyomenu-page-left" id="tokyomenu-page-left" style="z-index: 2;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/1.jpg" class="tokyomenu-image" alt="封面">
            <div class="tokyomenu-number">1</div>
          </div>
        </div>
      </div>

      <!-- 翻页内容（4 张 page，每张包含正反面，即 8 页） -->
      <!-- 第1页-2页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-1" style="z-index: 16;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/2.jpg" class="tokyomenu-image" alt="菜单1">
            <div class="tokyomenu-number">2</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/3.jpg" class="tokyomenu-image" alt="菜单2">
            <div class="tokyomenu-number">3</div>
          </div>
        </div>
      </div>

      <!-- 第3页-4页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-2" style="z-index: 15;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/4.jpg" class="tokyomenu-image" alt="菜单3">
            <div class="tokyomenu-number">4</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/5.jpg" class="tokyomenu-image" alt="菜单4">
            <div class="tokyomenu-number">5</div>
          </div>
        </div>
      </div>

      <!-- 第5页-6页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-3" style="z-index: 14;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/6.jpg" class="tokyomenu-image" alt="菜单5">
            <div class="tokyomenu-number">6</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/7.jpg" class="tokyomenu-image" alt="菜单6">
            <div class="tokyomenu-number">7</div>
          </div>
        </div>
      </div>

      <!-- 第7页-8页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-4" style="z-index: 13;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/8.jpg" class="tokyomenu-image" alt="菜单7">
            <div class="tokyomenu-number">8</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/9.jpg" class="tokyomenu-image" alt="菜单8">
            <div class="tokyomenu-number">9</div>
          </div>
        </div>
      </div>

      <!-- 第9页-10页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-5" style="z-index: 12;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/10.jpg" class="tokyomenu-image" alt="菜单9">
            <div class="tokyomenu-number">10</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/11.jpg" class="tokyomenu-image" alt="菜单10">
            <div class="tokyomenu-number">11</div>
          </div>
        </div>
      </div>

      <!-- 第11页-12页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-6" style="z-index: 11;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/12.jpg" class="tokyomenu-image" alt="菜单11">
            <div class="tokyomenu-number">12</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/13.jpg" class="tokyomenu-image" alt="菜单12">
            <div class="tokyomenu-number">13</div>
          </div>
        </div>
      </div>

      <!-- 第13页-14页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-7" style="z-index: 10;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/14.jpg" class="tokyomenu-image" alt="菜单13">
            <div class="tokyomenu-number">14</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/15.jpg" class="tokyomenu-image" alt="菜单14">
            <div class="tokyomenu-number">15</div>
          </div>
        </div>
      </div>

      <!-- 第15页-16页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-8" style="z-index: 9;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/16.jpg" class="tokyomenu-image" alt="菜单15">
            <div class="tokyomenu-number">16</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/17.jpg" class="tokyomenu-image" alt="菜单16">
            <div class="tokyomenu-number">17</div>
          </div>
        </div>
      </div>

      <!-- 第17页-18页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-9" style="z-index: 8;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/18.jpg" class="tokyomenu-image" alt="菜单17">
            <div class="tokyomenu-number">18</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/19.jpg" class="tokyomenu-image" alt="菜单18">
            <div class="tokyomenu-number">19</div>
          </div>
        </div>
      </div>

      <!-- 第19页-20页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-10" style="z-index: 7;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/20.jpg" class="tokyomenu-image" alt="菜单19">
            <div class="tokyomenu-number">20</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/21.jpg" class="tokyomenu-image" alt="菜单20">
            <div class="tokyomenu-number">21</div>
          </div>
        </div>
      </div>

      <!-- 第21页-22页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-11" style="z-index: 6;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/22.jpg" class="tokyomenu-image" alt="菜单21">
            <div class="tokyomenu-number">22</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/23.jpg" class="tokyomenu-image" alt="菜单22">
            <div class="tokyomenu-number">23</div>
          </div>
        </div>
      </div>

      <!-- 第23页-24页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-12" style="z-index: 5;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/24.jpg" class="tokyomenu-image" alt="菜单23">
            <div class="tokyomenu-number">24</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/25.jpg" class="tokyomenu-image" alt="菜单24">
            <div class="tokyomenu-number">25</div>
          </div>
        </div>
      </div>

      <!-- 第25页-26页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-13" style="z-index: 4;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/26.jpg" class="tokyomenu-image" alt="菜单25">
            <div class="tokyomenu-number">26</div>
          </div>
        </div>
        <div class="tokyomenu-page-back">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/27.jpg" class="tokyomenu-image" alt="菜单26">
            <div class="tokyomenu-number">27</div>
          </div>
        </div>
      </div>

      <!-- 第27页-28页 -->
      <div class="tokyomenu-page tokyomenu-page-right" id="tokyomenu-page-14" style="z-index: 3;">
        <div class="tokyomenu-page-front">
          <div class="tokyomenu-content">
            <img src="tokyomenu/tokyomenu/28.jpg" class="tokyomenu-image" alt="菜单27">
            <div class="tokyomenu-number">28</div>
          </div>
        </div>
        
      </div>
    </div>

    <!-- 翻页按钮 -->
    <div class="tokyomenu-nav">
      <button class="tokyomenu-nav-btn" id="tokyomenu-prev" onclick="tokyomenuPrev()">◀ 上一页</button>
      <button class="tokyomenu-nav-btn" id="tokyomenu-next" onclick="tokyomenuNext()">下一页 ▶</button>
    </div>
  </div>
  </section>
  </div>

<div class="swiper-slide">
  <section class="location-section">
    <div class="location-container">
      <div class="location-info">
        <?php echo getTokyoLocationHtml(); ?>
    </div>
      <div class="map-container">
        <iframe src="https://www.google.com/maps/d/embed?mid=1H0dUMmKC2N8DqeTOfx3ueTNAze-YJ5U&ehbc=2E312F" width="640" height="480"></iframe>
      </div>
    </div>
  </section>
  </div>

<div class="swiper-slide footer-slide">
  <section class="scroll-buffer">
  <footer class="footer">
    <div class="footer-section">
      <h4><a href="index.php">首页</a></h4>
      <ul>
        <li><a href="index.php#comprofile">公司简介</a></li>
        <li><a href="index.php#culture">公司文化</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4><a href="about.php">关于我们</a></h4>
      <ul>
        <li><a href="about.php#intro">集团简介</a></li>
        <li><a href="about.php#vision">信念与方向</a></li>
        <li><a href="about.php#values">核心价值观</a></li>
        <li><a href="about.php#timeline-1">发展历史</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4>旗下品牌</h4>
      <ul>
        <li><a href="tokyo-japanese-cuisine.php">TOKYO JAPANESE </br>CUISINE</li>
      </ul>
    </div>

    <div class="footer-section">
      <h4><a href="joinus.php">加入我们</a></h4>
      <ul>
        <li><a href="joinus.php">公司福利</li>
        <li><a href="joinus.php#comphoto-container">我们的足迹</li>
        <li><a href="joinus.php#particles">招聘的职位</li>
        <li><a href="joinus.php#map">联系我们</a></li>        
      </ul>
    </div>
  </footer>

  <button id="backToTop" onclick="scrollToTop()">&#8673;</button>
  
  <div class="footer-bottom">
    © 2025 Kunzz Holdings Sdn. Bhd. All rights reserved.
  </div>
  </section>
  </div>

  
  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->

<div class="social-sidebar">
    <!-- Facebook -->
    <a href="https://www.facebook.com/share/16ZihY9RN6/" target="_blank" class="social-icon facebook" title="进入 Facebook 世界">
        <img src="images/images/fbicon.png" alt="Facebook">
    </a>

    <!-- Instagram -->
    <a href="https://www.instagram.com" target="_blank" class="social-icon instagram" title="探索 Instagram 精彩">
        <img src="images/images/igicon.png" alt="Instagram">
    </a>

    <!-- WhatsApp -->
    <a href="https://www.whatsapp.com" target="_blank" class="social-icon whatsapp" title="连接 WhatsApp">
        <img src="images/images/wsicon.png" alt="WhatsApp">
    </a>
</div>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="app.js"></script>
<script>
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');
        const loginBtn = document.querySelector('.login-btn');

        // 登录下拉菜单元素
        const loginDropdownMenu = document.getElementById('loginDropdownMenu');

        // 语言切换下拉菜单元素
        const languageBtn = document.getElementById('languageBtn');
        const languageDropdownMenu = document.getElementById('languageDropdownMenu');

        function moveLoginBtn() {
            if (window.innerWidth <= 768) {
                if (!navMenu.contains(loginBtn)) {
                    navMenu.appendChild(loginBtn);
                }
            } else {
                // 如果宽度大于768，确保loginBtn在right-section中
                const rightSection = document.querySelector('.right-section');
                if (rightSection && !rightSection.contains(loginBtn)) {
                    rightSection.insertBefore(loginBtn, rightSection.firstChild);
                }
            }
        }

        // 点击汉堡切换菜单
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // ========== 登录下拉菜单功能 ==========
        let loginHoverTimeout;

        // 鼠标进入登录按钮区域时显示下拉菜单
        loginBtn.addEventListener('mouseenter', function() {
            // 清除可能存在的隐藏延时
            clearTimeout(loginHoverTimeout);
            
            // 显示菜单
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        // 鼠标离开登录按钮区域时延迟隐藏下拉菜单
        loginBtn.addEventListener('mouseleave', function() {
            // 设置延时隐藏，给用户时间移动到下拉菜单
            loginHoverTimeout = setTimeout(() => {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            }, 100); // 200ms延迟
        });

        // 鼠标进入登录下拉菜单时保持显示
        loginDropdownMenu.addEventListener('mouseenter', function() {
            // 清除隐藏延时
            clearTimeout(loginHoverTimeout);
            
            // 确保菜单保持显示
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        // 鼠标离开登录下拉菜单时隐藏
        loginDropdownMenu.addEventListener('mouseleave', function() {
            loginDropdownMenu.classList.remove('show');
            loginBtn.classList.remove('active');
        });

        // 点击登录下拉菜单项时的处理
        const loginDropdownItems = document.querySelectorAll('.login-dropdown-item');
        loginDropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                console.log('选择了登录：', this.textContent);
                
                // 关闭下拉菜单
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            });
        });

        // ========== 语言切换下拉菜单功能 ==========
        let languageHoverTimeout;

        // 鼠标进入语言按钮区域时显示下拉菜单
        languageBtn.addEventListener('mouseenter', function() {
            // 清除可能存在的隐藏延时
            clearTimeout(languageHoverTimeout);
            
            // 显示菜单
            languageDropdownMenu.classList.add('show');
            languageBtn.classList.add('active');
        });

        // 鼠标离开语言按钮区域时延迟隐藏下拉菜单
        languageBtn.addEventListener('mouseleave', function() {
            // 设置延时隐藏，给用户时间移动到下拉菜单
            languageHoverTimeout = setTimeout(() => {
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            }, 200); // 200ms延迟
        });

        // 鼠标进入语言下拉菜单时保持显示
        languageDropdownMenu.addEventListener('mouseenter', function() {
            // 清除隐藏延时
            clearTimeout(languageHoverTimeout);
            
            // 确保菜单保持显示
            languageDropdownMenu.classList.add('show');
            languageBtn.classList.add('active');
        });

        // 鼠标离开语言下拉菜单时隐藏
        languageDropdownMenu.addEventListener('mouseleave', function() {
            languageDropdownMenu.classList.remove('show');
            languageBtn.classList.remove('active');
        });

        // 点击语言下拉菜单项时的处理
        const languageDropdownItems = document.querySelectorAll('.language-dropdown-item');
        languageDropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                console.log('选择了语言：', this.textContent);

                // 关闭下拉菜单（这仍然可以保留）
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
                
                // 更新语言按钮显示
                const selectedLang = this.getAttribute('data-lang');
                if (selectedLang === 'en') {
                    languageBtn.textContent = 'EN';
                } else {
                    languageBtn.textContent = 'CN';
                }
                
                // 关闭下拉菜单
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
                
                // 这里可以添加实际的语言切换逻辑
                console.log('切换到语言：', selectedLang);
            });
        });

        // ESC键关闭所有下拉菜单
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            }
        });

        // 点击页面其他地方关闭下拉菜单
        document.addEventListener('click', function(e) {
            // 如果点击的不是登录相关元素，关闭登录下拉菜单
            if (!loginBtn.contains(e.target) && !loginDropdownMenu.contains(e.target)) {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            }
            
            // 如果点击的不是语言相关元素，关闭语言下拉菜单
            if (!languageBtn.contains(e.target) && !languageDropdownMenu.contains(e.target)) {
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            }
        });

        // 页面加载时处理
        window.addEventListener('DOMContentLoaded', moveLoginBtn);

        // 窗口大小改变时也处理，防止resize后login位置错乱
        window.addEventListener('resize', moveLoginBtn);
    </script>
<script>
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      const container = entry.target;

      if (entry.isIntersecting) {
        container.classList.add('visible');

        // 原有的 scale-fade-in 元素处理
        container.querySelectorAll('.scale-fade-in').forEach(el => {
          el.style.animation = 'none'; // 重置动画
          el.offsetHeight; // 触发重绘
          el.style.animation = ''; // 重新应用 CSS 动画
          el.style.animationPlayState = 'running';
        });

        // mission section 元素处理
        if (container.classList.contains('mission-section')) {
          container.classList.add('visible');
        }

        // 新增：location section 元素处理
        if (container.classList.contains('location-section')) {
          container.classList.add('contact-loaded');
        }

      } else {
        container.classList.remove('visible');

        // 原有的 scale-fade-in 元素处理
        container.querySelectorAll('.scale-fade-in').forEach(el => {
          el.style.animation = 'none'; // 停止当前动画
          el.style.opacity = '0'; // 恢复初始状态
          el.style.transform = 'translateY(20px)';
          el.offsetHeight; // 强制回流
          el.style.animation = '';
          el.style.animationPlayState = 'paused';
        });

        // mission section 元素处理 - 重置卡片状态
        if (container.classList.contains('mission-section')) {
          container.classList.remove('visible');
          // 让CSS自然控制状态，不强制重置
        }

        // 新增：location section 元素处理 - 移除 contact-loaded 类
        if (container.classList.contains('location-section')) {
          container.classList.remove('contact-loaded');
        }
      }
    });
  }, {
    threshold: 0.2
  });

  // 初始化：暂停动画并设置初始状态
  document.querySelectorAll('.animate-on-scroll').forEach(container => {
    container.querySelectorAll('.scale-fade-in').forEach(el => {
      el.style.animationPlayState = 'paused';
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
    });
    observer.observe(container);
  });

  // mission section 的观察器和初始化
  document.querySelectorAll('.mission-section').forEach(container => {
    // 不强制设置初始状态，让CSS来控制
    observer.observe(container);
  });

  // 新增：location section 的观察器和初始化
  document.querySelectorAll('.location-section').forEach(container => {
    // 让CSS控制初始状态，不需要强制设置
    observer.observe(container);
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // 等待DOM完全加载
document.addEventListener('DOMContentLoaded', function() {
    // 1. 先初始化主要的全屏垂直滑动Swiper
    const mainSwiper = new Swiper('.swiper:not(.environment-wrapper)', {
        direction: 'vertical',
        mousewheel: true,
        speed: 800,
        simulateTouch: false,
        allowTouchMove: true, // 允许程序控制
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        slidesPerView: 'auto',
        spaceBetween: 0,
        on: {
            slideChange: function() {
                console.log('主Swiper切换到:', this.activeIndex);
                updatePageIndicator(this.activeIndex);
            },
            reachEnd: function() {
                this.allowTouchMove = true;
            },
            setTransition: function(duration) {
                setTimeout(() => {
                    if (this.progress > 0.95) {
                        updatePageIndicator(5); // 滑到最后一页
                    } else {
                        updatePageIndicator(this.activeIndex);
                    }
                }, duration + 50);
            }
        }
    });

    // 2. 然后初始化工作环境中的水平滑动Swiper
    const environmentSwiper = new Swiper('.environment-wrapper', {
        direction: 'horizontal',
        loop: true,
        slidesPerView: 1,
        spaceBetween: 20,
        centeredSlides: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.environment-wrapper .swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.environment-wrapper .swiper-button-next',
            prevEl: '.environment-wrapper .swiper-button-prev',
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            }
        }
    });

    // 3. 页面指示器功能 - 确保只控制主Swiper
    const pageDots = document.querySelectorAll('.page-dot');

    // 点击圆点跳转到对应页面
    pageDots.forEach((dot, index) => {
        dot.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            console.log(`点击了第${index}个圆点`);
            
            // 确保使用主Swiper实例
            if (mainSwiper && mainSwiper.slideTo) {
                mainSwiper.slideTo(index, 800);
                console.log('主Swiper跳转到:', index);
            } else {
                console.error('主Swiper未正确初始化');
            }
        });
    });

    // 4. 更新页面指示器状态
    function updatePageIndicator(activeIndex) {
        console.log('更新页面指示器，当前页:', activeIndex);
        pageDots.forEach((dot, index) => {
            if (index === activeIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    // 5. 初始化页面指示器
    updatePageIndicator(0);
    
    // 6. 验证两个Swiper实例
    console.log('主Swiper实例:', mainSwiper);
    console.log('环境Swiper实例:', environmentSwiper);
});
    </script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const homeContent = document.querySelector('.home-content');
    
    // 强制隐藏，使用内联样式确保优先级
    homeContent.style.opacity = '0';
    homeContent.style.visibility = 'hidden';
    homeContent.style.transform = 'translateY(30px)';
    
    console.log('开始加载背景图...');
    
    const bgImg = new Image();
    bgImg.src = "images/images/j2餐厅1.jpg";

    bgImg.onload = function () {
        console.log('背景图加载完成！');
        
        document.querySelector('.home').classList.add('gradient-loaded');
        
        // 使用内联样式显示内容
        homeContent.style.opacity = '1';
        homeContent.style.visibility = 'visible';
        homeContent.style.transform = 'translateY(0)';
        homeContent.style.transition = 'all 0.8s ease-out';
        
        // 移除hidden类
        homeContent.classList.remove('hidden');
        
        // 显示其他界面元素
        document.querySelector('.navbar').classList.add('navbar-loaded');
        document.querySelector('.social-sidebar').classList.add('social-loaded');
        document.querySelector('.page-indicator').classList.add('indicator-loaded');
    };
    
    bgImg.onerror = function () {
        console.error('背景图加载失败');
        homeContent.style.opacity = '1';
        homeContent.style.visibility = 'visible';
        homeContent.style.transform = 'translateY(0)';
        homeContent.classList.remove('hidden');
        
        document.querySelector('.navbar').classList.add('navbar-loaded');
        document.querySelector('.social-sidebar').classList.add('social-loaded');
        document.querySelector('.page-indicator').classList.add('indicator-loaded');
    };
});
</script>
<script>
  function goToLocation() {
    const map = document.getElementById('custom-map');

    // ⚠️ 这里请替换成你 My Maps 中标记具体地点的链接（可以在地图中点击目标点 → 分享 → 嵌入地图 获取新的 URL）
    map.src = "https://www.google.com/maps/d/embed?mid=11C1m9L_Gcj_n8ynGotoCNc4rzq0FX54&ehbc=2E312F#target-location";
  }
</script>
<script>
let tokyomenuCurrentPage = 1;
const tokyomenuTotalPages = 14;
let tokyomenuAnimating = false;
let autoFlipTimer = null;
const AUTO_FLIP_INTERVAL = 3500;
const AUTO_FLIP_PAUSE = 4000;

function tokyomenuUpdateNav() {
    document.getElementById('tokyomenu-prev').disabled = tokyomenuCurrentPage === 1;
    document.getElementById('tokyomenu-next').disabled = tokyomenuCurrentPage === tokyomenuTotalPages;
}

function tokyomenuUpdateZIndex() {
    const coverPage = document.getElementById('tokyomenu-page-left');
    coverPage.style.zIndex = tokyomenuCurrentPage === 1 ? 50 : 1;

    for (let i = 1; i <= tokyomenuTotalPages; i++) {
        const page = document.getElementById(`tokyomenu-page-${i}`);
        if (i < tokyomenuCurrentPage) {
            page.style.zIndex = 1;
        } else if (i === tokyomenuCurrentPage) {
            page.style.zIndex = 100;
        } else {
            page.style.zIndex = tokyomenuTotalPages - i + 10;
        }
    }
}

function tokyomenuNext() {
    if (tokyomenuCurrentPage < tokyomenuTotalPages && !tokyomenuAnimating) {
        tokyomenuAnimating = true;
        const page = document.getElementById(`tokyomenu-page-${tokyomenuCurrentPage}`);
        page.classList.add('tokyomenu-turning');
        page.style.transform = 'rotateY(-180deg)';

        setTimeout(() => {
            page.classList.remove('tokyomenu-turning');
            tokyomenuCurrentPage++;
            tokyomenuUpdateZIndex();
            tokyomenuUpdateNav();
            tokyomenuAnimating = false;
        }, 1200);
    }
}

function tokyomenuPrev() {
    if (tokyomenuCurrentPage > 1 && !tokyomenuAnimating) {
        tokyomenuAnimating = true;
        tokyomenuCurrentPage--;
        const page = document.getElementById(`tokyomenu-page-${tokyomenuCurrentPage}`);
        page.classList.add('tokyomenu-turning');
        page.style.transform = 'rotateY(0deg)';

        setTimeout(() => {
            page.classList.remove('tokyomenu-turning');
            tokyomenuUpdateZIndex();
            tokyomenuUpdateNav();
            tokyomenuAnimating = false;
        }, 1200);
    }
}

function tokyomenuResetBookInstant() {
    for (let i = 1; i < tokyomenuTotalPages; i++) {
        const page = document.getElementById(`tokyomenu-page-${i}`);
        page.style.transition = 'none';
        page.style.transform = 'rotateY(0deg)';
        void page.offsetHeight;
        page.style.transition = 'transform 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
    }
    tokyomenuCurrentPage = 1;
    tokyomenuUpdateZIndex();
    tokyomenuUpdateNav();
}

function startAutoFlip() {
    stopAutoFlip();
    autoFlipTimer = setInterval(() => {
        if (tokyomenuAnimating) return;

        if (tokyomenuCurrentPage < tokyomenuTotalPages) {
            tokyomenuNext();
        } else {
            tokyomenuResetBookInstant();
        }
    }, AUTO_FLIP_INTERVAL);
}

function stopAutoFlip() {
    if (autoFlipTimer) {
        clearInterval(autoFlipTimer);
        autoFlipTimer = null;
    }
}

// 用户点击时暂停
document.querySelectorAll('.tokyomenu-page, .tokyomenu-nav-btn').forEach(el => {
    el.addEventListener('click', () => {
        stopAutoFlip();
        setTimeout(startAutoFlip, AUTO_FLIP_PAUSE);
    });
});

// 点击页面左右翻页
document.querySelectorAll('.tokyomenu-page').forEach(page => {
    page.addEventListener('click', function (e) {
        const rect = this.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const pageWidth = rect.width;

        if (clickX < pageWidth / 2) {
            tokyomenuPrev();
        } else {
            tokyomenuNext();
        }
    });
});

// 鼠标悬停时暂停自动翻页
const menuContainer = document.querySelector('.tokyomenu-container');
if (menuContainer) {
    menuContainer.addEventListener('mouseenter', () => {
        stopAutoFlip();
    });
    menuContainer.addEventListener('mouseleave', () => {
        startAutoFlip();
    });
}

// 🆕 进入页面时才开始自动翻页（IntersectionObserver）
const tokyomenuSection = document.querySelector('.tokyomenu-section');
if (tokyomenuSection) {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startAutoFlip();
            } else {
                stopAutoFlip();
            }
        });
    }, { threshold: 0.4 }); // 进入40%以上视口才触发

    observer.observe(tokyomenuSection);
}

// 初始化导航
tokyomenuUpdateNav();
</script>
<script>
        function showContent(contentId) {
            // 隐藏所有内容面板
            const panels = document.querySelectorAll('.content-panel');
            panels.forEach(panel => {
                panel.classList.remove('active');
            });
            
            // 显示选中的内容面板
            const activePanel = document.getElementById(contentId);
            if (activePanel) {
                activePanel.classList.add('active');
            }
            
            // 更新按钮状态
            const buttons = document.querySelectorAll('.values-tab-btn');
            buttons.forEach(btn => {
                btn.classList.remove('values-selected');
            });
            
            // 为当前按钮添加选中状态
            event.target.classList.add('values-selected');
            
            // 更新滑块位置
            updateTabSlider(event.target);
            
            // 切换背景图片
            updateBackground(contentId);
            
            // 重新触发动画
            setTimeout(() => {
                const titleElement = activePanel.querySelector('.values-content-title');
                const subtitleElement = activePanel.querySelector('.values-content-subtitle');
                const descriptionElement = activePanel.querySelector('.values-content-description');
                const imageElement = activePanel.querySelector('.values-content-image');
                
                // 重置并重新触发文本动画
                [titleElement, subtitleElement, descriptionElement].forEach(element => {
                    if (element) {
                        element.style.animation = 'none';
                        element.offsetHeight; // 触发重绘
                        element.style.animation = '';
                    }
                });
                
                // 重置并重新触发图片动画
                if (imageElement) {
                    imageElement.style.animation = 'none';
                    imageElement.offsetHeight; // 触发重绘
                    imageElement.style.animation = '';
                }
            }, 100);
        }
        
        function updateTabSlider(activeButton) {
            const slider = document.querySelector('.tab-slider');
            const container = document.querySelector('.tab-container');
            
            if (slider && container && activeButton) {
                // 获取容器的计算样式
                const containerStyle = window.getComputedStyle(container);
                const containerPadding = parseFloat(containerStyle.paddingLeft);
                
                // 计算按钮相对于容器的位置
                const containerRect = container.getBoundingClientRect();
                const buttonRect = activeButton.getBoundingClientRect();
                
                // 计算按钮相对于容器的左偏移，减去容器的内边距
                const leftOffset = buttonRect.left - containerRect.left - containerPadding;
                
                // 设置滑块的宽度和高度与按钮相同，使用setProperty确保优先级
                slider.style.setProperty('width', buttonRect.width + 'px', 'important');
                slider.style.setProperty('height', buttonRect.height + 'px', 'important');
                
                // 移动滑块到按钮位置
                slider.style.transform = `translateX(${leftOffset}px)`;
                
                // 强制重绘
                slider.offsetHeight;
                
                console.log('滑块更新:', {
                    buttonWidth: buttonRect.width,
                    buttonHeight: buttonRect.height,
                    leftOffset: leftOffset,
                    containerPadding: containerPadding,
                    sliderWidth: slider.style.width,
                    sliderHeight: slider.style.height
                });
            }
        }
        
        function updateBackground(contentId) {
            const valuesSection = document.querySelector('.values-section');
            
            if (valuesSection) {
                // 移除所有背景类
                valuesSection.classList.remove('mission-bg', 'vision-bg');
                
                // 添加切换动画类
                valuesSection.classList.add('background-transitioning');
                
                // 根据内容ID添加对应的背景类
                if (contentId === 'values') {
                    valuesSection.classList.add('mission-bg');
                } else if (contentId === 'mission') {
                    valuesSection.classList.add('vision-bg');
                }
                
                // 移除过渡类，让CSS动画生效
                setTimeout(() => {
                    valuesSection.classList.remove('background-transitioning');
                }, 50);
            }
        }
        
        // 预加载背景图片
        function preloadBackgroundImages() {
            const images = [
                'images/images/fujibg.jpg',
                'images/images/sushi.jpg'
            ];
            
            images.forEach(src => {
                const img = new Image();
                img.src = src;
            });
        }
        
        // 初始化滑块位置和背景
        document.addEventListener('DOMContentLoaded', function() {
            // 预加载背景图片
            preloadBackgroundImages();
            
            // 初始化滑块位置
            const firstButton = document.querySelector('.values-tab-btn.values-selected');
            if (firstButton) {
                // 延迟一点时间确保DOM完全渲染
                setTimeout(() => {
                    updateTabSlider(firstButton);
                }, 100);
                
                // 再次延迟确保所有样式都已应用
                setTimeout(() => {
                    updateTabSlider(firstButton);
                }, 500);
            }
            
            // 初始化背景为使命背景
            const valuesSection = document.querySelector('.values-section');
            if (valuesSection) {
                valuesSection.classList.add('mission-bg');
            }
        });
        
        // 窗口大小改变时重新计算滑块位置
        window.addEventListener('resize', function() {
            const activeButton = document.querySelector('.values-tab-btn.values-selected');
            if (activeButton) {
                updateTabSlider(activeButton);
            }
        });
    </script>
    <script>
    // 导航栏旗下品牌下拉菜单控制
    const navBrandsDropdown = document.querySelector('.nav-item.nav-dropdown');
    const navBrandsDropdownMenu = document.getElementById('brandsNavDropdownMenu');

    if (navBrandsDropdown && navBrandsDropdownMenu) {
        navBrandsDropdown.addEventListener('mouseenter', function() {
            navBrandsDropdownMenu.classList.add('show');
        });

        navBrandsDropdown.addEventListener('mouseleave', function() {
            navBrandsDropdownMenu.classList.remove('show');
        });
    }
</script>
</body>
</html>