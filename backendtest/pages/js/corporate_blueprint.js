
/**
 * Corporate Blueprint JS
 * Handles view loading and client-side rendering of strategy data.
 */

async function loadView() {
    try {
        console.log("Loading Corporate Blueprint view...");
        const response = await fetch(window.BASE_URL + "pages/html/" + window.PAGE_NAME + ".html", {
            credentials: "same-origin",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });
        if (!response.ok) throw new Error("Failed to load view");
        const html = await response.text();
        document.getElementById("app").innerHTML = html;

        // After loading HTML, initialize dynamic content
        initializeDynamicContent();
    } catch (error) {
        console.error("Error loading view:", error);
    }
}

function initializeDynamicContent() {
    if (!window.STRATEGY_DATA) {
        console.error("STRATEGY_DATA not found");
        return;
    }

    // Update company name
    const companyName = window.STRATEGY_DATA.companyOverview?.companyName || "KUNZZ HOLDINGS SDN BHD";
    const nameDisplay = document.getElementById("company-name-display");
    if (nameDisplay) nameDisplay.textContent = companyName;

    // Render sections
    renderTimeline();
    renderCoreSection();
    renderCultureExplanation();
    renderValuesExplanation();
    initOrgCharts();
    renderStrategicObjectives();

    // Final UI adjustments
    alignScoringSections();
    initAnimations();

    console.log("Dynamic content initialization complete.");
}

function renderTimeline() {
    const section = document.getElementById('timeline-section');
    const listContainer = document.getElementById('milestone-list');
    if (!listContainer || !window.STRATEGY_DATA.timeline) return;

    section.style.display = 'block';
    const timeline = window.STRATEGY_DATA.timeline;
    const totalItems = timeline.length;

    function bezierQuad(t, p0, p1, p2) {
        const mt = 1 - t;
        return [
            mt * mt * p0[0] + 2 * mt * t * p1[0] + t * t * p2[0],
            mt * mt * p0[1] + 2 * mt * t * p1[1] + t * t * p2[1]
        ];
    }

    const p0_1 = [15, 300], p1_1 = [180, 180], p2_1 = [300, 300];
    const p0_2 = [300, 300], p1_2 = [420, 420], p2_2 = [585, 300];

    let html = '';
    timeline.forEach((item, index) => {
        const t = totalItems > 1 ? index / (totalItems - 1) : 0;
        let point;
        if (t <= 0.5) {
            point = bezierQuad(t * 2, p0_1, p1_1, p2_1);
        } else {
            point = bezierQuad((t - 0.5) * 2, p0_2, p1_2, p2_2);
        }

        const svgWidthPercent = 83.33;
        const svgLeftOffset = (100 - svgWidthPercent) / 2;
        const xPercent = svgLeftOffset + ((point[0] / 600) * 100 * svgWidthPercent / 100);
        const yPercent = (point[1] / 600) * 100;
        const cardPosition = (index % 2 === 0) ? 'bottom' : 'top';

        html += `
            <div class="map-milestone milestone-${cardPosition}" 
                 style="left: ${xPercent}%; top: ${yPercent}%;"
                 data-year="${item.year}">
                <div class="milestone-pin"></div>
                <div class="milestone-card">
                    <div class="milestone-year">${item.year}年</div>
                    <div class="milestone-goal">${item.goal}</div>
                </div>
            </div>`;
    });

    listContainer.innerHTML = html;
}

function renderCoreSection() {
    const section = document.getElementById('core-section');
    const grid = document.getElementById('core-grid');
    if (!grid || !window.STRATEGY_DATA.corporateCore) return;

    section.style.display = 'block';
    const core = window.STRATEGY_DATA.corporateCore;
    let html = '';

    if (core.mission) {
        html += `
        <div class="core-card">
            <div class="core-card-number">01</div>
            <div class="core-card-content-wrapper">
                <div class="core-card-title">使命:初心&感性的目标</div>
                <div class="core-card-content">${core.mission.replace(/\n/g, '<br>')}</div>
            </div>
        </div>`;
    }

    if (core.vision) {
        html += `
        <div class="core-card">
            <div class="core-card-number">02</div>
            <div class="core-card-content-wrapper">
                <div class="core-card-title">愿景:理性可具体化的目标</div>
                <div class="core-card-content">${core.vision.replace(/\n/g, '<br>')}</div>
            </div>
        </div>`;
    }

    if (core.culture) {
        html += `
        <div class="core-card">
            <div class="core-card-number">03</div>
            <div class="core-card-content-wrapper">
                <div class="core-card-title">文化:做人的态度</div>
                <div class="core-card-content">${Array.isArray(core.culture) ? core.culture.join(', ') : core.culture}</div>
            </div>
        </div>`;
    }

    if (core.values) {
        html += `
        <div class="core-card">
            <div class="core-card-number">04</div>
            <div class="core-card-content-wrapper">
                <div class="core-card-title">价值观:做事的态度</div>
                <div class="core-card-content">${Array.isArray(core.values) ? core.values.join(', ') : core.values}</div>
            </div>
        </div>`;
    }

    grid.innerHTML = html;
}

function renderCultureExplanation() {
    const section = document.getElementById('culture-explanation-section');
    const list = document.getElementById('culture-explanation-list');
    if (!list || !window.STRATEGY_DATA.cultureExplanation) return;

    section.style.display = 'block';
    const explanations = window.STRATEGY_DATA.cultureExplanation;
    list.innerHTML = explanations.map((exp, index) => renderExplanationCard(exp, index)).join('');
}

function renderValuesExplanation() {
    const section = document.getElementById('values-explanation-section');
    const list = document.getElementById('values-explanation-list');
    if (!list || !window.STRATEGY_DATA.valuesExplanation) return;

    section.style.display = 'block';
    const explanations = window.STRATEGY_DATA.valuesExplanation;
    list.innerHTML = explanations.map((exp, index) => renderExplanationCard(exp, index)).join('');
}

function renderExplanationCard(exp, index) {
    const number = String(index + 1).padStart(2, '0');
    let scoringHtml = '';

    if (exp.scoring && Array.isArray(exp.scoring)) {
        const sortedScoring = [...exp.scoring].sort((a, b) => (a.point || 0) - (b.point || 0));
        scoringHtml = `
            <div class="culture-scoring">
                <div class="culture-scoring-title">评分标准:</div>
                ${sortedScoring.map(s => `
                    <div class="culture-scoring-item">
                        <div class="culture-scoring-point">${s.point}分:</div>
                        <div class="culture-scoring-description">${s.description}</div>
                    </div>
                `).join('')}
            </div>`;
    }

    return `
        <div class="culture-explanation-card">
            <div class="culture-explanation-content">
                <div class="culture-explanation-number">${number}</div>
                <div class="culture-explanation-key">${exp.key || ''}</div>
                <div class="culture-explanation-description">
                    ${(exp.description || '').replace(/\n/g, '<br>')}
                </div>
            </div>
            ${scoringHtml}
        </div>`;
}

function initOrgCharts() {
    const orgSection = document.getElementById('org-section');
    if (orgSection && window.STRATEGY_DATA.organizationStructure) {
        orgSection.style.display = 'block';
        const orgData = convertToOrgChartFormat(window.STRATEGY_DATA.organizationStructure);
        if (orgData) {
            $('#orgchart-container').orgchart({
                'data': orgData,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': false,
                'zoom': false,
                'toggleSiblingsResp': true,
                'createNode': function ($node, data) {
                    $node.addClass('level-' + (data.level || ''));
                    $node.html(`
                        <div class="orgchart-node-title">${data.title || '—'}</div>
                        <div class="orgchart-node-content">${data.name || '—'}</div>
                    `);
                },
                'draggable': false,
                'direction': 't2b'
            });
        }
    }

    const internalOrgSection = document.getElementById('internal-org-section');
    if (internalOrgSection && window.STRATEGY_DATA.internalOrganization) {
        internalOrgSection.style.display = 'block';
        renderInternalOrgChart();
    }
}

function convertToOrgChartFormat(orgStructure) {
    if (!orgStructure || !orgStructure.ceo) return null;
    const ceoNode = {
        'id': 'ceo',
        'name': orgStructure.ceo.name || '—',
        'title': orgStructure.ceo.title || orgStructure.ceo.fullTitle || 'CEO',
        'level': 'ceo',
        'children': []
    };
    if (orgStructure.cLevel) {
        orgStructure.cLevel.forEach((member, index) => {
            const cNode = {
                'id': 'clevel_' + index,
                'name': member.name || '—',
                'title': member.title || member.fullTitle || '',
                'level': 'clevel',
                'children': []
            };
            if (member.subordinates) {
                member.subordinates.forEach((sub, subIndex) => {
                    cNode.children.push({
                        'id': 'sub_' + index + '_' + subIndex,
                        'name': sub.name || '—',
                        'title': sub.title || sub.fullTitle || '',
                        'level': 'subordinate'
                    });
                });
            }
            ceoNode.children.push(cNode);
        });
    }
    if (orgStructure.pa) {
        ceoNode.children.push({
            'id': 'pa',
            'name': orgStructure.pa.name || '—',
            'title': orgStructure.pa.title || orgStructure.pa.fullTitle || 'PA',
            'level': 'pa'
        });
    }
    return ceoNode;
}

function renderInternalOrgChart() {
    const internalOrgData = window.STRATEGY_DATA.internalOrganization;
    if (!internalOrgData || !internalOrgData.departments) return;

    const buttonContainer = document.getElementById('internal-dept-buttons');
    const chartContainer = document.getElementById('internal-orgchart-container');

    const internalOrgChartData = convertInternalOrgToOrgChartFormat(internalOrgData);
    window.internalOrgChartData = internalOrgChartData;
    window.initializedCharts = {};

    buttonContainer.innerHTML = internalOrgChartData.map((dept, index) => `
        <button class="internal-dept-btn ${index === 0 ? 'active' : ''}" 
                data-dept-index="${index}"
                onclick="switchInternalDept(${index})">
            ${dept.departmentName}
        </button>
    `).join('');

    chartContainer.innerHTML = internalOrgChartData.map((dept, index) => `
        <div class="internal-dept-chart-wrapper ${index === 0 ? 'active' : ''}" data-dept-index="${index}">
            <div class="internal-dept-orgchart" id="internal-dept-chart-${index}" style="width: 100%; min-height: 500px;"></div>
        </div>
    `).join('');

    if (internalOrgChartData.length > 0) {
        setTimeout(() => switchInternalDept(0), 100);
    }
}

function convertInternalOrgToOrgChartFormat(internalOrgData) {
    return internalOrgData.departments.map((dept, deptIndex) => {
        const positions = dept.positions || [];
        if (positions.length === 0) return null;

        const first = positions[0];
        const root = {
            'id': 'dept_' + deptIndex,
            'name': first.name || '—',
            'title': first.title || dept.name,
            'level': 'department',
            'departmentName': dept.name,
            'children': []
        };

        for (let i = 1; i < positions.length; i++) {
            root.children.push({
                'id': 'dept_' + deptIndex + '_pos_' + i,
                'name': positions[i].name || '—',
                'title': positions[i].title || '',
                'level': 'position'
            });
        }
        return root;
    }).filter(Boolean);
}

window.switchInternalDept = function (deptIndex) {
    $('.internal-dept-btn').removeClass('active');
    $(`.internal-dept-btn[data-dept-index="${deptIndex}"]`).addClass('active');

    $('.internal-dept-chart-wrapper').removeClass('active');
    $(`.internal-dept-chart-wrapper[data-dept-index="${deptIndex}"]`).addClass('active');

    if (!window.initializedCharts[deptIndex] && window.internalOrgChartData[deptIndex]) {
        initializeInternalDeptChart(deptIndex, window.internalOrgChartData[deptIndex]);
    }
};

function initializeInternalDeptChart(index, deptTree) {
    $(`#internal-dept-chart-${index}`).orgchart({
        'data': deptTree,
        'nodeContent': 'title',
        'nodeId': 'id',
        'pan': false,
        'zoom': false,
        'toggleSiblingsResp': true,
        'createNode': function ($node, data) {
            $node.addClass('level-' + (data.level || ''));
            $node.html(`
                <div class="orgchart-node-title">${data.title || '—'}</div>
                <div class="orgchart-node-content">${data.name || '—'}</div>
            `);
        },
        'draggable': false,
        'direction': 't2b'
    });
    window.initializedCharts[index] = true;
}

function renderStrategicObjectives() {
    const section = document.getElementById('strategic-objectives-section');
    const listContainer = document.getElementById('strategicList');
    if (!listContainer || !window.STRATEGY_DATA.strategicObjectives) return;

    section.style.display = 'block';

    // Set ultimate goal header
    const strategyEndYear = window.STRATEGY_DATA.companyOverview?.strategyEndYear || (new Date().getFullYear() + 5);
    const ultimateGoal = window.STRATEGY_DATA.companyOverview?.ultimateGoal || "";
    document.getElementById("strategy-end-year").textContent = strategyEndYear;
    document.getElementById("ultimate-goal-text").textContent = ultimateGoal;

    const objectives = [];
    for (const year in window.STRATEGY_DATA.strategicObjectives) {
        window.STRATEGY_DATA.strategicObjectives[year].forEach(obj => {
            objectives.push({ ...obj, year });
        });
    }

    window.formattedStrategiesData = objectives.map((obj, index) => ({
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

    listContainer.innerHTML = window.formattedStrategiesData.map((obj, index) => `
        <button class="strategy-card ${index === 0 ? 'active' : ''}" 
                data-strategy-index="${index}"
                onclick="selectStrategy(${index})">
            <div class="strategy-icon-wrapper">
                <svg class="strategy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="strategy-content">
                <div class="strategy-meta">
                    <span class="strategy-id">${obj.deptName} • ${obj.year}</span>
                    <svg class="strategy-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="${index === 0 ? '' : 'display: none;'}">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <h3 class="strategy-title">${obj.strategy}</h3>
                <p class="strategy-description">${obj.department}</p>
            </div>
            <svg class="strategy-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>`).join('');

    const countEl = document.getElementById('strategicListCount');
    if (countEl) countEl.textContent = window.formattedStrategiesData.length;

    if (window.formattedStrategiesData.length > 0) {
        selectStrategy(0);
    }
}

window.selectStrategy = function (index) {
    const strategy = window.formattedStrategiesData[index];
    if (!strategy) return;

    document.querySelectorAll('.strategy-card').forEach((card, i) => {
        if (i === index) {
            card.classList.add('active');
            card.querySelector('.strategy-check').style.display = 'block';
        } else {
            card.classList.remove('active');
            card.querySelector('.strategy-check').style.display = 'none';
        }
    });

    const detailsEl = document.getElementById('strategicDetails');
    detailsEl.classList.add('hidden');

    setTimeout(() => {
        document.getElementById('detailsTitle').textContent = strategy.strategy;
        document.getElementById('detailsBadge').textContent = strategy.deptDisplay || strategy.deptName || 'Selected Pillar';
        document.getElementById('picName').textContent = strategy.pic || '—';

        const formatDate = (dateStr) => {
            if (!dateStr) return '—';
            try {
                const date = new Date(dateStr);
                return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            } catch (e) { return dateStr; }
        };

        document.getElementById('startDate').textContent = formatDate(strategy.startDate);
        document.getElementById('endDate').textContent = formatDate(strategy.endDate);

        const metricsList = document.getElementById('measureList');
        if (metricsList) {
            if (strategy.dashboardMetrics && strategy.dashboardMetrics.length > 0) {
                metricsList.innerHTML = strategy.dashboardMetrics.map(metric => `
                    <li class="measure-list-item">
                        <div class="measure-dot"></div>
                        <span class="measure-text">${metric}</span>
                    </li>
                `).join('');
            } else {
                metricsList.innerHTML = '<li class="measure-list-item"><span class="measure-text">暂无指标</span></li>';
            }
        }
        detailsEl.classList.remove('hidden');
    }, 300);
};

function initAnimations() {
    const timelineWrapper = document.querySelector('.timeline-wrapper');
    if (!timelineWrapper) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateTimeline(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3, rootMargin: '0px 0px -100px 0px' });

    observer.observe(timelineWrapper);

    function animateTimeline(container) {
        const routePath = container.querySelector('.map-route-path');
        if (routePath) {
            setTimeout(() => routePath.classList.add('animate-in'), 200);
        }

        const milestones = container.querySelectorAll('.map-milestone');
        milestones.forEach((milestone, index) => {
            setTimeout(() => milestone.classList.add('animate-in'), 1000 + (index * 200));
        });
    }

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
}

function alignScoringSections() {
    document.querySelectorAll('.culture-explanation-grid').forEach(grid => {
        const cards = grid.querySelectorAll('.culture-explanation-card');
        if (cards.length === 0) return;

        cards.forEach(card => {
            const content = card.querySelector('.culture-explanation-content');
            if (content) content.style.minHeight = 'auto';
        });

        void grid.offsetHeight;

        let maxHeight = 0;
        cards.forEach(card => {
            const content = card.querySelector('.culture-explanation-content');
            if (content) {
                const height = content.offsetHeight;
                if (height > maxHeight) maxHeight = height;
            }
        });

        cards.forEach(card => {
            const content = card.querySelector('.culture-explanation-content');
            if (content) content.style.minHeight = maxHeight + 'px';
        });
    });
}

// Global start
document.addEventListener('DOMContentLoaded', loadView);
