window.PAGE_NAME = "dashboard";

async function loadView() {
    try {
        const r = await fetch(window.BASE_URL + "pages/html/" + window.PAGE_NAME + ".html", {
            credentials: "same-origin",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });
        if (!r.ok) throw new Error("Failed to load view");
        document.getElementById("app").innerHTML = await r.text();
        initializeComponents();
    } catch (e) {
        console.error("View load failed:", e);
    }
}

function initializeComponents() {
    initSwiper();
    initAnimations();
    initVideo();
    initPageIndicators();
}

function initSwiper() {
    window.swiper = new Swiper('.swiper', {
        direction: 'vertical',
        mousewheel: true,
        speed: 800,
        simulateTouch: false,
        slidesPerView: 'auto',
        spaceBetween: 0,
        on: {
            slideChange: function () {
                updatePageIndicator(this.activeIndex);
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
}

function initAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const container = entry.target;
            if (entry.isIntersecting) {
                container.classList.add('visible');
                container.querySelectorAll('.animate-on-scroll').forEach(el => {
                    el.style.animationPlayState = 'running';
                });
            } else {
                container.classList.remove('visible');
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('section').forEach(section => observer.observe(section));
}

function initVideo() {
    const video = document.querySelector('.background-video');
    if (video) {
        video.play().catch(e => console.log("Video autoplay blocked", e));
    }
}

function initPageIndicators() {
    const pageDots = document.querySelectorAll('.page-dot');
    pageDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            if (window.swiper) window.swiper.slideTo(index);
        });
    });
}

function updatePageIndicator(activeIndex) {
    const pageDots = document.querySelectorAll('.page-dot');
    pageDots.forEach((dot, index) => {
        if (index === activeIndex) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

window.goToSlide = function (index) {
    if (window.swiper) window.swiper.slideTo(index);
};

// Start application
loadView();
