<?php
// 禁用页面缓存
header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
session_start();
include_once '../media_config.php';

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/about.css','../public_en/css/components/footer.css','../public_en/css/components/social.css'];
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
        <p>Discover the origins and growth journey of Kunzz Holdings</p>
      </div>
    </div>

    <div class="aboutus-intro">
      <div class="intro-content">
        <h1>About Kunzz Group</h1>
        <p>
          Kunzz Holdings is a diversified holding group headquartered in Malaysia, focused on resource integration and operational efficiency.<br>
          We provide strategic support and operational synergy for our subsidiaries. We are committed to building brands with heart,<br>
          unleashing team potential, and helping businesses stand out in a competitive landscape.
        </p>
      </div>
    </div>
</section>
    </div>
  
    <div class="swiper-slide">
    <section id="vision" class="vision">
    <div class="vision-content animate-on-scroll vision-slide-down">
      <h1>Our Beliefs &amp; Direction</h1>
      <p>
        We believe every great achievement begins with a clear set of beliefs.<br>
        Mission, vision, culture, and values are the guiding lights of our journey and the principles we uphold together.<br>
        Guided by these ideals, we keep growing, keep breaking through, and keep inspiring one another.
      </p>

      <div class="vision-cards">
        <!-- Card 1 -->
        <div class="vision-card animate-on-scroll slide-in-left">
          <div class="vision-label">Our Mission</div>
          <h2>Foster a Positive and Comfortable Work Environment</h2>
          <p>
            We believe that a great work environment nurtures a great team.
            We strive to build a warm, inclusive, and belonging space
            where every member can perform confidently and grow together.
            Here, challenges are met with support, and every effort is seen and valued.
          </p>
        </div>

        <!-- Card 2 -->
        <div class="vision-card animate-on-scroll slide-in-right">
          <div class="vision-label">Our Vision</div>
          <h2>Build a High-Performance Team, Shape the Future of the Industry</h2>
          <p>
            A great team is the core engine of sustained value creation.
            Only when efficiency and innovation go hand in hand can a team transcend limits and achieve the extraordinary.
            We are steadily walking the path toward becoming an industry benchmark,
            letting our achievements speak, and moving forward with conviction.
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
                Our core values run through every effort and every collaboration within our team.
                They unite us in culture, keep us grounded in challenges,
                and maintain our original intention throughout our growth.
            </p>
        </div>
      
        <div class="values-bottom animate-on-scroll card-tilt-in-left">
            <div class="values-card">
                <img src="../images/images/目标导向.png?v=<?php echo time(); ?>" alt="icon" class="values-icon">
                <h3>Goal-Oriented</h3>
                <p>Results-driven, focused on key tasks, with a clear sense of direction and purpose in every step.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/理念一致.png?v=<?php echo time(); ?>" alt="icon" class="values-icon">
                <h3>Aligned Values</h3>
                <p>Maintain a high level of consensus, stay on the same wavelength, and align on goals to minimize internal friction.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/追求卓越.png?v=<?php echo time(); ?>" alt="icon" class="values-icon">
                <h3>Pursuit of Excellence</h3>
                <p>Not satisfied with merely completing tasks — we strive to do better, achieve higher standards, and continuously optimize every aspect of our work.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/创新精神.png?v=<?php echo time(); ?>" alt="icon" class="values-icon">
                <h3>Innovative Spirit</h3>
                <p>Embrace change, dare to try, break existing frameworks, and continuously explore new methods, tools, and perspectives to drive business growth.</p>
            </div>
        </div>
    </section>
  </div>

  <div class="swiper-slide">
  <section class="timeline-section" id="timeline-1">
        <h1>— Our Development History —</h1>
        
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
                    <div class="timeline-item <?php echo $index === 0 ? 'active' : ''; ?>" data-year="<?php echo htmlspecialchars($year); ?>" data-month="<?php echo (int)($item['month'] ?? 0); ?>" data-index="<?php echo $index; ?>">
                        <div class="timeline-bullet"><?php echo htmlspecialchars($year); ?></div>
                    </div>
                    <?php 
                    $index++;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>

        <!-- 时间线主体：月份侧栏 + 卡片 -->
        <div class="timeline-body">
            <div class="timeline-month-sidebar" id="monthSidebar"></div>
            <div class="timeline-content-container">
                <div class="timeline-cards-wrapper">
                <?php 
                $index = 0;
                foreach ($timelineItems as $item): 
                    $year = $item['year'];
                ?>
                <!-- <?php echo htmlspecialchars($year); ?>年内容 -->
                    <div class="timeline-content-item" data-year="<?php echo htmlspecialchars($year); ?>" data-index="<?php echo $index; ?>" data-month="<?php echo (int)($item['month'] ?? 0); ?>">
                    <div class="timeline-content">
                        <div class="timeline-image">
                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($year); ?> Development">
                        </div>
                        <div class="timeline-text">
                            <div class="year-badge"><?php echo htmlspecialchars($year); ?><?php if (!empty($item['month'])) { $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December']; echo ' &middot; ' . ($monthNames[(int)$item['month'] - 1] ?? 'Month ' . (int)$item['month']); } ?></div>
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
        </div> <!-- close timeline-body -->
    </section>
  </div>

  <?php include '../public_en/footer.php'; ?>

  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->
<?php include '../public_en/social.php'; ?>
  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../app.js?v=<?php echo time(); ?>"></script>
<script src="../public_en/header.js?v=<?php echo time(); ?>"></script>
<script src="../public_en/footer.js?v=<?php echo time(); ?>"></script>
<script src="../public_en/social.js?v=<?php echo time(); ?>"></script>
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

            const clearInlineAnimProps = (el) => {
                if (!el) return;
                el.style.opacity = '';
                el.style.transform = '';
            };
            clearInlineAnimProps(title);
            clearInlineAnimProps(track);
            clearInlineAnimProps(container);
            items.forEach(clearInlineAnimProps);
            arrows.forEach(clearInlineAnimProps);

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
    simulateTouch: true,
    touchEventsTarget: 'container',
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
        swiper.slideTo(slideIndex, 0); // 第二个参数0表示不使用动画效果，立即跳转
    }
}
    </script>
<script>
    </script>
    <script>
        let currentIndex = 0;
        let years = <?php echo json_encode(getTimelineYearsFlat('en')); ?>;
        let totalItems = years.length;
        let timelineData = <?php echo json_encode(array_values(array_map(function($item) {
            return ['year' => $item['year'], 'month' => (int)($item['month'] ?? 0)];
        }, $timelineItems))); ?>;
        const navItems = document.querySelectorAll('.timeline-item');
        const container = document.getElementById('timelineContainer');
        const contentItems = document.querySelectorAll('.timeline-content-item');
        let isAnimating = false;

        // ===== 切换到指定卡片（核心函数）=====
        function switchToCard(index) {
            if (index < 0 || index >= totalItems || isAnimating) return;
            isAnimating = true;
            currentIndex = index;

            // 更新卡片显示
            contentItems.forEach(item => item.classList.remove('active'));
            contentItems[index].classList.add('active');

            // 同步年份导航 + 月份侧栏
            updateTimelineNav();

            setTimeout(() => { isAnimating = false; }, 400);
        }

        // ===== 年份导航更新 =====
        function updateTimelineNav() {
            const allNavItems = document.querySelectorAll('.timeline-item');
            const currentYear = years[currentIndex];
            
            allNavItems.forEach((item) => {
                item.classList.toggle('active', item.getAttribute('data-year') === currentYear);
            });

            const visibleItems = Array.from(allNavItems).filter(item => !item.classList.contains('year-duplicate'));
            const containerWidth = container.parentElement.offsetWidth;
            const firstVisible = visibleItems[0];
            /* Desktop: keep fixed 120px centering; mobile only uses measured width */
            const itemWidth = (window.matchMedia('(max-width: 768px)').matches && firstVisible)
                ? firstVisible.getBoundingClientRect().width
                : 120;
            
            const activeVisibleIndex = visibleItems.findIndex(item => item.classList.contains('active'));
            const centerPosition = containerWidth / 2;
            const currentItemPosition = (activeVisibleIndex >= 0 ? activeVisibleIndex : 0) * itemWidth + itemWidth / 2;
            const translateX = centerPosition - currentItemPosition;
            
            container.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            container.style.transform = `translateX(${translateX}px)`;
            
            setTimeout(() => { container.style.transition = ''; }, 400);

            updateMonthSidebar();
        }

        // ===== 前/后导航 =====
        function navigateTimeline(direction) {
            if (direction === 'next') {
                switchToCard(Math.min(currentIndex + 1, totalItems - 1));
            } else {
                switchToCard(Math.max(currentIndex - 1, 0));
            }
        }

        // ===== 按索引跳转 =====
        function selectCardIndex(index) {
            switchToCard(index);
        }

        // ===== 键盘导航 =====
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                navigateTimeline('prev');
            } else if (e.key === 'ArrowRight') {
                navigateTimeline('next');
            }
        });

        // ===== 左右滑动/拖拽 =====
        let isDragging = false, startX = 0, dragThreshold = 30, hasTriggered = false;

        function handleDragStart(e) {
            if (isAnimating) return;
            if (!e.target.closest('.timeline-content-item')) return;
            isDragging = true;
            hasTriggered = false;
            startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
            e.preventDefault();
        }

        function handleDragMove(e) {
            if (!isDragging || hasTriggered || isAnimating) return;
            const currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
            const deltaX = currentX - startX;
            if (Math.abs(deltaX) >= dragThreshold) {
                hasTriggered = true;
                navigateTimeline(deltaX > 0 ? 'prev' : 'next');
            }
            e.preventDefault();
        }

        function handleDragEnd() {
            isDragging = false;
            hasTriggered = false;
        }

        document.addEventListener('mousedown', handleDragStart);
        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);
        document.addEventListener('touchstart', handleDragStart, { passive: false });
        document.addEventListener('touchmove', handleDragMove, { passive: false });
        document.addEventListener('touchend', handleDragEnd);

        // ===== 初始化 =====
        // 构建年份-月份分组
        let yearGroups = {};
        timelineData.forEach((item, index) => {
            if (!yearGroups[item.year]) yearGroups[item.year] = [];
            yearGroups[item.year].push({ index, month: item.month });
        });

        // 隐藏重复年份导航项
        let seenYears = {};
        navItems.forEach(item => {
            const year = item.getAttribute('data-year');
            if (seenYears[year]) {
                item.classList.add('year-duplicate');
            } else {
                seenYears[year] = true;
            }
        });

        // 导航项点击 — 跳到该年份第一条
        navItems.forEach((item) => {
            item.addEventListener('click', () => {
                const year = item.getAttribute('data-year');
                const group = yearGroups[year];
                if (group && group.length > 0) {
                    switchToCard(group[0].index);
                }
            });
        });

        // ===== 月份侧栏 =====
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        function updateMonthSidebar() {
            const currentYear = years[currentIndex];
            const months = (yearGroups[currentYear] || []).filter(m => m.month > 0);
            const sidebar = document.getElementById('monthSidebar');
            
            sidebar.innerHTML = months.map(m => 
                `<div class="month-item ${m.index === currentIndex ? 'active' : ''}" onclick="selectCardIndex(${m.index})">
                    <div class="month-dot"></div>
                    <span>${monthNames[(m.month - 1)] || 'Month ' + m.month}</span>
                </div>`
            ).join('');

            // 动态创建连接线，精确从第一个圆点中心到最后一个圆点中心
            requestAnimationFrame(() => {
                const existingLine = sidebar.querySelector('.sidebar-line');
                if (existingLine) existingLine.remove();
                
                const dots = sidebar.querySelectorAll('.month-dot');
                if (dots.length < 1) return;
                
                const sidebarRect = sidebar.getBoundingClientRect();
                const firstDot = dots[0].getBoundingClientRect();
                const lastDot = dots[dots.length - 1].getBoundingClientRect();
                
                let lineTop, lineHeight;
                if (dots.length === 1) {
                    // Only one month — show a short vertical line
                    const dotCenter = firstDot.top + firstDot.height / 2 - sidebarRect.top + sidebar.scrollTop;
                    lineTop = dotCenter - 50;
                    lineHeight = 100;
                } else {
                    lineTop = firstDot.top + firstDot.height / 2 - sidebarRect.top + sidebar.scrollTop - 20;
                    const lineBottom = lastDot.top + lastDot.height / 2 - sidebarRect.top + sidebar.scrollTop + 20;
                    lineHeight = lineBottom - lineTop;
                }
                const lineLeft = firstDot.left + firstDot.width / 2 - sidebarRect.left - 1.5;
                
                const line = document.createElement('div');
                line.className = 'sidebar-line';
                line.style.top = lineTop + 'px';
                line.style.height = lineHeight + 'px';
                line.style.left = lineLeft + 'px';
                sidebar.appendChild(line);
            });
        }

        // 初始化：设置第一张为 active
        contentItems.forEach(item => item.classList.remove('active'));
        if (contentItems.length > 0) contentItems[0].classList.add('active');
        updateTimelineNav();

        // 窗口大小改变时重新计算
        window.addEventListener('resize', () => {
            setTimeout(() => { updateTimelineNav(); }, 100);
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
