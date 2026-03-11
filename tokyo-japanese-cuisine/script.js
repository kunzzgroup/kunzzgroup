// --- Random Image Spotlight ---
(function () {
    const pool = [
        { src: 'image/sushi-dish-asian-restaurant.jpg', label: '今日特选 · 精致寿司' },
        { src: 'image/vision.png', label: '今日特选 · 匠心料理' },
        { src: 'logo/tokyorestaurant.png', label: '今日特选 · 餐厅风貌' },
        { src: 'grandmenu/menu2.png', label: '今日特选 · 精选菜单' },
        { src: 'sushimenu/menu1.png', label: '今日特选 · 寿司菜单' },
        { src: 'logo/chef.png', label: '今日特选 · 主厨风采' }
    ];

    let current = Math.floor(Math.random() * pool.length);

    function setSpotlight(index) {
        const img = document.getElementById('random-spotlight-img');
        const label = document.getElementById('random-spotlight-label');
        if (!img || !label) return;
        img.style.opacity = '0';
        setTimeout(() => {
            img.src = pool[index].src;
            label.textContent = pool[index].label;
            img.style.transition = 'opacity 0.5s ease';
            img.style.opacity = '1';
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', function () {
        setSpotlight(current);
        // Click main photo to cycle to next image
        const frame = document.getElementById('editorial-main-wrap') ||
            document.querySelector('.random-spotlight-frame');
        if (frame) {
            frame.addEventListener('click', function () {
                current = (current + 1) % pool.length;
                setSpotlight(current);
            });
        }
    });
})();

// --- Swiper Global Declaration (Fix TDZ) ---
let swiper = null;

document.addEventListener('DOMContentLoaded', function () {
    // --- Sushi Menu Tabs Logic ---
    const sushiTabs = document.querySelectorAll('.sushi-menu-tab[data-sushi-category]');
    if (sushiTabs.length > 0) {
        const panels = document.querySelectorAll('.sushi-menu-panel');
        const categoryToPanel = {
            'sashimi': 'sushi-panel-sashimi',
            'sushi': 'sushi-panel-sushi',
            'hosomaki': 'sushi-panel-hosomaki',
            'ippin': 'sushi-panel-ippin',
            'temaki': 'sushi-panel-temaki',
            'special-roll': 'sushi-panel-special-roll',
            'sarada': 'sushi-panel-sarada'
        };

        sushiTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const cat = this.getAttribute('data-sushi-category');
                const panelId = categoryToPanel[cat];
                if (!panelId) return;

                sushiTabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');

                panels.forEach(p => {
                    p.classList.remove('active');
                    p.hidden = true;
                });
                const panel = document.getElementById(panelId);
                if (panel) {
                    panel.classList.add('active');
                    panel.hidden = false;
                }
            });
        });
    }

    // --- Grand Menu Tabs Logic ---
    const grandTabs = document.querySelectorAll('.grand-tab');
    if (grandTabs.length > 0) {
        const grandPanels = document.querySelectorAll('.grand-menu-panel');
        const grandMap = {
            'zensai': 'grand-panel-zensai',
            'wanmono': 'grand-panel-wanmono',
            'agemono': 'grand-panel-agemono',
            'yakimono': 'grand-panel-yakimono',
            'ippin-ryori': 'grand-panel-ippin-ryori',
            'menrui': 'grand-panel-menrui',
            'curry-don': 'grand-panel-curry-don',
            'donburi': 'grand-panel-donburi',
            'set-meal': 'grand-panel-set-meal',
            'dessert': 'grand-panel-dessert',
            'beverage': 'grand-panel-beverage'
        };

        grandTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const cat = this.getAttribute('data-grand-category');
                const panelId = grandMap[cat];
                if (!panelId) return;

                grandTabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');

                grandPanels.forEach(p => {
                    p.classList.remove('active');
                    p.hidden = true;
                });
                const panel = document.getElementById(panelId);
                if (panel) {
                    panel.classList.add('active');
                    panel.hidden = false;
                }
            });
        });
    }

    // --- Zoom Modal Logic ---
    const zoomModal = document.getElementById('zoom-modal');
    if (zoomModal) {
        const zoomModalImage = document.getElementById('zoom-modal-image');
        const zoomCloseBtn = document.querySelector('.zoom-modal-close');
        // Cover both sushi-menu-item images and haidilao card images
        const menuImages = document.querySelectorAll('.sushi-menu-item img, .haidilao-card .img-wrap img');

        function openZoom(imageSrc) {
            zoomModalImage.src = imageSrc;
            zoomModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeZoom() {
            zoomModal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        menuImages.forEach(function (img) {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function () {
                openZoom(this.src);
            });
        });

        if (zoomCloseBtn) {
            zoomCloseBtn.addEventListener('click', closeZoom);
        }

        zoomModal.addEventListener('click', function (e) {
            if (e.target === zoomModal) {
                closeZoom();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !zoomModal.classList.contains('hidden')) {
                closeZoom();
            }
        });
    }

    // --- Scroll Animations (Intersection Observer) ---
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target); // Run animation once
            }
        });
    }, observerOptions);

    const revealElements = document.querySelectorAll('.reveal');
    revealElements.forEach(el => observer.observe(el));

    // --- Optimized Parallax Effect ---
    let ticking = false;
    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                const scrolled = window.scrollY; // more performant than pageYOffset
                const parallaxElements = document.querySelectorAll('.parallax');

                parallaxElements.forEach(el => {
                    const speed = el.getAttribute('data-speed') || 0.5;
                    // Use translate3d for hardware acceleration
                    const yPos = -(scrolled * speed);
                    el.style.transform = `translate3d(0, ${yPos}px, 0)`;
                });
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true }); // Passive listener for better scroll performance

    // --- Sakura Animation (Only on landing page, not on menu pages) ---
    if (!document.body.classList.contains('haidilao-menu-body')) {
        const sakuraContainer = document.createElement('div');
        sakuraContainer.classList.add('sakura-container');
        document.body.appendChild(sakuraContainer);

        const petalImages = [
            'image/petal1.png',
            'image/petal2.png',
            'image/petal3.png',
            'image/petal4.png',
            'image/petal5.png',
            'image/petal6.png',
            'image/petal7.png',
            'image/petal8.png'
        ];

        function createSakura() {
            // Limit max petals to avoid DOM overload
            if (sakuraContainer.childElementCount > 20) return;

            const sakura = document.createElement('img');
            // Randomly select a petal image
            const randomImage = petalImages[Math.floor(Math.random() * petalImages.length)];
            sakura.src = randomImage;
            sakura.classList.add('sakura');
            sakura.style.willChange = 'transform, top, left'; // Hint to browser

            // Randomize starting position and animation properties
            const startLeft = Math.random() * 100;
            const animationDuration = Math.random() * 3 + 6; // Slower: 6-9s
            const animationDelay = Math.random() * 2;
            const size = Math.random() * 15 + 10; // 10-25px

            sakura.style.left = startLeft + 'vw';
            sakura.style.animationDuration = animationDuration + 's';
            sakura.style.animationDelay = animationDelay + 's';
            sakura.style.width = size + 'px';
            sakura.style.height = size + 'px';

            sakuraContainer.appendChild(sakura);

            // Remove after animation to prevent memory leaks
            setTimeout(() => {
                sakura.remove();
            }, (animationDuration + animationDelay) * 1000);
        }

        // Reduce frequency: every 800ms instead of 300ms
        setInterval(createSakura, 800);
    }

    // --- Swipable Hero Carousel Logic ---
    const heroTrack = document.getElementById('hero-slider-track');
    const heroSlides = document.querySelectorAll('.hero-slide');
    const heroDots = document.querySelectorAll('.hero-dot');

    if (heroTrack && heroSlides.length > 0) {
        let currentSlide = 0;
        const totalSlides = heroSlides.length;
        let slideInterval;

        function updateSlider() {
            // Move the track horizontally
            heroTrack.style.transform = `translateX(-${currentSlide * 100}%)`;

            // Update dot indicators
            heroDots.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlider();
        }

        function startAutoSlide() {
            if (slideInterval) clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 5000);
        }

        function resetAutoSlide() {
            startAutoSlide();
        }

        // Dot Click Navigation
        heroDots.forEach(dot => {
            dot.addEventListener('click', function () {
                currentSlide = parseInt(this.getAttribute('data-index'));
                updateSlider();
                resetAutoSlide();
            });
        });

        // --- Touch & Mouse Drag Swap Implementation ---
        let isDragging = false;
        let startPos = 0;
        let diff = 0;

        function getPositionX(e) {
            return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
        }

        function dragStart(e) {
            isDragging = true;
            startPos = getPositionX(e);
            if (slideInterval) clearInterval(slideInterval);

            // Disable animation for 1:1 drag feedback
            heroTrack.style.transition = 'none';
            heroTrack.style.cursor = 'grabbing';
            diff = 0;
        }

        function dragMove(e) {
            if (!isDragging) return;
            const currentPosition = getPositionX(e);
            diff = currentPosition - startPos;

            // Calculate percentage moved relative to screen width
            const diffPercent = (diff / window.innerWidth) * 100;
            const translatePercent = -(currentSlide * 100) + diffPercent;
            heroTrack.style.transform = `translateX(${translatePercent}%)`;
        }

        function dragEnd() {
            if (!isDragging) return;
            isDragging = false;

            // Re-enable snapping animation
            heroTrack.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
            heroTrack.style.cursor = 'grab';

            const swipeThreshold = window.innerWidth * 0.15; // 15% of screen width to register a swipe

            if (diff < -swipeThreshold) {
                // Swiped left
                currentSlide = (currentSlide + 1) % totalSlides;
            } else if (diff > swipeThreshold) {
                // Swiped right
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            }

            updateSlider();
            resetAutoSlide();
        }

        heroTrack.style.cursor = 'grab';

        // Attach events to the entire hero section so text and overlays don't block the swipe
        const heroSection = document.getElementById('hero');
        if (heroSection) {
            heroSection.style.cursor = 'grab';

            // Touch events
            heroSection.addEventListener('touchstart', dragStart, { passive: true });
            heroSection.addEventListener('touchmove', dragMove, { passive: true });

            // Mouse events for desktop testing
            heroSection.addEventListener('mousedown', (e) => {
                // Ignore drag if clicking on a button or link
                if (e.target.closest('a') || e.target.closest('button')) return;
                dragStart(e);
            });
        }

        window.addEventListener('touchend', dragEnd);
        window.addEventListener('mousemove', dragMove);
        window.addEventListener('mouseup', dragEnd);

        // Init auto-slide
        startAutoSlide();
    }

    // --- Haidilao Menu Logic ---
    const haidilaoTopTabs = document.querySelectorAll('.haidilao-top-tab');
    const haidilaoSidebarCategories = document.querySelectorAll('.sidebar-category');
    const haidilaoSidebarTabs = document.querySelectorAll('.haidilao-sidebar-tab');
    const haidilaoContentPanels = document.querySelectorAll('.haidilao-panel');

    if (haidilaoTopTabs.length > 0) {
        haidilaoTopTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Update active top tab
                haidilaoTopTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const mainCategory = this.getAttribute('data-menu');

                // Show corresponding sidebar category
                haidilaoSidebarCategories.forEach(sidebar => {
                    if (sidebar.id === 'sidebar-' + mainCategory) {
                        sidebar.style.display = 'flex';
                        // Auto-click first tab in this sidebar if none is active
                        const activeTab = sidebar.querySelector('.haidilao-sidebar-tab.active');
                        if (!activeTab) {
                            const firstTab = sidebar.querySelector('.haidilao-sidebar-tab');
                            if (firstTab) firstTab.click();
                        } else {
                            // Re-trigger click to ensure panel is shown
                            activeTab.click();
                        }
                    } else {
                        sidebar.style.display = 'none';
                    }
                });
            });
        });

        // Initialize based on URL hash (e.g., menu.php#sushi or menu.php#grand)
        const hash = window.location.hash.replace('#', '');
        let targetTab;

        if (hash) {
            targetTab = document.querySelector(`.haidilao-top-tab[data-menu="${hash}"]`);
        }

        // Fallback to the default active tab if no hash or invalid hash
        if (!targetTab) {
            targetTab = document.querySelector('.haidilao-top-tab.active');
        }

        if (targetTab) {
            // Need a slight delay to ensure DOM is fully ready if transitioning pages
            setTimeout(() => targetTab.click(), 50);
        }

        haidilaoSidebarTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Update active sidebar tab within its category
                const parentCategory = this.closest('.sidebar-category');
                parentCategory.querySelectorAll('.haidilao-sidebar-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const targetPanelId = this.getAttribute('data-target');

                // Show corresponding panel
                haidilaoContentPanels.forEach(panel => {
                    panel.style.display = 'none';
                    panel.classList.remove('active');
                    if (panel.id === targetPanelId) {
                        panel.style.display = 'block';
                        setTimeout(() => panel.classList.add('active'), 10);
                    }
                });
            });
        });
    }

    // --- Minimalist Mobile Sidebar Menu Logic ---
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileSidebarOverlay = document.getElementById('mobile-sidebar-overlay');
    const mobileSidebarClose = document.getElementById('mobile-sidebar-close');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    if (mobileMenuBtn && mobileSidebar && mobileSidebarOverlay && mobileSidebarClose) {
        function openSidebar() {
            mobileSidebar.classList.add('active');
            mobileSidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeSidebar() {
            mobileSidebar.classList.remove('active');
            mobileSidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileMenuBtn.addEventListener('click', openSidebar);
        mobileSidebarClose.addEventListener('click', closeSidebar);
        mobileSidebarOverlay.addEventListener('click', closeSidebar);

        sidebarLinks.forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    }

    // --- Centered Anchor Scroll ---
    // When clicking any internal nav link (#section), scroll so the section
    // is vertically centred in the viewport instead of top-aligned.
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            const hash = this.getAttribute('href');
            if (!hash || hash === '#') return;

            const target = document.querySelector(hash);
            if (!target) return;

            e.preventDefault();
            // Swiper anchor scrolling
            if (target && target.classList.contains('swiper-slide')) {
                const slides = Array.from(document.querySelectorAll('.swiper-slide'));
                const targetIndex = slides.indexOf(target);
                if (targetIndex !== -1) {
                    if (swiper && swiper.enabled) {
                        swiper.slideTo(targetIndex);
                    } else {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            } else {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // =========================================
    // SWIPER INIT (Frontend Reference style)
    // =========================================
    swiper = new Swiper('.swiper', {
        direction: 'vertical',
        mousewheel: {
            enabled: true,
            releaseOnEdges: true,  // Allow scroll to continue to footer after last slide
        },
        speed: 800,
        simulateTouch: false,
        slidesPerView: 1, // Changed from auto to 1 to fix vertical sizing issue
        spaceBetween: 0,
        keyboard: {
            enabled: true,
        },
        observer: true,
        observeParents: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            type: 'bullets',
        },
        breakpoints: {
            // Disable swiper on mobile (max-width: 767px)
            320: {
                enabled: false,
            },
            // Enable on desktop
            768: {
                enabled: true,
            }
        },
        on: {
            init: function () {
                // Hide pagination on initial load if on first slide
                const paginationEl = document.querySelector('.swiper-pagination');
                if (paginationEl) {
                    if (this.activeIndex === 0) {
                        paginationEl.style.opacity = '0';
                        paginationEl.style.visibility = 'hidden';
                    } else {
                        paginationEl.style.opacity = '1';
                        paginationEl.style.visibility = 'visible';
                    }
                }
            },
            slideChange: function () {
                // Toggle pagination visibility based on slide index
                const paginationEl = document.querySelector('.swiper-pagination');
                if (paginationEl) {
                    if (this.activeIndex === 0) {
                        paginationEl.style.opacity = '0';
                        paginationEl.style.visibility = 'hidden';
                    } else {
                        paginationEl.style.opacity = '1';
                        paginationEl.style.visibility = 'visible';
                    }
                }
            }
        }
    });

    // Fallback: Manually destroy/disable swiper if breakpoints fail
    function handleResize() {
        if (window.innerWidth <= 768 && swiper.enabled) {
            swiper.disable();
        } else if (window.innerWidth > 768 && !swiper.enabled) {
            swiper.enable();
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize(); // Check on load

    // =========================================
    // CULTURE CAROUSEL LOGIC
    // =========================================
    (function () {
        'use strict';
        const track = document.getElementById('cc-track');
        if (!track) return;

        const rawDivs = Array.from(track.querySelectorAll('.cc-card'));
        const SLIDES = rawDivs.map(el => ({
            title: el.getAttribute('data-title'),
            desc: el.getAttribute('data-desc'),
            html: el.innerHTML,
            bg: el.style.background
        }));
        rawDivs.forEach(el => el.remove());

        const N = SLIDES.length;
        if (N === 0) return;
        const mod = i => ((i % N) + N) % N;

        const titleEl = document.getElementById('cc-title');
        const descEl = document.getElementById('cc-desc');
        const dotsEl = document.getElementById('cc-dots');
        const outerEl = document.querySelector('.cc-carousel-outer');
        const prevBtn = document.getElementById('cc-prev');
        const nextBtn = document.getElementById('cc-next');

        const cards = SLIDES.map(s => {
            const el = document.createElement('div');
            el.className = 'cc-card';
            if (s.bg) el.style.background = s.bg;
            el.innerHTML = s.html;
            track.appendChild(el);
            return el;
        });

        if (dotsEl) {
            dotsEl.innerHTML = '';
            SLIDES.forEach((_, i) => {
                const d = document.createElement('button');
                d.className = 'cc-dot';
                d.addEventListener('click', () => go(i));
                dotsEl.appendChild(d);
            });
        }
        const dots = dotsEl ? Array.from(dotsEl.querySelectorAll('.cc-dot')) : [];

        const SIDE_S = 0.82;
        const SIDE_O = 0.45;
        const SIDE_F = 0.82;

        let CW, CH, GAP, STEP, TRACK_W, TRACK_H;

        function measure() {
            CW = cards[0].offsetWidth || 340;
            CH = cards[0].offsetHeight || 480;
            GAP = 40; // match CSS --gap
            STEP = CW + GAP;
            TRACK_W = CW * 3 + GAP * 2;
            TRACK_H = CH;

            track.style.width = TRACK_W + 'px';
            track.style.height = CH + 'px';
            if (outerEl) outerEl.style.width = TRACK_W + 'px';

            cards.forEach(c => {
                c.style.width = CW + 'px';
                c.style.height = CH + 'px';
                c.style.top = '0';
            });
        }

        function slotX(slot) { return (slot + 1) * STEP; }

        const state = cards.map(() => ({ x: 0, scale: SIDE_S, opacity: 0, visible: false }));

        function writeCard(i) {
            const st = state[i];
            if (!st.visible) {
                cards[i].style.opacity = '0';
                cards[i].style.transform = `translate3d(-9999px,0,0)`;
                cards[i].style.pointerEvents = 'none';
                cards[i].classList.remove('active');
                return;
            }
            cards[i].style.pointerEvents = 'auto';
            cards[i].style.transform = `translate3d(${st.x}px,0,0) scale(${st.scale})`;
            cards[i].style.opacity = String(st.opacity);
            cards[i].style.filter = `brightness(${st.scale === 1 ? 1 : SIDE_F})`;

            if (st.scale === 1) {
                cards[i].classList.add('active');
            } else {
                cards[i].classList.remove('active');
            }
        }

        const TRANSITION = 'transform .42s cubic-bezier(.25,.46,.45,.94), opacity .42s ease, filter .42s ease, box-shadow .42s ease';
        function enableTransition(i) { cards[i].style.transition = TRANSITION; }
        function disableTransition(i) { cards[i].style.transition = 'none'; }

        let cur = 0;
        let animating = false;

        function layout(instantly) {
            const L = mod(cur - 1);
            const C = cur;
            const R = mod(cur + 1);

            cards.forEach((_, i) => {
                if (instantly) disableTransition(i);
                if (i === L) {
                    state[i] = { x: slotX(-1), scale: SIDE_S, opacity: SIDE_O, visible: true };
                } else if (i === C) {
                    state[i] = { x: slotX(0), scale: 1, opacity: 1, visible: true };
                } else if (i === R) {
                    state[i] = { x: slotX(1), scale: SIDE_S, opacity: SIDE_O, visible: true };
                } else {
                    state[i] = { x: 0, scale: SIDE_S, opacity: 0, visible: false };
                }
                writeCard(i);
            });
        }

        function syncDots(i) { dots.forEach((d, j) => d.classList.toggle('active', j === i)); }

        let textTimer;
        function syncText(i, instant) {
            clearTimeout(textTimer);
            const s = SLIDES[i];
            if (!titleEl || !descEl) return;

            if (instant) {
                titleEl.textContent = s.title;
                descEl.textContent = s.desc;
                titleEl.classList.remove('out');
                descEl.classList.remove('out');
            } else {
                titleEl.classList.add('out');
                descEl.classList.add('out');
                textTimer = setTimeout(() => {
                    titleEl.textContent = s.title;
                    descEl.textContent = s.desc;
                    titleEl.classList.remove('out');
                    descEl.classList.remove('out');
                }, 180);
            }
        }

        let autoPlayTimer = null;
        function startAutoPlay() {
            if (autoPlayTimer) clearInterval(autoPlayTimer);
            autoPlayTimer = setInterval(() => {
                if (!dragging && !animating) {
                    go(mod(cur + 1));
                }
            }, 3000);
        }

        function go(next) {
            if (animating || next === cur) return;
            animating = true;
            startAutoPlay();

            const prev = cur;
            cur = mod(next);

            const fwd = mod(cur - prev);
            const dir = fwd <= N / 2 ? 1 : -1;

            const idxCenter = cur;
            const idxNewSide = mod(cur + dir);
            const idxOldSide = mod(prev - dir);
            const idxOldCenter = prev;

            disableTransition(idxCenter);
            disableTransition(idxNewSide);

            const stageOffX = dir > 0 ? slotX(2) : slotX(-2);

            state[idxCenter] = { x: stageOffX, scale: SIDE_S, opacity: SIDE_O, visible: true };
            state[idxNewSide] = { x: stageOffX, scale: SIDE_S, opacity: SIDE_O, visible: true };
            writeCard(idxCenter);
            writeCard(idxNewSide);

            syncText(cur, false);
            syncDots(cur);

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    enableTransition(idxCenter);
                    enableTransition(idxOldCenter);
                    enableTransition(idxNewSide);
                    enableTransition(idxOldSide);

                    state[idxCenter] = { x: slotX(0), scale: 1, opacity: 1, visible: true };
                    writeCard(idxCenter);

                    state[idxOldCenter] = { x: slotX(-dir), scale: SIDE_S, opacity: SIDE_O, visible: true };
                    writeCard(idxOldCenter);

                    state[idxNewSide] = { x: slotX(dir), scale: SIDE_S, opacity: SIDE_O, visible: true };
                    writeCard(idxNewSide);

                    state[idxOldSide] = { x: slotX(-dir * 2), scale: SIDE_S, opacity: 0, visible: true };
                    writeCard(idxOldSide);

                    const onDone = () => {
                        disableTransition(idxOldSide);
                        state[idxOldSide].visible = false;
                        writeCard(idxOldSide);
                        animating = false;
                    };

                    cards[idxCenter].addEventListener('transitionend', function h(e) {
                        if (e.propertyName !== 'transform') return;
                        cards[idxCenter].removeEventListener('transitionend', h);
                        onDone();
                    });

                    setTimeout(() => {
                        if (animating) onDone();
                    }, 450);
                });
            });
        }

        let dragging = false;
        let ptrX = 0;
        let dragBase = 0;
        let lastDelta = 0;
        let rafId = null;
        let snapX = {};

        function dragFrame() {
            if (!dragging) return;
            const delta = ptrX - dragBase;
            lastDelta = delta;

            const pull = Math.abs(delta);
            const d = pull > STEP
                ? Math.sign(delta) * (STEP + (pull - STEP) * 0.2)
                : delta;

            [mod(cur - 1), cur, mod(cur + 1)].forEach(i => {
                if (snapX[i] === undefined) return;
                const isCenter = (i === cur);
                cards[i].style.transform = `translate3d(${snapX[i] + d}px,0,0) scale(${isCenter ? 1 : SIDE_S})`;
            });

            const thresh = STEP * 0.28;
            if (delta < -thresh) highlightDrag(mod(cur + 1));
            else if (delta > thresh) highlightDrag(mod(cur - 1));
            else highlightDrag(cur);

            rafId = requestAnimationFrame(dragFrame);
        }

        let dragHighlight = -1;
        function highlightDrag(i) {
            if (dragHighlight === i) return;
            if (dragHighlight >= 0) {
                cards[dragHighlight].classList.remove('active');
                cards[dragHighlight].style.filter = `brightness(${SIDE_F})`;
            }
            dragHighlight = i;
            if (i === cur) {
                cards[i].classList.add('active');
            } else {
                cards[i].style.filter = 'brightness(1)';
            }
        }

        function onDown(e) {
            if (animating) return;
            dragging = true;
            lastDelta = 0;
            dragHighlight = -1;
            dragBase = e.clientX ?? e.touches[0].clientX;
            ptrX = dragBase;

            snapX = {};
            [mod(cur - 1), cur, mod(cur + 1)].forEach(i => {
                disableTransition(i);
                snapX[i] = state[i].x;
            });

            track.classList.add('grabbing');
            cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(dragFrame);
            if (autoPlayTimer) clearInterval(autoPlayTimer);
        }

        function onMove(e) {
            if (!dragging) return;
            ptrX = e.clientX ?? (e.touches ? e.touches[0].clientX : 0);
        }

        function onUp(e) {
            if (!dragging) return;
            dragging = false;
            cancelAnimationFrame(rafId);
            track.classList.remove('grabbing');

            [mod(cur - 1), cur, mod(cur + 1)].forEach(i => enableTransition(i));

            const cx = e.clientX ?? (e.changedTouches && e.changedTouches[0].clientX);

            if (Math.abs(lastDelta) < 5) {
                const el = document.elementFromPoint(cx, track.getBoundingClientRect().top + CH / 2);
                const card = el && el.closest('.cc-card');
                if (card) {
                    const idx = cards.indexOf(card);
                    if (idx !== -1 && idx !== cur) { go(idx); return; }
                }
                layout(false);
                startAutoPlay();
                return;
            }

            const thresh = STEP * 0.18;
            if (lastDelta < -thresh) go(mod(cur + 1));
            else if (lastDelta > thresh) go(mod(cur - 1));
            else { layout(false); startAutoPlay(); }
        }

        track.addEventListener('mousedown', onDown);
        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onUp);
        track.addEventListener('touchstart', onDown, { passive: true });
        track.addEventListener('touchmove', e => { if (dragging) { e.preventDefault(); onMove(e); } }, { passive: false });
        track.addEventListener('touchend', onUp);

        if (prevBtn) prevBtn.addEventListener('click', () => { if (!animating) go(mod(cur - 1)); });
        if (nextBtn) nextBtn.addEventListener('click', () => { if (!animating) go(mod(cur + 1)); });
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowRight' && !animating) go(mod(cur + 1));
            if (e.key === 'ArrowLeft' && !animating) go(mod(cur - 1));
        });

        if (outerEl) {
            outerEl.addEventListener('mouseenter', () => clearInterval(autoPlayTimer));
            outerEl.addEventListener('mouseleave', startAutoPlay);
        }

        function init() {
            measure();
            layout(true);
            syncDots(cur);
            syncText(cur, true);
            startAutoPlay();
        }

        window.addEventListener('resize', () => { measure(); layout(true); });

        // Ensure DOM layout is ready before measuring
        setTimeout(init, 50);

    })();

    // =========================================
    // ZEN ABOUT SECTION LOGIC
    // =========================================
    // 滚动淡入
    const zenElements = document.querySelectorAll(
        ".zen-title span, .zen-desc, .zen-img"
    );

    const zenObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("zen-active");
            }
        });
    }, { threshold: 0.2 });

    zenElements.forEach(el => zenObserver.observe(el));

    // 图片轻微视差
    window.addEventListener("scroll", function () {
        const scrollY = window.scrollY;
        const topImg = document.querySelector(".zen-img-top");
        const bottomImg = document.querySelector(".zen-img-bottom");

        if (topImg && bottomImg) {
            topImg.style.transform = `translateY(${scrollY * 0.05}px)`;
            bottomImg.style.transform = `translateY(${scrollY * 0.1}px)`;
        }
    });

    // 平滑滚动
    const scrollBtn = document.getElementById("zenScroll");
    if (scrollBtn) {
        scrollBtn.addEventListener("click", function () {
            // Integrate seamlessly with Swiper if it's active
            if (typeof swiper !== 'undefined' && swiper.enabled) {
                swiper.slideNext();
            } else {
                // Native fallback
                window.scrollTo({
                    top: window.innerHeight,
                    behavior: "smooth"
                });
            }
        });
    }
});
