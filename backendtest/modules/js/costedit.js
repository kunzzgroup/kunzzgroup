// API 配置
const API_BASE_URL = '/backendtest/api/costapi.php';

// ===== 页面版本标识（用于确认是否加载到最新代码，排查缓存/OPcache）=====
const COSTEDIT_BUILD_ID = '2025-12-18_02';
(function () {
    try {
        console.log('[costedit] build:', COSTEDIT_BUILD_ID);
        const el = document.getElementById('page-build-id');
        if (el) el.textContent = COSTEDIT_BUILD_ID;
    } catch (e) { }
})();

// Load configuration from PAGE_CONFIG defined in the template
const availableReportTypes = PAGE_CONFIG.availableReportTypes;
const reportDropdownEnabled = PAGE_CONFIG.reportDropdownEnabled;
const availableRestaurants = PAGE_CONFIG.availableRestaurants;
const restaurantDropdownEnabled = PAGE_CONFIG.restaurantDropdownEnabled;
const restaurantConfig = PAGE_CONFIG.restaurantConfig;

// 应用状态
let currentRestaurant = PAGE_CONFIG.defaultRestaurant;
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth() + 1;
let monthData = {};
let monthStockData = null;
let isLoading = false;
let pasteTargetDay = null;
const editingDays = new Set();

// 货币字段列表
const currencyFields = ['sales', 'c_beverage', 'c_kitchen'];
let preservedRowValues = new Map();

if (!availableRestaurants.includes(currentRestaurant)) {
    currentRestaurant = availableRestaurants.length ? availableRestaurants[0] : 'j1';
}
if (!restaurantConfig[currentRestaurant]) {
    restaurantConfig[currentRestaurant] = { name: 'J1', number: 1 };
}

// 初始化应用
function initApp() {
    // 启动会话自动刷新
    startSessionRefresh();

    initYearSelect();
    initCurrentMonth();
    refreshRestaurantDisplay();

    console.log('[initApp] Initialized with PAGE_CONFIG:', PAGE_CONFIG);
    console.log('[initApp] Current Restaurant:', currentRestaurant);

    loadMonthData();
}

// 初始化年份选择器
function initYearSelect() {
    const yearSelect = document.getElementById('year-select');
    const currentYear = new Date().getFullYear();

    // 生成从2022年到未来2年的选项
    for (let year = 2022; year <= currentYear + 2; year++) {
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

function refreshRestaurantDisplay() {
    const info = restaurantConfig[currentRestaurant];
    const numberBtn = document.querySelector('.number-btn');
    if (numberBtn && info) {
        if (restaurantDropdownEnabled) {
            numberBtn.innerHTML = `${info.number} <i class="fas fa-chevron-down"></i>`;
        } else {
            numberBtn.textContent = info.number;
        }
    }
    const infoElem = document.querySelector('#current-restaurant-info .stat-value');
    if (infoElem && info) {
        infoElem.textContent = info.name;
    }
    updateSelectedNumber();
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
    if (!availableRestaurants.includes(restaurant)) {
        showAlert('您没有权限查看该店铺', 'warning');
        return;
    }
    if (currentRestaurant === restaurant || isLoading) return;

    currentRestaurant = restaurant;
    refreshRestaurantDisplay();
    // 重新加载数据
    loadMonthData();
}

// 会话自动刷新机制
let sessionRefreshInterval;

function startSessionRefresh() {
    // 每5分钟刷新一次会话
    sessionRefreshInterval = setInterval(async () => {
        try {
            const response = await fetch('/backendtest/api/session_refresh_api.php');
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
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer) {
        tableContainer.innerHTML = `
            <div style="text-align: center; padding: 50px; background: #ffebee; border: 1px solid #f44336; border-radius: 8px; margin: 20px;">
                <h2 style="color: #C62828; margin: 0 0 15px 0;">🔒 会话已过期</h2>
                <p style="margin: 0 0 20px 0; color: #666;">您的登录会话已过期，请重新登录以继续使用。</p>
                <button onclick="window.location.href='../frontend/login.php'" 
                        style="background: #C62828; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px;">
                    重新登录
                </button>
            </div>
        `;
    }
}

// API 调用函数
async function apiCall(endpoint, options = {}) {
    try {
        const url = `${API_BASE_URL}${endpoint}`;
        console.log('[API Call] Requesting:', url, options);

        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });

        console.log('[API Call] Response status:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP错误: ${response.status}`);
        }

        // Clone response to log text if JSON parsing fails
        const clone = response.clone();
        try {
            const data = await response.json();
            console.log('[API Call] Response data:', data);

            if (data.code === 'SESSION_EXPIRED') {
                showSessionExpiredMessage();
                return { success: false, code: 'SESSION_EXPIRED' };
            }

            return data;
        } catch (e) {
            const text = await clone.text();
            console.error('[API Call] JSON Parse Error:', e);
            console.error('[API Call] Response Text:', text);
            throw e;
        }
    } catch (error) {
        console.error('API调用失败:', error);
        throw error;
    }
}

// 根据日期获取已存在的成本记录（用于“已存在但前端没有id”的兜底更新）
async function getExistingCostRecordByDate(dateStr) {
    try {
        const query = new URLSearchParams({
            action: 'list',
            restaurant: currentRestaurant,
            search_date: dateStr
        });
        const res = await apiCall(`?${query.toString()}`);
        if (res && res.success && Array.isArray(res.data) && res.data.length > 0) {
            return res.data[0];
        }
    } catch (e) {
        console.warn('按日期查询成本记录失败:', e);
    }
    return null;
}

// 加载月度数据
async function loadMonthData(preserveEditingState = false) {
    if (isLoading) return;

    if (!preserveEditingState) {
        editingDays.clear();
        preservedRowValues.clear();
    } else {
        captureEditingRowValues();
    }
    isLoading = true;
    currentYear = parseInt(document.getElementById('year-select').value);
    currentMonth = parseInt(document.getElementById('month-select').value);

    console.log('[loadMonthData] Loading data for:', currentYear, currentMonth, 'Restaurant:', currentRestaurant);

    try {
        const startDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-01`;
        const lastDay = new Date(currentYear, currentMonth, 0).getDate();
        const endDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;

        // 同时加载成本数据和KPI数据
        // 销售额字段将从KPI的净销售额（总销售额-折扣）自动获取
        const [costResult, kpiResult] = await Promise.all([
            apiCall(`?${new URLSearchParams({
                action: 'list',
                restaurant: currentRestaurant,
                start_date: startDate,
                end_date: endDate
            })}`),
            fetch(`/backendtest/api/kpiapi.php?${new URLSearchParams({
                action: 'list',
                restaurant: currentRestaurant,
                start_date: startDate,
                end_date: endDate
            })}`).then(res => res.json()).catch(() => ({ success: false, data: [] }))
        ]);

        const costData = costResult.data || [];
        const kpiData = kpiResult.data || [];

        // 将KPI数据转换为以日期为键的对象，并计算净销售额
        // 净销售额 = 总销售额(gross_sales) - 折扣(discounts)
        const kpiDataMap = {};
        kpiData.forEach(item => {
            const day = parseInt(item.date.split('-')[2]);
            const grossSales = parseFloat(item.gross_sales) || 0;
            const discounts = parseFloat(item.discounts) || 0;
            const netSales = grossSales - discounts;
            kpiDataMap[day] = {
                net_sales: netSales
            };
        });

        // 合并成本数据和KPI净销售额
        // 销售额字段优先使用KPI的净销售额，如果KPI中没有数据则使用成本数据中的销售额
        monthData = {};

        // 首先处理成本数据（这是主要数据源，必须保留）
        costData.forEach(item => {
            const day = parseInt(item.date.split('-')[2]);
            monthData[day] = item;
            // 如果KPI数据中有该日的净销售额，则使用KPI的净销售额覆盖成本数据的销售额
            if (kpiDataMap[day] && kpiDataMap[day].net_sales !== undefined) {
                monthData[day].sales = kpiDataMap[day].net_sales;
            }
        });

        // 对于成本数据中没有但KPI数据中有的日期，也添加到monthData中
        // 但只有在成本数据查询成功且确实没有该日期的记录时，才创建临时对象
        // 这样可以避免覆盖已保存但查询失败的成本数据
        if (costResult.success !== false) {
            Object.keys(kpiDataMap).forEach(day => {
                if (!monthData[day]) {
                    const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                    monthData[day] = {
                        date: dateStr,
                        sales: kpiDataMap[day].net_sales,
                        c_beverage: 0,
                        c_kitchen: 0
                    };
                }
            });
        }

        // 加载当月库存数据
        await loadMonthStock();

        generateExcelTable();
        updateMonthStats();
        setTimeout(() => {
            updateInputColors();
        }, 200);

    } catch (error) {
        monthData = {};
        monthStockData = null;
        generateExcelTable();
        updateMonthStats();
    } finally {
        isLoading = false;
    }
}

// 加载当月库存数据
async function loadMonthStock() {
    try {
        const yearMonth = `${currentYear}-${currentMonth.toString().padStart(2, '0')}`;
        const queryParams = new URLSearchParams({
            action: 'get_month_stock',
            restaurant: currentRestaurant,
            year_month: yearMonth
        });

        const result = await apiCall(`?${queryParams}`);
        if (result.success && result.data) {
            monthStockData = result.data;
            // 更新输入框的值
            const stockInput = document.getElementById('current-stock-input');
            if (stockInput) {
                stockInput.value = monthStockData.current_stock ? parseFloat(monthStockData.current_stock).toFixed(2) : '';
            }
        } else {
            monthStockData = null;
            document.getElementById('current-stock-input').value = '';
        }
    } catch (error) {
        console.error('加载库存数据失败:', error);
        monthStockData = null;
        document.getElementById('current-stock-input').value = '';
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
                <div class="input-container auto-filled-container">
                    <span class="currency-prefix">RM</span>
                    <input type="number" class="excel-input currency-input auto-filled" data-field="sales" data-day="${day}" 
                        value="${formatCurrencyDisplay(existingData.sales)}" min="0" step="0.01" 
                        placeholder="0.00" readonly disabled onchange="updateCalculations(${day})" 
                        title="销售额自动从KPI净销售额获取，不可手动编辑">
                </div>
            </td>
            <td>
                <div class="input-container">
                    <span class="currency-prefix">RM</span>
                    <input type="number" class="excel-input currency-input" data-field="c_beverage" data-day="${day}" 
                        value="${formatCurrencyDisplay(existingData.c_beverage)}" min="0" step="0.01" 
                        placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                </div>
            </td>
            <td>
                <div class="input-container">
                    <span class="currency-prefix">RM</span>
                    <input type="number" class="excel-input currency-input" data-field="c_kitchen" data-day="${day}" 
                        value="${formatCurrencyDisplay(existingData.c_kitchen)}" min="0" step="0.01" 
                        placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                </div>
            </td>
            <td class="calculated-cell" id="c-total-${day}">RM 0.00</td>
            <td class="calculated-cell" id="gross-total-${day}">RM 0.00</td>
            <td class="calculated-cell" id="cost-percent-${day}">0%</td>
            <td class="action-cell">
                <button class="edit-btn" id="edit-btn-${day}" onclick="toggleEdit(${day})" title="编辑${day}日数据">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-day-btn" onclick="clearDayData(${day})" title="清空${day}日成本（保留销售额）">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);

        setTimeout(() => {
            for (let day = 1; day <= daysInMonth; day++) {
                setRowReadonly(day, true, true);
            }
            restoreEditingStates();
        }, 0);

        updateCalculations(day);
    }

    setTimeout(() => {
        updateInputColors();
    }, 100);
}

// 格式化货币输入
function formatCurrencyInput(input) {
    const value = input.value;
    if (value && !isNaN(value)) {
        // 只在失去焦点时格式化
    }
}

// 格式化库存输入
function formatStockInput(input) {
    const value = input.value;
    if (value && value.includes('.')) {
        const parts = value.split('.');
        if (parts[1] && parts[1].length > 2) {
            input.value = parts[0] + '.' + parts[1].substring(0, 2);
        }
    }
}

// 显示提示信息
function showAlert(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    let existingToasts = container.querySelectorAll('.toast');
    while (existingToasts.length >= 3) {
        closeToast(existingToasts[0].id);
        if (existingToasts[0].parentNode) {
            existingToasts[0].parentNode.removeChild(existingToasts[0]);
        }
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

    setTimeout(() => {
        toast.classList.add('show');
    }, 0);

    setTimeout(() => {
        closeToast(toastId);
    }, 700);
}

// 关闭通知
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

// 关闭所有通知
function closeAllToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        closeToast(toast.id);
    });
}

// 页面加载完成后初始化
// document.addEventListener('DOMContentLoaded', initApp); // Moved to end of file

// 页面卸载时停止会话刷新
window.addEventListener('beforeunload', function () {
    stopSessionRefresh();
});

// 设置行的只读状态
function setRowReadonly(day, readonly, skipTracking = false) {
    const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
    if (!inputs.length) return;
    const row = inputs[0].closest('tr');
    if (!row) return;

    inputs.forEach(input => {
        // 销售额字段始终只读（从KPI自动获取）
        if (input.dataset.field === 'sales') {
            input.classList.add('readonly', 'auto-filled');
            input.setAttribute('readonly', 'readonly');
            input.setAttribute('disabled', 'disabled');
            return;
        }

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

    if (readonly) {
        row.classList.remove('editing-row');
    } else {
        row.classList.add('editing-row');
    }

    if (!skipTracking) {
        if (readonly) {
            editingDays.delete(day);
        } else {
            editingDays.add(day);
        }
    }
}

function captureEditingRowValues() {
    preservedRowValues.clear();
    if (!editingDays.size) return;
    editingDays.forEach(day => {
        const dayInputs = document.querySelectorAll(`input[data-day="${day}"]`);
        if (!dayInputs.length) return;
        const values = {};
        dayInputs.forEach(input => {
            const field = input.dataset.field;
            values[field] = input.value;
        });
        preservedRowValues.set(day, values);
    });
}

function restoreEditingRowValues() {
    if (!preservedRowValues.size) return;
    preservedRowValues.forEach((values, day) => {
        Object.entries(values).forEach(([field, value]) => {
            const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
            if (input) {
                input.value = value;
            }
        });
        updateCalculations(day);
    });
    preservedRowValues.clear();
}

function restoreEditingStates() {
    if (!editingDays.size) return;
    editingDays.forEach(day => {
        const editBtn = document.getElementById(`edit-btn-${day}`);
        if (!editBtn) return;
        setRowReadonly(day, false, true);
        editBtn.classList.add('save-mode');
        editBtn.innerHTML = '<i class="fas fa-save"></i>';
        editBtn.title = `保存${day}日数据`;
    });
    restoreEditingRowValues();
}

// 更新计算字段
function updateCalculations(day) {
    const sales = parseFloat(getInputValue('sales', day)) || 0;
    const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
    const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;

    // 总成本 = 饮料成本 + 厨房成本
    const cTotal = cBeverage + cKitchen;
    document.getElementById(`c-total-${day}`).textContent = `RM ${cTotal.toFixed(2)}`;

    // 毛利润 = 销售额 - 总成本
    const grossTotal = sales - cTotal;
    const grossTotalCell = document.getElementById(`gross-total-${day}`);
    grossTotalCell.textContent = `RM ${grossTotal.toFixed(2)}`;

    // 如果毛利润为负数，添加红色样式
    if (grossTotal < 0) {
        grossTotalCell.classList.add('negative');
    } else {
        grossTotalCell.classList.remove('negative');
    }

    // 成本率 = (总成本 / 销售额) * 100%
    const costPercent = sales > 0 ? (cTotal / sales) * 100 : 0;
    document.getElementById(`cost-percent-${day}`).textContent = `${costPercent.toFixed(2)}%`;

    updateMonthStats();
}

// 更新输入框颜色状态
function updateInputColors() {
    const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

    for (let day = 1; day <= daysInMonth; day++) {
        const dayInputs = document.querySelectorAll(`input[data-day="${day}"]`);

        const sales = getInputValue('sales', day).trim();
        const cBeverage = getInputValue('c_beverage', day).trim();
        const cKitchen = getInputValue('c_kitchen', day).trim();

        let filledKeyFields = 0;
        if (sales && sales !== '0' && sales !== '0.00') filledKeyFields++;
        if (cBeverage && cBeverage !== '0' && cBeverage !== '0.00') filledKeyFields++;
        if (cKitchen && cKitchen !== '0' && cKitchen !== '0.00') filledKeyFields++;

        const rowHasKeyData = filledKeyFields >= 1;

        dayInputs.forEach(input => {
            const field = input.dataset.field;
            const value = input.value.trim();

            const hasValue = value !== '' && value !== '0' && value !== '0.00';
            if (hasValue) {
                input.classList.remove('no-data');
                input.classList.add('has-data');
            } else {
                input.classList.remove('has-data');
                input.classList.add('no-data');
            }
        });
    }
}

// 获取输入框值
function getInputValue(field, day) {
    const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
    return input ? input.value : '';
}

// 更新月度统计
function updateMonthStats() {
    let filledDays = 0;
    let totalSales = 0;
    let totalCost = 0;
    let totalProfit = 0;

    const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

    for (let day = 1; day <= daysInMonth; day++) {
        const sales = parseFloat(getInputValue('sales', day)) || 0;
        const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
        const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;

        if (sales > 0 || cBeverage > 0 || cKitchen > 0) {
            filledDays++;
        }

        const cTotal = cBeverage + cKitchen;
        const grossTotal = sales - cTotal;

        totalSales += sales;
        totalCost += cTotal;
        totalProfit += grossTotal;
    }

    const avgCostPercent = totalSales > 0 ? (totalCost / totalSales) * 100 : 0;

    document.getElementById('filled-days').textContent = filledDays;
    document.getElementById('total-sales').textContent = totalSales.toFixed(2);
    document.getElementById('total-cost').textContent = totalCost.toFixed(2);
    document.getElementById('total-profit').textContent = totalProfit.toFixed(2);
    document.getElementById('avg-cost-percent').textContent = avgCostPercent.toFixed(2);
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
            const isThousandsSeparator =
                /\d/.test(prevChar) &&
                /\d/.test(nextChar) &&
                /^\d{1,3}($|[,\s\t])/.test(text.substring(i + 1));

            if (isThousandsSeparator) {
                current += char;
                inNumber = true;
            } else {
                if (current.trim()) {
                    values.push(current.trim());
                }
                current = '';
                inNumber = false;
            }
        } else if (/\s/.test(char)) {
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

    // 销售额字段不可编辑，从粘贴字段列表中移除
    const pasteFields = [
        'c_beverage',
        'c_kitchen'
    ];

    // 如果起始字段是销售额，则从饮料成本开始
    let startIndex = 0;
    if (startField === 'sales') {
        startIndex = 0; // 从饮料成本开始
        showAlert('销售额字段不可编辑，将从饮料成本开始粘贴', 'info');
    } else if (startField && pasteFields.includes(startField)) {
        startIndex = pasteFields.indexOf(startField);
    }

    if (lines.length > 1) {
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

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

        for (let lineIndex = 0; lineIndex < Math.min(lines.length, editingDays.length); lineIndex++) {
            const line = lines[lineIndex];
            const day = editingDays[lineIndex];

            let values = [];
            if (line.includes('\t')) {
                values = line.split('\t');
            } else if (line.includes(',')) {
                const numberPattern = /^[\d,]+\.?\d*$/;
                if (numberPattern.test(line.trim())) {
                    values = [line.trim()];
                } else {
                    values = splitWithNumberProtection(line);
                }
            } else {
                values = line.split(/\s+/);
            }

            let rowPasteCount = 0;
            const currentStartIndex = (lineIndex === 0) ? startIndex : 0;

            for (let i = 0; i < values.length && (currentStartIndex + i) < pasteFields.length; i++) {
                const fieldIndex = currentStartIndex + i;
                const field = pasteFields[fieldIndex];
                const value = values[i].trim();

                // 跳过销售额字段（不可编辑）
                if (field === 'sales') {
                    continue;
                }

                if (value && value !== '') {
                    const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
                    if (input && !input.classList.contains('auto-filled')) {
                        let cleanValue = value.replace(/[^\d.,-]/g, '');
                        cleanValue = cleanValue.replace(/,/g, '');

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
                updateCalculations(day);
            }
        }

        if (totalPasteCount > 0) {
            const fieldNames = {
                'c_beverage': '饮料成本',
                'c_kitchen': '厨房成本'
            };
            const startFieldName = startField && startField !== 'sales' ? fieldNames[startField] : '第一列';
            showAlert(`第一行从${startFieldName}开始，后续行从第一列开始，成功粘贴 ${lines.length} 行数据，共 ${totalPasteCount} 个字段到第 ${pastedDays.join(', ')} 日（销售额字段自动从KPI获取，不可编辑）`, 'success');
        } else {
            showAlert('未能识别有效的数据格式', 'error');
        }

    } else {
        const line = lines[0];
        let values = [];
        if (line.includes('\t')) {
            values = line.split('\t');
        } else if (line.includes(',')) {
            const numberPattern = /^[\d,]+\.?\d*$/;
            if (numberPattern.test(line.trim())) {
                values = [line.trim()];
            } else {
                values = splitWithNumberProtection(line);
            }
        } else {
            values = line.split(/\s+/);
        }

        let pasteCount = 0;

        for (let i = 0; i < values.length && (startIndex + i) < pasteFields.length; i++) {
            const fieldIndex = startIndex + i;
            const field = pasteFields[fieldIndex];
            const value = values[i].trim();

            // 跳过销售额字段（不可编辑）
            if (field === 'sales') {
                continue;
            }

            if (value && value !== '') {
                const input = document.querySelector(`input[data-field="${field}"][data-day="${targetDay}"]`);
                if (input && !input.classList.contains('auto-filled')) {
                    let cleanValue = value.replace(/[^\d.,-]/g, '');
                    cleanValue = cleanValue.replace(/,/g, '');

                    const numValue = parseFloat(cleanValue);
                    if (!isNaN(numValue)) {
                        input.value = cleanValue;
                        pasteCount++;
                    }
                }
            }
        }

        updateCalculations(targetDay);

        if (pasteCount > 0) {
            const fieldNames = {
                'c_beverage': '饮料成本',
                'c_kitchen': '厨房成本'
            };
            const startFieldName = startField && startField !== 'sales' ? fieldNames[startField] : '第一列';
            showAlert(`从${startFieldName}开始成功粘贴 ${pasteCount} 个字段到第${targetDay}日（销售额字段自动从KPI获取，不可编辑）`, 'success');
        } else {
            showAlert('未能识别有效的数据格式', 'error');
        }
    }
}

// 保存所有数据
async function saveAllData() {
    if (isLoading) return;

    const evt = (typeof event !== 'undefined') ? event : null;
    const saveBtn = evt && evt.target ? (evt.target.closest('button') || evt.target) : null;
    if (!saveBtn) {
        showAlert('未能识别保存按钮事件，请刷新页面后重试', 'warning');
        return;
    }
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<div class="loading"></div> 保存中...';
    saveBtn.disabled = true;

    try {
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        let successCount = 0;
        let skipCount = 0;
        let errorCount = 0;
        const errors = [];

        // 先保存库存数据
        const stockValue = document.getElementById('current-stock-input').value;
        if (stockValue && stockValue.trim() !== '') {
            try {
                const yearMonth = `${currentYear}-${currentMonth.toString().padStart(2, '0')}`;
                const stockData = {
                    year_month: yearMonth,
                    current_stock: parseFloat(stockValue) || 0,
                    restaurant: currentRestaurant
                };

                const stockResult = await apiCall('?action=save_month_stock', {
                    method: 'POST',
                    body: JSON.stringify(stockData)
                });

                if (!stockResult.success) {
                    showAlert('库存数据保存失败：' + (stockResult.message || '未知错误'), 'warning');
                }
            } catch (error) {
                console.error('保存库存失败:', error);
                showAlert('库存数据保存失败', 'warning');
            }
        }

        // 保存成本数据
        for (let day = 1; day <= daysInMonth; day++) {
            const sales = parseFloat(getInputValue('sales', day)) || 0;
            const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
            const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;

            const hasData = cBeverage > 0 || cKitchen > 0;

            if (hasData) {
                const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

                const recordData = {
                    date: dateStr,
                    c_beverage: cBeverage,
                    c_kitchen: cKitchen,
                    restaurant: currentRestaurant
                };

                try {
                    let result;
                    // 只有存在数据库记录ID时才执行更新（PUT）
                    // 某些日期可能只有从KPI同步来的销售额（客户端临时数据），此时 monthData[day] 存在但没有 id
                    if (monthData[day] && monthData[day].id) {
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

                        // 如果后端提示“该日期已存在”，说明数据库已有记录但前端没有拿到 id
                        // 这常见于：KPI 触发器先插入了 cost 记录，或 cost 记录是其它流程生成的
                        if (result && result.success === false && String(result.message || '').includes('已存在')) {
                            const existing = await getExistingCostRecordByDate(dateStr);
                            if (existing && existing.id) {
                                monthData[day] = { ...(monthData[day] || {}), ...existing };
                                recordData.id = existing.id;
                                result = await apiCall('', {
                                    method: 'PUT',
                                    body: JSON.stringify(recordData)
                                });
                            }
                        }
                    }

                    if (result.success === true) {
                        successCount++;
                        // 更新 monthData 以包含保存后的数据（包括 id）
                        if (result.data && result.data.id) {
                            monthData[day] = { ...(monthData[day] || {}), ...result.data };
                        }
                    } else if (result.success === false) {
                        const message = result.message || '';
                        if (message.includes('已存在') || message.includes('无变化')) {
                            skipCount++;
                        } else {
                            errorCount++;
                            errors.push(`${day}日: ${message}`);
                        }
                    } else {
                        successCount++;
                        // 即使 success 字段未定义，如果返回了数据，也更新 monthData
                        if (result.data && result.data.id) {
                            monthData[day] = { ...(monthData[day] || {}), ...result.data };
                        }
                    }

                } catch (error) {
                    errorCount++;
                    errors.push(`${day}日: ${error.message}`);
                }
            }
        }

        if (successCount > 0 || skipCount > 0) {
            let message = '';
            if (successCount > 0 && skipCount > 0) {
                message = `数据处理完成！成功保存 ${successCount} 条记录，${skipCount} 条记录无需更新`;
            } else if (successCount > 0) {
                message = `数据保存成功！共保存 ${successCount} 条记录`;
            } else if (skipCount > 0) {
                message = `数据检查完成！${skipCount} 条记录已是最新，无需更新`;
            }

            if (stockValue && stockValue.trim() !== '') {
                message += '，库存数据已保存';
            }

            if (errorCount > 0) {
                message += `，${errorCount} 条记录保存失败`;
            }

            showAlert(message, successCount > 0 ? 'success' : 'info');

            // 重新加载数据以确保显示最新的数据库值
            await loadMonthData();
        } else if (errorCount > 0) {
            showAlert(`保存失败：${errors.join('; ')}`, 'error');
            // 即使保存失败，也重新加载数据以确保一致性
            await loadMonthData();
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

// 清空单日“成本”数据（只清空饮料/厨房成本，保留销售额）
async function clearDayData(day) {
    if (!confirm(`确定要清空${day}日的饮料成本/厨房成本吗？销售额将保留（从KPI自动获取）。`)) {
        return;
    }

    const deleteBtn = event.target.closest('.delete-day-btn');
    const originalHTML = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<div class="loading"></div>';
    deleteBtn.disabled = true;

    try {
        const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

        // 只清空成本字段（销售额从 KPI 表获取，不存储在 cost 表）
        const recordData = {
            date: dateStr,
            c_beverage: 0,
            c_kitchen: 0,
            restaurant: currentRestaurant
        };

        let result = null;
        let id = monthData[day] && monthData[day].id ? monthData[day].id : null;

        // 如果前端没有 id，尝试按日期获取数据库记录（例如 KPI 触发器已提前写入 cost）
        if (!id) {
            const existing = await getExistingCostRecordByDate(dateStr);
            if (existing && existing.id) {
                monthData[day] = { ...(monthData[day] || {}), ...existing };
                id = existing.id;
            }
        }

        if (id) {
            // 清空成本：PUT 时不传 sales，避免任何情况下把销售额覆盖成 0
            const payload = {
                id,
                date: dateStr,
                c_beverage: 0,
                c_kitchen: 0,
                restaurant: currentRestaurant
            };
            result = await apiCall('', {
                method: 'PUT',
                body: JSON.stringify(payload)
            });
        } else {
            // 数据库确实没有记录时，插入一条 0 成本记录
            result = await apiCall('', {
                method: 'POST',
                body: JSON.stringify(recordData)
            });
        }

        if (result && result.success === false) {
            throw new Error(result.message || '清空成本失败');
        }

        // 清空输入框（仅成本字段），销售额保持
        const costFields = ['c_beverage', 'c_kitchen'];
        costFields.forEach(field => {
            const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
            if (input) input.value = '';
        });

        updateCalculations(day);
        updateInputColors();

        showAlert(`${day}日成本已清空（销售额保留）`, 'success');

    } catch (error) {
        showAlert(`清空${day}日成本失败: ${error.message}`, 'error');
        console.error('清空成本失败:', error);
    } finally {
        deleteBtn.innerHTML = originalHTML;
        deleteBtn.disabled = false;
    }
}

// 输入框光标定位处理
let inputFirstClickMap = new Map();

function handleInputFocus(input, isClick = false) {
    setTimeout(() => {
        if (isClick) {
            const inputKey = `${input.dataset.field}-${input.dataset.day}`;

            if (inputFirstClickMap.has(inputKey)) {
                return;
            }

            inputFirstClickMap.set(inputKey, true);
        }

        // 注意：type="number" 的 input 在部分浏览器（如 Chrome）不支持 selection API，
        // 调用 select()/setSelectionRange() 会抛 InvalidStateError，导致后续逻辑中断。
        const inputType = (input.getAttribute('type') || input.type || '').toLowerCase();
        if (inputType === 'number') {
            return;
        }

        try {
            if (input.value) {
                input.select();
            } else if (typeof input.setSelectionRange === 'function') {
                input.setSelectionRange(0, 0);
            }
        } catch (err) {
            // 忽略 selection 相关异常，确保不影响保存/计算等其它功能
        }
    }, 0);
}

function resetInputFirstClick(input) {
    const inputKey = `${input.dataset.field}-${input.dataset.day}`;
    inputFirstClickMap.delete(inputKey);
}

// 键盘快捷键支持
document.addEventListener('keydown', function (e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllData();
    }

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

    if (e.ctrlKey && e.key === 'v') {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.classList.contains('excel-input')) {
            const day = parseInt(activeElement.dataset.day);
            const currentField = activeElement.dataset.field;
            const row = activeElement.closest('tr');

            if (!row.classList.contains('editing-row')) {
                showAlert(`请先点击编辑按钮进入${day}日的编辑模式`, 'info');
                e.preventDefault();
                return;
            }

            pasteTargetDay = day;
            e.preventDefault();

            if (navigator.clipboard && navigator.clipboard.readText) {
                navigator.clipboard.readText().then(function (clipboardData) {
                    if (clipboardData) {
                        handlePasteData(clipboardData, pasteTargetDay, currentField);
                    } else {
                        showAlert('剪贴板为空', 'info');
                    }
                }).catch(function (err) {
                    showAlert('无法访问剪贴板，请手动输入或使用右键粘贴', 'error');
                });
            } else {
                showAlert('请使用右键菜单粘贴，或直接在输入框中按Ctrl+V', 'info');
            }
        }
    }
});

// 输入框事件处理
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('excel-input')) {
        const field = e.target.dataset.field;
        const value = e.target.value;

        resetInputFirstClick(e.target);

        if (currencyFields.includes(field)) {
            if (value.includes('.')) {
                const parts = value.split('.');
                if (parts[1] && parts[1].length > 2) {
                    e.target.value = parts[0] + '.' + parts[1].substring(0, 2);
                }
            }
        }

        updateInputColors();
    }
});

// 货币输入框失去焦点时格式化
document.addEventListener('blur', function (e) {
    if (e.target.classList.contains('currency-input')) {
        const value = e.target.value;
        if (value && !isNaN(value) && value !== '') {
            const num = parseFloat(value);
            e.target.value = num.toFixed(2);
        }
    }
}, true);

// 输入框focus事件
document.addEventListener('focus', function (e) {
    if (e.target.classList.contains('excel-input')) {
        handleInputFocus(e.target, false);
    }
}, true);

// 输入框click事件
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('excel-input')) {
        handleInputFocus(e.target, true);
    }
});

// 监听粘贴事件
document.addEventListener('paste', function (e) {
    if (e.target.classList.contains('excel-input')) {
        const day = parseInt(e.target.dataset.day);
        const currentField = e.target.dataset.field;
        const row = e.target.closest('tr');

        if (!row.classList.contains('editing-row')) {
            showAlert(`请先点击编辑按钮进入${day}日的编辑模式`, 'info');
            e.preventDefault();
            return;
        }

        const clipboardData = e.clipboardData || window.clipboardData;
        const pastedData = clipboardData.getData('text');

        if (pastedData && (pastedData.includes('\t') || pastedData.includes(',') || pastedData.split(/\s+/).length > 1)) {
            e.preventDefault();
            handlePasteData(pastedData, day, currentField);
        }
    }
});

// 切换数字下拉菜单
function toggleNumberDropdown() {
    if (!restaurantDropdownEnabled) return;
    const dropdown = document.getElementById('number-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }

    // 更新选中状态
    updateSelectedNumber();
}

// 选择餐厅数字
function selectNumber(number) {
    const restaurant = `j${number}`;
    if (!availableRestaurants.includes(restaurant)) {
        showAlert('您没有权限查看该店铺', 'warning');
        return;
    }
    const dropdown = document.getElementById('number-dropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
    }
    switchRestaurant(restaurant);
}

// 更新选中的数字状态
function updateSelectedNumber() {
    if (!restaurantDropdownEnabled) return;
    const info = restaurantConfig[currentRestaurant];
    const currentNumber = info ? String(info.number) : '';
    document.querySelectorAll('.number-item').forEach(item => {
        item.classList.remove('selected');
        if (item.textContent === currentNumber) {
            item.classList.add('selected');
        }
    });
}

// 切换报表类型下拉菜单
function toggleReportTypeDropdown() {
    if (!reportDropdownEnabled) return;
    const dropdown = document.getElementById('report-type-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// 点击外部关闭下拉菜单
document.addEventListener('click', function (e) {
    if (!e.target.closest('.number-dropdown')) {
        const dropdown = document.getElementById('number-dropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
        }
    }

    // 关闭报表类型下拉菜单
    if (!e.target.closest('.report-type-selector')) {
        const reportDropdown = document.getElementById('report-type-dropdown');
        if (reportDropdown) {
            reportDropdown.classList.remove('show');
        }
    }
});

// 切换编辑模式
function toggleEdit(day) {
    const editBtn = document.getElementById(`edit-btn-${day}`);
    const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
    const isEditing = editBtn.classList.contains('save-mode');

    if (isEditing) {
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
        const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
        const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;

        const hasData = cBeverage > 0 || cKitchen > 0;

        if (hasData) {
            const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

            const recordData = {
                date: dateStr,
                c_beverage: cBeverage,
                c_kitchen: cKitchen,
                restaurant: currentRestaurant
            };

            let result;
            // 只有存在数据库记录ID时才执行更新（PUT）
            // monthData[day] 可能来自 KPI 同步的临时数据，没有 id，必须走 POST 插入
            if (monthData[day] && monthData[day].id) {
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

                // 如果提示“已存在”，则按日期查出 id，再改为 PUT 更新一次
                if (result && result.success === false && String(result.message || '').includes('已存在')) {
                    const existing = await getExistingCostRecordByDate(dateStr);
                    if (existing && existing.id) {
                        monthData[day] = { ...(monthData[day] || {}), ...existing };
                        recordData.id = existing.id;
                        result = await apiCall('', {
                            method: 'PUT',
                            body: JSON.stringify(recordData)
                        });
                    }
                }
            }

            if (result.success === true || result.success !== false) {
                showAlert(`${day}日数据保存成功`, 'success');

                // 重新加载整个月的数据以确保一致性
                setRowReadonly(day, true);
                editBtn.classList.remove('save-mode');
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.title = `编辑${day}日数据`;

                await loadMonthData(true);
                return; // 提前返回，因为 loadMonthData 会刷新所有内容
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

        setRowReadonly(day, true);

        editBtn.classList.remove('save-mode');
        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
        editBtn.title = `编辑${day}日数据`;

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

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);
