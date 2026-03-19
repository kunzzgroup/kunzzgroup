<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'stock_inventory');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>最低库存设置 - 库存管理系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/backend/css/stockminimum.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                最低库存设置
            </h1>
            <button class="btn btn-secondary" onclick="goBack()">
                <i class="fas fa-arrow-left"></i>
                返回库存管理
            </button>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container"></div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="product-filter">货品名称</label>
                    <input type="text" id="product-filter" class="filter-input" placeholder="搜索货品名称...">
                </div>
                <div class="filter-group">
                    <label for="code-filter">货品编号</label>
                    <input type="text" id="code-filter" class="filter-input" placeholder="搜索货品编号...">
                </div>
                <div class="filter-group">
                    <label for="status-filter">预警状态</label>
                    <select id="status-filter" class="filter-input">
                        <option value="">全部状态</option>
                        <option value="active">已启用</option>
                        <option value="inactive">未启用</option>
                        <option value="warning">库存不足</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" onclick="searchSettings()">
                    <i class="fas fa-search"></i>
                    搜索
                </button>
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-refresh"></i>
                    重置
                </button>
                <button class="btn btn-success" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    刷新数据
                </button>
                <button class="btn btn-warning" onclick="saveAllSettings()">
                    <i class="fas fa-save"></i>
                    批量保存
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h3>最低库存设置</h3>
                <div id="table-stats">
                    显示 <span id="displayed-count">0</span> 个货品
                </div>
            </div>
            
            <div class="table-scroll-container">
                <table class="settings-table" id="settings-table">
                    <thead>
                        <tr>
                            <th>货品名称</th>
                            <th>货品编号</th>
                            <th>最低库存数量</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="settings-tbody">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    <!-- 动态通知内容 -->
    </div>

    <script src="js/stockminimum.js?v=<?php echo time(); ?>"></script>
</body>
</html>
