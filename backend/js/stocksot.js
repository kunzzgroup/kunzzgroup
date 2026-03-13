
const API_BASE_URL = 'stocksotapi.php';
const PRODUCT_API_URL = 'stockapi.php';
const PRICE_API_URL = 'stockeditapi.php';  // 价格API使用stockeditapi
const STOCK_VIEW_OPTIONS = [
    { value: 'list', label: '总库存' },
    { value: 'records', label: '进出货' },
    { value: 'remark', label: '货品备注' },
    { value: 'product', label: '货品种类' },
    { value: 'sot', label: '货品异常' }
];
let cachedSotAllowedViews = new Set();

// 应用状态
let stockData = [];
let productList = [];
let isLoading = false;
let nextRowId = 1;

// 批量删除状态
let isBatchDeleteMode = false;
let selectedRecords = new Set();

// 日期范围相关变量
let dateRange = { startDate: '', endDate: '' };
let calendarCurrentDate = new Date();
let calendarStartDate = null;
let calendarEndDate = null;
let isSelectingRange = false;

// 输入框光标定位处理
let inputFirstClickMap = new Map();

function handleInputFocus(input, isClick = false) {
    setTimeout(() => {
        if (isClick) {
            const inputKey = `${input.dataset.field}-${input.dataset.row}`;
            if (inputFirstClickMap.has(inputKey)) {
                return;
            }
            inputFirstClickMap.set(inputKey, true);
        }

        // 跳过 number 类型的输入框，因为它们不支持 setSelectionRange
        if (input.type === 'number') {
            return;
        }

        if (input.value) {
            input.select();
        } else {
            input.setSelectionRange(0, 0);
        }
    }, 0);
}

function resetInputFirstClick(input) {
    const inputKey = `${input.dataset.field}-${input.dataset.row}`;
    inputFirstClickMap.delete(inputKey);
}

function rebuildSotViewDropdown(allowedSet) {
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
        item.className = 'dropdown-item' + (opt.value === 'sot' ? ' active' : '');
        item.dataset.viewValue = opt.value;
        item.textContent = opt.label;
        item.onclick = function () { switchView(opt.value); };
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
        cachedSotAllowedViews = new Set(allowedViews);
        rebuildSotViewDropdown(allowedViews);
        if (allowedViews.size > 0 && !allowedViews.has('sot')) {
            const viewOrder = ['sot', 'records', 'product', 'remark', 'list'];
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

// 格式化货币显示
function formatCurrencyDisplay(value) {
    if (!value || value === '') return '';
    const num = parseFloat(value);
    if (isNaN(num)) return 0.00;
    return num.toFixed(2);
}

// 初始化应用
async function initApp() {
    await applyPagePermissions();
    await loadProductList();
    initEnhancedDatePickers();
    loadStockData();
    initRealTimeSearch();
    updateTotalAnomalyValue(); // 初始化总异常金额显示
}

// 初始化增强型日期选择器
function initEnhancedDatePickers() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1;
    const currentDay = today.getDate();

    // 初始化日历选择器默认值为今天
    calendarStartDate = new Date(today);
    calendarEndDate = new Date(today);

    // 设置dateRange为今天
    dateRange = {
        startDate: `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(currentDay).padStart(2, '0')}`,
        endDate: `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(currentDay).padStart(2, '0')}`
    };

    console.log('初始化日期选择器，设置日期范围为今天:', dateRange.startDate, '到', dateRange.endDate);

    // 更新日期范围显示
    updateDateRangeDisplay();
}

// 加载货品列表
async function loadProductList() {
    try {
        const response = await fetch(`${PRODUCT_API_URL}?action=list`);
        const result = await response.json();
        if (result.success) {
            productList = result.data || [];

            // 存储到 window 对象以供 combobox 使用
            window.productOptions = productList;
            window.codeNumberOptions = productList;
        }
    } catch (error) {
        console.error('加载货品列表失败:', error);
    }
}

// 创建 combobox
function createCombobox(type, value = '', recordId = null) {
    let options, placeholder, fieldName, displayField;

    if (type === 'code') {
        options = window.codeNumberOptions;
        placeholder = '输入或选择编号...';
        fieldName = 'product_code';
        displayField = 'product_code';
    } else if (type === 'product') {
        options = window.productOptions;
        placeholder = '输入或选择货品...';
        fieldName = 'product_name';
        displayField = 'product_name';
    }

    const containerId = `combo-${fieldName}-${recordId}`;
    const inputId = `${containerId}-input`;
    const dropdownId = `${containerId}-dropdown`;

    return `
                <div class="combobox-container" id="${containerId}">
                    <input 
                        type="text" 
                        class="combobox-input" 
                        id="${inputId}"
                        value="${value || ''}" 
                        placeholder="${placeholder}"
                        autocomplete="off"
                        ${recordId ? `data-record-id="${recordId}"` : ''}
                        data-field="${fieldName}"
                        data-type="${type}"
                    />
                    <i class="fas fa-chevron-down combobox-arrow"></i>
                    <div class="combobox-dropdown" id="${dropdownId}">
                        ${generateComboboxOptions(options, displayField || '')}
                    </div>
                </div>
            `;
}

// 生成下拉选项
function generateComboboxOptions(options, displayField) {
    if (!options || options.length === 0) {
        return '<div class="no-results">暂无选项</div>';
    }

    // 排序选项：数字开头的在前，然后按字母顺序
    const sortedOptions = [...options].sort((a, b) => {
        const valueA = (a[displayField] || '').toString().toLowerCase();
        const valueB = (b[displayField] || '').toString().toLowerCase();

        // 检查是否以数字开头
        const isNumberA = /^\d/.test(valueA);
        const isNumberB = /^\d/.test(valueB);

        // 如果一个是数字开头，一个不是，数字开头的排在前面
        if (isNumberA && !isNumberB) return -1;
        if (!isNumberA && isNumberB) return 1;

        // 都是数字开头或都不是数字开头，按字符串比较
        return valueA.localeCompare(valueB, 'zh-CN', { numeric: true });
    });

    return sortedOptions.map(option =>
        `<div class="combobox-option" data-value="${option[displayField]}">
                    ${option[displayField]}
                </div>`
    ).join('');
}

// 计算下拉列表位置
function calculateDropdownPosition(inputElement, dropdownElement) {
    const inputRect = inputElement.getBoundingClientRect();
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    const wasVisible = dropdownElement.style.display === 'block';
    if (!wasVisible) {
        dropdownElement.style.display = 'block';
        dropdownElement.style.visibility = 'hidden';
    }

    const options = dropdownElement.querySelectorAll('.combobox-option, .no-results');
    const optionCount = options.length;

    let dropdownHeight;
    if (dropdownElement.scrollHeight > 0 && optionCount > 0) {
        let totalHeight = 0;
        options.forEach(option => {
            totalHeight += option.offsetHeight;
        });

        if (totalHeight > 0) {
            dropdownHeight = Math.min(250, totalHeight + 4);
        } else {
            dropdownHeight = Math.min(250, dropdownElement.scrollHeight + 4);
        }
    } else {
        dropdownHeight = Math.min(250, 37 * Math.min(6, Math.max(1, optionCount)) + 4);
    }

    if (!wasVisible) {
        dropdownElement.style.display = '';
        dropdownElement.style.visibility = '';
    }

    let top = inputRect.bottom;
    let left = inputRect.left;

    const spaceBelow = viewportHeight - inputRect.bottom;
    const spaceAbove = inputRect.top;
    const isLastFewRows = inputRect.bottom > viewportHeight * 0.7;

    if (top + dropdownHeight > viewportHeight || (isLastFewRows && spaceAbove > dropdownHeight)) {
        top = inputRect.top - dropdownHeight;

        if (top < 0) {
            top = 10;
            dropdownHeight = Math.min(dropdownHeight, inputRect.top - 20);
        }
    }

    const dropdownWidth = Math.max(200, inputRect.width);
    if (left + dropdownWidth > viewportWidth) {
        left = viewportWidth - dropdownWidth - 10;
    }
    if (left < 10) {
        left = 10;
    }

    return { top, left, width: dropdownWidth, height: dropdownHeight };
}

// 显示下拉列表
function showComboboxDropdown(input) {
    hideAllComboboxDropdowns();

    const container = input.closest('.combobox-container');
    const dropdown = container.querySelector('.combobox-dropdown');

    if (dropdown) {
        const position = calculateDropdownPosition(input, dropdown);
        dropdown.style.top = position.top + 'px';
        dropdown.style.left = position.left + 'px';
        dropdown.style.width = position.width + 'px';
        dropdown.style.maxHeight = position.height + 'px';
        dropdown.classList.add('show');

        dropdown.querySelectorAll('.combobox-option').forEach(option => {
            option.classList.remove('highlighted');
        });
    }
}

// 隐藏所有下拉列表
function hideAllComboboxDropdowns() {
    document.querySelectorAll('.combobox-dropdown.show').forEach(dropdown => {
        dropdown.classList.remove('show');
    });
}

// 过滤下拉选项
function filterComboboxOptions(input) {
    clearTimeout(input._filterTimeout);
    input._filterTimeout = setTimeout(() => {
        const container = input.closest('.combobox-container');
        const dropdown = container.querySelector('.combobox-dropdown');
        const type = input.dataset.type;

        if (!dropdown) return;

        const searchTerm = input.value.toLowerCase();
        let options, displayField, filteredOptions;

        if (type === 'code') {
            options = window.codeNumberOptions;
            displayField = 'product_code';
            if (!options) return;
            filteredOptions = options.filter(option =>
                option[displayField].toLowerCase().includes(searchTerm)
            );
        } else if (type === 'product') {
            options = window.productOptions;
            displayField = 'product_name';
            if (!options) return;
            filteredOptions = options.filter(option =>
                option[displayField].toLowerCase().includes(searchTerm)
            );
        }

        if (filteredOptions.length === 0) {
            dropdown.innerHTML = '<div class="no-results">未找到匹配项</div>';
        } else {
            dropdown.innerHTML = generateComboboxOptions(filteredOptions, displayField || '');

            dropdown.querySelectorAll('.combobox-option').forEach(option => {
                option.addEventListener('click', () => selectComboboxOption(option, input));
            });
        }

        requestAnimationFrame(() => {
            showComboboxDropdown(input);
        });
    }, 100);
}

// 选择下拉选项
async function selectComboboxOption(optionElement, input) {
    const value = optionElement.dataset.value;
    const type = input.dataset.type;
    const row = input.closest('tr');

    input.value = value;
    hideAllComboboxDropdowns();

    // 触发联动更新
    if (type === 'code') {
        const product = productList.find(p => p.product_code === value);
        if (product) {
            const productNameInput = row.querySelector('.combobox-input[data-type="product"]');
            if (productNameInput) {
                productNameInput.value = product.product_name;
            }

            const specSelect = row.querySelector('select[data-field="specification"]');
            if (specSelect && product.specification) {
                specSelect.value = product.specification;
            }

            const categorySelect = row.querySelector('select[data-field="category"]');
            if (categorySelect && product.category) {
                categorySelect.value = product.category;
            }

            await updatePriceOptions(row, product.product_name);
        }
    } else if (type === 'product') {
        const product = productList.find(p => p.product_name === value);
        if (product) {
            const codeInput = row.querySelector('.combobox-input[data-type="code"]');
            if (codeInput) {
                codeInput.value = product.product_code;
            }

            const specSelect = row.querySelector('select[data-field="specification"]');
            if (specSelect && product.specification) {
                specSelect.value = product.specification;
            }

            const categorySelect = row.querySelector('select[data-field="category"]');
            if (categorySelect && product.category) {
                categorySelect.value = product.category;
            }

            await updatePriceOptions(row, value);
        }
    }
}

// 验证输入
function validateComboboxInput(input) {
    const type = input.dataset.type;
    const value = input.value.trim();

    if (!value) return true;

    let options, displayField;
    if (type === 'code') {
        options = window.codeNumberOptions;
        displayField = 'product_code';
    } else if (type === 'product') {
        options = window.productOptions;
        displayField = 'product_name';
    }

    if (!options) return false;

    const isValid = options.some(option => option[displayField] === value);

    if (!isValid && value) {
        input.style.borderColor = '#ef4444';
        setTimeout(() => {
            input.style.borderColor = '';
        }, 2000);
    }

    return isValid;
}

// 处理键盘事件
function handleComboboxKeydown(event, input) {
    const container = input.closest('.combobox-container');
    const dropdown = container.querySelector('.combobox-dropdown');

    if (!dropdown.classList.contains('show')) {
        if (event.key === 'ArrowDown' || event.key === 'Enter') {
            showComboboxDropdown(input);
            return;
        }
        return;
    }

    const options = dropdown.querySelectorAll('.combobox-option');
    let highlighted = dropdown.querySelector('.combobox-option.highlighted');

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            if (!highlighted) {
                options[0]?.classList.add('highlighted');
            } else {
                highlighted.classList.remove('highlighted');
                const next = highlighted.nextElementSibling;
                if (next && next.classList.contains('combobox-option')) {
                    next.classList.add('highlighted');
                    next.scrollIntoView({ block: 'nearest' });
                } else {
                    options[0]?.classList.add('highlighted');
                }
            }
            break;

        case 'ArrowUp':
            event.preventDefault();
            if (highlighted) {
                highlighted.classList.remove('highlighted');
                const prev = highlighted.previousElementSibling;
                if (prev && prev.classList.contains('combobox-option')) {
                    prev.classList.add('highlighted');
                    prev.scrollIntoView({ block: 'nearest' });
                } else {
                    options[options.length - 1]?.classList.add('highlighted');
                }
            }
            break;

        case 'Enter':
            event.preventDefault();
            if (highlighted) {
                selectComboboxOption(highlighted, input);
            }
            break;

        case 'Escape':
            hideAllComboboxDropdowns();
            break;
    }
}

// 绑定 combobox 事件
function bindComboboxEvents() {
    document.querySelectorAll('.combobox-input').forEach(input => {
        if (input._eventsbound) return;

        input.addEventListener('focus', () => showComboboxDropdown(input));
        input.addEventListener('input', () => filterComboboxOptions(input));
        input.addEventListener('keydown', (e) => handleComboboxKeydown(e, input));
        input.addEventListener('blur', () => {
            setTimeout(() => {
                const container = input.closest('.combobox-container');
                const dropdown = container.querySelector('.combobox-dropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    setTimeout(() => {
                        if (!dropdown.classList.contains('show')) {
                            validateComboboxInput(input);
                        }
                    }, 150);
                    return;
                }
                validateComboboxInput(input);
            }, 200);
        });

        input._eventsbound = true;
    });

    document.querySelectorAll('.combobox-option').forEach(option => {
        if (option._eventsbound) return;

        const clickHandler = () => {
            const container = option.closest('.combobox-container');
            const input = container.querySelector('.combobox-input');
            selectComboboxOption(option, input);
        };
        option.addEventListener('click', clickHandler);
        option._eventsbound = true;
    });
}

// 切换视图选择器下拉菜单
function toggleViewSelector() {
    const dropdown = document.getElementById('view-selector-dropdown');
    dropdown.classList.toggle('show');
}

// 检查URL参数中的system
const urlParams = new URLSearchParams(window.location.search);
const currentSystem = urlParams.get('system') || 'central';

function switchView(viewType) {
    const systemParam = `?system=${currentSystem}`;

    if (viewType === 'list') {
        window.location.href = `stocklistall${systemParam}`;
    } else if (viewType === 'records') {
        window.location.href = `stockeditall${systemParam}`;
    } else if (viewType === 'remark') {
        window.location.href = `stockremark${systemParam}`;
    } else if (viewType === 'product') {
        window.location.href = `stockproductname${systemParam}`;
    } else {
        hideViewDropdown();
    }
}

// 在 DOMContentLoaded 后更新系统显示
document.addEventListener('DOMContentLoaded', () => {
    const systemNames = {
        'central': '中央',
        'j1': 'J1',
        'j2': 'J2',
        'j3': 'J3'
    };
    const displayElement = document.getElementById('current-stock-type');
    if (displayElement && systemNames[currentSystem]) {
        displayElement.textContent = systemNames[currentSystem];
    }
});

function hideViewDropdown() {
    const dropdown = document.getElementById('view-selector-dropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
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
window.addEventListener('scroll', function () {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(function () {
        const backToTopBtn = document.getElementById('back-to-top-btn');
        const scrollThreshold = 150;

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

// 加载库存异常数据
async function loadStockData() {
    if (isLoading) return;

    isLoading = true;

    try {
        const productSearch = document.getElementById('product-search-filter').value.trim();

        const params = new URLSearchParams();
        params.append('limit', '5000');

        if (dateRange.startDate && dateRange.endDate) {
            params.append('start_date', dateRange.startDate);
            params.append('end_date', dateRange.endDate);
        }

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

            // 如果有搜索关键字，进行客户端过滤
            if (productSearch) {
                const searchLower = productSearch.toLowerCase();
                stockData = stockData.filter(item => {
                    return (
                        (item.product_code && item.product_code.toLowerCase().includes(searchLower)) ||
                        (item.product_name && item.product_name.toLowerCase().includes(searchLower)) ||
                        (item.category && item.category.toLowerCase().includes(searchLower))
                    );
                });
            }

            generateStockTable();
            updateStats();
            showAlert(`数据加载成功，共找到 ${stockData.length} 条记录`, 'success');
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

    function debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    const debouncedSearch = debounce(loadStockData, 300);

    if (productSearchInput) {
        productSearchInput.addEventListener('input', debouncedSearch);
    }
}

// 生成库存异常表格
function generateStockTable() {
    const tbody = document.getElementById('excel-tbody');
    tbody.innerHTML = '';

    // 按日期降序排序
    const sortedData = [...stockData].sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        return dateB - dateA;
    });

    sortedData.forEach((item, index) => {
        const row = createStockRow(item, index);
        tbody.appendChild(row);
    });

    // 绑定 combobox 事件
    setTimeout(bindComboboxEvents, 0);

    // 为所有现有记录加载价格选项
    setTimeout(() => {
        sortedData.forEach((item, index) => {
            if (item.product_name && item.id) {
                const row = document.querySelector(`input[data-row="${item.id}"]`)?.closest('tr') ||
                    document.querySelector(`select[data-row="${item.id}"]`)?.closest('tr') ||
                    document.querySelector(`.combobox-input[data-record-id="${item.id}"]`)?.closest('tr');
                if (row) {
                    // 直接传入当前价格
                    updatePriceOptions(row, item.product_name, item.price);
                }
            }
        });
    }, 300);
}

// 创建库存异常行
function createStockRow(data = {}, index = -1) {
    const row = document.createElement('tr');
    const isNewRow = index === -1;
    const rowId = isNewRow ? `new-${nextRowId++}` : data.id || index;

    // 新行默认可编辑，已有行默认只读
    if (isNewRow) {
        row.classList.add('new-row');
        row.classList.add('editing-row');
    }

    row.innerHTML = `
                <td class="serial-number-cell">
                    ${isNewRow ? '-' : (index + 1)}
                </td>
                <td>
                    <input type="date" class="excel-input datetime-input" data-field="date" data-row="${rowId}" 
                        value="${data.date || ''}" ${!isNewRow ? 'readonly' : ''} required>
                </td>
                <td>
                    ${createCombobox('code', data.product_code || '', rowId)}
                </td>
                <td>
                    ${createCombobox('product', data.product_name || '', rowId)}
                </td>
                <td>
                    <input type="number" class="excel-input" data-field="quantity" data-row="${rowId}" 
                        value="${data.quantity || ''}" min="0.01" step="0.01" placeholder="0.00" 
                        onchange="calculateTotal('${rowId}')" 
                        oninput="handleQuantityInput('${rowId}')" ${!isNewRow ? 'readonly' : ''} required>
                </td>
                <td>
                    <select class="excel-select" data-field="specification" data-row="${rowId}" ${!isNewRow ? 'disabled' : ''} required>
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
                    <select class="excel-select" data-field="price" data-row="${rowId}" 
                        onchange="handlePriceChange('${rowId}')" ${!isNewRow ? 'disabled' : ''} required>
                        <option value="">请选择价格</option>
                        <option value="manual">手动输入价格</option>
                        ${data.price && data.price !== 'manual' ? `<option value="${data.price}" selected>${parseFloat(data.price).toFixed(5)}</option>` : ''}
                    </select>
                </td>
                <td>
                    <input type="text" class="excel-input" data-field="total_price" data-row="${rowId}" 
                        value="${data.total_price || '0.00'}" readonly>
                </td>
                <td>
                    <select class="excel-select" data-field="category" data-row="${rowId}" ${!isNewRow ? 'disabled' : ''} required>
                        <option value="">选择类型</option>
                        <option value="Service Line" ${(data.category === 'Service Line' || data.category === 'Drinks') ? 'selected' : ''}>Service Line</option>
                        <option value="Sake" ${data.category === 'Sake' ? 'selected' : ''}>Sake</option>
                        <option value="Kitchen" ${data.category === 'Kitchen' ? 'selected' : ''}>Kitchen</option>
                        <option value="Sushi Bar" ${data.category === 'Sushi Bar' ? 'selected' : ''}>Sushi Bar</option>
                    </select>
                </td>
                <td>
                    <span class="action-cell" id="action-cell-${rowId}">
                        ${isBatchDeleteMode && !isNewRow ?
            `<input type="checkbox" class="batch-select-checkbox" 
                                    data-record-id="${rowId}" 
                                    onchange="toggleRecordSelection('${rowId}', this.checked)"
                                    ${selectedRecords.has(String(rowId)) ? 'checked' : ''}>` :
            `<button class="action-btn edit-btn ${isNewRow ? 'save-mode' : ''}" id="edit-btn-${rowId}" onclick="toggleEdit('${rowId}')" title="${isNewRow ? '保存记录' : '编辑记录'}">
                                <i class="fas ${isNewRow ? 'fa-save' : 'fa-edit'}"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteRow('${rowId}')" title="删除此行">
                                <i class="fas fa-trash-alt"></i>
                            </button>`
        }
                    </span>
                </td>
            `;

    // 对于已有行，需要设置 combobox 为只读
    if (!isNewRow) {
        setTimeout(() => {
            const comboboxInputs = row.querySelectorAll('.combobox-input');
            comboboxInputs.forEach(input => {
                input.setAttribute('readonly', 'readonly');
                input.style.pointerEvents = 'none';
            });
        }, 0);
    }

    return row;
}


// 更新单价选项
async function updatePriceOptions(row, productName, currentPrice = null) {
    const priceSelect = row.querySelector('select[data-field="price"]');

    if (!priceSelect) {
        console.error('找不到价格选择框');
        return;
    }

    // 获取当前价格（优先使用传入的参数，否则从 dataset 获取）
    if (!currentPrice) {
        currentPrice = priceSelect.dataset.currentPrice;
    }

    if (productName) {
        try {
            // 使用带库存信息的API（stockeditapi.php），设置required_qty为1以确保显示所有价格
            const url = `${PRICE_API_URL}?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=1`;

            const result = await fetch(url);
            const data = await result.json();

            if (data.success && data.data && data.data.length > 0) {
                let options = '<option value="">请选择价格</option>';
                // 添加手动输入价格选项
                options += '<option value="manual">手动输入价格</option>';

                let priceFound = false;
                data.data.forEach(item => {
                    const price = item.price;
                    const availableStock = item.available_stock || 0;

                    // 检查是否是当前价格
                    const isSelected = currentPrice && Math.abs(parseFloat(price) - parseFloat(currentPrice)) < 0.00001;
                    if (isSelected) priceFound = true;

                    // 如果是已选择的价格，只显示价格；否则显示库存信息
                    const displayText = isSelected
                        ? `${parseFloat(price).toFixed(5)}`
                        : `${parseFloat(price).toFixed(5)} (库存: ${availableStock})`;

                    options += `<option value="${price}" ${isSelected ? 'selected' : ''}>${displayText}</option>`;
                });

                // 如果当前价格不在列表中，添加它（可能是手动输入的）
                if (currentPrice && !priceFound && currentPrice !== '' && currentPrice !== 'manual') {
                    const formattedPrice = parseFloat(currentPrice).toFixed(5);
                    options += `<option value="${currentPrice}" selected>${formattedPrice}</option>`;
                }

                priceSelect.innerHTML = options;
            } else {
                // 即使没有价格数据，也保留手动输入选项和当前价格
                let options = '<option value="">暂无历史价格</option><option value="manual">手动输入价格</option>';

                if (currentPrice && currentPrice !== '' && currentPrice !== 'manual') {
                    const formattedPrice = parseFloat(currentPrice).toFixed(5);
                    options += `<option value="${currentPrice}" selected>${formattedPrice}</option>`;
                }

                priceSelect.innerHTML = options;
            }
        } catch (error) {
            console.error('获取价格选项失败:', error);
            // 即使出错也保留手动输入选项和当前价格
            let options = '<option value="">加载失败</option><option value="manual">手动输入价格</option>';

            if (currentPrice && currentPrice !== '' && currentPrice !== 'manual') {
                const formattedPrice = parseFloat(currentPrice).toFixed(5);
                options += `<option value="${currentPrice}" selected>${formattedPrice}</option>`;
            }

            priceSelect.innerHTML = options;
        }
    } else {
        // 没有选择货品时也保留手动输入选项
        priceSelect.innerHTML = '<option value="">请先选择货品</option><option value="manual">手动输入价格</option>';
    }
}

// 处理数量输入
let quantityInputTimeouts = {};
async function handleQuantityInput(rowId) {
    // 使用防抖，避免频繁触发
    if (quantityInputTimeouts[rowId]) {
        clearTimeout(quantityInputTimeouts[rowId]);
    }

    quantityInputTimeouts[rowId] = setTimeout(async () => {
        const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr') ||
            document.querySelector(`select[data-row="${rowId}"]`)?.closest('tr') ||
            document.querySelector(`.combobox-input[data-record-id="${rowId}"]`)?.closest('tr');

        if (row) {
            const quantityInput = row.querySelector('input[data-field="quantity"]');
            const productInput = row.querySelector('.combobox-input[data-type="product"]');

            if (quantityInput && productInput) {
                const quantity = parseFloat(quantityInput.value) || 0;
                const productName = productInput.value.trim();

                // 只有当数量大于0且已选择货品时，才更新价格选项
                if (quantity > 0 && productName) {
                    await updatePriceOptions(row, productName);
                }
            }
        }
    }, 500); // 500ms防抖延迟
}

// 处理价格选择变化
function handlePriceChange(rowId) {
    const row = document.querySelector(`select[data-row="${rowId}"]`)?.closest('tr');
    if (!row) return;

    const priceSelect = row.querySelector('select[data-field="price"]');
    if (!priceSelect) return;

    if (priceSelect.value === 'manual') {
        // 创建一个临时输入框来手动输入价格
        const existingInput = row.querySelector('.manual-price-input');
        if (existingInput) {
            existingInput.remove();
        }

        const td = priceSelect.closest('td');
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'excel-input manual-price-input';
        input.min = '0';
        input.step = '0.00001';
        input.placeholder = '输入价格';
        input.style.marginTop = '5px';
        input.style.width = '100%';

        input.addEventListener('change', function () {
            const price = parseFloat(this.value);
            if (!isNaN(price) && price >= 0) {
                // 添加手动输入的价格到选项中
                const formattedPrice = price.toFixed(5);

                // 移除之前的手动输入选项
                const oldManualOptions = priceSelect.querySelectorAll('option[data-manual="true"]');
                oldManualOptions.forEach(opt => opt.remove());

                const newOption = document.createElement('option');
                newOption.value = price;
                newOption.textContent = `${formattedPrice} (手动输入)`;
                newOption.selected = true;
                newOption.dataset.manual = 'true';

                // 在"手动输入价格"选项后插入
                const manualOption = priceSelect.querySelector('option[value="manual"]');
                if (manualOption) {
                    manualOption.parentNode.insertBefore(newOption, manualOption.nextSibling);
                }

                // 移除输入框
                this.remove();

                // 计算总价
                calculateTotal(rowId);
            } else {
                alert('请输入有效的价格');
            }
        });

        input.addEventListener('blur', function () {
            if (!this.value) {
                priceSelect.value = '';
                this.remove();
            }
        });

        td.appendChild(input);
        input.focus();
    } else {
        // 移除手动输入框（如果存在）
        const existingInput = row.querySelector('.manual-price-input');
        if (existingInput) {
            existingInput.remove();
        }

        // 计算总价
        calculateTotal(rowId);
    }
}

// 计算总价
function calculateTotal(rowId) {
    const row = document.querySelector(`select[data-row="${rowId}"]`)?.closest('tr');
    if (row) {
        const quantityInput = row.querySelector('input[data-field="quantity"]');
        const priceSelect = row.querySelector('select[data-field="price"]');
        const totalInput = row.querySelector('input[data-field="total_price"]');

        if (quantityInput && priceSelect && totalInput) {
            const quantity = Math.abs(parseFloat(quantityInput.value) || 0); // 确保数量为正数
            const priceValue = priceSelect.value;

            // 如果选择的是"manual"或空值，不计算总价
            if (priceValue === 'manual' || priceValue === '') {
                totalInput.value = '0.00';
                return;
            }

            const price = parseFloat(priceValue) || 0;
            const total = (quantity * price).toFixed(2);

            quantityInput.value = quantity.toFixed(2); // 更新为正数
            totalInput.value = total;

            // 更新总异常金额（对于新行）
            updateTotalAnomalyValueFromDOM();
        }
    }
}

// 从 DOM 计算总异常金额（用于未保存的新行）
function updateTotalAnomalyValueFromDOM() {
    let totalValue = 0;

    // 从已保存的数据计算
    stockData.forEach(item => {
        const totalPrice = parseFloat(item.total_price) || 0;
        totalValue += totalPrice;
    });

    // 加上未保存的新行
    const newRows = document.querySelectorAll('#excel-tbody tr.new-row');
    newRows.forEach(row => {
        const totalInput = row.querySelector('input[data-field="total_price"]');
        if (totalInput && totalInput.value) {
            const totalPrice = parseFloat(totalInput.value) || 0;
            totalValue += totalPrice;
        }
    });

    // 更新显示
    const totalValueElement = document.getElementById('total-anomaly-value');
    if (totalValueElement) {
        totalValueElement.textContent = totalValue.toFixed(2);
    }
}

// 添加新行
function addNewRow() {
    const tbody = document.getElementById('excel-tbody');

    const today = new Date().toISOString().split('T')[0];
    const newData = {
        date: today,
        product_code: '',
        product_name: '',
        quantity: '',
        specification: '',
        price: '',
        total_price: '0.00',
        category: ''
    };

    const newRow = createStockRow(newData);
    tbody.appendChild(newRow);

    // 立即绑定 combobox 事件
    setTimeout(() => {
        bindComboboxEvents();

        // 聚焦到日期输入框
        const dateInput = newRow.querySelector('input[data-field="date"]');
        if (dateInput) {
            dateInput.focus();
        }

        // 设置新行为编辑模式
        const rowId = `new-${nextRowId - 1}`;
        const newRowElement = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr') ||
            document.querySelector(`.combobox-input[data-record-id="${rowId}"]`)?.closest('tr');
        if (newRowElement) {
            newRowElement.classList.add('editing-row');
        }
    }, 0);

    updateStats();
    updateTotalAnomalyValueFromDOM(); // 更新总异常金额
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
        saveSingleRowData(rowId);
    } else {
        setRowReadonly(rowId, false);

        editBtn.classList.add('save-mode');
        editBtn.innerHTML = '<i class="fas fa-save"></i>';
        editBtn.title = '保存记录';
    }
}

// 设置行的只读状态
function setRowReadonly(rowId, readonly) {
    const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr') ||
        document.querySelector(`select[data-row="${rowId}"]`)?.closest('tr') ||
        document.querySelector(`.combobox-input[data-record-id="${rowId}"]`)?.closest('tr');

    if (!row) {
        console.error(`找不到行: ${rowId}`);
        return;
    }

    // 处理普通输入框（非 combobox）
    const inputs = row.querySelectorAll(`input[data-row="${rowId}"]`);
    inputs.forEach(input => {
        if (input.dataset.field === 'total_price') {
            return; // 总价始终只读
        }

        if (readonly) {
            input.setAttribute('readonly', 'readonly');
        } else {
            input.removeAttribute('readonly');
        }
    });

    // 处理 combobox 输入框
    const comboboxInputs = row.querySelectorAll(`.combobox-input[data-record-id="${rowId}"]`);
    comboboxInputs.forEach(input => {
        if (readonly) {
            input.setAttribute('readonly', 'readonly');
            input.style.pointerEvents = 'none';
        } else {
            input.removeAttribute('readonly');
            input.style.pointerEvents = '';
        }
    });

    // 处理下拉选择框
    const selects = row.querySelectorAll(`select[data-row="${rowId}"]`);
    selects.forEach(select => {
        if (readonly) {
            select.setAttribute('disabled', 'disabled');
        } else {
            select.removeAttribute('disabled');
        }
    });

    // 切换行的编辑样式
    if (readonly) {
        row.classList.remove('editing-row', 'new-row');
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
        const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr') ||
            document.querySelector(`select[data-row="${rowId}"]`)?.closest('tr') ||
            document.querySelector(`.combobox-input[data-record-id="${rowId}"]`)?.closest('tr');
        if (!row) {
            throw new Error('找不到对应的行');
        }

        const rowData = extractRowData(row);
        console.log('提取的行数据:', rowData);

        // 验证必填字段
        if (!rowData.date || !rowData.product_name || !rowData.quantity ||
            !rowData.specification || !rowData.price || !rowData.category) {
            throw new Error('请填写所有必填字段');
        }

        // 检查价格是否为空（extractRowData已经处理了"manual"的情况）
        if (!rowData.price || rowData.price === '') {
            throw new Error('请选择或输入价格');
        }

        // 确保数量为正数
        const quantity = Math.abs(parseFloat(rowData.quantity));
        if (quantity <= 0) {
            throw new Error('数量必须大于0');
        }
        rowData.quantity = quantity;

        let result;
        const isNewRecord = rowId.toString().startsWith('new-');

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
                const newId = result.data.id;
                updateRowIdComplete(row, rowId, newId);
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
            showAlert('记录保存成功', 'success');

            // 切换回只读模式
            setRowReadonly(rowId, true);

            // 更新编辑按钮状态 - 切换回编辑模式
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

        editBtn.innerHTML = originalHTML;
        editBtn.disabled = false;
    }
}

// 删除行
async function deleteRow(rowId) {
    if (!confirm('确定要删除这行数据吗？删除后，相关库存会恢复！')) {
        return;
    }

    const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr') ||
        document.querySelector(`select[data-row="${rowId}"]`)?.closest('tr') ||
        document.querySelector(`.combobox-input[data-record-id="${rowId}"]`)?.closest('tr');
    if (row) {
        // 如果是数据库中的记录，需要调用API删除
        if (!rowId.toString().startsWith('new-')) {
            try {
                const response = await fetch(`${API_BASE_URL}?id=${rowId}`, {
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

                showAlert('记录已删除，相关库存已恢复', 'success');
            } catch (error) {
                showAlert('删除记录失败: ' + error.message, 'error');
                return;
            }
        }

        row.remove();
        updateStats();
        updateTotalAnomalyValueFromDOM(); // 更新总异常金额
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
            if (!rowData.date || !rowData.product_name || !rowData.quantity ||
                !rowData.specification || !rowData.price || !rowData.category) {
                continue;
            }

            // 检查价格是否为空（extractRowData已经处理了"manual"的情况）
            if (!rowData.price || rowData.price === '') {
                errorCount++;
                errors.push(`第${Array.from(rows).indexOf(row) + 1}行: 请选择或输入价格`);
                continue;
            }

            // 确保数量为正数
            const quantity = Math.abs(parseFloat(rowData.quantity));
            if (quantity <= 0) {
                continue;
            }
            rowData.quantity = quantity;

            try {
                const firstInput = row.querySelector('input, select');
                const rowId = firstInput?.dataset.row;
                let result;

                if (rowId && rowId.toString().startsWith('new-')) {
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
                    if (rowId && rowId.toString().startsWith('new-') && result.data && result.data.id) {
                        updateRowIdComplete(row, rowId, result.data.id);
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
    const inputs = row.querySelectorAll('input[data-field]');
    const selects = row.querySelectorAll('select[data-field]');
    const comboboxInputs = row.querySelectorAll('.combobox-input[data-field]');

    inputs.forEach(input => {
        const field = input.dataset.field;
        let value = input.value.trim();

        if (field === 'quantity') {
            value = Math.abs(parseFloat(value) || 0); // 确保数量为正数
        }

        data[field] = value;
    });

    selects.forEach(select => {
        const field = select.dataset.field;
        let value = select.value.trim();

        // 如果价格字段的值是"manual"，不要保存它（这是一个占位符）
        if (field === 'price' && value === 'manual') {
            value = '';
        }

        data[field] = value;
    });

    // 从 combobox 输入框提取数据
    comboboxInputs.forEach(input => {
        const field = input.dataset.field;
        let value = input.value.trim();
        data[field] = value;
    });

    return data;
}

// 完整更新行ID
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

    // 更新所有 combobox 输入框的 data-record-id
    const comboboxInputs = row.querySelectorAll('.combobox-input');
    comboboxInputs.forEach(input => {
        if (input.dataset.recordId === oldId) {
            input.dataset.recordId = newId;
        }
    });

    // 更新 combobox 容器 ID
    const comboboxContainers = row.querySelectorAll('.combobox-container');
    comboboxContainers.forEach(container => {
        if (container.id.includes(oldId)) {
            container.id = container.id.replace(oldId, newId);
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

    // 移除新行样式
    row.classList.remove('new-row');

    const recordIndex = stockData.findIndex(item => item.id == oldId || (typeof item.id === 'undefined' && oldId.toString().startsWith('new-')));
    if (recordIndex === -1) {
        const rowData = extractRowData(row);
        rowData.id = newId;
        stockData.push(rowData);
    } else {
        stockData[recordIndex].id = newId;
    }

    console.log(`行ID更新完成: ${oldId} -> ${newId}`);
}

// 更新统计信息
function updateStats() {
    const rows = document.querySelectorAll('#excel-tbody tr');
    let totalRecords = rows.length;

    document.getElementById('total-records').textContent = totalRecords;

    // 计算总异常金额
    updateTotalAnomalyValue();
}

// 更新总异常金额
function updateTotalAnomalyValue() {
    let totalValue = 0;

    // 从 stockData 计算总价
    stockData.forEach(item => {
        const totalPrice = parseFloat(item.total_price) || 0;
        totalValue += totalPrice;
    });

    // 更新显示
    const totalValueElement = document.getElementById('total-anomaly-value');
    if (totalValueElement) {
        totalValueElement.textContent = totalValue.toFixed(2);
    }
}

// 显示通知
function showAlert(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const existingToasts = container.querySelectorAll('.toast');
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
    }, 4000);
}

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

// 输入框和下拉选择框事件处理
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('excel-input')) {
        resetInputFirstClick(e.target);
    }
});

// 键盘快捷键支持
document.addEventListener('keydown', function (e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllData();
    }

    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        addNewRow();
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

// 切换日历显示
function toggleCalendar() {
    const popup = document.getElementById('calendar-popup');
    const picker = document.getElementById('date-range-picker');

    if (popup.style.display === 'none') {
        const rect = picker.getBoundingClientRect();
        popup.style.top = (rect.bottom + 8) + 'px';
        popup.style.left = rect.left + 'px';
        popup.style.display = 'block';
        initCalendar();
        renderCalendar();
    } else {
        popup.style.display = 'none';
    }
}

// 初始化日历
function initCalendar() {
    const today = new Date();
    calendarCurrentDate = new Date(today.getFullYear(), today.getMonth(), 1);

    if (!calendarStartDate) {
        calendarStartDate = new Date(today);
        calendarStartDate.setHours(0, 0, 0, 0);
    }
    if (!calendarEndDate) {
        calendarEndDate = new Date(today);
        calendarEndDate.setHours(0, 0, 0, 0);
    }

    // 初始化年份选择器
    const yearSelect = document.getElementById('calendar-year-select');
    yearSelect.innerHTML = '';
    const currentYear = today.getFullYear();
    for (let year = 2022; year <= currentYear + 1; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year + '年';
        if (year === calendarCurrentDate.getFullYear()) {
            option.selected = true;
        }
        yearSelect.appendChild(option);
    }

    document.getElementById('calendar-month-select').value = calendarCurrentDate.getMonth();
    updateDateRangeDisplay();
}

// 切换月份
function changeMonth(delta) {
    calendarCurrentDate.setMonth(calendarCurrentDate.getMonth() + delta);
    document.getElementById('calendar-month-select').value = calendarCurrentDate.getMonth();
    document.getElementById('calendar-year-select').value = calendarCurrentDate.getFullYear();
    renderCalendar();
}

// 渲染日历
function renderCalendar() {
    const year = parseInt(document.getElementById('calendar-year-select').value);
    const month = parseInt(document.getElementById('calendar-month-select').value);

    calendarCurrentDate = new Date(year, month, 1);

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const prevLastDay = new Date(year, month, 0);

    const firstDayWeek = firstDay.getDay();
    const lastDate = lastDay.getDate();
    const prevLastDate = prevLastDay.getDate();

    const daysContainer = document.getElementById('calendar-days');
    daysContainer.innerHTML = '';

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // 上个月的日期
    for (let i = firstDayWeek - 1; i >= 0; i--) {
        const day = prevLastDate - i;
        const dayElement = createDayElement(day, year, month - 1, true);
        daysContainer.appendChild(dayElement);
    }

    // 当前月的日期
    for (let day = 1; day <= lastDate; day++) {
        const dayElement = createDayElement(day, year, month, false);
        daysContainer.appendChild(dayElement);
    }

    // 下个月的日期
    const totalCells = daysContainer.children.length;
    const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
    for (let day = 1; day <= remainingCells; day++) {
        const dayElement = createDayElement(day, year, month + 1, true);
        daysContainer.appendChild(dayElement);
    }
}

// 创建日期元素
function createDayElement(day, year, month, isOtherMonth) {
    const dayElement = document.createElement('div');
    dayElement.className = 'calendar-day';
    dayElement.textContent = day;

    const date = new Date(year, month, day);
    date.setHours(0, 0, 0, 0);

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (isOtherMonth) {
        dayElement.classList.add('other-month');
    }

    if (date.getTime() === today.getTime() && !isOtherMonth) {
        dayElement.classList.add('today');
    }

    if (calendarStartDate) {
        const startTime = calendarStartDate.getTime();
        const currentTime = date.getTime();

        if (calendarEndDate) {
            const endTime = calendarEndDate.getTime();

            if (currentTime === startTime && currentTime === endTime) {
                dayElement.classList.add('selected', 'start-date', 'end-date');
            } else if (currentTime === startTime) {
                dayElement.classList.add('start-date');
            } else if (currentTime === endTime) {
                dayElement.classList.add('end-date');
            } else if (currentTime > startTime && currentTime < endTime) {
                dayElement.classList.add('in-range');
            }
        } else {
            if (currentTime === startTime) {
                dayElement.classList.add('start-date', 'selecting');
            }
        }
    }

    dayElement.addEventListener('click', (e) => {
        e.stopPropagation();
        selectDate(date);
    });

    return dayElement;
}

// 选择日期
function selectDate(date) {
    if (!calendarStartDate || (calendarStartDate && calendarEndDate)) {
        calendarStartDate = new Date(date);
        calendarEndDate = null;
        isSelectingRange = true;
    } else {
        if (date < calendarStartDate) {
            calendarEndDate = calendarStartDate;
            calendarStartDate = new Date(date);
        } else {
            calendarEndDate = new Date(date);
        }
        isSelectingRange = false;

        updateDateRange();
        document.getElementById('calendar-popup').style.display = 'none';
    }

    renderCalendar();
    updateDateRangeDisplay();
}

// 更新日期范围显示
function updateDateRangeDisplay() {
    const display = document.getElementById('date-range-display');
    if (calendarStartDate && calendarEndDate) {
        const start = formatDateDisplay(calendarStartDate);
        const end = formatDateDisplay(calendarEndDate);
        display.textContent = `${start} - ${end}`;
    } else if (calendarStartDate) {
        const start = formatDateDisplay(calendarStartDate);
        display.textContent = `${start} - 选择结束日期`;
    } else {
        display.textContent = '选择日期范围';
    }
}

// 格式化日期显示
function formatDateDisplay(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}年${month}月${day}日`;
}

// 更新dateRange对象
function updateDateRange() {
    if (calendarStartDate && calendarEndDate) {
        dateRange.startDate = formatDateToYYYYMMDD(calendarStartDate);
        dateRange.endDate = formatDateToYYYYMMDD(calendarEndDate);
        console.log('日历选择器更新日期范围:', dateRange.startDate, '到', dateRange.endDate);
        loadStockData();
    }
}

// 格式化日期为 YYYY-MM-DD
function formatDateToYYYYMMDD(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// 快速选择下拉菜单控制
function toggleQuickSelectDropdown() {
    const dropdown = document.getElementById('quick-select-dropdown');
    dropdown.classList.toggle('show');
}

// 快速选择时间范围
function selectQuickRange(range) {
    const today = new Date();
    let startDate, endDate;

    switch (range) {
        case 'today':
            startDate = new Date(today);
            endDate = new Date(today);
            break;

        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            startDate = yesterday;
            endDate = yesterday;
            break;

        case 'thisWeek':
            const thisWeekStart = new Date(today);
            const dayOfWeek = thisWeekStart.getDay();
            const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
            thisWeekStart.setDate(thisWeekStart.getDate() - daysToMonday);
            startDate = thisWeekStart;
            endDate = new Date(today);
            break;

        case 'lastWeek':
            const lastWeekEnd = new Date(today);
            const lastWeekDayOfWeek = lastWeekEnd.getDay();
            const daysToLastSunday = lastWeekDayOfWeek === 0 ? 0 : lastWeekDayOfWeek;
            lastWeekEnd.setDate(lastWeekEnd.getDate() - daysToLastSunday - 1);
            const lastWeekStart = new Date(lastWeekEnd);
            lastWeekStart.setDate(lastWeekStart.getDate() - 6);
            startDate = lastWeekStart;
            endDate = lastWeekEnd;
            break;

        case 'thisMonth':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today);
            break;

        case 'lastMonth':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            startDate = lastMonth;
            endDate = lastMonthEnd;
            break;

        case 'thisYear':
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today);
            break;

        case 'lastYear':
            startDate = new Date(today.getFullYear() - 1, 0, 1);
            endDate = new Date(today.getFullYear() - 1, 11, 31);
            break;

        default:
            return;
    }

    const formatDate = (date) => {
        return date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');
    };

    dateRange = {
        startDate: formatDate(startDate),
        endDate: formatDate(endDate)
    };

    calendarStartDate = new Date(startDate);
    calendarStartDate.setHours(0, 0, 0, 0);
    calendarEndDate = new Date(endDate);
    calendarEndDate.setHours(0, 0, 0, 0);
    updateDateRangeDisplay();

    const quickSelectText = document.getElementById('quick-select-text');
    const rangeTexts = {
        'today': '今天',
        'yesterday': '昨天',
        'thisWeek': '本周',
        'lastWeek': '上周',
        'thisMonth': '这个月',
        'lastMonth': '上个月',
        'thisYear': '今年',
        'lastYear': '去年'
    };
    quickSelectText.textContent = rangeTexts[range] || '选择时间段';

    document.getElementById('quick-select-dropdown').classList.remove('show');
    loadStockData();
}

// 点击外部关闭日历和下拉菜单
document.addEventListener('click', function (e) {
    const calendar = document.getElementById('date-range-picker');
    const popup = document.getElementById('calendar-popup');
    if (calendar && popup && !calendar.contains(e.target) && !popup.contains(e.target)) {
        popup.style.display = 'none';
    }

    // 关闭快速选择下拉菜单
    if (!e.target.closest('.dropdown')) {
        const quickSelectDropdown = document.getElementById('quick-select-dropdown');
        if (quickSelectDropdown) {
            quickSelectDropdown.classList.remove('show');
        }
    }
});

// 全局点击事件（隐藏 combobox 下拉列表）
document.addEventListener('click', function (event) {
    if (!event.target.closest('.combobox-container')) {
        hideAllComboboxDropdowns();
    }
});

// 窗口滚动和大小变化时重新计算位置
window.addEventListener('scroll', hideAllComboboxDropdowns);
window.addEventListener('resize', hideAllComboboxDropdowns);

// 监听表格滚动，重新计算下拉列表位置
const tableContainer = document.querySelector('.table-scroll-container');
if (tableContainer) {
    tableContainer.addEventListener('scroll', () => {
        clearTimeout(tableContainer._scrollTimeout);
        tableContainer._scrollTimeout = setTimeout(() => {
            document.querySelectorAll('.combobox-dropdown.show').forEach(dropdown => {
                const container = dropdown.closest('.combobox-container');
                const input = container.querySelector('input');
                if (input) {
                    const position = calculateDropdownPosition(input, dropdown);
                    dropdown.style.top = position.top + 'px';
                    dropdown.style.left = position.left + 'px';
                    dropdown.style.width = position.width + 'px';
                    dropdown.style.maxHeight = position.height + 'px';
                }
            });
        }, 50);
    });
}

// 点击其他地方关闭下拉菜单
document.addEventListener('click', function (event) {
    const selector = event.target.closest('.selector-button');
    const dropdown = event.target.closest('.selector-dropdown');
    const dropdownItem = event.target.closest('.dropdown-item');

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

// 切换批量删除模式
function toggleBatchDelete() {
    isBatchDeleteMode = true;
    selectedRecords.clear();

    // 显示/隐藏按钮
    document.getElementById('batch-delete-btn').style.display = 'none';
    const confirmBtn = document.getElementById('confirm-batch-delete-btn');
    confirmBtn.style.display = 'inline-block';
    confirmBtn.disabled = true; // 初始禁用
    confirmBtn.innerHTML = '<i class="fas fa-check"></i> 确认删除';
    document.getElementById('cancel-batch-delete-btn').style.display = 'inline-block';

    // 更改表头
    document.getElementById('action-header').textContent = '选择';

    // 重新渲染表格
    generateStockTable();

    showAlert('批量删除模式已启用，请勾选要删除的记录', 'info');
}

// 取消批量删除模式
function cancelBatchDelete() {
    isBatchDeleteMode = false;
    selectedRecords.clear();

    // 显示/隐藏按钮
    document.getElementById('batch-delete-btn').style.display = 'inline-block';
    document.getElementById('confirm-batch-delete-btn').style.display = 'none';
    document.getElementById('cancel-batch-delete-btn').style.display = 'none';

    // 恢复表头
    document.getElementById('action-header').textContent = '操作';

    // 重新渲染表格
    generateStockTable();
}

// 切换记录选择状态
function toggleRecordSelection(recordId, isSelected) {
    // 确保 recordId 为字符串，以保持一致性
    recordId = String(recordId);

    if (isSelected) {
        selectedRecords.add(recordId);
    } else {
        selectedRecords.delete(recordId);
    }

    console.log('当前选中记录:', Array.from(selectedRecords));

    // 更新确认按钮状态
    const confirmBtn = document.getElementById('confirm-batch-delete-btn');
    if (selectedRecords.size > 0) {
        confirmBtn.innerHTML = `<i class="fas fa-check"></i> 确认删除 (${selectedRecords.size})`;
        confirmBtn.disabled = false;
    } else {
        confirmBtn.innerHTML = '<i class="fas fa-check"></i> 确认删除';
        confirmBtn.disabled = true;
    }
}

// 确认批量删除
async function confirmBatchDelete() {
    if (selectedRecords.size === 0) {
        showAlert('请至少选择一条记录', 'error');
        return;
    }

    if (!confirm(`确定要删除选中的 ${selectedRecords.size} 条记录吗？删除后，相关库存会恢复！`)) {
        return;
    }

    const confirmBtn = document.getElementById('confirm-batch-delete-btn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 删除中...';
    confirmBtn.disabled = true;

    try {
        let successCount = 0;
        let failCount = 0;

        // 逐个删除选中的记录
        for (const recordId of selectedRecords) {
            try {
                const response = await fetch(`${API_BASE_URL}?id=${recordId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const responseText = await response.text();
                const result = JSON.parse(responseText);

                if (result.success) {
                    successCount++;
                } else {
                    failCount++;
                }
            } catch (error) {
                failCount++;
                console.error(`删除记录 ${recordId} 失败:`, error);
            }
        }

        // 显示删除结果
        if (successCount > 0) {
            showAlert(`成功删除 ${successCount} 条记录，相关库存已恢复${failCount > 0 ? `，${failCount} 条失败` : ''}`,
                failCount > 0 ? 'warning' : 'success');
        } else {
            showAlert('删除失败', 'error');
        }

        // 退出批量删除模式并刷新数据
        cancelBatchDelete();

        // 重新加载数据
        loadStockData();

    } catch (error) {
        showAlert('批量删除时发生错误', 'error');
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    }
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);
