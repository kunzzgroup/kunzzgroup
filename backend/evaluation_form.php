<?php
// 包含会话验证
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <title>考核表单管理系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            min-height: 100vh;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: clamp(10px, 1.56vw, 30px);
            position: relative;
        }

        .header h1 {
            color: #000000ff;
            font-size: clamp(20px, 2.6vw, 50px);
            margin-bottom: 10px;
            text-align: left;
        }

        .header h1::after {
            content: "";
            display: block;
            height: 3px;
            width: 100%;
            margin-top: 16px;
            background: linear-gradient(90deg, rgba(255,92,0,0) 0%, rgba(0, 0, 0, 1) 25%, rgba(0, 0, 0, 1) 75%, rgba(255,92,0,0) 100%);
        }

        .back-button {
            background-color: #6b7280;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            position: absolute;
            top: 135px;
            right: 0;
        }

        .back-button:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.2);
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            gap: 20px;
            overflow: hidden;
        }

        .sidebar {
            width: 280px;
            flex-shrink: 0;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .main-content {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-section input,
        .form-section select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-section input:focus,
        .form-section select:focus {
            outline: none;
            border-color: #ff5c00;
        }

        .btn-primary {
            background-color: #ff5c00;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #e65100;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.3);
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            table-layout: fixed;
        }

        .evaluation-table thead {
            background: #ff5c00;
            color: white;
        }

        .evaluation-table th {
            padding: 15px 10px;
            text-align: center;
            font-weight: 600;
            border-right: 1px solid rgba(255,255,255,0.2);
            word-wrap: break-word;
        }

        .evaluation-table th:first-child {
            min-width: 180px;
            width: 180px;
            text-align: left;
            padding-left: 15px;
        }

        .evaluation-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            text-align: center;
            word-wrap: break-word;
        }

        .evaluation-table td:first-child {
            text-align: left;
            padding-left: 15px;
        }

        .evaluation-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .employee-name {
            font-weight: 500;
            color: #333;
        }

        .score-input {
            width: 100%;
            padding: 10px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
            min-height: 35px;
            box-sizing: border-box;
        }

        .score-input:focus {
            outline: none;
            border-color: #ff5c00;
        }

        .evaluation-table th small {
            display: block;
            font-size: 11px;
            margin-top: 4px;
            font-weight: normal;
            opacity: 0.9;
        }

        .form-header {
            background: #ff5c00;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            margin: -30px -30px 30px -30px;
        }

        .form-header h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .form-info {
            display: flex;
            gap: 30px;
            font-size: 14px;
            opacity: 0.95;
        }

        .form-list {
            margin-top: 20px;
        }

        .form-item {
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-item:hover {
            border-color: #ff5c00;
            background-color: #fff5f0;
        }

        .form-item.active {
            border-color: #ff5c00;
            background-color: #fff5f0;
        }

        .form-item-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .form-item-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .message.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .add-employee-btn {
            margin-top: 15px;
            padding: 8px 16px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .add-employee-btn:hover {
            background-color: #059669;
        }

        .save-form-btn {
            margin-top: 20px;
            padding: 15px 30px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .save-form-btn:hover {
            background-color: #2563eb;
        }

        .delete-btn {
            padding: 6px 12px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }

        .delete-btn:hover {
            background-color: #dc2626;
        }

        .print-btn {
            margin-top: 20px;
            padding: 15px 30px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .print-btn:hover {
            background-color: #059669;
        }

        /* PDF内容区域样式 */
        #pdf-content {
            display: none;
            background: white;
            padding: 30px 40px;
            width: 1100px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        #pdf-content .form-header {
            margin: 0 0 20px 0;
            border-radius: 0;
            padding: 20px;
        }

        #pdf-content .form-header h2 {
            font-size: 36px;
            margin-bottom: 15px;
        }

        #pdf-content .form-info {
            font-size: 16px;
            gap: 40px;
        }

        #pdf-content .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        #pdf-content .evaluation-table th {
            border: 1px solid #000;
            padding: 15px 10px;
            text-align: center;
            font-weight: 600;
            background: #ff5c00;
            color: white;
            min-width: 120px;
        }

        #pdf-content .evaluation-table th:first-child {
            min-width: 180px;
            text-align: left;
            padding-left: 15px;
        }

        #pdf-content .evaluation-table td {
            border: 1px solid #000;
            padding: 15px 10px;
            text-align: center;
            min-height: 40px;
            min-width: 120px;
        }

        #pdf-content .evaluation-table td.employee-name {
            text-align: left;
            padding-left: 15px;
            font-weight: 500;
            min-width: 180px;
        }

        #pdf-content .evaluation-table thead {
            background: #ff5c00;
            color: white;
        }

        #pdf-content .evaluation-table tbody tr {
            height: 50px;
        }

        #pdf-content .evaluation-table small {
            display: block;
            font-size: 10px;
            margin-top: 4px;
            font-weight: normal;
            opacity: 0.9;
        }

        /* 打印样式 */
        @media print {
            body {
                background: white;
            }

            .sidebar,
            .back-button,
            .save-form-btn,
            .print-btn,
            .header h1::after,
            #message {
                display: none !important;
            }

            .container {
                max-width: 100%;
                padding: 0;
                height: auto;
            }

            .header {
                margin-bottom: 20px;
            }

            .header h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }

            .main-content {
                box-shadow: none;
                padding: 20px;
                overflow: visible;
                max-height: none;
            }

            .content-wrapper {
                display: block;
            }

            .evaluation-table {
                page-break-inside: auto;
            }

            .evaluation-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .evaluation-table thead {
                display: table-header-group;
            }

            .evaluation-table tfoot {
                display: table-footer-group;
            }

            .score-input {
                border: 1px solid #000;
                background: transparent;
                padding: 8px;
                min-height: 30px;
            }

            .form-header {
                margin: 0;
                border-radius: 0;
            }

            .form-header h2 {
                font-size: 32px;
            }

            /* 确保表格完整显示 */
            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                border: 1px solid #333;
                padding: 10px;
            }

            /* 避免分页打断表格行 */
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
    <?php include 'sidebar.php'; ?>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>考核表单管理</h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> 返回
            </a>
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

                <div class="form-list" id="formList">
                    <!-- 表单列表将在这里动态加载 -->
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

        // 部门变化时加载员工和指标
        document.getElementById('department').addEventListener('change', function() {
            const dept = this.value;
            if (dept) {
                currentDepartment = dept;
                loadEmployees(dept);
                loadCriteria(dept);
                loadFormList();
            }
        });

        // 餐厅变化时加载表单列表
        document.getElementById('restaurant').addEventListener('change', function() {
            loadFormList();
        });

        // 加载员工列表
        async function loadEmployees(department) {
            try {
                const restaurant = document.getElementById('restaurant').value;
                const response = await fetch(`../backend/schedule_api.php?action=get_employees&work_area=${department}&restaurant=${restaurant}`);
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
                const response = await fetch(`evaluation_form_api.php?action=get_criteria&department=${department}`);
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
                <div style="background: #ff5c00; color: white; padding: 15px; margin: -30px -30px 20px -30px; text-align: center; font-weight: 600; font-size: 18px;">
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
                    <div style="background: #ff5c00; color: white; padding: 18px; text-align: center; font-weight: 600; font-size: 20px; margin-bottom: 25px; letter-spacing: 1px;">
                        ${deptNames[department] || department.toUpperCase()}
                    </div>
                    <table class="evaluation-table">
                        <thead>
                            <tr>
                                <th>Name</th>
            `;

            // 添加考核指标列（PDF版本）
            criteria.forEach(c => {
                html += `<th>${c.criteria_name_zh}<br><small style="font-size: 10px; margin-top: 4px; display: block; font-weight: normal; opacity: 0.9;">${c.criteria_name_en}</small></th>`;
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
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button class="save-form-btn" onclick="saveForm()" style="flex: 1;">
                        <i class="fas fa-save"></i> 保存表单
                    </button>
                    <button class="print-btn" onclick="downloadPDF()" style="flex: 1;">
                        <i class="fas fa-file-pdf"></i> 下载PDF
                    </button>
                </div>
            `;

            document.getElementById('mainContent').innerHTML = html;
            
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
                const response = await fetch('evaluation_form_api.php', {
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
                    loadFormList();
                } else {
                    showMessage(result.message || '保存失败', 'error');
                }
            } catch (error) {
                console.error('保存失败:', error);
                showMessage('保存失败: ' + error.message, 'error');
            }
        }

        // 加载表单列表
        async function loadFormList() {
            const restaurant = document.getElementById('restaurant').value;
            const department = document.getElementById('department').value;

            if (!department) {
                document.getElementById('formList').innerHTML = '';
                return;
            }

            try {
                const response = await fetch(`evaluation_form_api.php?action=list_forms&restaurant=${restaurant}&department=${department}`);
                const result = await response.json();
                
                if (result.success) {
                    const forms = result.data || [];
                    let html = '<h3 style="margin-bottom: 15px; font-size: 16px;">历史表单</h3>';
                    
                    if (forms.length === 0) {
                        html += '<p style="color: #6b7280; font-size: 14px;">暂无历史表单</p>';
                    } else {
                        forms.forEach(form => {
                            html += `
                                <div class="form-item" onclick="loadForm(${form.id}, this)">
                                    <div class="form-item-title">${form.form_name}</div>
                                    <div class="form-item-meta">
                                        ${form.evaluation_date} | ${form.evaluator_name}
                                    </div>
                                </div>
                            `;
                        });
                    }
                    
                    document.getElementById('formList').innerHTML = html;
                }
            } catch (error) {
                console.error('加载表单列表失败:', error);
            }
        }

        // 加载表单
        async function loadForm(formId, element) {
            try {
                const response = await fetch(`evaluation_form_api.php?action=get_form&form_id=${formId}`);
                const result = await response.json();

                if (result.success) {
                    const form = result.data;
                    currentFormId = formId;

                    // 设置表单信息
                    document.getElementById('restaurant').value = form.restaurant;
                    document.getElementById('department').value = form.department;
                    document.getElementById('evaluator_name').value = form.evaluator_name;
                    document.getElementById('evaluation_date').value = form.evaluation_date;

                    // 加载员工和指标
                    currentDepartment = form.department;
                    await loadEmployees(form.department);
                    await loadCriteria(form.department);

                    // 渲染表单
                    renderForm();

                    // 填充数据（延迟执行以确保DOM已渲染）
                    setTimeout(() => {
                        if (form.details) {
                            form.details.forEach(detail => {
                                criteria.forEach((c, index) => {
                                    const scoreField = `criteria_${index + 1}`;
                                    const input = document.querySelector(
                                        `.score-input[data-employee-name="${detail.employee_name}"][data-criteria-index="${index + 1}"]`
                                    );
                                    if (input && detail[scoreField]) {
                                        input.value = detail[scoreField];
                                    }
                                });
                            });
                        }
                    }, 100);

                    // 更新列表选中状态
                    document.querySelectorAll('.form-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    if (element) {
                        element.classList.add('active');
                    }
                }
            } catch (error) {
                console.error('加载表单失败:', error);
                showMessage('加载表单失败', 'error');
            }
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
                
                // 使用html2canvas将内容转换为图片
                const canvas = await html2canvas(pdfContent, {
                    scale: 2,
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
                // 留较小的边距以最大化内容宽度
                const marginX = 10; // 左右边距10mm
                const marginY = 10; // 上下边距10mm
                const availableWidth = pdfWidth - marginX * 2; // 277mm
                const availableHeight = pdfHeight - marginY * 2; // 190mm
                
                // 计算缩放比例，确保内容完全显示
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
