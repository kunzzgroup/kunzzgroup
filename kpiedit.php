<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅数据管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            font-size: clamp(8px, 0.74vw, 14px);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            color: #111827;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(22px, 1.67vw, 32px);
        }
        
        .header h1 {
            font-size: clamp(20px, 2.6vw, 50px);
            font-weight: bold;
            color: #000000ff;
        }
        
        .header .controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* 餐厅选择器样式 */
        .restaurant-selector {
            background: white;
            border-radius: clamp(6px, 0.63vw, 12px);
            padding: 6px;
            display: flex;
            gap: 0;
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            align-items: center;
        }

        .restaurant-prefix {
            background: #f99e00;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.42vw, 8px) 0 0 clamp(4px, 0.42vw, 8px);
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 700;
            width: clamp(26px, 2.08vw, 40px);
            text-align: center;
        }

        .number-dropdown {
            position: relative;
            display: inline-block;
        }

        .number-btn {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: 0 8px 8px 0;
            border: none;
            cursor: pointer;
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
            color: #000000ff;
            width: clamp(30px, 3.13vw, 60px);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .number-btn:hover {
            background: rgba(88, 62, 4, 0.1);
        }

        .number-dropdown-menu {
            display: none;
            position: absolute;
            top: clamp(29px, 2.29vw, 44px);
            right: clamp(-24px, -1.25vw, -15px);
            background: white;
            border: 2px solid #000000ff;
            border-radius: clamp(8px, 0.63vw, 12px);
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.15);
            z-index: 1000;
            padding: clamp(6px, 0.42vw, 8px);
            min-width: 90px;
        }

        .number-dropdown-menu.show {
            display: block;
        }

        .number-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
        }

        .number-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: clamp(20px, 2.08vw, 40px);
            height: clamp(20px, 2.08vw, 40px);
            border: 2px solid #e5e7eb;
            background: white;
            color: #000000ff;
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border-radius: clamp(4px, 0.42vw, 8px);
            transition: all 0.2s ease;
        }

        .number-item:hover {
            background-color: #f3f4f6;
            color: black;
            border-color: #d1d5db;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .number-item.selected {
            background: #f99e00;
            color: white;
            border-color: #f99e00;
        }

        .restaurant-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: transparent;
            color: #583e04;
            position: relative;
            min-width: 80px;
        }

        .restaurant-btn.active {
            background: #583e04;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.25);
        }

        .restaurant-btn:hover:not(.active) {
            background: rgba(88, 62, 4, 0.1);
            transform: translateY(-1px);
        }

        /* 下拉菜单样式 */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 120%;
            left: 2;
            background: white;
            border: 2px solid #583e04;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.15);
            z-index: 1000;
            min-width: 100%;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 10px 20px;
            border: none;
            background: transparent;
            color: #583e04;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-align: left;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: rgba(88, 62, 4, 0.1);
        }

        .dropdown-item:first-child {
            border-radius: 6px 6px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 6px 6px;
        }

        .back-button {
            background-color: #583e04;
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
        }
        
        .back-button:hover {
            background-color: #462d03;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        /* 月份选择器 */
        .month-selector {
            background: white;
            border-radius: 12px;
            padding: clamp(8px, 0.83vw, 16px) clamp(16px, 1.25vw, 24px);
            margin-bottom: clamp(16px, 1.25vw, 24px);
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
        }

        .month-selector select {
            padding: clamp(4px, 0.42vw, 8px) clamp(8px, 0.83vw, 16px);
            border: 1px solid #d1d5db;
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 500;
            background: white;
            color: #000000ff;
            cursor: pointer;
        }

        .month-selector select:focus {
            outline: none;
            border-color: #000000ff;
            box-shadow: 0 0 0 3px rgba(20, 14, 0, 0.1);
        }

        .month-selector label {
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 600;
            color: #000000ff;
        }

        /* Excel样式表格 */
        .excel-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.1);
            overflow: hidden;
            border: 2px solid #000000ff;
            height: 70vh; /* 设置固定高度，70%视窗高度 */
            display: flex;
            flex-direction: column;
        }

        .table-scroll-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed;
        }

        /* 确保表头固定 */
        .excel-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed;
        }

        .excel-table th {
            font-size: clamp(8px, 0.74vw, 14px);
            background: #636363;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) 0;
            text-align: center;
            font-weight: 600;
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 100; /* 改成更高的值 */
            white-space: nowrap;
        }

        .excel-table thead {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #583e04; /* 确保背景色覆盖 */
        }

        .excel-table thead tr {
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .excel-table td {
            font-size: clamp(8px, 0.74vw, 14px);
            padding: 0;
            border: 1px solid #d1d5db;
            text-align: center;
            position: relative;
            height: clamp(20px, 3.3vw, 40px);
        }

        .excel-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .excel-table tbody tr:hover {
            background-color: #fff9f1 !important;
        }

        .excel-table tbody tr:hover td {
            background-color: #fff9f1 !important;
        }

        /* 确保hover效果覆盖所有单元格样式 */
        .excel-table tbody tr:hover .date-cell,
        .excel-table tbody tr:hover .calculated-cell,
        .excel-table tbody tr:hover .weekend,
        .excel-table tbody tr:hover .excel-input.has-data,
        .excel-table tbody tr:hover .excel-input.no-data,
        .excel-table tbody tr:hover .input-container {
            background-color: #fff9f1 !important;
        }

        /* 编辑行不受hover影响 */
        .excel-table tbody tr.editing-row:hover td {
            background-color: #fff9f1 !important;
        }

        /* 日期列样式 */
        .date-cell {
            background: #f8f5eb !important;
            font-weight: 600;
            color: #583e04;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            min-width: 100px;
        }

        .weekend {
            background: #fef2f2 !important;
            color: #dc2626;
        }

        /* 输入框容器样式 */
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            height: clamp(20px, 3.3vw, 40px);
        }

        .currency-prefix {
            position: absolute;
            left: clamp(2px, 0.42vw, 8px);
            color: #6b7280;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            pointer-events: none;
            z-index: 2;
        }

        /* 输入框样式 */
        .excel-input {
            width: 100%;
            height: clamp(20px, 3.3vw, 40px);
            border: none;
            background: #fee2e2;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            transition: all 0.2s;
        }

        .excel-input.currency-input {
            padding-left: clamp(16px, 1.67vw, 32px);
            text-align: right;
            padding-right: clamp(2px, 0.42vw, 8px);
            background: #f0fdf4;
        }

        .excel-input:focus {
            background: #fff;
            border: 2px solid #583e04;
            outline: none;
            z-index: 5;
            position: relative;
        }

        /* 计算列样式 */
        .calculated-cell {
            background: #f0f9ff !important;
            color: #0369a1;
            font-weight: 600;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            min-width: 100px;
        }

        .action-buttons {
            padding: clamp(12px, 1.25vw, 24px);
            background: #ffffffff;
            border-bottom: 2px solid #000000ff; /* 改成 border-bottom */
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: clamp(0px, 0.83vw, 16px);
            flex-shrink: 0; /* 防止按钮区域被压缩 */
        }

        .btn {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.42vw, 8px);
            border: none;
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #f99e00;
            color: white;
            white-space: nowrap;
        }
        
        .btn-primary:hover {
            background-color: #f98500ff;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
        }

        /* 统计信息 */
        .stats-info {
            display: flex;
            gap: clamp(0px, 1.25vw, 24px);
            align-items: center;
            font-size: 12px;
            color: #6b7280;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-value {
            font-size: clamp(8px, 0.84vw, 16px);
            font-weight: bold;
            color: #000000ff;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .header .controls {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
            }
            
            .month-selector {
                flex-direction: column;
                gap: 16px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 12px;
            }
            
            .stats-info {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .stat-item {
                min-width: auto;
                width: 100%;
            }
        }

        /* 加载状态 */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #583e04;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 提示信息 */
        .alert {
            padding: 12px 16px;
            margin-bottom: 16px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        /* 隐藏类 */
        .hidden {
            display: none;
        }

        /* 删除单日数据按钮 */
        .delete-day-btn {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: clamp(4px, 0.32vw, 6px);
            width: clamp(18px, 1.67vw, 32px);
            height: clamp(18px, 1.67vw, 32px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: clamp(6px, 0.63vw, 12px);
            margin: clamp(1px, 0.31vw, 3px);
        }

        .delete-day-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .delete-day-btn i {
            font-size: clamp(6px, 0.63vw, 12px);
        }

        .delete-day-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .delete-day-btn:disabled:hover {
            background: #9ca3af;
            transform: none;
            box-shadow: none;
        }

        /* 编辑按钮样式 */
        .edit-btn {
            background: #f59e0b;
            color: white;
            border: none;
            border-radius: clamp(4px, 0.32vw, 6px);
            width: clamp(18px, 1.67vw, 32px);
            height: clamp(18px, 1.67vw, 32px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: clamp(6px, 0.63vw, 12px);
            margin: clamp(1px, 0.21vw, 2px);
        }

        .edit-btn:hover {
            background: #d97706;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .edit-btn i {
            font-size: clamp(6px, 0.63vw, 12px);
        }

        .edit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .edit-btn.save-mode {
            background: #10b981;
        }

        .edit-btn.save-mode:hover {
            background: #059669;
        }

        /* 只读输入框样式 */
        .excel-input.readonly {
            background: #f9fafb !important;
            pointer-events: none;
            cursor: not-allowed;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .excel-input.currency-input.readonly {
            background: #f9fafb;
        }

        /* 操作列样式调整 */
        .action-cell {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: clamp(2px, 0.21vw, 4px);
            padding: clamp(2px, 0.21vw, 4px);
        }

        /* 编辑行样式 */
        .excel-table tr.editing-row {
            background-color: #d1fae5 !important; /* 浅绿色 */
        }

        .excel-table tr.editing-row td {
            background-color: #d1fae5 !important; /* 浅绿色 */
        }

        .excel-table tr.editing-row .date-cell {
            background-color: #d1fae5 !important; /* 浅绿色 */
        }

        /* 编辑模式下计算列保持原来的蓝色 */
        .excel-table tr.editing-row .calculated-cell {
            background-color: #f0f9ff !important; /* 保持蓝色 */
        }

        .excel-table tr.editing-row .weekend {
            background-color: #d1fae5 !important; /* 浅绿色 */
        }

        /* 编辑模式下的输入框样式 */
        .excel-table tr.editing-row .excel-input {
            background-color: #d1fae5 !important; /* 浅绿色 */
        }

        .excel-table tr.editing-row .excel-input.currency-input {
            background-color: #d1fae5 !important; /* 浅绿色 */
        }

        /* 数据状态颜色 */
        .excel-input.has-data {
            background: #dbeafe !important; /* 浅蓝色 - 有数据 */
        }

        .excel-input.no-data {
            background: #fee2e2 !important; /* 浅红色 - 无数据 */
        }

        .excel-input:focus {
            background: #fff !important;
            border: 2px solid #583e04;
            outline: none;
            z-index: 5;
            position: relative;
        }

        /* 通知容器 */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }

        /* 通知基础样式 */
        .toast {
            width: clamp(100px, 15.63vw, 300px);
            padding: clamp(2px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(6px, 0.42vw, 8px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: clamp(8px, 0.74vw, 14px);
            display: flex;
            align-items: center;
            gap: 12px;
            pointer-events: auto;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(100%);
            opacity: 0;
        }

        /* 通知类型样式 */
        .toast-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9));
            color: white;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .toast-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
            color: white;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .toast-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 0.9));
            color: white;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .toast-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(217, 119, 6, 0.9));
            color: white;
            border-color: rgba(245, 158, 11, 0.3);
        }

        /* 通知图标 */
        .toast-icon {
            font-size: clamp(14px, 0.94vw, 18px);
            flex-shrink: 0;
        }

        /* 通知内容 */
        .toast-content {
            flex: 1;
            font-weight: 500;
            line-height: 1.4;
        }

        /* 关闭按钮 */
        .toast-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            opacity: 0.8;
            transition: opacity 0.2s;
            flex-shrink: 0;
        }

        .toast-close:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
        }

        /* 进度条 */
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 0 0 8px 8px;
            transform-origin: left;
            animation: toastProgress 4s linear forwards;
        }

        @keyframes toastProgress {
            0% {
                transform: scaleX(1);
            }
            100% {
                transform: scaleX(0);
            }
        }

        /* 响应式调整 */
        @media (max-width: 480px) {
            .toast-container {
                bottom: 10px;
                right: 10px;
                left: 10px;
            }
            
            .toast {
                min-width: auto;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>TOKYO JAPANESE CUISINE 数据后台</h1>
            </div>
            <div class="controls">
                <!-- 餐厅选择器 -->
                <div class="restaurant-selector">
                    <div class="restaurant-prefix">J</div>
                    <div class="number-dropdown">
                        <button class="number-btn dropdown-toggle" onclick="toggleNumberDropdown()">
                            1 <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="number-dropdown-menu" id="number-dropdown">
                            <div class="number-grid">
                                <button class="number-item" onclick="selectNumber(1)">1</button>
                                <button class="number-item" onclick="selectNumber(2)">2</button>
                                <button class="number-item" onclick="selectNumber(3)">3</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 月份选择器 -->
        <div class="month-selector">
            <div>
                <label for="year-select">年份:</label>
                <select id="year-select" onchange="loadMonthData()">
                    <!-- 动态生成年份选项 -->
                </select>
            </div>
            <div>
                <label for="month-select">月份:</label>
                <select id="month-select" onchange="loadMonthData()">
                    <option value="1">1月</option>
                    <option value="2">2月</option>
                    <option value="3">3月</option>
                    <option value="4">4月</option>
                    <option value="5">5月</option>
                    <option value="6">6月</option>
                    <option value="7">7月</option>
                    <option value="8">8月</option>
                    <option value="9">9月</option>
                    <option value="10">10月</option>
                    <option value="11">11月</option>
                    <option value="12">12月</option>
                </select>
            </div>
            <div id="current-restaurant-info" class="stat-item">
                <i class="fas fa-store"></i>
                <span>当前: <span class="stat-value">J1</span></span>
            </div>
        </div>
        
        <!-- Excel表格 -->
        <div class="excel-container">
            <div class="action-buttons">
                <div class="stats-info" id="month-stats">
                    <div class="stat-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>已填写: <span class="stat-value" id="filled-days">0</span> 天</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-chart-line"></i>
                        <span>月总净利润额: RM <span class="stat-value" id="total-sales">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>月总利润额: RM <span class="stat-value" id="total-tender">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span>月总顾客人数: <span class="stat-value" id="total-diners">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-table"></i>
                        <span>月总桌数: <span class="stat-value" id="total-tables">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calculator"></i>
                        <span>月总人均消费: RM <span class="stat-value" id="avg-per-customer">0</span></span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button class="btn btn-primary" onclick="saveAllData()">
                        <i class="fas fa-save"></i>
                        保存本月数据
                    </button>
                </div>
            </div>
            <div class="table-scroll-container">
            <table class="excel-table" id="excel-table">
                <thead>
                    <tr>
                        <th style="width: 7%;">日期</th>
                        <th style="width: 8%;">总销售额</th>
                        <th style="width: 6%;">折扣</th>
                        <th style="width: 8%;">净销售额</th>
                        <th style="width: 7%;">税</th>
                        <th style="width: 7%;">服务费</th>
                        <th style="width: 7%;">调整金额</th>
                        <th style="width: 8%;">投标金额</th>
                        <th style="width: 5%;">桌数总数</th>
                        <th style="width: 5%;">顾客总数</th>
                        <th style="width: 8%;">人均消费</th>
                        <th style="width: 5%;">新客人数</th>
                        <th style="width: 5%;">常客人数</th>
                        <th style="width: 7%;">常客人率 (%)</th>
                        <th style="width: 9%;">操作</th>
                    </tr>
                </thead>
                <tbody id="excel-tbody">
                    <!-- 动态生成行 -->
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    <!-- 动态通知内容 -->
    </div>

    <script>
        // API 配置
        const API_BASE_URL = 'kpiapi.php';
        
        // 应用状态
        let currentRestaurant = 'j1';
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth() + 1;
        let monthData = {};
        let isLoading = false;
        let pasteTargetDay = null;

        // 餐厅配置
        const restaurantConfig = {
            j1: { name: 'J1' },
            j2: { name: 'J2' },
            j3: { name: 'J3' }
        };

        // 货币字段列表 - 添加 adj_amount 字段
        const currencyFields = ['gross_sales', 'discounts', 'tax', 'service_fee', 'adj_amount', 'tender_amount'];

        // 初始化应用
        function initApp() {
            initYearSelect();
            initCurrentMonth();
            loadMonthData();
        }

        // 初始化年份选择器
        function initYearSelect() {
            const yearSelect = document.getElementById('year-select');
            const currentYear = new Date().getFullYear();
            
            // 生成从2023年到未来2年的选项
            for (let year = 2023; year <= currentYear + 2; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year + '年';
                if (year === currentYear) {
                    option.selected = true;
                }
                yearSelect.appendChild(option);
            }
        }

        // 初始化当前月份
        function initCurrentMonth() {
            const monthSelect = document.getElementById('month-select');
            monthSelect.value = currentMonth;
        }

        // 返回上一页
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }

        // 切换餐厅
        function switchRestaurant(restaurant) {
            if (currentRestaurant === restaurant || isLoading) return;
            
            currentRestaurant = restaurant;
            
            // 更新按钮状态
            document.querySelectorAll('.restaurant-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.restaurant === restaurant) {
                    btn.classList.add('active');
                }
            });
            
            // 更新餐厅信息显示
            document.querySelector('#current-restaurant-info .stat-value').textContent = 
                restaurantConfig[restaurant].name;
            
            // 重新加载数据
            loadMonthData();
        }

        // API 调用函数 - 修复版本
        async function apiCall(endpoint, options = {}) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                // 先检查HTTP状态码
                if (!response.ok) {
                    throw new Error(`HTTP错误: ${response.status}`);
                }
                
                const data = await response.json();
                
                // 返回完整的响应数据，让调用者处理success字段
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                throw error;
            }
        }

        // 加载月度数据
        async function loadMonthData() {
            if (isLoading) return;
            
            isLoading = true;
            currentYear = parseInt(document.getElementById('year-select').value);
            currentMonth = parseInt(document.getElementById('month-select').value);
            
            try {
                // 获取当月的开始和结束日期
                const startDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-01`;
                const lastDay = new Date(currentYear, currentMonth, 0).getDate();
                const endDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;
                
                const queryParams = new URLSearchParams({
                    action: 'list',
                    restaurant: currentRestaurant,
                    start_date: startDate,
                    end_date: endDate
                });
                
                const result = await apiCall(`?${queryParams}`);
                
                // 即使API返回success: false，也可能有数据
                const data = result.data || [];
                
                // 将数据转换为以日期为键的对象
                monthData = {};
                data.forEach(item => {
                    const day = parseInt(item.date.split('-')[2]);
                    monthData[day] = item;
                });
                
                generateExcelTable();
                updateMonthStats();
                // 更新输入框颜色
                setTimeout(() => {
                    updateInputColors();
                }, 200);
                
            } catch (error) {
                monthData = {};
                generateExcelTable();
                updateMonthStats();
            } finally {
                isLoading = false;
            }
        }

        // 格式化货币输入值显示
        function formatCurrencyDisplay(value) {
            if (!value || value === '') return '';
            const num = parseFloat(value);
            if (isNaN(num)) return '';
            return num.toFixed(2);
        }

        // 生成Excel表格
        function generateExcelTable() {
            const tbody = document.getElementById('excel-tbody');
            tbody.innerHTML = '';
            
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth - 1, day);
                const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                
                const existingData = monthData[day] || {};
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="date-cell ${isWeekend ? 'weekend' : ''}">${currentMonth}月${day}<small> (周${['日', '一', '二', '三', '四', '五', '六'][date.getDay()]})</small></td>
                    <td>
                        <div class="input-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input readonly" data-field="gross_sales" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.gross_sales)}" min="0" step="0.01" 
                                placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)" readonly>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input" data-field="discounts" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.discounts)}" min="0" step="0.01" 
                                placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                        </div>
                    </td>
                    <td class="calculated-cell" id="net-sales-${day}">RM 0.00</td>
                    <td>
                        <div class="input-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input" data-field="tax" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.tax)}" min="0" step="0.01" 
                                placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input" data-field="service_fee" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.service_fee)}" min="0" step="0.01" 
                                placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input" data-field="adj_amount" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.adj_amount)}" step="0.01" 
                                placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                        </div>
                    </td>
                    <td class="calculated-cell" id="tender-amount-${day}">RM 0.00</td>
                    <td><input type="number" class="excel-input" data-field="tables_used" data-day="${day}" 
                        value="${existingData.tables_used || ''}" min="0" max="50" 
                        placeholder="0"></td>
                    <td><input type="number" class="excel-input" data-field="diners" data-day="${day}" 
                        value="${existingData.diners || ''}" min="0" 
                        placeholder="0" onchange="updateCalculations(${day})"></td>
                    <td class="calculated-cell" id="avg-per-diner-${day}">RM 0</td>
                    <td><input type="number" class="excel-input" data-field="new_customers" data-day="${day}" 
                        value="${existingData.new_customers || ''}" min="0" 
                        placeholder="0" onchange="updateCalculations(${day})"></td>
                    <td><input type="number" class="excel-input" data-field="returning_customers" data-day="${day}" 
                        value="${existingData.returning_customers || ''}" min="0" 
                        placeholder="0" onchange="updateCalculations(${day})"></td>
                    <td class="calculated-cell" id="returning-customer-rate-${day}">0%</td>
                    <td class="action-cell">
                        <button class="edit-btn" id="edit-btn-${day}" onclick="toggleEdit(${day})" title="编辑${day}日数据">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="delete-day-btn" onclick="clearDayData(${day})" title="清空${day}日数据">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);

                // 强制设置所有输入框为只读状态
                setTimeout(() => {
                    for (let day = 1; day <= daysInMonth; day++) {
                        setRowReadonly(day, true);
                    }
                }, 0);
                
                // 初始计算
                updateCalculations(day);
            }

            setTimeout(() => {
                updateInputColors();
            }, 100);
        }

        // 格式化货币输入（实时格式化为两位小数）
        function formatCurrencyInput(input) {
            const value = input.value;
            if (value && !isNaN(value)) {
                // 在输入过程中不立即格式化，避免干扰用户输入
                // 只在失去焦点时格式化
            }
        }

        // 完全替换现有的 showAlert 函数
        function showAlert(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            // 先检查并限制通知数量（在添加新通知之前）
            const existingToasts = container.querySelectorAll('.toast');
            while (existingToasts.length >= 3) {
                closeToast(existingToasts[0].id);
                // 立即从DOM移除，不等待动画
                if (existingToasts[0].parentNode) {
                    existingToasts[0].parentNode.removeChild(existingToasts[0]);
                }
                // 重新获取当前通知列表
                existingToasts = container.querySelectorAll('.toast');
            }

            const toastId = 'toast-' + Date.now();
            const iconClass = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle', 
                'info': 'fa-info-circle',
                'warning': 'fa-exclamation-triangle'
            }[type] || 'fa-check-circle';

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.id = toastId;
            toast.innerHTML = `
                <i class="fas ${iconClass} toast-icon"></i>
                <div class="toast-content">${message}</div>
                <button class="toast-close" onclick="closeToast('${toastId}')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="toast-progress"></div>
            `;

            container.appendChild(toast);

            // 显示动画
            setTimeout(() => {
                toast.classList.add('show');
            }, 0);

            // 自动关闭
            setTimeout(() => {
                closeToast(toastId);
            }, 700);
        }

        // 添加关闭通知的函数
        function closeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }

        // 添加关闭所有通知的函数（可选）
        function closeAllToasts() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                closeToast(toast.id);
            });
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);

        // 设置行的只读状态
        function setRowReadonly(day, readonly) {
            const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
            const row = document.querySelector(`input[data-day="${day}"]`).closest('tr');
            
            inputs.forEach(input => {
                if (readonly) {
                    input.classList.add('readonly');
                    input.setAttribute('readonly', 'readonly');
                    input.setAttribute('disabled', 'disabled');
                } else {
                    input.classList.remove('readonly');
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                }
            });
            
            // 切换行的编辑样式
            if (readonly) {
                row.classList.remove('editing-row');
            } else {
                row.classList.add('editing-row');
            }
        }

        // 更新计算字段
        function updateCalculations(day) {
            const grossSales = parseFloat(getInputValue('gross_sales', day)) || 0;
            const discounts = parseFloat(getInputValue('discounts', day)) || 0;
            const tax = parseFloat(getInputValue('tax', day)) || 0;
            const serviceFee = parseFloat(getInputValue('service_fee', day)) || 0;
            const adjAmount = parseFloat(getInputValue('adj_amount', day)) || 0;
            const tenderAmount = parseFloat(getInputValue('tender_amount', day)) || 0;
            const diners = parseInt(getInputValue('diners', day)) || 0;
            const returningCustomers = parseInt(getInputValue('returning_customers', day)) || 0;
            const newCustomers = parseInt(getInputValue('new_customers', day)) || 0;
    
            // 净销售额 = 总销售额 - 折扣
            const netSales = grossSales - discounts;
            document.getElementById(`net-sales-${day}`).textContent = `RM ${netSales.toFixed(2)}`;
    
            // 投标金额 = 净销售额 + 税 + 服务费 + 调整金额
            const calculatedTenderAmount = netSales + tax + serviceFee + adjAmount;
            document.getElementById(`tender-amount-${day}`).textContent = `RM ${calculatedTenderAmount.toFixed(2)}`;
    
            // 人均消费 = (净销售额 + 调整金额) / 顾客人数
            const avgPerDiner = diners > 0 ? (netSales + adjAmount) / diners : 0;
            document.getElementById(`avg-per-diner-${day}`).textContent = `RM ${avgPerDiner.toFixed(2)}`;
    
            // 常客人率
            const totalCustomers = returningCustomers + newCustomers;
            const returningCustomerRate = totalCustomers > 0 ? (returningCustomers / totalCustomers) * 100 : 0;
            document.getElementById(`returning-customer-rate-${day}`).textContent = `${returningCustomerRate.toFixed(2)}%`;
    
            // 更新月度统计
            updateMonthStats();
        }

        // 更新输入框颜色状态
        function updateInputColors() {
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            
            for (let day = 1; day <= daysInMonth; day++) {
                // 获取该行的所有输入框
                const dayInputs = document.querySelectorAll(`input[data-day="${day}"]`);
                
                // 检查该行的关键字段（除了折扣）是否有数据
                const grossSales = getInputValue('gross_sales', day).trim();
                const diners = getInputValue('diners', day).trim();
                const tax = getInputValue('tax', day).trim();
                const serviceFee = getInputValue('service_fee', day).trim();
                const tablesUsed = getInputValue('tables_used', day).trim();
                const newCustomers = getInputValue('new_customers', day).trim();
                const returningCustomers = getInputValue('returning_customers', day).trim();
                
                // 计算已填写的关键字段数量（不包括折扣和调整金额）
                let filledKeyFields = 0;
                if (grossSales && grossSales !== '0' && grossSales !== '0.00') filledKeyFields++;
                if (diners && diners !== '0') filledKeyFields++;
                if (tax && tax !== '0' && tax !== '0.00') filledKeyFields++;
                if (serviceFee && serviceFee !== '0' && serviceFee !== '0.00') filledKeyFields++;
                if (tablesUsed && tablesUsed !== '0') filledKeyFields++;
                if (newCustomers && newCustomers !== '0') filledKeyFields++;
                if (returningCustomers && returningCustomers !== '0') filledKeyFields++;
                
                // 该行是否有足够的关键数据
                const rowHasKeyData = filledKeyFields >= 4;
                
                dayInputs.forEach(input => {
                    const field = input.dataset.field;
                    const value = input.value.trim();
                    
                    if (field === 'discounts') {
                        // 折扣列：如果该行其他关键字段有数据，就显示蓝色
                        if (rowHasKeyData) {
                            input.classList.remove('no-data');
                            input.classList.add('has-data');
                        } else {
                            input.classList.remove('has-data');
                            input.classList.add('no-data');
                        }
                    } else {
                        // 其他列：只看自己是否有数据
                        const hasValue = value !== '' && value !== '0' && value !== '0.00';
                        if (hasValue) {
                            input.classList.remove('no-data');
                            input.classList.add('has-data');
                        } else {
                            input.classList.remove('has-data');
                            input.classList.add('no-data');
                        }
                    }
                });
            }
        }

        // 获取输入框值
        function getInputValue(field, day) {
            const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
            return input ? input.value : '';
        }

        // 更新月度统计 - 修改计算逻辑
        function updateMonthStats() {
            let filledDays = 0;
            let totalNetSales = 0;  // 净销售额总计
            let totalAdjAmount = 0; // 调整金额总计
            let totalTenderAmount = 0; // 投标金额总计
            let totalDiners = 0;
            let totalTables = 0;    // 桌数总计
            
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            
            for (let day = 1; day <= daysInMonth; day++) {
                const grossSales = parseFloat(getInputValue('gross_sales', day)) || 0;
                const discounts = parseFloat(getInputValue('discounts', day)) || 0;
                const adjAmount = parseFloat(getInputValue('adj_amount', day)) || 0;
                const tax = parseFloat(getInputValue('tax', day)) || 0;
                const serviceFee = parseFloat(getInputValue('service_fee', day)) || 0;
                const diners = parseInt(getInputValue('diners', day)) || 0;
                const tables = parseInt(getInputValue('tables_used', day)) || 0;
                
                if (grossSales > 0 || diners > 0) {
                    filledDays++;
                }
                
                // 计算净销售额
                const netSales = grossSales - discounts;
                totalNetSales += netSales;
                totalAdjAmount += adjAmount;
                
                // 计算投标金额
                const tenderAmount = netSales + tax + serviceFee + adjAmount;
                totalTenderAmount += tenderAmount;
                
                totalDiners += diners;
                totalTables += tables;
            }
            
            // 月总销售额 = 净销售额 + 调整金额
            const monthTotalSales = totalNetSales
            
            // 月总人均消费 = 月总销售额 ÷ 月总顾客人数
            const monthlyAvgPerCustomer = totalDiners > 0 ? monthTotalSales / totalDiners : 0;
            
            document.getElementById('filled-days').textContent = filledDays;
            document.getElementById('total-sales').textContent = monthTotalSales.toLocaleString();
            document.getElementById('total-tender').textContent = totalTenderAmount.toLocaleString();
            document.getElementById('total-diners').textContent = totalDiners.toLocaleString();
            document.getElementById('total-tables').textContent = totalTables.toLocaleString();
            document.getElementById('avg-per-customer').textContent = monthlyAvgPerCustomer.toFixed(2);
        }

        // 智能分割数据，保护千位分隔符
        function splitWithNumberProtection(text) {
            const values = [];
            let current = '';
            let inNumber = false;
            
            for (let i = 0; i < text.length; i++) {
                const char = text[i];
                const nextChar = text[i + 1];
                const prevChar = text[i - 1];
                
                if (char === ',') {
                    // 检查逗号是否是千位分隔符
                    // 千位分隔符的特征：前后都是数字，且后面有1-3位数字
                    const isThousandsSeparator = 
                        /\d/.test(prevChar) && 
                        /\d/.test(nextChar) && 
                        /^\d{1,3}($|[,\s\t])/.test(text.substring(i + 1));
                    
                    if (isThousandsSeparator) {
                        current += char;
                        inNumber = true;
                    } else {
                        // 是分隔符
                        if (current.trim()) {
                            values.push(current.trim());
                        }
                        current = '';
                        inNumber = false;
                    }
                } else if (/\s/.test(char)) {
                    // 空格
                    if (current.trim()) {
                        values.push(current.trim());
                    }
                    current = '';
                    inNumber = false;
                } else {
                    current += char;
                    if (/\d/.test(char)) {
                        inNumber = true;
                    }
                }
            }
            
            if (current.trim()) {
                values.push(current.trim());
            }
            
            return values;
        }

        // 处理粘贴数据
        function handlePasteData(pasteData, targetDay, startField = null) {
            const lines = pasteData.trim().split('\n').filter(line => line.trim() !== '');
            
            // 要粘贴的字段顺序（对应7个列）
            const pasteFields = [
                'gross_sales',    // 总销售额
                'discounts',      // 折扣
                'tax',           // 税
                'service_fee',   // 服务费
                'adj_amount',    // 调整金额
                'tables_used',   // 桌数总数
                'diners'         // 顾客总数
            ];
            
            // 确定开始粘贴的列索引
            let startIndex = 0;
            if (startField && pasteFields.includes(startField)) {
                startIndex = pasteFields.indexOf(startField);
            }
            
            // 如果是多行数据，找到所有处于编辑模式的行
            if (lines.length > 1) {
                // 获取当前月份的天数
                const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
                
                // 找到所有编辑模式的行，从目标日期开始
                const editingDays = [];
                for (let day = targetDay; day <= daysInMonth; day++) {
                    const row = document.querySelector(`input[data-day="${day}"]`)?.closest('tr');
                    if (row && row.classList.contains('editing-row')) {
                        editingDays.push(day);
                    }
                }
                
                if (editingDays.length === 0) {
                    showAlert('没有找到处于编辑模式的行', 'error');
                    return;
                }
                
                if (lines.length > editingDays.length) {
                    showAlert(`数据有 ${lines.length} 行，但只有 ${editingDays.length} 行在编辑模式`, 'info');
                }
                
                let totalPasteCount = 0;
                const pastedDays = [];
                
                // 遍历每一行数据和对应的编辑行
                for (let lineIndex = 0; lineIndex < Math.min(lines.length, editingDays.length); lineIndex++) {
                    const line = lines[lineIndex];
                    const day = editingDays[lineIndex];
                    
                    // 解析当前行的数据
                    let values = [];
                    if (line.includes('\t')) {
                        values = line.split('\t');
                    } else if (line.includes(',')) {
                        // 检查是否是千位分隔符的情况
                        // 如果整行只有一个数字（包含千位分隔符），不按逗号分割
                        const numberPattern = /^[\d,]+\.?\d*$/;
                        if (numberPattern.test(line.trim())) {
                            values = [line.trim()];
                        } else {
                            // 智能分割：保护千位分隔符
                            values = splitWithNumberProtection(line);
                        }
                    } else {
                        values = line.split(/\s+/);
                    }
                    
                    let rowPasteCount = 0;
                    
                    // 确定当前行的开始列索引
                    // 第一行从指定列开始，后续行从第一列开始
                    const currentStartIndex = (lineIndex === 0) ? startIndex : 0;

                    // 从确定的列开始粘贴当前行
                    for (let i = 0; i < values.length && (currentStartIndex + i) < pasteFields.length; i++) {
                        const fieldIndex = currentStartIndex + i;
                        const field = pasteFields[fieldIndex];
                        const value = values[i].trim();
                        
                        if (value && value !== '') {
                            const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
                            if (input) {
                                // 清理数据
                                let cleanValue = value.replace(/[^\d.,-]/g, '');
                                cleanValue = cleanValue.replace(/,/g, '');
                                
                                // 验证数据
                                const numValue = parseFloat(cleanValue);
                                if (!isNaN(numValue)) {
                                    input.value = cleanValue;
                                    rowPasteCount++;
                                }
                            }
                        }
                    }
                    
                    if (rowPasteCount > 0) {
                        totalPasteCount += rowPasteCount;
                        pastedDays.push(day);
                        // 更新当前行的计算
                        updateCalculations(day);
                    }
                }
                
                // 显示成功消息
                if (totalPasteCount > 0) {
                    const fieldNames = {
                        'gross_sales': '总销售额',
                        'discounts': '折扣',
                        'tax': '税',
                        'service_fee': '服务费',
                        'adj_amount': '调整金额',
                        'tables_used': '桌数',
                        'diners': '顾客数'
                    };
                    const startFieldName = startField ? fieldNames[startField] : '第一列';
                    showAlert(`第一行从${startFieldName}开始，后续行从第一列开始，成功粘贴 ${lines.length} 行数据，共 ${totalPasteCount} 个字段到第 ${pastedDays.join(', ')} 日`, 'success');
                } else {
                    showAlert('未能识别有效的数据格式', 'error');
                }
                
            } else {
                // 单行粘贴逻辑（保持原有功能）
                const line = lines[0];
                let values = [];
                if (line.includes('\t')) {
                    values = line.split('\t');
                } else if (line.includes(',')) {
                    // 检查是否是千位分隔符的情况
                    const numberPattern = /^[\d,]+\.?\d*$/;
                    if (numberPattern.test(line.trim())) {
                        values = [line.trim()];
                    } else {
                        // 智能分割：保护千位分隔符
                        values = splitWithNumberProtection(line);
                    }
                } else {
                    values = line.split(/\s+/);
                }
                
                let pasteCount = 0;
                
                // 从指定列开始粘贴
                for (let i = 0; i < values.length && (startIndex + i) < pasteFields.length; i++) {
                    const fieldIndex = startIndex + i;
                    const field = pasteFields[fieldIndex];
                    const value = values[i].trim();
                    
                    if (value && value !== '') {
                        const input = document.querySelector(`input[data-field="${field}"][data-day="${targetDay}"]`);
                        if (input) {
                            // 清理数据，保留千位分隔符
                            let cleanValue = value.replace(/[^\d.,-]/g, ''); // 保留逗号
                            // 移除千位分隔符，只保留小数点
                            cleanValue = cleanValue.replace(/,/g, '');
                            
                            // 验证数据
                            const numValue = parseFloat(cleanValue);
                            if (!isNaN(numValue)) {
                                input.value = cleanValue;
                                pasteCount++;
                            }
                        }
                    }
                }
                
                // 更新计算
                updateCalculations(targetDay);
                
                // 显示成功消息
                if (pasteCount > 0) {
                    const fieldNames = {
                        'gross_sales': '总销售额',
                        'discounts': '折扣',
                        'tax': '税',
                        'service_fee': '服务费',
                        'adj_amount': '调整金额',
                        'tables_used': '桌数',
                        'diners': '顾客数'
                    };
                    const startFieldName = startField ? fieldNames[startField] : '第一列';
                    showAlert(`从${startFieldName}开始成功粘贴 ${pasteCount} 个字段到第${targetDay}日`, 'success');
                } else {
                    showAlert('未能识别有效的数据格式', 'error');
                }
            }
        }

        // 保存所有数据 - 修复版本
        async function saveAllData() {
            if (isLoading) return;
            
            const saveBtn = event.target;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<div class="loading"></div> 保存中...';
            saveBtn.disabled = true;
            
            try {
                const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
                let successCount = 0;
                let skipCount = 0;
                let errorCount = 0;
                const errors = [];
                
                for (let day = 1; day <= daysInMonth; day++) {
                    const grossSales = parseFloat(getInputValue('gross_sales', day)) || 0;
                    const diners = parseInt(getInputValue('diners', day)) || 0;
                    
                    // 只保存有数据的行
                    const hasData = grossSales !== '' && !isNaN(grossSales) || 
                diners !== '' && !isNaN(diners) ||
                (parseFloat(getInputValue('discounts', day)) !== '' && !isNaN(parseFloat(getInputValue('discounts', day)))) ||
                (parseFloat(getInputValue('tax', day)) !== '' && !isNaN(parseFloat(getInputValue('tax', day)))) ||
                (parseFloat(getInputValue('service_fee', day)) !== '' && !isNaN(parseFloat(getInputValue('service_fee', day)))) ||
                (parseFloat(getInputValue('adj_amount', day)) !== '' && !isNaN(parseFloat(getInputValue('adj_amount', day)))) ||
                (parseInt(getInputValue('tables_used', day)) !== '' && !isNaN(parseInt(getInputValue('tables_used', day)))) ||
                (parseInt(getInputValue('returning_customers', day)) !== '' && !isNaN(parseInt(getInputValue('returning_customers', day)))) ||
                (parseInt(getInputValue('new_customers', day)) !== '' && !isNaN(parseInt(getInputValue('new_customers', day))));

                    if (hasData) {
                                            const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                        
                                            const getNumericValue = (field, day, isInteger = false) => {
                        const value = getInputValue(field, day);
                        if (value === '' || value === null || value === undefined) return 0;
                        const num = isInteger ? parseInt(value) : parseFloat(value);
                        return isNaN(num) ? 0 : num;
                    };

                    const recordData = {
                        date: dateStr,
                        gross_sales: getNumericValue('gross_sales', day),
                        discounts: getNumericValue('discounts', day),
                        tax: getNumericValue('tax', day),
                        service_fee: getNumericValue('service_fee', day),
                        adj_amount: getNumericValue('adj_amount', day),
                        tender_amount: (getNumericValue('gross_sales', day) - getNumericValue('discounts', day)) + 
                                    getNumericValue('tax', day) + getNumericValue('service_fee', day) + 
                                    getNumericValue('adj_amount', day),
                        diners: getNumericValue('diners', day, true),
                        tables_used: getNumericValue('tables_used', day, true),
                        returning_customers: getNumericValue('returning_customers', day, true),
                        new_customers: getNumericValue('new_customers', day, true),
                        restaurant: currentRestaurant
                    };
                        
                        try {
                            let result;
                            // 如果已存在记录，更新；否则新增
                            if (monthData[day]) {
                                recordData.id = monthData[day].id;
                                result = await apiCall('', {
                                    method: 'PUT',
                                    body: JSON.stringify(recordData)
                                });
                            } else {
                                result = await apiCall('', {
                                    method: 'POST',
                                    body: JSON.stringify(recordData)
                                });
                            }
                            
                            // 检查结果
                            if (result.success === true) {
                                successCount++;
                            } else if (result.success === false) {
                                // 检查是否是"记录已存在"或"无变化"的情况
                                const message = result.message || '';
                                if (message.includes('已存在') || message.includes('无变化')) {
                                    skipCount++;
                                } else {
                                    errorCount++;
                                    errors.push(`${day}日: ${message}`);
                                }
                            } else {
                                successCount++;
                            }
                            
                        } catch (error) {
                            errorCount++;
                            errors.push(`${day}日: ${error.message}`);
                        }
                    }
                }
                
                // 根据结果显示不同的消息
                if (successCount > 0 || skipCount > 0) {
                    let message = '';
                    if (successCount > 0 && skipCount > 0) {
                        message = `数据处理完成！成功保存 ${successCount} 条记录，${skipCount} 条记录无需更新`;
                    } else if (successCount > 0) {
                        message = `数据保存成功！共保存 ${successCount} 条记录`;
                    } else if (skipCount > 0) {
                        message = `数据检查完成！${skipCount} 条记录已是最新，无需更新`;
                    }
                    
                    if (errorCount > 0) {
                        message += `，${errorCount} 条记录保存失败`;
                    }
                    
                    showAlert(message, successCount > 0 ? 'success' : 'info');
                    
                    // 重新加载数据以确保界面同步
                    await loadMonthData();
                } else if (errorCount > 0) {
                    showAlert(`保存失败：${errors.join('; ')}`, 'error');
                } else {
                    showAlert('没有需要保存的数据', 'info');
                }
                
            } catch (error) {
                showAlert('保存过程中发生错误，请检查网络连接后重试', 'error');
                console.error('保存错误:', error);
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        // 清空单日数据
        async function clearDayData(day) {
            if (!confirm(`确定要清空${day}日的所有数据吗？此操作不可恢复！`)) {
                return;
            }
            
            const deleteBtn = event.target.closest('.delete-day-btn');
            const originalHTML = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<div class="loading"></div>';
            deleteBtn.disabled = true;
            
            try {
                // 如果该日在数据库中有记录，先删除数据库记录
                if (monthData[day] && monthData[day].id) {
                    const result = await apiCall(`?action=delete&id=${monthData[day].id}&restaurant=${currentRestaurant}`, {
                        method: 'DELETE'
                    });
                    
                    if (result.success) {
                        // 从本地数据中移除该记录
                        delete monthData[day];
                        showAlert(`${day}日数据已从数据库删除`, 'success');
                    } else {
                        throw new Error(result.message || '删除失败');
                    }
                } else {
                    showAlert(`${day}日数据已清空`, 'info');
                }
                
                // 清空该日所有输入框
                const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
                inputs.forEach(input => {
                    input.value = '';
                });
                
                // 重新计算该日的数据
                updateCalculations(day);

                // 更新该行的颜色状态
                updateInputColors();
                
            } catch (error) {
                showAlert(`删除${day}日数据失败: ${error.message}`, 'error');
                console.error('删除数据失败:', error);
            } finally {
                deleteBtn.innerHTML = originalHTML;
                deleteBtn.disabled = false;
            }
        }

        // 显示提示信息
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'error' ? 'alert-error' : type === 'info' ? 'alert-info' : 'alert-success';
            const iconClass = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
            
            const alertElement = document.createElement('div');
            alertElement.className = `alert ${alertClass}`;
            alertElement.innerHTML = `
                <i class="fas ${iconClass}"></i>
                <span>${message}</span>
            `;
            
            alertContainer.appendChild(alertElement);
            
            setTimeout(() => {
                alertElement.remove();
            }, 5000);
        }

        // 输入框光标定位处理
        let inputFirstClickMap = new Map(); // 记录每个输入框是否已经被点击过
        
        function handleInputFocus(input, isClick = false) {
            // 延迟执行以确保focus事件完成后再设置光标位置
            setTimeout(() => {
                if (isClick) {
                    const inputKey = `${input.dataset.field}-${input.dataset.day}`;
                    
                    // 如果这个输入框已经被点击过，不处理光标位置
                    if (inputFirstClickMap.has(inputKey)) {
                        return; // 让浏览器处理正常的点击定位
                    }
                    
                    // 标记这个输入框已经被点击过
                    inputFirstClickMap.set(inputKey, true);
                }
                
                if (input.value) {
                    // 如果有值，选择所有内容（便于快速替换）
                    input.select();
                } else {
                    // 如果没有值，将光标设置到开头
                    input.setSelectionRange(0, 0);
                }
            }, 0);
        }

        // 重置输入框的首次点击状态（当输入框值发生变化时）
        function resetInputFirstClick(input) {
            const inputKey = `${input.dataset.field}-${input.dataset.day}`;
            inputFirstClickMap.delete(inputKey);
        }

        // 键盘快捷键支持
        document.addEventListener('keydown', function(e) {
            // Ctrl+S 保存数据
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveAllData();
            }
            
            // Tab键在输入框间移动
            if (e.key === 'Tab') {
                const inputs = Array.from(document.querySelectorAll('.excel-input'));
                const currentIndex = inputs.indexOf(document.activeElement);
                
                if (currentIndex !== -1) {
                    e.preventDefault();
                    const nextIndex = e.shiftKey ? 
                        (currentIndex - 1 + inputs.length) % inputs.length : 
                        (currentIndex + 1) % inputs.length;
                    inputs[nextIndex].focus();
                }
            }
            
            // Enter键移动到下一行同一列
            if (e.key === 'Enter' && document.activeElement.classList.contains('excel-input')) {
                e.preventDefault();
                const currentInput = document.activeElement;
                const field = currentInput.dataset.field;
                const currentDay = parseInt(currentInput.dataset.day);
                const nextDay = currentDay + 1;
                
                const nextInput = document.querySelector(`input[data-field="${field}"][data-day="${nextDay}"]`);
                if (nextInput) {
                    nextInput.focus();
                }
            }

            // Ctrl+V 粘贴功能
            if (e.ctrlKey && e.key === 'v') {
                const activeElement = document.activeElement;
                if (activeElement && activeElement.classList.contains('excel-input')) {
                    const day = parseInt(activeElement.dataset.day);
                    const currentField = activeElement.dataset.field;
                    const row = activeElement.closest('tr');
                    
                    // 检查是否在编辑模式
                    if (!row.classList.contains('editing-row')) {
                        showAlert(`请先点击编辑按钮进入${day}日的编辑模式`, 'info');
                        e.preventDefault();
                        return;
                    }
                    
                    pasteTargetDay = day;
                    e.preventDefault();
                    
                    // 尝试从剪贴板读取数据
                    if (navigator.clipboard && navigator.clipboard.readText) {
                        navigator.clipboard.readText().then(function(clipboardData) {
                            if (clipboardData) {
                                handlePasteData(clipboardData, pasteTargetDay, currentField);
                            } else {
                                showAlert('剪贴板为空', 'info');
                            }
                        }).catch(function(err) {
                            showAlert('无法访问剪贴板，请手动输入或使用右键粘贴', 'error');
                        });
                    } else {
                        // 降级处理：显示提示让用户使用右键粘贴
                        showAlert('请使用右键菜单粘贴，或直接在输入框中按Ctrl+V', 'info');
                    }
                }
            }
        });

        // 输入框事件处理
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('excel-input')) {
                const field = e.target.dataset.field;
                const value = e.target.value;
                
                // 重置首次点击状态，因为用户正在输入
                resetInputFirstClick(e.target);
                
                // 金额字段限制小数位数
                if (currencyFields.includes(field)) {
                    if (value.includes('.')) {
                        const parts = value.split('.');
                        if (parts[1] && parts[1].length > 2) {
                            e.target.value = parts[0] + '.' + parts[1].substring(0, 2);
                        }
                    }
                }
                
                // 整数字段去除小数点
                if (['diners', 'tables_used', 'returning_customers', 'new_customers'].includes(field)) {
                    if (value.includes('.')) {
                        e.target.value = value.split('.')[0];
                    }
                }

                // 更新输入框颜色
                updateInputColors();
            }
        });

        // 货币输入框失去焦点时格式化为两位小数
        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('currency-input')) {
                const value = e.target.value;
                if (value && !isNaN(value) && value !== '') {
                    const num = parseFloat(value);
                    e.target.value = num.toFixed(2);
                }
            }
        }, true);

        // 为所有输入框添加focus事件监听
        document.addEventListener('focus', function(e) {
            if (e.target.classList.contains('excel-input')) {
                handleInputFocus(e.target, false);
            }
        }, true);

        // 为所有输入框添加click事件监听
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('excel-input')) {
                handleInputFocus(e.target, true);
            }
        });

        // 监听输入框的paste事件（直接粘贴到输入框）
        document.addEventListener('paste', function(e) {
            if (e.target.classList.contains('excel-input')) {
                const day = parseInt(e.target.dataset.day);
                const currentField = e.target.dataset.field;
                const row = e.target.closest('tr');
                
                // 检查是否在编辑模式
                if (!row.classList.contains('editing-row')) {
                    showAlert(`请先点击编辑按钮进入${day}日的编辑模式`, 'info');
                    e.preventDefault();
                    return;
                }
                
                const clipboardData = e.clipboardData || window.clipboardData;
                const pastedData = clipboardData.getData('text');
                
                // 检查是否包含多个值（制表符、逗号或空格分隔）
                if (pastedData && (pastedData.includes('\t') || pastedData.includes(',') || pastedData.split(/\s+/).length > 1)) {
                    e.preventDefault();
                    handlePasteData(pastedData, day, currentField);
                }
                // 如果是单个值，让浏览器正常处理
            }
        });

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);
    </script>
    <script>
        // 切换数字下拉菜单
        function toggleNumberDropdown() {
            const dropdown = document.getElementById('number-dropdown');
            dropdown.classList.toggle('show');
    
            // 更新选中状态
            updateSelectedNumber();
        }

        // 选择餐厅数字
        function selectNumber(number) {
            const numberBtn = document.querySelector('.number-btn');
            numberBtn.innerHTML = `${number} <i class="fas fa-chevron-down"></i>`;
    
            // 关闭下拉菜单
            document.getElementById('number-dropdown').classList.remove('show');
    
            // 切换餐厅
            const restaurant = `j${number}`;
            switchRestaurant(restaurant);
        }

        // 更新选中的数字状态
        function updateSelectedNumber() {
            const currentNumber = currentRestaurant.replace('j', '');
            document.querySelectorAll('.number-item').forEach(item => {
                item.classList.remove('selected');
                if (item.textContent === currentNumber) {
                    item.classList.add('selected');
                }
            });
        }

        // 点击外部关闭下拉菜单
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.number-dropdown')) {
                document.getElementById('number-dropdown').classList.remove('show');
            }
        });

        // 切换编辑模式
        function toggleEdit(day) {
            const editBtn = document.getElementById(`edit-btn-${day}`);
            const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
            const isEditing = editBtn.classList.contains('save-mode');
            
            if (isEditing) {
                // 保存模式 - 保存这一行
                saveSingleRowData(day);
            } else {
                setRowReadonly(day, false);
                
                editBtn.classList.add('save-mode');
                editBtn.innerHTML = '<i class="fas fa-save"></i>';
                editBtn.title = `保存${day}日数据`;
            }
        }

        // 保存单行数据
        async function saveSingleRowData(day) {
            const editBtn = document.getElementById(`edit-btn-${day}`);
            const originalHTML = editBtn.innerHTML;
            editBtn.innerHTML = '<div class="loading"></div>';
            editBtn.disabled = true;
            
            try {
                const grossSales = parseFloat(getInputValue('gross_sales', day)) || 0;
                const diners = parseInt(getInputValue('diners', day)) || 0;
                
                // 检查是否有数据需要保存
                const hasData = grossSales > 0 || diners > 0 ||
                    (parseFloat(getInputValue('discounts', day)) || 0) > 0 ||
                    (parseFloat(getInputValue('tax', day)) || 0) > 0 ||
                    (parseFloat(getInputValue('service_fee', day)) || 0) > 0 ||
                    (parseFloat(getInputValue('adj_amount', day)) || 0) !== 0 ||
                    (parseInt(getInputValue('tables_used', day)) || 0) > 0 ||
                    (parseInt(getInputValue('returning_customers', day)) || 0) > 0 ||
                    (parseInt(getInputValue('new_customers', day)) || 0) > 0;

                if (hasData) {
                    const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                    
                    const getNumericValue = (field, day, isInteger = false) => {
                        const value = getInputValue(field, day);
                        if (value === '' || value === null || value === undefined) return 0;
                        const num = isInteger ? parseInt(value) : parseFloat(value);
                        return isNaN(num) ? 0 : num;
                    };

                    const recordData = {
                        date: dateStr,
                        gross_sales: getNumericValue('gross_sales', day),
                        discounts: getNumericValue('discounts', day),
                        tax: getNumericValue('tax', day),
                        service_fee: getNumericValue('service_fee', day),
                        adj_amount: getNumericValue('adj_amount', day),
                        tender_amount: (getNumericValue('gross_sales', day) - getNumericValue('discounts', day)) + 
                                    getNumericValue('tax', day) + getNumericValue('service_fee', day) + 
                                    getNumericValue('adj_amount', day),
                        diners: getNumericValue('diners', day, true),
                        tables_used: getNumericValue('tables_used', day, true),
                        returning_customers: getNumericValue('returning_customers', day, true),
                        new_customers: getNumericValue('new_customers', day, true),
                        restaurant: currentRestaurant
                    };
                    
                    let result;
                    if (monthData[day]) {
                        recordData.id = monthData[day].id;
                        result = await apiCall('', {
                            method: 'PUT',
                            body: JSON.stringify(recordData)
                        });
                    } else {
                        result = await apiCall('', {
                            method: 'POST',
                            body: JSON.stringify(recordData)
                        });
                    }
                    
                    if (result.success === true || result.success !== false) {
                        showAlert(`${day}日数据保存成功`, 'success');
                        // 更新本地数据
                        monthData[day] = recordData;
                    } else {
                        const message = result.message || '';
                        if (message.includes('已存在') || message.includes('无变化')) {
                            showAlert(`${day}日数据无需更新`, 'info');
                        } else {
                            throw new Error(message);
                        }
                    }
                } else {
                    showAlert(`${day}日数据已保存（空记录）`, 'info');
                }
                
                // 切换回只读模式
                const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
                setRowReadonly(day, true);
                
                editBtn.classList.remove('save-mode');
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.title = `编辑${day}日数据`;
                
                // 更新月度统计
                updateMonthStats();
                
            } catch (error) {
                showAlert(`保存${day}日数据失败: ${error.message}`, 'error');
                console.error('保存数据失败:', error);
            } finally {
                editBtn.disabled = false;
                if (!editBtn.classList.contains('save-mode')) {
                    editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                }
            }
        }
    </script>
</body>
</html>