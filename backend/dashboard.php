<?php
session_start();

// 超时时间（秒）
define('SESSION_TIMEOUT', 60);

// 如果 session 存在，检查是否过期
if (isset($_SESSION['user_id'])) {

    // 如果超过 1 分钟没活动，并且没有记住我
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) &&
        (!isset($_COOKIE['remember_token']) || $_COOKIE['remember_token'] !== '1')
    ) {
        // 清除 session
        session_unset();
        session_destroy();

        // 清除 cookie（可选）
        setcookie('user_id', '', time() - 60, "/");
        setcookie('username', '', time() - 60, "/");
        setcookie('position', '', time() - 60, "/");
        setcookie('remember_token', '', time() - 60, "/");

        // 跳转登录页
        header("Location: ../frontend/index.php");
        exit();
    }

    // 更新活动时间戳
    $_SESSION['last_activity'] = time();

} elseif (
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    // 记住我逻辑（恢复 session）
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['last_activity'] = time();
} else {
    // 没有 session，也没有有效 cookie
    header("Location: ../frontend/index.php");
    exit();
}

$username = $_SESSION['username'];
// 修改这行：检查position是否为空或null
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';
$avatarLetter = strtoupper($username[0]);
// 添加权限检查 - 检查用户注册码
$canViewAnalytics = true; // 默认可以查看
if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'u857194726_kunzzgroup';
    $dbuser = 'u857194726_kunzzgroup';
    $dbpass = 'Kholdings1688@';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $restrictedCodes = ['SUPPORT88']; // 限制访问的注册码
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userCode = $stmt->fetchColumn();
        
        $canViewAnalytics = !($userCode && in_array($userCode, $restrictedCodes));
    } catch (PDOException $e) {
        $canViewAnalytics = true; // 出错时默认允许访问
    }
}
?>


<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="animation.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>
  
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

// 页面指示器功能
const pageDots = document.querySelectorAll('.page-dot');

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
    </script>
<script>
  window.addEventListener('load', () => {
    // 创建一个虚拟图片对象检测背景图是否加载完成
    const bgImg = new Image();
    bgImg.src = "../images/images/封面7.png";

    bgImg.onload = function () {

      document.querySelector('.home').classList.add('gradient-loaded');
      
      document.querySelector('.home-content').classList.remove('hidden');

      // 强制触发重绘，重新开始动画（可选，增强兼容性）
      void document.querySelector('.home-content').offsetWidth;

      // 添加动画类（如果你的 fade-in-up 是靠 JavaScript 加载）
      document.querySelector('.home-content h1').classList.add('scale-fade-in');
      document.querySelector('.home-content p').classList.add('scale-fade-in');

      // 启动navbar动画 - 添加一个CSS类来触发动画
      document.querySelector('.navbar').classList.add('navbar-loaded');
      
      // 显示社交侧边栏
      document.querySelector('.social-sidebar').classList.add('social-loaded');
      
      // 显示页面指示器
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
  // 添加这个函数到你现有的JavaScript代码中
function goToSlide(slideIndex) {
  if (typeof swiper !== 'undefined') {
    swiper.slideTo(slideIndex);
  }
}

// 或者，如果你想要更具体的跳转函数
function goToCompanyProfile() {
  if (typeof swiper !== 'undefined') {
    swiper.slideTo(1); // 跳转到第2个slide（公司简介）
  }
}

function goToCulture() {
  if (typeof swiper !== 'undefined') {
    swiper.slideTo(2); // 跳转到第3个slide（公司文化）
  }
}
</script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const avatar = document.getElementById("user-avatar");
    const dropdown = document.getElementById("dropdown-menu");

    avatar.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdown.classList.toggle("show");
    });

    document.addEventListener("click", () => {
      dropdown.classList.remove("show");
    });
  });
</script>
<!-- sidebar scripts moved to sidebar.php -->
</body>
</html>
