document.addEventListener('DOMContentLoaded', function () {
    // ==========================================
    // 1. Animation Observers
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
    }, { threshold: 0.2 });

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const target = entry.target;
            if (entry.isIntersecting) {
                if (target.classList.contains('joinus-banner')) target.classList.add('joinus-loaded');
                else if (target.classList.contains('benefits-wrapper')) target.classList.add('benefits-loaded');
                else if (target.id === 'footprint-container') target.classList.add('footprint-loaded');
                else if (target.classList.contains('job-table-container')) target.classList.add('job-table-loaded');
                else if (target.classList.contains('jobs-grid')) target.classList.add('jobs-loaded');
                else if (target.classList.contains('contact-section-wrapper')) target.classList.add('contact-loaded');
            } else {
                if (target.classList.contains('joinus-banner')) target.classList.remove('joinus-loaded');
                else if (target.classList.contains('benefits-wrapper')) target.classList.remove('benefits-loaded');
                else if (target.id === 'footprint-container') target.classList.remove('footprint-loaded');
                else if (target.classList.contains('job-table-container')) target.classList.remove('job-table-loaded');
                else if (target.classList.contains('jobs-grid')) target.classList.remove('jobs-loaded');
                else if (target.classList.contains('contact-section-wrapper')) target.classList.remove('contact-loaded');
            }
        });
    }, { threshold: 0.2, rootMargin: '0px 0px -10% 0px' });

    // Initialize Observers
    document.querySelectorAll('.animate-on-scroll').forEach(container => {
        container.querySelectorAll('.scale-fade-in').forEach(el => {
            el.style.animationPlayState = 'paused';
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
        });
        observer.observe(container);
    });

    const joinusBanner = document.querySelector('.joinus-banner');
    const benefitsWrapper = document.querySelector('.benefits-wrapper');
    const footprintContainer = document.querySelector('#comphoto-container'); // Correction based on ID usage in HTML
    const jobTableContainer = document.querySelector('.job-table-container');
    const jobsGrid = document.querySelector('.jobs-grid');
    const contactWrapper = document.querySelector('.contact-section-wrapper');

    if (joinusBanner) sectionObserver.observe(joinusBanner);
    if (benefitsWrapper) sectionObserver.observe(benefitsWrapper);
    if (footprintContainer) sectionObserver.observe(footprintContainer);
    if (jobTableContainer) sectionObserver.observe(jobTableContainer);
    if (jobsGrid) sectionObserver.observe(jobsGrid);
    if (contactWrapper) sectionObserver.observe(contactWrapper);

    // ==========================================
    // 2. Swiper Initialization
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
                toggleBackToTopButton();
            },
            reachEnd: function () {
                this.allowTouchMove = true;
            },
            setTransition: function (duration) {
                setTimeout(() => {
                    if (this.progress > 0.95) {
                        updatePageIndicator(5);
                    } else {
                        updatePageIndicator(this.activeIndex);
                    }
                }, duration + 50);
            }
        }
    });

    const pageDots = document.querySelectorAll('.header-page-dot');
    pageDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            window.swiper.slideTo(index);
        });
    });

    function updatePageIndicator(activeIndex) {
        pageDots.forEach((dot, index) => {
            if (index === activeIndex) dot.classList.add('active');
            else dot.classList.remove('active');
        });
    }
    updatePageIndicator(0);

    const urlParams = new URLSearchParams(window.location.search);
    const slideParam = urlParams.get('slide');
    if (slideParam !== null) {
        const slideIndex = parseInt(slideParam, 10);
        if (!isNaN(slideIndex)) window.swiper.slideTo(slideIndex, 0);
    }

    // ==========================================
    // 3. Particles
    // ==========================================
    initParticles();
});

// ==========================================
// 4. Job Data & Modals
// ==========================================
let jobsData = {};

async function loadJobsData() {
    try {
        const possiblePaths = ['get_jobs_api.php', './get_jobs_api.php', '/get_jobs_api.php', 'job_positions_api.php'];
        let response = null;
        let lastError = null;

        for (const path of possiblePaths) {
            try {
                response = await fetch(`${path}?lang=zh`);
                if (response.ok) break;
            } catch (error) {
                lastError = error;
            }
        }

        if (!response || !response.ok) throw new Error(`API failed: ${lastError?.message || 'Unknown error'}`);

        const data = await response.json();
        if (data.success && data.companies) {
            jobsData = {};
            Object.values(data.companies).forEach(company => {
                company.jobs.forEach(job => {
                    jobsData[job.id] = {
                        title: job.title,
                        count: job.count,
                        experience: job.experience,
                        publish_date: job.publish_date,
                        company: company.name,
                        description: job.description,
                        address: job.address || '待定',
                        department: job.department || '',
                        salary: job.salary || ''
                    };
                });
            });
        } else {
            showJobLoadError();
        }
    } catch (error) {
        console.error('Failed to load jobs:', error);
        showJobLoadError();
    }
}

function showJobLoadError() {
    const jobsGrid = document.querySelector('.jobs-grid');
    if (jobsGrid) {
        jobsGrid.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                <h3>职位数据加载失败</h3>
                <p>请稍后刷新页面重试。</p>
                <button onclick="location.reload()" style="background: linear-gradient(135deg, #FF5C00 0%, #ff7a33 100%); color: white; border: none; padding: 10px 20px; border-radius: 25px; cursor: pointer; margin-top: 10px;">刷新页面</button>
            </div>
        `;
    }
}

function getJobData(jobId) { return jobsData[jobId] || null; }

function openJobDetail(jobId) {
    const jobData = getJobData(jobId);
    if (!jobData) {
        // Default data if missing
        document.getElementById('jobDetailTitle').textContent = '职位详情';
        document.getElementById('jobDetailDepartment').style.display = 'none';
        document.getElementById('jobDetailSalary').style.display = 'none';
    } else {
        document.getElementById('jobDetailTitle').textContent = jobData.title;
        document.getElementById('jobDetailCount').textContent = jobData.count;
        document.getElementById('jobDetailExperience').textContent = jobData.experience;
        document.getElementById('jobDetailPublishDate').textContent = jobData.publish_date;
        document.getElementById('jobDetailCompany').textContent = jobData.company;
        document.getElementById('jobDetailDescription').textContent = jobData.description;
        document.getElementById('jobDetailAddress').textContent = jobData.address;

        if (jobData.department) {
            document.getElementById('jobDetailDepartmentValue').textContent = jobData.department;
            document.getElementById('jobDetailDepartment').style.display = 'flex';
        } else {
            document.getElementById('jobDetailDepartment').style.display = 'none';
        }

        if (jobData.salary) {
            document.getElementById('jobDetailSalaryValue').textContent = jobData.salary;
            document.getElementById('jobDetailSalary').style.display = 'flex';
        } else {
            document.getElementById('jobDetailSalary').style.display = 'none';
        }
    }
    document.getElementById('jobDetailModal').style.display = 'flex';
}

function closeJobDetail() { document.getElementById('jobDetailModal').style.display = 'none'; }

function openFormFromDetail() {
    const jobTitle = document.getElementById('jobDetailTitle').textContent;
    closeJobDetail();
    openForm(jobTitle);
}

function openForm(position) {
    document.getElementById('formPosition').value = position;
    document.getElementById('formModal').style.display = 'flex';
}

function closeForm() { document.getElementById('formModal').style.display = 'none'; }

window.onclick = function (event) {
    const formModal = document.getElementById('formModal');
    const jobDetailModal = document.getElementById('jobDetailModal');
    if (event.target == formModal) formModal.style.display = 'none';
    if (event.target == jobDetailModal) jobDetailModal.style.display = 'none';
}

function initJobClickHandlers() {
    document.addEventListener('click', function (event) {
        const jobItem = event.target.closest('.job-item');
        if (jobItem) {
            const jobId = jobItem.getAttribute('data-job-id');
            if (jobId) openJobDetail(jobId);
        }
    });
}

// Particle Init
function initParticles() {
    const particles = document.getElementById('particles');
    if (!particles) return;

    const particleCount = 50;
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.width = Math.random() * 4 + 2 + 'px';
        particle.style.height = particle.style.width;
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
        particles.appendChild(particle);
    }
}

// ==========================================
// 5. Comphoto Logic (Our Footprint)
// ==========================================
// Requires external 'comphotoImages' variable
(function () {
    const photoData = [];
    let currentClickedImg = null;
    let animationId = null;
    let isPaused = false;
    const occupiedPositions = [];

    const getPhotoWidth = () => Math.min(120, Math.max(60, window.innerWidth * 0.08));
    const getPhotoHeight = () => Math.min(80, Math.max(40, window.innerWidth * 0.0533));
    const PHOTO_MARGIN = 10;
    const NAVBAR_HEIGHT = 80;

    function generateDiagonalAngle() {
        const minAngle = Math.PI / 6;
        const maxAngle = Math.PI / 3;
        const quadrant = Math.floor(Math.random() * 4);
        let baseAngle;
        switch (quadrant) {
            case 0: baseAngle = Math.random() * (maxAngle - minAngle) + minAngle; break;
            case 1: baseAngle = Math.PI - (Math.random() * (maxAngle - minAngle) + minAngle); break;
            case 2: baseAngle = Math.PI + (Math.random() * (maxAngle - minAngle) + minAngle); break;
            case 3: baseAngle = 2 * Math.PI - (Math.random() * (maxAngle - minAngle) + minAngle); break;
        }
        return baseAngle;
    }

    function generateVelocityFromAngle(angle) {
        const speed = 0.6;
        return { vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed };
    }

    function getBoundaries() {
        return {
            left: 0,
            right: window.innerWidth - getPhotoWidth(),
            top: NAVBAR_HEIGHT,
            bottom: window.innerHeight - getPhotoHeight()
        };
    }

    function isOverlapping(pos1, pos2) {
        const w = getPhotoWidth(), h = getPhotoHeight(), m = PHOTO_MARGIN;
        return !(pos1.x + w + m < pos2.x || pos2.x + w + m < pos1.x || pos1.y + h + m < pos2.y || pos2.y + h + m < pos1.y);
    }

    function getRandomNonOverlappingPosition() {
        const b = getBoundaries();
        let attempts = 0;
        while (attempts < 200) {
            const x = Math.random() * (b.right - b.left) + b.left;
            const y = Math.random() * (b.bottom - b.top) + b.top;
            const newPos = { x, y };
            if (!occupiedPositions.some(pos => isOverlapping(newPos, pos))) {
                occupiedPositions.push(newPos);
                return newPos;
            }
            attempts++;
        }
        return { x: b.left, y: b.top };
    }

    function correctBounceAngle(vx, vy, isHorizontal) {
        let newVx = isHorizontal ? -vx : vx;
        let newVy = isHorizontal ? vy : -vy;
        return { vx: newVx, vy: newVy };
    }

    function createComphoto(src, index) {
        const img = document.createElement('img');
        img.src = src;
        img.className = 'comphoto';
        img.loading = 'lazy';
        const pos = getRandomNonOverlappingPosition();
        const angle = generateDiagonalAngle();
        const v = generateVelocityFromAngle(angle);
        img.style.left = pos.x + 'px';
        img.style.top = pos.y + 'px';

        img.addEventListener('click', function () { openComphotoModal(this); });

        photoData.push({ element: img, x: pos.x, y: pos.y, vx: v.vx, vy: v.vy, index });
        return img;
    }

    function updatePhotos() {
        if (isPaused) return;
        const b = getBoundaries();
        photoData.forEach(p => {
            p.x += p.vx;
            p.y += p.vy;
            let bounced = false, isHoriz = false;

            if (p.x <= b.left || p.x >= b.right) {
                p.x = Math.max(b.left, Math.min(b.right, p.x));
                bounced = true; isHoriz = true;
            }
            if (p.y <= b.top || p.y >= b.bottom) {
                p.y = Math.max(b.top, Math.min(b.bottom, p.y));
                bounced = true; isHoriz = false;
            }
            if (bounced) {
                const nv = correctBounceAngle(p.vx, p.vy, isHoriz);
                p.vx = nv.vx; p.vy = nv.vy;
            }
            p.element.style.left = p.x + 'px';
            p.element.style.top = p.y + 'px';
        });
    }

    function animate() {
        updatePhotos();
        animationId = requestAnimationFrame(animate);
    }

    window.initComphoto = function () {
        if (typeof window.comphotoImages === 'undefined' || window.comphotoImages.length === 0) return;
        const container = document.getElementById('comphoto-container');
        if (!container) return;

        container.innerHTML = '<div class="comphoto-title">我们的足迹</div>';
        photoData.length = 0;
        occupiedPositions.length = 0;

        window.comphotoImages.forEach((src, idx) => {
            container.appendChild(createComphoto(src, idx));
        });
        animate();
    };

    function openComphotoModal(img) {
        currentClickedImg = img;
        isPaused = true;
        const modal = document.getElementById('comphoto-modal');
        const modalImg = document.getElementById('comphoto-modal-img');
        if (modal && modalImg) {
            modalImg.src = img.src;
            modal.style.display = 'block';
            img.classList.add('comphoto-hidden');
            setTimeout(() => modal.classList.add('show'), 10);
        }
    }

    window.closeComphotoModal = function () {
        const modal = document.getElementById('comphoto-modal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                if (currentClickedImg) currentClickedImg.classList.remove('comphoto-hidden');
                currentClickedImg = null;
                isPaused = false;
            }, 400);
        }
    };
})();

// Document Ready for Comphoto & Jobs
document.addEventListener('DOMContentLoaded', () => {
    if (window.initComphoto) window.initComphoto();
    loadJobsData();
    initJobClickHandlers();

    // Comphoto Close
    const closeBtn = document.querySelector('.comphoto-close');
    const modal = document.getElementById('comphoto-modal');
    if (closeBtn) closeBtn.addEventListener('click', window.closeComphotoModal);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) window.closeComphotoModal(); });
});

// ==========================================
// 6. Helpers & Footer
// ==========================================
function goToLocation() {
    const map = document.getElementById('custom-map');
    if (map) map.src = "https://www.google.com/maps/d/embed?mid=1WGUSQUviVSNKcc7LNK-aSDA6j6S3EMc&ehbc=2E312F#target-location";
}

function scrollToTop() {
    if (typeof window.swiper !== 'undefined') window.swiper.slideTo(0);
}

function toggleBackToTopButton() {
    const btn = document.getElementById('backToTop');
    if (btn && typeof window.swiper !== 'undefined') {
        btn.style.display = (window.swiper.activeIndex === window.swiper.slides.length - 1) ? 'block' : 'none';
    }
}

function resizeJobs() {
    const scale = Math.min(window.innerWidth / 1440, window.innerHeight / 900);
    document.documentElement.style.setProperty("--scale", scale);
}
window.addEventListener("resize", resizeJobs);
resizeJobs();

// ==========================================
// 7. Background Music
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    const bgMusic = document.getElementById('bgMusic');
    if (bgMusic) {
        bgMusic.volume = 0.3;
        const savedTime = localStorage.getItem('musicCurrentTime');
        const savedPlaying = localStorage.getItem('musicPlaying');
        const currentPage = window.location.pathname;

        if (savedTime) bgMusic.currentTime = parseFloat(savedTime);
        const tryPlay = () => bgMusic.play().then(() => {
            localStorage.setItem('musicPlaying', 'true');
            localStorage.setItem('musicPage', currentPage);
        }).catch(e => console.log('Music play failed', e));

        if (savedPlaying === 'true') setTimeout(tryPlay, 100);

        const startEvents = ['click', 'keydown', 'touchstart'];
        const startPlay = () => {
            tryPlay();
            startEvents.forEach(e => document.removeEventListener(e, startPlay));
        };
        startEvents.forEach(e => document.addEventListener(e, startPlay, { once: true }));

        setInterval(() => {
            if (!bgMusic.paused && bgMusic.currentTime > 0) {
                localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
                localStorage.setItem('musicPlaying', 'true');
            }
        }, 1000);

        window.addEventListener('beforeunload', () => {
            localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
            localStorage.setItem('musicPlaying', bgMusic.paused ? 'false' : 'true');
        });

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && localStorage.getItem('musicPlaying') === 'true' && bgMusic.paused) {
                tryPlay();
            }
        });
    }
});

// Helper Navigation
window.goToSlide = (idx) => window.swiper && window.swiper.slideTo(idx);
window.goToBenefits = () => window.goToSlide(0);
window.goToComphoto = () => window.goToSlide(1);
window.goToJob = () => window.goToSlide(2);
window.goToMap = () => window.goToSlide(4);
