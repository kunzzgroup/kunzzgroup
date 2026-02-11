// Hamburger menu and navigation
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');
const loginBtn = document.querySelector('.login-btn');

// Login dropdown menu elements
const loginDropdownMenu = document.getElementById('loginDropdownMenu');

// Language switcher dropdown elements
const languageBtn = document.getElementById('languageBtn');
const languageDropdownMenu = document.getElementById('languageDropdownMenu');

function moveLoginBtn() {
    if (window.innerWidth <= 768) {
        if (!navMenu.contains(loginBtn)) {
            navMenu.appendChild(loginBtn);
        }
    } else {
        // If width is greater than 768, ensure loginBtn is in right-section
        const rightSection = document.querySelector('.right-section');
        if (rightSection && !rightSection.contains(loginBtn)) {
            rightSection.insertBefore(loginBtn, rightSection.firstChild);
        }
    }
}

// Toggle menu on hamburger click
hamburger.addEventListener('click', () => {
    navMenu.classList.toggle('active');
});

// ========== Login dropdown menu functionality ==========
let loginHoverTimeout;

// Show dropdown when mouse enters login button area
loginBtn.addEventListener('mouseenter', function () {
    clearTimeout(loginHoverTimeout);
    loginDropdownMenu.classList.add('show');
    loginBtn.classList.add('active');
});

// Delay hiding dropdown when mouse leaves login button area
loginBtn.addEventListener('mouseleave', function () {
    loginHoverTimeout = setTimeout(() => {
        loginDropdownMenu.classList.remove('show');
        loginBtn.classList.remove('active');
    }, 100);
});

// Keep dropdown visible when mouse enters it
loginDropdownMenu.addEventListener('mouseenter', function () {
    clearTimeout(loginHoverTimeout);
    loginDropdownMenu.classList.add('show');
    loginBtn.classList.add('active');
});

// Hide dropdown when mouse leaves it
loginDropdownMenu.addEventListener('mouseleave', function () {
    loginDropdownMenu.classList.remove('show');
    loginBtn.classList.remove('active');
});

// Handle login dropdown item clicks
const loginDropdownItems = document.querySelectorAll('.login-dropdown-item');
loginDropdownItems.forEach(item => {
    item.addEventListener('click', function (e) {
        console.log('Selected login:', this.textContent);
        loginDropdownMenu.classList.remove('show');
        loginBtn.classList.remove('active');
    });
});

// ========== Language switcher dropdown functionality ==========
let languageHoverTimeout;

// Show dropdown when mouse enters language button area
languageBtn.addEventListener('mouseenter', function () {
    clearTimeout(languageHoverTimeout);
    languageDropdownMenu.classList.add('show');
    languageBtn.classList.add('active');
});

// Delay hiding dropdown when mouse leaves language button area
languageBtn.addEventListener('mouseleave', function () {
    languageHoverTimeout = setTimeout(() => {
        languageDropdownMenu.classList.remove('show');
        languageBtn.classList.remove('active');
    }, 200);
});

// Keep dropdown visible when mouse enters it
languageDropdownMenu.addEventListener('mouseenter', function () {
    clearTimeout(languageHoverTimeout);
    languageDropdownMenu.classList.add('show');
    languageBtn.classList.add('active');
});

// Hide dropdown when mouse leaves it
languageDropdownMenu.addEventListener('mouseleave', function () {
    languageDropdownMenu.classList.remove('show');
    languageBtn.classList.remove('active');
});

// Handle language dropdown item clicks
const languageDropdownItems = document.querySelectorAll('.language-dropdown-item');
languageDropdownItems.forEach(item => {
    item.addEventListener('click', function () {
        console.log('Selected language:', this.textContent);
        languageDropdownMenu.classList.remove('show');
        languageBtn.classList.remove('active');

        const selectedLang = this.getAttribute('data-lang');
        if (selectedLang === 'en') {
            languageBtn.textContent = 'EN';
        } else {
            languageBtn.textContent = 'CN';
        }

        languageDropdownMenu.classList.remove('show');
        languageBtn.classList.remove('active');

        console.log('Switching to language:', selectedLang);
    });
});

// ESC key closes all dropdowns
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        loginDropdownMenu.classList.remove('show');
        loginBtn.classList.remove('active');
        languageDropdownMenu.classList.remove('show');
        languageBtn.classList.remove('active');
    }
});

// Click outside closes dropdowns
document.addEventListener('click', function (e) {
    if (!loginBtn.contains(e.target) && !loginDropdownMenu.contains(e.target)) {
        loginDropdownMenu.classList.remove('show');
        loginBtn.classList.remove('active');
    }

    if (!languageBtn.contains(e.target) && !languageDropdownMenu.contains(e.target)) {
        languageDropdownMenu.classList.remove('show');
        languageBtn.classList.remove('active');
    }
});

// Handle login button on page load and resize
window.addEventListener('DOMContentLoaded', moveLoginBtn);
window.addEventListener('resize', moveLoginBtn);

// ========== Intersection Observer for animations ==========
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

// Initialize: pause animations and set initial state
document.querySelectorAll('.animate-on-scroll').forEach(container => {
    container.querySelectorAll('.scale-fade-in').forEach(el => {
        el.style.animationPlayState = 'paused';
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
    });
    observer.observe(container);
});

// ========== Swiper initialization ==========
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
                    updatePageIndicator(3);
                } else {
                    updatePageIndicator(this.activeIndex);
                }
            }, duration + 50);
        }
    }
});

// Page indicator functionality
const pageDots = document.querySelectorAll('.page-dot');

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

// ========== Background image loading ==========
window.addEventListener('load', () => {
    const bgImg = new Image();
    bgImg.src = "../../images/images/封面7.png";

    bgImg.onload = function () {
        document.querySelector('.home').classList.add('gradient-loaded');
        document.querySelector('.home-content').classList.remove('hidden');
        void document.querySelector('.home-content').offsetWidth;
        document.querySelector('.home-content h1').classList.add('scale-fade-in');
        document.querySelector('.home-content p').classList.add('scale-fade-in');
        document.querySelector('.navbar').classList.add('navbar-loaded');
        document.querySelector('.social-sidebar').classList.add('social-loaded');
        document.querySelector('.page-indicator').classList.add('indicator-loaded');
    };
});

// ========== Map location function ==========
function goToLocation() {
    const map = document.getElementById('custom-map');
    map.src = "https://www.google.com/maps/d/embed?mid=11C1m9L_Gcj_n8ynGotoCNc4rzq0FX54&ehbc=2E312F#target-location";
}

// ========== Slide navigation functions ==========
function goToSlide(slideIndex) {
    if (typeof swiper !== 'undefined') {
        swiper.slideTo(slideIndex);
    }
}

function goToCompanyProfile() {
    if (typeof swiper !== 'undefined') {
        swiper.slideTo(1);
    }
}

function goToCulture() {
    if (typeof swiper !== 'undefined') {
        swiper.slideTo(2);
    }
}

// ========== User avatar dropdown ==========
document.addEventListener("DOMContentLoaded", () => {
    const avatar = document.getElementById("user-avatar");
    const dropdown = document.getElementById("dropdown-menu");

    avatar.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdown.classList.toggle("show");
    });

    document.addEventListener("click", () => {
        dropdown.classList.remove("show");
    });
});
