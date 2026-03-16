
// API 配置
const API_BASE_URL = 'costapi.php';

// 应用状态
let actualData = [];
let allRestaurantsData = {};
let currentRestaurant = null;
let dateRange = {
    startDate: null,
    endDate: null
};
let currentChartDataType = 'totalCost';
let costChart = null;

// 日期选择器状态
let currentDatePicker = null;
let currentDateType = null;
let startDateValue = { year: null, month: null, day: null };
let endDateValue = { year: null, month: null, day: null };
let monthDateValue = { year: null, month: null };

// 日历选择器变量
let calendarCurrentDate = new Date();
let calendarStartDate = null;
let calendarEndDate = null;
let isSelectingRange = false;

// 钻取状态管理
let isDrillDownMode = false;
let originalDateRange = null;
let drillDownMonth = null;

// 餐厅配置
const restaurantConfig = {
    j1: {
        name: 'J1',
        tableName: 'j1cost',
        colors: {
            primary: '#583e04',
            secondary: '#805906'
        }
    },
    j2: {
        name: 'J2',
        tableName: 'j2cost',
        colors: {
            primary: '#583e04',
            secondary: '#805906'
        }
    },
    j3: {
        name: 'J3',
        tableName: 'j3cost',
        colors: {
            primary: '#583e04',
            secondary: '#805906'
        }
    },
    total: {
        name: '总',
        tableName: 'all_restaurants',
        colors: {
            primary: '#583e04',
            secondary: '#805906'
        }
    }
};

// 增强的日期选择器功能
function initEnhancedDatePickers() {
    // 获取当前日期
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1;
    const currentDay = today.getDate();

    // 计算当月1号
    const firstDayOfMonth = new Date(currentYear, currentMonth - 1, 1);
    firstDayOfMonth.setHours(0, 0, 0, 0);

    // 初始化日历选择器默认值为当月1号到今天
    calendarStartDate = new Date(firstDayOfMonth);
    calendarEndDate = new Date(today);

    // 格式化日期范围
    const startYear = firstDayOfMonth.getFullYear();
    const startMonth = firstDayOfMonth.getMonth() + 1;
    const startDay = firstDayOfMonth.getDate();

    // 正确设置dateRange为当月1号到今天
    dateRange = {
        startDate: `${startYear}-${String(startMonth).padStart(2, '0')}-${String(startDay).padStart(2, '0')}`,
        endDate: `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(currentDay).padStart(2, '0')}`
    };

    // 设置开始和结束日期初始值为当月1号到今天
    startDateValue = {
        year: startYear,
        month: startMonth,
        day: startDay
    };

    endDateValue = {
        year: currentYear,
        month: currentMonth,
        day: currentDay
    };

    // 月份选择器初始值为未选择状态（显示"--"）
    monthDateValue = {
        year: null,
        month: null
    };

    // 更新显示
    updateDateDisplay('month');
    updateDateRangeDisplay();

    // 绑定全局点击事件以关闭下拉框
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.enhanced-date-picker')) {
            hideAllDropdowns();
        }
    });
}

// 切换日历显示
function toggleCalendar() {
    const popup = document.getElementById('calendar-popup');
    const picker = document.getElementById('date-range-picker');

    if (popup.style.display === 'none' || !popup.style.display) {
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

    // 设置默认日期范围
    if (!calendarStartDate) {
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth() + 1;
        const firstDayOfMonth = new Date(currentYear, currentMonth - 1, 1);
        const lastDayOfMonth = new Date(currentYear, currentMonth, 0);
        calendarStartDate = new Date(firstDayOfMonth);
        calendarStartDate.setHours(0, 0, 0, 0);
        calendarEndDate = new Date(currentYear, currentMonth - 1, lastDayOfMonth.getDate());
        calendarEndDate.setHours(0, 0, 0, 0);
    }

    // 如果只有开始日期没有结束日期，设置选择状态
    if (calendarStartDate && !calendarEndDate) {
        isSelectingRange = true;
    } else if (calendarStartDate && calendarEndDate) {
        isSelectingRange = false;
    }

    // 根据已选择的开始日期设置日历显示的月份
    if (calendarStartDate) {
        calendarCurrentDate = new Date(calendarStartDate.getFullYear(), calendarStartDate.getMonth(), 1);
    } else {
        calendarCurrentDate = new Date(today.getFullYear(), today.getMonth(), 1);
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
async function selectDate(date) {
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
        await updateDateRange();
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

// 格式化日期为 YYYY-MM-DD
function formatDateToYYYYMMDD(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// 更新dateRange对象
async function updateDateRange() {
    if (calendarStartDate && calendarEndDate) {
        dateRange.startDate = formatDateToYYYYMMDD(calendarStartDate);
        dateRange.endDate = formatDateToYYYYMMDD(calendarEndDate);

        // 更新开始和结束日期选择器的值
        startDateValue = {
            year: calendarStartDate.getFullYear(),
            month: calendarStartDate.getMonth() + 1,
            day: calendarStartDate.getDate()
        };

        endDateValue = {
            year: calendarEndDate.getFullYear(),
            month: calendarEndDate.getMonth() + 1,
            day: calendarEndDate.getDate()
        };

        updateDateDisplay('start');
        updateDateDisplay('end');

        // 新增：退出钻取模式
        if (isDrillDownMode) {
            isDrillDownMode = false;
            drillDownMonth = null;
            originalDateRange = null;
            hideBackButtons();
        }

        // 只有选择了餐厅才加载数据
        if (isRestaurantSelected) {
            await loadData({
                start_date: dateRange.startDate,
                end_date: dateRange.endDate
            });
            updateDashboard();
        }

        // 更新图表日期范围显示
        updateChartDateRange();
    }
}

// 点击外部关闭日历
document.addEventListener('click', function (e) {
    const calendar = document.getElementById('date-range-picker');
    const popup = document.getElementById('calendar-popup');
    if (calendar && popup && !calendar.contains(e.target) && !popup.contains(e.target)) {
        popup.style.display = 'none';
    }
});

function updateDateDisplay(prefix) {
    if (prefix === 'month') {
        // 显示年份，如果未选择显示"--"
        const monthYearDisplay = document.getElementById('month-year-display');
        const monthMonthDisplay = document.getElementById('month-month-display');
        if (monthYearDisplay) {
            monthYearDisplay.textContent = monthDateValue.year || '--';
        }
        if (monthMonthDisplay) {
            monthMonthDisplay.textContent = monthDateValue.month ? String(monthDateValue.month).padStart(2, '0') : '--';
        }
    } else {
        // cost.php 中没有单独的 start/end 日期选择器，只有月份选择器和日期范围选择器
        // 所以这里不需要更新 start/end 的显示
        // 日期范围显示由 updateDateRangeDisplay() 函数处理
    }
}

function showDateDropdown(prefix, type) {
    hideAllDropdowns();

    const dropdown = document.getElementById(`${prefix}-dropdown`);
    const datePicker = document.getElementById(`${prefix}-date-picker`);

    currentDatePicker = prefix;
    currentDateType = type;

    datePicker.querySelectorAll('.date-part').forEach(part => {
        part.classList.remove('active');
    });

    datePicker.querySelector(`[data-type="${type}"]`).classList.add('active');

    generateDropdownContent(prefix, type);

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
    let dateValue;

    if (prefix === 'month') {
        dateValue = monthDateValue;
    } else {
        dateValue = prefix === 'start' ? startDateValue : endDateValue;
    }

    const today = new Date();

    dropdown.innerHTML = '';

    if (type === 'year') {
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
        const monthGrid = document.createElement('div');
        monthGrid.className = 'month-grid';

        const noneOption = document.createElement('div');
        noneOption.className = 'date-option';
        noneOption.textContent = '无';
        noneOption.style.gridColumn = '1 / -1';

        if (!dateValue.month) {
            noneOption.classList.add('selected');
        }

        noneOption.addEventListener('click', function () {
            selectDateValue(prefix, 'month', null);
        });

        monthGrid.appendChild(noneOption);

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
        const dayGrid = document.createElement('div');
        dayGrid.className = 'day-grid';

        const weekdays = ['日', '一', '二', '三', '四', '五', '六'];
        weekdays.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';
            dayHeader.textContent = day;
            dayGrid.appendChild(dayHeader);
        });

        const year = dateValue.year;
        const month = dateValue.month;
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        const startDayOfWeek = firstDay.getDay();

        for (let i = 0; i < startDayOfWeek; i++) {
            const emptyDay = document.createElement('div');
            dayGrid.appendChild(emptyDay);
        }

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
    let dateValue;

    if (prefix === 'month') {
        dateValue = monthDateValue;

        dateValue[type] = value;
        updateDateDisplay('month');
        hideAllDropdowns();
        handleMonthPickerChange();
        return;
    } else {
        dateValue = prefix === 'start' ? startDateValue : endDateValue;

        dateValue[type] = value;

        if (type === 'year' || type === 'month') {
            const daysInMonth = new Date(dateValue.year, dateValue.month, 0).getDate();
            if (dateValue.day > daysInMonth) {
                dateValue.day = daysInMonth;
            }
        }

        updateDateDisplay(prefix);
        hideAllDropdowns();
        updateDateRangeFromPickers();
    }
}

async function handleMonthPickerChange() {
    const year = monthDateValue.year;
    const month = monthDateValue.month;

    if (year && month) {
        const firstDay = `${year}-${String(month).padStart(2, '0')}-01`;
        const lastDay = new Date(year, month, 0).getDate();
        const lastDayFormatted = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;

        dateRange = {
            startDate: firstDay,
            endDate: lastDayFormatted
        };

        // 更新日历选择器
        calendarStartDate = new Date(year, month - 1, 1);
        calendarStartDate.setHours(0, 0, 0, 0);
        calendarEndDate = new Date(year, month - 1, lastDay);
        calendarEndDate.setHours(0, 0, 0, 0);

        startDateValue = {
            year: year,
            month: month,
            day: 1
        };

        endDateValue = {
            year: year,
            month: month,
            day: lastDay
        };

        updateDateDisplay('start');
        updateDateDisplay('end');
        updateDateRangeDisplay();
    }
    else if (year && !month) {
        const firstDay = `${year}-01-01`;
        const lastDay = `${year}-12-31`;

        dateRange = {
            startDate: firstDay,
            endDate: lastDay
        };

        // 更新日历选择器
        calendarStartDate = new Date(year, 0, 1);
        calendarStartDate.setHours(0, 0, 0, 0);
        calendarEndDate = new Date(year, 11, 31);
        calendarEndDate.setHours(0, 0, 0, 0);

        startDateValue = {
            year: year,
            month: 1,
            day: 1
        };

        endDateValue = {
            year: year,
            month: 12,
            day: 31
        };

        updateDateDisplay('start');
        updateDateDisplay('end');
        updateDateRangeDisplay();
    }
    else {
        return;
    }

    if (isDrillDownMode) {
        isDrillDownMode = false;
        drillDownMonth = null;
        originalDateRange = null;
        hideBackButtons();
    }

    if (isRestaurantSelected) {
        await loadData({
            start_date: dateRange.startDate,
            end_date: dateRange.endDate
        });
        updateDashboard();
    }
    document.getElementById('quick-select-text').textContent = '选择时间段';
    updateChartDateRange();
}

async function updateDateRangeFromPickers() {
    const startDateStr = `${startDateValue.year}-${String(startDateValue.month).padStart(2, '0')}-${String(startDateValue.day).padStart(2, '0')}`;
    const endDateStr = `${endDateValue.year}-${String(endDateValue.month).padStart(2, '0')}-${String(endDateValue.day).padStart(2, '0')}`;

    if (new Date(startDateStr) > new Date(endDateStr)) {
        alert('开始日期不能晚于结束日期');
        return;
    }

    dateRange = {
        startDate: startDateStr,
        endDate: endDateStr
    };

    // 更新日历选择器
    calendarStartDate = new Date(startDateValue.year, startDateValue.month - 1, startDateValue.day);
    calendarStartDate.setHours(0, 0, 0, 0);
    calendarEndDate = new Date(endDateValue.year, endDateValue.month - 1, endDateValue.day);
    calendarEndDate.setHours(0, 0, 0, 0);

    if (isDrillDownMode) {
        isDrillDownMode = false;
        drillDownMonth = null;
        originalDateRange = null;
        hideBackButtons();
    }

    if (isRestaurantSelected) {
        await loadData({
            start_date: dateRange.startDate,
            end_date: dateRange.endDate
        });
        updateDashboard();
    }
    document.getElementById('quick-select-text').textContent = '选择时间段';
    updateChartDateRange();
    updateDateRangeDisplay();
}

// 数据获取
async function loadData(params = {}) {
    try {
        const startDate = params.start_date || dateRange.startDate;
        const endDate = params.end_date || dateRange.endDate;

        if (currentRestaurant === 'total') {
            await loadAllRestaurantsData({ start_date: startDate, end_date: endDate });
            actualData = mergeAllRestaurantsData();
        } else {
            const queryParams = new URLSearchParams({
                action: 'list',
                restaurant: currentRestaurant,
                start_date: startDate,
                end_date: endDate
            });

            const result = await apiCall(`?${queryParams}`);
            actualData = result.data || [];
        }
        return actualData;
    } catch (error) {
        console.error('加载数据失败:', error);
        actualData = [];
        return [];
    }
}

async function loadSummary(startDate, endDate) {
    try {
        // 对于所有餐厅（包括total），都尝试调用API获取数据
        try {
            const queryParams = new URLSearchParams({
                action: 'summary',
                restaurant: currentRestaurant,
                start_date: startDate,
                end_date: endDate
            });

            const result = await apiCall(`?${queryParams}`);

            if (result.success && result.data) {
                // 加载库存数据（从月度库存记录获取）
                const stockData = await loadStockData(startDate, endDate);
                return {
                    ...result.data,
                    last_stock: stockData.last_stock,
                    current_stock: stockData.current_stock
                };
            }
        } catch (error) {
            console.error('API汇总失败，使用前端计算:', error);
        }

        // API失败时，使用前端计算
        const filteredData = getFilteredCostData();
        const stockData = await loadStockData(startDate, endDate);

        if (filteredData.length > 0) {
            const summary = {
                total_sales: filteredData.reduce((sum, item) => sum + item.sales, 0),
                total_cost: filteredData.reduce((sum, item) => sum + item.cTotal, 0),
                total_profit: filteredData.reduce((sum, item) => sum + item.grossTotal, 0),
                total_days: filteredData.length,
                last_stock: stockData.last_stock,
                current_stock: stockData.current_stock
            };
            return summary;
        }

        return {
            last_stock: stockData.last_stock,
            current_stock: stockData.current_stock
        };
    } catch (error) {
        console.error('加载汇总数据失败:', error);
        return {
            last_stock: 0,
            current_stock: 0
        };
    }
}

// 加载 J1 供应给 J2 和 J3 的数据（仅用于 J1 餐厅）
async function loadSupplyData(startDate, endDate) {
    try {
        const queryParams = new URLSearchParams({
            action: 'get_supply',
            restaurant: 'j1',
            start_date: startDate,
            end_date: endDate
        });

        const result = await apiCall(`?${queryParams}`);

        if (result.success && result.data) {
            return {
                j2_supply: parseFloat(result.data.supply_to_j2 || 0),
                j3_supply: parseFloat(result.data.supply_to_j3 || 0)
            };
        }

        return {
            j2_supply: 0,
            j3_supply: 0
        };
    } catch (error) {
        console.error('加载供应数据失败:', error);
        return {
            j2_supply: 0,
            j3_supply: 0
        };
    }
}

// 加载库存数据（从月度库存记录）
async function loadStockData(startDate, endDate) {
    try {
        // 获取日期范围内的年月
        const start = new Date(startDate);
        const end = new Date(endDate);

        // 获取当前选择的结束月份的库存（作为 current_stock）
        const currentYearMonth = `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}`;

        // 获取上个月的年月（作为 last_stock）
        const lastMonthDate = new Date(end.getFullYear(), end.getMonth() - 1, 1);
        const lastYearMonth = `${lastMonthDate.getFullYear()}-${String(lastMonthDate.getMonth() + 1).padStart(2, '0')}`;

        let currentStock = 0;
        let lastStock = 0;

        // 对于总计模式，需要汇总所有餐厅的库存
        if (currentRestaurant === 'total') {
            const restaurants = ['j1', 'j2', 'j3'];

            // 获取当前月库存
            for (const restaurant of restaurants) {
                try {
                    const queryParams = new URLSearchParams({
                        action: 'get_month_stock',
                        restaurant: restaurant,
                        year_month: currentYearMonth
                    });
                    const result = await apiCall(`?${queryParams}`);
                    if (result.success && result.data && result.data.current_stock) {
                        currentStock += parseFloat(result.data.current_stock);
                    }
                } catch (error) {
                    console.error(`获取${restaurant}当前库存失败:`, error);
                }
            }

            // 获取上月库存
            for (const restaurant of restaurants) {
                try {
                    const queryParams = new URLSearchParams({
                        action: 'get_month_stock',
                        restaurant: restaurant,
                        year_month: lastYearMonth
                    });
                    const result = await apiCall(`?${queryParams}`);
                    if (result.success && result.data && result.data.current_stock) {
                        lastStock += parseFloat(result.data.current_stock);
                    }
                } catch (error) {
                    console.error(`获取${restaurant}上月库存失败:`, error);
                }
            }
        } else {
            // 单个餐厅模式
            // 获取当前月库存
            try {
                const queryParams = new URLSearchParams({
                    action: 'get_month_stock',
                    restaurant: currentRestaurant,
                    year_month: currentYearMonth
                });
                const result = await apiCall(`?${queryParams}`);
                if (result.success && result.data && result.data.current_stock) {
                    currentStock = parseFloat(result.data.current_stock);
                }
            } catch (error) {
                console.error('获取当前库存失败:', error);
            }

            // 获取上月库存
            try {
                const queryParams = new URLSearchParams({
                    action: 'get_month_stock',
                    restaurant: currentRestaurant,
                    year_month: lastYearMonth
                });
                const result = await apiCall(`?${queryParams}`);
                if (result.success && result.data && result.data.current_stock) {
                    lastStock = parseFloat(result.data.current_stock);
                }
            } catch (error) {
                console.error('获取上月库存失败:', error);
            }
        }

        return {
            current_stock: currentStock,
            last_stock: lastStock
        };
    } catch (error) {
        console.error('加载库存数据失败:', error);
        return {
            current_stock: 0,
            last_stock: 0
        };
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

// 初始化应用
async function initApp() {
    console.log('开始初始化应用...');

    // 初始化增强日期选择器
    initEnhancedDatePickers();

    // 如果餐厅未选择，不加载数据
    if (!isRestaurantSelected) {
        console.log('等待餐厅选择...');
        document.getElementById('total-sales').textContent = '--';
        document.getElementById('total-cost').textContent = '--';
        document.getElementById('gross-total').textContent = '--';
        document.getElementById('cost-percent').textContent = '--';
        document.getElementById('last-stock').textContent = '--';
        document.getElementById('current-stock').textContent = '--';
        document.getElementById('date-info').textContent = '请先选择餐厅';
        return;
    }

    console.log('初始化后的日期范围:', dateRange);

    // 初始化主题色
    updateThemeColors(currentRestaurant);

    // 根据默认日期范围加载数据
    await loadData({
        start_date: dateRange.startDate,
        end_date: dateRange.endDate
    });
    updateDashboard();
}

// 数据转换和过滤
function convertToCostFormat(data) {
    return data.map(item => {
        const sales = parseFloat(item.sales) || 0;
        const cBeverage = parseFloat(item.c_beverage) || 0;
        const cKitchen = parseFloat(item.c_kitchen) || 0;
        const cGrab = parseFloat(item.c_grab) || 0;
        const cFoodpanda = parseFloat(item.c_foodpanda) || 0;
        const cShopee = parseFloat(item.c_shopee) || 0;
        const cTotal = cBeverage + cKitchen + cGrab + cFoodpanda + cShopee;
        const grossTotal = sales - cTotal;
        const costPercent = sales > 0 ? (cTotal / sales) * 100 : 0;

        return {
            date: item.date,
            sales: sales,
            cBeverage: cBeverage,
            cKitchen: cKitchen,
            cGrab: cGrab,
            cFoodpanda: cFoodpanda,
            cShopee: cShopee,
            cTotal: cTotal,
            grossTotal: grossTotal,
            costPercent: costPercent
        };
    });
}

function fillMissingDates(costData) {
    if (!dateRange.startDate || !dateRange.endDate) {
        return costData;
    }

    const start = new Date(dateRange.startDate);
    const end = new Date(dateRange.endDate);

    if (start > end) {
        return [];
    }

    const costDataMap = new Map(costData.map(item => [item.date, item]));
    const filledData = [];

    for (let current = new Date(start); current <= end; current.setDate(current.getDate() + 1)) {
        const dateKey = current.toISOString().split('T')[0];
        if (costDataMap.has(dateKey)) {
            filledData.push(costDataMap.get(dateKey));
        } else {
            filledData.push({
                date: dateKey,
                sales: 0,
                cBeverage: 0,
                cKitchen: 0,
                cGrab: 0,
                cFoodpanda: 0,
                cShopee: 0,
                cTotal: 0,
                grossTotal: 0,
                costPercent: 0
            });
        }
    }

    return filledData;
}

function getFilteredCostData() {
    const costData = fillMissingDates(convertToCostFormat(actualData));
    return costData.filter(item => {
        const itemDate = new Date(item.date);
        const start = new Date(dateRange.startDate);
        const end = new Date(dateRange.endDate);
        return itemDate >= start && itemDate <= end;
    }).sort((a, b) => new Date(a.date) - new Date(b.date));
}

// 更新仪表板
async function updateDashboard() {
    const summary = await loadSummary(dateRange.startDate, dateRange.endDate);
    const filteredData = getFilteredCostData();

    let displaySummary;
    if (filteredData.length > 0) {
        displaySummary = {
            total_sales: filteredData.reduce((sum, item) => sum + item.sales, 0),
            data_total_cost: filteredData.reduce((sum, item) => sum + item.cTotal, 0),
            total_profit: filteredData.reduce((sum, item) => sum + item.grossTotal, 0),
            total_grab_cost: filteredData.reduce((sum, item) => sum + (item.cGrab || 0), 0),
            total_foodpanda_cost: filteredData.reduce((sum, item) => sum + (item.cFoodpanda || 0), 0),
            total_shopee_cost: filteredData.reduce((sum, item) => sum + (item.cShopee || 0), 0),
            total_days: filteredData.length,
            last_stock: parseFloat(summary.last_stock || 0),
            current_stock: parseFloat(summary.current_stock || 0)
        };
    } else {
        displaySummary = {
            total_sales: parseFloat(summary.total_sales || 0),
            data_total_cost: parseFloat(summary.total_cost || 0),
            total_profit: parseFloat(summary.total_profit || 0),
            total_grab_cost: parseFloat(summary.total_grab_cost || 0),
            total_foodpanda_cost: parseFloat(summary.total_foodpanda_cost || 0),
            total_shopee_cost: parseFloat(summary.total_shopee_cost || 0),
            total_days: parseInt(summary.total_days || 0),
            last_stock: parseFloat(summary.last_stock || 0),
            current_stock: parseFloat(summary.current_stock || 0)
        };
    }

    // 计算实际总成本
    let actualTotalCost;
    let j2Supply = 0;
    let j3Supply = 0;

    if (currentRestaurant === 'j1') {
        const supplyData = await loadSupplyData(dateRange.startDate, dateRange.endDate);
        j2Supply = parseFloat(supplyData.j2_supply || 0);
        j3Supply = parseFloat(supplyData.j3_supply || 0);

        actualTotalCost = displaySummary.last_stock - displaySummary.current_stock + displaySummary.data_total_cost - j2Supply - j3Supply;

        document.getElementById('j2-supply').textContent = `${j2Supply.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('j3-supply').textContent = `${j3Supply.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    } else {
        actualTotalCost = displaySummary.last_stock - displaySummary.current_stock + displaySummary.data_total_cost;
    }

    displaySummary.total_cost = actualTotalCost;
    displaySummary.avg_cost_percent = displaySummary.total_sales > 0 ?
        (actualTotalCost / displaySummary.total_sales) * 100 : 0;
    displaySummary.total_profit = displaySummary.total_sales - actualTotalCost;

    // 获取动画相关的各项属性
    const totalSales = displaySummary.total_sales;
    const totalCost = actualTotalCost;
    const grossTotal = displaySummary.total_profit;
    const costPercent = displaySummary.avg_cost_percent;
    const grabCost = displaySummary.total_grab_cost;
    const foodpandaCost = displaySummary.total_foodpanda_cost;
    const shopeeCost = displaySummary.total_shopee_cost;

    // 执行数值动画
    if (typeof animateValue === 'function') {
        animateValue("total-sales", totalSales);
        animateValue("total-cost", totalCost);
        animateValue("gross-total", grossTotal);
        animateValue("cost-percent", costPercent, true);
        animateValue("total-grab-cost", grabCost);
        animateValue("total-foodpanda-cost", foodpandaCost);
        animateValue("total-shopee-cost", shopeeCost);
    } else {
        document.getElementById('total-sales').textContent = `${totalSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('total-cost').textContent = `${totalCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('gross-total').textContent = `${grossTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('cost-percent').textContent = `${costPercent.toFixed(2)}%`;
        if (document.getElementById('total-grab-cost')) document.getElementById('total-grab-cost').textContent = `${grabCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        if (document.getElementById('total-foodpanda-cost')) document.getElementById('total-foodpanda-cost').textContent = `${foodpandaCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        if (document.getElementById('total-shopee-cost')) document.getElementById('total-shopee-cost').textContent = `${shopeeCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    document.getElementById('last-stock').textContent = `${parseFloat(displaySummary.last_stock || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    document.getElementById('current-stock').textContent = `${parseFloat(displaySummary.current_stock || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    document.getElementById('date-info').textContent = `已选择 ${displaySummary.total_days || 0} 天的数据 - ${restaurantConfig[currentRestaurant].name}`;

    const chartTitle = document.getElementById('main-chart-title');
    const titles = {
        costPercent: '成本率趋势',
        grossTotal: '毛利润趋势',
        totalCost: '总成本趋势',
        deliveryCost: '外卖成本趋势'
    };

    let titleText = titles[currentChartDataType] || '总成本趋势';
    if (currentRestaurant === 'total') {
        titleText += ' (三店合计)';
    }
    chartTitle.textContent = titleText;

    updateCharts(filteredData);
    updateDashboardTable(filteredData);
    updateChartDateRange();
}

function updateCharts(data) {
    const ctx1 = document.getElementById('cost-chart').getContext('2d');
    const config = restaurantConfig[currentRestaurant];

    const aggregatedData = aggregateDataByPeriod(data, dateRange);
    const isMonthlyView = aggregatedData !== data;

    // 餐厅颜色配置
    const restaurantColors = {
        j1: {
            primary: '#583e04',
            secondary: '#805906'
        },
        j2: {
            primary: '#d97706',
            secondary: '#f59e0b'
        },
        j3: {
            primary: '#dc2626',
            secondary: '#f87171'
        }
    };

    if (costChart) {
        costChart.destroy();
    }

    if (currentRestaurant === 'total') {
        // 总计模式：显示三间餐厅的对比数据
        const comparisonData = prepareMonthlyComparisonData();

        const chartLabels = comparisonData.isMonthly ?
            comparisonData.dates :
            comparisonData.dates.map(date => new Date(date).getDate().toString());

            const dataLabels = {
            totalCost: ['J1 总成本', 'J2 总成本', 'J3 总成本'],
            grossTotal: ['J1 毛利润', 'J2 毛利润', 'J3 毛利润'],
            costPercent: ['J1 成本率', 'J2 成本率', 'J3 成本率']
        };

        let baseDatasets = [];

        if (currentChartDataType === 'deliveryCost') {
            const grabData = comparisonData.dates.map((_, i) => {
                const j1 = comparisonData.restaurants.j1[i] || createEmptyCostDataPoint();
                const j2 = comparisonData.restaurants.j2[i] || createEmptyCostDataPoint();
                const j3 = comparisonData.restaurants.j3[i] || createEmptyCostDataPoint();
                return (j1.cGrab || 0) + (j2.cGrab || 0) + (j3.cGrab || 0);
            });
            const fpData = comparisonData.dates.map((_, i) => {
                const j1 = comparisonData.restaurants.j1[i] || createEmptyCostDataPoint();
                const j2 = comparisonData.restaurants.j2[i] || createEmptyCostDataPoint();
                const j3 = comparisonData.restaurants.j3[i] || createEmptyCostDataPoint();
                return (j1.cFoodpanda || 0) + (j2.cFoodpanda || 0) + (j3.cFoodpanda || 0);
            });
            const shopeeData = comparisonData.dates.map((_, i) => {
                const j1 = comparisonData.restaurants.j1[i] || createEmptyCostDataPoint();
                const j2 = comparisonData.restaurants.j2[i] || createEmptyCostDataPoint();
                const j3 = comparisonData.restaurants.j3[i] || createEmptyCostDataPoint();
                return (j1.cShopee || 0) + (j2.cShopee || 0) + (j3.cShopee || 0);
            });

            baseDatasets = [
                {
                    label: 'Grab 总成本',
                    data: grabData,
                    borderColor: '#00B14F',
                    backgroundColor: function (context) {
                        const { ctx, chartArea } = context.chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(0, 177, 79, 0.3)');
                        gradient.addColorStop(1, 'rgba(0, 177, 79, 0.05)');
                        return gradient;
                    },
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 6
                },
                {
                    label: 'Foodpanda 总成本',
                    data: fpData,
                    borderColor: '#D70F64',
                    backgroundColor: function (context) {
                        const { ctx, chartArea } = context.chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(215, 15, 100, 0.3)');
                        gradient.addColorStop(1, 'rgba(215, 15, 100, 0.05)');
                        return gradient;
                    },
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 6
                },
                {
                    label: 'Shopee 总成本',
                    data: shopeeData,
                    borderColor: '#EE4D2D',
                    backgroundColor: function (context) {
                        const { ctx, chartArea } = context.chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(238, 77, 45, 0.3)');
                        gradient.addColorStop(1, 'rgba(238, 77, 45, 0.05)');
                        return gradient;
                    },
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 6
                }
            ];
        } else {
            baseDatasets = [
                    {
                        label: dataLabels[currentChartDataType][0],
                        data: comparisonData.restaurants.j1.map(item => getChartDataByType(item, currentChartDataType)),
                        borderColor: restaurantColors.j1.primary,
                        backgroundColor: function (context) {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;

                            if (!chartArea) {
                                return null;
                            }

                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(88, 62, 4, 0.3)');
                            gradient.addColorStop(1, 'rgba(88, 62, 4, 0.05)');

                            return gradient;
                        },
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: dataLabels[currentChartDataType][1],
                        data: comparisonData.restaurants.j2.map(item => getChartDataByType(item, currentChartDataType)),
                        borderColor: restaurantColors.j2.primary,
                        backgroundColor: function (context) {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;

                            if (!chartArea) {
                                return null;
                            }

                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(217, 119, 6, 0.3)');
                            gradient.addColorStop(1, 'rgba(217, 119, 6, 0.05)');

                            return gradient;
                        },
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: dataLabels[currentChartDataType][2],
                        data: comparisonData.restaurants.j3.map(item => getChartDataByType(item, currentChartDataType)),
                        borderColor: restaurantColors.j3.primary,
                        backgroundColor: function (context) {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;

                            if (!chartArea) {
                                return null;
                            }

                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(220, 38, 38, 0.3)');
                            gradient.addColorStop(1, 'rgba(220, 38, 38, 0.05)');

                            return gradient;
                        },
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }
                ];

            if (currentChartDataType === 'costPercent') {
                const threeStoreDeliveryRates = comparisonData.dates.map((_, i) => {
                    const j1 = comparisonData.restaurants.j1[i] || createEmptyCostDataPoint();
                    const j2 = comparisonData.restaurants.j2[i] || createEmptyCostDataPoint();
                    const j3 = comparisonData.restaurants.j3[i] || createEmptyCostDataPoint();
                    const sumDelivery = (j1.cGrab || 0) + (j1.cFoodpanda || 0) + (j1.cShopee || 0) +
                                      (j2.cGrab || 0) + (j2.cFoodpanda || 0) + (j2.cShopee || 0) +
                                      (j3.cGrab || 0) + (j3.cFoodpanda || 0) + (j3.cShopee || 0);
                    const sumSales = (j1.sales || 0) + (j2.sales || 0) + (j3.sales || 0);
                    return sumSales > 0 ? (sumDelivery / sumSales) * 100 : 0;
                });

                baseDatasets.push({
                    label: '三店外卖率',
                    data: threeStoreDeliveryRates,
                    borderColor: '#10b981', // Emerald 500 for distinct visibility
                    backgroundColor: function (context) {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
                        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');
                        return gradient;
                    },
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    borderDash: [5, 5] // Dashed line to differentiate from cost lines
                });
            }
        }

        costChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: baseDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: getYAxisFormatter(currentChartDataType)
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function (context) {
                                if (context.length > 0) {
                                    const dataIndex = context[0].dataIndex;
                                    if (comparisonData.isMonthly) {
                                        return comparisonData.dates[dataIndex];
                                    } else {
                                        const originalDates = Object.values(allRestaurantsData).flat()
                                            .map(item => item.date)
                                            .filter((date, index, self) => self.indexOf(date) === index)
                                            .sort();
                                        const filteredOriginalDates = originalDates.filter(date => {
                                            const itemDate = new Date(date);
                                            const start = new Date(dateRange.startDate);
                                            const end = new Date(dateRange.endDate);
                                            return itemDate >= start && itemDate <= end;
                                        });
                                        const date = filteredOriginalDates[dataIndex];
                                        return `${date} (${new Date(date).getDate()}号)`;
                                    }
                                }
                                return '';
                            },
                            label: getTooltipFormatter(currentChartDataType),
                            afterBody: function (context) {
                                if (context.length > 0) {
                                    const dataIndex = context[0].dataIndex;
                                    const j1Data = comparisonData.restaurants.j1[dataIndex];
                                    const j2Data = comparisonData.restaurants.j2[dataIndex];
                                    const j3Data = comparisonData.restaurants.j3[dataIndex];

                                    const periodText = comparisonData.isMonthly ? '当月汇总' : '当日汇总';

                                    // 根据当前选择的数据类型显示对应的汇总
                                    let summaryText = '';
                                    switch (currentChartDataType) {
                                        case 'totalCost':
                                            const totalCost = j1Data.cTotal + j2Data.cTotal + j3Data.cTotal;
                                            summaryText = `总成本: RM ${totalCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                            break;
                                        case 'grossTotal':
                                            const totalGross = j1Data.grossTotal + j2Data.grossTotal + j3Data.grossTotal;
                                            summaryText = `总毛利润: RM ${totalGross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                            break;
                                        case 'costPercent':
                                            const totalSales = j1Data.sales + j2Data.sales + j3Data.sales;
                                            const totalCostSum = j1Data.cTotal + j2Data.cTotal + j3Data.cTotal;
                                            const avgCostPercent = totalSales > 0 ? ((totalCostSum / totalSales) * 100).toFixed(2) : '0.00';
                                            summaryText = `平均成本率: ${avgCostPercent}%`;
                                            break;
                                        case 'deliveryCost':
                                            const grabTot = (j1Data.cGrab || 0) + (j2Data.cGrab || 0) + (j3Data.cGrab || 0);
                                            const fpTot = (j1Data.cFoodpanda || 0) + (j2Data.cFoodpanda || 0) + (j3Data.cFoodpanda || 0);
                                            const shoTot = (j1Data.cShopee || 0) + (j2Data.cShopee || 0) + (j3Data.cShopee || 0);
                                            const delivTotal = grabTot + fpTot + shoTot;
                                            summaryText = `外卖总成本: RM ${delivTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                            break;
                                    }

                                    return [
                                        '',
                                        `--- ${periodText} ---`,
                                        summaryText
                                    ];
                                }
                                return [];
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    } else {
        // 单店模式
        const chartLabels = isMonthlyView ?
            aggregatedData.map(item => item.displayDate) :
            aggregatedData.map(item => new Date(item.date).getDate().toString());

        const dataLabel = {
            costPercent: '成本率',
            grossTotal: '毛利润',
            totalCost: '总成本'
        };

        let baseDatasets = [];

        if (currentChartDataType === 'deliveryCost') {
            baseDatasets = [
                {
                    label: 'Grab 成本',
                    data: aggregatedData.map(item => item.cGrab || 0),
                    borderColor: '#00B14F',
                    backgroundColor: function (context) {
                        const { ctx, chartArea } = context.chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(0, 177, 79, 0.4)');
                        gradient.addColorStop(1, 'rgba(0, 177, 79, 0.02)');
                        return gradient;
                    },
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 8
                },
                {
                    label: 'Foodpanda 成本',
                    data: aggregatedData.map(item => item.cFoodpanda || 0),
                    borderColor: '#D70F64',
                    backgroundColor: function (context) {
                        const { ctx, chartArea } = context.chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(215, 15, 100, 0.4)');
                        gradient.addColorStop(1, 'rgba(215, 15, 100, 0.02)');
                        return gradient;
                    },
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 8
                },
                {
                    label: 'Shopee 成本',
                    data: aggregatedData.map(item => item.cShopee || 0),
                    borderColor: '#EE4D2D',
                    backgroundColor: function (context) {
                        const { ctx, chartArea } = context.chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(238, 77, 45, 0.4)');
                        gradient.addColorStop(1, 'rgba(238, 77, 45, 0.02)');
                        return gradient;
                    },
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 8
                }
            ];
        } else {
            baseDatasets = [{
                label: dataLabel[currentChartDataType],
                data: aggregatedData.map(item => getChartDataByType(item, currentChartDataType)),
                borderColor: config.colors.primary,
                backgroundColor: function (context) {
                    const chart = context.chart;
                    const { ctx, chartArea } = chart;

                    if (!chartArea) {
                        return null;
                    }

                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(88, 62, 4, 0.4)');
                    gradient.addColorStop(0.3, 'rgba(88, 62, 4, 0.2)');
                    gradient.addColorStop(0.7, 'rgba(88, 62, 4, 0.1)');
                    gradient.addColorStop(1, 'rgba(88, 62, 4, 0.02)');

                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 8
            }];
        }

        costChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: baseDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: getYAxisFormatter(currentChartDataType)
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: getTooltipFormatter(currentChartDataType)
                        }
                    }
                }
            }
        });
    }
}

function updateDashboardTable(data) {
    const tbody = document.querySelector('#dashboard-table tbody');
    tbody.innerHTML = '';

    const tableHeader = document.getElementById('table-header');
    const firstHeader = tableHeader.querySelector('th');
    if (currentRestaurant === 'total') {
        firstHeader.textContent = '日期 (三店合计)';
    } else {
        firstHeader.textContent = '日期';
    }

    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
                    <td>${item.date}</td>
                    <td>RM ${item.sales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>RM ${item.cBeverage.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>RM ${item.cKitchen.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>RM ${(item.cGrab || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>RM ${(item.cFoodpanda || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>RM ${(item.cShopee || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>RM ${item.cTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>RM ${item.grossTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>${item.costPercent.toFixed(2)}%</td>
                `;
        tbody.appendChild(row);
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 添加数据聚合函数
function aggregateDataByPeriod(data, dateRange) {
    const startDate = new Date(dateRange.startDate);
    const endDate = new Date(dateRange.endDate);
    const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));

    if (daysDiff > 60) {
        return aggregateByMonth(data);
    } else {
        return data;
    }
}

// 按月聚合数据
function aggregateByMonth(data) {
    const monthMap = new Map();

    data.forEach(item => {
        const date = new Date(item.date);
        const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

        if (!monthMap.has(monthKey)) {
            monthMap.set(monthKey, {
                date: monthKey,
                displayDate: `${date.getFullYear()}年${date.getMonth() + 1}月`,
                sales: 0,
                cBeverage: 0,
                cKitchen: 0,
                cGrab: 0,
                cFoodpanda: 0,
                cShopee: 0,
                cTotal: 0,
                grossTotal: 0,
                daysCount: 0
            });
        }

        const monthData = monthMap.get(monthKey);
        monthData.sales += item.sales;
        monthData.cBeverage += item.cBeverage;
        monthData.cKitchen += item.cKitchen;
        monthData.cGrab += (item.cGrab || 0);
        monthData.cFoodpanda += (item.cFoodpanda || 0);
        monthData.cShopee += (item.cShopee || 0);
        monthData.cTotal += item.cTotal;
        monthData.grossTotal += item.grossTotal;
        monthData.daysCount += 1;
    });

    return Array.from(monthMap.values()).map(item => ({
        ...item,
        costPercent: item.sales > 0 ? (item.cTotal / item.sales) * 100 : 0
    })).sort((a, b) => a.date.localeCompare(b.date));
}

// 更新主题颜色
function updateThemeColors(restaurant) {
    const config = restaurantConfig[restaurant];
    const root = document.documentElement;

    root.style.setProperty('--primary-color', config.colors.primary);
    root.style.setProperty('--secondary-color', config.colors.secondary);
}

// 格式化日期显示
function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate();
    return `${year}年${month}月${day}日`;
}

// 更新图表日期范围显示
function updateChartDateRange() {
    const chartDateRange = document.getElementById('chart-date-range');
    if (!chartDateRange) return;

    const startDateFormatted = formatDateForDisplay(dateRange.startDate);
    const endDateFormatted = formatDateForDisplay(dateRange.endDate);

    if (dateRange.startDate === dateRange.endDate) {
        chartDateRange.textContent = startDateFormatted;
    } else {
        chartDateRange.textContent = `${startDateFormatted} 至 ${endDateFormatted}`;
    }
}

function switchChartData(dataType) {
    currentChartDataType = dataType;

    document.querySelectorAll('.chart-data-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-type="${dataType}"]`).classList.add('active');

    const chartTitle = document.getElementById('main-chart-title');
    const titles = {
        costPercent: '成本率趋势',
        grossTotal: '毛利润趋势',
        totalCost: '总成本趋势',
        deliveryCost: '外卖成本趋势'
    };

    let titleText = titles[dataType];
    if (currentRestaurant === 'total') {
        titleText += ' (三店合计)';
    }
    chartTitle.textContent = titleText;

    const filteredData = getFilteredCostData();
    updateCharts(filteredData);
}

function getChartDataByType(item, dataType) {
    switch (dataType) {
        case 'costPercent':
            return item.costPercent;
        case 'grossTotal':
            return item.grossTotal;
        case 'totalCost':
            return item.cTotal;
        default:
            return item.costPercent;
    }
}

function getYAxisFormatter(dataType) {
    switch (dataType) {
        case 'costPercent':
            return function (value) {
                return value.toFixed(2) + '%';
            };
        case 'grossTotal':
        case 'totalCost':
        case 'deliveryCost':
            return function (value) {
                return 'RM ' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };
        default:
            return function (value) {
                return value.toString();
            };
    }
}

function getTooltipFormatter(dataType) {
    switch (dataType) {
        case 'costPercent':
            return function (context) {
                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
            };
        case 'grossTotal':
        case 'totalCost':
        case 'deliveryCost':
            return function (context) {
                return context.dataset.label + ': RM ' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };
        default:
            return function (context) {
                return context.dataset.label + ': ' + context.parsed.y;
            };
    }
}

// 加载所有餐厅数据
async function loadAllRestaurantsData(params = {}) {
    try {
        // 根据当前选择的字母确定要加载的餐厅
        const restaurants = [`${currentLetter.toLowerCase()}1`, `${currentLetter.toLowerCase()}2`, `${currentLetter.toLowerCase()}3`];

        // 确保有有效的日期参数
        const startDate = params.start_date || dateRange.startDate;
        const endDate = params.end_date || dateRange.endDate;

        const promises = restaurants.map(async (restaurant) => {
            const queryParams = new URLSearchParams({
                action: 'list',
                restaurant: restaurant,
                start_date: startDate,
                end_date: endDate
            });

            try {
                const result = await apiCall(`?${queryParams}`);
                return { restaurant, data: result.data || [] };
            } catch (error) {
                console.error(`加载${restaurant}数据失败:`, error);
                return { restaurant, data: [] };
            }
        });

        const results = await Promise.all(promises);

        // 存储各餐厅数据
        allRestaurantsData = {};
        results.forEach(({ restaurant, data }) => {
            allRestaurantsData[restaurant] = data;
        });

        return allRestaurantsData;
    } catch (error) {
        console.error('加载所有餐厅数据失败:', error);
        allRestaurantsData = {};
        return {};
    }
}

// 合并所有餐厅数据
function mergeAllRestaurantsData() {
    const dateMap = new Map();

    // 遍历所有餐厅数据
    Object.values(allRestaurantsData).forEach(restaurantData => {
        restaurantData.forEach(item => {
            const date = item.date;
            if (!dateMap.has(date)) {
                dateMap.set(date, {
                    date: date,
                    sales: 0,
                    c_beverage: 0,
                    c_kitchen: 0,
                    c_grab: 0,
                    c_foodpanda: 0,
                    c_shopee: 0
                });
            }

            const existing = dateMap.get(date);
            existing.sales += parseFloat(item.sales) || 0;
            existing.c_beverage += parseFloat(item.c_beverage) || 0;
            existing.c_kitchen += parseFloat(item.c_kitchen) || 0;
            existing.c_grab += parseFloat(item.c_grab) || 0;
            existing.c_foodpanda += parseFloat(item.c_foodpanda) || 0;
            existing.c_shopee += parseFloat(item.c_shopee) || 0;
        });
    });

    // 转换为数组并排序
    return Array.from(dateMap.values()).sort((a, b) => new Date(a.date) - new Date(b.date));
}

// 为总计模式准备对比数据
function prepareMonthlyComparisonData() {
    if (currentRestaurant !== 'total' || !allRestaurantsData) {
        return null;
    }

    const restaurants = ['j1', 'j2', 'j3'];
    const restaurantDataConverted = {};

    // 先转换每个餐厅的数据格式
    restaurants.forEach(restaurant => {
        const restaurantData = allRestaurantsData[restaurant] || [];
        restaurantDataConverted[restaurant] = fillMissingDates(convertToCostFormat(restaurantData));
    });

    // 获取所有日期并过滤
    const dateSet = new Set();
    Object.values(restaurantDataConverted).forEach(data => {
        data.forEach(item => dateSet.add(item.date));
    });

    const sortedDates = Array.from(dateSet).sort();
    const filteredDates = sortedDates.filter(date => {
        const itemDate = new Date(date);
        const start = new Date(dateRange.startDate);
        const end = new Date(dateRange.endDate);
        return itemDate >= start && itemDate <= end;
    });

    // 为每个餐厅创建过滤后的数据
    const filteredRestaurantData = {};
    restaurants.forEach(restaurant => {
        filteredRestaurantData[restaurant] = restaurantDataConverted[restaurant].filter(item => {
            const itemDate = new Date(item.date);
            const start = new Date(dateRange.startDate);
            const end = new Date(dateRange.endDate);
            return itemDate >= start && itemDate <= end;
        }).sort((a, b) => new Date(a.date) - new Date(b.date));
    });

    // 判断是否需要按月聚合
    const startDate = new Date(dateRange.startDate);
    const endDate = new Date(dateRange.endDate);
    const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));

    if (daysDiff > 60) {
        // 按月聚合
        const aggregatedData = {};
        restaurants.forEach(restaurant => {
            aggregatedData[restaurant] = aggregateByMonth(filteredRestaurantData[restaurant]);
        });

        // 获取所有月份
        const monthSet = new Set();
        Object.values(aggregatedData).forEach(data => {
            data.forEach(item => monthSet.add(item.date));
        });
        const months = Array.from(monthSet).sort();

        return {
            dates: months.map(monthKey => {
                const [year, month] = monthKey.split('-');
                return `${year}年${parseInt(month)}月`;
            }),
            restaurants: {
                j1: months.map(monthKey => aggregatedData.j1.find(item => item.date === monthKey) || createEmptyCostDataPoint()),
                j2: months.map(monthKey => aggregatedData.j2.find(item => item.date === monthKey) || createEmptyCostDataPoint()),
                j3: months.map(monthKey => aggregatedData.j3.find(item => item.date === monthKey) || createEmptyCostDataPoint())
            },
            isMonthly: true
        };
    } else {
        // 按天显示
        return {
            dates: filteredDates,
            restaurants: {
                j1: filteredDates.map(date => filteredRestaurantData.j1.find(item => item.date === date) || createEmptyCostDataPoint()),
                j2: filteredDates.map(date => filteredRestaurantData.j2.find(item => item.date === date) || createEmptyCostDataPoint()),
                j3: filteredDates.map(date => filteredRestaurantData.j3.find(item => item.date === date) || createEmptyCostDataPoint())
            },
            isMonthly: false
        };
    }
}

// 创建空成本数据点的辅助函数
function createEmptyCostDataPoint() {
    return {
        sales: 0,
        cBeverage: 0,
        cKitchen: 0,
        cGrab: 0,
        cFoodpanda: 0,
        cShopee: 0,
        cTotal: 0,
        grossTotal: 0,
        costPercent: 0
    };
}

function hideBackButtons() { }
function exitDrillDown() { }

// 快速选择下拉菜单控制
function toggleQuickSelectDropdown() {
    const dropdown = document.getElementById('quick-select-dropdown');
    hideAllDropdowns();
    dropdown.classList.toggle('show');
}

async function selectQuickRange(range) {
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

    // 更新日历选择器
    calendarStartDate = new Date(startDate);
    calendarStartDate.setHours(0, 0, 0, 0);
    calendarEndDate = new Date(endDate);
    calendarEndDate.setHours(0, 0, 0, 0);

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

    monthDateValue = {
        year: null,
        month: null
    };

    updateDateDisplay('start');
    updateDateDisplay('end');
    updateDateDisplay('month');
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

    if (isDrillDownMode) {
        isDrillDownMode = false;
        drillDownMonth = null;
        originalDateRange = null;
        hideBackButtons();
    }

    if (isRestaurantSelected) {
        await loadData({
            start_date: dateRange.startDate,
            end_date: dateRange.endDate
        });
        updateDashboard();
    }
    updateChartDateRange();
}

// 切换报表类型下拉菜单
function toggleReportTypeDropdown() {
    const dropdown = document.getElementById('report-type-dropdown');
    dropdown.classList.toggle('show');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.enhanced-date-picker')) {
        hideAllDropdowns();
    }

    if (!e.target.closest('.number-dropdown')) {
        document.getElementById('number-dropdown').classList.remove('show');
    }

    // 关闭快速选择下拉菜单
    if (!e.target.closest('.dropdown')) {
        document.getElementById('quick-select-dropdown').classList.remove('show');
    }

    // 关闭报表类型下拉菜单
    if (!e.target.closest('.report-type-selector')) {
        const reportDropdown = document.getElementById('report-type-dropdown');
        if (reportDropdown) {
            reportDropdown.classList.remove('show');
        }
    }
});

// 切换下拉菜单
function toggleRestaurantDropdown() {
    const dropdown = document.getElementById('restaurant-dropdown');
    dropdown.classList.toggle('show');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.restaurant-selector')) {
        const dropdown = document.getElementById('restaurant-dropdown');
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            hideNumberOptions();
        }
    }
});

function switchRestaurant(restaurant) {
    if (currentRestaurant === restaurant) return;

    currentRestaurant = restaurant;

    document.querySelectorAll('.restaurant-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.restaurant === restaurant) {
            btn.classList.add('active');
        }
    });

    document.body.className = `restaurant-${restaurant}`;
    updateThemeColors(restaurant);

    loadData().then(() => {
        updateDashboard();
    });
}

// 当前选择的字母和数字
let currentLetter = null;
let currentNumber = null;
let isRestaurantSelected = false;

function showNumberOptions(letter) {
    currentLetter = letter;

    document.querySelectorAll('.letter-item').forEach(item => {
        item.classList.remove('selected');
    });
    document.querySelector(`[onclick*="'${letter}'"]`).classList.add('selected');

    const numberSelection = document.getElementById('number-selection');
    const sectionTitle = numberSelection.querySelector('.section-title');
    const numberGrid = numberSelection.querySelector('.number-grid');

    sectionTitle.textContent = `选择${letter}分店`;

    numberGrid.innerHTML = '';

    if (letter === 'J') {
        numberGrid.innerHTML = `
                    <button class="number-item" onclick="selectRestaurant('1')">1</button>
                    <button class="number-item" onclick="selectRestaurant('2')">2</button>
                    <button class="number-item" onclick="selectRestaurant('3')">3</button>
                    <button class="number-item total-option" onclick="selectRestaurant('total')">总</button>
                `;
    }

    numberSelection.style.visibility = 'visible';
    numberSelection.style.opacity = '1';
}

async function selectRestaurant(number) {
    currentNumber = number;
    isRestaurantSelected = true;

    if (number === 'total') {
        currentRestaurant = 'total';
        updateRestaurantButton(`${currentLetter}总`);
    } else {
        currentRestaurant = `${currentLetter.toLowerCase()}${number}`;
        updateRestaurantButton(`${currentLetter}${number}`);
    }

    document.getElementById('restaurant-dropdown').classList.remove('show');
    updateThemeColors(currentRestaurant);

    // 更新 body 类以控制供应卡片显示
    document.body.className = `restaurant-${currentRestaurant}`;

    await loadData();
    updateDashboard();
}

function selectLetter(letter) {
    showNumberOptions(letter);
}

function hideNumberOptions() {
    const numberSelection = document.getElementById('number-selection');
    const sectionTitle = numberSelection.querySelector('.section-title');
    const numberGrid = numberSelection.querySelector('.number-grid');

    numberSelection.style.visibility = 'hidden';
    numberSelection.style.opacity = '0';

    sectionTitle.textContent = '选择餐厅';
    numberGrid.innerHTML = '';

    document.querySelectorAll('.letter-item').forEach(item => {
        item.classList.remove('selected');
    });

    currentLetter = null;
}

function updateRestaurantButton(text) {
    const restaurantBtn = document.querySelector('.restaurant-btn');
    restaurantBtn.innerHTML = `${text} <i class="fas fa-chevron-down"></i>`;
}