
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
document.addEventListener('keydown', function (e) {
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