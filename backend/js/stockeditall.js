
// HTML 反转义函数
function decodeHtml(html) {
    if (!html) return '';
    if (typeof html !== 'string') return html;
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

// API 配置
const STOCK_SYSTEM_OPTIONS = [
    { value: 'central', label: '中央' },
    { value: 'j1', label: 'J1' },
    { value: 'j2', label: 'J2' },
    { value: 'j3', label: 'J3' }
];
const STOCK_VIEW_OPTIONS = [
    { value: 'list', label: '总库存' },
    { value: 'records', label: '进出货' },
    { value: 'remark', label: '货品备注' },
    { value: 'product', label: '货品种类' },
    { value: 'sot', label: '货品异常' }
];
let cachedStockAllowedSystems = new Set();
let cachedStockAllowedViews = new Set();

let API_BASE_URL = 'stockeditapi.php';
let currentStockType = 'central';
const urlParams = new URLSearchParams(window.location.search);
const requestedStockType = urlParams.get('system');
const validStockTypes = new Set(STOCK_SYSTEM_OPTIONS.map(opt => opt.value));
if (requestedStockType && validStockTypes.has(requestedStockType)) {
    currentStockType = requestedStockType;
    switch (currentStockType) {
        case 'central':
            API_BASE_URL = 'stockeditapi.php';
            break;
        case 'j1':
            API_BASE_URL = 'j1stockeditpageapi.php';
            break;
        case 'j2':
            API_BASE_URL = 'j2stockeditpageapi.php';
            break;
        case 'j3':
            API_BASE_URL = 'j3stockeditpageapi.php';
            break;
    }
}

// 新增记录弹窗：备注 placeholder（中央保持原样，J1/J2/J3改为“输入备注（发票号码/损耗）”）
let defaultNewRecordRemarkPlaceholder = null;
function updateNewRecordRemarkPlaceholder() {
    const input = document.getElementById('new-record-remark');
    if (!input) return;

    if (defaultNewRecordRemarkPlaceholder === null) {
        defaultNewRecordRemarkPlaceholder = input.placeholder || '';
    }

    if (currentStockType === 'central') {
        input.placeholder = defaultNewRecordRemarkPlaceholder;
    } else {
        input.placeholder = '输入备注（发票号码 / 损耗）';
    }
}

// 应用状态
let stockData = [];
let isLoading = false;
let editingRowIds = new Set(); // 改为Set来存储多个正在编辑的行ID
let originalEditData = new Map();
// 批量删除状态
let isBatchDeleteMode = false;
let selectedRecords = new Set();
let lastDeletedIds = []; // 存储最后一次删除的记录ID，用于撤销
let undoTimer = null;

// 规格选项
const specifications = ['Tub', 'Kilo', 'Piece', 'Bottle', 'Box', 'Packet', 'Carton', 'Tin', 'Roll', 'Nos', 'mL', 'Glass'];

// 日期选择器状态
let currentDatePicker = null;
let currentDateType = null;
let startDateValue = { year: null, month: null, day: null };
let endDateValue = { year: null, month: null, day: null };
let dateRange = {
    startDate: null,
    endDate: null
};

// 新的日历选择器变量
let calendarCurrentDate = new Date();
let calendarStartDate = null;
let calendarEndDate = null;
let isSelectingRange = false;

// 全局计数器，确保新行具有唯一 ID
let newRowCounter = 0;

// 全局键盘快捷键 (CTRL+S 保存) - 使用 Capture 模式确保最高优先级
window.addEventListener('keydown', function (e) {
    // A. 撤销删除 (Ctrl+Shift+Z)
    if (e.ctrlKey && e.shiftKey && (e.code === 'KeyZ' || e.key === 'z' || e.key === 'Z')) {
        console.log('StockEdit Shortcut: CTRL+SHIFT+Z triggered (Undo Delete)');
        undoDelete();
        return;
    }
    // A2. 批量删除 (Ctrl+D)
    if ((e.ctrlKey || e.metaKey) && !e.shiftKey && (e.code === 'KeyD' || e.key === 'd' || e.key === 'D')) {
        e.preventDefault();

        if (typeof isBatchDeleteMode !== 'undefined' && isBatchDeleteMode) {
            console.log('StockEdit Shortcut: CTRL+D triggered (Confirm Batch Delete)');
            if (typeof confirmBatchDelete === 'function') {
                confirmBatchDelete();
            }
        } else {
            console.log('StockEdit Shortcut: CTRL+D triggered (Toggle Batch Delete)');
            if (typeof toggleBatchDelete === 'function') {
                toggleBatchDelete();
            }
        }
        return;
    }

    // A3. 打开新增记录弹窗 (Ctrl+Shift+A)
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.code === 'KeyA' || e.key === 'a' || e.key === 'A')) {
        const activeElement = document.activeElement;
        const isInput = activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA' || activeElement.isContentEditable);
        if (!isInput) {
            e.preventDefault();
            console.log('StockEdit Shortcut: CTRL+SHIFT+A triggered (Open Add Record Modal)');
            if (typeof showDateRowsModal === 'function') {
                showDateRowsModal();
            }
            return;
        }
    }

    // A4. 新增一行 (Ctrl+A)
    if ((e.ctrlKey || e.metaKey) && !e.shiftKey && (e.code === 'KeyA' || e.key === 'a' || e.key === 'A')) {
        const activeElement = document.activeElement;
        const isInput = activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA' || activeElement.isContentEditable);
        if (!isInput) {
            e.preventDefault();
            console.log('StockEdit Shortcut: CTRL+A triggered (Add New Row)');
            const now = new Date();
            const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            if (typeof addNewRowWithDate === 'function') {
                addNewRowWithDate(today, '');
                setTimeout(() => {
                    const tableContainer = document.querySelector('.table-scroll-container');
                    if (tableContainer) {
                        tableContainer.scrollTop = tableContainer.scrollHeight;
                    }
                }, 100);
            }
            return;
        }
    }

    // B. 检查是否按下 Ctrl+S 或 Cmd+S
    if ((e.ctrlKey || e.metaKey) && (e.code === 'KeyS' || e.key === 's' || e.key === 'S')) {
        e.preventDefault();
        e.stopPropagation();

        if (e.shiftKey) {
            console.log('StockEdit Shortcut: CTRL+SHIFT+S triggered (Batch Save)');
            if (typeof batchSaveNewRows === 'function') {
                batchSaveNewRows();
            }
            return;
        }

        const activeElement = document.activeElement;
        if (activeElement && activeElement !== document.body) {
            const row = activeElement.closest('tr');
            if (row) {
                if (row.classList.contains('new-row')) {
                    const saveBtn = row.querySelector('.save-new-btn');
                    if (saveBtn) {
                        saveNewRowRecord(saveBtn);
                        return;
                    }
                }
                let recordId = activeElement.getAttribute('data-record-id');
                if (!recordId) {
                    const inputWithId = row.querySelector('input[data-record-id]');
                    if (inputWithId) recordId = inputWithId.getAttribute('data-record-id');
                }
                if (recordId) {
                    const rid = parseInt(recordId);
                    if (typeof editingRowIds !== 'undefined' && editingRowIds.has(rid)) {
                        saveRecord(rid);
                        return;
                    }
                }
            }
        }
        const addForm = document.getElementById('add-form');
        if (addForm && addForm.classList.contains('show')) {
            saveNewRecord();
            return;
        }
        const newRows = document.querySelectorAll('.new-row');
        if (newRows.length > 0) {
            const saveBtn = newRows[0].querySelector('.save-new-btn');
            if (saveBtn) {
                saveNewRowRecord(saveBtn);
                return;
            }
        }
        if (typeof editingRowIds !== 'undefined' && editingRowIds.size > 0) {
            const rid = Array.from(editingRowIds)[0];
            saveRecord(rid);
            return;
        }
    }

    // C. 检查是否在新增记录弹窗中按下 Enter
    if (e.key === 'Enter') {
        const modal = document.getElementById('date-rows-modal');
        if (modal && modal.classList.contains('show')) {
            // 确保不是在 button 上按下的（浏览器默认会处理 button 的 click）
            if (e.target.tagName !== 'BUTTON') {
                e.preventDefault();
                createMultipleRows();
                return;
            }
        }
    }
}, true);


// 切换日历显示
function toggleCalendar() {
    const popup = document.getElementById('calendar-popup');
    const picker = document.getElementById('date-range-picker');

    if (popup.style.display === 'none') {
        // 获取触发元素的位置
        const rect = picker.getBoundingClientRect();

        // 设置日历位置（使用fixed定位）
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

    // 设置默认日期范围为今天
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

    // 设置月份选择器
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

    // 下个月的日期（填充剩余空格）
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

    // 标记今天
    if (date.getTime() === today.getTime() && !isOtherMonth) {
        dayElement.classList.add('today');
    }

    // 标记选中的日期
    if (calendarStartDate) {
        const startTime = calendarStartDate.getTime();
        const currentTime = date.getTime();

        if (calendarEndDate) {
            // 已选择开始和结束日期
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
            // 只选择了开始日期，还在选择结束日期
            if (currentTime === startTime) {
                dayElement.classList.add('start-date', 'selecting');
            }
        }
    }

    // 点击事件
    dayElement.addEventListener('click', (e) => {
        e.stopPropagation();
        selectDate(date);
    });

    // 鼠标悬停事件 - 预览范围
    dayElement.addEventListener('mouseenter', () => {
        if (isSelectingRange && calendarStartDate && !calendarEndDate) {
            highlightPreviewRange(date);
        }
    });

    return dayElement;
}

// 高亮预览范围
function highlightPreviewRange(hoverDate) {
    const days = document.querySelectorAll('.calendar-day');
    const startTime = calendarStartDate.getTime();
    const hoverTime = hoverDate.getTime();

    days.forEach(day => {
        // 移除预览样式
        day.classList.remove('preview-range', 'preview-end');

        // 获取日期（从元素的文本内容和当前日历月份）
        const dayText = parseInt(day.textContent);
        if (!dayText) return;

        // 构建该日期
        const year = parseInt(document.getElementById('calendar-year-select').value);
        const month = parseInt(document.getElementById('calendar-month-select').value);

        let dayDate;
        if (day.classList.contains('other-month')) {
            // 处理上个月或下个月的日期
            const firstDayOfMonth = new Date(year, month, 1);
            const firstDayWeek = firstDayOfMonth.getDay();

            if (dayText > 20) {
                // 上个月的日期
                dayDate = new Date(year, month - 1, dayText);
            } else {
                // 下个月的日期
                dayDate = new Date(year, month + 1, dayText);
            }
        } else {
            dayDate = new Date(year, month, dayText);
        }

        dayDate.setHours(0, 0, 0, 0);
        const dayTime = dayDate.getTime();

        // 添加预览高亮
        const minTime = Math.min(startTime, hoverTime);
        const maxTime = Math.max(startTime, hoverTime);

        if (dayTime > minTime && dayTime < maxTime) {
            day.classList.add('preview-range');
        } else if (dayTime === hoverTime && dayTime !== startTime) {
            day.classList.add('preview-end');
        }
    });
}

// 选择日期
function selectDate(date) {
    if (!calendarStartDate || (calendarStartDate && calendarEndDate)) {
        // 开始新的选择
        calendarStartDate = new Date(date);
        calendarEndDate = null;
        isSelectingRange = true;
    } else {
        // 选择结束日期
        if (date < calendarStartDate) {
            calendarEndDate = calendarStartDate;
            calendarStartDate = new Date(date);
        } else {
            calendarEndDate = new Date(date);
        }
        isSelectingRange = false;

        // 更新日期范围并关闭日历
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
async function updateDateRange() {
    if (calendarStartDate && calendarEndDate) {
        dateRange.startDate = formatDateToYYYYMMDD(calendarStartDate);
        dateRange.endDate = formatDateToYYYYMMDD(calendarEndDate);
        console.log('日历选择器更新日期范围:', dateRange.startDate, '到', dateRange.endDate);

        // 触发数据搜索（保留搜索框内容）
        await searchData();
    }
}

// 格式化日期为 YYYY-MM-DD
function formatDateToYYYYMMDD(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// 点击外部关闭日历
document.addEventListener('click', function (e) {
    const calendar = document.getElementById('date-range-picker');
    const popup = document.getElementById('calendar-popup');
    if (calendar && popup && !calendar.contains(e.target) && !popup.contains(e.target)) {
        popup.style.display = 'none';
    }
});

// 增强的日期选择器功能
function initEnhancedDatePickers() {
    // 获取当前日期
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

    // 设置开始和结束日期初始值为今天（用于旧的选择器，如果还在使用）
    startDateValue = {
        year: currentYear,
        month: currentMonth,
        day: currentDay
    };

    endDateValue = {
        year: currentYear,
        month: currentMonth,
        day: currentDay
    };
}

function updateDateDisplay(prefix) {
    const dateValue = prefix === 'start' ? startDateValue : endDateValue;

    document.getElementById(`${prefix}-year-display`).textContent = dateValue.year;
    document.getElementById(`${prefix}-month-display`).textContent = String(dateValue.month).padStart(2, '0');
    document.getElementById(`${prefix}-day-display`).textContent = String(dateValue.day).padStart(2, '0');
}

function showDateDropdown(prefix, type) {
    // 隐藏其他下拉框
    hideAllDropdowns();

    const dropdown = document.getElementById(`${prefix}-dropdown`);
    const datePicker = document.getElementById(`${prefix}-date-picker`);

    // 设置当前状态
    currentDatePicker = prefix;
    currentDateType = type;

    // 移除所有active状态
    datePicker.querySelectorAll('.date-part').forEach(part => {
        part.classList.remove('active');
    });

    // 添加当前选中的active状态
    datePicker.querySelector(`[data-type="${type}"]`).classList.add('active');

    // 生成下拉内容
    generateDropdownContent(prefix, type);

    // 显示下拉框
    dropdown.classList.add('show');
}

function hideAllDropdowns() {
    document.querySelectorAll('.date-dropdown').forEach(dropdown => {
        dropdown.classList.remove('show');
    });

    document.querySelectorAll('.date-part').forEach(part => {
        part.classList.remove('active');
    });

    currentDatePicker = null;
    currentDateType = null;
}

function generateDropdownContent(prefix, type) {
    const dropdown = document.getElementById(`${prefix}-dropdown`);
    const dateValue = prefix === 'start' ? startDateValue : endDateValue;

    const today = new Date();

    dropdown.innerHTML = '';

    if (type === 'year') {
        // 生成年份选择
        const yearGrid = document.createElement('div');
        yearGrid.className = 'year-grid';

        const currentYear = today.getFullYear();
        const startYear = 2022;
        const endYear = currentYear + 1;

        for (let year = startYear; year <= endYear; year++) {
            const yearOption = document.createElement('div');
            yearOption.className = 'date-option';
            yearOption.textContent = year;

            if (year === dateValue.year) {
                yearOption.classList.add('selected');
            }

            if (year === currentYear) {
                yearOption.classList.add('today');
            }

            yearOption.addEventListener('click', function () {
                selectDateValue(prefix, 'year', year);
            });

            yearGrid.appendChild(yearOption);
        }

        dropdown.appendChild(yearGrid);

    } else if (type === 'month') {
        // 生成月份选择
        const monthGrid = document.createElement('div');
        monthGrid.className = 'month-grid';

        const months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];

        months.forEach((monthName, index) => {
            const monthValue = index + 1;
            const monthOption = document.createElement('div');
            monthOption.className = 'date-option';
            monthOption.textContent = monthName;

            if (monthValue === dateValue.month) {
                monthOption.classList.add('selected');
            }

            if (dateValue.year === today.getFullYear() && monthValue === today.getMonth() + 1) {
                monthOption.classList.add('today');
            }

            monthOption.addEventListener('click', function () {
                selectDateValue(prefix, 'month', monthValue);
            });

            monthGrid.appendChild(monthOption);
        });

        dropdown.appendChild(monthGrid);

    } else if (type === 'day') {
        // 日期选择逻辑保持不变
        const dayGrid = document.createElement('div');
        dayGrid.className = 'day-grid';

        // 添加星期标题
        const weekdays = ['日', '一', '二', '三', '四', '五', '六'];
        weekdays.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';
            dayHeader.textContent = day;
            dayGrid.appendChild(dayHeader);
        });

        // 计算当月信息
        const year = dateValue.year;
        const month = dateValue.month;
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        const startDayOfWeek = firstDay.getDay();

        // 添加空白日期（上个月的）
        for (let i = 0; i < startDayOfWeek; i++) {
            const emptyDay = document.createElement('div');
            dayGrid.appendChild(emptyDay);
        }

        // 添加当月日期
        for (let day = 1; day <= daysInMonth; day++) {
            const dayOption = document.createElement('div');
            dayOption.className = 'date-option';
            dayOption.textContent = day;

            if (day === dateValue.day) {
                dayOption.classList.add('selected');
            }

            if (year === today.getFullYear() &&
                month === today.getMonth() + 1 &&
                day === today.getDate()) {
                dayOption.classList.add('today');
            }

            dayOption.addEventListener('click', function () {
                selectDateValue(prefix, 'day', day);
            });

            dayGrid.appendChild(dayOption);
        }

        dropdown.appendChild(dayGrid);
    }
}

function selectDateValue(prefix, type, value) {
    const dateValue = prefix === 'start' ? startDateValue : endDateValue;

    // 更新值
    dateValue[type] = value;

    // 如果选择了年份或月份，需要验证日期的有效性
    if (type === 'year' || type === 'month') {
        const daysInMonth = new Date(dateValue.year, dateValue.month, 0).getDate();
        if (dateValue.day > daysInMonth) {
            dateValue.day = daysInMonth;
        }
    }

    // 更新显示
    updateDateDisplay(prefix);

    // 隐藏下拉框
    hideAllDropdowns();

    // 更新日期范围
    updateDateRangeFromPickers();
}

async function updateDateRangeFromPickers() {
    const startDateStr = `${startDateValue.year}-${String(startDateValue.month).padStart(2, '0')}-${String(startDateValue.day).padStart(2, '0')}`;
    const endDateStr = `${endDateValue.year}-${String(endDateValue.month).padStart(2, '0')}-${String(endDateValue.day).padStart(2, '0')}`;

    // 验证日期有效性
    if (new Date(startDateStr) > new Date(endDateStr)) {
        alert('开始日期不能晚于结束日期');
        return;
    }

    dateRange = {
        startDate: startDateStr,
        endDate: endDateStr
    };

    // 重新搜索数据
    await searchData();
    document.getElementById('quick-select-text').textContent = '选择时间段';
}

// 快速选择下拉菜单控制
function toggleQuickSelectDropdown() {
    const dropdown = document.getElementById('quick-select-dropdown');

    // 关闭其他所有下拉菜单
    hideAllDropdowns();

    // 切换当前下拉菜单
    dropdown.classList.toggle('show');
}

// 快速选择时间范围
async function selectQuickRange(range) {
    const today = new Date();
    let startDate, endDate;

    switch (range) {
        case 'today':
            // 今天
            startDate = new Date(today);
            endDate = new Date(today);
            break;

        case 'yesterday':
            // 昨天
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            startDate = yesterday;
            endDate = yesterday;
            break;

        case 'thisWeek':
            // 本周（周一到今天）
            const thisWeekStart = new Date(today);
            const dayOfWeek = thisWeekStart.getDay();
            const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
            thisWeekStart.setDate(thisWeekStart.getDate() - daysToMonday);

            startDate = thisWeekStart;
            endDate = new Date(today);
            break;

        case 'lastWeek':
            // 上周（上周一到上周日）
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
            // 这个月（本月1号到今天）
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today);
            break;

        case 'lastMonth':
            // 上个月
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);

            startDate = lastMonth;
            endDate = lastMonthEnd;
            break;

        case 'thisYear':
            // 今年
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today);
            break;

        case 'lastYear':
            // 去年
            startDate = new Date(today.getFullYear() - 1, 0, 1);
            endDate = new Date(today.getFullYear() - 1, 11, 31);
            break;

        default:
            return;
    }

    // 格式化日期为 YYYY-MM-DD 格式
    const formatDate = (date) => {
        return date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');
    };

    // 更新日期范围
    dateRange = {
        startDate: formatDate(startDate),
        endDate: formatDate(endDate)
    };

    // 更新日历选择器的值
    calendarStartDate = new Date(startDate);
    calendarStartDate.setHours(0, 0, 0, 0);
    calendarEndDate = new Date(endDate);
    calendarEndDate.setHours(0, 0, 0, 0);
    updateDateRangeDisplay();

    // 更新开始和结束日期选择器的值（用于旧选择器）
    startDateValue = {
        year: startDate.getFullYear(),
        month: startDate.getMonth() + 1,
        day: startDate.getDate()
    };

    endDateValue = {
        year: endDate.getFullYear(),
        month: endDate.getMonth() + 1,
        day: endDate.getDate()
    };

    // 更新按钮显示文本
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

    // 关闭下拉菜单
    document.getElementById('quick-select-dropdown').classList.remove('show');

    // 重新搜索数据
    await searchData();
}


// 初始化应用
async function initApp() {
    // 测试新增表单相关函数是否已定义
    console.log('initApp: 检查新增表单函数...');
    console.log('handleAddFormOutQuantityChange:', typeof window.handleAddFormOutQuantityChange);
    console.log('loadAddFormProductPricesWithStock:', typeof window.loadAddFormProductPricesWithStock);

    // 测试函数是否可以直接调用
    if (typeof window.handleAddFormOutQuantityChange === 'function') {
        console.log('✓ handleAddFormOutQuantityChange 可用');
    } else {
        console.error('✗ handleAddFormOutQuantityChange 不可用');
    }

    // 为新增表单的输入框添加事件监听器（作为备选方案）
    // 使用延迟确保DOM已完全加载
    setTimeout(() => {
        const addOutQtyInput = document.getElementById('add-out-qty');
        const addInQtyInput = document.getElementById('add-in-qty');

        console.log('查找输入框元素 - addOutQtyInput:', addOutQtyInput, 'addInQtyInput:', addInQtyInput);

        if (addOutQtyInput) {
            // 移除旧的事件监听器（如果有）
            const oldHandler = addOutQtyInput.oninput;
            if (oldHandler) {
                addOutQtyInput.removeEventListener('input', oldHandler);
                addOutQtyInput.removeEventListener('change', oldHandler);
            }
            // 添加新的事件监听器
            addOutQtyInput.addEventListener('input', window.handleAddFormOutQuantityChange, true);
            addOutQtyInput.addEventListener('change', window.handleAddFormOutQuantityChange, true);
            // 同时设置oninput属性作为备选
            addOutQtyInput.setAttribute('oninput', 'window.handleAddFormOutQuantityChange()');
            addOutQtyInput.setAttribute('onchange', 'window.handleAddFormOutQuantityChange()');
            console.log('✓ 已为 add-out-qty 添加事件监听器');
            console.log('✓ add-out-qty 的oninput属性:', addOutQtyInput.getAttribute('oninput'));
        } else {
            console.warn('⚠ 找不到 add-out-qty 元素');
        }

        if (addInQtyInput) {
            const oldHandler = addInQtyInput.oninput;
            if (oldHandler) {
                addInQtyInput.removeEventListener('input', oldHandler);
            }
            addInQtyInput.addEventListener('input', window.handleAddFormOutQuantityChange, true);
            addInQtyInput.setAttribute('oninput', 'window.handleAddFormOutQuantityChange()');
            console.log('✓ 已为 add-in-qty 添加事件监听器');
            console.log('✓ add-in-qty 的oninput属性:', addInQtyInput.getAttribute('oninput'));
        } else {
            console.warn('⚠ 找不到 add-in-qty 元素');
        }

        // 测试函数是否可以直接调用
        console.log('测试：尝试直接调用函数');
        try {
            window.handleAddFormOutQuantityChange();
            console.log('✓ 函数可以直接调用');
        } catch (e) {
            console.error('✗ 函数调用失败:', e);
        }
    }, 500);

    // 设置默认日期为今天（使用本地时间，避免UTC时差导致日期偏移）
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    document.getElementById('add-date').value = today;
    document.getElementById('add-time').value = new Date().toTimeString().slice(0, 5);

    // 初始化增强型日期选择器
    initEnhancedDatePickers();

    // 应用页面权限，自动切换到第一个允许的系统
    const systemSwitched = await applyPagePermissions();

    // 如果系统已切换，switchStock 会处理数据加载，这里不需要重复加载
    // 只有在系统未切换时才需要加载数据
    if (!systemSwitched) {
        if (requestedStockType && requestedStockType !== 'central') {
            switchStock(requestedStockType);
        } else {
            // 加载数据
            loadStockData();
            loadCodeNumbers();
            loadProducts();
            loadShippers();  // 动态加载出货人列表
        }
    }

    // 添加实时搜索监听器
    setupRealTimeSearch();

    // 设置默认active状态
    const currentItem = document.querySelector(`.selector-dropdown .dropdown-item[data-type="${currentStockType}"]`);
    if (currentItem) {
        currentItem.classList.add('active');
    }

    // 设置默认active状态（延迟执行以确保DOM已加载）
    setTimeout(() => {
        const activeItem = document.querySelector(`.selector-dropdown .dropdown-item[data-type="${currentStockType}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
        }
    }, 100);

    // 控制Type字段的启用/禁用状态
    const typeSelect = document.getElementById('add-type');
    if (typeSelect) {
        if (currentStockType === 'central') {
            typeSelect.disabled = true;
            typeSelect.value = '';
        } else {
            typeSelect.disabled = false;
        }
    }

    // 新增：初始化时根据当前系统类型控制导出按钮
    const exportButton = document.querySelector('.btn-warning[onclick="exportData()"]');
    if (exportButton) {
        const supportedSystems = ['central', 'j1', 'j2', 'j3'];
        exportButton.style.display = supportedSystems.includes(currentStockType) ? 'inline-block' : 'none';
    }

    const mobileSelector = document.getElementById('mobile-selector');
    if (mobileSelector) {
        mobileSelector.style.display = currentStockType === 'j3' ? 'inline-flex' : 'none';
    }
    const mobileSelectorJ1 = document.getElementById('mobile-selector-j1');
    if (mobileSelectorJ1) {
        mobileSelectorJ1.style.display = currentStockType === 'j1' ? 'inline-flex' : 'none';
    }
    const mobileSelectorJ2 = document.getElementById('mobile-selector-j2');
    if (mobileSelectorJ2) {
        mobileSelectorJ2.style.display = currentStockType === 'j2' ? 'inline-flex' : 'none';
    }

    // 初始化：根据当前系统更新“新增记录”弹窗备注 placeholder
    updateNewRecordRemarkPlaceholder();
}

// 设置实时搜索
function setupRealTimeSearch() {
    const searchInput = document.getElementById('unified-filter');

    // 防抖处理，避免频繁搜索
    let debounceTimer;

    function handleSearch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchData();
        }, 300); // 300ms延迟
    }

    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }
}

// 切换库存选择器下拉菜单
function toggleStockSelector() {
    const dropdown = document.getElementById('stock-dropdown');
    dropdown.classList.toggle('show');
}

function switchStock(stockType, event = null) {
    if (cachedStockAllowedSystems && cachedStockAllowedSystems.size > 0 && !cachedStockAllowedSystems.has(stockType)) {
        const firstAllowed = Array.from(cachedStockAllowedSystems)[0];
        if (firstAllowed) {
            stockType = firstAllowed;
        } else {
            return;
        }
    }
    currentStockType = stockType;
    updateNewRecordRemarkPlaceholder();

    // 更新API地址
    switch (stockType) {
        case 'central':
            API_BASE_URL = 'stockeditapi.php';
            document.getElementById('page-title').textContent = '进出货 - 中央';
            document.getElementById('current-stock-type').textContent = '中央';
            break;
        case 'j1':
            API_BASE_URL = 'j1stockeditpageapi.php';
            document.getElementById('page-title').textContent = '进出货 - J1';
            document.getElementById('current-stock-type').textContent = 'J1';
            break;
        case 'j2':
            API_BASE_URL = 'j2stockeditpageapi.php';
            document.getElementById('page-title').textContent = '进出货 - J2';
            document.getElementById('current-stock-type').textContent = 'J2';
            break;
        case 'j3':
            API_BASE_URL = 'j3stockeditpageapi.php';
            document.getElementById('page-title').textContent = '进出货 - J3';
            document.getElementById('current-stock-type').textContent = 'J3';
            break;
    }

    const exportButton = document.querySelector('.btn-warning[onclick="exportData()"]');
    if (exportButton) {
        const supportedSystems = ['central', 'j1', 'j2', 'j3'];
        exportButton.style.display = supportedSystems.includes(stockType) ? 'inline-block' : 'none';
    }

    const mobileSelector = document.getElementById('mobile-selector');
    if (mobileSelector) {
        mobileSelector.style.display = stockType === 'j3' ? 'inline-flex' : 'none';
    }
    const mobileSelectorJ1 = document.getElementById('mobile-selector-j1');
    if (mobileSelectorJ1) {
        mobileSelectorJ1.style.display = stockType === 'j1' ? 'inline-flex' : 'none';
    }
    const mobileSelectorJ2 = document.getElementById('mobile-selector-j2');
    if (mobileSelectorJ2) {
        mobileSelectorJ2.style.display = stockType === 'j2' ? 'inline-flex' : 'none';
    }

    // 修改Type列的控制 - 不要隐藏，而是控制禁用状态
    const typeHeader = document.getElementById('type-header');
    const typeFormGroup = document.getElementById('type-form-group');

    if (stockType === 'central') {
        // 中央页面：显示Type列但禁用表单字段
        if (typeHeader) typeHeader.style.display = 'table-cell';
        if (typeFormGroup) {
            typeFormGroup.style.display = 'block';
            const typeSelect = document.getElementById('add-type');
            if (typeSelect) {
                typeSelect.disabled = true;
                typeSelect.value = '';
            }
        }
    } else {
        // J1/J2/J3页面：显示Type列并启用
        if (typeHeader) typeHeader.style.display = 'table-cell';
        if (typeFormGroup) {
            typeFormGroup.style.display = 'block';
            const typeSelect = document.getElementById('add-type');
            if (typeSelect) {
                typeSelect.disabled = false;
            }
        }
    }

    // 更新active状态
    document.querySelectorAll('#stock-dropdown .dropdown-item').forEach(item => {
        item.classList.remove('active');
    });
    const activeItem = document.querySelector(`#stock-dropdown .dropdown-item[data-type="${stockType}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }

    // 立即隐藏下拉菜单
    const dropdown = document.getElementById('stock-dropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
    }

    // 阻止事件冒泡，防止全局点击事件重新显示下拉菜单
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    // 清空当前数据并重新加载
    stockData = [];
    editingRowIds.clear();
    if (originalEditData) {
        originalEditData.clear();
    }

    // 重新加载数据
    loadStockData();
    loadCodeNumbers();
    loadProducts();
    loadShippers();  // 动态加载出货人列表

    // 更新 URL 参数以保持持久性
    const newParams = new URLSearchParams(window.location.search);
    newParams.set('system', stockType);
    window.history.replaceState(null, "", "?" + newParams.toString());
}

function rebuildStockSystemDropdown(allowedSet) {
    const dropdown = document.getElementById('stock-dropdown');
    if (!dropdown) return;
    const options = (allowedSet && allowedSet.size > 0)
        ? STOCK_SYSTEM_OPTIONS.filter(opt => allowedSet.has(opt.value))
        : STOCK_SYSTEM_OPTIONS.slice();
    if (options.length === 0) {
        options.push(STOCK_SYSTEM_OPTIONS[0]);
    }
    dropdown.innerHTML = '';
    options.forEach(opt => {
        const anchor = document.createElement('a');
        anchor.href = '#';
        anchor.className = 'dropdown-item' + (opt.value === currentStockType ? ' active' : '');
        anchor.dataset.type = opt.value;
        anchor.textContent = opt.label;
        anchor.onclick = function (evt) {
            evt.preventDefault();
            switchStock(opt.value, evt);
        };
        dropdown.appendChild(anchor);
    });
    if (!options.some(opt => opt.value === currentStockType) && options.length > 0) {
        switchStock(options[0].value);
    }
}

function rebuildStockViewDropdown(allowedSet) {
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
        item.className = 'dropdown-item' + (opt.value === 'records' ? ' active' : '');
        item.dataset.viewValue = opt.value;
        item.textContent = opt.label;
        item.onclick = function () {
            switchView(opt.value);
        };
        dropdown.appendChild(item);
    });
}

// 应用页面权限，隐藏不允许的下拉选项，并自动切换到允许的系统
async function applyPagePermissions() {
    try {
        const res = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_page_permissions' })
        });
        const data = await res.json();
        if (!data.success) return false; // 返回false表示没有切换系统
        const perms = data.page_permissions || {};
        const current = perms.stock_inventory || {};
        const allowedSystems = new Set(current.system || []);
        const allowedViews = new Set(current.views || current.view || []); // Use stockeditall views

        // 如果有权限限制，检查当前系统是否被允许
        cachedStockAllowedSystems = new Set(allowedSystems);
        cachedStockAllowedViews = new Set(allowedViews);
        rebuildStockSystemDropdown(allowedSystems);
        rebuildStockViewDropdown(allowedViews);

        if (allowedViews.size > 0 && !allowedViews.has('records')) {
            const viewOrder = ['records', 'remark', 'product', 'sot', 'list'];
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
                const param = currentStockType ? `?system=${currentStockType}` : '';
                window.location.href = `${base}${param}`;
                return true;
            }
        }

        if (allowedSystems.size > 0) {
            // 检查URL参数
            const urlParams = new URLSearchParams(window.location.search);
            const urlSystem = urlParams.get('system');

            // 如果当前系统不在允许列表中，或者没有设置系统（首次进入），切换到第一个允许的系统
            if (!allowedSystems.has(currentStockType) || (!urlSystem && allowedSystems.size > 0)) {
                const firstAllowed = Array.from(allowedSystems)[0];
                if (firstAllowed) {
                    // 更新当前系统变量
                    currentStockType = firstAllowed;

                    // 更新API地址
                    switch (firstAllowed) {
                        case 'central':
                            API_BASE_URL = 'stockeditapi.php';
                            document.getElementById('page-title').textContent = '进出货 - 中央';
                            document.getElementById('current-stock-type').textContent = '中央';
                            break;
                        case 'j1':
                            API_BASE_URL = 'j1stockeditpageapi.php';
                            document.getElementById('page-title').textContent = '进出货 - J1';
                            document.getElementById('current-stock-type').textContent = 'J1';
                            break;
                        case 'j2':
                            API_BASE_URL = 'j2stockeditpageapi.php';
                            document.getElementById('page-title').textContent = '进出货 - J2';
                            document.getElementById('current-stock-type').textContent = 'J2';
                            break;
                        case 'j3':
                            API_BASE_URL = 'j3stockeditpageapi.php';
                            document.getElementById('page-title').textContent = '进出货 - J3';
                            document.getElementById('current-stock-type').textContent = 'J3';
                            break;
                    }

                    // 更新URL参数
                    const newUrl = new URL(window.location);
                    newUrl.searchParams.set('system', firstAllowed);
                    window.history.replaceState({}, '', newUrl);

                    // 更新active状态
                    document.querySelectorAll('.selector-dropdown .dropdown-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    const activeItem = document.querySelector(`.selector-dropdown .dropdown-item[data-type="${firstAllowed}"]`);
                    if (activeItem) {
                        activeItem.classList.add('active');
                    }

                    // 控制Type字段和导出按钮
                    const typeSelect = document.getElementById('add-type');
                    if (typeSelect) {
                        if (firstAllowed === 'central') {
                            typeSelect.disabled = true;
                            typeSelect.value = '';
                        } else {
                            typeSelect.disabled = false;
                        }
                    }

                    const exportButton = document.querySelector('.btn-warning[onclick="exportData()"]');
                    if (exportButton) {
                        const supportedSystems = ['central', 'j1', 'j2', 'j3'];
                        exportButton.style.display = supportedSystems.includes(firstAllowed) ? 'inline-block' : 'none';
                    }

                    // 清空当前数据并重新加载
                    stockData = [];
                    editingRowIds.clear();
                    if (originalEditData) {
                        originalEditData.clear();
                    }

                    // 重新加载数据
                    loadStockData();
                    loadCodeNumbers();
                    loadProducts();

                    return true; // 返回true表示已切换系统
                }
            }

            // 系统下拉 - 隐藏不允许的选项
        }

        return false; // 返回false表示没有切换系统
    } catch (e) {
        return false;
    }
}

// 切换视图选择器下拉菜单
function toggleViewSelector() {
    const dropdown = document.getElementById('view-selector-dropdown');
    dropdown.classList.toggle('show');
}

function switchView(viewType) {
    const currentSystem = currentStockType || 'central';
    const systemParam = `?system=${currentSystem}`;

    if (viewType === 'list') {
        window.location.href = `stocklistall${systemParam}`;
    } else if (viewType === 'remark') {
        window.location.href = `stockremark?system=central`;
    } else if (viewType === 'product') {
        window.location.href = `stockproductname?system=overview`;
    } else if (viewType === 'sot') {
        window.location.href = `stocksot?system=central`;
    } else {
        // 保持在当前页面（库存记录）
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

// 根据当前库存类型生成target选项（仅在 central 模式下使用）
function generateTargetOptions(selectedValue = '') {
    let options = '';
    // 仅 central 页面需要 select，其他页面直接只读显示
    if (currentStockType === 'central') {
        options += `<option value="j1" ${selectedValue === 'j1' ? 'selected' : ''}>J1</option>`;
        options += `<option value="j2" ${selectedValue === 'j2' ? 'selected' : ''}>J2</option>`;
        options += `<option value="j3" ${selectedValue === 'j3' ? 'selected' : ''}>J3</option>`;
        options += `<option value="central" ${selectedValue === 'central' ? 'selected' : ''}>中央</option>`;
    }
    return options;
}

// 返回仪表盘
function goBack() {
    window.location.href = 'dashboard';
}

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

    // 如果点击的不是选择器按钮且不是下拉菜单内部，则隐藏所有下拉菜单
    if (!selector && !dropdown) {
        document.getElementById('stock-dropdown')?.classList.remove('show');
        document.getElementById('view-selector-dropdown')?.classList.remove('show');
    }
});

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

// 加载库存数据
async function loadStockData() {
    if (isLoading) return;

    isLoading = true;

    try {
        // 构建API URL，使用日期范围参数
        let apiUrl = '?action=list&limit=10000';

        // 如果设置了日期范围，添加日期参数
        if (dateRange.startDate && dateRange.endDate) {
            apiUrl += `&start_date=${dateRange.startDate}&end_date=${dateRange.endDate}`;
            console.log('加载数据，日期范围:', dateRange.startDate, '到', dateRange.endDate);
        } else {
            console.log('加载数据，未设置日期范围');
        }

        const result = await apiCall(apiUrl);

        if (result.success) {
            stockData = (result.data || []).map(record => {
                const decodedRecord = { ...record };
                ['product_name', 'code_number', 'receiver', 'remark', 'category', 'specification', 'applicant', 'approver', 'type'].forEach(field => {
                    if (decodedRecord[field]) {
                        decodedRecord[field] = decodeHtml(decodedRecord[field]);
                    }
                });
                return decodedRecord;
            });
        } else {
            stockData = [];
            showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
        }

        renderStockTable();
        updateStats();

    } catch (error) {
        stockData = [];
        renderStockTable();
        updateStats();
        showAlert('网络错误，请检查连接', 'error');
    } finally {
        isLoading = false;
    }
}

// 加载code number选项
async function loadCodeNumbers() {
    try {
        const result = await apiCall('?action=codenumbers');
        if (result.success && result.data) {
            window.codeNumberOptions = result.data.map(opt => {
                if (typeof opt === 'string') return decodeHtml(opt);
                if (opt.code_number) opt.code_number = decodeHtml(opt.code_number);
                return opt;
            });
        } else {
            window.codeNumberOptions = [];
        }
    } catch (error) {
        console.error('加载编号列表失败:', error);
        window.codeNumberOptions = [];
    }
}

// 加载product name选项
async function loadProducts() {
    try {
        const result = await apiCall('?action=products_list');
        if (result.success && result.data) {
            window.productOptions = result.data.map(opt => {
                if (typeof opt === 'string') return decodeHtml(opt);
                if (opt.product_name) opt.product_name = decodeHtml(opt.product_name);
                return opt;
            });
        } else {
            window.productOptions = [];
        }
    } catch (error) {
        console.error('加载货品列表失败:', error);
        window.productOptions = [];
    }
}

// 根据货品名称获取货品编号和规格
async function getCodeByProduct(productName) {
    try {
        const result = await apiCall(`?action=code_by_product&product_name=${encodeURIComponent(productName)}`);
        if (result.success && result.data) {
            return {
                product_code: result.data.product_code,
                specification: result.data.specification,
                supplier: result.data.supplier,
                category: result.data.category
            };
        }
    } catch (error) {
        console.error('获取货品编号失败:', error);
    }
    return null;
}

// 将 API 返回的 category 规范化为类型下拉选项值（Service Line / Kitchen / Sushi Bar / Sake）
function normalizeCategoryForType(category) {
    if (category == null || category === '') return '';
    const c = String(category).trim();
    if (c === 'Drinks' || c.toLowerCase() === 'service line') return 'Service Line';
    if (['Kitchen', 'Sushi Bar', 'Service Line', 'Sake'].indexOf(c) !== -1) return c;
    return c;
}

// 生成货品名称下拉选项
function generateProductOptions(selectedValue = '') {
    if (!window.productOptions) return '<option value="">加载中...</option>';

    let options = '<option value="">请选择货品</option>';
    window.productOptions.forEach(item => {
        const selected = item.product_name === selectedValue ? 'selected' : '';
        options += `<option value="${item.product_name}" ${selected}>${item.product_name}</option>`;
    });
    return options;
}

// 清空出货相关字段的辅助函数
function clearOutboundFields(container) {
    // 清空出货数量
    const outQtyInput = container.querySelector('input[id*="-out-qty"], input[data-field="out_quantity"]');
    if (outQtyInput) {
        outQtyInput.value = '';
        // 触发change事件以更新相关UI
        outQtyInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // 清空单价
    const priceInput = container.querySelector('input[id*="-price"], input[data-field="price"]');
    if (priceInput) {
        priceInput.value = '';
    }

    // 清空总价
    const totalInput = container.querySelector('input[id*="-total"], input[data-field="total_value"]');
    if (totalInput) {
        totalInput.value = '';
    }

    // 清空供应商/出货人（用户必须手动选择，不自动填入 system）
    const receiverInput = container.querySelector('input[id*="-receiver"], input[data-field="receiver"]');
    if (receiverInput) {
        receiverInput.value = '';
        // 保持收货人字段始终可输入，不设置disabled
    }

    // 清空Target下拉框（仅 central 模式有 select）
    if (currentStockType === 'central') {
        const targetSelect = container.querySelector('select[id*="-target"], select[data-field="target_system"]');
        if (targetSelect) {
            targetSelect.value = '';
            targetSelect.disabled = true;
            targetSelect.required = false;
        }
    }

    // 清空规格字段
    const specificationSelect = container.querySelector('select[id*="-specification"], select[data-field="specification"]');
    if (specificationSelect) {
        specificationSelect.value = '';
    }

    // 清空类型字段
    const typeSelect = container.querySelector('select[id*="-type"], select[data-field="type"]');
    if (typeSelect) {
        typeSelect.value = '';
    }

    // 更新单价选项
    updatePriceOptions(container, '');
}

// 更新单价选项的辅助函数（同一货品多供应商时，按当前行的编号过滤只显示该供应商价格）
async function updatePriceOptions(container, productName) {
    const priceSelect = container.querySelector('select[id*="-price"], select[data-field="price"]');
    if (priceSelect) {
        if (productName) {
            try {
                const row = container.closest('tr');
                const codeInput = row ? row.querySelector('.combobox[data-type="code"] .combobox-input, input[id*="-code_number-input"]') : null;
                const codeNumber = codeInput && codeInput.value ? codeInput.value.trim() : '';
                const codeParam = codeNumber ? `&code_number=${encodeURIComponent(codeNumber)}` : '';
                const result = await apiCall(`?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=1${codeParam}`);
                if (result.success && result.data && result.data.length > 0) {
                    let options = '<option value="">请选择价格</option>';
                    // 始终保留手动输入价格选项
                    options += '<option value="manual">手动输入价格</option>';

                    result.data.forEach(item => {
                        const price = item.price;
                        const availableStock = item.available_stock;
                        // 显示所有价格选项，不管库存是否足够
                        const stockInfo = `(库存: ${availableStock})`;
                        options += `<option value="${price}">${parseFloat(price).toFixed(5)} ${stockInfo}</option>`;
                    });
                    priceSelect.innerHTML = options;
                } else {
                    // 即使没有价格数据，也保留手动输入选项
                    priceSelect.innerHTML = '<option value="">暂无历史价格</option><option value="manual">手动输入价格</option>';
                }
            } catch (error) {
                console.error('获取价格选项失败:', error);
                // 即使出错也保留手动输入选项
                priceSelect.innerHTML = '<option value="">加载失败</option><option value="manual">手动输入价格</option>';
            }
        } else {
            // 没有选择货品时也保留手动输入选项
            priceSelect.innerHTML = '<option value="">请先选择货品</option><option value="manual">手动输入价格</option>';
        }
    }
}

async function refreshEditPriceSelect(recordId, productName) {
    const selectId = `price-select-${recordId}`;
    const priceSelect = document.getElementById(selectId);

    if (!priceSelect) {
        return;
    }

    const container = priceSelect.closest('.currency-display');
    if (container) {
        const manualInput = container.querySelector('.manual-price-input');
        if (manualInput) {
            manualInput.remove();
        }
    }

    if (!productName) {
        priceSelect.innerHTML = '<option value="">请先选择货品</option><option value="manual">手动输入价格</option>';
        priceSelect.value = '';
        priceSelect.dataset.productName = '';
        priceSelect.dataset.currentPrice = '';
        priceSelect.setAttribute('data-product-name', '');
        priceSelect.setAttribute('data-current-price', '');
        return;
    }

    const record = stockData.find(r => r.id === recordId);
    const outQty = record ? parseFloat(record.out_quantity || 0) : 0;
    const codeNumber = record && record.code_number ? String(record.code_number).trim() : '';

    let currentPrice = '';
    let oldQty = 0;

    // 获取编辑模式下原始数据的价格和数量，以将其还原至可用库存中供本次计算
    if (originalEditData && originalEditData.has(recordId)) {
        const oldData = originalEditData.get(recordId);
        // 如果货品名称未改变，则可以使用原来的价格
        if (oldData.product_name === productName) {
            currentPrice = oldData.price_raw || oldData.price || '';
            oldQty = parseFloat(oldData.out_quantity) || 0;
        }
    }

    // 将原始价格和还原数量传递给下拉加载方法
    await loadProductPricesWithStock(productName, selectId, currentPrice, outQty, codeNumber, oldQty);

    priceSelect.value = currentPrice;
    priceSelect.dataset.productName = productName;
    priceSelect.dataset.currentPrice = currentPrice;
    priceSelect.setAttribute('data-product-name', productName);
    priceSelect.setAttribute('data-current-price', currentPrice);

    if (record) {
        if (!record.price && currentPrice) {
            record.price_raw = currentPrice;
        } else {
            record.price_raw = currentPrice; // 保留原单价
        }
    }
    updateField(recordId, 'price', currentPrice);
}

// 收货人选项列表
// 出货人选项列表（硬编码基础名单 + 动态追加权限用户）
let receiverOptions = ['中央', 'JUN HAO', 'A KIM', 'MJ', 'HY', 'CINDY'];

// 从 stockeditapi 获取被赋予「出货人」权限的用户昵称，追加到现有列表
async function loadShippers() {
    try {
        const response = await fetch('stockeditapi.php?action=get_shippers');
        if (!response.ok) return;
        const result = await response.json();
        if (result.success && Array.isArray(result.data)) {
            result.data.forEach(name => {
                if (name && !receiverOptions.includes(name)) {
                    receiverOptions.push(name);
                }
            });
        }
    } catch (e) {
        // 静默失败，保持硬编码列表不变
        console.warn('加载出货人列表失败，使用默认值', e);
    }
}

// 处理出货数量变化，控制收货单位输入框状态
function handleOutQuantityChange(container, outQty) {
    // 收货人字段保持始终可输入状态，不需要根据出货数量控制
    // 这个函数保留用于其他可能的逻辑扩展
}

// 进/出货互斥控制
function enforceQuantityMutex(inInput, outInput) {
    if (!inInput || !outInput) return;
    const inVal = parseFloat(inInput.value) || 0;
    const outVal = parseFloat(outInput.value) || 0;

    if (inVal > 0) {
        outInput.value = '';
        outInput.disabled = true;
    } else if (outVal > 0) {
        inInput.value = '';
        inInput.disabled = true;
    } else {
        inInput.disabled = false;
        outInput.disabled = false;
    }
}

// 处理编辑模式下出货数量变化
function handleEditOutQuantityChange(recordId, value) {
    const outQty = parseFloat(value) || 0;
    const row = document.querySelector(`tr[data-record-id="${recordId}"]`) ||
        document.querySelector(`input[data-record-id="${recordId}"]`)?.closest('tr');

    if (row) {
        // 非central模式下无target select，无需控制
        if (currentStockType === 'central') {
            const targetSelect = document.getElementById(`target-select-${recordId}`);
            if (targetSelect) {
                if (outQty > 0) {
                    targetSelect.disabled = false;
                    targetSelect.required = true;
                } else {
                    targetSelect.disabled = true;
                    targetSelect.value = '';
                    targetSelect.required = false;
                }
            }
        }

        // 收货人字段保持始终可输入状态，不需要根据出货数量控制
    }

    // 更新数据库中的值
    updateField(recordId, 'out_quantity', value);
}

// 处理新行中出货数量变化
function handleNewRowOutQuantityChange(rowId, value) {
    const outQty = parseFloat(value) || 0;
    const row = document.getElementById(`${rowId}-out-qty`)?.closest('tr');

    if (row) {
        // 非central模式下无target select，无需控制
        if (currentStockType === 'central') {
            const targetSelect = document.getElementById(`${rowId}-target`);
            if (targetSelect) {
                if (outQty > 0) {
                    targetSelect.disabled = false;
                    targetSelect.required = true;
                } else {
                    targetSelect.disabled = true;
                    targetSelect.value = '';
                    targetSelect.required = false;
                }
            }
        }

        // 收货人字段保持始终可输入状态，不需要根据出货数量控制
    }
}

// 需要自动勾选货品备注的货品列表
const autoRemarkProducts = [
    'SALMON',
    'SALMON BELLY 10PCS',
    'SALMON HEAD 10PCS',
    'SALMON BELLY 10PCS (P)',
    'SALMON HEAD 10PCS (P)',
    'HAMACHI FILLET MIKA',
    'A5 AWAGYU',
    'MAGURO BLUE FIN'
];

// 处理货品名称变化
async function handleProductChange(selectElement, codeNumberElement) {
    const productName = selectElement.value;
    const container = selectElement.closest('tr') || selectElement.closest('.form-container') || document;
    const recordId = selectElement.getAttribute('data-record-id');

    // 判断是否为编辑模式（检查是否在编辑中的行）
    const isEditMode = recordId && editingRowIds.has(parseInt(recordId));

    // 检查是否需要自动勾选货品备注（仅限中央页面）
    if (currentStockType === 'central') {
        const shouldCheckRemark = autoRemarkProducts.includes(productName);

        // 如果是新增表单
        if (selectElement.id === 'add-product-name') {
            const remarkCheckbox = document.getElementById('add-product-remark');
            if (remarkCheckbox) {
                remarkCheckbox.checked = shouldCheckRemark;
                // 触发toggleRemarkNumber以更新备注编号输入框状态
                if (typeof toggleRemarkNumber === 'function') {
                    toggleRemarkNumber();
                }
            }
            // 检查出库数量，如果有出库数量则加载价格
            if (productName) {
                console.log('新增表单货品变化，货品名称:', productName);
                const outQty = parseFloat(document.getElementById('add-out-qty')?.value || 0);
                console.log('当前出库数量:', outQty);
                if (outQty > 0) {
                    console.log('出库数量>0，延迟调用handleAddFormOutQuantityChange');
                    // 延迟执行，确保其他字段已更新
                    setTimeout(() => {
                        handleAddFormOutQuantityChange();
                    }, 100);
                } else {
                    console.log('出库数量为0，不加载价格');
                }
            }
        }
        // 如果是表格中的行（编辑模式）
        else if (isEditMode && recordId) {
            const row = selectElement.closest('tr');
            if (row) {
                const remarkCheckbox = row.querySelector('.remark-checkbox');
                if (remarkCheckbox && !remarkCheckbox.disabled) {
                    remarkCheckbox.checked = shouldCheckRemark;
                    // 触发updateRemarkCheck以更新备注编号输入框状态和数据库
                    if (typeof updateRemarkCheck === 'function') {
                        updateRemarkCheck(parseInt(recordId), shouldCheckRemark);
                    }
                }
            }
        }
    }

    // 只在非编辑模式下清空部分字段（规格和类型会根据新货品自动更新）
    // 用户要求：出货格子已填写的数量在更换货品时保留，不清空
    if (!isEditMode) {
        const priceInput = container.querySelector('input[id*="-price"], input[data-field="price"]');
        if (priceInput) {
            priceInput.value = '';
        }

        const totalInput = container.querySelector('input[id*="-total"], input[data-field="total_value"]');
        if (totalInput) {
            totalInput.value = '';
        }

        const receiverInput = container.querySelector('input[id*="-receiver"], input[data-field="receiver"]');
        if (receiverInput) {
            // 供应商/出货人由用户手动选择，不自动填入 system
            receiverInput.value = '';
        }

        // 仅 central 模式有 target select
        if (currentStockType === 'central') {
            const targetSelect = container.querySelector('select[id*="-target"], select[data-field="target_system"]');
            if (targetSelect) {
                targetSelect.value = '';
                targetSelect.disabled = true;
                targetSelect.required = false;
            }
        }

        // 更新单价选项
        updatePriceOptions(container, '');

        if (recordId) {
            updateField(parseInt(recordId), 'price', '');
            updateField(parseInt(recordId), 'receiver', '');
            updateField(parseInt(recordId), 'target_system', '');
            // 注意：不清空 out_quantity（出货数量保留）、specification 和 type
        }
    }

    if (productName) {
        const result = await getCodeByProduct(productName);
        if (result) {
            const { product_code, specification, supplier, category } = result;

            // 如果没有传入codeNumberElement，自动查找
            if (!codeNumberElement) {
                const row = selectElement.closest('tr');
                codeNumberElement = row ? row.querySelector('td:nth-child(2) select') || row.querySelector('td:nth-child(2) input') : null;
            }

            if (codeNumberElement) {
                if (codeNumberElement.tagName === 'SELECT') {
                    // 如果是下拉框，设置对应的值
                    codeNumberElement.value = product_code;
                } else if (codeNumberElement.tagName === 'INPUT') {
                    codeNumberElement.value = product_code;
                } else {
                    codeNumberElement.textContent = product_code;
                }
            }

            // 如果是在编辑模式，更新数据
            const normalizedCategory = normalizeCategoryForType(category);
            const row = selectElement.closest('tr');
            if (row && !row.classList.contains('new-row')) {
                const recordId = parseInt(selectElement.getAttribute('data-record-id'));
                if (recordId) {
                    updateField(recordId, 'code_number', product_code);
                    if (specification) {
                        updateField(recordId, 'specification', specification);
                    }
                    if (normalizedCategory) {
                        updateField(recordId, 'type', normalizedCategory);
                    }
                }
            }

            // 自动填充规格
            if (specification) {
                const specificationSelect = container.querySelector('select[id$="-specification"], select[onchange*="specification"]');
                if (specificationSelect) {
                    specificationSelect.value = specification;
                    specificationSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 自动填充类型（规范化以匹配下拉选项，中央/新增表单也需显式设置 add-type）
            if (normalizedCategory) {
                if (selectElement.id === 'add-product-name') {
                    const addTypeEl = document.getElementById('add-type');
                    if (addTypeEl) {
                        addTypeEl.value = normalizedCategory;
                        addTypeEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                const typeSelect = container.querySelector('select[id$="-type"], select[onchange*="type"]');
                if (typeSelect) {
                    typeSelect.value = normalizedCategory;
                    typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 保存supplier到当前行，并在有进货数量时自动填充
            if (row && supplier) {
                row.dataset.supplier = supplier;
                updateSupplierIfNeeded(row, recordId ? parseInt(recordId) : null);
            }

            // 更新单价选项
            if (isEditMode) {
                const editRecordId = parseInt(recordId, 10);
                if (!Number.isNaN(editRecordId)) {
                    await refreshEditPriceSelect(editRecordId, productName);
                } else {
                    updatePriceOptions(container, productName);
                }
            } else {
                updatePriceOptions(container, productName);
            }
        }
    } else {
        // 如果清空了货品名称，也要清空相关字段
        clearOutboundFields(container);
    }
}

// 根据code number获取货品名称和规格
async function getProductByCode(codeNumber) {
    try {
        const result = await apiCall(`?action=product_by_code&code_number=${encodeURIComponent(codeNumber)}`);
        if (result.success && result.data) {
            return {
                product_name: result.data.product_name,
                specification: result.data.specification,
                supplier: result.data.supplier,
                category: result.data.category
            };
        }
    } catch (error) {
        console.error('获取货品名称失败:', error);
    }
    return null;
}

// 生成code number下拉选项
function generateCodeNumberOptions(selectedValue = '') {
    if (!window.codeNumberOptions) return '<option value="">加载中...</option>';

    let options = '<option value="">请选择编号</option>';
    window.codeNumberOptions.forEach(item => {
        const selected = item.code_number === selectedValue ? 'selected' : '';
        options += `<option value="${item.code_number}" ${selected}>${item.code_number}</option>`;
    });
    return options;
}

// 生成收货人选项
function generateReceiverOptions(selectedValue = '') {
    let options = '<option value="">请选择收货人</option>';
    receiverOptions.forEach(receiver => {
        const selected = receiver === selectedValue ? 'selected' : '';
        options += `<option value="${receiver}" ${selected}>${receiver}</option>`;
    });
    return options;
}

// 处理code number变化
async function handleCodeNumberChange(selectElement, productNameElement) {
    const codeNumber = selectElement.value;
    const container = selectElement.closest('tr') || selectElement.closest('.form-container') || document;
    const recordId = selectElement.getAttribute('data-record-id');

    // 判断是否为编辑模式（检查是否在编辑中的行）
    const isEditMode = recordId && editingRowIds.has(parseInt(recordId));

    // 只在非编辑模式下清空部分字段；出货数量在更换货品/编号时保留
    if (!isEditMode) {
        const priceInput = container.querySelector('input[id*="-price"], input[data-field="price"]');
        if (priceInput) {
            priceInput.value = '';
        }

        const totalInput = container.querySelector('input[id*="-total"], input[data-field="total_value"]');
        if (totalInput) {
            totalInput.value = '';
        }

        const receiverInput = container.querySelector('input[id*="-receiver"], input[data-field="receiver"]');
        if (receiverInput) {
            // 供应商/出货人由用户手动选择，不自动填入 system
            receiverInput.value = '';
        }

        // 仅 central 模式有 target select
        if (currentStockType === 'central') {
            const targetSelect = container.querySelector('select[id*="-target"], select[data-field="target_system"]');
            if (targetSelect) {
                targetSelect.value = '';
                targetSelect.disabled = true;
                targetSelect.required = false;
            }
        }

        updatePriceOptions(container, '');

        if (recordId) {
            updateField(parseInt(recordId), 'price', '');
            updateField(parseInt(recordId), 'receiver', '');
            updateField(parseInt(recordId), 'target_system', '');
        }
    }

    if (codeNumber) {
        const result = await getProductByCode(codeNumber);
        if (result) {
            const { product_name, specification, supplier, category } = result;

            // 如果没有传入productNameElement，自动查找
            if (!productNameElement) {
                const row = selectElement.closest('tr');
                productNameElement = row ? row.querySelector('td:nth-child(3) select') || row.querySelector('td:nth-child(3) input') : null;
            }

            if (productNameElement) {
                if (productNameElement.tagName === 'INPUT') {
                    productNameElement.value = product_name;
                } else if (productNameElement.tagName === 'SELECT') {
                    productNameElement.value = product_name;
                } else {
                    productNameElement.textContent = product_name;
                }
            }

            // 如果是在编辑模式，更新数据
            const row = selectElement.closest('tr');
            if (row && !row.classList.contains('new-row')) {
                const recordId = parseInt(selectElement.getAttribute('data-record-id'));
                if (recordId) {
                    updateField(recordId, 'product_name', product_name);
                    if (specification) {
                        updateField(recordId, 'specification', specification);
                    }
                    if (normalizeCategoryForType(category)) {
                        updateField(recordId, 'type', normalizeCategoryForType(category));
                    }
                }
            }

            // 自动填充规格
            if (specification) {
                const specificationSelect = container.querySelector('select[id$="-specification"], select[onchange*="specification"]');
                if (specificationSelect) {
                    specificationSelect.value = specification;
                    specificationSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 自动填充类型（规范化以匹配下拉选项，新增表单显式设置 add-type）
            const normalizedCategory = normalizeCategoryForType(category);
            if (normalizedCategory) {
                if (selectElement.id === 'add-code-number') {
                    const addTypeEl = document.getElementById('add-type');
                    if (addTypeEl) {
                        addTypeEl.value = normalizedCategory;
                        addTypeEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                const typeSelect = container.querySelector('select[id$="-type"], select[onchange*="type"]');
                if (typeSelect) {
                    typeSelect.value = normalizedCategory;
                    typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 保存supplier到当前行，并在有进货数量时自动填充
            if (row && supplier) {
                row.dataset.supplier = supplier;
                updateSupplierIfNeeded(row, recordId ? parseInt(recordId) : null);
            }

            // 检查是否需要自动勾选货品备注（仅限中央页面）
            if (currentStockType === 'central') {
                const shouldCheckRemark = autoRemarkProducts.includes(product_name);

                // 如果是新增表单
                if (selectElement.id === 'add-code-number') {
                    const remarkCheckbox = document.getElementById('add-product-remark');
                    if (remarkCheckbox) {
                        remarkCheckbox.checked = shouldCheckRemark;
                        // 触发toggleRemarkNumber以更新备注编号输入框状态
                        if (typeof toggleRemarkNumber === 'function') {
                            toggleRemarkNumber();
                        }
                    }
                }
                // 如果是表格中的行（编辑模式）
                else if (isEditMode && recordId && row) {
                    const remarkCheckbox = row.querySelector('.remark-checkbox');
                    if (remarkCheckbox && !remarkCheckbox.disabled) {
                        remarkCheckbox.checked = shouldCheckRemark;
                        // 触发updateRemarkCheck以更新备注编号输入框状态和数据库
                        if (typeof updateRemarkCheck === 'function') {
                            updateRemarkCheck(parseInt(recordId), shouldCheckRemark);
                        }
                    }
                }
            }

            // 更新单价选项
            if (recordId) {
                const editRecordId = parseInt(recordId, 10);
                if (!Number.isNaN(editRecordId)) {
                    await refreshEditPriceSelect(editRecordId, product_name);
                } else {
                    updatePriceOptions(container, product_name);
                }
            } else {
                updatePriceOptions(container, product_name);
            }
        }
    } else {
        // 如果清空了编号，也要清空相关字段
        clearOutboundFields(container);
    }
}

// 实时搜索数据
async function searchData() {
    if (isLoading) return Promise.resolve();

    isLoading = true;

    try {
        const params = new URLSearchParams({
            action: 'list'
        });

        const unifiedSearch = document.getElementById('unified-filter').value.trim().toLowerCase();

        // 使用新的日期范围
        if (dateRange.startDate) params.append('start_date', dateRange.startDate);
        if (dateRange.endDate) params.append('end_date', dateRange.endDate);

        // 不再 append product_code / product_name / receiver
        const result = await apiCall(`?${params}`);

        if (result.success) {
            let data = result.data || [];

            if (unifiedSearch) {
                data = data.filter(record => {
                    // 计算总价用于搜索
                    const inQty = parseFloat(record.in_quantity) || 0;
                    const outQty = parseFloat(record.out_quantity) || 0;
                    const price = parseFloat(record.price) || 0;
                    const netQty = inQty - outQty;
                    const total = netQty * price;

                    // 搜索所有字段
                    return (
                        (record.code_number && record.code_number.toLowerCase().includes(unifiedSearch)) ||
                        (record.product_name && record.product_name.toLowerCase().includes(unifiedSearch)) ||
                        (record.in_quantity && record.in_quantity.toString().includes(unifiedSearch)) ||
                        (record.out_quantity && record.out_quantity.toString().includes(unifiedSearch)) ||
                        (record.receiver && record.receiver.toLowerCase().includes(unifiedSearch)) ||
                        (record.specification && record.specification.toLowerCase().includes(unifiedSearch)) ||
                        (record.price && record.price.toString().includes(unifiedSearch)) ||
                        (total.toFixed(2).includes(unifiedSearch)) || // 搜索计算出的总价
                        ((record.type === 'Drinks' ? 'Service Line' : record.type) || '').toLowerCase().includes(unifiedSearch) ||
                        (record.remark && record.remark.toLowerCase().includes(unifiedSearch)) ||
                        (record.remark_number && record.remark_number.toLowerCase().includes(unifiedSearch)) // 添加备注编号搜索
                    );
                });
            }

            stockData = data;
        } else {
            stockData = [];
            showAlert('搜索失败: ' + (result.message || '未知错误'), 'error');
        }

        renderStockTable();
        updateStats();

    } catch (error) {
        showAlert('搜索时发生错误', 'error');
    } finally {
        isLoading = false;
    }
}

// 重置搜索过滤器
function resetFilters() {
    document.getElementById('date-filter').value = '';
    document.getElementById('code-filter').value = '';  // 新添加
    document.getElementById('product-filter').value = '';
    document.getElementById('receiver-filter').value = '';
    loadStockData();
}

// 保存新创建的行
function saveNewRows() {
    return Array.from(document.querySelectorAll('.new-row')).map(row => ({
        element: row.cloneNode(true),
        parent: row.parentNode
    }));
}

// 恢复新创建的行
function restoreNewRows(newRows) {
    if (newRows && newRows.length > 0) {
        setTimeout(() => {
            const tbody = document.getElementById('stock-tbody');
            newRows.forEach(({ element }, index) => {
                // 添加淡入动画
                element.style.opacity = '0';
                element.style.transform = 'translateY(-10px)';
                element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                tbody.appendChild(element);

                // 延迟显示，创建错落有致的效果
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';

                    // 动画完成后清理样式
                    setTimeout(() => {
                        element.style.transition = '';
                        element.style.transform = '';
                    }, 300);
                }, index * 30); // 每行延迟30ms，减少等待时间
            });

            // 重新绑定 combobox 事件
            bindComboboxEvents();

            // 更新批量保存按钮的可见性
            updateBatchSaveButtonVisibility();
        }, 50); // 减少初始延迟
    }
}

// 保存所有正在编辑的行的输入值
function saveEditingRowsInputValues() {
    const editingValues = new Map();

    editingRowIds.forEach(id => {
        // 找到对应的行
        const row = Array.from(document.querySelectorAll('tr')).find(r => {
            const saveBtn = r.querySelector(`[onclick*="saveRecord(${id})"]`);
            return saveBtn !== null;
        });

        if (!row) return;

        const values = {};

        // 保存所有输入框和选择框的值
        row.querySelectorAll('input, select').forEach(input => {
            const onchange = input.getAttribute('onchange');
            if (!onchange) return;

            // 从 onchange 属性中提取字段名
            let field = null;

            // 匹配 updateField(id, 'field_name', ...)
            const updateFieldMatch = onchange.match(/updateField\(\d+,\s*'([^']+)'/);
            if (updateFieldMatch) {
                field = updateFieldMatch[1];
            }

            // 匹配 handleEditOutQuantityChange(id, ...)
            else if (onchange.includes('handleEditOutQuantityChange')) {
                field = 'out_quantity';
            }

            // 匹配 updateRemarkCheck(id, ...)
            else if (onchange.includes('updateRemarkCheck')) {
                field = 'product_remark_checked';
            }

            if (field) {
                if (input.type === 'checkbox') {
                    values[field] = input.checked;
                } else {
                    values[field] = input.value;
                }
            }
        });

        // 特别处理 combobox (货品名称、编号、收货人)
        const comboboxInputs = row.querySelectorAll('.combobox-input');
        comboboxInputs.forEach(input => {
            const comboDiv = input.closest('.combobox');
            if (comboDiv) {
                const dataType = comboDiv.getAttribute('data-type');
                if (dataType) {
                    let field = null;
                    if (dataType === 'product') field = 'product_name';
                    else if (dataType === 'code') field = 'code_number';
                    else if (dataType === 'receiver') field = 'receiver';

                    if (field) {
                        values[field] = input.value;
                    }
                }
            }
        });

        editingValues.set(id, values);
    });

    return editingValues;
}

// 恢复所有正在编辑的行的输入值
function restoreEditingRowsInputValues(editingValues) {
    if (!editingValues || editingValues.size === 0) return;

    setTimeout(() => {
        editingValues.forEach((values, id) => {
            // 找到对应的行
            const row = Array.from(document.querySelectorAll('tr')).find(r => {
                const saveBtn = r.querySelector(`[onclick*="saveRecord(${id})"]`);
                return saveBtn !== null;
            });

            if (!row) return;

            // 先更新 stockData 中的值（静默更新，不触发保存）
            const record = stockData.find(r => r.id === id);
            if (record) {
                Object.keys(values).forEach(field => {
                    record[field] = values[field];
                });
            }

            // 恢复所有输入框的值
            Object.keys(values).forEach(field => {
                let input = null;

                // 特殊处理 combobox 字段
                if (field === 'product_name' || field === 'code_number' || field === 'receiver') {
                    const dataType = field === 'product_name' ? 'product' :
                        field === 'code_number' ? 'code' : 'receiver';
                    const comboDiv = row.querySelector(`.combobox[data-type="${dataType}"]`);
                    if (comboDiv) {
                        input = comboDiv.querySelector('.combobox-input');
                    }
                }
                // 查找其他输入框
                else {
                    // 尝试通过 onchange 属性查找
                    input = row.querySelector(`[onchange*="'${field}'"]`);

                    // 特殊字段的备用查找
                    if (!input && field === 'out_quantity') {
                        input = row.querySelector('[onchange*="handleEditOutQuantityChange"]');
                    }
                    if (!input && field === 'product_remark_checked') {
                        input = row.querySelector('[onchange*="updateRemarkCheck"]');
                    }
                    // type字段的特殊处理：通过ID查找
                    if (!input && field === 'type') {
                        const rowIdMatch = row.querySelector('input')?.id?.match(/^([^-]+-[^-]+)/);
                        if (rowIdMatch) {
                            input = document.getElementById(`${rowIdMatch[1]}-type`);
                        }
                    }
                }

                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = values[field];
                        // 不触发 change 事件，避免尝试保存到数据库
                    } else {
                        input.value = values[field];
                    }
                }
            });

            // 手动更新计算值显示（如总价）
            if (record) {
                updateCalculatedValues(id);
            }
        });
    }, 150); // 给表格渲染和价格选项加载更多时间
}

// 渲染库存表格
function renderStockTable() {
    const tbody = document.getElementById('stock-tbody');
    tbody.innerHTML = '';

    if (stockData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="15" style="padding: 20px; color: #6b7280;">暂无数据</td></tr>';
        return;
    }

    stockData.forEach(record => {
        const row = document.createElement('tr');
        const isEditing = editingRowIds.has(record.id);

        if (isEditing) {
            row.classList.add('editing-row');
        }

        // 计算总价
        const inQty = parseFloat(record.in_quantity) || 0;
        const outQty = parseFloat(record.out_quantity) || 0;
        const price = parseFloat(record.price_raw ?? record.price) || 0;
        const netQty = inQty - outQty;
        const total = netQty * price;

        row.innerHTML = `
                    <td class="date-cell">
                        ${isEditing ?
                `<input type="date" class="table-input" value="${record.date}" onchange="updateField(${record.id}, 'date', this.value)">` :
                formatDate(record.date)
            }
                    </td>
                    <td>
                        ${isEditing ?
                createCombobox('code', record.code_number, record.id) :
                `<span>${record.code_number || '-'}</span>`
            }
                    </td>
                    <td>
                        ${isEditing ?
                createCombobox('product', record.product_name, record.id) :
                `<span>${record.product_name}</span>`
            }
                    </td>
                    <td>
                        ${isEditing ?
                `<input type="number" class="table-input" id="edit-in-${record.id}" value="${(parseFloat(record.in_quantity) || 0) === 0 ? '' : (record.in_quantity || '')}" min="0" step="0.001" placeholder="0" onchange="updateField(${record.id}, 'in_quantity', this.value)" oninput="enforceQuantityMutex(this, document.getElementById('edit-out-${record.id}'))" ${(parseFloat(record.out_quantity) || 0) > 0 ? 'disabled' : ''}>` :
                `<span>${formatNumber(record.in_quantity)}</span>`
            }
                    </td>
                    <td>
                        ${isEditing ?
                `<input type="number" class="table-input" id="edit-out-${record.id}" value="${(parseFloat(record.out_quantity) || 0) === 0 ? '' : (record.out_quantity || '')}" min="0" step="0.001" placeholder="0" onchange="handleEditOutQuantityChange(${record.id}, this.value)" oninput="enforceQuantityMutex(document.getElementById('edit-in-${record.id}'), this)" ${(parseFloat(record.in_quantity) || 0) > 0 ? 'disabled' : ''}>` :
                `<span class="${outQty > 0 ? 'negative-value' : ''}">${formatNumber(record.out_quantity)}</span>`
            }
                    </td>
                    <td>
                        ${currentStockType !== 'central' ?
                // 非central：始终只读显示当前系统，不显示 select
                `<span>${currentStockType.toUpperCase()}</span>` :
                // central：编辑時显示 select，否則显示值
                (isEditing ?
                    `<select class="table-select" id="target-select-${record.id}" onchange="updateField(${record.id}, 'target_system', this.value)" ${(parseFloat(record.out_quantity || 0) === 0) ? 'disabled' : ''}>
                            <option value="">请选择</option>
                            ${generateTargetOptions(record.target_system)}
                        </select>` :
                    `<span>${record.target_system ? record.target_system.toUpperCase() : '-'}</span>`)
            }
                    </td>
                    <td>
                        ${isEditing ?
                `<select class="table-select" onchange="updateField(${record.id}, 'specification', this.value)">
                                ${specifications.map(spec =>
                    `<option value="${spec}" ${record.specification === spec ? 'selected' : ''}>${spec}</option>`
                ).join('')}
                            </select>` :
                `<span>${record.specification || '-'}</span>`
            }
                    </td>
                    <td>
                        ${isEditing ?
                (parseFloat(record.out_quantity || 0) > 0 && parseFloat(record.in_quantity || 0) === 0 ?
                    `<div class="currency-display"><span class="currency-symbol">RM</span><select class="table-select price-select" id="price-select-${record.id}" onchange="updateField(${record.id}, 'price', this.value)" data-product-name="${record.product_name}" data-current-price="${record.price_raw ?? record.price}"><option value="">请选择价格</option>${(record.price_raw ?? record.price) !== '' && (record.price_raw ?? record.price) !== null ? `<option value="${record.price_raw ?? record.price}" selected>${parseFloat(record.price_raw ?? record.price).toFixed(3)}</option>` : ''}</select></div>` :
                    `<div class="currency-display"><span class="currency-symbol">RM</span><input type="number" class="currency-input-edit" value="${parseFloat(record.price_raw ?? (record.price || 0)) === 0 ? '' : formatCurrencyEdit(record.price_raw ?? record.price)}" min="0" step="0.00001" placeholder="0.00" onchange="updateField(${record.id}, 'price', this.value)"></div>`
                ) :
                `<div class="currency-display"><span class="currency-symbol">RM</span><span class="currency-amount">${formatCurrency(record.price)}</span></div>`
            }
                    </td>
                    <td class="calculated-cell ${total < 0 ? 'negative-value negative-parentheses' : ''}">
                        <div class="currency-display ${total < 0 ? 'negative-value negative-parentheses' : ''}"><span class="currency-symbol">RM</span><span class="currency-amount">${formatCurrency(Math.abs(total))}</span></div>
                    </td>
                    <td>
                        ${isEditing ?
                (currentStockType !== 'central' ?
                    `<select class="table-select" onchange="updateField(${record.id}, 'type', this.value)">
                                    ${generateTypeOptions(record.type)}
                                </select>` :
                    `<select class="table-select" disabled>
                                    ${generateTypeOptions(record.type)}
                                </select>`
                ) :
                `<span>${(record.type === 'Drinks' ? 'Service Line' : record.type) || '-'}</span>`
            }
                    </td>
                    <td style="text-align: center;">
                        ${isEditing ?
                `<input type="checkbox" class="remark-checkbox" ${record.product_remark_checked ? 'checked' : ''} 
                            onchange="updateRemarkCheck(${record.id}, this.checked)">` :
                `<input type="checkbox" class="remark-checkbox" ${record.product_remark_checked ? 'checked' : ''} disabled>`
            }
                    </td>
                    <td>
                        ${isEditing ?
                createRemarkNumberInput(record.remark_number || '', record.id, !record.product_remark_checked) :
                `<span>${record.remark_number || '-'}</span>`
            }
                    </td>
                    <td>
                        ${isEditing ?
                createCombobox('receiver', record.receiver || '', record.id, false, inQty > 0) :
                `<span>${record.receiver || '-'}</span>`
            }
                    </td>
                    <td>
                        ${isEditing ?
                `<input type="text" class="table-input" value="${record.remark || ''}" onchange="updateField(${record.id}, 'remark', this.value)">` :
                `<span>${record.remark || '-'}</span>`
            }
                    </td>
                    <td>
                        <span class="created-user" data-user="${record.created_by || '-'}" data-time="${formatCreatedAt(record.created_at)}">${record.created_by || '-'}</span>
                    </td>
                    <td>
                        <span class="action-cell">
                            ${isBatchDeleteMode ?
                `<input type="checkbox" class="batch-select-checkbox" 
                                        data-record-id="${record.id}" 
                                        onchange="toggleRecordSelection(${record.id}, this.checked)"
                                        ${selectedRecords.has(record.id) ? 'checked' : ''}>` :
                (isEditing ?
                    `<button class="action-btn edit-btn save-mode" onclick="saveRecord(${record.id})" title="保存">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button class="action-btn" onclick="cancelEdit(${record.id})" title="取消" style="background: #6b7280;">
                                        <i class="fas fa-times"></i>
                                    </button>` :
                    `<button class="action-btn edit-btn" onclick="editRecord(${record.id})" title="编辑">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteRecord(${record.id})" title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>`
                )
            }
                        </span>
                    </td>
                `;

        tbody.appendChild(row);
    });

    setTimeout(bindComboboxEvents, 0);

    // 加载所有编辑中记录的价格选项
    setTimeout(() => {
        stockData.forEach(record => {
            if (editingRowIds.has(record.id) && record.product_name) {
                const outQty = parseFloat(record.out_quantity || 0);
                const inQty = parseFloat(record.in_quantity || 0);
                // 只有纯出库时才加载价格选项（带库存检查）
                if (outQty > 0 && inQty === 0) {
                    const codeNum = record.code_number ? String(record.code_number).trim() : '';
                    loadProductPricesWithStock(record.product_name, `price-select-${record.id}`, (record.price_raw ?? record.price), outQty, codeNum);
                }
            }
        });
    }, 200);
}

// 格式化日期
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = months[date.getMonth()];
    return `${day} ${month}`;
}

// 格式化创建时间（MySQL UTC 转 UTC+8 马来西亚时间）
function formatCreatedAt(createdAt) {
    if (!createdAt || createdAt === '-') return '-';
    // MySQL 返回格式: "2026-03-25 01:04:00"，需当作 UTC 解析
    const utcStr = createdAt.replace(' ', 'T') + 'Z';
    const date = new Date(utcStr);
    if (isNaN(date.getTime())) return createdAt.slice(0, 16);
    // 转换为 UTC+8
    const offset = 8 * 60; // 分钟
    const local = new Date(date.getTime() + offset * 60 * 1000);
    const yyyy = local.getUTCFullYear();
    const mm = String(local.getUTCMonth() + 1).padStart(2, '0');
    const dd = String(local.getUTCDate()).padStart(2, '0');
    const hh = String(local.getUTCHours()).padStart(2, '0');
    const mi = String(local.getUTCMinutes()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd} ${hh}:${mi}`;
}

// 格式化数字 - 统一显示三位小数
function formatNumber(value) {
    if (!value || value === '' || value === '0') return '0.000';
    const num = parseFloat(value);
    return isNaN(num) ? '0.000' : num.toFixed(3);
}

function roundCurrencyValue(value) {
    if (value === null || value === undefined || value === '' || value === '0') return 0;
    const num = typeof value === 'number' ? value : parseFloat(value);
    if (!isFinite(num)) return 0;

    const sign = num >= 0 ? 1 : -1;
    const correction = Number.EPSILON * Math.max(1, Math.abs(num));
    const absRoundedCents = Math.round((Math.abs(num) + correction) * 100);
    return sign * (absRoundedCents / 100);
}

function formatCentsToCurrency(cents) {
    if (cents === null || cents === undefined || isNaN(cents)) return '0.00';
    const roundedCents = Math.round(cents);
    const sign = roundedCents < 0 ? '-' : '';
    const absCents = Math.abs(roundedCents);
    const units = Math.floor(absCents / 100);
    const centsPart = (absCents % 100).toString().padStart(2, '0');
    return `${sign}${units}.${centsPart}`;
}

// 根据规则进行进位：1/2退回0，3/4进位5，6/7退回5，8/9进位0
function roundToNearestFive(value) {
    if (value === null || value === undefined || value === '' || value === '0') return 0;
    const num = typeof value === 'number' ? value : parseFloat(value);
    if (!isFinite(num)) return 0;

    const sign = num >= 0 ? 1 : -1;
    const absNum = Math.abs(num);

    // 获取整数部分和小数部分
    const integerPart = Math.floor(absNum);
    const decimalPart = absNum - integerPart;

    // 获取最后一位小数（以分为单位）
    const cents = Math.round(decimalPart * 100);
    const lastDigit = cents % 10;

    let roundedCents;
    if (lastDigit === 1 || lastDigit === 2) {
        // 1/2 退回 0
        roundedCents = Math.floor(cents / 10) * 10;
    } else if (lastDigit === 3 || lastDigit === 4) {
        // 3/4 进位 5
        roundedCents = Math.floor(cents / 10) * 10 + 5;
    } else if (lastDigit === 6 || lastDigit === 7) {
        // 6/7 退回 5
        roundedCents = Math.floor(cents / 10) * 10 + 5;
    } else if (lastDigit === 8 || lastDigit === 9) {
        // 8/9 进位 0（进位到下一位）
        roundedCents = (Math.floor(cents / 10) + 1) * 10;
    } else {
        // 0 或 5 保持不变
        roundedCents = cents;
    }

    return sign * (integerPart + roundedCents / 100);
}

// 计算 rounding 调整量（可以是正数或负数）
// 返回值 = roundToNearestFive(value) - value
function calculateRoundingAdjustment(value) {
    if (value === null || value === undefined || value === '' || value === '0') return 0;
    const num = typeof value === 'number' ? value : parseFloat(value);
    if (!isFinite(num)) return 0;

    const rounded = roundToNearestFive(num);
    return rounded - num;
}

// 格式化货币 - 显示时使用两位小数
function formatCurrency(value) {
    const rounded = roundCurrencyValue(value);
    return rounded.toFixed(2);
}

// 格式化货币 - 编辑时使用三位小数
function formatCurrencyEdit(value) {
    if (!value || value === '' || value === '0') return '0.000';
    const num = parseFloat(value);
    return isNaN(num) ? '0.000' : num.toFixed(3);
}

// 格式化货币用于PDF生成 - 使用进位逻辑
function formatCurrencyForPDF(value) {
    const rounded = roundCurrencyValue(value);
    return rounded.toFixed(2);
}

// 更新统计信息
function updateStats() {
    const totalRecords = stockData.length;

    document.getElementById('total-records').textContent = totalRecords;
}

// 显示日期和行数选择弹窗
function showDateRowsModal() {
    const modal = document.getElementById('date-rows-modal');
    const dateInput = document.getElementById('selected-date');
    const rowsCountInput = document.getElementById('rows-count');

    // 设置默认日期为今天（使用本地时间，避免UTC时差导致日期偏移）
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    dateInput.value = today;

    // 重置行数输入框为1
    rowsCountInput.value = 1;

    // 显示弹窗
    modal.classList.add('show');

    // 聚焦到日期输入框
    setTimeout(() => {
        dateInput.focus();
    }, 100);
}

// 关闭日期和行数选择弹窗
function closeDateRowsModal() {
    const modal = document.getElementById('date-rows-modal');
    modal.classList.remove('show');

    // 清空备注输入框
    document.getElementById('new-record-remark').value = '';
}

// 点击弹窗外部关闭弹窗
document.addEventListener('click', function (event) {
    const modal = document.getElementById('date-rows-modal');
    if (event.target === modal) {
        closeDateRowsModal();
    }
});

// 创建多行记录
function createMultipleRows() {
    const selectedDate = document.getElementById('selected-date').value;
    const rowsCount = parseInt(document.getElementById('rows-count').value);
    const remarkValue = document.getElementById('new-record-remark').value.trim();

    // 验证输入
    if (!selectedDate) {
        showAlert('请选择日期', 'error');
        return;
    }

    if (!rowsCount || rowsCount < 1 || rowsCount > 50) {
        showAlert('请输入有效的行数（1-50）', 'error');
        return;
    }

    // 关闭弹窗
    closeDateRowsModal();

    // 创建指定数量的行
    for (let i = 0; i < rowsCount; i++) {
        addNewRowWithDate(selectedDate, remarkValue);
    }

    // 滚动到表格底部
    setTimeout(() => {
        const tableContainer = document.querySelector('.table-scroll-container');
        if (tableContainer) {
            tableContainer.scrollTop = tableContainer.scrollHeight;
        }
    }, 100);

    showAlert(`成功创建 ${rowsCount} 行记录`, 'success');
}

// 添加新行到表格（带指定日期）
function addNewRowWithDate(selectedDate, remarkValue = '') {
    const tbody = document.getElementById('stock-tbody');
    const row = document.createElement('tr');
    row.className = 'new-row';

    const rowId = 'new-' + Date.now() + '-' + (newRowCounter++); // 使用时间戳+计数器确保绝对唯一

    row.innerHTML = `
                <td><input type="date" class="table-input" value="${selectedDate}" id="${rowId}-date"></td>
                <td>${createCombobox('code', '', null, rowId)}</td>
                <td>${createCombobox('product', '', null, rowId)}</td>
                <td><input type="number" class="table-input" min="0" step="0.001" placeholder="0" id="${rowId}-in-qty" oninput="updateNewRowTotal(this)"></td>
                <td><input type="number" class="table-input" min="0" step="0.001" placeholder="0" id="${rowId}-out-qty" oninput="updateNewRowTotal(this)" onchange="handleNewRowOutQuantityChange('${rowId}', this.value)"></td>
                <td>
                    ${currentStockType !== 'central' ?
            // 非central：只读显示 + 隐藏 input 保存值
            `<span>${currentStockType.toUpperCase()}</span>
                        <input type="hidden" id="${rowId}-target" value="${currentStockType}">` :
            // central：保留 select
            `<select class="table-select" id="${rowId}-target" disabled>
                            <option value="">请选择</option>
                            ${generateTargetOptions()}
                        </select>`
        }
                </td>
                <td>
                    <select class="table-select" id="${rowId}-specification">
                        <option value="">请选择规格</option>
                        ${specifications.map(spec => `<option value="${spec}">${spec}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <div class="currency-display"><span class="currency-symbol">RM</span><input type="number" class="currency-input-edit" min="0" step="0.00001" placeholder="0.00" id="${rowId}-price" oninput="updateNewRowTotal(this)"></div>
                </td>
                <td class="calculated-cell">
                    <div class="currency-display"><span class="currency-symbol">RM</span><span class="currency-amount">0.00</span></div>
                </td>
                <td>
                    <select class="table-select" id="${rowId}-type" ${currentStockType === 'central' ? 'disabled' : ''}>
                        <option value="">请选择类型</option>
                        <option value="Kitchen">Kitchen</option>
                        <option value="Sushi Bar">Sushi Bar</option>
                        <option value="Service Line">Service Line</option>
                        <option value="Sake">Sake</option>
                    </select>
                </td>
                <td style="text-align: center;">
                    <input type="checkbox" class="remark-checkbox" id="${rowId}-product-remark" onchange="toggleNewRowRemarkNumber('${rowId}')">
                </td>
                <td>
                    ${createNewRowRemarkNumberInput(rowId)}
                </td>
                <td>${createCombobox('receiver', '', null, rowId)}</td>
                <td><input type="text" class="table-input" placeholder="输入备注..." id="${rowId}-remark" value="${remarkValue}"></td>
                <td><span class="created-user" data-user="-" data-time="-">-</span></td>
                <td>
                    <span class="action-cell">
                        <button class="action-btn save-new-btn" onclick="saveNewRowRecord(this)" title="保存">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="action-btn cancel-new-btn" onclick="cancelNewRow(this)" title="取消">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                </td>
            `;

    // 添加到表格底部
    tbody.appendChild(row);

    // 绑定 combobox 事件
    setTimeout(() => {
        bindComboboxEvents();

        // 自动聚焦到货品名称输入框
        const productInput = document.getElementById('new-product_name-input');
        if (productInput) {
            productInput.focus();
        }

        // 滚动到表格底部，确保新记录行可见
        const tableContainer = document.querySelector('.table-scroll-container');
        if (tableContainer) {
            tableContainer.scrollTop = tableContainer.scrollHeight;
        }

        // 更新批量保存按钮的可见性
        updateBatchSaveButtonVisibility();
    }, 100);
}

// 添加新行到表格（使用今天的日期）
function addNewRow() {
    // 使用本地时间，避免UTC时差导致日期偏移
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    addNewRowWithDate(today);
}

// 自动填充supplier到receiver字段（只在有进货数量时）
function updateSupplierIfNeeded(row, recordId) {
    if (!row) return;

    // 获取进货数量
    let inQty = 0;
    const inQtyInput = row.querySelector('input[id$="-in-qty"]');
    if (inQtyInput) {
        inQty = parseFloat(inQtyInput.value) || 0;
    }

    // 如果是编辑模式，检查原始数据是否是进货
    if (recordId && !isNaN(parseInt(recordId)) && editingRowIds.has(parseInt(recordId)) && originalEditData && originalEditData.has(parseInt(recordId))) {
        const originalRecord = originalEditData.get(parseInt(recordId));
        const originalInQty = parseFloat(originalRecord.in_quantity) || 0;

        // 如果原始数据是进货（in_quantity > 0），则使用原始进货数量来判断
        // 这样即使编辑时改了进货数量，更换货品时仍会根据原始类型更新收货人
        if (originalInQty > 0) {
            inQty = originalInQty; // 使用原始进货数量
        } else {
            // 如果原始数据是出货（in_quantity = 0），则保持现状，不自动更新收货人
            return;
        }
    }

    // 查找receiver输入框
    const receiverInput = row.querySelector('.combobox-input[data-type="receiver"]');
    if (!receiverInput) return;

    const receiverContainer = receiverInput.closest('.combobox-container');
    const receiverArrow = receiverContainer ? receiverContainer.querySelector('.combobox-arrow') : null;

    // 只在有进货数量时才自动填充supplier，否则清空
    if (inQty > 0 && row.dataset.supplier) {
        receiverInput.value = row.dataset.supplier;

        // 锁定收货人字段（进货时）
        receiverInput.disabled = true;
        receiverInput.readOnly = true;
        receiverInput.classList.add('disabled');
        if (receiverArrow) {
            receiverArrow.classList.add('disabled');
        }

        // 如果有recordId，更新数据库
        if (recordId) {
            updateField(parseInt(recordId), 'receiver', row.dataset.supplier);
        }
    } else {
        // 供应商/出货人由用户手动选择，不自动填入 system
        receiverInput.value = '';

        // 解锁收货人字段（非进货时）
        receiverInput.disabled = false;
        receiverInput.readOnly = false;
        receiverInput.classList.remove('disabled');
        if (receiverArrow) {
            receiverArrow.classList.remove('disabled');
        }

        // 如果有recordId，更新数据库
        if (recordId) {
            updateField(parseInt(recordId), 'receiver', '');
        }
    }
}

// 更新新行的总价计算
function updateNewRowTotal(element) {
    const row = element.closest('tr');
    const idParts = element.id.split('-');
    const rowId = idParts[0] + '-' + idParts[1] + '-' + idParts[2]; // 获取行的唯一ID

    const inQtyInput = document.getElementById(`${rowId}-in-qty`);
    const outQtyInput = document.getElementById(`${rowId}-out-qty`);
    const inQty = parseFloat(inQtyInput.value) || 0;
    const outQty = parseFloat(outQtyInput.value) || 0;
    const price = parseFloat(document.getElementById(`${rowId}-price`).value) || 0;

    // 互斥判断
    enforceQuantityMutex(inQtyInput, outQtyInput);

    // 新增：当进货数量变化时，检查是否需要自动填充supplier
    if (element.id.includes('-in-qty') && row && row.dataset.supplier) {
        updateSupplierIfNeeded(row, null);
    }

    // 新增：控制收货人字段的锁定/解锁状态（只在有新行ID时）
    if (rowId && rowId.includes('-')) {
        const receiverInput = row.querySelector('.combobox-input[data-type="receiver"]');
        const receiverContainer = receiverInput ? receiverInput.closest('.combobox-container') : null;
        const receiverArrow = receiverContainer ? receiverContainer.querySelector('.combobox-arrow') : null;

        if (receiverInput && receiverContainer) {
            if (inQty > 0) {
                // 进货时锁定收货人字段
                receiverInput.disabled = true;
                receiverInput.readOnly = true;
                receiverInput.classList.add('disabled');
                if (receiverArrow) {
                    receiverArrow.classList.add('disabled');
                }
            } else {
                // 非进货时解锁收货人字段
                receiverInput.disabled = false;
                receiverInput.readOnly = false;
                receiverInput.classList.remove('disabled');
                if (receiverArrow) {
                    receiverArrow.classList.remove('disabled');
                }
            }
        }
    }

    // 控制Target下拉框（仅 central 模式有 select）
    if (currentStockType === 'central') {
        const targetSelect = document.getElementById(`${rowId}-target`);
        if (targetSelect && targetSelect.tagName === 'SELECT') {
            if (outQty > 0) {
                targetSelect.disabled = false;
                targetSelect.required = true;
            } else {
                targetSelect.disabled = true;
                targetSelect.value = '';
                targetSelect.required = false;
            }
        }
    }

    // 新增：检查是否需要显示价格下拉列表
    const productInput = document.getElementById(`${rowId}-product_name-input`);
    const productName = productInput ? productInput.value : '';
    const priceCell = document.getElementById(`${rowId}-price`).closest('.currency-display');

    if (outQty > 0 && inQty === 0 && productName) {
        // 纯出库且有货品名称，创建价格下拉选项（带库存检查）
        createNewRowPriceSelectWithStock(rowId, productName, price, outQty);
    } else if (outQty === 0 || inQty > 0) {
        // 恢复普通输入框
        restoreNewRowPriceInput(rowId);
    }

    const netQty = inQty - outQty;
    const total = netQty * price;

    const totalCell = row.querySelector('.calculated-cell');
    const currencyDisplay = totalCell.querySelector('.currency-display');
    const currencyAmount = totalCell.querySelector('.currency-amount');

    if (totalCell && currencyDisplay && currencyAmount) {
        // 更新数值
        currencyAmount.textContent = formatCurrency(Math.abs(total));

        // 添加或移除负数样式
        if (total < 0) {
            totalCell.classList.add('negative-value', 'negative-parentheses');
            currencyDisplay.classList.add('negative-value', 'negative-parentheses');
        } else {
            totalCell.classList.remove('negative-value', 'negative-parentheses');
            currencyDisplay.classList.remove('negative-value', 'negative-parentheses');
        }
    }
}

// 更新货品备注勾选状态
function updateRemarkCheck(id, checked) {
    const record = stockData.find(r => r.id === id);
    if (record) {
        record.product_remark_checked = checked;

        // 查找该行的备注编号输入框容器
        const row = document.querySelector(`tr:has(.remark-checkbox[onchange*="updateRemarkCheck(${id}"])`);
        if (row) {
            // 找到备注编号输入框（prefix 和 suffix）
            const remarkWrapper = row.querySelector('.remark-number-input-wrapper');
            if (remarkWrapper) {
                const prefixInput = remarkWrapper.querySelector('.remark-prefix');
                const suffixInput = remarkWrapper.querySelector('.remark-suffix');

                if (checked) {
                    // 启用输入框
                    remarkWrapper.style.opacity = '1';
                    remarkWrapper.removeAttribute('data-disabled');
                    if (prefixInput) prefixInput.disabled = false;
                    if (suffixInput) suffixInput.disabled = false;
                } else {
                    // 禁用输入框并清空值
                    remarkWrapper.style.opacity = '0.5';
                    remarkWrapper.setAttribute('data-disabled', 'true');
                    if (prefixInput) {
                        prefixInput.disabled = true;
                        prefixInput.value = '';
                    }
                    if (suffixInput) {
                        suffixInput.disabled = true;
                        suffixInput.value = '';
                    }
                    record.remark_number = '';
                    updateField(id, 'remark_number', '');
                }
            }
        }

        // 更新数据库
        updateField(id, 'product_remark_checked', checked);
    }
}

// 获取表单中的完整备注编号
function getFormRemarkNumber() {
    const prefix = (document.getElementById('add-remark-prefix')?.value || '').trim().toUpperCase();
    const suffix = (document.getElementById('add-remark-suffix')?.value || '').trim().toUpperCase();
    return (prefix || suffix) ? `${prefix}-${suffix}` : '';
}

// 控制新增表单中备注编号的启用状态
function toggleRemarkNumber() {
    const checkbox = document.getElementById('add-product-remark');
    const wrapper = document.getElementById('add-remark-wrapper');
    const prefixInput = document.getElementById('add-remark-prefix');
    const suffixInput = document.getElementById('add-remark-suffix');

    if (checkbox.checked) {
        wrapper.style.opacity = '1';
        prefixInput.disabled = false;
        suffixInput.disabled = false;
    } else {
        wrapper.style.opacity = '0.5';
        prefixInput.disabled = true;
        suffixInput.disabled = true;
        prefixInput.value = '';
        suffixInput.value = '';
        document.getElementById('add-remark-number').value = '';
    }
}

function toggleNewRowRemarkNumber(rowId) {
    const checkbox = document.getElementById(`${rowId}-product-remark`);
    const wrapper = document.getElementById(`${rowId}-remark-wrapper`);
    const prefixInput = document.getElementById(`${rowId}-remark-prefix`);
    const suffixInput = document.getElementById(`${rowId}-remark-suffix`);

    if (checkbox && wrapper && prefixInput && suffixInput) {
        if (checkbox.checked) {
            wrapper.style.opacity = '1';
            wrapper.setAttribute('data-disabled', 'false');
            prefixInput.disabled = false;
            suffixInput.disabled = false;

            // 绑定输入事件
            prefixInput.oninput = suffixInput.oninput = () => {
                prefixInput.value = (prefixInput.value || '').toUpperCase();
                suffixInput.value = (suffixInput.value || '').toUpperCase();
                updateNewRowRemarkNumber(rowId);
            };
        } else {
            wrapper.style.opacity = '0.5';
            wrapper.setAttribute('data-disabled', 'true');
            prefixInput.disabled = true;
            suffixInput.disabled = true;
            prefixInput.value = '';
            suffixInput.value = '';
            updateNewRowRemarkNumber(rowId);
        }
    }
}

// 创建备注编号输入框（用于编辑模式）
function createRemarkNumberInput(value, recordId, disabled) {
    const parts = value ? value.split('-') : ['', ''];
    const prefix = parts[0] || '';
    const suffix = parts[1] || '';
    const opacity = disabled ? '0.5' : '1';

    return `
                <div class="remark-number-input-wrapper" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 4px; background: white; padding: 0; opacity: ${opacity};" ${disabled ? 'data-disabled="true"' : ''}>
                    <input type="text" class="table-input remark-prefix" value="${prefix}" placeholder="" 
                        style="border: none; border-radius: 4px 0 0 4px; width: clamp(14px, 1.56vw, 30px); text-align: center; background: transparent; padding: 0px;" 
                        ${disabled ? 'disabled' : ''} 
                        oninput="this.value = this.value.toUpperCase();"
                        onchange="updateRemarkNumber(${recordId})">
                    <span style="padding: 0px; color: #6b7280; font-weight: bold;">-</span>
                    <input type="text" class="table-input remark-suffix" value="${suffix}" placeholder="" 
                        style="border: none; border-radius: 0 4px 4px 0; width: clamp(16px, 1.56vw, 30px); text-align: center; background: transparent; padding: 0px;" 
                        ${disabled ? 'disabled' : ''} 
                        oninput="this.value = this.value.toUpperCase();"
                        onchange="updateRemarkNumber(${recordId})">
                </div>
            `;
}

// 创建新行备注编号输入框
function createNewRowRemarkNumberInput(rowId) {
    return `
                <div class="remark-number-input-wrapper" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 4px; background: white; padding: 0;" id="${rowId}-remark-wrapper" data-disabled="true">
                    <input type="text" class="table-input remark-prefix" placeholder="" 
                        style="border: none; border-radius: 4px 0 0 4px; width: clamp(14px, 1.56vw, 30px); text-align: center; background: transparent; padding: 0px;" 
                        id="${rowId}-remark-prefix" disabled>
                    <span style="padding: 0px; color: #6b7280; font-weight: bold;">-</span>
                    <input type="text" class="table-input remark-suffix" placeholder="" 
                        style="border: none; border-radius: 0 4px 4px 0; width: clamp(16px, 1.56vw, 30px); text-align: center; background: transparent; padding: 0px;" 
                        id="${rowId}-remark-suffix" disabled>
                </div>
            `;
}

// 更新备注编号（合并前缀和后缀）
function updateRemarkNumber(recordId) {
    const row = document.querySelector(`[onchange*="updateRemarkNumber(${recordId})"]`).closest('tr');
    const wrapper = row.querySelector('.remark-number-input-wrapper');
    const prefixInput = wrapper.querySelector('.remark-prefix');
    const suffixInput = wrapper.querySelector('.remark-suffix');

    const prefix = (prefixInput.value || '').trim().toUpperCase();
    const suffix = (suffixInput.value || '').trim().toUpperCase();
    prefixInput.value = prefix;
    suffixInput.value = suffix;
    const fullValue = (prefix || suffix) ? `${prefix}-${suffix}` : '';

    updateField(recordId, 'remark_number', fullValue);
}

// 更新新行备注编号
function updateNewRowRemarkNumber(rowId) {
    const prefixInput = document.getElementById(`${rowId}-remark-prefix`);
    const suffixInput = document.getElementById(`${rowId}-remark-suffix`);

    if (prefixInput && suffixInput) {
        const prefix = (prefixInput.value || '').trim().toUpperCase();
        const suffix = (suffixInput.value || '').trim().toUpperCase();
        prefixInput.value = prefix;
        suffixInput.value = suffix;
        const fullValue = (prefix || suffix) ? `${prefix}-${suffix}` : '';

        // 更新隐藏的完整值（用于保存）
        const hiddenInput = document.getElementById(`${rowId}-remark-number`);
        if (!hiddenInput) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.id = `${rowId}-remark-number`;
            hidden.value = fullValue;
            prefixInput.closest('td').appendChild(hidden);
        } else {
            hiddenInput.value = fullValue;
        }
    }
}

// 提取行数据的辅助函数
function extractRowData(row) {
    const firstInput = row.querySelector('input');
    const idParts = firstInput.id.split('-');
    const rowId = idParts[0] + '-' + idParts[1] + '-' + idParts[2];

    // 获取收货人值（可能是combobox输入框）
    const receiverInput = document.getElementById(`${rowId}-receiver-input`);
    const receiverValue = receiverInput ? receiverInput.value : (document.getElementById(`${rowId}-receiver`) ? document.getElementById(`${rowId}-receiver`).value : '');

    return {
        date: document.getElementById(`${rowId}-date`) ? document.getElementById(`${rowId}-date`).value : '',
        codeValue: document.getElementById(`${rowId}-code_number-input`) ? document.getElementById(`${rowId}-code_number-input`).value : '',
        productValue: document.getElementById(`${rowId}-product_name-input`) ? document.getElementById(`${rowId}-product_name-input`).value : '',
        inQty: document.getElementById(`${rowId}-in-qty`) ? document.getElementById(`${rowId}-in-qty`).value : '',
        outQty: document.getElementById(`${rowId}-out-qty`) ? document.getElementById(`${rowId}-out-qty`).value : '',
        specification: document.getElementById(`${rowId}-specification`) ? document.getElementById(`${rowId}-specification`).value : '',
        price: document.getElementById(`${rowId}-price`) ? document.getElementById(`${rowId}-price`).value : '',
        receiver: receiverValue,
        remark: document.getElementById(`${rowId}-remark`) ? document.getElementById(`${rowId}-remark`).value : '',
        target: document.getElementById(`${rowId}-target`) ? document.getElementById(`${rowId}-target`).value : '',
        productRemarkChecked: document.getElementById(`${rowId}-product-remark`) ? document.getElementById(`${rowId}-product-remark`).checked : false,
        remarkNumber: document.getElementById(`${rowId}-remark-number`) ? document.getElementById(`${rowId}-remark-number`).value : '',
        type: document.getElementById(`${rowId}-type`) ? document.getElementById(`${rowId}-type`).value : ''
    };
}

// 恢复行数据的辅助函数
function restoreRowData(element, data) {
    const firstInput = element.querySelector('input');
    const idParts = firstInput.id.split('-');
    const rowId = idParts[0] + '-' + idParts[1] + '-' + idParts[2];

    if (document.getElementById(`${rowId}-date`)) document.getElementById(`${rowId}-date`).value = data.date;
    if (document.getElementById(`${rowId}-code_number-input`)) document.getElementById(`${rowId}-code_number-input`).value = data.codeValue;
    if (document.getElementById(`${rowId}-product_name-input`)) document.getElementById(`${rowId}-product_name-input`).value = data.productValue;
    if (document.getElementById(`${rowId}-in-qty`)) document.getElementById(`${rowId}-in-qty`).value = data.inQty;
    if (document.getElementById(`${rowId}-out-qty`)) document.getElementById(`${rowId}-out-qty`).value = data.outQty;

    // 恢复规格下拉框的选中状态
    const specSelect = document.getElementById(`${rowId}-specification`);
    if (specSelect && data.specification) {
        specSelect.value = data.specification;
        // 如果值不在选项中，添加并选中
        if (!Array.from(specSelect.options).some(opt => opt.value === data.specification)) {
            const option = document.createElement('option');
            option.value = data.specification;
            option.textContent = data.specification;
            specSelect.appendChild(option);
        }
        specSelect.value = data.specification;
    }

    if (document.getElementById(`${rowId}-price`)) document.getElementById(`${rowId}-price`).value = data.price;

    // 恢复收货人（combobox输入框）
    const receiverInput = document.getElementById(`${rowId}-receiver-input`);
    if (receiverInput && data.receiver) {
        receiverInput.value = data.receiver;
    }

    if (document.getElementById(`${rowId}-remark`)) document.getElementById(`${rowId}-remark`).value = data.remark;

    // 恢复target下拉框的选中状态
    const targetSelect = document.getElementById(`${rowId}-target`);
    if (targetSelect && data.target) {
        targetSelect.value = data.target;
    }

    // 恢复货品备注复选框
    const productRemarkCheckbox = document.getElementById(`${rowId}-product-remark`);
    if (productRemarkCheckbox && data.productRemarkChecked !== undefined) {
        productRemarkCheckbox.checked = data.productRemarkChecked;
    }

    // 恢复备注编号
    const remarkNumberInput = document.getElementById(`${rowId}-remark-number`);
    if (remarkNumberInput && data.remarkNumber) {
        remarkNumberInput.value = data.remarkNumber;
    }

    // 恢复类型下拉框的选中状态
    const typeSelect = document.getElementById(`${rowId}-type`);
    if (typeSelect && data.type !== undefined) {
        // 如果值不在选项中，添加并选中
        if (data.type && !Array.from(typeSelect.options).some(opt => opt.value === data.type)) {
            const option = document.createElement('option');
            option.value = data.type;
            option.textContent = data.type;
            typeSelect.appendChild(option);
        }
        typeSelect.value = data.type || '';
    }
}

// 保存新行记录
async function saveNewRowRecord(buttonElement, skipTableRefresh = false) {
    const row = buttonElement.closest('tr');
    const firstInput = row.querySelector('input');
    const idParts = firstInput.id.split('-');
    const rowId = idParts[0] + '-' + idParts[1] + '-' + idParts[2];

    console.log('保存新行记录，rowId:', rowId);
    console.log('行元素:', row);

    const codeInput = document.getElementById(`${rowId}-code_number-input`);
    const productInput = document.getElementById(`${rowId}-product_name-input`);

    console.log('codeInput:', codeInput);
    console.log('productInput:', productInput);

    // 获取收货人字段（combobox）
    const receiverInput = document.getElementById(`${rowId}-receiver-input`);

    console.log('receiverInput:', receiverInput);
    console.log('receiverInput value:', receiverInput ? receiverInput.value : 'null');

    const formData = {
        date: document.getElementById(`${rowId}-date`) ? document.getElementById(`${rowId}-date`).value : '',
        time: new Date().toTimeString().slice(0, 5),
        product_name: productInput ? productInput.value : '',
        in_quantity: parseFloat(document.getElementById(`${rowId}-in-qty`) ? document.getElementById(`${rowId}-in-qty`).value : 0) || 0,
        out_quantity: parseFloat(document.getElementById(`${rowId}-out-qty`) ? document.getElementById(`${rowId}-out-qty`).value : 0) || 0,
        specification: document.getElementById(`${rowId}-specification`) ? document.getElementById(`${rowId}-specification`).value : '',
        price: (() => {
            const hiddenInput = document.getElementById(`${rowId}-price`);
            const priceSelect = document.getElementById(`${rowId}-price-select`);
            // 当价格下拉列表激活时，hidden input 是空的，需要读 select 的值
            if (hiddenInput && hiddenInput.value !== '') return hiddenInput.value;
            if (priceSelect && priceSelect.value !== '' && priceSelect.value !== 'manual') return priceSelect.value;
            return hiddenInput ? hiddenInput.value : '';
        })(),
        receiver: receiverInput ? receiverInput.value : '',
        code_number: codeInput ? codeInput.value : '',
        remark: document.getElementById(`${rowId}-remark`) ? document.getElementById(`${rowId}-remark`).value : '',
        product_remark_checked: document.getElementById(`${rowId}-product-remark`) ? document.getElementById(`${rowId}-product-remark`).checked : false,
        remark_number: document.getElementById(`${rowId}-remark-number`) ? document.getElementById(`${rowId}-remark-number`).value.trim().toUpperCase() : '',
        type: document.getElementById(`${rowId}-type`) ? document.getElementById(`${rowId}-type`).value : ''
    };

    // 调试信息
    console.log('formData:', formData);
    console.log('product_name:', formData.product_name);
    console.log('specification:', formData.specification);
    console.log('receiver:', formData.receiver);

    // 验证必填字段
    if (!formData.product_name || !formData.specification || !formData.receiver) {
        const errorMsg = '请填写货品名称、规格单位和收货人';
        if (!skipTableRefresh) {
            showAlert(errorMsg, 'error');
        }
        throw new Error(errorMsg);
    }

    // 验证数量：不能为负数
    if (formData.in_quantity < 0 || formData.out_quantity < 0) {
        const errorMsg = '数量不能为负数';
        if (!skipTableRefresh) showAlert(errorMsg, 'error');
        throw new Error(errorMsg);
    }
    // 验证数量：进货和出货不能同时为 0
    if (formData.in_quantity <= 0 && formData.out_quantity <= 0) {
        const errorMsg = '进货或出货数量必须至少填入一项大于 0';
        if (!skipTableRefresh) showAlert(errorMsg, 'error');
        throw new Error(errorMsg);
    }

    // 验证单价：不能为空且不能小于0
    if (formData.price === '' || formData.price === null || formData.price === undefined || parseFloat(formData.price) < 0) {
        const errorMsg = '单价不能为空且不能小于0';
        if (!skipTableRefresh) showAlert(errorMsg, 'error');
        throw new Error(errorMsg);
    }

    // 当勾选货品备注时，必须填写备注编号
    if (formData.product_remark_checked && !formData.remark_number) {
        const errorMsg = '货品备注已勾选时，请填写备注编号';
        if (!skipTableRefresh) {
            showAlert(errorMsg, 'error');
        }
        throw new Error(errorMsg);
    }

    // 添加target处理
    if (currentStockType !== 'central') {
        // 非central页面：强制使用当前系统，不需要验证 select
        formData.target_system = currentStockType;
    } else if (formData.out_quantity > 0) {
        // central页面：需要验证 select
        const targetInput = document.getElementById(`${rowId}-target`);
        if (!targetInput || !targetInput.value) {
            const errorMsg = '当有出库数量时，请选择目标系统（J1、J2或J3）';
            if (!skipTableRefresh) {
                showAlert(errorMsg, 'error');
            }
            throw new Error(errorMsg);
        }
        formData.target_system = targetInput.value;
    }

    // 验证货品名称是否存在于数据库中
    if (formData.product_name && window.productOptions) {
        const validProducts = window.productOptions.map(p => p.product_name);
        if (!validProducts.includes(formData.product_name)) {
            const errorMsg = '货品名称不存在，请从下拉列表中选择有效的货品';
            if (!skipTableRefresh) {
                showAlert(errorMsg, 'error');
            }
            throw new Error(errorMsg);
        }

        // 同名多编号时，必须选择对应的编号，并确保编号与名称匹配
        const sameNameRows = window.productOptions.filter(p => p.product_name === formData.product_name);
        if (sameNameRows.length > 1) {
            const allowedCodes = sameNameRows.map(p => p.product_code).filter(Boolean);
            if (!formData.code_number) {
                const errorMsg = `该货品名称存在多个编号，请先选择对应的货品编号（例如：${allowedCodes.slice(0, 5).join(', ')}）`;
                if (!skipTableRefresh) {
                    showAlert(errorMsg, 'error');
                }
                throw new Error(errorMsg);
            }
            if (allowedCodes.length > 0 && !allowedCodes.includes(formData.code_number)) {
                const errorMsg = '货品编号与货品名称不匹配，请从下拉列表重新选择正确组合';
                if (!skipTableRefresh) {
                    showAlert(errorMsg, 'error');
                }
                throw new Error(errorMsg);
            }
        }
    }

    // 验证编号是否存在于数据库中
    if (formData.code_number && window.codeNumberOptions) {
        const validCodes = window.codeNumberOptions.map(c => c.code_number);
        if (!validCodes.includes(formData.code_number)) {
            const errorMsg = '货品编号不存在，请从下拉列表中选择有效的编号';
            if (!skipTableRefresh) {
                showAlert(errorMsg, 'error');
            }
            throw new Error(errorMsg);
        }
    }

    // 检查库存是否足够（RM0单价货品直接允许出货）
    if (formData.out_quantity > 0 && parseFloat(formData.price) > 0) {
        const stockCheck = await checkProductStock(formData.product_name, formData.out_quantity, formData.price);
        if (!stockCheck.sufficient) {
            let errorMsg = `库存不足！当前可用库存: ${stockCheck.availableStock}，请求出库: ${stockCheck.requested}`;
            if (['j1', 'j2', 'j3'].includes(currentStockType)) {
                errorMsg = '库存显示不足';
            }
            if (!skipTableRefresh) {
                showAlert(errorMsg, 'error');
            }
            throw new Error(errorMsg);
        }
    }

    try {
        const result = await apiCall('', {
            method: 'POST',
            body: JSON.stringify(formData)
        });

        if (result.success) {
            if (!skipTableRefresh) {
                showAlert('记录添加成功', 'success');
            }

            // 移除当前保存的行
            row.remove();

            // 添加新记录到 stockData 数组
            const newRecord = {
                id: result.data.id || Date.now(),
                date: formData.date,
                time: formData.time,
                code_number: formData.code_number,
                product_name: formData.product_name,
                in_quantity: formData.in_quantity,
                out_quantity: formData.out_quantity,
                target_system: formData.target_system,
                specification: formData.specification,
                price: formData.price,
                receiver: formData.receiver,
                remark: formData.remark,
                product_remark_checked: formData.product_remark_checked,
                remark_number: formData.remark_number,
                type: (result.data && result.data.type !== undefined) ? result.data.type : (formData.type || ''),
                // 立即从服务端响应获取 created_by（已解析为 nickname）和 created_at
                created_by: (result.data && result.data.created_by) ? result.data.created_by : '-',
                created_at: (result.data && result.data.created_at) ? result.data.created_at : new Date().toISOString()
            };

            stockData.push(newRecord); // 添加到数组末尾

            // 只在非批量保存模式下重新渲染表格
            if (!skipTableRefresh) {
                // 保存其他新增行
                const otherNewRows = Array.from(document.querySelectorAll('.new-row'));
                const savedRows = otherNewRows.map(r => ({
                    element: r.cloneNode(true),
                    data: extractRowData(r)
                }));

                // 重新渲染表格
                renderStockTable();

                // 恢复其他新增行
                setTimeout(() => {
                    const tbody = document.getElementById('stock-tbody');
                    savedRows.forEach(({ element, data }) => {
                        tbody.appendChild(element);

                        // 恢复行数据（特别是select元素的选中状态）
                        if (data) {
                            restoreRowData(element, data);
                        }
                    });
                    bindComboboxEvents();

                    // 更新批量保存按钮的可见性
                    updateBatchSaveButtonVisibility();
                }, 100);

                // 更新统计
                updateStats();
            }
        } else {
            showAlert('添加失败: ' + (result.message || '未知错误'), 'error');
        }
    } catch (error) {
        showAlert('保存时发生错误', 'error');
    }
}

// 取消新行
function cancelNewRow(buttonElement) {
    const row = buttonElement.closest('tr');
    row.remove();

    // 更新批量保存按钮的可见性
    updateBatchSaveButtonVisibility();
}

// 保存新记录
async function saveNewRecord() {
    // 确保表单中的下拉选项已加载
    if (window.codeNumberOptions && window.codeNumberOptions.length > 0) {
        const selectElement = document.getElementById('add-code-number');
        if (selectElement && selectElement.options.length <= 1) {
            selectElement.innerHTML = generateCodeNumberOptions();
        }
    }

    const formData = {
        date: document.getElementById('add-date').value,
        time: document.getElementById('add-time').value,
        product_name: document.getElementById('add-product-name').value,
        in_quantity: parseFloat(document.getElementById('add-in-qty').value) || 0,
        out_quantity: parseFloat(document.getElementById('add-out-qty').value) || 0,
        specification: document.getElementById('add-specification').value,
        price: document.getElementById('add-price').value,
        receiver: document.getElementById('add-receiver').value,
        applicant: document.getElementById('add-applicant').value,
        code_number: document.getElementById('add-code-number').value,
        remark: document.getElementById('add-remark').value,
        product_remark_checked: document.getElementById('add-product-remark').checked,
        remark_number: getFormRemarkNumber().trim().toUpperCase(),
        type: document.getElementById('add-type').value
    };

    // 验证必填字段
    const requiredFields = ['date', 'time', 'product_name', 'specification', 'receiver', 'applicant'];
    for (let field of requiredFields) {
        if (!formData[field]) {
            showAlert(`请填写${getFieldLabel(field)}`, 'error');
            return;
        }
    }

    // 验证数量：不能为负数
    if (formData.in_quantity < 0 || formData.out_quantity < 0) {
        showAlert('数量不能为负数', 'error');
        return;
    }
    // 验证数量：进货和出货不能同时为 0
    if (formData.in_quantity <= 0 && formData.out_quantity <= 0) {
        showAlert('进货或出货数量必须至少填入一项大于 0', 'error');
        return;
    }

    // 验证单价：不能为空且不能小于0（允许RM0）
    if (formData.price === '' || formData.price === null || formData.price === undefined || parseFloat(formData.price) < 0) {
        showAlert('单价不能为空且不能小于0', 'error');
        return;
    }

    if (formData.product_remark_checked && !formData.remark_number) {
        showAlert('货品备注已勾选时，请填写备注编号', 'error');
        return;
    }

    // 验证货品名称是否存在于数据库中
    if (formData.product_name && window.productOptions) {
        const validProducts = window.productOptions.map(p => p.product_name);
        if (!validProducts.includes(formData.product_name)) {
            showAlert('货品名称不存在，请从下拉列表中选择有效的货品', 'error');
            return;
        }
    }

    // 验证编号是否存在于数据库中
    if (formData.code_number && window.codeNumberOptions) {
        const validCodes = window.codeNumberOptions.map(c => c.code_number);
        if (!validCodes.includes(formData.code_number)) {
            showAlert('货品编号不存在，请从下拉列表中选择有效的编号', 'error');
            return;
        }
    }

    // 检查库存是否足够（RM0单价货品直接允许出货）
    if (formData.out_quantity > 0) {
        // 添加target验证
        const targetSystem = document.getElementById('add-target').value;
        if (!targetSystem) {
            showAlert('当有出库数量时，请选择目标系统（J1、J2或J3）', 'error');
            return;
        }
        formData.target_system = targetSystem;

        // 现有库存检查代码（RM0直接跳过库存检查）
        const stockCheck = parseFloat(formData.price) > 0 ? await checkProductStock(formData.product_name, formData.out_quantity, formData.price) : { sufficient: true, availableStock: 0, currentStock: 0 };
        if (!stockCheck.sufficient) {
            let errorMsg = `库存不足！当前可用库存: ${stockCheck.availableStock}，请求出库: ${formData.out_quantity}`;
            if (['j1', 'j2', 'j3'].includes(currentStockType)) {
                errorMsg = '库存显示不足';
            }
            showAlert(errorMsg, 'error');
            return;
        }
    }

    try {
        const result = await apiCall('', {
            method: 'POST',
            body: JSON.stringify(formData)
        });

        if (result.success) {
            showAlert('记录添加成功', 'success');
            toggleAddForm();

            // 添加新记录到 stockData 数组的开头并立即显示
            const newRecord = {
                id: result.data.id || Date.now(),
                date: formData.date,
                time: formData.time,
                code_number: formData.code_number,
                product_name: formData.product_name,
                in_quantity: formData.in_quantity,
                out_quantity: formData.out_quantity,
                target_system: formData.target_system,
                specification: formData.specification,
                price: formData.price,
                receiver: formData.receiver,
                applicant: formData.applicant,
                remark: formData.remark,
                product_remark_checked: formData.product_remark_checked,
                remark_number: formData.remark_number,
                type: (result.data && result.data.type !== undefined) ? result.data.type : (formData.type || ''),
                // 立即从服务端响应获取 created_by（已解析为 nickname）和 created_at
                created_by: (result.data && result.data.created_by) ? result.data.created_by : '-',
                created_at: (result.data && result.data.created_at) ? result.data.created_at : new Date().toISOString()
            };

            stockData.push(newRecord); // 添加到数组末尾
            renderStockTable();
            updateStats();
        } else {
            showAlert('添加失败: ' + (result.message || '未知错误'), 'error');
        }
    } catch (error) {
        showAlert('保存时发生错误', 'error');
    }
}

// 获取字段标签
function getFieldLabel(field) {
    const labels = {
        'date': '日期',
        'time': '时间',
        'product_name': '货品名称',
        'specification': '规格单位',
        'receiver': '收货人',
        'applicant': '申请人'
    };
    return labels[field] || field;
}

// 编辑记录
function editRecord(id) {
    // 如果已经在编辑中，直接返回
    if (editingRowIds.has(id)) {
        return;
    }

    editingRowIds.add(id);

    // 保存原始数据的深拷贝 - 初始化Map如果不存在
    if (!originalEditData) {
        originalEditData = new Map();
    }

    const record = stockData.find(r => r.id === id);
    if (record) {
        originalEditData.set(id, JSON.parse(JSON.stringify(record)));
        // 进入编辑时将价格切换为原始精度，避免未修改直接保存时被两位小数覆盖
        if (record.price_raw !== undefined && record.price_raw !== null && record.price_raw !== '') {
            record.price = record.price_raw;
        }
    }

    // 保存所有新创建的行
    const newRows = saveNewRows();

    // 添加过渡效果
    const tbody = document.getElementById('stock-tbody');
    tbody.style.transition = 'opacity 0.15s ease';
    tbody.style.opacity = '0.8';

    // 延迟重新渲染，让过渡效果更自然
    setTimeout(() => {
        // 重新渲染表格
        renderStockTable();

        // 恢复新创建的行
        restoreNewRows(newRows);

        // 恢复透明度
        setTimeout(() => {
            tbody.style.opacity = '1';
            tbody.style.transition = '';
        }, 30);
    }, 50); // 减少延迟时间
}

// 取消单个记录的编辑
function cancelEdit(id = null) {
    if (id !== null) {
        // 取消指定记录的编辑
        if (originalEditData && originalEditData.has(id)) {
            const recordIndex = stockData.findIndex(r => r.id === id);
            if (recordIndex !== -1) {
                stockData[recordIndex] = JSON.parse(JSON.stringify(originalEditData.get(id)));
            }
            originalEditData.delete(id);
        }
        editingRowIds.delete(id);
    } else {
        // 取消所有编辑
        if (originalEditData) {
            editingRowIds.forEach(editId => {
                if (originalEditData.has(editId)) {
                    const recordIndex = stockData.findIndex(r => r.id === editId);
                    if (recordIndex !== -1) {
                        stockData[recordIndex] = JSON.parse(JSON.stringify(originalEditData.get(editId)));
                    }
                }
            });
            originalEditData.clear();
        }
        editingRowIds.clear();
    }

    // 保存所有新创建的行
    const newRows = saveNewRows();

    // 添加过渡效果
    const tbody = document.getElementById('stock-tbody');
    tbody.style.transition = 'opacity 0.15s ease';
    tbody.style.opacity = '0.8';

    // 延迟重新渲染，让过渡效果更自然
    setTimeout(() => {
        renderStockTable();

        // 恢复新创建的行
        restoreNewRows(newRows);

        // 恢复透明度
        setTimeout(() => {
            tbody.style.opacity = '1';
            tbody.style.transition = '';
        }, 30);
    }, 50); // 减少延迟时间
}

// 更新字段
function updateField(id, field, value) {
    if ((field === 'in_quantity' || field === 'out_quantity') && value === '') {
        value = '0.00';
    }
    const record = stockData.find(r => r.id === id);
    if (record) {
        record[field] = value;

        // 进出货互斥检查
        if (field === 'in_quantity' || field === 'out_quantity') {
            const row = document.querySelector(`tr[data-record-id="${id}"]`);
            if (row) {
                // Find inputs using data-field
                const inInput = row.querySelector(`input[onchange*="updateField(${id}, 'in_quantity'"]`);
                const outInput = row.querySelector(`input[onchange*="handleEditOutQuantityChange(${id}"]`);
                enforceQuantityMutex(inInput, outInput);
            }
        }

        // 特殊处理出库数量变化
        if (field === 'out_quantity') {
            const outQty = parseFloat(value) || 0;
            const targetSelect = document.getElementById(`target-select-${id}`);
            if (targetSelect) {
                if (outQty > 0) {
                    targetSelect.disabled = false;
                    targetSelect.required = true;
                } else {
                    targetSelect.disabled = true;
                    if (typeof currentStockType !== 'undefined' && currentStockType && currentStockType !== 'central') {
                        targetSelect.value = currentStockType;
                        record.target_system = currentStockType;
                    } else {
                        targetSelect.value = '';
                        record.target_system = '';
                    }
                }
            }
        }

        // 特殊处理进货数量变化，动态锁定/解锁收货人字段
        if (field === 'in_quantity') {
            const inQty = parseFloat(value) || 0;
            // 查找编辑中的行，通过保存按钮的 onclick 属性
            const row = Array.from(document.querySelectorAll('tr')).find(r => {
                const saveBtn = r.querySelector(`[onclick*="saveRecord(${id})"]`);
                return saveBtn !== null;
            });

            if (row) {
                const receiverInput = row.querySelector('.combobox-input[data-type="receiver"]');
                const receiverContainer = receiverInput ? receiverInput.closest('.combobox-container') : null;
                const receiverArrow = receiverContainer ? receiverContainer.querySelector('.combobox-arrow') : null;

                if (receiverInput && receiverContainer) {
                    if (inQty > 0) {
                        // 进货时锁定收货人字段
                        receiverInput.disabled = true;
                        receiverInput.readOnly = true;
                        receiverInput.classList.add('disabled');
                        if (receiverArrow) {
                            receiverArrow.classList.add('disabled');
                        }
                    } else {
                        // 非进货时解锁收货人字段
                        receiverInput.disabled = false;
                        receiverInput.readOnly = false;
                        receiverInput.classList.remove('disabled');
                        if (receiverArrow) {
                            receiverArrow.classList.remove('disabled');
                        }
                    }
                }
            }
        }

        // 特殊处理备注相关字段 - 立即保存到数据库
        if (field === 'product_remark_checked' || field === 'remark_number') {
            saveFieldToDatabase(id, field, value);
        }

        // 移除自动重新渲染，改为只更新计算值
        if (field === 'in_quantity' || field === 'out_quantity' || field === 'price') {
            updateCalculatedValues(id);
        }
    }
}

// 保存单个字段到数据库
async function saveFieldToDatabase(id, field, value) {
    try {
        const result = await apiCall('', {
            method: 'PATCH',
            body: JSON.stringify({
                id: id,
                field: field,
                value: value
            })
        });

        if (!result.success) {
            console.error('保存字段到数据库失败:', result.message);
            showAlert(`保存${field}失败: ${result.message}`, 'error');
        }
    } catch (error) {
        console.error('保存字段到数据库时发生错误:', error);
        showAlert('保存字段时发生错误', 'error');
    }
}

// 更新计算值（不重新渲染整个表格）
function updateCalculatedValues(id) {
    const record = stockData.find(r => r.id === id);
    if (!record) return;

    // 计算总价
    const inQty = parseFloat(record.in_quantity) || 0;
    const outQty = parseFloat(record.out_quantity) || 0;
    const price = parseFloat(record.price_raw ?? record.price) || 0;
    const netQty = inQty - outQty;
    const total = netQty * price;

    // 更新页面上的总价显示
    const row = document.querySelector(`[data-record-id="${id}"]`)?.closest('tr');
    if (row) {
        const totalCell = row.querySelector('.calculated-cell');
        const currencyDisplay = totalCell?.querySelector('.currency-display');
        const currencyAmount = totalCell?.querySelector('.currency-amount');

        if (totalCell && currencyDisplay && currencyAmount) {
            // 更新数值
            currencyAmount.textContent = formatCurrency(Math.abs(total));

            // 添加或移除负数样式
            if (total < 0) {
                totalCell.classList.add('negative-value', 'negative-parentheses');
                currencyDisplay.classList.add('negative-value', 'negative-parentheses');
            } else {
                totalCell.classList.remove('negative-value', 'negative-parentheses');
                currencyDisplay.classList.remove('negative-value', 'negative-parentheses');
            }
        }

        // 更新出库数量的显示样式
        const outCell = row.querySelector('td:nth-child(5)');
        if (outCell) {
            const outSpan = outCell.querySelector('span');
            if (outSpan) {
                if (outQty > 0) {
                    outSpan.classList.add('negative-value');
                } else {
                    outSpan.classList.remove('negative-value');
                }
            }
        }
    }
}

// 切换新增表单显示状态
function toggleAddForm() {
    const form = document.getElementById('add-form');
    const isVisible = form.classList.contains('show');

    if (isVisible) {
        form.classList.remove('show');
    } else {
        form.classList.add('show');

        // 确保选项已加载
        setTimeout(() => {
            // 加载code number选项
            if (window.codeNumberOptions && window.codeNumberOptions.length > 0) {
                const selectElement = document.getElementById('add-code-number');
                if (selectElement) {
                    selectElement.innerHTML = generateCodeNumberOptions();
                }
            }

            // 更新target选项
            const targetSelect = document.getElementById('add-target');
            if (targetSelect) {
                const currentValue = targetSelect.value;
                const optionsHtml = generateTargetOptions(currentValue);
                const selectOptions = targetSelect.querySelectorAll('option:not([value=""])');
                selectOptions.forEach(option => option.remove());
                targetSelect.insertAdjacentHTML('beforeend', optionsHtml);
            }

            // 为表单中的下拉框绑定联动事件
            const addProductSelect = document.getElementById('add-product-name');
            const addCodeSelect = document.getElementById('add-code-number');

            if (addProductSelect) {
                addProductSelect.onchange = function () {
                    handleAddFormProductChange(this, addCodeSelect);
                };
            }

            if (addCodeSelect) {
                addCodeSelect.onchange = function () {
                    handleCodeNumberChange(this, addProductSelect);
                };
            }

            // 重置备注相关字段
            document.getElementById('add-product-remark').checked = false;
            document.getElementById('add-remark-number').value = '';
            document.getElementById('add-remark-number').disabled = true;

            // 自动填充收货单位（除了 central 之外）
            const receiverInput = document.getElementById('add-receiver-input');
            if (receiverInput) {
                if (currentStockType && currentStockType !== 'central') {
                    receiverInput.value = currentStockType.toUpperCase();
                } else {
                    receiverInput.value = '';
                }
            }
        }, 100);
    }
}

// 保存记录
async function saveRecord(id) {
    const record = stockData.find(r => r.id === id);
    if (!record) return;

    const remarkNumberValue = ((record.remark_number ?? '') + '').trim();
    record.remark_number = remarkNumberValue;

    if (record.product_remark_checked && !remarkNumberValue) {
        showAlert('货品备注已勾选时，请填写备注编号', 'error');
        return;
    }

    // 验证数量：进货和出货不能同时为 0
    const inQty = parseFloat(record.in_quantity) || 0;
    const outQty = parseFloat(record.out_quantity) || 0;
    if (inQty <= 0 && outQty <= 0) {
        showAlert('进货或出货数量必须至少填入一项大于 0', 'error');
        return;
    }

    // 验证单价：不能小于0（允许RM0）
    // 如果 record.price 为空/null/undefined，尝试从 DOM 价格下拉列表读取（当下拉激活时 record 可能没被更新）
    if (record.price === '' || record.price === null || record.price === undefined) {
        const priceSelect = document.getElementById(`price-select-${id}`);
        if (priceSelect && priceSelect.value !== '' && priceSelect.value !== 'manual') {
            // 从下拉列表读取并更新 record
            record.price = priceSelect.value;
        }
    }

    // 验证单价：不能为空且不能小于0（允许RM0）
    if (record.price === '' || record.price === null || record.price === undefined || parseFloat(record.price) < 0) {
        showAlert('单价不能为空且不能小于0', 'error');
        return;
    }

    // 验证货品名称是否存在于数据库中
    if (record.product_name && window.productOptions) {
        const validProducts = window.productOptions.map(p => p.product_name);
        if (!validProducts.includes(record.product_name)) {
            showAlert('货品名称不存在，请从下拉列表中选择有效的货品', 'error');
            return;
        }
    }

    // 验证编号是否存在于数据库中
    if (record.code_number && window.codeNumberOptions) {
        const validCodes = window.codeNumberOptions.map(c => c.code_number);
        if (!validCodes.includes(record.code_number)) {
            showAlert('货品编号不存在，请从下拉列表中选择有效的编号', 'error');
            return;
        }
    }

    // 检查库存是否足够（RM0单价货品直接允许出货）
    if (parseFloat(record.out_quantity) > 0 && parseFloat(record.price) > 0) {
        const stockCheck = await checkProductStock(record.product_name, record.out_quantity, record.price);

        // 尝试从原始编辑数据中恢复原有出库数量
        let oldOutQty = 0;
        if (originalEditData && originalEditData.has(id)) {
            const oldData = originalEditData.get(id);
            // 只有当编辑前后货品名称和价格没变时，原有出库数量才能归还可用库存中
            if (oldData.product_name === record.product_name && parseFloat(oldData.price || 0) === parseFloat(record.price || 0)) {
                oldOutQty = parseFloat(oldData.out_quantity) || 0;
            }
        }

        // 实际可用库存等同于现有库存加上原本该记录出库的数量
        const actualAvailable = stockCheck.availableStock + oldOutQty;
        if (parseFloat(record.out_quantity) > actualAvailable) {
            let errorMsg = `库存不足！当前可用库存 (修改前): ${actualAvailable}，请求修改为出库: ${record.out_quantity}`;
            if (['j1', 'j2', 'j3'].includes(currentStockType)) {
                errorMsg = '库存显示不足';
            }
            showAlert(errorMsg, 'error');
            return;
        }
    }

    try {
        const result = await apiCall('', {
            method: 'PUT',
            body: JSON.stringify(record)
        });

        if (result.success) {
            showAlert('记录更新成功', 'success');

            // 保存其他正在编辑的行的输入值，并排除当前保存的行
            const editingValues = saveEditingRowsInputValues();
            editingValues.delete(id);

            // 从编辑状态集合及原始数据缓存中移除当前行
            editingRowIds.delete(id);
            if (originalEditData) {
                originalEditData.delete(id);
            }

            // 使用后端返回的数据更新本地缓存，若无返回则保留现有数据
            const recordIndex = stockData.findIndex(r => r.id === id);
            if (recordIndex !== -1) {
                const currentRecord = stockData[recordIndex] || {};
                const serverRecord = (result.data && typeof result.data === 'object') ? result.data : {};
                stockData[recordIndex] = {
                    ...currentRecord,
                    ...serverRecord
                };
            }

            // 保存所有新创建的行
            const newRows = saveNewRows();

            // 重新渲染表格并保持统计信息最新
            renderStockTable();
            updateStats();

            // 恢复新增行和其他正在编辑的输入值
            restoreNewRows(newRows);
            restoreEditingRowsInputValues(editingValues);
        } else {
            showAlert('更新失败: ' + (result.message || '未知错误'), 'error');
        }
    } catch (error) {
        showAlert('保存时发生错误', 'error');
    }
}

// 批准记录
async function approveRecord(id) {
    if (!confirm('确定要批准此记录吗？')) return;

    try {
        const result = await apiCall('?action=approve', {
            method: 'PUT',
            body: JSON.stringify({ id: id })
        });

        if (result.success) {
            showAlert('记录批准成功', 'success');

            // 保存其他正在编辑的行的输入值
            const editingValues = saveEditingRowsInputValues();

            // 保存所有新创建的行
            const newRows = saveNewRows();

            // 重新搜索数据（保持搜索状态）但保留新行
            searchData().then(() => {
                // 恢复新创建的行
                restoreNewRows(newRows);
                // 恢复其他正在编辑的行的输入值
                restoreEditingRowsInputValues(editingValues);
            });
        } else {
            showAlert('批准失败: ' + (result.message || '未知错误'), 'error');
        }
    } catch (error) {
        showAlert('批准时发生错误', 'error');
    }
}

// 删除记录
async function deleteRecord(id) {
    if (!confirm('确定要删除此记录吗？记录将移至回收站。')) return;

    try {
        const result = await apiCall(`?id=${id}`, {
            method: 'DELETE'
        });

        if (result.success) {
            lastDeletedIds = [id];
            showUndoBar(1);

            const editingValues = saveEditingRowsInputValues();
            const newRows = saveNewRows();

            searchData().then(() => {
                restoreNewRows(newRows);
                restoreEditingRowsInputValues(editingValues);
            });
        } else {
            showAlert('删除失败: ' + (result.message || '未知错误'), 'error');
        }
    } catch (error) {
        showAlert('删除时发生错误', 'error');
    }
}

// 撤销删除功能
async function undoDelete() {
    if (!lastDeletedIds || lastDeletedIds.length === 0) return;

    try {
        const response = await fetch('stockeditapi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'restore',
                ids: lastDeletedIds,
                system: currentStockType
            })
        });
        const result = await response.json();

        if (result.success) {
            hideUndoBar();
            lastDeletedIds = [];
            showAlert('已撤销成功', 'success');

            const editingValues = saveEditingRowsInputValues();
            const newRows = saveNewRows();
            searchData().then(() => {
                restoreNewRows(newRows);
                restoreEditingRowsInputValues(editingValues);
            });
        } else {
            showAlert('撤销失败: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Undo failed:', error);
        showAlert('撤销时发生错误', 'error');
    }
}

// 显示撤销条
function showUndoBar(count) {
    let undoBar = document.getElementById('undoBar');
    if (!undoBar) {
        undoBar = document.createElement('div');
        undoBar.id = 'undoBar';
        document.body.appendChild(undoBar);

        // 注入样式
        const style = document.createElement('style');
        style.innerHTML = `
            #undoBar {
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%) translateY(100px);
                background: #333;
                color: white;
                padding: 12px 24px;
                border-radius: 8px;
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 15px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                transition: transform 0.3s ease;
                font-size: 14px;
            }
            #undoBar.show {
                transform: translateX(-50%) translateY(0);
            }
            #undoBar button {
                background: #4f46e5;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
            #undoBar button:hover {
                background: #4338ca;
            }
            #undoBar .shortcut-tip {
                color: #aaa;
                font-size: 11px;
                margin-left: 5px;
            }
        `;
        document.head.appendChild(style);
    }

    undoBar.innerHTML = `
        <span>已删除 ${count} 条记录</span>
        <button onclick="undoDelete()">撤销 <span class="shortcut-tip">(Ctrl+Shift+Z)</span></button>
    `;

    undoBar.classList.add('show');

    // 10秒后自动消失
    if (undoTimer) clearTimeout(undoTimer);
    undoTimer = setTimeout(hideUndoBar, 10000);
}

function hideUndoBar() {
    const undoBar = document.getElementById('undoBar');
    if (undoBar) {
        undoBar.classList.remove('show');
    }
}

// 刷新数据
function refreshData() {
    loadStockData();
}

// 刷新数据但保留新增行
function refreshDataKeepNewRows() {
    // 保存所有新增行
    const newRows = Array.from(document.querySelectorAll('.new-row')).map(row => ({
        element: row.cloneNode(true),
        parent: row.parentNode
    }));

    // 重新搜索数据（保持搜索状态）
    searchData().then(() => {
        // 恢复新增行到表格底部
        const tbody = document.getElementById('stock-tbody');
        newRows.forEach(({ element }) => {
            tbody.appendChild(element);
        });

        // 重新绑定事件
        setTimeout(bindComboboxEvents, 0);
    });
}

// 导出数据
function exportData() {
    // 设置默认日期为今天
    const today = new Date();

    // 格式化为 DD/MM/YYYY
    const formatDateToDDMMYYYY = (date) => {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    };

    document.getElementById('export-start-date').value = formatDateToDDMMYYYY(today);
    document.getElementById('export-end-date').value = formatDateToDDMMYYYY(today);

    // 设置发票日期默认为今天
    document.getElementById('export-invoice-date').value = formatDateToDDMMYYYY(today);

    // 清空发票号码后缀输入框
    document.getElementById('export-invoice-suffix').value = '';

    const exportSystemSelect = document.getElementById('export-system');
    const invoiceDateGroup = document.getElementById('export-invoice-date-group');
    const invoiceSuffixGroup = document.getElementById('export-invoice-suffix-group');
    const systemGroup = document.getElementById('export-system-group');
    const centralOnlyGroups = document.querySelectorAll('.export-central-only');

    if (currentStockType === 'central') {
        centralOnlyGroups.forEach(group => {
            if (group) {
                group.style.display = '';
            }
        });
        if (exportSystemSelect) {
            exportSystemSelect.disabled = false;
            exportSystemSelect.value = '';
        }
        if (invoiceDateGroup) {
            const invoiceInput = invoiceDateGroup.querySelector('#export-invoice-date');
            if (invoiceInput) {
                invoiceInput.value = formatDateToDDMMYYYY(today);
            }
        }
        if (invoiceSuffixGroup) {
            const suffixInput = invoiceSuffixGroup.querySelector('#export-invoice-suffix');
            if (suffixInput) {
                suffixInput.value = '';
            }
        }
    } else {
        centralOnlyGroups.forEach(group => {
            if (group) {
                group.style.display = 'none';
            }
        });
        if (exportSystemSelect) {
            exportSystemSelect.value = currentStockType;
            exportSystemSelect.disabled = true;
        }
        if (invoiceDateGroup) {
            const invoiceInput = invoiceDateGroup.querySelector('#export-invoice-date');
            if (invoiceInput) {
                invoiceInput.value = document.getElementById('export-end-date').value || formatDateToDDMMYYYY(today);
            }
        }
        if (invoiceSuffixGroup) {
            const suffixInput = invoiceSuffixGroup.querySelector('#export-invoice-suffix');
            if (suffixInput) {
                suffixInput.value = '';
            }
        }
        if (systemGroup) {
            const systemSelect = systemGroup.querySelector('#export-system');
            if (systemSelect) {
                systemSelect.value = currentStockType;
            }
        }
    }

    // 显示导出弹窗
    document.getElementById('export-modal').style.display = 'block';
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

    // 自动关闭：错误消息保留8秒，成功消息保留2秒
    const duration = (type === 'error' || type === 'warning') ? 8000 : 2000;
    setTimeout(() => {
        closeToast(toastId);
    }, duration);
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

// 添加快速选择下拉菜单的关闭逻辑
document.addEventListener('click', function (e) {
    // 关闭快速选择下拉菜单
    if (!e.target.closest('.dropdown')) {
        document.getElementById('quick-select-dropdown').classList.remove('show');
    }
});



// 创建 Combobox 组件
function createCombobox(type, value = '', recordId = null, isNewRow = false, disabled = false) {
    let options, placeholder, fieldName, displayField;

    if (type === 'code') {
        options = window.codeNumberOptions;
        placeholder = '输入或选择编号...';
        fieldName = 'code_number';
        displayField = 'code_number';
    } else if (type === 'product') {
        options = window.productOptions;
        placeholder = '输入或选择货品...';
        fieldName = 'product_name';
        displayField = 'product_name';
    } else if (type === 'receiver') {
        options = receiverOptions;
        placeholder = '输入或选择收货人...';
        fieldName = 'receiver';
        displayField = 'receiver';
    } else {
        // 默认处理
        options = window.productOptions;
        placeholder = '输入或选择货品...';
        fieldName = 'product_name';
        displayField = 'product_name';
    }

    let containerId;
    if (isNewRow === true) {
        containerId = `new-${fieldName}`;
    } else if (typeof isNewRow === 'string') {
        containerId = `${isNewRow}-${fieldName}`;
    } else {
        containerId = `combo-${fieldName}-${recordId}`;
    }
    const inputId = `${containerId}-input`;
    const dropdownId = `${containerId}-dropdown`;

    return `
                <div class="combobox-container" id="${containerId}">
                    <input 
                        type="text" 
                        class="combobox-input ${disabled ? 'disabled' : ''}" 
                        id="${inputId}"
                        value="${value || ''}" 
                        placeholder="${placeholder}"
                        autocomplete="off"
                        ${disabled ? 'readonly disabled' : ''}
                        ${recordId ? `data-record-id="${recordId}"` : ''}
                        data-field="${fieldName}"
                        data-type="${type}"
                    />
                    <i class="fas fa-chevron-down combobox-arrow ${disabled ? 'disabled' : ''}"></i>
                    <div class="combobox-dropdown" id="${dropdownId}">
                        ${generateComboboxOptions(options, displayField || '')}
                    </div>
                </div>
            `;
}

// 安全转义（用于 HTML 文本 / 属性）
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function escapeAttr(value) {
    // 属性里同样使用 HTML 转义即可
    return escapeHtml(value);
}

// 生成下拉选项
function generateComboboxOptions(options, displayField) {
    if (!options || options.length === 0) {
        return '<div class="no-results">暂无选项</div>';
    }

    // 如果是收货人选项，直接使用字符串数组
    if (Array.isArray(options) && typeof options[0] === 'string') {
        return options.map(option =>
            `<div class="combobox-option" data-value="${escapeAttr(option)}">
                        ${escapeHtml(option)}
                    </div>`
        ).join('');
    }

    // 其他情况，使用对象数组
    return options.map(option => {
        const rawValue = option ? (option[displayField] ?? '') : '';
        let label = rawValue;

        // 货品名称：优先显示 supplier（NAME (SUPPLIER)），没有 supplier 再回退显示编号（NAME (CODE)）
        if (option && option.product_name && displayField === 'product_name') {
            const supplier = option.supplier ? String(option.supplier).trim() : '';
            if (supplier) {
                label = `${option.product_name} (${supplier})`;
            } else if (option.product_code) {
                label = `${option.product_name} (${option.product_code})`;
            } else {
                label = `${option.product_name}`;
            }
        }

        // 如果是 code_number 且有 product_name，显示：CODE (NAME) 便于识别
        if (option && option.code_number && option.product_name && displayField === 'code_number') {
            label = `${option.code_number} (${option.product_name})`;
        }

        const attrs = [
            `data-value="${escapeAttr(rawValue)}"`,
            option && option.product_code ? `data-product-code="${escapeAttr(option.product_code)}"` : '',
            option && option.supplier ? `data-supplier="${escapeAttr(option.supplier)}"` : '',
            option && option.code_number ? `data-code-number="${escapeAttr(option.code_number)}"` : ''
        ].filter(Boolean).join(' ');

        return `<div class="combobox-option" ${attrs}>
                    ${escapeHtml(label)}
                </div>`;
    }).join('');
}

// 计算下拉列表位置
function calculateDropdownPosition(inputElement, dropdownElement) {
    const inputRect = inputElement.getBoundingClientRect();
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    // 获取实际的下拉列表高度
    // 先让下拉列表显示以获取真实高度，然后立即隐藏
    const wasVisible = dropdownElement.style.display === 'block';
    if (!wasVisible) {
        dropdownElement.style.display = 'block';
        dropdownElement.style.visibility = 'hidden';
    }

    // 计算选项数量和实际高度
    const options = dropdownElement.querySelectorAll('.combobox-option, .no-results');
    const optionCount = options.length;

    // 获取实际内容高度
    let dropdownHeight;
    if (dropdownElement.scrollHeight > 0 && optionCount > 0) {
        // 计算所有选项的实际总高度
        let totalHeight = 0;
        options.forEach(option => {
            totalHeight += option.offsetHeight;
        });

        // 如果计算出了实际高度，使用它；否则使用scrollHeight
        if (totalHeight > 0) {
            // 添加4px用于边框（2px上 + 2px下）
            dropdownHeight = Math.min(250, totalHeight + 4);
        } else {
            // 使用scrollHeight，添加少量边距
            dropdownHeight = Math.min(250, dropdownElement.scrollHeight + 4);
        }
    } else {
        // 估算下拉列表高度（假设每项37px = 10px padding上 + 10px padding下 + 14px字体 + 1.4行高 + 1px边框）
        dropdownHeight = Math.min(250, 37 * Math.min(6, Math.max(1, optionCount)) + 4);
    }

    // 恢复原始状态
    if (!wasVisible) {
        dropdownElement.style.display = '';
        dropdownElement.style.visibility = '';
    }

    // 获取表格容器信息
    const tableContainer = document.querySelector('.table-scroll-container');
    const containerRect = tableContainer ? tableContainer.getBoundingClientRect() : null;

    let top = inputRect.bottom;
    let left = inputRect.left;

    // 计算可用空间
    const spaceBelow = viewportHeight - inputRect.bottom;
    const spaceAbove = inputRect.top;

    // 对于最后几行，优先显示在上方
    const isLastFewRows = inputRect.bottom > viewportHeight * 0.7;

    // 检查是否会超出视口底部，或者对于最后几行优先显示在上方
    if (top + dropdownHeight > viewportHeight || (isLastFewRows && spaceAbove > dropdownHeight)) {
        // 显示在输入框上方
        top = inputRect.top - dropdownHeight;

        // 如果上方空间也不够，则调整高度
        if (top < 0) {
            top = 10;
            dropdownHeight = Math.min(dropdownHeight, inputRect.top - 20);
        }
    }

    // 计算下拉列表宽度：根据内容宽度动态调整，而不是固定为输入框宽度
    // 创建一个临时元素来测量文本宽度
    const measureElement = document.createElement('span');
    measureElement.style.position = 'absolute';
    measureElement.style.visibility = 'hidden';
    measureElement.style.whiteSpace = 'nowrap';
    measureElement.style.fontSize = '14px'; // 与 .combobox-option 的字体大小一致
    measureElement.style.padding = '0 12px'; // 与 .combobox-option 的 padding 一致
    document.body.appendChild(measureElement);

    // 计算所有选项中最长文本的宽度
    let maxTextWidth = 0;
    options.forEach(option => {
        const text = option.textContent || option.innerText || '';
        if (text.trim()) {
            measureElement.textContent = text;
            const textWidth = measureElement.offsetWidth;
            if (textWidth > maxTextWidth) {
                maxTextWidth = textWidth;
            }
        }
    });

    // 清理临时元素
    document.body.removeChild(measureElement);

    // 下拉列表宽度 = 最大文本宽度 + 一些边距（确保有足够空间）
    // 最小宽度为200px，最大宽度为400px，避免下拉列表过宽
    const contentWidth = maxTextWidth + 24; // 添加24px的额外边距
    const dropdownWidth = Math.min(400, Math.max(200, contentWidth));

    // 确保不会超出视口左右边界
    if (left + dropdownWidth > viewportWidth) {
        left = viewportWidth - dropdownWidth - 10;
    }
    if (left < 10) {
        left = 10;
    }

    // 如果表格容器存在，确保下拉列表在容器内可见
    if (containerRect) {
        const containerTop = containerRect.top;
        const containerBottom = containerRect.bottom;

        // 如果下拉列表超出容器顶部，调整位置
        if (top < containerTop) {
            top = containerTop + 5;
        }

        // 如果下拉列表超出容器底部，调整位置
        if (top + dropdownHeight > containerBottom) {
            top = containerBottom - dropdownHeight - 5;
        }
    }

    return { top, left, width: dropdownWidth, height: dropdownHeight };
}

// 显示下拉列表
function showComboboxDropdown(input) {
    // 如果输入框被禁用，不显示下拉列表
    if (input.disabled || input.classList.contains('disabled') || input.hasAttribute('readonly')) {
        return;
    }

    // 隐藏其他所有下拉列表
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

        // 重置高亮
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

// 过滤下拉选项 - 修复版本
function filterComboboxOptions(input) {
    // 如果输入框被禁用，不执行过滤
    if (input.disabled || input.classList.contains('disabled') || input.hasAttribute('readonly')) {
        return;
    }

    // 使用防抖来提高性能
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
            displayField = 'code_number';
            if (!options) return;
            filteredOptions = options.filter(option =>
                option[displayField].toLowerCase().includes(searchTerm)
            );
        } else if (type === 'product') {
            options = window.productOptions;
            displayField = 'product_name';
            if (!options) return;
            filteredOptions = options.filter(option => {
                const name = String(option?.product_name ?? '').toLowerCase();
                const code = String(option?.product_code ?? '').toLowerCase();
                const supplier = String(option?.supplier ?? '').toLowerCase();
                return name.includes(searchTerm) || code.includes(searchTerm) || supplier.includes(searchTerm);
            });
        } else if (type === 'receiver') {
            options = receiverOptions;
            if (!options) return;
            filteredOptions = options.filter(option =>
                option.toLowerCase().includes(searchTerm)
            );
        } else {
            return;
        }

        if (filteredOptions.length === 0) {
            dropdown.innerHTML = '<div class="no-results">未找到匹配项</div>';
        } else {
            dropdown.innerHTML = generateComboboxOptions(filteredOptions, displayField || '');

            // 重新绑定点击事件
            dropdown.querySelectorAll('.combobox-option').forEach(option => {
                option.addEventListener('click', () => selectComboboxOption(option, input));
            });
        }

        // 使用 requestAnimationFrame 确保 DOM 更新后再计算位置
        requestAnimationFrame(() => {
            showComboboxDropdown(input);
        });

        // 如果是编辑模式，只更新数据，不重新渲染表格
        const recordId = input.dataset.recordId;
        const fieldName = input.dataset.field;
        if (recordId && fieldName) {
            const record = stockData.find(r => r.id === parseInt(recordId));
            if (record) {
                record[fieldName] = input.value;
                // 不调用 updateField 避免重新渲染
            }
        }
    }, 100); // 100ms 防抖延迟
}

// 选择下拉选项
async function selectComboboxOption(optionElement, input) {
    // 如果输入框被禁用，不允许选择
    if (input.disabled || input.classList.contains('disabled') || input.hasAttribute('readonly')) {
        return;
    }

    const value = optionElement.dataset.value;
    const type = input.dataset.type;
    const recordId = input.dataset.recordId;
    const container = input.closest('tr') || input.closest('.form-container') || document;

    // 选择货品编号或货品名称时，只清空单价/总价/收货人/目标，出货数量保留
    if (type === 'code' || type === 'product') {
        const isEditMode = recordId && editingRowIds.has(parseInt(recordId));

        if (!isEditMode) {
            const priceInput = container.querySelector('input[id*="-price"], input[data-field="price"]');
            if (priceInput) {
                priceInput.value = '';
            }

            const totalInput = container.querySelector('input[id*="-total"], input[data-field="total_value"]');
            if (totalInput) {
                totalInput.value = '';
            }

            const receiverInput = container.querySelector('input[id*="-receiver"], input[data-field="receiver"]');
            if (receiverInput) {
                if (currentStockType && currentStockType !== 'central') {
                    receiverInput.value = currentStockType.toUpperCase();
                } else {
                    receiverInput.value = '';
                }
            }

            const targetSelect = container.querySelector('select[id*="-target"], select[data-field="target_system"]');
            if (targetSelect) {
                if (typeof currentStockType !== 'undefined' && currentStockType && currentStockType !== 'central') {
                    targetSelect.value = currentStockType;
                } else {
                    targetSelect.value = '';
                }
                targetSelect.disabled = true;
                targetSelect.required = false;
            }

            updatePriceOptions(container, '');

            if (recordId) {
                updateField(parseInt(recordId), 'price', '');
                updateField(parseInt(recordId), 'receiver', '');
                updateField(parseInt(recordId), 'target_system', '');
                // 出货数量 out_quantity 不清空，用户已填写的数量保留
            }
        }
    }

    // 标记正在进行选择操作
    input._isSelecting = true;

    input.value = value;
    hideAllComboboxDropdowns();

    // 清除选择标记
    setTimeout(() => {
        input._isSelecting = false;
    }, 200);

    // 触发联动更新
    if (type === 'code') {
        const result = await getProductByCode(value);
        if (result) {
            const { product_name, specification, supplier, category } = result;
            const containerId = input.closest('.combobox-container').id;
            const isNewRow = containerId.includes('new-');

            let relatedInputId;
            if (isNewRow) {
                // 对于新增行，提取行ID (new-TIMESTAMP-COUNTER)
                const idParts = containerId.split('-');
                if (idParts.length >= 3) {
                    relatedInputId = `${idParts[0]}-${idParts[1]}-${idParts[2]}-product_name-input`;
                } else {
                    relatedInputId = 'new-product_name-input'; // 兼容旧格式
                }
            } else {
                relatedInputId = `combo-product_name-${recordId}-input`;
            }

            const normalizedCategoryCode = normalizeCategoryForType(category);
            const relatedInput = document.getElementById(relatedInputId);
            if (relatedInput) {
                relatedInput.value = product_name;
                if (recordId) {
                    updateField(parseInt(recordId), 'product_name', product_name);
                    if (specification) {
                        updateField(parseInt(recordId), 'specification', specification);
                    }
                    if (normalizedCategoryCode) {
                        updateField(parseInt(recordId), 'type', normalizedCategoryCode);
                    }
                }
            }

            // 自动填充规格
            if (specification) {
                const row = input.closest('tr');
                const specificationSelect = row ? row.querySelector('select[id$="-specification"], select[onchange*="specification"]') : null;
                if (specificationSelect) {
                    specificationSelect.value = specification;
                    specificationSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 自动填充类型（规范化以匹配下拉选项）
            const normalizedCategory = normalizedCategoryCode;
            if (normalizedCategory) {
                const row = input.closest('tr');
                const typeSelect = row ? row.querySelector('select[id$="-type"], select[onchange*="type"]') : null;
                if (typeSelect) {
                    typeSelect.value = normalizedCategory;
                    typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 保存supplier到当前行，并在有进货数量时自动填充
            const row = input.closest('tr');
            if (row && supplier) {
                row.dataset.supplier = supplier;
                updateSupplierIfNeeded(row, recordId ? parseInt(recordId) : null);
            }

            // 检查是否需要自动勾选货品备注（仅限中央页面）
            if (currentStockType === 'central') {
                const shouldCheckRemark = autoRemarkProducts.includes(product_name);

                // 如果是新增行
                if (isNewRow && row) {
                    const remarkCheckbox = row.querySelector('.remark-checkbox');
                    if (remarkCheckbox && !remarkCheckbox.disabled) {
                        remarkCheckbox.checked = shouldCheckRemark;
                        // 触发toggleNewRowRemarkNumber以更新备注编号输入框状态
                        const idParts = containerId.split('-');
                        if (idParts.length >= 3 && typeof toggleNewRowRemarkNumber === 'function') {
                            toggleNewRowRemarkNumber(`${idParts[0]}-${idParts[1]}-${idParts[2]}`);
                        }
                    }
                }
                // 如果是编辑模式的行
                else if (recordId) {
                    const isEditMode = editingRowIds.has(parseInt(recordId));
                    if (isEditMode && row) {
                        const remarkCheckbox = row.querySelector('.remark-checkbox');
                        if (remarkCheckbox && !remarkCheckbox.disabled) {
                            remarkCheckbox.checked = shouldCheckRemark;
                            // 触发updateRemarkCheck以更新备注编号输入框状态和数据库
                            if (typeof updateRemarkCheck === 'function') {
                                updateRemarkCheck(parseInt(recordId), shouldCheckRemark);
                            }
                        }
                    }
                }
            }

            // 更新单价选项
            updatePriceOptions(container, product_name);
        }
    } else if (type === 'product') {
        // 优先使用选项中携带的 product_code（同名多编号时必须用这个，避免只靠名字反查）
        const selectedProductCode = optionElement.dataset.productCode || '';

        let resolved = null;
        if (selectedProductCode) {
            // 通过 code 精准获取规格/供应商/类型
            const byCode = await getProductByCode(selectedProductCode);
            resolved = {
                product_code: selectedProductCode,
                specification: byCode?.specification ?? '',
                supplier: byCode?.supplier ?? '',
                category: byCode?.category ?? ''
            };
            // 记录一下，便于后续调试/联动
            input.dataset.selectedProductCode = selectedProductCode;
        } else {
            // 回退：仍按名字查（可能会命中最小编号），但正常从下拉选择时应不会走到这里
            const byName = await getCodeByProduct(value);
            if (byName) {
                resolved = {
                    product_code: byName.product_code,
                    specification: byName.specification,
                    supplier: byName.supplier,
                    category: byName.category
                };
                input.dataset.selectedProductCode = byName.product_code || '';
            }
        }

        if (resolved) {
            const { product_code, specification, supplier, category } = resolved;
            const containerId = input.closest('.combobox-container').id;
            const isNewRow = containerId.includes('new-');

            let relatedInputId;
            if (isNewRow) {
                // 对于新增行，提取行ID (new-TIMESTAMP-COUNTER)
                const idParts = containerId.split('-');
                if (idParts.length >= 3) {
                    relatedInputId = `${idParts[0]}-${idParts[1]}-${idParts[2]}-code_number-input`;
                } else {
                    relatedInputId = 'new-code_number-input'; // 兼容旧格式
                }
            } else {
                relatedInputId = `combo-code_number-${recordId}-input`;
            }

            const normalizedCategoryProduct = normalizeCategoryForType(category);
            const relatedInput = document.getElementById(relatedInputId);
            if (relatedInput) {
                relatedInput.value = product_code;
                if (recordId) {
                    updateField(parseInt(recordId), 'code_number', product_code);
                    if (specification) {
                        updateField(parseInt(recordId), 'specification', specification);
                    }
                    if (normalizedCategoryProduct) {
                        updateField(parseInt(recordId), 'type', normalizedCategoryProduct);
                    }
                }
            }

            // 自动填充规格
            if (specification) {
                const row = input.closest('tr');
                const specificationSelect = row ? row.querySelector('select[id$="-specification"], select[onchange*="specification"]') : null;
                if (specificationSelect) {
                    specificationSelect.value = specification;
                    specificationSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 自动填充类型（规范化以匹配下拉选项）
            const normalizedCategory = normalizedCategoryProduct;
            if (normalizedCategory) {
                const row = input.closest('tr');
                const typeSelect = row ? row.querySelector('select[id$="-type"], select[onchange*="type"]') : null;
                if (typeSelect) {
                    typeSelect.value = normalizedCategory;
                    typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // 保存supplier到当前行，并在有进货数量时自动填充
            const row = input.closest('tr');
            if (row && supplier) {
                row.dataset.supplier = supplier;
                updateSupplierIfNeeded(row, recordId ? parseInt(recordId) : null);
            }

            // 检查是否需要自动勾选货品备注（仅限中央页面）
            if (currentStockType === 'central') {
                const shouldCheckRemark = autoRemarkProducts.includes(value);

                // 如果是新增行
                if (isNewRow && row) {
                    const remarkCheckbox = row.querySelector('.remark-checkbox');
                    if (remarkCheckbox && !remarkCheckbox.disabled) {
                        remarkCheckbox.checked = shouldCheckRemark;
                        // 触发toggleNewRowRemarkNumber以更新备注编号输入框状态
                        const idParts = containerId.split('-');
                        if (idParts.length >= 3 && typeof toggleNewRowRemarkNumber === 'function') {
                            toggleNewRowRemarkNumber(`${idParts[0]}-${idParts[1]}-${idParts[2]}`);
                        }
                    }
                }
                // 如果是编辑模式的行
                else if (recordId) {
                    const isEditMode = editingRowIds.has(parseInt(recordId));
                    if (isEditMode && row) {
                        const remarkCheckbox = row.querySelector('.remark-checkbox');
                        if (remarkCheckbox && !remarkCheckbox.disabled) {
                            remarkCheckbox.checked = shouldCheckRemark;
                            // 触发updateRemarkCheck以更新备注编号输入框状态和数据库
                            if (typeof updateRemarkCheck === 'function') {
                                updateRemarkCheck(parseInt(recordId), shouldCheckRemark);
                            }
                        }
                    }
                }
            }

            // 更新单价选项
            if (recordId) {
                const editRecordId = parseInt(recordId, 10);
                if (!Number.isNaN(editRecordId)) {
                    await refreshEditPriceSelect(editRecordId, value);
                } else {
                    updatePriceOptions(container, value);
                }
            } else {
                updatePriceOptions(container, value);
            }
        }
    } else if (type === 'receiver') {
        // 收货人类型不需要特殊处理，直接更新字段
        if (recordId) {
            updateField(parseInt(recordId), 'receiver', value);
        }
    }

    // 如果是编辑模式，更新字段（避免重复更新收货人）
    if (recordId && type !== 'receiver') {
        updateField(parseInt(recordId), input.dataset.field, value);
    }

    // 如果是编辑模式，确保数据已更新
    if (recordId) {
        const record = stockData.find(r => r.id === parseInt(recordId));
        if (record) {
            record[input.dataset.field] = value;
        }
    }
}

// 验证输入值是否在允许的选项中
function validateComboboxInput(input) {
    const type = input.dataset.type;
    const value = input.value.trim();

    if (!value) return true; // 空值允许

    if (type === 'code' && window.codeNumberOptions) {
        const validCodes = window.codeNumberOptions.map(c => c.code_number);
        return validCodes.includes(value);
    } else if (type === 'product') {
        // 对于货品名称，允许输入任何值（包括包含符号的自定义货品名称）
        return true;
    } else if (type === 'receiver' && receiverOptions) {
        return receiverOptions.includes(value);
    }

    return true;
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
                if (options.length > 0) {
                    options[0].classList.add('highlighted');
                }
            } else {
                highlighted.classList.remove('highlighted');
                const next = highlighted.nextElementSibling;
                if (next && next.classList.contains('combobox-option')) {
                    next.classList.add('highlighted');
                } else if (options.length > 0) {
                    options[0].classList.add('highlighted');
                }
            }
            break;

        case 'ArrowUp':
            event.preventDefault();
            if (!highlighted) {
                if (options.length > 0) {
                    options[options.length - 1].classList.add('highlighted');
                }
            } else {
                highlighted.classList.remove('highlighted');
                const prev = highlighted.previousElementSibling;
                if (prev && prev.classList.contains('combobox-option')) {
                    prev.classList.add('highlighted');
                } else if (options.length > 0) {
                    options[options.length - 1].classList.add('highlighted');
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

// 修改渲染后的事件绑定
function bindComboboxEvents() {
    // 为所有 combobox 输入框绑定事件
    document.querySelectorAll('.combobox-input').forEach(input => {
        // 只有在没有绑定过的情况下才绑定事件
        if (!input._eventsbound) {
            // 创建事件处理器
            const focusHandler = () => showComboboxDropdown(input);
            const inputHandler = () => filterComboboxOptions(input);
            const keydownHandler = (e) => {
                // 允许输入英文、数字、空格和常用符号
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', ' '];
                const isAlphaNumeric = /^[a-zA-Z0-9]$/.test(e.key);
                const isSymbol = /^[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]$/.test(e.key);

                if (!allowedKeys.includes(e.key) && !isAlphaNumeric && !isSymbol) {
                    e.preventDefault();
                    return;
                }

                handleComboboxKeydown(e, input);
            };

            // 添加 blur 事件处理器，确保编辑模式下数据被保存
            const blurHandler = (e) => {
                // 检查是否是点击下拉选项导致的blur
                const container = input.closest('.combobox-container');
                const dropdown = container.querySelector('.combobox-dropdown');

                // 如果下拉列表显示中且点击的是下拉选项，则不执行验证
                if (dropdown && dropdown.classList.contains('show')) {
                    // 延迟执行验证，给点击事件时间完成
                    setTimeout(() => {
                        // 再次检查下拉列表是否还显示，如果隐藏了说明选择已完成
                        if (!dropdown.classList.contains('show')) {
                            performValidation();
                        }
                    }, 150);
                    return;
                }

                performValidation();

                function performValidation() {

                    if (input._isSelecting) {
                        return;
                    }
                    // 验证输入值
                    if (input.value.trim() && !validateComboboxInput(input)) {
                        const type = input.dataset.type;
                        let fieldName = '字段';
                        if (type === 'code') fieldName = '货品编号';
                        else if (type === 'product') fieldName = '货品名称';
                        else if (type === 'receiver') fieldName = '收货人';
                        showAlert(`${fieldName}不存在，请从下拉列表中选择`, 'error');
                        // 不要立即重新聚焦，给用户机会点击其他地方
                        setTimeout(() => {
                            if (document.activeElement !== input) {
                                input.focus();
                            }
                        }, 100);
                        return;
                    }

                    const recordId = input.dataset.recordId;
                    const fieldName = input.dataset.field;
                    const type = input.dataset.type;

                    // 检查是否需要自动勾选货品备注（仅限中央页面，且是货品名称字段）
                    if (currentStockType === 'central' && type === 'product' && input.value.trim()) {
                        const productName = input.value.trim();
                        const shouldCheckRemark = autoRemarkProducts.includes(productName);
                        const row = input.closest('tr');

                        // 如果是新增行
                        if (row && row.classList.contains('new-row')) {
                            const remarkCheckbox = row.querySelector('.remark-checkbox');
                            if (remarkCheckbox && !remarkCheckbox.disabled) {
                                remarkCheckbox.checked = shouldCheckRemark;
                                // 触发toggleNewRowRemarkNumber以更新备注编号输入框状态
                                const containerId = input.closest('.combobox-container').id;
                                const idParts = containerId.split('-');
                                if (idParts.length >= 3 && typeof toggleNewRowRemarkNumber === 'function') {
                                    toggleNewRowRemarkNumber(`${idParts[0]}-${idParts[1]}-${idParts[2]}`);
                                }
                            }
                        }
                        // 如果是编辑模式的行
                        else if (recordId) {
                            const isEditMode = editingRowIds.has(parseInt(recordId));
                            if (isEditMode && row) {
                                const remarkCheckbox = row.querySelector('.remark-checkbox');
                                if (remarkCheckbox && !remarkCheckbox.disabled) {
                                    remarkCheckbox.checked = shouldCheckRemark;
                                    // 触发updateRemarkCheck以更新备注编号输入框状态和数据库
                                    if (typeof updateRemarkCheck === 'function') {
                                        updateRemarkCheck(parseInt(recordId), shouldCheckRemark);
                                    }
                                }
                            }
                        }
                    }

                    if (recordId && fieldName) {
                        const record = stockData.find(r => r.id === parseInt(recordId));
                        if (record && record[fieldName] !== input.value) {
                            record[fieldName] = input.value;
                            // 如果是数值相关字段，需要重新计算
                            if (fieldName === 'in_quantity' || fieldName === 'out_quantity' || fieldName === 'price') {
                                renderStockTable();
                            }
                        }
                    }
                }
            };

            // 绑定事件监听器
            input.addEventListener('focus', focusHandler);
            input.addEventListener('input', inputHandler);
            input.addEventListener('keydown', keydownHandler);
            input.addEventListener('blur', blurHandler); // 这是新添加的一行

            // 标记已绑定
            input._eventsbound = true;
        }
    });

    // 为所有 combobox 选项绑定点击事件
    document.querySelectorAll('.combobox-option').forEach(option => {
        if (!option._eventsbound) {
            const clickHandler = () => {
                const container = option.closest('.combobox-container');
                const input = container.querySelector('.combobox-input');
                selectComboboxOption(option, input);
            };
            option.addEventListener('click', clickHandler);
            option._eventsbound = true;
        }
    });
}

// 全局点击事件（隐藏下拉列表）
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
        // 延迟执行，避免频繁计算
        clearTimeout(tableContainer._scrollTimeout);
        tableContainer._scrollTimeout = setTimeout(() => {
            const visibleDropdowns = document.querySelectorAll('.combobox-dropdown.show');
            visibleDropdowns.forEach(dropdown => {
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

// 加载货品的所有进货价格选项（按编号过滤只显示该供应商价格）
async function loadProductPrices(productName, selectElementId, currentPrice = '') {
    try {
        let codeNumber = '';
        const m = selectElementId && selectElementId.match(/^price-select-(\d+)$/);
        if (m && typeof stockData !== 'undefined') {
            const rec = stockData.find(r => r.id === parseInt(m[1], 10));
            if (rec && rec.code_number) codeNumber = String(rec.code_number).trim();
        }
        const codeParam = codeNumber ? `&code_number=${encodeURIComponent(codeNumber)}` : '';
        const result = await apiCall(`?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=1${codeParam}`);
        const selectElement = document.getElementById(selectElementId);

        if (!selectElement) return;

        if (result.success && result.data && result.data.length > 0) {
            let options = '<option value="">请选择价格</option>';
            // 始终保留手动输入价格选项
            options += '<option value="manual">手动输入</option>';;

            result.data.forEach(item => {
                const price = item.price;
                const availableStock = item.available_stock;
                const selected = price == currentPrice ? 'selected' : '';
                const stockInfo = `(库存: ${availableStock})`;
                options += `<option value="${price}" ${selected}>${parseFloat(price).toFixed(3)} ${stockInfo}</option>`;
            });
            selectElement.innerHTML = options;
        } else {
            // 即使没有价格数据，也保留手动输入选项
            selectElement.innerHTML = '<option value="">暂无历史价格</option><option value="manual">手动输入价格</option>';
        }

        // 如果选择了"手动输入价格"，显示输入框
        selectElement.addEventListener('change', function () {
            handlePriceSelectChange(this);
        });

    } catch (error) {
        console.error('加载货品价格失败:', error);
        const selectElement = document.getElementById(selectElementId);
        if (selectElement) {
            // 即使出错也保留手动输入选项
            selectElement.innerHTML = '<option value="">加载失败</option><option value="manual">手动输入价格</option>';
        }
    }
}

async function createNewRowPriceSelectWithStock(rowId, productName, currentPrice = '', requiredQty = 0) {
    const priceInput = document.getElementById(`${rowId}-price`);
    const priceCell = priceInput.closest('.currency-display');

    // 检查是否已经是下拉选项
    if (priceCell.querySelector('.price-select')) {
        return;
    }

    // 创建下拉选项
    const selectElement = document.createElement('select');
    selectElement.className = 'table-select price-select';
    selectElement.id = `${rowId}-price-select`;
    selectElement.innerHTML = '<option value="">正在加载...</option>';

    // 隐藏输入框，显示下拉选项
    priceInput.style.display = 'none';
    priceCell.appendChild(selectElement);

    // 加载价格选项（带库存检查）
    await loadNewRowProductPricesWithStock(productName, selectElement.id, currentPrice, requiredQty);

    // 绑定变化事件
    selectElement.addEventListener('change', function () {
        handleNewRowPriceSelectChange(this, rowId);
    });
}

// 3. 新增函数：加载新行货品价格选项（带库存检查，按编号过滤只显示该供应商价格）
async function loadNewRowProductPricesWithStock(productName, selectElementId, currentPrice = '', requiredQty = 0) {
    try {
        let codeNumber = '';
        const selectEl = document.getElementById(selectElementId);
        if (selectEl) {
            const row = selectEl.closest('tr');
            const codeInput = row ? row.querySelector('.combobox[data-type="code"] .combobox-input, input[id*="-code_number-input"]') : null;
            if (codeInput && codeInput.value) codeNumber = codeInput.value.trim();
        }
        const codeParam = codeNumber ? `&code_number=${encodeURIComponent(codeNumber)}` : '';
        const result = await apiCall(`?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=${requiredQty}${codeParam}`);
        const selectElement = document.getElementById(selectElementId);

        if (!selectElement) return;

        if (result.success && result.data && result.data.length > 0) {
            let options = '<option value="">请选择价格</option>';
            options += '<option value="manual">手动输入</option>';

            result.data.forEach(item => {
                const price = item.price;
                const availableStock = item.available_stock;
                const selected = price == currentPrice ? 'selected' : '';

                if (availableStock >= requiredQty) {
                    const stockInfo = requiredQty > 0 && availableStock < requiredQty
                        ? `(库存: ${availableStock}, 不足)`
                        : `(库存: ${availableStock})`;
                    options += `<option value="${price}" ${selected}>${parseFloat(price).toFixed(3)} ${stockInfo}</option>`;
                }
            });

            selectElement.innerHTML = options;
        } else {
            selectElement.innerHTML = '<option value="">暂无足够库存的价格</option><option value="manual">手动输入价格</option>';
        }

    } catch (error) {
        console.error('加载货品价格失败:', error);
        const selectElement = document.getElementById(selectElementId);
        if (selectElement) {
            selectElement.innerHTML = '<option value="">加载失败</option><option value="manual">手动输入价格</option>';
        }
    }
}

window.loadAddFormProductPricesWithStock = async function (productName, requiredQty = 0) {
    try {
        const checkQty = requiredQty > 0 ? requiredQty : 1;
        const addCodeEl = document.getElementById('add-code-number');
        const codeNumber = addCodeEl && addCodeEl.value ? String(addCodeEl.value).trim() : '';
        const codeParam = codeNumber ? `&code_number=${encodeURIComponent(codeNumber)}` : '';
        const result = await apiCall(`?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=${checkQty}${codeParam}`);
        const selectElement = document.getElementById('add-price-select');
        const priceInput = document.getElementById('add-price');

        if (!selectElement || !priceInput) {
            console.error('找不到价格元素:', { selectElement, priceInput });
            return;
        }

        console.log('API返回结果:', result);

        if (result.success && result.data && result.data.length > 0) {
            // 过滤出有库存的价格（库存>0）
            const pricesWithStock = result.data.filter(item => item.available_stock > 0);
            console.log('有库存的价格数量:', pricesWithStock.length, pricesWithStock);

            if (pricesWithStock.length === 0) {
                // 没有库存，显示库存不足
                console.log('没有库存');
                selectElement.style.display = 'none';
                priceInput.style.display = 'block';
                priceInput.value = '';
                priceInput.placeholder = '库存不足';
                priceInput.disabled = true;
                priceInput.style.color = '#dc2626';
                priceInput.style.backgroundColor = '#fef2f2';
                return;
            }

            // 有库存，根据价格数量决定显示方式
            if (pricesWithStock.length === 1) {
                const singlePrice = pricesWithStock[0].price;
                const priceValue = parseFloat(singlePrice);
                const displayPrice = priceValue.toFixed(3);
                console.log('✓ 只有一个价格，自动填充单价:', priceValue, '原始值:', singlePrice);
                console.log('价格元素状态 - selectElement:', selectElement, 'priceInput:', priceInput);

                // 确保元素存在
                if (!selectElement || !priceInput) {
                    console.error('✗ 价格元素不存在');
                    return;
                }

                // 强制设置显示和值
                selectElement.style.display = 'none';
                selectElement.innerHTML = ''; // 清空下拉选单
                priceInput.style.display = 'block';
                priceInput.disabled = false;
                priceInput.placeholder = '0.00';
                priceInput.style.color = '';
                priceInput.style.backgroundColor = '';

                // 使用多种方式设置值，确保能够填充
                const finalValue = priceValue.toFixed(5);
                priceInput.value = finalValue;
                priceInput.setAttribute('value', finalValue);

                // 使用 setTimeout 确保值被设置（防止其他代码覆盖）
                setTimeout(() => {
                    if (priceInput.value !== finalValue) {
                        console.warn('价格值被覆盖，重新设置');
                        priceInput.value = finalValue;
                    }
                    console.log('✓ 单价已填充到输入框，值:', priceInput.value, '期望值:', finalValue);
                }, 100);

                // 立即输出当前值
                console.log('✓ 单价已填充到输入框，值:', priceInput.value, '期望值:', finalValue);
                console.log('✓ 输入框元素:', priceInput);
                console.log('✓ 输入框display:', priceInput.style.display);
                console.log('✓ 输入框disabled:', priceInput.disabled);

                // 触发input和change事件，确保其他相关逻辑能响应
                priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                priceInput.dispatchEvent(new Event('change', { bubbles: true }));

                console.log('✓ 已触发input和change事件');
            } else {
                // 有多个价格，显示下拉选单
                let options = '<option value="">请选择价格</option>';
                pricesWithStock.forEach(item => {
                    const price = item.price;
                    const availableStock = item.available_stock;
                    const stockInfo = requiredQty > 0 && availableStock < requiredQty
                        ? `(库存: ${availableStock}, 不足)`
                        : `(库存: ${availableStock})`;
                    options += `<option value="${price}">${parseFloat(price).toFixed(5)} ${stockInfo}</option>`;
                });
                selectElement.innerHTML = options;
                selectElement.style.display = 'block';
                priceInput.style.display = 'none';
                priceInput.disabled = false;
                priceInput.style.color = '';
                priceInput.style.backgroundColor = '';
            }
        } else {
            // 没有价格数据，显示库存不足
            selectElement.style.display = 'none';
            priceInput.style.display = 'block';
            priceInput.value = '';
            priceInput.placeholder = '库存不足';
            priceInput.disabled = true;
            priceInput.style.color = '#dc2626';
            priceInput.style.backgroundColor = '#fef2f2';
        }

    } catch (error) {
        console.error('加载货品价格失败:', error);
        const selectElement = document.getElementById('add-price-select');
        const priceInput = document.getElementById('add-price');
        if (selectElement && priceInput) {
            selectElement.style.display = 'none';
            priceInput.style.display = 'block';
            priceInput.value = '';
            priceInput.disabled = false;
            priceInput.placeholder = '0.00';
            priceInput.style.color = '';
            priceInput.style.backgroundColor = '';
        }
    }
}

async function loadProductPricesWithStock(productName, selectElementId, currentPrice = '', requiredQty = 0, codeNumber = '', oldQtyForCurrentPrice = 0) {
    try {
        let code = codeNumber;
        if (!code && selectElementId) {
            const m = selectElementId.match(/^price-select-(\d+)$/);
            if (m) {
                const rec = stockData.find(r => r.id === parseInt(m[1], 10));
                if (rec && rec.code_number) code = String(rec.code_number).trim();
            }
        }
        const codeParam = code ? `&code_number=${encodeURIComponent(code)}` : '';
        const result = await apiCall(`?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=${requiredQty}${codeParam}`);
        const selectElement = document.getElementById(selectElementId);

        if (!selectElement) return;

        if (result.success && result.data && result.data.length > 0) {
            let options = '<option value="">请选择价格</option>';
            options += '<option value="manual">手动输入价格</option>';

            let currentPriceIncluded = false;

            result.data.forEach(item => {
                const price = item.price;
                let availableStock = item.available_stock;

                // 给当前正在编辑的价格加上原来的出库数量，相当于暂不扣减（仅当价格匹配时）
                if (price == currentPrice) {
                    availableStock = parseFloat(availableStock) + parseFloat(oldQtyForCurrentPrice);
                }

                // 仅当 currentPrice 非空且匹配时才标记为 selected
                const selected = (currentPrice !== '' && currentPrice !== null && currentPrice !== undefined && price == currentPrice) ? 'selected' : '';

                // 只显示库存足够的价格选项，但当前价格即使库存不足也要显示（已选中的选项）
                if (availableStock >= requiredQty || (currentPrice !== '' && price == currentPrice)) {
                    const stockInfo = availableStock >= requiredQty ? `(库存: ${availableStock})` : `(库存不足: ${availableStock})`;
                    options += `<option value="${price}" ${selected}>${parseFloat(price).toFixed(5)} ${stockInfo}</option>`;
                    if (currentPrice !== '' && price == currentPrice) currentPriceIncluded = true;
                }
            });

            // 若当前价格未在 API 结果中（库存为0被过滤），仍补上显示（库存: 0）供编辑保存
            // 若当前价格未在 API 结果中（库存为0被过滤），仍补上显示（库存: 0）供编辑保存
            if (currentPrice !== '' && currentPrice !== null && currentPrice !== undefined && !currentPriceIncluded) {
                options += `<option value="${currentPrice}" selected>${parseFloat(currentPrice).toFixed(5)} (库存: 0)</option>`;
            }

            selectElement.innerHTML = options;
        } else {
            // 无数据时若有当前价格（编辑模式），仍显示该选项供保存
            let fallbackOption = '';
            if (currentPrice !== '' && currentPrice !== null && currentPrice !== undefined) {
                fallbackOption = `<option value="${currentPrice}" selected>${parseFloat(currentPrice).toFixed(5)} (库存: 0)</option>`;
            }
            selectElement.innerHTML = `<option value="">暂无足够库存的价格</option><option value="manual">手动输入价格</option>${fallbackOption}`;
        }

        // 绑定变化事件
        if (!selectElement.dataset.priceChangeBound) {
            selectElement.addEventListener('change', function () {
                handlePriceSelectChange(this);
            });
            selectElement.dataset.priceChangeBound = 'true';
        }

    } catch (error) {
        console.error('加载货品价格失败:', error);
        const selectElement = document.getElementById(selectElementId);
        if (selectElement) {
            selectElement.innerHTML = '<option value="">加载失败</option><option value="manual">手动输入价格</option>';
        }
    }
}

// 处理价格选择变化
function handlePriceSelectChange(selectElement) {
    const recordId = selectElement.id.replace('price-select-', '');
    const container = selectElement.closest('.currency-display');

    if (selectElement.value === 'manual') {
        // 显示输入框
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'table-input currency-input-edit manual-price-input';
        input.min = '0';
        input.step = '0.00001';
        input.placeholder = '输入价格';
        input.style.marginLeft = '5px';
        input.style.width = '80px';

        input.addEventListener('change', function () {
            updateField(parseInt(recordId), 'price', this.value);
            // 更新下拉选择框的值
            selectElement.value = this.value;
        });

        input.addEventListener('blur', function () {
            if (!this.value) {
                selectElement.value = '';
                updateField(parseInt(recordId), 'price', '');
            }
        });

        // 移除已存在的输入框
        const existingInput = container.querySelector('.manual-price-input');
        if (existingInput) {
            existingInput.remove();
        }

        container.appendChild(input);
        input.focus();
    } else {
        // 移除手动输入框
        const existingInput = container.querySelector('.manual-price-input');
        if (existingInput) {
            existingInput.remove();
        }

        // 更新价格值
        updateField(parseInt(recordId), 'price', selectElement.value);
    }
}

// 处理新增表单中货品变化时加载价格选项
function handleAddFormProductChange(selectElement, codeNumberElement) {
    const productName = selectElement.value;

    // 原有的货品变化处理
    handleProductChange(selectElement, codeNumberElement);

    // 根据出库数量决定是否加载价格选项
    if (productName) {
        handleAddFormOutQuantityChange();
    } else {
        const priceSelect = document.getElementById('add-price-select');
        const priceInput = document.getElementById('add-price');
        if (priceSelect) {
            priceSelect.innerHTML = '<option value="">请先选择货品</option>';
            priceSelect.style.display = 'none';
        }
        if (priceInput) {
            priceInput.style.display = 'block';
            priceInput.value = '';
        }
    }
}

// 加载新增表单的价格选项（按编号过滤只显示该供应商价格）
async function loadAddFormProductPrices(productName) {
    try {
        const addCodeEl = document.getElementById('add-code-number');
        const codeNumber = addCodeEl && addCodeEl.value ? String(addCodeEl.value).trim() : '';
        const codeParam = codeNumber ? `&code_number=${encodeURIComponent(codeNumber)}` : '';
        const result = await apiCall(`?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=1${codeParam}`);
        const selectElement = document.getElementById('add-price-select');

        if (!selectElement) return;

        if (result.success && result.data && result.data.length > 0) {
            let options = '<option value="">请选择价格</option>';
            // 始终保留手动输入价格选项
            options += '<option value="manual">手动输入价格</option>';

            result.data.forEach(item => {
                const price = item.price;
                const availableStock = item.available_stock;
                // 显示所有价格选项，不管库存是否足够
                const stockInfo = `(库存: ${availableStock})`;
                options += `<option value="${price}">${parseFloat(price).toFixed(5)} ${stockInfo}</option>`;
            });
            selectElement.innerHTML = options;
            selectElement.style.display = 'block';
            document.getElementById('add-price').style.display = 'none';
        } else {
            // 即使没有价格数据，也保留手动输入选项
            selectElement.innerHTML = '<option value="">暂无历史价格</option><option value="manual">手动输入价格</option>';
        }

    } catch (error) {
        console.error('加载货品价格失败:', error);
        const selectElement = document.getElementById('add-price-select');
        if (selectElement) {
            // 即使出错也保留手动输入选项
            selectElement.innerHTML = '<option value="">加载失败</option><option value="manual">手动输入价格</option>';
        }
    }
}

// 处理新增表单价格选择变化
function handleAddFormPriceChange() {
    const selectElement = document.getElementById('add-price-select');
    const inputElement = document.getElementById('add-price');

    if (selectElement && inputElement && selectElement.value) {
        // 将选中的价格填充到输入框并显示输入框
        inputElement.value = parseFloat(selectElement.value).toFixed(5);
        selectElement.style.display = 'none';
        inputElement.style.display = 'block';
        inputElement.disabled = false;
        inputElement.focus();
    }
}

// 处理新增表单出库数量变化
window.handleAddFormOutQuantityChange = function () {
    console.log('=== handleAddFormOutQuantityChange 被调用 ===');
    const inInput = document.getElementById('add-in-qty');
    const outInput = document.getElementById('add-out-qty');
    const outQty = parseFloat(outInput.value) || 0;
    const inQty = parseFloat(inInput.value) || 0;
    const productName = document.getElementById('add-product-name').value;
    const priceSelect = document.getElementById('add-price-select');
    const priceInput = document.getElementById('add-price');

    // 互斥控制
    enforceQuantityMutex(inInput, outInput);

    console.log('出库数量:', outQty, '入库数量:', inQty, '货品名称:', productName);

    if (outQty > 0 && inQty === 0 && productName) {
        console.log('条件满足，开始加载价格');
        // 纯出库且有货品名称，加载价格（带库存检查）
        // 先清空，等待API返回后再决定显示方式
        if (priceSelect) {
            priceSelect.style.display = 'none';
            priceSelect.innerHTML = ''; // 清空下拉选单内容
        }
        if (priceInput) {
            priceInput.style.display = 'block';
            priceInput.value = '';
            priceInput.disabled = false;
            priceInput.placeholder = '加载中...';
            priceInput.style.color = '';
            priceInput.style.backgroundColor = '';
        }
        console.log('调用 loadAddFormProductPricesWithStock，货品:', productName, '数量:', outQty);
        window.loadAddFormProductPricesWithStock(productName, outQty).then(() => {
            console.log('价格加载完成，检查输入框值:', document.getElementById('add-price')?.value);
        }).catch(error => {
            console.error('价格加载失败:', error);
        });
    } else {
        // 入库或出库为0，显示普通输入框
        priceSelect.style.display = 'none';
        priceInput.style.display = 'block';
        priceInput.disabled = false;
        priceInput.placeholder = '0.00';
        priceInput.style.color = '';
        priceInput.style.backgroundColor = '';
        if (outQty === 0 && inQty === 0) {
            priceInput.value = '';
        }
    }

    // 控制Target下拉框状态
    const targetSelect = document.getElementById('add-target');
    if (outQty > 0) {
        targetSelect.disabled = false;
        targetSelect.required = true;
    } else {
        targetSelect.disabled = true;
        if (typeof currentStockType !== 'undefined' && currentStockType && currentStockType !== 'central') {
            targetSelect.value = currentStockType;
        } else {
            targetSelect.value = '';
        }
        targetSelect.required = false;
    }

    // 收货人字段保持始终可输入状态，不需要根据出货数量控制
};

// 确保函数在全局作用域中可用
console.log('handleAddFormOutQuantityChange 函数已定义:', typeof window.handleAddFormOutQuantityChange);

// 同时创建一个不带window前缀的全局别名，确保内联事件处理器可以访问
if (typeof window.handleAddFormOutQuantityChange === 'function') {
    // 创建一个全局变量别名（不带window前缀）
    if (typeof handleAddFormOutQuantityChange === 'undefined') {
        handleAddFormOutQuantityChange = window.handleAddFormOutQuantityChange;
    }
    console.log('✓ handleAddFormOutQuantityChange 函数已成功定义在全局作用域');
    console.log('✓ 函数可以直接访问:', typeof handleAddFormOutQuantityChange);
} else {
    console.error('✗ handleAddFormOutQuantityChange 函数未定义');
}

// 加载新增表单的价格选项

// 检查货品库存是否足够（按货品名称和价格分别计算）
async function checkProductStock(productName, outQuantity, price = null) {
    if (!productName || outQuantity <= 0) {
        return { sufficient: true, availableStock: 0, currentStock: 0 };
    }

    try {
        let apiUrl;
        if (price !== null && price !== '' && price !== undefined) {
            // 按货品名称和价格检查库存
            apiUrl = `?action=product_stock_by_price&product_name=${encodeURIComponent(productName)}&price=${encodeURIComponent(price)}`;
        } else {
            // 按货品名称检查总库存
            apiUrl = `?action=product_stock&product_name=${encodeURIComponent(productName)}`;
        }

        const result = await apiCall(apiUrl);

        if (result.success && result.data) {
            const availableStock = parseFloat(result.data.available_stock || 0);
            const currentStock = parseFloat(result.data.current_stock || 0);

            return {
                sufficient: availableStock >= outQuantity,
                availableStock: availableStock,
                currentStock: currentStock,
                requested: outQuantity
            };
        } else {
            // 如果无法获取库存信息，默认允许（可能是新货品）
            return { sufficient: true, availableStock: 0, currentStock: 0 };
        }

    } catch (error) {
        console.error('检查库存失败:', error);
        // 网络错误时默认允许保存
        return { sufficient: true, availableStock: 0, currentStock: 0 };
    }
}

// 为新行创建价格下拉选项
function createNewRowPriceSelect(rowId, productName, currentPrice = '') {
    const priceInput = document.getElementById(`${rowId}-price`);
    const priceCell = priceInput.closest('.currency-display');

    // 检查是否已经是下拉选项
    if (priceCell.querySelector('.price-select')) {
        return;
    }

    // 创建下拉选项
    const selectElement = document.createElement('select');
    selectElement.className = 'table-select price-select';
    selectElement.id = `${rowId}-price-select`;
    selectElement.innerHTML = '<option value="">正在加载...</option>';

    // 隐藏输入框，显示下拉选项
    priceInput.style.display = 'none';
    priceCell.appendChild(selectElement);

    // 加载价格选项
    loadNewRowProductPrices(productName, selectElement.id, currentPrice);

    // 绑定变化事件
    selectElement.addEventListener('change', function () {
        handleNewRowPriceSelectChange(this, rowId);
    });
}

// 恢复新行价格输入框
function restoreNewRowPriceInput(rowId) {
    const priceInput = document.getElementById(`${rowId}-price`);
    const priceCell = priceInput.closest('.currency-display');
    const selectElement = priceCell.querySelector('.price-select');

    if (selectElement) {
        selectElement.remove();
        priceInput.style.display = 'block';
        priceInput.value = '';
    }
}

// 加载新行货品价格选项（按编号过滤只显示该供应商价格）
async function loadNewRowProductPrices(productName, selectElementId, currentPrice = '') {
    try {
        let codeNumber = '';
        const selectEl = document.getElementById(selectElementId);
        if (selectEl) {
            const row = selectEl.closest('tr');
            const codeInput = row ? row.querySelector('.combobox[data-type="code"] .combobox-input, input[id*="-code_number-input"]') : null;
            if (codeInput && codeInput.value) codeNumber = codeInput.value.trim();
        }
        const codeParam = codeNumber ? `&code_number=${encodeURIComponent(codeNumber)}` : '';
        const result = await apiCall(`?action=product_prices_with_stock&product_name=${encodeURIComponent(productName)}&required_qty=1${codeParam}`);
        const selectElement = document.getElementById(selectElementId);

        if (!selectElement) return;

        if (result.success && result.data && result.data.length > 0) {
            let options = '<option value="">请选择价格</option>';
            // 始终保留手动输入价格选项
            options += '<option value="manual">手动输入价格</option>';

            result.data.forEach(item => {
                const price = item.price;
                const availableStock = item.available_stock;
                const selected = price == currentPrice ? 'selected' : '';
                // 显示所有价格选项，不管库存是否足够
                const stockInfo = `(库存: ${availableStock})`;
                options += `<option value="${price}" ${selected}>${parseFloat(price).toFixed(5)} ${stockInfo}</option>`;
            });
            selectElement.innerHTML = options;
        } else {
            // 即使没有价格数据，也保留手动输入选项
            selectElement.innerHTML = '<option value="">暂无历史价格</option><option value="manual">手动输入价格</option>';
        }

    } catch (error) {
        console.error('加载货品价格失败:', error);
        const selectElement = document.getElementById(selectElementId);
        if (selectElement) {
            // 即使出错也保留手动输入选项
            selectElement.innerHTML = '<option value="">加载失败</option><option value="manual">手动输入价格</option>';
        }
    }
}

// 处理新行价格下拉选择变化
function handleNewRowPriceSelectChange(selectElement, rowId) {
    const priceInput = document.getElementById(`${rowId}-price`);
    const container = selectElement.closest('.currency-display');

    if (selectElement.value === 'manual') {
        // 显示手动输入框
        const manualInput = document.createElement('input');
        manualInput.type = 'number';
        manualInput.className = 'table-input currency-input-edit manual-price-input';
        manualInput.min = '0';
        manualInput.step = '0.00001';
        manualInput.placeholder = '输入价格';
        manualInput.style.marginLeft = '5px';
        manualInput.style.width = '80px';

        manualInput.addEventListener('input', function () {
            priceInput.value = this.value;
            updateNewRowTotal(priceInput);
        });

        manualInput.addEventListener('blur', function () {
            if (!this.value) {
                selectElement.value = '';
                priceInput.value = '';
                updateNewRowTotal(priceInput);
            }
        });

        // 移除已存在的手动输入框
        const existingInput = container.querySelector('.manual-price-input');
        if (existingInput) {
            existingInput.remove();
        }

        container.appendChild(manualInput);
        manualInput.focus();
    } else {
        // 移除手动输入框
        const existingInput = container.querySelector('.manual-price-input');
        if (existingInput) {
            existingInput.remove();
        }

        // 更新价格值
        priceInput.value = selectElement.value;
        updateNewRowTotal(priceInput);
    }
}

// 关闭导出弹窗
function closeExportModal() {
    document.getElementById('export-modal').style.display = 'none';

    // 重置导出按钮状态
    const exportBtn = document.querySelector('.export-modal-actions .btn-success');
    if (exportBtn) {
        exportBtn.innerHTML = '<i class="fas fa-download"></i> 导出Excel';
        exportBtn.disabled = false;
    }

    // 清空发票号码后缀输入框
    document.getElementById('export-invoice-suffix').value = '';
}

// 确认导出
async function confirmExport() {
    const startDate = document.getElementById('export-start-date').value;
    const endDate = document.getElementById('export-end-date').value;
    const exportSystemInput = document.getElementById('export-system');
    const invoiceDateInput = document.getElementById('export-invoice-date');
    const invoiceSuffixInput = document.getElementById('export-invoice-suffix');
    let exportSystem = exportSystemInput ? exportSystemInput.value : '';
    let invoiceDate = invoiceDateInput ? invoiceDateInput.value : '';
    let invoiceSuffix = invoiceSuffixInput ? invoiceSuffixInput.value : '';
    const isCentralContext = currentStockType === 'central';
    const exportActionBtn = document.querySelector('.export-modal-actions .btn-success');
    const originalExportBtnText = exportActionBtn ? exportActionBtn.innerHTML : '';

    // 验证输入
    if (!startDate || !endDate) {
        showAlert('请选择开始和结束日期', 'error');
        return;
    }

    if (isCentralContext) {
        if (!exportSystem) {
            showAlert('请选择导出系统', 'error');
            return;
        }
        if (!invoiceDate) {
            showAlert('请选择发票日期', 'error');
            return;
        }
        if (!invoiceSuffix || invoiceSuffix.length !== 3 || !/^\d{3}$/.test(invoiceSuffix)) {
            showAlert('请输入三位数字的发票号码后缀（例如：001）', 'error');
            return;
        }
    } else {
        exportSystem = currentStockType;
        if (exportSystemInput) {
            exportSystemInput.value = exportSystem;
        }
        if (invoiceDateInput) {
            invoiceDateInput.value = invoiceDate;
        }
        if (!invoiceSuffix || invoiceSuffix.length !== 3 || !/^\d{3}$/.test(invoiceSuffix)) {
            invoiceSuffix = '';
            if (invoiceSuffixInput) {
                invoiceSuffixInput.value = invoiceSuffix;
            }
        }
    }

    // 验证日期格式并转换为YYYY-MM-DD格式
    const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;

    const parseDate = (dateStr) => {
        const match = dateStr.match(dateRegex);
        if (!match) {
            throw new Error('无效的日期格式');
        }
        const [, day, month, year] = match;
        return new Date(year, month - 1, day);
    };

    let startDateObj, endDateObj, invoiceDateObj;
    try {
        startDateObj = parseDate(startDate);
        endDateObj = parseDate(endDate);
        invoiceDateObj = isCentralContext ? parseDate(invoiceDate) : null;
    } catch (error) {
        showAlert('日期格式错误，请使用DD/MM/YYYY格式', 'error');
        return;
    }

    const formatDateToYYYYMMDD = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (!isCentralContext) {
        if (startDateObj > endDateObj) {
            showAlert('开始日期不能晚于结束日期', 'error');
            return;
        }
        if (exportActionBtn) {
            exportActionBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 导出中...';
            exportActionBtn.disabled = true;
        }
        try {
            const formattedStart = formatDateToYYYYMMDD(startDateObj);
            const formattedEnd = formatDateToYYYYMMDD(endDateObj);
            await exportBranchStockExcel(exportSystem, formattedStart, formattedEnd);
            closeExportModal();
        } catch (error) {
            console.error('Excel 导出失败:', error);
            showAlert(error.message || '导出失败，请重试', 'error');
        } finally {
            if (exportActionBtn) {
                exportActionBtn.innerHTML = originalExportBtnText || '<i class="fas fa-download"></i> 导出Excel';
                exportActionBtn.disabled = false;
            }
        }
        return;
    }

    // 转换发票日期为YYYY-MM-DD格式用于生成发票号码
    // 生成发票号码：格式为 J1-2510-001
    const generatedInvoiceNumber = generateInvoiceNumber(exportSystem, formatDateToYYYYMMDD(invoiceDateObj), invoiceSuffix);

    if (startDateObj > endDateObj) {
        showAlert('开始日期不能晚于结束日期', 'error');
        return;
    }

    // 显示加载状态
    if (exportActionBtn) {
        exportActionBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 生成中...';
        exportActionBtn.disabled = true;
    }

    try {

        // 获取指定日期范围内的出库数据（转换为YYYY-MM-DD格式）
        const params = new URLSearchParams({
            action: 'list',
            start_date: formatDateToYYYYMMDD(startDateObj),
            end_date: formatDateToYYYYMMDD(endDateObj)
        });

        const result = await apiCall(`?${params}`);

        if (!result.success) {
            throw new Error('获取数据失败');
        }

        console.log('API返回的数据总数:', (result.data || []).length);
        console.log('查询日期范围:', formatDateToYYYYMMDD(startDateObj), '到', formatDateToYYYYMMDD(endDateObj));

        // 过滤出库数据 - 按日期范围、出库数量和收货单位筛选
        const outData = (result.data || []).filter(record => {
            const outQty = parseFloat(record.out_quantity);
            if (outQty <= 0) {
                console.log('记录出库数量为0或负数，跳过:', record.id, 'out_quantity:', record.out_quantity);
                return false;
            }

            // 检查收货单位是否匹配选择的店面
            const targetSystem = record.target_system;
            if (!targetSystem || targetSystem.toLowerCase() !== exportSystem.toLowerCase()) {
                console.log('记录收货单位不匹配，跳过:', record.id, 'target_system:', targetSystem, '期望:', exportSystem);
                return false;
            }

            // 检查日期范围 - 只使用设定的日期字段，不使用创建时间
            const recordDate = record.date;
            if (!recordDate) {
                console.log('记录缺少date字段，跳过:', record.id);
                return false;
            }

            const recordDateObj = new Date(recordDate);
            // 创建日期对象的副本用于比较，避免修改原始对象
            const startDateForCompare = new Date(startDateObj);
            const endDateForCompare = new Date(endDateObj);

            // 设置时间为当天的开始和结束
            startDateForCompare.setHours(0, 0, 0, 0);
            endDateForCompare.setHours(23, 59, 59, 999);
            recordDateObj.setHours(0, 0, 0, 0);

            const isInRange = recordDateObj >= startDateForCompare && recordDateObj <= endDateForCompare;
            if (!isInRange) {
                console.log('记录日期不在范围内:', record.id, '日期:', recordDate, '范围:', formatDateToYYYYMMDD(startDateForCompare), '到', formatDateToYYYYMMDD(endDateForCompare));
            }

            return isInRange;
        });

        console.log('过滤后的出库数据数量:', outData.length);

        // 允许导出未来日期，即使没有数据也可以导出
        if (outData.length === 0) {
            showAlert('指定日期范围内没有出库数据，将生成空的PDF文件', 'info');
            // 继续执行，不返回，允许生成空的PDF
        }

        // 检查日期范围是否超过一天
        // 先比较日期部分（年月日），而不是包含时间的完整日期对象
        const startDateOnly = new Date(startDateObj.getFullYear(), startDateObj.getMonth(), startDateObj.getDate());
        const endDateOnly = new Date(endDateObj.getFullYear(), endDateObj.getMonth(), endDateObj.getDate());
        const daysDiff = Math.ceil((endDateOnly - startDateOnly) / (1000 * 60 * 60 * 24));
        const isMultiDay = daysDiff >= 1; // 只有当日期差大于等于1天（即跨天）时才认为是多天

        let finalData = outData;
        let isGroupedByDate = false;

        // 如果日期范围超过一天，按日期分组
        if (isMultiDay) {
            isGroupedByDate = true;
            const groupedByDate = {};

            // 按日期分组并计算每天的总金额 - 只使用设定的日期字段，不使用创建时间
            outData.forEach(record => {
                const recordDate = record.date;
                if (!recordDate) return;

                const dateObj = new Date(recordDate);
                const dateKey = formatDateToYYYYMMDD(dateObj);

                if (!groupedByDate[dateKey]) {
                    groupedByDate[dateKey] = {
                        date: dateKey,
                        total: 0,
                        records: []
                    };
                }

                const outQty = parseFloat(record.out_quantity) || 0;
                const price = parseFloat(record.price) || 0;
                const total = outQty * price;

                groupedByDate[dateKey].total += total;
                groupedByDate[dateKey].records.push(record);
            });

            // 转换为按日期排序的数组，格式化为显示格式
            finalData = Object.keys(groupedByDate)
                .sort()
                .map((dateKey, index) => {
                    const group = groupedByDate[dateKey];
                    const dateObj = new Date(dateKey);
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const year = dateObj.getFullYear();
                    const dateDisplay = `${day}/${month}/${year}`;

                    return {
                        item_number: index + 1,
                        product_name: dateDisplay,
                        out_quantity: 0,
                        specification: '-',
                        price: 0,
                        total_value: group.total,
                        is_date_group: true,
                        date: dateKey
                    };
                });
        }

        // 根据记录数量决定使用单页还是多页模板
        const recordCount = finalData.length;
        const useMultiPage = (exportSystem === 'j1' && recordCount > 27) || (exportSystem === 'j2' && recordCount > 24) || (exportSystem === 'j3' && recordCount > 24);

        if (useMultiPage) {
            // 使用多页模板
            const pageCount = Math.ceil(recordCount / (exportSystem === 'j1' ? 27 : 24));
            showAlert(`记录数量较多(${recordCount}条)，将使用多页模板生成PDF (共${pageCount}页)`, 'info');
            await generateMultiPageInvoicePDF(finalData, formatDateToYYYYMMDD(startDateObj), formatDateToYYYYMMDD(endDateObj), exportSystem, generatedInvoiceNumber, formatDateToYYYYMMDD(invoiceDateObj), isGroupedByDate);
        } else {
            // 使用单页模板
            await generateInvoicePDF(finalData, formatDateToYYYYMMDD(startDateObj), formatDateToYYYYMMDD(endDateObj), exportSystem, generatedInvoiceNumber, formatDateToYYYYMMDD(invoiceDateObj), isGroupedByDate);
        }

        showAlert('PDF发票生成成功', 'success');
        closeExportModal();

    } catch (error) {
        console.error('导出失败:', error);
        showAlert('生成PDF发票失败，请重试', 'error');
    } finally {
        if (exportActionBtn) {
            exportActionBtn.innerHTML = originalExportBtnText || '<i class="fas fa-download"></i> 导出Excel';
            exportActionBtn.disabled = false;
        }
    }
}

async function exportBranchStockExcel(exportSystem, startDate, endDate) {
    const query = new URLSearchParams({
        system: exportSystem,
        start_date: startDate,
        end_date: endDate,
        ts: Date.now().toString()
    });

    const response = await fetch(`export_branch_stock_excel.php?${query.toString()}`, {
        method: 'GET',
        cache: 'no-store'
    });

    if (!response.ok) {
        throw new Error('导出失败，请稍后重试');
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    const filename = `${exportSystem.toUpperCase()}_STOCK_${startDate.replace(/-/g, '')}_${endDate.replace(/-/g, '')}.xls`;

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    showAlert('Excel 导出成功', 'success');
}

// 点击弹窗外部关闭
window.addEventListener('click', function (event) {
    const modal = document.getElementById('export-modal');
    if (event.target === modal) {
        closeExportModal();
    }
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
        const scrollThreshold = 150; // 滚动超过150px后显示按钮

        if (window.pageYOffset > scrollThreshold) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    }, 10);
});

// 生成发票号码 - 格式：J1-2510-001（店面-年月-序号）
function generateInvoiceNumber(exportSystem, invoiceDate, userSuffix) {
    // 从发票日期提取年月（YYMM格式）
    const date = new Date(invoiceDate);
    const month = String(date.getMonth() + 1).padStart(2, '0'); // 月份补零
    const year = date.getFullYear().toString().slice(-2); // 取后两位年份
    const yearMonth = year + month;

    // 确保用户输入的后缀是三位数
    const suffix = String(userSuffix).padStart(3, '0');

    // 生成发票号码：店面-年月-序号（店面代码大写）
    const invoiceNumber = `${exportSystem.toUpperCase()}-${yearMonth}-${suffix}`;

    console.log(`发票号码: ${invoiceNumber}`);
    return invoiceNumber;
}

// 生成PDF发票
async function generateInvoicePDF(outData, startDate, endDate, exportSystem, invoiceNumber = '', invoiceDate = '', isGroupedByDate = false) {
    try {

        console.log('开始生成PDF发票:', {
            exportSystem,
            dataLength: outData ? outData.length : 0,
            startDate,
            endDate,
            invoiceNumber
        });

        // 如果没有提供发票号码，自动生成一个
        if (!invoiceNumber) {
            invoiceNumber = generateInvoiceNumber(exportSystem);
        }

        // 下载现有的PDF模板
        let templateFile;
        if (exportSystem === 'j2') {
            templateFile = `../invoice/invoice/j2invoice.pdf?ts=${Date.now()}`;
        } else if (exportSystem === 'j3') {
            templateFile = `../invoice/invoice/j3invoice.pdf?ts=${Date.now()}`;
        } else {
            templateFile = `../invoice/invoice/j1invoice.pdf?ts=${Date.now()}`;
        }
        const templateResponse = await fetch(templateFile);
        if (!templateResponse.ok) {
            throw new Error('无法加载PDF模板');
        }

        const templateBytes = await templateResponse.arrayBuffer();

        // 使用PDF-lib库来编辑PDF
        const { PDFDocument, rgb, StandardFonts } = PDFLib;
        const pdfDoc = await PDFDocument.load(templateBytes);

        // 获取第一页
        const page = pdfDoc.getPage(0);
        const { width, height } = page.getSize();

        // 嵌入字体
        const boldFont = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
        const regularFont = await pdfDoc.embedFont(StandardFonts.Helvetica);
        const monoFont = await pdfDoc.embedFont(StandardFonts.Courier);
        const monoBoldFont = await pdfDoc.embedFont(StandardFonts.CourierBold);

        // 设置字体大小和颜色
        const fontSize = 11;
        const smallFontSize = 9;
        const textColor = rgb(0, 0, 0);
        const whiteColor = rgb(1, 1, 1); // 白色

        // 字体对齐辅助函数
        function getRightAlignedX(text, maxX, charWidth = 6) {
            return maxX - (text.length * charWidth);
        }

        function getCenterAlignedX(text, centerX, charWidth = 6) {
            return centerX - (text.length * charWidth / 2);
        }

        // 按小数点对齐：anchorX 作为右边界（文本右端对齐），小数点位于固定偏移
        // 规则：anchorX 代表整列的右边界；若包含小数点，将小数点对齐到 (anchorX - dotOffset)
        // 这样无需改任何坐标，只通过计算 x 返回值实现对齐
        function getDecimalAlignedX(text, anchorX, font, size, dotOffset = 0) {
            const str = String(text ?? '');
            const dotIndex = str.indexOf('.');
            if (dotIndex >= 0) {
                // 宽度= 整个字符串宽度；小数点左侧宽度用于将小数点放在 anchorX - dotOffset
                const leftPart = str.substring(0, dotIndex);
                const leftWidth = font.widthOfTextAtSize(leftPart, size);
                return (anchorX - dotOffset) - leftWidth;
            }
            // 无小数点：按右边界对齐
            const width = font.widthOfTextAtSize(str, size);
            return anchorX - width;
        }

        // 填入日期 (右上角区域)
        const currentDate = invoiceDate ?
            new Date(invoiceDate).toLocaleDateString('en-GB') :
            new Date().toLocaleDateString('en-GB');

        if (exportSystem === 'j1') {
            // J1模板的日期位置
            page.drawText(` ${currentDate}`, {
                x: 495.5, // J1模板DATE冒号后面的位置
                y: height - 110.5,
                size: fontSize,
                color: textColor,
                font: boldFont,
            });

            // J1模板的发票号码位置
            if (invoiceNumber) {
                page.drawText(invoiceNumber, {
                    x: 500, // J1模板Invoice No位置
                    y: height - 96.5, // 调整到Invoice No行
                    size: fontSize,
                    color: textColor,
                    font: boldFont,
                });
            }
        } else if (exportSystem === 'j2') {
            // J2模板的日期位置
            page.drawText(` ${currentDate}`, {
                x: 495.5, // J2模板DATE冒号后面的位置 (可根据需要调整)
                y: height - 110.5, // J2模板的Y坐标 (可根据需要调整)
                size: fontSize,
                color: textColor,
                font: boldFont,
            });

            // J2模板的发票号码位置
            if (invoiceNumber) {
                page.drawText(invoiceNumber, {
                    x: 500,
                    y: height - 96.5, // 调整到Invoice No行
                    size: fontSize,
                    color: textColor,
                    font: boldFont,
                });
            }
        } else if (exportSystem === 'j3') {
            // J3模板的日期位置
            page.drawText(` ${currentDate}`, {
                x: 495.5, // J3模板DATE冒号后面的位置
                y: height - 110.5, // J3模板的Y坐标
                size: fontSize,
                color: textColor,
                font: boldFont,
            });

            // J3模板的发票号码位置
            if (invoiceNumber) {
                page.drawText(invoiceNumber, {
                    x: 500, // J3模板Invoice No位置
                    y: height - 96.5, // 调整到Invoice No行
                    size: fontSize,
                    color: textColor,
                    font: boldFont,
                });
            }
        }

        // 计算总金额（以分为单位累计，避免浮点误差）
        let grandTotalCents = 0;
        let grandTotalRaw = 0; // 原始总金额（不进位）

        // 填入数据行 (从第一个数据行开始)
        let yPosition, lineHeight;
        if (exportSystem === 'j1') {
            yPosition = height - 162; // J1模板的起始Y坐标
            lineHeight = 20; // J1模板的行高
        } else if (exportSystem === 'j2') {
            yPosition = height - 202; // J2模板的起始Y坐标
            lineHeight = 20; // J2模板的行高
        } else { // j3
            yPosition = height - 202; // J3模板的起始Y坐标
            lineHeight = 20; // J3模板的行高
        }

        // 清除缓存并强制刷新 - 版本 2.0
        console.log('=== PDF生成调试信息 v2.0 ===');
        console.log('outData类型:', typeof outData);
        console.log('outData长度:', outData.length);
        console.log('outData内容:', outData);

        if (outData.length === 0) {
            console.warn('警告：outData为空，将显示空白发票');
        }

        outData.forEach((record, index) => {
            const itemNumber = isGroupedByDate ? (record.item_number || index + 1) : (index + 1);
            let totalRaw, totalRounded;

            if (isGroupedByDate && record.is_date_group) {
                // 按日期分组：使用总金额
                totalRaw = parseFloat(record.total_value) || 0;
                totalRounded = roundCurrencyValue(totalRaw);
            } else {
                // 正常明细：计算金额
                const outQty = parseFloat(record.out_quantity) || 0;
                const price = parseFloat(record.price) || 0;
                totalRaw = outQty * price;
                totalRounded = roundCurrencyValue(totalRaw);
            }

            grandTotalCents += Math.round(totalRounded * 100);
            grandTotalRaw += totalRaw; // 累计原始金额

            // NO (第一列) - 居中对齐
            const itemText = itemNumber.toString();
            page.drawText(itemText, {
                x: getCenterAlignedX(itemText, exportSystem === 'j1' ? 42 : 42, 6),
                y: yPosition,
                size: smallFontSize,
                color: textColor,
            });

            // Descriptions (第二列) - 左对齐，调整产品名称显示，处理长文本
            const productName = record.product_name || '';
            const maxProductNameLength = 25;
            const displayProductName = productName.length > maxProductNameLength
                ? productName.substring(0, maxProductNameLength) + '...'
                : productName;

            page.drawText(displayProductName.toUpperCase(), {
                x: exportSystem === 'j1' ? 80 : 80,
                y: yPosition,
                size: smallFontSize,
                color: textColor,
            });

            // Quantity (第三列) - 右对齐（显示三位小数）
            let qtyText;
            if (isGroupedByDate && record.is_date_group) {
                qtyText = '-';
            } else {
                const outQty = parseFloat(record.out_quantity) || 0;
                qtyText = formatNumber(outQty);
            }
            page.drawText(qtyText, {
                x: getDecimalAlignedX(qtyText, exportSystem === 'j1' ? 373 : 373, monoBoldFont, smallFontSize, 0),
                y: yPosition,
                size: smallFontSize,
                color: textColor,
                font: monoBoldFont,
            });

            // UOM (第四列) - 左对齐
            let uomText;
            if (isGroupedByDate && record.is_date_group) {
                uomText = '-';
            } else {
                uomText = record.specification || '';
            }
            page.drawText(uomText.toUpperCase(), {
                x: exportSystem === 'j1' ? 406 : 406,
                y: yPosition,
                size: 8,
                color: textColor,
            });

            // Price RM (第五列) - 右对齐
            let priceText;
            if (isGroupedByDate && record.is_date_group) {
                priceText = '-';
            } else {
                const price = parseFloat(record.price) || 0;
                priceText = formatCurrencyForPDF(price);
            }
            page.drawText(priceText, {
                x: getDecimalAlignedX(priceText, exportSystem === 'j1' ? 488 : 488, monoBoldFont, smallFontSize, 0),
                y: yPosition,
                size: smallFontSize,
                color: textColor,
                font: monoBoldFont,
            });

            // Total RM (第六列) - 右对齐
            const totalText = formatCurrencyForPDF(totalRounded);
            page.drawText(totalText, {
                x: getDecimalAlignedX(totalText, exportSystem === 'j1' ? 548 : 548, monoBoldFont, smallFontSize, 0),
                y: yPosition,
                size: smallFontSize,
                color: textColor,
                font: monoBoldFont,
            });

            yPosition -= lineHeight;
        });

        if (exportSystem === 'j2') {
            const subtotalRaw = grandTotalRaw; // 原始subtotal（不进位）
            const subtotalCents = Math.round(subtotalRaw * 100); // SUBTOTAL 显示原始值（转换为分）

            if (isGroupedByDate) {
                // 按日期分组：不计算 Charge 15%，直接显示 Rounding 和 Total
                const roundingAdjustment = calculateRoundingAdjustment(subtotalRaw);
                const roundingCents = Math.round(roundingAdjustment * 100);
                const finalTotalCents = subtotalCents + roundingCents;

                // 填入Rounding（调整量）
                const roundingText = formatCurrencyForPDF(roundingAdjustment);
                page.drawText(roundingText, {
                    x: getRightAlignedX(roundingText, 583, 8),
                    y: height - 701, // Rounding在Total上方
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入最终Total（rounding后的金额）
                const finalTotalText = formatCentsToCurrency(finalTotalCents);
                page.drawText(finalTotalText, {
                    x: getRightAlignedX(finalTotalText, 580, 8),
                    y: height - 717, // 调整到最终Total行
                    size: fontSize,
                    color: textColor,
                    font: boldFont,
                });
            } else {
                // 正常模式：计算subtotal, charge 15%, rounding调整量, 和最终total
                // SUBTOTAL 使用原始值（不进位）
                const chargeCents = Math.round(subtotalCents * 15 / 100);
                const subtotalPlusCharge = subtotalRaw + (chargeCents / 100); // SUBTOTAL + Charge
                const roundingAdjustment = calculateRoundingAdjustment(subtotalPlusCharge);
                const roundingCents = Math.round(roundingAdjustment * 100);
                const finalTotalCents = subtotalCents + chargeCents + roundingCents;

                // 填入Subtotal（原始值）
                const subtotalText = formatCentsToCurrency(subtotalCents);
                page.drawText(subtotalText, {
                    x: getRightAlignedX(subtotalText, 588, 8),
                    y: height - 681, // 调整到Subtotal行
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入Charge 15%
                const chargeText = formatCentsToCurrency(chargeCents);
                page.drawText(chargeText, {
                    x: getRightAlignedX(chargeText, 585.5, 8),
                    y: height - 692, // 调整到Charge行
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入Rounding（调整量）
                const roundingText = formatCurrencyForPDF(roundingAdjustment);
                page.drawText(roundingText, {
                    x: getRightAlignedX(roundingText, 583, 8),
                    y: height - 701, // Rounding在Total上方
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入最终Total（subtotal + charge + rounding）
                const finalTotalText = formatCentsToCurrency(finalTotalCents);
                page.drawText(finalTotalText, {
                    x: getRightAlignedX(finalTotalText, 580, 8),
                    y: height - 717, // 调整到最终Total行
                    size: fontSize,
                    color: textColor,
                    font: boldFont,
                });
            }
        } else if (exportSystem === 'j3') {
            const subtotalRaw = grandTotalRaw; // 原始subtotal（不进位）
            const subtotalCents = Math.round(subtotalRaw * 100); // SUBTOTAL 显示原始值（转换为分）

            if (isGroupedByDate) {
                // 按日期分组：不计算 Charge 15%，直接显示 Rounding 和 Total
                const roundingAdjustment = calculateRoundingAdjustment(subtotalRaw);
                const roundingCents = Math.round(roundingAdjustment * 100);
                const finalTotalCents = subtotalCents + roundingCents;

                // 填入Rounding（调整量）
                const roundingText = formatCurrencyForPDF(roundingAdjustment);
                page.drawText(roundingText, {
                    x: getRightAlignedX(roundingText, 583, 8),
                    y: height - 701, // Rounding在Total上方
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入最终Total（rounding后的金额）
                const finalTotalText = formatCentsToCurrency(finalTotalCents);
                page.drawText(finalTotalText, {
                    x: getRightAlignedX(finalTotalText, 580, 8),
                    y: height - 717, // 调整到最终Total行
                    size: fontSize,
                    color: textColor,
                    font: boldFont,
                });
            } else {
                // 正常模式：计算subtotal, charge 15%, rounding调整量, 和最终total
                // SUBTOTAL 使用原始值（不进位）
                const chargeCents = Math.round(subtotalCents * 15 / 100);
                const subtotalPlusCharge = subtotalRaw + (chargeCents / 100); // SUBTOTAL + Charge
                const roundingAdjustment = calculateRoundingAdjustment(subtotalPlusCharge);
                const roundingCents = Math.round(roundingAdjustment * 100);
                const finalTotalCents = subtotalCents + chargeCents + roundingCents;

                // 填入Subtotal（原始值）
                const subtotalText = formatCentsToCurrency(subtotalCents);
                page.drawText(subtotalText, {
                    x: getRightAlignedX(subtotalText, 588, 8),
                    y: height - 681, // 调整到Subtotal行
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入Charge 15%
                const chargeText = formatCentsToCurrency(chargeCents);
                page.drawText(chargeText, {
                    x: getRightAlignedX(chargeText, 585.5, 8),
                    y: height - 692, // 调整到Charge行
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入Rounding（调整量）
                const roundingText = formatCurrencyForPDF(roundingAdjustment);
                page.drawText(roundingText, {
                    x: getRightAlignedX(roundingText, 583, 8),
                    y: height - 701, // Rounding在Total上方
                    size: smallFontSize,
                    color: textColor,
                });

                // 填入最终Total（subtotal + charge + rounding）
                const finalTotalText = formatCentsToCurrency(finalTotalCents);
                page.drawText(finalTotalText, {
                    x: getRightAlignedX(finalTotalText, 580, 8),
                    y: height - 717, // 调整到最终Total行
                    size: fontSize,
                    color: textColor,
                    font: boldFont,
                });
            }
        } else {
            // J1模板：显示Rounding调整量和总计
            const totalRaw = grandTotalRaw; // 原始总金额（不进位）
            const roundingAdjustment = calculateRoundingAdjustment(totalRaw);
            const totalRounded = roundToNearestFive(totalRaw); // rounding后的总金额
            const totalCents = Math.round(totalRounded * 100);

            // 填入Rounding（调整量）
            const roundingText = formatCurrencyForPDF(roundingAdjustment);
            page.drawText(roundingText, {
                x: getRightAlignedX(roundingText, 575, 8),
                y: height - 700, // Rounding在Total上方
                size: smallFontSize,
                color: textColor,
            });

            // 填入Total（rounding后的金额）
            const totalText = formatCentsToCurrency(totalCents);
            page.drawText(totalText, {
                x: getRightAlignedX(totalText, 574, 8),
                y: height - 717,
                size: fontSize,
                color: textColor,
                font: boldFont,
            });
        }

        // 生成并下载PDF
        const pdfBytes = await pdfDoc.save();

        // 创建下载链接
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `invoice_${exportSystem}_${startDate}_${endDate}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

    } catch (error) {
        console.error('PDF生成失败:', error);
        console.error('错误详情:', {
            message: error.message,
            stack: error.stack,
            exportSystem: exportSystem,
            dataLength: outData ? outData.length : 0
        });
        throw error;
    }
}

// 生成多页PDF发票
async function generateMultiPageInvoicePDF(outData, startDate, endDate, exportSystem, invoiceNumber = '', invoiceDate = '', isGroupedByDate = false) {
    try {
        console.log('开始生成多页PDF发票:', {
            exportSystem,
            dataLength: outData ? outData.length : 0,
            startDate,
            endDate,
            invoiceNumber
        });

        // 如果没有提供发票号码，自动生成一个
        if (!invoiceNumber) {
            invoiceNumber = generateInvoiceNumber(exportSystem);
        }

        // 计算每页可容纳的记录数
        const recordsPerPage = exportSystem === 'j1' ? 27 : 24;
        const totalPages = Math.ceil(outData.length / recordsPerPage);

        console.log(`多页PDF: 总记录数 ${outData.length}, 每页 ${recordsPerPage} 条, 共 ${totalPages} 页`);

        // 加载所需的模板文件
        const templateFiles = [];
        for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
            let templateFile;
            if (pageIndex === 0) {
                // 第一页使用 (1) 模板
                if (exportSystem === 'j2') {
                    templateFile = `../invoice/invoice/j2invoiceMulti(1).pdf?ts=${Date.now()}`;
                } else if (exportSystem === 'j3') {
                    templateFile = `../invoice/invoice/j3invoiceMulti(1).pdf?ts=${Date.now()}`;
                } else {
                    templateFile = `../invoice/invoice/j1invoiceMulti(1).pdf?ts=${Date.now()}`;
                }
            } else {
                // 后续页使用 (2) 模板
                if (exportSystem === 'j2') {
                    templateFile = `../invoice/invoice/j2invoiceMulti(2).pdf?ts=${Date.now()}`;
                } else if (exportSystem === 'j3') {
                    templateFile = `../invoice/invoice/j3invoiceMulti(2).pdf?ts=${Date.now()}`;
                } else {
                    templateFile = `../invoice/invoice/j1invoiceMulti(2).pdf?ts=${Date.now()}`;
                }
            }
            templateFiles.push(templateFile);
        }

        // 使用PDF-lib库来创建最终PDF
        const { PDFDocument, rgb, StandardFonts } = PDFLib;
        const finalPdfDoc = await PDFDocument.create();

        // 嵌入字体
        const boldFont = await finalPdfDoc.embedFont(StandardFonts.HelveticaBold);
        const regularFont = await finalPdfDoc.embedFont(StandardFonts.Helvetica);
        const monoFont = await finalPdfDoc.embedFont(StandardFonts.Courier);
        const monoBoldFont = await finalPdfDoc.embedFont(StandardFonts.CourierBold);

        // 设置字体大小和颜色
        const fontSize = 11;
        const smallFontSize = 9;
        const textColor = rgb(0, 0, 0);
        const whiteColor = rgb(1, 1, 1);

        // 字体对齐辅助函数
        function getRightAlignedX(text, maxX, charWidth = 6) {
            return maxX - (text.length * charWidth);
        }

        function getCenterAlignedX(text, centerX, charWidth = 6) {
            return centerX - (text.length * charWidth / 2);
        }

        // 按小数点对齐（等宽字体下更精确）
        function getDecimalAlignedX(text, anchorX, font, size) {
            const str = String(text ?? '');
            const dotIndex = str.indexOf('.');
            if (dotIndex >= 0) {
                const leftPart = str.substring(0, dotIndex);
                const leftWidth = font.widthOfTextAtSize(leftPart, size);
                return anchorX - leftWidth;
            }
            const width = font.widthOfTextAtSize(str, size);
            return anchorX - width;
        }

        let grandTotalCents = 0;
        let grandTotalRaw = 0; // 原始总金额（不进位）

        // 为每页加载模板并填入数据
        for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
            try {
                // 加载当前页的模板
                const templateResponse = await fetch(templateFiles[pageIndex]);
                if (!templateResponse.ok) {
                    throw new Error(`无法加载模板文件: ${templateFiles[pageIndex]}`);
                }

                const templateBytes = await templateResponse.arrayBuffer();
                const templateDoc = await PDFDocument.load(templateBytes);

                // 复制模板页到最终文档
                const [templatePage] = await finalPdfDoc.copyPages(templateDoc, [0]);
                const page = finalPdfDoc.addPage(templatePage);
                const { width, height } = page.getSize();

                // 填入日期和发票号码（每一页都显示）
                const currentDate = invoiceDate ?
                    new Date(invoiceDate).toLocaleDateString('en-GB') :
                    new Date().toLocaleDateString('en-GB');

                if (exportSystem === 'j1') {
                    // J1模板的日期位置
                    page.drawText(` ${currentDate}`, {
                        x: 495.5,
                        y: height - 110.5,
                        size: fontSize,
                        color: textColor,
                        font: boldFont,
                    });

                    // J1模板的发票号码位置
                    if (invoiceNumber) {
                        page.drawText(invoiceNumber, {
                            x: 500,
                            y: height - 96.5,
                            size: fontSize,
                            color: textColor,
                            font: boldFont,
                        });
                    }
                } else if (exportSystem === 'j2') {
                    // J2模板的日期位置
                    page.drawText(` ${currentDate}`, {
                        x: 495.5,
                        y: height - 110.5,
                        size: fontSize,
                        color: textColor,
                        font: boldFont,
                    });

                    // J2模板的发票号码位置
                    if (invoiceNumber) {
                        page.drawText(invoiceNumber, {
                            x: 500,
                            y: height - 96.5,
                            size: fontSize,
                            color: textColor,
                            font: boldFont,
                        });
                    }
                } else if (exportSystem === 'j3') {
                    // J3模板的日期位置
                    page.drawText(` ${currentDate}`, {
                        x: 495.5,
                        y: height - 110.5,
                        size: fontSize,
                        color: textColor,
                        font: boldFont,
                    });

                    // J3模板的发票号码位置
                    if (invoiceNumber) {
                        page.drawText(invoiceNumber, {
                            x: 500,
                            y: height - 96.5,
                            size: fontSize,
                            color: textColor,
                            font: boldFont,
                        });
                    }
                }

                // 计算当前页的数据范围
                const startIndex = pageIndex * recordsPerPage;
                const endIndex = Math.min(startIndex + recordsPerPage, outData.length);
                const pageData = outData.slice(startIndex, endIndex);

                // 填入数据行
                let yPosition, lineHeight;
                if (exportSystem === 'j1') {
                    if (pageIndex === 0) {
                        yPosition = height - 162; // J1第一页位置
                    } else {
                        yPosition = height - 162;  // J1第二页位置
                    }
                    lineHeight = 20;
                } else if (exportSystem === 'j2') {
                    if (pageIndex === 0) {
                        yPosition = height - 202; // J2第一页位置（原来的位置）
                    } else {
                        yPosition = height - 202; // J2第二页位置（可调整这个数值）
                    }
                    lineHeight = 20;
                } else { // j3
                    if (pageIndex === 0) {
                        yPosition = height - 202; // J3第一页位置
                    } else {
                        yPosition = height - 202; // J3第二页位置
                    }
                    lineHeight = 20;
                }

                pageData.forEach((record, index) => {
                    const itemNumber = isGroupedByDate ? (record.item_number || startIndex + index + 1) : (startIndex + index + 1);
                    let totalRaw, totalRounded;

                    if (isGroupedByDate && record.is_date_group) {
                        // 按日期分组：使用总金额
                        totalRaw = parseFloat(record.total_value) || 0;
                        totalRounded = roundCurrencyValue(totalRaw);
                    } else {
                        // 正常明细：计算金额
                        const outQty = parseFloat(record.out_quantity) || 0;
                        const price = parseFloat(record.price) || 0;
                        totalRaw = outQty * price;
                        totalRounded = roundCurrencyValue(totalRaw);
                    }

                    grandTotalCents += Math.round(totalRounded * 100);
                    grandTotalRaw += totalRaw; // 累计原始金额

                    // NO (第一列)
                    const itemText = itemNumber.toString();
                    page.drawText(itemText, {
                        x: getCenterAlignedX(itemText, 42, 6),
                        y: yPosition,
                        size: smallFontSize,
                        color: textColor,
                    });

                    // Descriptions (第二列)
                    const productName = record.product_name || '';
                    const maxProductNameLength = 25;
                    const displayProductName = productName.length > maxProductNameLength
                        ? productName.substring(0, maxProductNameLength) + '...'
                        : productName;

                    page.drawText(displayProductName.toUpperCase(), {
                        x: 80,
                        y: yPosition,
                        size: smallFontSize,
                        color: textColor,
                    });

                    // Quantity (第三列)（显示三位小数）
                    let qtyText;
                    if (isGroupedByDate && record.is_date_group) {
                        qtyText = '-';
                    } else {
                        const outQty = parseFloat(record.out_quantity) || 0;
                        qtyText = formatNumber(outQty);
                    }
                    page.drawText(qtyText, {
                        x: getDecimalAlignedX(qtyText, 373, monoBoldFont, smallFontSize, 0),
                        y: yPosition,
                        size: smallFontSize,
                        color: textColor,
                        font: monoBoldFont,
                    });

                    // UOM (第四列)
                    let uomText;
                    if (isGroupedByDate && record.is_date_group) {
                        uomText = '-';
                    } else {
                        uomText = record.specification || '';
                    }
                    page.drawText(uomText.toUpperCase(), {
                        x: 406,
                        y: yPosition,
                        size: smallFontSize,
                        color: textColor,
                    });

                    // Price RM (第五列)
                    let priceText;
                    if (isGroupedByDate && record.is_date_group) {
                        priceText = '-';
                    } else {
                        const price = parseFloat(record.price) || 0;
                        priceText = formatCurrencyForPDF(price);
                    }
                    page.drawText(priceText, {
                        x: getDecimalAlignedX(priceText, 488, monoBoldFont, smallFontSize, 0),
                        y: yPosition,
                        size: smallFontSize,
                        color: textColor,
                        font: monoBoldFont,
                    });

                    // Total RM (第六列)
                    const totalText = formatCurrencyForPDF(totalRounded);
                    page.drawText(totalText, {
                        x: getDecimalAlignedX(totalText, 548, monoBoldFont, smallFontSize, 0),
                        y: yPosition,
                        size: smallFontSize,
                        color: textColor,
                        font: monoBoldFont,
                    });

                    yPosition -= lineHeight;
                });

                // 只在最后一页显示总计
                if (pageIndex === totalPages - 1) {
                    if (exportSystem === 'j2') {
                        const subtotalRaw = grandTotalRaw; // 原始subtotal（不进位）
                        const subtotalCents = Math.round(subtotalRaw * 100); // SUBTOTAL 显示原始值（转换为分）

                        if (isGroupedByDate) {
                            // 按日期分组：不计算 Charge 15%，直接显示 Rounding 和 Total
                            const roundingAdjustment = calculateRoundingAdjustment(subtotalRaw);
                            const roundingCents = Math.round(roundingAdjustment * 100);
                            const finalTotalCents = subtotalCents + roundingCents;

                            // 填入Rounding（调整量）
                            const roundingText = formatCurrencyForPDF(roundingAdjustment);
                            page.drawText(roundingText, {
                                x: getRightAlignedX(roundingText, 590, 8),
                                y: height - 701, // Rounding在Total上方
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入最终Total（rounding后的金额）
                            const finalTotalText = formatCentsToCurrency(finalTotalCents);
                            page.drawText(finalTotalText, {
                                x: getRightAlignedX(finalTotalText, 588, 8),
                                y: height - 717,
                                size: fontSize,
                                color: textColor,
                                font: boldFont,
                            });
                        } else {
                            // 正常模式：计算subtotal, charge 15%, rounding调整量, 和最终total
                            // SUBTOTAL 使用原始值（不进位）
                            const chargeCents = Math.round(subtotalCents * 15 / 100);
                            const subtotalPlusCharge = subtotalRaw + (chargeCents / 100); // SUBTOTAL + Charge
                            const roundingAdjustment = calculateRoundingAdjustment(subtotalPlusCharge);
                            const roundingCents = Math.round(roundingAdjustment * 100);
                            const finalTotalCents = subtotalCents + chargeCents + roundingCents;

                            // 填入Subtotal（原始值）
                            const subtotalText = formatCentsToCurrency(subtotalCents);
                            page.drawText(subtotalText, {
                                x: getRightAlignedX(subtotalText, 588, 8),
                                y: height - 681,
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入Charge 15%
                            const chargeText = formatCentsToCurrency(chargeCents);
                            page.drawText(chargeText, {
                                x: getRightAlignedX(chargeText, 585.5, 8),
                                y: height - 692,
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入Rounding（调整量）
                            const roundingText = formatCurrencyForPDF(roundingAdjustment);
                            page.drawText(roundingText, {
                                x: getRightAlignedX(roundingText, 590, 8),
                                y: height - 701, // Rounding在Total上方
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入最终Total（subtotal + charge + rounding）
                            const finalTotalText = formatCentsToCurrency(finalTotalCents);
                            page.drawText(finalTotalText, {
                                x: getRightAlignedX(finalTotalText, 588, 8),
                                y: height - 717,
                                size: fontSize,
                                color: textColor,
                                font: boldFont,
                            });
                        }
                    } else if (exportSystem === 'j3') {
                        const subtotalRaw = grandTotalRaw; // 原始subtotal（不进位）
                        const subtotalCents = Math.round(subtotalRaw * 100); // SUBTOTAL 显示原始值（转换为分）

                        if (isGroupedByDate) {
                            // 按日期分组：不计算 Charge 15%，直接显示 Rounding 和 Total
                            const roundingAdjustment = calculateRoundingAdjustment(subtotalRaw);
                            const roundingCents = Math.round(roundingAdjustment * 100);
                            const finalTotalCents = subtotalCents + roundingCents;

                            // 填入Rounding（调整量）
                            const roundingText = formatCurrencyForPDF(roundingAdjustment);
                            page.drawText(roundingText, {
                                x: getRightAlignedX(roundingText, 583, 8),
                                y: height - 701, // Rounding在Total上方
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入最终Total（rounding后的金额）
                            const finalTotalText = formatCentsToCurrency(finalTotalCents);
                            page.drawText(finalTotalText, {
                                x: getRightAlignedX(finalTotalText, 580, 8),
                                y: height - 717,
                                size: fontSize,
                                color: textColor,
                                font: boldFont,
                            });
                        } else {
                            // 正常模式：计算subtotal, charge 15%, rounding调整量, 和最终total
                            // SUBTOTAL 使用原始值（不进位）
                            const chargeCents = Math.round(subtotalCents * 15 / 100);
                            const subtotalPlusCharge = subtotalRaw + (chargeCents / 100); // SUBTOTAL + Charge
                            const roundingAdjustment = calculateRoundingAdjustment(subtotalPlusCharge);
                            const roundingCents = Math.round(roundingAdjustment * 100);
                            const finalTotalCents = subtotalCents + chargeCents + roundingCents;

                            // 填入Subtotal（原始值）
                            const subtotalText = formatCentsToCurrency(subtotalCents);
                            page.drawText(subtotalText, {
                                x: getRightAlignedX(subtotalText, 588, 8),
                                y: height - 681,
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入Charge 15%
                            const chargeText = formatCentsToCurrency(chargeCents);
                            page.drawText(chargeText, {
                                x: getRightAlignedX(chargeText, 585.5, 8),
                                y: height - 692,
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入Rounding（调整量）
                            const roundingText = formatCurrencyForPDF(roundingAdjustment);
                            page.drawText(roundingText, {
                                x: getRightAlignedX(roundingText, 583, 8),
                                y: height - 701, // Rounding在Total上方
                                size: smallFontSize,
                                color: textColor,
                            });

                            // 填入最终Total（subtotal + charge + rounding）
                            const finalTotalText = formatCentsToCurrency(finalTotalCents);
                            page.drawText(finalTotalText, {
                                x: getRightAlignedX(finalTotalText, 580, 8),
                                y: height - 717,
                                size: fontSize,
                                color: textColor,
                                font: boldFont,
                            });
                        }
                    } else {
                        // J1模板：显示Rounding调整量和总计
                        const totalRaw = grandTotalRaw; // 原始总金额（不进位）
                        const roundingAdjustment = calculateRoundingAdjustment(totalRaw);
                        const totalRounded = roundToNearestFive(totalRaw); // rounding后的总金额
                        const totalCents = Math.round(totalRounded * 100);

                        // 填入Rounding（调整量）
                        const roundingText = formatCurrencyForPDF(roundingAdjustment);
                        page.drawText(roundingText, {
                            x: getRightAlignedX(roundingText, 590, 8),
                            y: height - 700, // Rounding在Total上方
                            size: smallFontSize,
                            color: textColor,
                        });

                        // 填入Total（rounding后的金额）
                        const totalText = formatCentsToCurrency(totalCents);
                        page.drawText(totalText, {
                            x: getRightAlignedX(totalText, 588, 8),
                            y: height - 717,
                            size: fontSize,
                            color: textColor,
                            font: boldFont,
                        });
                    }
                }

            } catch (templateError) {
                console.error(`加载模板 ${templateFiles[pageIndex]} 失败:`, templateError);
                throw new Error(`无法加载模板文件: ${templateFiles[pageIndex]}`);
            }
        }

        // 生成并下载PDF
        const pdfBytes = await finalPdfDoc.save();

        // 创建下载链接
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `invoice_${exportSystem}_multipage_${startDate}_${endDate}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

    } catch (error) {
        console.error('多页PDF生成失败:', error);
        console.error('错误详情:', {
            message: error.message,
            stack: error.stack,
            exportSystem: exportSystem,
            dataLength: outData ? outData.length : 0
        });
        throw error;
    }
}

// 处理导出系统选择变化
function handleExportSystemChange() {
    // 发票号码现在完全自动生成，不需要处理界面变化
    console.log('导出系统已选择:', document.getElementById('export-system').value);
}

// 切换批量删除模式
function toggleBatchDelete() {
    isBatchDeleteMode = true;
    selectedRecords.clear();

    // 显示/隐藏按钮
    document.getElementById('batch-delete-btn').style.display = 'none';
    document.getElementById('confirm-batch-delete-btn').style.display = 'inline-block';
    document.getElementById('cancel-batch-delete-btn').style.display = 'inline-block';

    // 更改表头
    document.getElementById('action-header').textContent = '选择';

    // 保存所有新创建的行
    const newRows = saveNewRows();

    // 重新渲染表格
    renderStockTable();

    // 恢复新创建的行
    restoreNewRows(newRows);

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

    // 保存所有新创建的行
    const newRows = saveNewRows();

    // 重新渲染表格
    renderStockTable();

    // 恢复新创建的行
    restoreNewRows(newRows);
}

// 切换记录选择状态
function toggleRecordSelection(recordId, isSelected) {
    if (isSelected) {
        selectedRecords.add(recordId);
    } else {
        selectedRecords.delete(recordId);
    }

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

// 生成Type选项
function generateTypeOptions(selectedValue = '') {
    const typeOptions = ['Kitchen', 'Sushi Bar', 'Service Line', 'Sake'];
    let options = '<option value="">请选择类型</option>';
    typeOptions.forEach(type => {
        const selected = (type === selectedValue || (type === 'Service Line' && selectedValue === 'Drinks')) ? 'selected' : '';
        options += `<option value="${type}" ${selected}>${type}</option>`;
    });
    return options;
}

// 确认批量删除
async function confirmBatchDelete() {
    if (selectedRecords.size === 0) {
        showAlert('请至少选择一条记录', 'error');
        return;
    }

    if (!confirm(`确定要删除选中的 ${selectedRecords.size} 条记录吗？此操作不可恢复！`)) {
        return;
    }

    const confirmBtn = document.getElementById('confirm-batch-delete-btn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 删除中...';
    confirmBtn.disabled = true;

    try {
        // 使用批量删除 API (现在后端 handleDelete 已支持 ids 参数)
        const idList = Array.from(selectedRecords);
        const result = await apiCall(`?ids=${idList.join(',')}`, {
            method: 'DELETE'
        });

        if (result.success) {
            lastDeletedIds = [...idList];
            showUndoBar(idList.length);
            cancelBatchDelete();

            const newRows = saveNewRows();
            loadStockData().then(() => {
                restoreNewRows(newRows);
            });
        } else {
            showAlert('删除失败: ' + (result.message || '未知错误'), 'error');
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        }
    } catch (error) {
        showAlert('批量删除时发生错误', 'error');
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    }
}

// 更新批量保存按钮的可见性
function updateBatchSaveButtonVisibility() {
    const newRows = document.querySelectorAll('.new-row');
    const batchSaveBtn = document.getElementById('batch-save-btn');

    if (newRows.length >= 2) {
        batchSaveBtn.style.display = 'inline-block';
    } else {
        batchSaveBtn.style.display = 'none';
    }
}

// 批量保存所有新记录
async function batchSaveNewRows() {
    const newRows = document.querySelectorAll('.new-row');

    if (newRows.length === 0) {
        showAlert('没有需要保存的新记录', 'info');
        return;
    }

    // 提取所有行数据并寻找统一的 document_date
    const rowsData = [];
    let commonDate = null;
    try {
        for (const row of newRows) {
            // 获取行的唯一 ID (new-TIMESTAMP-COUNTER)
            const firstInput = row.querySelector('input');
            if (!firstInput || !firstInput.id) continue;

            const idParts = firstInput.id.split('-');
            const rowId = idParts[0] + '-' + idParts[1] + '-' + idParts[2];

            // 获取各行的基本数据
            const codeInput = document.getElementById(`${rowId}-code_number-input`);
            const productInput = document.getElementById(`${rowId}-product_name-input`);
            const receiverInput = document.getElementById(`${rowId}-receiver-input`);

            const rowDate = document.getElementById(`${rowId}-date`) ? document.getElementById(`${rowId}-date`).value : '';
            if (!rowDate) {
                throw new Error('请确保所有行都选择了日期');
            }

            const data = {
                date: rowDate,
                time: new Date().toTimeString().slice(0, 5),
                product_name: productInput ? productInput.value : '',
                in_quantity: parseFloat(document.getElementById(`${rowId}-in-qty`) ? document.getElementById(`${rowId}-in-qty`).value : 0) || 0,
                out_quantity: parseFloat(document.getElementById(`${rowId}-out-qty`) ? document.getElementById(`${rowId}-out-qty`).value : 0) || 0,
                specification: document.getElementById(`${rowId}-specification`) ? document.getElementById(`${rowId}-specification`).value : '',
                price: (() => {
                    const hiddenInput = document.getElementById(`${rowId}-price`);
                    const priceSelect = document.getElementById(`${rowId}-price-select`);
                    if (hiddenInput && hiddenInput.value !== '') return hiddenInput.value;
                    if (priceSelect && priceSelect.value !== '' && priceSelect.value !== 'manual') return priceSelect.value;
                    return hiddenInput ? hiddenInput.value : 0;
                })(),
                receiver: receiverInput ? receiverInput.value : '',
                code_number: codeInput ? codeInput.value : '',
                remark: document.getElementById(`${rowId}-remark`) ? document.getElementById(`${rowId}-remark`).value : '',
                product_remark_checked: document.getElementById(`${rowId}-product-remark`) ? document.getElementById(`${rowId}-product-remark`).checked : false,
                remark_number: document.getElementById(`${rowId}-remark-number`) ? document.getElementById(`${rowId}-remark-number`).value.trim().toUpperCase() : '',
                target_system: document.getElementById(`${rowId}-target`) ? document.getElementById(`${rowId}-target`).value : '',
                type: document.getElementById(`${rowId}-type`) ? document.getElementById(`${rowId}-type`).value : ''
            };

            // 基本验证
            if (!data.product_name || !data.specification || !data.receiver) {
                throw new Error('请确保所有行都填写了货品名称、规格单位和收货人');
            }
            if (data.in_quantity < 0 || data.out_quantity < 0) {
                throw new Error('行记录中存在负数数量');
            }
            if (data.in_quantity <= 0 && data.out_quantity <= 0) {
                throw new Error('每行记录必须至少填入一项进货或出货数量');
            }

            rowsData.push(data);
        }
    } catch (error) {
        showAlert(error.message, 'error');
        return;
    }

    const batchSaveBtn = document.getElementById('batch-save-btn');
    const originalText = batchSaveBtn.innerHTML;
    batchSaveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 校验库存中...';
    batchSaveBtn.disabled = true;

    try {
        // ========== 前端库存预校验 ==========
        const outSummary = {};
        for (const row of rowsData) {
            const outQty = parseFloat(row.out_quantity) || 0;
            if (outQty > 0) {
                const key = (row.product_name || '') + '||' + (row.price || 0);
                if (!outSummary[key]) {
                    outSummary[key] = {
                        product_name: row.product_name,
                        price: row.price || 0,
                        total_out: 0
                    };
                }
                outSummary[key].total_out += outQty;
            }
        }

        // 检查每个产品+价格组合的库存
        for (const key of Object.keys(outSummary)) {
            const item = outSummary[key];
            try {
                const stockResp = await fetch(`${API_BASE_URL}?action=product_stock_by_price&product_name=${encodeURIComponent(item.product_name)}&price=${encodeURIComponent(item.price)}`);
                const stockResult = await stockResp.json();
                if (stockResult.success && stockResult.data) {
                    const availableStock = parseFloat(stockResult.data.available_stock) || 0;
                    if (item.total_out > availableStock) {
                        throw new Error(`产品 [${item.product_name}] (价格 RM${item.price}) 库存不足！可用库存: ${availableStock}，请求出库: ${item.total_out}`);
                    }
                }
            } catch (stockErr) {
                if (stockErr.message.includes('库存不足')) {
                    throw stockErr;
                }
                // 如果库存查询失败，交给后端校验
                console.warn('前端库存预检查失败，将由后端校验:', stockErr);
            }
        }
        // ========== 前端库存预校验结束 ==========

        batchSaveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';

        const payload = {
            action: 'batch_save',
            rows: rowsData
        };

        const response = await fetch(API_BASE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            showAlert(result.message || `成功保存 ${rowsData.length} 条记录`, 'success');

            // 成功后清空所有新行并重新加载
            const tbody = document.getElementById('stock-tbody');
            newRows.forEach(row => row.remove());

            await searchData(); // 刷新表格数据
            updateStats();
            updateBatchSaveButtonVisibility();
        } else {
            showAlert(`保存失败：${result.message}`, 'error');
        }

    } catch (error) {
        console.error('批量保存错误:', error);
        showAlert(error.message || '批量保存时发生网络连接或解析错误', 'error');
    } finally {
        batchSaveBtn.innerHTML = originalText;
        batchSaveBtn.disabled = false;
    }
}
