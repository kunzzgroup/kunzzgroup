function isCorporateBlueprintEditReactV2Page() {
    return /corporate_blueprint_edit-v2/.test(window.location.pathname || '');
}

function getIndexVar(name) {
    return window[name] ?? 0;
}

function setIndexVar(name, value) {
    window[name] = value;
}

// 表单提交处理
function handleFormSubmit(event) {
    console.log('表单提交事件触发');
    const form = event.target;

    // 显示加载状态
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '保存中...';

        // 如果提交失败，恢复按钮状态（5秒后）
        setTimeout(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }, 5000);
    }

    // 计算表单字段数量（用于调试）
    const inputs = form.querySelectorAll('input, textarea, select');
    console.log('表单字段总数:', inputs.length);

    return true; // 允许表单正常提交
}

// 页面加载时检查
function bootCorporateBlueprintEdit() {
    console.log('企业蓝图编辑页面初始化');
    const form = document.getElementById('corporate-form');
    if (form) {
        console.log('表单元素找到');
        form.addEventListener('submit', function () {
            console.log('表单提交监听器触发');
        });
    } else {
        console.error('未找到表单元素');
    }
}

window.bootCorporateBlueprintEdit = bootCorporateBlueprintEdit;
window.reinitCorporateBlueprintEdit = bootCorporateBlueprintEdit;
window.handleFormSubmit = handleFormSubmit;
window.switchTab = switchTab;
window.addCLevel = addCLevel;
window.removeCLevel = removeCLevel;
window.addDepartment = addDepartment;
window.removeDepartment = removeDepartment;
window.addPosition = addPosition;
window.removePosition = removePosition;
window.addTimeline = addTimeline;
window.removeTimeline = removeTimeline;
window.addCulture = addCulture;
window.removeCulture = removeCulture;
window.addValue = addValue;
window.removeValue = removeValue;
window.addCultureExplanation = addCultureExplanation;
window.removeCultureExplanation = removeCultureExplanation;
window.addValuesExplanation = addValuesExplanation;
window.removeValuesExplanation = removeValuesExplanation;
window.addYear = addYear;
window.removeYear = removeYear;
window.addObjective = addObjective;
window.removeObjective = removeObjective;

if (!isCorporateBlueprintEditReactV2Page()) {
    document.addEventListener('DOMContentLoaded', bootCorporateBlueprintEdit);
}

// 标签切换函数
function switchTab(tabName, btnElement) {
    // 隐藏所有section
    document.querySelectorAll('.tab-section').forEach(section => {
        section.classList.remove('active');
    });

    // 移除所有按钮的active类
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // 显示选中的section
    const targetSection = document.querySelector(`.tab-section[data-tab="${tabName}"]`);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    // 添加按钮的active类
    if (btnElement) {
        btnElement.classList.add('active');
    }

    // 滚动到顶部（平滑滚动）
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function addCLevel() {
    const container = document.getElementById('clevel-container');
    const clevelIndex = getIndexVar('clevelIndex');
    const html = `
        <div class="clevel-item">
            <div class="form-row">
                <div class="form-group">
                    <label>姓名</label>
                    <input type="text" name="clevel[${clevelIndex}][name]" value="">
                </div>
                <div class="form-group">
                    <label>职位</label>
                    <input type="text" name="clevel[${clevelIndex}][title]" value="">
                </div>
            </div>
            <div class="form-group">
                <label>完整职位名称</label>
                <input type="text" name="clevel[${clevelIndex}][fullTitle]" value="">
            </div>
            <div class="form-group">
                <label>汇报对象</label>
                <input type="text" name="clevel[${clevelIndex}][reportsTo]" value="CEO">
            </div>
            <button type="button" class="remove-btn" onclick="removeCLevel(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    setIndexVar('clevelIndex', getIndexVar('clevelIndex') + 1);
}

function removeCLevel(btn) {
    if (confirm('确定要删除这个C-Level高管吗？')) {
        btn.closest('.clevel-item').remove();
    }
}

function addDepartment() {
    const container = document.getElementById('departments-container');
    const deptIndex = getIndexVar('deptIndex');
    const html = `
        <div class="department-item">
            <div class="form-group">
                <label>部门名称</label>
                <input type="text" name="departments[${deptIndex}][name]" value="">
            </div>
            <div class="positions-container">
                <h3>职位列表</h3>
                <div class="position-item">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>职位</label>
                        <input type="text" name="departments[${deptIndex}][positions][0][title]" value="" placeholder="职位名称">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>姓名</label>
                        <input type="text" name="departments[${deptIndex}][positions][0][name]" value="" placeholder="人员姓名">
                    </div>
                    <button type="button" class="remove-btn" onclick="removePosition(this)">删除</button>
                </div>
            </div>
            <button type="button" class="add-btn" onclick="addPosition(this)">添加职位</button>
            <button type="button" class="remove-btn" onclick="removeDepartment(this)" style="margin-left: 10px;">删除部门</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    setIndexVar('deptIndex', getIndexVar('deptIndex') + 1);
}

function removeDepartment(btn) {
    if (confirm('确定要删除这个部门吗？所有职位也将被删除。')) {
        btn.closest('.department-item').remove();
    }
}

function addPosition(btn) {
    const departmentItem = btn.closest('.department-item');
    const deptIndexAttr = departmentItem.querySelector('input[name*="[name]"]').name.match(/departments\[(\d+)\]/)[1];
    const positionsContainer = departmentItem.querySelector('.positions-container');
    const existingPositions = positionsContainer.querySelectorAll('.position-item');
    const posIndex = existingPositions.length;

    const html = `
        <div class="position-item">
            <div class="form-group" style="margin-bottom: 0;">
                <label>职位</label>
                <input type="text" name="departments[${deptIndexAttr}][positions][${posIndex}][title]" value="" placeholder="职位名称">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>姓名</label>
                <input type="text" name="departments[${deptIndexAttr}][positions][${posIndex}][name]" value="" placeholder="人员姓名">
            </div>
            <button type="button" class="remove-btn" onclick="removePosition(this)">删除</button>
        </div>
    `;
    positionsContainer.querySelector('h3').insertAdjacentHTML('afterend', html);
}

function removePosition(btn) {
    if (confirm('确定要删除这个职位吗？')) {
        btn.closest('.position-item').remove();
    }
}

// Timeline functions
function addTimeline() {
    const container = document.getElementById('timeline-container');
    const timelineIndex = getIndexVar('timelineIndex');
    const html = `
        <div class="timeline-item">
            <div class="form-group" style="margin-bottom: 0;">
                <label>年份</label>
                <input type="number" name="timeline[${timelineIndex}][year]" value="" placeholder="2024">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>目标</label>
                <input type="text" name="timeline[${timelineIndex}][goal]" value="" placeholder="创建X间子公司">
            </div>
            <button type="button" class="remove-btn" onclick="removeTimeline(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    setIndexVar('timelineIndex', timelineIndex + 1);
}

function removeTimeline(btn) {
    if (confirm('确定要删除这个时间线项目吗？')) {
        btn.closest('.timeline-item').remove();
    }
}

// Culture functions
function addCulture() {
    const container = document.getElementById('culture-container');
    const cultureIndex = getIndexVar('cultureIndex');
    const html = `
        <div class="culture-item">
            <div class="form-group" style="margin-bottom: 0;">
                <label>文化项</label>
                <input type="text" name="culture[${cultureIndex}]" value="" placeholder="例如：Innovation">
            </div>
            <button type="button" class="remove-btn" onclick="removeCulture(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    setIndexVar('cultureIndex', cultureIndex + 1);
}

function removeCulture(btn) {
    if (confirm('确定要删除这个文化项吗？')) {
        btn.closest('.culture-item').remove();
    }
}

// Values functions
function addValue() {
    const container = document.getElementById('values-container');
    const valuesIndex = getIndexVar('valuesIndex');
    const html = `
        <div class="values-item">
            <div class="form-group" style="margin-bottom: 0;">
                <label>价值观</label>
                <input type="text" name="values[${valuesIndex}]" value="" placeholder="例如：Customer First">
            </div>
            <button type="button" class="remove-btn" onclick="removeValue(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    setIndexVar('valuesIndex', valuesIndex + 1);
}

function removeValue(btn) {
    if (confirm('确定要删除这个价值观吗？')) {
        btn.closest('.values-item').remove();
    }
}

// Culture Explanation functions
function addCultureExplanation() {
    const container = document.getElementById('culture-explanation-container');
    const cultureExplanationIndex = getIndexVar('cultureExplanationIndex');
    let scoringHtml = '';
    for (let i = 1; i <= 5; i++) {
        scoringHtml += `
            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 10px; align-items: center;">
                <label style="margin: 0; font-weight: 600;">${i}分:</label>
                <input type="text" name="cultureExplanation[${cultureExplanationIndex}][scoring][${i - 1}][description]" value="" placeholder="评分描述">
                <input type="hidden" name="cultureExplanation[${cultureExplanationIndex}][scoring][${i - 1}][point]" value="${i}">
            </div>
        `;
    }
    const html = `
        <div class="culture-explanation-item">
            <div class="form-group">
                <label>关键词 (Key)</label>
                <input type="text" name="cultureExplanation[${cultureExplanationIndex}][key]" value="" placeholder="例如：积极向上">
            </div>
            <div class="form-group">
                <label>描述 (Description)</label>
                <textarea name="cultureExplanation[${cultureExplanationIndex}][description]" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>评分标准</label>
                ${scoringHtml}
            </div>
            <button type="button" class="remove-btn" onclick="removeCultureExplanation(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    setIndexVar('cultureExplanationIndex', cultureExplanationIndex + 1);
}

function removeCultureExplanation(btn) {
    if (confirm('确定要删除这个文化解释吗？')) {
        btn.closest('.culture-explanation-item').remove();
    }
}

// Values Explanation functions
function addValuesExplanation() {
    const container = document.getElementById('values-explanation-container');
    const valuesExplanationIndex = getIndexVar('valuesExplanationIndex');
    let scoringHtml = '';
    for (let i = 1; i <= 5; i++) {
        scoringHtml += `
            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 10px; align-items: center;">
                <label style="margin: 0; font-weight: 600;">${i}分:</label>
                <input type="text" name="valuesExplanation[${valuesExplanationIndex}][scoring][${i - 1}][description]" value="" placeholder="评分描述">
                <input type="hidden" name="valuesExplanation[${valuesExplanationIndex}][scoring][${i - 1}][point]" value="${i}">
            </div>
        `;
    }
    const html = `
        <div class="values-explanation-item">
            <div class="form-group">
                <label>关键词 (Key)</label>
                <input type="text" name="valuesExplanation[${valuesExplanationIndex}][key]" value="" placeholder="例如：目标导向">
            </div>
            <div class="form-group">
                <label>描述 (Description)</label>
                <textarea name="valuesExplanation[${valuesExplanationIndex}][description]" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>评分标准</label>
                ${scoringHtml}
            </div>
            <button type="button" class="remove-btn" onclick="removeValuesExplanation(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    setIndexVar('valuesExplanationIndex', valuesExplanationIndex + 1);
}

function removeValuesExplanation(btn) {
    if (confirm('确定要删除这个价值观解释吗？')) {
        btn.closest('.values-explanation-item').remove();
    }
}

// Strategic Objectives functions
function addYear() {
    const year = prompt('请输入年份（例如：2024）:');
    if (!year) return;

    const container = document.getElementById('strategic-objectives-container');
    const html = `
        <div class="year-objectives">
            <h3>${year}年</h3>
            <div class="objectives-list" data-year="${year}">
            </div>
            <button type="button" class="add-btn" onclick="addObjective('${year}')">添加${year}年目标</button>
            <button type="button" class="remove-btn" onclick="removeYear('${year}')" style="margin-left: 10px;">删除${year}年</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    addObjective(year);
}

function removeYear(year) {
    if (confirm(`确定要删除${year}年的所有目标吗？`)) {
        const yearDivs = document.querySelectorAll('.year-objectives');
        yearDivs.forEach(div => {
            const h3 = div.querySelector('h3');
            if (h3 && h3.textContent.includes(year)) {
                div.remove();
            }
        });
    }
}

function addObjective(year) {
    const yearDivs = document.querySelectorAll('.year-objectives');
    let yearDiv = null;
    yearDivs.forEach(div => {
        const h3 = div.querySelector('h3');
        if (h3 && h3.textContent.includes(year)) {
            yearDiv = div;
        }
    });
    if (!yearDiv) return;

    const objectivesList = yearDiv.querySelector('.objectives-list');
    const objIndex = objectivesList.querySelectorAll('.objective-item').length;

    const html = `
        <div class="objective-item">
            <div class="form-row">
                <div class="form-group">
                    <label>部门</label>
                    <input type="text" name="strategicObjectives[${year}][${objIndex}][department]" value="" placeholder="例如：Technology">
                </div>
                <div class="form-group">
                    <label>负责人 (PIC)</label>
                    <input type="text" name="strategicObjectives[${year}][${objIndex}][pic]" value="" placeholder="例如：CTO">
                </div>
            </div>
            <div class="form-group">
                <label>策略</label>
                <textarea name="strategicObjectives[${year}][${objIndex}][strategy]" rows="2"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>开始日期</label>
                    <input type="date" name="strategicObjectives[${year}][${objIndex}][startDate]" value="">
                </div>
                <div class="form-group">
                    <label>结束日期</label>
                    <input type="date" name="strategicObjectives[${year}][${objIndex}][endDate]" value="">
                </div>
            </div>
            <div class="form-group">
                <label>仪表板指标 (每行一个)</label>
                <textarea name="strategicObjectives[${year}][${objIndex}][dashboardMetrics]" rows="3" placeholder="System Uptime (%)
Infrastructure Cost Reduction (%)
Implementation Timeline Adherence (%)"></textarea>
                <small style="color: #666;">每行一个指标</small>
            </div>
            <button type="button" class="remove-btn" onclick="removeObjective(this, '${year}')">删除</button>
        </div>
    `;
    objectivesList.insertAdjacentHTML('beforeend', html);
}

function removeObjective(btn, year) {
    if (confirm('确定要删除这个目标吗？')) {
        btn.closest('.objective-item').remove();
    }
}
