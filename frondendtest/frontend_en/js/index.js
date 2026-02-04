// Intersection Observer for Animations
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

// Swiper Initialization
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
        slideChange: function () {
            // 更新页面指示器
            updatePageIndicator(this.activeIndex);
        },
        // 添加这个事件来处理最后一页的特殊情况
        reachEnd: function () {
            // 确保最后一页正确显示
            this.allowTouchMove = true;
        },
        // 添加进度监听来处理最后一页的双向滑动
        setTransition: function (duration) {
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

// Background Media Loading and Animation Trigger
window.addEventListener('load', () => {
    const video = document.querySelector('.background-video');
    const bgImage = document.querySelector('.background-image');

    // 触发动画的通用函数
    function triggerAnimations() {
        const home = document.querySelector('.home');
        const homeContent = document.querySelector('.home-content');

        if (home) home.classList.add('gradient-loaded');
        if (homeContent) {
            homeContent.classList.remove('hidden');

            // 强制触发重绘，重新开始动画
            void homeContent.offsetWidth;

            // 添加动画类
            const h1 = homeContent.querySelector('h1');
            const p = homeContent.querySelector('p');
            if (h1) h1.classList.add('scale-fade-in');
            if (p) p.classList.add('scale-fade-in');
        }
    }

    // 处理视频背景
    if (video) {
        // 监听视频是否可以播放（有足够的数据开始播放）
        video.addEventListener('canplay', function () {
            triggerAnimations();
        });
    }

    // 处理图片背景
    if (bgImage) {
        bgImage.addEventListener('load', function () {
            triggerAnimations();
        });

        // 如果图片已经加载完成
        if (bgImage.complete) {
            bgImage.dispatchEvent(new Event('load'));
        }
    }

    // 备用方案：如果视频/图片加载失败或很慢，设置一个最大等待时间
    setTimeout(() => {
        const home = document.querySelector('.home');
        if (home && !home.classList.contains('gradient-loaded')) {
            triggerAnimations();
        }
    }, 500);
});

// Map Function
function goToLocation() {
    const map = document.getElementById('custom-map');
    // ⚠️ 这里请替换成你 My Maps 中标记具体地点的链接（可以在地图中点击目标点 → 分享 → 嵌入地图 获取新的 URL）
    map.src = "https://www.google.com/maps/d/embed?mid=11C1m9L_Gcj_n8ynGotoCNc4rzq0FX54&ehbc=2E312F#target-location";
}

// Background Music Logic
document.addEventListener('DOMContentLoaded', function () {
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
        document.addEventListener(event, startPlay, {
            once: true
        });
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
