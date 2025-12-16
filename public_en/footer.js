// Footer相关JavaScript功能

// 返回顶部功能
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

// 智能导航函数：同页面滚动，跨页面跳转
function footerNavigate(anchorEl, targetPage, slideIndex) {
  // 获取当前页面文件名
  const currentPage = window.location.pathname.split('/').pop();

  // 检查是否在同一页面
  if (currentPage === targetPage) {
    // 同一页面：直接滚动到指定slide，不改变URL
    const swiperInstance = typeof swiper !== 'undefined' ? swiper :
                          (window.optimizedSwiper && window.optimizedSwiper.swiper ? window.optimizedSwiper.swiper : null);

    if (swiperInstance && typeof swiperInstance.slideTo === 'function') {
      swiperInstance.slideTo(slideIndex);
      return false; // 阻止默认跳转
    }
  }

  // 不同页面：允许默认链接跳转（URL会带参数）
  return true;
}

// 跳转到指定slide功能（用于footer导航）
// 返回值：true = 允许默认跳转；false = 阻止默认跳转（已用swiper处理）
function goToSlide(slideIndex, anchorEl) {
  if (typeof swiper !== 'undefined' && swiper && typeof swiper.slideTo === 'function') {
    try {
      swiper.slideTo(slideIndex);
      return false; // 使用swiper切换，不进行链接跳转
    } catch (e) {
      // 兜底：如果swiper异常，回退到链接跳转
      if (anchorEl && anchorEl.href) {
        window.location.href = anchorEl.href;
        return false;
      }
      return true;
    }
  }
  // 未加载swiper，允许默认链接跳转
  return true;
}

// 更具体的跳转函数
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

// 返回顶部按钮的显示/隐藏逻辑
document.addEventListener('DOMContentLoaded', function() {
  const backToTopBtn = document.getElementById("backToTop");

  if (backToTopBtn) {
    window.addEventListener("scroll", function () {
      const scrollTop = window.scrollY;

      // 回到顶部按钮显示/隐藏逻辑
      if (scrollTop > 100) {
        backToTopBtn.style.display = "block";
      } else {
        backToTopBtn.style.display = "none";
      }
    });
  }

  // 检查URL参数中是否有slide参数，自动导航到对应的slide
  const urlParams = new URLSearchParams(window.location.search);
  const slideParam = urlParams.get('slide');

  if (slideParam !== null && typeof swiper !== 'undefined') {
    const slideIndex = parseInt(slideParam, 10);
    if (!isNaN(slideIndex)) {
      // 等待swiper完全初始化后再跳转
      setTimeout(function() {
        if (swiper && typeof swiper.slideTo === 'function') {
          swiper.slideTo(slideIndex, 0); // 第二个参数0表示不使用动画效果，立即跳转
        }
      }, 100);
    }
  }
});
