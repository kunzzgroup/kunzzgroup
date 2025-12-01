<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>最低库存设置 - 库存管理系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            font-size: clamp(8px, 0.74vw, 14px);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #faf7f2;
            color: #1a202c;
            line-height: 1.6;
        }

        .container {
            width: clamp(600px, 46.88vw, 900px);
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: transparent;
            color: #000000ff;
            padding: clamp(10px, 1.04vw, 20px) 0px;
            border-radius: 12px;
            margin-bottom: clamp(12px, 1.25vw, 24px);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: clamp(16px, 2.08vw, 40px);
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .btn {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border: none;
            border-radius: clamp(4px, 0.32vw, 6px);
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: clamp(4px, 0.42vw, 8px);
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-success {
            background-color: #059669;
            color: white;
        }

        .btn-success:hover {
            background-color: #047857;
        }

        .btn-warning {
            background-color: #f99e00;
            color: white;
        }

        .btn-warning:hover {
            background-color: #f98500ff;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: clamp(16px, 1.25vw, 24px);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: clamp(10px, 0.83vw, 16px);
            margin-bottom: clamp(14px, 1.04vw, 20px);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .filter-input {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: clamp(8px, 0.74vw, 14px);
            transition: border-color 0.2s;
        }

        .filter-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #3b82f6;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .stat-card .value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            padding: clamp(10px, 1.04vw, 20px);
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-scroll-container {
            overflow-x: auto;
            max-height: 600px;
        }

        .settings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .settings-table th,
        .settings-table td {
            font-size: clamp(8px, 0.74vw, 14px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .settings-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .settings-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .quantity-input {
            width: clamp(70px, 5.21vw, 100px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: clamp(8px, 0.74vw, 14px);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #3b82f6;
        }

        input:checked + .slider:before {
            transform: translateX(20px);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
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
        }

        .no-data i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #d1d5db;
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
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: 14px;
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
            font-size: 18px;
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

        @media (max-width: 768px) {
            .container {
                padding: 12px;
            }

            .header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                justify-content: center;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                gap: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                最低库存设置
            </h1>
            <button class="btn btn-secondary" onclick="goBack()">
                <i class="fas fa-arrow-left"></i>
                返回库存管理
            </button>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container"></div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="product-filter">货品名称</label>
                    <input type="text" id="product-filter" class="filter-input" placeholder="搜索货品名称...">
                </div>
                <div class="filter-group">
                    <label for="code-filter">货品编号</label>
                    <input type="text" id="code-filter" class="filter-input" placeholder="搜索货品编号...">
                </div>
                <div class="filter-group">
                    <label for="status-filter">预警状态</label>
                    <select id="status-filter" class="filter-input">
                        <option value="">全部状态</option>
                        <option value="active">已启用</option>
                        <option value="inactive">未启用</option>
                        <option value="warning">库存不足</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" onclick="searchSettings()">
                    <i class="fas fa-search"></i>
                    搜索
                </button>
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-refresh"></i>
                    重置
                </button>
                <button class="btn btn-success" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    刷新数据
                </button>
                <button class="btn btn-warning" onclick="saveAllSettings()">
                    <i class="fas fa-save"></i>
                    批量保存
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h3>最低库存设置</h3>
                <div id="table-stats">
                    显示 <span id="displayed-count">0</span> 个货品
                </div>
            </div>
            
            <div class="table-scroll-container">
                <table class="settings-table" id="settings-table">
                    <thead>
                        <tr>
                            <th>货品名称</th>
                            <th>货品编号</th>
                            <th>最低库存数量</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="settings-tbody">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    <!-- 动态通知内容 -->
    </div>

    <script>
        // 全局变量
        let allProducts = [];
        let filteredProducts = [];
        let isLoading = false;
        let pendingChanges = new Set();

        // 初始化
        function initApp() {
            loadProductsAndSettings();
        }

        // 加载货品和设置数据
        async function loadProductsAndSettings() {
            if (isLoading) return;
            
            isLoading = true;
            setLoadingState(true);
            
            try {
                // 这里需要创建对应的API接口
                const response = await fetch('stockminimumapi.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    allProducts = result.data || [];
                    filteredProducts = [...allProducts];
                    renderSettingsTable();
                    updateStats();
                }
                
            } catch (error) {
                console.error('Error:', error);
                // 静默处理，不显示错误提示
            } finally {
                isLoading = false;
                setLoadingState(false);
            }
        }

        // 设置加载状态
        function setLoadingState(loading) {
            const tbody = document.getElementById('settings-tbody');
            if (loading) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" style="padding: 40px; text-align: center;">
                            <div class="loading"></div>
                            <div style="margin-top: 16px; color: #6b7280;">正在加载数据...</div>
                        </td>
                    </tr>
                `;
            }
        }

        // 渲染设置表格
        function renderSettingsTable() {
            const tbody = document.getElementById('settings-tbody');
            
            if (filteredProducts.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无货品数据</div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            filteredProducts.forEach(product => {
                html += `
                    <tr>
                        <td><strong>${product.product_name}</strong></td>
                        <td>${product.product_code || '-'}</td>
                        <td>
                            <input type="number" 
                                class="quantity-input"
                                value="${product.minimum_quantity}"
                                min="0"
                                step="0.01"
                                onchange="markAsChanged('${product.product_name}', this.value)"
                                placeholder="设置最低数量">
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" 
                                    onclick="saveIndividualSetting('${product.product_name}')"
                                    style="padding: 4px 8px; font-size: clamp(8px, 0.63vw, 12px);">
                                <i class="fas fa-save"></i>
                                保存
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            document.getElementById('displayed-count').textContent = filteredProducts.length;
        }

        // 更新统计
        function updateStats() {
            const totalProducts = allProducts.length;
            const configuredAlerts = allProducts.filter(p => p.minimum_quantity > 0).length;
            const unconfiguredAlerts = totalProducts - configuredAlerts;

            document.getElementById('total-products').textContent = totalProducts;
            document.getElementById('configured-alerts').textContent = configuredAlerts;
            document.getElementById('unconfigured-alerts').textContent = unconfiguredAlerts;
        }

        // 标记为已更改
        function markAsChanged(productName, minQuantity) {
            const product = allProducts.find(p => p.product_name === productName);
            if (product) {
                product.minimum_quantity = parseFloat(minQuantity) || 0;
                pendingChanges.add(productName);
                
                // 重新渲染表格以更新状态
                updateStats();
            }
        }

        // 保存单个设置
        async function saveIndividualSetting(productName) {
            const product = allProducts.find(p => p.product_name === productName);
            if (!product) return;

            try {
                const response = await fetch('stockminimumapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'save_single',
                        product_name: productName,
                        minimum_quantity: product.minimum_quantity
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    pendingChanges.delete(productName);
                    showAlert(`${productName} 设置保存成功`, 'success');
                } else {
                    showAlert('保存失败: ' + (result.message || '未知错误'), 'error');
                }

            } catch (error) {
                showAlert('保存失败，请检查网络连接', 'error');
                console.error('Error:', error);
            }
        }

        // 批量保存所有更改
        async function saveAllSettings() {
            if (pendingChanges.size === 0) {
                showAlert('没有需要保存的更改', 'info');
                return;
            }

            const changedProducts = Array.from(pendingChanges).map(productName => {
                const product = allProducts.find(p => p.product_name === productName);
                return {
                    product_name: productName,
                    minimum_quantity: product.minimum_quantity
                };
            });

            try {
                const response = await fetch('stockminimumapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'save_batch',
                        products: changedProducts
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    pendingChanges.clear();
                    showAlert(`成功保存 ${changedProducts.length} 个货品的设置`, 'success');
                } else {
                    showAlert('批量保存失败: ' + (result.message || '未知错误'), 'error');
                }

            } catch (error) {
                showAlert('保存失败，请检查网络连接', 'error');
                console.error('Error:', error);
            }
        }

        // 搜索设置
        function searchSettings() {
            const productFilter = document.getElementById('product-filter').value.toLowerCase();
            const codeFilter = document.getElementById('code-filter').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;

            filteredProducts = allProducts.filter(product => {
                const matchProduct = !productFilter || product.product_name.toLowerCase().includes(productFilter);
                const matchCode = !codeFilter || (product.product_code && product.product_code.toLowerCase().includes(codeFilter));
                
                let matchStatus = true;
                if (statusFilter) {
                    switch (statusFilter) {
                        case 'active':
                            matchStatus = product.minimum_quantity > 0;
                            break;
                        case 'inactive':
                            matchStatus = product.minimum_quantity <= 0;
                            break;
                        case 'warning':
                            // 这里可以根据实际库存数量来判断
                            matchStatus = product.minimum_quantity > 0;
                            break;
                    }
                }

                return matchProduct && matchCode && matchStatus;
            });

            renderSettingsTable();
            
            if (filteredProducts.length === 0) {
                showAlert('未找到匹配的记录', 'info');
            } else {
                showAlert(`找到 ${filteredProducts.length} 条匹配记录`, 'success');
            }
        }

        // 重置过滤器
        function resetFilters() {
            document.getElementById('product-filter').value = '';
            document.getElementById('code-filter').value = '';
            document.getElementById('status-filter').value = '';
            
            filteredProducts = [...allProducts];
            renderSettingsTable();
            
            showAlert('搜索条件已重置', 'info');
        }

        // 刷新数据
        function refreshData() {
            if (pendingChanges.size > 0) {
                if (!confirm('有未保存的更改，刷新将丢失这些更改。确定要继续吗？')) {
                    return;
                }
                pendingChanges.clear();
            }
            
            loadProductsAndSettings();
        }

        // 返回库存管理
        function goBack() {
            if (pendingChanges.size > 0) {
                if (!confirm('有未保存的更改，离开将丢失这些更改。确定要离开吗？')) {
                    return;
                }
            }
            
            window.location.href = 'stocklistall.php';
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

        // 键盘快捷键
        document.addEventListener('keydown', function(e) {
            // Ctrl+S 保存
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveAllSettings();
            }
            
            // Ctrl+F 聚焦搜索框
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('product-filter').focus();
            }

            // 添加Ctrl+G聚焦编号搜索框
            if (e.ctrlKey && e.key === 'g') {
                e.preventDefault();
                document.getElementById('code-filter').focus();
            }
        });

        // 离开页面前检查未保存更改
        window.addEventListener('beforeunload', function(e) {
            if (pendingChanges.size > 0) {
                e.preventDefault();
                e.returnValue = '有未保存的更改，确定要离开吗？';
            }
        });
    </script>
</body>
</html>