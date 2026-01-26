<?php
// 防止浏览器/代理缓存，确保修改能立刻生效
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟库存管理 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            color: #111827;
            overflow-y: auto;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            height: 100vh; /* Keep original height */
            overflow-y: auto; /* Allow container to scroll vertically */
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(18px, 1.67vw, 32px);
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

        /* 页面选择器样式 */
        .view-selector {
            position: relative;
        }

        .selector-button {
            background-color: #f99e00;
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
            width: clamp(80px, 6.77vw, 130px);
            justify-content: space-between;
        }
        
        .selector-button:hover {
            background-color: #f98500ff;
            border-radius: 8px;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .selector-dropdown {
            position: absolute;
            top: 96%;
            right: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.2);
            width: 100%;
            z-index: 1000;
            display: none;
            margin-top: 4px;
        }

        .selector-dropdown.show {
            display: block;
        }

        .dropdown-item {
            padding: clamp(6px, 0.42vw, 8px) clamp(10px, 0.83vw, 16px);
            cursor: pointer;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.2s;
            color: #000000ff;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f8f5eb;
            border-radius: 8px;
        }

        .dropdown-item.active {
            background-color: #f99e00;
            color: white;
            border-radius: 4px;
        }

        .back-button {
            background-color: #6b7280;
            color: white;
            font-weight: 500;
            padding: 13px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            margin-left: 16px;
        }
        
        .back-button:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.2);
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
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: clamp(8px, 0.74vw, 14px);
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
            animation: toastProgress 0.7s linear forwards;
        }

        @keyframes toastProgress {
            0% { transform: scaleX(1); }
            100% { transform: scaleX(0); }
        }

        /* 搜索和过滤区域 */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 24px 40px;
            margin-bottom: 24px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #583e04;
        }

        .filter-input, .filter-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #583e04;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 3px rgba(88, 62, 4, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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
            background-color: #f99300;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #f98500ff;
            transform: translateY(-1px);
        }
        
        /* 套装显示样式 - 共享单元格 */
        .set-shared-cell {
            border-top: none !important;
            border-bottom: none !important;
            vertical-align: middle !important;
        }
        
        /* 套装行样式 */
        tr[data-type="set"] {
            border-top: 1px solid #e5e7eb;
        }
        
        tr[data-type="set"]:not(:first-child) {
            border-top: none;
        }

        /* 套装管理样式 */
        .set-item-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: #f9fafb;
        }

        .dishware-select {
            flex: 2;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .quantity-input {
            flex: 1;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: clamp(8px, 0.74vw, 14px); /* 使用响应式字体大小 */
            text-align: center;
        }

        /* 隐藏 number 输入框的上下箭头（总库存页面） */
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input {
            -moz-appearance: textfield; /* Firefox */
        }

        .btn-remove {
            padding: 8px 12px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-remove:hover {
            background-color: #dc2626;
        }

        .btn-add-item {
            padding: 10px 20px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }

        .btn-add-item:hover {
            background-color: #059669;
        }

        .set-expand-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            font-size: 16px;
            padding: 4px;
        }

        .set-expand-btn:hover {
            color: #374151;
        }

        .set-items-detail {
            background-color: #f9fafb;
            border-left: 3px solid #f99300;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }

        .set-item-detail {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .set-item-detail:last-child {
            border-bottom: none;
        }

        /* 套装模态框特殊样式 - 与总库存弹窗保持一致 */
        #setModal .modal-content {
            max-width: 800px;
            width: 90%;
        }

        #setModal .modal-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        #setModal .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        #setModal .form-group[style*="grid-column: 1 / -1"] {
            grid-column: 1 / -1;
        }

        #setModal .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #583e04;
        }

        #setModal .form-group input,
        #setModal .form-group textarea,
        #setModal .form-group select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        #setModal .form-group input:focus,
        #setModal .form-group textarea:focus,
        #setModal .form-group select:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 2px rgba(88, 62, 4, 0.1);
        }

        #setModal .form-group textarea {
            resize: vertical;
            min-height: 60px;
        }

        #setModal .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 2px solid #e5e7eb;
        }

        /* 套装项目容器样式 - 与总库存弹窗保持一致 */
        #set-items-container {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }

        /* 套装项目行样式 - 与总库存弹窗保持一致 */
        #setModal .set-item-row {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: white;
        }

        #setModal .dishware-select {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        #setModal .dishware-select:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 2px rgba(88, 62, 4, 0.1);
        }

        #setModal .btn-remove {
            padding: 8px 12px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            transition: background-color 0.2s ease;
        }

        #setModal .btn-remove:hover {
            background-color: #dc2626;
        }

        #setModal .btn-add-item {
            padding: 8px 16px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-top: 12px;
            transition: background-color 0.2s ease;
        }

        #setModal .btn-add-item:hover {
            background-color: #059669;
        }

        /* 管理餐厅店面按钮样式 */
        #manage-restaurants-btn {
            color: white !important;
        }

        #manage-restaurants-btn i,
        #manage-restaurants-btn .fas {
            color: white !important;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            #setModal .modal-content {
                width: 98%;
                margin: 10px;
            }
            
            #setModal .modal-form {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            #setModal .set-item-row {
                flex-direction: column;
                gap: 12px;
            }
            
            #setModal .dishware-select {
                width: 100%;
            }
            
            #setModal .btn-remove {
                width: 100%;
                justify-content: center;
            }
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

        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* 总库存卡片 */
        .summary-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            transition: transform 0.2s ease;
            margin-bottom: 24px;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.15);
        }

        .summary-card h3 {
            color: #583e04;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-currency-display {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .summary-currency-display .currency-symbol {
            font-size: 32px;
            font-weight: bold;
            color: #583e04;
        }

        .summary-currency-display .value {
            font-size: 32px;
            font-weight: 700;
            color: #583e04;
        }

        /* 库存表格 */
        .stock-table {
            table-layout: fixed;
            width: 100%;
            min-width: 1200px; /* 减少最小宽度 */
            border-collapse: collapse;
            font-size: 14px;
        }

        .stock-table thead {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #636363;
        }

        .stock-table th {
            background: #636363;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 100;
            white-space: nowrap;
            min-width: 80px;
        }

        .stock-table td {
            padding: clamp(2px, 0.31vw, 6px) clamp(6px, 0.63vw, 12px);
            font-size: clamp(8px, 0.74vw, 14px);
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: middle;
        }

        /* 确保加载状态的单元格完全居中 */
        .stock-table td[colspan] {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .stock-table tr:nth-child(even) {
            background-color: white;
        }

        .stock-table tr:hover {
            background-color: #e5ebf8ff;
        }
        
        /* JavaScript控制的套装行hover效果 - 只对同一个套装的行生效 */
        .stock-table tr.set-hover-active {
            background-color: #e5ebf8ff !important;
        }
        
        .stock-table tr.set-hover-active td {
            background-color: #e5ebf8ff !important;
        }
        
        .stock-table tr.set-hover-active .set-shared-cell {
            background-color: #e5ebf8ff !important;
        }

        /* 固定表格列宽 - 库存管理页面 */
        .stock-table th:nth-child(1), .stock-table td:nth-child(1) { width: 50px; }  /* No. */
        .stock-table th:nth-child(2), .stock-table td:nth-child(2) { 
            width: 70px; 
            text-align: center; /* 确保照片列居中 */
        }  /* Photo */
        .stock-table th:nth-child(3), .stock-table td:nth-child(3) { width: 180px; } /* Product Name */
        .stock-table th:nth-child(4), .stock-table td:nth-child(4) { width: 100px; } /* Code */
        .stock-table th:nth-child(5), .stock-table td:nth-child(5) { width: 80px; } /* Category */
        .stock-table th:nth-child(6), .stock-table td:nth-child(6) { width: 120px; } /* Size */
        .stock-table th:nth-child(7), .stock-table td:nth-child(7) { width: 90px; } /* Price */
        .stock-table th:nth-child(8), .stock-table td:nth-child(8) { width: 80px; } /* 文化楼 */
        .stock-table th:nth-child(9), .stock-table td:nth-child(9) { width: 80px; } /* 中央 */
        .stock-table th:nth-child(10), .stock-table td:nth-child(10) { width: 60px; } /* J1 */
        .stock-table th:nth-child(11), .stock-table td:nth-child(11) { width: 60px; } /* J2 */
        .stock-table th:nth-child(12), .stock-table td:nth-child(12) { width: 60px; } /* J3 */
        .stock-table th:nth-child(13), .stock-table td:nth-child(13) { width: 80px; } /* Total */
        .stock-table th:nth-child(14), .stock-table td:nth-child(14) { width: 100px; } /* Actions */

        /* 套装表格列宽（使用百分比） */
        #sets-table {
            table-layout: fixed;
            width: 100%;
        }
        
        /* 基础列（固定位置） */
        #sets-table th:nth-child(1), #sets-table td:nth-child(1) { width: 4%; }  /* 序号 */
        #sets-table th:nth-child(2), #sets-table td:nth-child(2) { width: 5%; }  /* 套装名称 */
        #sets-table th:nth-child(3), #sets-table td:nth-child(3) { width: 6%; }  /* 套装编号 */
        #sets-table th:nth-child(4), #sets-table td:nth-child(4) { width: 30%; } /* 包含项目 */
        #sets-table th:nth-child(5), #sets-table td:nth-child(5) { width: 6%; }  /* 单价 (RM) */
        
        /* 餐厅列（动态，每个餐厅列）- 使用属性选择器 */
        #sets-table th[data-restaurant-header], 
        #sets-table td[data-restaurant-id] { 
            width: 4.5%; 
        }
        
        /* 最后两列（总库存和操作） */
        #sets-table th:nth-last-child(2), #sets-table td:nth-last-child(2) { width: 6%; }  /* 总库存 */
        #sets-table th:last-child, #sets-table td:last-child { width: 6%; }  /* 操作 */

        /* 破损记录表格列宽 */
        #j1-table th:nth-child(1), #j1-table td:nth-child(1) { width: 100px; } /* Date */
        #j1-table th:nth-child(2), #j1-table td:nth-child(2) { width: 50px; }  /* No. */
        #j1-table th:nth-child(3), #j1-table td:nth-child(3) { width: 180px; } /* Product Name */
        #j1-table th:nth-child(4), #j1-table td:nth-child(4) { width: 100px; } /* Code */
        #j1-table th:nth-child(5), #j1-table td:nth-child(5) { width: 80px; } /* Category */
        #j1-table th:nth-child(6), #j1-table td:nth-child(6) { width: 120px; } /* Size */
        #j1-table th:nth-child(7), #j1-table td:nth-child(7) { width: 100px; } /* Current Stock */
        #j1-table th:nth-child(8), #j1-table td:nth-child(8) { width: 100px; } /* Break Quantity */
        #j1-table th:nth-child(9), #j1-table td:nth-child(9) { width: 90px; } /* Unit Price */
        #j1-table th:nth-child(10), #j1-table td:nth-child(10) { width: 90px; } /* Total Price */
        #j1-table th:nth-child(11), #j1-table td:nth-child(11) { width: 100px; } /* Actions */

        #j2-table th:nth-child(1), #j2-table td:nth-child(1) { width: 100px; } /* Date */
        #j2-table th:nth-child(2), #j2-table td:nth-child(2) { width: 50px; }  /* No. */
        #j2-table th:nth-child(3), #j2-table td:nth-child(3) { width: 180px; } /* Product Name */
        #j2-table th:nth-child(4), #j2-table td:nth-child(4) { width: 100px; } /* Code */
        #j2-table th:nth-child(5), #j2-table td:nth-child(5) { width: 80px; } /* Category */
        #j2-table th:nth-child(6), #j2-table td:nth-child(6) { width: 120px; } /* Size */
        #j2-table th:nth-child(7), #j2-table td:nth-child(7) { width: 100px; } /* Current Stock */
        #j2-table th:nth-child(8), #j2-table td:nth-child(8) { width: 100px; } /* Break Quantity */
        #j2-table th:nth-child(9), #j2-table td:nth-child(9) { width: 90px; } /* Unit Price */
        #j2-table th:nth-child(10), #j2-table td:nth-child(10) { width: 90px; } /* Total Price */
        #j2-table th:nth-child(11), #j2-table td:nth-child(11) { width: 100px; } /* Actions */

        #j3-table th:nth-child(1), #j3-table td:nth-child(1) { width: 100px; } /* Date */
        #j3-table th:nth-child(2), #j3-table td:nth-child(2) { width: 50px; }  /* No. */
        #j3-table th:nth-child(3), #j3-table td:nth-child(3) { width: 180px; } /* Product Name */
        #j3-table th:nth-child(4), #j3-table td:nth-child(4) { width: 100px; } /* Code */
        #j3-table th:nth-child(5), #j3-table td:nth-child(5) { width: 80px; } /* Category */
        #j3-table th:nth-child(6), #j3-table td:nth-child(6) { width: 120px; } /* Size */
        #j3-table th:nth-child(7), #j3-table td:nth-child(7) { width: 100px; } /* Current Stock */
        #j3-table th:nth-child(8), #j3-table td:nth-child(8) { width: 100px; } /* Break Quantity */
        #j3-table th:nth-child(9), #j3-table td:nth-child(9) { width: 90px; } /* Unit Price */
        #j3-table th:nth-child(10), #j3-table td:nth-child(10) { width: 90px; } /* Total Price */
        #j3-table th:nth-child(11), #j3-table td:nth-child(11) { width: 100px; } /* Actions */

        /* 响应式列宽调整 */
        @media (max-width: 1200px) {
            .stock-table th:nth-child(1), .stock-table td:nth-child(1) { width: 40px; }  /* No. */
            .stock-table th:nth-child(2), .stock-table td:nth-child(2) { width: 60px; }  /* Photo */
            .stock-table th:nth-child(3), .stock-table td:nth-child(3) { width: 150px; } /* Product Name */
            .stock-table th:nth-child(4), .stock-table td:nth-child(4) { width: 80px; } /* Code */
            .stock-table th:nth-child(5), .stock-table td:nth-child(5) { width: 60px; } /* Category */
            .stock-table th:nth-child(6), .stock-table td:nth-child(6) { width: 100px; } /* Size */
            .stock-table th:nth-child(7), .stock-table td:nth-child(7) { width: 80px; } /* Price */
            .stock-table th:nth-child(8), .stock-table td:nth-child(8) { width: 70px; } /* 文化楼 */
            .stock-table th:nth-child(9), .stock-table td:nth-child(9) { width: 70px; } /* 中央 */
            .stock-table th:nth-child(10), .stock-table td:nth-child(10) { width: 50px; } /* J1 */
            .stock-table th:nth-child(11), .stock-table td:nth-child(11) { width: 50px; } /* J2 */
            .stock-table th:nth-child(12), .stock-table td:nth-child(12) { width: 50px; } /* J3 */
            .stock-table th:nth-child(13), .stock-table td:nth-child(13) { width: 70px; } /* Total */
            .stock-table th:nth-child(14), .stock-table td:nth-child(14) { width: 80px; } /* Actions */
        }

        .table-container {
            position: relative;
            overflow-y: auto;
            max-height: calc(100vh - 300px); /* 根据实际页面高度调整 */
            background: white;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.1);
            border: 2px solid #000000ff;
            overflow: visible;
            display: flex;
            flex-direction: column;
            max-height: 68vh;
            min-height: 400px; /* 确保最小高度，避免跳动 */
            position: relative; /* 为加载覆盖层提供定位上下文 */
        }

        .table-scroll-container {
            overflow-x: auto; /* 允许水平滚动 */
            overflow-y: auto;
            flex: 1;
            position: relative;
            transition: opacity 0.2s ease; /* 添加平滑过渡效果 */
            /* 确保 sticky 定位在滚动容器内工作 */
            height: 100%;
        }

        /* 自定义滚动条样式 */
        .table-scroll-container::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* 操作按钮 */
        .action-buttons {
            padding: 14px 24px;
            background: #f8f5eb;
            border-top: 2px solid #583e04;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        /* 统计信息 */
        .stats-info {
            display: flex;
            gap: 4px;
            align-items: center;
            font-size: 14px;
            color: #6b7280;
            flex-wrap: wrap;
            margin-right: -50px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 150px;
        }

        .stat-value {
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: bold;
            color: #583e04;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
        }

        .positive-value {
            color: #10b981;
            font-weight: 600;
        }

        .zero-value {
            color: #6b7280;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }

        .no-data i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .loading {
            display: inline-block;
            width: clamp(16px, 1.25vw, 20px);
            height: clamp(16px, 1.25vw, 20px);
            border: clamp(2px, 0.21vw, 3px) solid #f3f3f3;
            border-top: clamp(2px, 0.21vw, 3px) solid #583e04;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .total-row {
            background: #f8f5eb !important;
            border-top: 2px solid #000000ff;
            font-weight: 600;
            color: #000000ff;
        }

        /* 照片样式 */
        .product-photo {
            width: clamp(120px, 3.13vw, 150px);
            height: clamp(120px, 3.13vw, 150px);
            object-fit: cover;
            border-radius: clamp(4px, 0.42vw, 8px);
            border: 1px solid #e5e7eb;
            display: block;
            margin: 0 auto; /* 确保照片在单元格中居中 */
        }

        .no-photo {
            width: clamp(30px, 3.13vw, 60px);
            height: clamp(30px, 3.13vw, 60px);
            background: #f3f4f6;
            border: 1px dashed #d1d5db;
            border-radius: clamp(4px, 0.42vw, 8px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: clamp(10px, 1.04vw, 20px);
            margin: 0 auto; /* 确保在单元格中居中 */
        }

        /* 确保照片单元格内容居中 */
        .stock-table td:nth-child(2) {
            text-align: center !important;
            vertical-align: middle !important;
        }

        /* 数量输入框 */
        .quantity-input {
            width: 80px;
            padding: clamp(2px, 0.31vw, 4px) clamp(4px, 0.42vw, 8px);
            border: 1px solid #d1d5db;
            border-radius: 4px;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 12px); /* 使用响应式字体大小 */
        }

        /* 隐藏 number 输入框的上下箭头 */
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input {
            -moz-appearance: textfield; /* Firefox */
        }

        .quantity-input:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 2px rgba(88, 62, 4, 0.1);
        }

        /* 货币显示 */
        .currency-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            box-sizing: border-box;
            font-size: 14px;
            width: 100%;
        }

        .currency-symbol {
            color: #6b7280;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            text-align: left;
            flex-shrink: 0;
        }

        .currency-amount {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            color: #000000ff;
            text-align: right;
            flex-shrink: 0;
        }

        /* 模态框样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 24px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            border: 2px solid #583e04;
        }

        /* 餐厅店面管理模态框特殊样式 */
        #restaurantModal .modal-content {
            max-width: 95vw;
            width: auto;
            min-width: 500px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        #restaurantModal .modal-body {
            flex: 1;
            overflow: visible;
            display: flex;
            flex-direction: column;
            min-width: 0;
            width: 100%;
        }

        #restaurantModal .table-container {
            overflow: visible;
            display: block;
            width: 100%;
            min-width: 0;
        }

        #restaurantModal .table-scroll-container {
            overflow: visible;
            width: 100%;
            max-height: 60vh;
            overflow-y: auto;
            overflow-x: visible;
        }

        #restaurantModal .stock-table {
            width: auto;
            min-width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }

        #restaurantModal .stock-table th,
        #restaurantModal .stock-table td {
            padding: 8px 8px;
            box-sizing: border-box;
            white-space: nowrap;
        }

        /* 序号列 - 固定宽度 */
        #restaurantModal .stock-table th:nth-child(1),
        #restaurantModal .stock-table td:nth-child(1) {
            text-align: center;
            width: 50px;
            min-width: 50px;
            max-width: 50px;
        }

        /* 餐厅店面名称列 - 自适应 */
        #restaurantModal .stock-table th:nth-child(2),
        #restaurantModal .stock-table td:nth-child(2) {
            min-width: 150px;
            white-space: normal;
        }

        /* 操作列 - 固定宽度 */
        #restaurantModal .stock-table th:nth-child(3),
        #restaurantModal .stock-table td:nth-child(3) {
            text-align: center;
            width: 120px;
            min-width: 120px;
            max-width: 120px;
            white-space: nowrap;
        }

        #restaurantModal .stock-table .action-btn {
            margin: 0 2px;
            padding: 4px 6px;
            font-size: 11px;
        }

        /* 响应式调整 - 小屏幕时保持所有列可见 */
        @media (max-width: 768px) {
            #restaurantModal .modal-content {
                width: 98vw;
                min-width: 450px;
                padding: 12px;
            }
            
            #restaurantModal .stock-table th,
            #restaurantModal .stock-table td {
                padding: 6px 4px;
                font-size: 12px;
            }
            
            #restaurantModal .stock-table th:nth-child(1),
            #restaurantModal .stock-table td:nth-child(1) {
                width: 50px;
                min-width: 50px;
                max-width: 50px;
            }
            
            #restaurantModal .stock-table th:nth-child(2),
            #restaurantModal .stock-table td:nth-child(2) {
                min-width: 120px;
            }
            
            #restaurantModal .stock-table th:nth-child(3),
            #restaurantModal .stock-table td:nth-child(3) {
                width: 110px;
                min-width: 110px;
                max-width: 110px;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #583e04;
        }

        .close {
            color: #6b7280;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #583e04;
        }

        .modal-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .modal-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .modal-form label {
            font-size: 14px;
            font-weight: 600;
            color: #583e04;
        }
        
        /* 数量行样式 */
        .quantity-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .quantity-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 120px;
        }
        
        .quantity-field label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
        }
        
        .quantity-field input {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
            text-align: center;
        }
        
        .quantity-field input:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 2px rgba(88, 62, 4, 0.1);
        }
        

        .modal-form input {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .modal-form input:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 2px rgba(88, 62, 4, 0.1);
        }

        .modal-form label.required::after {
            content: " *";
            color: #dc2626;
        }

        .modal-form select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .modal-form select:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 2px rgba(88, 62, 4, 0.1);
        }

        /* 照片上传区域 */
        .photo-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.2s;
            cursor: pointer;
            margin-top: 8px;
        }

        .photo-upload-area:hover {
            border-color: #583e04;
            background: #f3f4f6;
        }

        .photo-upload-area.dragover {
            border-color: #583e04;
            background: #fef3c7;
        }

        .photo-upload-icon {
            font-size: 32px;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        .photo-upload-text {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .photo-upload-hint {
            font-size: 12px;
            color: #9ca3af;
        }

        .photo-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            margin-top: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .file-input {
            display: none;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 2px solid #e5e7eb;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .header h1 {
                font-size: 32px;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
                width: 100%;
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


    /* 统一顶部行样式 */
    .unified-header-row {
        display: flex;
        align-items: center;
        gap: 0px;
        padding: clamp(8px, 0.83vw, 16px) clamp(18px, 1.25vw, 24px);
        background: white;
        border-radius: 12px;
        margin-bottom: clamp(14px, 1.25vw, 24px);
        border: 2px solid #000000ff;
        box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
        flex-wrap: nowrap;
        justify-content: space-between;
    }

    .header-summary {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 140px;
        flex-shrink: 0;
        margin-right: 0px;
    }

    .summary-title {
        font-size: clamp(14px, 1.5vw, 28px);
        font-weight: 600;
        color: #000000ff;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .summary-amount {
        display: flex;
        align-items: baseline;
        gap: clamp(0px, 0.31vw, 6px);
    }

    .summary-amount .currency-symbol {
        font-size: clamp(18px, 1.88vw, 36px);
        font-weight: bold;
        color: #000000ff;
    }

    .summary-amount .value {
        font-size: clamp(18px, 1.88vw, 36px);
        margin-left: 6px;
        font-weight: 700;
        color: #000000ff;
    }

    .header-center-section {
        flex: 1;
        min-width: 50px;
        display: flex;
        align-items: center;
        gap: clamp(12px, 1.25vw, 24px);
    }

    .header-search {
        display: flex;
        align-items: center;
        gap: clamp(6px, 0.63vw, 12px);
    }

    .category-filter {
        display: flex;
        align-items: center;
        gap: clamp(6px, 0.63vw, 12px);
    }

    .header-stats {
        margin-top: 0px;
        display: flex;
        gap: clamp(8px, 0.83vw, 16px);
        font-size: clamp(6px, 0.63vw, 12px);
        color: #6b7280;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .header-right-section {
        margin-top: clamp(18px, 1.83vw, 35px);
        display: flex;
        align-items: center;
        gap: clamp(8px, 1.04vw, 20px);
        margin-left: auto;
    }

    .header-stats .stat-value {
        font-weight: bold;
        color: #000000ff;
    }

    .unified-search-input {
        padding: clamp(4px, 0.42vw, 8px) 12px;
        border: 1px solid #d1d5db;
        border-radius: clamp(4px, 0.42vw, 8px);
        font-size: clamp(8px, 0.74vw, 14px);
        background-color: #ffffff;
        transition: all 0.2s ease;
    }

    .unified-search-input:focus {
        outline: none;
        border-color: #000000ff;
        box-shadow: 0 0 10px rgba(31, 14, 0, 0.8);
    }

    .unified-search-input::placeholder {
        color: #9ca3af;
    }

    .btn-warning {
        background-color: #f99e00;
        color: white;
        padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
        margin-top: 0px;
        border-radius: clamp(4px, 0.42vw, 8px);
        border: none;
        cursor: pointer;
        font-size: clamp(8px, 0.74vw, 14px);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        flex-shrink: 0;
        white-space: nowrap;
    }
    
    .btn-warning:hover {
        background-color: #f98500ff;
        transform: translateY(-1px);
    }

    /* 操作按钮样式 */
    .action-btn {
        width: clamp(22px, 1.46vw, 28px);
        height: clamp(22px, 1.46vw, 28px);
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(8px, 0.625vw, 12px);
        color: white;
        transition: all 0.2s;
        margin: 0 2px;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .action-btn.edit-btn {
        background: #f59e0b;
    }

    .action-btn.edit-btn:hover {
        background: #d97706;
    }

    .action-btn.delete-btn {
        background: #ef4444;
    }

    .action-btn.delete-btn:hover {
        background: #dc2626;
    }

    /* 编辑行样式 */
    .editing-row {
        background-color: #e0f2fe !important;
    }

        /* 加载覆盖层样式 */
        #loading-overlay {
            backdrop-filter: blur(2px);
            border-radius: 4px;
        }

        /* 转置表格：表头在左侧，内容向右横向滚动（用于总库存表和分类表格） */
        #stock-table.transposed,
        .stock-table.transposed {
            table-layout: auto !important;
            width: max-content !important;
            min-width: 100% !important;
        }

        #stock-table.transposed thead,
        .stock-table.transposed thead {
            display: none;
        }

        /* 覆盖原本的固定列宽规则，避免 nth-child 影响转置表 */
        #stock-table.transposed th,
        #stock-table.transposed td,
        .stock-table.transposed th,
        .stock-table.transposed td {
            width: auto !important;
            min-width: 140px; /* Default min-width for content cells */
            white-space: nowrap;
        }

        /* 左侧"表头列"固定 */
        #stock-table.transposed th.row-header,
        .stock-table.transposed th.row-header {
            position: sticky !important;
            left: 0;
            top: unset !important;
            z-index: 300;
            background: #636363;
            color: #fff;
            text-align: center;
            min-width: 110px;
            max-width: 110px;
            width: 110px !important;
            border: 1px solid #d1d5db;
            box-sizing: border-box;
        }

        /* 内容单元格 */
        #stock-table.transposed td,
        .stock-table.transposed td {
            text-align: center;
            border: 1px solid #d1d5db;
            background: white;
            min-width: 180px; /* Minimum width for data columns */
            max-width: none;
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-word;
            box-sizing: border-box;
        }

        /* 照片列 - 使用原始尺寸 */
        #stock-table.transposed tr[data-row="照片"] td,
        .stock-table.transposed tr[data-row="照片"] td { 
            min-width: 180px; /* 足够的宽度以容纳原始照片尺寸 */
            padding: clamp(4px, 0.42vw, 8px);
        }
        
        #stock-table.transposed tr[data-row="照片"] td img.product-photo,
        .stock-table.transposed tr[data-row="照片"] td img.product-photo {
            width: clamp(100px, 7.8vw, 150px) !important;
            height: clamp(100px, 7.8vw, 150px) !important;
            object-fit: cover;
            border-radius: clamp(4px, 0.42vw, 8px);
            border: 1px solid #e5e7eb;
            display: block;
            margin: 0 auto;
            max-width: 100%;
        }
        
        #stock-table.transposed tr[data-row="照片"] td .no-photo,
        .stock-table.transposed tr[data-row="照片"] td .no-photo {
            width: clamp(100px, 7.8vw, 150px) !important;
            height: clamp(100px, 7.8vw, 150px) !important;
            display: flex;
            margin: 0 auto;
            max-width: 100%;
        }
        
        /* 产品名称列 - 允许自动扩展 */
        #stock-table.transposed tr[data-row="产品名称"] td,
        .stock-table.transposed tr[data-row="产品名称"] td { 
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
            text-align: center !important;
            padding: clamp(4px, 0.42vw, 8px) clamp(8px, 0.83vw, 16px);
            min-width: 200px;
        }
        
        /* 其他列的最小宽度设置 */
        #stock-table.transposed tr[data-row="编号"] td,
        #stock-table.transposed tr[data-row="分类"] td,
        #stock-table.transposed tr[data-row="单价"] td,
        #stock-table.transposed tr[data-row="文化楼"] td,
        #stock-table.transposed tr[data-row="中央"] td,
        #stock-table.transposed tr[data-row="总数"] td,
        #stock-table.transposed tr[data-row="操作"] td,
        .stock-table.transposed tr[data-row="编号"] td,
        .stock-table.transposed tr[data-row="分类"] td,
        .stock-table.transposed tr[data-row="单价"] td,
        .stock-table.transposed tr[data-row="文化楼"] td,
        .stock-table.transposed tr[data-row="中央"] td,
        .stock-table.transposed tr[data-row="总数"] td,
        .stock-table.transposed tr[data-row="操作"] td { 
            white-space: nowrap;
        }
        
        /* 单价行的货币显示居中 */
        #stock-table.transposed tr[data-row="单价"] td .currency-display,
        .stock-table.transposed tr[data-row="单价"] td .currency-display {
            justify-content: center;
            width: auto;
            margin: 0 auto;
            gap: clamp(4px, 0.42vw, 8px); /* RM和数字之间的间距 */
        }
        
        #stock-table.transposed tr[data-row="尺寸"] td,
        .stock-table.transposed tr[data-row="尺寸"] td { 
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        #stock-table.transposed tr[data-row="J1"] td,
        #stock-table.transposed tr[data-row="J2"] td,
        #stock-table.transposed tr[data-row="J3"] td,
        .stock-table.transposed tr[data-row="J1"] td,
        .stock-table.transposed tr[data-row="J2"] td,
        .stock-table.transposed tr[data-row="J3"] td { 
            white-space: nowrap;
        }
        
        /* 操作列特殊宽度 */
        #stock-table.transposed tr[data-row="操作"] td,
        .stock-table.transposed tr[data-row="操作"] td { min-width: 110px; }

        /* 小屏幕时减小列宽 - 不影响默认页面大小 */
        @media (max-width: 1400px) {
            /* 内容单元格 - 减小最小宽度 */
            #stock-table.transposed td,
            .stock-table.transposed td {
                min-width: 120px;
            }
            
            /* 照片列 - 减小最小宽度 */
            #stock-table.transposed tr[data-row="照片"] td,
            .stock-table.transposed tr[data-row="照片"] td { 
                min-width: 120px;
            }
            
            /* 照片尺寸 - 减小最小值但保持最大值 */
            #stock-table.transposed tr[data-row="照片"] td img.product-photo,
            .stock-table.transposed tr[data-row="照片"] td img.product-photo {
                width: clamp(90px, 7.8vw, 150px) !important;
                height: clamp(90px, 7.8vw, 150px) !important;
            }
            
            #stock-table.transposed tr[data-row="照片"] td .no-photo,
            .stock-table.transposed tr[data-row="照片"] td .no-photo {
                width: clamp(90px, 7.8vw, 150px) !important;
                height: clamp(90px, 7.8vw, 150px) !important;
            }
            
            /* 产品名称列 - 减小最小宽度 */
            #stock-table.transposed tr[data-row="产品名称"] td,
            .stock-table.transposed tr[data-row="产品名称"] td { 
                min-width: 130px;
            }
        }

        /* 分类容器样式 - 用于全部分类显示 */
        .categories-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 0;
            /* Removed overflow-y: auto; max-height: 68vh; min-height: 400px; */
        }

        /* 破损记录容器样式 - 左右排列 */
        .break-records-container {
            display: flex;
            flex-direction: row;
            gap: 20px;
            width: 100%;
            /* 确保至少显示三个容器，每个容器最小宽度 */
            min-width: calc(3 * 600px + 2 * 20px + 40px); /* 3个容器 + 2个gap + padding */
        }

        .break-record-section {
            background: white;
            border-radius: 12px;
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            display: flex;
            flex-direction: column;
            flex: 0 0 600px; /* 固定宽度，不缩放 */
            min-width: 600px;
            max-width: 600px;
        }

        .break-record-header {
            background: #636363;
            color: white;
            padding: clamp(10px, 1.04vw, 20px) clamp(16px, 1.25vw, 24px);
            font-size: clamp(14px, 1.04vw, 20px);
            font-weight: 600;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .break-record-table-wrapper {
            overflow-x: auto;
            overflow-y: visible; /* 改为 visible，允许下拉菜单溢出 */
            flex: 1;
            max-height: calc(100vh - 350px);
            position: relative; /* 为下拉菜单提供定位上下文 */
        }

        .break-record-table {
            width: 100%;
            min-width: 500px;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed; /* 固定表格布局 */
        }

        .break-record-table thead {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #636363;
        }

        .break-record-table th {
            background: #636363;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 100;
            white-space: nowrap;
        }

        /* 固定列宽（使用百分比）- 破损记录页面（6列） */
        #j1-page .break-record-table th:nth-child(1),
        #j1-page .break-record-table td:nth-child(1),
        #j2-page .break-record-table th:nth-child(1),
        #j2-page .break-record-table td:nth-child(1),
        #j3-page .break-record-table th:nth-child(1),
        #j3-page .break-record-table td:nth-child(1) {
            width: 8%; /* No. */
        }

        #j1-page .break-record-table th:nth-child(2),
        #j1-page .break-record-table td:nth-child(2),
        #j2-page .break-record-table th:nth-child(2),
        #j2-page .break-record-table td:nth-child(2),
        #j3-page .break-record-table th:nth-child(2),
        #j3-page .break-record-table td:nth-child(2) {
            width: 25%; /* 编号 */
        }

        #j1-page .break-record-table th:nth-child(3),
        #j1-page .break-record-table td:nth-child(3),
        #j2-page .break-record-table th:nth-child(3),
        #j2-page .break-record-table td:nth-child(3),
        #j3-page .break-record-table th:nth-child(3),
        #j3-page .break-record-table td:nth-child(3) {
            width: 13%; /* 破损数量 */
        }

        #j1-page .break-record-table th:nth-child(4),
        #j1-page .break-record-table td:nth-child(4),
        #j2-page .break-record-table th:nth-child(4),
        #j2-page .break-record-table td:nth-child(4),
        #j3-page .break-record-table th:nth-child(4),
        #j3-page .break-record-table td:nth-child(4) {
            width: 18%; /* 单价 */
        }

        #j1-page .break-record-table th:nth-child(5),
        #j1-page .break-record-table td:nth-child(5),
        #j2-page .break-record-table th:nth-child(5),
        #j2-page .break-record-table td:nth-child(5),
        #j3-page .break-record-table th:nth-child(5),
        #j3-page .break-record-table td:nth-child(5) {
            width: 18%; /* 总价 */
        }

        #j1-page .break-record-table th:nth-child(6),
        #j1-page .break-record-table td:nth-child(6),
        #j2-page .break-record-table th:nth-child(6),
        #j2-page .break-record-table td:nth-child(6),
        #j3-page .break-record-table th:nth-child(6),
        #j3-page .break-record-table td:nth-child(6) {
            width: 18%; /* 操作 */
        }

        .break-record-table td {
            padding: clamp(2px, 0.31vw, 6px) clamp(6px, 0.63vw, 12px);
            font-size: clamp(8px, 0.74vw, 14px);
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: middle;
            overflow: visible; /* 改为 visible，允许下拉菜单显示 */
            text-overflow: ellipsis;
            position: relative; /* 为下拉菜单提供定位上下文 */
        }

        .break-record-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .break-record-table tr:hover {
            background-color: #f3f4f6;
        }

        .category-section {
            background: white;
            border-radius: 12px;
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;

        }

        .category-header {
            background: #636363;
            color: white;
            padding: clamp(10px, 1.04vw, 20px) clamp(16px, 1.25vw, 24px);
            font-size: clamp(14px, 1.04vw, 20px);
            font-weight: 600;
            border-radius: 10px 10px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* Removed position: sticky; top: 0; z-index: 200; */
        }

        .category-header .category-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .category-header .category-count {
            font-size: clamp(12px, 1.04vw, 16px);
            font-weight: 400;
            opacity: 0.9;
        }

        .category-table-wrapper {
            overflow-x: auto; /* Keep horizontal scrolling */
            overflow-y: visible; /* Allow content to expand vertically */
            /* Removed max-height: 500px; flex: 1; */
        }

        .category-table-wrapper::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .category-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .category-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .category-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* 隐藏默认表格容器当显示分类容器时 */
        .table-container.hide-for-categories {
            display: none;
        }

        /* 显示分类容器 */
        #categories-container {
            display: none;
        }

        #categories-container.show {
            display: block;
        }

        /* 破损记录容器样式 - 左右排列 */
        .break-records-container {
            display: flex;
            flex-direction: row;
            gap: 20px;
            padding: 20px 0px;
            align-items: flex-start;
            width: 100%;
            /* 确保至少显示三个容器，每个容器最小宽度 */
            min-width: calc(3 * 600px + 2 * 20px + 40px); /* 3个容器 + 2个gap + padding */
        }
        
        /* 转卖页面选择单个餐厅时的样式 */
        #transfer-records-container.single-restaurant {
            justify-content: center;
            min-width: auto;
            width: 100%;
        }
        
        #transfer-records-container.single-restaurant .break-record-section {
            margin: 0 auto;
        }
        
        /* 破损记录页面选择单个餐厅时的样式 */
        .break-records-container.single-restaurant,
        #break-records-container.single-restaurant,
        #break-records-container-j2.single-restaurant,
        #break-records-container-j3.single-restaurant {
            justify-content: center;
            min-width: auto;
            width: 100%;
        }
        
        .break-records-container.single-restaurant .break-record-section,
        #break-records-container.single-restaurant .break-record-section,
        #break-records-container-j2.single-restaurant .break-record-section,
        #break-records-container-j3.single-restaurant .break-record-section {
            margin: 0 auto;
        }

        .break-record-section {
            background: white;
            border-radius: 12px;
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            display: flex;
            flex-direction: column;
            flex: 0 0 600px; /* 固定宽度，不缩放 */
            min-width: 600px;
            max-width: 600px;
        }

        .break-record-header {
            background: #636363;
            color: white;
            padding: clamp(10px, 1.04vw, 20px) clamp(16px, 1.25vw, 24px);
            font-size: clamp(14px, 1.04vw, 20px);
            font-weight: 600;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .break-record-table-wrapper {
            overflow-x: auto;
            overflow-y: visible; /* 改为 visible，允许下拉菜单溢出 */
            flex: 1;
            max-height: calc(100vh - 350px);
            position: relative; /* 为下拉菜单提供定位上下文 */
        }

        .break-record-table {
            width: 100%;
            min-width: 500px;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed; /* 固定表格布局 */
        }

        .break-record-table thead {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #636363;
        }

        .break-record-table th {
            background: #636363;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 100;
            white-space: nowrap;
        }

        /* 固定列宽（使用百分比）- 转卖页面（7列） */
        #transfer-page .break-record-table th:nth-child(1),
        #transfer-page .break-record-table td:nth-child(1) {
            width: 8%; /* No. */
        }

        #transfer-page .break-record-table th:nth-child(2),
        #transfer-page .break-record-table td:nth-child(2) {
            width: 12%; /* 编号 */
        }

        #transfer-page .break-record-table th:nth-child(3),
        #transfer-page .break-record-table td:nth-child(3) {
            width: 10%; /* 数量 */
        }

        #transfer-page .break-record-table th:nth-child(4),
        #transfer-page .break-record-table td:nth-child(4) {
            width: 8%; /* 进出 */
        }

        #transfer-page .break-record-table th:nth-child(5),
        #transfer-page .break-record-table td:nth-child(5) {
            width: 15%; /* 单价 */
        }

        #transfer-page .break-record-table th:nth-child(6),
        #transfer-page .break-record-table td:nth-child(6) {
            width: 15%; /* 总价 */
        }

        #transfer-page .break-record-table th:nth-child(7),
        #transfer-page .break-record-table td:nth-child(7) {
            width: 14%; /* 操作 */
        }

        .break-record-table td {
            padding: clamp(2px, 0.31vw, 6px) 0px;
            font-size: clamp(8px, 0.74vw, 14px);
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: middle;
            overflow: visible; /* 改为 visible，允许下拉菜单显示 */
            text-overflow: ellipsis;
            position: relative; /* 为下拉菜单提供定位上下文 */
        }

        .break-record-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .break-record-table tr:hover {
            background-color: #f3f4f6;
        }

        /* 转卖记录下拉列表样式 - 居中选项 */
        .transfer-to-select,
        .transfer-to-select-edit {
            text-align: center !important;
            text-align-last: center !important; /* 居中最后一行（选中的选项） */
        }

        .transfer-to-select option,
        .transfer-to-select-edit option {
            text-align: center !important;
            padding: 4px 8px;
        }

        /* 新行样式 */
        .break-record-table tr.new-row {
            background-color: #e8f5e9;
            position: static; /* 改为 static，避免创建层叠上下文 */
            z-index: auto; /* 确保不会创建层叠上下文覆盖下拉列表 */
        }

        .break-record-table tr.new-row:hover {
            background-color: #c8e6c9;
        }

        .break-record-table tr.new-row td {
            padding: 8px;
            font-size: clamp(8px, 0.74vw, 14px); /* 使用响应式字体大小，与其他行一致 */
            position: static; /* 改为 static，避免创建层叠上下文 */
            z-index: auto; /* 确保不会创建层叠上下文覆盖下拉列表 */
        }

        .break-record-table tr.new-row input,
        .break-record-table tr.new-row select {
            width: 100%;
            max-width: 100%;
            padding: 4px 8px;
            border: none;
            background: transparent;
            font-size: clamp(8px, 0.74vw, 14px); /* 使用响应式字体大小，与其他行一致 */
            box-sizing: border-box;
            outline: none;
            position: relative;
            z-index: 1; /* 低层级，确保下拉列表可以覆盖 */
        }

        /* 隐藏新行中 number 输入框的上下箭头 */
        .break-record-table tr.new-row input[type="number"]::-webkit-outer-spin-button,
        .break-record-table tr.new-row input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .break-record-table tr.new-row input[type="number"] {
            -moz-appearance: textfield; /* Firefox */
        }

        .break-record-table tr.new-row input:focus,
        .break-record-table tr.new-row select:focus {
            background: #f0f0f0;
            border-radius: 2px;
        }

        /* Combobox 样式 */
        .combobox-container {
            position: relative;
            width: 100%;
            z-index: 1; /* 低层级，确保下拉列表可以覆盖 */
        }

        /* 只让下拉菜单显示在最上层，输入框保持正常层级 */
        .break-record-table tr.new-row .combobox-container {
            z-index: 1; /* 低层级，确保下拉列表可以覆盖 */
        }

        .break-record-table tr.new-row .combobox-dropdown {
            z-index: 2147483647 !important; /* 使用最大z-index值，确保显示在所有元素之上 */
            position: fixed !important; /* 使用 fixed 定位，脱离文档流 */
        }

        .combobox-input {
            width: 100%;
            padding: 4px 24px 4px 8px;
            border: none;
            background: transparent;
            font-size: clamp(8px, 0.74vw, 14px); /* 使用响应式字体大小 */
            outline: none;
            box-sizing: border-box;
            position: relative;
            z-index: 1; /* 低层级，确保下拉列表可以覆盖 */
        }

        /* 确保输入框的占位符文本不会覆盖下拉列表 */
        .combobox-input::placeholder {
            z-index: 0;
            position: relative;
        }

        .combobox-input:focus {
            background: #f0f0f0;
            border-radius: 2px;
        }

        .combobox-arrow {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #999;
            font-size: 12px;
        }

        .combobox-dropdown {
            position: fixed !important; /* 使用 fixed 定位，脱离文档流，避免被其他元素覆盖 */
            top: 100%;
            left: 0;
            background: white !important;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden; /* 隐藏水平溢出 */
            z-index: 2147483647 !important; /* 使用最大z-index值，确保显示在所有元素之上 */
            display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: clamp(60px, 5.21vw, 100px); /* 使用 clamp 设置宽度 */
            pointer-events: auto !important; /* 确保可以点击 */
            /* 确保下拉列表在body下时也能正确显示 */
            margin: 0;
            padding: 0;
        }

        .combobox-dropdown.show {
            display: block;
        }

        .combobox-option {
            padding: 8px 12px;
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px); /* 使用响应式字体大小 */
            white-space: nowrap; /* 防止文本换行 */
            overflow: visible; /* 允许文本完整显示 */
        }

        .combobox-option:hover {
            background: #f0f0f0;
        }

        .combobox-option.selected {
            background: #e3f2fd;
        }

        /* 弹窗样式 */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-overlay .modal-content {
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1);
        }

        /* 拖拽排序样式 */
        .stock-table.transposed tr[data-restaurant-row] {
            user-select: none;
        }

        .stock-table.transposed tr[data-restaurant-row]:hover {
            background-color: #f3f4f6;
        }

        .stock-table.transposed tr[data-restaurant-row].dragging {
            opacity: 0.5;
            background-color: #e0f2fe;
        }

        .stock-table.transposed tr[data-restaurant-row].drag-over {
            border-top: 3px solid #f99e00;
        }

        /* 只有NO列的th可以拖动 */
        .stock-table.transposed tr[data-restaurant-row] th.row-header:first-child {
            position: relative;
            cursor: move;
        }

        .stock-table.transposed tr[data-restaurant-row] th.row-header:first-child::before {
            content: '☰';
            position: absolute;
            left: 8px;
            color: #9ca3af;
            font-size: 16px;
            cursor: move;
            pointer-events: none;
        }

        .stock-table.transposed tr[data-restaurant-row]:hover th.row-header:first-child::before {
            color: #f99e00;
        }

        /* 其他列不可拖动 */
        .stock-table.transposed tr[data-restaurant-row] td {
            cursor: default;
        }

        /* 餐厅列头拖拽样式（用于普通表格，非转置表格） */
        .stock-table thead th[data-restaurant-header] {
            cursor: move;
            user-select: none;
            position: relative;
        }

        .stock-table thead th[data-restaurant-header]::before {
            content: '☰';
            position: absolute;
            left: 4px;
            color: #9ca3af;
            font-size: 14px;
            cursor: move;
            pointer-events: none;
        }

        .stock-table thead th[data-restaurant-header]:hover::before {
            color: #f99e00;
        }

        .stock-table thead th[data-restaurant-header].dragging {
            opacity: 0.5;
            background-color: #e0f2fe;
        }

        .stock-table thead th[data-restaurant-header].drag-over {
            border-left: 3px solid #f99e00;
        }

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1 id="page-title">碗碟库存管理</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">总库存</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item active" onclick="switchPage('stock')">总库存</div>
                        <div class="dropdown-item" onclick="switchPage('j1')">破损记录</div>
                        <div class="dropdown-item" onclick="switchPage('transfer')">碗碟转卖</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Toast Notifications -->
        <div class="toast-container" id="toast-container">
            <!-- 动态通知内容 -->
        </div>
        
        
         <!-- 统一顶部行 -->
         <div class="unified-header-row">
             <div class="header-center-section">
                <div class="header-search">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">搜索</span>
                    <input type="text" id="unified-filter" class="unified-search-input" 
                        placeholder="搜索碗碟名称、编号或分类...">
                </div>
                
                <div class="category-filter">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">分类</span>
                    <select id="category-filter" class="unified-search-input">
                        <option value="">全部分类</option>
                        <option value="AG">AG</option>
                        <option value="CU">CU</option>
                        <option value="DN">DN</option>
                        <option value="DR">DR</option>
                        <option value="IP">IP</option>
                        <option value="MA">MA</option>
                        <option value="ME">ME</option>
                        <option value="MU">MU</option>
                        <option value="OM">OM</option>
                        <option value="OT">OT</option>
                        <option value="SA">SA</option>
                        <option value="SK">SK</option>
                        <option value="SU">SU</option>
                        <option value="SAR">SAR</option>
                        <option value="SER">SER</option>
                        <option value="SET">SET</option>
                        <option value="TA">TA</option>
                        <option value="TE">TE</option>
                        <option value="WAN">WAN</option>
                        <option value="YA">YA</option>
                        <option value="用具">用具</option>
                    </select>
                </div>
            </div>
            
            <div class="header-right-section">
                <button class="btn btn-info" onclick="openRestaurantModal()" id="manage-restaurants-btn" style="background-color: #17a2b8; border-color: #17a2b8; color: white;">
                    <i class="fas fa-store" style="color: white;"></i>
                    管理餐厅店面
                </button>
                
                <button class="btn btn-success" onclick="openAddModal()" id="add-dishware-btn">
                    <i class="fas fa-plus"></i>
                    添加碗碟
                </button>
                
                <button class="btn btn-warning" onclick="exportData()">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <div class="header-stats">
                    <span>显示记录: <span class="stat-value" id="displayed-records">0</span></span>
                    <span>总记录: <span class="stat-value" id="total-count">0</span></span>
                </div>
            </div>
        </div>
        

        <!-- 页面内容区域 -->
        <div id="page-content">
            <!-- 库存管理页面 -->
            <div id="stock-page" class="page-content">
                <!-- 视图切换按钮 -->
                <div style="display: flex; gap: 8px; margin-bottom: 16px; padding: 0 20px;">
                    <button class="btn" id="stock-view-btn" onclick="switchStockView('dishware')" style="background: #f99e00; color: white; border: 1px solid #ddd; padding: clamp(4px, 0.42vw, 8px) clamp(12px, 1.25vw, 24px); font-size: clamp(10px, 0.83vw, 16px);">
                        碗碟
                    </button>
                    <button class="btn" id="sets-view-btn" onclick="switchStockView('sets')" style="background: white; color: #333; border: 1px solid #ddd; padding: clamp(4px, 0.42vw, 8px) clamp(12px, 1.25vw, 24px); font-size: clamp(10px, 0.83vw, 16px);">
                        套装
                    </button>
                </div>
                
                <!-- 碗碟视图 -->
                <div id="dishware-view">
                    <!-- 单个分类或搜索结果的表格容器 -->
                    <div class="table-container" id="single-table-container">
                        <div class="table-scroll-container">
                            <table class="stock-table" id="stock-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>照片</th>
                                        <th>产品名称</th>
                                        <th>编号</th>
                                        <th>分类</th>
                                        <th>尺寸</th>
                                        <th>单价</th>
                                        <th>文化楼</th>
                                        <th>中央</th>
                                        <th>J1</th>
                                        <th>J2</th>
                                        <th>J3</th>
                                        <th>总数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="stock-tbody">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- 全部分类的容器（按分类分组显示） -->
                    <div id="categories-container" class="categories-container">
                        <!-- 动态生成分类容器 -->
                    </div>
                </div>
                
                <!-- 套装视图 -->
                <div id="sets-view" style="display: none;">
                    <div class="table-container">
                        <div class="table-scroll-container">
                            <table class="stock-table" id="sets-table">
                                <thead>
                                    <tr>
                                        <th>序号</th>
                                        <th>套装名称</th>
                                        <th>套装编号</th>
                                        <th>包含项目</th>
                                        <th>单价 (RM)</th>
                                        <th>文华楼</th>
                                        <th>中央</th>
                                        <th>J1</th>
                                        <th>J2</th>
                                        <th>J3</th>
                                        <th>总库存</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="sets-tbody">
                                    <!-- 动态填充 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 合并的破损记录页面（J1、J2、J3左右排列） -->
            <div id="j1-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="break-records-container" class="break-records-container">
                        <!-- 动态生成三个店铺的表格 -->
                    </div>
                </div>
            </div>
            
            <!-- J2和J3页面指向同一个合并页面 -->
            <div id="j2-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="break-records-container-j2" class="break-records-container">
                        <!-- 动态生成三个店铺的表格 -->
                    </div>
                </div>
            </div>
            
            <div id="j3-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="break-records-container-j3" class="break-records-container">
                        <!-- 动态生成三个店铺的表格 -->
                    </div>
                </div>
            </div>

            <!-- 碗碟转卖页面 -->
            <div id="transfer-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="transfer-records-container" class="break-records-container">
                        <!-- 动态生成餐厅的转卖记录表格 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加碗碟模态框 -->
    <div id="addModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 class="modal-title">添加碗碟信息</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="add-form" enctype="multipart/form-data">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="required">碗碟名称</label>
                        <input type="text" id="add-product-name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label class="required">分类</label>
                        <select id="add-category" name="category" required>
                            <option value="">请选择分类</option>
                            <option value="AG">AG</option>
                            <option value="CU">CU</option>
                            <option value="DN">DN</option>
                            <option value="DR">DR</option>
                            <option value="IP">IP</option>
                            <option value="MA">MA</option>
                            <option value="ME">ME</option>
                            <option value="MU">MU</option>
                            <option value="OM">OM</option>
                            <option value="OT">OT</option>
                            <option value="SA">SA</option>
                            <option value="SK">SK</option>
                            <option value="SU">SU</option>
                            <option value="SAR">SAR</option>
                            <option value="SER">SER</option>
                            <option value="SET">SET</option>
                            <option value="TA">TA</option>
                            <option value="TE">TE </option>
                            <option value="WAN">WAN</option>
                            <option value="YA">YA</option>
                            <option value="用具">用具</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>产品编号</label>
                        <input type="text" id="add-code-number" name="code_number" placeholder="001" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>尺寸规格</label>
                        <input type="text" id="add-size" name="size" placeholder="例如：直径15cm">
                    </div>
                    <div class="form-group">
                        <label>单价 (RM)</label>
                        <input type="number" id="add-unit-price" name="unit_price" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>照片上传</label>
                        <div class="photo-upload-area" onclick="document.getElementById('add-photo').click()">
                            <div class="photo-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="photo-upload-text">点击上传照片或拖拽照片到此处</div>
                            <div class="photo-upload-hint">支持 JPG, PNG, GIF 格式，最大 5MB</div>
                            <img id="add-photo-preview" class="photo-preview" style="display: none;">
                        </div>
                        <input type="file" id="add-photo" name="photo" class="file-input" accept="image/*">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="add-submit-btn">
                        <i class="fas fa-save"></i>
                        保存碗碟信息
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 编辑碗碟信息模态框 -->
    <div id="editModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 class="modal-title">编辑碗碟信息</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="edit-form" enctype="multipart/form-data">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="required">碗碟名称</label>
                        <input type="text" id="edit-product-name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label class="required">分类</label>
                        <select id="edit-category" name="category" required>
                            <option value="">请选择分类</option>
                            <option value="AG">AG</option>
                            <option value="CU">CU</option>
                            <option value="DN">DN</option>
                            <option value="DR">DR</option>
                            <option value="IP">IP</option>
                            <option value="MA">MA</option>
                            <option value="ME">ME</option>
                            <option value="MU">MU</option>
                            <option value="OM">OM</option>
                            <option value="OT">OT</option>
                            <option value="SA">SA</option>
                            <option value="SK">SK</option>
                            <option value="SU">SU</option>
                            <option value="SAR">SAR</option>
                            <option value="SER">SER</option>
                            <option value="SET">SET</option>
                            <option value="TA">TA</option>
                            <option value="TE">TE</option>
                            <option value="WAN">WAN</option>
                            <option value="YA">YA</option>
                            <option value="用具">用具</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>产品编号</label>
                        <input type="text" id="edit-code-number" name="code_number" placeholder="001" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>尺寸规格</label>
                        <input type="text" id="edit-size" name="size" placeholder="例如：直径15cm">
                    </div>
                    <div class="form-group">
                        <label class="required">单价 (RM)</label>
                        <input type="number" id="edit-unit-price" name="unit_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>库存数量</label>
                        <div class="quantity-row" id="edit-restaurant-quantities">
                            <!-- 动态生成餐厅店面输入框 -->
                        </div>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>照片上传</label>
                        <div class="photo-upload-area" onclick="document.getElementById('edit-photo').click()">
                            <div class="photo-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="photo-upload-text">点击上传新照片或拖拽照片到此处</div>
                            <div class="photo-upload-hint">支持 JPG, PNG, GIF 格式，最大 5MB</div>
                            <img id="edit-photo-preview" class="photo-preview" style="display: none;">
                        </div>
                        <input type="file" id="edit-photo" name="photo" class="file-input" accept="image/*">
                        <input type="hidden" id="delete-photo-flag" name="delete_photo" value="0">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>套装设置</label>
                        <div id="set-settings-container" style="border: 1px solid #ddd; border-radius: 8px; padding: 16px; background: #f9fafb;">
                            <div style="margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                    <span style="font-weight: 600;">当前套装成员：</span>
                                    <span id="current-set-members" style="color: #666;">暂无</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <label style="font-weight: 600; margin: 0;">添加套装成员：</label>
                                    <select id="set-member-select" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                        <option value="">请选择要加入套装的碗碟</option>
                                    </select>
                                    <button type="button" onclick="addSetMember()" class="btn btn-primary" style="padding: 8px 16px; white-space: nowrap;">
                                        <i class="fas fa-plus"></i> 添加
                                    </button>
                                </div>
                            </div>
                            <div id="selected-set-members" style="margin-top: 12px;">
                                <!-- 动态显示已选择的套装成员 -->
                            </div>
                            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                                <button type="button" onclick="removeFromSet()" class="btn btn-secondary" style="padding: 8px 16px;">
                                    <i class="fas fa-unlink"></i> 从套装中移除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="edit-submit-btn">
                        <i class="fas fa-save"></i>
                        保存更改
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 添加破损记录模态框 -->
    <div id="damageModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title" id="damage-modal-title">添加破损记录</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="damage-form">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="required">破损日期</label>
                        <input type="date" id="damage-date" name="break_date" required>
                    </div>
                    <div class="form-group">
                        <label>产品编号</label>
                        <select id="damage-code-select" name="code_number" onchange="handleDamageCodeChange(this)">
                            <option value="">请选择编号</option>
                            <!-- 动态填充选项 -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">产品名称</label>
                        <select id="damage-product-select" name="product_name" required onchange="handleDamageProductChange(this)">
                            <option value="">请选择产品</option>
                            <!-- 动态填充选项 -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">破损数量</label>
                        <input type="number" id="damage-quantity" name="break_quantity" min="1" required onchange="calculateDamageTotal()">
                    </div>
                    <div class="form-group">
                        <label>单价 (RM)</label>
                        <input type="number" id="damage-unit-price" name="unit_price" step="0.01" min="0" readonly style="background: #f3f4f6;">
                    </div>
                    <div class="form-group">
                        <label>总价 (RM)</label>
                        <input type="number" id="damage-total-price" name="total_price" step="0.01" readonly style="background: #f3f4f6;">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="damage-submit-btn">
                        <i class="fas fa-save"></i>
                        保存破损记录
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 添加破损记录行数选择弹窗 -->
    <div id="break-rows-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">新增破损记录</h3>
                <button class="modal-close" onclick="closeBreakRowsModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="break-rows-count" style="display: block; margin-bottom: 8px; font-weight: 600;">要创建的行数 *</label>
                    <input type="number" id="break-rows-count" class="form-input" min="1" max="50" value="1" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="closeBreakRowsModal()">取消</button>
                <button class="btn btn-primary" onclick="createMultipleBreakRows()">
                    <i class="fas fa-plus"></i>
                    创建记录
                </button>
            </div>
        </div>
    </div>

    <!-- 餐厅店面管理模态框 -->
    <div id="restaurantModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h2 class="modal-title">管理餐厅店面</h2>
                <span class="close" onclick="closeRestaurantModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-success" onclick="openAddRestaurantModal()">
                        <i class="fas fa-plus"></i>
                        添加餐厅店面
                    </button>
                </div>
                <div class="table-container">
                    <div class="table-scroll-container">
                        <table class="stock-table" id="restaurants-table">
                            <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>餐厅店面名称</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="restaurants-tbody">
                                <!-- 动态填充 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRestaurantModal()">关闭</button>
            </div>
        </div>
    </div>

    <!-- 添加/编辑餐厅店面模态框 -->
    <div id="addRestaurantModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title" id="restaurant-modal-title">添加餐厅店面</h2>
                <span class="close" onclick="closeRestaurantModal()">&times;</span>
            </div>
            <form id="restaurant-form">
                <input type="hidden" id="restaurant-id" name="id">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">餐厅店面名称</label>
                        <input type="text" id="restaurant-name" name="name" required placeholder="例如：新店">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRestaurantModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="restaurant-submit-btn">
                        <i class="fas fa-save"></i>
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 转卖记录行数选择弹窗 -->
    <div id="transfer-rows-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">新增转卖记录</h3>
                <button class="modal-close" onclick="closeTransferRowsModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="transfer-rows-count" style="display: block; margin-bottom: 8px; font-weight: 600;">要创建的行数 *</label>
                    <input type="number" id="transfer-rows-count" class="form-input" min="1" max="50" value="1" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="closeTransferRowsModal()">取消</button>
                <button class="btn btn-primary" onclick="createMultipleTransferRows()">
                    <i class="fas fa-plus"></i>
                    创建记录
                </button>
            </div>
        </div>
    </div>

    <!-- 套装管理模态框 -->
    <div id="setModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="set-modal-title">添加套装</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="set-form">
                <input type="hidden" name="set_id" id="set-id">
                <div class="modal-form">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">套装名称</label>
                        <input type="text" id="set-name" name="set_name" required placeholder="例如：日式茶具套装">
                    </div>
                    <div class="form-group">
                        <label>套装价格 (RM)</label>
                        <input type="number" id="set-price" name="set_price" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">选择碗碟</label>
                        <div id="set-items-container">
                            <div class="set-item-row">
                                <select name="dishware_id[]" class="dishware-select" required>
                                    <option value="">请选择碗碟</option>
                                </select>
                                <button type="button" onclick="removeSetItem(this)" class="btn-remove">删除</button>
                            </div>
                        </div>
                        <button type="button" onclick="addSetItem()" class="btn-add-item">+ 添加碗碟</button>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">取消</button>
                    <button type="submit" class="btn btn-primary" id="set-submit-btn">
                        <i class="fas fa-save"></i>
                        保存套装
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // API 配置
        const API_BASE_URL = 'dishware_api.php';
        
        // 应用状态
        let stockData = [];
        let filteredData = [];
        let isLoading = false;
        let currentEditId = null;
        let totalQuantity = 0;
        let selectedPhoto = null;
        let selectedEditPhoto = null;
        let editingRowIds = new Set(); // 存储正在编辑的行ID
        let originalEditData = new Map(); // 存储原始数据用于取消编辑
        let currentPage = 'stock'; // 当前页面
        let stockViewType = 'dishware'; // 总库存页面的视图类型：'dishware' 或 'sets'
        let breakRecordsData = {
            j1: [],
            j2: [],
            j3: []
        };
        let transferRecordsData = {
            j1: [],
            j2: [],
            j3: []
        }; // 存储各店铺的破损记录数据
        let restaurants = []; // 存储餐厅店面列表

        // 自然排序函数，正确处理字母和数字混合
        function naturalSort(a, b) {
            const aParts = a.toString().match(/(\d+|\D+)/g) || [];
            const bParts = b.toString().match(/(\d+|\D+)/g) || [];
            
            const maxLength = Math.max(aParts.length, bParts.length);
            
            for (let i = 0; i < maxLength; i++) {
                const aPart = aParts[i] || '';
                const bPart = bParts[i] || '';
                
                // 如果都是数字，按数字大小比较
                if (/^\d+$/.test(aPart) && /^\d+$/.test(bPart)) {
                    const numA = parseInt(aPart, 10);
                    const numB = parseInt(bPart, 10);
                    if (numA !== numB) {
                        return numA - numB;
                    }
                } else {
                    // 否则按字符串比较
                    const comparison = aPart.localeCompare(bPart, 'zh-CN', { numeric: true });
                    if (comparison !== 0) {
                        return comparison;
                    }
                }
            }
            return 0;
        }

        // 判断是否为中文字符
        function isChinese(str) {
            if (!str) return false;
            return /[\u4e00-\u9fa5]/.test(str);
        }

        // 按编号排序数据
        function sortByCodeNumber(data) {
            return [...data].sort((a, b) => {
                // 获取分类信息
                let categoryA = '';
                let categoryB = '';
                
                if (a.item_type === 'set') {
                    if (a.items && a.items.length > 0) {
                        categoryA = a.items[0].category || a.category || '';
                    } else {
                        categoryA = a.category || '';
                    }
                } else {
                    categoryA = a.category || '';
                }
                
                if (b.item_type === 'set') {
                    if (b.items && b.items.length > 0) {
                        categoryB = b.items[0].category || b.category || '';
                    } else {
                        categoryB = b.category || '';
                    }
                } else {
                    categoryB = b.category || '';
                }
                
                // 判断是否为中文分类
                const isChineseA = isChinese(categoryA);
                const isChineseB = isChinese(categoryB);
                
                // 先按分类排序：中文分类排在最后
                if (isChineseA && !isChineseB) {
                    return 1; // A是中文，B不是，A排在后面
                } else if (!isChineseA && isChineseB) {
                    return -1; // A不是中文，B是中文，A排在前面
                } else if (isChineseA && isChineseB) {
                    // 都是中文，按分类名称排序
                    const categoryCompare = categoryA.localeCompare(categoryB, 'zh-CN');
                    if (categoryCompare !== 0) {
                        return categoryCompare;
                    }
                } else {
                    // 都不是中文，按分类字母排序
                    const categoryCompare = categoryA.localeCompare(categoryB);
                    if (categoryCompare !== 0) {
                        return categoryCompare;
                    }
                }
                
                // 分类相同，按编号排序
                let codeA = '';
                let codeB = '';
                
                if (a.item_type === 'set') {
                    if (a.items && a.items.length > 0) {
                        // 套装按第一个item的编号排序
                        codeA = a.items[0].code_number || a.code_number || '';
                    } else {
                        // 套装没有items，使用套装自己的编号
                        codeA = a.code_number || a.set_code || '';
                    }
                } else {
                    codeA = a.code_number || '';
                }
                
                if (b.item_type === 'set') {
                    if (b.items && b.items.length > 0) {
                        // 套装按第一个item的编号排序
                        codeB = b.items[0].code_number || b.code_number || '';
                    } else {
                        // 套装没有items，使用套装自己的编号
                        codeB = b.code_number || b.set_code || '';
                    }
                } else {
                    codeB = b.code_number || '';
                }
                
                return naturalSort(codeA, codeB);
            });
        }

        // 初始化应用
        async function initApp() {
            await loadRestaurants(); // 先加载餐厅店面列表
            loadStockData();
            setupEventListeners();
            setupRealTimeSearch();
            setupPageSwitcher();
            setupSetFormSubmit();
            
            // 初始化总库存页面的视图切换按钮
            if (currentPage === 'stock') {
                switchStockView(stockViewType);
            }
            
            // 测试模态框关闭功能
            console.log('应用初始化完成，测试模态框功能...');
            setTimeout(() => {
                const closeButtons = document.querySelectorAll('.close');
                console.log('找到关闭按钮数量:', closeButtons.length);
                closeButtons.forEach((btn, index) => {
                    console.log(`关闭按钮 ${index}:`, btn);
                    btn.addEventListener('click', function() {
                        console.log('关闭按钮被点击');
                        closeModal();
                    });
                });
            }, 1000);
        }

        // 加载餐厅店面列表
        async function loadRestaurants() {
            try {
                const result = await apiCall('?action=restaurants');
                if (result.success) {
                    restaurants = result.data || [];
                    console.log('餐厅店面加载成功:', restaurants);
                    // 更新表格头部
                    updateTableHeaders();
                    // 更新编辑模态框的输入框
                    updateEditModalRestaurantInputs();
                } else {
                    console.error('加载餐厅店面失败:', result.message);
                    // 如果加载失败，使用默认的餐厅店面
                    restaurants = [
                        { id: 1, name: '文化楼', code: 'wenhua', display_order: 1 },
                        { id: 2, name: '中央', code: 'central', display_order: 2 },
                        { id: 3, name: 'J1', code: 'j1', display_order: 3 },
                        { id: 4, name: 'J2', code: 'j2', display_order: 4 },
                        { id: 5, name: 'J3', code: 'j3', display_order: 5 }
                    ];
                }
            } catch (error) {
                console.error('加载餐厅店面时发生错误:', error);
                // 使用默认的餐厅店面
                restaurants = [
                    { id: 1, name: '文化楼', code: 'wenhua', display_order: 1 },
                    { id: 2, name: '中央', code: 'central', display_order: 2 },
                    { id: 3, name: 'J1', code: 'j1', display_order: 3 },
                    { id: 4, name: 'J2', code: 'j2', display_order: 4 },
                    { id: 5, name: 'J3', code: 'j3', display_order: 5 }
                ];
            }
        }

        // 获取动态字段定义（包含动态餐厅店面列）
        function getDynamicFieldDefs() {
            const baseFields = [
                { label: 'NO', key: 'no' },
                { label: '照片', key: 'photo' },
                { label: '编号', key: 'code_number' },
                { label: '产品名称', key: 'product_name' },
                { label: '分类', key: 'category' },
                { label: '尺寸', key: 'size' },
                { label: '单价', key: 'unit_price' }
            ];
            
            // 添加动态餐厅店面列（使用索引作为key，因为不再有code）
            const restaurantFields = restaurants
                .sort((a, b) => (a.display_order || 0) - (b.display_order || 0))
                .map((restaurant, index) => ({
                    label: restaurant.name,
                    key: 'restaurant_' + index,
                    restaurantId: restaurant.id
                }));
            
            // 添加总数和操作列
            const endFields = [
                { label: '总数', key: 'total' },
                { label: '操作', key: 'actions' }
            ];
            
            return [...baseFields, ...restaurantFields, ...endFields];
        }

        // 填充餐厅店面数据到行对象
        function fillRestaurantStocks(rowData, item) {
            restaurants
                .sort((a, b) => (a.display_order || 0) - (b.display_order || 0))
                .forEach((restaurant, index) => {
                    // 使用餐厅ID从restaurant_stocks中获取，或者使用索引
                    const quantity = item.restaurant_stocks?.[restaurant.id] || 
                                   item['restaurant_' + index + '_quantity'] || 
                                   0;
                    rowData['restaurant_' + index] = String(quantity);
                });
            return rowData;
        }

        // 更新编辑模态框中的餐厅店面输入框
        function updateEditModalRestaurantInputs() {
            const container = document.getElementById('edit-restaurant-quantities');
            if (!container) return;
            
            container.innerHTML = restaurants
                .sort((a, b) => (a.display_order || 0) - (b.display_order || 0))
                .map((restaurant, index) => `
                    <div class="quantity-field">
                        <label>${restaurant.name}数量</label>
                        <input type="number" id="edit-restaurant-${restaurant.id}" min="0" class="quantity-input" data-restaurant-id="${restaurant.id}">
                    </div>
                `).join('');
        }

        // 填充编辑模态框的餐厅店面数据
        function fillEditModalRestaurantData(item) {
            restaurants
                .sort((a, b) => (a.display_order || 0) - (b.display_order || 0))
                .forEach((restaurant, index) => {
                    const input = document.getElementById(`edit-restaurant-${restaurant.id}`);
                    if (input) {
                        // 优先从restaurant_stocks中获取，否则使用索引字段
                        const quantity = item.restaurant_stocks?.[restaurant.id] || 
                                       item['restaurant_' + index + '_quantity'] || 
                                       0;
                        input.value = quantity;
                    }
                });
        }

        // 获取编辑模态框的餐厅店面数据
        function getEditModalRestaurantData() {
            const data = {
                restaurant_quantities: []
            };
            restaurants
                .sort((a, b) => (a.display_order || 0) - (b.display_order || 0))
                .forEach((restaurant) => {
                    const input = document.getElementById(`edit-restaurant-${restaurant.id}`);
                    if (input) {
                        data.restaurant_quantities.push(parseInt(input.value) || 0);
                    }
                });
            return data;
        }

        // 更新表格头部（用于普通表格，非转置表格）
        function updateTableHeaders() {
            // 更新总库存表格头部
            const stockTable = document.querySelector('#stock-table thead tr');
            if (stockTable) {
                const baseHeaders = ['No.', '照片', '产品名称', '编号', '分类', '尺寸', '单价'];
                const sortedRestaurants = [...restaurants].sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
                const restaurantHeaders = sortedRestaurants.map(r => ({
                    name: r.name,
                    id: r.id
                }));
                const endHeaders = ['总数', '操作'];
                
                let html = '';
                baseHeaders.forEach(h => {
                    html += `<th>${h}</th>`;
                });
                restaurantHeaders.forEach(r => {
                    html += `<th data-restaurant-header data-restaurant-id="${r.id}" draggable="true">${r.name}</th>`;
                });
                endHeaders.forEach(h => {
                    html += `<th>${h}</th>`;
                });
                
                stockTable.innerHTML = html;
                
                // 初始化列头拖拽功能
                initColumnDragAndDrop('#stock-table');
            }
            
            // 更新套装管理表格头部
            const setsTable = document.querySelector('#sets-table thead tr');
            if (setsTable) {
                const baseHeaders = ['序号', '套装名称', '套装编号', '包含项目', '单价 (RM)'];
                const sortedRestaurants = [...restaurants].sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
                const restaurantHeaders = sortedRestaurants.map(r => ({
                    name: r.name,
                    id: r.id
                }));
                const endHeaders = ['总库存', '操作'];
                
                let html = '';
                baseHeaders.forEach(h => {
                    html += `<th>${h}</th>`;
                });
                restaurantHeaders.forEach(r => {
                    html += `<th data-restaurant-header data-restaurant-id="${r.id}" draggable="true">${r.name}</th>`;
                });
                endHeaders.forEach(h => {
                    html += `<th>${h}</th>`;
                });
                
                setsTable.innerHTML = html;
                
                // 初始化列头拖拽功能
                initColumnDragAndDrop('#sets-table');
            }
        }

        // 初始化列头拖拽功能（用于普通表格的列头）
        let draggedColumn = null;
        let draggedColumnIndex = null;

        function initColumnDragAndDrop(tableSelector) {
            const table = document.querySelector(tableSelector);
            if (!table) return;
            
            const restaurantHeaders = table.querySelectorAll('thead th[data-restaurant-header]');
            
            restaurantHeaders.forEach((header, index) => {
                // 移除旧的事件监听器（如果存在）
                const newHeader = header.cloneNode(true);
                header.parentNode.replaceChild(newHeader, header);
                
                newHeader.addEventListener('dragstart', (e) => handleColumnDragStart(e, newHeader, index));
                newHeader.addEventListener('dragover', handleColumnDragOver);
                newHeader.addEventListener('dragenter', handleColumnDragEnter);
                newHeader.addEventListener('dragleave', handleColumnDragLeave);
                newHeader.addEventListener('drop', (e) => handleColumnDrop(e, newHeader, table));
                newHeader.addEventListener('dragend', handleColumnDragEnd);
            });
        }

        function handleColumnDragStart(e, header, index) {
            draggedColumn = header;
            draggedColumnIndex = index;
            header.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', header.innerHTML);
        }

        function handleColumnDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            return false;
        }

        function handleColumnDragEnter(e) {
            const targetHeader = e.target.closest('th[data-restaurant-header]');
            if (targetHeader && targetHeader !== draggedColumn) {
                targetHeader.classList.add('drag-over');
            }
        }

        function handleColumnDragLeave(e) {
            const targetHeader = e.target.closest('th[data-restaurant-header]');
            if (targetHeader) {
                targetHeader.classList.remove('drag-over');
            }
        }

        function handleColumnDrop(e, targetHeader, table) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            e.preventDefault();

            if (!draggedColumn || !targetHeader || draggedColumn === targetHeader) {
                targetHeader.classList.remove('drag-over');
                return false;
            }

            const thead = table.querySelector('thead tr');
            if (!thead) return false;

            const restaurantHeaders = Array.from(thead.querySelectorAll('th[data-restaurant-header]'));
            const draggedIndex = restaurantHeaders.indexOf(draggedColumn);
            const targetIndex = restaurantHeaders.indexOf(targetHeader);

            if (draggedIndex === -1 || targetIndex === -1 || draggedIndex === targetIndex) {
                targetHeader.classList.remove('drag-over');
                return false;
            }

            // 保存原始顺序
            const originalOrder = restaurantHeaders.map(h => parseInt(h.getAttribute('data-restaurant-id')));

            // 移动列头
            try {
                if (draggedIndex < targetIndex) {
                    const nextSibling = targetHeader.nextSibling;
                    if (nextSibling && nextSibling.parentNode === thead) {
                        thead.insertBefore(draggedColumn, nextSibling);
                    } else {
                        thead.appendChild(draggedColumn);
                    }
                } else {
                    thead.insertBefore(draggedColumn, targetHeader);
                }

                // 同时移动对应的数据列
                moveTableColumns(table, draggedIndex, targetIndex);

                // 获取新的顺序
                const newRestaurantHeaders = Array.from(thead.querySelectorAll('th[data-restaurant-header]'));
                const newOrder = newRestaurantHeaders.map(h => parseInt(h.getAttribute('data-restaurant-id')));

                // 更新顺序到数据库
                updateRestaurantOrder(newOrder, originalOrder);
            } catch (error) {
                console.error('移动列时发生错误:', error);
                targetHeader.classList.remove('drag-over');
                return false;
            }

            targetHeader.classList.remove('drag-over');
            return false;
        }

        function handleColumnDragEnd(e) {
            const headers = document.querySelectorAll('th[data-restaurant-header]');
            headers.forEach(header => {
                header.classList.remove('dragging', 'drag-over');
            });
            draggedColumn = null;
            draggedColumnIndex = null;
        }

        // 移动表格的数据列（与列头同步）
        function moveTableColumns(table, fromIndex, toIndex) {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            // 计算实际列索引（需要考虑基础列）
            const baseColumnCount = table.id === 'stock-table' ? 7 : 5; // No., 照片, 产品名称, 编号, 分类, 尺寸, 单价 或 序号, 套装名称, 套装编号, 包含项目, 单价
            const fromColIndex = baseColumnCount + fromIndex;
            const toColIndex = baseColumnCount + toIndex;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.forEach(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                if (cells.length > Math.max(fromColIndex, toColIndex)) {
                    const cell = cells[fromColIndex];
                    if (cell && cell.parentNode === row) {
                        // 先移除单元格
                        const removedCell = row.removeChild(cell);
                        
                        // 重新计算目标位置（因为已经移除了一个单元格）
                        const newTargetIndex = fromIndex < toIndex ? toColIndex - 1 : toColIndex;
                        const targetCell = cells[newTargetIndex];
                        
                        if (targetCell && targetCell.parentNode === row) {
                            if (fromIndex < toIndex) {
                                // 向下移动：插入到目标单元格之后
                                const nextSibling = targetCell.nextSibling;
                                if (nextSibling) {
                                    row.insertBefore(removedCell, nextSibling);
                                } else {
                                    row.appendChild(removedCell);
                                }
                            } else {
                                // 向上移动：插入到目标单元格之前
                                row.insertBefore(removedCell, targetCell);
                            }
                        } else {
                            // 如果找不到目标单元格，追加到末尾
                            row.appendChild(removedCell);
                        }
                    }
                }
            });
        }

        // 初始化拖拽排序功能
        function initDragAndDrop() {
            // 等待表格渲染完成
            setTimeout(() => {
                const restaurantRows = document.querySelectorAll('.stock-table.transposed tr[data-restaurant-row]');
                
                restaurantRows.forEach(row => {
                    // 只让NO列的th可以拖动
                    const noHeader = row.querySelector('th.row-header:first-child');
                    if (noHeader) {
                        noHeader.setAttribute('draggable', 'true');
                        noHeader.addEventListener('dragstart', handleDragStart.bind(row));
                        noHeader.addEventListener('dragover', handleDragOver.bind(row));
                        noHeader.addEventListener('dragenter', handleDragEnter.bind(row));
                        noHeader.addEventListener('dragleave', handleDragLeave.bind(row));
                        noHeader.addEventListener('drop', handleDrop.bind(row));
                        noHeader.addEventListener('dragend', handleDragEnd.bind(row));
                    }
                    
                    // 移除整行的draggable属性（如果之前设置过）
                    row.removeAttribute('draggable');
                });
            }, 100);
        }

        let draggedRow = null;
        let draggedRestaurantId = null;

        function handleDragStart(e) {
            // this 是 th 元素，需要获取其父行
            draggedRow = this.closest('tr[data-restaurant-row]');
            if (!draggedRow) return;
            
            draggedRestaurantId = draggedRow.getAttribute('data-restaurant-id');
            draggedRow.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', draggedRow.innerHTML);
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            return false;
        }

        function handleDragEnter(e) {
            // this 是 th 元素，需要获取其父行
            const targetRow = this.closest('tr[data-restaurant-row]');
            if (targetRow && targetRow !== draggedRow) {
                targetRow.classList.add('drag-over');
            }
        }

        function handleDragLeave(e) {
            // this 是 th 元素，需要获取其父行
            const targetRow = this.closest('tr[data-restaurant-row]');
            if (targetRow) {
                targetRow.classList.remove('drag-over');
            }
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            e.preventDefault();

            // this 是 th 元素，需要获取其父行
            const targetRow = this.closest('tr[data-restaurant-row]');
            if (!targetRow || !draggedRow || draggedRow === targetRow) {
                if (targetRow) targetRow.classList.remove('drag-over');
                return false;
            }

            const table = targetRow.closest('table');
            if (!table || !draggedRow) return false;
            
            // 获取父节点（可能是 tbody 或 table）
            const parentNode = draggedRow.parentNode;
            if (!parentNode || parentNode !== targetRow.parentNode) {
                console.error('拖拽行和目标行不在同一个父节点下');
                targetRow.classList.remove('drag-over');
                return false;
            }
            
            // 获取所有餐厅行（在拖拽前的位置）
            const allRestaurantRows = Array.from(parentNode.querySelectorAll('tr[data-restaurant-row]'));
            const draggedIndex = allRestaurantRows.indexOf(draggedRow);
            const targetIndex = allRestaurantRows.indexOf(targetRow);
            
            if (draggedIndex === -1 || targetIndex === -1 || draggedIndex === targetIndex) {
                targetRow.classList.remove('drag-over');
                return false;
            }
            
            // 保存原始顺序（用于失败时恢复）
            const originalOrder = allRestaurantRows.map(row => parseInt(row.getAttribute('data-restaurant-id')));
            
            // 移动DOM元素（立即更新UI）
            try {
                // 先移除 draggedRow（如果它还在 DOM 中）
                if (draggedRow.parentNode === parentNode) {
                    if (draggedIndex < targetIndex) {
                        // 向下拖拽：插入到目标行的下一个兄弟节点之前
                        const nextSibling = targetRow.nextSibling;
                        if (nextSibling && nextSibling.parentNode === parentNode) {
                            parentNode.insertBefore(draggedRow, nextSibling);
                        } else {
                            // 如果没有下一个兄弟节点，追加到末尾
                            parentNode.appendChild(draggedRow);
                        }
                    } else {
                        // 向上拖拽：插入到目标行之前
                        parentNode.insertBefore(draggedRow, targetRow);
                    }
                } else {
                    console.error('拖拽行已不在父节点中');
                    targetRow.classList.remove('drag-over');
                    return false;
                }
                
                // 获取新的顺序
                const newRestaurantRows = Array.from(parentNode.querySelectorAll('tr[data-restaurant-row]'));
                const newOrder = newRestaurantRows.map(row => parseInt(row.getAttribute('data-restaurant-id')));
                
                // 更新顺序到数据库
                updateRestaurantOrder(newOrder, originalOrder);
            } catch (error) {
                console.error('移动行时发生错误:', error);
                // 如果失败，恢复原始顺序
                restoreRestaurantOrder(originalOrder);
                targetRow.classList.remove('drag-over');
                return false;
            }
            
            targetRow.classList.remove('drag-over');
            return false;
        }

        function handleDragEnd(e) {
            const rows = document.querySelectorAll('.stock-table.transposed tr[data-restaurant-row]');
            rows.forEach(row => {
                row.classList.remove('dragging', 'drag-over');
            });
            draggedRow = null;
            draggedRestaurantId = null;
        }

        // 更新餐厅店面顺序
        async function updateRestaurantOrder(newOrder, originalOrder = null) {
            if (!newOrder || newOrder.length === 0) return;
            
            // 如果顺序没有变化，不需要更新
            if (originalOrder && JSON.stringify(newOrder) === JSON.stringify(originalOrder)) {
                return;
            }
            
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'update_restaurant_order',
                        orders: newOrder
                    })
                });
                
                if (result.success) {
                    // 更新本地 restaurants 数组的顺序
                    const sortedRestaurants = newOrder.map((id, index) => {
                        const restaurant = restaurants.find(r => r.id == id);
                        if (restaurant) {
                            restaurant.display_order = index + 1;
                        }
                        return restaurant;
                    }).filter(r => r);
                    
                    // 保持其他餐厅（如果有新添加的）
                    const otherRestaurants = restaurants.filter(r => !newOrder.includes(r.id));
                    restaurants = [...sortedRestaurants, ...otherRestaurants].sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
                    
                    // 重新加载数据以更新所有表格的显示
                    // 注意：由于顺序已更新，两个页面都会自动同步
                    // 无论当前在哪个页面，都重新加载两个页面的数据以确保同步
                    if (currentPage === 'stock') {
                        await loadStockData(true);
                        // 同时更新套装管理页面（如果已加载）
                        if (document.getElementById('sets-table')) {
                            await loadSetsData();
                        }
                    } else if (currentPage === 'stock' && stockViewType === 'sets') {
                        await loadSetsData();
                        // 同时更新总库存页面（如果已加载）
                        if (document.getElementById('stock-table')) {
                            await loadStockData(true);
                        }
                    }
                    
                    // 重新初始化拖拽功能
                    setTimeout(() => {
                        initDragAndDrop();
                        // 同时更新表头（如果是普通表格视图）
                        updateTableHeaders();
                    }, 100);
                    
                    showAlert('餐厅店面顺序已更新', 'success');
                } else {
                    showAlert('更新顺序失败: ' + (result.message || '未知错误'), 'error');
                    // 如果失败，恢复原始顺序
                    if (originalOrder) {
                        restoreRestaurantOrder(originalOrder);
                    } else {
                        // 如果不知道原始顺序，重新加载数据
                        if (currentPage === 'stock') {
                            await loadStockData(true);
                        }
                    }
                }
            } catch (error) {
                console.error('更新餐厅店面顺序时发生错误:', error);
                showAlert('更新顺序失败: ' + error.message, 'error');
                // 如果失败，恢复原始顺序
                if (originalOrder) {
                    restoreRestaurantOrder(originalOrder);
                } else {
                    // 如果不知道原始顺序，重新加载数据
                    if (currentPage === 'stock') {
                        await loadStockData(true);
                    }
                }
            }
        }

        // 恢复餐厅店面顺序（当更新失败时）
        function restoreRestaurantOrder(originalOrder) {
            const tables = document.querySelectorAll('.stock-table.transposed');
            tables.forEach(table => {
                // 获取父节点（可能是 tbody 或 table）
                const tbody = table.querySelector('tbody');
                const parentNode = tbody || table;
                
                const rows = Array.from(parentNode.querySelectorAll('tr[data-restaurant-row]'));
                const rowMap = new Map();
                rows.forEach(row => {
                    const id = parseInt(row.getAttribute('data-restaurant-id'));
                    rowMap.set(id, row);
                });
                
                // 按照原始顺序重新排列
                originalOrder.forEach(id => {
                    const row = rowMap.get(id);
                    if (row && row.parentNode === parentNode) {
                        parentNode.appendChild(row);
                    }
                });
            });
        }

        // 设置页面切换器
        function setupPageSwitcher() {
            // 设置默认激活的下拉菜单项
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.classList.remove('active');
                if (item.onclick.toString().includes("'stock'")) {
                    item.classList.add('active');
                }
            });
        }

        // 切换页面选择器下拉菜单
        function toggleViewSelector() {
            document.getElementById('view-selector-dropdown').classList.toggle('show');
        }

        // 页面切换函数
        function switchPage(pageType) {
            currentPage = pageType;
            
            // 更新下拉按钮文本
            const currentView = document.getElementById('current-view');
            const pageNames = {
                'stock': '总库存',
                'j1': '破损记录',
                'j2': '破损记录',
                'j3': '破损记录',
                'transfer': '碗碟转卖'
            };
            
            if (currentView) {
                currentView.textContent = pageNames[pageType];
            }
            
            // 更新active状态
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            const activeItem = document.querySelector(`.dropdown-item[onclick*="'${pageType}'"]`);
            if (activeItem) {
                activeItem.classList.add('active');
            }
            
            // 隐藏所有页面
            const pages = document.querySelectorAll('.page-content');
            pages.forEach(page => {
                page.style.display = 'none';
            });
            
            // 显示当前页面（j1、j2、j3都显示j1-page）
            let pageId = `${pageType}-page`;
            if (pageType === 'j2' || pageType === 'j3') {
                pageId = 'j1-page';
            }
            const currentPageElement = document.getElementById(pageId);
            if (currentPageElement) {
                currentPageElement.style.display = 'block';
            }
            
            // 如果是转卖页面，显示transfer-page
            if (pageType === 'transfer') {
                const transferPage = document.getElementById('transfer-page');
                if (transferPage) {
                    transferPage.style.display = 'block';
                }
            }
            
            // 如果是总库存页面，根据视图类型显示相应内容
            if (pageType === 'stock') {
                switchStockView(stockViewType);
            }
            
            // 根据页面类型更新页面标题和按钮
            updatePageHeader(pageType);
            
            // 加载对应页面的数据
            loadPageData(pageType);
            
            // 更新统计信息
            updateStats();
            
            // 隐藏下拉菜单
            document.getElementById('view-selector-dropdown').classList.remove('show');
        }

        // 更新页面头部
        function updatePageHeader(pageType) {
            const title = document.getElementById('page-title');
            const addButton = document.getElementById('add-dishware-btn');
            
            switch(pageType) {
                case 'stock':
                    if (title) title.textContent = '总库存';
                    updateStockViewButton();
                    updateStockAddButton();
                    restoreCategoryFilter();
                    break;
                case 'j1':
                case 'j2':
                case 'j3':
                    if (title) title.textContent = '破损记录';
                    if (addButton) {
                        // 隐藏顶部的"记录破损"按钮，因为每个容器都有自己的按钮
                        addButton.style.display = 'none';
                    }
                    // 将分类下拉菜单改为餐厅选择
                    updateCategoryFilterToRestaurantForBreak();
                    // 加载所有破损记录
                    loadAllBreakRecords();
                    break;
                case 'transfer':
                    if (title) title.textContent = '碗碟转卖';
                    if (addButton) {
                        // 隐藏顶部的按钮，因为每个容器都有自己的按钮
                        addButton.style.display = 'none';
                    }
                    // 将分类下拉菜单改为餐厅选择
                    updateCategoryFilterToRestaurant();
                    // 加载所有转卖记录
                    loadAllTransferRecords();
                    break;
                default:
                    // 其他页面恢复分类下拉菜单
                    restoreCategoryFilter();
                    break;
            }
        }

        // 切换总库存页面的视图（碗碟/套装）
        function switchStockView(viewType) {
            stockViewType = viewType;
            
            const dishwareView = document.getElementById('dishware-view');
            const setsView = document.getElementById('sets-view');
            const stockViewBtn = document.getElementById('stock-view-btn');
            const setsViewBtn = document.getElementById('sets-view-btn');
            
            if (viewType === 'dishware') {
                if (dishwareView) dishwareView.style.display = '';
                if (setsView) setsView.style.display = 'none';
                if (stockViewBtn) {
                    stockViewBtn.style.background = '#f99e00';
                    stockViewBtn.style.color = 'white';
                }
                if (setsViewBtn) {
                    setsViewBtn.style.background = 'white';
                    setsViewBtn.style.color = '#333';
                }
                // 加载碗碟数据
                loadStockData();
            } else if (viewType === 'sets') {
                if (dishwareView) dishwareView.style.display = 'none';
                if (setsView) setsView.style.display = '';
                if (stockViewBtn) {
                    stockViewBtn.style.background = 'white';
                    stockViewBtn.style.color = '#333';
                }
                if (setsViewBtn) {
                    setsViewBtn.style.background = '#f99e00';
                    setsViewBtn.style.color = 'white';
                }
                // 加载套装数据
                loadSetsData();
            }
            
            updateStockAddButton();
            updateStats();
        }
        
        // 更新总库存页面的视图切换按钮状态
        function updateStockViewButton() {
            if (currentPage !== 'stock') return;
            
            const stockViewBtn = document.getElementById('stock-view-btn');
            const setsViewBtn = document.getElementById('sets-view-btn');
            
            if (stockViewType === 'dishware') {
                if (stockViewBtn) {
                    stockViewBtn.style.background = '#f99e00';
                    stockViewBtn.style.color = 'white';
                }
                if (setsViewBtn) {
                    setsViewBtn.style.background = 'white';
                    setsViewBtn.style.color = '#333';
                }
            } else {
                if (stockViewBtn) {
                    stockViewBtn.style.background = 'white';
                    stockViewBtn.style.color = '#333';
                }
                if (setsViewBtn) {
                    setsViewBtn.style.background = '#f99e00';
                    setsViewBtn.style.color = 'white';
                }
            }
        }
        
        // 更新总库存页面的添加按钮
        function updateStockAddButton() {
            if (currentPage !== 'stock') return;
            
            const addButton = document.getElementById('add-dishware-btn');
            if (!addButton) return;
            
            if (stockViewType === 'dishware') {
                addButton.innerHTML = '<i class="fas fa-plus"></i> 添加碗碟';
                addButton.onclick = () => openAddModal();
                addButton.style.display = 'inline-flex';
            } else {
                addButton.innerHTML = '<i class="fas fa-plus"></i> 添加套装';
                addButton.onclick = () => openSetModal();
                addButton.style.display = 'inline-flex';
            }
        }

        // 加载页面数据
        function loadPageData(pageType) {
            switch(pageType) {
                case 'stock':
                    if (stockViewType === 'dishware') {
                        loadStockData();
                    } else {
                        loadSetsData();
                    }
                    break;
                case 'j1':
                case 'j2':
                case 'j3':
                    loadAllBreakRecords();
                    break;
                case 'transfer':
                    loadAllTransferRecords();
                    break;
            }
        }

        // 加载打破记录
        async function loadBreakRecords(shopType) {
            console.log('loadBreakRecords 被调用，shopType:', shopType);
            try {
                // 同时加载J1、J2、J3的破损记录
                const [j1Result, j2Result, j3Result] = await Promise.all([
                    apiCall('?action=damage_records&shop_type=j1'),
                    apiCall('?action=damage_records&shop_type=j2'),
                    apiCall('?action=damage_records&shop_type=j3')
                ]);
                
                // 存储破损记录数据
                if (j1Result.success) {
                    breakRecordsData['j1'] = j1Result.data || [];
                }
                if (j2Result.success) {
                    breakRecordsData['j2'] = j2Result.data || [];
                }
                if (j3Result.success) {
                    breakRecordsData['j3'] = j3Result.data || [];
                }
                
                // 渲染合并的破损记录表格
                renderCombinedBreakRecords();
                updateStats();
                
            } catch (error) {
                console.error('加载破损记录时发生错误:', error);
                showAlert('加载破损记录失败: ' + error.message, 'error');
                renderCombinedBreakRecords();
            }
        }

        // 渲染破损记录表格（合并页面，左右排列）
        function renderBreakRecordsTable(shopType, records) {
            // 如果传入的是单个shopType，加载所有三个店铺的数据
            if (shopType === 'j1' || shopType === 'j2' || shopType === 'j3') {
                loadAllBreakRecords();
                return;
            }
        }

        // 加载所有店铺的破损记录（只加载J开头的餐厅）
        async function loadAllBreakRecords() {
            try {
                // 获取所有餐厅列表，筛选出J开头的餐厅（排除"中央"和"文化楼"）
                const jRestaurants = restaurants.filter(r => {
                    const name = r.name.toLowerCase();
                    const lowerName = r.name.toLowerCase();
                    // 只显示J开头的餐厅，排除"中央"和"文化楼"
                    return lowerName.startsWith('j') && 
                           lowerName !== '中央' && 
                           lowerName !== '文化楼' &&
                           name !== 'wenhua' && 
                           name !== 'central';
                }).sort((a, b) => {
                    // 按名称排序（J1, J2, J3, J4...）
                    const nameA = a.name.toLowerCase();
                    const nameB = b.name.toLowerCase();
                    const numA = parseInt(nameA.replace('j', '')) || 0;
                    const numB = parseInt(nameB.replace('j', '')) || 0;
                    return numA - numB;
                });

                // 同时加载所有J开头店铺的数据
                const promises = jRestaurants.map(restaurant => {
                    const shopType = restaurant.name.toLowerCase();
                    return apiCall(`?action=damage_records&shop_type=${shopType}`).then(result => ({
                        shopType: shopType,
                        restaurant: restaurant,
                        result: result
                    }));
                });

                const results = await Promise.all(promises);

                // 存储数据
                results.forEach(({ shopType, result }) => {
                    if (result.success) {
                        breakRecordsData[shopType] = result.data || [];
                    } else {
                        breakRecordsData[shopType] = [];
                    }
                });

                // 存储J餐厅列表供渲染使用
                window.jRestaurantsForBreak = jRestaurants;

                // 渲染合并页面
                renderMergedBreakRecordsPage();
                // 应用餐厅过滤
                filterBreakRecordsByRestaurant();
                updateStats();
            } catch (error) {
                console.error('加载破损记录时发生错误:', error);
                showAlert('加载破损记录失败: ' + error.message, 'error');
            }
        }

        // 刷新单个餐厅的破损记录（保留新行）
        async function refreshSingleRestaurantBreakRecords(shopType, excludeRecordId = null) {
            try {
                // 只加载对应餐厅的数据
                const result = await apiCall(`?action=damage_records&shop_type=${shopType}`);
                
                if (result.success) {
                    // 更新数据
                    breakRecordsData[shopType] = result.data || [];
                    
                    // 找到对应的表格tbody
                    const tbody = document.getElementById(`${shopType}-break-tbody`);
                    if (!tbody) return;
                    
                    // 保存所有新行（.new-row）及其数据
                    const newRows = Array.from(tbody.querySelectorAll('tr.new-row'));
                    const newRowsData = newRows.map(row => {
                        const codeInput = row.querySelector('.break-code-input');
                        const quantityInput = row.querySelector('.break-quantity-input');
                        const priceInput = row.querySelector('.break-price-input');
                        const rowId = codeInput?.id?.replace('-code', '') || '';
                        
                        return {
                            row: row.cloneNode(true), // 克隆节点
                            rowId: rowId,
                            code: codeInput?.value || '',
                            quantity: quantityInput?.value || '',
                            price: priceInput?.value || '',
                            productId: codeInput?.dataset?.productId || ''
                        };
                    });
                    
                    // 保存所有正在编辑的行（.editing-row）及其数据，排除当前保存的行
                    const editingRows = Array.from(tbody.querySelectorAll('tr.editing-row'));
                    const editingRowsData = editingRows
                        .filter(row => {
                            const recordId = row.dataset.id;
                            return recordId && recordId != excludeRecordId;
                        })
                        .map(row => {
                            const recordId = row.dataset.id;
                            const codeInput = row.querySelector('.break-code-input');
                            const quantitySpan = row.querySelector('.editable-quantity');
                            const codeRowId = codeInput?.id?.replace('-code', '') || '';
                            
                            // 保存完整的行节点（克隆）
                            return {
                                recordId: recordId,
                                rowClone: row.cloneNode(true), // 深度克隆整个行节点
                                codeRowId: codeRowId,
                                code: codeInput?.value || '',
                                quantity: quantitySpan?.textContent?.trim() || '',
                                productId: codeInput?.dataset?.productId || '',
                                originalCode: row.dataset.originalCode || '',
                                originalQuantity: row.dataset.originalQuantity || ''
                            };
                        });
                    
                    // 重新渲染该餐厅的表格行（不包括新行和正在编辑的行）
                    const records = breakRecordsData[shopType] || [];
                    const rowsHtml = renderBreakRecordsRows(records, shopType);
                    
                    // 清空tbody并添加已保存的记录
                    tbody.innerHTML = rowsHtml;
                    
                    // 恢复正在编辑的行 - 直接替换对应行
                    editingRowsData.forEach(({ recordId, rowClone, codeRowId, code, quantity, productId }) => {
                        const restoredRow = tbody.querySelector(`tr[data-id="${recordId}"][data-shop="${shopType}"]`);
                        if (restoredRow && rowClone) {
                            // 从克隆的行中恢复输入框的值
                            const codeInput = rowClone.querySelector('.break-code-input');
                            const quantitySpan = rowClone.querySelector('.editable-quantity');
                            
                            if (codeInput) {
                                codeInput.value = code;
                                if (productId) {
                                    codeInput.dataset.productId = productId;
                                    codeInput.setAttribute('data-product-id', productId);
                                }
                            }
                            if (quantitySpan) {
                                quantitySpan.textContent = quantity;
                            }
                            
                            // 确保行有 editing-row 类
                            rowClone.classList.add('editing-row');
                            
                            // 替换当前行
                            restoredRow.replaceWith(rowClone);
                            
                            // 重新绑定事件
                            setTimeout(() => {
                                bindBreakComboboxEvents(codeRowId);
                            }, 100);
                        }
                    });
                    
                    // 重新添加所有新行
                    newRowsData.forEach(({ row, rowId, code, quantity, price, productId }) => {
                        if (row && rowId) {
                            // 恢复输入框的值
                            const clonedCodeInput = row.querySelector('.break-code-input');
                            const clonedQuantityInput = row.querySelector('.break-quantity-input');
                            const clonedPriceInput = row.querySelector('.break-price-input');
                            
                            if (clonedCodeInput) {
                                clonedCodeInput.value = code;
                                if (productId) {
                                    clonedCodeInput.dataset.productId = productId;
                                    clonedCodeInput.setAttribute('data-product-id', productId);
                                }
                            }
                            if (clonedQuantityInput) {
                                clonedQuantityInput.value = quantity;
                            }
                            if (clonedPriceInput) {
                                clonedPriceInput.value = price;
                            }
                            
                            tbody.appendChild(row);
                            
                            // 重新绑定事件
                            setTimeout(() => {
                                bindBreakComboboxEvents(rowId);
                            }, 100);
                        }
                    });
                }
            } catch (error) {
                console.error('刷新单个餐厅破损记录时发生错误:', error);
            }
        }

        // 渲染合并的破损记录页面
        function renderMergedBreakRecordsPage() {
            // 更新所有可能的容器
            const containers = [
                document.getElementById('break-records-container'),
                document.getElementById('break-records-container-j2'),
                document.getElementById('break-records-container-j3')
            ].filter(c => c !== null);
            
            if (containers.length === 0) {
                console.error('找不到破损记录容器');
                return;
            }

            // 使用动态获取的J餐厅列表
            const jRestaurants = window.jRestaurantsForBreak || restaurants.filter(r => {
                const name = r.name.toLowerCase();
                const lowerName = r.name.toLowerCase();
                // 只显示J开头的餐厅，排除"中央"和"文化楼"
                return lowerName.startsWith('j') && 
                       lowerName !== '中央' && 
                       lowerName !== '文化楼' &&
                       name !== 'wenhua' && 
                       name !== 'central';
            }).sort((a, b) => {
                const nameA = a.name.toLowerCase();
                const nameB = b.name.toLowerCase();
                const numA = parseInt(nameA.replace('j', '')) || 0;
                const numB = parseInt(nameB.replace('j', '')) || 0;
                return numA - numB;
            });

            if (jRestaurants.length === 0) {
                containers.forEach(container => {
                    container.innerHTML = `
                        <div style="padding: 40px; text-align: center; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: clamp(42px, 2.5vw, 48px); opacity: 0.5; margin-bottom: clamp(8px, 0.83vw, 16px);"></i>
                            <div>暂无J开头的餐厅店面</div>
                        </div>
                    `;
                });
                return;
            }

            let html = '';
            
            jRestaurants.forEach(restaurant => {
                const shopType = restaurant.name.toLowerCase();
                const records = breakRecordsData[shopType] || [];
                
                // 计算总破损金额
                const totalBreakAmount = records.reduce((sum, record) => {
                    return sum + (parseFloat(record.total_price) || 0);
                }, 0);
                
                html += `
                    <div class="break-record-section">
                        <div class="break-record-header">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span>${restaurant.name}</span>
                                <span style="font-size: clamp(14px, 0.94vw, 18px); opacity: 0.9;">总破损：RM ${formatCurrency(totalBreakAmount)}</span>
                            </div>
                            <button class="btn btn-success" onclick="openBreakRowsModal('${shopType}')" style="padding: clamp(3px, 0.31vw, 6px) clamp(6px, 0.63vw, 12px); font-size: clamp(8px, 0.74vw, 12px); white-space: nowrap;">
                                <i class="fas fa-plus"></i> 记录破损
                            </button>
                        </div>
                        <div class="break-record-table-wrapper">
                            <table class="break-record-table" id="${shopType}-break-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>编号</th>
                                        <th>数量</th>
                                        <th>单价</th>
                                        <th>总价</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="${shopType}-break-tbody">
                                    ${renderBreakRecordsRows(records, shopType)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });

            // 更新所有容器
            containers.forEach(container => {
                container.innerHTML = html;
            });
        }

        // 渲染破损记录行
        function renderBreakRecordsRows(records, shopId) {
            if (records.length === 0) {
                return `
                    <tr>
                        <td colspan="6" class="no-data" style="padding: clamp(20px, 2.76vw, 53px); text-align: center; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: clamp(42px, 2.5vw, 48px); opacity: 0.5; margin-bottom: clamp(8px, 0.83vw, 16px);"></i>
                            <div>暂无破损记录</div>
                        </td>
                    </tr>
                `;
            }

            let rows = '';
            records.forEach((record, index) => {
                rows += `
                    <tr data-id="${record.id}" data-shop="${shopId}">
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${record.code_number || '-'}</td>
                        <td class="text-center"><span>${record.break_quantity}</span></td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${formatCurrency(record.unit_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${formatCurrency(record.total_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="action-btn edit-btn" onclick="editBreakRecord(${record.id}, '${shopId}')" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteBreakRecord(${record.id}, '${shopId}')" title="删除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            return rows;
        }

        // 打开破损记录模态框
        function openBreakModal(shopType) {
            // 检查stockData是否已加载
            if (!stockData || stockData.length === 0) {
                showAlert('正在加载碗碟数据，请稍后再试', 'warning');
                // 尝试重新加载数据
                loadStockData(true, false).then(() => {
                    // 数据加载完成后，重新尝试打开模态框
                    setTimeout(() => {
                        openBreakModal(shopType);
                    }, 500);
                });
                return;
            }
            
            // 设置模态框标题
            const modalTitle = document.getElementById('damage-modal-title');
            modalTitle.textContent = `添加 ${shopType.toUpperCase()} 破损记录`;
            
            // 填充编号和产品选择下拉框
            populateDamageSelects();
            
            // 设置默认日期为今天
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('damage-date').value = today;
            
            // 清空表单（但保留日期）
            document.getElementById('damage-form').reset();
            document.getElementById('damage-date').value = today;
            
            // 存储当前店铺类型
            window.currentShopType = shopType;
            
            // 显示模态框
            document.getElementById('damageModal').style.display = 'block';
        }

        // 填充破损记录选择框
        function populateDamageSelects() {
            const codeSelect = document.getElementById('damage-code-select');
            const productSelect = document.getElementById('damage-product-select');
            
            if (!codeSelect || !productSelect) {
                console.error('找不到选择框元素');
                return;
            }
            
            // 清空选择框
            codeSelect.innerHTML = '<option value="">请选择编号</option>';
            productSelect.innerHTML = '<option value="">请选择产品</option>';
            
            console.log('填充破损记录选择框，stockData长度:', stockData.length);
            
            if (!stockData || stockData.length === 0) {
                console.warn('stockData为空或未加载');
                return;
            }
            
            // 收集所有单个碗碟（包括套装中的碗碟）
            const allIndividualItems = [];
            
            // 添加独立的单个碗碟
            const individualItems = stockData.filter(item => item.item_type === 'individual');
            allIndividualItems.push(...individualItems);
            
            // 添加套装中的碗碟
            const setItems = stockData.filter(item => item.item_type === 'set');
            setItems.forEach(set => {
                if (set.items && set.items.length > 0) {
                    allIndividualItems.push(...set.items);
                }
            });
            
            // 填充编号选择框
            const uniqueCodes = new Set();
            allIndividualItems.forEach(item => {
                if (item.code_number && !uniqueCodes.has(item.code_number)) {
                    uniqueCodes.add(item.code_number);
                    const option = document.createElement('option');
                    option.value = item.code_number;
                    option.textContent = item.code_number;
                    option.dataset.productName = item.product_name;
                    option.dataset.dishwareId = item.id;
                    option.dataset.price = item.unit_price;
                    codeSelect.appendChild(option);
                }
            });
            
            // 填充产品选择框
            allIndividualItems.forEach(item => {
                if (item.id && item.product_name) {
                    const option = document.createElement('option');
                    option.value = item.product_name;
                    option.textContent = `${item.product_name} (${item.code_number || '无编号'}) - RM${formatCurrency(item.unit_price)}`;
                    option.dataset.codeNumber = item.code_number;
                    option.dataset.dishwareId = item.id;
                    option.dataset.price = item.unit_price;
                    productSelect.appendChild(option);
                }
            });
            
            console.log('破损记录选择框已填充');
            console.log('编号选项数量:', codeSelect.options.length);
            console.log('产品选项数量:', productSelect.options.length);
        }

        // 处理编号选择变化
        function handleDamageCodeChange(codeSelect) {
            const productSelect = document.getElementById('damage-product-select');
            const unitPriceInput = document.getElementById('damage-unit-price');
            
            if (!productSelect || !unitPriceInput) return;
            
            const selectedOption = codeSelect.options[codeSelect.selectedIndex];
            if (selectedOption.value) {
                // 根据编号选择对应的产品
                const productName = selectedOption.dataset.productName;
                const dishwareId = selectedOption.dataset.dishwareId;
                const price = selectedOption.dataset.price;
                
                // 更新产品选择框
                for (let i = 0; i < productSelect.options.length; i++) {
                    if (productSelect.options[i].value === productName) {
                        productSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // 更新单价
                unitPriceInput.value = formatCurrency(price);
                calculateDamageTotal();
                
                // 存储dishware_id用于提交
                window.currentDishwareId = dishwareId;
            } else {
                // 清空产品选择
                productSelect.selectedIndex = 0;
                unitPriceInput.value = '';
                document.getElementById('damage-total-price').value = '';
                window.currentDishwareId = null;
            }
        }

        // 处理产品选择变化
        function handleDamageProductChange(productSelect) {
            const codeSelect = document.getElementById('damage-code-select');
            const unitPriceInput = document.getElementById('damage-unit-price');
            
            if (!codeSelect || !unitPriceInput) return;
            
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption.value) {
                // 根据产品选择对应的编号
                const codeNumber = selectedOption.dataset.codeNumber;
                const dishwareId = selectedOption.dataset.dishwareId;
                const price = selectedOption.dataset.price;
                
                // 更新编号选择框
                for (let i = 0; i < codeSelect.options.length; i++) {
                    if (codeSelect.options[i].value === codeNumber) {
                        codeSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // 更新单价
                unitPriceInput.value = formatCurrency(price);
                calculateDamageTotal();
                
                // 存储dishware_id用于提交
                window.currentDishwareId = dishwareId;
            } else {
                // 清空编号选择
                codeSelect.selectedIndex = 0;
                unitPriceInput.value = '';
                document.getElementById('damage-total-price').value = '';
                window.currentDishwareId = null;
            }
        }


        // 计算破损记录总价
        function calculateDamageTotal() {
            const quantity = parseFloat(document.getElementById('damage-quantity').value) || 0;
            const unitPrice = parseFloat(document.getElementById('damage-unit-price').value) || 0;
            const totalPrice = quantity * unitPrice;
            
            document.getElementById('damage-total-price').value = formatCurrency(totalPrice);
        }

        // 处理破损记录表单提交
        async function handleDamageFormSubmit(event) {
            event.preventDefault();
            
            if (!window.currentShopType) {
                showAlert('店铺类型未设置', 'error');
                return;
            }
            
            const form = event.target;
            const formData = new FormData(form);
            
            const breakQuantity = formData.get('break_quantity');
            const unitPrice = formData.get('unit_price');
            const totalPrice = formData.get('total_price');
            const breakDate = formData.get('break_date');
            
            // 获取dishware_id
            const dishwareId = window.currentDishwareId;
            
            // 调试信息
            console.log('--- 破损记录表单提交调试 ---');
            console.log('dishwareId:', dishwareId);
            console.log('typeof dishwareId:', typeof dishwareId);
            console.log('breakQuantity:', breakQuantity);
            console.log('unitPrice:', unitPrice);
            console.log('totalPrice:', totalPrice);
            
            // 额外调试：检查选择框状态
            const codeSelect = document.getElementById('damage-code-select');
            const productSelect = document.getElementById('damage-product-select');
            console.log('编号选择框值:', codeSelect ? codeSelect.value : '未找到元素');
            console.log('产品选择框值:', productSelect ? productSelect.value : '未找到元素');
            console.log('--- 调试结束 ---');
            
            // 更严格的验证
            if (!dishwareId) {
                showAlert('请选择产品', 'error');
                return;
            }
            
            if (!breakQuantity || breakQuantity <= 0) {
                showAlert('请输入有效的破损数量', 'error');
                return;
            }
            
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_damage_record',
                        dishware_id: dishwareId,
                        shop_type: window.currentShopType,
                        break_quantity: parseInt(breakQuantity),
                        unit_price: parseFloat(unitPrice) || 0,
                        total_price: parseFloat(totalPrice) || 0,
                        break_date: breakDate,
                        recorded_by: 'system'
                    })
                });
                
                if (result.success) {
                    showAlert('破损记录添加成功', 'success');
                    closeModal();
                    
                    // 刷新当前页面的数据
                    console.log('破损记录添加成功，当前页面:', currentPage, '店铺类型:', window.currentShopType);
                    if (currentPage === 'stock') {
                        // 如果在库存页面，刷新库存数据
                        console.log('刷新库存数据');
                        loadStockData(true, false);
                    } else if (currentPage === 'j1' || currentPage === 'j2' || currentPage === 'j3') {
                        // 如果在破损记录页面，刷新破损记录数据
                        console.log('刷新破损记录数据，页面类型:', currentPage);
                        loadAllBreakRecords();
                    } else {
                        console.warn('未知的页面类型:', currentPage);
                    }
                    
                    // 同时刷新总库存页面（如果已加载），确保库存同步
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('添加失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('添加破损记录时发生错误:', error);
                showAlert('添加破损记录失败: ' + error.message, 'error');
            }
        }

        // 添加破损记录（保留原函数用于兼容性）
        async function addBreakRecord(shopType, dishwareId, breakQuantity) {
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_damage_record',
                        dishware_id: dishwareId,
                        shop_type: shopType,
                        break_quantity: parseInt(breakQuantity),
                        recorded_by: 'system'
                    })
                });
                
                if (result.success) {
                    showAlert('破损记录添加成功', 'success');
                    
                    // 刷新当前页面的数据
                    if (currentPage === 'stock') {
                        // 如果在库存页面，刷新库存数据
                        loadStockData(true, false);
                    } else {
                        // 如果在破损记录页面，刷新破损记录数据
                        loadBreakRecords(shopType);
                    }
                } else {
                    showAlert('添加失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('添加破损记录时发生错误:', error);
                showAlert('添加破损记录失败: ' + error.message, 'error');
            }
        }

        // 更新破损数量
        async function updateBreakQuantity(recordId, newQuantity, shopId = null) {
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_damage_record',
                        id: recordId,
                        break_quantity: parseInt(newQuantity)
                    })
                });
                
                if (result.success) {
                    showAlert('破损数量更新成功', 'success');
                    
                    // 刷新当前页面的数据
                    if (currentPage === 'stock') {
                        // 如果在库存页面，刷新库存数据
                        loadStockData(true, false);
                    } else {
                        // 如果在破损记录页面，刷新所有破损记录数据
                        loadAllBreakRecords();
                    }
                    
                    // 同时刷新总库存页面（如果已加载），确保库存同步
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('更新失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('更新破损数量时发生错误:', error);
                showAlert('更新破损数量失败: ' + error.message, 'error');
            }
        }

        // 编辑破损记录 - 进入编辑模式
        function editBreakRecord(recordId, shopId = null) {
            // 找到对应的行
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                showAlert('找不到要编辑的记录', 'error');
                return;
            }
            
            // 检查是否已经在编辑中
            if (row.classList.contains('editing-row')) {
                return;
            }
            
            // 检查stockData是否已加载
            if (!stockData || stockData.length === 0) {
                showAlert('正在加载碗碟数据，请稍后再试', 'warning');
                loadStockData(true, false).then(() => {
                    setTimeout(() => {
                        editBreakRecord(recordId, shopId);
                    }, 500);
                });
                return;
            }
            
            // 标记为编辑中
            row.classList.add('editing-row');
            
            // 获取当前记录数据
            const cells = row.querySelectorAll('td');
            if (cells.length < 6) return;
            
            // 保存原始数据
            const originalCode = cells[1].textContent.trim();
            const quantityEl = cells[2].querySelector('.quantity-input') || cells[2].querySelector('span');
            const originalQuantity = quantityEl && quantityEl.classList.contains('quantity-input')
                ? quantityEl.value
                : (quantityEl ? quantityEl.textContent.trim() : '0');
            row.dataset.originalCode = originalCode;
            row.dataset.originalQuantity = originalQuantity;
            
            // 获取当前记录信息（从 breakRecordsData 中查找）
            const records = breakRecordsData[shopId] || [];
            const record = records.find(r => r.id == recordId);
            
            if (!record) {
                showAlert('找不到记录数据', 'error');
                row.classList.remove('editing-row');
                return;
            }
            
            // 生成编号选项（用于combobox）
            let codeOptions = [];
            if (stockData && stockData.length > 0) {
                stockData.forEach(item => {
                    const code = item.code_number || '';
                    if (code) {
                        codeOptions.push({
                            code: code,
                            id: item.id,
                            price: item.unit_price || 0
                        });
                    }
                });
            }
            
            // 找到当前编号对应的产品ID
            const currentProduct = stockData.find(item => item.code_number === originalCode);
            const currentProductId = currentProduct ? currentProduct.id : '';
            
            // 编辑编号列 - 使用 combobox
            const codeCell = cells[1];
            const codeRowId = `edit-${recordId}-${Date.now()}`;
            codeCell.innerHTML = `
                <div class="combobox-container" id="${codeRowId}-code-combo">
                    <input 
                        type="text" 
                        class="combobox-input break-code-input" 
                        id="${codeRowId}-code"
                        value="${originalCode}"
                        placeholder="输入或选择编号..."
                        autocomplete="off"
                        data-row-id="${codeRowId}"
                        data-field="code"
                        data-product-id="${currentProductId}"
                    />
                    <i class="fas fa-chevron-down combobox-arrow"></i>
                    <div class="combobox-dropdown" id="${codeRowId}-code-dropdown">
                        ${codeOptions.map(opt => `<div class="combobox-option" data-value="${opt.code}" data-id="${opt.id}" data-price="${opt.price}">${opt.code}</div>`).join('')}
                    </div>
                </div>
            `;
            
            // 数量列：使用 contenteditable span（直接编辑，不显示输入框）
            cells[2].innerHTML = `
                <span contenteditable="true" class="editable-quantity" 
                      id="edit-${recordId}-qty"
                      style="display: inline-block; min-width: 40px; padding: 2px 4px; border: 1px solid #ccc; border-radius: 4px; background: #fff; outline: none; text-align: center;"
                      oninput="this.textContent = this.textContent.replace(/[^0-9.]/g, '');">${originalQuantity}</span>
            `;
            
            // 替换操作按钮为保存和取消
            const actionCell = cells[5];
            actionCell.innerHTML = `
                <button class="action-btn save-btn" onclick="saveEditBreakRecord(${recordId}, '${shopId}', '${codeRowId}')" title="保存" style="background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-right: 4px;">
                    <i class="fas fa-check"></i>
                </button>
                <button class="action-btn cancel-btn" onclick="cancelEditBreakRecord(${recordId}, '${shopId}')" title="取消" style="background: #6c757d; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // 绑定 combobox 事件
            setTimeout(() => {
                bindBreakComboboxEvents(codeRowId);
            }, 100);
        }
        
        // 保存编辑的破损记录
        async function saveEditBreakRecord(recordId, shopId, codeRowId) {
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                showAlert('找不到要保存的记录', 'error');
                return;
            }
            
            const cells = row.querySelectorAll('td');
            if (cells.length < 6) return;
            
            // 获取编辑后的值
            const codeInput = document.getElementById(`${codeRowId}-code`);
            const quantitySpan = document.getElementById(`edit-${recordId}-qty`) || cells[2].querySelector('.editable-quantity');
            
            if (!codeInput || !quantitySpan) {
                showAlert('找不到输入元素', 'error');
                return;
            }
            
            const newCode = codeInput.value.trim();
            const productId = codeInput.dataset.productId || codeInput.getAttribute('data-product-id');
            const newQuantity = parseFloat(quantitySpan.textContent.trim()) || 0;
            
            // 验证
            if (!newCode || !productId) {
                showAlert('请输入或选择编号', 'error');
                return;
            }
            
            if (newQuantity < 0) {
                showAlert('请输入有效的破损数量', 'error');
                return;
            }
            
            try {
                // 获取当前记录以获取单价
                const records = breakRecordsData[shopId] || [];
                const record = records.find(r => r.id == recordId);
                
                if (!record) {
                    showAlert('找不到记录数据', 'error');
                    return;
                }
                
                // 如果编号改变了，需要获取新的产品信息
                let unitPrice = record.unit_price || 0;
                if (newCode !== row.dataset.originalCode) {
                    const newProduct = stockData.find(item => item.code_number === newCode);
                    if (newProduct) {
                        unitPrice = newProduct.unit_price || 0;
                    }
                }
                
                const totalPrice = newQuantity * unitPrice;
                
                // 更新破损记录
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_damage_record',
                        id: recordId,
                        dishware_id: productId,
                        break_quantity: newQuantity,
                        unit_price: unitPrice,
                        total_price: totalPrice
                    })
                });
                
                if (result.success) {
                    showAlert('破损记录更新成功', 'success');
                    // 退出编辑模式并只刷新当前餐厅的数据，保留其他正在编辑的行
                    row.classList.remove('editing-row');
                    await refreshSingleRestaurantBreakRecords(shopId, recordId);
                    // 刷新总库存
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('更新失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('保存编辑的破损记录时发生错误:', error);
                showAlert('保存失败: ' + error.message, 'error');
            }
        }
        
        // 取消编辑破损记录
        function cancelEditBreakRecord(recordId, shopId) {
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                return;
            }
            
            // 退出编辑模式
            row.classList.remove('editing-row');
            
            // 重新渲染该行（恢复到原始状态）
            const records = breakRecordsData[shopId] || [];
            const record = records.find(r => r.id == recordId);
            
            if (record) {
                // 找到该行在数组中的索引
                const index = records.findIndex(r => r.id == recordId);
                if (index !== -1) {
                    // 重新渲染该行
                    const tbody = row.parentElement;
                    const newRow = document.createElement('tr');
                    newRow.setAttribute('data-id', record.id);
                    newRow.setAttribute('data-shop', shopId);
                    newRow.innerHTML = `
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${record.code_number || '-'}</td>
                        <td class="text-center"><span>${record.break_quantity}</span></td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${formatCurrency(record.unit_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${formatCurrency(record.total_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="action-btn edit-btn" onclick="editBreakRecord(${record.id}, '${shopId}')" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteBreakRecord(${record.id}, '${shopId}')" title="删除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    row.replaceWith(newRow);
                }
            }
        }

        // 删除破损记录
        async function deleteBreakRecord(recordId, shopId = null) {
            if (!confirm('确定要删除此破损记录吗？此操作不可恢复！')) return;
            
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_damage_record',
                        id: recordId
                    })
                });
                
                if (result.success) {
                    showAlert('破损记录删除成功', 'success');
                    
                    // 刷新当前页面的数据
                    if (currentPage === 'stock') {
                        // 如果在库存页面，刷新库存数据
                        loadStockData(true, false);
                    } else {
                        // 如果在破损记录页面，刷新所有破损记录数据
                        loadAllBreakRecords();
                    }
                    
                    // 同时刷新总库存页面（如果已加载），确保库存同步
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('删除失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('删除破损记录时发生错误:', error);
                showAlert('删除破损记录失败: ' + error.message, 'error');
            }
        }

        // 当前选中的餐厅类型（用于创建新行）
        let currentBreakShopType = null;

        // 打开破损记录行数选择弹窗
        function openBreakRowsModal(shopType) {
            currentBreakShopType = shopType;
            const modal = document.getElementById('break-rows-modal');
            if (modal) {
                modal.classList.add('show');
                document.getElementById('break-rows-count').value = 1;
            }
        }

        // 关闭破损记录行数选择弹窗
        function closeBreakRowsModal() {
            const modal = document.getElementById('break-rows-modal');
            if (modal) {
                modal.classList.remove('show');
            }
        }

        // 点击弹窗外部关闭弹窗
        document.addEventListener('click', function(event) {
            const breakModal = document.getElementById('break-rows-modal');
            if (event.target === breakModal) {
                closeBreakRowsModal();
            }
            const transferModal = document.getElementById('transfer-rows-modal');
            if (event.target === transferModal) {
                closeTransferRowsModal();
            }
        });

        // 创建多行破损记录
        function createMultipleBreakRows() {
            const rowsCount = parseInt(document.getElementById('break-rows-count').value);
            
            // 验证输入
            if (!rowsCount || rowsCount < 1 || rowsCount > 50) {
                showAlert('请输入有效的行数（1-50）', 'error');
                return;
            }
            
            if (!currentBreakShopType) {
                showAlert('餐厅类型未设置', 'error');
                return;
            }
            
            // 关闭弹窗
            closeBreakRowsModal();
            
            // 检查stockData是否已加载
            if (!stockData || stockData.length === 0) {
                showAlert('正在加载碗碟数据，请稍后再试', 'warning');
                loadStockData(true, false).then(() => {
                    setTimeout(() => {
                        createMultipleBreakRows();
                    }, 500);
                });
                return;
            }
            
            // 创建指定数量的行
            for (let i = 0; i < rowsCount; i++) {
                addNewBreakRow(currentBreakShopType);
            }
            
            // 滚动到表格底部
            setTimeout(() => {
                const tbody = document.getElementById(`${currentBreakShopType}-break-tbody`);
                if (tbody) {
                    tbody.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            }, 100);
            
            showAlert(`成功创建 ${rowsCount} 行记录`, 'success');
        }

        // 添加新行到破损记录表格
        function addNewBreakRow(shopType) {
            const tbody = document.getElementById(`${shopType}-break-tbody`);
            if (!tbody) {
                console.error(`找不到表格tbody: ${shopType}-break-tbody`);
                return;
            }
            
            // 如果tbody中只有"暂无破损记录"的行，先清空
            const noDataRow = tbody.querySelector('tr td.no-data');
            if (noDataRow) {
                tbody.innerHTML = '';
            }
            
            const row = document.createElement('tr');
            row.className = 'new-row';
            const rowId = 'new-break-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            
            // 获取当前行数（用于显示序号）
            const currentRowCount = tbody.querySelectorAll('tr:not(.new-row)').length;
            const newRowIndex = currentRowCount + tbody.querySelectorAll('tr.new-row').length + 1;
            
            // 生成产品选择选项（只显示编号）
            let productOptions = '<option value="">请选择产品</option>';
            if (stockData && stockData.length > 0) {
                stockData.forEach(item => {
                    const code = item.code_number || '';
                    // 只显示编号，如果没有编号则显示产品名称作为备选
                    const displayText = code || item.product_name || '';
                    productOptions += `<option value="${item.id}" data-code="${code}" data-price="${item.unit_price || 0}">${displayText}</option>`;
                });
            }
            
            // 生成编号选项（用于combobox）
            let codeOptions = [];
            if (stockData && stockData.length > 0) {
                stockData.forEach(item => {
                    const code = item.code_number || '';
                    if (code) {
                        codeOptions.push({
                            code: code,
                            id: item.id,
                            price: item.unit_price || 0
                        });
                    }
                });
            }
            
            row.innerHTML = `
                <td class="text-center">${newRowIndex}</td>
                <td class="text-center">
                    <div class="combobox-container" id="${rowId}-code-combo">
                        <input 
                            type="text" 
                            class="combobox-input break-code-input" 
                            id="${rowId}-code"
                            placeholder="输入或选择编号..."
                            autocomplete="off"
                            data-row-id="${rowId}"
                            data-field="code"
                        />
                        <i class="fas fa-chevron-down combobox-arrow"></i>
                        <div class="combobox-dropdown" id="${rowId}-code-dropdown">
                            ${codeOptions.map(opt => `<div class="combobox-option" data-value="${opt.code}" data-id="${opt.id}" data-price="${opt.price}">${opt.code}</div>`).join('')}
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <input type="text" 
                           class="break-quantity-input" 
                           id="${rowId}-quantity" 
                           placeholder="0" 
                           value="" 
                           onblur="calculateBreakRowTotal('${rowId}')" 
                           style="width: 100%; padding: 4px 8px; border: none; background: transparent; text-align: center; outline: none;">
                </td>
                <td class="text-center">
                    <div class="currency-display">
                        <span class="currency-symbol">RM</span>
                        <input type="text" 
                               class="break-price-input" 
                               id="${rowId}-price" 
                               value="" 
                               onblur="calculateBreakRowTotal('${rowId}')" 
                               style="width: 80px; border: none; background: transparent; text-align: center; outline: none;">
                    </div>
                </td>
                <td class="text-center">
                    <div class="currency-display">
                        <span class="currency-symbol">RM</span>
                        <span class="currency-amount" id="${rowId}-total">0.00</span>
                    </div>
                </td>
                <td class="text-center">
                    <button class="action-btn save-btn" onclick="saveNewBreakRow('${rowId}', '${shopType}')" title="保存" style="background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-right: 4px;">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="action-btn cancel-btn" onclick="cancelNewBreakRow('${rowId}', '${shopType}')" title="取消" style="background: #6c757d; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            
            // 绑定combobox事件
            setTimeout(() => {
                bindBreakComboboxEvents(rowId);
            }, 100);
            
            tbody.appendChild(row);
        }

        // 绑定破损记录combobox事件
        function bindBreakComboboxEvents(rowId) {
            const codeInput = document.getElementById(`${rowId}-code`);
            const codeDropdown = document.getElementById(`${rowId}-code-dropdown`);
            const container = document.getElementById(`${rowId}-code-combo`);
            
            if (!codeInput || !codeDropdown || !container) return;
            
            // 将下拉列表移到body下，避免被表格元素覆盖
            if (codeDropdown.parentElement !== document.body) {
                document.body.appendChild(codeDropdown);
            }
            
            // 显示下拉（使用 fixed 定位）
            const showDropdown = () => {
                const rect = codeInput.getBoundingClientRect();
                // 确保下拉列表在body下
                if (codeDropdown.parentElement !== document.body) {
                    document.body.appendChild(codeDropdown);
                }
                codeDropdown.style.position = 'fixed';
                codeDropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                codeDropdown.style.left = rect.left + 'px';
                codeDropdown.style.zIndex = '2147483647'; // 使用最大z-index值
                codeDropdown.style.backgroundColor = 'white'; // 确保背景色
                codeDropdown.style.width = codeDropdown.style.width || 'clamp(60px, 5.21vw, 100px)';
                // 使用 CSS clamp，不需要设置 JavaScript 宽度
                codeDropdown.classList.add('show');
                filterBreakComboboxOptions(codeInput, codeDropdown);
            };
            
            codeInput.addEventListener('focus', showDropdown);
            
            // 输入过滤
            codeInput.addEventListener('input', () => {
                filterBreakComboboxOptions(codeInput, codeDropdown);
                if (!codeDropdown.classList.contains('show')) {
                    showDropdown();
                }
            });
            
            // 选择选项
            codeDropdown.querySelectorAll('.combobox-option').forEach(option => {
                option.addEventListener('click', () => {
                    const code = option.dataset.value;
                    const productId = option.dataset.id;
                    const price = parseFloat(option.dataset.price || 0);
                    
                    codeInput.value = code;
                    codeInput.dataset.productId = productId;
                    codeInput.setAttribute('data-product-id', productId);
                    codeDropdown.classList.remove('show');
                    // 选择后将下拉列表移回原位置
                    if (codeDropdown.parentElement === document.body && codeDropdown._originalParent) {
                        codeDropdown._originalParent.appendChild(codeDropdown);
                    }
                    
                    // 如果是编辑模式，需要找到对应的价格输入框
                    const row = codeInput.closest('tr');
                    if (row && row.classList.contains('editing-row')) {
                        // 检查是破损记录还是转卖记录的编辑模式
                        const isTransferEdit = row.querySelector('.transfer-to-select-edit');
                        
                        if (isTransferEdit) {
                            // 转卖记录编辑模式：更新单价显示（只读，自动从产品信息获取）
                            const priceSpan = document.getElementById(`${codeRowId}-price`);
                            const priceSymbol = document.querySelector(`#${codeRowId}-price`).parentElement.querySelector('.currency-symbol');
                            if (priceSpan) {
                                priceSpan.textContent = price.toFixed(2);
                                // 检查是否是转卖（out）记录，如果是则显示负号
                                const row = codeInput.closest('tr');
                                if (row && row.dataset.type === 'out') {
                                    if (priceSymbol) {
                                        priceSymbol.textContent = '-RM';
                                        priceSymbol.style.color = '#dc3545';
                                        priceSpan.style.color = '#dc3545';
                                    }
                                } else {
                                    if (priceSymbol) {
                                        priceSymbol.textContent = 'RM';
                                        priceSymbol.style.color = '#000000';
                                        priceSpan.style.color = '#000000';
                                    }
                                }
                            }
                            calculateEditTransferTotal(codeRowId);
                        } else {
                            // 破损记录编辑模式：更新单价显示（只读）
                            const cells = row.querySelectorAll('td');
                            if (cells.length >= 4) {
                                const priceCell = cells[3];
                                priceCell.innerHTML = `
                                    <div class="currency-display">
                                        <span class="currency-symbol">RM</span>
                                        <span class="currency-amount">${price.toFixed(2)}</span>
                                    </div>
                                `;
                                // 重新计算总价
                                const quantityEl = cells[2].querySelector('.quantity-input') || cells[2].querySelector('.editable-quantity');
                                if (quantityEl) {
                                    const quantity = quantityEl.classList.contains('quantity-input') 
                                        ? parseFloat(quantityEl.value) || 0
                                        : parseFloat(quantityEl.textContent.trim()) || 0;
                                    const totalPrice = quantity * price;
                                    const totalCell = cells[4];
                                    totalCell.innerHTML = `
                                        <div class="currency-display">
                                            <span class="currency-symbol">RM</span>
                                            <span class="currency-amount">${totalPrice.toFixed(2)}</span>
                                        </div>
                                    `;
                                }
                            }
                        }
                    } else {
                        // 检查是破损记录还是转卖记录
                        const priceEl = document.getElementById(`${rowId}-price`);
                        if (priceEl) {
                            // 检查是转卖记录（span）还是破损记录（input）
                            const isTransferRow = priceEl.tagName === 'SPAN' || priceEl.classList.contains('currency-amount');
                            
                            if (isTransferRow) {
                                // 转卖记录新行模式：更新单价显示（span）
                                priceEl.textContent = price.toFixed(2);
                                // 计算总价
                                if (typeof calculateTransferRowTotal === 'function') {
                                    calculateTransferRowTotal(rowId);
                                }
                            } else {
                                // 破损记录新行模式：更新单价输入框
                                priceEl.value = price.toFixed(2);
                                // 计算总价
                                if (row && row.classList.contains('new-row')) {
                                    calculateBreakRowTotal(rowId);
                                } else {
                                    calculateBreakRowTotal(rowId);
                                }
                            }
                        } else {
                            // 如果没有找到价格元素，尝试计算总价
                            calculateBreakRowTotal(rowId);
                        }
                    }
                });
            });
            
            // 点击外部关闭
            const closeHandler = (e) => {
                if (!container.contains(e.target) && !codeDropdown.contains(e.target)) {
                    codeDropdown.classList.remove('show');
                    // 关闭时将下拉列表移回原位置
                    if (codeDropdown.parentElement === document.body && container) {
                        container.appendChild(codeDropdown);
                    }
                }
            };
            setTimeout(() => {
                document.addEventListener('click', closeHandler);
                codeInput._closeHandler = closeHandler;
            }, 100);
            
            // 窗口滚动时更新位置
            const updatePosition = () => {
                if (codeDropdown.classList.contains('show')) {
                    const rect = codeInput.getBoundingClientRect();
                    // 确保下拉列表在body下
                    if (codeDropdown.parentElement !== document.body) {
                        document.body.appendChild(codeDropdown);
                    }
                    codeDropdown.style.position = 'fixed';
                    codeDropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                    codeDropdown.style.left = rect.left + 'px';
                    codeDropdown.style.zIndex = '2147483647'; // 使用最大z-index值
                    codeDropdown.style.backgroundColor = 'white'; // 确保背景色
                    // 宽度由 CSS clamp 控制，不需要更新
                }
            };
            window.addEventListener('scroll', updatePosition, true);
            window.addEventListener('resize', updatePosition);
            
            // 保存原始父元素引用，以便关闭时移回
            codeDropdown._originalParent = container;
        }

        // 过滤combobox选项
        function filterBreakComboboxOptions(input, dropdown) {
            if (!dropdown) return;
            
            const searchTerm = input.value.toLowerCase();
            const options = dropdown.querySelectorAll('.combobox-option');
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        }

        // 计算破损记录行总价
        function calculateBreakRowTotal(rowId) {
            const quantityInput = document.getElementById(`${rowId}-quantity`);
            const priceInput = document.getElementById(`${rowId}-price`);
            const totalSpan = document.getElementById(`${rowId}-total`);
            
            if (!quantityInput || !priceInput || !totalSpan) return;
            
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            totalSpan.textContent = total.toFixed(2);
        }

        // 保存新破损记录行
        async function saveNewBreakRow(rowId, shopType) {
            const codeInput = document.getElementById(`${rowId}-code`);
            const quantityInput = document.getElementById(`${rowId}-quantity`);
            const priceInput = document.getElementById(`${rowId}-price`);
            
            // 验证元素是否存在
            if (!codeInput || !quantityInput || !priceInput) {
                showAlert('找不到输入元素，请刷新页面后重试', 'error');
                return;
            }
            
            const productId = codeInput.dataset.productId;
            const code = codeInput.value.trim();
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            
            // 验证
            if (!code || !productId) {
                showAlert('请输入或选择编号', 'error');
                return;
            }
            
            if (quantity <= 0) {
                showAlert('请输入有效的破损数量', 'error');
                return;
            }
            
            try {
                const today = new Date().toISOString().split('T')[0];
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_damage_record',
                        dishware_id: productId,
                        shop_type: shopType,
                        break_quantity: quantity,
                        unit_price: price,
                        total_price: quantity * price,
                        break_date: today,
                        recorded_by: 'system'
                    })
                });
                
                if (result.success) {
                    showAlert('破损记录添加成功', 'success');
                    
                    // 移除当前保存的行
                    const codeInput = document.getElementById(`${rowId}-code`);
                    if (codeInput) {
                        const row = codeInput.closest('tr');
                        if (row) {
                            // 移除事件监听器（如果有）
                            const closeHandler = codeInput._closeHandler;
                            if (closeHandler) {
                                document.removeEventListener('click', closeHandler);
                            }
                            // 移除下拉列表（如果在body下）
                            const codeDropdown = document.getElementById(`${rowId}-code-dropdown`);
                            if (codeDropdown && codeDropdown.parentElement === document.body) {
                                document.body.removeChild(codeDropdown);
                            }
                            row.remove();
                        }
                    }
                    
                    // 只刷新对应餐厅的数据，保留其他新行
                    // 添加延迟确保后端数据已提交
                    setTimeout(async () => {
                        await refreshSingleRestaurantBreakRecords(shopType);
                        
                        // 验证记录是否已加载
                        const records = breakRecordsData[shopType] || [];
                        const savedRecordId = result.data?.id;
                        if (savedRecordId && !records.find(r => r.id == savedRecordId)) {
                            console.warn('保存的记录未在刷新后找到，尝试重新加载所有记录');
                            // 如果刷新后找不到新记录，重新加载所有记录
                            loadAllBreakRecords();
                        }
                    }, 300);
                    
                    // 刷新总库存
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('添加失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('保存破损记录时发生错误:', error);
                showAlert('保存破损记录失败: ' + error.message, 'error');
            }
        }

        // 取消新破损记录行
        function cancelNewBreakRow(rowId, shopType) {
            // 通过 code 输入框找到行
            const codeInput = document.getElementById(`${rowId}-code`);
            if (!codeInput) {
                console.error('找不到code输入框:', rowId);
                return;
            }
            
            const row = codeInput.closest('tr');
            if (row) {
                // 移除事件监听器（如果有）
                const closeHandler = codeInput._closeHandler;
                if (closeHandler) {
                    document.removeEventListener('click', closeHandler);
                }
                
                row.remove();
            }
            
            // 如果表格为空，显示"暂无破损记录"
            const tbody = document.getElementById(`${shopType}-break-tbody`);
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="no-data" style="padding: clamp(20px, 2.76vw, 53px); text-align: center; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: clamp(42px, 2.5vw, 48px); opacity: 0.5; margin-bottom: clamp(8px, 0.83vw, 16px);"></i>
                            <div>暂无破损记录</div>
                        </td>
                    </tr>
                `;
            }
        }

        // 设置套装行hover效果
        function setupSetRowHoverEffect() {
            // 清除所有套装行的hover效果
            function clearAllSetHover() {
                document.querySelectorAll('tr.set-hover-active').forEach(row => {
                    row.classList.remove('set-hover-active');
                });
            }
            
            // 为指定套装的所有行添加hover效果
            function setHoverForSet(setId) {
                if (!setId) return;
                // 先清除所有hover效果
                clearAllSetHover();
                // 为这个套装的所有行添加hover效果
                const allSetRows = document.querySelectorAll(`tr[data-type="set"][data-id="${setId}"]`);
                allSetRows.forEach(setRow => {
                    setRow.classList.add('set-hover-active');
                });
            }
            
            // 使用事件委托，监听表格容器
            const tableContainer = document.querySelector('.table-scroll-container') || document.querySelector('.table-container');
            if (!tableContainer) return;
            
            // 使用mouseenter和mouseleave事件（不会冒泡，更适合）
            // 为每个套装行单独绑定事件
            function attachSetHoverEvents() {
                // 清除旧的监听器（通过重新绑定）
                const allSetRows = document.querySelectorAll('tr[data-type="set"]');
                allSetRows.forEach(row => {
                    // 移除可能存在的旧事件监听器
                    row.removeEventListener('mouseenter', row._setHoverEnter);
                    row.removeEventListener('mouseleave', row._setHoverLeave);
                    
                    // 创建新的事件处理函数
                    row._setHoverEnter = function() {
                        const setId = this.getAttribute('data-id');
                        setHoverForSet(setId);
                    };
                    
                    row._setHoverLeave = function(e) {
                        // 检查鼠标是否进入了同一套装的另一行
                        const relatedTarget = e.relatedTarget;
                        if (relatedTarget) {
                            const targetRow = relatedTarget.closest('tr[data-type="set"]');
                            if (targetRow && targetRow.getAttribute('data-id') === this.getAttribute('data-id')) {
                                // 鼠标进入了同一套装的另一行，不清除
                                return;
                            }
                        }
                        // 真的离开了套装，清除hover效果
                        clearAllSetHover();
                    };
                    
                    // 绑定事件
                    row.addEventListener('mouseenter', row._setHoverEnter);
                    row.addEventListener('mouseleave', row._setHoverLeave);
                });
            }
            
            // 初始绑定
            attachSetHoverEvents();
            
            // 当表格重新渲染时，重新绑定事件（使用防抖）
            let rebindTimeout = null;
            const observer = new MutationObserver(function(mutations) {
                // 检查是否是子元素变化（表格重新渲染）
                let shouldRebind = false;
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        shouldRebind = true;
                    }
                });
                
                if (shouldRebind) {
                    // 使用防抖，避免频繁重新绑定
                    if (rebindTimeout) {
                        clearTimeout(rebindTimeout);
                    }
                    rebindTimeout = setTimeout(function() {
                        attachSetHoverEvents();
                    }, 100); // 100ms延迟，避免频繁重新绑定
                }
            });
            
            const tbody = document.querySelector('#stock-tbody');
            if (tbody) {
                observer.observe(tbody, {
                    childList: true,
                    subtree: false // 只观察直接子元素的变化
                });
            }
        }


        // 设置事件监听器
        function setupEventListeners() {
            // 套装行hover效果处理
            setupSetRowHoverEffect();
            
            // 添加表单照片上传
            const addPhotoInput = document.getElementById('add-photo');
            const addPhotoUploadArea = document.querySelector('#addModal .photo-upload-area');
            
            if (addPhotoInput && addPhotoUploadArea) {
                addPhotoInput.addEventListener('change', handleAddPhotoSelect);
                
                // 拖拽上传
                addPhotoUploadArea.addEventListener('dragover', handleDragOver);
                addPhotoUploadArea.addEventListener('dragleave', handleDragLeave);
                addPhotoUploadArea.addEventListener('drop', handleAddDrop);
            }
            
            // 编辑表单照片上传
            const editPhotoInput = document.getElementById('edit-photo');
            const editPhotoUploadArea = document.querySelector('#editModal .photo-upload-area');
            
            if (editPhotoInput && editPhotoUploadArea) {
                editPhotoInput.addEventListener('change', handleEditPhotoSelect);
                
                // 拖拽上传
                editPhotoUploadArea.addEventListener('dragover', handleDragOver);
                editPhotoUploadArea.addEventListener('dragleave', handleDragLeave);
                editPhotoUploadArea.addEventListener('drop', handleEditDrop);
            }
            
            // 表单提交
            const addForm = document.getElementById('add-form');
            if (addForm) {
                addForm.addEventListener('submit', handleAddFormSubmit);
            }
            
            const editForm = document.getElementById('edit-form');
            if (editForm) {
                editForm.addEventListener('submit', handleEditFormSubmit);
            }
            
            const damageForm = document.getElementById('damage-form');
            if (damageForm) {
                damageForm.addEventListener('submit', handleDamageFormSubmit);
            }
        }

        // 将分类下拉菜单改为餐厅选择（破损记录页面）
        function updateCategoryFilterToRestaurantForBreak() {
            const categoryFilterDiv = document.querySelector('.category-filter');
            const categorySelect = document.getElementById('category-filter');
            
            if (!categoryFilterDiv || !categorySelect) return;
            
            // 保存原始分类选项（用于恢复）
            if (!categorySelect.dataset.originalHTML) {
                categorySelect.dataset.originalHTML = categorySelect.innerHTML;
            }
            
            // 更新标签
            const label = categoryFilterDiv.querySelector('span');
            if (label) {
                label.textContent = '餐厅';
            }
            
            // 更新下拉菜单为餐厅列表
            let restaurantOptions = '<option value="">全部餐厅</option>';
            const jRestaurants = window.jRestaurantsForBreak || restaurants.filter(r => {
                const name = r.name.toLowerCase();
                return name.startsWith('j') && 
                       name !== '中央' && 
                       name !== '文化楼' &&
                       name !== 'wenhua' && 
                       name !== 'central';
            }).sort((a, b) => {
                const nameA = a.name.toLowerCase();
                const nameB = b.name.toLowerCase();
                const numA = parseInt(nameA.replace('j', '')) || 0;
                const numB = parseInt(nameB.replace('j', '')) || 0;
                return numA - numB;
            });
            
            jRestaurants.forEach(restaurant => {
                restaurantOptions += `<option value="${restaurant.name.toLowerCase()}">${restaurant.name}</option>`;
            });
            
            categorySelect.innerHTML = restaurantOptions;
            categorySelect.value = breakRestaurantFilter || '';
            
            // 移除旧的事件监听器，添加新的事件监听器
            categorySelect.removeEventListener('change', handleCategoryFilterChange);
            categorySelect.removeEventListener('change', handleTransferRestaurantFilterChange);
            categorySelect.removeEventListener('change', handleBreakRestaurantFilterChange);
            categorySelect.addEventListener('change', handleBreakRestaurantFilterChange);
            
            // 应用当前的餐厅过滤
            setTimeout(() => {
                filterBreakRecordsByRestaurant();
            }, 100);
        }
        
        // 将分类下拉菜单改为餐厅选择（转卖页面）
        function updateCategoryFilterToRestaurant() {
            const categoryFilterDiv = document.querySelector('.category-filter');
            const categorySelect = document.getElementById('category-filter');
            
            if (!categoryFilterDiv || !categorySelect) return;
            
            // 保存原始分类选项（用于恢复）
            if (!categorySelect.dataset.originalHTML) {
                categorySelect.dataset.originalHTML = categorySelect.innerHTML;
            }
            
            // 更新标签
            const label = categoryFilterDiv.querySelector('span');
            if (label) {
                label.textContent = '餐厅';
            }
            
            // 更新下拉菜单为餐厅列表
            let restaurantOptions = '<option value="">全部餐厅</option>';
            const jRestaurants = window.jRestaurantsForTransfer || restaurants.filter(r => {
                const name = r.name.toLowerCase();
                return name.startsWith('j') && 
                       name !== '中央' && 
                       name !== '文化楼' &&
                       name !== 'wenhua' && 
                       name !== 'central';
            }).sort((a, b) => {
                const nameA = a.name.toLowerCase();
                const nameB = b.name.toLowerCase();
                const numA = parseInt(nameA.replace('j', '')) || 0;
                const numB = parseInt(nameB.replace('j', '')) || 0;
                return numA - numB;
            });
            
            jRestaurants.forEach(restaurant => {
                restaurantOptions += `<option value="${restaurant.name.toLowerCase()}">${restaurant.name}</option>`;
            });
            
            categorySelect.innerHTML = restaurantOptions;
            categorySelect.value = transferRestaurantFilter || '';
            
            // 移除旧的事件监听器，添加新的事件监听器
            categorySelect.removeEventListener('change', handleCategoryFilterChange);
            categorySelect.removeEventListener('change', handleTransferRestaurantFilterChange);
            categorySelect.addEventListener('change', handleTransferRestaurantFilterChange);
            
            // 应用当前的餐厅过滤
            setTimeout(() => {
                filterTransferRecordsByRestaurant();
            }, 100);
        }
        
        // 恢复分类下拉菜单（其他页面）
        function restoreCategoryFilter() {
            const categoryFilterDiv = document.querySelector('.category-filter');
            const categorySelect = document.getElementById('category-filter');
            
            if (!categoryFilterDiv || !categorySelect) return;
            
            // 恢复标签
            const label = categoryFilterDiv.querySelector('span');
            if (label) {
                label.textContent = '分类';
            }
            
            // 恢复原始分类选项
            if (categorySelect.dataset.originalHTML) {
                categorySelect.innerHTML = categorySelect.dataset.originalHTML;
            }
            
            // 移除转卖和破损页面的事件监听器，恢复分类过滤事件
            categorySelect.removeEventListener('change', handleTransferRestaurantFilterChange);
            categorySelect.removeEventListener('change', handleBreakRestaurantFilterChange);
            categorySelect.addEventListener('change', handleCategoryFilterChange);
        }
        
        // 处理转卖页面餐厅过滤变化
        function handleTransferRestaurantFilterChange() {
            const categorySelect = document.getElementById('category-filter');
            if (!categorySelect) return;
            
            transferRestaurantFilter = categorySelect.value || '';
            filterTransferRecordsByRestaurant();
        }
        
        // 处理破损记录页面餐厅过滤变化
        function handleBreakRestaurantFilterChange() {
            const categorySelect = document.getElementById('category-filter');
            if (!categorySelect) return;
            
            breakRestaurantFilter = categorySelect.value || '';
            filterBreakRecordsByRestaurant();
        }
        
        // 处理分类过滤变化（库存页面）
        function handleCategoryFilterChange() {
            searchData();
        }
        
        // 根据选择的餐厅过滤破损记录
        function filterBreakRecordsByRestaurant() {
            // 获取所有破损记录容器
            const containers = [
                document.getElementById('break-records-container'),
                document.getElementById('break-records-container-j2'),
                document.getElementById('break-records-container-j3')
            ].filter(c => c !== null);
            
            if (containers.length === 0) return;
            
            containers.forEach(container => {
                const scrollContainer = container?.closest('.table-scroll-container');
                
                if (!breakRestaurantFilter) {
                    // 显示所有餐厅，恢复原始布局
                    container.classList.remove('single-restaurant');
                    if (scrollContainer) {
                        scrollContainer.style.overflowX = 'auto';
                        scrollContainer.style.overflowY = 'visible';
                    }
                    
                    const sections = container.querySelectorAll('.break-record-section');
                    sections.forEach(section => {
                        section.style.display = '';
                        section.style.margin = '';
                    });
                } else {
                    // 只显示选中的餐厅，居中显示
                    container.classList.add('single-restaurant');
                    if (scrollContainer) {
                        scrollContainer.style.overflowX = 'hidden';
                        scrollContainer.style.overflowY = 'visible';
                    }
                    
                    const sections = container.querySelectorAll('.break-record-section');
                    sections.forEach(section => {
                        const header = section.querySelector('.break-record-header');
                        if (header) {
                            const titleSpan = header.querySelector('span');
                            if (titleSpan) {
                                // 获取餐厅名称（移除"破损"文字）
                                const restaurantName = titleSpan.textContent.replace('破损', '').trim().toLowerCase();
                                if (restaurantName === breakRestaurantFilter) {
                                    section.style.display = '';
                                } else {
                                    section.style.display = 'none';
                                }
                            }
                        }
                    });
                }
            });
        }
        
        // 根据选择的餐厅过滤转卖记录
        function filterTransferRecordsByRestaurant() {
            const container = document.getElementById('transfer-records-container');
            const scrollContainer = container?.closest('.table-scroll-container');
            if (!container) return;
            
            if (!transferRestaurantFilter) {
                // 显示所有餐厅，恢复原始布局
                container.classList.remove('single-restaurant');
                if (scrollContainer) {
                    scrollContainer.style.overflowX = 'auto';
                    scrollContainer.style.overflowY = 'visible';
                }
                
                const sections = container.querySelectorAll('.break-record-section');
                sections.forEach(section => {
                    section.style.display = '';
                    section.style.margin = '';
                });
            } else {
                // 只显示选中的餐厅，居中显示
                container.classList.add('single-restaurant');
                if (scrollContainer) {
                    scrollContainer.style.overflowX = 'hidden';
                    scrollContainer.style.overflowY = 'visible';
                }
                
                const sections = container.querySelectorAll('.break-record-section');
                sections.forEach(section => {
                    const header = section.querySelector('.break-record-header');
                    if (header) {
                        const titleSpan = header.querySelector('span');
                        if (titleSpan) {
                            // 获取餐厅名称（移除"转卖"文字）
                            const restaurantName = titleSpan.textContent.replace('转卖', '').trim().toLowerCase();
                            if (restaurantName === transferRestaurantFilter) {
                                section.style.display = '';
                            } else {
                                section.style.display = 'none';
                            }
                        }
                    }
                });
            }
        }

        // 设置实时搜索
        function setupRealTimeSearch() {
            const searchInput = document.getElementById('unified-filter');
            const categorySelect = document.getElementById('category-filter');
            
            // 防抖处理，避免频繁搜索
            let debounceTimer;
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        if (currentPage === 'transfer') {
                            // 转卖页面不执行搜索
                            return;
                        }
                        searchData();
                    }, 300); // 300ms延迟
                });
            }
            
            if (categorySelect) {
                // 移除所有可能的事件监听器
                categorySelect.removeEventListener('change', handleCategoryFilterChange);
                categorySelect.removeEventListener('change', handleTransferRestaurantFilterChange);
                categorySelect.removeEventListener('change', handleBreakRestaurantFilterChange);
                
                // 根据当前页面添加相应的事件监听器
                if (currentPage === 'transfer') {
                    categorySelect.addEventListener('change', handleTransferRestaurantFilterChange);
                } else if (currentPage === 'j1' || currentPage === 'j2' || currentPage === 'j3') {
                    categorySelect.addEventListener('change', handleBreakRestaurantFilterChange);
                } else {
                    categorySelect.addEventListener('change', handleCategoryFilterChange);
                }
            }
        }



        // API 调用函数
        async function apiCall(endpoint, options = {}) {
            try {
                // 合并headers，确保Content-Type正确设置
                const headers = {
                    'Content-Type': 'application/json',
                    ...options.headers
                };
                
                // 如果是FormData，不设置Content-Type（让浏览器自动设置）
                if (options.body instanceof FormData) {
                    delete headers['Content-Type'];
                }
                
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    ...options,
                    headers: headers
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP错误 ${response.status}: ${errorText || response.statusText}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                // 如果是网络错误，提供更友好的错误信息
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    throw new Error('无法连接到服务器，请检查网络连接');
                }
                throw error;
            }
        }

        // 加载库存数据
        async function loadStockData(forceReload = false, showLoading = true) {
            if (isLoading && !forceReload) return;
            
            isLoading = true;
            if (showLoading) {
            setLoadingState(true);
            }
            
                try {
                    // 同时加载库存数据和套装数据
                    const [stockResult, setsResult] = await Promise.all([
                        apiCall('?action=stock'),
                        apiCall('?action=set_stock')
                    ]);
                    
                    let combinedData = [];
                    
                    // 处理单个碗碟库存数据
                    if (stockResult.success) {
                        const individualItems = stockResult.data.items || [];
                        // 更新餐厅店面列表（如果API返回了）
                        if (stockResult.data.restaurants && stockResult.data.restaurants.length > 0) {
                            restaurants = stockResult.data.restaurants;
                        }
                        // 过滤掉已经在套装中的碗碟，避免重复显示
                        const filteredIndividualItems = individualItems.filter(item => !item.is_in_set);
                        filteredIndividualItems.forEach(item => {
                            item.item_type = 'individual';
                        });
                        combinedData = [...filteredIndividualItems];
                    }
                    
                    // 处理套装库存数据
                    if (setsResult.success) {
                        const setItems = setsResult.data.items || [];
                        // 更新餐厅店面列表（如果API返回了）
                        if (setsResult.data.restaurants && setsResult.data.restaurants.length > 0) {
                            restaurants = setsResult.data.restaurants;
                        }
                        console.log('加载套装数据 - 套装数量:', setItems.length);
                        
                        // 为每个套装加载详细信息
                        for (const setItem of setItems) {
                            console.log('处理套装:', {
                                id: setItem.id,
                                set_name: setItem.set_name,
                                set_code: setItem.set_code,
                                set_price: setItem.set_price
                            });
                            try {
                                const detailResult = await apiCall(`?action=set_detail&id=${setItem.id}`);
                                if (detailResult.success) {
                                    const setDetail = detailResult.data;
                                    setItem.item_type = 'set';
                                    setItem.product_name = setDetail.set_name;
                                    setItem.code_number = setDetail.set_code;
                                    setItem.category = 'SET';
                                    setItem.size = setDetail.set_size;
                                    setItem.unit_price = setDetail.set_price;
                                    setItem.items = setDetail.items; // 包含套装中的碗碟详情
                                    console.log(`套装${setItem.id}详情加载成功:`, {
                                        product_name: setItem.product_name,
                                        code_number: setItem.code_number,
                                        items_count: setItem.items ? setItem.items.length : 0
                                    });
                                    
                                    // 为套装中的每个货品加载库存信息
                                    if (setDetail.items && setDetail.items.length > 0) {
                                        for (const item of setDetail.items) {
                                            // 从单个碗碟库存数据中查找对应的库存信息
                                            const stockItem = stockResult.success ? 
                                                stockResult.data.items.find(si => si.id === item.id) : null;
                                            
                                            if (stockItem) {
                                                // 复制库存数量信息
                                                item.wenhua_quantity = stockItem.wenhua_quantity || 0;
                                                item.central_quantity = stockItem.central_quantity || 0;
                                                item.j1_quantity = stockItem.j1_quantity || 0;
                                                item.j2_quantity = stockItem.j2_quantity || 0;
                                                item.j3_quantity = stockItem.j3_quantity || 0;
                                                item.total_quantity = stockItem.total_quantity || 0;
                                                
                                                // 使用单个碗碟的最新信息（包括尺寸）
                                                item.size = stockItem.size || item.size || '';
                                                item.product_name = stockItem.product_name || item.product_name || '';
                                                item.code_number = stockItem.code_number || item.code_number || '';
                                                item.category = stockItem.category || item.category || '';
                                            } else {
                                                // 如果没有找到库存信息，设置为0
                                                item.wenhua_quantity = 0;
                                                item.central_quantity = 0;
                                                item.j1_quantity = 0;
                                                item.j2_quantity = 0;
                                                item.j3_quantity = 0;
                                                item.total_quantity = 0;
                                            }
                                            
                                            // 套装中的碗碟使用套装的价格
                                            item.unit_price = setDetail.set_price;
                                        }
                                    }
                                }
                            } catch (error) {
                                console.warn(`加载套装${setItem.id}详情失败:`, error);
                                // 即使详情加载失败，也保留基本信息
                                setItem.item_type = 'set';
                                setItem.product_name = setItem.set_name || '';
                                setItem.code_number = setItem.set_code || '';
                                setItem.category = 'SET';
                                setItem.size = setItem.set_size || '';
                                setItem.unit_price = setItem.set_price || 0;
                                setItem.items = []; // 设置为空数组，避免undefined
                                console.log(`套装${setItem.id}基本信息保留:`, {
                                    product_name: setItem.product_name,
                                    code_number: setItem.code_number
                                });
                            }
                        }
                        
                        console.log('所有套装处理完成，添加到combinedData - 套装数量:', setItems.length);
                        combinedData = [...combinedData, ...setItems];
                        console.log('combinedData总数:', combinedData.length, '其中套装数量:', combinedData.filter(item => item.item_type === 'set').length);
                    }
                    
                    stockData = combinedData;
                    
                    // 按编号进行字母数字排序
                    stockData = sortByCodeNumber(stockData);
                    filteredData = [...stockData];
                    
                    // 平滑更新表格
                    await smoothUpdateTable();
                    // 初始化拖拽排序功能
                    initDragAndDrop();
                    updateStats();
                    
                    // 确保搜索和过滤状态正确更新
                    const searchTerm = document.getElementById('unified-filter')?.value || '';
                    const categoryFilter = document.getElementById('category-filter')?.value || '';
                    if (searchTerm || categoryFilter) {
                        searchData();
                    }
                    
                    if (stockData.length === 0) {
                        showAlert('当前没有库存数据', 'info');
                    }
                
            } catch (error) {
                stockData = [];
                filteredData = [];
                console.error('加载数据时发生错误:', error);
                showAlert('加载数据失败: ' + error.message, 'error');
                renderStockTable();
            } finally {
                isLoading = false;
                if (showLoading) {
                setLoadingState(false);
                }
            }
        }

        // 平滑更新表格
        async function smoothUpdateTable() {
            const categoryFilter = document.getElementById('category-filter')?.value || '';
            const searchTerm = document.getElementById('unified-filter')?.value || '';
            
            // 如果选择"全部分类"且没有搜索关键词，直接调用按分类渲染函数
            if (!categoryFilter && !searchTerm) {
                renderStockTableByCategory();
                return;
            }
            
            const tbody = document.getElementById('stock-tbody');
            if (!tbody) return;
            
            // 添加淡出效果
            tbody.style.opacity = '0.7';
            tbody.style.transition = 'opacity 0.2s ease';
            
            // 短暂延迟后更新内容
            await new Promise(resolve => setTimeout(resolve, 100));
            
            renderStockTable();
            
            // 恢复透明度
            tbody.style.opacity = '1';
        }

        // 搜索数据
        function searchData() {
            const searchTerm = document.getElementById('unified-filter').value.toLowerCase();
            const categoryFilter = document.getElementById('category-filter').value;
            
            // 如果没有搜索关键词和分类过滤，使用全部数据
            if (!searchTerm && !categoryFilter) {
                filteredData = sortByCodeNumber(stockData);
                // 直接调用按分类渲染函数
                renderStockTableByCategory();
                updateStats();
                return;
            }
            
            filteredData = stockData.filter(item => {
                const searchText = [
                    item.product_name || '',
                    item.code_number || '',
                    item.category || '',
                    item.size || ''
                ].join(' ').toLowerCase();
                
                const matchesSearch = searchText.includes(searchTerm);
                
                // 对于套装，需要检查套装中的items是否有匹配的分类
                let matchesCategory = true;
                if (categoryFilter) {
                    if (item.item_type === 'set') {
                        // 检查套装中的items是否有匹配的分类
                        if (item.items && item.items.length > 0) {
                            matchesCategory = item.items.some(setItem => 
                                setItem.category === categoryFilter
                            );
                        } else {
                            // 如果套装没有items，使用套装自己的category
                            matchesCategory = item.category === categoryFilter;
                        }
                    } else {
                        // 单个碗碟直接比较category
                        matchesCategory = item.category === categoryFilter;
                    }
                }
                
                return matchesSearch && matchesCategory;
            });
            
            // 保持按编号排序
            filteredData = sortByCodeNumber(filteredData);
            
            // 平滑更新表格
            smoothUpdateTable();
            updateStats();
        }

        // 重置搜索过滤器
        function resetFilters() {
            document.getElementById('unified-filter').value = '';
            document.getElementById('category-filter').value = '';
            
            // 保持按编号排序
            filteredData = sortByCodeNumber(stockData);
            
            // 重置后显示全部分类（按分类分组显示）
            renderStockTableByCategory();
            updateStats();
        }

        // 设置加载状态
        function setLoadingState(loading) {
            const tbody = document.getElementById('stock-tbody');
            const tableContainer = document.querySelector('.table-container');
            
            if (loading) {
                // 保持表格高度稳定，避免跳动
                const currentHeight = tbody.offsetHeight;
                
                // 创建一个覆盖整个表格容器的加载状态
                const loadingOverlay = document.createElement('div');
                loadingOverlay.id = 'loading-overlay';
                loadingOverlay.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255, 255, 255, 0.95);
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                    min-height: ${Math.max(currentHeight, 200)}px;
                `;
                
                loadingOverlay.innerHTML = `
                    <div class="loading"></div>
                    <div style="margin-top: 16px; color: #6b7280; font-size: clamp(8px, 0.74vw, 14px);">正在更新数据...</div>
                `;
                
                // 确保表格容器有相对定位
                if (tableContainer) {
                    tableContainer.style.position = 'relative';
                    tableContainer.appendChild(loadingOverlay);
                }
                
                // 清空表格内容但保持结构
                tbody.innerHTML = `
                    <tr>
                        <td colspan="14" style="padding: 0; height: ${Math.max(currentHeight, 200)}px; visibility: hidden;"></td>
                    </tr>
                `;
            } else {
                // 移除加载覆盖层
                const loadingOverlay = document.getElementById('loading-overlay');
                if (loadingOverlay && loadingOverlay.parentNode) {
                    loadingOverlay.parentNode.removeChild(loadingOverlay);
                }
            }
        }

        // 计算总数量

         // 更新统计信息
         function updateStats() {
             let displayedRecords, totalRecords;
             
             if (currentPage === 'stock') {
                 // 库存页面使用库存数据
                 displayedRecords = filteredData.length;
                 totalRecords = stockData.length;
             } else if (currentPage === 'stock' && stockViewType === 'sets') {
                 // 总库存页面的套装视图使用套装数据
                 displayedRecords = setsData.length;
                 totalRecords = setsData.length;
             } else if (currentPage === 'j1' || currentPage === 'j2' || currentPage === 'j3') {
                 // 破损记录页面使用破损记录数据
                 const records = breakRecordsData[currentPage] || [];
                 displayedRecords = records.length;
                 totalRecords = records.length;
             } else {
                 // 默认情况
                 displayedRecords = 0;
                 totalRecords = 0;
             }
             
             document.getElementById('displayed-records').textContent = displayedRecords;
             document.getElementById('total-count').textContent = totalRecords;
             
             console.log(`统计信息更新 - 页面: ${currentPage}, 显示记录: ${displayedRecords}, 总记录: ${totalRecords}`);
         }

        // 渲染库存表格（转置：表头在左，内容向右横向滚动）
        function renderStockTable() {
            // 检查是否选择"全部分类"
            const categoryFilter = document.getElementById('category-filter')?.value || '';
            const searchTerm = document.getElementById('unified-filter')?.value || '';
            
            // 如果选择"全部分类"且没有搜索关键词，按分类分组显示
            if (!categoryFilter && !searchTerm) {
                renderStockTableByCategory();
                return;
            }
            
            // 否则使用原来的单表格显示方式
            const table = document.getElementById('stock-table');
            const tbody = document.getElementById('stock-tbody');
            if (!table || !tbody) return;
            
            // 显示单表格容器，隐藏分类容器
            const singleTableContainer = document.getElementById('single-table-container');
            const categoriesContainer = document.getElementById('categories-container');
            if (singleTableContainer) singleTableContainer.style.display = 'block';
            if (categoriesContainer) categoriesContainer.classList.remove('show');

            // 先把 filteredData 展平为“展示行”（原本表格的一行）数组
            const displayRows = [];
            let rowIndex = 1;

            function currencyHtml(val) {
                return `
                    <div class="currency-display">
                        <span class="currency-symbol">RM</span>
                        <span class="currency-amount">${formatCurrency(val)}</span>
                    </div>
                `;
            }

            function photoHtmlFrom(path, altText, iconClass = 'fa-image') {
                return path
                    ? `<img src="${path}" alt="${altText || ''}" class="product-photo">`
                    : `<div class="no-photo"><i class="fas ${iconClass}"></i></div>`;
            }

            if (!filteredData || filteredData.length === 0) {
                table.classList.remove('transposed');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="14" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无库存数据</div>
                        </td>
                    </tr>
                `;
                return;
            }

            filteredData.forEach((item) => {
                if (item.item_type === 'set') {
                    const set = item;
                    const setPrice = typeof set.set_price !== 'undefined' ? set.set_price : (set.unit_price || 0);

                    if (set.items && Array.isArray(set.items) && set.items.length > 0) {
                        set.items.forEach((setItem) => {
                            const totalQty = parseInt(setItem.total_quantity) || 0;
                            const totalClass = totalQty > 0 ? 'positive-value' : 'zero-value';
                            // 每个套装项目都有独立的序号
                            const displayIndex = rowIndex++;

                            const rowData = {
                                no: String(displayIndex),
                                photo: photoHtmlFrom(setItem.photo_path, setItem.product_name || '', 'fa-image'),
                                product_name: `<strong>${setItem.product_name || '-'}</strong>`,
                                code_number: setItem.code_number || '-',
                                category: setItem.category || set.category || '-',
                                size: setItem.size || '-',
                                unit_price: currencyHtml(setPrice),
                                total: `<span class="${totalClass}">${totalQty}</span>`,
                                actions: `
                                    <button class="action-btn edit-btn" onclick="openEditModal(${setItem.id})" title="编辑">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteDishwareFromSet(${setItem.id}, ${set.id})" title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                `,
                                // 添加套装标识，用于合并显示
                                set_id: set.id,
                                set_category: setItem.category || set.category || '-',
                                set_size: setItem.size || '-',
                                set_price: setPrice
                            };
                            fillRestaurantStocks(rowData, setItem);
                            displayRows.push(rowData);
                        });
                    } else {
                        const totalQty = parseInt(set.total_quantity) || 0;
                        const totalClass = totalQty > 0 ? 'positive-value' : 'zero-value';
                        // 套装没有items时，也使用独立的序号
                        const displayIndex = rowIndex++;
                        
                        const rowData = {
                            no: String(displayIndex),
                            photo: photoHtmlFrom(set.photo_path, set.product_name || set.set_name || '', 'fa-box'),
                            product_name: `<strong>${set.product_name || set.set_name || '-'}</strong>`,
                            code_number: set.code_number || set.set_code || '-',
                            category: set.category || 'SET',
                            size: '-',
                            unit_price: currencyHtml(setPrice),
                            total: `<span class="${totalClass}">${totalQty}</span>`,
                            actions: `
                                <button class="action-btn edit-btn" onclick="editSet(${set.id})" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteSet(${set.id})" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `
                        };
                        fillRestaurantStocks(rowData, set);
                        displayRows.push(rowData);
                    }
                } else {
                    const totalQty = parseInt(item.total_quantity) || 0;
                    const totalClass = totalQty > 0 ? 'positive-value' : 'zero-value';
                    
                    const rowData = {
                        no: String(rowIndex++),
                        photo: photoHtmlFrom(item.photo_path, item.product_name || '', 'fa-image'),
                        product_name: `<strong>${item.product_name || '-'}</strong>`,
                        code_number: item.code_number || '-',
                        category: item.category || '-',
                        size: item.size || '-',
                        unit_price: currencyHtml(item.unit_price || 0),
                        total: `<span class="${totalClass}">${totalQty}</span>`,
                        actions: `
                            <button class="action-btn edit-btn" onclick="openEditModal(${item.id})" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteDishware(${item.id})" title="删除">
                                <i class="fas fa-trash"></i>
                            </button>
                        `
                    };
                    fillRestaurantStocks(rowData, item);
                    displayRows.push(rowData);
                }
            });

            // 转置渲染：左侧是“字段名”，右侧每一列是一个“展示行”
            table.classList.add('transposed');
            const thead = table.querySelector('thead');
            if (thead) thead.innerHTML = '';

            // 使用动态字段定义（包含餐厅店面）
            const fieldDefs = getDynamicFieldDefs();

            let html = '';
            fieldDefs.forEach((f) => {
                // 检查是否是餐厅店面行
                const isRestaurantRow = f.restaurantId !== undefined;
                const rowAttributes = isRestaurantRow 
                    ? `data-row="${f.label}" data-restaurant-row data-restaurant-id="${f.restaurantId}"`
                    : `data-row="${f.label}"`;
                
                html += `<tr ${rowAttributes}><th class="row-header">${f.label}</th>`;
                
                // 对于分类、尺寸、单价行，检查是否需要合并套装项目
                if ((f.key === 'category' || f.key === 'size' || f.key === 'unit_price') && displayRows.length > 0) {
                    let i = 0;
                    while (i < displayRows.length) {
                        const currentRow = displayRows[i];
                        const currentSetId = currentRow.set_id;
                        const currentValue = currentRow[f.key] || '-';
                        
                        // 如果当前行属于套装，检查后续行是否也属于同一套装且值相同
                        if (currentSetId && (f.key === 'category' || f.key === 'size' || f.key === 'unit_price')) {
                            let mergeCount = 1;
                            let j = i + 1;
                            
                            // 检查后续行是否属于同一套装且值相同
                            while (j < displayRows.length) {
                                const nextRow = displayRows[j];
                                if (nextRow.set_id === currentSetId && 
                                    (nextRow[f.key] || '-') === currentValue) {
                                    mergeCount++;
                                    j++;
                                } else {
                                    break;
                                }
                            }
                            
                            // 如果有多列需要合并，使用colspan（在转置表格中，合并列）
                            if (mergeCount > 1) {
                                html += `<td colspan="${mergeCount}" style="vertical-align: middle; text-align: center;">${currentValue}</td>`;
                                // 跳过已合并的列
                                i += mergeCount;
                            } else {
                                html += `<td>${currentValue}</td>`;
                                i++;
                            }
                        } else {
                            // 非套装项目或不需要合并的字段，正常渲染
                            html += `<td>${currentValue}</td>`;
                            i++;
                        }
                    }
                } else {
                    // 其他字段正常渲染
                    displayRows.forEach((r) => {
                        const cell = (r && typeof r[f.key] !== 'undefined') ? r[f.key] : '-';
                        html += `<td>${cell}</td>`;
                    });
                }
                
                html += `</tr>`;
            });

            tbody.innerHTML = html;
            // 初始化拖拽排序功能
            setTimeout(() => {
                initDragAndDrop();
            }, 100);
        }

        // 按分类分组渲染库存表格
        function renderStockTableByCategory() {
            const categoriesContainer = document.getElementById('categories-container');
            const singleTableContainer = document.getElementById('single-table-container');
            
            if (!categoriesContainer) return;
            
            // 显示分类容器，隐藏单表格容器
            categoriesContainer.classList.add('show');
            if (singleTableContainer) singleTableContainer.style.display = 'none';
            
            // 如果没有数据，显示空状态
            if (!stockData || stockData.length === 0) {
                categoriesContainer.innerHTML = `
                    <div class="category-section">
                        <div class="category-header">
                            <div class="category-title">暂无数据</div>
                        </div>
                        <div class="category-table-wrapper" style="padding: 40px; text-align: center; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5; margin-bottom: 16px;"></i>
                            <div>暂无库存数据</div>
                        </div>
                    </div>
                `;
                return;
            }
            
            // 按分类分组数据
            const categoryGroups = {};
            
            stockData.forEach((item) => {
                let category = '';
                
                if (item.item_type === 'set') {
                    // 套装使用套装自己的分类，如果没有则使用SET
                    category = item.category || 'SET';
                    
                    // 如果套装中有items，也可以按items的分类分组
                    if (item.items && item.items.length > 0) {
                        // 对于套装，我们可以使用套装中的第一个item的分类
                        const firstItemCategory = item.items[0].category || item.category || 'SET';
                        category = firstItemCategory;
                    }
                } else {
                    category = item.category || '未分类';
                }
                
                if (!categoryGroups[category]) {
                    categoryGroups[category] = [];
                }
                categoryGroups[category].push(item);
            });
            
            // 对分类进行排序（中文分类排在最后）
            const sortedCategories = Object.keys(categoryGroups).sort((a, b) => {
                const isChineseA = /[\u4e00-\u9fa5]/.test(a);
                const isChineseB = /[\u4e00-\u9fa5]/.test(b);
                
                if (isChineseA && !isChineseB) return 1;
                if (!isChineseA && isChineseB) return -1;
                if (isChineseA && isChineseB) return a.localeCompare(b, 'zh-CN');
                return a.localeCompare(b);
            });
            
            // 生成HTML
            let html = '';
            
            sortedCategories.forEach((category) => {
                const items = categoryGroups[category];
                // 对每个分类内的项目进行排序
                const sortedItems = sortByCodeNumber(items);
                
                html += `
                    <div class="category-section">
                        <div class="category-header">
                            <div class="category-title">
                                <span>${category}</span>
                                <span class="category-count">(${sortedItems.length} 项)</span>
                            </div>
                        </div>
                        <div class="category-table-wrapper">
                            ${renderCategoryTable(sortedItems)}
                        </div>
                    </div>
                `;
            });
            
            categoriesContainer.innerHTML = html;
            // 初始化拖拽排序功能
            initDragAndDrop();
        }
        
        // 渲染单个分类的表格
        function renderCategoryTable(items) {
            if (!items || items.length === 0) {
                return '<div style="padding: 40px; text-align: center; color: #6b7280;">该分类暂无数据</div>';
            }
            
            // 先把 items 展平为"展示行"数组
            const displayRows = [];
            let rowIndex = 1;
            
            function currencyHtml(val) {
                return `
                    <div class="currency-display">
                        <span class="currency-symbol">RM</span>
                        <span class="currency-amount">${formatCurrency(val)}</span>
                    </div>
                `;
            }
            
            function photoHtmlFrom(path, altText, iconClass = 'fa-image') {
                return path
                    ? `<img src="${path}" alt="${altText || ''}" class="product-photo">`
                    : `<div class="no-photo"><i class="fas ${iconClass}"></i></div>`;
            }
            
            items.forEach((item) => {
                if (item.item_type === 'set') {
                    const set = item;
                    const setPrice = typeof set.set_price !== 'undefined' ? set.set_price : (set.unit_price || 0);
                    
                    if (set.items && Array.isArray(set.items) && set.items.length > 0) {
                        set.items.forEach((setItem) => {
                            const totalQty = parseInt(setItem.total_quantity) || 0;
                            const totalClass = totalQty > 0 ? 'positive-value' : 'zero-value';
                            // 每个套装项目都有独立的序号
                            const displayIndex = rowIndex++;

                            const rowData = {
                                no: String(displayIndex),
                                photo: photoHtmlFrom(setItem.photo_path, setItem.product_name || '', 'fa-image'),
                                product_name: `<strong>${setItem.product_name || '-'}</strong>`,
                                code_number: setItem.code_number || '-',
                                category: setItem.category || set.category || '-',
                                size: setItem.size || '-',
                                unit_price: currencyHtml(setPrice),
                                total: `<span class="${totalClass}">${totalQty}</span>`,
                                actions: `
                                    <button class="action-btn edit-btn" onclick="openEditModal(${setItem.id})" title="编辑">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteDishwareFromSet(${setItem.id}, ${set.id})" title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                `,
                                // 添加套装标识，用于合并显示
                                set_id: set.id,
                                set_category: setItem.category || set.category || '-',
                                set_size: setItem.size || '-',
                                set_price: setPrice
                            };
                            fillRestaurantStocks(rowData, setItem);
                            displayRows.push(rowData);
                        });
                    } else {
                        const totalQty = parseInt(set.total_quantity) || 0;
                        const totalClass = totalQty > 0 ? 'positive-value' : 'zero-value';
                        // 套装没有items时，也使用独立的序号
                        const displayIndex = rowIndex++;
                        
                        const rowData = {
                            no: String(displayIndex),
                            photo: photoHtmlFrom(set.photo_path, set.product_name || set.set_name || '', 'fa-box'),
                            product_name: `<strong>${set.product_name || set.set_name || '-'}</strong>`,
                            code_number: set.code_number || set.set_code || '-',
                            category: set.category || 'SET',
                            size: '-',
                            unit_price: currencyHtml(setPrice),
                            total: `<span class="${totalClass}">${totalQty}</span>`,
                            actions: `
                                <button class="action-btn edit-btn" onclick="editSet(${set.id})" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteSet(${set.id})" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `
                        };
                        fillRestaurantStocks(rowData, set);
                        displayRows.push(rowData);
                    }
                } else {
                    const totalQty = parseInt(item.total_quantity) || 0;
                    const totalClass = totalQty > 0 ? 'positive-value' : 'zero-value';
                    
                    const rowData = {
                        no: String(rowIndex++),
                        photo: photoHtmlFrom(item.photo_path, item.product_name || '', 'fa-image'),
                        product_name: `<strong>${item.product_name || '-'}</strong>`,
                        code_number: item.code_number || '-',
                        category: item.category || '-',
                        size: item.size || '-',
                        unit_price: currencyHtml(item.unit_price || 0),
                        total: `<span class="${totalClass}">${totalQty}</span>`,
                        actions: `
                            <button class="action-btn edit-btn" onclick="openEditModal(${item.id})" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteDishware(${item.id})" title="删除">
                                <i class="fas fa-trash"></i>
                            </button>
                        `
                    };
                    fillRestaurantStocks(rowData, item);
                    displayRows.push(rowData);
                }
            });
            
            // 转置渲染：左侧是"字段名"，右侧每一列是一个"展示行"
            const fieldDefs = getDynamicFieldDefs();
            
            let tableHtml = '<table class="stock-table transposed">';
            
            fieldDefs.forEach((f) => {
                // 检查是否是餐厅店面行
                const isRestaurantRow = f.restaurantId !== undefined;
                const rowAttributes = isRestaurantRow 
                    ? `data-row="${f.label}" data-restaurant-row data-restaurant-id="${f.restaurantId}"`
                    : `data-row="${f.label}"`;
                
                tableHtml += `<tr ${rowAttributes}><th class="row-header">${f.label}</th>`;
                
                // 对于分类、尺寸、单价行，检查是否需要合并套装项目
                if ((f.key === 'category' || f.key === 'size' || f.key === 'unit_price') && displayRows.length > 0) {
                    let i = 0;
                    while (i < displayRows.length) {
                        const currentRow = displayRows[i];
                        const currentSetId = currentRow.set_id;
                        const currentValue = currentRow[f.key] || '-';
                        
                        // 如果当前行属于套装，检查后续行是否也属于同一套装且值相同
                        if (currentSetId && (f.key === 'category' || f.key === 'size' || f.key === 'unit_price')) {
                            let mergeCount = 1;
                            let j = i + 1;
                            
                            // 检查后续行是否属于同一套装且值相同
                            while (j < displayRows.length) {
                                const nextRow = displayRows[j];
                                if (nextRow.set_id === currentSetId && 
                                    (nextRow[f.key] || '-') === currentValue) {
                                    mergeCount++;
                                    j++;
                                } else {
                                    break;
                                }
                            }
                            
                            // 如果有多列需要合并，使用colspan（在转置表格中，合并列）
                            if (mergeCount > 1) {
                                tableHtml += `<td colspan="${mergeCount}" style="vertical-align: middle; text-align: center;">${currentValue}</td>`;
                                // 跳过已合并的列
                                i += mergeCount;
                            } else {
                                tableHtml += `<td>${currentValue}</td>`;
                                i++;
                            }
                        } else {
                            // 非套装项目或不需要合并的字段，正常渲染
                            tableHtml += `<td>${currentValue}</td>`;
                            i++;
                        }
                    }
                } else {
                    // 其他字段正常渲染
                    displayRows.forEach((r) => {
                        const cell = (r && typeof r[f.key] !== 'undefined') ? r[f.key] : '-';
                        tableHtml += `<td>${cell}</td>`;
                    });
                }
                
                tableHtml += `</tr>`;
            });
            
            tableHtml += '</table>';
            
            return tableHtml;
        }

        // 打开编辑模态框
        function openEditModal(id) {
            // 首先在stockData中查找
            let item = stockData.find(item => item.id == id);
            
            // 如果在stockData中找不到，可能在套装中，需要从套装数据中查找
            if (!item) {
                // 查找所有套装数据
                const allSets = stockData.filter(item => item.item_type === 'set');
                for (const set of allSets) {
                    if (set.items && set.items.length > 0) {
                        const foundItem = set.items.find(setItem => setItem.id == id);
                        if (foundItem) {
                            item = foundItem;
                            break;
                        }
                    }
                }
            }
            
            if (!item) {
                console.error('找不到ID为', id, '的碗碟数据');
                showAlert('找不到碗碟数据', 'error');
                return;
            }
            
            currentEditId = id;
            
            // 初始化套装相关变量
            window.currentSetId = null;
            window.currentSetMembers = [];
            
            // 确保餐厅店面输入框已更新
            updateEditModalRestaurantInputs();
            
            // 填充表单数据
            document.getElementById('edit-product-name').value = item.product_name || '';
            
            // 获取分类（优先使用item.category）
            let category = item.category || '';
            
            // 解析现有编号，分离分类和数字部分
            const fullCode = item.code_number || '';
            let codeNumber = '';
            
            if (fullCode) {
                // 如果已经有分类信息，从编号中移除分类部分
                if (category) {
                    // 检查编号是否以分类开头
                    if (fullCode.startsWith(category)) {
                        codeNumber = fullCode.substring(category.length);
                    } else {
                        // 如果编号不以分类开头，尝试从编号中提取分类
                        // 先尝试匹配英文分类（2-3个大写字母）
                        const englishCategoryMatch = fullCode.match(/^([A-Z]{2,3})/);
                        if (englishCategoryMatch) {
                            const extractedCategory = englishCategoryMatch[1];
                            // 如果提取的分类与item.category相同，移除它
                            if (extractedCategory === category) {
                                codeNumber = fullCode.substring(extractedCategory.length);
                            } else {
                                // 分类不匹配，保留原编号
                                codeNumber = fullCode;
                            }
                        } else {
                            // 尝试匹配中文分类（中文字符）
                            const chineseCategoryMatch = fullCode.match(/^([\u4e00-\u9fa5]+)/);
                            if (chineseCategoryMatch) {
                                const extractedCategory = chineseCategoryMatch[1];
                                // 如果提取的分类与item.category相同，移除它
                                if (extractedCategory === category) {
                                    codeNumber = fullCode.substring(extractedCategory.length);
                                } else {
                                    // 分类不匹配，保留原编号
                                    codeNumber = fullCode;
                                }
                            } else {
                                // 无法提取分类，保留原编号
                                codeNumber = fullCode;
                            }
                        }
                    }
                } else {
                    // 没有分类信息，尝试从编号中提取分类
                    // 先尝试匹配英文分类（2-3个大写字母）
                    const englishCategoryMatch = fullCode.match(/^([A-Z]{2,3})/);
                    if (englishCategoryMatch) {
                        category = englishCategoryMatch[1];
                        codeNumber = fullCode.substring(category.length);
                    } else {
                        // 尝试匹配中文分类（中文字符）
                        const chineseCategoryMatch = fullCode.match(/^([\u4e00-\u9fa5]+)/);
                        if (chineseCategoryMatch) {
                            category = chineseCategoryMatch[1];
                            codeNumber = fullCode.substring(category.length);
                        } else {
                            // 无法提取分类，直接使用原编号
                            codeNumber = fullCode;
                        }
                    }
                }
            }
            
            document.getElementById('edit-category').value = category;
            document.getElementById('edit-code-number').value = codeNumber;
            document.getElementById('edit-size').value = item.size || '';
            document.getElementById('edit-unit-price').value = item.unit_price || '';
            // 使用动态餐厅店面填充
            fillEditModalRestaurantData(item);
            
            // 重置删除标记
            const deletePhotoFlag = document.getElementById('delete-photo-flag');
            if (deletePhotoFlag) {
                deletePhotoFlag.value = '0';
            }
            
            // 隐藏新照片预览
            const preview = document.getElementById('edit-photo-preview');
            if (preview) {
                preview.style.display = 'none';
            }
            
            document.getElementById('editModal').style.display = 'block';
            
            // 加载套装信息
            loadDishwareSetInfo(id);
        }
        
        // 加载碗碟的套装信息
        async function loadDishwareSetInfo(dishwareId) {
            try {
                const response = await fetch(`${API_BASE_URL}?action=dishware_set_info&dishware_id=${dishwareId}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    // 显示当前套装成员
                    const members = result.data.members || [];
                    const memberNames = members.map(m => m.display).join(', ');
                    const currentSetMembersEl = document.getElementById('current-set-members');
                    if (currentSetMembersEl) {
                        currentSetMembersEl.textContent = memberNames || '暂无';
                    }
                    
                    // 保存当前套装ID和成员ID
                    window.currentSetId = result.data.set_id;
                    window.currentSetMembers = members.map(m => parseInt(m.id));
                } else {
                    const currentSetMembersEl = document.getElementById('current-set-members');
                    if (currentSetMembersEl) {
                        currentSetMembersEl.textContent = '暂无';
                    }
                    window.currentSetId = null;
                    window.currentSetMembers = [];
                }
                
                // 加载可选择的碗碟列表（排除当前碗碟）
                loadAvailableDishwareForSet(dishwareId);
                
                // 更新已选择的套装成员显示
                updateSelectedSetMembersDisplay();
                
            } catch (error) {
                console.error('加载套装信息失败:', error);
                const currentSetMembersEl = document.getElementById('current-set-members');
                if (currentSetMembersEl) {
                    currentSetMembersEl.textContent = '加载失败';
                }
            }
        }
        
        // 加载可用于套装的碗碟列表
        async function loadAvailableDishwareForSet(excludeId) {
            const select = document.getElementById('set-member-select');
            if (!select) return;
            
            // 清空现有选项（保留第一个选项）
            select.innerHTML = '<option value="">请选择要加入套装的碗碟</option>';
            
            // 从stockData中获取所有碗碟（排除当前碗碟和套装）
            const availableItems = stockData.filter(item => 
                item.id != excludeId && 
                item.item_type !== 'set' &&
                item.product_name
            );
            
            // 按编号排序
            availableItems.sort((a, b) => {
                const codeA = (a.code_number || '').toLowerCase();
                const codeB = (b.code_number || '').toLowerCase();
                return codeA.localeCompare(codeB);
            });
            
            // 添加到下拉列表
            availableItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.code_number || ''} - ${item.product_name || ''}`;
                select.appendChild(option);
            });
        }
        
        // 添加套装成员
        function addSetMember() {
            const select = document.getElementById('set-member-select');
            if (!select || !select.value) {
                showAlert('请选择要添加的碗碟', 'warning');
                return;
            }
            
            const memberId = parseInt(select.value);
            
            // 初始化当前套装成员数组
            if (!window.currentSetMembers) {
                window.currentSetMembers = [];
            }
            
            // 如果当前碗碟不在列表中，添加它
            if (!window.currentSetMembers.includes(currentEditId)) {
                window.currentSetMembers.push(currentEditId);
            }
            
            // 添加新成员
            if (!window.currentSetMembers.includes(memberId)) {
                window.currentSetMembers.push(memberId);
            }
            
            // 更新显示
            updateSelectedSetMembersDisplay();
            
            // 清空选择
            select.value = '';
        }
        
        // 更新已选择的套装成员显示
        function updateSelectedSetMembersDisplay() {
            const container = document.getElementById('selected-set-members');
            if (!container) return;
            
            if (!window.currentSetMembers || window.currentSetMembers.length === 0) {
                container.innerHTML = '';
                return;
            }
            
            // 获取成员信息
            const members = window.currentSetMembers.map(id => {
                const item = stockData.find(i => i.id == id);
                if (!item) {
                    // 如果在stockData中找不到，可能在套装中查找
                    const allSets = stockData.filter(i => i.item_type === 'set');
                    for (const set of allSets) {
                        if (set.items && set.items.length > 0) {
                            const foundItem = set.items.find(setItem => setItem.id == id);
                            if (foundItem) {
                                return foundItem;
                            }
                        }
                    }
                }
                return item;
            }).filter(item => item);
            
            if (members.length === 0) {
                container.innerHTML = '';
                return;
            }
            
            let html = '<div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">';
            html += '<span style="font-weight: 600; margin-right: 8px;">已选择成员：</span>';
            
            members.forEach((member, index) => {
                const isCurrent = member.id == currentEditId;
                html += `
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; background: ${isCurrent ? '#fef3c7' : '#e0e7ff'}; border-radius: 4px; font-size: 12px;">
                        ${member.code_number || ''} - ${member.product_name || ''}
                        ${!isCurrent ? `<button type="button" onclick="removeSetMember(${member.id})" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; margin-left: 4px;" title="移除">
                            <i class="fas fa-times"></i>
                        </button>` : '<span style="color: #f59e0b; font-weight: 600;">(当前)</span>'}
                    </span>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }
        
        // 移除套装成员
        function removeSetMember(memberId) {
            if (!window.currentSetMembers) return;
            
            window.currentSetMembers = window.currentSetMembers.filter(id => id != memberId);
            updateSelectedSetMembersDisplay();
        }
        
        // 从套装中移除
        async function removeFromSet() {
            if (!currentEditId) return;
            
            if (!confirm('确定要从套装中移除这个碗碟吗？')) {
                return;
            }
            
            try {
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_dishware_set_relation',
                        dishware_id: currentEditId,
                        member_ids: []
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('已从套装中移除', 'success');
                    // 重新加载套装信息
                    loadDishwareSetInfo(currentEditId);
                    // 重新加载数据
                    setTimeout(() => {
                        loadStockData(true, false);
                    }, 200);
                } else {
                    showAlert('移除失败：' + result.message, 'error');
                }
                
            } catch (error) {
                console.error('移除套装失败:', error);
                showAlert('移除套装失败: ' + error.message, 'error');
            }
        }

        // 处理添加表单照片选择
        function handleAddPhotoSelect(event) {
            const file = event.target.files[0];
            if (file) {
                selectedPhoto = file;
                previewAddPhoto(file);
            }
        }

        // 处理编辑表单照片选择
        function handleEditPhotoSelect(event) {
            const file = event.target.files[0];
            if (file) {
                selectedEditPhoto = file;
                previewEditPhoto(file);
            }
        }

        // 预览添加表单照片
        function previewAddPhoto(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('add-photo-preview');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }

        // 预览编辑表单照片
        function previewEditPhoto(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('edit-photo-preview');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }

        // 拖拽处理
        function handleDragOver(event) {
            event.preventDefault();
            event.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(event) {
            event.currentTarget.classList.remove('dragover');
        }

        function handleAddDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('dragover');
            
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                selectedPhoto = files[0];
                document.getElementById('add-photo').files = files;
                previewAddPhoto(files[0]);
            }
        }

        function handleEditDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('dragover');
            
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                selectedEditPhoto = files[0];
                document.getElementById('edit-photo').files = files;
                previewEditPhoto(files[0]);
            }
        }

        // 删除碗碟
        async function deleteDishware(id) {
            if (!confirm('确定要删除此碗碟吗？此操作不可恢复！')) return;
            
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id: id
                    })
                });
                
                if (result.success) {
                    showAlert('碗碟删除成功', 'success');
                    // 平滑重新加载数据，不显示加载状态
                    setTimeout(() => {
                        loadStockData(true, false);
                    }, 200);
                } else {
                    showAlert('删除失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('删除碗碟时发生错误:', error);
                showAlert('删除碗碟失败: ' + error.message, 'error');
            }
        }

        // 从套装中删除碗碟
        async function deleteDishwareFromSet(dishwareId, setId) {
            if (!confirm('确定要删除这个碗碟吗？删除后将从套装中自动扣除。此操作不可撤销。')) {
                return;
            }
            
            try {
                // 首先删除碗碟
                const deleteResult = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id: dishwareId
                    })
                });
                
                if (deleteResult.success) {
                    // 然后从套装中移除该碗碟
                    const removeResult = await apiCall('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'remove_item_from_set',
                            set_id: setId,
                            dishware_id: dishwareId
                        })
                    });
                    
                    if (removeResult.success) {
                        showAlert('删除成功，已从套装中自动扣除', 'success');
                        setTimeout(() => {
                            loadStockData(true, false);
                        }, 200);
                    } else {
                        showAlert('碗碟删除成功，但从套装中移除失败: ' + (removeResult.message || '未知错误'), 'warning');
                        setTimeout(() => {
                            loadStockData(true, false);
                        }, 200);
                    }
                } else {
                    showAlert('删除失败: ' + (deleteResult.message || '未知错误'), 'error');
                }
                
            } catch (error) {
                console.error('从套装中删除碗碟时发生错误:', error);
                showAlert('删除失败: ' + error.message, 'error');
            }
        }

        // 关闭模态框
        function closeModal() {
            // 清理套装相关变量
            window.currentSetId = null;
            window.currentSetMembers = [];
            console.log('closeModal 函数被调用');
            try {
                const modals = ['editModal', 'addModal', 'damageModal', 'setModal', 'restaurantModal', 'addRestaurantModal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.style.display = 'none';
                        console.log(`模态框 ${modalId} 已隐藏`);
                    } else {
                        console.warn(`找不到模态框: ${modalId}`);
                    }
                });
                
                currentEditId = null;
                selectedPhoto = null;
                selectedEditPhoto = null;
                window.currentShopType = null;
                window.currentDishwareId = null;
                // 清理套装相关变量
                window.currentSetId = null;
                window.currentSetMembers = [];
                resetAddForm();
                resetEditForm();
                console.log('模态框关闭完成');
            } catch (error) {
                console.error('关闭模态框时发生错误:', error);
            }
        }

        // 打开添加碗碟模态框
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        // 重置添加表单
        function resetAddForm() {
            document.getElementById('add-form').reset();
            selectedPhoto = null;
            const preview = document.getElementById('add-photo-preview');
            if (preview) {
                preview.style.display = 'none';
            }
        }


        // 重置编辑表单
        function resetEditForm() {
            document.getElementById('edit-form').reset();
            selectedEditPhoto = null;
            const preview = document.getElementById('edit-photo-preview');
                if (preview) {
                preview.style.display = 'none';
            }
            const deletePhotoFlag = document.getElementById('delete-photo-flag');
            if (deletePhotoFlag) {
                deletePhotoFlag.value = '0';
            }
        }


        // 处理添加表单提交
        async function handleAddFormSubmit(event) {
            event.preventDefault();
            
            if (isLoading) return;
            
            const formData = new FormData();
            const form = event.target;
            
            // 添加表单数据
            formData.append('action', 'add');
            formData.append('product_name', form.product_name.value);
            
            // 自动组合完整的产品编号
            const category = form.category.value;
            const codeNumber = form.code_number.value;
            const fullCodeNumber = category && codeNumber ? category + codeNumber : codeNumber;
            formData.append('code_number', fullCodeNumber);
            
            formData.append('category', form.category.value);
            formData.append('size', form.size.value);
            formData.append('unit_price', form.unit_price.value);
            
            // 如果有照片，先上传照片
            if (selectedPhoto) {
                try {
                    const photoFormData = new FormData();
                    photoFormData.append('action', 'upload_photo');
                    photoFormData.append('photo', selectedPhoto);
                    
                    const photoResponse = await fetch(API_BASE_URL, {
                        method: 'POST',
                        body: photoFormData
                    });
                    
                    const photoResult = await photoResponse.json();
                    
                    if (photoResult.success) {
                        formData.append('photo_path', photoResult.data.photo_path);
                    } else {
                        showAlert('照片上传失败：' + photoResult.message, 'error');
                        return;
                    }
                } catch (error) {
                    showAlert('照片上传失败：' + error.message, 'error');
                    return;
                }
            }
            
            // 提交碗碟信息
            try {
                isLoading = true;
                setAddLoadingState(true);
                
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('碗碟信息保存成功！', 'success');
                    closeModal();
                    // 平滑重新加载数据，不显示加载状态
                    setTimeout(() => {
                        loadStockData(true, false);
                    }, 200);
                } else {
                    showAlert('保存失败：' + result.message, 'error');
                }
                
            } catch (error) {
                console.error('添加碗碟时发生错误:', error);
                showAlert('添加碗碟失败: ' + error.message, 'error');
            } finally {
                isLoading = false;
                setAddLoadingState(false);
            }
        }

        // 设置添加表单加载状态
        function setAddLoadingState(loading) {
            const button = document.getElementById('add-submit-btn');
            
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<div class="loading"></div> 处理中...';
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-save"></i> 保存碗碟信息';
            }
        }

        // 处理编辑表单提交
        async function handleEditFormSubmit(event) {
            event.preventDefault();
            
            if (!currentEditId) return;
            
            if (isLoading) return;
            
            const formData = new FormData();
            const form = event.target;
            
            // 添加表单数据
            formData.append('action', 'update');
            formData.append('id', currentEditId);
            formData.append('product_name', form.product_name.value);
            
            // 自动组合完整的产品编号
            const category = form.category.value;
            const codeNumber = form.code_number.value;
            const fullCodeNumber = category && codeNumber ? category + codeNumber : codeNumber;
            formData.append('code_number', fullCodeNumber);
            
            formData.append('category', form.category.value);
            formData.append('size', form.size.value);
            formData.append('unit_price', form.unit_price.value);
            // 使用动态餐厅店面数据
            const restaurantData = getEditModalRestaurantData();
            Object.keys(restaurantData).forEach(key => {
                formData.append(key, restaurantData[key]);
            });
            
            // 检查是否要删除当前照片
            const deletePhotoFlag = document.getElementById('delete-photo-flag');
            if (deletePhotoFlag && deletePhotoFlag.value === '1') {
                formData.append('delete_photo', '1');
            }
            
            // 如果有新照片，先上传照片
            if (selectedEditPhoto) {
                try {
                    const photoFormData = new FormData();
                    photoFormData.append('action', 'upload_photo');
                    photoFormData.append('photo', selectedEditPhoto);
                    
                    const photoResponse = await fetch(API_BASE_URL, {
                        method: 'POST',
                        body: photoFormData
                    });
                    
                    const photoResult = await photoResponse.json();
                    
                    if (photoResult.success) {
                        formData.append('photo_path', photoResult.data.photo_path);
                } else {
                        showAlert('照片上传失败：' + photoResult.message, 'error');
                        return;
                }
            } catch (error) {
                    showAlert('照片上传失败：' + error.message, 'error');
                    return;
                }
            }
            
            // 提交更新
            try {
                isLoading = true;
                setEditLoadingState(true);
                
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // 同时更新库存
                    const stockUpdateData = {
                        action: 'update_stock',
                        dishware_id: currentEditId,
                        ...getEditModalRestaurantData()
                    };
                    
                    const stockResponse = await fetch(API_BASE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(stockUpdateData)
                    });
                    
                    const stockResult = await stockResponse.json();
                    
                    if (stockResult.success) {
                        showAlert('碗碟信息和库存更新成功！', 'success');
                    } else {
                        showAlert('碗碟信息更新成功，但库存更新失败：' + stockResult.message, 'warning');
                    }
                    
                    // 保存套装关系
                    if (window.currentSetMembers && window.currentSetMembers.length > 0) {
                        try {
                            const setRelationData = {
                                action: 'update_dishware_set_relation',
                                dishware_id: currentEditId,
                                member_ids: window.currentSetMembers
                            };
                            
                            const setResponse = await fetch(API_BASE_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(setRelationData)
                            });
                            
                            const setResult = await setResponse.json();
                            
                            if (setResult.success) {
                                showAlert('碗碟信息、库存和套装关系更新成功！', 'success');
                            } else {
                                showAlert('碗碟信息和库存更新成功，但套装关系更新失败：' + setResult.message, 'warning');
                            }
                        } catch (error) {
                            console.error('保存套装关系失败:', error);
                            showAlert('碗碟信息和库存更新成功，但套装关系更新失败', 'warning');
                        }
                    }
                    
                    closeModal();
                    // 平滑重新加载数据，不显示加载状态
                    setTimeout(() => {
                        loadStockData(true, false);
                    }, 200);
                } else {
                    showAlert('更新失败：' + result.message, 'error');
                }
                
            } catch (error) {
                console.error('更新碗碟时发生错误:', error);
                showAlert('更新碗碟失败: ' + error.message, 'error');
            } finally {
                isLoading = false;
                setEditLoadingState(false);
            }
        }

        // 设置编辑表单加载状态
        function setEditLoadingState(loading) {
            const button = document.getElementById('edit-submit-btn');
            
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<div class="loading"></div> 处理中...';
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-save"></i> 保存更改';
            }
        }


        // 格式化货币
        function formatCurrency(value) {
            if (!value || value === '' || value === '0') return '0.00';
            const num = parseFloat(value);
            return isNaN(num) ? '0.00' : num.toFixed(2);
        }

        // 格式化日期
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('zh-CN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }

        // 刷新数据
        function refreshData() {
            loadStockData(true);
        }

        // 导出数据
        function exportData() {
            if (filteredData.length === 0) {
                showAlert('没有数据可导出', 'error');
                return;
            }
            
            try {
                // 创建CSV数据
                const headers = ['No.', '产品名称', '编号', '分类', '尺寸', '单价', '文化楼', '中央', 'J1', 'J2', 'J3', '总数'];
                let csvContent = headers.join(',') + '\n';
                
                filteredData.forEach((item, index) => {
                    const row = [
                        index + 1,
                        `"${item.product_name}"`,
                        item.code_number || '',
                        item.category,
                        item.size || '',
                        item.unit_price,
                        item.wenhua_quantity || 0,
                        item.central_quantity || 0,
                        item.j1_quantity || 0,
                        item.j2_quantity || 0,
                        item.j3_quantity || 0,
                        item.total_quantity || 0
                    ];
                    csvContent += row.join(',') + '\n';
                });
                
                // 创建下载链接
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `dishware_stock_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('数据导出成功', 'success');
            } catch (error) {
                showAlert('导出失败', 'error');
            }
        }

        // 显示提示信息
        function showAlert(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            // 先检查并限制通知数量（在添加新通知之前）
            let existingToasts = container.querySelectorAll('.toast');
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

        // 键盘快捷键支持
        document.addEventListener('keydown', function(e) {
            // Ctrl+F 聚焦搜索框
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('unified-filter');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Escape键关闭模态框或重置搜索
            if (e.key === 'Escape') {
                // 检查是否有模态框打开
                const modals = ['editModal', 'addModal', 'damageModal', 'setModal', 'restaurantModal', 'addRestaurantModal'];
                let modalOpen = false;
                
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && modal.style.display === 'block') {
                        modalOpen = true;
                        closeModal();
                    }
                });
                
                // 如果没有模态框打开，重置搜索
                if (!modalOpen) {
                    resetFilters();
                }
            }
        });

        // 点击外部关闭下拉菜单和模态框
        document.addEventListener('click', function(event) {
            const editModal = document.getElementById('editModal');
            const addModal = document.getElementById('addModal');
            const damageModal = document.getElementById('damageModal');
            const setModal = document.getElementById('setModal');
            const restaurantModal = document.getElementById('restaurantModal');
            const addRestaurantModal = document.getElementById('addRestaurantModal');
            
            // 处理模态框关闭
            if (event.target == editModal || event.target == addModal || event.target == damageModal || event.target == setModal || event.target == restaurantModal || event.target == addRestaurantModal) {
                closeModal();
            }
            
            // 处理下拉菜单关闭
            if (!event.target.closest('.view-selector')) {
                document.getElementById('view-selector-dropdown').classList.remove('show');
            }
        });

        // 确保所有模态框关闭按钮都能正常工作
        document.addEventListener('DOMContentLoaded', function() {
            // 为所有关闭按钮添加点击事件
            const closeButtons = document.querySelectorAll('.close');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    closeModal();
                });
            });
            
            // 为所有取消按钮添加点击事件
            const cancelButtons = document.querySelectorAll('.btn-secondary');
            cancelButtons.forEach(button => {
                if (button.textContent.includes('取消')) {
                    button.addEventListener('click', function() {
                        closeModal();
                    });
                }
            });
        });

        // 套装管理相关函数
        let setsData = [];

        // 加载套装数据
        async function loadSetsData() {
            try {
                // 使用 set_stock action 来获取包含库存信息的套装数据
                const result = await apiCall('?action=set_stock');
                
                if (result.success) {
                    setsData = result.data.items || [];
                    // 更新餐厅列表（如果API返回了）
                    if (result.data.restaurants && result.data.restaurants.length > 0) {
                        restaurants = result.data.restaurants;
                    }
                    renderSetsTable();
                } else {
                    showAlert('获取套装数据失败: ' + (result.message || '未知错误'), 'error');
                    renderSetsTable([]);
                }
                
            } catch (error) {
                console.error('加载套装数据时发生错误:', error);
                showAlert('加载套装数据失败: ' + error.message, 'error');
                renderSetsTable([]);
            }
        }

        // 渲染套装表格
        function renderSetsTable() {
            const tbody = document.getElementById('sets-tbody');
            if (!tbody) return;
            
            // 计算总列数（基础列 + 餐厅列 + 结束列）
            const baseColumnCount = 5; // 序号, 套装名称, 套装编号, 包含项目, 单价
            const restaurantCount = restaurants.length;
            const endColumnCount = 2; // 总库存, 操作
            const totalColumnCount = baseColumnCount + restaurantCount + endColumnCount;
            
            if (setsData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${totalColumnCount}" class="no-data">暂无套装数据</td>
                    </tr>
                `;
                return;
            }
            
            // 按套装名称排序（使用自然排序，正确处理数字）
            const sortedSetsData = [...setsData].sort((a, b) => {
                const nameA = a.set_name || '';
                const nameB = b.set_name || '';
                return naturalSort(nameA, nameB);
            });
            
            // 获取排序后的餐厅列表
            const sortedRestaurants = [...restaurants].sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
            
            let tableRows = '';
            sortedSetsData.forEach((set, index) => {
                // 获取餐厅库存数据
                const restaurantStocks = sortedRestaurants.map(r => {
                    const stock = set.restaurant_stocks?.[r.id] || 0;
                    return `<td data-label="${r.name}" data-restaurant-id="${r.id}">${stock}</td>`;
                }).join('');
                
                const totalStock = sortedRestaurants.reduce((sum, r) => {
                    return sum + (parseInt(set.restaurant_stocks?.[r.id] || 0));
                }, 0);
                
                tableRows += `
                    <tr>
                        <td data-label="序号">${index + 1}</td>
                        <td data-label="套装名称">
                            <button class="set-expand-btn" onclick="toggleSetExpansion(${set.id})" title="展开/折叠">
                                <i class="fas fa-chevron-right" id="expand-icon-${set.id}"></i>
                            </button>
                            ${set.set_name}
                        </td>
                        <td data-label="套装编号">${set.set_code || '-'}</td>
                        <td data-label="包含项目">${set.items_list || '-'}</td>
                        <td data-label="单价 (RM)">RM ${set.formatted_price}</td>
                        ${restaurantStocks}
                        <td data-label="总库存">${totalStock}</td>
                        <td data-label="操作">
                            <button class="action-btn edit-btn" onclick="editSet(${set.id})" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteSet(${set.id})" title="删除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr id="set-detail-${set.id}" style="display: none;">
                        <td colspan="${totalColumnCount}">
                            <div class="set-items-detail">
                                <h4>套装详情</h4>
                                <div id="set-items-${set.id}">
                                    <!-- 动态填充套装项目 -->
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = tableRows;
            
            // 更新表头（确保顺序正确）
            updateTableHeaders();
        }

        // 切换套装展开状态
        function toggleSetExpansion(setId) {
            const detailRow = document.getElementById(`set-detail-${setId}`);
            const icon = document.getElementById(`expand-icon-${setId}`);
            
            if (detailRow.style.display === 'none') {
                detailRow.style.display = 'table-row';
                icon.className = 'fas fa-chevron-down';
                loadSetDetails(setId);
            } else {
                detailRow.style.display = 'none';
                icon.className = 'fas fa-chevron-right';
            }
        }

        // 加载套装详情
        async function loadSetDetails(setId) {
            try {
                const result = await apiCall(`?action=set_detail&id=${setId}`);
                
                if (result.success) {
                    const set = result.data;
                    const container = document.getElementById(`set-items-${setId}`);
                    
                    if (set.items && set.items.length > 0) {
                        let itemsHtml = '';
                        set.items.forEach((item, index) => {
                            itemsHtml += `
                                <div class="set-item-detail">
                                    <span>${item.product_name} (${item.code_number})</span>
                                    <span>数量: ${item.quantity_in_set}</span>
                                </div>
                            `;
                        });
                        container.innerHTML = itemsHtml;
                    } else {
                        container.innerHTML = '<p>暂无项目</p>';
                    }
                } else {
                    showAlert('获取套装详情失败: ' + (result.message || '未知错误'), 'error');
                }
                
            } catch (error) {
                console.error('加载套装详情时发生错误:', error);
                showAlert('加载套装详情失败: ' + error.message, 'error');
            }
        }

        // 打开套装模态框
        function openSetModal(setId = null) {
            const modal = document.getElementById('setModal');
            const title = document.getElementById('set-modal-title');
            const form = document.getElementById('set-form');
            
            // 重置表单
            form.reset();
            
            if (setId) {
                title.textContent = '编辑套装';
                loadSetForEdit(setId);
            } else {
                title.textContent = '添加套装';
                populateDishwareSelects();
            }
            
            // 重新绑定表单提交事件
            setupSetFormSubmit();
            
            modal.style.display = 'block';
            console.log('套装模态框已打开');
        }

        // 加载套装用于编辑
        async function loadSetForEdit(setId) {
            try {
                // 确保stockData已加载
                if (!stockData || stockData.length === 0) {
                    console.log('stockData未加载，正在加载...');
                    await loadStockData(true, false);
                }
                
                const result = await apiCall(`?action=set_detail&id=${setId}`);
                
                if (result.success) {
                    const set = result.data;
                    
                    // 设置套装ID
                    const setIdEl = document.getElementById('set-id');
                    if (setIdEl) setIdEl.value = setId;
                    
                    // 填充基本信息
                    const setNameEl = document.getElementById('set-name');
                    const setPriceEl = document.getElementById('set-price');
                    
                    if (setNameEl) setNameEl.value = set.set_name || '';
                    if (setPriceEl) setPriceEl.value = set.set_price || '';
                    
                    // 注意：我们移除了set-code和set-description字段，因为简化了表单
                    
                    // 填充套装项目
                    populateSetItems(set.items);
                } else {
                    showAlert('获取套装信息失败: ' + (result.message || '未知错误'), 'error');
                }
                
            } catch (error) {
                console.error('加载套装信息时发生错误:', error);
                showAlert('加载套装信息失败: ' + error.message, 'error');
            }
        }

        // 填充套装项目
        function populateSetItems(items) {
            const container = document.getElementById('set-items-container');
            container.innerHTML = '';
            
            if (items && items.length > 0) {
                items.forEach((item, index) => {
                    // 尝试不同的字段名，确保能获取到正确的ID
                    const dishwareId = item.dishware_id || item.id || item.dishwareId;
                    console.log('填充套装项目，item:', item, 'dishwareId:', dishwareId);
                    addSetItem(dishwareId);
                });
                
                // 等待DOM渲染完成后，再次确保选中值被设置
                setTimeout(() => {
                    items.forEach((item, index) => {
                        const dishwareId = item.dishware_id || item.id || item.dishwareId;
                        if (dishwareId) {
                            const selects = container.querySelectorAll('.dishware-select');
                            if (selects[index]) {
                                selects[index].value = dishwareId;
                                console.log('设置选中值:', dishwareId, '到选择框', index);
                            }
                        }
                    });
                }, 50);
            } else {
                addSetItem();
            }
        }

        // 填充碗碟选择下拉框
        function populateDishwareSelects() {
            const selects = document.querySelectorAll('.dishware-select');
            console.log('找到碗碟选择框数量:', selects.length);
            console.log('当前stockData长度:', stockData ? stockData.length : '未定义');
            
            if (!stockData || stockData.length === 0) {
                console.warn('stockData为空，无法填充碗碟选择框');
                return;
            }
            
            // 收集所有单个碗碟（包括套装中的碗碟）
            const allIndividualItems = [];
            
            // 添加独立的单个碗碟
            const individualItems = stockData.filter(item => item.item_type === 'individual');
            allIndividualItems.push(...individualItems);
            
            // 添加套装中的碗碟
            const setItems = stockData.filter(item => item.item_type === 'set');
            setItems.forEach(set => {
                if (set.items && set.items.length > 0) {
                    allIndividualItems.push(...set.items);
                }
            });
            
            selects.forEach((select, index) => {
                console.log(`填充第${index + 1}个选择框`);
                // 保存当前选中的值
                const currentValue = select.value;
                
                select.innerHTML = '<option value="">请选择碗碟</option>';
                
                allIndividualItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.code_number || '无编号'} ${item.product_name}`;
                    select.appendChild(option);
                });
                
                // 恢复之前选中的值
                if (currentValue) {
                    select.value = currentValue;
                }
            });
            
            console.log('碗碟选择框填充完成');
        }

        // 添加套装项目行
        function addSetItem(selectedDishwareId = '') {
            const container = document.getElementById('set-items-container');
            const row = document.createElement('div');
            row.className = 'set-item-row';
            
            // 收集所有单个碗碟（包括套装中的碗碟）
            const allIndividualItems = [];
            
            // 添加独立的单个碗碟
            const individualItems = stockData.filter(item => item.item_type === 'individual');
            allIndividualItems.push(...individualItems);
            
            // 添加套装中的碗碟
            const setItems = stockData.filter(item => item.item_type === 'set');
            setItems.forEach(set => {
                if (set.items && set.items.length > 0) {
                    allIndividualItems.push(...set.items);
                }
            });
            
            let dishwareOptions = '<option value="">请选择碗碟</option>';
            allIndividualItems.forEach(item => {
                // 使用严格比较，确保类型匹配
                const selected = String(item.id) === String(selectedDishwareId) ? 'selected' : '';
                dishwareOptions += `<option value="${item.id}" ${selected}>${item.code_number || '无编号'} ${item.product_name}</option>`;
            });
            
            row.innerHTML = `
                <select name="dishware_id[]" class="dishware-select" required>
                    ${dishwareOptions}
                </select>
                <button type="button" onclick="removeSetItem(this)" class="btn-remove">删除</button>
            `;
            
            container.appendChild(row);
            
            // 确保选中值被正确设置（使用setTimeout确保DOM已渲染）
            if (selectedDishwareId) {
                setTimeout(() => {
                    const select = row.querySelector('.dishware-select');
                    if (select) {
                        select.value = String(selectedDishwareId);
                        console.log('addSetItem: 设置选中值', selectedDishwareId, '实际值:', select.value);
                    }
                }, 10);
            }
        }

        // 删除套装项目行
        function removeSetItem(button) {
            const container = document.getElementById('set-items-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            } else {
                showAlert('至少需要保留一个套装项目', 'warning');
            }
        }

        // 编辑套装
        function editSet(setId) {
            openSetModal(setId);
        }

        // 删除套装
        async function deleteSet(setId) {
            if (!confirm('确定要删除这个套装吗？')) {
                return;
            }
            
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_set',
                        id: setId
                    })
                });
                
                if (result.success) {
                    showAlert('删除套装成功', 'success');
                    // 刷新数据
                    if (currentPage === 'stock' && stockViewType === 'sets') {
                        loadSetsData();
                    } else if (currentPage === 'stock') {
                        // 如果在总库存页面但不在套装视图，也刷新套装数据（因为可能影响库存）
                        loadSetsData();
                        loadStockData(true);
                    }
                } else {
                    showAlert('删除失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('删除套装时发生错误:', error);
                showAlert('删除套装失败: ' + error.message, 'error');
            }
        }

        // 处理套装表单提交
        function setupSetFormSubmit() {
            const setForm = document.getElementById('set-form');
            if (!setForm) {
                console.error('找不到套装表单元素');
                return;
            }
            
            // 移除现有的事件监听器
            setForm.removeEventListener('submit', handleSetFormSubmit);
            
            // 添加新的事件监听器
            setForm.addEventListener('submit', handleSetFormSubmit);
            console.log('套装表单提交事件已绑定');
            
            // 也为提交按钮添加点击事件监听器作为备用
            const submitBtn = document.getElementById('set-submit-btn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    console.log('套装保存按钮被点击');
                    // 不阻止默认行为，让表单提交事件处理
                });
            }
        }
        
        // 套装表单提交处理函数
        async function handleSetFormSubmit(event) {
            event.preventDefault();
            console.log('套装表单提交被触发');
            
            try {
                const form = event.target;
                if (!form) {
                    console.error('表单元素不存在');
                    showAlert('表单提交失败：找不到表单元素', 'error');
                    return;
                }
                
                const formData = new FormData(form);
                
                console.log('表单数据:', {
                    set_name: formData.get('set_name'),
                    set_price: formData.get('set_price'),
                    dishware_ids: formData.getAll('dishware_id[]')
                });
                
                // 收集套装项目
                const items = [];
                const dishwareIds = formData.getAll('dishware_id[]');
                
                for (let i = 0; i < dishwareIds.length; i++) {
                    if (dishwareIds[i]) {
                        items.push({
                            dishware_id: dishwareIds[i],
                            quantity_in_set: 1  // 默认每个碗碟数量为1
                        });
                    }
                }
                
                console.log('收集到的套装项目:', items);
                
                // 验证必填字段
                const setName = formData.get('set_name');
                const setSize = formData.get('set_size');
                const setPrice = formData.get('set_price');
                
                if (!setName || setName.trim() === '') {
                    showAlert('请输入套装名称', 'error');
                    return;
                }
                
                if (items.length === 0) {
                    showAlert('请至少添加一个碗碟', 'error');
                    return;
                }
                
                // 自动生成套装编号
                const setCode = 'SET' + Date.now().toString().slice(-6);
                
                // 安全地获取模态框标题
                const modalTitle = document.getElementById('set-modal-title');
                const isEdit = modalTitle && modalTitle.textContent === '编辑套装';
                
                const data = {
                    action: isEdit ? 'update_set' : 'add_set',
                    set_name: formData.get('set_name'),
                    set_size: '', // 不再使用尺寸字段
                    set_code: setCode,
                    set_price: formData.get('set_price'),
                    description: '',
                    items: items
                };
                
                // 如果是编辑，添加ID
                const setId = form.querySelector('input[name="set_id"]');
                if (setId && setId.value) {
                    data.id = setId.value;
                }
                
                console.log('准备提交的数据:', data);
                
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                console.log('API响应:', result);
                
                if (result.success) {
                    showAlert(data.action === 'add_set' ? '添加套装成功' : '更新套装成功', 'success');
                    closeModal();
                    // 刷新数据
                    if (currentPage === 'stock' && stockViewType === 'sets') {
                        loadSetsData();
                    } else if (currentPage === 'stock') {
                        // 如果在总库存页面但不在套装视图，也刷新套装数据（因为可能影响库存）
                        loadSetsData();
                        loadStockData(true);
                    }
                } else {
                    showAlert((data.action === 'add_set' ? '添加' : '更新') + '失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('保存套装时发生错误:', error);
                showAlert('保存套装失败: ' + error.message, 'error');
            }
        }
        // ========== 餐厅店面管理相关函数 ==========
        
        // 打开餐厅店面管理模态框
        function openRestaurantModal() {
            const modal = document.getElementById('restaurantModal');
            if (modal) {
                modal.style.display = 'block';
                loadRestaurantsList();
            }
        }

        // 关闭餐厅店面管理模态框
        function closeRestaurantModal() {
            const modal = document.getElementById('restaurantModal');
            const addModal = document.getElementById('addRestaurantModal');
            if (modal) {
                modal.style.display = 'none';
                console.log('餐厅店面管理模态框已关闭');
            }
            if (addModal) {
                addModal.style.display = 'none';
                console.log('添加餐厅店面模态框已关闭');
            }
            // 也调用通用的 closeModal 以确保所有模态框都被关闭
            closeModal();
        }

        // 加载餐厅店面列表
        async function loadRestaurantsList() {
            try {
                const result = await apiCall('?action=restaurants');
                if (result.success) {
                    const tbody = document.getElementById('restaurants-tbody');
                    if (tbody) {
                        if (result.data && result.data.length > 0) {
                            tbody.innerHTML = result.data.map((restaurant, index) => `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${restaurant.name}</td>
                                    <td>
                                        <button class="action-btn edit-btn" onclick="editRestaurant(${restaurant.id})" title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn" onclick="deleteRestaurant(${restaurant.id})" title="删除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('');
                        } else {
                            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #6b7280;">暂无餐厅店面数据</td></tr>';
                        }
                    }
                } else {
                    showAlert('加载餐厅店面列表失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('加载餐厅店面列表时发生错误:', error);
                showAlert('加载餐厅店面列表失败: ' + error.message, 'error');
            }
        }

        // 打开添加/编辑餐厅店面模态框
        function openAddRestaurantModal(restaurantId = null) {
            const modal = document.getElementById('addRestaurantModal');
            const form = document.getElementById('restaurant-form');
            const title = document.getElementById('restaurant-modal-title');
            
            if (modal && form) {
                if (restaurantId) {
                    // 编辑模式
                    if (title) title.textContent = '编辑餐厅店面';
                    loadRestaurantData(restaurantId);
                } else {
                    // 添加模式
                    if (title) title.textContent = '添加餐厅店面';
                    form.reset();
                    document.getElementById('restaurant-id').value = '';
                }
                modal.style.display = 'block';
            }
        }

        // 加载餐厅店面数据（用于编辑）
        async function loadRestaurantData(restaurantId) {
            try {
                const result = await apiCall('?action=restaurants');
                if (result.success) {
                    const restaurant = result.data.find(r => r.id == restaurantId);
                    if (restaurant) {
                        document.getElementById('restaurant-id').value = restaurant.id;
                        document.getElementById('restaurant-name').value = restaurant.name;
                    }
                }
            } catch (error) {
                console.error('加载餐厅店面数据失败:', error);
                showAlert('加载餐厅店面数据失败', 'error');
            }
        }

        // 编辑餐厅店面
        function editRestaurant(restaurantId) {
            openAddRestaurantModal(restaurantId);
        }

        // 删除餐厅店面
        async function deleteRestaurant(restaurantId) {
            if (!confirm('确定要删除这个餐厅店面吗？删除后该店面的库存数据将被移除。')) {
                return;
            }
            
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'delete_restaurant',
                        id: restaurantId
                    })
                });
                
                if (result.success) {
                    showAlert('删除餐厅店面成功', 'success');
                    await loadRestaurants(); // 重新加载餐厅店面列表
                    loadRestaurantsList(); // 刷新管理界面
                    // 如果当前在破损页面，刷新破损记录页面
                    if (currentPage === 'j1' || currentPage === 'j2' || currentPage === 'j3') {
                        loadAllBreakRecords();
                    }
                    // 重新加载库存数据以更新表格
                    if (currentPage === 'stock') {
                        loadStockData();
                    } else if (currentPage === 'stock' && stockViewType === 'sets') {
                        loadSetsData();
                    } else if (currentPage === 'j1' || currentPage === 'j2' || currentPage === 'j3') {
                        // 如果在破损页面，刷新破损记录页面
                        loadAllBreakRecords();
                    }
                    // 更新表头（确保餐厅列顺序正确）
                    updateTableHeaders();
                } else {
                    showAlert('删除餐厅店面失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('删除餐厅店面时发生错误:', error);
                showAlert('删除餐厅店面失败: ' + error.message, 'error');
            }
        }

        // 餐厅店面表单提交
        document.addEventListener('DOMContentLoaded', function() {
            const restaurantForm = document.getElementById('restaurant-form');
            if (restaurantForm) {
                restaurantForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const restaurantId = document.getElementById('restaurant-id').value;
                    const name = document.getElementById('restaurant-name').value.trim();
                    
                    if (!name) {
                        showAlert('请输入餐厅店面名称', 'error');
                        return;
                    }
                    
                    try {
                        const action = restaurantId ? 'update_restaurant' : 'add_restaurant';
                        const data = {
                            action: action,
                            name: name
                        };
                        
                        if (restaurantId) {
                            data.id = restaurantId;
                        }
                        
                        const result = await apiCall('', {
                            method: 'POST',
                            body: JSON.stringify(data)
                        });
                        
                        if (result.success) {
                            showAlert(restaurantId ? '更新餐厅店面成功' : '添加餐厅店面成功', 'success');
                            closeRestaurantModal();
                            await loadRestaurants(); // 重新加载餐厅店面列表
                            loadRestaurantsList(); // 刷新管理界面
                            // 重新加载库存数据以更新表格
                            if (currentPage === 'stock') {
                                loadStockData();
                            } else if (currentPage === 'stock' && stockViewType === 'sets') {
                                loadSetsData();
                            } else if (currentPage === 'j1' || currentPage === 'j2' || currentPage === 'j3') {
                                // 如果在破损页面，刷新破损记录页面
                                loadAllBreakRecords();
                            }
                            // 更新表头（确保餐厅列顺序正确）
                            updateTableHeaders();
                        } else {
                            showAlert((restaurantId ? '更新' : '添加') + '餐厅店面失败: ' + (result.message || '未知错误'), 'error');
                        }
                    } catch (error) {
                        console.error('保存餐厅店面时发生错误:', error);
                        showAlert((restaurantId ? '更新' : '添加') + '餐厅店面失败: ' + error.message, 'error');
                    }
                });
            }
        });

        // ==================== 碗碟转卖功能 ====================
        
        // 加载所有餐厅的转卖记录
        async function loadAllTransferRecords() {
            try {
                // 获取所有餐厅列表，筛选出J开头的餐厅（排除"中央"和"文化楼"）
                const jRestaurants = restaurants.filter(r => {
                    const name = r.name.toLowerCase();
                    const lowerName = r.name.toLowerCase();
                    return lowerName.startsWith('j') && 
                           lowerName !== '中央' && 
                           lowerName !== '文化楼' &&
                           name !== 'wenhua' && 
                           name !== 'central';
                }).sort((a, b) => {
                    const nameA = a.name.toLowerCase();
                    const nameB = b.name.toLowerCase();
                    const numA = parseInt(nameA.replace('j', '')) || 0;
                    const numB = parseInt(nameB.replace('j', '')) || 0;
                    return numA - numB;
                });

                // 同时加载所有J开头店铺的数据
                const promises = jRestaurants.map(restaurant => {
                    const shopType = restaurant.name.toLowerCase();
                    return apiCall(`?action=transfer_records&shop_type=${shopType}`).then(result => ({
                        shopType: shopType,
                        restaurant: restaurant,
                        result: result
                    }));
                });

                const results = await Promise.all(promises);

                // 存储数据
                results.forEach(({ shopType, result }) => {
                    if (result.success) {
                        transferRecordsData[shopType] = result.data || [];
                    } else {
                        transferRecordsData[shopType] = [];
                    }
                });

                // 存储J餐厅列表供渲染使用
                window.jRestaurantsForTransfer = jRestaurants;

                // 渲染合并页面
                renderMergedTransferRecordsPage();
                // 应用餐厅过滤
                filterTransferRecordsByRestaurant();
                updateStats();
            } catch (error) {
                console.error('加载转卖记录时发生错误:', error);
                showAlert('加载转卖记录失败: ' + error.message, 'error');
            }
        }

        // 刷新单个餐厅的转卖记录（保留新行）
        async function refreshSingleRestaurantTransferRecords(shopType) {
            try {
                // 只加载对应餐厅的数据
                const result = await apiCall(`?action=transfer_records&shop_type=${shopType}`);
                
                if (result.success) {
                    // 更新数据
                    transferRecordsData[shopType] = result.data || [];
                    
                    // 找到对应的表格tbody
                    const tbody = document.getElementById(`${shopType}-transfer-tbody`);
                    if (!tbody) return;
                    
                    // 保存所有新行（.new-row）及其数据
                    const newRows = Array.from(tbody.querySelectorAll('tr.new-row'));
                    const newRowsData = newRows.map(row => {
                        const codeInput = row.querySelector('.break-code-input');
                        const quantityInput = row.querySelector('.break-quantity-input');
                        const toSelect = row.querySelector('.transfer-to-select');
                        const rowId = codeInput?.id?.replace('-code', '') || '';
                        const priceSpan = rowId ? row.querySelector(`#${rowId}-price`) : null;
                        const totalSpan = rowId ? row.querySelector(`#${rowId}-total`) : null;
                        
                        return {
                            row: row.cloneNode(true), // 克隆节点
                            rowId: rowId,
                            code: codeInput?.value || '',
                            quantity: quantityInput?.value || '',
                            price: priceSpan?.textContent?.trim() || '0.00',
                            total: totalSpan?.textContent?.trim() || '0.00',
                            toShop: toSelect?.value || '',
                            productId: codeInput?.dataset?.productId || ''
                        };
                    });
                    
                    // 根据过滤状态筛选记录
                    const allRecords = transferRecordsData[shopType] || [];
                    const filterType = transferFilterState[shopType] || 'all';
                    const filteredRecords = filterType === 'all' 
                        ? allRecords 
                        : allRecords.filter(r => r.record_type === filterType);
                    
                    // 重新渲染该餐厅的表格行（不包括新行）
                    const rowsHtml = renderTransferRecordsRows(filteredRecords, shopType);
                    
                    // 清空tbody并添加已保存的记录
                    tbody.innerHTML = rowsHtml;
                    
                    // 重新添加所有新行
                    newRowsData.forEach(({ row, rowId, code, quantity, price, total, toShop, productId }) => {
                        if (row && rowId) {
                            // 恢复输入框的值
                            const clonedCodeInput = row.querySelector('.break-code-input');
                            const clonedQuantityInput = row.querySelector('.break-quantity-input');
                            const clonedPriceSpan = row.querySelector(`#${rowId}-price`);
                            const clonedTotalSpan = row.querySelector(`#${rowId}-total`);
                            const clonedToSelect = row.querySelector('.transfer-to-select');
                            
                            if (clonedCodeInput) {
                                clonedCodeInput.value = code;
                                if (productId) {
                                    clonedCodeInput.dataset.productId = productId;
                                    clonedCodeInput.setAttribute('data-product-id', productId);
                                }
                            }
                            if (clonedQuantityInput) {
                                clonedQuantityInput.value = quantity;
                            }
                            if (clonedPriceSpan) {
                                clonedPriceSpan.textContent = price;
                            }
                            if (clonedTotalSpan) {
                                clonedTotalSpan.textContent = total;
                            }
                            if (clonedToSelect) {
                                clonedToSelect.value = toShop;
                            }
                            
                            tbody.appendChild(row);
                            
                            // 重新绑定事件
                            setTimeout(() => {
                                bindBreakComboboxEvents(rowId);
                            }, 100);
                        }
                    });
                }
            } catch (error) {
                console.error('刷新单个餐厅转卖记录时发生错误:', error);
            }
        }

        // 渲染合并的转卖记录页面
        function renderMergedTransferRecordsPage() {
            const container = document.getElementById('transfer-records-container');
            if (!container) {
                console.error('找不到转卖记录容器');
                return;
            }

            // 使用动态获取的J餐厅列表
            const jRestaurants = window.jRestaurantsForTransfer || restaurants.filter(r => {
                const name = r.name.toLowerCase();
                const lowerName = r.name.toLowerCase();
                return lowerName.startsWith('j') && 
                       lowerName !== '中央' && 
                       lowerName !== '文化楼' &&
                       name !== 'wenhua' && 
                       name !== 'central';
            }).sort((a, b) => {
                const nameA = a.name.toLowerCase();
                const nameB = b.name.toLowerCase();
                const numA = parseInt(nameA.replace('j', '')) || 0;
                const numB = parseInt(nameB.replace('j', '')) || 0;
                return numA - numB;
            });

            if (jRestaurants.length === 0) {
                container.innerHTML = `
                    <div style="padding: 40px; text-align: center; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: clamp(42px, 2.5vw, 48px); opacity: 0.5; margin-bottom: clamp(8px, 0.83vw, 16px);"></i>
                        <div>暂无J开头的餐厅店面</div>
                    </div>
                `;
                return;
            }

            let html = '';
            
            jRestaurants.forEach(restaurant => {
                const shopType = restaurant.name.toLowerCase();
                const allRecords = transferRecordsData[shopType] || [];
                
                // 初始化过滤状态
                if (!transferFilterState[shopType]) {
                    transferFilterState[shopType] = 'all';
                }
                
                // 根据过滤状态筛选记录
                const filterType = transferFilterState[shopType];
                const records = filterType === 'all' 
                    ? allRecords 
                    : allRecords.filter(r => r.record_type === filterType);
                
                // 计算转卖和来自的数量
                const outCount = allRecords.filter(r => r.record_type === 'out').length;
                const inCount = allRecords.filter(r => r.record_type === 'in').length;
                
                html += `
                    <div class="break-record-section">
                        <div class="break-record-header">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span>${restaurant.name}转卖</span>
                                <span style="font-size: 12px; opacity: 0.9;">(${records.length} 项)</span>
                                <div style="display: flex; gap: 4px; margin-left: 8px;">
                                    <button class="btn" 
                                            onclick="setTransferFilter('${shopType}', 'all')" 
                                            style="padding: clamp(2px, 0.21vw, 4px) clamp(6px, 0.63vw, 12px); font-size: clamp(8px, 0.74vw, 12px); white-space: nowrap; background: ${filterType === 'all' ? '#f99e00' : 'white'}; color: ${filterType === 'all' ? 'white' : '#333'}; border: 1px solid #ddd;">
                                        全部
                                    </button>
                                    <button class="btn" 
                                            onclick="setTransferFilter('${shopType}', 'out')" 
                                            style="padding: clamp(2px, 0.21vw, 4px) clamp(6px, 0.63vw, 12px); font-size: clamp(8px, 0.74vw, 12px); white-space: nowrap; background: ${filterType === 'out' ? '#f99e00' : 'white'}; color: ${filterType === 'out' ? 'white' : '#333'}; border: 1px solid #ddd;">
                                        转卖
                                    </button>
                                    <button class="btn" 
                                            onclick="setTransferFilter('${shopType}', 'in')" 
                                            style="padding: clamp(2px, 0.21vw, 4px) clamp(6px, 0.63vw, 12px); font-size: clamp(8px, 0.74vw, 12px); white-space: nowrap; background: ${filterType === 'in' ? '#f99e00' : 'white'}; color: ${filterType === 'in' ? 'white' : '#333'}; border: 1px solid #ddd;">
                                        来自
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-success" onclick="openTransferRowsModal('${shopType}')" style="padding: clamp(3px, 0.31vw, 6px) clamp(6px, 0.63vw, 12px); font-size: clamp(8px, 0.74vw, 12px); white-space: nowrap;">
                                <i class="fas fa-plus"></i> 转卖碗碟
                            </button>
                        </div>
                        <div class="break-record-table-wrapper">
                            <table class="break-record-table" id="${shopType}-transfer-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>编号</th>
                                        <th>数量</th>
                                        <th>进出</th>
                                        <th>单价</th>
                                        <th>总价</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="${shopType}-transfer-tbody">
                                    ${renderTransferRecordsRows(records, shopType)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // 渲染转卖记录行
        function renderTransferRecordsRows(records, shopId) {
            if (records.length === 0) {
                return `
                    <tr>
                        <td colspan="7" class="no-data" style="padding: clamp(20px, 2.76vw, 53px); text-align: center; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: clamp(42px, 2.5vw, 48px); opacity: 0.5; margin-bottom: clamp(8px, 0.83vw, 16px);"></i>
                            <div>暂无转卖记录</div>
                        </td>
                    </tr>
                `;
            }

            let rows = '';
            records.forEach((record, index) => {
                const isInRecord = record.record_type === 'in';
                const isOutRecord = record.record_type === 'out';
                // 进出列：只显示餐厅名称，不显示"转给"或"来自"
                const transferDirection = isOutRecord 
                    ? (record.to_restaurant_name || record.to_shop_type.toUpperCase()) 
                    : (record.from_restaurant_name || record.from_shop_type.toUpperCase());
                
                // 根据记录类型设置价格颜色：转卖（out）=红色，来自（in）=绿色
                const priceColor = isOutRecord ? '#dc3545' : '#000000';
                
                rows += `
                    <tr data-id="${record.id}" data-shop="${shopId}" data-type="${record.record_type}" data-related="${record.related_record_id || ''}">
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${record.code_number || '-'}</td>
                        <td class="text-center"><span>${record.quantity}</span></td>
                        <td class="text-center">${transferDirection}</td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol" style="color: ${priceColor};">${isOutRecord ? '-' : ''}RM</span>
                                <span class="currency-amount" style="color: ${priceColor};">${formatCurrency(record.unit_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol" style="color: ${priceColor};">${isOutRecord ? '-' : ''}RM</span>
                                <span class="currency-amount" style="color: ${priceColor};">${formatCurrency(record.total_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center" id="transfer-action-${record.id}">
                            ${isInRecord ? 
                                '<span style="color: #6b7280; font-size: 12px;">自动生成</span>' :
                                `
                                <button class="action-btn edit-btn" onclick="editTransferRecord(${record.id}, '${shopId}')" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteTransferRecord(${record.id}, '${shopId}')" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>
                                `
                            }
                        </td>
                    </tr>
                `;
            });

            return rows;
        }

        // 当前选中的餐厅类型（用于创建新行）
        let currentTransferShopType = null;
        
        // 转卖记录过滤状态：'all' = 全部, 'out' = 转卖, 'in' = 来自
        const transferFilterState = {};
        
        // 转卖页面餐厅过滤状态
        let transferRestaurantFilter = '';
        
        // 破损记录页面餐厅过滤状态
        let breakRestaurantFilter = '';

        // 打开转卖记录行数选择弹窗
        function openTransferRowsModal(shopType) {
            currentTransferShopType = shopType;
            const modal = document.getElementById('transfer-rows-modal');
            if (modal) {
                modal.classList.add('show');
                document.getElementById('transfer-rows-count').value = 1;
            }
        }

        // 关闭转卖记录行数选择弹窗
        function closeTransferRowsModal() {
            const modal = document.getElementById('transfer-rows-modal');
            if (modal) {
                modal.classList.remove('show');
            }
        }

        // 创建多行转卖记录
        function createMultipleTransferRows() {
            const rowsCount = parseInt(document.getElementById('transfer-rows-count').value);
            
            if (!rowsCount || rowsCount < 1 || rowsCount > 50) {
                showAlert('请输入有效的行数（1-50）', 'error');
                return;
            }
            
            if (!currentTransferShopType) {
                showAlert('餐厅类型未设置', 'error');
                return;
            }
            
            closeTransferRowsModal();
            
            if (!stockData || stockData.length === 0) {
                showAlert('正在加载碗碟数据，请稍后再试', 'warning');
                loadStockData(true, false).then(() => {
                    setTimeout(() => {
                        createMultipleTransferRows();
                    }, 500);
                });
                return;
            }
            
            for (let i = 0; i < rowsCount; i++) {
                addNewTransferRow(currentTransferShopType);
            }
            
            setTimeout(() => {
                const tbody = document.getElementById(`${currentTransferShopType}-transfer-tbody`);
                if (tbody) {
                    tbody.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            }, 100);
            
            showAlert(`成功创建 ${rowsCount} 行记录`, 'success');
        }

        // 设置转卖记录过滤状态
        function setTransferFilter(shopType, filterType) {
            transferFilterState[shopType] = filterType;
            
            // 重新渲染该餐厅的表格
            const tbody = document.getElementById(`${shopType}-transfer-tbody`);
            if (!tbody) return;
            
            // 保存所有新行（.new-row）及其数据
            const newRows = Array.from(tbody.querySelectorAll('tr.new-row'));
            const newRowsData = newRows.map(row => {
                const codeInput = row.querySelector('.break-code-input');
                const quantityInput = row.querySelector('.break-quantity-input');
                const toSelect = row.querySelector('.transfer-to-select');
                const rowId = codeInput?.id?.replace('-code', '') || '';
                const priceSpan = rowId ? row.querySelector(`#${rowId}-price`) : null;
                const totalSpan = rowId ? row.querySelector(`#${rowId}-total`) : null;
                
                return {
                    row: row.cloneNode(true),
                    rowId: rowId,
                    code: codeInput?.value || '',
                    quantity: quantityInput?.value || '',
                    price: priceSpan?.textContent?.trim() || '0.00',
                    total: totalSpan?.textContent?.trim() || '0.00',
                    toShop: toSelect?.value || '',
                    productId: codeInput?.dataset?.productId || ''
                };
            });
            
            // 根据过滤状态筛选记录
            const allRecords = transferRecordsData[shopType] || [];
            const filteredRecords = filterType === 'all' 
                ? allRecords 
                : allRecords.filter(r => r.record_type === filterType);
            
            // 重新渲染表格行
            const rowsHtml = renderTransferRecordsRows(filteredRecords, shopType);
            tbody.innerHTML = rowsHtml;
            
            // 重新添加所有新行
            newRowsData.forEach(({ row, rowId, code, quantity, price, total, toShop, productId }) => {
                if (row && rowId) {
                    const clonedCodeInput = row.querySelector('.break-code-input');
                    const clonedQuantityInput = row.querySelector('.break-quantity-input');
                    const clonedPriceSpan = row.querySelector(`#${rowId}-price`);
                    const clonedTotalSpan = row.querySelector(`#${rowId}-total`);
                    const clonedToSelect = row.querySelector('.transfer-to-select');
                    
                    if (clonedCodeInput) {
                        clonedCodeInput.value = code;
                        if (productId) {
                            clonedCodeInput.dataset.productId = productId;
                            clonedCodeInput.setAttribute('data-product-id', productId);
                        }
                    }
                    if (clonedQuantityInput) {
                        clonedQuantityInput.value = quantity;
                    }
                    if (clonedPriceSpan) {
                        clonedPriceSpan.textContent = price;
                    }
                    if (clonedTotalSpan) {
                        clonedTotalSpan.textContent = total;
                    }
                    if (clonedToSelect) {
                        clonedToSelect.value = toShop;
                    }
                    
                    tbody.appendChild(row);
                    
                    setTimeout(() => {
                        bindBreakComboboxEvents(rowId);
                    }, 100);
                }
            });
            
            // 更新标题中的项数和按钮状态
            const header = document.querySelector(`#${shopType}-transfer-table`)?.closest('.break-record-section')?.querySelector('.break-record-header');
            if (header) {
                const allRecords = transferRecordsData[shopType] || [];
                const outCount = allRecords.filter(r => r.record_type === 'out').length;
                const inCount = allRecords.filter(r => r.record_type === 'in').length;
                
                // 更新项数显示
                const countSpan = header.querySelector('span[style*="font-size: 12px"]');
                if (countSpan) {
                    countSpan.textContent = `(${filteredRecords.length} 项)`;
                }
                
                // 更新按钮状态
                const buttons = header.querySelectorAll('button[onclick*="setTransferFilter"]');
                buttons.forEach(btn => {
                    const onclick = btn.getAttribute('onclick');
                    if (onclick.includes("'all'")) {
                        btn.style.background = filterType === 'all' ? '#f99e00' : 'white';
                        btn.style.color = filterType === 'all' ? 'white' : '#333';
                    } else if (onclick.includes("'out'")) {
                        btn.style.background = filterType === 'out' ? '#f99e00' : 'white';
                        btn.style.color = filterType === 'out' ? 'white' : '#333';
                    } else if (onclick.includes("'in'")) {
                        btn.style.background = filterType === 'in' ? '#f99e00' : 'white';
                        btn.style.color = filterType === 'in' ? 'white' : '#333';
                    }
                });
            }
        }

        // 添加新行到转卖记录表格
        function addNewTransferRow(shopType) {
            const tbody = document.getElementById(`${shopType}-transfer-tbody`);
            if (!tbody) {
                console.error(`找不到表格tbody: ${shopType}-transfer-tbody`);
                return;
            }
            
            const noDataRow = tbody.querySelector('tr td.no-data');
            if (noDataRow) {
                tbody.innerHTML = '';
            }
            
            const row = document.createElement('tr');
            row.className = 'new-row';
            const rowId = 'new-transfer-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            
            const currentRowCount = tbody.querySelectorAll('tr:not(.new-row)').length;
            const newRowIndex = currentRowCount + tbody.querySelectorAll('tr.new-row').length + 1;
            
            // 生成编号选项（用于combobox）
            let codeOptions = [];
            if (stockData && stockData.length > 0) {
                stockData.forEach(item => {
                    const code = item.code_number || '';
                    if (code) {
                        codeOptions.push({
                            code: code,
                            id: item.id,
                            price: item.unit_price || 0
                        });
                    }
                });
            }
            
            // 生成餐厅选项（用于进出下拉列表）
            let restaurantOptions = '';
            const jRestaurants = window.jRestaurantsForTransfer || restaurants.filter(r => {
                const name = r.name.toLowerCase();
                return name.startsWith('j') && name !== shopType;
            });
            jRestaurants.forEach(r => {
                restaurantOptions += `<option value="${r.name.toLowerCase()}" style="text-align: center;">${r.name}</option>`;
            });
            
            row.innerHTML = `
                <td class="text-center">${newRowIndex}</td>
                <td class="text-center">
                    <div class="combobox-container" id="${rowId}-code-combo">
                        <input 
                            type="text" 
                            class="combobox-input break-code-input" 
                            id="${rowId}-code"
                            placeholder="输入或选择编号..."
                            autocomplete="off"
                            data-row-id="${rowId}"
                            data-field="code"
                        />
                        <i class="fas fa-chevron-down combobox-arrow"></i>
                        <div class="combobox-dropdown" id="${rowId}-code-dropdown">
                            ${codeOptions.map(opt => `<div class="combobox-option" data-value="${opt.code}" data-id="${opt.id}" data-price="${opt.price}">${opt.code}</div>`).join('')}
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <input type="text" 
                           class="break-quantity-input" 
                           id="${rowId}-quantity" 
                           placeholder="0" 
                           value="" 
                           onblur="calculateTransferRowTotal('${rowId}')" 
                           style="width: 100%; padding: 4px 8px; border: none; background: transparent; text-align: center; outline: none;">
                </td>
                <td class="text-center">
                    <select class="transfer-to-select" id="${rowId}-to" style="width: 100%; padding: 4px 8px; border: none; background: transparent; text-align: center; text-align-last: center; outline: none; font-size: clamp(8px, 0.74vw, 14px);">
                        <option value="" style="text-align: center;">选择餐厅</option>
                        ${restaurantOptions}
                    </select>
                </td>
                <td class="text-center">
                    <div class="currency-display">
                        <span class="currency-symbol">RM</span>
                        <span class="currency-amount" id="${rowId}-price">0.00</span>
                    </div>
                </td>
                <td class="text-center">
                    <div class="currency-display">
                        <span class="currency-symbol">RM</span>
                        <span class="currency-amount" id="${rowId}-total">0.00</span>
                    </div>
                </td>
                <td class="text-center">
                    <button class="action-btn save-btn" onclick="saveNewTransferRow('${rowId}', '${shopType}')" title="保存" style="background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-right: 4px;">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="action-btn cancel-btn" onclick="cancelNewTransferRow('${rowId}', '${shopType}')" title="取消" style="background: #6c757d; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            
            // 绑定combobox事件
            setTimeout(() => {
                bindBreakComboboxEvents(rowId);
            }, 100);
        }

        // 计算转卖记录行总价
        function calculateTransferRowTotal(rowId) {
            const quantityInput = document.getElementById(`${rowId}-quantity`);
            const priceSpan = document.getElementById(`${rowId}-price`);
            const totalSpan = document.getElementById(`${rowId}-total`);
            
            if (!quantityInput || !priceSpan || !totalSpan) return;
            
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceSpan.textContent.trim()) || 0;
            const total = quantity * price;
            totalSpan.textContent = total.toFixed(2);
        }

        // 保存新转卖记录行
        async function saveNewTransferRow(rowId, shopType) {
            const codeInput = document.getElementById(`${rowId}-code`);
            const quantityInput = document.getElementById(`${rowId}-quantity`);
            const priceSpan = document.getElementById(`${rowId}-price`);
            const toSelect = document.getElementById(`${rowId}-to`);
            
            if (!codeInput || !quantityInput || !priceSpan || !toSelect) {
                showAlert('找不到输入元素，请刷新页面后重试', 'error');
                return;
            }
            
            const productId = codeInput.dataset.productId;
            const code = codeInput.value.trim();
            const quantity = parseFloat(quantityInput.value) || 0;
            const toShopType = toSelect.value;
            
            if (!code || !productId) {
                showAlert('请输入或选择编号', 'error');
                return;
            }
            
            if (!toShopType) {
                showAlert('请选择转卖给哪间餐厅', 'error');
                return;
            }
            
            if (quantity <= 0) {
                showAlert('请输入有效的转卖数量', 'error');
                return;
            }
            
            // 单价从产品信息中自动获取
            const product = stockData.find(item => item.id == productId || item.code_number === code);
            if (!product) {
                showAlert('找不到产品信息', 'error');
                return;
            }
            
            const unitPrice = product.unit_price || 0;
            
            try {
                const today = new Date().toISOString().split('T')[0];
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_transfer_record',
                        dishware_id: productId,
                        from_shop_type: shopType,
                        to_shop_type: toShopType,
                        quantity: quantity,
                        unit_price: unitPrice,
                        transfer_date: today,
                        recorded_by: 'system'
                    })
                });
                
                if (result.success) {
                    showAlert('转卖记录添加成功', 'success');
                    
                    // 移除当前保存的行
                    if (codeInput) {
                        const row = codeInput.closest('tr');
                        if (row) {
                            const closeHandler = codeInput._closeHandler;
                            if (closeHandler) {
                                document.removeEventListener('click', closeHandler);
                            }
                            const codeDropdown = document.getElementById(`${rowId}-code-dropdown`);
                            if (codeDropdown && codeDropdown.parentElement === document.body) {
                                document.body.removeChild(codeDropdown);
                            }
                            row.remove();
                        }
                    }
                    
                    // 刷新转卖记录（只刷新相关餐厅，保留其他新行）
                    // 需要刷新转出餐厅和接收餐厅的数据
                    await refreshSingleRestaurantTransferRecords(shopType);
                    await refreshSingleRestaurantTransferRecords(toShopType);
                    
                    // 刷新总库存
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('添加失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('保存转卖记录时发生错误:', error);
                showAlert('保存转卖记录失败: ' + error.message, 'error');
            }
        }

        // 取消新转卖记录行
        function cancelNewTransferRow(rowId, shopType) {
            const codeInput = document.getElementById(`${rowId}-code`);
            if (!codeInput) {
                return;
            }
            
            const row = codeInput.closest('tr');
            if (row) {
                const closeHandler = codeInput._closeHandler;
                if (closeHandler) {
                    document.removeEventListener('click', closeHandler);
                }
                const codeDropdown = document.getElementById(`${rowId}-code-dropdown`);
                if (codeDropdown && codeDropdown.parentElement === document.body) {
                    document.body.removeChild(codeDropdown);
                }
                row.remove();
                
                // 检查是否还有数据
                const tbody = document.getElementById(`${shopType}-transfer-tbody`);
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="no-data" style="padding: clamp(20px, 2.76vw, 53px); text-align: center; color: #6b7280;">
                                <i class="fas fa-inbox" style="font-size: clamp(42px, 2.5vw, 48px); opacity: 0.5; margin-bottom: clamp(8px, 0.83vw, 16px);"></i>
                                <div>暂无转卖记录</div>
                            </td>
                        </tr>
                    `;
                }
            }
        }

        // 显示转卖记录的保存/取消按钮（当数量改变时）
        function showTransferSaveButtons(recordId, shopId) {
            const actionCell = document.getElementById(`transfer-action-${recordId}`);
            if (!actionCell) return;
            
            // 检查是否已经在编辑模式
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (row && row.classList.contains('editing-row')) {
                return; // 如果已经在编辑模式，不显示保存/取消按钮
            }
            
            // 保存原始按钮HTML（如果还没有保存）
            if (!actionCell.dataset.originalHtml) {
                actionCell.dataset.originalHtml = actionCell.innerHTML;
            }
            
            // 显示保存和取消按钮
            actionCell.innerHTML = `
                <button class="action-btn save-btn" onclick="saveTransferQuantity(${recordId}, '${shopId}')" title="保存" style="background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-right: 4px;">
                    <i class="fas fa-check"></i>
                </button>
                <button class="action-btn cancel-btn" onclick="cancelTransferQuantity(${recordId}, '${shopId}')" title="取消" style="background: #6c757d; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            `;
        }
        
        // 保存转卖数量
        async function saveTransferQuantity(recordId, shopId) {
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                showAlert('找不到要保存的记录', 'error');
                return;
            }
            
            const quantityInput = row.querySelector('.transfer-quantity-input');
            if (!quantityInput) {
                showAlert('找不到数量输入框', 'error');
                return;
            }
            
            const newQuantity = parseFloat(quantityInput.value) || 0;
            
            if (newQuantity <= 0) {
                showAlert('请输入有效的转卖数量', 'error');
                return;
            }
            
            try {
                // 获取当前记录
                const records = transferRecordsData[shopId] || [];
                const record = records.find(r => r.id == recordId);
                
                if (!record) {
                    showAlert('找不到记录数据', 'error');
                    return;
                }
                
                const unitPrice = record.unit_price || 0;
                const totalPrice = newQuantity * unitPrice;
                
                // 更新转卖记录
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_transfer_record',
                        id: recordId,
                        dishware_id: record.dishware_id,
                        to_shop_type: record.to_shop_type,
                        quantity: newQuantity,
                        unit_price: unitPrice,
                        total_price: totalPrice
                    })
                });
                
                if (result.success) {
                    showAlert('转卖记录更新成功', 'success');
                    // 更新原始值
                    quantityInput.dataset.originalValue = newQuantity;
                    // 恢复操作按钮
                    restoreTransferActionButtons(recordId, shopId);
                    // 刷新数据
                    loadAllTransferRecords();
                    // 刷新总库存
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('更新失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('保存转卖数量时发生错误:', error);
                showAlert('保存失败: ' + error.message, 'error');
            }
        }
        
        // 取消转卖数量修改
        function cancelTransferQuantity(recordId, shopId) {
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                return;
            }
            
            const quantityInput = row.querySelector('.transfer-quantity-input');
            if (quantityInput) {
                // 恢复原始值
                const originalValue = quantityInput.dataset.originalValue || quantityInput.value;
                quantityInput.value = originalValue;
            }
            
            // 恢复操作按钮
            restoreTransferActionButtons(recordId, shopId);
        }
        
        // 恢复转卖记录的操作按钮
        function restoreTransferActionButtons(recordId, shopId) {
            const actionCell = document.getElementById(`transfer-action-${recordId}`);
            if (!actionCell) return;
            
            // 如果有保存的原始HTML，恢复它
            if (actionCell.dataset.originalHtml) {
                actionCell.innerHTML = actionCell.dataset.originalHtml;
                delete actionCell.dataset.originalHtml;
            } else {
                // 否则使用默认的编辑和删除按钮
                actionCell.innerHTML = `
                    <button class="action-btn edit-btn" onclick="editTransferRecord(${recordId}, '${shopId}')" title="编辑">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteTransferRecord(${recordId}, '${shopId}')" title="删除">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }
        }

        // 编辑转卖记录 - 进入编辑模式
        function editTransferRecord(recordId, shopId) {
            console.log('editTransferRecord called:', recordId, shopId);
            
            // 找到对应的行
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                console.error('找不到行，recordId:', recordId, 'shopId:', shopId);
                showAlert('找不到要编辑的记录', 'error');
                return;
            }
            
            // 检查是否已经在编辑中
            if (row.classList.contains('editing-row')) {
                console.log('已经在编辑模式中');
                return;
            }
            
            // 检查是否是进货记录（不允许编辑）
            if (row.dataset.type === 'in') {
                showAlert('进货记录不允许编辑', 'error');
                return;
            }
            
            // 检查stockData是否已加载
            if (!stockData || stockData.length === 0) {
                showAlert('正在加载碗碟数据，请稍后再试', 'warning');
                loadStockData(true, false).then(() => {
                    setTimeout(() => {
                        editTransferRecord(recordId, shopId);
                    }, 500);
                });
                return;
            }
            
            try {
                // 标记为编辑中
                row.classList.add('editing-row');
                
                // 获取当前记录数据
                const cells = row.querySelectorAll('td');
                console.log('表格列数:', cells.length);
                if (cells.length < 7) {
                    console.error('表格列数不正确:', cells.length, '需要7列');
                    row.classList.remove('editing-row');
                    return;
                }
            
            // 保存原始数据
            const originalCode = cells[1].textContent.trim();
            const quantityEl = cells[2].querySelector('.quantity-input, .transfer-quantity-input') || cells[2].querySelector('span');
            const originalQuantity = quantityEl && (quantityEl.classList.contains('quantity-input') || quantityEl.classList.contains('transfer-quantity-input'))
                ? quantityEl.value
                : (quantityEl ? quantityEl.textContent.trim() : '0');
            const originalToShop = row.dataset.toShop || '';
            row.dataset.originalCode = originalCode;
            row.dataset.originalQuantity = originalQuantity;
            row.dataset.originalToShop = originalToShop;
            
            // 获取当前记录信息（从 transferRecordsData 中查找）
            const records = transferRecordsData[shopId] || [];
            const record = records.find(r => r.id == recordId);
            
            if (!record) {
                showAlert('找不到记录数据', 'error');
                row.classList.remove('editing-row');
                return;
            }
            
            // 生成编号选项（用于combobox）
            let codeOptions = [];
            if (stockData && stockData.length > 0) {
                stockData.forEach(item => {
                    const code = item.code_number || '';
                    if (code) {
                        codeOptions.push({
                            code: code,
                            id: item.id,
                            price: item.unit_price || 0
                        });
                    }
                });
            }
            
            // 找到当前编号对应的产品ID
            const currentProduct = stockData.find(item => item.code_number === originalCode);
            const currentProductId = currentProduct ? currentProduct.id : '';
            
            // 编辑编号列 - 使用 combobox
            const codeCell = cells[1];
            const codeRowId = `edit-transfer-${recordId}-${Date.now()}`;
            codeCell.innerHTML = `
                <div class="combobox-container" id="${codeRowId}-code-combo">
                    <input 
                        type="text" 
                        class="combobox-input break-code-input" 
                        id="${codeRowId}-code"
                        value="${originalCode}"
                        placeholder="输入或选择编号..."
                        autocomplete="off"
                        data-row-id="${codeRowId}"
                        data-field="code"
                        data-product-id="${currentProductId}"
                    />
                    <i class="fas fa-chevron-down combobox-arrow"></i>
                    <div class="combobox-dropdown" id="${codeRowId}-code-dropdown">
                        ${codeOptions.map(opt => `<div class="combobox-option" data-value="${opt.code}" data-id="${opt.id}" data-price="${opt.price}">${opt.code}</div>`).join('')}
                    </div>
                </div>
            `;
            
            // 数量列：使用 contenteditable span（直接编辑，不显示输入框）
            cells[2].innerHTML = `
                <span contenteditable="true" class="editable-quantity" 
                      id="${codeRowId}-qty"
                      style="display: inline-block; min-width: 40px; padding: 2px 4px; border: 1px solid #ccc; border-radius: 4px; background: #fff; outline: none; text-align: center;"
                      oninput="this.textContent = this.textContent.replace(/[^0-9.]/g, ''); calculateEditTransferTotal('${codeRowId}');">${originalQuantity}</span>
            `;
            
            // 编辑进出列 - 改为下拉列表
            const toCell = cells[3];
            let restaurantOptions = '';
            const jRestaurants = window.jRestaurantsForTransfer || restaurants.filter(r => {
                const name = r.name.toLowerCase();
                return name.startsWith('j') && name !== shopId;
            });
            const currentToShop = record.to_shop_type || record.to_shop_type || '';
            jRestaurants.forEach(r => {
                const rName = r.name.toLowerCase();
                restaurantOptions += `<option value="${rName}" ${rName === currentToShop ? 'selected' : ''} style="text-align: center;">${r.name}</option>`;
            });
            toCell.innerHTML = `
                <select class="transfer-to-select-edit" id="${codeRowId}-to" style="width: 100%; padding: 4px 8px; border: none; background: transparent; text-align: center; text-align-last: center; outline: none; font-size: clamp(8px, 0.74vw, 14px);">
                    <option value="" style="text-align: center;">选择餐厅</option>
                    ${restaurantOptions}
                </select>
            `;
            
            // 编辑单价列 - 只读显示（自动从产品信息获取）
            const priceCell = cells[4];
            const currentPrice = parseFloat(record.unit_price) || 0;
            const isOutRecordEdit = record.record_type === 'out';
            priceCell.innerHTML = `
                <div class="currency-display">
                    <span class="currency-symbol" style="color: ${isOutRecordEdit ? '#dc3545' : '#000000'};">${isOutRecordEdit ? '-' : ''}RM</span>
                    <span class="currency-amount" id="${codeRowId}-price" style="color: ${isOutRecordEdit ? '#dc3545' : '#000000'};">${currentPrice.toFixed(2)}</span>
                </div>
            `;
            
            // 总价列保持显示，但会动态更新
            const totalCell = cells[5];
            const currentTotal = parseFloat(record.total_price) || 0;
            totalCell.innerHTML = `
                <div class="currency-display">
                    <span class="currency-symbol" style="color: ${isOutRecordEdit ? '#dc3545' : '#000000'};">${isOutRecordEdit ? '-' : ''}RM</span>
                    <span class="currency-amount" id="${codeRowId}-total" style="color: ${isOutRecordEdit ? '#dc3545' : '#000000'};">${currentTotal.toFixed(2)}</span>
                </div>
            `;
            
            // 替换操作按钮为保存和取消
            console.log('准备替换操作按钮，cells.length:', cells.length);
            const actionCell = cells[6];
            if (!actionCell) {
                console.error('找不到操作列单元格，cells.length:', cells.length, 'cells:', cells);
                row.classList.remove('editing-row');
                return;
            }
            
            console.log('找到操作列单元格，当前内容:', actionCell.innerHTML);
            
            // 确保操作列有正确的ID
            if (!actionCell.id) {
                actionCell.id = `transfer-action-${recordId}`;
            }
            
            // 保存原始按钮HTML
            if (!actionCell.dataset.originalHtml) {
                actionCell.dataset.originalHtml = actionCell.innerHTML;
            }
            
            // 替换为保存和取消按钮
            const saveCancelButtons = `
                <button class="action-btn save-btn" onclick="saveEditTransferRecord(${recordId}, '${shopId}', '${codeRowId}')" title="保存" style="background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-right: 4px;">
                    <i class="fas fa-check"></i>
                </button>
                <button class="action-btn cancel-btn" onclick="cancelEditTransferRecord(${recordId}, '${shopId}')" title="取消" style="background: #6c757d; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            actionCell.innerHTML = saveCancelButtons;
            console.log('操作按钮已替换为保存和取消按钮，新内容:', actionCell.innerHTML);
            
            // 绑定 combobox 事件
            setTimeout(() => {
                bindBreakComboboxEvents(codeRowId);
            }, 100);
            
            // contenteditable span 的 input 事件已在 HTML 中通过 oninput 绑定
            } catch (error) {
                console.error('编辑转卖记录时发生错误:', error);
                row.classList.remove('editing-row');
                showAlert('编辑失败: ' + error.message, 'error');
            }
        }
        
        // 计算编辑转卖记录的总价
        function calculateEditTransferTotal(codeRowId) {
            const row = document.querySelector(`tr.editing-row`);
            if (!row) return;
            
            const quantitySpan = document.getElementById(`${codeRowId}-qty`);
            const priceSpan = document.getElementById(`${codeRowId}-price`);
            const totalSpan = document.getElementById(`${codeRowId}-total`);
            
            if (!quantitySpan || !priceSpan || !totalSpan) return;
            
            const quantity = parseFloat(quantitySpan.textContent.trim()) || 0;
            const price = parseFloat(priceSpan.textContent.trim()) || 0;
            const total = quantity * price;
            totalSpan.textContent = total.toFixed(2);
        }
        
        // 保存编辑的转卖记录
        async function saveEditTransferRecord(recordId, shopId, codeRowId) {
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                showAlert('找不到要保存的记录', 'error');
                return;
            }
            
            const cells = row.querySelectorAll('td');
            if (cells.length < 7) return;
            
            // 获取编辑后的值
            const codeInput = document.getElementById(`${codeRowId}-code`);
            const quantitySpan = document.getElementById(`${codeRowId}-qty`) || cells[2].querySelector('.editable-quantity');
            const toSelect = document.getElementById(`${codeRowId}-to`);
            
            if (!codeInput || !quantitySpan || !toSelect) {
                showAlert('找不到输入元素', 'error');
                return;
            }
            
            const newCode = codeInput.value.trim();
            const productId = codeInput.dataset.productId || codeInput.getAttribute('data-product-id');
            const newQuantity = parseFloat(quantitySpan.textContent.trim()) || 0;
            const newToShopType = toSelect.value;
            
            // 验证
            if (!newCode || !productId) {
                showAlert('请输入或选择编号', 'error');
                return;
            }
            
            if (!newToShopType) {
                showAlert('请选择转卖给哪间餐厅', 'error');
                return;
            }
            
            if (newQuantity <= 0) {
                showAlert('请输入有效的转卖数量', 'error');
                return;
            }
            
            try {
                // 获取当前记录以获取单价
                const records = transferRecordsData[shopId] || [];
                const record = records.find(r => r.id == recordId);
                
                if (!record) {
                    showAlert('找不到记录数据', 'error');
                    return;
                }
                
                // 单价始终从产品信息中自动获取
                const product = stockData.find(item => item.id == productId || item.code_number === newCode);
                if (!product) {
                    showAlert('找不到产品信息', 'error');
                    return;
                }
                
                const unitPrice = product.unit_price || 0;
                const totalPrice = newQuantity * unitPrice;
                
                // 更新转卖记录
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_transfer_record',
                        id: recordId,
                        dishware_id: productId,
                        to_shop_type: newToShopType,
                        quantity: newQuantity,
                        unit_price: unitPrice,
                        total_price: totalPrice
                    })
                });
                
                if (result.success) {
                    showAlert('转卖记录更新成功', 'success');
                    // 退出编辑模式并刷新数据
                    row.classList.remove('editing-row');
                    loadAllTransferRecords();
                    // 刷新总库存
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('更新失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('保存编辑的转卖记录时发生错误:', error);
                showAlert('保存失败: ' + error.message, 'error');
            }
        }
        
        // 取消编辑转卖记录
        function cancelEditTransferRecord(recordId, shopId) {
            const row = document.querySelector(`tr[data-id="${recordId}"][data-shop="${shopId}"]`);
            if (!row) {
                return;
            }
            
            // 退出编辑模式
            row.classList.remove('editing-row');
            
            // 重新渲染该行（恢复到原始状态）
            const records = transferRecordsData[shopId] || [];
            const record = records.find(r => r.id == recordId);
            
            if (record) {
                // 找到该行在数组中的索引
                const index = records.findIndex(r => r.id == recordId);
                if (index !== -1) {
                    // 重新渲染该行
                    const tbody = row.parentElement;
                    const isOutRecord = record.record_type === 'out';
                    // 进出列：只显示餐厅名称，不显示"转给"或"来自"
                    const transferDirection = isOutRecord 
                        ? (record.to_restaurant_name || record.to_shop_type.toUpperCase()) 
                        : (record.from_restaurant_name || record.from_shop_type.toUpperCase());
                    
                    // 根据记录类型设置价格颜色：转卖（out）=红色，来自（in）=绿色
                    const priceColor = isOutRecord ? '#dc3545' : '#000000';
                    
                    const newRow = document.createElement('tr');
                    newRow.setAttribute('data-id', record.id);
                    newRow.setAttribute('data-shop', shopId);
                    newRow.setAttribute('data-type', record.record_type);
                    newRow.setAttribute('data-related', record.related_record_id || '');
                    newRow.innerHTML = `
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${record.code_number || '-'}</td>
                        <td class="text-center"><span>${record.quantity}</span></td>
                        <td class="text-center">${transferDirection}</td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol" style="color: ${priceColor};">${isOutRecord ? '-' : ''}RM</span>
                                <span class="currency-amount" style="color: ${priceColor};">${formatCurrency(record.unit_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol" style="color: ${priceColor};">${isOutRecord ? '-' : ''}RM</span>
                                <span class="currency-amount" style="color: ${priceColor};">${formatCurrency(record.total_price || 0)}</span>
                            </div>
                        </td>
                        <td class="text-center" id="transfer-action-${record.id}">
                            ${isOutRecord ? 
                                `
                                <button class="action-btn edit-btn" onclick="editTransferRecord(${record.id}, '${shopId}')" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteTransferRecord(${record.id}, '${shopId}')" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>
                                ` :
                                '<span style="color: #6b7280; font-size: 12px;">自动生成</span>'
                            }
                        </td>
                    `;
                    row.replaceWith(newRow);
                }
            }
        }

        // 删除转卖记录
        async function deleteTransferRecord(recordId, shopId) {
            if (!confirm('确定要删除此转卖记录吗？此操作不可恢复！')) return;
            
            try {
                const result = await apiCall('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_transfer_record',
                        id: recordId
                    })
                });
                
                if (result.success) {
                    showAlert('转卖记录删除成功', 'success');
                    loadAllTransferRecords();
                    if (document.getElementById('stock-table')) {
                        loadStockData(true, false);
                    }
                } else {
                    showAlert('删除失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('删除转卖记录时发生错误:', error);
                showAlert('删除转卖记录失败: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
