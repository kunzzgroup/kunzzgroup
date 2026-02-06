// Global variables to store data
let orgData = null;
let internalOrgData = null;
let formattedStrategiesData = [];

// Load data from HTML data attributes
function loadDataFromDOM() {
    const dataContainer = document.getElementById('corporate-data');

    if (!dataContainer) {
        console.error('Data container not found');
        return false;
    }

    try {
        // Parse organization chart data
        const orgChartJson = dataContainer.getAttribute('data-org-chart');
        orgData = orgChartJson ? JSON.parse(orgChartJson) : null;

        // Parse internal organization data
        const internalOrgJson = dataContainer.getAttribute('data-internal-org');
        internalOrgData = internalOrgJson ? JSON.parse(internalOrgJson) : [];

        // Parse and format strategic objectives data
        const objectivesJson = dataContainer.getAttribute('data-objectives');
        const strategiesData = objectivesJson ? JSON.parse(objectivesJson) : [];

        // Format strategies data
        formattedStrategiesData = strategiesData.map((obj, index) => ({
            deptName: 'S' + (index + 1) + '-' + (obj.department || ''),
            deptDisplay: obj.department || '',
            strategy: obj.strategy || '',
            department: obj.department || '',
            pic: obj.pic || '',
            startDate: obj.startDate || '',
            endDate: obj.endDate || '',
            dashboardMetrics: obj.dashboardMetrics || [],
            year: obj.year || ''
        }));

        return true;
    } catch (error) {
        console.error('Error parsing data from DOM:', error);
        return false;
    }
}

// Initialize when DOM is ready
$(document).ready(function () {
    // Load data from DOM first
    if (!loadDataFromDOM()) {
        console.error('Failed to load data from DOM');
        return;
    }

    // Timeline Animation
    initializeTimelineAnimation();

    // Organization Chart Initialization
    initializeOrgChart();

    // Internal Organization Charts
    initializeInternalOrgCharts();

    // Strategic Objectives
    if (formattedStrategiesData && formattedStrategiesData.length > 0) {
        initializeStrategicObjectives();
    }
});

// Timeline Animation Functions
function initializeTimelineAnimation() {
    const timelineWrapper = document.querySelector('.timeline-wrapper');
    if (!timelineWrapper) return;

    // Create IntersectionObserver to watch timeline container
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Trigger timeline animation
                animateTimeline(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.3,
        rootMargin: '0px 0px -100px 0px'
    });

    observer.observe(timelineWrapper);

    function animateTimeline(container) {
        // 1. Draw path first
        const routePath = container.querySelector('.map-route-path');
        if (routePath) {
            setTimeout(() => {
                routePath.classList.add('animate-in');
            }, 200);
        }

        // 2. Show milestones one by one (按路径顺序)
        const milestones = container.querySelectorAll('.map-milestone');
        milestones.forEach((milestone, index) => {
            setTimeout(() => {
                milestone.classList.add('animate-in');
            }, 1000 + (index * 200)); // Start showing milestones after path animation
        });
    }

    //Add milestone hover path highlight effect
    const milestones = document.querySelectorAll('.map-milestone');
    milestones.forEach(milestone => {
        milestone.addEventListener('mouseenter', function () {
            this.style.zIndex = '20';
        });
        milestone.addEventListener('mouseleave', function () {
            this.style.zIndex = '10';
        });
    });
}

// Organization Chart Functions
function initializeOrgChart() {
    if (!orgData) {
        console.error('组织架构数据为空');
        $('#orgchart-container').html('<p style="text-align: center; color: #6b7280; padding: 40px;">无法加载组织架构数据</p>');
        return;
    }

    console.log('组织架构数据:', orgData);

    // Initialize OrgChart.js tree structure
    $('#orgchart-container').orgchart({
        'data': orgData,
        'nodeContent': 'title',
        'nodeId': 'id',
        'pan': false,
        'zoom': false,
        'toggleSiblingsResp': true,
        'createNode': function ($node, data) {
            // Custom node styling
            const level = data.level || '';
            $node.addClass('level-' + level);

            // Custom node content - display position and name
            const title = data.title || '—';
            const name = data.name || '—';

            $node.html(
                '<div class="orgchart-node-title">' + title + '</div>' +
                '<div class="orgchart-node-content">' + name + '</div>'
            );
        },
        'draggable': false,
        'direction': 't2b'
    });

    // Center the organization chart
    setTimeout(function () {
        const orgchartEl = $('#orgchart-container .orgchart');
        if (orgchartEl.length) {
            const containerWidth = $('#orgchart-container').width();
            const chartWidth = orgchartEl.outerWidth();
            if (chartWidth < containerWidth) {
                const offsetLeft = (containerWidth - chartWidth) / 2;
                orgchartEl.css('margin-left', offsetLeft + 'px');
            }
        }
    }, 100);
}

// Internal Organization Charts Functions
const initializedCharts = {}; // Track initialized charts

function switchInternalDept(deptIndex) {
    // Update button state
    $('.internal-dept-btn').removeClass('active');
    $('.internal-dept-btn[data-dept-index="' + deptIndex + '"]').addClass('active');

    // Update chart display
    $('.internal-dept-chart-wrapper').removeClass('active');
    $('.internal-dept-chart-wrapper[data-dept-index="' + deptIndex + '"]').addClass('active');

    // If this department's chart hasn't been initialized, initialize it
    if (internalOrgData && !initializedCharts[deptIndex] && internalOrgData[deptIndex]) {
        initializeDeptChart(deptIndex, internalOrgData[deptIndex]);
        initializedCharts[deptIndex] = true;
    }
}

function initializeDeptChart(deptIndex, deptData) {
    const chartId = '#internal-dept-chart-' + deptIndex;

    $(chartId).orgchart({
        'data': deptData,
        'nodeContent': 'title',
        'nodeId': 'id',
        'pan': false,
        'zoom': false,
        'toggleSiblingsResp': true,
        'createNode': function ($node, data) {
            const level = data.level || '';
            $node.addClass('level-' + level);

            const title = data.title || '—';
            const name = data.name || '—';

            $node.html(
                '<div class="orgchart-node-title">' + title + '</div>' +
                '<div class="orgchart-node-content">' + name + '</div>'
            );
        },
        'draggable': false,
        'direction': 't2b'
    });

    // Center the chart
    setTimeout(function () {
        const orgchartEl = $(chartId + ' .orgchart');
        if (orgchartEl.length) {
            const containerWidth = $(chartId).width();
            const chartWidth = orgchartEl.outerWidth();
            if (chartWidth < containerWidth) {
                const offsetLeft = (containerWidth - chartWidth) / 2;
                orgchartEl.css('margin-left', offsetLeft + 'px');
            }
        }
    }, 100);
}

function initializeInternalOrgCharts() {
    if (!internalOrgData || !Array.isArray(internalOrgData) || internalOrgData.length === 0) {
        return;
    }

    // Initialize the first department chart
    if (internalOrgData[0]) {
        initializeDeptChart(0, internalOrgData[0]);
        initializedCharts[0] = true;
    }
}

// Strategic Objectives Functions
function selectStrategy(index) {
    if (!formattedStrategiesData || !Array.isArray(formattedStrategiesData)) return;

    const strategy = formattedStrategiesData[index];
    if (!strategy) return;

    // Update card state
    document.querySelectorAll('.strategy-card').forEach((card, i) => {
        if (i === index) {
            card.classList.add('active');
            const checkEl = card.querySelector('.strategy-check');
            if (checkEl) checkEl.style.display = 'block';
        } else {
            card.classList.remove('active');
            const checkEl = card.querySelector('.strategy-check');
            if (checkEl) checkEl.style.display = 'none';
        }
    });

    // Update detail view
    const detailsEl = document.getElementById('strategicDetails');
    if (!detailsEl) return;

    detailsEl.classList.add('hidden');

    setTimeout(() => {
        // Update content
        const titleEl = document.getElementById('detailsTitle');
        const badgeEl = document.getElementById('detailsBadge');
        const picEl = document.getElementById('picName');

        if (titleEl) titleEl.textContent = strategy.strategy;
        if (badgeEl) badgeEl.textContent = strategy.deptDisplay || strategy.deptName || 'Selected Pillar';
        if (picEl) picEl.textContent = strategy.pic || '—';

        // Format dates
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

        const startDateEl = document.getElementById('startDate');
        const endDateEl = document.getElementById('endDate');

        if (startDateEl) startDateEl.textContent = formatDate(strategy.startDate);
        if (endDateEl) endDateEl.textContent = formatDate(strategy.endDate);

        // Update metrics
        const metricsList = document.getElementById('measureList');
        if (metricsList) {
            if (strategy.dashboardMetrics && strategy.dashboardMetrics.length > 0) {
                metricsList.innerHTML = strategy.dashboardMetrics.map(metric =>
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

function initializeStrategicObjectives() {
    // Initialize animation
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

    // Update strategy count
    const countEl = document.getElementById('strategicListCount');
    if (countEl && formattedStrategiesData) {
        countEl.textContent = formattedStrategiesData.length.toString();
    }
}
