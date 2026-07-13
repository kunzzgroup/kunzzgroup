
// Expects strategiesData, orgData, internalOrgData from inline PHP script.

function isCorporateBlueprintReactV2Page() {
    return /corporate_blueprint-v2/.test(window.location.pathname || '');
}

function getFormattedStrategiesData() {
    const raw = window.strategiesData || [];
    return raw.map((obj, index) => ({
        deptName: 'S' + (index + 1) + '-' + (obj.department || ''),
        deptDisplay: obj.department || '',
        strategy: obj.strategy || '',
        department: obj.department || '',
        pic: obj.pic || '',
        startDate: obj.startDate || '',
        endDate: obj.endDate || '',
        dashboardMetrics: obj.dashboardMetrics || [],
        year: obj.year || '',
    }));
}

const initializedCharts = {};

function selectStrategy(index) {
    const strategy = getFormattedStrategiesData()[index];
    if (!strategy) return;

    document.querySelectorAll('.strategy-card').forEach((card, i) => {
        if (i === index) {
            card.classList.add('active');
            const check = card.querySelector('.strategy-check');
            if (check) check.style.display = 'block';
        } else {
            card.classList.remove('active');
            const check = card.querySelector('.strategy-check');
            if (check) check.style.display = 'none';
        }
    });

    const detailsEl = document.getElementById('strategicDetails');
    if (!detailsEl) return;

    detailsEl.classList.add('hidden');

    setTimeout(() => {
        const titleEl = document.getElementById('detailsTitle');
        if (titleEl) titleEl.textContent = strategy.strategy;

        const badgeEl = document.getElementById('detailsBadge');
        if (badgeEl) badgeEl.textContent = strategy.deptDisplay || strategy.deptName || 'Selected Pillar';

        const picEl = document.getElementById('picName');
        if (picEl) picEl.textContent = strategy.pic || '—';

        const formatDate = (dateStr) => {
            if (!dateStr) return '—';
            try {
                const date = new Date(dateStr);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            } catch (e) {
                return dateStr;
            }
        };

        const startEl = document.getElementById('startDate');
        if (startEl) startEl.textContent = formatDate(strategy.startDate);

        const endEl = document.getElementById('endDate');
        if (endEl) endEl.textContent = formatDate(strategy.endDate);

        const metricsList = document.getElementById('measureList');
        if (metricsList) {
            if (strategy.dashboardMetrics && strategy.dashboardMetrics.length > 0) {
                metricsList.innerHTML = strategy.dashboardMetrics.map((metric) =>
                    `<li class="measure-list-item">
                        <div class="measure-dot"></div>
                        <span class="measure-text">${metric}</span>
                    </li>`
                ).join('');
            } else {
                metricsList.innerHTML = '<li class="measure-list-item"><span class="measure-text">暂无指标</span></li>';
            }
        }

        detailsEl.classList.remove('hidden');
    }, 300);
}

function initStrategyCards() {
    const cards = document.querySelectorAll('.strategy-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateX(-30px)';
        setTimeout(() => {
            card.style.transition = 'all 0.8s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateX(0)';
        }, 300 + (index * 100));
    });

    const countEl = document.getElementById('strategicListCount');
    if (countEl) {
        countEl.textContent = getFormattedStrategiesData().length.toString();
    }
}

function initTimelineAnimation() {
    const timelineWrapper = document.querySelector('.timeline-wrapper');
    if (!timelineWrapper) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animateTimeline(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.3,
        rootMargin: '0px 0px -100px 0px',
    });

    observer.observe(timelineWrapper);

    function animateTimeline(container) {
        const routePath = container.querySelector('.map-route-path');
        if (routePath) {
            setTimeout(() => {
                routePath.classList.add('animate-in');
            }, 200);
        }

        const milestones = container.querySelectorAll('.map-milestone');
        milestones.forEach((milestone, index) => {
            setTimeout(() => {
                milestone.classList.add('animate-in');
            }, 1000 + (index * 200));
        });
    }

    document.querySelectorAll('.map-milestone').forEach((milestone) => {
        milestone.addEventListener('mouseenter', function () {
            this.style.zIndex = '20';
        });
        milestone.addEventListener('mouseleave', function () {
            this.style.zIndex = '10';
        });
    });
}

function initMainOrgChart() {
    const orgData = window.orgData;
    if (!orgData || typeof window.jQuery === 'undefined') {
        return;
    }

    const $container = window.jQuery('#orgchart-container');
    if ($container.length === 0) return;

    $container.empty().orgchart({
        data: orgData,
        nodeContent: 'title',
        nodeId: 'id',
        pan: false,
        zoom: false,
        toggleSiblingsResp: true,
        createNode($node, data) {
            const level = data.level || '';
            $node.addClass('level-' + level);
            const title = data.title || '—';
            const name = data.name || '—';
            $node.html(
                '<div class="orgchart-node-title">' + title + '</div>' +
                '<div class="orgchart-node-content">' + name + '</div>'
            );
        },
        draggable: false,
        direction: 't2b',
    });

    setTimeout(() => {
        const orgchartEl = $container.find('.orgchart');
        if (orgchartEl.length) {
            const containerWidth = $container.width();
            const chartWidth = orgchartEl.outerWidth();
            if (chartWidth < containerWidth) {
                orgchartEl.css('margin-left', ((containerWidth - chartWidth) / 2) + 'px');
            }
        }
    }, 100);
}

function switchInternalDept(deptIndex) {
    if (typeof window.jQuery === 'undefined') return;

    const $ = window.jQuery;
    $('.internal-dept-btn').removeClass('active');
    $('.internal-dept-btn[data-dept-index="' + deptIndex + '"]').addClass('active');
    $('.internal-dept-chart-wrapper').removeClass('active');
    $('.internal-dept-chart-wrapper[data-dept-index="' + deptIndex + '"]').addClass('active');

    if (window.internalOrgData && window.internalOrgData[deptIndex] && !initializedCharts[deptIndex]) {
        initializeDeptChart(deptIndex, window.internalOrgData[deptIndex]);
    }
}

function initializeDeptChart(index, deptTree) {
    if (typeof window.jQuery === 'undefined') return;

    const $ = window.jQuery;
    const containerId = '#internal-dept-chart-' + index;
    const $container = $(containerId);

    if ($container.length === 0) {
        console.warn('容器不存在:', containerId);
        return;
    }

    $container.empty().orgchart({
        data: deptTree,
        nodeContent: 'title',
        nodeId: 'id',
        pan: false,
        zoom: false,
        toggleSiblingsResp: true,
        createNode($node, data) {
            const level = data.level || '';
            $node.addClass('level-' + level);
            const title = data.title || '—';
            const name = data.name || '—';
            $node.html(
                '<div class="orgchart-node-title">' + title + '</div>' +
                '<div class="orgchart-node-content">' + name + '</div>'
            );
        },
        draggable: false,
        direction: 't2b',
    });

    initializedCharts[index] = true;

    setTimeout(() => {
        const orgchartEl = $container.find('.orgchart');
        if (orgchartEl.length) {
            const containerWidth = $container.width();
            const chartWidth = orgchartEl.outerWidth();
            if (chartWidth < containerWidth) {
                orgchartEl.css('margin-left', ((containerWidth - chartWidth) / 2) + 'px');
            }
        }
    }, 100);
}

function initInternalOrgCharts() {
    const internalOrgData = window.internalOrgData;
    if (!internalOrgData || internalOrgData.length === 0) {
        return;
    }

    Object.keys(initializedCharts).forEach((key) => {
        delete initializedCharts[key];
    });

    initializeDeptChart(0, internalOrgData[0]);
}

function initOrgCharts() {
    initMainOrgChart();
    initInternalOrgCharts();
}

function alignScoringSections() {
    document.querySelectorAll('.culture-explanation-grid').forEach((grid) => {
        const cards = grid.querySelectorAll('.culture-explanation-card');
        if (cards.length === 0) return;

        cards.forEach((card) => {
            const content = card.querySelector('.culture-explanation-content');
            if (content) content.style.minHeight = 'auto';
        });

        void grid.offsetHeight;

        let maxHeight = 0;
        cards.forEach((card) => {
            const content = card.querySelector('.culture-explanation-content');
            if (content && content.offsetHeight > maxHeight) {
                maxHeight = content.offsetHeight;
            }
        });

        cards.forEach((card) => {
            const content = card.querySelector('.culture-explanation-content');
            if (content) content.style.minHeight = maxHeight + 'px';
        });
    });
}

function bootCorporateBlueprint() {
    initStrategyCards();
    initTimelineAnimation();
    initOrgCharts();
    alignScoringSections();
}

window.bootCorporateBlueprint = bootCorporateBlueprint;
window.reinitCorporateBlueprint = bootCorporateBlueprint;
window.selectStrategy = selectStrategy;
window.switchInternalDept = switchInternalDept;

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(alignScoringSections, 250);
});

if (!isCorporateBlueprintReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootCorporateBlueprint);
    } else {
        bootCorporateBlueprint();
    }
}
