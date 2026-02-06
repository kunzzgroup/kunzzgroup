window.PAGE_NAME = "dashboard";

async function loadView() {
    const r = await fetch(window.BASE_URL + "pages/html/" + window.PAGE_NAME + ".html", {
        credentials: "same-origin",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    });
    document.getElementById("app").innerHTML = await r.text();
    initializeComponents();
}

function initializeComponents() {
    // Re-initialize libraries that depend on DOM
    if (typeof Swiper !== 'undefined') {
        initSwiper();
    }
    initObserver();
    initDelegation();
    initLoadTriggers();
}

function initSwiper() {
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
                        updatePageIndicator(3);
                    } else {
                        updatePageIndicator(this.activeIndex);
                    }
                }, duration + 50);
            }
        }
    });

    updatePageIndicator(0);
}

function updatePageIndicator(activeIndex) {
    const pageDots = document.querySelectorAll('.page-dot');
    pageDots.forEach((dot, index) => {
        dot.classList.toggle('active', index === activeIndex);
    });
}

function initObserver() {
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
    }, { threshold: 0.2 });

    document.querySelectorAll('.animate-on-scroll').forEach(container => {
        container.querySelectorAll('.scale-fade-in').forEach(el => {
            el.style.animationPlayState = 'paused';
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
        });
        observer.observe(container);
    });
}

function initDelegation() {
    document.addEventListener('click', function (e) {
        // Hamburger
        if (e.target.closest('#hamburger')) {
            document.getElementById('navMenu').classList.toggle('active');
        }

        // Dropdowns
        if (e.target.closest('.login-btn')) {
            const menu = document.getElementById('loginDropdownMenu');
            menu.classList.toggle('show');
            e.target.closest('.login-btn').classList.toggle('active');
        }

        if (e.target.closest('#languageBtn')) {
            const menu = document.getElementById('languageDropdownMenu');
            menu.classList.toggle('show');
            e.target.closest('#languageBtn').classList.toggle('active');
        }

        if (e.target.closest('#user-avatar')) {
            document.getElementById('dropdown-menu').classList.toggle('show');
            e.stopPropagation();
        } else {
            const dropdown = document.getElementById('dropdown-menu');
            if (dropdown) dropdown.classList.remove('show');
        }

        // Language items
        const langItem = e.target.closest('.language-dropdown-item');
        if (langItem) {
            const selectedLang = langItem.getAttribute('data-lang');
            document.getElementById('languageBtn').textContent = selectedLang.toUpperCase();
            console.log('Switching language to: ' + selectedLang);
        }

        // Slide navigation
        if (e.target.closest('[onclick^="goToSlide"]')) {
            // Since we use delegation, we should ideally use data attributes
            // but for now we handle common triggers
        }
    });

    // Close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.show, .active').forEach(el => {
                el.classList.remove('show', 'active');
            });
        }
    });
}

function initLoadTriggers() {
    // Ported from window.onload logic
    const bgImg = new Image();
    bgImg.src = "../../images/images/封面7.png";
    bgImg.onload = function () {
        document.querySelector('.home')?.classList.add('gradient-loaded');
        document.querySelector('.home-content')?.classList.remove('hidden');
        document.querySelector('.navbar')?.classList.add('navbar-loaded');
        document.querySelector('.social-sidebar')?.classList.add('social-loaded');
        document.querySelector('.page-indicator')?.classList.add('indicator-loaded');
    };
}

// Global functions ported from dashboard.php
window.goToSlide = function (slideIndex) {
    if (window.swiper) window.swiper.slideTo(slideIndex);
};

window.goToCompanyProfile = () => window.goToSlide(1);
window.goToCulture = () => window.goToSlide(2);

window.goToLocation = function () {
    const map = document.getElementById('custom-map');
    if (map) {
        map.src = "https://www.google.com/maps/d/embed?mid=11C1m9L_Gcj_n8ynGotoCNc4rzq0FX54&ehbc=2E312F#target-location";
    }
};

// Initial load
loadView();
