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
  <div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
  <section class="home">
  <?php echo getMediaHtml('home_background'); ?>

  <div class="home-content hidden animate-on-scroll">
    <h1 class="scale-fade-in">Make The Space Warm <span style="font-size: 1.5em;">.</span> Let The Team Shine</h1>
    <div class="decor-line scale-fade-in"></div>
    <p class="scale-fade-in">
      We build a comfortable atmosphere with details and nourish every passion and dedication in a positive culture.<br />
      We believe that efficiency comes from trust and innovation comes from freedom. A team with warmth,<br />
      can create sustained value and move steadily forward in the direction of the industry benchmark.
    </p>
  </div>
</section>
  </div>

  <div class="swiper-slide">
  <section class="about-section" id="comprofile">
  <div class="comprofile-section">
    <div class="comprofile-text">
        <p class="comprofile-subtitle animate-on-scroll slide-in-left delay-1">
            <span class="circle"></span>Company Profile
        </p>
      <h2 class="comprofile-title animate-on-scroll slide-in-left delay-2">KUNZZ HOLDINGS</h2>
      <p class="comprofile-description animate-on-scroll slide-in-left delay-3">
        Kunzz Holdings was established in 2023 with the original intention of establishing a unified management platform for its businesses and improving the efficiency of resource integration. We adhere to the mission of "creating a positive and comfortable working environment", continue to promote the construction of organizational atmosphere, and achieve a warmer corporate culture. We believe in the core spirit of positivity, efficiency, flexibility, and integrity, always take goal orientation and consistent concepts as the criteria, pursue excellence, and be innovative.
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
      <div class="stat-label">Year of Establishment</div>
    </div>
    <div class="divider"></div>
    <div class="stat-box">
      <div class="stat-number">3</div>
      <div class="stat-label">Number of Subsidiaries</div>
    </div>
    <div class="divider"></div>
    <div class="stat-box">
      <div class="stat-number">70+</div>
      <div class="stat-label">Number of Employees</div>
    </div>
  </div> 
  </section>
  </div>

  <div class="swiper-slide">
  <section id="culture" class="culture-section">
    <div class="culture-left animate-on-scroll card-tilt-in-left">
      <div class="culture-card">
        <img src="../images/images/积极向上 (1).png" alt="icon" class="culture-icon">
        <h3>Positive</h3>
        <p>Stay Positive in Challenges<br>Embrace Change to Grow</p>
      </div>
      <div class="culture-card">
        <img src="../images/images/高效执行 (1).png" alt="icon" class="culture-icon">
        <h3>Efficient</h3>
        <p>Say it, do it. Respond fast.<br>Result-Driven & Action-Oriented</p>
      </div>
      <div class="culture-card">
        <img src="../images/images/灵活应变 (1).png" alt="icon" class="culture-icon">
        <h3>Flexible</h3>
        <p>Face Change and Challenges<br>Stay Open and Adjust Fast</p>
      </div>
      <div class="culture-card">
        <img src="../images/images/诚信待人 (1).png" alt="icon" class="culture-icon">
        <h3>Honesty</h3>
        <p>Build Trust with Sincerity<br>is Our Core Principle</p>
      </div>
    </div>

    <div class="culture-right animate-on-scroll">
      <h2 class="culture-title animate-on-scroll culture-scale-fade delay-6">Our Core Values<br>Company Culture</h2>
      <p class="culture-description animate-on-scroll culture-scale-fade delay-7">
        At Kunzz Holdings, we believe that culture determines height. We are goal-oriented and based on ideas to build a team with efficient execution and high collaborative spirit.
        We advocate flat communication, respect the growth rhythm of each member, and encourage sharing, learning and co-creation. Here, every effort can be seen and every breakthrough is worth encouraging.
      </p>
      <a href="about.php" class="culture-button animate-on-scroll culture-scale-fade delay-8">Learn More &gt;&gt;</a>
    </div>
  </section>
  </div>

<?php include '../public_en/footer.php'; ?>

  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->
<?php include '../public_en/social.php'; ?>
  
<script src="../app.js" defer></script>
<script src="../public_en/header.js" defer></script>
<script src="../public_en/footer.js" defer></script>
<script src="../public_en/social.js" defer></script>
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
        // 恢复到你原来的配置，只添加最小的修改
const swiper = new Swiper('.swiper', {
    direction: 'vertical',
    mousewheel: true,
    speed: 800,
    simulateTouch: false,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    // 添加这个配置来处理不同高度的slide
    slidesPerView: 'auto',
    spaceBetween: 0,
    on: {
        slideChange: function() {
            // 更新页面指示器
            updatePageIndicator(this.activeIndex);
        },
        // 添加这个事件来处理最后一页的特殊情况
        reachEnd: function() {
            // 确保最后一页正确显示
            this.allowTouchMove = true;
        },
        // 添加进度监听来处理最后一页的双向滑动
        setTransition: function(duration) {
            // 在过渡结束后检查进度
            setTimeout(() => {
                if (this.progress > 0.95) {
                    updatePageIndicator(3); // 滑到最后一页
                } else {
                    updatePageIndicator(this.activeIndex); // 从最后一页滑回来时用正常的activeIndex
                }
            }, duration + 50);
        }
    }
});

// 页面指示器功能（与 header 中的指示器类名保持一致）
const pageDots = document.querySelectorAll('.header-page-dot');

// 点击圆点跳转到对应页面
pageDots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        swiper.slideTo(index);
    });
});

// 更新页面指示器状态
function updatePageIndicator(activeIndex) {
    pageDots.forEach((dot, index) => {
        if (index === activeIndex) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

// 初始化页面指示器
updatePageIndicator(0);

// 检查URL参数中是否有slide参数，自动导航到对应的slide
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