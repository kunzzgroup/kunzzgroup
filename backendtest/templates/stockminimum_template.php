<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>最低库存设置 - 库存管理系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../css/stockminimum.css" rel="stylesheet">
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

        <!-- Stats Section (Assuming it was there or needs to be there, I see updateStats in JS) -->
        <!-- Wait, I missed the stats section in the original file view? -->
        <!-- Let me check lines 149-181 of original file for CSS stats section. -->
        <!-- Ah, updateStats function (lines 674) updates IDs: total-products, configured-alerts, unconfigured-alerts. -->
        <!-- Where are these elements in HTML? -->
        <!-- I must have missed them in the view or they were not in the first 480 lines or lines 800+. -->
        <!-- The original file view I got 479-563. -->
        <!-- I don't see stats section there! -->
        <!-- But updateStats is there. -->
        <!-- Maybe it was removed or I missed a block. -->
        <!-- Let's check lines 561-564 is toast container. -->
        <!-- Lines 534 is Table Container. -->
        <!-- Maybe Stats Section is between Filter and Table? -->
        <!-- Let's check lines 530-535 again. -->
        <!-- Line 533 is empty space. -->
        <!-- Maybe it got deleted or I just didn't see it because it was off screen or something? -->
        <!-- Or maybe it IS missing in HTML but present in JS? -->
        <!-- But CSS defines .stats-section (lines 149-154). -->
        <!-- It MUST be there. -->
        <!-- Ah, I viewed 1-800. -->
        <!-- The CSS is lines 13-476. -->
        <!-- The HTML starts at 478. -->
        <!-- Lines 494-532 is Filter Section. -->
        <!-- Lines 535-558 is Table Container. -->
        <!-- Where is .stats-section div? -->
        <!-- It seems it is MISSING in the HTML structure I read! -->
        <!-- If it is missing, then `updateStats` JS function will fail (document.getElementById returns null). -->
        <!-- Wait, if JS has `updateStats` and it runs, it would error. -->
        <!-- Unless `document.getElementById` returns null and `.textContent` throws error. -->
        <!-- But `stockminimum.php` was supposedly working? -->
        <!-- Maybe I should add it to the template if it's supposed to be there. -->
        <!-- The CSS suggests it should be there. -->
        <!-- I will add it between Filter and Table. -->
        
        <div class="stats-section">
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <h3>总货品数</h3>
                <div class="value" id="total-products">0</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-bell"></i>
                <h3>已设置预警</h3>
                <div class="value" id="configured-alerts">0</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-bell-slash"></i>
                <h3>未设置预警</h3>
                <div class="value" id="unconfigured-alerts">0</div>
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

    <script src="../js/stockminimum.js"></script>
</body>
</html>
