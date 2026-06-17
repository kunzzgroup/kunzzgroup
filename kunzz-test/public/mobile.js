/**
 * mobile.js — KUNZZ Holdings 手机端专属 JS
 * 注意：sidebar / hamburger 逻辑已移至 header.js
 * 本文件只负责：
 * 1. 注入底部固定 CTA
 * 2. 图片 lazy loading
 * 3. Swiper 禁用标记
 */

(function () {
    'use strict';

    var isMobile = window.innerWidth <= 768;

    /* ============================================================
       标记 HTML 节点供 CSS 参考
    ============================================================ */
    if (isMobile) {
        document.documentElement.classList.add('is-mobile');
    }

    window.addEventListener('resize', function () {
        isMobile = window.innerWidth <= 768;
        document.documentElement.classList.toggle('is-mobile', isMobile);
    });

    /* ============================================================
       DOMContentLoaded 内执行
    ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {

        /* ---- 注入底部固定 CTA ---- */
        if (window.innerWidth <= 768 && !document.getElementById('mobile-fixed-cta')) {
            var cta = document.createElement('div');
            cta.id = 'mobile-fixed-cta';
            cta.className = 'mobile-fixed-cta';
            cta.innerHTML = [
                '<a href="joinus.php" class="mobile-cta-join">💼 加入我们</a>',
                '<a href="joinus.php#contact" class="mobile-cta-contact">✉️ 联系我们</a>'
            ].join('');
            document.body.appendChild(cta);
        }

        /* ---- 图片 lazy loading ---- */
        if (window.innerWidth <= 768) {
            document.querySelectorAll('img:not([loading])').forEach(function (img) {
                if (img.classList.contains('header-logo') ||
                    img.classList.contains('mobile-sidebar-logo')) return;
                img.setAttribute('loading', 'lazy');
            });
        }

        /* ---- Swiper 禁用标记（CSS 用 !important 覆盖 transform）---- */
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.swiper').forEach(function (el) {
                el.setAttribute('data-mobile-disabled', 'true');
            });

            /* 页面完全加载后，尝试 disable swiper mousewheel */
            window.addEventListener('load', function () {
                document.querySelectorAll('.swiper').forEach(function (el) {
                    if (el.swiper && el.swiper.mousewheel) {
                        try { el.swiper.mousewheel.disable(); } catch (e) {}
                    }
                });
            });
        }

    });

})();
