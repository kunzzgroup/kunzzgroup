/**
 * header.js — KUNZZ Holdings
 * 手机端：Tokyo 风格右侧 sidebar + 旗下品牌 accordion
 * 桌面端：hover 下拉菜单（登入、语言、旗下品牌）
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       元素引用
    ============================================================ */
    const hamburger          = document.getElementById('hamburger');
    const sidebar            = document.getElementById('mobileSidebar');
    const sidebarOverlay     = document.getElementById('mobileSidebarOverlay');
    const sidebarClose       = document.getElementById('mobileSidebarClose');
    const brandsTrigger      = document.getElementById('brandsSidebarTrigger');
    const brandsDropdown     = document.getElementById('brandsSidebarDropdown');

    const loginBtn           = document.getElementById('loginBtn');
    const loginDropdownMenu  = document.getElementById('loginDropdownMenu');
    const languageBtn        = document.getElementById('languageBtn');
    const languageDropdownMenu = document.getElementById('languageDropdownMenu');

    /* ============================================================
       手机端 Sidebar 开关
    ============================================================ */
    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('active');
        sidebarOverlay && sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('active');
        sidebarOverlay && sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    /* 汉堡按钮点击 → 打开 sidebar */
    if (hamburger) {
        hamburger.addEventListener('click', function (e) {
            e.stopPropagation();
            openSidebar();
        });
    }

    /* ✕ 按钮关闭 */
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    /* 点遮罩关闭 */
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    /* ESC 关闭 */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
            hideAllDropdowns();
        }
    });

    /* ============================================================
       Sidebar 旗下品牌 accordion
    ============================================================ */
    if (brandsTrigger && brandsDropdown) {
        brandsTrigger.addEventListener('click', function () {
            brandsDropdown.classList.toggle('open');
        });
    }

    /* Sidebar 内链接点击 → 自动关闭 sidebar */
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeSidebar();
            });
        });
    }

    /* ============================================================
       桌面端：登入 hover 下拉
    ============================================================ */
    let loginTimer;

    if (loginBtn && loginDropdownMenu) {
        loginBtn.addEventListener('mouseenter', function () {
            clearTimeout(loginTimer);
            loginDropdownMenu.classList.add('show');
        });
        loginBtn.addEventListener('mouseleave', function () {
            loginTimer = setTimeout(() => loginDropdownMenu.classList.remove('show'), 120);
        });
        loginDropdownMenu.addEventListener('mouseenter', function () {
            clearTimeout(loginTimer);
        });
        loginDropdownMenu.addEventListener('mouseleave', function () {
            loginDropdownMenu.classList.remove('show');
        });

        /* 手机端 tap 支持 */
        loginBtn.addEventListener('click', function (e) {
            if (window.innerWidth > 768) return;
            e.stopPropagation();
            loginDropdownMenu.classList.toggle('show');
        });
    }

    /* ============================================================
       桌面端：语言 hover 下拉
    ============================================================ */
    let langTimer;

    if (languageBtn && languageDropdownMenu) {
        languageBtn.addEventListener('mouseenter', function () {
            clearTimeout(langTimer);
            languageDropdownMenu.classList.add('show');
        });
        languageBtn.addEventListener('mouseleave', function () {
            langTimer = setTimeout(() => languageDropdownMenu.classList.remove('show'), 200);
        });
        languageDropdownMenu.addEventListener('mouseenter', function () {
            clearTimeout(langTimer);
        });
        languageDropdownMenu.addEventListener('mouseleave', function () {
            languageDropdownMenu.classList.remove('show');
        });

        /* 手机端 tap 支持 */
        languageBtn.addEventListener('click', function (e) {
            if (window.innerWidth > 768) return;
            e.stopPropagation();
            languageDropdownMenu.classList.toggle('show');
        });

        /* 语言选择后更新按钮文字 */
        languageDropdownMenu.querySelectorAll('.header-language-dropdown-item').forEach(function (item) {
            item.addEventListener('click', function () {
                const lang = this.getAttribute('data-lang');
                if (languageBtn) {
                    languageBtn.textContent = lang === 'en' ? 'English' : '中文';
                }
                languageDropdownMenu.classList.remove('show');
            });
        });
    }

    /* ============================================================
       桌面端：旗下品牌 hover 下拉
    ============================================================ */
    const navBrandsDropdown = document.querySelector('.header-nav-dropdown');
    const navBrandsMenu     = document.getElementById('brandsNavDropdownMenu');

    if (navBrandsDropdown && navBrandsMenu) {
        navBrandsDropdown.addEventListener('mouseenter', function () {
            navBrandsMenu.classList.add('show');
        });
        navBrandsDropdown.addEventListener('mouseleave', function () {
            navBrandsMenu.classList.remove('show');
        });
    }

    /* ============================================================
       点击页面其他地方关闭所有下拉
    ============================================================ */
    function hideAllDropdowns() {
        loginDropdownMenu  && loginDropdownMenu.classList.remove('show');
        languageDropdownMenu && languageDropdownMenu.classList.remove('show');
        navBrandsMenu      && navBrandsMenu.classList.remove('show');
    }

    document.addEventListener('click', function (e) {
        if (loginBtn && loginDropdownMenu &&
            !loginBtn.contains(e.target) &&
            !loginDropdownMenu.contains(e.target)) {
            loginDropdownMenu.classList.remove('show');
        }
        if (languageBtn && languageDropdownMenu &&
            !languageBtn.contains(e.target) &&
            !languageDropdownMenu.contains(e.target)) {
            languageDropdownMenu.classList.remove('show');
        }
    });
});
