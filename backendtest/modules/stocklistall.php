<?php
// 包含会话验证
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="../css/stocklistall.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <!-- 低库存预警弹窗 -->
    <div id="low-stock-modal" class="low-stock-modal">
        <div class="low-stock-modal-content">
            <div class="low-stock-modal-header">
                <h2>
                    <i class="fas fa-exclamation-triangle"></i>
                    库存不足提醒
                </h2>
                <button class="close-modal" onclick="closeLowStockModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="low-stock-modal-body">
                <div id="low-stock-content">
                    <!-- Dynamic content -->
                </div>
            </div>
            <div class="modal-footer">
                <div class="alert-summary" id="alert-summary">
                    <!-- Summary info -->
                </div>
            </div>
        </div>
    </div>

    <!-- 导出日期选择弹窗 -->
    <div id="export-date-modal" class="export-date-modal">
        <div class="export-date-modal-content">
            <div class="export-date-modal-header">
                <h2>
                    <i class="fas fa-calendar-alt"></i>
                    选择导出日期
                </h2>
                <button class="close-modal" onclick="closeExportDateModalComplete()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="export-date-modal-body">
                <div class="date-selector-group">
                    <label for="export-end-date">选择日期</label>
                    <input type="date" id="export-end-date" required>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeExportDateModalComplete()">
                    <i class="fas fa-times"></i>
                    取消
                </button>
                <button class="btn btn-primary" onclick="confirmExport()">
                    <i class="fas fa-download"></i>
                    确认导出
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <div>
                <h1 id="page-title">总库存 - 中央</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">总库存</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item active" onclick="switchView('list')">总库存</div>
                        <div class="dropdown-item" onclick="switchView('records')">进出货</div>
                        <div class="dropdown-item" onclick="switchView('remark')">货品备注</div>
                        <div class="dropdown-item" onclick="switchView('product')">货品种类</div>
                        <div class="dropdown-item" onclick="switchView('sot')">货品异常</div>
                    </div>
                </div>
                <div class="system-selector">
                    <button class="selector-button" onclick="toggleSelector()">
                        <span id="current-system">中央</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="selector-dropdown">
                        <div class="dropdown-item active" onclick="switchSystem('central', event)">中央</div>
                        <div class="dropdown-item" onclick="switchSystem('j1', event)">J1</div>
                        <div class="dropdown-item" onclick="switchSystem('j2', event)">J2</div>
                        <div class="dropdown-item" onclick="switchSystem('j3', event)">J3</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 中央库存页面 -->
        <div id="central-page" class="page-section active">
            <div class="unified-header-row">
                <div class="header-summary">
                    <div class="summary-title">总库存</div>
                    <div class="summary-amount">
                        <span class="currency-symbol">RM</span>
                        <span class="value" id="central-total-value">0.00</span>
                    </div>
                </div>
                
                <div class="type-grid-container">
                    <div class="type-grid-item">
                        <div class="grid-title">J1供应</div>
                        <div class="grid-value" id="central-j1-supply-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">J2供应</div>
                        <div class="grid-value" id="central-j2-supply-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">J3供应</div>
                        <div class="grid-value" id="central-j3-supply-value">0.00</div>
                    </div>
                </div>

                <div class="header-right-section">
                <div class="header-search">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">搜索</span>
                    <input type="text" id="central-unified-filter" class="unified-search-input" 
                        placeholder="输入关键字搜索...">
                </div>
                
                <button class="btn btn-warning" onclick="exportData('central')">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <button class="btn btn-primary" onclick="goToMinimumSettings()">
                    <i class="fas fa-cog"></i>
                    设置最低库存
                </button>
                
                <div class="header-stats">
                    <span>显示记录: <span class="stat-value" id="central-displayed-records">0</span></span>
                    <span>总记录: <span class="stat-value" id="central-total-records">0</span></span>
                </div>
                </div>
            </div>

            <div class="table-container">              
                <div class="table-scroll-container">
                    <table class="stock-table" id="central-stock-table">
                        <thead>
                            <tr>
                                <th>序号.</th>
                                <th>货品编号</th>
                                <th>货品</th>
                                <th>最低库存</th>
                                <th>库存数量</th>
                                <th>规格</th>
                                <th>单价</th>
                                <th>总价</th>
                            </tr>
                        </thead>
                        <tbody id="central-stock-tbody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- J1库存页面 -->
        <div id="j1-page" class="page-section">
            <div class="unified-header-row">
                <div class="header-summary">
                    <div class="summary-title">总库存</div>
                    <div class="summary-amount">
                        <span class="currency-symbol">RM</span>
                        <span class="value" id="j1-total-value">0.00</span>
                    </div>
                </div>
                
                <div class="type-grid-container">
                    <div class="type-grid-item">
                        <div class="grid-title">Drinks</div>
                        <div class="grid-value" id="j1-drinks-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Sake</div>
                        <div class="grid-value" id="j1-sake-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Kitchen</div>
                        <div class="grid-value" id="j1-kitchen-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Sushi Bar</div>
                        <div class="grid-value" id="j1-sushi-bar-value">0.00</div>
                    </div>
                </div>
                
                <div class="header-right-section">
                <div class="header-search">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #583e04; white-space: nowrap;">搜索</span>
                    <input type="text" id="j1-unified-filter" class="unified-search-input" 
                        placeholder="搜索序号、货品编号、货品、库存数量、规格、单价、总价...">
                </div>
                
                <button class="btn btn-warning" onclick="exportData('j1')">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <button class="btn btn-primary" onclick="goToMinimumSettings()">
                    <i class="fas fa-cog"></i>
                    设置最低库存
                </button>
                
                <div class="header-stats">
                    <span>显示记录: <span class="stat-value" id="j1-displayed-records">0</span></span>
                    <span>总记录: <span class="stat-value" id="j1-total-records">0</span></span>
                </div>
                </div>
            </div>

            <div class="table-container">                           
                <div class="table-scroll-container">
                    <table class="stock-table" id="j1-stock-table">
                        <thead>
                            <tr>
                                <th>序号.</th>
                                <th>货品编号</th>
                                <th>货品</th>
                                <th>最低库存</th>
                                <th>库存总量</th>
                                <th>规格</th>
                                <th>单价</th>
                                <th>总价</th>
                            </tr>
                        </thead>
                        <tbody id="j1-stock-tbody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- J2库存页面 -->
        <div id="j2-page" class="page-section">
            <div class="unified-header-row">
                <div class="header-summary">
                    <div class="summary-title">总库存</div>
                    <div class="summary-amount">
                        <span class="currency-symbol">RM</span>
                        <span class="value" id="j2-total-value">0.00</span>
                    </div>
                </div>
                
                <div class="type-grid-container">
                    <div class="type-grid-item">
                        <div class="grid-title">Drinks</div>
                        <div class="grid-value" id="j2-drinks-value">0.00</div>
                    </div>
                    <div class="type-grid-item" style="display: none;">
                        <div class="grid-title">Sake</div>
                        <div class="grid-value" id="j2-sake-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Kitchen</div>
                        <div class="grid-value" id="j2-kitchen-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Sushi Bar</div>
                        <div class="grid-value" id="j2-sushi-bar-value">0.00</div>
                    </div>
                </div>
                
                <div class="header-right-section">
                <div class="header-search">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #583e04; white-space: nowrap;">搜索</span>
                    <input type="text" id="j2-unified-filter" class="unified-search-input" 
                        placeholder="搜索货品名称、编号或规格单位...">
                </div>
                
                <button class="btn btn-warning" onclick="exportData('j2')">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <button class="btn btn-primary" onclick="goToMinimumSettings()">
                    <i class="fas fa-cog"></i>
                    设置最低库存
                </button>
                
                <div class="header-stats">
                    <span>显示记录: <span class="stat-value" id="j2-displayed-records">0</span></span>
                    <span>总记录: <span class="stat-value" id="j2-total-records">0</span></span>
                </div>
                </div>
            </div>

            <div class="table-container">                              
                <div class="table-scroll-container">
                    <table class="stock-table" id="j2-stock-table">
                        <thead>
                            <tr>
                                <th>序号.</th>
                                <th>货品编号</th>
                                <th>货品</th>
                                <th>最低库存</th>
                                <th>库存总量</th>
                                <th>规格</th>
                                <th>单价</th>
                                <th>总价</th>
                            </tr>
                        </thead>
                        <tbody id="j2-stock-tbody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- J3库存页面 -->
        <div id="j3-page" class="page-section">
            <div class="unified-header-row">
                <div class="header-summary">
                    <div class="summary-title">总库存</div>
                    <div class="summary-amount">
                        <span class="currency-symbol">RM</span>
                        <span class="value" id="j3-total-value">0.00</span>
                    </div>
                </div>
                
                <div class="type-grid-container">
                    <div class="type-grid-item">
                        <div class="grid-title">Drinks</div>
                        <div class="grid-value" id="j3-drinks-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Sake</div>
                        <div class="grid-value" id="j3-sake-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Kitchen</div>
                        <div class="grid-value" id="j3-kitchen-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Sushi Bar</div>
                        <div class="grid-value" id="j3-sushi-bar-value">0.00</div>
                    </div>
                </div>
                
                <div class="header-right-section">
                <div class="header-search">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #583e04; white-space: nowrap;">搜索</span>
                    <input type="text" id="j3-unified-filter" class="unified-search-input" 
                        placeholder="搜索货品名称、编号或规格单位...">
                </div>
                
                <button class="btn btn-warning" onclick="exportData('j3')">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <button class="btn btn-primary" onclick="goToMinimumSettings()">
                    <i class="fas fa-cog"></i>
                    设置最低库存
                </button>
                
                <div class="header-stats">
                    <span>显示记录: <span class="stat-value" id="j3-displayed-records">0</span></span>
                    <span>总记录: <span class="stat-value" id="j3-total-records">0</span></span>
                </div>
                </div>
            </div>

            <div class="table-container">                              
                <div class="table-scroll-container">
                    <table class="stock-table" id="j3-stock-table">
                        <thead>
                            <tr>
                                <th>序号.</th>
                                <th>货品编号</th>
                                <th>货品</th>
                                <th>最低库存</th>
                                <th>库存总量</th>
                                <th>规格</th>
                                <th>单价</th>
                                <th>总价</th>
                            </tr>
                        </thead>
                        <tbody id="j3-stock-tbody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 价格分析页面 -->
        <div id="remark-page" class="page-section">
            <!-- 中央库存页面的过滤部分 -->
            <div class="filter-section">
                <div class="search-row">
                    <div class="search-group">
                        <label for="central-unified-filter">搜索货品</label>
                        <label for="central-unified-filter" class="search-label">搜索</label>
                        <input type="text" id="central-unified-filter" class="unified-search-input" 
                            placeholder="搜索货品名称、编号或规格单位...">
                    </div>
                    <button class="btn btn-warning" onclick="exportData('central')">
                        <i class="fas fa-download"></i>
                        导出数据
                    </button>
                </div>
            </div>

            <div id="remark-products-container">
                <!-- Dynamic content -->
            </div>
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
        // 全局状态
        let currentSystem = 'central';
        let stockData = {
            central: [],
            j1: [],
            j2: [],
            j3: [],
            remark: []
        };
        let filteredData = {
            central: [],
            j1: [],
            j2: [],
            j3: [],
            remark: []
        };
        let isLoading = {
            central: false,
            j1: false,
            j2: false,
            j3: false,
            remark: false
        };
        let currentView = 'list';

        let lowStockSettings = {};

        const VIEW_NAMES = {
            list: '总库存',
            records: '进出货',
            remark: '货品备注'
        };

        const SYSTEM_OPTIONS = [
            { value: 'central', label: '中央' },
            { value: 'j1', label: 'J1' },
            { value: 'j2', label: 'J2' },
            { value: 'j3', label: 'J3' }
        ];

        const VIEW_OPTIONS = [
            { value: 'list', label: '总库存' },
            { value: 'records', label: '进出货' },
            { value: 'remark', label: '货品备注' },
            { value: 'product', label: '货品种类' },
            { value: 'sot', label: '货品异常' }
        ];

        let cachedAllowedSystems = new Set();
        let cachedAllowedViews = new Set();

        // API配置
        const API_CONFIG = {
            central: '/backendtest/api/stocklistapi.php',
            j1: '/backendtest/api/j1stocklistapi.php',
            j2: '/backendtest/api/j2stocklistapi.php',
            j3: '/backendtest/api/j3stocklistapi.php',
            remark: '/backendtest/api/stockremarkapi.php'
        };

        const SYSTEM_NAMES = {
            central: '中央',
            j1: 'J1',
            j2: 'J2',
            j3: 'J3',
            remark: '货品备注'
        };

        const PAGE_TITLES = {
            central: '总库存 - 中央',
            j1: '总库存 - J1',
            j2: '总库存 - J2',
            j3: '总库存 - J3',
            remark: '货品备注'
        };

        function rebuildSystemDropdown(allowedSet) {
            const dropdown = document.getElementById('selector-dropdown');
            if (!dropdown) return;
            const available = (allowedSet && allowedSet.size > 0)
                ? SYSTEM_OPTIONS.filter(opt => allowedSet.has(opt.value))
                : SYSTEM_OPTIONS.slice();

            if (available.length === 0) {
                available.push(SYSTEM_OPTIONS[0]);
            }

            if (!available.some(opt => opt.value === currentSystem)) {
                currentSystem = available[0].value;
                const currentSystemEl = document.getElementById('current-system');
                const pageTitleEl = document.getElementById('page-title');
                if (currentSystemEl) currentSystemEl.textContent = SYSTEM_NAMES[currentSystem];
                if (pageTitleEl) pageTitleEl.textContent = PAGE_TITLES[currentSystem];
                document.querySelectorAll('.page-section').forEach(page => page.classList.remove('active'));
                const targetPage = document.getElementById(currentSystem + '-page');
                if (targetPage) targetPage.classList.add('active');
            }

            dropdown.innerHTML = '';
            available.forEach(opt => {
                const item = document.createElement('div');
                item.className = 'dropdown-item' + (opt.value === currentSystem ? ' active' : '');
                item.dataset.systemValue = opt.value;
                item.textContent = opt.label;
                item.onclick = function(event) {
                    switchSystem(opt.value, event);
                };
                dropdown.appendChild(item);
            });
        }

        function rebuildViewDropdown(allowedSet) {
            const dropdown = document.getElementById('view-selector-dropdown');
            if (!dropdown) return;
            const available = (allowedSet && allowedSet.size > 0)
                ? VIEW_OPTIONS.filter(opt => allowedSet.has(opt.value))
                : VIEW_OPTIONS.slice();

            if (available.length === 0) {
                available.push(VIEW_OPTIONS[0]);
            }

            dropdown.innerHTML = '';
            available.forEach(opt => {
                const item = document.createElement('div');
                item.className = 'dropdown-item' + (opt.value === currentView ? ' active' : '');
                item.dataset.viewValue = opt.value;
                item.textContent = opt.label;
                item.onclick = function() {
                    switchView(opt.value);
                };
                dropdown.appendChild(item);
            });
        }

        // 初始化应用
        async function initApp() {
            // 启动会话自动刷新
            startSessionRefresh();
            
            // 先应用页面权限，获取允许的系统列表
            await applyPagePermissions();
            
            // 检查URL参数中的system（在权限检查之后）
            const urlParams = new URLSearchParams(window.location.search);
            const urlSystem = urlParams.get('system');
            
            // 如果URL中有系统参数，验证它是否在允许列表中
            if (urlSystem && ['central', 'j1', 'j2', 'j3'].includes(urlSystem)) {
                // 再次检查权限，确保URL中的系统是被允许的
                try {
                    const res = await fetch('/backendtest/api/generatecodeapi.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'get_page_permissions' })
                    });
                    const data = await res.json();
                    if (data.success && data.page_permissions && data.page_permissions.stock_inventory) {
                        const allowedSystems = new Set(data.page_permissions.stock_inventory.system || []);
                        // 如果有权限限制，检查URL中的系统是否被允许
                        if (allowedSystems.size > 0 && !allowedSystems.has(urlSystem)) {
                            // URL中的系统不在允许列表中，使用第一个允许的系统
                            const firstAllowed = Array.from(allowedSystems)[0];
                            if (firstAllowed) {
                                currentSystem = firstAllowed;
                                // 更新URL参数
                                const newUrl = new URL(window.location);
                                newUrl.searchParams.set('system', firstAllowed);
                                window.history.replaceState({}, '', newUrl);
                            }
                        } else if (allowedSystems.size === 0) {
                            // 没有权限限制，使用URL中的系统
                            currentSystem = urlSystem;
                        } else {
                            // URL中的系统在允许列表中，使用它
                            currentSystem = urlSystem;
                        }
                    } else {
                        // 没有权限限制，使用URL中的系统
                        currentSystem = urlSystem;
                    }
                } catch (e) {
                    // 权限检查失败，使用URL中的系统（向后兼容）
                    currentSystem = urlSystem;
                }
            }
            
            loadData(currentSystem);
            checkLowStockAlerts();
            
            // 添加实时搜索监听器
            setupRealTimeSearch();
            
            // 点击外部关闭下拉菜单
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.system-selector')) {
                    document.getElementById('selector-dropdown').classList.remove('show');
                }
                if (!e.target.closest('.view-selector')) {
                    document.getElementById('view-selector-dropdown').classList.remove('show');
                }
            });
        }

        // 设置实时搜索
        function setupRealTimeSearch() {
            const systems = ['central', 'j1', 'j2', 'j3'];
            
            systems.forEach(system => {
                const searchInput = document.getElementById(`${system}-unified-filter`);
                if (searchInput) {
                    // 防抖处理，避免频繁搜索
                    let debounceTimer;
                    searchInput.addEventListener('input', function() {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => {
                            searchData(system);
                        }, 300); // 300ms延迟
                    });
                }
            });
        }

        // 切换系统选择器
        function toggleSelector() {
            document.getElementById('selector-dropdown').classList.toggle('show');
        }

        // 切换系统
        async function switchSystem(system, evt) {
            if (system === currentSystem) return;
            
            // 检查权限：如果用户有权限限制，验证目标系统是否被允许
            try {
                if (cachedAllowedSystems && cachedAllowedSystems.size > 0 && !cachedAllowedSystems.has(system)) {
                    const firstAllowed = Array.from(cachedAllowedSystems)[0];
                    if (firstAllowed) {
                        system = firstAllowed;
                    } else {
                        return;
                    }
                } else {
                    const res = await fetch('/backendtest/api/generatecodeapi.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'get_page_permissions' })
                    });
                    const data = await res.json();
                    if (data.success && data.page_permissions && data.page_permissions.stock_inventory) {
                        const allowedSystems = new Set(data.page_permissions.stock_inventory.system || []);
                        cachedAllowedSystems = new Set(allowedSystems);
                        if (allowedSystems.size > 0 && !allowedSystems.has(system)) {
                            const firstAllowed = Array.from(allowedSystems)[0];
                            if (firstAllowed) {
                                system = firstAllowed;
                            } else {
                                return;
                            }
                        }
                    }
                }
            } catch (e) {
                // 权限检查失败时允许切换（向后兼容）
            }
            
            currentSystem = system;
            
            // 更新URL参数
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('system', system);
            window.history.replaceState({}, '', newUrl);
            
            // 更新UI
            document.getElementById('current-system').textContent = SYSTEM_NAMES[system];
            document.getElementById('page-title').textContent = PAGE_TITLES[system];
            
            // 更新下拉菜单激活状态
            document.querySelectorAll('#selector-dropdown .dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            if (evt && evt.target) {
                evt.target.classList.add('active');
            } else {
                const nextActive = document.querySelector(`#selector-dropdown .dropdown-item[data-system-value="${system}"]`);
                if (nextActive) {
                    nextActive.classList.add('active');
                }
            }
            
            // 切换页面
            document.querySelectorAll('.page-section').forEach(page => {
                page.classList.remove('active');
            });
            document.getElementById(system + '-page').classList.add('active');
            
            // 隐藏下拉菜单
            document.getElementById('selector-dropdown').classList.remove('show');
            
            // 加载数据
            loadData(system);
        }

        // 切换视图选择器
        function toggleViewSelector() {
            document.getElementById('view-selector-dropdown').classList.toggle('show');
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
                const allowedSystems = new Set(current.system || []);
                const allowedViews = new Set(current.view || []);

                // 如果有权限限制，检查当前系统是否被允许
                if (allowedSystems.size > 0) {
                    // 检查URL参数
                    const urlParams = new URLSearchParams(window.location.search);
                    const urlSystem = urlParams.get('system');
                    
                    // 如果当前系统不在允许列表中，或者没有设置系统（首次进入），切换到第一个允许的系统
                    if (!allowedSystems.has(currentSystem) || (!urlSystem && allowedSystems.size > 0)) {
                        const firstAllowed = Array.from(allowedSystems)[0];
                        if (firstAllowed) {
                            currentSystem = firstAllowed;
                            // 更新URL参数（不刷新页面）
                            const newUrl = new URL(window.location);
                            newUrl.searchParams.set('system', firstAllowed);
                            window.history.replaceState({}, '', newUrl);
                            // 更新UI
                            const currentSystemEl = document.getElementById('current-system');
                            const pageTitleEl = document.getElementById('page-title');
                            if (currentSystemEl) {
                                currentSystemEl.textContent = SYSTEM_NAMES[firstAllowed];
                            }
                            if (pageTitleEl) {
                                pageTitleEl.textContent = PAGE_TITLES[firstAllowed];
                            }
                            // 切换页面显示
                            document.querySelectorAll('.page-section').forEach(page => {
                                page.classList.remove('active');
                            });
                            const targetPage = document.getElementById(firstAllowed + '-page');
                            if (targetPage) {
                                targetPage.classList.add('active');
                            }
                        }
                    }
                    
                    // 系统下拉 - 隐藏不允许的选项
                    const sysDropdown = document.getElementById('selector-dropdown');
                    if (sysDropdown) {
                        const map = { '中央':'central', 'J1':'j1', 'J2':'j2', 'J3':'j3' };
                        Array.from(sysDropdown.children).forEach(item => {
                            const text = item.textContent.trim();
                            const key = map[text];
                            if (key && !allowedSystems.has(key)) {
                                item.remove();
                            }
                        });
                    }
                }
                cachedAllowedSystems = new Set(allowedSystems);
                rebuildSystemDropdown(allowedSystems);

                // 视图下拉 - 隐藏不允许的选项
                cachedAllowedViews = new Set(allowedViews);
                rebuildViewDropdown(allowedViews);

                // 如果用户无法访问总库存视图，则跳转到首个允许的视图页面
                if (allowedViews.size > 0 && !allowedViews.has('list')) {
                    const viewOrder = ['records', 'remark', 'product', 'sot'];
                    const viewToOpen = viewOrder.find(view => allowedViews.has(view));
                    if (viewToOpen) {
                        const viewRedirectMap = {
                            records: 'stockeditall.php',
                            remark: 'stockremark.php',
                            product: 'stockproductname.php',
                            sot: 'stocksot.php'
                        };
                        const targetUrl = viewRedirectMap[viewToOpen];
                        if (targetUrl) {
                            const systemParam = currentSystem ? `?system=${currentSystem}` : '';
                            window.location.href = `${targetUrl}${systemParam}`;
                            return;
                        }
                    }
                }
            } catch (e) {
                // ignore
            }
        }

        function switchView(view) {
            if (view === currentView) return;
            
            if (view === 'records') {
                // 跳转到库存记录页面
                window.location.href = 'stockeditall.php';
                return;
            }
            
            if (view === 'remark') {
                // 跳转到Remark页面
                window.location.href = 'stockremark.php';
                return;
            }
            
            if (view === 'product') {
                // 跳转到货品种类页面
                window.location.href = 'stockproductname.php';
                return;
            }
            
            if (view === 'sot') {
                // 跳转到货品异常页面
                window.location.href = 'stocksot.php';
                return;
            }
            
            currentView = view;
            
            // 更新UI
            document.getElementById('current-view').textContent = VIEW_NAMES[view];
            
            // 更新下拉菜单激活状态
            document.querySelectorAll('#view-selector-dropdown .dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // 隐藏下拉菜单
            document.getElementById('view-selector-dropdown').classList.remove('show');
        }

        // 返回仪表盘
        function goBack() {
            window.location.href = 'dashboard.php';
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
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.innerHTML = `
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
            if (isLoading[system]) return;
            
            isLoading[system] = true;
            setLoadingState(system, true);
            
            try {
                // 所有系统都加载低库存设置，以便显示最低库存列
                await loadLowStockSettings();
                
                let result;
                if (system === 'remark') {
                    result = await apiCall(system, '?action=analysis');
                } else {
                    result = await apiCall(system, '?action=summary');
                }
                
                if (result.success) {
                    if (system === 'remark') {
                        stockData[system] = result.data.products || [];
                    } else {
                        stockData[system] = result.data.summary || [];
                        
                        // J2系统过滤掉Sake类型的数据
                        if (system === 'j2') {
                            stockData[system] = stockData[system].filter(item => {
                                return item.type !== 'Sake';
                            });
                        }
                        
                        updateSummaryCards(system, result.data);
                        if (system === 'central') {
                            // 同步更新中央页面右侧的 J1/J2/J3 供应统计
                            updateCentralSupplyBoxes();
                        }
                    }
                    
                    filteredData[system] = [...stockData[system]];
                    
                    if (system === 'remark') {
                        renderRemarkProducts();
                    } else {
                        renderStockTable(system);
                        updateStats(system);
                    }
                    
                    if (stockData[system].length === 0) {
                        let message = system === 'remark' ? 
                            '当前没有发现多价格货品' : 
                            `当前没有${SYSTEM_NAMES[system]}数据`;
                        showAlert(message, 'info');
                    }
                } else {
                    stockData[system] = [];
                    filteredData[system] = [];
                    showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
                    
                    if (system === 'remark') {
                        renderRemarkProducts();
                    } else {
                        renderStockTable(system);
                    }
                }
                
            } catch (error) {
                stockData[system] = [];
                filteredData[system] = [];
                console.error('Error:', error);
                
                if (system === 'remark') {
                    renderRemarkProducts();
                } else {
                    renderStockTable(system);
                }
            } finally {
                isLoading[system] = false;
                setLoadingState(system, false);
            }
        }

        async function loadLowStockSettings() {
            try {
                // 添加时间戳防止缓存，确保每次获取最新数据
                const timestamp = new Date().getTime();
                const response = await fetch(`/backendtest/api/stockminimumapi.php?action=list&_t=${timestamp}`, {
                    cache: 'no-cache',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    lowStockSettings = {};
                    result.data.forEach(item => {
                        // 去除产品名称首尾空格，确保匹配准确
                        const productName = (item.product_name || '').trim();
                        if (productName) {
                            // 直接使用数据库中的值，确保与 stockminimum.php 和数据库完全一致
                            // 使用 floatval 确保精确的数值
                            const minQty = parseFloat(item.minimum_quantity);
                            if (!isNaN(minQty)) {
                                // 如果同一个产品名称有多个记录，保留最大的值（确保显示正确的设置）
                                if (!lowStockSettings[productName] || minQty > lowStockSettings[productName]) {
                                    lowStockSettings[productName] = minQty;
                                }
                            }
                        }
                    });
                    // 调试：检查 AKA TOSAKA 的值
                    console.log('已加载最低库存设置，总数:', Object.keys(lowStockSettings).length);
                    if (lowStockSettings['AKA TOSAKA']) {
                        console.log('AKA TOSAKA 最低库存:', lowStockSettings['AKA TOSAKA'], '类型:', typeof lowStockSettings['AKA TOSAKA']);
                    }
                }
            } catch (error) {
                console.error('加载低库存设置失败:', error);
            }
        }

        // 检查是否库存不足
        function isLowStock(productName, currentStock) {
            const minimumQuantity = lowStockSettings[productName];
            if (!minimumQuantity || minimumQuantity <= 0) {
                return false;
            }
            
            // 计算同名货品的总库存
            const totalStockForProduct = filteredData[currentSystem].reduce((total, item) => {
                if (item.product_name === productName) {
                    return total + parseFloat(item.total_stock || 0);
                }
                return total;
            }, 0);
            
            return totalStockForProduct <= parseFloat(minimumQuantity);
        }

        // 实时搜索数据
        function searchData(system) {
            if (system === 'remark') {
                searchRemarkData();
                return;
            }

            const searchTerm = document.getElementById(`${system}-unified-filter`).value.toLowerCase();

            filteredData[system] = stockData[system].filter(item => {
                // 获取最低库存值用于搜索
                const minimumQuantity = lowStockSettings[item.product_name] || 0;
                const minimumStockStr = minimumQuantity > 0 ? minimumQuantity.toString() : '';
                
                // 搜索所有字段，包括序号、货品编号、货品名称、最低库存、库存数量、规格、单价、总价
                return (
                    (item.no && item.no.toString().includes(searchTerm)) ||
                    (item.product_name && item.product_name.toLowerCase().includes(searchTerm)) ||
                    (item.code_number && item.code_number.toLowerCase().includes(searchTerm)) ||
                    (minimumStockStr && minimumStockStr.includes(searchTerm)) ||
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

        // 搜索价格分析数据
        function searchRemarkData() {
            const productFilter = document.getElementById('remark-product-filter').value.toLowerCase();
            const codeFilter = document.getElementById('remark-code-filter').value.toLowerCase();
            const minVariants = parseInt(document.getElementById('remark-min-variants').value) || 0;
            const sortBy = document.getElementById('remark-sort-by').value;

            // 过滤数据
            filteredData.remark = stockData.remark.filter(item => {
                const matchProduct = !productFilter || item.product_name.toLowerCase().includes(productFilter);
                const matchCode = !codeFilter || (item.code_number && item.code_number.toLowerCase().includes(codeFilter));
                const matchVariants = item.variants.length >= minVariants;

                return matchProduct && matchCode && matchVariants;
            });

            // 排序数据
            sortRemarkData(sortBy);
            renderRemarkProducts();
            
            if (filteredData.remark.length === 0) {
                showAlert('未找到匹配的记录', 'info');
            } else {
                showAlert(`找到 ${filteredData.remark.length} 个匹配货品`, 'success');
            }
        }

        // 排序价格分析数据
        function sortRemarkData(sortBy) {
            switch (sortBy) {
                case 'name_asc':
                    filteredData.remark.sort((a, b) => a.product_name.localeCompare(b.product_name));
                    break;
                case 'name_desc':
                    filteredData.remark.sort((a, b) => b.product_name.localeCompare(a.product_name));
                    break;
                case 'variants_desc':
                    filteredData.remark.sort((a, b) => b.variants.length - a.variants.length);
                    break;
                case 'variants_asc':
                    filteredData.remark.sort((a, b) => a.variants.length - b.variants.length);
                    break;
                case 'price_diff_desc':
                    filteredData.remark.sort((a, b) => b.price_difference - a.price_difference);
                    break;
                case 'price_diff_asc':
                    filteredData.remark.sort((a, b) => a.price_difference - b.price_difference);
                    break;
            }
        }

        // 重置搜索过滤器
        function resetFilters(system) {
            if (system === 'remark') {
                document.getElementById('remark-product-filter').value = '';
                document.getElementById('remark-code-filter').value = '';
                document.getElementById('remark-min-variants').value = '';
                document.getElementById('remark-sort-by').value = 'name_asc';
                
                filteredData.remark = [...stockData.remark];
                sortRemarkData('name_asc');
                renderRemarkProducts();
            } else {
                // 修改这部分
                document.getElementById(`${system}-unified-filter`).value = '';
                
                filteredData[system] = [...stockData[system]];
                renderStockTable(system);
                updateStats(system);
            }
            
            showAlert('搜索条件已重置', 'info');
        }

        // 设置加载状态
        function setLoadingState(system, loading) {
            if (system === 'remark') {
                const container = document.getElementById('remark-products-container');
                if (loading) {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 60px;">
                            <div class="loading"></div>
                            <div style="margin-top: 16px; color: #6b7280;">正在分析库存价格数据...</div>
                        </div>
                    `;
                }
            } else {
                const tbody = document.getElementById(`${system}-stock-tbody`);
                if (loading) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" style="padding: 40px; text-align: center;">
                                <div class="loading"></div>
                                <div style="margin-top: 16px; color: #6b7280;">正在加载${SYSTEM_NAMES[system]}数据...</div>
                            </td>
                        </tr>
                    `;
                }
            }
        }

        // 更新汇总卡片
        function updateSummaryCards(system, data) {
            document.getElementById(`${system}-total-value`).textContent = data.formatted_total_value || '0.00';
            
            // 只为 J1、J2、J3 更新类型统计
            if (system !== 'central' && data.type_stats) {
                const drinksEl = document.getElementById(`${system}-drinks-value`);
                const sakeEl = document.getElementById(`${system}-sake-value`);
                const kitchenEl = document.getElementById(`${system}-kitchen-value`);
                const sushiBarEl = document.getElementById(`${system}-sushi-bar-value`);
                
                // 更新数值并检查是否为负数
                if (drinksEl) {
                    drinksEl.textContent = data.type_stats.formatted_drinks || '0.00';
                    drinksEl.classList.toggle('negative', data.type_stats.drinks < 0);
                }
                // J2系统不显示sake统计
                if (sakeEl && system !== 'j2') {
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

        // 在中央页面显示 J1/J2/J3 供应总值（从j*stockinout_data表的入库数量*单价）
        async function updateCentralSupplyBoxes() {
            try {
                const systems = ['j1', 'j2', 'j3'];
                for (const sys of systems) {
                    const result = await apiCall(sys, '?action=supply_total');
                    const el = document.getElementById(`central-${sys}-supply-value`);
                    if (el && result && result.success && result.data) {
                        el.textContent = result.data.formatted_total_value || '0.00';
                        // 若需要负数高亮，可在此添加判断
                    }
                }
            } catch (e) {
                // 静默失败，避免影响中央页面主流程
                console.error('更新中央供应统计失败:', e);
            }
        }

        // 更新统计信息
        function updateStats(system) {
            const displayedRecords = filteredData[system].length;
            const totalRecords = stockData[system].length;
            
            document.getElementById(`${system}-displayed-records`).textContent = displayedRecords;
            document.getElementById(`${system}-total-records`).textContent = totalRecords;
        }

        // 替换现有的 renderStockTable 函数
        function renderStockTable(system) {
            const tbody = document.getElementById(`${system}-stock-tbody`);
            
            if (filteredData[system].length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无${SYSTEM_NAMES[system]}数据</div>
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
                
                // 获取最低库存 - 确保产品名称匹配（去除首尾空格）
                const productName = (item.product_name || '').trim();
                const minimumQuantity = lowStockSettings[productName] || 0;
                
                // 调试：检查特定产品的值
                if (productName === 'AKA TOSAKA') {
                    console.log('渲染 AKA TOSAKA:', {
                        productName: productName,
                        itemProductName: item.product_name,
                        minimumQuantity: minimumQuantity,
                        lowStockSettingsValue: lowStockSettings[productName],
                        allAKAKeys: Object.keys(lowStockSettings).filter(k => k.includes('AKA') || k.includes('TOSAKA'))
                    });
                }
                
                let rowClass = '';
                
                // 检查低库存 - 所有系统都应用
                // 使用当前行的库存进行检查（与弹窗API逻辑一致，按规格检查）
                if (minimumQuantity > 0) {
                    // 使用更精确的浮点数比较（考虑精度问题）
                    // 当库存小于或等于最低库存时，标记为低库存
                    const minQty = parseFloat(minimumQuantity);
                    const currentQty = parseFloat(stockValue);
                    
                    // 使用更严格的比较：如果当前库存 <= 最低库存，则标记为低库存
                    // 使用 Math.abs 处理浮点数精度问题，容差为 0.001
                    const diff = currentQty - minQty;
                    const isLowStockItem = diff <= 0.001; // 允许0.001的精度误差
                    
                    rowClass = isLowStockItem ? 'low-stock-row' : '';
                }
                // 直接显示最低库存值，不进行格式化处理，确保与数据库一致
                let minimumStockDisplay = '-';
                if (minimumQuantity > 0) {
                    // 根据规格决定小数位数：kilo 显示3位，其他显示2位
                    const specification = (item.specification || '').trim().toLowerCase();
                    if (specification === 'kilo') {
                        minimumStockDisplay = parseFloat(minimumQuantity).toFixed(3);
                    } else {
                        minimumStockDisplay = parseFloat(minimumQuantity).toFixed(2);
                    }
                }
                const minimumStockClass = minimumQuantity > 0 ? 'minimum-stock-value' : 'zero-value';
                
                tableRows += `
                    <tr class="${rowClass}">
                        <td class="text-center">${item.no}</td>
                        <td class="text-center">${item.code_number || '-'}</td>
                        <td><strong>${item.product_name}</strong></td>
                        <td class="stock-cell">
                            <div class="currency-display ${minimumStockClass}">
                                <span class="currency-symbol">&nbsp;</span>
                                <span class="currency-amount">${minimumStockDisplay}</span>
                            </div>
                        </td>
                        <td class="stock-cell">
                            <div class="currency-display ${stockClass}">
                                <span class="currency-symbol">&nbsp;</span>
                                <span class="currency-amount">${formatStockQuantity(item)}</span>
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
                    <td colspan="7" class="text-right" style="font-size: clamp(10px, 0.84vw, 16px); padding-right: 15px; text-align: right;">总计:</td>
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

        // 渲染价格分析货品列表
        function renderRemarkProducts() {
            const container = document.getElementById('remark-products-container');
            
            if (filteredData.remark.length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <i class="fas fa-search"></i>
                        <h3>没有找到多价格货品</h3>
                        <p>当前筛选条件下没有发现货品有多个价格变体</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            filteredData.remark.forEach(product => {
                html += `
                    <div class="product-group">
                        <div class="product-header">
                            <span>${product.product_name}</span>
                            <span class="price-count">${product.variants.length} 个价格</span>
                        </div>
                        <table class="price-variants-table">
                            <thead>
                                <tr>
                                    <th>排序</th>
                                    <th>货品编号</th>
                                    <th>库存数量</th>
                                    <th>单价</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${renderVariants(product.variants, product.max_price)}
                            </tbody>
                        </table>
                    </div>
                `;
            });
            
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

        // 格式化货币 - 统一显示两位小数
        function formatCurrency(value) {
            if (!value || value === '' || value === '0') return '0.00';
            const num = parseFloat(value);
            if (isNaN(num)) return '0.00';
            
            // 直接格式化为两位小数显示
            return num.toFixed(2);
        }

        // 根据规格动态格式化库存数量
        function formatStockQuantity(item) {
            const specification = item.specification ? item.specification.trim().toLowerCase() : '';
            const rawStock = parseFloat(item.total_stock);
            const fallbackFormatted = item.formatted_stock || item.total_stock || '';

            if (specification === 'kilo') {
                if (!isNaN(rawStock)) {
                    return rawStock.toFixed(3);
                }
                const parsedFallback = parseFloat(fallbackFormatted);
                if (!isNaN(parsedFallback)) {
                    return parsedFallback.toFixed(3);
                }
                return fallbackFormatted || '0.000';
            }

            return fallbackFormatted || '0.00';
        }

        // 刷新数据
        function refreshData(system) {
            loadData(system);
        }

        // 存储当前要导出的系统
        let currentExportSystem = null;

        // 导出数据为PDF（英文格式，使用jsPDF直接生成）
        function exportData(system) {
            // 检查数据是否存在
            if (!filteredData[system] || filteredData[system].length === 0) {
                // 如果没有过滤数据，尝试使用原始数据
                if (!stockData[system] || stockData[system].length === 0) {
                    showAlert('没有数据可导出', 'error');
                    return;
                } else {
                    // 使用原始数据
                    filteredData[system] = [...stockData[system]];
                }
            }
            
            // 保存当前系统并显示日期选择模态框
            currentExportSystem = system;
            showExportDateModal();
        }

        // 显示导出日期选择模态框
        function showExportDateModal() {
            const modal = document.getElementById('export-date-modal');
            const endDateInput = document.getElementById('export-end-date');
            
            // 设置默认日期为今天
            const today = new Date();
            endDateInput.value = formatDateForInput(today);
            
            // 设置最大日期为今天
            const todayStr = formatDateForInput(today);
            endDateInput.max = todayStr;
            
            modal.style.display = 'block';
        }

        // 关闭导出日期选择模态框
        function closeExportDateModal() {
            document.getElementById('export-date-modal').style.display = 'none';
            // 不在这里清空 currentExportSystem，因为 confirmExport 还需要它
        }
        
        // 完全关闭导出日期选择模态框（清空所有状态）
        function closeExportDateModalComplete() {
            document.getElementById('export-date-modal').style.display = 'none';
            currentExportSystem = null;
        }

        // 格式化日期为YYYY-MM-DD格式（用于input[type="date"]）
        function formatDateForInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // 确认导出
        function confirmExport() {
            const endDateInput = document.getElementById('export-end-date');
            const endDate = endDateInput.value;
            
            if (!endDate) {
                showAlert('请选择日期', 'error');
                return;
            }
            
            // 调试：检查当前系统
            console.log('confirmExport - currentExportSystem:', currentExportSystem);
            console.log('confirmExport - filteredData:', filteredData);
            console.log('confirmExport - stockData:', stockData);
            
            if (!currentExportSystem) {
                console.error('currentExportSystem is null!');
                showAlert('系统错误：无法确定导出系统', 'error');
                return;
            }
            
            // 保存系统变量，因为关闭模态框可能会清空它
            const systemToExport = currentExportSystem;
            
            // 关闭模态框
            closeExportDateModalComplete();
            
            // 执行导出
            performExport(systemToExport, endDate);
        }

        // 执行实际的导出操作
        async function performExport(system, endDate) {
            // 显示加载提示
            showAlert('正在根据日期获取数据...', 'info');
            
            try {
                // 根据日期重新获取数据
                let result;
                let dataToExport;
                
                if (system === 'remark') {
                    result = await apiCall(system, '?action=analysis');
                    if (result.success) {
                        dataToExport = result.data.products || [];
                    } else {
                        showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
                        return;
                    }
                } else {
                    // 构建带日期的API URL（只使用结束日期）
                    const apiUrl = `${API_CONFIG[system]}?action=summary&end_date=${endDate}`;
                    const response = await fetch(apiUrl, {
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP错误: ${response.status}`);
                    }
                    
                    result = await response.json();
                    
                    // 检查是否是会话过期
                    if (result.code === 'SESSION_EXPIRED') {
                        showSessionExpiredMessage();
                        return;
                    }
                    
                    if (result.success) {
                        dataToExport = result.data.summary || [];
                        
                        // J2系统过滤掉Sake类型的数据
                        if (system === 'j2') {
                            dataToExport = dataToExport.filter(item => {
                                return item.type !== 'Sake';
                            });
                        }
                    } else {
                        showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
                        return;
                    }
                }
                
                if (!dataToExport || dataToExport.length === 0) {
                    showAlert('所选日期没有数据可导出', 'error');
                    return;
                }
                
                console.log('Data to export:', dataToExport.length, 'records');
                
                // 执行PDF生成
                generatePDF(system, dataToExport, endDate);
                
            } catch (error) {
                console.error('获取数据失败:', error);
                showAlert('获取数据失败: ' + error.message, 'error');
            }
        }

        // 生成PDF文件
        function generatePDF(system, dataToExport, endDate) {
            try {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape', 'mm', 'a4');
                
                // 获取系统名称（英文）
                const systemNameMap = {
                    'central': 'Central',
                    'j1': 'J1',
                    'j2': 'J2',
                    'j3': 'J3'
                };
                const systemName = systemNameMap[system] || system.toUpperCase();
                const title = system === 'remark' ? 'Product Price Analysis Report' : `${systemName} Stock Summary Report`;
                
                // 设置标题
                doc.setFontSize(16);
                doc.setFont(undefined, 'bold');
                doc.text(title, 14, 15);
                
                // 格式化日期显示
                const formatDateForDisplay = (dateStr) => {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                };
                
                // 添加日期信息
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                const exportTimeStr = new Date().toLocaleString('en-US', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                doc.text(`Export Time: ${exportTimeStr}`, 14, 22);
                doc.text(`As of Date: ${formatDateForDisplay(endDate)}`, 14, 28);
                doc.text(`Records: ${dataToExport.length}`, 200, 22);
                
                // 准备表格数据
                let headers, tableData, columnStyles;
                
                if (system === 'remark') {
                    // 价格分析
                    headers = [['Product Name', 'Rank', 'Code Number', 'Stock', 'Unit Price']];
                    tableData = [];
                    
                    dataToExport.forEach(product => {
                        product.variants.forEach((variant, index) => {
                            tableData.push([
                                product.product_name || '-',
                                (index + 1).toString(),
                                variant.code_number || '-',
                                variant.formatted_stock || variant.stock || '0.00',
                                variant.formatted_price || variant.price || '0.00'
                            ]);
                        });
                    });
                    
                    columnStyles = {
                        0: { cellWidth: 70 },
                        1: { cellWidth: 20 },
                        2: { cellWidth: 50 },
                        3: { cellWidth: 40 },
                        4: { cellWidth: 40 }
                    };
                } else {
                    // 库存汇总
                    headers = [['No.', 'Product Name', 'Code Number', 'Minimum Stock', 'Total Stock', 'Specification', 'Unit Price', 'Total Price']];
                    tableData = [];
                    let totalValue = 0;
                    
                    dataToExport.forEach((item, index) => {
                        if (!item) return;
                        
                        const productName = (item.product_name || '').trim();
                        const minimumQuantity = lowStockSettings[productName] || 0;
                        let minimumStockDisplay = '-';
                        if (minimumQuantity > 0) {
                            const specification = (item.specification || '').trim().toLowerCase();
                            if (specification === 'kilo') {
                                minimumStockDisplay = parseFloat(minimumQuantity).toFixed(3);
                            } else {
                                minimumStockDisplay = parseFloat(minimumQuantity).toFixed(2);
                            }
                        }
                        
                        const totalPrice = parseFloat(item.total_price) || 0;
                        totalValue += totalPrice;
                        
                        tableData.push([
                            (item.no || (index + 1)).toString(),
                            item.product_name || '-',
                            item.code_number || '-',
                            minimumStockDisplay,
                            item.formatted_stock || item.total_stock || '0.00',
                            item.specification || '-',
                            item.formatted_price || item.price || '0.00',
                            item.formatted_total_price || item.total_price || '0.00'
                        ]);
                    });
                    
                    // 添加总计行
                    tableData.push([
                        '',
                        'Total',
                        '',
                        '',
                        '',
                        '',
                        '',
                        `RM ${formatCurrency(totalValue)}`
                    ]);
                    
                    columnStyles = {
                        0: { cellWidth: 18 },
                        1: { cellWidth: 55 },
                        2: { cellWidth: 35 },
                        3: { cellWidth: 28 },
                        4: { cellWidth: 28 },
                        5: { cellWidth: 25 },
                        6: { cellWidth: 35 },
                        7: { cellWidth: 35 }
                    };
                }
                
                // 生成表格（调整起始位置以容纳日期范围）
                doc.autoTable({
                    head: headers,
                    body: tableData,
                    startY: 34,
                    styles: {
                        fontSize: 8,
                        cellPadding: 2,
                        overflow: 'linebreak',
                        cellWidth: 'wrap'
                    },
                    headStyles: {
                        fillColor: [99, 99, 99],
                        textColor: [255, 255, 255],
                        fontStyle: 'bold',
                        fontSize: 9
                    },
                    alternateRowStyles: {
                        fillColor: [245, 245, 245]
                    },
                    columnStyles: columnStyles,
                    margin: { top: 28, left: 14, right: 14 },
                    didDrawPage: function (data) {
                        // 添加页脚
                        doc.setFontSize(8);
                        doc.text(
                            `Page ${data.pageNumber}`,
                            doc.internal.pageSize.width / 2,
                            doc.internal.pageSize.height - 10,
                            { align: 'center' }
                        );
                    }
                });
                
                // 保存PDF（文件名包含日期）
                const formatDateForFileName = (dateStr) => {
                    return dateStr.replace(/-/g, '');
                };
                const fileName = system === 'remark' 
                    ? `stock_price_analysis_${formatDateForFileName(endDate)}.pdf`
                    : `${system}_stock_summary_${formatDateForFileName(endDate)}.pdf`;
                
                doc.save(fileName);
                showAlert('PDF导出成功', 'success');
                
            } catch (error) {
                console.error('导出失败:', error);
                showAlert('导出失败: ' + error.message, 'error');
            }
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
        window.addEventListener('beforeunload', function() {
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
        window.addEventListener('scroll', function() {
            // 使用防抖优化性能
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                const backToTopBtn = document.getElementById('back-to-top-btn');
                const scrollThreshold = 150; // 滚动超过300px后显示按钮
                
                if (window.pageYOffset > scrollThreshold) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            }, 10);
        });

        // 键盘快捷键支持
        document.addEventListener('keydown', function(e) {
            // Ctrl+F 聚焦搜索框
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const activeFilterId = `${currentSystem}-unified-filter`;
                const activeFilter = document.getElementById(activeFilterId);
                if (activeFilter) {
                    activeFilter.focus();
                }
            }
            
            // Escape键重置搜索
            if (e.key === 'Escape') {
                resetFilters(currentSystem);
            }

            // 数字键1-5快速切换系统
            if (e.ctrlKey && e.key >= '1' && e.key <= '5') {
                e.preventDefault();
                const systems = ['central', 'j1', 'j2', 'j3', 'remark'];
                const systemIndex = parseInt(e.key) - 1;
                if (systems[systemIndex]) {
                    // 模拟点击切换
                    const dropdownItems = document.querySelectorAll('.dropdown-item');
                    if (dropdownItems[systemIndex]) {
                        switchSystem(systems[systemIndex]);
                    }
                }

                // Home键回到顶部
                    if (e.key === 'Home' && e.ctrlKey) {
                        e.preventDefault();
                        scrollToTop();
                    }
            }
        });

        // 定时刷新数据（每5分钟）
        setInterval(() => {
            if (!document.hidden) { // 只在页面可见时刷新
                loadData(currentSystem);
            }
        }, 300000); // 5分钟 = 300000毫秒

        // 监听页面可见性变化，当页面重新可见时刷新数据
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // 页面重新可见时，重新加载最低库存设置和当前系统数据
                loadLowStockSettings().then(() => {
                    loadData(currentSystem);
                });
            }
        });

        // 监听窗口焦点变化，当窗口重新获得焦点时刷新数据
        window.addEventListener('focus', function() {
            // 窗口重新获得焦点时，重新加载最低库存设置和当前系统数据
            loadLowStockSettings().then(() => {
                loadData(currentSystem);
            });
        });

        // 检查低库存预警
        async function checkLowStockAlerts() {
            try {
                const result = await apiCall('central', '?action=low_stock_alerts');
                
                if (result.success && result.data.alerts && result.data.alerts.length > 0) {
                    showLowStockModal(result.data.alerts);
                }
            } catch (error) {
                console.error('检查低库存预警失败:', error);
            }
        }

        // 显示低库存弹窗
        function showLowStockModal(alerts) {
            const modal = document.getElementById('low-stock-modal');
            const content = document.getElementById('low-stock-content');
            const summary = document.getElementById('alert-summary');
            
            // 按货品名称字母顺序排序
            const sortedAlerts = [...alerts].sort((a, b) => {
                const nameA = (a.product_name || '').toUpperCase();
                const nameB = (b.product_name || '').toUpperCase();
                return nameA.localeCompare(nameB);
            });
            
            let html = `
                <div style="font-size: clamp(8px, 0.84vw, 16px); padding: clamp(6px, 0.63vw, 12px); background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; color: #b91c1c;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    发现 ${sortedAlerts.length} 个货品库存不足，请及时补货！
                </div>
                <table class="low-stock-table">
                    <thead>
                        <tr>
                            <th>货品名称</th>
                            <th>货品编号</th>
                            <th>规格</th>
                            <th>当前库存</th>
                            <th>最低库存</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            sortedAlerts.forEach(alert => {
                const currentStock = parseFloat(alert.current_stock);
                const minimumStock = parseFloat(alert.minimum_quantity);
                
                let statusClass = 'stock-critical';
                if (currentStock <= 0) {
                    statusClass = 'stock-critical';
                } else if (currentStock <= minimumStock * 0.5) {
                    statusClass = 'stock-critical';
                } else {
                    statusClass = 'stock-warning';
                }
                
                // 格式化最低库存显示，与列表保持一致
                const minStockDisplay = minimumStock > 0 ? minimumStock.toFixed(2) : '-';
                
                html += `
                    <tr>
                        <td><strong>${(alert.product_name || '').trim()}</strong></td>
                        <td>${alert.code_number || '-'}</td>
                        <td>${alert.specification || '-'}</td>
                        <td class="${statusClass}">${alert.formatted_stock}</td>
                        <td>${minStockDisplay}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            
            content.innerHTML = html;
            summary.textContent = `共 ${sortedAlerts.length} 个货品库存不足`;
            modal.style.display = 'block';
        }

        // 关闭低库存弹窗
        function closeLowStockModal() {
            document.getElementById('low-stock-modal').style.display = 'none';
        }

        // 跳转到最低库存设置页面
        function goToMinimumSettings() {
            window.open('stockminimum.php', '_blank');
        }

        // 点击弹窗外部关闭
        document.addEventListener('click', function(e) {
            const lowStockModal = document.getElementById('low-stock-modal');
            const exportDateModal = document.getElementById('export-date-modal');
            if (e.target === lowStockModal) {
                closeLowStockModal();
            }
            if (e.target === exportDateModal) {
                closeExportDateModalComplete();
            }
        });
    </script>
</body>
</html>