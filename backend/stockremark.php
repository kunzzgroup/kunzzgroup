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
    <title>库存价格分析 - 库存管理系统</title>
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
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            background-attachment: fixed;
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
            gap: 0px;
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

        /* Alert Messages */
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

        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* 搜索和过滤区域 */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: clamp(10px, 1.15vw, 22px) clamp(18px, 1.25vw, 24px);
            margin-bottom: clamp(14px, 1.25vw, 24px);
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            color: #000000ff;
        }

        .filter-input, .filter-select {
            width: clamp(140px, 13.02vw, 250px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border: 1px solid #d1d5db;
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(8px, 0.74vw, 14px);
            background: white;
            color: #583e04;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #000000ff;
            box-shadow: 0 0 0 3px rgba(39, 27, 0, 0.1);
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
            background-color: #f99e00;
            color: white;
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

        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
            transform: translateY(-1px);
        }

        /* 货品组容器 - 始终保持4列 */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(14px, 1.25vw, 24px);
            margin-bottom: clamp(14px, 1.25vw, 24px);
            min-width: 0; /* 允许内容缩小 */
        }

        /* 货品组显示 */
        .product-group {
            background: white;
            border-radius: 12px;
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            overflow: hidden;
            display: flex;
            min-width: 0; /* 允许内容缩小 */
            height: clamp(220px, 19.17vw, 368px);
        }

        .product-header {
            background: #f99e00;
            color: white;
            padding: 2px;
            font-size: clamp(9px, 0.94vw, 18px);
            font-weight: 600;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            width: 35%;
            gap: 12px;
        }

        .product-header .price-count {
            font-size: 11px;
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
        }

        .product-table-container {
            width: 65%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .table-wrapper {
            overflow-y: auto;
            overflow-x: hidden;
            max-height: calc(10 * clamp(32px, 2.6vw, 50px) + clamp(32px, 2.6vw, 50px)); /* 10行数据 + 1行表头 */
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .product-info-item {
            margin-bottom: 8px;
        }

        .product-info-item:last-child {
            margin-bottom: 0;
        }

        .price-variants-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            table-layout: fixed;
        }

        /* 固定表头 */
        .table-wrapper thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f5eb;
        }

        .price-variants-table th {
            background: #f8f5eb;
            color: #000000ff;
            font-size: clamp(8px, 0.625vw, 12px);
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #887d66ff;
        }

        .price-variants-table td {
            font-size: clamp(7px, 0.625vw, 12px);
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }

        .price-variants-table th,
        .price-variants-table td {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-bottom: 1px solid #dbdbdb;
            text-align: center;
            vertical-align: middle;
        }

        .price-variants-table th:nth-child(1),
        .price-variants-table td:nth-child(1) { 
            width: 46%; 
            text-align: center;
        }

        .price-variants-table th:nth-child(2),
        .price-variants-table td:nth-child(2) { 
            width: 55%; 
            text-align: center;
        }

        .price-variants-table tr:hover {
            background-color: #f9fafb;
        }

        /* 价格差异高亮 */
        .highest-price {
            background-color: #fef3c7 !important;
            font-weight: 600;
        }

        .price-difference {
            font-size: 12px;
            color: #dc2626;
            font-weight: 500;
        }

        /* 货币显示 */
        .currency-display {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .currency-symbol {
            color: #6b7280;
            font-weight: 500;
        }

        .currency-amount {
            font-weight: 600;
            color: #583e04;
        }

        .highest-price .currency-amount {
            color: #dc2626;
            font-weight: 700;
        }

        /* 统计信息 */
        .stats-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }

        .stat-card {
            text-align: center;
            padding: 16px;
            background: #f8f5eb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #583e04;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

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

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            font-style: italic;
        }

        .no-data i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .no-data h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #374151;
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
            width: clamp(90px, 6.77vw, 130px);
            justify-content: space-between;
            position: relative; /* 添加这个，因为下拉菜单需要 */
        }

        .selector-button:hover {
            background-color: #f98500ff;
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
            text-decoration: none;
            display: block;
        }

        .selector-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }

        .selector-dropdown .dropdown-item:hover {
            background-color: #f8f5eb;
            border-radius: 8px;
        }

        .selector-dropdown .dropdown-item.active {
            background-color: #f99e00 !important;
            color: white !important;
        }

        /* 视图选择器样式 */
        .view-selector {
            position: relative;
            margin-right: 16px;
        }

        /* .view-selector .selector-button {
            background-color: #583e04;
            min-width: 120px;
        } */

        .view-selector .selector-button:hover {
            background-color: #f98500ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .view-selector .selector-dropdown {
            width: 100%;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .filter-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            /* 保持4列，不改变列数 */

            .product-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .price-variants-table {
                font-size: 12px;
            }

            .price-variants-table th,
            .price-variants-table td {
                width: auto;
                min-width: 80px;
                padding: 8px 4px;
                word-wrap: break-word;
                overflow-wrap: break-word;
                white-space: nowrap; /* 防止换行 */
            }
        }

        /* 排序指示器 */
        .sort-indicator {
            margin-left: 8px;
            opacity: 0.5;
        }

        .sort-indicator.active {
            opacity: 1;
            color: #583e04;
        }

        .product-title {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .product-code {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }

        .product-name {
            font-size: 18px;
            font-weight: 600;
        }

        /* 回到顶部按钮 */
        .back-to-top {
            position: fixed;
            bottom: clamp(16px, 1.56vw, 30px);
            right: clamp(12px, 1.04vw, 20px);
            width: clamp(36px, 2.6vw, 50px);
            height: clamp(36px, 2.6vw, 50px);
            background-color: #f99e00;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(12px, 0.94vw, 18px);
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.3);
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            z-index: 1000;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background-color: #f98500ff;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(88, 62, 4, 0.4);
        }

        .back-to-top:active {
            transform: translateY(-1px);
        }

        .total-quantity {
            margin-left: 25px;
            color: #059669;
            font-weight: 600;
            background-color: #ecfdf5;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.9em;
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>货品备注</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">货品备注</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item" onclick="switchView('list')">总库存</div>
                        <div class="dropdown-item" onclick="switchView('records')">进出货</div>
                        <div class="dropdown-item active" onclick="switchView('remark')">货品备注</div>
                        <div class="dropdown-item" onclick="switchView('product')">货品种类</div>
                        <div class="dropdown-item" onclick="switchView('sot')">货品异常</div>
                    </div>
                </div>
                <button class="selector-button" style="justify-content: center;">
                    <span id="current-stock-type">中央</span>
                </button>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 搜索和过滤区域 -->
        <div class="filter-section">
            <div style="display: flex; align-items: end; gap: 26px; margin-bottom: clamp(10px, 0.83vw, 16px);">
                <div class="filter-group" style="flex: 1;">
                    <label for="product-filter">搜索货品</label>
                    <input type="text" id="product-filter" class="filter-input" placeholder="输入关键字搜索...">
                </div>
            </div>
        </div>

        <!-- 货品列表 -->
        <div id="products-container">
            <!-- Dynamic content -->
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    <!-- 动态通知内容 -->
    </div>

    <!-- 回到顶部按钮 -->
    <button class="back-to-top" id="back-to-top-btn" onclick="scrollToTop()" title="回到顶部">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
        // API 配置
        let API_BASE_URL = 'stockremarkapi.php';
        const STOCK_VIEW_OPTIONS = [
            { value: 'list', label: '总库存' },
            { value: 'records', label: '进出货' },
            { value: 'remark', label: '货品备注' },
            { value: 'product', label: '货品种类' },
            { value: 'sot', label: '货品异常' }
        ];
        let cachedRemarkAllowedViews = new Set();

        // 应用状态
        let stockData = [];
        let filteredData = [];
        let isLoading = false;

        // 智能格式化数量函数
        function formatQuantity(number) {
            const num = parseFloat(number);
            
            // 如果是整数，不显示小数点
            if (Math.floor(num) === num) {
                return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
            }
            
            // 检查原始精度，最多3位小数
            const decimalPart = num - Math.floor(num);
            
            if (Math.round(decimalPart * 10) / 10 === Math.round(decimalPart * 1000) / 1000) {
                // 只有1位有效小数
                return num.toLocaleString('en-US', {minimumFractionDigits: 1, maximumFractionDigits: 1});
            } else if (Math.round(decimalPart * 100) / 100 === Math.round(decimalPart * 1000) / 1000) {
                // 有2位有效小数
                return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                // 有3位有效小数
                return num.toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3});
            }
        }

        function rebuildRemarkViewDropdown(allowedSet) {
            const dropdown = document.getElementById('view-selector-dropdown');
            if (!dropdown) return;
            const options = (allowedSet && allowedSet.size > 0)
                ? STOCK_VIEW_OPTIONS.filter(opt => allowedSet.has(opt.value))
                : STOCK_VIEW_OPTIONS.slice();
            if (options.length === 0) {
                options.push(STOCK_VIEW_OPTIONS[0]);
            }
            dropdown.innerHTML = '';
            options.forEach(opt => {
                const item = document.createElement('div');
                item.className = 'dropdown-item' + (opt.value === 'remark' ? ' active' : '');
                item.dataset.viewValue = opt.value;
                item.textContent = opt.label;
                item.onclick = function() { switchView(opt.value); };
                dropdown.appendChild(item);
            });
        }

        async function applyPagePermissions() {
            try {
                const res = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_page_permissions' })
                });
                const data = await res.json();
                if (!data.success) return;
                const perms = data.page_permissions || {};
                const current = perms.stock_inventory || {};
                const allowedViews = new Set(current.view || []);
                cachedRemarkAllowedViews = new Set(allowedViews);
                rebuildRemarkViewDropdown(allowedViews);
                if (allowedViews.size > 0 && !allowedViews.has('remark')) {
                    const viewOrder = ['remark', 'records', 'product', 'sot', 'list'];
                    const viewRedirectMap = {
                        list: 'stocklistall.php',
                        records: 'stockeditall.php',
                        remark: 'stockremark.php',
                        product: 'stockproductname.php',
                        sot: 'stocksot.php'
                    };
                    const viewToOpen = viewOrder.find(view => allowedViews.has(view));
                    if (viewToOpen) {
                        const base = viewRedirectMap[viewToOpen] || 'stocklistall.php';
                        window.location.href = base;
                    }
                }
            } catch (e) {
                // ignore
            }
        }

        // 初始化应用
        async function initApp() {
            await applyPagePermissions();
            loadStockRemarks();
            initRealTimeSearch();
        }
        
        // 初始化实时搜索
        function initRealTimeSearch() {
            const productFilter = document.getElementById('product-filter');
            
            // 防抖函数
            let debounceTimer;
            
            productFilter.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    searchData();
                }, 300); // 300ms延迟
            });
        }

        // 切换视图选择器下拉菜单
        function toggleViewSelector() {
            const dropdown = document.getElementById('view-selector-dropdown');
            dropdown.classList.toggle('show');
        }

        function switchView(viewType) {
            if (viewType === 'list') {
                window.location.href = 'stocklistall.php';
            } else if (viewType === 'records') {
                window.location.href = 'stockeditall.php';
            } else if (viewType === 'product') {
                // 跳转到货品种类页面
                window.location.href = 'stockproductname.php';
            } else if (viewType === 'sot') {
                // 跳转到货品异常页面
                window.location.href = 'stocksot.php';
            } else {
                // 保持在当前页面（库存价格分析）
                hideViewDropdown();
            }
        }

        // 隐藏视图选择器下拉菜单
        function hideViewDropdown() {
            const dropdown = document.getElementById('view-selector-dropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }

        // 返回仪表盘
        function goBack() {
            window.location.href = 'dashboard.php';
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
                    throw new Error(`HTTP错误: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                throw error;
            }
        }

        // 自定义产品排序函数
        function sortProducts(products) {
            // 定义排序顺序（按用户要求的顺序）
            const sortOrder = [
                'salmon',
                'salmon belly 10pcs',
                'salmon head 10pcs',
                'salmon belly 10pcs (p)',
                'salmon head 10pcs (p)',
                'hamachi fillet mika',
                'a5 awagyu',
                'maguro blue fin'
            ];
            
            // 创建匹配模式（从长到短，用于优先匹配更具体的名称）
            const matchPatterns = [
                { pattern: 'salmon belly 10pcs (p)', index: 3 },
                { pattern: 'salmon head 10pcs (p)', index: 4 },
                { pattern: 'salmon belly 10pcs', index: 1 },
                { pattern: 'salmon head 10pcs', index: 2 },
                { pattern: 'salmon', index: 0 },
                { pattern: 'hamachi fillet mika', index: 5 },
                { pattern: 'a5 awagyu', index: 6 },
                { pattern: 'maguro blue fin', index: 7 }
            ];
            
            return products.sort((a, b) => {
                const nameA = (a.product_name || '').toLowerCase().trim();
                const nameB = (b.product_name || '').toLowerCase().trim();
                
                // 查找在排序顺序中的位置（优先匹配更长的模式）
                let indexA = -1;
                let indexB = -1;
                
                // 从长到短匹配，确保更具体的名称优先匹配
                for (const matchPattern of matchPatterns) {
                    const patternLower = matchPattern.pattern.toLowerCase();
                    
                    if (indexA === -1 && nameA.includes(patternLower)) {
                        indexA = matchPattern.index;
                    }
                    if (indexB === -1 && nameB.includes(patternLower)) {
                        indexB = matchPattern.index;
                    }
                    
                    // 如果都找到了，可以提前退出
                    if (indexA !== -1 && indexB !== -1) {
                        break;
                    }
                }
                
                // 情况1: 两个产品都在固定列表中，按列表顺序排序（前8个位置固定）
                if (indexA !== -1 && indexB !== -1) {
                    return indexA - indexB;
                }
                
                // 情况2: 只有A在固定列表中，A排在前面（固定位置）
                if (indexA !== -1) {
                    return -1;
                }
                
                // 情况3: 只有B在固定列表中，B排在前面（固定位置）
                if (indexB !== -1) {
                    return 1;
                }
                
                // 情况4: 两个产品都不在固定列表中，排在最后面，按字母顺序排序
                return nameA.localeCompare(nameB);
            });
        }

        // 加载库存价格分析数据
        async function loadStockRemarks() {
            if (isLoading) return;
            
            isLoading = true;
            setLoadingState(true);
            
            try {
                const result = await apiCall('?action=analysis');
                
                if (result.success) {
                    stockData = sortProducts(result.data.products || []);
                    filteredData = [...stockData];
                    renderProducts();
                    
                    if (stockData.length === 0) {
                        showAlert('当前没有发现多价格货品', 'info');
                    } else {
                        showAlert(`发现 ${stockData.length} 个货品有多个价格`, 'success');
                    }
                } else {
                    stockData = [];
                    filteredData = [];
                    showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
                    renderProducts();
                }
                
            } catch (error) {
                stockData = [];
                filteredData = [];
                showAlert('网络错误，请检查连接', 'error');
                renderProducts();
            } finally {
                isLoading = false;
                setLoadingState(false);
            }
        }

        function searchData() {
            const productFilter = document.getElementById('product-filter').value.toLowerCase();
            
            // 过滤数据
            filteredData = stockData.filter(item => {
                const matchProduct = !productFilter || item.product_name.toLowerCase().includes(productFilter);
                return matchProduct;
            });

            renderProducts();
            
            // 实时搜索时只在没有结果时显示提示
            if (productFilter && filteredData.length === 0) {
                showAlert('未找到匹配的记录', 'info');
            }
        }

        // 重置搜索过滤器
        function resetFilters() {
            document.getElementById('product-filter').value = '';
            
            filteredData = [...stockData];
            renderProducts();
            showAlert('搜索条件已重置', 'info');
        }

        // 设置加载状态
        function setLoadingState(loading) {
            const container = document.getElementById('products-container');
            
            if (loading) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 60px;">
                        <div class="loading"></div>
                        <div style="margin-top: 16px; color: #6b7280;">正在分析库存价格数据...</div>
                    </div>
                `;
            }
        }

        // 渲染货品列表
        function renderProducts() {
            const container = document.getElementById('products-container');
            
            if (filteredData.length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <i class="fas fa-search"></i>
                        <h3>没有找到货品备注</h3>
                        <p>当前筛选条件下没有发现已标记备注的货品</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="products-grid">';
            
            filteredData.forEach(product => {
                // 检查是否有任何variant的specification包含"kilo"（不区分大小写）
                const hasKilo = product.variants.some(variant => {
                    const spec = (variant.specification || '').toLowerCase();
                    return spec.includes('kilo') || spec.includes('kg');
                });
                
                // 计算行数（variants的数量）
                const rowCount = product.variants.length;
                
                // 构建总量和总数显示
                let totalDisplay = '';
                const normalizedName = (product.product_name || '').toLowerCase().trim();
                const needsPiecesTotal = normalizedName === 'salmon belly 10pcs' || normalizedName === 'salmon head 10pcs';
                
                if (hasKilo) {
                    // 如果是kilo单位，显示总量和总数
                    totalDisplay = `<div style="display: flex; flex-direction: column; gap: 4px; margin-top: 6px; align-items: center;">
                        <span style="color:rgb(0, 0, 0); font-weight: 600; background-color: rgba(0, 0, 0, 0.1); padding: 2px 6px; border-radius: 4px; font-size: clamp(7px, 0.67vw, 11px);">总数: ${rowCount}</span>
                        <span style="color:rgb(0, 0, 0); font-weight: 600; background-color: rgba(0, 0, 0, 0.1); padding: 2px 5px; border-radius: 4px; font-size: clamp(7px, 0.67vw, 11px);">总量: ${product.total_quantity}</span>
                    </div>`;
                } else if (needsPiecesTotal) {
                    const totalPieces = rowCount * 10;
                    totalDisplay = `<div style="display: flex; flex-direction: column; gap: 4px; margin-top: 6px; align-items: center;">
                        <span style="color:rgb(0, 0, 0); font-weight: 600; background-color: rgba(0, 0, 0, 0.1); padding: 2px 6px; border-radius: 4px; font-size: clamp(7px, 0.67vw, 11px);">总数: ${rowCount}</span>
                        <span style="color:rgb(0, 0, 0); font-weight: 600; background-color: rgba(0, 0, 0, 0.1); padding: 2px 5px; border-radius: 4px; font-size: clamp(7px, 0.67vw, 11px);">总量: ${totalPieces}</span>
                    </div>`;
                } else {
                    // 如果不是kilo单位，只显示总数
                    totalDisplay = `<div style="margin-top: 6px;">
                        <span style="color:rgb(0, 0, 0); font-weight: 600; background-color: rgba(0, 0, 0, 0.1); padding: 2px 6px; border-radius: 4px; font-size: clamp(7px, 0.67vw, 11px);">总数: ${rowCount}</span>
                    </div>`;
                }
                
                html += `
                        <div class="product-group">
                            <div class="product-header">
                                <div class="product-info-item">
                                    <div style="font-weight: 600;">${product.product_name}</div>
                                    ${totalDisplay}
                                </div>
                            </div>
                            <div class="product-table-container">
                                <div class="table-wrapper">
                                    <table class="price-variants-table">
                                        <thead>
                                            <tr>
                                                <th>备注编号</th>
                                                <th>数量/重量</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                
                // 按备注编号字母数字顺序排序variants（支持字母和数字混合排序）
                const sortedVariants = [...product.variants].sort((a, b) => {
                    const remarkA = a.remark_number || '';
                    const remarkB = b.remark_number || '';
                    
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
                    
                    return naturalSort(remarkA, remarkB);
                });
                
                // 为每个variant添加一行
                sortedVariants.forEach(variant => {
                    html += `
                        <tr>
                            <td>${variant.remark_number || '-'}</td>
                            <td>${variant.formatted_quantity} ${variant.specification || ''}</td>
                        </tr>`;
                    });
                
                html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        // 渲染价格变体
        function renderVariants(variants, maxPrice) {
            let html = '';
            
            variants.forEach((variant, index) => {
                const isHighest = parseFloat(variant.price) === parseFloat(maxPrice);
                const rowClass = isHighest ? 'highest-price' : '';
                
                html += `
                    <tr class="${rowClass}">
                        <td><strong>${index + 1}</strong></td>
                        <td>${variant.code_number || '-'}</td>
                        <td>${variant.formatted_stock}</td>
                        <td>
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${variant.formatted_price}</span>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            return html;
        }

        // 刷新数据
        function refreshData() {
            loadStockRemarks();
        }

        // 导出数据
        function exportData() {
            if (filteredData.length === 0) {
                showAlert('没有数据可导出', 'error');
                return;
            }
            
            try {
                // 创建CSV数据
                const headers = ['Product Name', 'Rank', 'Code Number', 'Stock', 'Unit Price'];
                let csvContent = headers.join(',') + '\n';
                
                filteredData.forEach(product => {
                    product.variants.forEach((variant, index) => {
                        const priceDiff = product.max_price - parseFloat(variant.price);
                        const row = [
                            `"${product.product_name}"`,
                            index + 1,
                            variant.code_number || '',
                            variant.formatted_stock,
                            variant.formatted_price
                        ];
                        csvContent += row.join(',') + '\n';
                    });
                });
                
                // 创建下载链接
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `stock_price_analysis_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('数据导出成功', 'success');
            } catch (error) {
                showAlert('导出失败', 'error');
            }
        }

        // 回到顶部功能
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // 监听滚动事件，控制回到顶部按钮显示
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            // 使用防抖优化性能
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                const backToTopBtn = document.getElementById('back-to-top-btn');
                const scrollThreshold = 150; // 滚动超过150px后显示按钮
                
                if (window.pageYOffset > scrollThreshold) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            }, 10);
        });

        // 完全替换现有的 showAlert 函数
        function showAlert(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            // 先检查并限制通知数量（在添加新通知之前）
            const existingToasts = container.querySelectorAll('.toast');
            while (existingToasts.length >= 999) {
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

        document.addEventListener('click', function(event) {
            const selector = event.target.closest('.selector-button');
            const dropdown = event.target.closest('.selector-dropdown');
            const dropdownItem = event.target.closest('.dropdown-item');
            
            // 移除库存选择器相关的逻辑，只保留视图选择器
            if (dropdownItem) {
                const parentDropdown = dropdownItem.closest('.selector-dropdown');
                if (parentDropdown) {
                    parentDropdown.classList.remove('show');
                }
                return;
            }
            
            if (!selector && !dropdown) {
                document.getElementById('view-selector-dropdown')?.classList.remove('show');
            }
        });

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);

        // 键盘快捷键支持
        document.addEventListener('keydown', function(e) {
            // Ctrl+F 聚焦搜索框
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('product-filter').focus();
            }
            
            // Escape键重置搜索
            if (e.key === 'Escape') {
                resetFilters();
            }
        });

        // 定时刷新数据（可选，每10分钟刷新一次）
        setInterval(() => {
            if (!document.hidden) { // 只在页面可见时刷新
                loadStockRemarks();
            }
        }, 600000); // 10分钟 = 600000毫秒
    </script>
</body>
</html>