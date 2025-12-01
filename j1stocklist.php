<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>J1库存汇总 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f1dfbc;
            color: #111827;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        
        .header h1 {
            font-size: 56px;
            font-weight: bold;
            color: #583e04;
        }
        
        .header .controls {
            display: flex;
            align-items: center;
            gap: 16px;
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
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #583e04;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #462d03;
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


        .summary-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            transition: transform 0.2s ease;
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

        .summary-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #583e04;
        }

        .summary-card.total-value .value {
            color: #583e04;
        }

        /* 货币显示容器 */
        .currency-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 20px;
            height: 40px;
            box-sizing: border-box;
            font-size: 14px;
            width: 100%;
        }

        .currency-display .currency-symbol {
            color: #6b7280;
            font-weight: 500;
            text-align: left;
            flex-shrink: 0;
        }

        .currency-display .currency-amount {
            font-weight: 500;
            color: #583e04;
            text-align: right;
            flex-shrink: 0;
        }

        .currency-symbol {
            font-size: 14px;
            color: #6b7280;
        }

        .stock-table {
            table-layout: fixed;
            width: 100%;
            min-width: 1400px;
            border-collapse: collapse;
            font-size: 14px;
        }

        .stock-table th {
            background: #583e04;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            border: 1px solid #462d03;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            min-width: 80px;
        }

        .stock-table td {
            padding: 12px 8px;
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: middle;
        }

        .stock-table tr:nth-child(even) {
            background-color: white;
        }

        .stock-table tr:hover {
            background-color: #e5ebf8ff;
        }

        /* 固定表格列宽 */
        .stock-table th:nth-child(1), .stock-table td:nth-child(1) { width: 80px; }  /* No. */
        .stock-table th:nth-child(2), .stock-table td:nth-child(2) { width: 200px; } /* Product Name */
        .stock-table th:nth-child(3), .stock-table td:nth-child(3) { width: 120px; } /* Code Number */
        .stock-table th:nth-child(4), .stock-table td:nth-child(4) { width: 120px; } /* Total Stock */
        .stock-table th:nth-child(5), .stock-table td:nth-child(5) { width: 100px; } /* Specification */
        .stock-table th:nth-child(6), .stock-table td:nth-child(6) { width: 120px; } /* Unit Price */
        .stock-table th:nth-child(7), .stock-table td:nth-child(7) { width: 120px; } /* Total Price */

        .table-container {
            background: white;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.1);
            border: 2px solid #583e04;
            overflow: visible;
        }

        .table-scroll-container {
            overflow-x: auto;
            overflow-y: visible;
        }

        /* 操作按钮 */
        .action-buttons {
            padding: 24px;
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
            gap: 24px;
            align-items: center;
            font-size: 14px;
            color: #6b7280;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 150px;
        }

        .stat-value {
            font-size: 16px;
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

        .total-row {
            background: #f8f5eb !important;
            border-top: 2px solid #583e04;
            font-weight: 600;
            color: #583e04;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            /* 移动端改为上下排列 */
            .main-content-row {
                flex-direction: column;
                gap: 16px;
            }
            
            .summary-section {
                flex: none;
                width: 100%;
                min-width: auto;
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

        /* 确保负数的货币显示也是红色 */
        .positive-value .currency-symbol {
            color: #6b7280 !important;
            font-weight: 600;
        }

        .positive-value .currency-amount {
            color: #583e04 !important;
            font-weight: 600;
        }

        .zero-value .currency-symbol,
        .zero-value .currency-amount {
            color: #6b7280 !important;
        }

        .total-row .currency-symbol,
        .total-row .currency-amount {
            color: #583e04 !important;
            font-weight: 600;
        }

        /* 价格单元格样式 */
        .stock-table td.price-cell {
            padding: 0;
            text-align: left;
        }

        .stock-table td.stock-cell {
            padding: 0;
            text-align: center;
        }

        .stock-cell .currency-display {
            justify-content: center;
        }

        /* 确保价格单元格内容填满 */
        .price-cell .currency-display {
            width: 100%;
            margin: 0;
        }

        /* 总库存价值专用的货币显示样式 */
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
            color: #10b981;
        }

        /* 主要内容行布局 */
        .main-content-row {
            display: flex;
            gap: 24px;
            margin-bottom: 24px;
            align-items: stretch; /* 改为stretch让子元素高度一致 */
        }

        /* 左侧总库存区域 */
        .summary-section {
            flex: 0 0 400px; /* 固定宽度400px */
            min-width: 400px;
            display: flex; /* 添加flex布局 */
            flex-direction: column; /* 垂直排列 */
        }

        /* 右侧搜索过滤区域 */
        .filter-section {
            flex: 1; /* 占据剩余空间 */
            min-width: 0; /* 允许缩小 */
            display: flex; /* 添加flex布局 */
            flex-direction: column; /* 垂直排列 */
        }

        /* 总库存卡片样式调整 */
        .summary-section .summary-card {
            width: 100%;
            margin-bottom: 24px;
            flex: 1; /* 让卡片填满整个高度 */
            display: flex;
            flex-direction: column;
            justify-content: center; /* 垂直居中内容 */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>J1库存汇总报表</h1>
            </div>
            <div class="controls">
                <button class="back-button" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i>
                    返回上一页
                </button>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 总库存和搜索区域左右排列 -->
        <div class="main-content-row">
            <!-- 左侧：总库存 -->
            <div class="summary-section">
                <div class="summary-card total-value">
                    <h3>J1总库存</h3>
                    <div class="summary-currency-display">
                        <span class="currency-symbol">RM</span>
                        <span class="value" id="total-value">0.00</span>
                    </div>
                </div>
            </div>
            
            <!-- 右侧：搜索和过滤区域 -->
            <div class="filter-section">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="product-filter">产品名称</label>
                        <input type="text" id="product-filter" class="filter-input" placeholder="搜索产品名称...">
                    </div>
                    <div class="filter-group">
                        <label for="code-filter">产品编号</label>
                        <input type="text" id="code-filter" class="filter-input" placeholder="搜索产品编号...">
                    </div>
                    <div class="filter-group">
                        <label for="spec-filter">规格单位</label>
                        <input type="text" id="spec-filter" class="filter-input" placeholder="搜索规格单位...">
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" onclick="searchData()">
                        <i class="fas fa-search"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="resetFilters()">
                        <i class="fas fa-refresh"></i>
                        重置
                    </button>
                    <button class="btn btn-warning" onclick="exportData()">
                        <i class="fas fa-download"></i>
                        导出CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Stock Summary Table -->
        <div class="table-container">
            <div class="action-buttons">
                <div class="stats-info" id="stock-stats">
                    <div class="stat-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>显示记录: <span class="stat-value" id="displayed-records">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-boxes"></i>
                        <span>总记录: <span class="stat-value" id="total-records">0</span></span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button class="btn btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        刷新数据
                    </button>
                </div>
            </div>
            
            <div class="table-scroll-container">
                <table class="stock-table" id="stock-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>PRODUCT</th>
                            <th>Code Number</th>
                            <th>Total Stock</th>
                            <th>Specification</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody id="stock-tbody">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // API 配置
        const API_BASE_URL = 'j1stocklistapi.php';
        
        // 应用状态
        let stockData = [];
        let filteredData = [];
        let isLoading = false;

        // 初始化应用
        function initApp() {
            loadStockSummary();
        }

        // 返回上一页
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
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
                    throw new Error(`HTTP错误: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                throw error;
            }
        }

        // 加载库存汇总数据
        async function loadStockSummary() {
            if (isLoading) return;
            
            isLoading = true;
            setLoadingState(true);
            
            try {
                const result = await apiCall('?action=summary');
                
                if (result.success) {
                    stockData = result.data.summary || [];
                    filteredData = [...stockData];
                    updateSummaryCards(result.data);
                    renderStockTable();
                    updateStats();
                    
                    if (stockData.length === 0) {
                        showAlert('当前没有J1库存数据', 'info');
                    }
                } else {
                    stockData = [];
                    filteredData = [];
                    showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
                    renderStockTable();
                }
                
            } catch (error) {
                stockData = [];
                filteredData = [];
                showAlert('网络错误，请检查连接', 'error');
                renderStockTable();
            } finally {
                isLoading = false;
                setLoadingState(false);
            }
        }

        // 搜索数据
        function searchData() {
            const productFilter = document.getElementById('product-filter').value.toLowerCase();
            const codeFilter = document.getElementById('code-filter').value.toLowerCase();
            const specFilter = document.getElementById('spec-filter').value.toLowerCase();

            filteredData = stockData.filter(item => {
                const matchProduct = !productFilter || item.product_name.toLowerCase().includes(productFilter);
                const matchCode = !codeFilter || (item.code_number && item.code_number.toLowerCase().includes(codeFilter));
                const matchSpec = !specFilter || (item.specification && item.specification.toLowerCase().includes(specFilter));

                return matchProduct && matchCode && matchSpec;
            });

            renderStockTable();
            updateStats();
            
            if (filteredData.length === 0) {
                showAlert('未找到匹配的记录', 'info');
            } else {
                showAlert(`找到 ${filteredData.length} 条匹配记录`, 'success');
            }
        }

        // 重置搜索过滤器
        function resetFilters() {
            document.getElementById('product-filter').value = '';
            document.getElementById('code-filter').value = '';
            document.getElementById('spec-filter').value = '';
            
            filteredData = [...stockData];
            renderStockTable();
            updateStats();
            showAlert('搜索条件已重置', 'info');
        }

        // 设置加载状态
        function setLoadingState(loading) {
            const tbody = document.getElementById('stock-tbody');
            
            if (loading) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center;">
                            <div class="loading"></div>
                            <div style="margin-top: 16px; color: #6b7280;">正在加载J1库存数据...</div>
                        </td>
                    </tr>
                `;
            }
        }

        // 更新汇总卡片
        function updateSummaryCards(data) {
            document.getElementById('total-value').textContent = data.formatted_total_value || '0.00';
        }

        // 更新统计信息
        function updateStats() {
            const displayedRecords = filteredData.length;
            const totalRecords = stockData.length;
            
            document.getElementById('displayed-records').textContent = displayedRecords;
            document.getElementById('total-records').textContent = totalRecords;
        }

        // 渲染库存表格
        function renderStockTable() {
            const tbody = document.getElementById('stock-tbody');
            
            if (filteredData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无J1库存数据</div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            let totalValue = 0;
            let tableRows = '';
            
            filteredData.forEach((item, index) => {
                const stockValue = parseFloat(item.total_stock) || 0;
                const priceValue = parseFloat(item.total_price) || 0;
                const stockClass = stockValue > 0 ? 'positive-value' : 'zero-value';
                const priceClass = priceValue > 0 ? 'positive-value' : 'zero-value';
                
                tableRows += `
                    <tr>
                        <td class="text-center">${item.no}</td>
                        <td><strong>${item.product_name}</strong></td>
                        <td class="text-center">${item.code_number || '-'}</td>
                        <td class="stock-cell">
                                <div class="currency-display ${stockClass}">
                                    <span class="currency-symbol">&nbsp;</span>
                                    <span class="currency-amount">${item.formatted_stock}</span>
                                </div>
                            </td>
                        <td class="text-center">${item.specification || '-'}</td>
                        <td class="price-cell">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${item.formatted_price}</span>
                            </div>
                        </td>
                        <td class="price-cell">
                            <div class="currency-display ${priceClass}">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${item.formatted_total_price}</span>
                            </div>
                        </td>
                    </tr>
                `;
                totalValue += priceValue;
            });
            
            // 添加总计行
            tableRows += `
                <tr class="total-row">
                    <td colspan="6" class="text-right" style="font-size: 16px; padding-right: 15px; text-align: right;">总计:</td>
                    <td class="price-cell positive-value" style="font-size: 16px;">
                        <div class="currency-display">
                            <span class="currency-symbol">RM</span>
                            <span class="currency-amount">${formatCurrency(totalValue)}</span>
                        </div>
                    </td>
                </tr>
            `;
            
            tbody.innerHTML = tableRows;
        }

        // 格式化货币
        function formatCurrency(value) {
            if (!value || value === '' || value === '0') return '0.00';
            const num = parseFloat(value);
            return isNaN(num) ? '0.00' : num.toFixed(2);
        }

        // 刷新数据
        function refreshData() {
            loadStockSummary();
        }

        // 导出数据
        function exportData() {
            if (filteredData.length === 0) {
                showAlert('没有数据可导出', 'error');
                return;
            }
            
            try {
                // 创建CSV数据
                const headers = ['No.', 'Product Name', 'Code Number', 'Total Stock', 'Specification', 'Unit Price', 'Total Price'];
                let csvContent = headers.join(',') + '\n';
                
                filteredData.forEach(item => {
                    const row = [
                        item.no,
                        `"${item.product_name}"`,
                        item.code_number || '',
                        item.formatted_stock,
                        item.specification || '',
                        item.formatted_price,
                        item.formatted_total_price
                    ];
                    csvContent += row.join(',') + '\n';
                });
                
                // 创建下载链接
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `j1_stock_summary_${new Date().toISOString().split('T')[0]}.csv`);
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

        // 定时刷新数据（可选，每5分钟刷新一次）
        setInterval(() => {
            if (!document.hidden) { // 只在页面可见时刷新
                loadStockSummary();
            }
        }, 300000); // 5分钟 = 300000毫秒
    </script>
</body>
</html>