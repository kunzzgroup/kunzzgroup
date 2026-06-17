// Header相关的JavaScript功能
document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const loginBtn = document.getElementById('loginBtn');
    const loginDropdownMenu = document.getElementById('loginDropdownMenu');
    const languageBtn = document.getElementById('languageBtn');
    const languageDropdownMenu = document.getElementById('languageDropdownMenu');
    const navBrandsDropdown = document.querySelector('.header-nav-item.header-nav-dropdown');
    const navBrandsDropdownMenu = document.getElementById('brandsNavDropdownMenu');

    function isMobileNav() {
        return window.innerWidth <= 768;
    }

    function closeMobileBrandsMenu() {
        if (!navBrandsDropdownMenu || !navBrandsDropdown) return;
        navBrandsDropdownMenu.classList.remove('show');
        navBrandsDropdown.classList.remove('is-open');
        const trig = navBrandsDropdown.querySelector('.header-nav-dropdown-trigger');
        if (trig) trig.setAttribute('aria-expanded', 'false');
    }

    function closeMobileLoginMenu() {
        if (!loginDropdownMenu || !loginBtn) return;
        loginDropdownMenu.classList.remove('show');
        loginBtn.classList.remove('active');
        loginBtn.setAttribute('aria-expanded', 'false');
        const wrap = loginBtn.closest('.header-login-dropdown');
        if (wrap) wrap.classList.remove('is-open');
    }

    function closeAllNavSubmenus() {
        closeMobileBrandsMenu();
        closeMobileLoginMenu();
    }

    function setMobileMenuOpen(isOpen) {
        if (!navMenu) return;
        navMenu.classList.toggle('active', isOpen);
        document.body.classList.toggle('header-mobile-menu-open', isOpen);
        document.documentElement.classList.toggle('header-mobile-menu-open', isOpen);
        if (hamburger) {
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
        if (!isOpen) {
            closeAllNavSubmenus();
        }
    }

    /** 小屏下将整块登录下拉移入导航抽屉，避免按钮与下拉分离 */
    function moveMobileNavBlocks() {
        const rightSection = document.querySelector('.header-right-section');
        const loginWrap = document.querySelector('.header-login-dropdown');
        if (!navMenu || !loginWrap || !rightSection) return;
        if (window.innerWidth <= 768) {
            if (!navMenu.contains(loginWrap)) {
                navMenu.appendChild(loginWrap);
            }
        } else if (!rightSection.contains(loginWrap)) {
            rightSection.insertBefore(loginWrap, rightSection.firstChild);
        }
    }

    if (hamburger && navMenu) {
        hamburger.setAttribute('aria-controls', 'navMenu');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            setMobileMenuOpen(!navMenu.classList.contains('active'));
        });
    }

    if (navMenu) {
        navMenu.querySelectorAll('a[href]').forEach(function (a) {
            a.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    setMobileMenuOpen(false);
                }
            });
        });
    }

    let loginHoverTimeout;

    if (loginBtn && loginDropdownMenu) {
        loginBtn.addEventListener('mouseenter', function () {
            if (isMobileNav() && navMenu && navMenu.contains(loginBtn)) return;
            clearTimeout(loginHoverTimeout);
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        loginBtn.addEventListener('mouseleave', function () {
            if (isMobileNav() && navMenu && navMenu.contains(loginBtn)) return;
            loginHoverTimeout = setTimeout(() => {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            }, 100);
        });

        loginBtn.addEventListener('click', function (e) {
            if (!isMobileNav() || !navMenu || !navMenu.contains(loginBtn)) return;
            e.preventDefault();
            e.stopPropagation();
            closeMobileBrandsMenu();
            const nextOpen = !loginDropdownMenu.classList.contains('show');
            loginDropdownMenu.classList.toggle('show', nextOpen);
            loginBtn.classList.toggle('active', nextOpen);
            loginBtn.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
            const wrap = loginBtn.closest('.header-login-dropdown');
            if (wrap) wrap.classList.toggle('is-open', nextOpen);
        });
    }

    if (loginDropdownMenu && loginBtn) {
        loginDropdownMenu.addEventListener('mouseenter', function () {
            if (isMobileNav() && navMenu && navMenu.contains(loginBtn)) return;
            clearTimeout(loginHoverTimeout);
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        loginDropdownMenu.addEventListener('mouseleave', function () {
            if (isMobileNav() && navMenu && navMenu.contains(loginBtn)) return;
            loginDropdownMenu.classList.remove('show');
            loginBtn.classList.remove('active');
        });

        const loginDropdownItems = document.querySelectorAll('.header-login-dropdown-item');
        loginDropdownItems.forEach(item => {
            item.addEventListener('click', function () {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
                loginBtn.setAttribute('aria-expanded', 'false');
                const wrap = loginBtn.closest('.header-login-dropdown');
                if (wrap) wrap.classList.remove('is-open');
            });
        });
    }

    let languageHoverTimeout;

    if (languageBtn && languageDropdownMenu) {
        languageBtn.addEventListener('mouseenter', function () {
            clearTimeout(languageHoverTimeout);
            languageDropdownMenu.classList.add('show');
            languageBtn.classList.add('active');
        });

        languageBtn.addEventListener('mouseleave', function () {
            languageHoverTimeout = setTimeout(() => {
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            }, 200);
        });
    }

    if (languageDropdownMenu && languageBtn) {
        languageDropdownMenu.addEventListener('mouseenter', function () {
            clearTimeout(languageHoverTimeout);
            languageDropdownMenu.classList.add('show');
            languageBtn.classList.add('active');
        });

        languageDropdownMenu.addEventListener('mouseleave', function () {
            languageDropdownMenu.classList.remove('show');
            languageBtn.classList.remove('active');
        });

        const languageDropdownItems = document.querySelectorAll('.header-language-dropdown-item');
        languageDropdownItems.forEach(item => {
            item.addEventListener('click', function () {
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
                const selectedLang = this.getAttribute('data-lang');
                if (selectedLang === 'en') {
                    languageBtn.textContent = 'English';
                } else if (selectedLang === 'my') {
                    languageBtn.textContent = 'Malay';
                } else {
                    languageBtn.textContent = '中文';
                }
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            });
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            setMobileMenuOpen(false);
            closeAllNavSubmenus();
            if (loginDropdownMenu && loginBtn) {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            }
            if (languageDropdownMenu && languageBtn) {
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768 && navMenu && navMenu.classList.contains('active')) {
            const onBurger = hamburger && hamburger.contains(e.target);
            if (!navMenu.contains(e.target) && !onBurger) {
                setMobileMenuOpen(false);
            }
        }

        if (window.innerWidth <= 768 && navBrandsDropdown && navBrandsDropdownMenu) {
            if (!navBrandsDropdown.contains(e.target)) {
                navBrandsDropdownMenu.classList.remove('show');
                navBrandsDropdown.classList.remove('is-open');
                const trig = navBrandsDropdown.querySelector('.header-nav-dropdown-trigger');
                if (trig) trig.setAttribute('aria-expanded', 'false');
            }
        }

        if (loginBtn && loginDropdownMenu && !loginBtn.contains(e.target) && !loginDropdownMenu.contains(e.target)) {
            loginDropdownMenu.classList.remove('show');
            loginBtn.classList.remove('active');
            loginBtn.setAttribute('aria-expanded', 'false');
            const wrap = loginBtn.closest('.header-login-dropdown');
            if (wrap) wrap.classList.remove('is-open');
        }

        if (languageBtn && languageDropdownMenu && !languageBtn.contains(e.target) && !languageDropdownMenu.contains(e.target)) {
            languageDropdownMenu.classList.remove('show');
            languageBtn.classList.remove('active');
        }
    });

    moveMobileNavBlocks();

    window.addEventListener('resize', function () {
        moveMobileNavBlocks();
        if (window.innerWidth > 768) {
            setMobileMenuOpen(false);
            closeAllNavSubmenus();
        }
    });

    if (navBrandsDropdown && navBrandsDropdownMenu) {
        navBrandsDropdown.addEventListener('mouseenter', function () {
            if (isMobileNav()) return;
            navBrandsDropdownMenu.classList.add('show');
        });

        navBrandsDropdown.addEventListener('mouseleave', function () {
            if (isMobileNav()) return;
            navBrandsDropdownMenu.classList.remove('show');
        });

        const brandsTrigger = navBrandsDropdown.querySelector('.header-nav-dropdown-trigger');
        if (brandsTrigger) {
            brandsTrigger.addEventListener('click', function (e) {
                if (!isMobileNav()) return;
                e.preventDefault();
                e.stopPropagation();
                closeMobileLoginMenu();
                const nextOpen = !navBrandsDropdownMenu.classList.contains('show');
                navBrandsDropdownMenu.classList.toggle('show', nextOpen);
                navBrandsDropdown.classList.toggle('is-open', nextOpen);
                brandsTrigger.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
            });
            brandsTrigger.addEventListener('keydown', function (e) {
                if (!isMobileNav()) return;
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    brandsTrigger.click();
                }
            });
        }
    }
});
