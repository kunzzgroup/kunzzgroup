
// 全局状态（仅保留 J3）
let currentSystem = 'j3';
let stockData = { j3: [] };
let filteredData = { j3: [] };
let isLoading = { j3: false };

// API配置（仅 J3）
const API_CONFIG = { j3: 'j3stocklistapi.php' };

// 初始化应用
function initApp() {
    // 启动会话自动刷新
    startSessionRefresh();
    // 仅加载 J3 数据
    loadData('j3');
    // 实时搜索监听器
    setupRealTimeSearch();
}

// 设置实时搜索
function setupRealTimeSearch() {
    const searchInput = document.getElementById('j3-unified-filter');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                searchData('j3');
            }, 300);
        });
    }
}

// 已移除系统/视图切换逻辑，页面仅保留 J3
function toggleViewSelector() {
    const dd = document.getElementById('view-selector-dropdown');
    if (dd) dd.classList.toggle('show');
}

function switchView(view, e) {
    if (e) e.stopPropagation();
    if (view === 'records') {
        window.location.href = 'j3stockinoutpage';
        return;
    }
    if (view === 'product') {
        window.location.href = 'stockproductname';
        return;
    }
    // list = 当前页
    document.getElementById('current-view').textContent = '总库存';
    document.querySelectorAll('#view-selector-dropdown .dropdown-item').forEach(i => i.classList.remove('active'));
    const dd = document.getElementById('view-selector-dropdown');
    if (dd) dd.classList.remove('show');
}

// 点击外部关闭下拉（仅视图选择器）
document.addEventListener('click', function (e) {
    if (!e.target.closest('.view-selector')) {
        const vd = document.getElementById('view-selector-dropdown');
        if (vd) vd.classList.remove('show');
    }
});

// 返回仪表盘
function goBack() {
    window.location.href = 'dashboard';
}

// 会话自动刷新机制
let sessionRefreshInterval;

function startSessionRefresh() {
    // 每5分钟刷新一次会话
    sessionRefreshInterval = setInterval(async () => {
        try {
            const response = await fetch('session_refresh_api.php');
            const result = await response.json();

            if (!result.success && result.code === 'SESSION_EXPIRED') {
                clearInterval(sessionRefreshInterval);
                showSessionExpiredMessage();
            }
        } catch (error) {
            console.error('会话刷新失败:', error);
        }
    }, 5 * 60 * 1000); // 5分钟
}

function stopSessionRefresh() {
    if (sessionRefreshInterval) {
        clearInterval(sessionRefreshInterval);
    }
}

// 显示会话过期消息
function showSessionExpiredMessage() {
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.innerHTML = `
                    <div style="text-align: center; padding: 50px; background: #ffebee; border: 1px solid #f44336; border-radius: 8px; margin: 20px;">
                        <h2 style="color: #C62828; margin: 0 0 15px 0;">🔒 会话已过期</h2>
                        <p style="margin: 0 0 20px 0; color: #666;">您的登录会话已过期，请重新登录以继续使用。</p>
                        <button onclick="window.location.href='../frontend/login'" 
                                style="background: #C62828; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px;">
                            重新登录
                        </button>
                    </div>
                `;
    }
}

// API调用函数
async function apiCall(system, endpoint, options = {}) {
    try {
        const baseUrl = API_CONFIG[system];
        const response = await fetch(`${baseUrl}${endpoint}`, {
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

        // 检查是否是会话过期
        if (data.code === 'SESSION_EXPIRED') {
            showSessionExpiredMessage();
            return { success: false, code: 'SESSION_EXPIRED' };
        }

        return data;
    } catch (error) {
        console.error('API调用失败:', error);
        throw error;
    }
}

// 修改 loadData 函数
async function loadData(system) {
    if (system !== 'j3') system = 'j3';
    if (isLoading.j3) return;
    isLoading.j3 = true;
    setLoadingState('j3', true);
    try {
        const result = await apiCall('j3', '?action=summary');
        if (result.success) {
            stockData.j3 = result.data.summary || [];
            updateSummaryCards('j3', result.data);
            filteredData.j3 = [...stockData.j3];
            renderStockTable('j3');
            updateStats('j3');
            if (stockData.j3.length === 0) {
                showAlert('当前没有J3数据', 'info');
            }
        } else {
            stockData.j3 = [];
            filteredData.j3 = [];
            showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
            renderStockTable('j3');
        }
    } catch (error) {
        stockData.j3 = [];
        filteredData.j3 = [];
        console.error('Error:', error);
        renderStockTable('j3');
    } finally {
        isLoading.j3 = false;
        setLoadingState('j3', false);
    }
}

// 已移除低库存设置与检查逻辑

// 实时搜索数据
function searchData(system) {
    const searchTerm = document.getElementById(`${system}-unified-filter`).value.toLowerCase();

    filteredData[system] = stockData[system].filter(item => {
        // 搜索所有字段，包括序号、货品编号、货品名称、库存数量、规格、单价、总价
        return (
            (item.no && item.no.toString().includes(searchTerm)) ||
            (item.product_name && item.product_name.toLowerCase().includes(searchTerm)) ||
            (item.code_number && item.code_number.toLowerCase().includes(searchTerm)) ||
            (item.total_stock && item.total_stock.toString().includes(searchTerm)) ||
            (item.specification && item.specification.toLowerCase().includes(searchTerm)) ||
            (item.price && item.price.toString().includes(searchTerm)) ||
            (item.total_price && item.total_price.toString().includes(searchTerm)) ||
            (item.formatted_total_price && item.formatted_total_price.includes(searchTerm))
        );
    });

    renderStockTable(system);
    updateStats(system);
}

// 已移除价格分析（remark）相关逻辑

// 重置搜索过滤器
function resetFilters(system) {
    if (system !== 'j3') system = 'j3';
    const input = document.getElementById('j3-unified-filter');
    if (input) input.value = '';
    filteredData.j3 = [...stockData.j3];
    renderStockTable('j3');
    updateStats('j3');
    showAlert('搜索条件已重置', 'info');
}

// 设置加载状态
function setLoadingState(system, loading) {
    const tbody = document.getElementById(`${system}-stock-tbody`);
    if (loading && tbody) {
        tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center;">
                            <div class="loading"></div>
                            <div style="margin-top: 16px; color: #6b7280;">正在加载数据...</div>
                        </td>
                    </tr>
                `;
    }
}

// 更新汇总卡片
function updateSummaryCards(system, data) {
    document.getElementById(`${system}-total-value`).textContent = data.formatted_total_value || '0.00';

    // 更新 J3 类型统计（若返回）
    if (data.type_stats) {
        const serviceLineEl = document.getElementById(`${system}-service-line-value`);
        const sakeEl = document.getElementById(`${system}-sake-value`);
        const kitchenEl = document.getElementById(`${system}-kitchen-value`);
        const sushiBarEl = document.getElementById(`${system}-sushi-bar-value`);

        // 更新数值并检查是否为负数
        if (serviceLineEl) {
            serviceLineEl.textContent = data.type_stats.formatted_service_line || '0.00';
            serviceLineEl.classList.toggle('negative', data.type_stats.service_line < 0);
        }
        if (sakeEl) {
            sakeEl.textContent = data.type_stats.formatted_sake || '0.00';
            sakeEl.classList.toggle('negative', data.type_stats.sake < 0);
        }
        if (kitchenEl) {
            kitchenEl.textContent = data.type_stats.formatted_kitchen || '0.00';
            kitchenEl.classList.toggle('negative', data.type_stats.kitchen < 0);
        }
        if (sushiBarEl) {
            sushiBarEl.textContent = data.type_stats.formatted_sushi_bar || '0.00';
            sushiBarEl.classList.toggle('negative', data.type_stats.sushi_bar < 0);
        }
    }
}

// 更新统计信息
function updateStats(system) {
    const displayedRecords = filteredData[system].length;
    const totalRecords = stockData[system].length;

    document.getElementById(`${system}-displayed-records`).textContent = displayedRecords;
    document.getElementById(`${system}-total-records`).textContent = totalRecords;
}

// 替换现有的 renderStockTable 函数（仅 J3）
function renderStockTable(system) {
    const tbody = document.getElementById(`${system}-stock-tbody`);

    if (filteredData[system].length === 0) {
        tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无数据</div>
                        </td>
                    </tr>
                `;
        return;
    }

    let totalValue = 0;
    let tableRows = '';

    filteredData[system].forEach((item, index) => {
        const stockValue = parseFloat(item.total_stock) || 0;
        const priceValue = parseFloat(item.total_price) || 0;
        const stockClass = stockValue > 0 ? 'positive-value' : 'zero-value';
        const priceClass = priceValue > 0 ? 'positive-value' : 'zero-value';

        let rowClass = '';

        tableRows += `
                    <tr class="${rowClass}">
                        <td class="text-center">${item.no}</td>
                        <td class="text-center">${item.code_number || '-'}</td>
                        <td><strong>${item.product_name}</strong></td>
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
                    <td colspan="6" class="text-right" style="font-size: clamp(10px, 0.84vw, 16px); padding-right: 15px; text-align: right;">总计:</td>
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

// 已移除 remark 渲染相关函数

// 格式化货币 - 统一显示两位小数
function formatCurrency(value) {
    if (!value || value === '' || value === '0') return '0.00';
    const num = parseFloat(value);
    if (isNaN(num)) return '0.00';

    // 直接格式化为两位小数显示
    return num.toFixed(2);
}

// 刷新数据
function refreshData(system) {
    loadData(system);
}

// 导出数据
function exportData(system) {
    if (system !== 'j3') system = 'j3';
    if (filteredData[system].length === 0) {
        showAlert('没有数据可导出', 'error');
        return;
    }

    try {
        let csvContent, fileName;
        // 库存汇总导出（仅 J3）
        const headers = ['No.', 'Product Name', 'Code Number', 'Total Stock', 'Specification', 'Unit Price', 'Total Price'];
        csvContent = headers.join(',') + '\n';
        filteredData[system].forEach(item => {
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
        fileName = `${system}_stock_summary_${new Date().toISOString().split('T')[0]}.csv`;

        // 创建下载链接
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showAlert('数据导出成功', 'success');
    } catch (error) {
        showAlert('导出失败', 'error');
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
        const cfg = {
        'success': { icon: '✅', title: '操作成功' },
        'error':   { icon: '❌', title: '操作失败' },
        'info':    { icon: 'ℹ️', title: '提示信息' },
        'warning': { icon: '⚠️', title: '注意' }
    }[type] || { icon: '✅', title: '操作成功' };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.id = toastId;
        toast.innerHTML = `
        <div class="toast-icon-wrap">${cfg.icon}</div>
        <div class="toast-body">
            <div class="toast-title">${cfg.title}</div>
            <div class="toast-msg">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast('${toastId}')">&times;</button>
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
    }, 70000);
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

// 页面卸载时停止会话刷新
window.addEventListener('beforeunload', function () {
    stopSessionRefresh();
});

// 回到顶部功能
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// 监听滚动事件，控制回到顶部按钮显示
let scrollTimeout;
window.addEventListener('scroll', function () {
    // 使用防抖优化性能
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(function () {
        const backToTopBtn = document.getElementById('back-to-top-btn');
        const scrollThreshold = 150; // 滚动超过300px后显示按钮

        if (window.pageYOffset > scrollThreshold) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    }, 10);
});

// 键盘快捷键支持（仅 J3）
document.addEventListener('keydown', function (e) {
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        const filter = document.getElementById('j3-unified-filter');
        if (filter) filter.focus();
    }
    if (e.key === 'Escape') {
        resetFilters('j3');
    }
    if (e.ctrlKey && e.key === 'Home') {
        e.preventDefault();
        scrollToTop();
    }
});

// 定时刷新数据（每5分钟）
setInterval(() => {
    if (!document.hidden) {
        loadData('j3');
    }
}, 300000);

// 已移除低库存预警弹窗相关逻辑
