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
            overflow-y: hidden;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            height: 100vh;
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
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
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
            width: clamp(30px, 3.13vw, 60px);
            height: clamp(30px, 3.13vw, 60px);
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
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            text-align: center;
            font-size: 12px;
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
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
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

        /* 小屏：表头在左侧竖排（用 td[data-label] 显示字段名） */
        @media (max-width: 900px) {
            .table-scroll-container {
                overflow-x: hidden;
            }

            .stock-table {
                min-width: 0;
                table-layout: auto;
            }

            .stock-table thead {
                display: none;
            }

            .stock-table,
            .stock-table tbody,
            .stock-table tr {
                display: block;
                width: 100%;
            }

            .stock-table tr {
                margin: 10px 10px 12px;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                overflow: hidden;
                background: white;
            }

            .stock-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                text-align: right;
                border: none;
                border-bottom: 1px solid #f1f1f1;
                padding: 10px 12px;
            }

            .stock-table td::before {
                content: attr(data-label);
                flex: 0 0 auto;
                color: #583e04;
                font-weight: 700;
                text-align: left;
                white-space: nowrap;
            }

            .stock-table td:last-child {
                border-bottom: none;
            }

            /* 空数据/加载行（colspan）避免被 flex 撑坏 */
            .stock-table td[colspan] {
                display: block !important;
                text-align: center !important;
            }

            .stock-table td[colspan]::before {
                content: '';
                display: none;
            }

            .product-photo,
            .no-photo {
                margin: 0;
            }

            .action-btn {
                width: 34px;
                height: 34px;
            }
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
                        <div class="dropdown-item" onclick="switchPage('sets')">套装管理</div>
                        <div class="dropdown-item" onclick="switchPage('j1')">J1破损</div>
                        <div class="dropdown-item" onclick="switchPage('j2')">J2破损</div>
                        <div class="dropdown-item" onclick="switchPage('j3')">J3破损</div>
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
        <div class="table-container">
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
            </div>

            <!-- 套装管理页面 -->
            <div id="sets-page" class="page-content" style="display: none;">
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

            <!-- J1 打破记录页面 -->
            <div id="j1-page" class="page-content" style="display: none;">
                <div class="table-container">
                    <div class="table-scroll-container">
                        <table class="stock-table" id="j1-table">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th>No.</th>
                                    <th>产品名称</th>
                                    <th>编号</th>
                                    <th>分类</th>
                                    <th>尺寸</th>
                                    <th>当前库存</th>
                                    <th>破损数量</th>
                                    <th>单价</th>
                                    <th>总价</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="j1-tbody">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- J2 打破记录页面 -->
            <div id="j2-page" class="page-content" style="display: none;">
                <div class="table-container">
                    <div class="table-scroll-container">
                        <table class="stock-table" id="j2-table">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th>No.</th>
                                    <th>产品名称</th>
                                    <th>编号</th>
                                    <th>分类</th>
                                    <th>尺寸</th>
                                    <th>当前库存</th>
                                    <th>破损数量</th>
                                    <th>单价</th>
                                    <th>总价</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="j2-tbody">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- J3 打破记录页面 -->
            <div id="j3-page" class="page-content" style="display: none;">
                <div class="table-container">
                    <div class="table-scroll-container">
                        <table class="stock-table" id="j3-table">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th>No.</th>
                                    <th>产品名称</th>
                                    <th>编号</th>
                                    <th>分类</th>
                                    <th>尺寸</th>
                                    <th>当前库存</th>
                                    <th>破损数量</th>
                                    <th>单价</th>
                                    <th>总价</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="j3-tbody">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
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
                        <div class="quantity-row">
                            <div class="quantity-field">
                                <label>文化楼数量</label>
                                <input type="number" id="edit-wenhua" min="0" class="quantity-input">
                            </div>
                            <div class="quantity-field">
                                <label>中央数量</label>
                                <input type="number" id="edit-central" min="0" class="quantity-input">
                            </div>
                            <div class="quantity-field">
                                <label>J1数量</label>
                                <input type="number" id="edit-j1" min="0" class="quantity-input">
                            </div>
                            <div class="quantity-field">
                                <label>J2数量</label>
                                <input type="number" id="edit-j2" min="0" class="quantity-input">
                            </div>
                            <div class="quantity-field">
                                <label>J3数量</label>
                                <input type="number" id="edit-j3" min="0" class="quantity-input">
                            </div>
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
        let breakRecordsData = {
            j1: [],
            j2: [],
            j3: []
        }; // 存储各店铺的破损记录数据

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
        function initApp() {
            loadStockData();
            setupEventListeners();
            setupRealTimeSearch();
            setupPageSwitcher();
            setupSetFormSubmit();
            
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
                'sets': '套装管理',
                'j1': 'J1破损',
                'j2': 'J2破损',
                'j3': 'J3破损'
            };
            
            if (currentView) {
                currentView.textContent = pageNames[pageType];
            }
            
            // 更新active状态
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`.dropdown-item[onclick*="'${pageType}'"]`).classList.add('active');
            
            // 隐藏所有页面
            const pages = document.querySelectorAll('.page-content');
            pages.forEach(page => {
                page.style.display = 'none';
            });
            
            // 显示当前页面
            const currentPageElement = document.getElementById(`${pageType}-page`);
            if (currentPageElement) {
                currentPageElement.style.display = 'block';
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
                    if (addButton) {
                        addButton.innerHTML = '<i class="fas fa-plus"></i> 添加碗碟';
                        addButton.onclick = () => openAddModal();
                        addButton.style.display = 'inline-flex';
                    }
                    break;
                case 'sets':
                    if (title) title.textContent = '套装管理';
                    if (addButton) {
                        addButton.innerHTML = '<i class="fas fa-plus"></i> 添加套装';
                        addButton.onclick = () => openSetModal();
                        addButton.style.display = 'inline-flex';
                    }
                    break;
                case 'j1':
                    if (title) title.textContent = 'J1破损';
                    if (addButton) {
                        addButton.innerHTML = '<i class="fas fa-plus"></i> 记录破损';
                        addButton.onclick = () => openBreakModal('j1');
                        addButton.style.display = 'inline-flex';
                    }
                    break;
                case 'j2':
                    if (title) title.textContent = 'J2破损';
                    if (addButton) {
                        addButton.innerHTML = '<i class="fas fa-plus"></i> 记录破损';
                        addButton.onclick = () => openBreakModal('j2');
                        addButton.style.display = 'inline-flex';
                    }
                    break;
                case 'j3':
                    if (title) title.textContent = 'J3破损';
                    if (addButton) {
                        addButton.innerHTML = '<i class="fas fa-plus"></i> 记录破损';
                        addButton.onclick = () => openBreakModal('j3');
                        addButton.style.display = 'inline-flex';
                    }
                    break;
            }
        }

        // 加载页面数据
        function loadPageData(pageType) {
            switch(pageType) {
                case 'stock':
                    loadStockData();
                    break;
                case 'sets':
                    loadSetsData();
                    break;
                case 'j1':
                case 'j2':
                case 'j3':
                    loadBreakRecords(pageType);
                    break;
            }
        }

        // 加载打破记录
        async function loadBreakRecords(shopType) {
            console.log('loadBreakRecords 被调用，shopType:', shopType);
            try {
                const result = await apiCall(`?action=damage_records&shop_type=${shopType}`);
                
                if (result.success) {
                    const records = result.data || [];
                    console.log('成功获取破损记录，数量:', records.length);
                    // 存储破损记录数据
                    breakRecordsData[shopType] = records;
                    renderBreakRecordsTable(shopType, records);
                    updateStats();
                } else {
                    console.error('获取破损记录失败:', result.message);
                    showAlert('获取破损记录失败: ' + (result.message || '未知错误'), 'error');
                    renderBreakRecordsTable(shopType, []);
                }
                
            } catch (error) {
                console.error('加载破损记录时发生错误:', error);
                showAlert('加载破损记录失败: ' + error.message, 'error');
                renderBreakRecordsTable(shopType, []);
            }
        }

        // 渲染破损记录表格
        function renderBreakRecordsTable(shopType, records) {
            console.log('renderBreakRecordsTable 被调用，shopType:', shopType, 'records数量:', records.length);
            const tbody = document.getElementById(`${shopType}-tbody`);
            if (!tbody) {
                console.error('找不到tbody元素:', `${shopType}-tbody`);
                return;
            }
            
            if (records.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无破损记录</div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            let tableRows = '';
            
            records.forEach((record, index) => {
                // 照片显示
                tableRows += `
                    <tr data-id="${record.id}">
                        <td data-label="日期" class="text-center">${formatDate(record.break_date || record.created_at)}</td>
                        <td data-label="NO" class="text-center">${index + 1}</td>
                        <td data-label="产品名称"><strong>${record.product_name}</strong></td>
                        <td data-label="编号" class="text-center">${record.code_number || '-'}</td>
                        <td data-label="分类" class="text-center">${record.category}</td>
                        <td data-label="尺寸" class="text-center">${record.size || '-'}</td>
                        <td data-label="当前库存" class="text-center">
                            <input type="number" class="quantity-input" 
                                   value="${record.current_stock || 0}" 
                                   readonly style="background: #f3f4f6;">
                        </td>
                        <td data-label="破损数量" class="text-center">
                            <input type="number" class="quantity-input" 
                                   value="${record.break_quantity}" 
                                   onchange="updateBreakQuantity(${record.id}, this.value)"
                                   min="0">
                        </td>
                        <td data-label="单价" class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${formatCurrency(record.unit_price || 0)}</span>
                            </div>
                        </td>
                        <td data-label="总价" class="text-center">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${formatCurrency(record.total_price || 0)}</span>
                            </div>
                        </td>
                        <td data-label="操作" class="text-center">
                            <button class="action-btn edit-btn" onclick="editBreakRecord(${record.id})" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteBreakRecord(${record.id})" title="删除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = tableRows;
            console.log('破损记录表格已更新，行数:', records.length);
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
                        loadBreakRecords(currentPage);
                    } else {
                        console.warn('未知的页面类型:', currentPage);
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
        async function updateBreakQuantity(recordId, newQuantity) {
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
                        // 如果在破损记录页面，刷新破损记录数据
                        loadBreakRecords(currentPage);
                    }
                } else {
                    showAlert('更新失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('更新破损数量时发生错误:', error);
                showAlert('更新破损数量失败: ' + error.message, 'error');
            }
        }

        // 编辑破损记录
        function editBreakRecord(recordId) {
            // 这里可以实现编辑破损记录的模态框
            showAlert('编辑功能待实现', 'info');
        }

        // 删除破损记录
        async function deleteBreakRecord(recordId) {
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
                        // 如果在破损记录页面，刷新破损记录数据
                        loadBreakRecords(currentPage);
                    }
                } else {
                    showAlert('删除失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('删除破损记录时发生错误:', error);
                showAlert('删除破损记录失败: ' + error.message, 'error');
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
                        searchData();
                    }, 300); // 300ms延迟
                });
            }
            
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    searchData();
                });
            }
        }



        // API 调用函数
        async function apiCall(endpoint, options = {}) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
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
            smoothUpdateTable();
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
             } else if (currentPage === 'sets') {
                 // 套装管理页面使用套装数据
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

        // 渲染库存表格
        function renderStockTable() {
            const tbody = document.getElementById('stock-tbody');
            
            if (filteredData.length === 0) {
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
            
            // 调试信息：检查套装数据
            const setsInData = filteredData.filter(item => item.item_type === 'set');
            console.log('渲染表格 - 套装数量:', setsInData.length);
            setsInData.forEach(set => {
                console.log('套装数据:', {
                    id: set.id,
                    code_number: set.code_number,
                    product_name: set.product_name,
                    items_count: set.items ? set.items.length : 0,
                    has_items: !!(set.items && set.items.length > 0)
                });
            });
            
            let tableRows = '';
            let rowIndex = 1;
            
            filteredData.forEach((item) => {
                if (item.item_type === 'set') {
                    const set = item;
                    const setPrice = typeof set.set_price !== 'undefined' ? set.set_price : set.unit_price || 0;
                    const displayIndex = rowIndex++;
                    
                    // 检查套装是否有items且items不为空
                    if (set.items && Array.isArray(set.items) && set.items.length > 0) {
                        // 为套装中的每个item创建一行
                        set.items.forEach((setItem, itemIndex) => {
                            const itemStockQuantity = parseInt(setItem.total_quantity) || 0;
                            const itemStockClass = itemStockQuantity > 0 ? 'positive-value' : 'zero-value';
                            
                            const photoHtml = setItem.photo_path ?
                                `<img src="${setItem.photo_path}" alt="${setItem.product_name || ''}" class="product-photo">` :
                                `<div class="no-photo"><i class="fas fa-image"></i></div>`;
                            
                            tableRows += `
                                <tr data-id="${set.id}" data-type="set" data-item-id="${setItem.id}">
                                    ${itemIndex === 0 ? `<td data-label="NO" class="text-center set-shared-cell" rowspan="${set.items.length}">${displayIndex}</td>` : ''}
                                    <td data-label="照片" class="text-center">${photoHtml}</td>
                                    <td data-label="产品名称"><strong>${setItem.product_name || '-'}</strong></td>
                                    <td data-label="编号" class="text-center">${setItem.code_number || '-'}</td>
                                    <td data-label="分类" class="text-center">${setItem.category || set.category || '-'}</td>
                                    <td data-label="尺寸" class="text-center">${setItem.size || '-'}</td>
                                    ${itemIndex === 0 ? `
                                        <td data-label="单价" class="text-center set-shared-cell" rowspan="${set.items.length}">
                                            <div class="currency-display">
                                                <span class="currency-symbol">RM</span>
                                                <span class="currency-amount">${formatCurrency(setPrice)}</span>
                                            </div>
                                        </td>
                                    ` : ''}
                                    <td data-label="文化楼" class="text-center">${setItem.wenhua_quantity || 0}</td>
                                    <td data-label="中央" class="text-center">${setItem.central_quantity || 0}</td>
                                    <td data-label="J1" class="text-center">${setItem.j1_quantity || 0}</td>
                                    <td data-label="J2" class="text-center">${setItem.j2_quantity || 0}</td>
                                    <td data-label="J3" class="text-center">${setItem.j3_quantity || 0}</td>
                                    <td data-label="总数" class="text-center ${itemStockClass}">${setItem.total_quantity || 0}</td>
                                    <td data-label="操作" class="text-center">
                                        <button class="action-btn edit-btn" onclick="openEditModal(${setItem.id})" title="编辑碗碟">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn" onclick="deleteDishwareFromSet(${setItem.id}, ${set.id})" title="删除碗碟">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        // 套装没有items或items为空，显示套装基本信息
                        // 使用套装自己的库存信息（如果存在）
                        let wenhuaQuantity = parseInt(set.wenhua_quantity) || 0;
                        let centralQuantity = parseInt(set.central_quantity) || 0;
                        let j1Quantity = parseInt(set.j1_quantity) || 0;
                        let j2Quantity = parseInt(set.j2_quantity) || 0;
                        let j3Quantity = parseInt(set.j3_quantity) || 0;
                        let totalQuantity = parseInt(set.total_quantity) || (wenhuaQuantity + centralQuantity + j1Quantity + j2Quantity + j3Quantity);
                        
                        const stockClass = totalQuantity > 0 ? 'positive-value' : 'zero-value';
                        const photoHtml = set.photo_path ?
                            `<img src="${set.photo_path}" alt="${set.product_name || ''}" class="product-photo">` :
                            `<div class="no-photo"><i class="fas fa-box"></i></div>`;
                        
                        tableRows += `
                            <tr data-id="${set.id}" data-type="set">
                                <td data-label="NO" class="text-center">${displayIndex}</td>
                                <td data-label="照片" class="text-center">${photoHtml}</td>
                                <td data-label="产品名称"><strong>${set.product_name || set.set_name || '-'}</strong></td>
                                <td data-label="编号" class="text-center">${set.code_number || set.set_code || '-'}</td>
                                <td data-label="分类" class="text-center">${set.category || 'SET'}</td>
                                <td data-label="尺寸" class="text-center">-</td>
                                <td data-label="单价" class="text-center">
                                    <div class="currency-display">
                                        <span class="currency-symbol">RM</span>
                                        <span class="currency-amount">${formatCurrency(setPrice)}</span>
                                    </div>
                                </td>
                                <td data-label="文化楼" class="text-center">${wenhuaQuantity}</td>
                                <td data-label="中央" class="text-center">${centralQuantity}</td>
                                <td data-label="J1" class="text-center">${j1Quantity}</td>
                                <td data-label="J2" class="text-center">${j2Quantity}</td>
                                <td data-label="J3" class="text-center">${j3Quantity}</td>
                                <td data-label="总数" class="text-center ${stockClass}">${totalQuantity}</td>
                                <td data-label="操作" class="text-center">
                                    <button class="action-btn edit-btn" onclick="editSet(${set.id})" title="编辑套装">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteSet(${set.id})" title="删除套装">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                } else {
                    const stockQuantity = parseInt(item.total_quantity) || 0;
                    const stockClass = stockQuantity > 0 ? 'positive-value' : 'zero-value';
                    const photoHtml = item.photo_path ? 
                        `<img src="${item.photo_path}" alt="${item.product_name}" class="product-photo">` :
                        `<div class="no-photo"><i class="fas fa-image"></i></div>`;
                    
                    tableRows += `
                        <tr data-id="${item.id}" data-type="individual">
                            <td data-label="NO" class="text-center">${rowIndex++}</td>
                            <td data-label="照片" class="text-center">${photoHtml}</td>
                            <td data-label="产品名称"><strong>${item.product_name}</strong></td>
                            <td data-label="编号" class="text-center">${item.code_number || '-'}</td>
                            <td data-label="分类" class="text-center">${item.category}</td>
                            <td data-label="尺寸" class="text-center">${item.size || '-'}</td>
                            <td data-label="单价" class="text-center">
                                <div class="currency-display">
                                    <span class="currency-symbol">RM</span>
                                    <span class="currency-amount">${formatCurrency(item.unit_price)}</span>
                                </div>
                            </td>
                            <td data-label="文化楼" class="text-center">${item.wenhua_quantity || 0}</td>
                            <td data-label="中央" class="text-center">${item.central_quantity || 0}</td>
                            <td data-label="J1" class="text-center">${item.j1_quantity || 0}</td>
                            <td data-label="J2" class="text-center">${item.j2_quantity || 0}</td>
                            <td data-label="J3" class="text-center">${item.j3_quantity || 0}</td>
                            <td data-label="总数" class="text-center ${stockClass}">${item.total_quantity || 0}</td>
                            <td data-label="操作" class="text-center">
                                <button class="action-btn edit-btn" onclick="openEditModal(${item.id})" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteDishware(${item.id})" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }
            });
            
            tbody.innerHTML = tableRows;
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
            document.getElementById('edit-wenhua').value = item.wenhua_quantity || 0;
            document.getElementById('edit-central').value = item.central_quantity || 0;
            document.getElementById('edit-j1').value = item.j1_quantity || 0;
            document.getElementById('edit-j2').value = item.j2_quantity || 0;
            document.getElementById('edit-j3').value = item.j3_quantity || 0;
            
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
            console.log('closeModal 函数被调用');
            try {
                const modals = ['editModal', 'addModal', 'damageModal', 'setModal'];
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
            formData.append('wenhua_quantity', form.querySelector('#edit-wenhua').value);
            formData.append('central_quantity', form.querySelector('#edit-central').value);
            formData.append('j1_quantity', form.querySelector('#edit-j1').value);
            formData.append('j2_quantity', form.querySelector('#edit-j2').value);
            formData.append('j3_quantity', form.querySelector('#edit-j3').value);
            
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
                        wenhua_quantity: parseInt(form.querySelector('#edit-wenhua').value) || 0,
                        central_quantity: parseInt(form.querySelector('#edit-central').value) || 0,
                        j1_quantity: parseInt(form.querySelector('#edit-j1').value) || 0,
                        j2_quantity: parseInt(form.querySelector('#edit-j2').value) || 0,
                        j3_quantity: parseInt(form.querySelector('#edit-j3').value) || 0
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
                const modals = ['editModal', 'addModal', 'damageModal', 'setModal'];
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
            
            // 处理模态框关闭
            if (event.target == editModal || event.target == addModal || event.target == damageModal || event.target == setModal) {
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
                const result = await apiCall('?action=sets');
                
                if (result.success) {
                    setsData = result.data || [];
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
            
            if (setsData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="12" class="no-data">暂无套装数据</td>
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
            
            let tableRows = '';
            sortedSetsData.forEach((set, index) => {
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
                        <td data-label="文华楼">0</td>
                        <td data-label="中央">0</td>
                        <td data-label="J1">0</td>
                        <td data-label="J2">0</td>
                        <td data-label="J3">0</td>
                        <td data-label="总库存">0</td>
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
                        <td colspan="12">
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
                    loadSetsData();
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
                    loadSetsData();
                } else {
                    showAlert((data.action === 'add_set' ? '添加' : '更新') + '失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('保存套装时发生错误:', error);
                showAlert('保存套装失败: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
