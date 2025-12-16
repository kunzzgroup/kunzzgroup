<?php
session_start();
include_once '../media_config.php';


// 禁用页面缓存
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 设置页面特定的变量
$pageTitle = 'KUNZZ HOLDINGS';
$additionalCSS = ['css/joinus.css','../public_en/css/components/header.css','../public_en/css/components/social.css','../public_en/css/components/footer.css']; // footer.css 放最后，确保样式优先级
$showPageIndicator = true;
$totalSlides = 5;

// 包含header
include '../public_en/header.php';
?>

<div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
  <section class="joinus-section">
    <!-- 上半部分：加入我们 -->
    <div class="joinus-banner">
        <?php echo getMediaHtml('joinus_background', ['style' => 'width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: -1;']); ?>
        <div class="joinus-content">
            <h1>Join Us</h1>
            <p>Here, your effort shapes more than results — you help build the brand and grow alongside it.</p>
        </div>
    </div>

    <!-- 下半部分：员工福利 -->
    <div class="benefits-wrapper" id="benefits">
      <h2>Benefits</h2>
      <div class="benefits-grid">
        <div class="benefit-item">
          <img src="../../images/images/带薪假期.png" alt="带薪假期">
          <p>Annual Leave</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/旅游奖励.png" alt="旅游奖励">
          <p>Travel Incentive</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/汽车奖励.png" alt="汽车奖励">
          <p>Car Allowance</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/房子奖励.png" alt="房子奖励">
          <p>Housing Allowance</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/年度绩效奖励.png" alt="年度绩效奖励">
          <p>Annual Bonus</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/专业培训与学习机会.png" alt="专业培训与学习机会">
          <p>Training & Learning</p>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="swiper-slide">
    <div class="comphoto-section" id="comphoto-container">
        <div class="comphoto-title">Our Journey</div>
    </div>
        <div id="comphoto-modal" class="comphoto-modal">
            <span class="comphoto-close">&times;</span>
            <div class="comphoto-modal-content">
                <img id="comphoto-modal-img" src="" alt="Enlarged photo">
            </div>
        </div>
    </div>

<div class="swiper-slide">

    <div class="job-section">
        <div class="job-table-container">
            <h2 class="job-table-title">Career Opportunities</h2>
        </div>
    <div class ="jobs-wrapper">    
        <div class="jobs-container">
            <?php echo getJobsHtml('en'); ?>
        </div>
    </div>    
</div>

    <!-- 职位详情弹窗 -->
    <div id="jobDetailModal" class="modal">
        <div class="job-detail-modal">
            <span class="close-btn" onclick="closeJobDetail()">&times;</span>
            <div class="job-detail-content">
                <h2 id="jobDetailTitle">Position Details</h2>
                <div class="job-detail-meta">
                    <div class="job-detail-item">
                        <span class="job-detail-label">Number of positions:</span>
                        <span id="jobDetailCount">-</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">Years of experience:</span>
                        <span id="jobDetailExperience">-</span>
                        <span class="job-detail-label"> Years</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">Posted:</span>
                        <span id="jobDetailPublishDate">-</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">Company:</span>
                        <span id="jobDetailCompany">-</span>
                    </div>
                    <div class="job-detail-item" id="jobDetailDepartment" style="display: none;">
                        <span class="job-detail-label">Department:</span>
                        <span id="jobDetailDepartmentValue">-</span>
                    </div>
                    <div class="job-detail-item" id="jobDetailSalary" style="display: none;">
                        <span class="job-detail-label">Salary:</span>
                        <span id="jobDetailSalaryValue">-</span>
                    </div>
                </div>
                <div class="job-detail-description">
                    <h3>Position Details: </h3>
                    <p id="jobDetailDescription">-</p>
                </div>
                <div class="job-detail-address">
                    <h3>Work location: </h3>
                    <p id="jobDetailAddress">-</p>
                </div>
                <div class="apply-btn-container">
                    <button class="apply-btn" onclick="openFormFromDetail()">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 弹窗表单 -->
    <div id="formModal" class="modal">
        <div class="job-modal-content">
            <span class="close-btn" onclick="closeForm()">&times;</span>
            <form class="job-form" id="jobApplicationForm" action="https://api.web3forms.com/submit" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="access_key" value="a18bc4c6-2f16-4861-8d10-a3de747cab50">
                <input type="hidden" name="redirect" value="https://kunzzgroup.com/frontend_en/success.html">
                <h2>Apply</h2>
                <label>Position Title: </label>
                <input type="text" id="formPosition" name="position" readonly>
                
                <!-- 中文姓名和性别在同一行 -->
                <div class="job-form-row">
                    <div class="job-half-width">
                        <label>Chinese Name: </label>
                        <input type="text" name="chinese_name" required pattern="[\u4e00-\u9fa5]{2,}" title="Please enter your Chinese name (at least two characters)">
                    </div>
                    <div class="job-half-width">
                        <label>Gender: </label>
                        <select name="gender" required>
                            <option value="">Please select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Others</option>
                        </select>
                    </div>
                </div>
                
                <label>English Name: </label>
                <input type="text" name="english_name" required pattern="[A-Za-z ]{2,}" title="Please enter your English name (letters only)">
                <label>Email: </label>
                <input type="email" name="email" required>
                <label>Phone Number: </label>
                <div class="job-phone-group">
                    <select name="country_code" required>
                        <option value="+60">MY (+60)</option>
                        <option value="+65">SG (+65)</option>
                        <option value="+86">CN (+86)</option>
                        <option value="+852">HK (+852)</option>
                        <option value="+81">JP (+81)</option>
                    </select>
                    <input type="tel" name="phone" required pattern="\d{1,10}" maxlength="10" title="Please enter a phone number with up to 10 digits.">
                </div>
                <label>Upload Resume (PDF, ≤3MB):</label>
                <input type="file" name="resume" id="resume" accept=".pdf" required>
                <button type="submit" class="job-submit-btn">Submit</button>
            </form>
        </div>
    </div>
  </div>    

  <!-- 意见表格 -->
  <div class="swiper-slide">
  <div class="form-wrapper">
  <h2 class="main-title">Kindly Provide Your Feedback</h2>
  <section class="join-us-form"> 
    <form id="jobApplicationForm" action="https://api.web3forms.com/submit" method="POST" enctype="multipart/form-data">

      <!-- 中文姓名 + 性别 -->
      <div class="form-group-row">
        <div class="half-width">
          <label for="chineseName">Chinese Name*</label>
          <input type="hidden" name="access_key" value="a18bc4c6-2f16-4861-8d10-a3de747cab50">
          <input type="hidden" name="redirect" value="https://kunzzgroup.com/frontend_en/success.html">
          <input type="text" id="chineseName" name="chineseName" placeholder="Please enter your Chinese name" required pattern="[\u4e00-\u9fa5]{2,}" title="Please enter your Chinese name (at least two characters)">
        </div>

        <div class="half-width">
          <label>Gender*</label>
          <div class="gender-options">
            <label><input type="radio" name="gender" value="male" required> Male</label>
            <label><input type="radio" name="gender" value="female" required> Female</label>
          </div>
        </div>
      </div>

      <!-- 英文姓名 + 职位类别 -->
      <div class="form-group-row">
        <div class="half-width">
          <label for="englishName">English Name*</label>
          <input type="text" id="englishName" name="englishName" placeholder="Please enter your English name" required pattern="[A-Za-z ]{2,}" title="Please enter your English name (letters only)">
        </div>
      </div>

      <!-- 手机号码 -->
      <div class="form-group">
        <label for="phone">Phone Number*</label>
        <div class="phone-input">
          <select id="countryCode" name="countryCode" required>
            <option value="+60">MY (+60)</option>
            <option value="+65">SG (+65)</option>
            <option value="+86">CN (+86)</option>
            <option value="+852">HK (+852)</option>
            <option value="+81">JP (+81)</option>
            <!-- 可以加更多国家 -->
          </select>
          <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Please enter your Phone Number" required pattern="\d{1,10}" maxlength="10" inputmode="numeric" title="Please enter a phone number">
        </div>
      </div>

      <!-- 电子邮箱 -->
      <div class="form-group">
        <label for="email">Email*</label>
        <input type="email" id="email" name="email" placeholder="Please enter your Email" required pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter your Email">
      </div>

      <!-- 信息 -->
      <div class="form-group">
        <label for="message">Information*</label>
        <textarea id="message" name="message" rows="5" required></textarea>
      </div>

      <!-- 提交按钮 -->
      <div class="form-group">
        <button type="submit" class="submit-btn">Submit</button>
      </div>
    </form>
</section>
</div>
</div>  

<div class="swiper-slide">
  <div class="contact-section-wrapper" id="map">
  <section class="contact-container">
  <div class="contact-info">
    <h2>Contact Us</h2>
    <p>Company Name：Kunzz Holdings Sdn. Bhd.</p>
    <p>
      Address：
      <a href="javascript:void(0);" onclick="goToLocation()" class="no-style-link">
        25, Jln Tanjong 3, Taman Desa Cemerlang, 81800 Ulu Tiram, Johor Darul Ta'zim
      </a>
    </p>
    <p>Phone Number：+60 13-553 5355</p>
    <p>Email Address：kunzzholdings@gmail.com</p>
    <p>Business Hours：Monday to Friday 9AM-6PM</p>
  </div>

  <div class="map-container">
    <iframe
      id="custom-map"
      src="https://www.google.com/maps/d/embed?mid=1WGUSQUviVSNKcc7LNK-aSDA6j6S3EMc&ehbc=2E312F"
      width="640"
      height="480"
    ></iframe>
  </div>
</section>
</div>
</div>

<?php include '../public_en/footer.php'; ?>

  </div>
</div>
<?php include '../public_en/social.php'; ?>
    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="app.js"></script>
<script src="../public_en/header.js"></script>
<script src="../public_en/footer.js"></script>
<script src="../public_en/social.js"></script>
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

// AboutUs & JoinUs & CompPhoto & JobTable & JobCards & Contact 专用的 IntersectionObserver - 支持重复触发
const aboutObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        const target = entry.target;

        if (entry.isIntersecting) {
            // 直接触发动画，不再等待图片加载
            if (target.classList.contains('aboutus-banner')) {
                target.classList.add('content-loaded');
            } else if (target.classList.contains('aboutus-intro')) {
                target.classList.add('intro-loaded');
            } else if (target.classList.contains('joinus-banner')) {
                target.classList.add('joinus-loaded');
            } else if (target.classList.contains('benefits-wrapper')) {
                target.classList.add('benefits-loaded');
            } else if (target.id === 'footprint-container') {
                // 为"我们的足迹"容器添加动画类
                target.classList.add('footprint-loaded');
            } else if (target.classList.contains('job-table-container')) {
                // 为招聘职位容器添加动画类
                target.classList.add('job-table-loaded');
            } else if (target.classList.contains('jobs-grid')) {
                // 为职位卡片网格添加动画类
                target.classList.add('jobs-loaded');
            } else if (target.classList.contains('contact-section-wrapper')) {
                // 为联系我们区域添加动画类
                target.classList.add('contact-loaded');
            }
        } else {
            // 离开视窗时移除动画类，重置状态
            if (target.classList.contains('aboutus-banner')) {
                target.classList.remove('content-loaded');
            } else if (target.classList.contains('aboutus-intro')) {
                target.classList.remove('intro-loaded');
            } else if (target.classList.contains('joinus-banner')) {
                target.classList.remove('joinus-loaded');
            } else if (target.classList.contains('benefits-wrapper')) {
                target.classList.remove('benefits-loaded');
            } else if (target.id === 'footprint-container') {
                // 离开视窗时移除动画类
                target.classList.remove('footprint-loaded');
            } else if (target.classList.contains('job-table-container')) {
                // 离开视窗时移除动画类
                target.classList.remove('job-table-loaded');
            } else if (target.classList.contains('jobs-grid')) {
                // 离开视窗时移除动画类
                target.classList.remove('jobs-loaded');
            } else if (target.classList.contains('contact-section-wrapper')) {
                // 离开视窗时移除动画类
                target.classList.remove('contact-loaded');
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

    // 初始化 aboutus & joinus & comphoto & jobtable & jobcards & contact 观察器
    const aboutBanner = document.querySelector('.aboutus-banner');
    const aboutIntro = document.querySelector('.aboutus-intro');
    const joinusBanner = document.querySelector('.joinus-banner');
    const benefitsWrapper = document.querySelector('.benefits-wrapper');
    const footprintContainer = document.querySelector('#footprint-container'); // 修复：正确的ID
    const jobTableContainer = document.querySelector('.job-table-container');
    const jobsGrid = document.querySelector('.jobs-grid');
    const contactWrapper = document.querySelector('.contact-section-wrapper');
    
    if (aboutBanner) {
        aboutObserver.observe(aboutBanner);
    }
    
    if (aboutIntro) {
        aboutObserver.observe(aboutIntro);
    }

    if (joinusBanner) {
        aboutObserver.observe(joinusBanner);
    }

    if (benefitsWrapper) {
        aboutObserver.observe(benefitsWrapper);
    }

    // 添加"我们的足迹"容器的观察器
    if (footprintContainer) {
        aboutObserver.observe(footprintContainer);
    }

    // 添加招聘职位容器的观察器
    if (jobTableContainer) {
        aboutObserver.observe(jobTableContainer);
    }

    // 添加职位卡片网格的观察器
    if (jobsGrid) {
        aboutObserver.observe(jobsGrid);
    }

    // 添加联系我们区域的观察器
    if (contactWrapper) {
        aboutObserver.observe(contactWrapper);
    }

    // 初始化时间线观察器
    const timelineSection = document.querySelector('.timeline-section');
    if (timelineSection) {
        // 初始化时间线元素状态
        resetTimelineAnimation(timelineSection);
        timelineObserver.observe(timelineSection);
    }
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
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const homeContent = document.querySelector('.home-content');
    
    // 强制隐藏主内容，等待背景图加载
    if (homeContent) {
        homeContent.style.opacity = '0';
        homeContent.style.visibility = 'hidden';
        homeContent.style.transform = 'translateY(30px)';
    }
    
    console.log('开始加载背景图...');
    
    const bgImg = new Image();
    // 使用动态配置的背景图片路径
    <?php 
    $media = getMediaConfig('joinus_background');
    $filePath = $media['file'];
    if (strpos($filePath, '/') !== 0 && strpos($filePath, 'http') !== 0) {
        $filePath = '../' . $filePath;
    }
    $timestamp = file_exists($filePath) ? '?v=' . filemtime($filePath) : '?v=' . time();
    echo "bgImg.src = \"$filePath$timestamp\";";
    ?>

    bgImg.onload = function () {
        console.log('背景图加载完成！开始显示动画');
        
        
        // 显示背景渐变
        const homeSection = document.querySelector('.home');
        if (homeSection) {
            homeSection.classList.add('gradient-loaded');
        }
        
        // 显示主要内容
        if (homeContent) {
            homeContent.style.opacity = '1';
            homeContent.style.visibility = 'visible';
            homeContent.style.transform = 'translateY(0)';
            homeContent.style.transition = 'all 0.8s ease-out';
            homeContent.classList.remove('hidden');
        }
    };
    
    bgImg.onerror = function () {
        console.error('背景图加载失败，但仍显示界面元素');
        
        
        if (homeContent) {
            homeContent.style.opacity = '1';
            homeContent.style.visibility = 'visible';
            homeContent.style.transform = 'translateY(0)';
            homeContent.classList.remove('hidden');
        }
    };

    // 添加超时保护：如果5秒内背景图还没加载完成，强制显示所有元素
    setTimeout(() => {
        if (homeContent && homeContent.style.opacity === '0') {
            homeContent.style.opacity = '1';
            homeContent.style.visibility = 'visible';
            homeContent.style.transform = 'translateY(0)';
            homeContent.classList.remove('hidden');
        }
    }, 5000);
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
                    updatePageIndicator(4); // 滑到最后一页（索引0-4，共5页）
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
const totalItems = 3;
const years = ['2022', '2023', '2025'];
let navItems = [];
let container = null;

// 延迟初始化，确保DOM加载完成
function initTimelineElements() {
    navItems = document.querySelectorAll('.timeline-item');
    container = document.getElementById('timelineContainer');
    
    if (navItems.length > 0 && container) {
        updateTimelineNav();
        updateCardPositions();
        bindNavItemClicks();
    }
}

// 拖拽相关变量 - 优化后的设置
let isDragging = false;
let startX = 0;
let currentX = 0;
let dragThreshold = 15; // 增加阈值，减少误触
let hasTriggered = false;
let dragStartTime = 0; // 记录拖拽开始时间
let isAnimating = false; // 防止动画期间的操作冲突

function updateTimelineNav() {
    // 检查元素是否存在
    if (!container || !navItems || navItems.length === 0) {
        console.warn('Timeline elements not found, skipping updateTimelineNav');
        return;
    }
    
    // 更新导航状态
    navItems.forEach((item, index) => {
        item.classList.toggle('active', index === currentIndex);
    });

    // 计算居中位置
    if (container.parentElement) {
        const containerWidth = container.parentElement.offsetWidth;
        const itemWidth = 120;
        const centerOffset = containerWidth / 2 - itemWidth / 2;
        const translateX = centerOffset - (currentIndex * itemWidth);
        
        container.style.transform = `translateX(${translateX}px)`;
    }
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
    if (isAnimating) return; // 防止动画期间重复触发
    
    isAnimating = true;
    
    if (direction === 'next') {
        currentIndex = (currentIndex + 1) % totalItems;
    } else {
        currentIndex = (currentIndex - 1 + totalItems) % totalItems;
    }
    
    showTimelineItem(years[currentIndex]);
    
    // 动画完成后重置标志
    setTimeout(() => {
        isAnimating = false;
    }, 300); // 假设动画时长为300ms
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
    updateTimelineNav();
    updateCardPositions();
    currentIndex = years.indexOf(year);
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

// 导航项点击 - 延迟绑定
function bindNavItemClicks() {
    if (navItems && navItems.length > 0) {
        navItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                if (!isDragging && !isAnimating) {
                    currentIndex = index;
                    showTimelineItem(years[currentIndex]);
                }
            });
        });
    }
}

// 优化的点击处理 - 添加延迟避免与拖拽冲突
document.addEventListener('click', (e) => {
    if (isDragging || hasTriggered || isAnimating) return;
    
    const card = e.target.closest('.timeline-content-item');
    if (card && !card.classList.contains('active')) {
        // 添加小延迟确保不是拖拽操作
        clickTimeout = setTimeout(() => {
            if (!isDragging) {
                const year = card.getAttribute('data-year');
                selectCard(year);
            }
        }, 10);
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

// 初始化 - 延迟到DOM加载完成
document.addEventListener('DOMContentLoaded', function() {
    initTimelineElements();
});

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
  function goToLocation() {
    const map = document.getElementById('custom-map');

    // ⚠️ 这里请替换成你 My Maps 中标记具体地点的链接（可以在地图中点击目标点 → 分享 → 嵌入地图 获取新的 URL）
    map.src = "https://www.google.com/maps/d/embed?mid=1WGUSQUviVSNKcc7LNK-aSDA6j6S3EMc&ehbc=2E312F#target-location";
  }
</script>
<script>
        // 我们的足迹 - 照片轮播
        <?php
        // 使用 media_config.php 中的 getCompanyPhotos() 函数获取照片
        $photos = getCompanyPhotos();
        
        // 如果 getCompanyPhotos() 返回的照片数量不够，从 comphoto/comphoto/ 目录补充
        if (count($photos) < 30) { // 最多获取30张照片
            $comphotoDir = '../comphoto/comphoto/';
            if (is_dir($comphotoDir)) {
                $files = glob($comphotoDir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
                if ($files) {
                    // 按文件名排序
                    sort($files);
                    $addedCount = 0;
                    foreach ($files as $file) {
                        // 限制最多30张照片
                        if (count($photos) >= 30) {
                            break;
                        }
                        
                        $photoPath = '/' . str_replace('../', '', $file);
                        // 避免重复添加
                        if (!in_array($photoPath, $photos)) {
                            $photos[] = $photoPath;
                            $addedCount++;
                        }
                    }
                    echo "// 从 comphoto/comphoto/ 目录补充了 " . $addedCount . " 张照片\n";
                }
            }
        }
        
        // 强制限制最多30张照片
        if (count($photos) > 30) {
            $photos = array_slice($photos, 0, 30);
            echo "// 照片数量超过30张，已截取前30张\n";
        }
        
        echo "// 最终照片总数: " . count($photos) . " 张（上限30张）\n";
       
        echo "const comphotoImages = " . json_encode($photos) . ";";
        
        ?>

        // 照片数据存储
        const photoData = [];
        let currentClickedImg = null;
        let animationId = null;
        let isPaused = false;

        // 物理参数（响应式）
        const getPhotoWidth = () => Math.min(120, Math.max(60, window.innerWidth * 0.08));
        const getPhotoHeight = () => Math.min(80, Math.max(40, window.innerWidth * 0.0533));
        const PHOTO_WIDTH = getPhotoWidth();
        const PHOTO_HEIGHT = getPhotoHeight();
        const NAVBAR_HEIGHT = 80;
        const PHOTO_MARGIN = 10;

        // 存储已占用的位置
        const occupiedPositions = [];

        // 生成合适的斜线角度 - 确保明显的斜线运动
        function generateDiagonalAngle() {
            // 定义允许的角度范围，避免接近水平和垂直
            const minAngle = Math.PI / 6;  // 30度
            const maxAngle = Math.PI / 3;  // 60度
            
            // 随机选择四个象限之一
            const quadrant = Math.floor(Math.random() * 4);
            let baseAngle;
            
            switch(quadrant) {
                case 0: // 第一象限 (右上)
                    baseAngle = Math.random() * (maxAngle - minAngle) + minAngle;
                    break;
                case 1: // 第二象限 (左上)
                    baseAngle = Math.PI - (Math.random() * (maxAngle - minAngle) + minAngle);
                    break;
                case 2: // 第三象限 (左下)
                    baseAngle = Math.PI + (Math.random() * (maxAngle - minAngle) + minAngle);
                    break;
                case 3: // 第四象限 (右下)
                    baseAngle = 2 * Math.PI - (Math.random() * (maxAngle - minAngle) + minAngle);
                    break;
            }
            
            return baseAngle;
        }

        // 根据角度生成速度向量
        function generateVelocityFromAngle(angle) {
            const speed = 0.6;
            return {
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed
            };
        }

        // 修正反弹后的角度，确保保持斜线运动
        function correctBounceAngle(vx, vy, isHorizontalBounce) {
            let newVx = vx, newVy = vy;
            
            if (isHorizontalBounce) {
                newVx = -vx; // 水平反弹
            } else {
                newVy = -vy; // 垂直反弹
            }
            
            // 计算当前角度
            let currentAngle = Math.atan2(newVy, newVx);
            if (currentAngle < 0) currentAngle += 2 * Math.PI;
            
            // 检查角度是否太接近水平或垂直方向
            const tolerance = Math.PI / 8; // 22.5度的容差
            const horizontalAngles = [0, Math.PI, 2 * Math.PI];
            const verticalAngles = [Math.PI / 2, 3 * Math.PI / 2];
            
            let needsCorrection = false;
            
            // 检查是否太接近水平方向
            for (let hAngle of horizontalAngles) {
                if (Math.abs(currentAngle - hAngle) < tolerance) {
                    needsCorrection = true;
                    break;
                }
            }
            
            // 检查是否太接近垂直方向
            if (!needsCorrection) {
                for (let vAngle of verticalAngles) {
                    if (Math.abs(currentAngle - vAngle) < tolerance) {
                        needsCorrection = true;
                        break;
                    }
                }
            }
            
            if (needsCorrection) {
                // 重新生成一个合适的斜线角度
                const newAngle = generateDiagonalAngle();
                const velocity = generateVelocityFromAngle(newAngle);
                return { vx: velocity.vx, vy: velocity.vy };
            }
            
            return { vx: newVx, vy: newVy };
        }

        // 检查两个矩形是否重叠（响应式）
        function isOverlapping(pos1, pos2, width, height, margin) {
            const photoWidth = getPhotoWidth();
            const photoHeight = getPhotoHeight();
            return !(pos1.x + photoWidth + margin < pos2.x || 
                    pos2.x + photoWidth + margin < pos1.x || 
                    pos1.y + photoHeight + margin < pos2.y || 
                    pos2.y + photoHeight + margin < pos1.y);
        }

        // 生成不重叠的随机位置
        function getRandomNonOverlappingPosition() {
            const boundaries = getBoundaries();
            let attempts = 0;
            const maxAttempts = 200;
            
            while (attempts < maxAttempts) {
                const x = Math.random() * (boundaries.right - boundaries.left) + boundaries.left;
                const y = Math.random() * (boundaries.bottom - boundaries.top) + boundaries.top;
                
                const newPos = { x, y };
                
                let overlaps = false;
                for (let occupiedPos of occupiedPositions) {
                    if (isOverlapping(newPos, occupiedPos, PHOTO_WIDTH, PHOTO_HEIGHT, PHOTO_MARGIN)) {
                        overlaps = true;
                        break;
                    }
                }
                
                if (!overlaps) {
                    occupiedPositions.push(newPos);
                    return newPos;
                }
                
                attempts++;
            }
            
            // 备选网格布局（响应式）
            const photoWidth = getPhotoWidth();
            const photoHeight = getPhotoHeight();
            const cols = Math.floor((boundaries.right - boundaries.left) / (photoWidth + PHOTO_MARGIN));
            const index = occupiedPositions.length;
            const col = index % cols;
            const row = Math.floor(index / cols);
            
            const fallbackPos = {
                x: boundaries.left + col * (photoWidth + PHOTO_MARGIN),
                y: boundaries.top + row * (photoHeight + PHOTO_MARGIN)
            };
            
            occupiedPositions.push(fallbackPos);
            return fallbackPos;
        }

        // 获取边界（响应式）
        function getBoundaries() {
            const photoWidth = getPhotoWidth();
            const photoHeight = getPhotoHeight();
            return {
                left: 0,
                right: window.innerWidth - photoWidth,
                top: NAVBAR_HEIGHT,
                bottom: window.innerHeight - photoHeight
            };
        }

        // 创建照片元素和数据
        function createComphoto(src, index) {
            const img = document.createElement('img');
            img.src = src;
            img.className = 'comphoto';
            img.loading = 'lazy';
            
            const pos = getRandomNonOverlappingPosition();
            
            // 生成斜线角度和速度
            const angle = generateDiagonalAngle();
            const velocity = generateVelocityFromAngle(angle);
            
            img.style.left = pos.x + 'px';
            img.style.top = pos.y + 'px';
            
            img.addEventListener('click', function() {
                openComphotoModal(this);
            });
            
            const photoInfo = {
                element: img,
                x: pos.x,
                y: pos.y,
                vx: velocity.vx,
                vy: velocity.vy,
                index: index
            };
            
            photoData.push(photoInfo);
            return img;
        }

        // 更新照片位置
        function updatePhotos() {
            if (isPaused) return;
            
            const boundaries = getBoundaries();
            
            photoData.forEach(photo => {
                photo.x += photo.vx;
                photo.y += photo.vy;
                
                let bounced = false;
                let isHorizontalBounce = false;
                
                // 检查水平边界碰撞
                if (photo.x <= boundaries.left || photo.x >= boundaries.right) {
                    photo.x = Math.max(boundaries.left, Math.min(boundaries.right, photo.x));
                    bounced = true;
                    isHorizontalBounce = true;
                }
                
                // 检查垂直边界碰撞
                if (photo.y <= boundaries.top || photo.y >= boundaries.bottom) {
                    photo.y = Math.max(boundaries.top, Math.min(boundaries.bottom, photo.y));
                    bounced = true;
                    isHorizontalBounce = false;
                }
                
                if (bounced) {
                    // 使用修正后的反弹角度
                    const correctedVelocity = correctBounceAngle(photo.vx, photo.vy, isHorizontalBounce);
                    photo.vx = correctedVelocity.vx;
                    photo.vy = correctedVelocity.vy;
                }
                
                photo.element.style.left = photo.x + 'px';
                photo.element.style.top = photo.y + 'px';
            });
        }

        // 动画循环
        function animate() {
            updatePhotos();
            animationId = requestAnimationFrame(animate);
        }

        // 初始化comphoto照片弹跳效果
        function initComphoto() {
            console.log('开始初始化comphoto照片弹跳效果');
            console.log('PHP照片数据:', comphotoImages);
            
            const container = document.getElementById('comphoto-container');
            if (!container) {
                console.error('找不到comphoto容器');
                return;
            }
            
            // 使用实际照片
            const imagesToUse = comphotoImages || [];
            console.log('使用的照片数量:', imagesToUse.length);
            
            if (imagesToUse.length === 0) {
                console.warn('没有找到任何照片');
                return;
            }
            
            // 清空容器
            container.innerHTML = '<div class="comphoto-title">Our Journey</div>';
            
            // 清空之前的数据
            photoData.length = 0;
            occupiedPositions.length = 0;
            
            // 创建照片
            imagesToUse.forEach((photo, index) => {
                const photoElement = createComphoto(photo, index);
                container.appendChild(photoElement);
            });
            
            // 开始动画
            animate();
            
            console.log('comphoto照片弹跳效果初始化完成');
        }

        // 暂停/恢复动画
        function pauseAnimation() {
            isPaused = true;
        }

        function resumeAnimation() {
            isPaused = false;
        }
        
        // 丝滑打开模态框
        function openComphotoModal(clickedImg) {
            currentClickedImg = clickedImg;
            pauseAnimation();

            const rect = clickedImg.getBoundingClientRect();
            const modal = document.getElementById('comphoto-modal');
            const modalImg = document.getElementById('comphoto-modal-img');
            const modalContent = document.querySelector('.comphoto-modal-content');
            
            if (modal && modalImg && modalContent) {
                modalImg.src = clickedImg.src;
                modal.style.display = 'block';
                
                modalContent.style.left = rect.left + 'px';
                modalContent.style.top = rect.top + 'px';
                modalContent.style.width = rect.width + 'px';
                modalContent.style.height = rect.height + 'px';
                modalContent.style.borderRadius = '8px';
                
                document.body.style.overflow = 'hidden';
                clickedImg.classList.add('comphoto-hidden');
                modalContent.offsetHeight;
                
                requestAnimationFrame(() => {
                    modal.classList.add('show');
                    
                    const scaleMultiplier = 8;
                    const centerX = window.innerWidth / 2;
                    const centerY = window.innerHeight / 2;
                    
                    modalContent.style.transition = 'all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                    modalContent.style.left = centerX - (rect.width * scaleMultiplier) / 2 + 'px';
                    modalContent.style.top = centerY - (rect.height * scaleMultiplier) / 2 + 'px';
                    modalContent.style.width = rect.width * scaleMultiplier + 'px';
                    modalContent.style.height = rect.height * scaleMultiplier + 'px';
                    modalContent.style.borderRadius = '12px';
                });
            }
        }

        // 关闭照片模态框
        function closeComphotoModal() {
            const modal = document.getElementById('comphoto-modal');
            const modalContent = document.querySelector('.comphoto-modal-content');
            
            if (modal && modalContent) {
                modal.classList.remove('show');
                
                if (currentClickedImg) {
                    const rect = currentClickedImg.getBoundingClientRect();
                    modalContent.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                    modalContent.style.left = rect.left + 'px';
                    modalContent.style.top = rect.top + 'px';
                    modalContent.style.width = rect.width + 'px';
                    modalContent.style.height = rect.height + 'px';
                    modalContent.style.borderRadius = '8px';
                    
                    setTimeout(() => {
                        modal.style.display = 'none';
                        currentClickedImg.classList.remove('comphoto-hidden');
                        currentClickedImg = null;
                        document.body.style.overflow = '';
                        resumeAnimation();
                    }, 400);
                } else {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                    resumeAnimation();
                }
            }
        }

        // 在 DOM 加载完成后初始化
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initComphoto);
        } else {
            // DOM已经加载完成
            initComphoto();
        }

        // 添加模态框关闭事件
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.querySelector('.comphoto-close');
            const modal = document.getElementById('comphoto-modal');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', closeComphotoModal);
            }
            
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeComphotoModal();
                    }
                });
            }
        });
    </script>
    <script>
        // 粒子动画初始化
function initParticles() {
    const particles = document.getElementById('particles');
    
    // 检查particles元素是否存在
    if (!particles) {
        console.warn('Particles element not found, skipping particle animation');
        return;
    }
    
    const particleCount = 50;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.width = Math.random() * 4 + 2 + 'px';
        particle.style.height = particle.style.width;
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
        particles.appendChild(particle);
    }
}

// 存储职位数据的全局变量
let jobsData = {};

// 从服务器获取职位数据
async function loadJobsData() {
    try {
        console.log('开始加载职位数据...'); // 调试信息
        console.log('正在请求: get_jobs_api.php');
        
        // 尝试多个可能的路径
        const possiblePaths = [
            'get_jobs_api.php',
            './get_jobs_api.php',
            '/get_jobs_api.php',
            'job_positions_api.php'  // 备用API文件
        ];
        
        let response = null;
        let lastError = null;
        
        for (const path of possiblePaths) {
            try {
                console.log(`尝试路径: ${path}`);
                response = await fetch(`${path}?lang=en`);
                console.log(`路径 ${path} 响应状态:`, response.status);
                
                if (response.ok) {
                    console.log(`成功使用路径: ${path}`);
                    break;
                }
            } catch (error) {
                console.log(`路径 ${path} 失败:`, error.message);
                lastError = error;
            }
        }
        
        if (!response || !response.ok) {
            throw new Error(`所有API路径都失败，最后错误: ${lastError?.message || '未知错误'}`);
        }
        const data = await response.json();
        console.log('服务器返回的数据:', data); // 调试信息
        
        if (data.success && data.companies) {
            // 将职位数据存储到全局变量中
            jobsData = {};
            
            Object.values(data.companies).forEach(company => {
                company.jobs.forEach(job => {
                    jobsData[job.id] = {
                        title: job.title,
                        count: job.count,
                        experience: job.experience,
                        publish_date: job.publish_date,
                        company: company.name,
                        description: job.description,
                        address: job.address || '待定',
                        department: job.department || '',
                        salary: job.salary || ''
                    };
                });
            });
            
            console.log('职位数据加载完成:', jobsData); // 调试信息
        } else {
            console.error('服务器返回失败:', data.error); // 调试信息
            // 显示错误信息给用户
            showJobLoadError();
        }
    } catch (error) {
        console.error('加载职位数据失败:', error);
        // 显示错误信息给用户
        showJobLoadError();
    }
}

// 显示职位加载错误信息
function showJobLoadError() {
    const jobsGrid = document.querySelector('.jobs-grid');
    if (jobsGrid) {
        jobsGrid.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                <h3>职位数据加载失败</h3>
                <p>请稍后刷新页面重试，或联系管理员检查后台职位配置。</p>
                <button onclick="location.reload()" style="
                    background: linear-gradient(135deg, #FF5C00 0%, #ff7a33 100%);
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 25px;
                    cursor: pointer;
                    margin-top: 10px;
                ">刷新页面</button>
            </div>
        `;
    }
}

// 获取职位数据的函数
function getJobData(jobId) {
    return jobsData[jobId] || null;
}

// 打开职位详情弹窗
function openJobDetail(jobId) {
    console.log('尝试打开职位详情:', jobId); // 调试信息
    const jobData = getJobData(jobId);
    console.log('职位数据:', jobData); // 调试信息
    
    if (!jobData) {
        console.log('未找到职位数据，使用默认数据'); // 调试信息
        // 使用默认数据作为后备
        const defaultData = {
            title: 'Position Details',
            count: '1',
            experience: '1',
            publish_date: '2025-01-01',
            company: 'KUNZZHOLDINGS',
            description: '',
            address: ''
        };
        
        document.getElementById('jobDetailTitle').textContent = defaultData.title;
        document.getElementById('jobDetailCount').textContent = defaultData.count;
        document.getElementById('jobDetailExperience').textContent = defaultData.experience;
        document.getElementById('jobDetailPublishDate').textContent = defaultData.publish_date;
        document.getElementById('jobDetailCompany').textContent = defaultData.company;
        document.getElementById('jobDetailDescription').textContent = defaultData.description;
        document.getElementById('jobDetailAddress').textContent = defaultData.address;
        
        // 隐藏部门和薪资信息
        document.getElementById('jobDetailDepartment').style.display = 'none';
        document.getElementById('jobDetailSalary').style.display = 'none';
    } else {
        // 填充弹窗数据
        document.getElementById('jobDetailTitle').textContent = jobData.title;
        document.getElementById('jobDetailCount').textContent = jobData.count;
        document.getElementById('jobDetailExperience').textContent = jobData.experience;
        document.getElementById('jobDetailPublishDate').textContent = jobData.publish_date;
        document.getElementById('jobDetailCompany').textContent = jobData.company;
        document.getElementById('jobDetailDescription').textContent = jobData.description;
        document.getElementById('jobDetailAddress').textContent = jobData.address;
        
        // 显示部门和薪资信息（如果有的话）
        if (jobData.department) {
            document.getElementById('jobDetailDepartmentValue').textContent = jobData.department;
            document.getElementById('jobDetailDepartment').style.display = 'flex';
        } else {
            document.getElementById('jobDetailDepartment').style.display = 'none';
        }
        
        if (jobData.salary) {
            document.getElementById('jobDetailSalaryValue').textContent = jobData.salary;
            document.getElementById('jobDetailSalary').style.display = 'flex';
        } else {
            document.getElementById('jobDetailSalary').style.display = 'none';
        }
    }
    
    // 显示弹窗
    document.getElementById('jobDetailModal').style.display = 'flex';
}

// 关闭职位详情弹窗
function closeJobDetail() {
    document.getElementById('jobDetailModal').style.display = 'none';
}

// 从详情弹窗打开申请表单
function openFormFromDetail() {
    const jobTitle = document.getElementById('jobDetailTitle').textContent;
    closeJobDetail();
    openForm(jobTitle);
}

function openForm(position) {
    document.getElementById('formPosition').value = position;
    document.getElementById('formModal').style.display = 'flex';
}

function closeForm() {
    document.getElementById('formModal').style.display = 'none';
}

// 点击弹窗外部关闭
window.onclick = function(event) {
    const formModal = document.getElementById('formModal');
    const jobDetailModal = document.getElementById('jobDetailModal');
    
    if (event.target == formModal) {
        formModal.style.display = 'none';
    }
    
    if (event.target == jobDetailModal) {
        jobDetailModal.style.display = 'none';
    }
}

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    initParticles();
    
    // 加载职位数据
    loadJobsData();
    
    // 初始化职位点击功能
    initJobClickHandlers();
});

// 职位点击功能
function initJobClickHandlers() {
    // 使用事件委托来处理动态添加的职位卡片点击事件
    document.addEventListener('click', function(event) {
        const jobItem = event.target.closest('.job-item');
        if (jobItem) {
            const jobId = jobItem.getAttribute('data-job-id');
            if (jobId) {
                console.log('点击了职位:', jobId);
                openJobDetail(jobId);
            }
        }
    });
}
    </script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".job-card");
  cards.forEach(card => {
    card.addEventListener("transitionend", (e) => {
      // 只在transform或opacity动画结束后添加 interactive 类
      if (e.propertyName === "transform" || e.propertyName === "opacity") {
        card.classList.add("interactive");
      }
    }, { once: true }); // once 确保只触发一次
  });
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
function goToBenefits() {
  if (typeof swiper !== 'undefined') {
    swiper.slideTo(0);
  }
}

function goToComphoto() {
  if (typeof swiper !== 'undefined') {
    swiper.slideTo(1);
  }
}

function goToJob() {
  if (typeof swiper !== 'undefined') {
    swiper.slideTo(2);
  }
}

function goToMap() {
  if (typeof swiper !== 'undefined') {
    swiper.slideTo(4); // 跳转到第3个slide（公司文化）
  }
}

function resizeJobs() {
  const baseWidth = 1440;  // 设计稿宽度
  const baseHeight = 900;  // 设计稿高度
  const scaleX = window.innerWidth / baseWidth;
  const scaleY = window.innerHeight / baseHeight;
  const scale = Math.min(scaleX, scaleY);
  document.documentElement.style.setProperty("--scale", scale);
}
window.addEventListener("resize", resizeJobs);
resizeJobs();

</script>

<script>
// Footer 相关功能
function scrollToTop() {
    if (typeof swiper !== 'undefined') {
        swiper.slideTo(0);
    }
}

// 显示/隐藏回到顶部按钮
function toggleBackToTopButton() {
    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
        if (typeof swiper !== 'undefined') {
            // 当在最后一页时显示按钮
            if (swiper.activeIndex === swiper.slides.length - 1) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        }
    }
}

// 监听 Swiper 变化
if (typeof swiper !== 'undefined') {
    swiper.on('slideChange', function() {
        toggleBackToTopButton();
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    toggleBackToTopButton();
});
</script>

<!-- 背景音乐 -->
<?php echo getBgMusicHtml(); ?>
</body>
</html>