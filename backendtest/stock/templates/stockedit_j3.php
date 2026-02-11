<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理系统</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="animation.css" />
    <link rel="stylesheet" href="css/stockedit_j3.css" />
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>J3 库存管理</h1>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view-text">出库模式</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-dropdown">
                        <div class="dropdown-item active" onclick="switchView('out')">出库模式</div>
                        <div class="dropdown-item" onclick="switchView('in')">入库模式</div>
                        <div class="dropdown-item" onclick="switchView('all')">全部记录</div>
                    </div>
                </div>
                
                <div class="system-selector">
                    <button class="selector-button" onclick="toggleStockSelector()">
                        <span id="current-stock-text">J3 库存</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="stock-dropdown">
                        <div class="dropdown-item" onclick="switchStock('all')">全店铺库存</div>
                        <div class="dropdown-item" onclick="switchStock('j1')">J1 库存</div>
                        <div class="dropdown-item" onclick="switchStock('j2')">J2 库存</div>
                        <div class="dropdown-item active" onclick="switchStock('j3')">J3 库存</div>
                    </div>
                </div>
                <a href="../login/logout.php" class="back-button">
                    <i class="fas fa-sign-out-alt"></i> 退出登录
                </a>
            </div>
        </div>

        <div class="unified-header-row">
            <div class="header-search">
                <div class="filter-group">
                    <label class="date-label-with-icon">
                        <i class="far fa-calendar-alt"></i> 选择日期范围
                    </label>
                    <div class="date-range-picker" id="date-range-btn" onclick="toggleCalendar(event)">
                        <i class="far fa-calendar-alt"></i>
                        <span id="date-range-text">正在加载...</span>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="search-group">
                    <label class="search-label">
                        <i class="fas fa-search"></i> 搜索
                    </label>
                    <div class="action-buttons-group">
                        <input type="text" id="unified-search" class="unified-search-input" placeholder="搜索货品、编号、收货人..." oninput="handleSearchInput()">
                        <button class="btn btn-primary" onclick="searchData()">
                            <i class="fas fa-search"></i> 查询
                        </button>
                    </div>
                </div>
            </div>

            <div class="header-right-group">
                <div class="header-stats">
                    <div>当前显示: <span id="stat-count" class="stat-value">0</span> 条记录</div>
                    <div>出库总额: <span id="stat-out-total" class="stat-value">RM 0.00</span></div>
                    <div>入库总额: <span id="stat-in-total" class="stat-value">RM 0.00</span></div>
                </div>
                <div class="batch-actions">
                    <button class="btn btn-success" onclick="showDateRowsModal()">
                        <i class="fas fa-plus"></i> 新增记录
                    </button>
                    <button class="btn btn-primary" id="batch-save-btn" onclick="batchSaveNewRows()" style="display: none;">
                        <i class="fas fa-save"></i> 批量保存
                    </button>
                    <button class="btn btn-danger" id="batch-delete-btn" onclick="toggleBatchDelete()">
                        <i class="fas fa-trash-alt"></i> 批量删除
                    </button>
                    <button class="btn btn-success" id="confirm-batch-delete-btn" onclick="confirmBatchDelete()" style="display: none;" disabled>
                        <i class="fas fa-check"></i> 确认删除
                    </button>
                    <button class="btn btn-secondary" id="cancel-batch-delete-btn" onclick="cancelBatchDelete()" style="display: none;">
                        <i class="fas fa-times"></i> 取消
                    </button>
                    <button class="btn btn-warning" onclick="document.getElementById('export-modal').style.display='block'">
                        <i class="fas fa-file-export"></i> 导出发票
                    </button>
                </div>
            </div>
        </div>

        <div class="table-container" id="table-container">
            <div class="table-scroll-container">
                <table class="stock-table" id="stock-table">
                    <thead>
                        <tr>
                            <th>日期</th>
                            <th>货品编号</th>
                            <th class="product-name-col">货品名称</th>
                            <th>进货</th>
                            <th>出货</th>
                            <th>收货单位</th>
                            <th>规格</th>
                            <th>单价 RM</th>
                            <th>总价 RM</th>
                            <th>类型</th>
                            <th>产品备注</th>
                            <th>备注编号</th>
                            <th class="receiver-col">收货人</th>
                            <th>备注</th>
                            <th id="action-header">操作</th>
                        </tr>
                    </thead>
                    <tbody id="stock-tbody">
                        <!-- 数据将通过 JavaScript 动态加载 -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 导出弹窗 -->
    <div id="export-modal" class="export-modal">
        <div class="export-modal-content">
            <button class="close-export-modal" onclick="closeExportModal()">&times;</button>
            <h3><i class="fas fa-file-export"></i> 导出PDF发票</h3>
            
            <div class="export-form-group">
                <label for="export-start-date">开始日期</label>
                <input type="text" id="export-start-date" placeholder="DD/MM/YYYY" readonly onclick="toggleStandaloneCalendar(event, 'export-start-date')">
            </div>
            
            <div class="export-form-group">
                <label for="export-end-date">结束日期</label>
                <input type="text" id="export-end-date" placeholder="DD/MM/YYYY" readonly onclick="toggleStandaloneCalendar(event, 'export-end-date')">
            </div>

            <div class="export-form-group">
                <label for="export-invoice-date">发票日期</label>
                <input type="text" id="export-invoice-date" placeholder="DD/MM/YYYY" readonly onclick="toggleStandaloneCalendar(event, 'export-invoice-date')">
            </div>
            
            <div class="export-form-group">
                <label for="export-system">选择店面 (发票代号)</label>
                <select id="export-system" onchange="handleExportSystemChange()">
                    <option value="j1">J1 - Tokyo Restaurant</option>
                    <option value="j2">J2 - Tokyo Japanese Restaurant</option>
                    <option value="j3" selected>J3 - Tokyo Sushi</option>
                </select>
            </div>

            <div class="export-form-group">
                <label for="export-invoice-suffix">发票号码后缀 (三位数字，如 001)</label>
                <input type="text" id="export-invoice-suffix" maxlength="3" placeholder="例如：001" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            
            <div class="export-modal-actions">
                <button class="btn btn-secondary" onclick="closeExportModal()">取消</button>
                <button class="btn btn-success" onclick="confirmExport()">
                    <i class="fas fa-download"></i> 导出PDF发票
                </button>
            </div>
        </div>
    </div>

    <!-- 多行新增弹窗 -->
    <div id="date-rows-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">新增记录</h3>
                <button class="modal-close" onclick="closeDateRowsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal-date">选择日期</label>
                    <input type="text" id="modal-date" class="form-input" placeholder="DD/MM/YYYY" readonly onclick="toggleStandaloneCalendar(event, 'modal-date')">
                </div>
                <div class="form-group">
                    <label for="modal-rows">新增行数</label>
                    <input type="number" id="modal-rows" class="form-input" min="1" max="50" value="1">
                </div>
                <div class="form-group">
                    <label for="modal-remark">备注内容 (可选)</label>
                    <input type="text" id="modal-remark" class="form-input" placeholder="将应用到所有新行">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-modal-secondary" onclick="closeDateRowsModal()">取消</button>
                <button class="btn-modal btn-modal-primary" onclick="createMultipleRows()">
                    <i class="fas fa-check"></i> 确认
                </button>
            </div>
        </div>
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
                <select id="calendar-month-select" onchange="renderCalendar()">
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
                <select id="calendar-year-select" onchange="renderCalendar()">
                    <!-- 动态生成年份 -->
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

    <script src="js/stockedit_j3.js"></script>
</body>
</html>
