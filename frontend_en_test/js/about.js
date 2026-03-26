document.addEventListener("DOMContentLoaded", function () {
    // ==========================================
    // 1. Navigation & Dropdowns (Hamburger, Login, Language)
    // ==========================================
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const loginBtn = document.querySelector('.login-btn');
    const loginDropdownMenu = document.getElementById('loginDropdownMenu');
    const languageBtn = document.getElementById('languageBtn');
    const languageDropdownMenu = document.getElementById('languageDropdownMenu');

    function moveLoginBtn() {
        if (window.innerWidth <= 768) {
            if (navMenu && !navMenu.contains(loginBtn)) {
                navMenu.appendChild(loginBtn);
            }
        } else {
            const rightSection = document.querySelector('.right-section');
            if (rightSection && !rightSection.contains(loginBtn)) {
                rightSection.insertBefore(loginBtn, rightSection.firstChild);
            }
        }
    }

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // Login Dropdown
    let loginHoverTimeout;
    if (loginBtn && loginDropdownMenu) {
        loginBtn.addEventListener('mouseenter', function () {
            clearTimeout(loginHoverTimeout);
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        loginBtn.addEventListener('mouseleave', function () {
            loginHoverTimeout = setTimeout(() => {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            }, 100);
        });

        loginDropdownMenu.addEventListener('mouseenter', function () {
            clearTimeout(loginHoverTimeout);
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        loginDropdownMenu.addEventListener('mouseleave', function () {
            loginDropdownMenu.classList.remove('show');
            loginBtn.classList.remove('active');
        });
    }

    // Language Dropdown
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

        languageDropdownMenu.addEventListener('mouseenter', function () {
            clearTimeout(languageHoverTimeout);
            languageDropdownMenu.classList.add('show');
            languageBtn.classList.add('active');
        });

        languageDropdownMenu.addEventListener('mouseleave', function () {
            languageDropdownMenu.classList.remove('show');
            languageBtn.classList.remove('active');
        });
    }

    // Global Click Listener for Dropdowns
    document.addEventListener('click', function (e) {
        if (loginBtn && loginDropdownMenu && !loginBtn.contains(e.target) && !loginDropdownMenu.contains(e.target)) {
            loginDropdownMenu.classList.remove('show');
            loginBtn.classList.remove('active');
        }
        if (languageBtn && languageDropdownMenu && !languageBtn.contains(e.target) && !languageDropdownMenu.contains(e.target)) {
            languageDropdownMenu.classList.remove('show');
            languageBtn.classList.remove('active');
        }
    });

    window.addEventListener('resize', moveLoginBtn);
    moveLoginBtn(); // Init

    // Nav Brands Dropdown
    const navBrandsDropdown = document.querySelector('.nav-item.nav-dropdown');
    const navBrandsDropdownMenu = document.getElementById('brandsNavDropdownMenu');
    if (navBrandsDropdown && navBrandsDropdownMenu) {
        navBrandsDropdown.addEventListener('mouseenter', function () {
            navBrandsDropdownMenu.classList.add('show');
        });
        navBrandsDropdown.addEventListener('mouseleave', function () {
            navBrandsDropdownMenu.classList.remove('show');
        });
    }

    // ==========================================
    // 2. Swiper Initialization
    // ==========================================
    const swiper = new Swiper('.swiper', {
        direction: 'vertical',
        mousewheel: true,
        speed: 800,
        simulateTouch: false,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        slidesPerView: 'auto',
        spaceBetween: 0,
        on: {
            slideChange: function () {
                updatePageIndicator(this.activeIndex);
            },
            reachEnd: function () {
                this.allowTouchMove = true;
            },
            setTransition: function (duration) {
                setTimeout(() => {
                    if (this.progress > 0.95) {
                        updatePageIndicator(4);
                    } else {
                        updatePageIndicator(this.activeIndex);
                    }
                }, duration + 50);
            }
        }
    });

    // Make functions available globally for onclick handlers
    window.goToSlide = function (slideIndex) {
        if (swiper) swiper.slideTo(slideIndex);
    };
    window.goToAboutusIntro = () => window.goToSlide(0);
    window.goToVision = () => window.goToSlide(1);
    window.goToValues = () => window.goToSlide(2);
    window.goToTimeline = () => window.goToSlide(3);

    // Page Dots
    const pageDots = document.querySelectorAll('.header-page-dot');
    pageDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            swiper.slideTo(index);
        });
    });

    function updatePageIndicator(activeIndex) {
        pageDots.forEach((dot, index) => {
            if (index === activeIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    updatePageIndicator(0);

    // URL Params for Slide
    const urlParams = new URLSearchParams(window.location.search);
    const slideParam = urlParams.get('slide');
    if (slideParam !== null) {
        const slideIndex = parseInt(slideParam, 10);
        if (!isNaN(slideIndex)) {
            swiper.slideTo(slideIndex, 0);
        }
    }

    // ==========================================
    // 3. Animation Observers (IntersectionObserver)
    // ==========================================
    // General Observer
    const generalObserver = new IntersectionObserver((entries) => {
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
    }, { threshold: 0.2 });

    document.querySelectorAll('.animate-on-scroll').forEach(container => {
        container.querySelectorAll('.scale-fade-in').forEach(el => {
            el.style.animationPlayState = 'paused';
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
        });
        generalObserver.observe(container);
    });

    // About Us Specific Observer
    const aboutObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const target = entry.target;
            if (entry.isIntersecting) {
                if (target.classList.contains('aboutus-banner')) target.classList.add('content-loaded');
                else if (target.classList.contains('aboutus-intro')) target.classList.add('intro-loaded');
            } else {
                if (target.classList.contains('aboutus-banner')) target.classList.remove('content-loaded');
                else if (target.classList.contains('aboutus-intro')) target.classList.remove('intro-loaded');
            }
        });
    }, { threshold: 0.2, rootMargin: '0px 0px -10% 0px' });

    const aboutBanner = document.querySelector('.aboutus-banner');
    const aboutIntro = document.querySelector('.aboutus-intro');
    if (aboutBanner) aboutObserver.observe(aboutBanner);
    if (aboutIntro) aboutObserver.observe(aboutIntro);

    // ==========================================
    // 4. Timeline Logic (Carousel)
    // ==========================================
    const timelineSection = document.querySelector('.timeline-section');
    if (timelineSection) {

        if (typeof years === 'undefined') {
            console.error("Timeline years data not found. Ensure PHP sets 'years' variable.");
            return;
        }

        let currentIndex = 0;
        let totalItems = years.length;
        const container = document.getElementById('timelineContainer');
        const navItems = document.querySelectorAll('.timeline-item');
        const contentItems = document.querySelectorAll('.timeline-content-item');

        // Drag variables
        let isDragging = false;
        let startX = 0;
        let currentX = 0;
        let dragThreshold = 15;
        let hasTriggered = false;
        let dragStartTime = 0;
        let isAnimating = false;

        // Build year groups from DOM data attributes
        let yearGroups = {};
        contentItems.forEach((item, index) => {
            const year = item.getAttribute('data-year');
            const month = parseInt(item.getAttribute('data-month') || '0', 10);
            if (!yearGroups[year]) yearGroups[year] = [];
            yearGroups[year].push({ index, month });
        });

        // Hide duplicate year nav items
        let seenYears = {};
        navItems.forEach(item => {
            const year = item.getAttribute('data-year');
            if (seenYears[year]) {
                item.classList.add('year-duplicate');
            } else {
                seenYears[year] = true;
            }
        });

        function updateTimelineNav() {
            const allNavItems = document.querySelectorAll('.timeline-item');
            const currentYear = years[currentIndex];

            allNavItems.forEach((item) => {
                item.classList.toggle('active', item.getAttribute('data-year') === currentYear);
            });

            if (container && container.parentElement) {
                const visibleItems = Array.from(allNavItems).filter(item => !item.classList.contains('year-duplicate'));
                const containerWidth = container.parentElement.offsetWidth;
                const itemWidth = 120;
                const activeVisibleIndex = visibleItems.findIndex(item => item.classList.contains('active'));
                const centerPosition = containerWidth / 2;
                const currentItemPosition = (activeVisibleIndex >= 0 ? activeVisibleIndex : 0) * itemWidth + itemWidth / 2;
                const translateX = centerPosition - currentItemPosition;

                container.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                container.style.transform = `translateX(${translateX}px)`;
                setTimeout(() => { container.style.transition = ''; }, 400);
            }

            updateMonthSidebar();
        }

        function updateCardPositions() {
            contentItems.forEach((card, index) => {
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

        window.navigateTimeline = function (direction) {
            if (isAnimating) return;
            isAnimating = true;
            if (direction === 'next') {
                currentIndex = (currentIndex + 1) % totalItems;
            } else {
                currentIndex = (currentIndex - 1 + totalItems) % totalItems;
            }
            updateTimelineNav();
            updateCardPositions();
            setTimeout(() => { isAnimating = false; }, 400);
        };

        window.selectCardIndex = function (index) {
            if (isAnimating) return;
            if (index < 0 || index >= totalItems) return;
            currentIndex = index;
            updateTimelineNav();
            updateCardPositions();
        };

        // Month sidebar
        function updateMonthSidebar() {
            const currentYear = years[currentIndex];
            const months = yearGroups[currentYear] || [];
            const sidebar = document.getElementById('monthSidebar');
            if (!sidebar) return;

            sidebar.innerHTML = months.map(m =>
                `<div class="month-item ${m.index === currentIndex ? 'active' : ''}" onclick="selectCardIndex(${m.index})">
                    <div class="month-dot"></div>
                    <span>${m.month}月</span>
                </div>`
            ).join('');
        }

        // Drag handling
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
            if (Math.abs(deltaX) >= dragThreshold && dragTime > 50) {
                hasTriggered = true;
                if (deltaX > 0) navigateTimeline('prev');
                else navigateTimeline('next');
                setTimeout(() => handleDragEnd(e), 50);
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
        }

        // Event listeners
        document.addEventListener('mousedown', (e) => {
            const card = e.target.closest('.timeline-content-item');
            if (card && !isAnimating) handleDragStart(e);
        });
        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);
        document.addEventListener('mouseleave', handleDragEnd);

        document.addEventListener('touchstart', (e) => {
            const card = e.target.closest('.timeline-content-item');
            if (card && !isAnimating) handleDragStart(e);
        }, { passive: false });
        document.addEventListener('touchmove', handleDragMove, { passive: false });
        document.addEventListener('touchend', handleDragEnd);

        // Nav item click (by index)
        navItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                if (!isDragging && !isAnimating) {
                    currentIndex = index;
                    updateTimelineNav();
                    updateCardPositions();
                }
            });
        });

        // Click handling for prev/next cards
        document.addEventListener('click', (e) => {
            if (isDragging || hasTriggered || isAnimating) return;
            const card = e.target.closest('.timeline-content-item');
            if (card) {
                if (card.classList.contains('prev')) {
                    navigateTimeline('prev');
                } else if (card.classList.contains('next')) {
                    navigateTimeline('next');
                } else if (!card.classList.contains('active')) {
                    const idxAttr = card.getAttribute('data-index');
                    const idx = parseInt(idxAttr, 10);
                    if (!isNaN(idx)) selectCardIndex(idx);
                }
            }
        });

        // Keyboard
        document.addEventListener('keydown', (e) => {
            if (!isAnimating) {
                if (e.key === 'ArrowLeft') navigateTimeline('prev');
                else if (e.key === 'ArrowRight') navigateTimeline('next');
            }
        });

        // Timeline entry animation observer
        const timelineObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const sec = entry.target;
                if (entry.isIntersecting) {
                    sec.classList.add('timeline-active');
                    resetAndStartTimelineAnimation(sec);
                } else {
                    sec.classList.remove('timeline-active');
                    resetTimelineAnimation(sec);
                }
            });
        }, { threshold: 0.3, rootMargin: '0px 0px -20% 0px' });

        timelineObserver.observe(timelineSection);
        resetTimelineAnimation(timelineSection);

        // Init
        updateTimelineNav();
        updateCardPositions();

        window.addEventListener('resize', () => {
            if (!isAnimating) setTimeout(updateTimelineNav, 100);
        });
    }

    function resetAndStartTimelineAnimation(timelineSection) {
        const title = timelineSection.querySelector('h1');
        const track = timelineSection.querySelector('.timeline-track');
        const container = timelineSection.querySelector('.timeline-items-container');
        const items = timelineSection.querySelectorAll('.timeline-item');
        const arrows = timelineSection.querySelectorAll('.nav-arrow');
        [title, track, container, ...items, ...arrows].forEach(el => {
            if (el) {
                el.style.animation = 'none';
                el.offsetHeight;
                el.style.animation = '';
            }
        });
    }

    function resetTimelineAnimation(timelineSection) {
        const title = timelineSection.querySelector('h1');
        const track = timelineSection.querySelector('.timeline-track');
        const container = timelineSection.querySelector('.timeline-items-container');
        const items = timelineSection.querySelectorAll('.timeline-item');
        const arrows = timelineSection.querySelectorAll('.nav-arrow');

        if (title) { title.style.opacity = '0'; title.style.transform = 'translateY(20px)'; }
        if (track) { track.style.transform = 'translateY(-50%) scaleX(0)'; }
        if (container) { container.style.opacity = '0'; }
        items.forEach(item => { item.style.opacity = '0'; item.style.transform = 'scale(0.5) translateY(20px)'; });
        arrows.forEach(arrow => { arrow.style.opacity = '0'; arrow.style.transform = 'translateY(-50%) scale(0.8)'; });
    }

    // ==========================================
    // 5. Background Music Logic
    // ==========================================
    const bgMusic = document.getElementById('bgMusic');
    if (bgMusic) {
        bgMusic.volume = 0.3;
        const savedTime = localStorage.getItem('musicCurrentTime');
        const savedPlaying = localStorage.getItem('musicPlaying');
        const currentPage = window.location.pathname;

        if (savedTime) bgMusic.currentTime = parseFloat(savedTime);

        function tryPlay() {
            bgMusic.play().then(() => {
                localStorage.setItem('musicPlaying', 'true');
                localStorage.setItem('musicPage', currentPage);
            }).catch(error => console.log('音乐播放失败:', error));
        }

        if (savedPlaying === 'true') setTimeout(tryPlay, 100);

        const startEvents = ['click', 'keydown', 'touchstart'];
        const startPlay = () => {
            tryPlay();
            startEvents.forEach(event => document.removeEventListener(event, startPlay));
        };
        startEvents.forEach(event => document.addEventListener(event, startPlay, { once: true }));

        setInterval(() => {
            if (!bgMusic.paused && bgMusic.currentTime > 0) {
                localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
                localStorage.setItem('musicPlaying', 'true');
                localStorage.setItem('musicPage', currentPage);
            }
        }, 1000);

        window.addEventListener('beforeunload', () => {
            if (bgMusic) {
                localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
                localStorage.setItem('musicPlaying', bgMusic.paused ? 'false' : 'true');
                localStorage.setItem('musicPage', currentPage);
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                const shouldPlay = localStorage.getItem('musicPlaying') === 'true';
                if (shouldPlay && bgMusic.paused) tryPlay();
            }
        });
    }
});
