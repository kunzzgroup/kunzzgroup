<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
// 包含会话验证
require_once 'session_check.php';
// 包含权限验证
require_once 'stock_permission_check.php';

// 服务器端获取系统参数
$system = isset($_GET['system']) ? $_GET['system'] : 'central';
$system_names = [
    'central' => '中央',
    'j1' => 'J1',
    'j2' => 'J2',
    'j3' => 'J3'
];
$display_name = isset($system_names[$system]) ? $system_names[$system] : '中央';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>货品异常 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/stocksot.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>货品异常</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">货品异常</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item" onclick="switchView('list')">总库存</div>
                        <div class="dropdown-item" onclick="switchView('records')">进出货</div>
                        <div class="dropdown-item" onclick="switchView('remark')">货品备注</div>
                        <div class="dropdown-item" onclick="switchView('product')">货品种类</div>
                        <div class="dropdown-item active" onclick="switchView('sot')">货品异常</div>
                    </div>
                </div>
                <button class="selector-button" style="justify-content: center;">
                    <span id="current-stock-type"><?php echo $display_name; ?></span>
                </button>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 统一顶部行 -->
        <div class="unified-header-row">
            <div class="header-summary">
                <div class="summary-title">总异常</div>
                <div class="summary-amount">
                    <span class="currency-symbol">RM</span>
                    <span class="value" id="total-anomaly-value">0.00</span>
                </div>
            </div>

            <div class="date-controls">
                <!-- 日期范围选择器 -->
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <label class="date-label">日期范围</label>
                    <div class="date-range-picker" id="date-range-picker" onclick="toggleCalendar()">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="date-range-display">选择日期范围</span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <label class="date-label-with-icon">
                        <i class="fas fa-clock" style="color: #000000ff;"></i>
                        快速选择
                    </label>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" onclick="toggleQuickSelectDropdown()">
                            <i class="fas fa-calendar-alt"></i>
                            <span id="quick-select-text">时段</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="quick-select-dropdown">
                            <button class="dropdown-item" onclick="selectQuickRange('today')">今天</button>
                            <button class="dropdown-item" onclick="selectQuickRange('yesterday')">昨天</button>
                            <button class="dropdown-item" onclick="selectQuickRange('thisWeek')">本周</button>
                            <button class="dropdown-item" onclick="selectQuickRange('lastWeek')">上周</button>
                            <button class="dropdown-item" onclick="selectQuickRange('thisMonth')">这个月</button>
                            <button class="dropdown-item" onclick="selectQuickRange('lastMonth')">上个月</button>
                            <button class="dropdown-item" onclick="selectQuickRange('thisYear')">今年</button>
                            <button class="dropdown-item" onclick="selectQuickRange('lastYear')">去年</button>
                        </div>
                </div>
            </div>
        </div>

            <div class="header-right-group">
                <div class="header-search">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">搜索</span>
                    <input type="text" id="product-search-filter" class="unified-search-input" 
                        placeholder="输入关键字搜索...">
                </div>
                
                <button class="btn btn-success" onclick="addNewRow()">
                    <i class="fas fa-plus"></i>
                    新增记录
                </button>
                
                <button class="btn btn-primary" onclick="saveAllData()">
                    <i class="fas fa-save"></i>
                    保存数据
                </button>
                
                <button class="btn btn-danger" id="batch-delete-btn" onclick="toggleBatchDelete()">
                    <i class="fas fa-trash-alt"></i>
                    批量删除
                </button>
                <button class="btn btn-success" id="confirm-batch-delete-btn" onclick="confirmBatchDelete()" style="display: none;">
                    <i class="fas fa-check"></i>
                    确认删除
                </button>
                <button class="btn btn-secondary" id="cancel-batch-delete-btn" onclick="cancelBatchDelete()" style="display: none;">
                    <i class="fas fa-times"></i>
                    取消
                </button>
                
                <div class="header-stats">
                    <span>总记录数: <span class="stat-value" id="total-records">0</span></span>
                </div>
            </div>
        </div>
        
        <!-- Excel表格 -->
        <div class="excel-container">          
            <div class="table-scroll-container">
            <table class="excel-table" id="excel-table">
                <thead>
                    <tr>
                        <th style="min-width: 60px;">序号</th>
                        <th style="min-width: 100px;">日期</th>
                        <th style="min-width: 120px;">货品编号</th>
                        <th style="min-width: 200px;">货品</th>
                        <th style="min-width: 80px;">数量</th>
                        <th style="min-width: 100px;">规格</th>
                        <th style="min-width: 120px;">单价</th>
                        <th style="min-width: 100px;">总价</th>
                        <th style="min-width: 100px;">类型</th>
                        <th style="min-width: 100px;" id="action-header">操作</th>
                    </tr>
                </thead>
                <tbody id="excel-tbody">
                    <!-- 动态生成行 -->
                </tbody>
            </table>
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

    <!-- 日历弹窗 -->
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

    <script src="js/stocksot.js?v=<?php echo time(); ?>"></script>
</body>
</html>


