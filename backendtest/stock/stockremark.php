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
    <link rel="stylesheet" href="../css/stockremark.css">
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
                const res = await fetch('/backendtest/api/generatecodeapi.php', {
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
                    totalDisplay = `<div style="display: flex; flex-direction: column; gap: 4px; margin-top: 6px; align-items: flex-start;">
                        <span style="color:rgb(0, 0, 0); font-weight: 800; padding: 2px 0px; border-radius: 4px; font-size: clamp(13px, 0.94vw, 18px);">总数: ${rowCount}</span>
                        <span style="color:rgb(0, 0, 0); font-weight: 800; padding: 2px 0px; border-radius: 4px; font-size: clamp(13px, 0.94vw, 18px);">总量: ${product.total_quantity}</span>
                    </div>`;
                } else if (needsPiecesTotal) {
                    const totalPieces = rowCount * 10;
                    totalDisplay = `<div style="display: flex; flex-direction: column; gap: 4px; margin-top: 6px; align-items: flex-start;">
                        <span style="color:rgb(0, 0, 0); font-weight: 800; padding: 2px 0px; border-radius: 4px; font-size: clamp(13px, 0.94vw, 18px);">总数: ${rowCount}</span>
                        <span style="color:rgb(0, 0, 0); font-weight: 800; padding: 2px 0px; border-radius: 4px; font-size: clamp(13px, 0.94vw, 18px);">总量: ${totalPieces}</span>
                    </div>`;
                } else {
                    // 如果不是kilo单位，只显示总数
                    totalDisplay = `<div style="margin-top: 6px;">
                        <span style="color:rgb(0, 0, 0); font-weight: 800; padding: 2px 0px; border-radius: 4px; font-size: clamp(13px, 0.94vw, 18px);">总数: ${rowCount}</span>
                    </div>`;
                }
                
                html += `
                        <div class="product-group">
                            <div class="product-header">
                                <div class="product-info-item">
                                    <div style="font-weight: 800;">${product.product_name}</div>
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
