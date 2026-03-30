<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 招聘列表</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            /* 品牌橙灰主题 */
            --primary-color: #ff7b00; 
            --primary-hover: #e66e00;
            --primary-light: #fff7ed; 
            
            --bg-body: #faf7f2;
            --bg-white: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --highlight: #ff7b00;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            padding: 0;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* 匹配 sidebar 的主内容区 */
        .main-content {
            flex: 1;
            padding: 0 32px 32px;
            min-width: 0;
            overflow-x: auto;
        }

        /* ================= 布局与复用工具类 ================= */
        .layout-container {
            width: 100%;
            min-width: 0;
        }
        /* 内容卡片：筛选 + 表格 + 分页 包裹在一起 */
        .content-card {
            background: #fff;
            border-radius: 12px;
            border: 2px solid #000;
            box-shadow: 0 8px 32px rgba(0,0,0,0.10);
            margin-top: 20px;
            overflow: hidden;
        }
        @media (max-width: 1024px) {
            .layout-container { min-width: auto; width: 100%; }
        }

        .flex-row { display: flex; flex-direction: row; }
        .items-center { align-items: center; }
        .items-end { align-items: flex-end; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .flex-wrap { flex-wrap: wrap; }
        .gap-4 { gap: 4px; }
        .gap-8 { gap: 8px; }
        .gap-10 { gap: 10px; }
        .gap-12 { gap: 12px; }
        .gap-16 { gap: 16px; }
        .ml-auto { margin-left: auto; }
        .mb-4 { margin-bottom: 4px; }
        .mb-8 { margin-bottom: 8px; }
        .mt-4 { margin-top: 4px; }
        .mt-24 { margin-top: 24px; }
        .text-center { text-align: center; }
        .font-normal { font-weight: normal; }
        .font-medium { font-weight: 500; }
        .font-bold { font-weight: bold; }
        .text-12 { font-size: 12px; }
        .text-14 { font-size: 14px; }
        .text-primary { color: var(--primary-color); }
        .text-muted { color: var(--text-muted); }
        .text-main { color: var(--text-main); }
        .pointer-events-none { pointer-events: none; }

        /* ================= 头部区域 ================= */
        .header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 28px 0 20px;
        }
        .header h1 {
            font-size: clamp(24px, 2.6vw, 40px); font-weight: bold; color: #000; margin: 0;
        }
        .header h1::after {
            content: ""; display: block; height: 3px; width: 100%; margin-top: 12px;
            background: linear-gradient(90deg, rgba(255,92,0,0) 0%, rgba(0,0,0,1) 25%, rgba(0,0,0,1) 75%, rgba(255,92,0,0) 100%);
        }

        /* 按钮与链接 */
        .btn {
            padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: bold;
            cursor: pointer; border: 1px solid transparent; background: transparent; transition: all 0.2s;
        }
        .btn-primary {
            background: var(--primary-color); color: white;
            font-weight: bold; letter-spacing: 0.5px;
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(249,158,0,0.35); }
        .btn-default { border-color: var(--border-color); color: var(--text-muted); background: white; }
        .btn-default:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .btn-export { display: flex; align-items: center; gap: 4px; font-weight: normal; }
        .icon-sm { width: 14px; height: 14px; }
        .icon-18 { width: 18px; height: 18px; }
        .icon-margin { margin-left: 4px; }
        
        .btn-link-action {
            color: var(--primary-color); background: transparent; border: none;
            font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; padding: 0;
        }
        .btn-link-action:hover { text-decoration: underline; }
        .btn-link { 
            background: #f3f4f6; color: #4b5563; font-size: 12px; padding: 6px 13px; border-radius: 999px;
            display: inline-flex; align-items: center; gap: 4px; border: none; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-link:hover { background: #e5e7eb; color: var(--text-main); }

        /* ================= 标签 (Chip) 筛选系统 ================= */
        .filter-bar-container {
            background-color: var(--bg-white);
            display: flex; flex-direction: column;
        }
        
        .filter-content {
            display: flex; flex-direction: column; gap: 14px; padding: 20px 24px;
        }

        .filter-row {
            display: flex; align-items: flex-start; gap: 16px;
        }
        
        .filter-label {
            font-size: 12px; color: var(--primary-color); font-weight: 700;
            width: 70px; flex-shrink: 0; padding-top: 8px; letter-spacing: 0.5px;
        }

        .chip-list {
            display: flex; flex-wrap: wrap; gap: 10px; flex: 1;
        }

        .chip {
            padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border-color);
            background: white; color: var(--text-main); font-size: 13px; cursor: pointer;
            transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;
            user-select: none; outline: none;
        }
        .chip:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .chip.active { 
            background: var(--primary-light); color: var(--primary-color); 
            border-color: var(--primary-color); font-weight: bold; 
        }
        
        .chip-count {
            background: #f3f4f6; color: #6b7280; padding: 2px 6px; 
            border-radius: 10px; font-size: 11px; font-weight: normal; transition: all 0.2s;
        }
        .chip.active .chip-count { background: var(--primary-color); color: white; }

        /* 智能搜索栏和日期单独处理 */
        .search-date-row {
            display: flex; align-items: center; justify-content: space-between; 
            gap: 16px; padding-top: 8px; border-top: 1px dashed var(--border-color);
        }

        .smart-search-wrapper {
            width: 40px; height: 38px; border-radius: 20px; 
            background: white; border: 1px solid var(--border-color);
            display: flex; align-items: center; overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: relative;
        }
        .smart-search-wrapper.expanded {
            width: 250px; border-color: var(--primary-color); border-radius: 6px; cursor: default;
        }
        .smart-search-icon {
            width: 40px; height: 100%; display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); flex-shrink: 0; transition: color 0.3s;
        }
        .smart-search-wrapper.expanded .smart-search-icon { color: var(--primary-color); }
        .smart-search-input {
            width: 100%; border: none; background: transparent; height: 100%;
            padding-right: 12px; font-size: 14px; color: var(--text-main);
            outline: none; display: none;
        }
        .smart-search-wrapper.expanded .smart-search-input { display: block; }
        
        .search-suggestions {
            visibility: hidden; opacity: 0; position: absolute; left: 0; top: 100%; width: 250px;
            background: white; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            margin-top: 8px; z-index: 100; overflow: hidden; border: 1px solid var(--border-color);
            transition: opacity 0.2s, visibility 0.2s; text-align: left;
        }
        .search-suggestions.show { visibility: visible; opacity: 1; }
        .suggest-header { font-size: 11px; color: #9ca3af; padding: 8px 12px 4px; font-weight: bold; background: #f9fafb; border-bottom: 1px solid #f3f4f6;}
        .suggest-item { display: block; padding: 8px 12px; font-size: 14px; color: var(--text-main); text-decoration: none; transition: background 0.15s; }
        .suggest-item:hover { background-color: var(--primary-light); color: var(--primary-color); }

        .filter-date-wrap { display: flex; align-items: center; gap: 8px; } 
        .date-input-wrapper { position: relative; height: 38px; display: flex; align-items: center; width: 220px; }
        .date-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #9ca3af; pointer-events: none; }
        .form-control {
            padding: 8px 12px; border: 1px solid var(--border-color);
            border-radius: 4px; font-size: 14px; outline: none;
            width: 100%; transition: border-color 0.2s; height: 38px; box-sizing: border-box;
        }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 2px rgba(255, 123, 0, 0.1); }
        .date-input { padding-left: 34px; cursor: pointer; }
        
        .quick-select-wrapper { position: relative; height: 38px; }
        .btn-quick-select { height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; box-sizing: border-box; white-space: nowrap; }
        .menu-divider { height: 1px; background: #e5e7eb; margin: 4px 0; }
        .quick-select-menu {
            visibility: hidden; opacity: 0; position: absolute; right: 0; top: 100%; margin-top: 8px;
            background: white; border: 1px solid var(--border-color);
            border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 50; width: 120px; padding: 8px 0; text-align: left;
            transition: opacity 0.15s, visibility 0.15s;
        }
        .quick-select-menu.show { visibility: visible; opacity: 1; }
        .quick-select-menu a { display: block; padding: 8px 16px; text-decoration: none; color: var(--text-main); font-size: 14px; }
        .quick-select-menu a:hover { background: var(--primary-light); color: var(--primary-color); }

        /* ================= 已选条件展示区 ================= */
        .active-filters-bar {
            display: none; align-items: center; gap: 12px; padding: 12px 24px;
            background: #f9fafb; border-top: 1px dashed var(--border-color); flex-wrap: wrap;
        }
        .active-tag {
            display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px;
            background: white; border: 1px solid var(--primary-color); color: var(--primary-color);
            border-radius: 4px; font-size: 12px; font-weight: 500;
        }
        .active-tag-close { cursor: pointer; font-size: 14px; color: var(--primary-color); opacity: 0.6; line-height: 1;}
        .active-tag-close:hover { opacity: 1; }

        /* ================= 移动端 Drawer (抽屉) ================= */
        .mobile-filter-btn { display: none; }
        .drawer-header { display: none; }
        .drawer-overlay-filter { display: none; }

        @media (max-width: 1024px) {
            .mobile-filter-btn { display: inline-flex; }
            
            .filter-bar-container {
                position: fixed; top: 0; right: -100%; width: 340px; height: 100vh;
                background: white; z-index: 2000; flex-direction: column;
                box-shadow: -4px 0 15px rgba(0,0,0,0.1);
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
                overflow-y: auto; padding: 0;
            }
            .filter-bar-container.drawer-open { right: 0; }
            
            .drawer-overlay-filter {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.4); z-index: 1999; display: block; 
                opacity: 0; visibility: hidden; transition: all 0.3s;
            }
            .drawer-overlay-filter.show { opacity: 1; visibility: visible; }
            
            .drawer-header { 
                display: flex; justify-content: space-between; align-items: center; 
                padding: 20px 24px; border-bottom: 1px solid var(--border-color); 
                background: var(--bg-white); position: sticky; top: 0; z-index: 10;
            }
            .drawer-header h3 { font-size: 16px; margin: 0; color: var(--text-main); }
            .drawer-close { background: none; border: none; font-size: 24px; color: var(--text-muted); cursor: pointer; line-height: 1;}
            
            .filter-content { padding: 20px; gap: 24px; }
            .filter-row { flex-direction: column; gap: 12px; }
            .search-date-row { flex-direction: column; align-items: stretch; border-top: none; padding-top: 0; gap: 24px;}
            .filter-date-wrap { flex-direction: column; align-items: stretch; }
            .date-input-wrapper { width: 100%; }
        }

        /* ==================== 表格区域 ==================== */
        .table-container { 
            overflow-x: auto;
            border-top: 2px solid #000;
            min-height: 300px;
            overflow-y: hidden;
        }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 1000px; margin: 0; }
        .data-table th {
            padding: 14px 16px; background-color: #636363; font-size: clamp(8px, 0.74vw, 14px);
            color: #fff; font-weight: bold; letter-spacing: 0.5px; border-bottom: none;
            white-space: nowrap; border: 1px solid #d1d5db;
        }
        .data-table th:first-child, .data-table td:first-child { padding-left: 28px; }
        .data-table td { 
            padding: clamp(0px, 0.52vw, 10px) 16px;
            font-size: clamp(8px, 0.74vw, 14px); border-bottom: 1px solid var(--border-color);
            vertical-align: middle; border: 1px solid #d1d5db;
        }
        .table-row { transition: background-color 0.2s; }
        .table-row:hover { background-color: #fff9f1; }
        .empty-state { padding: 48px; text-align: center; color: var(--text-muted); }

        .company-badge {
            background-color: #f3f4f6;
            color: #4b5563;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        /* 状态徽章与菜单 */
        .status-wrapper { position: relative; display: inline-block; } 
        .badge {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 4px 10px; border-radius: 4px; font-size: 12px; border: 1px solid transparent;
            cursor: pointer; transition: all 0.2s; user-select: none; white-space: nowrap;
        }
        .badge:hover { filter: brightness(0.95); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .badge-red { background: #fee2e2; color: #dc2626; }
        .badge-yellow { background: #fef3c7; color: #d97706; }
        .badge-green { background: #d1fae5; color: #059669; }
        .badge-gray { background: #f3f4f6; color: #4b5563; }

        .status-popover {
            visibility: hidden; opacity: 0; position: absolute; left: 50%; transform: translateX(-50%); top: 100%; margin-top: 2px;
            background: white; border: 1px solid var(--border-color); border-radius: 6px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100; min-width: 100px; padding: 4px; text-align: left;
            transition: opacity 0.15s, visibility 0.15s;
        }
        .status-popover.show { visibility: visible; opacity: 1; }
        .status-option { 
            display: block; padding: 6px 8px; border-radius: 4px; font-size: 12px; 
            color: var(--text-main); cursor: pointer; text-decoration: none; transition: all 0.2s; 
        }
        .status-option:hover { background: #f3f4f6; }
        .status-option.active { background: var(--primary-light); color: var(--primary-color); font-weight: bold; }

        /* 分页 */
        .pagination-bar { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: var(--bg-white); border-top: 1px solid var(--border-color); }
        .btn-page { padding: 4px 12px; background: white; color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px; cursor: pointer; }
        .btn-page:hover { border-color: var(--text-main); color: var(--text-main); }
        .current-page { padding: 4px 12px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--primary-color); border-radius: 4px; font-size: 14px; font-weight: bold; }

        /* ==================== 弹窗区域 ==================== */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(2px);
            display: flex; justify-content: center; align-items: center;
            z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-box {
            background: white; border-radius: 8px; width: 900px; max-width: 95%;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); transform: translateY(20px); transition: transform 0.3s;
            display: flex; flex-direction: column; max-height: 90vh;
        }
        .modal-overlay.active .modal-box { transform: translateY(0); }
        .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { font-size: 18px; color: var(--text-main); font-weight: bold; }
        .btn-close { background: none; border: none; font-size: 24px; color: var(--text-muted); cursor: pointer; line-height: 1; }
        .modal-body { padding: 24px; overflow-y: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        .info-section h3 { font-size: 14px; color: var(--text-muted); margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;}
        .info-grid { display: grid; grid-template-columns: 100px 1fr; gap: 12px; font-size: 14px; margin-bottom: 16px; text-align: left; }
        .action-section { background: #f9fafb; padding: 24px; border-radius: 8px; border: 1px solid var(--border-color); text-align: left; }
        .action-section h3 { font-size: 14px; color: var(--text-main); margin-bottom: 16px; font-weight: bold;}
        .modal-footer { padding: 20px 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px; background: #f9fafb; border-radius: 0 0 8px 8px; }
        .modal-field-label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px; font-weight: bold; }
        .modal-select { border-color: var(--primary-color); background-color: var(--primary-light); }
        .modal-link { color: var(--primary-color); text-decoration: none; }
        .btn-resume-modal { border: 1px solid var(--border-color); }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/sidebar.php'; ?>

    <div id="drawerOverlay" class="drawer-overlay-filter" onclick="toggleDrawer(false)"></div>

    <div class="main-content">
    <div class="layout-container">
        
        <header class="header">
            <h1>招聘申请列表</h1>
            <div class="flex-row gap-10">
                <button class="btn btn-default mobile-filter-btn" onclick="toggleDrawer(true)">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    筛选条件
                </button>
                <button class="btn btn-primary btn-export text-14" onclick="exportToExcel()">
                    ⬇ 导出 Excel
                </button>
            </div>
        </header>

        <div id="filterContainer" class="filter-bar-container">
            
            <div class="drawer-header">
                <h3>高级筛选</h3>
                <button class="drawer-close" onclick="toggleDrawer(false)">&times;</button>
            </div>

            <div class="filter-content">
                
                <div class="filter-row">
                    <div class="filter-label">申请公司</div>
                    <div class="chip-list" id="chipListCompany">
                        </div>
                </div>

                <div class="filter-row">
                    <div class="filter-label">申请职位</div>
                    <div class="chip-list" id="chipListJob">
                        </div>
                </div>

                <div class="filter-row">
                    <div class="filter-label">处理状态</div>
                    <div class="chip-list" id="chipListStatus">
                        </div>
                </div>

                <div class="search-date-row">
                    <div style="position: relative;">
                        <div id="smartSearchWrapper" class="smart-search-wrapper">
                            <span class="smart-search-icon">
                                <svg class="icon-18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input type="text" id="smartInput" class="smart-search-input" placeholder="搜索姓名 / 邮箱 / 手机号">
                        </div>
                        <div id="searchSuggestions" class="search-suggestions">
                            <div class="suggest-header">🔍 快速建议匹配</div>
                            <div id="suggestionList"></div>
                        </div>
                    </div>

                    <div class="filter-date-wrap">
                        <div class="date-input-wrapper">
                            <svg class="date-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <input type="text" id="dateRangePicker" class="form-control border-highlight date-input" placeholder="选择提交日期" readonly>
                        </div>
                        <div class="quick-select-wrapper">
                            <button id="btnQuickSelect" class="btn btn-default btn-quick-select">
                                时段
                                <svg class="icon-sm icon-margin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="quickSelectMenu" class="quick-select-menu">
                                <a href="#" data-range="today">今天</a>
                                <a href="#" data-range="yesterday">昨天</a>
                                <div class="menu-divider"></div>
                                <a href="#" data-range="thisWeek">本周</a>
                                <a href="#" data-range="lastWeek">上周</a>
                                <div class="menu-divider"></div>
                                <a href="#" data-range="thisMonth">这个月</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="activeFiltersArea" class="active-filters-bar">
                <span class="text-12 text-muted font-bold">已选条件：</span>
                <div id="activeFiltersList" class="flex-row items-center gap-8 flex-wrap" style="flex:1;"></div>
                <button class="btn-link-action text-12" onclick="resetAllFilters()">清空全部</button>
            </div>

        </div><!-- /#filterContainer -->

        <div class="content-card">
        <div id="filterContainer-inner"></div><!-- placeholder -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>候选人</th>
                        <th>所属公司</th>
                        <th>申请职位</th>
                        <th>联系方式</th>
                        <th>简历附件</th>
                        <th>申请时间</th>
                        <th>状态</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    </tbody>
            </table>
        </div>

        <footer class="pagination-bar">
            <span id="totalCountInfo" class="text-muted text-14">共计 0 条记录</span>
            <div class="page-controls">
                <button class="btn-page">上一页</button>
                <span class="current-page">1</span>
                <button class="btn-page">下一页</button>
            </div>
        </footer>

        </div><!-- /.content-card -->

    </div>
    <div id="applicantModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h2>候选人详情档案</h2>
                <button class="btn-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="info-section">
                    <h3>基础申请信息</h3>
                    <div class="info-grid">
                        <span class="text-muted">申请公司：</span><span class="font-bold text-main" id="modalCompany"></span>
                        <span class="text-muted">申请职位：</span><span class="font-bold text-primary" id="modalJob"></span>
                        <span class="text-muted">提交时间：</span><span class="font-normal text-main" id="modalTime"></span>
                    </div>
                    <h3 class="mt-24">个人联系资料</h3>
                    <div class="info-grid">
                        <span class="text-muted">中文姓名：</span><span class="font-bold text-main" id="modalZhName"></span>
                        <span class="text-muted">英文姓名：</span><span class="font-bold text-main" id="modalEnName"></span>
                        <span class="text-muted">性别：</span><span class="font-normal text-main" id="modalGender"></span>
                        <span class="text-muted">电子邮箱：</span><span><a href="#" id="modalEmailLink" class="modal-link font-bold"></a></span>
                        <span class="text-muted">电话号码：</span><span class="font-bold text-main" id="modalPhone"></span>
                        <span class="text-muted items-center flex-row">简历附件：</span>
                        <span><button class="btn-link btn-resume-modal" id="modalResumeBtn">📄 下载/预览简历</button></span>
                    </div>
                </div>
                <div class="action-section">
                    <h3>HR 处理进度跟进</h3>
                    <div class="mb-8">
                        <label class="modal-field-label">修改当前状态：</label>
                        <select id="modalStatusSelect" class="form-control modal-select">
                            <option value="0">🔴 待处理</option>
                            <option value="1">🟡 沟通中</option>
                            <option value="2">🟢 已录用</option>
                            <option value="3">⚪ 已淘汰</option>
                        </select>
                    </div>
                    <div>
                        <label class="modal-field-label mt-24">内部备注 (仅 HR 可见)：</label>
                        <textarea id="modalRemarks" class="form-control" rows="7" style="height: auto;" placeholder="在此记录面试情况、期望薪资、背景调查结果等..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" onclick="closeModal()">取消关闭</button>
                <button class="btn btn-primary" onclick="saveModalChanges()">保存更新</button>
            </div>
        </div>
    </div>

    <template id="rowTemplate">
        <tr class="table-row">
            <td>
                <div>
                    <div class="font-bold text-main text-14 js-name"></div>
                    <div class="text-muted text-12 mt-4 js-subname"></div>
                </div>
            </td>
            <td>
                <span class="company-badge js-company"></span>
            </td>
            <td class="font-medium text-primary js-job-title"></td>
            <td>
                <div class="text-14 text-main mb-4 js-email"></div>
                <div class="text-12 text-muted js-phone"></div>
            </td>
            <td>
                <button class="btn-link js-resume">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    预览 PDF
                </button>
            </td>
            <td>
                <div class="text-14 text-main mb-4 js-date"></div>
                <div class="text-12 text-muted js-time"></div>
            </td>
            <td class="status-cell">
                <div class="status-wrapper">
                    <div class="badge js-status-badge" title="点击修改状态"></div>
                    <div class="status-popover js-status-popover">
                        <a href="#" data-val="0" class="status-option">🔴 待处理</a>
                        <a href="#" data-val="1" class="status-option">🟡 沟通中</a>
                        <a href="#" data-val="2" class="status-option">🟢 已录用</a>
                        <a href="#" data-val="3" class="status-option">⚪ 已淘汰</a>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button class="btn-link-action btn-action-detail">详情</button>
            </td>
        </tr>
    </template>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/zh.js"></script>

    <script>
        // ── 静态配置 ───────────────────────────────────────────────────────────
        const companyJobsMap = {
            'KUNZZ HOLDINGS': ['人事部', '技术部', '销售部', '设计部'],
            'TOKYO JAPANESE CUISINE': ['服务员', '厨师', '寿司师傅', '店长'],
            'TOKYO IZAKAYA': ['店长', '服务员', '厨师', '寿司师傅']
        };
        const statusConfig = [
            { val: 0, label: '待处理', icon: '🔴' },
            { val: 1, label: '沟通中', icon: '🟡' },
            { val: 2, label: '已录用', icon: '🟢' },
            { val: 3, label: '已淘汰', icon: '⚪' }
        ];

        // ── 全局数据 ───────────────────────────────────────────────────────────
        let allData   = [];   // 当前已加载的申请记录
        let statsData = { total: 0, '0': 0, '1': 0, '2': 0, '3': 0 };  // 统计数据

        // 核心：全局状态管理
        let state = {
            keyword: '',
            company: '',
            jobTitle: '',
            status: '',
            dateStart: '',
            dateEnd: '',
            dateLabel: ''
        };

        let fpInstance = null; 
        let currentEditingId = null; let isSearchExpanded = false;

        const els = {
            smartWrapper: document.getElementById('smartSearchWrapper'),
            smartInput: document.getElementById('smartInput'),
            suggestions: document.getElementById('searchSuggestions'),
            suggestionList: document.getElementById('suggestionList'),
            
            chipListCompany: document.getElementById('chipListCompany'),
            chipListJob: document.getElementById('chipListJob'),
            chipListStatus: document.getElementById('chipListStatus'),
            
            activeArea: document.getElementById('activeFiltersArea'),
            activeList: document.getElementById('activeFiltersList'),

            datePicker: document.getElementById('dateRangePicker'),
            btnQuick: document.getElementById('btnQuickSelect'),
            quickMenu: document.getElementById('quickSelectMenu'),
            
            tableBody: document.getElementById('tableBody'),
            rowTemplate: document.getElementById('rowTemplate'),
            totalCountInfo: document.getElementById('totalCountInfo'),
            
            drawer: document.getElementById('filterContainer'),
            drawerOverlay: document.getElementById('drawerOverlay'),
            
            modal: document.getElementById('applicantModal'),
            modalStatusSelect: document.getElementById('modalStatusSelect'),
            modalRemarks: document.getElementById('modalRemarks')
        };

        document.addEventListener('DOMContentLoaded', () => {
            initDatePicker();
            // 先渲染 chip DOM（让筛选区立即可见），再拉取数据
            fetchStats().then(() => {
                renderChips();     // ← 必须先建立 chip DOM
                fetchData();       // 拉数据，完成后只更新数字
            });
            
            // 搜索框事件
            els.smartWrapper.addEventListener('click', (e) => {
                if (!isSearchExpanded) { e.stopPropagation(); expandSearch(); }
            });
            els.smartInput.addEventListener('input', () => {
                const val = els.smartInput.value.trim();
                if (val.length > 0) {
                    generateSuggestions(val);
                    els.suggestions.classList.add('show');
                } else {
                    els.suggestions.classList.remove('show');
                }
                state.keyword = val;
                // 搜索关键词变化时重新拉取
                fetchData();
            });

            // 快捷日期事件
            els.btnQuick.addEventListener('click', (e) => {
                e.stopPropagation(); els.quickMenu.classList.toggle('show');
            });
            els.quickMenu.addEventListener('click', (e) => {
                if(e.target.tagName === 'A' && e.target.getAttribute('data-range')) {
                    e.preventDefault(); setQuickDate(e.target.dataset.range, e.target.textContent);
                }
            });

            // 全局点击事件处理
            document.addEventListener('click', (e) => {
                if(!els.quickMenu.contains(e.target) && e.target !== els.btnQuick) els.quickMenu.classList.remove('show');
                if(!e.target.classList.contains('js-status-badge')) {
                    document.querySelectorAll('.js-status-popover.show').forEach(pop => pop.classList.remove('show'));
                }
                if(!els.smartWrapper.contains(e.target) && !els.suggestions.contains(e.target)) {
                    els.suggestions.classList.remove('show');
                    if(els.smartInput.value.trim() === '') collapseSearch();
                }
            });
        });

        // ── API 拉取函数 ─────────────────────────────────────────────────────────
        async function fetchStats() {
            try {
                const res  = await fetch('hireapi.php?action=stats');
                const json = await res.json();
                if (json.code === 200) { statsData = json.data; }
            } catch(e) { console.warn('统计加载失败', e); }
        }

        async function fetchData() {
            els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">数据加载中…</td></tr>`;
            const params = new URLSearchParams({ action: 'list', page_size: 200 });
            if (state.company)       params.set('company',    state.company);
            if (state.jobTitle)      params.set('job_title',  state.jobTitle);
            if (state.status !== '') params.set('status',     state.status);
            if (state.keyword)       params.set('keyword',    state.keyword);
            if (state.dateStart)     params.set('date_start', state.dateStart);
            if (state.dateEnd)       params.set('date_end',   state.dateEnd);
            try {
                const res  = await fetch('hireapi.php?' + params.toString());
                const json = await res.json();
                if (json.code === 200) {
                    allData = json.data.list;
                    renderTable(allData);
                    await fetchStats();
                    renderChips();        // 数据到位后重建 chips，确保公司/职位计数正确
                    updateChipCounts();   // 更新状态计数
                    renderActiveTags();
                } else {
                    els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">加载失败：${json.msg}</td></tr>`;
                }
            } catch(e) {
                els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">网络错误，请刷新页面重试</td></tr>`;
            }
        }

        function applyState() {
            renderChips();      // 重建 chips（active 状态正确 + 新 onclick）
            renderActiveTags();
            fetchData();        // 拉数据，完成后只更新计数，不重建 chips
        }

        // 仅更新状态 chip 的计数数字，不重建 DOM
        function updateChipCounts() {
            document.querySelectorAll('.chip[data-chip-status]').forEach(btn => {
                const span = btn.querySelector('.chip-count');
                if (!span) return;
                const v = btn.getAttribute('data-chip-status');
                span.textContent = v === '' ? (statsData.total || 0) : (statsData[String(v)] || 0);
            });
        }

        function renderChips() {
            // 1. 公司
            els.chipListCompany.innerHTML = '';
            els.chipListCompany.appendChild(createChip('company', '', '全部', '🏢'));
            Object.keys(companyJobsMap).forEach(c => {
                els.chipListCompany.appendChild(createChip('company', c, c, ''));
            });
            // 2. 职位（联动公司）
            els.chipListJob.innerHTML = '';
            const jobs = state.company
                ? companyJobsMap[state.company]
                : [...new Set(Object.values(companyJobsMap).flat())];
            jobs.forEach(j => {
                els.chipListJob.appendChild(createChip('jobTitle', j, j, ''));
            });
            // 3. 状态
            els.chipListStatus.innerHTML = '';
            els.chipListStatus.appendChild(createChip('status', '', '全部', '📊'));
            statusConfig.forEach(cfg => {
                els.chipListStatus.appendChild(createChip('status', cfg.val, cfg.label, cfg.icon));
            });
        }

        // 创建单个 Chip（所有类型都显示计数）
        function createChip(type, value, label, icon) {
            const btn         = document.createElement('button');
            const isActive    = state[type] !== '' && state[type].toString() === value.toString();
            const isAllButton = value === '';
            const active      = isAllButton ? state[type] === '' : isActive;

            btn.className = `chip ${active ? 'active' : ''}`;

            // 计算每个 chip 的数量
            let cnt = 0;
            if (type === 'status') {
                // 状态：来自 statsData（服务端统计，不受当前筛选影响）
                cnt = isAllButton ? (statsData.total || 0) : (statsData[String(value)] || 0);
                btn.setAttribute('data-chip-status', String(value));
            } else if (type === 'company') {
                // 公司：从 allData 客户端统计
                cnt = isAllButton
                    ? allData.length
                    : allData.filter(r => r.company_name === value).length;
            } else if (type === 'jobTitle') {
                // 职位：从 allData 统计（受当前 company 筛选影响）
                cnt = allData.filter(r => r.job_title === value).length;
            }

            const countHtml = `<span class="chip-count">${cnt}</span>`;
            btn.innerHTML = `${icon ? `<span style="margin-right:2px">${icon}</span>` : ''}${label}${countHtml}`;

            btn.onclick = () => {
                state[type] = (active && !isAllButton) ? '' : value;
                if (type === 'company') state.jobTitle = '';
                applyState();
            };
            return btn;
        }

        // ── Export Excel (UTF-8 BOM CSV) ─────────────────────────────────────────
        window.exportToExcel = function() {
            if (!allData || allData.length === 0) { alert('没有可导出的数据'); return; }
            var statusLabel = { 0: '待处理', 1: '沟通中', 2: '已录用', 3: '已淘汰' };
            var headers = ['序号','中文姓名','英文姓名','性别','申请公司','申请职位',
                           '邮箱','电话区号','电话号码','简历链接','状态','HR备注','申请时间'];
            var rows = allData.map(function(r, i) {
                return [
                    i + 1, r.chinese_name||'', r.english_name||'', r.gender||'',
                    r.company_name||'', r.job_title||'', r.email||'',
                    r.phone_code||'', r.phone_number||'',
                    r.resume_file_url ? (location.origin + '/backend/' + r.resume_file_url) : '',
                    statusLabel[r.status] !== undefined ? statusLabel[r.status] : String(r.status),
                    r.hr_remarks||'', (r.created_at||'').replace('T',' ')
                ];
            });
            function esc(v) {
                var s = String(v).replace(/"/g, '""');
                return (/[",\n\r]/.test(s)) ? ('"' + s + '"') : s;
            }
            var csv = [headers].concat(rows).map(function(row){ return row.map(esc).join(','); }).join('\r\n');
            var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            var url  = URL.createObjectURL(blob);
            var a    = document.createElement('a');
            var now  = new Date();
            var ts   = '' + now.getFullYear() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0');
            a.href = url; a.download = '招聘申请列表_' + ts + '.csv';
            document.body.appendChild(a); a.click();
            document.body.removeChild(a); URL.revokeObjectURL(url);
        };

        function renderActiveTags() {
            els.activeList.innerHTML = '';
            let hasActive = false;

            const addTag = (label, type) => {
                hasActive = true;
                const span = document.createElement('span');
                span.className = 'active-tag';
                span.innerHTML = `${label} <span class="active-tag-close" onclick="removeFilter('${type}')">&times;</span>`;
                els.activeList.appendChild(span);
            };

            if (state.keyword) addTag(`关键词: ${state.keyword}`, 'keyword');
            if (state.company) addTag(`公司: ${state.company}`, 'company');
            if (state.jobTitle) addTag(`职位: ${state.jobTitle}`, 'jobTitle');
            if (state.status !== '') {
                const sLabel = statusConfig.find(c => c.val == state.status).label;
                addTag(`状态: ${sLabel}`, 'status');
            }
            if (state.dateStart) addTag(`日期: ${state.dateLabel}`, 'date');

            els.activeArea.style.display = hasActive ? 'flex' : 'none';
        }

        window.removeFilter = function(type) {
            if (type === 'keyword') {
                state.keyword = ''; els.smartInput.value = ''; collapseSearch();
            } else if (type === 'date') {
                state.dateStart = ''; state.dateEnd = ''; state.dateLabel = ''; fpInstance.clear();
            } else {
                state[type] = '';
                if(type === 'company') state.jobTitle = '';
            }
            applyState();
        }

        window.resetAllFilters = function() {
            state = { keyword: '', company: '', jobTitle: '', status: '', dateStart: '', dateEnd: '', dateLabel: '' };
            els.smartInput.value = ''; collapseSearch(); fpInstance.clear();
            applyState();
        }

        function expandSearch() {
            els.smartWrapper.classList.add('expanded'); setTimeout(() => els.smartInput.focus(), 150);
            isSearchExpanded = true;
        }
        function collapseSearch() {
            els.smartWrapper.classList.remove('expanded'); isSearchExpanded = false;
        }

        function generateSuggestions(keyword) {
            els.suggestionList.innerHTML = '';
            // 从已加载的 allData 里做客户端匹配建议
            const matches = allData.filter(app =>
                (app.chinese_name || '').toLowerCase().includes(keyword.toLowerCase()) ||
                (app.email        || '').toLowerCase().includes(keyword.toLowerCase()) ||
                (app.phone_number || '').includes(keyword)
            ).slice(0, 3);

            if (matches.length === 0) {
                els.suggestionList.innerHTML = `<div style="padding: 10px 12px; font-size: 12px; color: #9ca3af;">无精准匹配，按 Enter 直接搜索</div>`;
                return;
            }

            matches.forEach(match => {
                const phone = match.phone_code ? `${match.phone_code} ${match.phone_number}` : match.phone_number;
                const a = document.createElement('a'); a.href = '#'; a.className = 'suggest-item';
                a.innerHTML = `<span style="font-weight:bold;">${match.chinese_name}</span> <span style="font-size:12px;color:#9ca3af;">(${phone})</span>`;
                a.addEventListener('click', (e) => {
                    e.preventDefault(); els.smartInput.value = match.chinese_name; els.suggestions.classList.remove('show'); 
                    state.keyword = match.chinese_name; fetchData();
                });
                els.suggestionList.appendChild(a);
            });
        }

        function initDatePicker() {
            fpInstance = flatpickr(els.datePicker, {
                mode: "range", locale: "zh", dateFormat: "Y年m月d日",
                onChange: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        state.dateStart = formatDate(selectedDates[0]); state.dateEnd = formatDate(selectedDates[1]); 
                        state.dateLabel = `${state.dateStart} 至 ${state.dateEnd}`;
                        applyState(); 
                    } else if (selectedDates.length === 0) {
                        state.dateStart = ''; state.dateEnd = ''; state.dateLabel = ''; applyState(); 
                    }
                }
            });
        }

        function formatDate(date) {
            const y = date.getFullYear(); const m = String(date.getMonth() + 1).padStart(2, '0'); const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function setQuickDate(type, label) {
            if(type === 'all') { removeFilter('date'); return; }
            const now = new Date(); let start, end; now.setHours(0, 0, 0, 0);
            switch (type) {
                case 'today': start = new Date(now); end = new Date(now); break;
                case 'yesterday': start = new Date(now); start.setDate(now.getDate() - 1); end = new Date(start); break;
                case 'thisWeek': const day = now.getDay() || 7; start = new Date(now); start.setDate(now.getDate() - day + 1); end = new Date(now); break;
                case 'lastWeek': const lastWeekDay = now.getDay() || 7; end = new Date(now); end.setDate(now.getDate() - lastWeekDay); start = new Date(end); start.setDate(end.getDate() - 6); break;
                case 'thisMonth': start = new Date(now.getFullYear(), now.getMonth(), 1); end = new Date(now); break;
            }
            fpInstance.setDate([start, end]);
            state.dateStart = formatDate(start); state.dateEnd = formatDate(end); state.dateLabel = label;
            els.quickMenu.classList.remove('show'); applyState(); 
        }
        
        // 这两个函数不再需要，保留占位避免変量未定义截断表达式错误
        function getFilteredData()  { return allData; }
        function getFilteredCount(s) { return 0; }   // chip 数量已由 statsData 提供

        function renderTable(list) {
            els.tableBody.innerHTML = ''; 
            if(list.length === 0) {
                els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">没有找到匹配的记录</td></tr>`;
                els.totalCountInfo.textContent = `共计加载 0 条数据`;
                return;
            }

            list.forEach(app => {
                const clone = els.rowTemplate.content.cloneNode(true);
                const createdAt  = app.created_at  || '';
                const [datePart, timePart] = createdAt.split('T').length > 1
                    ? createdAt.replace('T', ' ').split(' ')   // ISO 格式
                    : createdAt.split(' ');                     // 普通格式

                // 候选人姓名
                clone.querySelector('.js-name').textContent    = `${app.chinese_name || ''} (${app.english_name || ''})`;
                clone.querySelector('.js-subname').textContent = app.gender || '';
                clone.querySelector('.js-company').textContent = app.company_name || '';
                clone.querySelector('.js-job-title').textContent = app.job_title || '';

                // 联系方式
                clone.querySelector('.js-email').textContent = `✉️ ${app.email || ''}`;
                const phone = app.phone_code ? `${app.phone_code} ${app.phone_number}` : (app.phone_number || '');
                clone.querySelector('.js-phone').textContent = `📞 ${phone}`;

                // 时间
                clone.querySelector('.js-date').textContent = datePart  || '';
                clone.querySelector('.js-time').textContent = timePart  || '';

                // 简历预览按鈕
                const resumeBtn = clone.querySelector('.js-resume');
                if (app.resume_file_url) {
                    resumeBtn.onclick = () => window.open('/backend/' + app.resume_file_url, '_blank');
                } else {
                    resumeBtn.textContent = '无附件';
                    resumeBtn.style.opacity = '0.4';
                    resumeBtn.style.pointerEvents = 'none';
                }

                // 状态徽章
                const badge   = clone.querySelector('.js-status-badge');
                const popover = clone.querySelector('.js-status-popover');
                updateBadgeUI(badge, app.status, popover);

                badge.addEventListener('click', (e) => {
                    e.stopPropagation(); 
                    document.querySelectorAll('.js-status-popover.show').forEach(pop => {
                        if (pop !== popover) pop.classList.remove('show');
                    });
                    popover.classList.toggle('show');
                });

                // 内联状态修改（直接调 PUT API）
                popover.querySelectorAll('.status-option').forEach(opt => {
                    opt.addEventListener('click', async (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const newStatus = parseInt(opt.dataset.val);
                        try {
                            const res  = await fetch('hireapi.php', {
                                method : 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body   : JSON.stringify({ id: app.id, status: newStatus })
                            });
                            const json = await res.json();
                            if (json.code === 200) {
                                // 更新内存中的数据
                                const idx = allData.findIndex(item => item.id === app.id);
                                if (idx > -1) allData[idx].status = newStatus;
                                app.status = newStatus;
                                updateBadgeUI(badge, newStatus, popover);
                                await fetchStats();
                                renderChips();
                            }
                        } catch(err) {
                            console.error('状态更新失败', err);
                        }
                        popover.classList.remove('show');
                    });
                });

                clone.querySelector('.btn-action-detail').addEventListener('click', () => openModal(app.id));
                els.tableBody.appendChild(clone);
            });
            els.totalCountInfo.textContent = `共计 ${list.length} 条记录`;
        }

        function updateBadgeUI(badgeElement, statusVal, popoverElement) {
            badgeElement.className = 'badge js-status-badge'; 
            switch (Number(statusVal)) {
                case 0: badgeElement.textContent = '待处理'; badgeElement.classList.add('badge-red'); break;
                case 1: badgeElement.textContent = '沟通中'; badgeElement.classList.add('badge-yellow'); break;
                case 2: badgeElement.textContent = '已录用'; badgeElement.classList.add('badge-green'); break;
                default: badgeElement.textContent = '已淘汰'; badgeElement.classList.add('badge-gray'); break;
            }

            if(popoverElement) {
                popoverElement.querySelectorAll('.status-option').forEach(opt => {
                    if (parseInt(opt.dataset.val) === Number(statusVal)) {
                        opt.classList.add('active');
                    } else {
                        opt.classList.remove('active');
                    }
                });
            }
        }

        window.toggleDrawer = function(show) {
            if (show) {
                els.drawer.classList.add('drawer-open');
                els.drawerOverlay.classList.add('show');
                document.body.style.overflow = 'hidden'; 
            } else {
                els.drawer.classList.remove('drawer-open');
                els.drawerOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function openModal(id) {
            currentEditingId = id;
            const app = allData.find(item => item.id === id);
            if (!app) return;

            document.getElementById('modalCompany').textContent = app.company_name || '';
            document.getElementById('modalJob').textContent     = app.job_title    || '';
            document.getElementById('modalTime').textContent    = (app.created_at  || '').replace('T', ' ');
            document.getElementById('modalZhName').textContent  = app.chinese_name || '';
            document.getElementById('modalEnName').textContent  = app.english_name || '';
            document.getElementById('modalGender').textContent  = app.gender        || '';

            const emailLink = document.getElementById('modalEmailLink');
            emailLink.textContent = app.email || '';
            emailLink.href        = `mailto:${app.email}`;

            const phone = app.phone_code ? `${app.phone_code} ${app.phone_number}` : (app.phone_number || '');
            document.getElementById('modalPhone').textContent = phone;

            // 简历按鈕
            const resumeBtn = document.getElementById('modalResumeBtn');
            if (app.resume_file_url) {
                resumeBtn.onclick = () => window.open('/backend/' + app.resume_file_url, '_blank');
                resumeBtn.style.opacity = '1';
            } else {
                resumeBtn.textContent = '无简历附件';
                resumeBtn.style.opacity = '0.4';
                resumeBtn.onclick = null;
            }

            els.modalStatusSelect.value = app.status   ?? 0;
            els.modalRemarks.value      = app.hr_remarks || '';
            els.modal.classList.add('active');
        }

        window.closeModal = function() {
            els.modal.classList.remove('active'); currentEditingId = null;
        }

        window.saveModalChanges = async function() {
            if (currentEditingId === null) return;
            const status     = parseInt(els.modalStatusSelect.value);
            const hr_remarks = els.modalRemarks.value;

            try {
                const res  = await fetch('hireapi.php', {
                    method : 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body   : JSON.stringify({ id: currentEditingId, status, hr_remarks })
                });
                const json = await res.json();
                if (json.code === 200) {
                    // 同步内存
                    const idx = allData.findIndex(item => item.id === currentEditingId);
                    if (idx > -1) { allData[idx].status = status; allData[idx].hr_remarks = hr_remarks; }
                    renderTable(allData);
                    await fetchStats();
                    renderChips();
                    closeModal();
                } else {
                    alert('保存失败：' + json.msg);
                }
            } catch(err) {
                alert('网络错误，保存失败');
            }
        }
    </script>
    </div><!-- /.main-content -->
</body>
</html>
