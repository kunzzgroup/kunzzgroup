document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // 0. Global/Shared Init
    // ==========================================

    // --- Background Image Loading ---
    const homeContent = document.querySelector('.home-content'); // Note: joinus might not have .home-content if it differs from index, but code had it. 
    // JoinUs doesn't seemingly have .home-content in the viewed HTML, but the script referenced it. 
    // Checking lines 548: querySelector('.home-content'). 
    // If it exists, we handle it.

    if (homeContent) {
        homeContent.style.opacity = '0';
        homeContent.style.visibility = 'hidden';
        homeContent.style.transform = 'translateY(30px)';
    }

    if (window.bgImgSrc) {
        console.log('开始加载背景图...');
        const bgImg = new Image();
        bgImg.src = window.bgImgSrc;

        bgImg.onload = function () {
            console.log('背景图加载完成！开始显示动画');
            const homeSection = document.querySelector('.home'); // Again, check if .home exists in JoinUs.
            if (homeSection) homeSection.classList.add('gradient-loaded');

            if (homeContent) {
                homeContent.style.opacity = '1';
                homeContent.style.visibility = 'visible';
                homeContent.style.transform = 'translateY(0)';
                homeContent.style.transition = 'all 0.8s ease-out';
                homeContent.classList.remove('hidden');
            }
        };

        bgImg.onerror = function () {
            console.error('背景图加载失败，但仍显示界面元素');
            if (homeContent) {
                homeContent.style.opacity = '1';
                homeContent.style.visibility = 'visible';
                homeContent.style.transform = 'translateY(0)';
                homeContent.classList.remove('hidden');
            }
        };

        // Timeout protection
        setTimeout(() => {
            if (homeContent && homeContent.style.opacity === '0') {
                homeContent.style.opacity = '1';
                homeContent.style.visibility = 'visible';
                homeContent.style.transform = 'translateY(0)';
                homeContent.classList.remove('hidden');
            }
        }, 5000);
    }

    // ==========================================
    // 1. Swiper Initialization
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

    const urlParams = new URLSearchParams(window.location.search);
    const slideParam = urlParams.get('slide');
    if (slideParam !== null) {
        const slideIndex = parseInt(slideParam, 10);
        if (!isNaN(slideIndex)) {
            swiper.slideTo(slideIndex, 0);
        }
    }

    // ==========================================
    // 2. Animation Observers
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

    document.querySelectorAll('.animate-on-scroll').forEach(container => {
        container.querySelectorAll('.scale-fade-in').forEach(el => {
            el.style.animationPlayState = 'paused';
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
        });
        observer.observe(container);
    });

    const aboutObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const target = entry.target;
            if (entry.isIntersecting) {
                if (target.classList.contains('aboutus-banner')) target.classList.add('content-loaded');
                else if (target.classList.contains('aboutus-intro')) target.classList.add('intro-loaded');
                else if (target.classList.contains('joinus-banner')) target.classList.add('joinus-loaded');
                else if (target.classList.contains('benefits-wrapper')) target.classList.add('benefits-loaded');
                else if (target.id === 'footprint-container') target.classList.add('footprint-loaded');
                else if (target.classList.contains('job-table-container')) target.classList.add('job-table-loaded');
                else if (target.classList.contains('jobs-grid')) target.classList.add('jobs-loaded');
                else if (target.classList.contains('contact-section-wrapper')) target.classList.add('contact-loaded');
            } else {
                if (target.classList.contains('aboutus-banner')) target.classList.remove('content-loaded');
                else if (target.classList.contains('aboutus-intro')) target.classList.remove('intro-loaded');
                else if (target.classList.contains('joinus-banner')) target.classList.remove('joinus-loaded');
                else if (target.classList.contains('benefits-wrapper')) target.classList.remove('benefits-loaded');
                else if (target.id === 'footprint-container') target.classList.remove('footprint-loaded');
                else if (target.classList.contains('job-table-container')) target.classList.remove('job-table-loaded');
                else if (target.classList.contains('jobs-grid')) target.classList.remove('jobs-loaded');
                else if (target.classList.contains('contact-section-wrapper')) target.classList.remove('contact-loaded');
            }
        });
    }, { threshold: 0.2, rootMargin: '0px 0px -10% 0px' });

    ['.aboutus-banner', '.aboutus-intro', '.joinus-banner', '.benefits-wrapper', '#footprint-container',
        '.job-table-container', '.jobs-grid', '.contact-section-wrapper'].forEach(selector => {
            const el = document.querySelector(selector);
            if (el) aboutObserver.observe(el);
        });

    // ==========================================
    // 3. Comphoto Animation
    // ==========================================
    initComphoto();

    function initComphoto() {
        // console.log('开始初始化comphoto照片弹跳效果');
        // console.log('PHP照片数据:', window.comphotoImages);

        const container = document.getElementById('comphoto-container');
        if (!container) return;

        const imagesToUse = window.comphotoImages || [];
        if (imagesToUse.length === 0) return;

        container.innerHTML = '<div class="comphoto-title">Our Journey</div>';

        const photoData = [];
        const occupiedPositions = [];
        let isPaused = false;

        // Physics Params
        const getPhotoWidth = () => Math.min(120, Math.max(60, window.innerWidth * 0.08));
        const getPhotoHeight = () => Math.min(80, Math.max(40, window.innerWidth * 0.0533));
        const NAVBAR_HEIGHT = 80;
        const PHOTO_MARGIN = 10;

        function getBoundaries() {
            return {
                left: 0,
                right: window.innerWidth - getPhotoWidth(),
                top: NAVBAR_HEIGHT,
                bottom: window.innerHeight - getPhotoHeight()
            };
        }

        function createComphoto(src, index) {
            const img = document.createElement('img');
            img.src = src;
            img.className = 'comphoto';
            img.loading = 'lazy';

            const pos = getRandomNonOverlappingPosition(occupiedPositions, getPhotoWidth(), getPhotoHeight(), PHOTO_MARGIN, getBoundaries());
            const angle = generateDiagonalAngle();
            const velocity = generateVelocityFromAngle(angle);

            img.style.left = pos.x + 'px';
            img.style.top = pos.y + 'px';

            img.addEventListener('click', function () {
                openComphotoModal(this, pauseAnimation, resumeAnimation);
            });

            photoData.push({
                element: img,
                x: pos.x,
                y: pos.y,
                vx: velocity.vx,
                vy: velocity.vy,
                index: index
            });

            return img;
        }

        function pauseAnimation() { isPaused = true; }
        function resumeAnimation() { isPaused = false; }

        function updatePhotos() {
            if (isPaused) return;
            const boundaries = getBoundaries();

            photoData.forEach(photo => {
                photo.x += photo.vx;
                photo.y += photo.vy;

                let bounced = false;
                let isHorizontalBounce = false;

                if (photo.x <= boundaries.left || photo.x >= boundaries.right) {
                    photo.x = Math.max(boundaries.left, Math.min(boundaries.right, photo.x));
                    bounced = true;
                    isHorizontalBounce = true;
                }

                if (photo.y <= boundaries.top || photo.y >= boundaries.bottom) {
                    photo.y = Math.max(boundaries.top, Math.min(boundaries.bottom, photo.y));
                    bounced = true;
                    isHorizontalBounce = false;
                }

                if (bounced) {
                    const corrected = correctBounceAngle(photo.vx, photo.vy, isHorizontalBounce);
                    photo.vx = corrected.vx;
                    photo.vy = corrected.vy;
                }

                photo.element.style.left = photo.x + 'px';
                photo.element.style.top = photo.y + 'px';
            });
        }

        function animate() {
            updatePhotos();
            requestAnimationFrame(animate);
        }

        imagesToUse.forEach((photo, index) => {
            container.appendChild(createComphoto(photo, index));
        });

        animate();
    }

    // Helpers for Comphoto (moved outside to be cleaner or inside if closure needed, keeping shared logic)
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

    function correctBounceAngle(vx, vy, isHorizontalBounce) {
        let newVx = vx, newVy = vy;
        if (isHorizontalBounce) newVx = -vx; else newVy = -vy;

        let currentAngle = Math.atan2(newVy, newVx);
        if (currentAngle < 0) currentAngle += 2 * Math.PI;

        const tolerance = Math.PI / 8;
        const criticalAngles = [0, Math.PI, 2 * Math.PI, Math.PI / 2, 3 * Math.PI / 2];
        let needsCorrection = false;

        for (let angle of criticalAngles) {
            if (Math.abs(currentAngle - angle) < tolerance) {
                needsCorrection = true;
                break;
            }
        }

        if (needsCorrection) {
            const newAngle = generateDiagonalAngle();
            const v = generateVelocityFromAngle(newAngle);
            return { vx: v.vx, vy: v.vy };
        }
        return { vx: newVx, vy: newVy };
    }

    function isOverlapping(pos1, pos2, w, h, margin) {
        return !(pos1.x + w + margin < pos2.x || pos2.x + w + margin < pos1.x || pos1.y + h + margin < pos2.y || pos2.y + h + margin < pos1.y);
    }

    function getRandomNonOverlappingPosition(occupied, w, h, margin, boundaries) {
        let attempts = 0;
        const maxAttempts = 200;
        while (attempts < maxAttempts) {
            const x = Math.random() * (boundaries.right - boundaries.left) + boundaries.left;
            const y = Math.random() * (boundaries.bottom - boundaries.top) + boundaries.top;
            const newPos = { x, y };
            let overlaps = false;
            for (let occupiedPos of occupied) {
                if (isOverlapping(newPos, occupiedPos, w, h, margin)) {
                    overlaps = true; break;
                }
            }
            if (!overlaps) {
                occupied.push(newPos);
                return newPos;
            }
            attempts++;
        }
        // Fallback
        const cols = Math.floor((boundaries.right - boundaries.left) / (w + margin));
        const index = occupied.length;
        const col = index % cols;
        const row = Math.floor(index / cols);
        const fallbackPos = { x: boundaries.left + col * (w + margin), y: boundaries.top + row * (h + margin) };
        occupied.push(fallbackPos);
        return fallbackPos;
    }

    function openComphotoModal(clickedImg, pauseCb, resumeCb) {
        if (pauseCb) pauseCb();
        const modal = document.getElementById('comphoto-modal');
        const modalImg = document.getElementById('comphoto-modal-img');
        const modalContent = document.querySelector('.comphoto-modal-content');

        if (modal && modalImg && modalContent) {
            modalImg.src = clickedImg.src;
            modal.style.display = 'block';

            const rect = clickedImg.getBoundingClientRect();
            modalContent.style.left = rect.left + 'px';
            modalContent.style.top = rect.top + 'px';
            modalContent.style.width = rect.width + 'px';
            modalContent.style.height = rect.height + 'px';

            document.body.style.overflow = 'hidden';
            clickedImg.classList.add('comphoto-hidden');
            modalContent.offsetHeight;

            requestAnimationFrame(() => {
                modal.classList.add('show');
                const scaleMultiplier = 8;
                const centerX = window.innerWidth / 2;
                const centerY = window.innerHeight / 2;
                modalContent.style.left = centerX - (rect.width * scaleMultiplier) / 2 + 'px';
                modalContent.style.top = centerY - (rect.height * scaleMultiplier) / 2 + 'px';
                modalContent.style.width = rect.width * scaleMultiplier + 'px';
                modalContent.style.height = rect.height * scaleMultiplier + 'px';
            });

            // Close logic
            function closeHandler() {
                modal.classList.remove('show');
                modalContent.style.left = rect.left + 'px';
                modalContent.style.top = rect.top + 'px';
                modalContent.style.width = rect.width + 'px';
                modalContent.style.height = rect.height + 'px';

                setTimeout(() => {
                    modal.style.display = 'none';
                    clickedImg.classList.remove('comphoto-hidden');
                    document.body.style.overflow = '';
                    if (resumeCb) resumeCb();
                    // Cleanup event listeners if needed, or rely on simple toggle
                }, 400);
            }

            const closeBtn = document.querySelector('.comphoto-close');
            if (closeBtn) closeBtn.onclick = closeHandler;
            modal.onclick = (e) => { if (e.target === modal) closeHandler(); };
        }
    }

    // ==========================================
    // 4. Job Data & Modals
    // ==========================================
    initParticles();
    loadJobsData();

    function initParticles() {
        const particles = document.getElementById('particles');
        if (!particles) return;
        for (let i = 0; i < 50; i++) {
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

    // Make functions available globally for onclick handlers in HTML
    window.jobsData = {};
    window.loadJobsData = async function () {
        try {
            const possiblePaths = ['get_jobs_api.php', './get_jobs_api.php', '/get_jobs_api.php', 'job_positions_api.php'];
            let response = null;
            for (const path of possiblePaths) {
                try {
                    response = await fetch(`${path}?lang=en`);
                    if (response.ok) break;
                } catch (e) { }
            }
            if (!response || !response.ok) throw new Error('API Failed');

            const data = await response.json();
            if (data.success && data.companies) {
                window.jobsData = {};
                Object.values(data.companies).forEach(company => {
                    company.jobs.forEach(job => {
                        window.jobsData[job.id] = {
                            title: job.title,
                            count: job.count,
                            experience: job.experience,
                            publish_date: job.publish_date,
                            company: company.name,
                            description: job.description,
                            address: job.address || 'Pending',
                            department: job.department || '',
                            salary: job.salary || ''
                        };
                    });
                });
            } else {
                showJobLoadError();
            }
        } catch (error) {
            console.error('Job load failed:', error);
            showJobLoadError();
        }
    };

    function showJobLoadError() {
        const jobsGrid = document.querySelector('.jobs-grid');
        if (jobsGrid) {
            jobsGrid.innerHTML = `<div style="text-align:center;padding:40px;color:#666;"><h3>Failed to load jobs</h3><button onclick="location.reload()">Reload</button></div>`;
        }
    }

    window.openJobDetail = function (jobId) {
        const jobData = window.jobsData[jobId] || {
            title: 'Position Details', count: '-', experience: '-', publish_date: '-',
            company: '-', description: '-', address: '-'
        };

        document.getElementById('jobDetailTitle').textContent = jobData.title;
        document.getElementById('jobDetailCount').textContent = jobData.count;
        document.getElementById('jobDetailExperience').textContent = jobData.experience;
        document.getElementById('jobDetailPublishDate').textContent = jobData.publish_date;
        document.getElementById('jobDetailCompany').textContent = jobData.company;
        document.getElementById('jobDetailDescription').textContent = jobData.description;
        document.getElementById('jobDetailAddress').textContent = jobData.address;

        const deptEl = document.getElementById('jobDetailDepartment');
        if (jobData.department && deptEl) {
            document.getElementById('jobDetailDepartmentValue').textContent = jobData.department;
            deptEl.style.display = 'flex';
        } else if (deptEl) deptEl.style.display = 'none';

        const salEl = document.getElementById('jobDetailSalary');
        if (jobData.salary && salEl) {
            document.getElementById('jobDetailSalaryValue').textContent = jobData.salary;
            salEl.style.display = 'flex';
        } else if (salEl) salEl.style.display = 'none';

        document.getElementById('jobDetailModal').style.display = 'flex';
    };

    window.closeJobDetail = function () {
        document.getElementById('jobDetailModal').style.display = 'none';
    };

    window.openFormFromDetail = function () {
        const title = document.getElementById('jobDetailTitle').textContent;
        window.closeJobDetail();
        window.openForm(title);
    };

    window.openForm = function (position) {
        document.getElementById('formPosition').value = position;
        document.getElementById('formModal').style.display = 'flex';
    };

    window.closeForm = function () {
        document.getElementById('formModal').style.display = 'none';
    };

    // Map Link
    window.goToLocation = function () {
        const map = document.getElementById('custom-map');
        if (map) map.src = "https://www.google.com/maps/d/embed?mid=1WGUSQUviVSNKcc7LNK-aSDA6j6S3EMc&ehbc=2E312F#target-location";
    };
});
