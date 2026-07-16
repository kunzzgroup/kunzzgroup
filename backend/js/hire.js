function isHireReactV2Page() {
    return /\/hire-v2(?:\/|$|\?)/.test(window.location.pathname || '');
}

function getHireBackendBase() {
    if (window.__KUNZZ_BACKEND_BASE__) {
        return String(window.__KUNZZ_BACKEND_BASE__).replace(/\/$/, '');
    }
    const path = window.location.pathname || '';
    const match = path.match(/^(.*?\/backend)(?:\/|$)/);
    if (match) return match[1];
    return '/backend';
}

function getHireApiUrl(query = '') {
    return `${getHireBackendBase()}/hireapi.php${query}`;
}

function getHireResumeUrl(id) {
    return `${getHireBackendBase()}/resume.php?id=${encodeURIComponent(id)}`;
}

function fetchHireApi(url, options = {}) {
    return fetch(url, { credentials: 'include', ...options });
}

function showToast(message, type = 'info') {
    console.warn(`[hire] ${type}: ${message}`);
    if (type === 'error' && typeof window.alert === 'function') {
        window.alert(message);
    }
}

let hireBooted = false;

function bootHire() {
    if (hireBooted) return;
    const root = document.querySelector('[data-hire-content-root]');
    if (!root) return;
    hireBooted = true;
    bootHirePage();
}
// ── 静态配置 ───────────────────────────────────────────────────────────
const companyJobsMap = {
    'KUNZZ HOLDINGS': ['人事部', '技术部', '销售部', '设计部'],
    'TOKYO JAPANESE CUISINE': ['服务员', '厨师', '寿司师傅', '店长'],
    'TOKYO IZAKAYA': ['店长', '服务员', '厨师', '寿司师傅']
};
const statusConfig = [
    { val: '0', label: '待处理', icon: '🔴' },
    { val: '1', label: '沟通中', icon: '🟡' },
    { val: '2', label: '已录用', icon: '🟢' },
    { val: '3', label: '已淘汰', icon: '⚪' }
];

// ── 全局数据 ───────────────────────────────────────────────────────────
let allData = [];   // 当前过滤后的申请记录
let rawData = [];   // 全量未过滤数据（用于公司/职位 chip 计数）
let statsData = { total: 0, '0': 0, '1': 0, '2': 0, '3': 0 };  // 统计数据

// 核心：全局状态管理
let state = {
    keyword: '',
    company: '',
    jobTitle: '',
    status: '',
    dateStart: '',
    dateEnd: '',
    dateLabel: '',
    page: 1,
    pageSize: 20
};

let pagination = { total: 0, totalPages: 1 };  // 服务端分页信息

let fpInstance = null;
let currentEditingId = null; let isSearchExpanded = false;

const els = {
    smartWrapper: document.getElementById('smartSearchWrapper'),
    smartInput: document.getElementById('smartInput'),
    suggestions: document.getElementById('searchSuggestions'),
    suggestionList: document.getElementById('suggestionList'),

    chipListCompany: document.getElementById('chipListCompany'),
    chipListJob: document.getElementById('chipListJob'),
    chipListStatus: document.getElementById('chipListStatus'),

    activeArea: document.getElementById('activeFiltersArea'),
    activeList: document.getElementById('activeFiltersList'),

    datePicker: document.getElementById('dateRangePicker'),
    btnQuick: document.getElementById('btnQuickSelect'),
    quickMenu: document.getElementById('quickSelectMenu'),

    tableBody: document.getElementById('tableBody'),
    rowTemplate: document.getElementById('rowTemplate'),
    totalCountInfo: document.getElementById('totalCountInfo'),
    pageControls: document.getElementById('pageControls'),
    btnPrev: document.getElementById('btnPrevPage'),
    btnNext: document.getElementById('btnNextPage'),
    currentPageNum: document.getElementById('currentPageNum'),

    drawer: document.getElementById('filterContainer'),
    drawerOverlay: document.getElementById('drawerOverlay'),

    modal: document.getElementById('applicantModal'),
    modalStatusSelect: document.getElementById('modalStatusSelect'),
    modalRemarks: document.getElementById('modalRemarks')
};

function bootHirePage() {
    initDatePicker();
    // 加载全量数据（用于 chip 计数）+ 芯片化 + 拉取当前过滤结果
    fetchStats().then(async () => {
        await loadRawData();
        renderChips();
        fetchData();
    });

    // 分页按钮
    els.btnPrev.addEventListener('click', () => {
        if (state.page > 1) { state.page--; fetchData(); }
    });
    els.btnNext.addEventListener('click', () => {
        const totalPages = Math.ceil(pagination.total / state.pageSize);
        if (state.page < totalPages) { state.page++; fetchData(); }
    });

    // 搜索框事件
    els.smartWrapper.addEventListener('click', (e) => {
        if (!isSearchExpanded) { e.stopPropagation(); expandSearch(); }
    });
    els.smartInput.addEventListener('input', () => {
        const val = els.smartInput.value.trim();
        if (val.length > 0) {
            generateSuggestions(val);
            els.suggestions.classList.add('show');
        } else {
            els.suggestions.classList.remove('show');
        }
        state.keyword = val;
        // 搜索关键词变化时重新拉取
        fetchData();
    });

    // 快捷日期事件
    els.btnQuick.addEventListener('click', (e) => {
        e.stopPropagation(); els.quickMenu.classList.toggle('show');
    });
    els.quickMenu.addEventListener('click', (e) => {
        if (e.target.tagName === 'A' && e.target.getAttribute('data-range')) {
            e.preventDefault(); setQuickDate(e.target.dataset.range, e.target.textContent);
        }
    });

    // 全局点击事件处理
    document.addEventListener('click', (e) => {
        if (!els.quickMenu.contains(e.target) && e.target !== els.btnQuick) els.quickMenu.classList.remove('show');
        if (!els.smartWrapper.contains(e.target) && !els.suggestions.contains(e.target)) {
            els.suggestions.classList.remove('show');
            if (els.smartInput.value.trim() === '') collapseSearch();
        }
    });

    bindGlobalStatusPopover();
}

// ── 全局 Status Popover（在主 script 里，与 fetchData 同作用域）────────────
let _gPop = null;
let _gAppId = null, _gBadgeEl = null;
let _gPopBound = false;

function bindGlobalStatusPopover() {
    _gPop = document.getElementById('globalStatusPopover');
    if (!_gPop || _gPopBound) return;
    _gPopBound = true;

function showGlobalPopover(badge, appId, currentStatus) {
    if (!_gPop) return;
    if (_gBadgeEl === badge && _gPop.classList.contains('show')) {
        closeGlobalPopover(); return;
    }
    _gAppId = appId; _gBadgeEl = badge;
    _gPop.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.toggle('active', parseInt(opt.dataset.val) === parseInt(currentStatus));
    });
    const rect = badge.getBoundingClientRect();
    _gPop.style.top = (rect.bottom + 4) + 'px';
    _gPop.style.left = (rect.left + rect.width / 2 - 55) + 'px';
    _gPop.classList.add('show');
}
function closeGlobalPopover() {
    if (!_gPop) return;
    _gPop.classList.remove('show');
    _gBadgeEl = null; _gAppId = null;
}

    // 全局 popover 选项点击
    _gPop.addEventListener('click', async (e) => {
        const opt = e.target.closest('.status-option');
        if (!opt || _gAppId === null) return;
        e.preventDefault(); e.stopPropagation();
        const id = _gAppId, newStatus = parseInt(opt.dataset.val);
        closeGlobalPopover();
        try {
            const res = await fetchHireApi(getHireApiUrl(''), {
                method: 'PUT', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status: newStatus })
            });
            const json = await res.json();
            if (json.code === 200) {
                // 同步更新 rawData，让 chip 计数立即反映新状态
                const r = rawData.find(x => x.id == id);
                if (r) r.status = String(newStatus);
                state.page = 1;        // 状态变更后回到第 1 页
                applyState();          // 重建 chips + 刷新数据
            }
        } catch (err) { console.warn('状态更新失败', err); }
    });
    document.addEventListener('click', (e) => { if (_gPop && !_gPop.contains(e.target)) closeGlobalPopover(); });
    document.addEventListener('scroll', closeGlobalPopover, true);
}

// ── API 拉取函数 ─────────────────────────────────────────────────────────
async function loadRawData() {
    try {
        const res = await fetchHireApi(getHireApiUrl('?action=list&page_size=2000&allow_large=1'));
        const json = await res.json();
        if (json.code === 200) rawData = json.data.list;
    } catch (e) { console.warn('loadRawData failed', e); }
}

async function fetchStats() {
    try {
        const res = await fetchHireApi(getHireApiUrl('?action=stats'));
        const json = await res.json();
        if (json.code === 200) { statsData = json.data; }
    } catch (e) { console.warn('统计加载失败', e); }
}

async function fetchData() {
    els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">\u6570\u636e\u52a0\u8f7d\u4e2d\u2026</td></tr>`;
    const params = new URLSearchParams({ action: 'list', page: state.page, page_size: state.pageSize });
    if (state.company) params.set('company', state.company);
    if (state.jobTitle) params.set('job_title', state.jobTitle);
    if (state.status !== '') params.set('status', state.status);
    if (state.keyword) params.set('keyword', state.keyword);
    if (state.dateStart) params.set('date_start', state.dateStart);
    if (state.dateEnd) params.set('date_end', state.dateEnd);
    try {
        const res = await fetchHireApi(getHireApiUrl('?' + params.toString()));
        const json = await res.json();
        if (json.code === 200) {
            const isInitialLoad = !window._firstLoadDone;
            window._firstLoadDone = true;
            allData = json.data.list;

            // \u66f4\u65b0\u5206\u9875\u4fe1\u606f
            const total = json.data.total ?? allData.length;
            const totalPages = json.data.total_pages ?? 1;
            pagination.total = total;
            pagination.totalPages = totalPages;

            renderTable(allData);
            updatePaginationUI();
            await fetchStats();
            renderChips();
            updateChipCounts();
            renderActiveTags();
            // \u9996\u6b21\u52a0\u8f7d\u65f6\u89e6\u53d1 toast \u901a\u77e5
            if (isInitialLoad) {
                const pendingCount = rawData.filter(r => String(r.status) === '0').length;
                document.dispatchEvent(new CustomEvent('hireDataLoaded', { detail: { pendingCount } }));
            }
        } else {
            els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">\u52a0\u8f7d\u5931\u8d25\uff1a${json.msg}</td></tr>`;
        }
    } catch (e) {
        els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">\u7f51\u7edc\u9519\u8bef\uff0c\u8bf7\u5237\u65b0\u9875\u9762\u91cd\u8bd5</td></tr>`;
    }
}

function updatePaginationUI() {
    const { total, totalPages } = pagination;
    // 只有 1 页时隐藏整个分页栏
    els.pageControls.style.display = totalPages <= 1 ? 'none' : 'flex';
    els.currentPageNum.textContent = state.page;
    // 显示当前页条数（不是总数）
    const pageCount = allData.length;
    els.totalCountInfo.textContent = `共计 ${pageCount} 条记录`;
    // 第 1 页时禁用上一页
    els.btnPrev.disabled = state.page <= 1;
    els.btnPrev.style.opacity = state.page <= 1 ? '0.4' : '1';
    // 最后一页时禁用下一页
    els.btnNext.disabled = state.page >= totalPages;
    els.btnNext.style.opacity = state.page >= totalPages ? '0.4' : '1';
}


function applyState() {
    state.page = 1;         // 切换过滤时回到第 1 页
    renderChips();
    renderActiveTags();
    fetchData();        // 拉数据，完成后只更新计数，不重建 chips
}

// 仅更新状态 chip 的计数数字，不重建 DOM
function updateChipCounts() {
    // 状态计数基数：rawData 按当前公司/职位过滤（不受已选状态影响）
    const statusBase = rawData.filter(r => {
        if (state.company && r.company_name !== state.company) return false;
        if (state.jobTitle && r.job_title !== state.jobTitle) return false;
        return true;
    });
    document.querySelectorAll('.chip[data-chip-status]').forEach(btn => {
        const span = btn.querySelector('.chip-count');
        if (!span) return;
        const v = btn.getAttribute('data-chip-status');
        span.textContent = v === ''
            ? statusBase.length
            : statusBase.filter(r => String(r.status) === v).length;
    });
}

function renderChips() {
    // 1. 公司
    els.chipListCompany.innerHTML = '';
    els.chipListCompany.appendChild(createChip('company', '', '全部', '🏢'));
    Object.keys(companyJobsMap).forEach(c => {
        els.chipListCompany.appendChild(createChip('company', c, c, ''));
    });
    // 2. 职位（联动公司）
    els.chipListJob.innerHTML = '';
    const jobs = state.company
        ? companyJobsMap[state.company]
        : [...new Set(Object.values(companyJobsMap).flat())];
    jobs.forEach(j => {
        els.chipListJob.appendChild(createChip('jobTitle', j, j, ''));
    });
    // 3. 状态
    els.chipListStatus.innerHTML = '';
    els.chipListStatus.appendChild(createChip('status', '', '全部', '📊'));
    statusConfig.forEach(cfg => {
        els.chipListStatus.appendChild(createChip('status', cfg.val, cfg.label, cfg.icon));
    });
}

// 创建单个 Chip（所有类型都显示计数）
function createChip(type, value, label, icon) {
    const btn = document.createElement('button');
    const isActive = state[type] !== '' && state[type].toString() === value.toString();
    const isAllButton = value === '';
    const active = isAllButton ? state[type] === '' : isActive;

    btn.className = `chip ${active ? 'active' : ''}`;

    // 计算每个 chip 的数量
    let cnt = 0;
    if (type === 'status') {
        // 状态计数基数：rawData 按当前公司/职位过滤
        // 不受已选状态影响，移除后不会整行崩零
        const statusBase = rawData.filter(r => {
            if (state.company && r.company_name !== state.company) return false;
            if (state.jobTitle && r.job_title !== state.jobTitle) return false;
            return true;
        });
        cnt = isAllButton
            ? statusBase.length
            : statusBase.filter(r => String(r.status) === String(value)).length;
        btn.setAttribute('data-chip-status', String(value));
    } else if (type === 'company') {
        // 公司：用 rawData（全量）统计 → 无论当前选检哪个公司，其他公司计数不变
        cnt = isAllButton
            ? rawData.length
            : rawData.filter(r => r.company_name === value).length;
    } else if (type === 'jobTitle') {
        // 职位：用 rawData，但若有公司过滤则局限在该公司的 rawData
        const base = state.company
            ? rawData.filter(r => r.company_name === state.company)
            : rawData;
        cnt = base.filter(r => r.job_title === value).length;
    }

    const countHtml = `<span class="chip-count">${cnt}</span>`;
    btn.innerHTML = `${icon ? `<span style="margin-right:2px">${icon}</span>` : ''}${label}${countHtml}`;

    btn.onclick = () => {
        state[type] = (active && !isAllButton) ? '' : value;
        if (type === 'company') state.jobTitle = '';
        applyState();
    };
    return btn;
}

// ── Export Excel (UTF-8 BOM CSV) ─────────────────────────────────────────
window.exportToExcel = function () {
    if (!allData || allData.length === 0) { showToast('没有可导出的数据', 'warning'); return; }
    var statusLabel = { 0: '待处理', 1: '沟通中', 2: '已录用', 3: '已淘汰' };
    var headers = ['序号', '中文姓名', '英文姓名', '性别', '申请公司', '申请职位',
        '邮箱', '电话区号', '电话号码', '简历链接', '状态', 'HR备注', '申请时间'];
    var rows = allData.map(function (r, i) {
        return [
            i + 1, r.chinese_name || '', r.english_name || '', r.gender || '',
            r.company_name || '', r.job_title || '', r.email || '',
            r.phone_code || '', r.phone_number || '',
            r.resume_file_url ? (location.origin + getHireBackendBase() + '/' + r.resume_file_url.replace(/^\/?/, '')) : '',
            statusLabel[r.status] !== undefined ? statusLabel[r.status] : String(r.status),
            r.hr_remarks || '', (r.created_at || '').replace('T', ' ')
        ];
    });
    function esc(v) {
        var s = String(v).replace(/"/g, '""');
        return (/[",\n\r]/.test(s)) ? ('"' + s + '"') : s;
    }
    var csv = [headers].concat(rows).map(function (row) { return row.map(esc).join(','); }).join('\r\n');
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    var now = new Date();
    var ts = '' + now.getFullYear() + String(now.getMonth() + 1).padStart(2, '0') + String(now.getDate()).padStart(2, '0');
    a.href = url; a.download = '招聘申请列表_' + ts + '.csv';
    document.body.appendChild(a); a.click();
    document.body.removeChild(a); URL.revokeObjectURL(url);
};

function renderActiveTags() {
    els.activeList.innerHTML = '';
    let hasActive = false;

    const addTag = (label, type) => {
        hasActive = true;
        const span = document.createElement('span');
        span.className = 'active-tag';
        span.innerHTML = `${label} <span class="active-tag-close" onclick="removeFilter('${type}')">&times;</span>`;
        els.activeList.appendChild(span);
    };

    if (state.keyword) addTag(`关键词: ${state.keyword}`, 'keyword');
    if (state.company) addTag(`公司: ${state.company}`, 'company');
    if (state.jobTitle) addTag(`职位: ${state.jobTitle}`, 'jobTitle');
    if (state.status !== '') {
        const sLabel = statusConfig.find(c => c.val == state.status).label;
        addTag(`状态: ${sLabel}`, 'status');
    }
    if (state.dateStart) addTag(`日期: ${state.dateLabel}`, 'date');

    els.activeArea.style.display = hasActive ? 'flex' : 'none';
}

window.removeFilter = function (type) {
    if (type === 'keyword') {
        state.keyword = ''; els.smartInput.value = ''; collapseSearch();
    } else if (type === 'date') {
        state.dateStart = ''; state.dateEnd = ''; state.dateLabel = ''; fpInstance.clear();
    } else {
        state[type] = '';
        if (type === 'company') state.jobTitle = '';
    }
    applyState();
}

window.resetAllFilters = function () {
    state = { keyword: '', company: '', jobTitle: '', status: '', dateStart: '', dateEnd: '', dateLabel: '', page: 1, pageSize: 20 };
    els.smartInput.value = ''; collapseSearch(); fpInstance.clear();
    applyState();
}

function expandSearch() {
    els.smartWrapper.classList.add('expanded'); setTimeout(() => els.smartInput.focus(), 150);
    isSearchExpanded = true;
}
function collapseSearch() {
    els.smartWrapper.classList.remove('expanded'); isSearchExpanded = false;
}

function generateSuggestions(keyword) {
    els.suggestionList.innerHTML = '';
    // 从已加载的 allData 里做客户端匹配建议
    const matches = allData.filter(app =>
        (app.chinese_name || '').toLowerCase().includes(keyword.toLowerCase()) ||
        (app.email || '').toLowerCase().includes(keyword.toLowerCase()) ||
        (app.phone_number || '').includes(keyword)
    ).slice(0, 3);

    if (matches.length === 0) {
        els.suggestionList.innerHTML = `<div style="padding: 10px 12px; font-size: 12px; color: #9ca3af;">无精准匹配，按 Enter 直接搜索</div>`;
        return;
    }

    matches.forEach(match => {
        const phone = match.phone_code ? `${match.phone_code} ${match.phone_number}` : match.phone_number;
        const a = document.createElement('a'); a.href = '#'; a.className = 'suggest-item';
        a.innerHTML = `<span style="font-weight:bold;">${match.chinese_name}</span> <span style="font-size:12px;color:#9ca3af;">(${phone})</span>`;
        a.addEventListener('click', (e) => {
            e.preventDefault(); els.smartInput.value = match.chinese_name; els.suggestions.classList.remove('show');
            state.keyword = match.chinese_name; fetchData();
        });
        els.suggestionList.appendChild(a);
    });
}

function initDatePicker() {
    fpInstance = flatpickr(els.datePicker, {
        mode: "range", locale: "zh", dateFormat: "Y年m月d日",
        appendTo: document.body,
        static: false,
        onOpen: function (_, __, instance) {
            // 下一帧等 flatpickr 渲染完后再定位
            requestAnimationFrame(() => positionCalendar(instance));
        },
        onChange: function (selectedDates) {
            if (selectedDates.length === 2) {
                state.dateStart = formatDate(selectedDates[0]);
                state.dateEnd = formatDate(selectedDates[1]);
                state.dateLabel = `${state.dateStart} 至 ${state.dateEnd}`;
                applyState();
            } else if (selectedDates.length === 0) {
                state.dateStart = ''; state.dateEnd = ''; state.dateLabel = '';
                applyState();
            }
        }
    });

    // 滚动 / 缩放时重新定位
    const reposition = () => {
        if (fpInstance && fpInstance.isOpen) positionCalendar(fpInstance);
    };
    window.addEventListener('scroll', reposition, true);
    window.addEventListener('resize', reposition);
}

function positionCalendar(instance) {
    const cal = instance.calendarContainer;
    const input = els.datePicker;
    const rect = input.getBoundingClientRect();
    // position: fixed 相对于 viewport，直接用 getBoundingClientRect 的坐标
    cal.style.position = 'fixed';
    cal.style.top = (rect.bottom + 4) + 'px';
    cal.style.left = rect.left + 'px';
    cal.style.width = rect.width + 'px';
    cal.style.zIndex = '99999';
    cal.style.margin = '0';
}

function formatDate(date) {
    const y = date.getFullYear(); const m = String(date.getMonth() + 1).padStart(2, '0'); const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function setQuickDate(type, label) {
    if (type === 'all') { removeFilter('date'); return; }
    const now = new Date(); let start, end; now.setHours(0, 0, 0, 0);
    switch (type) {
        case 'today': start = new Date(now); end = new Date(now); break;
        case 'yesterday': start = new Date(now); start.setDate(now.getDate() - 1); end = new Date(start); break;
        case 'thisWeek': const day = now.getDay() || 7; start = new Date(now); start.setDate(now.getDate() - day + 1); end = new Date(now); break;
        case 'lastWeek': const lastWeekDay = now.getDay() || 7; end = new Date(now); end.setDate(now.getDate() - lastWeekDay); start = new Date(end); start.setDate(end.getDate() - 6); break;
        case 'thisMonth': start = new Date(now.getFullYear(), now.getMonth(), 1); end = new Date(now); break;
    }
    fpInstance.setDate([start, end]);
    state.dateStart = formatDate(start); state.dateEnd = formatDate(end); state.dateLabel = label;
    els.quickMenu.classList.remove('show'); applyState();
}

// 这两个函数不再需要，保留占位避免変量未定义截断表达式错误
function getFilteredData() { return allData; }
function getFilteredCount(s) { return 0; }   // chip 数量已由 statsData 提供

function renderTable(list) {
    els.tableBody.innerHTML = '';
    if (list.length === 0) {
        els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">没有找到匹配的记录</td></tr>`;
        els.totalCountInfo.textContent = `共计加载 0 条数据`;
        return;
    }

    list.forEach(app => {
        const clone = els.rowTemplate.content.cloneNode(true);
        const createdAt = app.created_at || '';
        const [datePart, timePart] = createdAt.split('T').length > 1
            ? createdAt.replace('T', ' ').split(' ')   // ISO 格式
            : createdAt.split(' ');                     // 普通格式

        // 应聘者姓名
        clone.querySelector('.js-name').textContent = `${app.chinese_name || ''} (${app.english_name || ''})`;
        clone.querySelector('.js-subname').textContent = app.gender || '';
        clone.querySelector('.js-company').textContent = app.company_name || '';
        clone.querySelector('.js-job-title').textContent = app.job_title || '';

        // 联系方式
        clone.querySelector('.js-email').textContent = `✉️ ${app.email || ''}`;
        const phone = app.phone_code ? `${app.phone_code} ${app.phone_number}` : (app.phone_number || '');
        clone.querySelector('.js-phone').textContent = `📞 ${phone}`;

        // 时间
        clone.querySelector('.js-date').textContent = datePart || '';
        clone.querySelector('.js-time').textContent = timePart || '';

        // 简历预览按鈕
        const resumeBtn = clone.querySelector('.js-resume');
        if (app.resume_file_url) {
            resumeBtn.onclick = () => window.open(getHireResumeUrl(app.id), '_blank');
        } else {
            resumeBtn.textContent = '无附件';
            resumeBtn.style.opacity = '0.4';
            resumeBtn.style.pointerEvents = 'none';
        }

        // 状态徽章 — 用全局 popover portal
        const badge = clone.querySelector('.js-status-badge');
        updateBadgeUI(badge, app.status);

        badge.addEventListener('click', (e) => {
            e.stopPropagation();
            showGlobalPopover(badge, app.id, app.status);
        });

        clone.querySelector('.btn-action-detail').addEventListener('click', () => openModal(app.id));
        els.tableBody.appendChild(clone);
    });
    // totalCountInfo 由 updatePaginationUI 统一更新
}

function updateBadgeUI(badgeElement, statusVal, popoverElement) {
    badgeElement.className = 'badge js-status-badge';
    switch (Number(statusVal)) {
        case 0: badgeElement.textContent = '待处理'; badgeElement.classList.add('badge-red'); break;
        case 1: badgeElement.textContent = '沟通中'; badgeElement.classList.add('badge-yellow'); break;
        case 2: badgeElement.textContent = '已录用'; badgeElement.classList.add('badge-green'); break;
        default: badgeElement.textContent = '已淘汰'; badgeElement.classList.add('badge-gray'); break;
    }

    if (popoverElement) {
        popoverElement.querySelectorAll('.status-option').forEach(opt => {
            if (parseInt(opt.dataset.val) === Number(statusVal)) {
                opt.classList.add('active');
            } else {
                opt.classList.remove('active');
            }
        });
    }
}

window.toggleDrawer = function (show) {
    if (show) {
        els.drawer.classList.add('drawer-open');
        els.drawerOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    } else {
        els.drawer.classList.remove('drawer-open');
        els.drawerOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function openModal(id) {
    currentEditingId = id;
    const app = allData.find(item => item.id === id);
    if (!app) return;

    document.getElementById('modalCompany').textContent = app.company_name || '';
    document.getElementById('modalJob').textContent = app.job_title || '';
    document.getElementById('modalTime').textContent = (app.created_at || '').replace('T', ' ');
    document.getElementById('modalZhName').textContent = app.chinese_name || '';
    document.getElementById('modalEnName').textContent = app.english_name || '';
    document.getElementById('modalGender').textContent = app.gender || '';

    const emailLink = document.getElementById('modalEmailLink');
    emailLink.textContent = app.email || '';
    emailLink.href = `mailto:${app.email}`;

    const phone = app.phone_code ? `${app.phone_code} ${app.phone_number}` : (app.phone_number || '');
    document.getElementById('modalPhone').textContent = phone;

    // 简历按鈕
    const resumeBtn = document.getElementById('modalResumeBtn');
    if (app.resume_file_url) {
        resumeBtn.onclick = () => window.open(getHireResumeUrl(app.id), '_blank');
        resumeBtn.style.opacity = '1';
    } else {
        resumeBtn.textContent = '无简历附件';
        resumeBtn.style.opacity = '0.4';
        resumeBtn.onclick = null;
    }

    els.modalStatusSelect.value = app.status ?? 0;
    els.modalRemarks.value = app.hr_remarks || '';
    els.modal.classList.add('active');
}

window.closeModal = function () {
    els.modal.classList.remove('active'); currentEditingId = null;
}

window.saveModalChanges = async function () {
    if (currentEditingId === null) return;
    const status = parseInt(els.modalStatusSelect.value);
    const hr_remarks = els.modalRemarks.value;

    try {
        const res = await fetchHireApi(getHireApiUrl(''), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentEditingId, status, hr_remarks })
        });
        const json = await res.json();
        if (json.code === 200) {
            // 同步内存
            const idx = allData.findIndex(item => item.id === currentEditingId);
            if (idx > -1) { allData[idx].status = status; allData[idx].hr_remarks = hr_remarks; }
            renderTable(allData);
            await fetchStats();
            renderChips();
            closeModal();
        } else {
            showToast('保存失败：' + json.msg, 'error');
        }
    } catch (err) {
        showToast('网络错误，保存失败', 'error');
    }
}

let toastTimer = null;
function showPendingToast(count) {
    if (count <= 0) return;
    const toast = document.getElementById('pendingToast');
    const msg = document.getElementById('toastMsg');
    const prog = document.getElementById('toastProgress');
    msg.textContent = `共有 ${count} 位申请人待处理，请及时审批。`;
    // reset progress bar
    prog.style.animation = 'none';
    prog.offsetHeight;  // reflow
    prog.style.animation = '';
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(dismissToast, 6000);
}
function dismissToast() {
    clearTimeout(toastTimer);
    const toast = document.getElementById('pendingToast');
    toast.classList.remove('show');
}
// 在数据加载完毕后触发 toast（利用 fetchData 事件）
document.addEventListener('hireDataLoaded', (e) => {
    showPendingToast(e.detail.pendingCount);
});

window.bootHire = bootHire;
window.reinitHire = bootHire;
window.dismissToast = dismissToast;

if (!isHireReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootHire);
    } else {
        bootHire();
    }
}