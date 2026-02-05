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

// C-Level functions
function addCLevel() {
    const container = document.getElementById('clevel-container');
    const index = container.querySelectorAll('.clevel-item').length;
    const html = `
        <div class="clevel-item">
            <div class="form-row">
                <div class="form-group">
                    <label>姓名</label>
                    <input type="text" name="clevel[${index}][name]" value="">
                </div>
                <div class="form-group">
                    <label>职位</label>
                    <input type="text" name="clevel[${index}][title]" value="">
                </div>
            </div>
            <div class="form-group">
                <label>完整职位名称</label>
                <input type="text" name="clevel[${index}][fullTitle]" value="">
            </div>
            <div class="form-group">
                <label>汇报对象</label>
                <input type="text" name="clevel[${index}][reportsTo]" value="CEO">
            </div>
            <button type="button" class="remove-btn" onclick="removeCLevel(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeCLevel(btn) {
    if (confirm('确定要删除这个C-Level高管吗？')) {
        btn.closest('.clevel-item').remove();
        reindexCLevel();
    }
}

function reindexCLevel() {
    document.querySelectorAll('#clevel-container .clevel-item').forEach((item, index) => {
        item.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/clevel\[\d+\]/, `clevel[${index}]`);
        });
    });
}

// Internal Organization functions
function addDepartment() {
    const container = document.getElementById('departments-container');
    const index = container.querySelectorAll('.department-item').length;
    const html = `
        <div class="department-item">
            <div class="form-group">
                <label>部门名称</label>
                <input type="text" name="departments[${index}][name]" value="">
            </div>
            <div class="positions-container">
                <h3>职位列表</h3>
                <div class="position-item">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>职位</label>
                        <input type="text" name="departments[${index}][positions][0][title]" value="" placeholder="职位名称">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>姓名</label>
                        <input type="text" name="departments[${index}][positions][0][name]" value="" placeholder="人员姓名">
                    </div>
                    <button type="button" class="remove-btn" onclick="removePosition(this)">删除</button>
                </div>
            </div>
            <button type="button" class="add-btn" onclick="addPosition(this)">添加职位</button>
            <button type="button" class="remove-btn" onclick="removeDepartment(this)" style="margin-left: 10px;">删除部门</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeDepartment(btn) {
    if (confirm('确定要删除这个部门吗？所有职位也将被删除。')) {
        btn.closest('.department-item').remove();
        reindexDepartments();
    }
}

function reindexDepartments() {
    document.querySelectorAll('#departments-container .department-item').forEach((item, dIndex) => {
        item.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/departments\[\d+\]/, `departments[${dIndex}]`);
        });
    });
}

function addPosition(btn) {
    const departmentItem = btn.closest('.department-item');
    const deptMatch = departmentItem.querySelector('input[name*="[name]"]').name.match(/departments\[(\d+)\]/);
    if (!deptMatch) return;
    const deptIndexAttr = deptMatch[1];
    const positionsContainer = departmentItem.querySelector('.positions-container');
    const posIndex = positionsContainer.querySelectorAll('.position-item').length;

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
    positionsContainer.insertAdjacentHTML('beforeend', html);
}

function removePosition(btn) {
    if (confirm('确定要删除这个职位吗？')) {
        const pContainer = btn.closest('.positions-container');
        btn.closest('.position-item').remove();
        reindexPositions(pContainer);
    }
}

function reindexPositions(container) {
    const deptMatch = container.closest('.department-item').querySelector('input[name*="[name]"]').name.match(/departments\[(\d+)\]/);
    if (!deptMatch) return;
    const dIndex = deptMatch[1];
    container.querySelectorAll('.position-item').forEach((item, pIndex) => {
        item.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/positions\[\d+\]/, `positions[${pIndex}]`);
        });
    });
}

// Timeline functions
function addTimeline() {
    const container = document.getElementById('timeline-container');
    const index = container.querySelectorAll('.timeline-item').length;
    const html = `
        <div class="timeline-item">
            <div class="form-group" style="margin-bottom: 0;">
                <label>年份</label>
                <input type="number" name="timeline[${index}][year]" value="" placeholder="2024">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>目标</label>
                <input type="text" name="timeline[${index}][goal]" value="" placeholder="创建X间子公司">
            </div>
            <button type="button" class="remove-btn" onclick="removeTimeline(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeTimeline(btn) {
    if (confirm('确定要删除这个时间线项目吗？')) {
        btn.closest('.timeline-item').remove();
        reindexTimeline();
    }
}

function reindexTimeline() {
    document.querySelectorAll('#timeline-container .timeline-item').forEach((item, index) => {
        item.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/timeline\[\d+\]/, `timeline[${index}]`);
        });
    });
}

// Culture functions
function addCulture() {
    const container = document.getElementById('culture-container');
    const index = container.querySelectorAll('.culture-item').length;
    const html = `
        <div class="culture-item">
            <div class="form-group" style="margin-bottom: 0;">
                <label>文化项</label>
                <input type="text" name="culture[${index}]" value="" placeholder="例如：Innovation">
            </div>
            <button type="button" class="remove-btn" onclick="removeCulture(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeCulture(btn) {
    if (confirm('确定要删除这个文化项吗？')) {
        btn.closest('.culture-item').remove();
        reindexCulture();
    }
}

function reindexCulture() {
    document.querySelectorAll('#culture-container .culture-item').forEach((item, index) => {
        item.querySelector('input').name = `culture[${index}]`;
    });
}

// Values functions
function addValue() {
    const container = document.getElementById('values-container');
    const index = container.querySelectorAll('.values-item').length;
    const html = `
        <div class="values-item">
            <div class="form-group" style="margin-bottom: 0;">
                <label>价值观</label>
                <input type="text" name="values[${index}]" value="" placeholder="例如：Customer First">
            </div>
            <button type="button" class="remove-btn" onclick="removeValue(this)">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeValue(btn) {
    if (confirm('确定要删除这个价值观吗？')) {
        btn.closest('.values-item').remove();
        reindexValues();
    }
}

function reindexValues() {
    document.querySelectorAll('#values-container .values-item').forEach((item, index) => {
        item.querySelector('input').name = `values[${index}]`;
    });
}

// Explanation functions (Culture/Values)
function addExplanation(type) {
    const containerId = type === 'culture' ? 'culture-explanation-container' : 'values-explanation-container';
    const container = document.getElementById(containerId);
    const index = container.querySelectorAll(`.${type}-explanation-item`).length;
    const namePrefix = type === 'culture' ? 'cultureExplanation' : 'valuesExplanation';

    let scoringHtml = '';
    for (let i = 1; i <= 5; i++) {
        scoringHtml += `
            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 10px; align-items: center;">
                <label style="margin: 0; font-weight: 600;">${i}分:</label>
                <input type="text" name="${namePrefix}[${index}][scoring][${i - 1}][description]" value="" placeholder="评分描述">
                <input type="hidden" name="${namePrefix}[${index}][scoring][${i - 1}][point]" value="${i}">
            </div>
        `;
    }
    const html = `
        <div class="${type}-explanation-item">
            <div class="form-group">
                <label>关键词 (Key)</label>
                <input type="text" name="${namePrefix}[${index}][key]" value="" placeholder="例如：${type === 'culture' ? '积极向上' : '目标导向'}">
            </div>
            <div class="form-group">
                <label>描述 (Description)</label>
                <textarea name="${namePrefix}[${index}][description]" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>评分标准</label>
                ${scoringHtml}
            </div>
            <button type="button" class="remove-btn" onclick="removeExplanation(this, '${type}')">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeExplanation(btn, type) {
    if (confirm(`确定要删除这个${type === 'culture' ? '文化' : '价值观'}解释吗？`)) {
        btn.closest(`.${type}-explanation-item`).remove();
        reindexExplanations(type);
    }
}

function reindexExplanations(type) {
    const containerId = type === 'culture' ? 'culture-explanation-container' : 'values-explanation-container';
    const namePrefix = type === 'culture' ? 'cultureExplanation' : 'valuesExplanation';
    document.querySelectorAll(`#${containerId} .${type}-explanation-item`).forEach((item, index) => {
        item.querySelectorAll('input, textarea').forEach(input => {
            input.name = input.name.replace(new RegExp(`${namePrefix}\\[\\d+\\]`), `${namePrefix}[${index}]`);
        });
    });
}

// Wrapper for existing calls
function addCultureExplanation() { addExplanation('culture'); }
function removeCultureExplanation(btn) { removeExplanation(btn, 'culture'); }
function addValuesExplanation() { addExplanation('values'); }
function removeValuesExplanation(btn) { removeExplanation(btn, 'values'); }

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
            if (h3 && h3.textContent.includes(year)) div.remove();
        });
    }
}

function addObjective(year) {
    const yearDivs = document.querySelectorAll('.year-objectives');
    let yearDiv = Array.from(yearDivs).find(div => div.querySelector('h3').textContent.includes(year));
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
        const list = btn.closest('.objectives-list');
        btn.closest('.objective-item').remove();
        reindexObjectives(list, year);
    }
}

function reindexObjectives(list, year) {
    list.querySelectorAll('.objective-item').forEach((item, index) => {
        item.querySelectorAll('input, textarea').forEach(input => {
            input.name = input.name.replace(/strategicObjectives\[\d+\]\[\d+\]/, `strategicObjectives[${year}][${index}]`);
        });
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function () {
    console.log('页面加载完成');
    const form = document.getElementById('corporate-form');
    if (form) {
        console.log('表单元素找到');
        form.addEventListener('submit', function (e) {
            console.log('表单提交监听器触发');
        });
    }
});
