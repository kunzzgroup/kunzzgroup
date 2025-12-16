<?php
session_start();
include_once '../media_config.php';

// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 如果已登录或记住我，跳转到 dashboard
if (isset($_SESSION['user_id']) || (isset($_COOKIE['user_id']) && isset($_COOKIE['username']))) {
    header("Location: dashboard.php");
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
  <div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
  <section class="home">
  <?php echo getMediaHtml('home_background'); ?>

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
      <!-- 你可以换成自己的图片 -->
      <img src="../images/images/logo.png" alt="公司介绍图" />
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

  <div class="swiper-slide">
  <section id="culture" class="culture-section">
    <div class="culture-left animate-on-scroll card-tilt-in-left">
      <div class="culture-card">
        <img src="../images/images/积极向上 (1).png" alt="icon" class="culture-icon">
        <h3>积极向上</h3>
        <p>始终以正面心态面对挑战<br>在变化中寻找成长机会</p>
      </div>
      <div class="culture-card">
        <img src="../images/images/高效执行 (1).png" alt="icon" class="culture-icon">
        <h3>高效执行</h3>
        <p>说到做到，快速响应<br>追求结果导向与行动力</p>
      </div>
      <div class="culture-card">
        <img src="../images/images/灵活应变 (1).png" alt="icon" class="culture-icon">
        <h3>灵活应变</h3>
        <p>面对市场变化和问题<br>保持开放思维，快速调整策略</p>
      </div>
      <div class="culture-card">
        <img src="../images/images/诚信待人 (1).png" alt="icon" class="culture-icon">
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

<?php include '../public/footer.php'; ?>

  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->
<?php include '../public/social.php'; ?>
  
<script src="../app.js" defer></script>
<script src="../public/header.js" defer></script>
<script src="../public/footer.js" defer></script>
<script src="../public/social.js" defer></script>
<script>
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      const container = entry.target;

      if (entry.isIntersecting) {
        container.classList.add('visible');

        container.querySelectorAll('.scale-fade-in').forEach(el => {
          el.style.animation = 'none'; // 重置动画
          el.offsetHeight; // 触发重绘
          el.style.animation = ''; // 重新应用 CSS 动画
          el.style.animationPlayState = 'running';
        });

      } else {
        container.classList.remove('visible');

        container.querySelectorAll('.scale-fade-in').forEach(el => {
          el.style.animation = 'none'; // 停止当前动画
          el.style.opacity = '0'; // 恢复初始状态
          el.style.transform = 'translateY(20px)';
          el.offsetHeight; // 强制回流
          el.style.animation = '';
          el.style.animationPlayState = 'paused';
        });
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
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
// Initialize Swiper (same as other pages)
const swiper = new Swiper('.swiper', {
    direction: 'vertical',
    mousewheel: true,
    speed: 800,
    simulateTouch: false,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    slidesPerView: 'auto',
    spaceBetween: 0,
    on: {
        slideChange: function() {
            updatePageIndicator(this.activeIndex);
        },
        reachEnd: function() {
            this.allowTouchMove = true;
        },
        setTransition: function(duration) {
            setTimeout(() => {
                if (this.progress > 0.95) {
                    updatePageIndicator(3); // Last slide for index.php (0-3, total 4 slides)
                } else {
                    updatePageIndicator(this.activeIndex);
                }
            }, duration + 50);
        }
    }
});

// Page indicator functionality
const pageDots = document.querySelectorAll('.header-page-dot');

pageDots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        swiper.slideTo(index);
    });
});

function updatePageIndicator(activeIndex) {
    pageDots.forEach((dot, index) => {
        if (index === activeIndex) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

// Initialize page indicator
updatePageIndicator(0);

// Check URL parameter for slide navigation
const urlParams = new URLSearchParams(window.location.search);
const slideParam = urlParams.get('slide');

if (slideParam !== null) {
    const slideIndex = parseInt(slideParam, 10);
    if (!isNaN(slideIndex)) {
        swiper.slideTo(slideIndex, 0);
    }
}
</script>
<script>
window.addEventListener('load', () => {
  const video = document.querySelector('.background-video');
  const bgImage = document.querySelector('.background-image');
  
  // 触发动画的通用函数
  function triggerAnimations() {
    document.querySelector('.home').classList.add('gradient-loaded');
    document.querySelector('.home-content').classList.remove('hidden');

    // 强制触发重绘，重新开始动画
    void document.querySelector('.home-content').offsetWidth;

    // 添加动画类
    document.querySelector('.home-content h1').classList.add('scale-fade-in');
    document.querySelector('.home-content p').classList.add('scale-fade-in');

  }
  
  // 处理视频背景
  if (video) {
    // 监听视频是否可以播放（有足够的数据开始播放）
    video.addEventListener('canplay', function() {
      triggerAnimations();
    });
  }
  
  // 处理图片背景
  if (bgImage) {
    bgImage.addEventListener('load', function() {
      triggerAnimations();
    });
    
    // 如果图片已经加载完成
    if (bgImage.complete) {
      bgImage.dispatchEvent(new Event('load'));
    }
  }

  // 备用方案：如果视频/图片加载失败或很慢，设置一个最大等待时间
  setTimeout(() => {
    if (!document.querySelector('.home').classList.contains('gradient-loaded')) {
      triggerAnimations();
    }
  }, 500);
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
    document.addEventListener('DOMContentLoaded', function() {
      const bgMusic = document.getElementById('bgMusic');
      
      if (!bgMusic) {
        console.log('背景音乐元素未找到');
        return;
      }

      // 设置固定音量（例如 0.3 表示 30%）
      bgMusic.volume = 0.3;

      // 从 localStorage 恢复播放进度和状态
      const savedTime = localStorage.getItem('musicCurrentTime');
      const savedPlaying = localStorage.getItem('musicPlaying');
      const currentPage = window.location.pathname;

      if (savedTime) {
        bgMusic.currentTime = parseFloat(savedTime);
      }

      function tryPlay() {
        bgMusic.play().then(() => {
          localStorage.setItem('musicPlaying', 'true');
          localStorage.setItem('musicPage', currentPage);
        }).catch(error => {
          console.log('音乐播放失败:', error);
        });
      }

      // 如果之前在播放，立即继续播放
      if (savedPlaying === 'true') {
        // 稍微延迟以确保音频加载完成
        setTimeout(tryPlay, 100);
      }

      // 用户交互时开始播放
      const startEvents = ['click', 'keydown', 'touchstart'];
      const startPlay = () => {
        tryPlay();
        startEvents.forEach(event => {
          document.removeEventListener(event, startPlay);
        });
      };

      startEvents.forEach(event => {
        document.addEventListener(event, startPlay, { once: true });
      });

      // 定期保存播放进度
      setInterval(() => {
        if (!bgMusic.paused && bgMusic.currentTime > 0) {
          localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
          localStorage.setItem('musicPlaying', 'true');
          localStorage.setItem('musicPage', currentPage);
        }
      }, 1000);

      // 页面卸载前保存状态
      window.addEventListener('beforeunload', () => {
        if (bgMusic) {
          localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
          localStorage.setItem('musicPlaying', bgMusic.paused ? 'false' : 'true');
          localStorage.setItem('musicPage', currentPage);
        }
      });

      // 页面可见性变化时处理音乐
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
          // 页面变为可见时，检查是否应该继续播放
          const shouldPlay = localStorage.getItem('musicPlaying') === 'true';
          if (shouldPlay && bgMusic.paused) {
            tryPlay();
          }
        }
      });

      // 音乐加载错误处理
      bgMusic.addEventListener('error', (e) => {
        console.error('音乐加载失败:', e);
      });

      // 音乐加载成功处理
      bgMusic.addEventListener('loadeddata', () => {
        console.log('音乐加载完成');
      });
    });
    </script>
    
    <!-- 背景音乐 -->
    <?php echo getBgMusicHtml(); ?>
</body>
</html>