// API 配置
const API_BASE_URL = 'kpiapi.php';

const availableReportTypes = window.kpiConfig.reportPermissions;
const reportDropdownEnabled = window.kpiConfig.showReportDropdown;
const availableRestaurants = window.kpiConfig.restaurantPermissions;
const restaurantDropdownEnabled = window.kpiConfig.showRestaurantDropdown;
const restaurantConfig = window.kpiConfig.restaurantConfigAllowed;

// 用户职位权限控制
const isOperationManager = window.kpiConfig.isOperationManager;
const operationManagerEditableFields = ['new_customers', 'returning_customers']; // Operation Manager 可以编辑的字段

// 应用状态
let currentRestaurant = window.kpiConfig.defaultRestaurant;
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth() + 1;
let monthData = {};
let isLoading = false;
let pasteTargetDay = null;

// 货币字段列表 - 添加 adj_amount 字段
const currencyFields = ['gross_sales', 'discounts', 'tax', 'service_fee', 'adj_amount', 'tender_amount'];

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
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer) {
        tableContainer.innerHTML = `
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

        // 检查是否是会话过期
        if (data.code === 'SESSION_EXPIRED') {
            showSessionExpiredMessage();
            return { success: false, code: 'SESSION_EXPIRED' };
        }

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
                        ${!isOperationManager ? '<button class="delete-day-btn" onclick="clearDayData(' + day + ')" title="清空' + day + '日数据"><i class="fas fa-trash-alt"></i></button>' : ''}
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

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 页面卸载时停止会话刷新
window.addEventListener('beforeunload', function () {
    stopSessionRefresh();
});

// 设置行的只读状态
function setRowReadonly(day, readonly) {
    const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
    const row = document.querySelector(`input[data-day="${day}"]`).closest('tr');

    inputs.forEach(input => {
        const field = input.dataset.field;

        // 如果是 Operation Manager，只允许编辑特定字段
        if (isOperationManager) {
            const canEdit = !readonly && operationManagerEditableFields.includes(field);
            if (canEdit) {
                input.classList.remove('readonly');
                input.removeAttribute('readonly');
                input.removeAttribute('disabled');
            } else {
                input.classList.add('readonly');
                input.setAttribute('readonly', 'readonly');
                input.setAttribute('disabled', 'disabled');
            }
        } else {
            // 非 Operation Manager，正常处理
            if (readonly) {
                input.classList.add('readonly');
                input.setAttribute('readonly', 'readonly');
                input.setAttribute('disabled', 'disabled');
            } else {
                input.classList.remove('readonly');
                input.removeAttribute('readonly');
                input.removeAttribute('disabled');
            }
        }
    });

    // 切换行的编辑样式（只有当至少有一个字段可编辑时才显示编辑样式）
    if (readonly) {
        row.classList.remove('editing-row');
    } else {
        // 检查是否有可编辑的字段
        const hasEditableField = Array.from(inputs).some(input => {
            if (isOperationManager) {
                return operationManagerEditableFields.includes(input.dataset.field);
            }
            return true;
        });
        if (hasEditableField) {
            row.classList.add('editing-row');
        }
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
document.addEventListener('keydown', function (e) {
    // Ctrl+S 保存数据
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllData();
    }

    // 粘贴功能
    if (e.ctrlKey && e.key === 'v') {
        // 不阻止默认行为，通过paste事件处理
    }
});

// 监听粘贴事件
document.addEventListener('paste', function (e) {
    // 如果焦点在输入框上
    if (e.target.classList.contains('excel-input')) {
        // 如果只读，不处理
        if (e.target.hasAttribute('readonly') || e.target.disabled) {
            return;
        }

        // 获取剪贴板数据
        const clipboardData = (e.clipboardData || window.clipboardData).getData('text');
        if (!clipboardData) return;

        // 检查是否是多行或者包含制表符的数据（Excel复制的数据）
        if (clipboardData.includes('\n') || clipboardData.includes('\t')) {
            e.preventDefault();

            const day = parseInt(e.target.dataset.day);
            const field = e.target.dataset.field;

            // 尝试智能粘贴
            handlePasteData(clipboardData, day, field);
        }
    }
});

// 启用编辑模式
function toggleEdit(day) {
    // 检查是否有编辑权限
    if (!availableRestaurants.includes(currentRestaurant)) {
        showAlert('您没有权限编辑该店铺数据', 'warning');
        return;
    }

    const row = document.querySelector(`input[data-day="${day}"]`).closest('tr');
    const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
    const editBtn = document.getElementById(`edit-btn-${day}`);

    // 检查当前是否是编辑状态（通过检查第一个非OperationManager限制的输入框）
    // 注意：Operation Manager模式下，某些字段可能永远只读，所以我们要检查是否有"只读"标记
    let isEditing = row.classList.contains('editing-row');

    if (isEditing) {
        // 保存并退出编辑模式
        setRowReadonly(day, true);
        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
        editBtn.classList.remove('save-mode');
        editBtn.title = `编辑${day}日数据`;

        // 检查数据是否有变更并保存
        // 这里可以调用单日保存逻辑，或者提示用户点击保存按钮
        // 为了简单起见，我们只切换UI状态，用户需要点击"保存本月数据"
        showAlert('已退出编辑模式，请记得点击上方"保存本月数据"按钮保存更改', 'info');
    } else {
        // 进入编辑模式
        setRowReadonly(day, false);

        // 检查是否有字段变为可编辑（可能因为Operation Manager限制导致没有字段可编辑）
        const hasEditable = Array.from(inputs).some(input => !input.hasAttribute('readonly'));

        if (hasEditable) {
            editBtn.innerHTML = '<i class="fas fa-check"></i>';
            editBtn.classList.add('save-mode');
            editBtn.title = `完成编辑`;
            // 聚焦到第一个可编辑的输入框
            const firstEditable = Array.from(inputs).find(input => !input.hasAttribute('readonly'));
            if (firstEditable) {
                firstEditable.focus();
            }
        } else {
            showAlert('当前权限无法编辑此行数据', 'warning');
        }
    }
}

// 监听点击、焦点事件，处理光标位置
const table = document.getElementById('excel-table');
if (table) {
    table.addEventListener('click', function (e) {
        if (e.target.classList.contains('excel-input') && !e.target.readOnly) {
            handleInputFocus(e.target, true);
        }
    });

    table.addEventListener('focusin', function (e) {
        if (e.target.classList.contains('excel-input') && !e.target.readOnly) {
            // 如果不是点击触发的（即Tab切换），则处理
            // 点击触发的由click事件处理
        }
    });
}

// 切换餐厅下拉菜单
function toggleNumberDropdown() {
    if (!restaurantDropdownEnabled) return;
    const dropdown = document.getElementById('number-dropdown');
    dropdown.classList.toggle('show');

    // 点击外部关闭
    if (dropdown.classList.contains('show')) {
        document.addEventListener('click', closeNumberDropdownOutside);
    } else {
        document.removeEventListener('click', closeNumberDropdownOutside);
    }
}

function closeNumberDropdownOutside(event) {
    const dropdown = document.getElementById('number-dropdown');
    const toggle = document.querySelector('.number-dropdown .dropdown-toggle');
    if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
        dropdown.classList.remove('show');
        document.removeEventListener('click', closeNumberDropdownOutside);
    }
}

function selectNumber(number) {
    // Find restaurant key by number
    let targetRestaurant = null;
    for (const key in restaurantConfig) {
        if (restaurantConfig[key].number == number) {
            targetRestaurant = key;
            break;
        }
    }

    if (targetRestaurant) {
        switchRestaurant(targetRestaurant);
        document.getElementById('number-dropdown').classList.remove('show');
        document.removeEventListener('click', closeNumberDropdownOutside);
    }
}

// 切换报表类型下拉菜单
function toggleReportTypeDropdown() {
    if (!reportDropdownEnabled) return;
    const dropdown = document.getElementById('report-type-dropdown');
    dropdown.classList.toggle('show');

    // 点击外部关闭
    if (dropdown.classList.contains('show')) {
        document.addEventListener('click', closeReportTypeDropdownOutside);
    } else {
        document.removeEventListener('click', closeReportTypeDropdownOutside);
    }
}

function closeReportTypeDropdownOutside(event) {
    const dropdown = document.getElementById('report-type-dropdown');
    const toggle = document.querySelector('.report-type-selector');
    if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
        dropdown.classList.remove('show');
        document.removeEventListener('click', closeReportTypeDropdownOutside);
    }
}
