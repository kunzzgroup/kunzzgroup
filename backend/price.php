<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'price_comparison');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
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
    <title>餐厅价格对比</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../animation.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="css/price.css?v=<?php echo time(); ?>">
    
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>餐厅价格对比</h1>
            <div style="display: flex; gap: 12px; align-items: center;">
                <!-- 对比模式选择器 -->
                <div class="restaurant-selector">
                    <button class="selector-button" onclick="toggleComparisonModeSelector()">
                        <span id="current-comparison-mode">餐厅对比</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="comparison-mode-dropdown">
                        <div class="dropdown-item active" data-mode="restaurant" onclick="switchComparisonMode('restaurant')">餐厅对比</div>
                        <div class="dropdown-item" data-mode="supplier" onclick="switchComparisonMode('supplier')">批发商对比</div>
                    </div>
                </div>
                <!-- 餐厅选择器 -->
                <div class="restaurant-selector">
                    <button class="selector-button" onclick="toggleRestaurantSelector()">
                        <span id="current-restaurant">总览</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="restaurant-dropdown">
                        <div class="dropdown-item active" data-restaurant="overview" onclick="switchRestaurant('overview')">总览</div>
                        <!-- 动态加载的餐厅列表 -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 搜索和过滤区域 -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-left">
                    <div class="filter-group" style="flex: 0 0 180px;">
                    <label>搜索</label>
                        <input type="text" id="search-input" class="filter-input" placeholder="输入货品名称" style="text-transform: uppercase;">
                </div>
                    <div class="filter-group" style="flex: 0 0 140px;">
                    <label>类型</label>
                    <select id="type-filter" class="filter-select">
                        <option value="">全部类型</option>
                    </select>
                </div>
                </div>
                <div class="filter-right">
                    <button class="btn btn-success" onclick="openAddRestaurantModal()">
                        <i class="fas fa-store"></i>
                        新增餐馆
                    </button>
                    <button class="btn btn-success" id="add-record-btn" style="display: none;" onclick="openAddRowsModal()">
                        <i class="fas fa-plus"></i>
                        新增记录
                    </button>
                    <button class="btn btn-primary" id="batch-save-btn" style="display: none;" onclick="batchSaveNewRows()">
                        <i class="fas fa-save"></i>
                        批量保存
                    </button>
                    <button class="btn btn-danger" id="batch-delete-btn" style="display: none;" onclick="enterBatchDeleteMode()">
                        <i class="fas fa-trash-alt"></i>
                        批量删除
                    </button>
                    <button class="btn btn-success" id="confirm-batch-delete-btn" style="display: none;" disabled onclick="confirmBatchDelete()">
                        <i class="fas fa-check"></i>
                        确认删除
                    </button>
                    <button class="btn btn-secondary" id="cancel-batch-delete-btn" style="display: none;" onclick="cancelBatchDelete()">
                        <i class="fas fa-times"></i>
                        取消
                    </button>
                </div>
            </div>
        </div>

        <!-- 价格对比表格 -->
        <div class="table-container">
            <div class="table-scroll-container">
                <table class="price-table" id="price-table">
                    <thead id="price-thead">
                        <tr>
                            <th style="min-width: 60px;">序号</th>
                            <th style="min-width: 150px;">货品名称</th>
                            <th style="min-width: 100px;">类型</th>
                            <!-- 动态加载的餐厅列 -->
                            <th style="min-width: 80px;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="price-tbody">
                        <tr>
                            <td colspan="4" style="padding: 20px; color: #6b7280; text-align: center;">
                                加载中...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
        <!-- 动态通知 -->
    </div>

    <!-- 行数选择弹窗 -->
    <div id="add-rows-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">新增记录</h3>
                <button class="modal-close" onclick="closeAddRowsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="rows-count">要创建的行数 *</label>
                        <input type="number" id="rows-count" class="form-input" min="1" max="100" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="default-type">默认类型（可选）</label>
                        <select id="default-type" class="form-select">
                            <option value="">请选择类型</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-modal-secondary" onclick="closeAddRowsModal()">取消</button>
                <button class="btn-modal btn-modal-primary" onclick="createMultipleRows()">
                    <i class="fas fa-plus"></i>
                    创建记录
                </button>
            </div>
        </div>
    </div>

    <!-- 新增餐馆模态框 -->
    <div id="add-restaurant-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="restaurant-modal-title">新增餐馆</h3>
                <button class="modal-close" onclick="closeAddRestaurantModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="restaurant-name-cn">餐馆中文名称 *</label>
                        <input type="text" id="restaurant-name-cn" class="form-input" placeholder="输入餐馆中文名称..." required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label for="restaurant-name-en">餐馆英文名称 *</label>
                        <input type="text" id="restaurant-name-en" class="form-input" placeholder="Enter restaurant English name..." required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>
                <div class="existing-restaurants">
                    <div class="section-title">已存在餐馆</div>
                    <div id="restaurant-list" class="restaurant-list">
                        <div class="empty-placeholder">暂无餐馆</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-modal-secondary" onclick="closeAddRestaurantModal()">取消</button>
                <button class="btn-modal btn-modal-primary" onclick="saveRestaurant()">
                    <i class="fas fa-save"></i>
                    <span id="restaurant-modal-submit-text">保存</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 新增记录模态框 -->
    <div id="add-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">新增价格对比记录</h3>
                <button class="modal-close" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="add-product-name">货品名称 *</label>
                        <input type="text" id="add-product-name" class="form-input" placeholder="输入货品名称..." required>
                    </div>
                    <div class="form-group">
                        <label for="add-type">类型</label>
                        <select id="add-type" class="form-select">
                            <option value="">请选择类型</option>
                        </select>
                    </div>
                    <!-- 动态生成的餐厅价格输入框 -->
                    <div id="add-restaurant-prices" class="form-group full-width" style="grid-column: 1 / -1;">
                        <label style="margin-bottom: 12px; display: block; font-weight: 600; color: #000000ff;">餐厅价格</label>
                        <div id="add-prices-container" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                            <!-- 动态生成 -->
                    </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-modal-secondary" onclick="closeAddModal()">取消</button>
                <button class="btn-modal btn-modal-primary" onclick="saveFoodRecord()">
                    <i class="fas fa-save"></i>
                    保存
                </button>
            </div>
        </div>
    </div>

    <script src="js/price.js?v=<?php echo time(); ?>"></script>
</body>
</html>


