<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存产品管理后台 - J2</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/j2stockproductname.css">
</head>
<body>
    <?php $basePath = '../'; include __DIR__ . '/../sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>库存产品管理后台 - J2</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">货品种类</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item" onclick="switchView('list')">总库存</div>
                        <div class="dropdown-item" onclick="switchView('records')">进出货</div>
                        <div class="dropdown-item" onclick="switchView('remark')">货品备注</div>
                        <div class="dropdown-item active" onclick="switchView('product')">货品种类</div>
                    </div>
                </div>
                <div class="system-selector">
                    <button class="selector-button" onclick="toggleSystemSelector()">
                        <span id="current-system">J2</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="system-selector-dropdown">
                        <div class="dropdown-item" onclick="switchSystem('overview')">总览</div>
                        <div class="dropdown-item" onclick="switchSystem('central')">中央</div>
                        <div class="dropdown-item" onclick="switchSystem('j1')">J1</div>
                        <div class="dropdown-item active" onclick="switchSystem('j2')">J2</div>
                        <div class="dropdown-item" onclick="switchSystem('j3')">J3</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 搜索过滤栏 -->
        <div class="filter-bar">
            <div class="filter-group">
                <div class="filter-item">
                    <label>搜索产品</label>
                    <input type="text" class="filter-input" id="product-search-filter" placeholder="搜索产品编号或产品名字">
                </div>
                <div class="filter-item">
                    <label>批准状态</label>
                    <select class="filter-input" id="approval-status-filter">
                        <option value="">所有状态</option>
                        <option value="approved">已批准</option>
                        <option value="pending">待批准</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-group">
                <button class="btn btn-success" onclick="addNewRow()">
                    <i class="fas fa-plus"></i>
                    添加新记录
                </button>
                <button class="btn btn-primary" onclick="saveAllData()">
                    <i class="fas fa-save"></i>
                    保存所有数据
                </button>
                
                <div class="stats-info" id="stock-stats">
                    <div class="stat-item">
                        <i class="fas fa-boxes"></i>
                        <span>总记录数: <span class="stat-value" id="total-records">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-check-circle"></i>
                        <span>已批准: <span class="stat-value" id="approved-count">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <span>待批准: <span class="stat-value" id="pending-count">0</span></span>
                    </div>
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
                        <th style="min-width: 120px;">产品编号</th>
                        <th style="min-width: 200px;">产品名字</th>
                        <th style="min-width: 150px;">规格</th>
                        <th style="min-width: 120px;">货物类型</th>
                        <th style="min-width: 150px;">供应商</th>
                        <th style="min-width: 120px;">申请人</th>
                        <th style="min-width: 100px;">系统分配</th>
                        <th style="min-width: 120px;">状态</th>
                        <th style="min-width: 100px;">操作</th>
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

    <!-- Data container for JavaScript (hidden) -->
    <div id="stock-data" 
         style="display: none;"
         data-can-approve='<?php echo json_encode($canApprove, JSON_UNESCAPED_UNICODE); ?>'>
    </div>

    <script src="../js/j2stockproductname.js"></script>
</body>
</html>
