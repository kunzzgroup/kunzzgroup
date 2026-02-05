<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理系统</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/j3stockinoutpage.css">
</head>
<body>
    <?php $basePath = '../'; include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1 id="page-title">进出货 - J3</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">进出货</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item" onclick="switchView('list')">总库存</div>
                        <div class="dropdown-item active" onclick="switchView('records')">进出货</div>
                        <div class="dropdown-item" onclick="switchView('product')">货品种类</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>

        <!-- 日期和行数选择弹窗 -->
        <div id="date-rows-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">新增记录</h3>
                    <button class="modal-close" onclick="closeDateRowsModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>选择日期</label>
                        <input type="date" id="batch-date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>添加行数</label>
                        <input type="number" id="batch-rows" class="form-input" value="1" min="1" max="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-modal btn-modal-secondary" onclick="closeDateRowsModal()">
                        <i class="fas fa-times"></i>
                        取消
                    </button>
                    <button class="btn-modal btn-modal-primary" onclick="confirmAddBatchRows()">
                        <i class="fas fa-check"></i>
                        确认添加
                    </button>
                </div>
            </div>
        </div>

        <!-- 搜索和过滤区域 -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>搜索</label>
                    <input type="text" id="search-input" class="filter-input" placeholder="搜索货品编号或名称...">
                </div>
                <div class="filter-group">
                    <label>开始日期</label>
                    <input type="date" id="start-date" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>结束日期</label>
                    <input type="date" id="end-date" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>货物类型</label>
                    <select id="type-filter" class="filter-select">
                        <option value="">全部类型</option>
                        <option value="Drinks">Drinks</option>
                        <option value="Sake">Sake</option>
                        <option value="Kitchen">Kitchen</option>
                        <option value="Sushi Bar">Sushi Bar</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i>
                    应用筛选
                </button>
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo"></i>
                    重置
                </button>
                <button class="btn btn-success" onclick="openAddRowModal()">
                    <i class="fas fa-plus"></i>
                    新增记录
                </button>
                <button class="btn btn-warning" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf"></i>
                    导出PDF
                </button>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="table-container">
            <div class="table-scroll-container">
                <table class="stock-table" id="stock-table">
                    <thead>
                        <tr>
                            <th>日期</th>
                            <th>货品编号</th>
                            <th>货品</th>
                            <th>进货</th>
                            <th>出货</th>
                            <th>收货单位</th>
                            <th>规格</th>
                            <th>单价</th>
                            <th>总价</th>
                            <th>类型</th>
                            <th>产品备注</th>
                            <th>备注编号</th>
                            <th>名字/收货人</th>
                            <th>备注</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="stock-tbody">
                        <!-- Dynamic content will be inserted by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 操作按钮区域 -->
        <div class="action-buttons">
            <div class="stats-info">
                <div class="stat-item">
                    <i class="fas fa-list"></i>
                    <span>总记录: <span class="stat-value" id="total-records">0</span></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-eye"></i>
                    <span>显示: <span class="stat-value" id="displayed-records">0</span></span>
                </div>
            </div>
            <div>
                <button class="btn btn-success" onclick="saveAllChanges()">
                    <i class="fas fa-save"></i>
                    保存所有更改
                </button>
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

    <!-- 日历弹窗（放在body末尾以确保最高层级） -->
    <div class="calendar-popup" id="calendar-popup" style="display: none;">
        <div class="calendar-header">
            <button class="calendar-nav-btn" onclick="event.stopPropagation(); changeMonth(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="calendar-month-year" onclick="event.stopPropagation();">
                <select id="calendar-year-select" class="calendar-select" onclick="event.stopPropagation();" onchange="renderCalendar()">
                    <!-- 动态生成年份 -->
                </select>
                <select id="calendar-month-select" class="calendar-select" onclick="event.stopPropagation();" onchange="renderCalendar()">
                    <option value="0">1月</option>
                    <option value="1">2月</option>
                    <option value="2">3月</option>
                    <option value="3">4月</option>
                    <option value="4">5月</option>
                    <option value="5">6月</option>
                    <option value="6">7月</option>
                    <option value="7">8月</option>
                    <option value="8">9月</option>
                    <option value="9">10月</option>
                    <option value="10">11月</option>
                    <option value="11">12月</option>
                </select>
            </div>
            <button class="calendar-nav-btn" onclick="event.stopPropagation(); changeMonth(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="calendar-weekdays">
            <div class="calendar-weekday">日</div>
            <div class="calendar-weekday">一</div>
            <div class="calendar-weekday">二</div>
            <div class="calendar-weekday">三</div>
            <div class="calendar-weekday">四</div>
            <div class="calendar-weekday">五</div>
            <div class="calendar-weekday">六</div>
        </div>
        <div class="calendar-days" id="calendar-days">
            <!-- 动态生成日期 -->
        </div>
    </div>

    <script src="../js/j3stockinoutpage.js"></script>
</body>
</html>
