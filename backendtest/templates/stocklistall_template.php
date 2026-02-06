<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="../css/stocklistall.css">
</head>
<body>
    <?php $basePath = '../'; include __DIR__ . '/../sidebar.php'; ?>
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

    <script src="../js/stocklistall.js"></script>
</body>
</html>
