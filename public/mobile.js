/**
 * mobile.js — KUNZZ Holdings 手机端专属 JS
 * 功能：
 * 1. 手机端禁用 Swiper 鼠标滚轮 + touch 捕获
 * 2. 汉堡菜单：滑入侧边栏 + 遮罩层
 * 3. 旗下品牌导航项：tap 展开 accordion
 * 4. 点击菜单外区域关闭菜单
 */

(function () {
    'use strict';

    var isMobile = window.innerWidth <= 768;

    /* ============================================================
       1. 标记 HTML 为手机模式，供 CSS 参考
       ============================================================ */
    if (isMobile) {
        document.documentElement.classList.add('is-mobile');
    }

    window.addEventListener('resize', function () {
        isMobile = window.innerWidth <= 768;
        document.documentElement.classList.toggle('is-mobile', isMobile);
    });

    /* ============================================================
       2. 等待 DOM 完全加载
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {

        /* ---- 汉堡菜单元素 ---- */
        var hamburger = document.getElementById('hamburger');
        var navMenu = document.getElementById('navMenu');

        /* ---- 遮罩层（动态创建） ---- */
        var overlay = document.createElement('div');
        overlay.id = 'mobile-menu-overlay';
        overlay.style.cssText = [
            'display:none',
            'position:fixed',
            'inset:0',
            'background:rgba(0,0,0,0.5)',
            'z-index:9980',
            'backdrop-filter:blur(2px)',
            '-webkit-backdrop-filter:blur(2px)'
        ].join(';');
        document.body.appendChild(overlay);

        /* ---- 菜单开关 ---- */
        function openMenu() {
            if (!navMenu) return;
            navMenu.classList.add('active');
            overlay.style.display = 'block';
            hamburger && (hamburger.textContent = '✕');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            if (!navMenu) return;
            navMenu.classList.remove('active');
            overlay.style.display = 'none';
            hamburger && (hamburger.textContent = '☰');
            document.body.style.overflow = '';
        }

        if (hamburger) {
            hamburger.addEventListener('click', function (e) {
                e.stopPropagation();
                if (navMenu && navMenu.classList.contains('active')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        }

        /* 点遮罩关闭 */
        overlay.addEventListener('click', closeMenu);

        /* ---- 旗下品牌 accordion（手机端 tap 展开）---- */
        var brandDropdown = document.querySelector('.header-nav-item.header-nav-dropdown');
        var brandTrigger = brandDropdown && brandDropdown.querySelector('.header-nav-dropdown-trigger');

        if (brandTrigger) {
            brandTrigger.addEventListener('click', function (e) {
                if (window.innerWidth > 768) return; // 桌面端不干涉
                e.preventDefault();
                e.stopPropagation();
                brandDropdown.classList.toggle('mobile-open');
            });
        }

        /* ---- 点击菜单内链接自动关闭菜单 ---- */
        if (navMenu) {
            navMenu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 768) {
                        closeMenu();
                    }
                });
            });
        }

        /* ============================================================
           3. 手机端注入固定底部 CTA
           ============================================================ */
        if (window.innerWidth <= 768) {
            // 判断是否已经存在 CTA（避免重复注入）
            if (!document.getElementById('mobile-fixed-cta')) {
                var cta = document.createElement('div');
                cta.id = 'mobile-fixed-cta';
                cta.className = 'mobile-fixed-cta';
                cta.innerHTML = [
                    '<a href="joinus.php" class="mobile-cta-join">',
                    '  💼 加入我们',
                    '</a>',
                    '<a href="joinus.php#contact" class="mobile-cta-contact">',
                    '  ✉️ 联系我们',
                    '</a>'
                ].join('');
                document.body.appendChild(cta);
            }
        }

        /* ============================================================
           4. 手机端为所有图片添加 lazy loading
           ============================================================ */
        if (window.innerWidth <= 768) {
            document.querySelectorAll('img:not([loading])').forEach(function (img) {
                // 跳过 logo 等关键图片
                if (img.classList.contains('header-logo') || img.classList.contains('background-image')) return;
                img.setAttribute('loading', 'lazy');
            });
        }

        /* ============================================================
           5. 禁用 Swiper 的 wheel 事件在手机端（仅口触屏）
           ============================================================ */
        if (window.innerWidth <= 768) {
            // 给 swiper container 添加标记，告知 mobile.css 用 !important 覆盖
            document.querySelectorAll('.swiper').forEach(function (el) {
                el.setAttribute('data-mobile-disabled', 'true');
            });

            // 如果有全局 swiper 实例，尝试禁用
            // （各页面的 swiper 变量在 DOMContentLoaded 后可能还未创建，
            //   用 load 事件保底）
            window.addEventListener('load', function () {
                // 查找 Swiper 实例并禁用 mousewheel
                document.querySelectorAll('.swiper').forEach(function (el) {
                    if (el.swiper) {
                        el.swiper.mousewheel && el.swiper.mousewheel.disable && el.swiper.mousewheel.disable();
                        el.swiper.params.simulateTouch = false;
                    }
                });
            });
        }

    }); // end DOMContentLoaded

})();
