<?php
if (!headers_sent()) {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}
?>
<?php
// 包含会话验证
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理系统 - 进出货管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/stockedit.css?v=2026">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>中央进出货库存</h1>
            </div>
            <div class="controls">
                <div class="page-selector" onclick="togglePageDropdown()">
                    <i class="fas fa-external-link-alt"></i>
                    切换页面
                    <i class="fas fa-chevron-down"></i>
                    <div class="dropdown-menu" id="page-dropdown">
                        <a href="j1stockedit" class="dropdown-item">
                            <i class="fas fa-file-alt"></i>
                            J1 stock
                        </a>
                        <a href="j2stockedit" class="dropdown-item">
                            <i class="fas fa-file-alt"></i>
                            J2 stock
                        </a>
                    </div>
                </div>
                <button class="back-button" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i>
                    返回上一页
                </button>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 搜索和过滤区域 -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="date-filter">日期</label>
                    <input type="date" id="date-filter" class="filter-input">
                </div>
                <!-- 在这里添加新的产品编号搜索栏 -->
                <div class="filter-group">
                    <label for="code-filter">产品编号</label>
                    <input type="text" id="code-filter" class="filter-input" placeholder="搜索产品编号...">
                </div>
                <div class="filter-group">
                    <label for="product-filter">产品名称</label>
                    <input type="text" id="product-filter" class="filter-input" placeholder="搜索产品名称...">
                </div>
                <div class="filter-group">
                    <label for="receiver-filter">收货人</label>
                    <input type="text" id="receiver-filter" class="filter-input" placeholder="搜索收货人...">
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" onclick="searchData()">
                    <i class="fas fa-search"></i>
                    搜索
                </button>
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-refresh"></i>
                    重置
                </button>
                <button class="btn btn-success" onclick="addNewRow()">
                    <i class="fas fa-plus"></i>
                    新增记录
                </button>
                <button class="btn btn-warning" onclick="exportData()">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
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
                    <label for="add-product-name">产品名称 *</label>
                    <select id="add-product-name" class="form-select" onchange="handleProductChange(this, document.getElementById('add-code-number'))" required>
                        <option value="">请选择产品名称</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-in-qty">入库数量</label>
                    <input type="number" id="add-in-qty" class="form-input" min="0" step="0.01" placeholder="0.00" oninput="handleAddFormOutQuantityChange()">
                </div>
                <div class="form-group">
                    <label for="add-out-qty">出库数量</label>
                    <input type="number" id="add-out-qty" class="form-input" min="0" step="0.01" placeholder="0.00" oninput="handleAddFormOutQuantityChange()">
                </div>
                <div class="form-group">
                    <label for="add-target">目标系统</label>
                    <select id="add-target" class="form-select" disabled>
                        <option value="">请选择</option>
                        <option value="j1">J1</option>
                        <option value="j2">J2</option>
                        <option value="central">Central</option>
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
                    </select>
                </div>
                <div class="form-group">
                    <label for="add-price">单价</label>
                    <div class="currency-display" style="border: 1px solid #d1d5db; border-radius: 8px; background: white;">
                        <span class="currency-symbol">RM</span>
                        <select id="add-price-select" class="form-select" style="border: none; background: transparent; display: none;" onchange="handleAddFormPriceChange()">
                            <option value="">请先选择产品</option>
                        </select>
                        <input type="number" id="add-price" class="currency-input-edit" min="0" step="0.01" placeholder="0.00" style="border: none; background: transparent;">
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-receiver">收货人 *</label>
                    <input type="text" id="add-receiver" class="form-input" placeholder="输入收货人..." required>
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
        
        <!-- 库存表格 -->
        <div class="table-container">
            <div class="action-buttons">
                <div class="stats-info" id="stock-stats">
                    <div class="stat-item">
                        <i class="fas fa-boxes"></i>
                        <span>总记录数: <span class="stat-value" id="total-records">0</span></span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button class="btn btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        刷新数据
                    </button>
                </div>
            </div>
            <div class="table-scroll-container">
            <table class="stock-table" id="stock-table">
                <thead>
                    <tr>
                        <th style="min-width: 100px;">DATE</th>
                        <th style="min-width: 100px;">Code Number</th>
                        <th class="product-name-col">PRODUCT</th>
                        <th style="min-width: 80px;">In</th>
                        <th style="min-width: 80px;">Out</th>
                        <th style="min-width: 100px;">Target</th>
                        <th style="min-width: 100px;">Specification</th>
                        <th style="min-width: 100px;">Price</th>
                        <th style="min-width: 100px;">Total</th>
                        <th class="receiver-col">Name</th>
                        <th style="min-width: 100px;">Remark</th>
                        <th style="min-width: 80px;">操作</th>
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
        <h3>导出数据设置</h3>
        
        <div class="export-form-group">
            <label for="export-start-date">开始日期</label>
            <input type="date" id="export-start-date" required>
        </div>
        
        <div class="export-form-group">
            <label for="export-end-date">结束日期</label>
            <input type="date" id="export-end-date" required>
        </div>
        
        <div class="export-form-group">
            <label>数据类型</label>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="export-in-data" value="in" checked>
                    <label for="export-in-data">入库数据</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="export-out-data" value="out" checked>
                    <label for="export-out-data">出库数据</label>
                </div>
            </div>
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

    <script src="js/stockedit.js?v=2026"></script>
</body>
</html>