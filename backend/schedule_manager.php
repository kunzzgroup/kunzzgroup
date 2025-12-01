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
    <title>员工排班管理系统</title>
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
        
        .btn-control.btn-copy {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .btn-control.btn-copy:hover {
            background: #059669;
            border-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        }


        /* 表格容器样式 - 与 generatecode.php 一致 */
        .table-container {
            background: white;
            overflow: hidden; /* 容器不处理滚动，由wrapper处理 */
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 2px solid #000000ff;
            min-height: 0;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .table-wrapper {
            overflow-x: auto; /* 同时处理横向和纵向滚动 */
            overflow-y: auto;
            flex: 1;
            min-height: 0;
            position: relative;
            /* 滚动条样式 */
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }
        
        /* 确保scheduleContainer和schedule-grid可以横向扩展 */
        #scheduleContainer {
            min-width: fit-content;
            width: fit-content;
            display: block;
        }
        
        .schedule-grid {
            min-width: fit-content;
            width: fit-content;
            display: grid; /* 保持grid布局 */
        }
        
        /* Webkit浏览器滚动条样式 - 纵向滚动条 */
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
            background: #636363;
            color: white;
            padding: clamp(8px, 0.8vw, 15px) clamp(6px, 0.5vw, 10px);
            text-align: center;
            font-weight: bold;
            font-size: clamp(8px, 0.74vw, 14px);
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
        }

        th.no-col { width: 50px; }
        th.name-col { width: 150px; }
        th.phone-col { width: 120px; }
        th.position-col { width: 120px; }
        th.date-col { 
            width: 50px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        th.date-col:hover {
            background: #ff5c00 !important;
            color: white !important;
        }

        td {
            padding: clamp(6px, 0.5vw, 10px) clamp(6px, 0.5vw, 10px);
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            border: 1px solid #d1d5db;
            vertical-align: middle;
            text-align: center;
        }

        td.employee-info {
            text-align: left;
            background: #ffffff;
        }

        tr:hover {
            background: #fff9f1;
            transition: all 0.2s ease;
        }

        /* 部门行样式 */
        .department-row {
            background: #636363 !important;
            color: white !important;
            font-weight: 700;
            font-size: clamp(10px, 0.9vw, 14px);
            text-align: left;
            padding: clamp(8px, 0.8vw, 12px) clamp(10px, 1vw, 16px) !important;
        }

        /* 日期单元格 */
        .date-cell {
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            text-align: center;
            font-weight: 600;
        }

        .date-cell:hover {
            opacity: 0.8;
            border-color: #ffd700;
            box-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
        }

        .date-cell.weekend {
            background: #fff9e6;
        }

        .date-cell.has-value {
            font-weight: 700;
        }

        /* CSS Grid布局样式 */
        .schedule-grid {
            display: grid;
            background: white;
            border: 1px solid #000;
            width: fit-content;
            min-width: fit-content;
            /* 移除 max-width 限制，允许横向扩展 */
            /* overflow-x 由容器处理，这里不需要 */
        }

        .grid-cell {
            padding: clamp(6px, 0.5vw, 10px);
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            border: 1px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            overflow: hidden;
        }

        .grid-cell.grid-header {
            background: #636363;
            color: white;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
            line-height: 1.2;
        }

        .grid-cell.grid-header.weekend {
            background: #ff9800;
        }

        .grid-cell.grid-header.date-header {
            cursor: pointer;
            transition: all 0.2s;
        }

        .grid-cell.grid-header.date-header:hover {
            background: #ff5c00 !important;
        }
        
        .grid-cell.grid-header.date-header.selected-column {
            outline: 3px solid #10b981;
            outline-offset: -3px;
        }
        
        .grid-cell.sticky-col {
            position: sticky;
            z-index: 12;
        }
        
        .grid-cell.grid-header.sticky-col {
            z-index: 20;
        }
        
        .sticky-col-1 {
            left: 0;
        }
        
        .sticky-col-2 {
            left: 50px; /* No. 列宽度 */
        }
        
        .grid-cell.grid-no {
            justify-content: center;
            font-weight: 600;
            background: #fafafa;
        }

        .grid-cell.grid-employee-info {
            justify-content: flex-start;
            padding-left: 12px;
            background: #fafafa;
            font-weight: 600;
        }

        .grid-cell.grid-date {
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            font-weight: 600;
        }

        .grid-cell.grid-date:hover {
            opacity: 0.8;
            border-color: #ffd700;
            box-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
        }

        .grid-cell.grid-date.weekend {
            background: #fff9e6;
        }

        .grid-cell.grid-date.has-value {
            font-weight: 700;
        }

        .grid-cell.grid-department {
            background: #636363 !important;
            color: white !important;
            font-weight: 700;
            font-size: clamp(10px, 0.9vw, 14px);
            justify-content: flex-start !important;
            padding-left: clamp(10px, 1vw, 16px) !important;
            grid-column: 1 / -1;
        }

        /* 编辑模式样式 */
        .grid-cell.editable {
            cursor: text;
            border: 2px solid #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        
        .grid-cell.selected {
            outline: 2px solid #3b82f6 !important;
            outline-offset: -2px;
            background: rgba(59, 130, 246, 0.1) !important;
        }
        
        .grid-cell.edit-mode {
            cursor: text;
            position: relative;
        }
        
        .grid-cell.modified {
            box-shadow: 0 0 0 2px rgba(251, 146, 60, 0.5) !important;
        }
        
        .edit-mode-active .grid-cell.grid-date {
            cursor: text !important;
        }
        
        .edit-mode-active .grid-cell.grid-date:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.3) !important;
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

        /* 设置菜单 */
        .settings-dropdown {
            position: absolute;
            right: 20px;
            top: 140px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            min-width: 180px;
        }

        .settings-dropdown .menu-link {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s;
            font-size: clamp(11px, 0.95vw, 13px);
        }

        .settings-dropdown .menu-link:hover {
            background: #f5f5f5;
            color: #ff5c00;
        }

        /* 卡片样式 */
        .shift-card,
        .leave-card,
        .employee-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: clamp(12px, 1.2vw, 18px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .shift-card:hover,
        .leave-card:hover,
        .employee-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .shift-list,
        .leave-list,
        .employee-list {
            margin-top: 16px;
        }
        
        .employee-list table tr:hover,
        .shift-list table tr:hover {
            background: #fff9f1 !important;
        }

        .leave-code {
            font-size: clamp(18px, 1.8vw, 24px);
            font-weight: bold;
        }

        .leave-name {
            font-size: clamp(10px, 0.9vw, 12px);
            opacity: 0.8;
            margin-top: 4px;
        }

        .employee-name {
            font-size: clamp(14px, 1.2vw, 16px);
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }

        .employee-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: clamp(11px, 0.95vw, 13px);
            color: #6b7280;
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

        .legend {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .legend-section {
            flex: 1;
            min-width: 300px;
        }

        .legend-section h4 {
            margin-bottom: 12px;
            color: #111827;
            font-size: clamp(12px, 1.1vw, 14px);
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
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
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
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

        /* 打印样式 */
        @page {
            size: landscape;  /* 横向打印 */
            margin: 1cm 0.5cm;
        }
        
        @media print {
            .schedule-controls, .header, body .informationmenu, .card, .no-print {
                display: none !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .container {
                padding: 10px !important;
                max-width: 100% !important;
            }
            
            .table-container {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin-bottom: 0 !important;
                overflow: visible !important; /* 打印时不需要滚动 */
            }
            
            .table-wrapper {
                overflow: visible !important;
            }
            
            table {
                font-size: 8px !important;
                page-break-inside: auto !important;
            }
            
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            
            thead {
                display: table-header-group !important;
            }
            
            th {
                background: #e0e0e0 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 4px 2px !important;
            }
            
            td {
                padding: 3px 2px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* 确保颜色在打印/PDF中显示 */
            .date-cell {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .department-row {
                background: #d0d0d0 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                page-break-after: avoid !important;
            }
            
            .date-cell.weekend {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* 打印图例 */
            .print-legend {
                display: block !important;
                page-break-before: always !important;
                margin-top: 20px;
            }
        }
        
        /* 网页中隐藏打印图例 */
        .print-legend {
            display: none;
        }

        /* 响应式 */
        @media (max-width: 768px) {
            .schedule-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .controls-left,
            .controls-right {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <!-- 页面标题 -->
        <div class="header">
            <h1 id="page-title">员工排班管理系统 - J1</h1>
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
                        <!-- 年月选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-calendar" style="color: #ff5c00;"></i>
                                选择年份和月份
                            </label>
                            <div class="enhanced-date-picker" id="schedule-date-picker">
                                <div class="date-part" data-type="year" onclick="showScheduleDateDropdown('year')">
                                    <span id="schedule-year-display">2024</span>
                                </div>
                                <span class="date-separator">年</span>
                                <div class="date-part" data-type="month" onclick="showScheduleDateDropdown('month')">
                                    <span id="schedule-month-display">01</span>
                                </div>
                                <span class="date-separator">月</span>
            
                                <div class="date-dropdown" id="schedule-dropdown"></div>
                            </div>
                        </div>
                        
                        <!-- 复制到下月按钮 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; visibility: hidden;">占位</label>
                            <button class="btn-control btn-copy" onclick="copyToNextMonth()" title="将当前月的排班复制到下一个月">
                                <i class="fas fa-copy"></i> 复制到下月
                            </button>
                        </div>
                    </div>
                    <div class="controls-right">
                        <button id="saveAllBtn" class="btn-control" onclick="saveAllChanges()" style="background: #3b82f6; color: white; border-color: #3b82f6;">
                            <i class="fas fa-save"></i> 保存所有更改
                        </button>
                        <button class="btn-generate" onclick="showManagementPanel('shifts')">
                            <i class="fas fa-clock"></i> 班次管理
                        </button>
                        <button class="btn-generate" onclick="showManagementPanel('employees')">
                            <i class="fas fa-users"></i> 员工管理
                        </button>
                        <button class="btn-generate" onclick="showManagementPanel('legend')">
                            <i class="fas fa-info-circle"></i> 图例说明
                        </button>
                        <button class="btn-control" onclick="downloadPDFDirect()">
                            <i class="fas fa-file-pdf"></i> 下载PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 排班表 -->
        <div class="table-container">
            <div class="table-wrapper">
                <div id="scheduleContainer">
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <div class="loading" style="margin: 0 auto 10px;"></div>
                        <div>正在加载排班表...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 打印图例（仅在打印时显示） -->
        <div class="print-legend">
            <h3 style="text-align: center; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #000; border-top: 2px solid #000; padding-top: 20px;">班次与假期图例</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 900px; margin: 0 auto;">
                <div>
                    <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">班次 (Shifts)</h4>
                    <div id="printShiftLegend"></div>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">请假 (Leave)</h4>
                    <div id="printLeaveLegend"></div>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">假期 (Holiday)</h4>
                    <div id="printHolidayLegend"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 设置整列假期模态框 -->
    <div id="columnHolidayModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeColumnHolidayModal()">&times;</span>
                <h3 style="margin-top: 8px;"><i class="fas fa-calendar-day"></i> 设置公共假期</h3>
                <p id="columnDateInfo" style="color: #6b7280; font-size: 13px; margin-top: 4px;"></p>
            </div>
            <div class="form-group">
                <label>选择公共假期类型（将应用到所有员工）:</label>
                <div id="columnHolidayOptions" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;"></div>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-delete" onclick="clearColumnSchedule()">
                    <i class="fas fa-eraser"></i> 清除整列
                </button>
                <button class="btn-action btn-cancel" onclick="closeColumnHolidayModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
            </div>
        </div>
    </div>

    <!-- 原批量日期模态已移除 -->

    <!-- 设置排班模态框 -->
    <div id="scheduleModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeScheduleModal()">&times;</span>
                <h3 style="margin-top: 8px;">设置排班</h3>
                <p id="modalEmployeeInfo" style="color: #6b7280; font-size: 13px; margin-top: 4px;"></p>
            </div>
            <div class="form-group">
                <label>选择类型:</label>
                <select id="scheduleType" onchange="updateScheduleOptions()">
                    <option value="">-- 选择 --</option>
                    <option value="shift">班次</option>
                    <option value="leave">请假</option>
                </select>
            </div>
            <div class="form-group">
                <label>选择值:</label>
                <div id="scheduleOptions" style="min-height: 100px;"></div>
            </div>
            <div class="form-group">
                <label>备注:</label>
                <textarea id="scheduleNotes" rows="3" placeholder="可选的备注信息"></textarea>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-delete" onclick="deleteCurrentSchedule()">
                    <i class="fas fa-trash"></i> 删除
                </button>
                <button class="btn-action btn-cancel" onclick="closeScheduleModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
                <button class="btn-action btn-save" onclick="saveSchedule()">
                    <i class="fas fa-check"></i> 保存
                </button>
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

    <!-- 添加/编辑班次模态框 -->
    <div id="shiftModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeShiftModal()">&times;</span>
                <h3 id="shiftModalTitle" style="margin-top: 8px;"><i class="fas fa-clock"></i> 添加班次</h3>
            </div>
            <input type="hidden" id="shiftId" value="">
            <div class="form-group">
                <label>班次代码 (如 A, B, C):</label>
                <input type="text" id="shiftCode" maxlength="10" required style="text-transform: uppercase;">
            </div>
            <div class="form-group">
                <label>开始时间:</label>
                <input type="time" id="shiftStartTime" required>
            </div>
            <div class="form-group">
                <label>结束时间:</label>
                <input type="time" id="shiftEndTime" required>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-cancel" onclick="closeShiftModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
                <button class="btn-action btn-save" onclick="saveShift()">
                    <i class="fas fa-check"></i> 保存
                </button>
            </div>
        </div>
    </div>

    <script>
    // 全局变量
    let employees = [];
    let shifts = [];
    let leaveTypes = [];
    
    // 默认假期类型（若数据库暂未配置，则使用此数据）
    const defaultHolidayTypes = [
        {
            code: 'IPH',
            name: 'International Public Holiday',
            description: '国际公共假期',
            color: '#0ea5e9',
            type: 'holiday'
        },
        {
            code: 'ICPH',
            name: 'International School Public Holiday',
            description: '国际学校公共假期',
            color: '#8b5cf6',
            type: 'holiday'
        }
    ];
    let schedules = [];
    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth() + 1;
    let selectedCell = null;
    
    const AUTO_SAVE_DEBOUNCE = 800;
    const autoSaveTimers = new Map();
    
    // 从URL参数获取餐厅，如果没有则默认J1
    const urlParams = new URLSearchParams(window.location.search);
    let currentRestaurant = urlParams.get('restaurant') || 'J1';
    
    // 日期选择器状态
    let currentDateType = null;
    let scheduleDateValue = { year: currentYear, month: currentMonth };
    
    // 编辑模式相关变量
    let isEditMode = true;
    let selectedCells = [];
    let isSelecting = false;
    let selectionStart = null;
    let modifiedCells = new Map(); // 存储修改的单元格数据
    let editModeListenersAttached = false;
    let editModeInfoShown = false;
    let currentDaysInMonth = 31;
    
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

    function findScheduleRecord(employeeId, dateStr) {
        return schedules.find(s => s.employee_id == employeeId && s.schedule_date === dateStr);
    }
    
    function encodeHolidayOverlayData(scheduleRecord) {
        if (!scheduleRecord || !scheduleRecord.value_type || scheduleRecord.value_type === 'holiday') {
            return scheduleRecord?.notes || null;
        }
        return JSON.stringify({
            overlay: true,
            original_type: scheduleRecord.value_type,
            original_code: scheduleRecord.value_code,
            original_notes: scheduleRecord.notes || ''
        });
    }
    
    function decodeHolidayOverlayNotes(notes) {
        if (!notes) return null;
        const trimmed = notes.trim();
        if (!trimmed || trimmed === 'null') return null;
        if (trimmed.startsWith('{')) {
            try {
                const data = JSON.parse(trimmed);
                if (data && (data.overlay || data.original_type || data.original_code || data.original)) {
                    return {
                        type: data.original_type || data.original?.type || 'shift',
                        code: data.original_code || data.original?.code || data.code || '',
                        notes: data.original_notes || data.original?.notes || data.notes || ''
                    };
                }
            } catch (error) {
                // fall through to legacy handling
            }
        }
        return {
            type: 'shift',
            code: trimmed,
            notes: ''
        };
    }
    
    function getEditableDateCells() {
        return Array.from(document.querySelectorAll('.grid-cell.grid-date'));
    }
    
    function focusDateCell(cell) {
        if (!cell) return;
        clearSelection();
        cell.focus();
        if (cell.childNodes.length > 0) {
            const range = document.createRange();
            range.selectNodeContents(cell);
            range.collapse(false);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }
    
    function getAdjacentDateCell(cell, direction) {
        const cells = getEditableDateCells();
        const index = cells.indexOf(cell);
        if (index === -1) return null;
        const days = currentDaysInMonth || 31;
        let targetIndex = null;
        
        switch (direction) {
            case 'left':
                if (index % days !== 0) targetIndex = index - 1;
                break;
            case 'right':
                if ((index + 1) % days !== 0) targetIndex = index + 1;
                break;
            case 'up':
                if (index - days >= 0) targetIndex = index - days;
                break;
            case 'down':
                if (index + days < cells.length) targetIndex = index + days;
                break;
        }
        
        return targetIndex !== null ? cells[targetIndex] : null;
    }
    
    function moveFocusToAdjacentCell(cell, direction) {
        const targetCell = getAdjacentDateCell(cell, direction);
        if (targetCell) {
            setTimeout(() => focusDateCell(targetCell), 0);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // 根据URL参数初始化餐厅显示
        updateRestaurantDisplay();
        
        initScheduleDatePicker();
        loadAllData();
        
        // 点击其他地方关闭下拉菜单
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.enhanced-date-picker')) {
                hideScheduleDropdown();
            }
            // 关闭餐厅选择器下拉菜单
            if (!e.target.closest('.restaurant-selector')) {
                document.getElementById('restaurant-dropdown').classList.remove('show');
            }
        });
    });
    
    // 更新餐厅显示
    function updateRestaurantDisplay() {
        document.getElementById('page-title').textContent = `员工排班管理系统 - ${currentRestaurant}`;
        
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
        loadAllData();
    }
    
    // 初始化日期选择器
    function initScheduleDatePicker() {
        scheduleDateValue = {
            year: currentYear,
            month: currentMonth
        };
        updateScheduleDateDisplay();
    }
    
    // 更新日期显示
    function updateScheduleDateDisplay() {
        document.getElementById('schedule-year-display').textContent = scheduleDateValue.year;
        document.getElementById('schedule-month-display').textContent = String(scheduleDateValue.month).padStart(2, '0');
    }
    
    // 显示日期下拉菜单
    function showScheduleDateDropdown(type) {
        const dropdown = document.getElementById('schedule-dropdown');
        const datePicker = document.getElementById('schedule-date-picker');
        
        currentDateType = type;
        
        // 清除所有active状态
        datePicker.querySelectorAll('.date-part').forEach(part => {
            part.classList.remove('active');
        });
        
        // 激活当前选择的部分
        datePicker.querySelector(`[data-type="${type}"]`).classList.add('active');
        
        // 生成下拉内容
        generateScheduleDropdownContent(type);
        
        // 显示下拉菜单
        dropdown.classList.add('show');
    }
    
    // 隐藏日期下拉菜单
    function hideScheduleDropdown() {
        const dropdown = document.getElementById('schedule-dropdown');
        dropdown.classList.remove('show');
        
        const datePicker = document.getElementById('schedule-date-picker');
        datePicker.querySelectorAll('.date-part').forEach(part => {
            part.classList.remove('active');
        });
        
        currentDateType = null;
    }
    
    // 生成下拉菜单内容
    function generateScheduleDropdownContent(type) {
        const dropdown = document.getElementById('schedule-dropdown');
        dropdown.innerHTML = '';
        
        if (type === 'year') {
            const yearGrid = document.createElement('div');
            yearGrid.className = 'year-grid';
            
            const currentYearNow = new Date().getFullYear();
            const startYear = 2020;
            const endYear = currentYearNow + 1;
            
            for (let year = startYear; year <= endYear; year++) {
                const yearOption = document.createElement('div');
                yearOption.className = 'date-option';
                yearOption.textContent = year;
                
                if (year === scheduleDateValue.year) {
                    yearOption.classList.add('selected');
                }
                
                yearOption.addEventListener('click', function() {
                    selectScheduleDateValue('year', year);
                });
                
                yearGrid.appendChild(yearOption);
            }
            
            dropdown.appendChild(yearGrid);
            
        } else if (type === 'month') {
            const monthGrid = document.createElement('div');
            monthGrid.className = 'month-grid';
            
            for (let month = 1; month <= 12; month++) {
                const monthOption = document.createElement('div');
                monthOption.className = 'date-option';
                monthOption.textContent = month;
                
                if (month === scheduleDateValue.month) {
                    monthOption.classList.add('selected');
                }
                
                monthOption.addEventListener('click', function() {
                    selectScheduleDateValue('month', month);
                });
                
                monthGrid.appendChild(monthOption);
            }
            
            dropdown.appendChild(monthGrid);
        }
    }
    
    // 选择日期值
    function selectScheduleDateValue(type, value) {
        scheduleDateValue[type] = value;
        
        // 更新全局变量
        if (type === 'year') {
            currentYear = value;
        } else if (type === 'month') {
            currentMonth = value;
        }
        
        updateScheduleDateDisplay();
        hideScheduleDropdown();
        
        // 重新加载排班数据
        loadSchedule();
    }
    

    async function loadAllData() {
        await Promise.all([
            loadEmployees(),
            loadShifts(),
            loadLeaveTypes()
        ]);
        loadSchedule();
        updatePrintLegend();
    }
    
    // 更新打印图例内容
    function updatePrintLegend() {
        // 班次图例
        let shiftHtml = '';
        shifts.forEach(shift => {
            shiftHtml += `
                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                    <div style="width: 35px; height: 25px; background: white; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; margin-right: 10px;">${shift.shift_code}</div>
                    <div style="font-size: 11px; color: #333;">${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}</div>
                </div>
            `;
        });
        document.getElementById('printShiftLegend').innerHTML = shiftHtml;
        
        // 请假图例
        let leaveHtml = '';
        leaveTypes.filter(lt => lt.type === 'leave').forEach(leave => {
            const textColor = getContrastColor(leave.color);
            leaveHtml += `
                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                    <div style="width: 45px; height: 25px; background: ${leave.color}; color: ${textColor}; border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; margin-right: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">${leave.code}</div>
                    <div style="font-size: 11px; color: #333;">${leave.name}</div>
                </div>
            `;
        });
        document.getElementById('printLeaveLegend').innerHTML = leaveHtml;
        
        // 假期图例
        let holidayHtml = '';
        leaveTypes.filter(lt => lt.type === 'holiday').forEach(holiday => {
            const textColor = getContrastColor(holiday.color);
            holidayHtml += `
                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                    <div style="width: 55px; height: 25px; background: ${holiday.color}; color: ${textColor}; border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px; margin-right: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">${holiday.code}</div>
                    <div style="font-size: 10px; color: #333;">${holiday.name}</div>
                </div>
            `;
        });
        document.getElementById('printHolidayLegend').innerHTML = holidayHtml;
    }

    async function loadEmployees() {
        try {
            const workAreaFilter = document.getElementById('workAreaFilter');
            const workArea = workAreaFilter ? workAreaFilter.value : '';
            let url = `schedule_api.php?action=get_employees&restaurant=${currentRestaurant}`;
            if (workArea) {
                url += '&work_area=' + workArea;
            }
            
            const response = await fetch(url);
            const result = await response.json();
            if (result.success) {
                employees = result.data;
            }
        } catch (error) {
            console.error('加载员工失败:', error);
        }
    }

    async function loadShifts() {
        try {
            const response = await fetch(`schedule_api.php?action=get_shifts&restaurant=${currentRestaurant}`);
            const result = await response.json();
            console.log('加载班次API响应:', result);
            
            if (result.success) {
                shifts = result.data;
                console.log('班次数据已更新:', shifts);
            } else {
                console.error('加载班次失败:', result.error);
            }
        } catch (error) {
            console.error('加载班次异常:', error);
        }
    }

    function ensureDefaultLeaveTypes() {
        if (!Array.isArray(leaveTypes)) {
            leaveTypes = [];
        }
        defaultHolidayTypes.forEach(defaultType => {
            const exists = leaveTypes.some(
                lt => lt.code === defaultType.code && lt.type === defaultType.type
            );
            if (!exists) {
                leaveTypes.push({ ...defaultType });
            }
        });
    }
    
    async function loadLeaveTypes() {
        try {
            const response = await fetch('schedule_api.php?action=get_leave_types');
            const result = await response.json();
            if (result.success) {
                leaveTypes = result.data;
                ensureDefaultLeaveTypes();
            } else {
                ensureDefaultLeaveTypes();
            }
        } catch (error) {
            console.error('加载假期类型失败:', error);
            ensureDefaultLeaveTypes();
        }
    }

    async function loadSchedule() {
        // currentYear 和 currentMonth 已经由日期选择器更新
        
        const container = document.getElementById('scheduleContainer');
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <div class="loading" style="margin: 0 auto 10px;"></div>
                <div>正在加载排班表...</div>
            </div>
        `;
        
        try {
            await loadEmployees();
            
            const workAreaFilter = document.getElementById('workAreaFilter');
            const workArea = workAreaFilter ? workAreaFilter.value : '';
            let url = `schedule_api.php?action=get_schedules&year=${currentYear}&month=${currentMonth}&restaurant=${currentRestaurant}`;
            if (workArea) {
                url += '&work_area=' + workArea;
            }
            
            const response = await fetch(url);
            const result = await response.json();
            if (result.success) {
                schedules = result.data;
                renderScheduleTable();
                updatePrintLegend();
            }
        } catch (error) {
            console.error('加载排班表失败:', error);
            container.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div class="alert alert-error">加载排班表失败，请重试</div>
                </div>
            `;
        }
    }

    function renderScheduleTable() {
        const container = document.getElementById('scheduleContainer');
        
        if (employees.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6b7280;">
                    <div class="alert alert-error">没有找到员工数据，请先添加员工</div>
                </div>
            `;
            return;
        }
        
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        currentDaysInMonth = daysInMonth;
        const totalCols = 4 + daysInMonth;  // No. + 名字 + 手机 + 职位 + 日期列
        
        // 设置grid-template-columns：固定宽度
        const gridColumns = `50px 150px 120px 150px repeat(${daysInMonth}, 50px)`;
        
        let html = `<div class="schedule-grid" style="grid-template-columns: ${gridColumns};">`;
        
        // 表头
        html += `<div class="grid-cell grid-header sticky-col sticky-col-1">No.</div>`;
        html += `<div class="grid-cell grid-header sticky-col sticky-col-2">名字</div>`;
        html += `<div class="grid-cell grid-header">手机号码</div>`;
        html += `<div class="grid-cell grid-header">职位</div>`;
        
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(currentYear, currentMonth - 1, day);
            const dayOfWeek = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'][date.getDay()];
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            html += `<div class="grid-cell grid-header date-header ${isWeekend ? 'weekend' : ''}" data-date="${dateStr}" data-day="${day}" onclick="setColumnHoliday(event, '${dateStr}', ${day})" title="点击设置整列公共假期">${dayOfWeek}<br>${currentMonth}/${day}</div>`;
        }
        
        // 按部门分组
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
            
            // 部门标题行（横跨所有列）
            html += `<div class="grid-cell grid-department">${dept.name}</div>`;
            
            if (deptEmployees.length > 0) {
                deptEmployees.forEach((employee, index) => {
                    // No.列
                    html += `<div class="grid-cell grid-no sticky-col sticky-col-1">${index + 1}</div>`;
                    // 名字
                    html += `<div class="grid-cell grid-employee-info grid-employee-name sticky-col sticky-col-2"><strong>${employee.name.toUpperCase()}</strong></div>`;
                    // 手机号码
                    html += `<div class="grid-cell grid-employee-info">${employee.phone}</div>`;
                    // 职位
                    html += `<div class="grid-cell grid-employee-info">${employee.position}</div>`;
                    
                    // 日期列
                    for (let day = 1; day <= daysInMonth; day++) {
                        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        const schedule = schedules.find(s => s.employee_id == employee.id && s.schedule_date === dateStr);
                        const date = new Date(currentYear, currentMonth - 1, day);
                        const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                        
                        let cellClass = 'grid-cell grid-date';
                        if (isWeekend) cellClass += ' weekend';
                        if (schedule) cellClass += ' has-value';
                        
                        const cellData = schedule ? getCellDisplayData(schedule) : { color: 'white', textColor: '#000', code: '', showText: true, shiftCode: null };
                        const cellStyle = schedule && cellData.color !== 'transparent' 
                            ? `background: ${cellData.color} !important; color: ${cellData.textColor};` 
                            : `color: ${cellData.textColor};`;
                        
                        html += `<div class="${cellClass}" style="${cellStyle}" onclick="openScheduleModal(${employee.id}, '${dateStr}', '${employee.name}')">`;
                        // 如果有班次代码（在公共假期上加班），显示班次代码
                        if (cellData.shiftCode) {
                            html += cellData.shiftCode;
                        } else if (cellData.showText && cellData.code) {
                            html += cellData.code;
                        } else {
                            html += '&nbsp;';
                        }
                        html += '</div>';
                    }
                });
            }
            // 移除了空行占位逻辑 - 如果没有员工就不显示任何行
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        if (isEditMode) {
            enableEditMode();
        }
    }

    function openScheduleModal(employeeId, dateStr, employeeName) {
        selectedCell = { employeeId, dateStr };
        
        document.getElementById('modalEmployeeInfo').textContent = `${employeeName.toUpperCase()} - ${dateStr}`;
        document.getElementById('scheduleType').value = '';
        document.getElementById('scheduleOptions').innerHTML = '';
        document.getElementById('scheduleNotes').value = '';
        
        const existingSchedule = schedules.find(s => 
            s.employee_id == employeeId && s.schedule_date === dateStr
        );
        
        if (existingSchedule) {
            if (existingSchedule.value_type === 'holiday') {
                const overlayData = decodeHolidayOverlayNotes(existingSchedule.notes);
                if (overlayData && overlayData.code) {
                    const mappedType = overlayData.type === 'leave' ? 'leave' : 'shift';
                    document.getElementById('scheduleType').value = mappedType;
                    document.getElementById('scheduleNotes').value = overlayData.notes || '';
                    updateScheduleOptions();
                    
                    setTimeout(() => {
                        const radio = document.querySelector(`input[name="scheduleValue"][value="${overlayData.code}"]`);
                        if (radio) radio.checked = true;
                    }, 100);
                } else if (existingSchedule.notes && existingSchedule.notes.trim()) {
                    document.getElementById('scheduleType').value = 'shift';
                    document.getElementById('scheduleNotes').value = '';
                    updateScheduleOptions();
                    
                    setTimeout(() => {
                        const radio = document.querySelector(`input[name="scheduleValue"][value="${existingSchedule.notes.trim()}"]`);
                        if (radio) radio.checked = true;
                    }, 100);
                } else {
                    document.getElementById('scheduleType').value = existingSchedule.value_type;
                    document.getElementById('scheduleNotes').value = existingSchedule.notes || '';
                    updateScheduleOptions();
                }
            } else {
                document.getElementById('scheduleType').value = existingSchedule.value_type;
                document.getElementById('scheduleNotes').value = existingSchedule.notes || '';
                updateScheduleOptions();
                
                setTimeout(() => {
                    const radio = document.querySelector(`input[name="scheduleValue"][value="${existingSchedule.value_code}"]`);
                    if (radio) radio.checked = true;
                }, 100);
            }
        }
        
        document.getElementById('scheduleModal').style.display = 'block';
    }

    function updateScheduleOptions() {
        const type = document.getElementById('scheduleType').value;
        const container = document.getElementById('scheduleOptions');
        
        if (!type) {
            container.innerHTML = '<div style="color: #9ca3af; text-align: center; padding: 20px;">请先选择类型</div>';
            return;
        }
        
        let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px;">';
        
        if (type === 'shift') {
            shifts.forEach(shift => {
                html += `<label style="display: block; padding: 12px 8px; background: white; 
                         color: #000; border-radius: 6px; cursor: pointer; 
                         text-align: center; font-weight: 600; border: 2px solid #ddd; transition: all 0.2s;">
                            <input type="radio" name="scheduleValue" value="${shift.shift_code}" style="margin-right: 6px;">
                            ${shift.shift_code}
                            <div style="font-size: 9px; margin-top: 4px; opacity: 0.7;">
                                ${formatTime(shift.start_time)}-${formatTime(shift.end_time)}
                            </div>
                         </label>`;
            });
        } else {
            const filteredLeaveTypes = leaveTypes.filter(lt => lt.type === type);
            filteredLeaveTypes.forEach(leave => {
                const textColor = getContrastColor(leave.color);
                html += `<label style="display: block; padding: 12px 8px; background: ${leave.color}; 
                         color: ${textColor}; border-radius: 6px; cursor: pointer; 
                         text-align: center; font-weight: 600; border: 2px solid transparent; transition: all 0.2s;">
                            <input type="radio" name="scheduleValue" value="${leave.code}" style="margin-right: 6px;">
                            ${leave.code}
                            <div style="font-size: 9px; margin-top: 4px; opacity: 0.9;">${leave.name}</div>
                         </label>`;
            });
        }
        
        html += '</div>';
        container.innerHTML = html;
        
        // 添加选中效果
        container.querySelectorAll('label').forEach(label => {
            label.addEventListener('click', function() {
                container.querySelectorAll('label').forEach(l => l.style.borderColor = 'transparent');
                this.style.borderColor = '#ff5c00';
            });
        });
    }

    async function saveSchedule() {
        if (!selectedCell) return;
        
        const type = document.getElementById('scheduleType').value;
        const selectedValue = document.querySelector('input[name="scheduleValue"]:checked');
        const notes = document.getElementById('scheduleNotes').value;
        
        if (!type || !selectedValue) {
            showMessage('请选择排班类型和值', 'error');
            return;
        }
        
        const btn = document.querySelector('#scheduleModal .btn-save');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<div class="loading"></div> 保存中...';
        btn.disabled = true;
        
        try {
            const existingSchedule = findScheduleRecord(selectedCell.employeeId, selectedCell.dateStr);
            let payloadNotes = notes;
            
            if (type === 'holiday') {
                if (existingSchedule && existingSchedule.value_type !== 'holiday') {
                    payloadNotes = encodeHolidayOverlayData(existingSchedule);
                } else if (existingSchedule && existingSchedule.value_type === 'holiday') {
                    payloadNotes = existingSchedule.notes || null;
                }
            }
            
            const response = await fetch('schedule_api.php?action=save_schedule', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    employee_id: selectedCell.employeeId,
                    schedule_date: selectedCell.dateStr,
                    value_type: type,
                    value_code: selectedValue.value,
                    notes: payloadNotes
                })
            });
            
            const result = await response.json();
            if (result.success) {
                showMessage('保存成功', 'success');
                closeScheduleModal();
                loadSchedule();
            } else {
                showMessage('保存失败: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('保存排班失败:', error);
            showMessage('保存失败，请重试', 'error');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    async function deleteCurrentSchedule() {
        if (!selectedCell) return;
        
        if (!confirm('确定要删除这条排班记录吗？')) return;
        
        try {
            const response = await fetch('schedule_api.php?action=delete_schedule', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    employee_id: selectedCell.employeeId,
                    schedule_date: selectedCell.dateStr
                })
            });
            
            const result = await response.json();
            if (result.success) {
                showMessage('删除成功', 'success');
                closeScheduleModal();
                loadSchedule();
            } else {
                showMessage('删除失败', 'error');
            }
        } catch (error) {
            console.error('删除排班失败:', error);
            showMessage('删除失败，请重试', 'error');
        }
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').style.display = 'none';
        selectedCell = null;
    }

    function showManagementPanel(type) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'block';
        modal.style.zIndex = '10000'; // 管理面板在底层
        
        let title = '';
        let content = '';
        
        if (type === 'shifts') {
            title = '<i class="fas fa-clock"></i> 班次管理';
            content = `
                <div id="shiftListModal" class="shift-list"></div>
            `;
        } else if (type === 'employees') {
            title = '<i class="fas fa-users"></i> 员工管理';
            content = `
                <button class="btn-generate" onclick="showAddEmployeeModal()">
                    <i class="fas fa-user-plus"></i> 添加新员工
                </button>
                <div id="employeeListModal" class="employee-list"></div>
            `;
        } else if (type === 'legend') {
            title = '<i class="fas fa-info-circle"></i> 图例说明';
            content = `
                <div class="legend">
                    <div class="legend-section">
                        <h4>📋 请假类型 (Leave Types)</h4>
                        <div id="leaveTypesLegendModal" class="leave-list"></div>
                    </div>
                    <div class="legend-section">
                        <h4>🎉 公共假期 (Public Holidays)</h4>
                        <div id="holidayTypesLegendModal" class="leave-list"></div>
                    </div>
                </div>
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
        if (type === 'shifts') {
            displayShiftsInModal();
        } else if (type === 'employees') {
            displayEmployeesInModal();
            modal.dataset.preventOutsideClose = 'true';
        } else if (type === 'legend') {
            displayLegendInModal();
        }
        
        if (type !== 'employees') {
            modal.onclick = function(event) {
                if (event.target === modal) {
                    modal.remove();
                }
            };
        }
    }

    function displayShiftsInModal() {
        const container = document.getElementById('shiftListModal');
        if (!container) return;
        
        let html = `
            <div style="overflow-x: auto; margin-top: 16px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">序号</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">班次代码</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">餐厅</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">开始时间</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">结束时间</th>
                            <th style="background: #636363; color: white; padding: 12px 8px; border: 1px solid #d1d5db; font-size: 12px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        shifts.forEach((shift, index) => {
            html += `
                <tr style="transition: background 0.2s;">
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center; font-weight: 600;">${index + 1}</td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center; font-weight: 700; font-size: 18px;">${shift.shift_code}</td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center; font-weight: 600; color: #f99e00;">${shift.restaurant || 'J1'}</td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">${formatTime(shift.start_time)}</td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">${formatTime(shift.end_time)}</td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">
                        <button class="btn-action" onclick="editShift(${shift.id}, '${shift.shift_code}', '${shift.start_time}', '${shift.end_time}')" title="编辑班次" style="background: #f99e00; color: white; margin-right: 5px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteShift(${shift.id})" title="删除班次">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        // 添加新行（用于直接添加）
        html += `
                <tr id="newShiftRow" style="background: #f0f9ff;">
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center; font-weight: 600;">${shifts.length + 1}</td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">
                        <input type="text" id="newShiftCode" placeholder="如 A" maxlength="10" 
                               style="width: 80px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-weight: 700; text-transform: uppercase;">
                    </td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center; font-weight: 600; color: #f99e00;">${currentRestaurant}</td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">
                        <input type="time" id="newShiftStart" value="08:00" 
                               style="width: 100px; padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">
                        <input type="time" id="newShiftEnd" value="17:00" 
                               style="width: 100px; padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px 8px; border: 1px solid #d1d5db; text-align: center;">
                        <button class="btn-action btn-save" onclick="saveShiftInline()" title="保存班次">
                            <i class="fas fa-check"></i>
                        </button>
                    </td>
                </tr>
        `;
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = html;
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
                                <button class="btn-action" onclick="editEmployee(${employee.id}, '${employee.name}', '${employee.phone}', '${employee.position}', '${employee.work_area}')" title="编辑员工" style="background: #f99e00; color: white; margin-right: 5px;">
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

    function displayLegendInModal() {
        const leaveContainer = document.getElementById('leaveTypesLegendModal');
        const holidayContainer = document.getElementById('holidayTypesLegendModal');
        if (!leaveContainer || !holidayContainer) return;
        
        let leaveHtml = '';
        leaveTypes.filter(lt => lt.type === 'leave').forEach(leave => {
            const textColor = getContrastColor(leave.color);
            leaveHtml += `<div class="leave-card" style="background: ${leave.color}; color: ${textColor};">
                            <div class="leave-code">${leave.code}</div>
                            <div class="leave-name">${leave.name}</div>
                            <div class="leave-name">${leave.description}</div>
                          </div>`;
        });
        leaveContainer.innerHTML = leaveHtml;
        
        let holidayHtml = '';
        leaveTypes.filter(lt => lt.type === 'holiday').forEach(holiday => {
            const textColor = getContrastColor(holiday.color);
            holidayHtml += `<div class="leave-card" style="background: ${holiday.color}; color: ${textColor};">
                              <div class="leave-code">${holiday.code}</div>
                              <div class="leave-name">${holiday.name}</div>
                              <div class="leave-name">${holiday.description}</div>
                            </div>`;
        });
        holidayContainer.innerHTML = holidayHtml;
    }

    // 获取职位在阶级中的排序索引
    function getPositionRank(workArea, position) {
        const positions = positionHierarchy[workArea] || [];
        const index = positions.indexOf(position);
        return index === -1 ? 999 : index; // 未知职位排在最后
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
                loadSchedule();
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
                loadSchedule();
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

    function showAddShiftModal() {
        document.getElementById('shiftId').value = '';
        document.getElementById('shiftCode').value = '';
        document.getElementById('shiftStartTime').value = '08:00';
        document.getElementById('shiftEndTime').value = '17:00';
        document.getElementById('shiftModalTitle').innerHTML = '<i class="fas fa-clock"></i> 添加班次';
        document.getElementById('shiftModal').style.display = 'block';
    }
    
    function editShift(id, code, startTime, endTime) {
        document.getElementById('shiftId').value = id;
        document.getElementById('shiftCode').value = code;
        document.getElementById('shiftStartTime').value = startTime;
        document.getElementById('shiftEndTime').value = endTime;
        document.getElementById('shiftModalTitle').innerHTML = '<i class="fas fa-clock"></i> 编辑班次';
        document.getElementById('shiftCode').disabled = true; // 编辑时不允许修改班次代码
        document.getElementById('shiftModal').style.display = 'block';
    }
    
    async function saveShift() {
        const id = document.getElementById('shiftId').value;
        const code = document.getElementById('shiftCode').value.trim().toUpperCase();
        const startTime = document.getElementById('shiftStartTime').value;
        const endTime = document.getElementById('shiftEndTime').value;
        
        if (!code || !startTime || !endTime) {
            showMessage('请填写所有字段', 'error');
            return;
        }
        
        const isEdit = id !== '';
        
        try {
            const action = isEdit ? 'update_shift' : 'add_shift';
            const requestData = isEdit 
                ? { id, start_time: startTime, end_time: endTime }
                : { shift_code: code, restaurant: currentRestaurant, start_time: startTime, end_time: endTime };
            
            const response = await fetch(`schedule_api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            if (result.success) {
                showMessage(isEdit ? '班次更新成功' : '班次添加成功', 'success');
                document.getElementById('shiftCode').disabled = false; // 恢复编辑状态
                closeShiftModal();
                await loadShifts();
                // 如果管理面板打开着，刷新它
                const shiftListModal = document.getElementById('shiftListModal');
                if (shiftListModal) {
                    displayShiftsInModal();
                }
            } else {
                showMessage((isEdit ? '更新失败: ' : '添加失败: ') + result.error, 'error');
            }
        } catch (error) {
            console.error(isEdit ? '更新班次失败:' : '添加班次失败:', error);
            showMessage(isEdit ? '更新失败，请重试' : '添加失败，请重试', 'error');
        }
    }

    async function saveShiftInline() {
        const code = document.getElementById('newShiftCode').value.trim().toUpperCase();
        const startTime = document.getElementById('newShiftStart').value;
        const endTime = document.getElementById('newShiftEnd').value;
        
        if (!code || !startTime || !endTime) {
            showMessage('请填写所有字段', 'error');
            return;
        }
        
        const btn = document.querySelector('#newShiftRow .btn-save');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<div class="loading" style="width: 16px; height: 16px;"></div>';
        btn.disabled = true;
        
        try {
            const response = await fetch('schedule_api.php?action=add_shift', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    shift_code: code,
                    restaurant: currentRestaurant,
                    start_time: startTime,
                    end_time: endTime
                })
            });
            
            const result = await response.json();
            console.log('添加班次API响应:', result);
            
            if (result.success) {
                showMessage('班次添加成功', 'success');
                await loadShifts();
                console.log('重新加载后的班次数据:', shifts);
                displayShiftsInModal();
            } else {
                showMessage('添加失败: ' + (result.error || '未知错误'), 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        } catch (error) {
            console.error('添加班次失败:', error);
            showMessage('添加失败，请重试', 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    async function deleteShift(id) {
        if (!confirm('确定要删除这个班次吗？')) return;
        
        try {
            console.log('删除班次ID:', id);
            
            const response = await fetch('schedule_api.php?action=delete_shift', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            
            const result = await response.json();
            console.log('删除班次API响应:', result);
            
            if (result.success) {
                showMessage('删除成功', 'success');
                await loadShifts();
                console.log('重新加载后的班次数据:', shifts);
                displayShiftsInModal();
            } else {
                showMessage('删除失败: ' + (result.error || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('删除班次失败:', error);
            showMessage('删除失败，请重试', 'error');
        }
    }

    function closeShiftModal() {
        document.getElementById('shiftModal').style.display = 'none';
        document.getElementById('shiftCode').disabled = false; // 恢复编辑状态
    }

    function getLeaveTextColor(code) {
        const upper = (code || '').toUpperCase();
        return (upper === 'RO' || upper === 'DO') ? '#FFFFFF' : '#000000';
    }
    
    function getCellDisplayData(schedule) {
        let color = 'transparent';
        let textColor = '#000000';  // 默认黑色字体
        let code = schedule.value_code;
        let showText = true;
        let shiftCode = null;
        
        if (schedule.value_type === 'shift') {
            // 班次显示为黑色文字，无背景
            color = 'transparent';
            showText = true;
        } else if (schedule.value_type === 'leave') {
            const leaveType = leaveTypes.find(lt => lt.code === schedule.value_code);
            if (leaveType) {
                color = leaveType.color;
                textColor = getLeaveTextColor(schedule.value_code);
                showText = true;
            }
        } else if (schedule.value_type === 'holiday') {
            const holidayType = leaveTypes.find(lt => lt.code === schedule.value_code);
            const overlayData = decodeHolidayOverlayNotes(schedule.notes);
            
            if (overlayData && overlayData.type === 'leave') {
                const leaveType = leaveTypes.find(lt => lt.code === overlayData.code && lt.type === 'leave');
                color = leaveType ? leaveType.color : (holidayType ? holidayType.color : '#f3f4f6');
                textColor = getLeaveTextColor(overlayData.code);
                shiftCode = overlayData.code;
                showText = true;
            } else {
                if (holidayType) {
                    color = holidayType.color;
                }
                showText = false;
                
                if (overlayData && overlayData.code) {
                    shiftCode = overlayData.code;
                    textColor = '#000000';
                } else if (schedule.notes && schedule.notes.trim() && !schedule.notes.includes('整列设置')) {
                    shiftCode = schedule.notes.trim();
                    textColor = '#000000';
                }
            }
        }
        
        return { color, textColor, code, showText, shiftCode };
    }

    function getContrastColor(color) {
        if (!color || color === 'transparent' || color === 'white') return '#000000';
        
        let r, g, b;
        
        // 处理 hex 格式 (#RRGGBB)
        if (color.startsWith('#')) {
            r = parseInt(color.substr(1, 2), 16);
            g = parseInt(color.substr(3, 2), 16);
            b = parseInt(color.substr(5, 2), 16);
        }
        // 处理 rgb() 或 rgba() 格式
        else if (color.startsWith('rgb')) {
            const match = color.match(/\d+/g);
            if (match && match.length >= 3) {
                r = parseInt(match[0]);
                g = parseInt(match[1]);
                b = parseInt(match[2]);
            } else {
                return '#000000';
            }
        } else {
            return '#000000';
        }
        
        // 计算亮度
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return brightness > 155 ? '#000000' : '#ffffff';
    }

    function formatTime(timeStr) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        const hour = parseInt(parts[0]);
        const minute = parts[1];
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minute}${ampm}`;
    }

    function getWorkAreaName(area) {
        const names = {
            'service_line': 'Service Line',
            'sushi_bar': 'Sushi Bar',
            'kitchen': 'Kitchen'
        };
        return names[area] || area;
    }

    // 下载PDF - 单页横向长页面，包含表格和图例
    async function downloadPDFDirect() {
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
            
            // 获取当前月份的天数
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const part1Days = Math.ceil(daysInMonth / 2);
            
            // 创建两个Grid表格
            const grid1 = createGridTable(1, part1Days, true);   // 第一页：含员工信息
            const grid2 = createGridTable(part1Days + 1, daysInMonth, false);  // 第二页：不含员工信息
            
            // 创建图例
            const legend = createLegendElement();
            
            // 创建第二页容器（Grid表格 + 图例）
            const page2Container = document.createElement('div');
            page2Container.style.cssText = 'display: flex; gap: 20px; align-items: flex-start; background: white;';
            page2Container.appendChild(grid2);
            page2Container.appendChild(legend);
            
            // 添加到body（隐藏）
            grid1.style.position = 'absolute';
            grid1.style.left = '-9999px';
            page2Container.style.position = 'absolute';
            page2Container.style.left = '-9999px';
            
            document.body.appendChild(grid1);
            document.body.appendChild(page2Container);
            
            // 创建PDF
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: 'a4'
            });
            
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            
            // 截取第一页
            const canvas1 = await html2canvas(grid1, {
                scale: 2.5,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            });
            
            // 截取第二页
            const canvas2 = await html2canvas(page2Container, {
                scale: 2.5,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            });
            
            const imgData1 = canvas1.toDataURL('image/png');
            const imgData2 = canvas2.toDataURL('image/png');
            
            // 使用统一的缩放比例（使用较小的比例）
            const ratio1 = Math.min((pdfWidth - 40) / canvas1.width, (pdfHeight - 40) / canvas1.height);
            const ratio2 = Math.min((pdfWidth - 40) / canvas2.width, (pdfHeight - 40) / canvas2.height);
            const unifiedRatio = Math.min(ratio1, ratio2);
            
            // 第一页
            const w1 = canvas1.width * unifiedRatio;
            const h1 = canvas1.height * unifiedRatio;
            const x1 = (pdfWidth - w1) / 2;
            const y1 = 20;
            pdf.addImage(imgData1, 'PNG', x1, y1, w1, h1);
            
            // 第二页
            pdf.addPage();
            const w2 = canvas2.width * unifiedRatio;
            const h2 = canvas2.height * unifiedRatio;
            const x2 = (pdfWidth - w2) / 2;
            const y2 = 20;
            pdf.addImage(imgData2, 'PNG', x2, y2, w2, h2);
            
            // 清理临时元素
            grid1.remove();
            page2Container.remove();
            
            // 下载PDF（包含餐厅名称）
            pdf.save(`排班表_${currentYear}年${currentMonth}月_${currentRestaurant}.pdf`);
            
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
    
    // 创建Grid表格用于PDF导出
    function createGridTable(startDay, endDay, includeEmployeeInfo = true) {
        const daysInRange = endDay - startDay + 1;
        
        // 根据是否包含员工信息设置列宽
        let gridColumns;
        if (includeEmployeeInfo) {
            // 包含员工信息：No.(50px) + 名字(150px) + 手机(120px) + 职位(150px) + 日期(50px×n)
            gridColumns = `50px 150px 120px 150px repeat(${daysInRange}, 50px)`;
        } else {
            // 不包含员工信息：只有日期列
            gridColumns = `repeat(${daysInRange}, 50px)`;
        }
        
        const gridDiv = document.createElement('div');
        gridDiv.className = 'schedule-grid';
        gridDiv.style.gridTemplateColumns = gridColumns;
        gridDiv.style.border = '2px solid #000';
        gridDiv.style.fontSize = '9px';
        
        let html = '';
        
        // 表头
        if (includeEmployeeInfo) {
            html += `<div class="grid-cell grid-header" style="padding: 6px;">No.</div>`;
            html += `<div class="grid-cell grid-header" style="padding: 6px;">名字</div>`;
            html += `<div class="grid-cell grid-header" style="padding: 6px;">手机号码</div>`;
            html += `<div class="grid-cell grid-header" style="padding: 6px;">职位</div>`;
        }
        
        for (let day = startDay; day <= endDay; day++) {
            const date = new Date(currentYear, currentMonth - 1, day);
            const dayOfWeek = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'][date.getDay()];
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
            const bgColor = isWeekend ? '#ff9800' : '#636363';
            html += `<div class="grid-cell grid-header ${isWeekend ? 'weekend' : ''}" style="background: ${bgColor}; padding: 6px; line-height: 1.1;">${dayOfWeek}<br>${currentMonth}/${day}</div>`;
        }
        
        // 按部门分组
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
            
            // 部门标题行
            html += `<div class="grid-cell grid-department" style="padding: 8px;">${dept.name}</div>`;
            
            if (deptEmployees.length > 0) {
                deptEmployees.forEach((employee, index) => {
                    // 员工信息列（如果需要）
                    if (includeEmployeeInfo) {
                        html += `<div class="grid-cell grid-no" style="padding: 5px; font-weight: bold;">${index + 1}</div>`;
                        html += `<div class="grid-cell grid-employee-info" style="padding: 5px;"><strong>${employee.name.toUpperCase()}</strong></div>`;
                        html += `<div class="grid-cell grid-employee-info" style="padding: 5px;">${employee.phone}</div>`;
                        html += `<div class="grid-cell grid-employee-info" style="padding: 5px;">${employee.position}</div>`;
                    }
                    
                    // 日期列
                    for (let day = startDay; day <= endDay; day++) {
                        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        const schedule = schedules.find(s => s.employee_id == employee.id && s.schedule_date === dateStr);
                        const date = new Date(currentYear, currentMonth - 1, day);
                        const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                        
                        let cellClass = 'grid-cell grid-date';
                        let cellBg = isWeekend ? '#fff9e6' : 'white';
                        let cellText = '';
                        let textColor = '#000';
                        
                        if (schedule) {
                            const cellData = getCellDisplayData(schedule);
                            if (cellData.color !== 'transparent') {
                                cellBg = cellData.color;
                            }
                            textColor = cellData.textColor;
                            if (cellData.shiftCode) {
                                cellText = cellData.shiftCode;
                            } else if (cellData.showText && cellData.code) {
                                cellText = cellData.code;
                            } else {
                                cellText = '';
                            }
                        }
                        
                        html += `<div class="${cellClass}" style="background: ${cellBg}; color: ${textColor}; padding: 4px; font-weight: bold;">${cellText || '&nbsp;'}</div>`;
                    }
                });
            }
            // 移除了空行占位逻辑 - 如果没有员工就不显示任何行
        });
        
        gridDiv.innerHTML = html;
        return gridDiv;
    }
    
    // 创建图例元素
    function createLegendElement() {
        const legendDiv = document.createElement('div');
        legendDiv.style.cssText = `
            background: white;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            width: 380px;
            flex-shrink: 0;
        `;
        
        let html = '<h3 style="text-align: center; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #000;">班次与假期图例</h3>';
        html += '<div style="display: flex; flex-direction: column; gap: 15px;">';
        
        // 班次类型
        html += '<div>';
        html += '<h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">班次 (Shifts)</h4>';
        shifts.forEach(shift => {
            html += `
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <div style="
                        width: 45px;
                        height: 35px;
                        background: white;
                        border: 2px solid #000;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 16px;
                        margin-right: 12px;
                    ">${shift.shift_code}</div>
                    <div style="font-size: 14px; color: #333; font-weight: 500;">
                        ${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        // 请假类型
        html += '<div>';
        html += '<h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">请假 (Leave)</h4>';
        leaveTypes.filter(lt => lt.type === 'leave').forEach(leave => {
            const textColor = getContrastColor(leave.color);
            html += `
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <div style="
                        width: 55px;
                        height: 35px;
                        background: ${leave.color};
                        color: ${textColor};
                        border: 1px solid #333;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 13px;
                        margin-right: 12px;
                    ">${leave.code}</div>
                    <div style="font-size: 13px; color: #333; font-weight: 500;">${leave.name}</div>
                </div>
            `;
        });
        html += '</div>';
        
        // 公共假期
        html += '<div>';
        html += '<h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">假期 (Holiday)</h4>';
        leaveTypes.filter(lt => lt.type === 'holiday').forEach(holiday => {
            const textColor = getContrastColor(holiday.color);
            html += `
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <div style="
                        width: 65px;
                        height: 35px;
                        background: ${holiday.color};
                        color: ${textColor};
                        border: 1px solid #333;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 12px;
                        margin-right: 12px;
                    ">${holiday.code}</div>
                    <div style="font-size: 13px; color: #333; font-weight: 500;">${holiday.name}</div>
                </div>
            `;
        });
        html += '</div>';
        
        html += '</div>';
        legendDiv.innerHTML = html;
        return legendDiv;
    }
    
    // 创建部分表格（指定日期范围）
    function createPartialTable(startDay, endDay, includeEmployeeInfo) {
        const table = document.createElement('table');
        table.style.cssText = 'border-collapse: collapse; font-size: 9px; width: auto;';
        
        const daysInRange = endDay - startDay + 1;
        
        // 固定的行高，确保两个表格高度一致
        const headerHeight = '22px';
        const cellHeight = '20px';
        const deptRowHeight = '24px';
        
        // 表头
        let html = '<thead><tr>';
        
        if (includeEmployeeInfo) {
            html += `<th style="border: 1px solid #000; padding: 6px 4px; background: #636363; color: white; font-weight: bold; min-width: 30px; height: ${headerHeight}; text-align: center;">No.</th>`;
            html += `<th style="border: 1px solid #000; padding: 6px 4px; background: #636363; color: white; font-weight: bold; min-width: 80px; height: ${headerHeight};">名字</th>`;
            html += `<th style="border: 1px solid #000; padding: 6px 4px; background: #636363; color: white; font-weight: bold; min-width: 70px; height: ${headerHeight};">手机号码</th>`;
            html += `<th style="border: 1px solid #000; padding: 6px 4px; background: #636363; color: white; font-weight: bold; min-width: 75px; height: ${headerHeight};">职位</th>`;
        }
        
        for (let day = startDay; day <= endDay; day++) {
            const date = new Date(currentYear, currentMonth - 1, day);
            const dayOfWeek = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'][date.getDay()];
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
            const bgColor = isWeekend ? '#fff9e6' : '#636363';
            const textColor = isWeekend ? '#000' : 'white';
            html += `<th style="border: 1px solid #000; padding: 6px 3px; background: ${bgColor}; color: ${textColor}; font-weight: bold; min-width: 38px; text-align: center; height: ${headerHeight};">${dayOfWeek}<br>${currentMonth}/${day}</th>`;
        }
        html += '</tr></thead>';
        
        // 表体
        html += '<tbody>';
        
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
            
            // 部门标题行 - 固定高度
            const colspanCount = includeEmployeeInfo ? (4 + daysInRange) : daysInRange;
            html += `<tr style="height: ${deptRowHeight};"><td colspan="${colspanCount}" style="border: 1px solid #000; padding: 8px; background: #636363; color: white; font-weight: bold; text-align: left; height: ${deptRowHeight};">${dept.name}</td></tr>`;
            
            if (deptEmployees.length > 0) {
                deptEmployees.forEach((employee, empIndex) => {
                    html += `<tr style="height: ${cellHeight};">`;
                    
                    if (includeEmployeeInfo) {
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; text-align: center; height: ${cellHeight}; font-weight: bold;">${empIndex + 1}</td>`;
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; text-align: left; height: ${cellHeight};"><strong>${employee.name.toUpperCase()}</strong></td>`;
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; text-align: center; height: ${cellHeight};">${employee.phone}</td>`;
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; text-align: center; height: ${cellHeight};">${employee.position}</td>`;
                    }
                    
                    for (let day = startDay; day <= endDay; day++) {
                        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        const schedule = schedules.find(s => s.employee_id == employee.id && s.schedule_date === dateStr);
                        const date = new Date(currentYear, currentMonth - 1, day);
                        const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                        
                        let cellBg = isWeekend ? '#fff9e6' : 'white';
                        let cellText = '';
                        let textColor = '#000';
                        
                        if (schedule) {
                            const cellData = getCellDisplayData(schedule);
                            if (cellData.color !== 'transparent') {
                                cellBg = cellData.color;
                            }
                            textColor = cellData.textColor;
                            if (cellData.shiftCode) {
                                cellText = cellData.shiftCode;
                            } else if (cellData.showText && cellData.code) {
                                cellText = cellData.code;
                            } else {
                                cellText = '';
                            }
                        }
                        
                        html += `<td style="border: 1px solid #000; padding: 4px 2px; background: ${cellBg}; color: ${textColor}; text-align: center; font-weight: bold; height: ${cellHeight};">${cellText || '&nbsp;'}</td>`;
                    }
                    html += '</tr>';
                });
            } else {
                // 空行占位 - 固定高度
                for (let i = 0; i < 3; i++) {
                    html += `<tr style="height: ${cellHeight};">`;
                    if (includeEmployeeInfo) {
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; height: ${cellHeight};"></td>`;
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; height: ${cellHeight};"></td>`;
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; height: ${cellHeight};"></td>`;
                        html += `<td style="border: 1px solid #000; padding: 5px 4px; height: ${cellHeight};"></td>`;
                    }
                    for (let day = startDay; day <= endDay; day++) {
                        html += `<td style="border: 1px solid #000; padding: 4px 2px; height: ${cellHeight};"></td>`;
                    }
                    html += '</tr>';
                }
            }
        });
        
        html += '</tbody>';
        table.innerHTML = html;
        return table;
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


    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            if (event.target.dataset.preventOutsideClose === 'true' || event.target.id === 'employeeModal') {
                return;
            }
            
            event.target.style.display = 'none';
            
            if (event.target.id === 'columnHolidayModal') {
                clearColumnSelection();
            }
        }
    }
    
    // 设置整列假期
    let selectedColumnDates = [];
    let lastSelectedColumnDay = null;
    function clearColumnSelection() {
        selectedColumnDates = [];
        lastSelectedColumnDay = null;
        updateColumnSelectionDisplay();
        updateColumnModalInfo();
    }
    
    function updateColumnSelectionDisplay() {
        const headers = document.querySelectorAll('.grid-cell.grid-header.date-header');
        headers.forEach(header => {
            const headerDate = header.dataset.date;
            if (selectedColumnDates.some(item => item.dateStr === headerDate)) {
                header.classList.add('selected-column');
            } else {
                header.classList.remove('selected-column');
            }
        });
    }
    
    function updateColumnModalInfo() {
        const infoEl = document.getElementById('columnDateInfo');
        if (!infoEl) return;
        
        if (selectedColumnDates.length === 0) {
            infoEl.textContent = '请选择要设置为公共假期的日期列';
            return;
        }
        
        if (selectedColumnDates.length === 1) {
            const only = selectedColumnDates[0];
            infoEl.textContent = `设置 ${currentMonth}月${only.day}日 为公共假期`;
            return;
        }
        
        const daysText = selectedColumnDates
            .map(item => `${currentMonth}月${item.day}日`)
            .join('、');
        infoEl.textContent = `已选择 ${selectedColumnDates.length} 天：${daysText}`;
    }
    
    function buildHolidaySchedulePayload(employeeId, dateStr, valueType, holidayCode) {
        const existingSchedule = findScheduleRecord(employeeId, dateStr);
        let notes = null;
        
        if (existingSchedule) {
            if (existingSchedule.value_type === valueType && existingSchedule.value_code === holidayCode) {
                return null; // 无需重复设置
            }
            if (existingSchedule.value_type === 'holiday' && valueType === 'holiday') {
                notes = existingSchedule.notes || null;
            } else {
                notes = encodeHolidayOverlayData(existingSchedule);
            }
        }
        
        return {
            employee_id: employeeId,
            schedule_date: dateStr,
            value_type: valueType,
            value_code: holidayCode,
            notes
        };
    }

    function determineCellValueType(value) {
        if (!value) return null;
        const upperValue = value.toUpperCase();
        if (shifts.find(shift => shift.shift_code === upperValue)) {
            return { type: 'shift', code: upperValue };
        }
        const leave = leaveTypes.find(lt => lt.code === upperValue && lt.type === 'leave');
        if (leave) {
            return { type: 'leave', code: upperValue };
        }
        const holiday = leaveTypes.find(lt => lt.code === upperValue && lt.type === 'holiday');
        if (holiday) {
            return { type: 'holiday', code: upperValue };
        }
        return { type: 'shift', code: upperValue };
    }
    
    function updateLocalScheduleRecord(record) {
        if (!record) return;
        const existing = findScheduleRecord(record.employee_id, record.schedule_date);
        if (existing) {
            existing.value_type = record.value_type;
            existing.value_code = record.value_code;
            existing.notes = record.notes;
        } else {
            schedules.push({
                employee_id: record.employee_id,
                schedule_date: record.schedule_date,
                value_type: record.value_type,
                value_code: record.value_code,
                notes: record.notes || null
            });
        }
    }
    
    function removeLocalScheduleRecord(employeeId, dateStr) {
        const index = schedules.findIndex(s => s.employee_id == employeeId && s.schedule_date === dateStr);
        if (index !== -1) {
            schedules.splice(index, 1);
        }
    }
    
    async function saveScheduleRequest(payload) {
        const response = await fetch('schedule_api.php?action=save_schedule', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || '自动保存失败');
        }
        return result;
    }
    
    async function deleteScheduleRequest(employeeId, dateStr) {
        const response = await fetch('schedule_api.php?action=delete_schedule', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                employee_id: employeeId,
                schedule_date: dateStr
            })
        });
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || '删除失败');
        }
        return result;
    }
    
    function scheduleAutoSave(cell) {
        const cellData = getCellData(cell);
        if (!cellData) return;
        const key = cellData.key;
        if (autoSaveTimers.has(key)) {
            clearTimeout(autoSaveTimers.get(key));
        }
        autoSaveTimers.set(key, setTimeout(() => {
            autoSaveTimers.delete(key);
            autoSaveCell(cell, cellData);
        }, AUTO_SAVE_DEBOUNCE));
    }
    
    async function autoSaveCell(cell, cellDataOverride = null) {
        const cellData = cellDataOverride || getCellData(cell);
        if (!cellData) return;
        const key = cellData.key;
        const value = (cell.textContent || '').trim().toUpperCase();
        const existingSchedule = findScheduleRecord(cellData.employeeId, cellData.dateStr);
        
        try {
            if (!value) {
                if (existingSchedule) {
                    if (existingSchedule.value_type === 'holiday') {
                        await saveScheduleRequest({
                            employee_id: cellData.employeeId,
                            schedule_date: cellData.dateStr,
                            value_type: 'holiday',
                            value_code: existingSchedule.value_code,
                            notes: null
                        });
                        existingSchedule.notes = null;
                        const displayData = getCellDisplayData(existingSchedule);
                        if (displayData) {
                            cell.style.background = displayData.color !== 'transparent' ? displayData.color : '';
                            cell.style.color = displayData.textColor || '#000';
                            if (displayData.shiftCode) {
                                cell.textContent = displayData.shiftCode;
                            } else {
                                cell.innerHTML = '&nbsp;';
                            }
                        }
                    } else {
                        await deleteScheduleRequest(cellData.employeeId, cellData.dateStr);
                        removeLocalScheduleRecord(cellData.employeeId, cellData.dateStr);
                        cell.style.background = '';
                        cell.style.color = '#000';
                        cell.innerHTML = '&nbsp;';
                    }
                } else {
                    cell.style.background = '';
                    cell.style.color = '#000';
                    cell.innerHTML = '&nbsp;';
                }
                modifiedCells.delete(key);
                cell.classList.remove('modified');
                return;
            }
            
            const valueInfo = determineCellValueType(value);
            if (!valueInfo) return;
            
            let notes = null;
            if (valueInfo.type === 'holiday') {
                if (existingSchedule && existingSchedule.value_type !== 'holiday') {
                    notes = encodeHolidayOverlayData(existingSchedule);
                } else if (existingSchedule && existingSchedule.value_type === 'holiday') {
                    notes = existingSchedule.notes || null;
                }
            } else if (existingSchedule && existingSchedule.value_type === valueInfo.type) {
                notes = existingSchedule.notes || null;
            }
            
            await saveScheduleRequest({
                employee_id: cellData.employeeId,
                schedule_date: cellData.dateStr,
                value_type: valueInfo.type,
                value_code: valueInfo.code,
                notes
            });
            
            updateLocalScheduleRecord({
                employee_id: cellData.employeeId,
                schedule_date: cellData.dateStr,
                value_type: valueInfo.type,
                value_code: valueInfo.code,
                notes
            });
            
            modifiedCells.delete(key);
            cell.classList.remove('modified');
        } catch (error) {
            console.error('自动保存失败:', error);
            showMessage('自动保存失败，请稍后重试', 'error');
        }
    }
    
    function setColumnHoliday(event, dateStr, day) {
        if (event) {
            event.stopPropagation();
        }
        
        // 直接使用当前点击的日期，取消多选与清除功能
        selectedColumnDates = [{
            dateStr,
            day
        }];
        lastSelectedColumnDay = day;
        updateColumnSelectionDisplay();
        
        // 只显示公共假期类型（holiday）
        const holidayOptions = leaveTypes.filter(lt => lt.type === 'holiday');
        let html = '';
        
        if (holidayOptions.length === 0) {
            html = '<div class="alert alert-error">暂无公共假期类型</div>';
        } else {
            holidayOptions.forEach(holiday => {
                const textColor = getContrastColor(holiday.color);
                html += `
                    <div onclick="applyColumnHoliday('${holiday.code}', '${holiday.type}')" 
                         style="padding: 16px; background: ${holiday.color}; color: ${textColor}; 
                                border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600; 
                                transition: all 0.2s; border: 3px solid transparent;">
                        <div style="font-size: 16px; margin-bottom: 4px;">${holiday.code}</div>
                        <div style="font-size: 10px; opacity: 0.9;">${holiday.name}</div>
                    </div>
                `;
            });
        }
        
        document.getElementById('columnHolidayOptions').innerHTML = html;
        updateColumnModalInfo();
        document.getElementById('columnHolidayModal').style.display = 'block';
    }
    
    async function applyColumnHoliday(code, type) {
        if (selectedColumnDates.length === 0 || employees.length === 0) {
            showMessage('请先选择要设置的日期列', 'error');
            return;
        }
        
        const selectedText = selectedColumnDates.map(item => `${currentMonth}月${item.day}日`).join('、');
        
        if (!confirm(`确定要将以下日期设置为 ${code} 假期吗？\n${selectedText}\n这将影响所有员工。`)) {
            return;
        }
        
        try {
            const scheduleData = [];
            employees.forEach(employee => {
                selectedColumnDates.forEach(item => {
                        const payload = buildHolidaySchedulePayload(employee.id, item.dateStr, type, code);
                    if (payload) {
                        scheduleData.push(payload);
                    }
                });
            });
            
            if (scheduleData.length === 0) {
                showMessage('所选日期已设置为该假期', 'info');
                closeColumnHolidayModal();
                return;
            }
            
            const response = await fetch('schedule_api.php?action=save_schedules_batch', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ schedules: scheduleData })
            });
            
            const result = await response.json();
            if (result.success) {
                showMessage(`已将 ${selectedColumnDates.length} 天设置为 ${code} 假期`, 'success');
                closeColumnHolidayModal();
                loadSchedule();
            } else {
                showMessage('设置失败: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('设置整列假期失败:', error);
            showMessage('设置失败，请重试', 'error');
        }
    }
    
    async function clearColumnSchedule() {
        if (selectedColumnDates.length === 0 || employees.length === 0) {
            showMessage('请先选择要清除的日期列', 'error');
            return;
        }
        
        const selectedText = selectedColumnDates.map(item => `${currentMonth}月${item.day}日`).join('、');
        
        if (!confirm(`确定要清除以下日期的所有排班吗？\n${selectedText}`)) {
            return;
        }
        
        try {
            const restoreSchedules = [];
            const deleteTargets = [];
            
            employees.forEach(employee => {
                selectedColumnDates.forEach(item => {
                    const existingSchedule = findScheduleRecord(employee.id, item.dateStr);
                    if (!existingSchedule || existingSchedule.value_type !== 'holiday') {
                        return;
                    }
                    const overlayData = decodeHolidayOverlayNotes(existingSchedule.notes);
                    if (overlayData && overlayData.code) {
                        restoreSchedules.push({
                            employee_id: employee.id,
                            schedule_date: item.dateStr,
                            value_type: overlayData.type || 'shift',
                            value_code: overlayData.code,
                            notes: overlayData.notes || null
                        });
                    } else {
                        deleteTargets.push({
                            employee_id: employee.id,
                            schedule_date: item.dateStr
                        });
                    }
                });
            });
            
            if (restoreSchedules.length === 0 && deleteTargets.length === 0) {
                showMessage('所选日期没有假期记录可清除', 'info');
                closeColumnHolidayModal();
                return;
            }
            
            if (restoreSchedules.length > 0) {
                await fetch('schedule_api.php?action=save_schedules_batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ schedules: restoreSchedules })
                });
            }
            
            if (deleteTargets.length > 0) {
                await Promise.all(deleteTargets.map(target => 
                    fetch('schedule_api.php?action=delete_schedule', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(target)
                    })
                ));
            }
            
            showMessage(`已清除 ${selectedColumnDates.length} 天的假期标记`, 'success');
            closeColumnHolidayModal();
            loadSchedule();
        } catch (error) {
            console.error('清除整列失败:', error);
            showMessage('清除失败，请重试', 'error');
        }
    }
    
    function closeColumnHolidayModal() {
        document.getElementById('columnHolidayModal').style.display = 'none';
        clearColumnSelection();
    }
    
    // 复制当前月排班到下一个月
    async function copyToNextMonth() {
        if (schedules.length === 0) {
            showMessage('当前月份没有排班数据可复制', 'error');
            return;
        }
        
        // 计算下一个月
        let nextYear = currentYear;
        let nextMonth = currentMonth + 1;
        if (nextMonth > 12) {
            nextMonth = 1;
            nextYear += 1;
        }
        
        const nextMonthText = `${nextYear}年${nextMonth}月`;
        const currentMonthText = `${currentYear}年${currentMonth}月`;
        
        if (!confirm(`确定要将 ${currentMonthText} 的排班表复制到 ${nextMonthText} 吗？\n\n注意：如果 ${nextMonthText} 已有排班数据，将会被覆盖。`)) {
            return;
        }
        
        // 显示加载提示
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'copyLoading';
        loadingDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 20000;
            text-align: center;
        `;
        loadingDiv.innerHTML = `
            <div class="loading" style="margin: 0 auto 15px; width: 30px; height: 30px;"></div>
            <h3 style="color: #111; margin-bottom: 8px; font-size: 16px;">正在复制排班表...</h3>
            <p style="color: #666; font-size: 13px;">正在处理 ${schedules.length} 条记录</p>
        `;
        document.body.appendChild(loadingDiv);
        
        try {
            // 获取当前月的天数和下月的天数
            const currentDaysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const nextDaysInMonth = new Date(nextYear, nextMonth, 0).getDate();
            
            // 准备复制的排班数据
            const copiedSchedules = [];
            
            schedules.forEach(schedule => {
                const scheduleDate = new Date(schedule.schedule_date);
                const day = scheduleDate.getDate();
                
                // 只复制日期在下月范围内的排班（例如：当前月有31天，下月只有30天，则只复制1-30日）
                if (day <= nextDaysInMonth) {
                    const newDate = `${nextYear}-${String(nextMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    copiedSchedules.push({
                        employee_id: schedule.employee_id,
                        schedule_date: newDate,
                        value_type: schedule.value_type,
                        value_code: schedule.value_code,
                        notes: schedule.notes || ''
                    });
                }
            });
            
            if (copiedSchedules.length === 0) {
                document.getElementById('copyLoading').remove();
                showMessage('没有可复制的排班数据', 'error');
                return;
            }
            
            // 批量保存
            const response = await fetch('schedule_api.php?action=save_schedules_batch', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ schedules: copiedSchedules })
            });
            
            const result = await response.json();
            
            document.getElementById('copyLoading').remove();
            
            if (result.success) {
                showMessage(`成功复制 ${copiedSchedules.length} 条排班到 ${nextMonthText}！`, 'success');
                
                // 切换到下一个月
                currentYear = nextYear;
                currentMonth = nextMonth;
                scheduleDateValue = { year: nextYear, month: nextMonth };
                updateScheduleDateDisplay();
                
                // 重新加载数据
                await loadSchedule();
            } else {
                showMessage('复制失败: ' + (result.error || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('复制排班失败:', error);
            if (document.getElementById('copyLoading')) {
                document.getElementById('copyLoading').remove();
            }
            showMessage('复制失败，请重试', 'error');
        }
    }
    
    // ============ 编辑模式功能 ============
    
    // 启用编辑模式
    function enableEditMode() {
        const container = document.getElementById('scheduleContainer');
        if (container) {
            container.classList.add('edit-mode-active');
        }
        
        selectedCells = [];
        const dateCells = document.querySelectorAll('.grid-cell.grid-date');
        
        dateCells.forEach(cell => {
            // 设置可编辑属性
            cell.contentEditable = true;
            cell.classList.add('edit-mode');
            
            // 移除点击事件（防止打开旧的模态框）
            cell.onclick = null;
            
            // 添加输入事件监听
            cell.addEventListener('input', handleCellInput);
            cell.addEventListener('blur', handleCellBlur);
            cell.addEventListener('keydown', handleCellKeydown);
            cell.addEventListener('focus', handleCellFocus);
            cell.addEventListener('click', handleCellClick);
            
            // 添加鼠标选择事件
            cell.addEventListener('mousedown', handleCellMouseDown);
            cell.addEventListener('mouseenter', handleCellMouseEnter);
            cell.addEventListener('mouseup', handleCellMouseUp);
            
            // 添加粘贴事件
            cell.addEventListener('paste', handleCellPaste);
        });
        
        if (!editModeListenersAttached) {
            document.addEventListener('mouseup', handleGlobalMouseUp);
            document.addEventListener('keydown', handleGlobalKeydown);
            editModeListenersAttached = true;
        }
        
        if (!editModeInfoShown) {
            showMessage('已进入编辑模式：内容自动保存，可用 Shift 拖动多选，Enter 批量输入，Delete 清除。', 'info');
            editModeInfoShown = true;
        }
    }
    
    // 禁用编辑模式
    // 处理单元格获得焦点
    function handleCellFocus(e) {
        const cell = e.target;
        const value = cell.textContent.trim();
        
        // 保存原始值和样式，用于后续恢复
        cell.dataset.originalValue = value;
        cell.dataset.originalBgColor = cell.style.background || '';
        cell.dataset.originalTextColor = cell.style.color || '';
        
        // 如果内容是空白字符或空，清空单元格
        if (!value || value === '' || value === '\u00A0') {
            cell.textContent = '';
        } else {
            // 选中全部内容，方便用户直接覆盖
            const range = document.createRange();
            range.selectNodeContents(cell);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }
    
    // 处理单元格点击（非Shift点击时清除选择）
    function handleCellClick(e) {
        if (!e.shiftKey) {
            // 非Shift点击时清除之前的选择
            clearSelection();
        }
    }
    
    // 处理单元格输入
    function handleCellInput(e) {
        const cell = e.target;
        let value = cell.textContent.trim();
        
        // 自动转大写
        value = value.toUpperCase();
        
        // 如果处理后的值与原内容不同，更新单元格内容
        if (cell.textContent.trim() !== value) {
            const selection = window.getSelection();
            let cursorPos = 0;
            
            // 尝试获取当前光标位置
            try {
                const range = selection.getRangeAt(0);
                cursorPos = range.startOffset;
            } catch (e) {
                // 如果获取失败，光标位置设为末尾
                cursorPos = value.length;
            }
            
            cell.textContent = value;
            
            // 恢复光标位置
            if (cell.firstChild && value.length > 0) {
                try {
                    const range = document.createRange();
                    range.setStart(cell.firstChild, Math.min(cursorPos, value.length));
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } catch (e) {
                    // 如果设置光标失败，忽略
                }
            }
        }
        
        // 检查是否有原始背景色（假期颜色）
        const hasHolidayBg = cell.dataset.originalBgColor && 
                            cell.dataset.originalBgColor !== '' && 
                            cell.dataset.originalBgColor !== 'transparent';
        
        // 应用样式，传入是否保留背景色的标志
        applyCellStyle(cell, value, hasHolidayBg);
        
        // 标记为已修改
        cell.classList.add('modified');
        
        // 存储修改
        const cellData = getCellData(cell);
        if (cellData) {
            modifiedCells.set(cellData.key, {
                employeeId: cellData.employeeId,
                dateStr: cellData.dateStr,
                value: value,
                cell: cell,
                keepHolidayBg: hasHolidayBg  // 标记是否保留假期背景
            });
        }
    }
    
    // 处理单元格失焦
    function handleCellBlur(e) {
        const cell = e.target;
        const value = cell.textContent.trim().toUpperCase();
        const originalValue = (cell.dataset.originalValue || '').trim().toUpperCase();
        
        const hasChanged = !(value === originalValue || (value === '' && originalValue === '\u00A0'));
        
        if (!hasChanged) {
            // 恢复原始背景色
            if (cell.dataset.originalBgColor) {
                cell.style.background = cell.dataset.originalBgColor;
            }
            // 恢复原始文字颜色
            if (cell.dataset.originalTextColor) {
                cell.style.color = cell.dataset.originalTextColor;
            } else {
                cell.style.color = '#000';
            }
            
            // 如果原始值是空的，显示空白
            if (!originalValue) {
                cell.innerHTML = '&nbsp;';
            }
            
            // 清除临时数据
            delete cell.dataset.originalValue;
            delete cell.dataset.originalBgColor;
            delete cell.dataset.originalTextColor;
            
            return;
        }
        
        // 如果值为空但原来有内容，说明用户删除了内容
        if (!value && originalValue) {
            cell.style.background = '';
            cell.style.color = '#000';
            cell.innerHTML = '&nbsp;';
        }
        // 如果值为空且原来也是空，保持原样
        else if (!value && !originalValue) {
            cell.style.color = '#000';
            cell.innerHTML = '&nbsp;';
        }
        
        // 清除临时数据
        delete cell.dataset.originalValue;
        delete cell.dataset.originalBgColor;
        delete cell.dataset.originalTextColor;
        
        if (hasChanged) {
            scheduleAutoSave(cell);
        }
    }
    
    // 处理键盘事件（Enter、Escape等）
    function handleCellKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const cell = e.target;
            cell.blur();
            moveFocusToAdjacentCell(cell, 'right');
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            const cell = e.target;
            cell.blur();
            moveFocusToAdjacentCell(cell, 'right');
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            const cell = e.target;
            cell.blur();
            moveFocusToAdjacentCell(cell, 'left');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const cell = e.target;
            cell.blur();
            moveFocusToAdjacentCell(cell, 'up');
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            const cell = e.target;
            cell.blur();
            moveFocusToAdjacentCell(cell, 'down');
        } else if (e.key === 'Escape') {
            e.preventDefault();
            const cell = e.target;
            
            // 恢复保存的原始值和样式
            if (cell.dataset.originalValue !== undefined) {
                const originalValue = cell.dataset.originalValue;
                if (originalValue) {
                    cell.textContent = originalValue;
                } else {
                    cell.innerHTML = '&nbsp;';
                }
                
                if (cell.dataset.originalBgColor) {
                    cell.style.background = cell.dataset.originalBgColor;
                }
                if (cell.dataset.originalTextColor) {
                    cell.style.color = cell.dataset.originalTextColor;
                } else {
                    cell.style.color = '#000';
                }
            } else {
                // 如果没有保存的原始值，从数据库恢复
                const cellData = getCellData(cell);
                if (cellData) {
                    restoreCellOriginalValue(cell, cellData);
                }
            }
            
            // 移除修改标记
            const cellData = getCellData(cell);
            if (cellData) {
                modifiedCells.delete(cellData.key);
                cell.classList.remove('modified');
            }
            
            // 清除临时数据
            delete cell.dataset.originalValue;
            delete cell.dataset.originalBgColor;
            delete cell.dataset.originalTextColor;
            
            cell.blur();
        }
    }
    
    // 获取单元格数据（employeeId 和 dateStr）
    function getCellData(cell) {
        const gridContainer = cell.closest('.schedule-grid');
        if (!gridContainer) return null;
        
        const cells = Array.from(gridContainer.querySelectorAll('.grid-cell'));
        const cellIndex = cells.indexOf(cell);
        
        if (cellIndex === -1) return null;
        
        // 计算列数（包括员工信息列）
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        const totalCols = 4 + daysInMonth; // No. + 名字 + 手机 + 职位 + 日期列
        
        // 计算行和列
        let currentIndex = 0;
        let currentRow = 0;
        let employeeIndex = -1;
        let currentDept = null;
        
        for (let i = 0; i <= cellIndex; i++) {
            const c = cells[i];
            
            if (c.classList.contains('grid-department')) {
                currentDept = c.textContent.trim();
                currentRow++;
                employeeIndex = -1;
                continue;
            }
            
            if (c.classList.contains('grid-header')) {
                continue;
            }
            
            // 检查是否是员工信息行的第一个单元格
            if (c.classList.contains('grid-no')) {
                employeeIndex++;
                currentRow++;
            }
            
            if (c === cell && c.classList.contains('grid-date')) {
                // 找到对应的员工和日期
                const deptKey = getDeptKeyFromName(currentDept);
                const deptEmployees = employees.filter(e => e.work_area === deptKey);
                
                // 按职位阶级排序
                deptEmployees.sort((a, b) => {
                    const rankA = getPositionRank(deptKey, a.position);
                    const rankB = getPositionRank(deptKey, b.position);
                    return rankA - rankB;
                });
                
                if (employeeIndex >= 0 && employeeIndex < deptEmployees.length) {
                    const employee = deptEmployees[employeeIndex];
                    
                    // 计算日期
                    const row = cells.slice(0, i).filter(c => 
                        c.classList.contains('grid-no')).length - 1;
                    const rowCells = [];
                    let rowStart = i;
                    while (rowStart > 0 && !cells[rowStart].classList.contains('grid-no')) {
                        rowStart--;
                    }
                    for (let j = rowStart; j < cells.length; j++) {
                        if (cells[j].classList.contains('grid-date')) {
                            rowCells.push(cells[j]);
                        }
                        if (cells[j + 1] && cells[j + 1].classList.contains('grid-no')) {
                            break;
                        }
                    }
                    
                    const dayIndex = rowCells.indexOf(cell);
                    if (dayIndex >= 0) {
                        const day = dayIndex + 1;
                        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        
                        return {
                            employeeId: employee.id,
                            dateStr: dateStr,
                            key: `${employee.id}-${dateStr}`
                        };
                    }
                }
            }
        }
        
        return null;
    }
    
    // 从部门名称获取部门key
    function getDeptKeyFromName(deptName) {
        const deptMap = {
            'SERVICE LINE': 'service_line',
            'SUSHI BAR': 'sushi_bar',
            'KITCHEN': 'kitchen'
        };
        return deptMap[deptName] || 'service_line';
    }
    
    // 恢复单元格原始值
    function restoreCellOriginalValue(cell, cellData) {
        const schedule = schedules.find(s => 
            s.employee_id == cellData.employeeId && s.schedule_date === cellData.dateStr
        );
        
        if (schedule) {
            const cellDisplay = getCellDisplayData(schedule);
            if (cellDisplay.shiftCode) {
                cell.textContent = cellDisplay.shiftCode;
            } else if (cellDisplay.showText && cellDisplay.code) {
                cell.textContent = cellDisplay.code;
            } else {
                cell.innerHTML = '&nbsp;';
            }
            
            if (cellDisplay.color !== 'transparent') {
                cell.style.background = cellDisplay.color;
            } else {
                cell.style.background = '';
            }
            // 根据类型设置字体颜色
            cell.style.color = cellDisplay.textColor;
        } else {
            cell.innerHTML = '&nbsp;';
            cell.style.background = '';
            cell.style.color = '#000';
        }
    }
    
    // 应用单元格样式（根据输入值自动识别类型）
    function applyCellStyle(cell, value, keepHolidayBg = false) {
        if (!value) {
            if (!keepHolidayBg) {
                cell.style.background = '';
            }
            cell.style.color = '#000';
            return;
        }
        
        // 检查是否是班次代码
        const shift = shifts.find(s => s.shift_code === value);
        if (shift) {
            // 班次：黑色字体
            cell.style.color = '#000';
            // 如果有假期背景则保留，否则透明背景
            if (keepHolidayBg && cell.dataset.originalBgColor) {
                cell.style.background = cell.dataset.originalBgColor;
            } else {
                cell.style.background = '';
            }
            return;
        }
        
        // 检查是否是请假代码
        const leave = leaveTypes.find(lt => lt.code === value && lt.type === 'leave');
        if (leave) {
            cell.style.background = leave.color;
            cell.style.color = getLeaveTextColor(value);
            return;
        }
        
        // 检查是否是假期代码
        const holiday = leaveTypes.find(lt => lt.code === value && lt.type === 'holiday');
        if (holiday) {
            cell.style.background = holiday.color;
            cell.style.color = '#000';  // 假期用黑色字体
            return;
        }
        
        // 未识别的代码
        cell.style.color = '#000';
        if (keepHolidayBg && cell.dataset.originalBgColor) {
            // 保留假期背景色
            cell.style.background = cell.dataset.originalBgColor;
        } else {
            // 无背景
            cell.style.background = '';
        }
    }
    
    // 处理单元格鼠标按下（开始选择）
    function handleCellMouseDown(e) {
        if (e.shiftKey) {
            e.preventDefault();
            isSelecting = true;
            selectionStart = e.target;
            clearSelection();
            e.target.classList.add('selected');
            selectedCells = [e.target];
        }
    }
    
    // 处理单元格鼠标进入（扩展选择）
    function handleCellMouseEnter(e) {
        if (isSelecting && selectionStart) {
            updateSelection(e.target);
        }
    }
    
    // 处理单元格鼠标松开
    function handleCellMouseUp(e) {
        if (isSelecting) {
            isSelecting = false;
        }
    }
    
    // 处理全局鼠标松开
    function handleGlobalMouseUp(e) {
        isSelecting = false;
    }
    
    // 清除选择
    function clearSelection() {
        selectedCells.forEach(cell => cell.classList.remove('selected'));
        selectedCells = [];
    }
    
    // 更新选择范围
    function updateSelection(endCell) {
        if (!selectionStart || !endCell.classList.contains('grid-date')) return;
        
        clearSelection();
        
        const gridContainer = selectionStart.closest('.schedule-grid');
        const allCells = Array.from(gridContainer.querySelectorAll('.grid-cell.grid-date'));
        
        const startIndex = allCells.indexOf(selectionStart);
        const endIndex = allCells.indexOf(endCell);
        
        if (startIndex === -1 || endIndex === -1) return;
        
        const minIndex = Math.min(startIndex, endIndex);
        const maxIndex = Math.max(startIndex, endIndex);
        
        // 计算矩形选择区域
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        
        // 获取开始和结束单元格的行列位置
        const startRow = Math.floor(startIndex / daysInMonth);
        const startCol = startIndex % daysInMonth;
        const endRow = Math.floor(endIndex / daysInMonth);
        const endCol = endIndex % daysInMonth;
        
        const minRow = Math.min(startRow, endRow);
        const maxRow = Math.max(startRow, endRow);
        const minCol = Math.min(startCol, endCol);
        const maxCol = Math.max(startCol, endCol);
        
        // 选择矩形区域内的所有单元格
        for (let row = minRow; row <= maxRow; row++) {
            for (let col = minCol; col <= maxCol; col++) {
                const index = row * daysInMonth + col;
                if (index < allCells.length) {
                    allCells[index].classList.add('selected');
                    selectedCells.push(allCells[index]);
                }
            }
        }
    }
    
    // 处理粘贴事件
    function handleCellPaste(e) {
        e.preventDefault();
        
        const pasteData = e.clipboardData.getData('text');
        if (!pasteData) return;
        
        // 解析粘贴的数据（支持从Excel复制的制表符分隔数据）
        // 清理空行
        const rows = pasteData.split('\n')
            .filter(row => row.trim() !== '')
            .map(row => row.split('\t'));
        
        if (rows.length === 0) return;
        
        // 如果有选中的单元格，从第一个选中的单元格开始粘贴
        let startCell = selectedCells.length > 0 ? selectedCells[0] : e.target;
        
        const gridContainer = startCell.closest('.schedule-grid');
        const allGridCells = Array.from(gridContainer.querySelectorAll('.grid-cell'));
        const startCellIndex = allGridCells.indexOf(startCell);
        
        if (startCellIndex === -1) return;
        
        // 找到起始单元格所在行的行首（向前找到grid-no）
        let rowStartIndex = startCellIndex;
        while (rowStartIndex > 0 && !allGridCells[rowStartIndex].classList.contains('grid-no')) {
            rowStartIndex--;
        }
        
        // 从行首开始，收集这一行的所有日期单元格
        const currentRowCells = [];
        let idx = rowStartIndex;
        while (idx < allGridCells.length) {
            const cell = allGridCells[idx];
            
            if (cell.classList.contains('grid-date')) {
                currentRowCells.push(cell);
            }
            
            // 遇到下一行的grid-no或grid-department，停止
            if (idx > rowStartIndex && 
                (cell.classList.contains('grid-no') || cell.classList.contains('grid-department'))) {
                break;
            }
            
            idx++;
        }
        
        // 找到起始单元格在当前行中的索引位置
        const startColIndex = currentRowCells.indexOf(startCell);
        if (startColIndex === -1) {
            console.error('无法找到起始单元格在行中的位置');
            return;
        }
        
        // 批量粘贴
        let pastedCount = 0;
        
        for (let rowIdx = 0; rowIdx < rows.length; rowIdx++) {
            // 获取目标行的所有日期单元格
            let targetRowCells;
            
            if (rowIdx === 0) {
                // 第一行就是当前行
                targetRowCells = currentRowCells;
            } else {
                // 找到下面第 rowIdx 行的单元格
                targetRowCells = getRowCellsByOffset(allGridCells, rowStartIndex, rowIdx);
            }
            
            if (!targetRowCells || targetRowCells.length === 0) break;
            
            // 在这一行粘贴数据
            for (let colIdx = 0; colIdx < rows[rowIdx].length; colIdx++) {
                const targetColIndex = startColIndex + colIdx;
                
                if (targetColIndex >= targetRowCells.length) break;
                
                const targetCell = targetRowCells[targetColIndex];
                let value = rows[rowIdx][colIdx].trim().toUpperCase();
                
                if (value) {
                    targetCell.textContent = value;
                    
                    // 检查目标单元格是否有假期背景色
                    const hasHolidayBg = targetCell.style.background && 
                                        targetCell.style.background !== '' && 
                                        targetCell.style.background !== 'transparent' &&
                                        targetCell.style.background !== 'white';
                    
                    applyCellStyle(targetCell, value, hasHolidayBg);
                    targetCell.classList.add('modified');
                    
                    // 存储修改
                    const cellData = getCellData(targetCell);
                    if (cellData) {
                        modifiedCells.set(cellData.key, {
                            employeeId: cellData.employeeId,
                            dateStr: cellData.dateStr,
                            value: value,
                            cell: targetCell,
                            keepHolidayBg: hasHolidayBg
                        });
                        scheduleAutoSave(targetCell);
                        pastedCount++;
                    }
                } else {
                    // 粘贴空值，清除单元格
                    targetCell.innerHTML = '&nbsp;';
                    targetCell.style.background = '';
                    targetCell.style.color = '#000';
                    targetCell.classList.add('modified');
                    
                    const cellData = getCellData(targetCell);
                    if (cellData) {
                        modifiedCells.set(cellData.key, {
                            employeeId: cellData.employeeId,
                            dateStr: cellData.dateStr,
                            value: '',
                            cell: targetCell
                        });
                        scheduleAutoSave(targetCell);
                        pastedCount++;
                    }
                }
            }
        }
        
        showMessage(`已粘贴 ${pastedCount} 个单元格`, 'success');
    }
    
    // 根据行偏移量获取目标行的所有日期单元格
    function getRowCellsByOffset(allGridCells, startRowIndex, rowOffset) {
        // 从起始行开始，向下跳过 rowOffset 行
        let currentIndex = startRowIndex;
        let rowsSkipped = 0;
        
        while (currentIndex < allGridCells.length) {
            const cell = allGridCells[currentIndex];
            
            // 每遇到一个grid-no，表示新的一行
            if (cell.classList.contains('grid-no')) {
                if (rowsSkipped === rowOffset) {
                    // 找到目标行，收集这一行的所有日期单元格
                    const rowCells = [];
                    let idx = currentIndex;
                    
                    while (idx < allGridCells.length) {
                        const c = allGridCells[idx];
                        
                        if (c.classList.contains('grid-date')) {
                            rowCells.push(c);
                        }
                        
                        // 遇到下一行的grid-no或grid-department，停止
                        if (idx > currentIndex && 
                            (c.classList.contains('grid-no') || c.classList.contains('grid-department'))) {
                            break;
                        }
                        
                        idx++;
                    }
                    
                    return rowCells;
                }
                
                rowsSkipped++;
            }
            
            currentIndex++;
        }
        
        return null;
    }
    
    // 处理全局键盘事件（Ctrl+C 复制选中的单元格）
    function handleGlobalKeydown(e) {
        if (!isEditMode) return;
        
        // Ctrl+C 或 Cmd+C
        if ((e.ctrlKey || e.metaKey) && e.key === 'c' && selectedCells.length > 0) {
            e.preventDefault();
            copyCellsToClipboard();
        }
        
        // Delete 键删除选中单元格的内容
        if (e.key === 'Delete' && selectedCells.length > 0) {
            e.preventDefault();
            selectedCells.forEach(cell => {
                cell.innerHTML = '&nbsp;';
                cell.style.background = '';
                cell.style.color = '#000';
                cell.classList.add('modified');
                
                const cellData = getCellData(cell);
                if (cellData) {
                    modifiedCells.set(cellData.key, {
                        employeeId: cellData.employeeId,
                        dateStr: cellData.dateStr,
                        value: '',
                        cell: cell
                    });
                    scheduleAutoSave(cell);
                }
            });
            showMessage(`已清除 ${selectedCells.length} 个单元格`, 'success');
        }
        
        // Enter 键批量输入选中单元格的内容
        if (e.key === 'Enter' && selectedCells.length > 1) {
            e.preventDefault();
            showBatchInputModal();
        }
    }
    
    // 显示批量输入对话框
    function showBatchInputModal() {
        if (selectedCells.length === 0) {
            showMessage('请先选择要编辑的单元格', 'error');
            return;
        }
        
        const modal = document.createElement('div');
        modal.id = 'batchInputModal';
        modal.className = 'modal';
        modal.style.display = 'block';
        modal.style.zIndex = '10002';
        
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <span class="close" onclick="closeBatchInputModal()">&times;</span>
                    <h3 style="margin-top: 8px;"><i class="fas fa-edit"></i> 批量输入</h3>
                    <p style="color: #6b7280; font-size: 13px; margin-top: 4px;">已选择 ${selectedCells.length} 个单元格</p>
                </div>
                <div class="form-group">
                    <label>输入班次/请假/假期代码：</label>
                    <input type="text" id="batchInputValue" placeholder="如: A, AL, PH 等" 
                           style="width: 100%; padding: 12px; border: 2px solid #d1d5db; border-radius: 6px; 
                                  font-size: 16px; font-weight: 600; text-transform: uppercase; text-align: center;"
                           autofocus>
                    <div style="margin-top: 12px; padding: 12px; background: #f3f4f6; border-radius: 6px; font-size: 12px; color: #6b7280;">
                        <strong>提示：</strong>
                        <ul style="margin: 8px 0 0 20px; line-height: 1.8;">
                            <li>输入班次代码（如 A、B、C）</li>
                            <li>输入请假代码（如 AL、MC）</li>
                            <li>输入假期代码（如 PH、CNY）</li>
                            <li>自动转换为大写并识别类型</li>
                        </ul>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-action btn-cancel" onclick="closeBatchInputModal()">
                        <i class="fas fa-times"></i> 取消
                    </button>
                    <button class="btn-action btn-save" onclick="applyBatchInput()">
                        <i class="fas fa-check"></i> 应用到所有选中
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // 自动聚焦到输入框
        setTimeout(() => {
            const input = document.getElementById('batchInputValue');
            if (input) {
                input.focus();
                // 添加Enter键提交
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyBatchInput();
                    }
                });
            }
        }, 100);
        
        // 点击外部关闭
        modal.onclick = function(event) {
            if (event.target === modal) {
                closeBatchInputModal();
            }
        };
    }
    
    // 关闭批量输入对话框
    function closeBatchInputModal() {
        const modal = document.getElementById('batchInputModal');
        if (modal) {
            modal.remove();
        }
    }
    
    // 应用批量输入
    function applyBatchInput() {
        const input = document.getElementById('batchInputValue');
        if (!input) return;
        
        const value = input.value.trim().toUpperCase();
        
        if (!value) {
            showMessage('请输入代码', 'error');
            input.focus();
            return;
        }
        
        // 检查是否是有效的代码
        const isShift = shifts.find(s => s.shift_code === value);
        const isLeave = leaveTypes.find(lt => lt.code === value && lt.type === 'leave');
        const isHoliday = leaveTypes.find(lt => lt.code === value && lt.type === 'holiday');
        
        if (!isShift && !isLeave && !isHoliday) {
            if (!confirm(`代码 "${value}" 未识别，确定要继续吗？\n它将被当作班次代码处理。`)) {
                return;
            }
        }
        
        let appliedCount = 0;
        
        // 应用到所有选中的单元格
        selectedCells.forEach(cell => {
            // 检查目标单元格是否有假期背景色
            const hasHolidayBg = cell.style.background && 
                                cell.style.background !== '' && 
                                cell.style.background !== 'transparent' &&
                                cell.style.background !== 'white' &&
                                cell.style.background !== 'rgb(255, 255, 255)';
            
            cell.textContent = value;
            applyCellStyle(cell, value, hasHolidayBg);
            cell.classList.add('modified');
            
            // 存储修改
            const cellData = getCellData(cell);
            if (cellData) {
            modifiedCells.set(cellData.key, {
                employeeId: cellData.employeeId,
                dateStr: cellData.dateStr,
                value: value,
                cell: cell,
                keepHolidayBg: hasHolidayBg
            });
            scheduleAutoSave(cell);
                appliedCount++;
            }
        });
        
        // 关闭对话框
        closeBatchInputModal();
        
        // 清除选择
        clearSelection();
        
        showMessage(`已将 "${value}" 应用到 ${appliedCount} 个单元格`, 'success');
    }
    
    // 复制选中的单元格到剪贴板
    function copyCellsToClipboard() {
        if (selectedCells.length === 0) return;
        
        const gridContainer = selectedCells[0].closest('.schedule-grid');
        const allCells = Array.from(gridContainer.querySelectorAll('.grid-cell.grid-date'));
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        
        // 找到选择范围的边界
        const indices = selectedCells.map(cell => allCells.indexOf(cell));
        const rows = indices.map(i => Math.floor(i / daysInMonth));
        const cols = indices.map(i => i % daysInMonth);
        
        const minRow = Math.min(...rows);
        const maxRow = Math.max(...rows);
        const minCol = Math.min(...cols);
        const maxCol = Math.max(...cols);
        
        // 构建复制的文本（制表符分隔）
        let copyText = '';
        for (let row = minRow; row <= maxRow; row++) {
            const rowData = [];
            for (let col = minCol; col <= maxCol; col++) {
                const index = row * daysInMonth + col;
                if (index < allCells.length) {
                    const cell = allCells[index];
                    const value = cell.textContent.trim();
                    // 空白符号（&nbsp;）也视为空
                    rowData.push((value === '' || value === '\u00A0') ? '' : value);
                } else {
                    rowData.push('');
                }
            }
            copyText += rowData.join('\t') + '\n';
        }
        
        // 复制到剪贴板
        navigator.clipboard.writeText(copyText).then(() => {
            showMessage(`已复制 ${selectedCells.length} 个单元格`, 'success');
        }).catch(err => {
            console.error('复制失败:', err);
        });
    }
    
    // 保存所有更改
    async function saveAllChanges() {
        if (modifiedCells.size === 0) {
            showMessage('没有需要保存的更改', 'info');
            return;
        }
        
        if (!confirm(`确定要保存 ${modifiedCells.size} 个更改吗？`)) {
            return;
        }
        
        const saveBtn = document.getElementById('saveAllBtn');
        const originalHtml = saveBtn.innerHTML;
        saveBtn.innerHTML = '<div class="loading" style="width: 16px; height: 16px;"></div> 保存中...';
        saveBtn.disabled = true;
        
        try {
            const schedulesToSave = [];
            
            for (const [key, data] of modifiedCells.entries()) {
                const value = data.value.trim().toUpperCase();
                
                if (!value) {
                    // 删除记录
                    await fetch('schedule_api.php?action=delete_schedule', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            employee_id: data.employeeId,
                            schedule_date: data.dateStr
                        })
                    });
                } else {
                    // 确定类型
                    let valueType = 'shift';
                    let valueCode = value;
                    let notes = null;
                    
                    const shift = shifts.find(s => s.shift_code === value);
                    const leave = leaveTypes.find(lt => lt.code === value && lt.type === 'leave');
                    const holiday = leaveTypes.find(lt => lt.code === value && lt.type === 'holiday');
                    
                    if (leave) {
                        valueType = 'leave';
                    } else if (holiday) {
                        valueType = 'holiday';
                    } else if (shift && data.keepHolidayBg) {
                        // 如果是班次代码且保留假期背景，说明是在假期加班
                        // 需要找到原来的假期记录
                        const existingSchedule = schedules.find(s => 
                            s.employee_id == data.employeeId && s.schedule_date === data.dateStr
                        );
                        
                        if (existingSchedule && existingSchedule.value_type === 'holiday') {
                            // 保持假期类型，但将班次代码放到notes
                            valueType = 'holiday';
                            valueCode = existingSchedule.value_code;
                            notes = value; // 班次代码保存到notes
                        }
                    }
                    
                    schedulesToSave.push({
                        employee_id: data.employeeId,
                        schedule_date: data.dateStr,
                        value_type: valueType,
                        value_code: valueCode,
                        notes: notes
                    });
                }
            }
            
            // 批量保存
            if (schedulesToSave.length > 0) {
                const response = await fetch('schedule_api.php?action=save_schedules_batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ schedules: schedulesToSave })
                });
                
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.error || '保存失败');
                }
            }
            
            showMessage(`成功保存 ${modifiedCells.size} 个更改！`, 'success');
            
            // 清除修改标记
            modifiedCells.forEach(data => {
                data.cell.classList.remove('modified');
            });
            modifiedCells.clear();
            
            // 重新加载数据
            await loadSchedule();
            
        } catch (error) {
            console.error('保存失败:', error);
            showMessage('保存失败: ' + error.message, 'error');
        } finally {
            saveBtn.innerHTML = originalHtml;
            saveBtn.disabled = false;
        }
    }
    
    </script>
</body>
</html>
