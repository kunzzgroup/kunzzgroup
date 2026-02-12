<?php
// 包含初始化文件
require_once dirname(__DIR__) . '/core/init.php';
// 包含会话验证
require_once CORE_PATH . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js">
    </script>    <link rel="stylesheet" href="../css/evaluation_form.css">
    <title>考核表单管理系统</title>

    <?php include CORE_PATH . '/sidebar.php'; ?>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>考核表单管理</h1>
            <div class="controls">
                <div class="toggle-standards-selector">
                    <button class="selector-button" onclick="toggleStandardsDropdown()">
                        <span id="current-mode">考核表单</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="standards-dropdown">
                        <div class="dropdown-item active" onclick="switchToFormMode()">考核表单</div>
                        <div class="dropdown-item" onclick="switchToStandardsMode()">考核标准</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="message" class="message"></div>

        <div class="content-wrapper">
            <div class="sidebar">
                <div class="form-section">
                    <label for="restaurant">餐厅</label>
                    <select id="restaurant">
                        <option value="J1">J1分店</option>
                        <option value="J2">J2分店</option>
                        <option value="J3">J3分店</option>
                    </select>
                </div>

                <div class="form-section">
                    <label for="department">部门</label>
                    <select id="department">
                        <option value="">请选择部门</option>
                        <option value="service_line">服务部门 (SERVICE LINE)</option>
                        <option value="sushi_bar">寿司吧 (SUSHI BAR)</option>
                        <option value="kitchen">厨房 (KITCHEN)</option>
                    </select>
                </div>

                <div class="form-section">
                    <label for="evaluator_name">评估人姓名</label>
                    <input type="text" id="evaluator_name" placeholder="请输入评估人姓名">
                </div>

                <div class="form-section">
                    <label for="evaluation_date">评估日期</label>
                    <input type="date" id="evaluation_date" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <button class="btn-primary" onclick="createNewForm()">
                    <i class="fas fa-plus"></i> 创建新表单
                </button>

                <div id="formButtons" style="margin-top: 100px; display: none; flex-direction: column; gap: 10px;">
                    <!-- 按钮将在这里动态显示 -->
                </div>
            </div>

            <div class="main-content" id="mainContent">
                <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                    <i class="fas fa-clipboard-list" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p>请选择或创建一个考核表单</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentFormId = null;
        let currentDepartment = '';
        let employees = [];
        let criteria = [];
        let isStandardsMode = false;

        // 考核标准数据：standards[department][criteria_order][score] = text
        const standards = {
            service_line: {},
            sushi_bar: {},
            kitchen: {}
        };

        // 部门变化时加载员工和指标
        document.getElementById('department').addEventListener('change', function() {
            const dept = this.value;
            if (dept) {
                currentDepartment = dept;
                loadEmployees(dept);
                loadCriteria(dept);
            }
        });

        // 加载员工列表
        async function loadEmployees(department) {
            try {
                const restaurant = document.getElementById('restaurant').value;
                const response = await fetch(`/backendtest/api/schedule_api.php?action=get_employees&work_area=${department}&restaurant=${restaurant}`);
                const result = await response.json();
                if (result.success) {
                    employees = result.data.filter(emp => emp.is_active === 1 || emp.is_active === '1');
                }
            } catch (error) {
                console.error('加载员工失败:', error);
                showMessage('加载员工列表失败', 'error');
            }
        }

        // 加载考核指标
        async function loadCriteria(department) {
            try {
                const response = await fetch(`/backendtest/api/evaluation_form_api.php?action=get_criteria&department=${department}`);
                const result = await response.json();
                if (result.success) {
                    criteria = result.data;
                }
            } catch (error) {
                console.error('加载指标失败:', error);
            }
        }

        // 创建新表单
        function createNewForm() {
            const department = document.getElementById('department').value;
            const restaurant = document.getElementById('restaurant').value;
            const evaluatorName = document.getElementById('evaluator_name').value.trim();
            const evaluationDate = document.getElementById('evaluation_date').value;

            if (!department) {
                showMessage('请选择部门', 'error');
                return;
            }

            if (!evaluatorName) {
                showMessage('请输入评估人姓名', 'error');
                return;
            }

            if (!evaluationDate) {
                showMessage('请选择评估日期', 'error');
                return;
            }

            if (employees.length === 0) {
                showMessage('该部门暂无员工，请先添加员工', 'error');
                return;
            }

            currentFormId = null;
            renderForm();
            // 渲染表单后更新按钮
            setTimeout(() => {
                updateSidebarButtons();
            }, 200);
        }

        // 切换下拉菜单显示
        function toggleStandardsDropdown() {
            const dropdown = document.getElementById('standards-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        }

        // 切换到表单模式
        function switchToFormMode() {
            isStandardsMode = false;
            updateDropdownSelection('考核表单', 0);
            // 显示左侧筛选区
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.remove('standards-hidden');
            }
            // 回到表单
            document.getElementById('mainContent').innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                    <i class="fas fa-clipboard-list" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p>请选择或创建一个考核表单</p>
                </div>
            `;
            closeDropdown();
        }

        // 切换到标准模式
        function switchToStandardsMode() {
            isStandardsMode = true;
            updateDropdownSelection('考核标准', 1);
            // 隐藏左侧筛选区（餐厅、部门、评估人、日期等）
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.add('standards-hidden');
            }
            showStandardsEditor();
            closeDropdown();
        }

        // 更新下拉选择状态
        function updateDropdownSelection(text, index) {
            const currentMode = document.getElementById('current-mode');
            if (currentMode) {
                currentMode.textContent = text;
            }
            
            const dropdown = document.getElementById('standards-dropdown');
            if (dropdown) {
                const items = dropdown.querySelectorAll('.dropdown-item');
                items.forEach((item, i) => {
                    if (i === index) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }
        }

        // 关闭下拉菜单
        function closeDropdown() {
            const dropdown = document.getElementById('standards-dropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }

        // 点击外部关闭下拉菜单
        document.addEventListener('click', function(event) {
            const selector = document.querySelector('.toggle-standards-selector');
            if (selector && !selector.contains(event.target)) {
                closeDropdown();
            }
        });

        async function showStandardsEditor() {
            // 拉取三个部门的指标 + 标准
            try {
                showMessage('正在加载考核标准...', 'success');

                const [c1, c2, c3] = await Promise.all([
                    fetch(`/backendtest/api/evaluation_form_api.php?action=get_criteria&department=service_line`).then(r => r.json()),
                    fetch(`/backendtest/api/evaluation_form_api.php?action=get_criteria&department=sushi_bar`).then(r => r.json()),
                    fetch(`/backendtest/api/evaluation_form_api.php?action=get_criteria&department=kitchen`).then(r => r.json())
                ]);

                const criteriaByDept = {
                    service_line: c1.success ? c1.data : [],
                    sushi_bar: c2.success ? c2.data : [],
                    kitchen: c3.success ? c3.data : []
                };

                const sres = await fetch(`/backendtest/api/evaluation_form_api.php?action=get_standards`).then(r => r.json());
                const rows = sres.success ? (sres.data || []) : [];

                // 初始化结构
                ['service_line', 'sushi_bar', 'kitchen'].forEach(d => {
                    standards[d] = {};
                    for (let i = 1; i <= 7; i++) standards[d][i] = {};
                    for (let i = 1; i <= 7; i++) for (let s = 1; s <= 5; s++) standards[d][i][s] = '';
                });
                rows.forEach(r => {
                    if (standards[r.department] && standards[r.department][parseInt(r.criteria_order)] && standards[r.department][parseInt(r.criteria_order)][parseInt(r.score)] !== undefined) {
                        standards[r.department][parseInt(r.criteria_order)][parseInt(r.score)] = r.description_text || '';
                    }
                });

                // 缓存指标数据
                _criteriaByDeptCache = criteriaByDept;
                // 生成UI（默认 service_line）
                _currentActiveDept = 'service_line';
                renderStandardsUI('service_line', criteriaByDept);
            } catch (e) {
                console.error(e);
                showMessage('加载考核标准失败', 'error');
            }
        }

        function renderStandardsUI(activeDept, criteriaByDept) {
            const deptLabel = {
                service_line: 'SERVICE (前台)',
                sushi_bar: 'SUSHI',
                kitchen: 'KITCHEN'
            };

            const activeCriteria = criteriaByDept[activeDept] || [];
            // 只取前5个指标（你说每个部门5页）
            const pages = activeCriteria.slice(0, 5);

            let html = `
                <div class="standards-wrap">
                    <div class="standards-toolbar">
                        <div class="left">
                            <div class="standards-tabs">
                                <button class="standards-tab ${activeDept === 'service_line' ? 'active' : ''}" onclick="switchStandardsDept('service_line')">SERVICE</button>
                                <button class="standards-tab ${activeDept === 'sushi_bar' ? 'active' : ''}" onclick="switchStandardsDept('sushi_bar')">SUSHI</button>
                                <button class="standards-tab ${activeDept === 'kitchen' ? 'active' : ''}" onclick="switchStandardsDept('kitchen')">KITCHEN</button>
                            </div>
                            <div style="color:#6b7280;font-size:14px;">点击导出PDF将只生成当前选择部门的标准</div>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-secondary" onclick="exportStandardsPDF()"><i class="fas fa-file-pdf"></i> 导出标准PDF</button>
                            <button class="btn-primary" onclick="saveStandards()"><i class="fas fa-save"></i> 保存标准</button>
                        </div>
                    </div>
            `;

            pages.forEach((c, idx) => {
                const co = parseInt(c.criteria_order || (idx + 1));
                const title = c.criteria_name_zh || `指标${idx + 1}`;
                html += `
                    <div class="standards-page" data-criteria-order="${co}">
                        <div class="standards-page-title">${title}</div>
                        <table class="standards-table">
                            <thead>
                                <tr>
                                    <th class="standards-score">分数</th>
                                    <th>说明</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                for (let s = 1; s <= 5; s++) {
                    const val = (standards[activeDept]?.[co]?.[s]) || '';
                    html += `
                        <tr>
                            <td class="standards-score">${s}</td>
                            <td>
                                <textarea class="standards-textarea"
                                    data-dept="${activeDept}"
                                    data-criteria-order="${co}"
                                    data-score="${s}"
                                    placeholder="请输入 ${title} 的 ${s} 分说明...">${escapeHtml(val)}</textarea>
                            </td>
                        </tr>
                    `;
                }
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            });

            html += `
                    <div id="standards-pdf"></div>
                </div>
            `;

            document.getElementById('mainContent').innerHTML = html;
        }

        let _criteriaByDeptCache = null;
        let _currentActiveDept = 'service_line'; // 记录当前激活的部门
        function switchStandardsDept(dept) {
            if (!_criteriaByDeptCache) {
                showMessage('请先加载考核标准', 'error');
                return;
            }
            _currentActiveDept = dept; // 更新当前激活的部门
            renderStandardsUI(dept, _criteriaByDeptCache);
        }

        // textarea输入同步到standards对象
        document.addEventListener('input', function(e) {
            if (!e.target.classList.contains('standards-textarea')) return;
            const dept = e.target.getAttribute('data-dept');
            const co = parseInt(e.target.getAttribute('data-criteria-order'));
            const sc = parseInt(e.target.getAttribute('data-score'));
            if (!dept || !co || !sc) return;
            if (!standards[dept]) standards[dept] = {};
            if (!standards[dept][co]) standards[dept][co] = {};
            standards[dept][co][sc] = e.target.value;
        });

        async function saveStandards() {
            try {
                const items = [];
                ['service_line', 'sushi_bar', 'kitchen'].forEach(dept => {
                    for (let co = 1; co <= 5; co++) {
                        for (let sc = 1; sc <= 5; sc++) {
                            items.push({
                                department: dept,
                                criteria_order: co,
                                score: sc,
                                description_text: (standards[dept]?.[co]?.[sc]) || ''
                            });
                        }
                    }
                });

                const res = await fetch('/backendtest/api/evaluation_form_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'save_standards', items })
                }).then(r => r.json());

                if (res.success) {
                    showMessage('考核标准已保存', 'success');
                } else {
                    showMessage(res.message || '保存失败', 'error');
                }
            } catch (e) {
                console.error(e);
                showMessage('保存失败', 'error');
            }
        }

        async function exportStandardsPDF() {
            try {
                // 获取当前激活的部门
                const activeDept = _currentActiveDept || 'service_line';
                const deptLabels = {
                    service_line: 'SERVICE',
                    sushi_bar: 'SUSHI',
                    kitchen: 'KITCHEN'
                };
                
                showMessage(`正在生成 ${deptLabels[activeDept]} 标准PDF，请稍候...`, 'success');
                const { jsPDF } = window.jspdf;

                // 只导出当前选择的部门
                const pdfContainer = document.getElementById('standards-pdf');
                if (!pdfContainer) return;

                // 获取当前部门的指标名称
                const response = await fetch(`/backendtest/api/evaluation_form_api.php?action=get_criteria&department=${activeDept}`);
                const result = await response.json();
                const criteriaList = result.success ? result.data : [];

                // 生成HTML页（只生成当前部门的5页）
                let pagesHtml = '';
                const list = criteriaList.slice(0, 5);
                list.forEach((c, idx) => {
                    const co = parseInt(c.criteria_order || (idx + 1));
                    const titleZh = c.criteria_name_zh || `指标${idx + 1}`;
                    pagesHtml += `
                        <div class="standards-pdf-page">
                            <div class="standards-page-title">${escapeHtml(titleZh)}</div>
                            <table class="standards-table">
                                <thead>
                                    <tr>
                                        <th class="standards-score" style="width: 80px;">分数</th>
                                        <th>说明</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${[1,2,3,4,5].map(sc => {
                                        const text = (standards[activeDept]?.[co]?.[sc]) || '';
                                        return `
                                        <tr>
                                            <td class="standards-score">${sc}</td>
                                            <td style="white-space: pre-wrap; font-size: 15px; line-height: 1.6; padding: 18px;">${escapeHtml(text)}</td>
                                        </tr>
                                    `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                });

                pdfContainer.innerHTML = pagesHtml;
                pdfContainer.style.display = 'block';
                await new Promise(r => setTimeout(r, 200));

                // A4 横向，规格与表单一致
                const pdf = new jsPDF('l', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const marginX = 8;
                const marginY = 8;
                const availableWidth = pdfWidth - marginX * 2;
                const availableHeight = pdfHeight - marginY * 2;

                const pageEls = Array.from(pdfContainer.querySelectorAll('.standards-pdf-page'));
                for (let i = 0; i < pageEls.length; i++) {
                    const el = pageEls[i];
                    const canvas = await html2canvas(el, {
                        scale: 2.2,
                        useCORS: true,
                        logging: false,
                        backgroundColor: '#ffffff',
                        width: el.scrollWidth,
                        height: el.scrollHeight,
                        windowWidth: el.scrollWidth,
                        windowHeight: el.scrollHeight
                    });
                    const imgData = canvas.toDataURL('image/png', 1.0);
                    const imgWidth = canvas.width;
                    const imgHeight = canvas.height;
                    const ratio = Math.min(availableWidth / imgWidth, availableHeight / imgHeight);
                    const w = imgWidth * ratio;
                    const h = imgHeight * ratio;
                    const x = (pdfWidth - w) / 2;
                    const y = marginY;

                    if (i > 0) pdf.addPage();
                    pdf.addImage(imgData, 'PNG', x, y, w, h);
                }

                const fileName = `考核标准_${deptLabels[activeDept]}_${new Date().toISOString().slice(0,10)}.pdf`;
                pdf.save(fileName);
                showMessage(`${deptLabels[activeDept]} 标准PDF下载成功`, 'success');
            } catch (e) {
                console.error(e);
                showMessage('导出失败', 'error');
            } finally {
                const pdfContainer = document.getElementById('standards-pdf');
                if (pdfContainer) pdfContainer.style.display = 'none';
            }
        }

        function escapeHtml(str) {
            return String(str || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        // 渲染表单
        function renderForm() {
            const department = document.getElementById('department').value;
            const restaurant = document.getElementById('restaurant').value;
            const evaluatorName = document.getElementById('evaluator_name').value;
            const evaluationDate = document.getElementById('evaluation_date').value;

            const deptNames = {
                'service_line': 'SERVICE LINE',
                'sushi_bar': 'SUSHI BAR',
                'kitchen': 'KITCHEN'
            };

            let html = `
                <div class="form-header">
                    <h2>TOKYO IZAKAYA</h2>
                    <div class="form-info">
                        <div><strong>Name:</strong> ${evaluatorName}</div>
                        <div><strong>Date:</strong> ${evaluationDate}</div>
                    </div>
                </div>
                <div style="background: #bf7f3b; color: white; padding: 20px; margin: -30px -30px 25px -30px; text-align: center; font-weight: 600; font-size: 20px; letter-spacing: 1px;">
                    ${deptNames[department] || department.toUpperCase()}
                </div>
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>Name</th>
            `;

            // 添加考核指标列
            criteria.forEach(c => {
                html += `<th>${c.criteria_name_zh}<br><small>${c.criteria_name_en}</small></th>`;
            });

            html += `</tr></thead><tbody>`;

            // 添加员工行
            employees.forEach((emp, index) => {
                html += `<tr>
                    <td class="employee-name">${emp.name}</td>`;
                
                criteria.forEach((c, cIndex) => {
                    html += `<td>
                        <input type="text" 
                               class="score-input" 
                               data-employee-id="${emp.id || index}"
                               data-employee-name="${emp.name}"
                               data-criteria-index="${cIndex + 1}"
                               maxlength="20">
                    </td>`;
                });

                html += `</tr>`;
            });

            html += `</tbody></table>`;

            html += `
                <div id="pdf-content">
                    <div class="form-header">
                        <h2>TOKYO IZAKAYA</h2>
                        <div class="form-info">
                            <div><strong>Name:</strong> ${evaluatorName}</div>
                            <div><strong>Date:</strong> ${evaluationDate}</div>
                        </div>
                    </div>
                    <div style="background: #bf7f3b; color: white; padding: 22px; text-align: center; font-weight: 600; font-size: 24px; margin-bottom: 30px; letter-spacing: 1.5px;">
                        ${deptNames[department] || department.toUpperCase()}
                    </div>
                    <table class="evaluation-table">
                        <thead>
                            <tr>
                                <th>Name</th>
            `;

            // 添加考核指标列（PDF版本）
            criteria.forEach(c => {
                html += `<th>${c.criteria_name_zh}<br><small style="font-size: 16px; margin-top: 5px; display: block; font-weight: 600; opacity: 1;">${c.criteria_name_en}</small></th>`;
            });

            html += `</tr></thead><tbody>`;

            // 添加员工行（PDF版本）
            employees.forEach((emp, index) => {
                html += `<tr>
                    <td class="employee-name">${emp.name}</td>`;
                
                criteria.forEach((c, cIndex) => {
                    html += `<td></td>`;
                });

                html += `</tr>`;
            });

            html += `</tbody></table>
                </div>
            `;

            document.getElementById('mainContent').innerHTML = html;
            
            // 更新侧边栏的按钮
            updateSidebarButtons();
            
            // 延迟填充PDF内容区域的数据
            setTimeout(() => {
                updatePDFContent();
            }, 100);
        }

        // 保存表单
        async function saveForm() {
            const department = document.getElementById('department').value;
            const restaurant = document.getElementById('restaurant').value;
            const evaluatorName = document.getElementById('evaluator_name').value.trim();
            const evaluationDate = document.getElementById('evaluation_date').value;

            if (!evaluatorName || !evaluationDate) {
                showMessage('请填写评估人姓名和评估日期', 'error');
                return;
            }

            // 收集表单数据
            const formData = {
                action: currentFormId ? 'update_form' : 'create_form',
                form_id: currentFormId,
                form_name: `${deptNames[department]} - ${evaluationDate}`,
                department: department,
                restaurant: restaurant,
                evaluator_name: evaluatorName,
                evaluation_date: evaluationDate,
                details: []
            };

            // 收集每个员工的评分
            const scoreInputs = document.querySelectorAll('.score-input');
            const employeeScores = {};

            scoreInputs.forEach(input => {
                const employeeId = input.getAttribute('data-employee-id');
                const employeeName = input.getAttribute('data-employee-name');
                const criteriaIndex = input.getAttribute('data-criteria-index');
                const score = input.value.trim();

                if (!employeeScores[employeeId]) {
                    employeeScores[employeeId] = {
                        employee_id: employeeId,
                        employee_name: employeeName,
                        scores: {}
                    };
                }
                employeeScores[employeeId].scores[`criteria_${criteriaIndex}`] = score;
            });

            // 转换为详情数组
            Object.values(employeeScores).forEach(emp => {
                const detail = {
                    employee_id: emp.employee_id,
                    employee_name: emp.employee_name,
                    ...emp.scores
                };
                formData.details.push(detail);
            });

            try {
                const response = await fetch('/backendtest/api/evaluation_form_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                if (result.success) {
                    showMessage('表单保存成功', 'success');
                    currentFormId = result.data.form_id || result.data.id;
                    updateSidebarButtons();
                } else {
                    showMessage(result.message || '保存失败', 'error');
                }
            } catch (error) {
                console.error('保存失败:', error);
                showMessage('保存失败: ' + error.message, 'error');
            }
        }

        // 更新侧边栏按钮
        function updateSidebarButtons() {
            const buttonArea = document.getElementById('formButtons');
            if (!buttonArea) return;

            // 显示按钮区域
            buttonArea.style.display = 'flex';
            buttonArea.style.flexDirection = 'column';
            buttonArea.style.gap = '10px';

            buttonArea.innerHTML = `
                <button class="save-form-btn" onclick="saveForm()" style="width: 100%; padding: 12px; background-color: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-save"></i> 保存表单
                </button>
                <button class="print-btn" onclick="downloadPDF()" style="width: 100%; padding: 12px; background-color: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-file-pdf"></i> 下载PDF
                </button>
            `;
        }

        // 显示消息
        function showMessage(message, type) {
            const messageEl = document.getElementById('message');
            messageEl.textContent = message;
            messageEl.className = `message ${type}`;
            messageEl.style.display = 'block';
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 3000);
        }

        const deptNames = {
            'service_line': 'SERVICE LINE',
            'sushi_bar': 'SUSHI BAR',
            'kitchen': 'KITCHEN'
        };

        // 更新PDF内容区域的数据
        function updatePDFContent() {
            const pdfContent = document.getElementById('pdf-content');
            if (!pdfContent) return;

            // 同步输入框的值到PDF内容区域
            const scoreInputs = document.querySelectorAll('.score-input');
            scoreInputs.forEach(input => {
                const employeeName = input.getAttribute('data-employee-name');
                const criteriaIndex = parseInt(input.getAttribute('data-criteria-index'));
                const value = input.value.trim();
                
                // 查找对应的PDF表格行
                const pdfRows = pdfContent.querySelectorAll('.evaluation-table tbody tr');
                pdfRows.forEach(row => {
                    const nameCell = row.querySelector('td.employee-name');
                    if (nameCell && nameCell.textContent.trim() === employeeName) {
                        const cells = row.querySelectorAll('td');
                        // criteriaIndex是从1开始的，cells[0]是姓名，cells[1]是第一个指标
                        const cellIndex = criteriaIndex; // criteriaIndex已经是1,2,3...对应cells[1],cells[2],cells[3]...
                        if (cells[cellIndex]) {
                            cells[cellIndex].textContent = value;
                        }
                    }
                });
            });
        }

        // 监听输入框变化，实时更新PDF内容
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('score-input')) {
                updatePDFContent();
            }
        });

        // 下载PDF
        async function downloadPDF() {
            const pdfContent = document.getElementById('pdf-content');
            if (!pdfContent) {
                showMessage('找不到表单内容', 'error');
                return;
            }

            // 更新PDF内容
            updatePDFContent();

            // 显示加载提示
            showMessage('正在生成PDF，请稍候...', 'success');

            // 临时显示PDF内容
            const originalDisplay = pdfContent.style.display;
            pdfContent.style.display = 'block';
            
            // 确保内容已渲染
            await new Promise(resolve => setTimeout(resolve, 300));

            try {
                const { jsPDF } = window.jspdf;
                
                // 使用html2canvas将内容转换为图片，使用更高的scale以获得更清晰的图片
                const canvas = await html2canvas(pdfContent, {
                    scale: 2.5,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    width: pdfContent.scrollWidth,
                    height: pdfContent.scrollHeight,
                    windowWidth: pdfContent.scrollWidth,
                    windowHeight: pdfContent.scrollHeight
                });

                const imgData = canvas.toDataURL('image/png', 1.0);
                
                // 创建PDF（A4尺寸，横向以容纳表格）
                const pdf = new jsPDF('l', 'mm', 'a4'); // 'l' = landscape (297mm x 210mm)
                const pdfWidth = pdf.internal.pageSize.getWidth(); // 297mm
                const pdfHeight = pdf.internal.pageSize.getHeight(); // 210mm
                
                // 计算图片尺寸以适应PDF页面
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                
                // A4横向尺寸: 297mm x 210mm
                // 使用更小的边距以最大化内容显示
                const marginX = 8; // 左右边距8mm
                const marginY = 8; // 上下边距8mm
                const availableWidth = pdfWidth - marginX * 2; // 281mm
                const availableHeight = pdfHeight - marginY * 2; // 194mm
                
                // 计算缩放比例，确保内容完全显示并尽可能大
                const ratioWidth = availableWidth / imgWidth;
                const ratioHeight = availableHeight / imgHeight;
                const ratio = Math.min(ratioWidth, ratioHeight);
                
                const imgScaledWidth = imgWidth * ratio;
                const imgScaledHeight = imgHeight * ratio;
                
                // 居中显示
                const xOffset = (pdfWidth - imgScaledWidth) / 2;
                const yOffset = marginY;

                // 添加图片
                pdf.addImage(imgData, 'PNG', xOffset, yOffset, imgScaledWidth, imgScaledHeight);
                
                // 如果内容超过一页，添加新页
                let heightLeft = imgScaledHeight;
                let position = yOffset;
                
                if (heightLeft > pdfHeight) {
                    while (heightLeft > 0) {
                        position = position - pdfHeight;
                        if (position < -imgScaledHeight) break;
                        
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', xOffset, position, imgScaledWidth, imgScaledHeight);
                        heightLeft -= pdfHeight;
                    }
                }

                // 生成文件名
                const department = document.getElementById('department').value;
                const evaluationDate = document.getElementById('evaluation_date').value;
                const deptName = deptNames[department] || department;
                const fileName = `考核表单_${deptName}_${evaluationDate}.pdf`;

                // 下载PDF
                pdf.save(fileName);
                
                showMessage('PDF下载成功', 'success');
            } catch (error) {
                console.error('生成PDF失败:', error);
                showMessage('生成PDF失败: ' + error.message, 'error');
            } finally {
                // 恢复原始显示状态
                pdfContent.style.display = originalDisplay;
            }
        }
    </script>
</body>
</html>
