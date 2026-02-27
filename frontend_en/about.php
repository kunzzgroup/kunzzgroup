<?php
// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
include_once '../media_config.php';

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/about.css','../public_en/css/components/header.css','../public_en/css/components/footer.css','../public_en/css/components/social.css'];
$showPageIndicator = true;
$totalSlides = 5;

// 包含header
include '../public_en/header.php';

// 在header之后获取时间线数据（扁平记录，允许同一年多条）
$timelineItems = getTimelineItems('en');
?>
    
<div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
    <section class="aboutus-section">
    <div class="aboutus-banner">
        <?php echo getMediaHtml('about_background'); ?>
      <div class="aboutus-content">
        <h1>About Us</h1>
        <p>Explore Kunzz Holdings’ Vision and Growth Journey</p>
      </div>
    </div>

    <div class="aboutus-intro">
      <div class="intro-content">
        <h1>Group Profile</h1>
        <p>
          Kunzz Holdings is a diversified Malaysian group dedicated to resource integration and efficiency.<br>
          We offer strategic guidance and operational synergy to all our subsidiaries.<br>
          We build brands. We empower teams. We create impact.
        </p>
      </div>
    </div>
</section>
    </div>
  
    <div class="swiper-slide">
    <section id="vision" class="vision">
    <div class="vision-content animate-on-scroll vision-slide-down">
      <h1>Our Beliefs and Direction</h1>
      <p>
        We believe that every great achievement begins with a clear belief.<br>
        Our mission, vision, culture, and values are both the guiding light and the bottom line we all uphold.<br>
        With these principles in mind, we continue to grow, to break through, and to lift each other higher.
      </p>

      <div class="vision-cards">
        <!-- Card 1 -->
        <div class="vision-card animate-on-scroll slide-in-left">
          <div class="vision-label">Our Mission</div>
          <h2>Creating a positive and comfortable working environment</h2>
          <p>
            Here, we believe that a positive work environment nurtures stronger teams. 
            We strive to create a warm and welcoming space where everyone feels a true sense of belonging — 
            a place where each member can feel safe to give their best and grow together. In such an environment, 
            challenges no longer feel cold, and every effort is seen, valued, and appreciated.
          </p>
        </div>

        <!-- Card 2 -->
        <div class="vision-card animate-on-scroll slide-in-right">
          <div class="vision-label">Our Vision</div>
          <h2>Build an efficient team, create the future of the industry</h2>
          <p>
            A great team is the source of continuous value creation for any enterprise. 
            Only when efficiency and innovation go hand in hand can a team break boundaries and achieve greatness. 
            With steady steps, we are on the path to becoming an industry benchmark — letting achievements speak and moving forward with belief.
          </p>
        </div>
      </div>
    </div>
  </section>
  </div>

  <div class="swiper-slide">
  <section id="values" class="values-section">
        <div class="values-top animate-on-scroll">
            <h2 class="values-title animate-on-scroll values-scale-fade delay-3">Our Core <span style="color: #FF5C00;">Values</span></h2>
            <p class="values-description animate-on-scroll values-scale-fade delay-4">
                Our core values are present in every effort and every act of collaboration. 
                They unite us in culture, strengthen our belief through challenges, 
                and keep our original purpose steady as we grow.
            </p>
        </div>
      
        <div class="values-bottom animate-on-scroll card-tilt-in-left">
            <div class="values-card">
                <img src="../images/images/目标导向.png?v=2026" alt="icon" class="values-icon">
                <h3>Goal-Oriented</h3>
                <p>Result-oriented, focused on key tasks, with clear direction and purpose at every step.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/理念一致.png?v=2026" alt="icon" class="values-icon">
                <h3>Aligned Thinking</h3>
                <p>Maintain strong consensus, stay mentally in sync, align on goals, and reduce internal friction.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/追求卓越.png?v=2026" alt="icon" class="values-icon">
                <h3>Seek Excellence</h3>
                <p>Not just completing tasks — but doing them better, aiming higher, and improving continuously.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/创新精神.png?v=2026" alt="icon" class="values-icon">
                <h3>Creativity</h3>
                <p>Embrace change, dare to try, break limits, and keep exploring new ways to grow.</p>
            </div>
        </div>
    </section>
  </div>

  <div class="swiper-slide">
  <section class="timeline-section" id="timeline-1">
        <h1>— Milestones —</h1>
        
        <!-- 横向时间线导航 -->
        <div class="timeline-nav">
            <div class="nav-arrow prev" onclick="navigateTimeline('prev')">‹</div>
            <div class="nav-arrow next" onclick="navigateTimeline('next')">›</div>
            
            <div class="timeline-scroll-container">
                <div class="timeline-track"></div>
                <div class="timeline-items-container" id="timelineContainer">
                    <?php 
                    $index = 0;
                    foreach ($timelineItems as $item): 
                        $year = $item['year'];
                    ?>
                    <div class="timeline-item <?php echo $index === 0 ? 'active' : ''; ?>" data-year="<?php echo htmlspecialchars($year); ?>">
                        <div class="timeline-bullet"><?php echo htmlspecialchars($year); ?></div>
                    </div>
                    <?php 
                    $index++;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>

        <!-- 卡片容器 -->
        <div class="timeline-content-container">
            <div class="timeline-cards-wrapper">
                <?php 
                $index = 0;
                foreach ($timelineItems as $item): 
                    $year = $item['year'];
                    $itemClass = $index === 0 ? 'active' : ($index === 1 ? 'next' : 'hidden');
                ?>
                <!-- <?php echo htmlspecialchars($year); ?>年内容 -->
                <div class="timeline-content-item <?php echo $itemClass; ?>" data-year="<?php echo htmlspecialchars($year); ?>" data-index="<?php echo $index; ?>">
                    <div class="timeline-content" onclick="selectCardIndex(<?php echo (int)$index; ?>)">
                        <div class="timeline-image">
                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($year); ?>年发展">
                        </div>
                        <div class="timeline-text">
                            <div class="year-badge"><?php echo $year; ?><?php echo !empty($item['month']) ? ' · ' . (int)$item['month'] : ''; ?></div>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description1']); ?></p>
                            <p><?php echo htmlspecialchars($item['description2']); ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                $index++;
                endforeach; 
                ?>
            </div>
        </div>
    </section>
  </div>

  <?php include '../public_en/footer.php'; ?>

  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->
<?php include '../public_en/social.php'; ?>
  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../app.js?v=2026"></script>
<script src="../public_en/header.js?v=2026"></script>
<script src="../public_en/footer.js?v=2026"></script>
<script src="../public_en/social.js?v=2026"></script>
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
        // 通用的 animate-on-scroll observer（保持原有逻辑）
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const container = entry.target;

                if (entry.isIntersecting) {
                    container.classList.add('visible');

                    container.querySelectorAll('.scale-fade-in').forEach(el => {
                        el.style.animation = 'none';
                        el.offsetHeight;
                        el.style.animation = '';
                        el.style.animationPlayState = 'running';
                    });

                } else {
                    container.classList.remove('visible');

                    container.querySelectorAll('.scale-fade-in').forEach(el => {
                        el.style.animation = 'none';
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(20px)';
                        el.offsetHeight;
                        el.style.animation = '';
                        el.style.animationPlayState = 'paused';
                    });
                }
            });
        }, {
            threshold: 0.2
        });

        // AboutUs 专用的 IntersectionObserver - 支持重复触发
        const aboutObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const target = entry.target;

                if (entry.isIntersecting) {
                    // 直接触发动画，不再等待图片加载
                    if (target.classList.contains('aboutus-banner')) {
                        target.classList.add('content-loaded');
                    } else if (target.classList.contains('aboutus-intro')) {
                        target.classList.add('intro-loaded');
                    }
                } else {
                    // 离开视窗时移除动画类，重置状态
                    if (target.classList.contains('aboutus-banner')) {
                        target.classList.remove('content-loaded');
                    } else if (target.classList.contains('aboutus-intro')) {
                        target.classList.remove('intro-loaded');
                    }
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -10% 0px'
        });

        // 时间线专用的 IntersectionObserver - 支持重复触发
        const timelineObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const timelineSection = entry.target;

                if (entry.isIntersecting) {
                    // 进入视窗时启动时间线动画
                    timelineSection.classList.add('timeline-active');
                    resetAndStartTimelineAnimation(timelineSection);
                } else {
                    // 离开视窗时重置动画状态
                    timelineSection.classList.remove('timeline-active');
                    resetTimelineAnimation(timelineSection);
                }
            });
        }, {
            threshold: 0.3,
            rootMargin: '0px 0px -20% 0px'
        });

        // 重置并启动时间线动画
        function resetAndStartTimelineAnimation(timelineSection) {
            const title = timelineSection.querySelector('h1');
            const track = timelineSection.querySelector('.timeline-track');
            const container = timelineSection.querySelector('.timeline-items-container');
            const items = timelineSection.querySelectorAll('.timeline-item');
            const arrows = timelineSection.querySelectorAll('.nav-arrow');

            // 重置所有元素的动画
            [title, track, container, ...items, ...arrows].forEach(el => {
                if (el) {
                    el.style.animation = 'none';
                    el.offsetHeight; // 强制重排
                    el.style.animation = ''; // 恢复原始动画
                }
            });
        }

        // 重置时间线动画状态
        function resetTimelineAnimation(timelineSection) {
            const title = timelineSection.querySelector('h1');
            const track = timelineSection.querySelector('.timeline-track');
            const container = timelineSection.querySelector('.timeline-items-container');
            const items = timelineSection.querySelectorAll('.timeline-item');
            const arrows = timelineSection.querySelectorAll('.nav-arrow');

            // 重置标题
            if (title) {
                title.style.opacity = '0';
                title.style.transform = 'translateY(20px)';
            }

            // 重置轨道
            if (track) {
                track.style.transform = 'translateY(-50%) scaleX(0)';
            }

            // 重置容器
            if (container) {
                container.style.opacity = '0';
            }

            // 重置项目
            items.forEach(item => {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.5) translateY(20px)';
            });

            // 重置箭头
            arrows.forEach(arrow => {
                arrow.style.opacity = '0';
                arrow.style.transform = 'translateY(-50%) scale(0.8)';
            });
        }

        // 初始化观察器
        document.addEventListener('DOMContentLoaded', () => {
            // 初始化通用 animate-on-scroll 观察器
            document.querySelectorAll('.animate-on-scroll').forEach(container => {
                container.querySelectorAll('.scale-fade-in').forEach(el => {
                    el.style.animationPlayState = 'paused';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(20px)';
                });
                observer.observe(container);
            });

            // 初始化 aboutus 观察器
            const aboutBanner = document.querySelector('.aboutus-banner');
            const aboutIntro = document.querySelector('.aboutus-intro');
            
            if (aboutBanner) {
                aboutObserver.observe(aboutBanner);
            }
            
            if (aboutIntro) {
                aboutObserver.observe(aboutIntro);
            }

            // 初始化时间线观察器
            const timelineSection = document.querySelector('.timeline-section');
            if (timelineSection) {
                // 初始化时间线元素状态
                resetTimelineAnimation(timelineSection);
                timelineObserver.observe(timelineSection);
            }

            // 页面加载完成后立即检查可见元素并触发动画
            setTimeout(() => {
                const banner = document.querySelector('.aboutus-banner');
                const intro = document.querySelector('.aboutus-intro');
                const timeline = document.querySelector('.timeline-section');
                
                if (banner && isElementInViewport(banner)) {
                    banner.classList.add('content-loaded');
                }
                
                if (intro && isElementInViewport(intro)) {
                    intro.classList.add('intro-loaded');
                }

                if (timeline && isElementInViewport(timeline)) {
                    timeline.classList.add('timeline-active');
                    resetAndStartTimelineAnimation(timeline);
                }
            }, 100); // 给DOM一点时间完成渲染
        });

        // 检查元素是否在视窗内
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
                rect.bottom > 0 &&
                rect.left < (window.innerWidth || document.documentElement.clientWidth) &&
                rect.right > 0
            );
        }
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
                    updatePageIndicator(4); // 滑到最后一页
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
    </script>
    <script>
        let currentIndex = 0;
        let years = <?php echo json_encode(getTimelineYearsFlat('en')); ?>;
        let totalItems = years.length;
        const navItems = document.querySelectorAll('.timeline-item');
        const container = document.getElementById('timelineContainer');

        // 拖拽相关变量 - 优化后的设置
        let isDragging = false;
        let startX = 0;
        let currentX = 0;
        let dragThreshold = 15; // 增加阈值，减少误触
        let hasTriggered = false;
        let dragStartTime = 0; // 记录拖拽开始时间
        let isAnimating = false; // 防止动画期间的操作冲突

        function updateTimelineNav() {
            const navItems = document.querySelectorAll('.timeline-item');
            
            // 更新导航状态
            navItems.forEach((item, index) => {
                item.classList.toggle('active', index === currentIndex);
            });

            // 简化的居中计算：让当前选中的年份在中间
            const containerWidth = container.parentElement.offsetWidth;
            const totalItems = navItems.length;
            const itemWidth = 120; // 使用固定的item宽度（与CSS中的min-width一致）
            
            // 计算让当前年份居中的translateX值
            const centerPosition = containerWidth / 2;
            const currentItemPosition = currentIndex * itemWidth + itemWidth / 2;
            const translateX = centerPosition - currentItemPosition;
            
            // 使用CSS transition实现平滑滚动
            container.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            container.style.transform = `translateX(${translateX}px)`;
            
            // 清除transition，避免影响后续操作
            setTimeout(() => {
                container.style.transition = '';
            }, 400);
        }

        function updateCardPositions() {
            const cards = document.querySelectorAll('.timeline-content-item');
            
            cards.forEach((card, index) => {
                card.classList.remove('active', 'prev', 'next', 'hidden');
                
                if (index === currentIndex) {
                    card.classList.add('active');
                } else if (index === (currentIndex - 1 + totalItems) % totalItems) {
                    card.classList.add('prev');
                } else if (index === (currentIndex + 1) % totalItems) {
                    card.classList.add('next');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        function navigateTimeline(direction) {
            if (isAnimating) return;
            
            isAnimating = true;
            
            if (direction === 'next') {
                currentIndex = (currentIndex + 1) % totalItems;
            } else {
                currentIndex = (currentIndex - 1 + totalItems) % totalItems;
            }
            
            updateTimelineNav();
            updateCardPositions();
            
            // 动画完成后重置标志
            setTimeout(() => {
                isAnimating = false;
            }, 400); // 增加到600ms匹配新的动画时长
        }

        function selectCard(year) {
            if (isAnimating) return;
            
            const index = years.indexOf(year.toString());
            if (index !== -1 && index !== currentIndex) {
                currentIndex = index;
                showTimelineItem(year.toString());
            }
        }

        function showTimelineItem(year) {
            // Keep currentIndex; just refresh view to avoid jumping to first duplicate year
            updateTimelineNav();
            updateCardPositions();
        }

        // 新增：按索引选择，避免同一年份重复时跳到第一条
        function selectCardIndex(index) {
            if (isAnimating) return;
            if (index < 0 || index >= totalItems) return;
            currentIndex = index;
            updateTimelineNav();
            updateCardPositions();
        }

        // 优化后的拖拽处理
        function handleDragStart(e) {
            if (isAnimating) return;
            
            const clickedCard = e.target.closest('.timeline-content-item');
            if (!clickedCard) return;
            
            isDragging = true;
            hasTriggered = false;
            dragStartTime = Date.now();
            startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
            
            document.body.style.cursor = 'grabbing';
            document.body.style.userSelect = 'none';
            
            e.preventDefault();
            e.stopPropagation();
        }

        function handleDragMove(e) {
            if (!isDragging || hasTriggered || isAnimating) return;
            
            currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
            const deltaX = currentX - startX;
            const dragTime = Date.now() - dragStartTime;
            
            // 增加时间限制，避免过快触发
            if (Math.abs(deltaX) >= dragThreshold && dragTime > 50) {
                hasTriggered = true;
                
                if (deltaX > 0) {
                    navigateTimeline('prev');
                } else {
                    navigateTimeline('next');
                }
                
                // 延迟结束拖拽，给动画时间
                setTimeout(() => {
                    handleDragEnd(e);
                }, 50);
            }
            
            e.preventDefault();
        }

        function handleDragEnd(e) {
            if (!isDragging) return;
            
            isDragging = false;
            hasTriggered = false;
            dragStartTime = 0;
            
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            
            startX = 0;
            currentX = 0;
        }

        // 改进的事件监听器
        let clickTimeout;

        document.addEventListener('mousedown', (e) => {
            const card = e.target.closest('.timeline-content-item');
            if (card && !isAnimating) {
                // 清除之前的点击超时
                if (clickTimeout) {
                    clearTimeout(clickTimeout);
                }
                handleDragStart(e);
            }
        });

        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);
        document.addEventListener('mouseleave', handleDragEnd);

        // 触摸事件
        document.addEventListener('touchstart', (e) => {
            const card = e.target.closest('.timeline-content-item');
            if (card && !isAnimating) {
                handleDragStart(e);
            }
        }, { passive: false });

        document.addEventListener('touchmove', handleDragMove, { passive: false });
        document.addEventListener('touchend', handleDragEnd);

        // 导航项点击（strictly by index）
        navItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                if (!isDragging && !isAnimating) {
                    currentIndex = index;
                    updateTimelineNav();
                    updateCardPositions();
                }
            });
        });

        // 优化的点击处理 - 支持左右卡片切换（严格按索引，不按年份）
        document.addEventListener('click', (e) => {
            if (isDragging || hasTriggered || isAnimating) return;
            
            const card = e.target.closest('.timeline-content-item');
            if (card) {
                // 检查是否点击的是左右卡片
                if (card.classList.contains('prev')) {
                    // 点击左侧卡片，切换到上一个
                    navigateTimeline('prev');
                    return;
                } else if (card.classList.contains('next')) {
                    // 点击右侧卡片，切换到下一个
                    navigateTimeline('next');
                    return;
                } else if (!card.classList.contains('active')) {
                    // 点击其他卡片，直接按索引跳转
                    const idxAttr = card.getAttribute('data-index');
                    const idx = parseInt(idxAttr, 10);
                    if (!isNaN(idx)) {
                        selectCardIndex(idx);
                    }
                }
            }
        });

        // 键盘导航
        document.addEventListener('keydown', (e) => {
            if (!isAnimating) {
                if (e.key === 'ArrowLeft') {
                    navigateTimeline('prev');
                } else if (e.key === 'ArrowRight') {
                    navigateTimeline('next');
                }
            }
        });

        // 防止文本选择
        document.addEventListener('selectstart', (e) => {
            if (isDragging) {
                e.preventDefault();
            }
        });

        // 初始化
        updateTimelineNav();
        updateCardPositions();

        // 窗口大小改变时重新计算位置
        window.addEventListener('resize', () => {
            if (!isAnimating) {
                setTimeout(() => {
                    updateTimelineNav();
                }, 100);
            }
        });
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
    <script>
        // 添加这个函数到你现有的JavaScript代码中
        function goToSlide(slideIndex) {
        if (typeof swiper !== 'undefined') {
            swiper.slideTo(slideIndex);
        }
        }

        // 或者，如果你想要更具体的跳转函数
        function goToAboutusIntro() {
        if (typeof swiper !== 'undefined') {
            swiper.slideTo(0);
        }
        }

        function goToVision() {
        if (typeof swiper !== 'undefined') {
            swiper.slideTo(1); // 跳转到第3个slide（公司文化）
        }
        }

        function goToValues() {
        if (typeof swiper !== 'undefined') {
            swiper.slideTo(2); // 跳转到第3个slide（公司文化）
        }
        }

        function goToTimeline() {
        if (typeof swiper !== 'undefined') {
            swiper.slideTo(3); // 跳转到第3个slide（公司文化）
        }
        }
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

        // =========================
        // 🚀 Scroll 动画优化
        // =========================

        // 使用 IntersectionObserver 来观察元素
        document.addEventListener("DOMContentLoaded", () => {
        const elements = document.querySelectorAll(".animate-on-scroll");

        const observer = new IntersectionObserver(
            (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                // 逐个延迟触发动画（避免同时执行卡顿）
                setTimeout(() => {
                    entry.target.classList.add("visible");
                }, entry.target.dataset.delay || 0);

                // 只触发一次，进入视口后取消观察
                observer.unobserve(entry.target);
                }
            });
            },
            {
            threshold: 0.1, // 元素至少 10% 出现在视口中才触发
            }
        );

        elements.forEach((el, index) => {
            // 给每个元素一个递增延迟（50ms * index）
            el.dataset.delay = index * 50;
            observer.observe(el);
        });
        });
    </script>
    
    <!-- 背景音乐 -->
    <?php echo getBgMusicHtml(); ?>
</body>
</html>