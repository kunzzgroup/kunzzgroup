<?php
require_once __DIR__ . '/permission_guard.php';
requireStockView('records');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
// 包含会话验证
require_once 'session_check.php';

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
    <title>库存管理系统</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../animation.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="css/stockeditall.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="css/sidebar.css?v=<?php echo time(); ?>" />
    
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1 id="page-title">进出货 - <?php echo $display_name; ?></h1>
            </div>
            <div class="controls">
                <div class="mobile-selector" id="mobile-selector" style="display: none;">
                    <a id="mobile-link-button" class="selector-button" href="../j3/j3stockeditmobile">手机版</a>
                </div>
                <div class="mobile-selector" id="mobile-selector-j1" style="display: none;">
                    <a id="mobile-link-button-j1" class="selector-button" href="../j1/j1stockeditmobile">手机版</a>
                </div>
                <div class="mobile-selector" id="mobile-selector-j2" style="display: none;">
                    <a id="mobile-link-button-j2" class="selector-button" href="../j2/j2stockeditmobile">手机版</a>
                </div>
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">进出货</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item" onclick="switchView('list')">总库存</div>
                        <div class="dropdown-item active" onclick="switchView('records')">进出货</div>
                        <div class="dropdown-item" onclick="switchView('remark')">货品备注</div>
                        <div class="dropdown-item" onclick="switchView('product')">货品种类</div>
                        <div class="dropdown-item" onclick="switchView('sot')">货品异常</div>
                    </div>
                </div>
                <div class="system-selector">
                    <button class="selector-button" onclick="toggleStockSelector()">
                        <span id="current-stock-type"><?php echo $display_name; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="stock-dropdown">
                        <a href="#" class="dropdown-item active" onclick="switchStock('central', event); return false;" data-type="central">
                            中央
                        </a>
                        <a href="#" class="dropdown-item" onclick="switchStock('j1', event); return false;" data-type="j1">
                            J1
                        </a>
                        <a href="#" class="dropdown-item" onclick="switchStock('j2', event); return false;" data-type="j2">
                            J2
                        </a>
                        <a href="#" class="dropdown-item" onclick="switchStock('j3', event); return false;" data-type="j3">
                            J3
                        </a>
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
                        <label for="selected-date">选择日期 *</label>
                        <input type="date" id="selected-date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="rows-count">要创建的行数 *</label>
                        <input type="number" id="rows-count" class="form-input" min="1" max="50" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="new-record-remark">备注</label>
                        <input type="text" id="new-record-remark" class="form-input" placeholder="输入备注（可选）">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-modal btn-modal-secondary" onclick="closeDateRowsModal()">取消</button>
                    <button class="btn-modal btn-modal-primary" onclick="createMultipleRows()">
                        <i class="fas fa-plus"></i>
                        创建记录
                    </button>
                </div>
            </div>
        </div>

        <!-- 新增记录表单 -->
        <div id="add-form" class="add-form">
            <h3 style="color: #583e04; margin-bottom: 16px;">新增库存记录</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="add-date">日期 *</label>
                    <input type="date" id="add-date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="add-time">时间 *</label>
                    <input type="time" id="add-time" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="add-product-name">货品名称 *</label>
                    <select id="add-product-name" class="form-select" onchange="handleProductChange(this, document.getElementById('add-code-number'))" required>
                        <option value="">请选择货品名称</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-in-qty">入库数量</label>
                    <input type="number" id="add-in-qty" class="form-input" min="0" step="0.001" placeholder="0" oninput="window.handleAddFormOutQuantityChange()">
                </div>
                <div class="form-group">
                    <label for="add-out-qty">出库数量</label>
                    <input type="number" id="add-out-qty" class="form-input" min="0" step="0.001" placeholder="0" oninput="window.handleAddFormOutQuantityChange()" onchange="window.handleAddFormOutQuantityChange()">
                </div>
                <div class="form-group" id="target-form-group">
                    <label for="add-target">目标系统</label>
                    <select id="add-target" class="form-select" disabled>
                        <option value="">请选择</option>
                        <option value="j1">J1</option>
                        <option value="j2">J2</option>
                        <option value="j3">J3</option>
                        <option value="central">中央</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-specification">规格单位 *</label>
                    <select id="add-specification" class="form-select" required>
                        <option value="">请选择规格</option>
                        <option value="Tub">Tub</option>
                        <option value="Kilo">Kilo</option>
                        <option value="Piece">Piece</option>
                        <option value="Bottle">Bottle</option>
                        <option value="Box">Box</option>
                        <option value="Packet">Packet</option>
                        <option value="Carton">Carton</option>
                        <option value="Tin">Tin</option>
                        <option value="Roll">Roll</option>
                        <option value="Nos">Nos</option>
                        <option value="mL">mL</option>
                        <option value="Glass">Glass</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-price">单价</label>
                    <div class="currency-display" style="border: 1px solid #d1d5db; border-radius: 8px; background: white;">
                        <span class="currency-symbol">RM</span>
                        <select id="add-price-select" class="form-select" style="border: none; background: transparent; display: none;" onchange="handleAddFormPriceChange()">
                            <option value="">请先选择货品</option>
                        </select>
                        <input type="number" id="add-price" class="currency-input-edit" min="0" step="0.01" placeholder="0.00" style="border: none; background: transparent;">
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-receiver">收货人 *</label>
                    <div class="combobox-container" id="add-receiver">
                        <input 
                            type="text" 
                            class="combobox-input" 
                            id="add-receiver-input"
                            value="" 
                            placeholder="输入或选择收货人..."
                            autocomplete="off"
                            data-field="receiver"
                            data-type="receiver"
                        />
                        <i class="fas fa-chevron-down combobox-arrow"></i>
                        <div class="combobox-dropdown" id="add-receiver-dropdown">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-applicant">申请人 *</label>
                    <input type="text" id="add-applicant" class="form-input" placeholder="输入申请人..." required>
                </div>
                <div class="form-group">
                    <label for="add-code-number">编号</label>
                    <select id="add-code-number" class="form-select" onchange="handleCodeNumberChange(this, document.getElementById('add-product-name'))">
                        <option value="">请选择编号</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-remark">备注</label>
                    <input type="text" id="add-remark" class="form-input" placeholder="输入备注...">
                </div>
                <div class="form-group" id="type-form-group">
                    <label for="add-type">类型</label>
                    <select id="add-type" class="form-select">
                        <option value="">请选择类型</option>
                        <option value="Kitchen">Kitchen</option>
                        <option value="Sushi Bar">Sushi Bar</option>
                        <option value="Service Line">Service Line</option>
                        <option value="Sake">Sake</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-product-remark">货品备注</label>
                    <input type="checkbox" id="add-product-remark" onchange="toggleRemarkNumber()">
                </div>
                <div class="form-group">
                    <label for="add-remark-number">备注编号</label>
                    <div class="remark-number-input-wrapper" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 8px; background: white; padding: 0;" id="add-remark-wrapper">
                        <input type="text" id="add-remark-prefix" class="form-input" placeholder="" style="border: none; border-radius: 8px 0 0 8px; width: 30px; text-align: center; background: transparent;" oninput="this.value = this.value.toUpperCase();" disabled>
                        <span style="padding: 0 4px; color: #6b7280; font-weight: bold;">-</span>
                        <input type="text" id="add-remark-suffix" class="form-input" placeholder="" style="border: none; border-radius: 0 8px 8px 0; width: 30px; text-align: center; background: transparent;" oninput="this.value = this.value.toUpperCase();" disabled>
                    </div>
                    <input type="hidden" id="add-remark-number">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" onclick="toggleAddForm()">
                    <i class="fas fa-times"></i>
                    取消
                </button>
                <button class="btn btn-success" onclick="saveNewRecord()">
                    <i class="fas fa-save"></i>
                    保存
                </button>
            </div>
        </div>

        <div class="unified-header-row">
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
                    <input type="text" id="unified-filter" class="unified-search-input" 
                        placeholder="输入关键字搜索...">
                </div>
                
                <button class="btn btn-success" onclick="showDateRowsModal()">
                    <i class="fas fa-plus"></i>
                    新增记录
                </button>
                
                <button class="btn btn-warning" onclick="exportData()">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <div class="batch-actions" style="display: flex; gap: 8px;">
                    <button class="btn btn-primary" id="batch-save-btn" onclick="batchSaveNewRows()" style="display: none;">
                        <i class="fas fa-save"></i>
                        批量保存
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
                </div>
                
                <div class="header-stats">
                    <span>总记录数: <span class="stat-value" id="total-records">0</span></span>
                </div>
            </div>
        </div>
        
        <!-- 库存表格 -->
        <div class="table-container">
            <div class="table-scroll-container">
            <table class="stock-table" id="stock-table">
                <thead>
                    <tr>
                        <th style="min-width: 100px;">日期</th>
                        <th style="min-width: 100px;">货品编号</th>
                        <th class="product-name-col">货品</th>
                        <th style="min-width: 80px;">进货</th>
                        <th style="min-width: 80px;">出货</th>
                        <th style="min-width: 100px;">收货单位</th>
                        <th style="min-width: 100px;">规格</th>
                        <th style="min-width: 100px;">单价</th>
                        <th style="min-width: 100px;">总价</th>
                        <th style="min-width: 80px;" id="type-header">类型</th>
                        <th style="min-width: 80px;">货品备注</th>
                        <th style="min-width: 100px;">备注编号</th>
                        <th class="receiver-col">供应商/出货人</th>
                        <th style="min-width: 60px;">创建人</th>
                        <th style="min-width: 100px;">备注</th>
                        <th style="min-width: 80px;" id="action-header">操作</th>
                    </tr>
                </thead>
                <tbody id="stock-tbody">
                    <!-- 动态生成行 -->
                </tbody>
            </table>
            </div>
        </div>

        <!-- 导出数据弹窗 -->
        <div id="export-modal" class="export-modal">
            <div class="export-modal-content">
                <button class="close-export-modal" onclick="closeExportModal()">&times;</button>
                <h3>生成Excel</h3>
                
                <div class="export-form-group">
                    <label for="export-start-date">开始日期</label>
                    <input type="text" id="export-start-date" placeholder="DD/MM/YYYY" pattern="\d{2}/\d{2}/\d{4}" required>
                    <small style="color: #6b7280; font-size: 12px;">可以选择过去或未来的日期</small>
                </div>
                
                <div class="export-form-group">
                    <label for="export-end-date">结束日期</label>
                    <input type="text" id="export-end-date" placeholder="DD/MM/YYYY" pattern="\d{2}/\d{2}/\d{4}" required>
                    <small style="color: #6b7280; font-size: 12px;">可以选择过去或未来的日期</small>
                </div>
                
                <div class="export-form-group export-central-only" id="export-system-group">
                    <label for="export-system">店面</label>
                    <select id="export-system" required onchange="handleExportSystemChange()">
                        <option value="">请选择系统</option>
                        <option value="j1">J1</option>
                        <option value="j2">J2</option>
                        <option value="j3">J3</option>
                    </select>
                </div>

                <div class="export-form-group export-central-only" id="export-invoice-date-group">
                    <label for="export-invoice-date">发票日期</label>
                    <input type="text" id="export-invoice-date" placeholder="DD/MM/YYYY" pattern="\d{2}/\d{2}/\d{4}">
                    <small style="color: #6b7280; font-size: 12px;">可以选择过去或未来的日期</small>
                </div>

                <div class="export-form-group export-central-only" id="export-invoice-suffix-group">
                    <label for="export-invoice-suffix">发票号码后三位 *</label>
                    <input type="text" id="export-invoice-suffix" placeholder="输入三位数字（例如：001）" maxlength="3" pattern="[0-9]{3}" required>
                    <small style="color: #6b7280; font-size: 12px;">格式示例：J1-2510-001（店面-年月-序号）</small>
                </div>

                
                <div class="export-modal-actions">
                    <button class="btn btn-secondary" onclick="closeExportModal()">
                        <i class="fas fa-times"></i>
                        取消
                    </button>
                    <button class="btn btn-success" onclick="confirmExport()">
                        <i class="fas fa-download"></i>
                        导出Excel
                    </button>
                </div>
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

    <style>
    .user-hover-box {
        position: absolute;
        background: #fff;
        border: 1px solid #ddd;
        padding: 10px 14px;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        font-size: 13px;
        z-index: 9999;
        display: none;
        min-width: 160px;
        pointer-events: none;
    }
    .created-user {
        cursor: pointer;
        color: #2c7be5;
        font-weight: 500;
    }
    </style>
    <div id="userHoverBox" class="user-hover-box"></div>
    <script src="js/stockeditall.js?v=<?php echo time(); ?>"></script>
    <script>
    (function() {
        const hoverBox = document.getElementById("userHoverBox");
        if (!hoverBox) return;
        document.addEventListener("mouseover", function(e) {
            if (e.target.classList.contains("created-user")) {
                const user = e.target.getAttribute("data-user") || "-";
                const time = e.target.getAttribute("data-time") || "-";
                hoverBox.innerHTML = '<div style="font-weight:600;">' + user + '</div>' + '<div style="margin-top:4px;color:#666;">操作时间：' + time + '</div>';
                hoverBox.style.display = "block";
            }
        });
        document.addEventListener("mousemove", function(e) {
            if (hoverBox.style.display === "block") {
                hoverBox.style.top = (e.pageY + 12) + "px";
                hoverBox.style.left = (e.pageX + 12) + "px";
            }
        });
        document.addEventListener("mouseout", function(e) {
            if (e.target.classList.contains("created-user")) {
                hoverBox.style.display = "none";
            }
        });
    })();
    </script>
</body>
<script>
// 页面权限检查已在 applyPagePermissions 函数中处理，这里不需要重复代码
</html>
