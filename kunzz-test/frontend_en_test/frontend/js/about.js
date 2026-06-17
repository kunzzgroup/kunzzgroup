document.addEventListener('DOMContentLoaded', function () {
    // ==========================================
    // 1. General Animation Observer
    // ==========================================
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

    // ==========================================
    // 2. AboutUs Specific Observer
    // ==========================================
    const aboutObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const target = entry.target;

            if (entry.isIntersecting) {
                if (target.classList.contains('aboutus-banner')) {
                    target.classList.add('content-loaded');
                } else if (target.classList.contains('aboutus-intro')) {
                    target.classList.add('intro-loaded');
                }
            } else {
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

    // ==========================================
    // 3. Timeline Observer & Logic
    // ==========================================
    const timelineObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const timelineSection = entry.target;

            if (entry.isIntersecting) {
                timelineSection.classList.add('timeline-active');
                resetAndStartTimelineAnimation(timelineSection);
            } else {
                timelineSection.classList.remove('timeline-active');
                resetTimelineAnimation(timelineSection);
            }
        });
    }, {
        threshold: 0.3,
        rootMargin: '0px 0px -20% 0px'
    });

    function resetAndStartTimelineAnimation(timelineSection) {
        const title = timelineSection.querySelector('h1');
        const track = timelineSection.querySelector('.timeline-track');
        const container = timelineSection.querySelector('.timeline-items-container');
        const items = timelineSection.querySelectorAll('.timeline-item');
        const arrows = timelineSection.querySelectorAll('.nav-arrow');

        [title, track, container, ...items, ...arrows].forEach(el => {
            if (el) {
                el.style.animation = 'none';
                el.offsetHeight; // Force reflow
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

        if (title) {
            title.style.opacity = '0';
            title.style.transform = 'translateY(20px)';
        }
        if (track) {
            track.style.transform = 'translateY(-50%) scaleX(0)';
        }
        if (container) {
            container.style.opacity = '0';
        }
        items.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'scale(0.5) translateY(20px)';
        });
        arrows.forEach(arrow => {
            arrow.style.opacity = '0';
            arrow.style.transform = 'translateY(-50%) scale(0.8)';
        });
    }

    // Initialize Observers
    document.querySelectorAll('.animate-on-scroll').forEach(container => {
        container.querySelectorAll('.scale-fade-in').forEach(el => {
            el.style.animationPlayState = 'paused';
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
        });
        observer.observe(container);
    });

    const aboutBanner = document.querySelector('.aboutus-banner');
    const aboutIntro = document.querySelector('.aboutus-intro');
    if (aboutBanner) aboutObserver.observe(aboutBanner);
    if (aboutIntro) aboutObserver.observe(aboutIntro);

    const timelineSection = document.querySelector('.timeline-section');
    if (timelineSection) {
        resetTimelineAnimation(timelineSection);
        timelineObserver.observe(timelineSection);
    }

    // Check visible elements immediately
    setTimeout(() => {
        const banner = document.querySelector('.aboutus-banner');
        const intro = document.querySelector('.aboutus-intro');
        const timeline = document.querySelector('.timeline-section');

        if (banner && isElementInViewport(banner)) banner.classList.add('content-loaded');
        if (intro && isElementInViewport(intro)) intro.classList.add('intro-loaded');
        if (timeline && isElementInViewport(timeline)) {
            timeline.classList.add('timeline-active');
            resetAndStartTimelineAnimation(timeline);
        }
    }, 100);

    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
            rect.bottom > 0 &&
            rect.left < (window.innerWidth || document.documentElement.clientWidth) &&
            rect.right > 0
        );
    }

    // ==========================================
    // 4. Swiper Initialization
    // ==========================================
    window.swiper = new Swiper('.swiper', {
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
                        updatePageIndicator(4); // Last slide index
                    } else {
                        updatePageIndicator(this.activeIndex);
                    }
                }, duration + 50);
            }
        }
    });

    // Page Indicator Logic
    const pageDots = document.querySelectorAll('.header-page-dot');
    pageDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            window.swiper.slideTo(index);
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

    // URL Param Navigation
    const urlParams = new URLSearchParams(window.location.search);
    const slideParam = urlParams.get('slide');
    if (slideParam !== null) {
        const slideIndex = parseInt(slideParam, 10);
        if (!isNaN(slideIndex)) {
            window.swiper.slideTo(slideIndex, 0);
        }
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
            }).catch(error => {
                console.log('Music play failed:', error);
            });
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

        bgMusic.addEventListener('error', (e) => console.error('Music load failed:', e));
        bgMusic.addEventListener('loadeddata', () => console.log('Music loaded'));
    }

    // ==========================================
    // 6. Navigation Dropdown
    // ==========================================
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
    // 7. Scroll Animation Optimization (Staggered)
    // ==========================================
    const animatedElements = document.querySelectorAll(".animate-on-scroll");
    const staggerObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add("visible");
                    }, entry.target.dataset.delay || 0);
                    staggerObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1 }
    );

    animatedElements.forEach((el, index) => {
        el.dataset.delay = index * 50;
        staggerObserver.observe(el);
    });

    // ==========================================
    // 8. Timeline Interaction Logic
    // ==========================================
    window.timelineInteract = function () {
        let currentIndex = 0;
        let years = window.timelineYearsData || []; // Getting data from global variable
        let totalItems = years.length;
        const navItems = document.querySelectorAll('.timeline-item');
        const container = document.getElementById('timelineContainer');

        if (!container) return; // Exit if timeline not present

        // Variables
        let isDragging = false;
        let startX = 0;
        let currentX = 0;
        let dragThreshold = 15;
        let hasTriggered = false;
        let dragStartTime = 0;
        let isAnimating = false;

        function updateTimelineNav() {
            const navItems = document.querySelectorAll('.timeline-item');
            navItems.forEach((item, index) => {
                item.classList.toggle('active', index === currentIndex);
            });

            const containerWidth = container.parentElement.offsetWidth;
            const itemWidth = 120;
            const centerPosition = containerWidth / 2;
            const currentItemPosition = currentIndex * itemWidth + itemWidth / 2;
            const translateX = centerPosition - currentItemPosition;

            container.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            container.style.transform = `translateX(${translateX}px)`;

            setTimeout(() => { container.style.transition = ''; }, 400);
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
        }

        window.selectCardIndex = function (index) {
            if (isAnimating) return;
            if (index < 0 || index >= totalItems) return;
            currentIndex = index;
            updateTimelineNav();
            updateCardPositions();
        }

        // Drag Handlers
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
                if (deltaX > 0) {
                    navigateTimeline('prev');
                } else {
                    navigateTimeline('next');
                }
                setTimeout(() => { handleDragEnd(e); }, 50);
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

        // Event Listeners
        let clickTimeout;
        document.addEventListener('mousedown', (e) => {
            const card = e.target.closest('.timeline-content-item');
            if (card && !isAnimating) {
                if (clickTimeout) clearTimeout(clickTimeout);
                handleDragStart(e);
            }
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

        navItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                if (!isDragging && !isAnimating) {
                    currentIndex = index;
                    updateTimelineNav();
                    updateCardPositions();
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (isDragging || hasTriggered || isAnimating) return;
            const card = e.target.closest('.timeline-content-item');
            if (card) {
                if (card.classList.contains('prev')) {
                    navigateTimeline('prev');
                    return;
                } else if (card.classList.contains('next')) {
                    navigateTimeline('next');
                    return;
                } else if (!card.classList.contains('active')) {
                    const idxAttr = card.getAttribute('data-index');
                    const idx = parseInt(idxAttr, 10);
                    if (!isNaN(idx)) selectCardIndex(idx);
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!isAnimating) {
                if (e.key === 'ArrowLeft') navigateTimeline('prev');
                else if (e.key === 'ArrowRight') navigateTimeline('next');
            }
        });

        document.addEventListener('selectstart', (e) => {
            if (isDragging) e.preventDefault();
        });

        // Init
        updateTimelineNav();
        updateCardPositions();

        window.addEventListener('resize', () => {
            if (!isAnimating) {
                setTimeout(() => { updateTimelineNav(); }, 100);
            }
        });
    };

    // Initialize Timeline Interaction
    window.timelineInteract();
});

// Helper functions globally accessible
window.goToSlide = function (slideIndex) {
    if (typeof window.swiper !== 'undefined') window.swiper.slideTo(slideIndex);
};
window.goToAboutusIntro = function () { window.goToSlide(0); };
window.goToVision = function () { window.goToSlide(1); };
window.goToValues = function () { window.goToSlide(2); };
window.goToTimeline = function () { window.goToSlide(3); };
