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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <title>手机管理系统</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(18px, 1.67vw, 32px);
        }

        .header h1 {
            color: #000000ff;
            font-size: clamp(20px, 2.6vw, 50px);
            font-weight: bold;
        }

        /* 餐厅选择器样式 */
        .restaurant-selector {
            position: relative;
        }

        .selector-button {
            background-color: #ff9e00;
            color: white;
            font-weight: 500;
            padding: clamp(6px, 0.52vw, 10px) clamp(16px, 1.04vw, 20px);
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: clamp(10px, 0.73vw, 14px);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            width: clamp(100px, 8vw, 150px);
            justify-content: space-between;
            position: relative;
        }

        .selector-button:hover {
            background-color: #f98500ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .selector-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.2);
            min-width: 150px;
            z-index: 10000;
            display: none;
            margin-top: 4px;
        }

        .selector-dropdown.show {
            display: block;
        }

        .selector-dropdown .dropdown-item {
            padding: clamp(6px, 0.42vw, 8px) clamp(10px, 0.83vw, 16px);
            cursor: pointer;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.2s;
            color: #000000ff;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
        }

        .selector-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }

        .selector-dropdown .dropdown-item:hover {
            background-color: #f8f5eb;
        }

        .selector-dropdown .dropdown-item.active {
            background-color: #ff9e00;
            color: white;
        }

        /* 卡片样式 */
        .card {
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            margin-bottom: clamp(14px, 1.67vw, 32px);
        }
        
        .card-body {
            padding: clamp(5.5px, 0.7vw, 13.5px) clamp(14px, 1.25vw, 24px);
        }

        /* 控制栏样式 */
        .schedule-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: clamp(10px, 1.5vw, 30px);
            flex-wrap: wrap;
        }

        .controls-left {
            display: flex;
            gap: clamp(10px, 1.5vw, 30px);
            align-items: center;
            flex: 1;
        }

        .controls-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* 增强日期选择器样式 */
        .enhanced-date-picker {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: clamp(2px, 0.31vw, 6px) clamp(0px, 0.21vw, 4px);
            gap: 0px;
            min-width: 100px;
            transition: all 0.2s;
            position: relative;
        }

        .enhanced-date-picker:focus-within {
            border-color: #ff5c00;
            box-shadow: 0 0 0 3px rgba(255, 92, 0, 0.1);
        }

        .enhanced-date-picker:hover {
            border-color: #9ca3af;
        }

        .date-part {
            position: relative;
            cursor: pointer;
            padding: 0px clamp(2px, 0.42vw, 8px);
            border-radius: 4px;
            transition: all 0.2s;
            text-align: center;
            user-select: none;
            background: transparent;
            border: 1px solid transparent;
            font-size: clamp(8px, 0.74vw, 14px);
            color: #374151;
            font-weight: 600;
        }

        .date-part:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .date-part.active {
            background-color: #ff5c00;
            color: white;
            border-color: #ff5c00;
        }

        .date-separator {
            color: #9ca3af;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            user-select: none;
            margin: 0 2px;
        }

        /* 下拉选择面板 */
        .date-dropdown {
            position: absolute;
            top: 120%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            margin-top: 4px;
            max-height: 220px;
            overflow-y: auto;
            display: none;
        }

        .date-dropdown.show {
            display: block;
            animation: dropdownFadeIn 0.2s ease-out;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 月份选择网格 */
        .month-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(0px, 0.21vw, 4px);
            padding: clamp(4px, 0.42vw, 8px);
        }

        /* 年份选择网格 */
        .year-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(0px, 0.21vw, 4px);
            padding: clamp(2px, 0.36vw, 8px);
        }

        /* 选择项通用样式 */
        .date-option {
            padding: clamp(1px, 0.1vw, 2px);
            text-align: center;
            cursor: pointer;
            border-radius: clamp(4px, 0.31vw, 6px);
            transition: all 0.2s;
            font-size: clamp(6px, 0.63vw, 12px);
            color: #374151;
            background: transparent;
            border: 1px solid transparent;
        }

        .date-option:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .date-option.selected {
            background-color: #ff5c00;
            color: white;
            border-color: #ff5c00;
        }

        .form-label {
            display: block;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: 8px;
        }

        .btn-control {
            background: white;
            color: #333;
            border: 1px solid #d1d5db;
            padding: clamp(6px, 0.52vw, 10px) clamp(10px, 0.83vw, 16px);
            border-radius: 6px;
            font-size: clamp(10px, 0.73vw, 14px);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-control:hover {
            background: #f5f5f5;
            border-color: #9ca3af;
        }

        .btn-control.btn-save {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .btn-control.btn-save:hover {
            background: #059669;
            border-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        }

        /* 表格容器样式 */
        .table-container {
            background: white;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 2px solid #000000ff;
            min-height: 0;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .table-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
            position: relative;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }
        
        .table-wrapper::-webkit-scrollbar {
            width: 12px;
        }
        
        .table-wrapper::-webkit-scrollbar-track {
            background: #f7fafc;
            border-radius: 6px;
        }
        
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 6px;
        }
        
        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d1d5db;
            table-layout: fixed;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            background: #f99e00;
            color: white;
            padding: clamp(8px, 0.8vw, 15px) clamp(6px, 0.5vw, 10px);
            text-align: center;
            font-weight: bold;
            font-size: clamp(8px, 0.74vw, 14px);
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
        }

        th.no-col { width: 20px; }
        th.name-col { width: 200px; }
        th.position-col { width: 100px; }
        th.get-col { width: 40px; }
        th.time-start-col { width: 150px; }
        th.time-end-col { width: 150px; }
        th.return-col { width: 40px; }

        td {
            padding: clamp(6px, 0.5vw, 10px) clamp(6px, 0.5vw, 10px);
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            border: 1px solid #d1d5db;
            vertical-align: middle;
            text-align: center;
        }

        td input[type="text"],
        td input[type="time"] {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: clamp(8px, 0.74vw, 14px);
            text-align: center;
        }

        td input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

                tbody tr:nth-child(even) {
            background-color:rgb(255, 230, 183);
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tr:hover {
            background:rgb(255, 240, 104) !important;
            transition: all 0.2s ease;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f4f6;
            border-radius: 50%;
            border-top-color: #ff5c00;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: clamp(11px, 0.95vw, 13px);
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .btn-generate {
            background: #f99e00;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: clamp(11px, 0.9vw, 13px);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-generate:hover {
            background: #f98500ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 158, 0, 0.3);
        }

        /* 模态框样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            margin-bottom: 20px;
            font-size: clamp(14px, 1.2vw, 18px);
            font-weight: 600;
            color: #000;
        }

        .modal-header .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .modal-header .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: clamp(11px, 0.95vw, 13px);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: clamp(11px, 0.95vw, 13px);
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff5c00;
            box-shadow: 0 0 0 3px rgba(255, 92, 0, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: clamp(11px, 0.95vw, 13px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-save {
            background: #10b981;
            color: white;
        }

        .btn-save:hover {
            background: #059669;
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
        }

        .btn-cancel:hover {
            background: #4b5563;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .employee-list table tr:hover,
        .shift-list table tr:hover {
            background: #fff9f1 !important;
        }

        .employee-list tbody tr:nth-child(even),
        .employee-list tbody tr:nth-child(odd) {
            background: #ffffff !important;
        }

        .work-area-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: clamp(10px, 0.85vw, 11px);
            font-weight: 600;
            color: white;
            margin-top: 6px;
        }

        .work-area-service_line { background: #3b82f6; }
        .work-area-sushi_bar { background: #10b981; }
        .work-area-kitchen { background: #ef4444; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <!-- 页面标题 -->
        <div class="header">
            <h1 id="page-title">手机管理系统 - J1</h1>
            <div class="restaurant-selector">
                <button class="selector-button" onclick="toggleRestaurantSelector()">
                    <span id="current-restaurant">J1</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="selector-dropdown" id="restaurant-dropdown">
                    <div class="dropdown-item active" data-restaurant="J1" onclick="switchRestaurant('J1')">J1</div>
                    <div class="dropdown-item" data-restaurant="J2" onclick="switchRestaurant('J2')">J2</div>
                </div>
            </div>
        </div>

        <!-- 控制栏 -->
        <div class="card">
            <div class="card-body">
                <div class="schedule-controls">
                    <div class="controls-left">
                        <!-- 日期选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-calendar" style="color: #ff5c00;"></i>
                                选择日期
                            </label>
                            <div class="enhanced-date-picker" id="date-picker">
                                <input type="date" id="selected-date" style="border: none; outline: none; background: transparent; font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #374151; padding: 0; width: 140px;">
                            </div>
                        </div>
                    </div>
                    <div class="controls-right">
                        <button class="btn-generate" onclick="showManagementPanel('employees')">
                            <i class="fas fa-users"></i> 员工管理
                        </button>
                        <button class="btn-control" onclick="downloadPDF()">
                            <i class="fas fa-file-pdf"></i> 下载PDF
                        </button>
                        <button class="btn-control btn-save" onclick="saveAllData()">
                            <i class="fas fa-save"></i> 保存所有
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 表格 -->
        <div class="table-container">
            <div class="table-wrapper">
                <table id="phoneTable">
                    <thead>
                        <tr>
                            <th class="no-col" rowspan="2">NO</th>
                            <th colspan="2" style="position: relative; width: 200px; background: #f99e00;">
                                <div style="text-align: left; padding-left: 5px; font-weight: bold;">DATE:</div>
                            </th>
                            <th class="position-col" rowspan="2">POSITION</th>
                            <th class="get-col" rowspan="2">GET</th>
                            <th class="time-start-col" colspan="2" style="border-left: 2px solid #fff; border-right: 2px solid #fff;">TIME</th>
                            <th class="return-col" rowspan="2">RETURN</th>
                        </tr>
                        <tr>
                            <th colspan="2" style="position: relative; width: 200px; background: #f99e00;">
                                <div style="text-align: left; padding-left: 5px; font-weight: bold;">NAME</div>
                            </th>
                            <th class="time-start-col" style="border-left: 2px solid #fff;">START</th>
                            <th class="time-end-col" style="border-right: 2px solid #fff;">END</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                                <div class="loading" style="margin: 0 auto 10px;"></div>
                                <div>正在加载数据...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 添加/编辑员工模态框 -->
    <div id="employeeModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeEmployeeModal()">&times;</span>
                <h3 id="employeeModalTitle" style="margin-top: 8px;"><i class="fas fa-user-plus"></i> 添加员工</h3>
            </div>
            <input type="hidden" id="employeeId" value="">
            <div class="form-group">
                <label>姓名:</label>
                <input type="text" id="employeeName" required>
            </div>
            <div class="form-group">
                <label>手机号码:</label>
                <input type="tel" id="employeePhone" required>
            </div>
            <div class="form-group">
                <label>工作区域:</label>
                <select id="employeeWorkArea" required onchange="updatePositionOptions()">
                    <option value="service_line">Service Line</option>
                    <option value="sushi_bar">Sushi Bar</option>
                    <option value="kitchen">Kitchen</option>
                </select>
            </div>
            <div class="form-group">
                <label>职位:</label>
                <select id="employeePosition" required>
                    <option value="">-- 请选择职位 --</option>
                </select>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-cancel" onclick="closeEmployeeModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
                <button class="btn-action btn-save" onclick="saveEmployee()">
                    <i class="fas fa-check"></i> 保存
                </button>
            </div>
        </div>
    </div>

    <script>
    // 全局变量
    let employees = [];
    let currentRestaurant = 'J1';
    let selectedDate = new Date().toISOString().split('T')[0];
    let phoneRecords = [];

    // 从URL参数获取餐厅，如果没有则默认J1
    const urlParams = new URLSearchParams(window.location.search);
    currentRestaurant = urlParams.get('restaurant') || 'J1';

    document.addEventListener('DOMContentLoaded', function() {
        // 初始化日期选择器
        document.getElementById('selected-date').value = selectedDate;
        document.getElementById('selected-date').addEventListener('change', function() {
            selectedDate = this.value;
            loadData();
        });

        // 根据URL参数初始化餐厅显示
        updateRestaurantDisplay();
        
        // 加载数据
        loadData();
        
        // 点击其他地方关闭下拉菜单
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.restaurant-selector')) {
                document.getElementById('restaurant-dropdown').classList.remove('show');
            }
        });
    });
    
    // 更新餐厅显示
    function updateRestaurantDisplay() {
        document.getElementById('page-title').textContent = `手机管理系统 - ${currentRestaurant}`;
        
        const restaurantSelector = document.querySelector('.restaurant-selector');
        
        // 只在J1和J2时显示切换按钮
        if (currentRestaurant === 'J1' || currentRestaurant === 'J2') {
            restaurantSelector.style.display = 'block';
            document.getElementById('current-restaurant').textContent = currentRestaurant;
            
            // 更新下拉菜单的active状态
            document.querySelectorAll('#restaurant-dropdown .dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            const activeItem = document.querySelector(`#restaurant-dropdown .dropdown-item[data-restaurant="${currentRestaurant}"]`);
            if (activeItem) {
                activeItem.classList.add('active');
            }
        } else {
            // J3或其他餐厅时隐藏切换按钮
            restaurantSelector.style.display = 'none';
        }
    }
    
    // 切换餐厅选择器下拉菜单
    function toggleRestaurantSelector() {
        const dropdown = document.getElementById('restaurant-dropdown');
        dropdown.classList.toggle('show');
    }
    
    // 切换餐厅
    function switchRestaurant(restaurant) {
        // 只允许在J1和J2之间切换
        if (restaurant !== 'J1' && restaurant !== 'J2') {
            return;
        }
        
        if (restaurant === currentRestaurant) {
            // 如果选择的是当前餐厅，只关闭下拉菜单
            document.getElementById('restaurant-dropdown').classList.remove('show');
            return;
        }
        
        currentRestaurant = restaurant;
        
        // 更新URL参数（不刷新页面）
        const url = new URL(window.location);
        url.searchParams.set('restaurant', restaurant);
        window.history.pushState({ restaurant: restaurant }, '', url);
        
        // 更新显示
        updateRestaurantDisplay();
        
        // 隐藏下拉菜单
        document.getElementById('restaurant-dropdown').classList.remove('show');
        
        // 重新加载数据
        loadData();
    }

    // 加载员工数据
    async function loadEmployees() {
        try {
            const response = await fetch(`schedule_api.php?action=get_employees&restaurant=${currentRestaurant}`);
            const result = await response.json();
            if (result.success) {
                employees = result.data;
                return true;
            }
            return false;
        } catch (error) {
            console.error('加载员工失败:', error);
            return false;
        }
    }

    // 加载手机记录数据
    async function loadPhoneRecords() {
        try {
            // TODO: 实现从API加载手机记录
            // 这里暂时返回空数组
            return [];
        } catch (error) {
            console.error('加载手机记录失败:', error);
            return [];
        }
    }

    // 加载所有数据
    async function loadData() {
        const tbody = document.getElementById('tableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">
                        <div class="loading" style="margin: 0 auto 10px;"></div>
                        <div>正在加载数据...</div>
                    </td>
                </tr>
            `;

        try {
            await loadEmployees();
            phoneRecords = await loadPhoneRecords();
            renderTable();
        } catch (error) {
            console.error('加载数据失败:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px;">
                        <div class="alert alert-error">加载数据失败，请重试</div>
                    </td>
                </tr>
            `;
        }
    }

    // 渲染表格
    function renderTable() {
        const tbody = document.getElementById('tableBody');
        
        if (employees.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">
                        <div class="alert alert-error">没有找到员工数据，请先添加员工</div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        
        // 按部门分组
        const departments = [
            { key: 'service_line', name: 'SERVICE LINE' },
            { key: 'sushi_bar', name: 'SUSHI BAR' },
            { key: 'kitchen', name: 'KITCHEN' }
        ];

        let rowNumber = 1;

        departments.forEach(dept => {
            const deptEmployees = employees.filter(e => e.work_area === dept.key);
            
            // 按职位阶级排序员工
            deptEmployees.sort((a, b) => {
                const rankA = getPositionRank(dept.key, a.position);
                const rankB = getPositionRank(dept.key, b.position);
                return rankA - rankB;
            });

            if (deptEmployees.length > 0) {
                deptEmployees.forEach((employee, index) => {
                    // 查找该员工的手机记录
                    const record = phoneRecords.find(r => 
                        r.employee_id == employee.id && r.record_date === selectedDate
                    ) || {
                        employee_id: employee.id,
                        record_date: selectedDate,
                        get_checked: false,
                        start_time: '',
                        end_time: '',
                        return_checked: false
                    };

                    html += `
                        <tr data-employee-id="${employee.id}">
                            <td style="font-weight: bold;">${rowNumber}</td>
                            <td colspan="2" style="text-align: left; padding-left: 12px; font-weight: bold;">${employee.name.toUpperCase()}</td>
                            <td style="font-weight: bold;">${getWorkAreaName(employee.work_area)}</td>
                            <td>
                                <input type="checkbox" 
                                       data-field="get_checked" 
                                       ${record.get_checked ? 'checked' : ''}
                                       onchange="updateRecord(${employee.id}, 'get_checked', this.checked)">
                            </td>
                            <td>
                                <input type="time" 
                                       data-field="start_time" 
                                       value="${record.start_time || ''}"
                                       onchange="updateRecord(${employee.id}, 'start_time', this.value)">
                            </td>
                            <td>
                                <input type="time" 
                                       data-field="end_time" 
                                       value="${record.end_time || ''}"
                                       onchange="updateRecord(${employee.id}, 'end_time', this.value)">
                            </td>
                            <td>
                                <input type="checkbox" 
                                       data-field="return_checked" 
                                       ${record.return_checked ? 'checked' : ''}
                                       onchange="updateRecord(${employee.id}, 'return_checked', this.checked)">
                            </td>
                        </tr>
                    `;
                    rowNumber++;
                });
            }
        });

        tbody.innerHTML = html;
    }

    // 职位阶级定义（按阶级从高到低排序）
    const positionHierarchy = {
        'service_line': [
            'MANAGER',
            'ASST. MANAGER',
            'SUPERVISOR',
            'SENIOR CAPTAIN',
            'CAPTAIN',
            'SENIOR WAITRESS',
            'SENIOR WAITER',
            'WAITRESS',
            'WAITER'
        ],
        'sushi_bar': [
            'HEAD CHEF',
            'OUTLET CHEF',
            'ASST. CHEF',
            'COMIS 1',
            'COMIS 2',
            'COMIS 3',
            'SUSHI HELPER'
        ],
        'kitchen': [
            'HEAD CHEF',
            'OUTLET CHEF',
            'ASST. CHEF',
            'COMIS 1',
            'COMIS 2',
            'COMIS 3',
            'KITCHEN HELPER'
        ]
    };

    // 获取职位在阶级中的排序索引
    function getPositionRank(workArea, position) {
        const positions = positionHierarchy[workArea] || [];
        const index = positions.indexOf(position);
        return index === -1 ? 999 : index; // 未知职位排在最后
    }

    // 更新记录（临时存储）
    function updateRecord(employeeId, field, value) {
        let record = phoneRecords.find(r => 
            r.employee_id == employeeId && r.record_date === selectedDate
        );

        if (!record) {
            record = {
                employee_id: employeeId,
                record_date: selectedDate,
                get_checked: false,
                start_time: '',
                end_time: '',
                return_checked: false
            };
            phoneRecords.push(record);
        }

        record[field] = value;
    }

    // 保存所有数据
    async function saveAllData() {
        try {
            // TODO: 实现保存到API
            // const response = await fetch('phone_api.php?action=save_records', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ 
            //         records: phoneRecords,
            //         restaurant: currentRestaurant,
            //         date: selectedDate
            //     })
            // });
            
            showMessage('保存成功', 'success');
        } catch (error) {
            console.error('保存失败:', error);
            showMessage('保存失败，请重试', 'error');
        }
    }

    // 下载PDF - 纵向格式
    async function downloadPDF() {
        if (employees.length === 0) {
            showMessage('没有数据可下载', 'error');
            return;
        }
        
        // 显示加载提示
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'pdfLoading';
        loadingDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 40px 60px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 20000;
            text-align: center;
        `;
        loadingDiv.innerHTML = `
            <div class="loading" style="margin: 0 auto 20px; width: 40px; height: 40px;"></div>
            <h3 style="color: #111; margin-bottom: 10px; font-size: 18px;">正在生成PDF...</h3>
            <p style="color: #666; font-size: 14px;">正在处理表格数据，请稍候</p>
        `;
        document.body.appendChild(loadingDiv);
        
        try {
            const { jsPDF } = window.jspdf;
            
            // 获取原始表格
            const originalTable = document.getElementById('phoneTable');
            
            // 创建完整表格副本的函数
            function createTableCopy() {
                // 复制整个表格
                const table = originalTable.cloneNode(true);
                table.style.cssText = `
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 14px;
                    margin: 0;
                `;
                
                // 处理输入框
                const inputs = table.querySelectorAll('input');
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        const td = input.parentElement;
                        const isChecked = input.checked;
                        const checkboxHtml = `
                            <div style="
                                width: 12px; 
                                height: 12px; 
                                border: 0.3px solid #000; 
                                margin: 0 auto; 
                                display: inline-flex; 
                                align-items: center; 
                                justify-content: center; 
                                font-size: 14px; 
                                font-weight: 700;
                                background: ${isChecked ? '#000' : 'transparent'};
                                color: ${isChecked ? '#fff' : '#000'};
                            ">${isChecked ? '✓' : ''}</div>
                        `;
                        td.innerHTML = checkboxHtml;
                        td.style.textAlign = 'center';
                        td.style.padding = '4px';
                    } else if (input.type === 'time') {
                        const td = input.parentElement;
                        td.textContent = input.value || '';
                        td.style.textAlign = 'center';
                    }
                });
                
                // 设置表头样式
                const ths = table.querySelectorAll('th');
                ths.forEach(th => {
                    th.style.background = '#f99e00';
                    th.style.color = 'white';
                    th.style.padding = '5px 3px';
                    th.style.border = '0.3px solid #000';
                    th.style.fontWeight = 'bold';
                    th.style.textAlign = 'center';
                    th.style.fontSize = '12px';
                    th.style.webkitPrintColorAdjust = 'exact';
                    th.style.printColorAdjust = 'exact';
                });
                
                // 设置表格行交替颜色
                const tbodyRows = table.querySelectorAll('tbody tr');
                tbodyRows.forEach((row, index) => {
                    if (index % 2 === 1) {
                        row.style.backgroundColor = 'rgb(255, 230, 183)';
                    } else {
                        row.style.backgroundColor = '#ffffff';
                    }
                    row.style.webkitPrintColorAdjust = 'exact';
                    row.style.printColorAdjust = 'exact';
                });
                
                // 设置单元格样式
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const tds = row.querySelectorAll('td');
                    tds.forEach((td, index) => {
                        td.style.padding = '4px 3px';
                        td.style.border = '0.3px solid #000';
                        td.style.textAlign = 'center';
                        td.style.fontSize = '12px';
                        td.style.webkitPrintColorAdjust = 'exact';
                        td.style.printColorAdjust = 'exact';
                        
                        // NO列（第0列）、NAME列（第1列，colspan=2）、POSITION列（第2列）
                        if (index === 0 || index === 1 || index === 2) {
                            td.style.fontWeight = 'bold';
                        }
                    });
                });
                
                return table;
            }
            
            // 创建容器函数
            function createContainer() {
                const container = document.createElement('div');
                container.style.cssText = `
                    position: absolute;
                    left: -9999px;
                    width: 200mm;
                    background: white;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                `;
                document.body.appendChild(container);
                return container;
            }
            
            // 添加表格到PDF的函数
            async function addTableToPDF(pdf, table, isFirstTable) {
                const container = createContainer();
                container.appendChild(table);
                
                // 等待DOM更新
                await new Promise(resolve => setTimeout(resolve, 100));
                
                // 截取表格
                const canvas = await html2canvas(container, {
                    scale: 3,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    width: container.offsetWidth,
                    height: container.scrollHeight,
                    onclone: (clonedDoc) => {
                        const clonedContainer = clonedDoc.querySelector('div[style*="left: -9999px"]');
                        if (clonedContainer) {
                            clonedContainer.style.webkitPrintColorAdjust = 'exact';
                            clonedContainer.style.printColorAdjust = 'exact';
                        }
                    }
                });
                
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const margin = 0;
                const contentWidth = pdfWidth - (margin * 2);
                const contentHeight = pdfHeight - (margin * 2);
                
                // 计算图片尺寸
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                const imgWidthMM = imgWidth * 0.264583;
                const imgHeightMM = imgHeight * 0.264583;
                
                // 计算缩放比例以适应页面宽度
                const widthRatio = contentWidth / imgWidthMM;
                const finalWidth = imgWidthMM * widthRatio;
                const finalHeight = imgHeightMM * widthRatio;
                
                // 如果不是第一个表格，确保从新页开始
                if (!isFirstTable) {
                    pdf.addPage();
                }
                
                // 如果内容超过一页高度，需要分页
                if (finalHeight > contentHeight) {
                    let sourceY = 0;
                    let pageIndex = 0;
                    
                    while (sourceY < imgHeight) {
                        // 计算剩余内容高度
                        const remainingSourceHeight = imgHeight - sourceY;
                        if (remainingSourceHeight <= 0) break;
                        
                        // 计算当前页应该显示的高度
                        const remainingDisplayHeight = finalHeight - (sourceY / imgHeight * finalHeight);
                        const pageDisplayHeight = Math.min(contentHeight, remainingDisplayHeight);
                        
                        // 计算对应的源高度
                        const pageSourceHeight = (pageDisplayHeight / finalHeight) * imgHeight;
                        const actualSourceHeight = Math.min(pageSourceHeight, remainingSourceHeight);
                        
                        // 如果实际高度太小（小于1像素），说明已经处理完，退出
                        if (actualSourceHeight < 1) break;
                        
                        // 如果不是第一页，添加新页（第一个表格的第一页已经在PDF中，第二个表格的第一页已经在上面添加）
                        if (pageIndex > 0) {
                            pdf.addPage();
                        }
                        
                        const tempCanvas = document.createElement('canvas');
                        tempCanvas.width = imgWidth;
                        tempCanvas.height = actualSourceHeight;
                        const tempCtx = tempCanvas.getContext('2d');
                        tempCtx.drawImage(canvas, 0, sourceY, imgWidth, actualSourceHeight, 0, 0, imgWidth, actualSourceHeight);
                        
                        const pageImgData = tempCanvas.toDataURL('image/png');
                        const actualPageDisplayHeight = (actualSourceHeight / imgHeight) * finalHeight;
                        
                        // 只有当实际高度大于0时才添加图片
                        if (actualPageDisplayHeight > 0 && actualSourceHeight > 0) {
                            pdf.addImage(pageImgData, 'PNG', margin, margin, finalWidth, actualPageDisplayHeight);
                            sourceY += actualSourceHeight;
                            pageIndex++;
                        } else {
                            // 如果没有内容，退出循环
                            break;
                        }
                    }
                } else {
                    // 单页内容，填满整个A4页面
                    const imgData = canvas.toDataURL('image/png');
                    pdf.addImage(imgData, 'PNG', margin, margin, finalWidth, finalHeight);
                }
                
                // 清理临时元素
                container.remove();
            }
            
            // 创建PDF（纵向）
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });
            
            // 创建第一个表格（完整复制）
            const table1 = createTableCopy();
            table1.style.marginBottom = '0';
            
            // 创建第二个表格（完整复制）
            const table2 = createTableCopy();
            table2.style.marginTop = '0';
            table2.style.marginBottom = '0';
            
            // 分别添加两个表格到PDF
            await addTableToPDF(pdf, table1, true);
            await addTableToPDF(pdf, table2, false);
            
            // 下载PDF
            const dateStr = selectedDate.replace(/-/g, '');
            pdf.save(`手机管理_${currentRestaurant}_${dateStr}.pdf`);
            
            document.getElementById('pdfLoading').remove();
            showMessage('PDF下载成功！', 'success');
            
        } catch (error) {
            console.error('生成PDF失败:', error);
            if (document.getElementById('pdfLoading')) {
                document.getElementById('pdfLoading').remove();
            }
            showMessage('PDF生成失败: ' + error.message, 'error');
        }
    }

    function showMessage(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '10000';
        alertDiv.style.minWidth = '250px';
        alertDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateX(100px)';
            alertDiv.style.transition = 'all 0.3s';
            setTimeout(() => alertDiv.remove(), 300);
        }, 3000);
    }

    // 员工管理相关函数
    function showManagementPanel(type) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'block';
        modal.style.zIndex = '10000';
        
        let title = '';
        let content = '';
        
        if (type === 'employees') {
            title = '<i class="fas fa-users"></i> 员工管理';
            content = `
                <button class="btn-generate" onclick="showAddEmployeeModal()">
                    <i class="fas fa-user-plus"></i> 添加新员工
                </button>
                <div id="employeeListModal" class="employee-list"></div>
            `;
        }
        
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 900px; max-height: 80vh; overflow-y: auto;">
                <div class="modal-header">
                    <span class="close" onclick="this.closest('.modal').remove()">&times;</span>
                    <h3 style="margin-top: 8px;">${title}</h3>
                </div>
                <div style="margin-top: 20px;">
                    ${content}
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // 填充数据
        if (type === 'employees') {
            displayEmployeesInModal();
        }
        
        modal.onclick = function(event) {
            if (event.target === modal) {
                modal.remove();
            }
        };
    }

    function displayEmployeesInModal() {
        const container = document.getElementById('employeeListModal');
        if (!container) return;
        
        if (employees.length === 0) {
            container.innerHTML = '<div class="alert alert-error">暂无员工数据</div>';
            return;
        }
        
        // 部门上限定义
        const departmentLimits = {
            'service_line': 8,
            'sushi_bar': 4,
            'kitchen': 13
        };
        
        let html = `
            <div style="overflow-x: auto; margin-top: 16px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">No.</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">姓名</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">手机号码</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">职位</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">工作区域</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // 按部门分组显示
        const departments = [
            { key: 'service_line', name: 'SERVICE LINE' },
            { key: 'sushi_bar', name: 'SUSHI BAR' },
            { key: 'kitchen', name: 'KITCHEN' }
        ];
        
        departments.forEach(dept => {
            const deptEmployees = employees.filter(e => e.work_area === dept.key);
            
            // 按职位阶级排序员工
            deptEmployees.sort((a, b) => {
                const rankA = getPositionRank(dept.key, a.position);
                const rankB = getPositionRank(dept.key, b.position);
                return rankA - rankB;
            });
            
            const maxLimit = departmentLimits[dept.key];
            const currentCount = deptEmployees.length;
            const isAtLimit = currentCount >= maxLimit;
            const limitColor = isAtLimit ? '#ef4444' : '#10b981';
            
            // 部门标题行（显示当前人数和上限）
            html += `
                <tr>
                    <td colspan="6" style="background: #636363; color: white; font-weight: bold; padding: 10px 12px; border: 1px solid #d1d5db; text-align: left;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>${dept.name}</span>
                            <span style="background: ${limitColor}; padding: 4px 12px; border-radius: 12px; font-size: 11px;">
                                ${currentCount} / ${maxLimit}
                            </span>
                        </div>
                    </td>
                </tr>
            `;
            
            if (deptEmployees.length > 0) {
                // 该部门的员工（独立编号）
                deptEmployees.forEach((employee, index) => {
                    html += `
                        <tr style="transition: background 0.2s;">
                            <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center; font-weight: 600;">${index + 1}</td>
                            <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center; font-weight: 600;">${employee.name.toUpperCase()}</td>
                            <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">${employee.phone}</td>
                            <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">${employee.position}</td>
                            <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">
                                <span class="work-area-badge work-area-${employee.work_area}">
                                    ${getWorkAreaName(employee.work_area)}
                                </span>
                            </td>
                            <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">
                                <button class="btn-action" onclick="editEmployee(${employee.id}, ${JSON.stringify(employee.name)}, ${JSON.stringify(employee.phone)}, ${JSON.stringify(employee.position)}, ${JSON.stringify(employee.work_area)})" title="编辑员工" style="background: #f99e00; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteEmployee(${employee.id})" title="删除员工">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                // 空行占位
                html += `
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #999; font-style: italic; border: 1px solid #d1d5db;">
                            暂无员工
                        </td>
                    </tr>
                `;
            }
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = html;
    }

    // 更新职位选项
    function updatePositionOptions() {
        const workArea = document.getElementById('employeeWorkArea').value;
        const positionSelect = document.getElementById('employeePosition');
        const currentPosition = positionSelect.value;
        
        // 清空现有选项
        positionSelect.innerHTML = '<option value="">-- 请选择职位 --</option>';
        
        // 添加对应工作区域的职位选项
        if (positionHierarchy[workArea]) {
            positionHierarchy[workArea].forEach(position => {
                const option = document.createElement('option');
                option.value = position;
                option.textContent = position;
                positionSelect.appendChild(option);
            });
        }
        
        // 尝试恢复之前选择的职位（如果在新的工作区域中存在）
        if (currentPosition && positionHierarchy[workArea]?.includes(currentPosition)) {
            positionSelect.value = currentPosition;
        }
    }
    
    function showAddEmployeeModal() {
        document.getElementById('employeeId').value = '';
        document.getElementById('employeeName').value = '';
        document.getElementById('employeePhone').value = '';
        document.getElementById('employeeWorkArea').value = 'service_line';
        document.getElementById('employeeModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> 添加员工';
        updatePositionOptions(); // 更新职位选项
        document.getElementById('employeeModal').style.display = 'block';
    }
    
    function editEmployee(id, name, phone, position, workArea) {
        document.getElementById('employeeId').value = id;
        document.getElementById('employeeName').value = name;
        document.getElementById('employeePhone').value = phone;
        document.getElementById('employeeWorkArea').value = workArea;
        updatePositionOptions(); // 先更新职位选项
        document.getElementById('employeePosition').value = position; // 再设置职位值
        document.getElementById('employeeModalTitle').innerHTML = '<i class="fas fa-user-edit"></i> 编辑员工';
        document.getElementById('employeeModal').style.display = 'block';
    }

    async function saveEmployee() {
        const id = document.getElementById('employeeId').value;
        const name = document.getElementById('employeeName').value.trim();
        const phone = document.getElementById('employeePhone').value.trim();
        const position = document.getElementById('employeePosition').value.trim();
        const workArea = document.getElementById('employeeWorkArea').value;
        
        if (!name || !phone || !position) {
            showMessage('请填写所有必填字段', 'error');
            return;
        }
        
        const isEdit = id !== '';
        
        // 如果是添加新员工，检查部门人数上限
        if (!isEdit) {
            const departmentLimits = {
                'service_line': 8,
                'sushi_bar': 4,
                'kitchen': 13
            };
            
            const currentCount = employees.filter(e => e.work_area === workArea).length;
            const maxLimit = departmentLimits[workArea];
            
            if (currentCount >= maxLimit) {
                const deptName = getWorkAreaName(workArea);
                showMessage(`${deptName} 已达到人数上限 (${maxLimit}人)，无法添加更多员工`, 'error');
                return;
            }
        }
        
        try {
            const action = isEdit ? 'update_employee' : 'add_employee';
            const requestData = isEdit 
                ? { id, name, phone, position, work_area: workArea }
                : { name, phone, position, work_area: workArea, restaurant: currentRestaurant };
            
            const response = await fetch(`schedule_api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            if (result.success) {
                showMessage(isEdit ? '员工更新成功' : '员工添加成功', 'success');
                closeEmployeeModal();
                await loadEmployees();
                renderTable();
                // 如果管理面板打开着，刷新它
                const employeeListModal = document.getElementById('employeeListModal');
                if (employeeListModal) {
                    displayEmployeesInModal();
                }
            } else {
                showMessage((isEdit ? '更新失败: ' : '添加失败: ') + result.error, 'error');
            }
        } catch (error) {
            console.error(isEdit ? '更新员工失败:' : '添加员工失败:', error);
            showMessage(isEdit ? '更新失败，请重试' : '添加失败，请重试', 'error');
        }
    }

    async function deleteEmployee(id) {
        if (!confirm('确定要删除这个员工吗？相关的排班记录也将被删除。')) return;
        
        try {
            const response = await fetch('schedule_api.php?action=delete_employee', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            
            const result = await response.json();
            if (result.success) {
                showMessage('删除成功', 'success');
                await loadEmployees();
                renderTable();
                displayEmployeesInModal();
            } else {
                showMessage('删除失败', 'error');
            }
        } catch (error) {
            console.error('删除员工失败:', error);
            showMessage('删除失败，请重试', 'error');
        }
    }

    function closeEmployeeModal() {
        document.getElementById('employeeModal').style.display = 'none';
    }

    function getWorkAreaName(area) {
        const names = {
            'service_line': 'Service Line',
            'sushi_bar': 'Sushi Bar',
            'kitchen': 'Kitchen'
        };
        return names[area] || area;
    }

    // 点击模态框外部关闭
    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>

