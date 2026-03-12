

// 检查用户权限的函数
async function checkUserPermissions() {
    try {
        const response = await fetch('/backendtest/api/check_permissions_api.php');
        const result = await response.json();
        return {
            canApprove: result.canApprove || false,
            canApply: result.canApply || false
        };
    } catch (error) {
        console.error('检查权限失败:', error);
        return { canApprove: false, canApply: false };
    }
}

// 全局变量存储用户权限
let userCanApprove = false;
let userCanApply = false;

// 初始化权限检查
async function initPermissions() {
    const perms = await checkUserPermissions();
    userCanApprove = perms.canApprove;
    userCanApply = perms.canApply;

    // 根据权限控制全局按钮显示
    const addBtn = document.querySelector('.add-row-btn');
    const saveAllBtn = document.querySelector('.save-all-btn');
    if (addBtn) addBtn.style.display = userCanApply ? 'inline-block' : 'none';
    if (saveAllBtn) saveAllBtn.style.display = userCanApply ? 'inline-block' : 'none';
}

const API_BASE_URL = 'stockapi.php';  // 如果在同一目录
const SYSTEM_TYPE = 'J1';  // 当前系统类型

// 应用状态
let stockData = [];
let isLoading = false;
let nextRowId = 1;

// 输入框光标定位处理
let inputFirstClickMap = new Map(); // 记录每个输入框是否已经被点击过

function handleInputFocus(input, isClick = false) {
    // 延迟执行以确保focus事件完成后再设置光标位置
    setTimeout(() => {
        if (isClick) {
            const inputKey = `${input.dataset.field}-${input.dataset.row}`;

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
    const inputKey = `${input.dataset.field}-${input.dataset.row}`;
    inputFirstClickMap.delete(inputKey);
}

// 货币字段列表
const currencyFields = [];

// 格式化货币输入值显示
function formatCurrencyDisplay(value) {
    if (!value || value === '') return '';
    const num = parseFloat(value);
    if (isNaN(num)) return 0.00;
    return num.toFixed(2);
}

// 初始化应用
async function initApp() {
    await initPermissions();
    loadStockData();
    initRealTimeSearch(); // 添加这行
}

// 切换视图选择器下拉菜单
function toggleViewSelector() {
    const dropdown = document.getElementById('view-selector-dropdown');
    dropdown.classList.toggle('show');
}

function switchView(viewType) {
    if (viewType === 'list') {
        // 跳转到总库存页面
        window.location.href = 'stocklistall.php';
    } else if (viewType === 'records') {
        // 跳转到进出货页面
        window.location.href = 'stockeditall.php';
    } else if (viewType === 'remark') {
        // 跳转到货品备注页面
        window.location.href = 'stockremark.php';
    } else {
        // 保持在当前页面（货品种类）
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

// 切换系统选择器下拉菜单
function toggleSystemSelector() {
    const dropdown = document.getElementById('system-selector-dropdown');
    dropdown.classList.toggle('show');
}

// 切换系统
function switchSystem(system) {
    const systemPages = {
        'overview': 'stockproductname.php',
        'central': 'centerstockproductname.php',
        'j1': 'j1stockproductname.php',
        'j2': 'j2stockproductname.php',
        'j3': 'j3stockproductname.php'
    };

    if (systemPages[system]) {
        if (system === 'j1') {
            // 如果点击的是当前页面（J1），只关闭下拉菜单
            document.getElementById('system-selector-dropdown').classList.remove('show');
        } else {
            // 跳转到其他系统页面
            window.location.href = systemPages[system];
        }
    }
}

// 返回上一页
function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

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

// API 调用函数
async function apiCall(endpoint, options = {}) {
    try {
        console.log('API调用:', `${API_BASE_URL}${endpoint}`, options);

        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });

        const responseText = await response.text();
        console.log('API响应:', responseText);

        if (!response.ok) {
            throw new Error(`HTTP错误: ${response.status} - ${responseText}`);
        }

        const data = JSON.parse(responseText);
        console.log('解析后的数据:', data);
        return data;
    } catch (error) {
        console.error('API调用失败:', error);
        throw error;
    }
}

// 加载库存数据
async function loadStockData() {
    if (isLoading) return;

    isLoading = true;

    try {
        // 获取搜索参数
        const productSearch = document.getElementById('product-search-filter').value.trim();
        const approvalStatus = document.getElementById('approval-status-filter').value.trim();

        // 构建URL参数
        const params = new URLSearchParams();
        params.append('action', 'list');
        params.append('system_assign', SYSTEM_TYPE);  // 自动过滤J1系统数据

        if (productSearch) params.append('product_search', productSearch);
        if (approvalStatus) params.append('approval_status', approvalStatus);

        const url = `${API_BASE_URL}?${params.toString()}`;
        console.log('请求URL:', url);

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const responseText = await response.text();
        console.log('API响应文本:', responseText);

        if (!response.ok) {
            throw new Error(`HTTP错误: ${response.status} - ${responseText}`);
        }

        const result = JSON.parse(responseText);
        console.log('解析后的数据:', result);

        if (result.success) {
            stockData = result.data || [];
            generateStockTable();
            updateStats();
            showAlert(`库存数据加载成功，共找到 ${stockData.length} 条记录`, 'success');
        } else {
            throw new Error(result.message || '加载失败');
        }

    } catch (error) {
        console.error('加载数据失败:', error);
        stockData = [];
        generateStockTable();
        updateStats();
        showAlert('数据加载失败: ' + error.message, 'error');
    } finally {
        isLoading = false;
    }
}

// 实时搜索功能
function initRealTimeSearch() {
    const productSearchInput = document.getElementById('product-search-filter');
    const approvalStatusSelect = document.getElementById('approval-status-filter');

    // 防抖函数
    function debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // 创建防抖版本的搜索函数
    const debouncedSearch = debounce(loadStockData, 300);

    // 为产品搜索输入框添加实时搜索
    if (productSearchInput) {
        productSearchInput.addEventListener('input', debouncedSearch);
    }

    // 为批准状态选择框添加实时搜索
    if (approvalStatusSelect) {
        approvalStatusSelect.addEventListener('change', loadStockData);
    }
}

// 清空过滤器函数（保留但简化）
function clearFilters() {
    document.getElementById('product-search-filter').value = '';
    document.getElementById('approval-status-filter').value = '';

    showAlert('过滤器已清空，重新加载所有数据', 'info');
    loadStockData();
}

// 生成库存表格
function generateStockTable() {
    const tbody = document.getElementById('excel-tbody');
    tbody.innerHTML = '';

    // 按产品名称的第一个字母排序
    const sortedData = [...stockData].sort((a, b) => {
        const nameA = (a.product_name || '').trim().toLowerCase();
        const nameB = (b.product_name || '').trim().toLowerCase();

        if (nameA < nameB) return -1;
        if (nameA > nameB) return 1;
        return 0;
    });

    sortedData.forEach((item, index) => {
        const row = createStockRow(item, index);
        tbody.appendChild(row);
    });
}

// 创建库存行
function createStockRow(data = {}, index = -1) {
    const row = document.createElement('tr');
    const isNewRow = index === -1;
    const rowId = isNewRow ? `new-${nextRowId++}` : data.id || index;

    if (isNewRow) {
        row.classList.add('new-row');
    }

    // 根据批准状态设置行样式
    if (data.approver) {
        row.classList.add('status-approved');
    } else if (!isNewRow) {
        row.classList.add('status-pending');
    }

    row.innerHTML = `
                <td class="serial-number-cell">
                    ${isNewRow ? '-' : (index + 1)}
                </td>
                <td>
                    <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="product_code" data-row="${rowId}" 
                        value="${data.product_code || ''}" placeholder="产品编号" required ${!isNewRow ? 'readonly disabled' : ''}>
                </td>
                <td>
                    <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="product_name" data-row="${rowId}" 
                        value="${data.product_name || ''}" placeholder="产品名称" required ${!isNewRow ? 'readonly disabled' : ''}>
                </td>
                <td>
                    <select class="excel-select ${!isNewRow ? 'readonly' : ''}" data-field="specification" data-row="${rowId}" 
                        required ${!isNewRow ? 'disabled' : ''}>
                        <option value="">选择规格</option>
                        <option value="Tub" ${data.specification === 'Tub' ? 'selected' : ''}>Tub</option>
                        <option value="Kilo" ${data.specification === 'Kilo' ? 'selected' : ''}>Kilo</option>
                        <option value="Piece" ${data.specification === 'Piece' ? 'selected' : ''}>Piece</option>
                        <option value="Bottle" ${data.specification === 'Bottle' ? 'selected' : ''}>Bottle</option>
                        <option value="Box" ${data.specification === 'Box' ? 'selected' : ''}>Box</option>
                        <option value="Packet" ${data.specification === 'Packet' ? 'selected' : ''}>Packet</option>
                        <option value="Carton" ${data.specification === 'Carton' ? 'selected' : ''}>Carton</option>
                        <option value="Tin" ${data.specification === 'Tin' ? 'selected' : ''}>Tin</option>
                        <option value="Roll" ${data.specification === 'Roll' ? 'selected' : ''}>Roll</option>
                        <option value="Nos" ${data.specification === 'Nos' ? 'selected' : ''}>Nos</option>
                    </select>
                </td>
                <td>
                    <select class="excel-select ${!isNewRow ? 'readonly' : ''}" data-field="category" data-row="${rowId}" 
                        required ${!isNewRow ? 'disabled' : ''}>
                        <option value="">选择类型</option>
                        <option value="Drinks" ${data.category === 'Drinks' ? 'selected' : ''}>Drinks</option>
                        <option value="Sake" ${data.category === 'Sake' ? 'selected' : ''}>Sake</option>
                        <option value="Kitchen" ${data.category === 'Kitchen' ? 'selected' : ''}>Kitchen</option>
                        <option value="Sushi Bar" ${data.category === 'Sushi Bar' ? 'selected' : ''}>Sushi Bar</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="supplier" data-row="${rowId}" 
                        value="${data.supplier || ''}" placeholder="供应商名称" required ${!isNewRow ? 'readonly disabled' : ''}>
                </td>
                <td>
                    <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="applicant" data-row="${rowId}" 
                        value="${data.applicant || ''}" placeholder="申请人" required ${!isNewRow ? 'readonly disabled' : ''}>
                </td>
                <td>
                    <select class="excel-select readonly" data-field="system_assign" data-row="${rowId}" disabled>
                        <option value="J1" selected>J1</option>
                    </select>
                </td>
                <td style="padding: 8px;">
                    ${data.approver ?
            '<span style="color: #065f46; font-weight: 600;">已批准</span>' :
            '<span style="color: #92400e; font-weight: 600;">待批准</span>'
        }
                </td>
                <td class="action-cell">
                    ${userCanApply ? `
                    <button class="edit-btn ${isNewRow ? 'save-mode' : ''}" id="edit-btn-${rowId}" onclick="toggleEdit('${rowId}')" title="${isNewRow ? '保存记录' : '编辑记录'}">
                        <i class="fas ${isNewRow ? 'fa-save' : 'fa-edit'}"></i>
                    </button>
                    <button class="delete-row-btn" onclick="deleteRow('${rowId}')" title="删除此行">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    ` : ''}
                </td>
            `;

    return row;
}

// 添加新行
function addNewRow() {
    if (!userCanApply) {
        showAlert('您没有权限添加记录 (缺少[申请权限])', 'error');
        return;
    }
    const tbody = document.getElementById('excel-tbody');

    const newData = {
        product_code: '',
        product_name: '',
        specification: '',
        category: '',
        supplier: '',
        applicant: '',
        system_assign: SYSTEM_TYPE,  // 自动设置为J1系统
        approver: ''
    };

    const newRow = createStockRow(newData);
    tbody.appendChild(newRow);

    // 聚焦到产品编号输入框
    const productCodeInput = newRow.querySelector('input[data-field="product_code"]');
    if (productCodeInput) {
        productCodeInput.focus();
    }

    // 设置新行为编辑模式（非只读状态）
    setTimeout(() => {
        const rowId = `new-${nextRowId - 1}`; // 获取刚创建的行ID
        const newRowElement = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr');
        if (newRowElement) {
            newRowElement.classList.add('editing-row');
        }
    }, 0);

    updateStats();
}

// 删除行
function deleteRow(rowId) {
    if (!confirm('确定要删除这行数据吗？此操作不可恢复！')) {
        return;
    }

    const row = document.querySelector(`tr:has(input[data-row="${rowId}"])`);
    if (row) {
        // 如果是数据库中的记录，需要调用API删除
        if (!rowId.toString().startsWith('new-')) {
            deleteFromDatabase(rowId);
        }

        row.remove();
        updateStats();
        showAlert('行已删除', 'success');
    }
}

// 从数据库删除记录
async function deleteFromDatabase(id) {
    try {
        const response = await fetch(`${API_BASE_URL}?id=${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        const responseText = await response.text();
        console.log('DELETE响应:', responseText);
        const result = JSON.parse(responseText);

        if (!result.success) {
            throw new Error(result.message || '删除失败');
        }
    } catch (error) {
        showAlert('删除记录失败: ' + error.message, 'error');
    }
}

// 保存所有数据
async function saveAllData() {
    if (isLoading) return;

    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<div class="loading"></div> 保存中...';
    saveBtn.disabled = true;

    try {
        const rows = document.querySelectorAll('#excel-tbody tr');
        let successCount = 0;
        let errorCount = 0;
        const errors = [];

        for (const row of rows) {
            const rowData = extractRowData(row);

            // 验证必填字段
            if (!rowData.product_code || !rowData.product_name || !rowData.specification ||
                !rowData.category || !rowData.supplier || !rowData.applicant) {
                continue; // 跳过不完整的行
            }

            try {
                const rowId = row.querySelector('input').dataset.row;
                let result;

                if (rowId.toString().startsWith('new-')) {
                    // 新记录
                    const response = await fetch(API_BASE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(rowData)
                    });
                    const responseText = await response.text();
                    console.log('POST响应:', responseText);
                    result = JSON.parse(responseText);
                } else {
                    // 更新现有记录
                    rowData.id = rowId;
                    const response = await fetch(API_BASE_URL, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(rowData)
                    });
                    const responseText = await response.text();
                    console.log('PUT响应:', responseText);
                    result = JSON.parse(responseText);
                }

                if (result.success) {
                    successCount++;
                    // 更新行ID（针对新记录）
                    if (rowId.toString().startsWith('new-') && result.data && result.data.id) {
                        updateRowId(row, rowId, result.data.id);
                    }
                } else {
                    throw new Error(result.message || '保存失败');
                }

            } catch (error) {
                errorCount++;
                errors.push(`第${Array.from(rows).indexOf(row) + 1}行: ${error.message}`);
            }
        }

        if (successCount > 0) {
            showAlert(`成功保存 ${successCount} 条记录${errorCount > 0 ? `，${errorCount} 条失败` : ''}`, 'success');
            // 重新加载数据以确保同步
            await loadStockData();
        } else if (errorCount > 0) {
            showAlert(`保存失败：${errors.join('; ')}`, 'error');
        } else {
            showAlert('没有需要保存的完整数据', 'info');
        }

    } catch (error) {
        showAlert('保存过程中发生错误', 'error');
        console.error('保存错误:', error);
    } finally {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }
}

// 提取行数据
function extractRowData(row) {
    const data = {};
    const inputs = row.querySelectorAll('input');
    const selects = row.querySelectorAll('select');

    inputs.forEach(input => {
        const field = input.dataset.field;
        let value = input.value.trim();

        data[field] = value;
    });

    selects.forEach(select => {
        const field = select.dataset.field;
        let value = select.value.trim();

        data[field] = value;
    });

    // 检查是否已批准（通过查看批准状态列的内容）
    const approvalStatusCell = row.querySelector('td:nth-child(9)'); // 批准状态列
    const isApproved = approvalStatusCell && approvalStatusCell.textContent.includes('已批准');

    // 如果已批准，从stockData中获取实际的批准人
    if (isApproved) {
        const rowId = row.querySelector('input').dataset.row;
        const originalRecord = stockData.find(item => item.id == rowId);
        if (originalRecord && originalRecord.approver) {
            data.approver = originalRecord.approver;
        }
    }

    return data;
}

// 更新行ID
function updateRowId(row, oldId, newId) {
    const inputs = row.querySelectorAll('input');
    const selects = row.querySelectorAll('select');

    inputs.forEach(input => {
        if (input.dataset.row === oldId) {
            input.dataset.row = newId;
        }
    });

    selects.forEach(select => {
        if (select.dataset.row === oldId) {
            select.dataset.row = newId;
        }
    });

    const deleteBtn = row.querySelector('.delete-row-btn');
    if (deleteBtn) {
        deleteBtn.setAttribute('onclick', `deleteRow('${newId}')`);
    }

    // 移除新行样式
    row.classList.remove('new-row');
}

// 更新统计信息
function updateStats() {
    const rows = document.querySelectorAll('#excel-tbody tr');
    let totalRecords = rows.length;
    let approvedCount = 0;
    let pendingCount = 0;

    rows.forEach(row => {
        // 检查是否已批准（通过查看批准状态列的内容）
        const approvalStatusCell = row.querySelector('td:nth-child(9)'); // 批准状态列
        const isApproved = approvalStatusCell && approvalStatusCell.textContent.includes('已批准');

        if (isApproved) {
            approvedCount++;
        } else {
            pendingCount++;
        }
    });

    document.getElementById('total-records').textContent = totalRecords;
    document.getElementById('approved-count').textContent = approvedCount;
    document.getElementById('pending-count').textContent = pendingCount;
}

// 清空过滤器
function clearFilters() {
    document.getElementById('product-code-filter').value = '';
    document.getElementById('product-name-filter').value = '';
    document.getElementById('approval-status-filter').value = '';

    showAlert('过滤器已清空，重新加载所有数据', 'info');
    loadStockData();
}

// 完全替换现有的 showAlert 函数
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

// 输入框和下拉选择框事件处理
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('excel-input')) {
        const field = e.target.dataset.field;
        const value = e.target.value;
        const row = e.target.closest('tr');

        resetInputFirstClick(e.target);
        updateStats();
    }
});

// 下拉选择框事件处理
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('excel-select')) {
        const field = e.target.dataset.field;
        const value = e.target.value;
        const row = e.target.closest('tr');

        updateStats();
    }
});


// 键盘快捷键支持
document.addEventListener('keydown', function (e) {
    // Ctrl+S 保存数据
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllData();
    }

    // Ctrl+N 添加新行
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        addNewRow();
    }

    // Tab键在输入框和下拉选择框间移动
    if (e.key === 'Tab') {
        const inputs = Array.from(document.querySelectorAll('.excel-input:not([readonly])'));
        const selects = Array.from(document.querySelectorAll('.excel-select:not([disabled])'));
        const allElements = [...inputs, ...selects].sort((a, b) => {
            const aRow = a.closest('tr');
            const bRow = b.closest('tr');
            if (aRow === bRow) {
                return Array.from(aRow.children).indexOf(a.closest('td')) - Array.from(bRow.children).indexOf(b.closest('td'));
            }
            return Array.from(document.querySelectorAll('#excel-tbody tr')).indexOf(aRow) - Array.from(document.querySelectorAll('#excel-tbody tr')).indexOf(bRow);
        });

        const currentIndex = allElements.indexOf(document.activeElement);

        if (currentIndex !== -1) {
            e.preventDefault();
            const nextIndex = e.shiftKey ?
                (currentIndex - 1 + allElements.length) % allElements.length :
                (currentIndex + 1) % allElements.length;
            allElements[nextIndex].focus();
        }
    }

    // Enter键移动到下一行同一列  
    if (e.key === 'Enter' && (document.activeElement.classList.contains('excel-input') || document.activeElement.classList.contains('excel-select')) && !document.activeElement.readOnly && !document.activeElement.disabled) {
        e.preventDefault();
        const currentElement = document.activeElement;
        const field = currentElement.dataset.field;

        const currentRow = currentElement.closest('tr');
        const nextRow = currentRow.nextElementSibling;

        if (nextRow) {
            const nextElement = nextRow.querySelector(`input[data-field="${field}"]:not([readonly]), select[data-field="${field}"]:not([disabled])`);
            if (nextElement) {
                nextElement.focus();
            }
        } else {
            // 如果是最后一行，添加新行并聚焦
            addNewRow();
            setTimeout(() => {
                const newRow = document.querySelector('#excel-tbody tr:last-child');
                const newElement = newRow.querySelector(`input[data-field="${field}"]:not([readonly]), select[data-field="${field}"]:not([disabled])`);
                if (newElement) {
                    newElement.focus();
                }
            }, 100);
        }
    }
});

// 为所有输入框添加focus事件监听
document.addEventListener('focus', function (e) {
    if (e.target.classList.contains('excel-input')) {
        handleInputFocus(e.target, false);
    }
}, true);

// 为所有输入框添加click事件监听
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('excel-input')) {
        handleInputFocus(e.target, true);
    }
});

// 点击其他地方关闭下拉菜单
document.addEventListener('click', function (event) {
    const selector = event.target.closest('.selector-button');
    const dropdown = event.target.closest('.selector-dropdown');
    const dropdownItem = event.target.closest('.dropdown-item');

    // 如果点击的是下拉选项，立即隐藏对应的下拉菜单
    if (dropdownItem) {
        const parentDropdown = dropdownItem.closest('.selector-dropdown');
        if (parentDropdown) {
            parentDropdown.classList.remove('show');
        }
        return;
    }

    // 如果点击的不是选择器按钮或下拉菜单，隐藏所有下拉菜单
    if (!selector && !dropdown) {
        document.getElementById('view-selector-dropdown')?.classList.remove('show');
        document.getElementById('system-selector-dropdown')?.classList.remove('show');
    }
});

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 批准记录
async function approveRecord(rowId) {
    if (!userCanApprove) {
        showAlert('您没有权限执行此操作', 'error');
        return;
    }

    if (!confirm('确定要批准这条记录吗？')) {
        return;
    }

    const approveBtn = document.querySelector(`button[onclick="approveRecord('${rowId}')"]`);
    const originalText = approveBtn.innerHTML;
    approveBtn.innerHTML = '<div class="loading"></div> 批准中...';
    approveBtn.disabled = true;

    try {
        const response = await fetch(`${API_BASE_URL}?action=approve`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: rowId
            })
        });

        const responseText = await response.text();
        console.log('批准响应:', responseText);
        const result = JSON.parse(responseText);

        if (result.success) {
            // 更新界面
            const row = approveBtn.closest('tr');
            const approverCell = approveBtn.closest('td');

            // 更新批准状态列
            approverCell.innerHTML = `
                        <span style="color: #065f46; font-weight: 600;">已批准</span>
                    `;

            // 更新状态列
            const statusCell = row.querySelector('td:nth-child(9)');
            if (statusCell) {
                statusCell.innerHTML = '<span style="color: #065f46; font-weight: 600;">已批准</span>';
            }

            // 更新行样式
            row.classList.remove('status-pending');
            row.classList.add('status-approved');

            updateStats();
            showAlert('记录已批准', 'success');

        } else {
            throw new Error(result.message || '批准失败');
        }

    } catch (error) {
        console.error('批准失败:', error);
        showAlert('批准失败: ' + error.message, 'error');
        approveBtn.innerHTML = originalText;
        approveBtn.disabled = false;
    }
}

// 切换编辑模式
function toggleEdit(rowId) {
    const editBtn = document.getElementById(`edit-btn-${rowId}`);
    if (!editBtn) {
        console.error(`找不到编辑按钮: edit-btn-${rowId}`);
        return;
    }

    const isEditing = editBtn.classList.contains('save-mode');

    if (isEditing) {
        // 保存模式 - 保存这一行
        saveSingleRowData(rowId);
    } else {
        // 切换到编辑模式
        setRowReadonly(rowId, false);

        editBtn.classList.add('save-mode');
        editBtn.innerHTML = '<i class="fas fa-save"></i>';
        editBtn.title = '保存记录';
    }
}

// 设置行的只读状态
function setRowReadonly(rowId, readonly) {
    const inputs = document.querySelectorAll(`input[data-row="${rowId}"]`);
    const selects = document.querySelectorAll(`select[data-row="${rowId}"]`);
    const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr');

    if (!row) {
        console.error(`找不到行: ${rowId}`);
        return;
    }

    // 处理输入框
    inputs.forEach(input => {
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

    // 处理下拉选择框
    selects.forEach(select => {
        // 系统分配字段始终保持只读，不允许编辑
        if (select.dataset.field === 'system_assign') {
            select.classList.add('readonly');
            select.setAttribute('disabled', 'disabled');
            return; // 跳过系统分配字段的处理
        }

        if (readonly) {
            select.classList.add('readonly');
            select.setAttribute('disabled', 'disabled');
        } else {
            select.classList.remove('readonly');
            select.removeAttribute('disabled');
        }
    });

    // 切换行的编辑样式
    if (readonly) {
        row.classList.remove('editing-row');
    } else {
        row.classList.add('editing-row');
    }
}

// 保存单行数据
async function saveSingleRowData(rowId) {
    const editBtn = document.getElementById(`edit-btn-${rowId}`);
    if (!editBtn) {
        console.error(`找不到编辑按钮: edit-btn-${rowId}`);
        return;
    }

    const originalHTML = editBtn.innerHTML;
    editBtn.innerHTML = '<div class="loading"></div>';
    editBtn.disabled = true;

    try {
        const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr');
        if (!row) {
            throw new Error('找不到对应的行');
        }

        const rowData = extractRowData(row);
        console.log('提取的行数据:', rowData);

        // 验证必填字段（只对新记录进行严格验证）
        const isNewRecord = rowId.toString().startsWith('new-');

        if (isNewRecord) {
            // 新记录必须填写所有必填字段
            if (!rowData.product_code || !rowData.product_name || !rowData.specification ||
                !rowData.category || !rowData.supplier || !rowData.applicant) {
                throw new Error('请填写所有必填字段');
            }
        } else {
            // 现有记录允许部分字段为空，但至少要有产品编号或产品名称
            if (!rowData.product_code && !rowData.product_name) {
                throw new Error('产品编号和产品名称至少需要填写一个');
            }
        }

        let result;

        // 如果是编辑现有记录，清除批准状态（编辑后需要重新批准）
        if (!isNewRecord) {
            // 清除批准状态，需要在总览页面重新批准
            rowData.approver = '';
        }

        if (isNewRecord) {
            // 新记录
            const response = await fetch(API_BASE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(rowData)
            });
            const responseText = await response.text();
            console.log('POST响应:', responseText);
            result = JSON.parse(responseText);

            if (result.success && result.data && result.data.id) {
                // 更新行ID和相关元素
                const newId = result.data.id;
                updateRowIdComplete(row, rowId, newId);
                // 更新当前使用的rowId变量
                rowId = newId;
            }
        } else {
            // 更新现有记录
            rowData.id = rowId;
            const response = await fetch(API_BASE_URL, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(rowData)
            });
            const responseText = await response.text();
            console.log('PUT响应:', responseText);
            result = JSON.parse(responseText);
        }

        if (result.success) {
            showAlert('记录保存成功，需要在总览页面重新批准', 'success');

            // 如果是编辑现有记录，更新状态列显示为"待批准"
            if (!isNewRecord) {
                const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr');
                if (row) {
                    const statusCell = row.querySelector('td:nth-child(9)');
                    if (statusCell) {
                        statusCell.innerHTML = '<span style="color: #92400e; font-weight: 600;">待批准</span>';
                    }
                    // 更新行样式
                    row.classList.remove('status-approved');
                    row.classList.add('status-pending');
                }
            }

            // 切换回只读模式
            setRowReadonly(rowId, true);

            // 更新编辑按钮状态
            const currentEditBtn = document.getElementById(`edit-btn-${rowId}`);
            if (currentEditBtn) {
                currentEditBtn.classList.remove('save-mode');
                currentEditBtn.innerHTML = '<i class="fas fa-edit"></i>';
                currentEditBtn.title = '编辑记录';
                currentEditBtn.disabled = false;
            }

            updateStats();
        } else {
            throw new Error(result.message || '保存失败');
        }

    } catch (error) {
        console.error('保存数据失败:', error);
        showAlert('保存失败: ' + error.message, 'error');

        // 恢复按钮状态
        editBtn.innerHTML = originalHTML;
        editBtn.disabled = false;
    }
}

// 完整更新行ID（修复版本）
function updateRowIdComplete(row, oldId, newId) {
    console.log(`更新行ID: ${oldId} -> ${newId}`);

    // 更新所有input的data-row属性
    const inputs = row.querySelectorAll('input');
    inputs.forEach(input => {
        if (input.dataset.row === oldId) {
            input.dataset.row = newId;
        }
    });

    // 更新所有select的data-row属性
    const selects = row.querySelectorAll('select');
    selects.forEach(select => {
        if (select.dataset.row === oldId) {
            select.dataset.row = newId;
        }
    });

    // 更新编辑按钮的ID和事件
    const editBtn = row.querySelector(`#edit-btn-${oldId}`);
    if (editBtn) {
        editBtn.id = `edit-btn-${newId}`;
        editBtn.setAttribute('onclick', `toggleEdit('${newId}')`);
    }

    // 更新删除按钮的事件
    const deleteBtn = row.querySelector('.delete-row-btn');
    if (deleteBtn) {
        deleteBtn.setAttribute('onclick', `deleteRow('${newId}')`);
    }

    // 更新批准按钮的事件（如果存在）
    const approveBtn = row.querySelector(`button[onclick*="approveRecord('${oldId}')"]`);
    if (approveBtn) {
        approveBtn.setAttribute('onclick', `approveRecord('${newId}')`);
    }

    // 移除新行样式
    row.classList.remove('new-row');

    const recordIndex = stockData.findIndex(item => item.id == oldId || (typeof item.id === 'undefined' && oldId.toString().startsWith('new-')));
    if (recordIndex === -1) {
        // 如果是新记录，添加到stockData中
        const rowData = extractRowData(row);
        rowData.id = newId;
        stockData.push(rowData);
    } else {
        // 更新现有记录的ID
        stockData[recordIndex].id = newId;
    }

    console.log(`行ID更新完成: ${oldId} -> ${newId}`);
}
