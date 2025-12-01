<?php
// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
include_once '../media_config.php';

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/about.css','../public/css/components/header.css','../public/css/components/footer.css','../public/css/components/social.css'];
$showPageIndicator = true;
$totalSlides = 5;

// 包含header
include '../public/header.php';

// 在header之后获取时间线数据（扁平记录，允许同一年多条）
$timelineItems = getTimelineItems('zh');
?>
    
<div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
    <section class="aboutus-section">
    <div class="aboutus-banner">
        <?php echo getMediaHtml('about_background'); ?>
      <div class="aboutus-content">
        <h1>关于我们</h1>
        <p>深入了解 Kunzz Holdings 的初心与成长轨迹</p>
      </div>
    </div>

    <div class="aboutus-intro">
      <div class="intro-content">
        <h1>集团简介</h1>
        <p>
          Kunzz Holdings 是一家总部位于马来西亚的多元化控股集团，专注资源整合与效率提升，<br>
          为旗下公司提供战略支持与运营协同。我们致力于用心打造品牌，<br>
          激发团队潜力，助力企业在竞争中脱颖而出。
        </p>
      </div>
    </div>
</section>
    </div>
  
    <div class="swiper-slide">
    <section id="vision" class="vision">
    <div class="vision-content animate-on-scroll vision-slide-down">
      <h1>我们的信念与方向</h1>
      <p>
        我们相信，所有伟大的成就，都始于一份清晰的信念。<br>
        使命、愿景、文化与价值观，是前进的灯塔，也是我们共同坚守的底线。<br>
        在这样的理念指引下，我们持续成长、持续突破、持续成就彼此。
      </p>

      <div class="vision-cards">
        <!-- Card 1 -->
        <div class="vision-card animate-on-scroll slide-in-left">
          <div class="vision-label">我们的使命</div>
          <h2>塑造积极向上和舒适的工作环境</h2>
          <p>
            在这里，我们相信好的工作环境，能孕育出更好的团队。
            我们努力打造一个温暖、有温度、有归属感的空间，
            让每位成员都能安心发挥，共同成长。
            在这里，挑战不再冰冷，努力也值得被看见。
          </p>
        </div>

        <!-- Card 2 -->
        <div class="vision-card animate-on-scroll slide-in-right">
          <div class="vision-label">我们的愿景</div>
          <h2>打造高效的团队，创造行业未来</h2>
          <p>
            一个好团队，是企业价值持续创造的源头。
            唯有高效与创新并行，团队才能突破边界、成就非凡。
            我们正以坚实步伐，走在打造行业标杆的路上，
            用成就说话，用信念前行。
          </p>
        </div>
      </div>
    </div>
  </section>
  </div>

  <div class="swiper-slide">
  <section id="values" class="values-section">
        <div class="values-top animate-on-scroll">
            <h2 class="values-title animate-on-scroll values-scale-fade delay-3">我们的核心<span style="color: #FF5C00;">价值观</span></h2>
            <p class="values-description animate-on-scroll values-scale-fade delay-4">
                核心价值观，贯穿在每一份努力、每一个团队协作之中。
                它让我们在文化中凝聚一致，在挑战中保持信念，
                在成长中维持不变的初心。
            </p>
        </div>
      
        <div class="values-bottom animate-on-scroll card-tilt-in-left">
            <div class="values-card">
                <img src="../images/images/目标导向.png" alt="icon" class="values-icon">
                <h3>目标导向</h3>
                <p>以结果为导向，聚焦关键任务，明确每一步的方向与意义。</p>
            </div>
            <div class="values-card">
                <img src="../images/images/理念一致.png" alt="icon" class="values-icon">
                <h3>理念一致</h3>
                <p>保持高度共识，思想同频，目标一致，减少内耗。</p>
            </div>
            <div class="values-card">
                <img src="../images/images/追求卓越.png" alt="icon" class="values-icon">
                <h3>追求卓越</h3>
                <p>不满足于完成任务，要追求干得更好，更高标准地完成目标，持续优化每项工作。</p>
            </div>
            <div class="values-card">
                <img src="../images/images/创新精神.png" alt="icon" class="values-icon">
                <h3>创新精神</h3>
                <p>拥抱变化、敢于尝试，突破既有框架，不断探索新方法、新工具与新角度，推动企业成长。</p>
            </div>
        </div>
    </section>
  </div>

  <div class="swiper-slide">
  <section class="timeline-section" id="timeline-1">
        <h1>— 我们的发展历史 —</h1>
        
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
                            <div class="year-badge"><?php echo htmlspecialchars($year); ?>年<?php echo !empty($item['month']) ? ' · ' . (int)$item['month'] . '月' : ''; ?></div>
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

  <?php include '../public/footer.php'; ?>

  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->
<?php include '../public/social.php'; ?>
  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../app.js"></script>
<script src="../public/header.js"></script>
<script src="../public/footer.js"></script>
<script src="../public/social.js"></script>
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
        swiper.slideTo(slideIndex, 0); // 第二个参数0表示不使用动画效果，立即跳转
    }
}
    </script>
<script>
    </script>
    <script>
        let currentIndex = 0;
        let years = <?php echo json_encode(getTimelineYearsFlat('zh')); ?>;
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
            // 保持 currentIndex 不变，仅刷新展示，避免重复年份回跳到首个
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

        // 导航项点击（严格按索引）
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