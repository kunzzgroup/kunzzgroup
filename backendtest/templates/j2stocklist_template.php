<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>J2库存汇总 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/j2stocklist.css">
</head>
<body>
<?php $basePath = '../'; include __DIR__ . '/../sidebar.php'; ?>
        <div class="header">
            <div>
                <h1>J2库存汇总报表</h1>
            </div>
            <div class="controls">
                <button class="back-button" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i>
                    返回上一页
                </button>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 总库存和搜索区域左右排列 -->
        <div class="main-content-row">
            <!-- 左侧：总库存 -->
            <div class="summary-section">
                <div class="summary-card total-value">
                    <h3>J2总库存</h3>
                    <div class="summary-currency-display">
                        <span class="currency-symbol">RM</span>
                        <span class="value" id="total-value">0.00</span>
                    </div>
                </div>
            </div>
            
            <!-- 右侧：搜索和过滤区域 -->
            <div class="filter-section">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="product-filter">产品名称</label>
                        <input type="text" id="product-filter" class="filter-input" placeholder="搜索产品名称...">
                    </div>
                    <div class="filter-group">
                        <label for="code-filter">产品编号</label>
                        <input type="text" id="code-filter" class="filter-input" placeholder="搜索产品编号...">
                    </div>
                    <div class="filter-group">
                        <label for="spec-filter">规格单位</label>
                        <input type="text" id="spec-filter" class="filter-input" placeholder="搜索规格单位...">
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
                    <button class="btn btn-warning" onclick="exportData()">
                        <i class="fas fa-download"></i>
                        导出CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Stock Summary Table -->
        <div class="table-container">
            <div class="action-buttons">
                <div class="stats-info" id="stock-stats">
                    <div class="stat-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>显示记录: <span class="stat-value" id="displayed-records">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-boxes"></i>
                        <span>总记录: <span class="stat-value" id="total-records">0</span></span>
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
                            <th>No.</th>
                            <th>PRODUCT</th>
                            <th>Code Number</th>
                            <th>Total Stock</th>
                            <th>Specification</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody id="stock-tbody">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../js/j2stocklist.js"></script>
</body>
</html>
