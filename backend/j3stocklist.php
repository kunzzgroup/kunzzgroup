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
// 包含会话验证
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/backend/css/j3stocklist.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    

    <div class="container">
        <div class="header">
            <div>
                <h1 id="page-title">总库存 - J3</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">总库存</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item active" onclick="switchView('list', event)">总库存</div>
                        <div class="dropdown-item" onclick="switchView('records', event)">进出货</div>
                        <div class="dropdown-item" onclick="switchView('product', event)">货品种类</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        

        <!-- J3库存页面 -->
        <div id="j3-page" class="page-section active">
            <div class="unified-header-row">
                <div class="header-summary">
                    <div class="summary-title">总库存</div>
                    <div class="summary-amount">
                        <span class="currency-symbol">RM</span>
                        <span class="value" id="j3-total-value">0.00</span>
                    </div>
                </div>
                
                <div class="type-grid-container">
                    <div class="type-grid-item">
                        <div class="grid-title">Service Line</div>
                        <div class="grid-value" id="j3-service-line-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Sake</div>
                        <div class="grid-value" id="j3-sake-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Kitchen</div>
                        <div class="grid-value" id="j3-kitchen-value">0.00</div>
                    </div>
                    <div class="type-grid-item">
                        <div class="grid-title">Sushi Bar</div>
                        <div class="grid-value" id="j3-sushi-bar-value">0.00</div>
                    </div>
                </div>
                
                <div class="header-right-section">
                <div class="header-search">
                    <div class="smartSearchWrapper">
                        <i class="fas fa-search smartSearch-icon"></i>
                        <input type="text" id="j3-unified-filter" class="smartSearch-input"
                            placeholder="搜索货品名称、编号或规格单位...">
                    </div>
                </div>
                
                <button class="btn btn-warning" onclick="exportData('j3')">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <div class="header-stats">
                    <span>显示记录: <span class="stat-value" id="j3-displayed-records">0</span></span>
                    <span>总记录: <span class="stat-value" id="j3-total-records">0</span></span>
                </div>
                </div>
            </div>

            <div class="table-container">                              
                <div class="table-scroll-container">
                    <table class="stock-table" id="j3-stock-table">
                        <thead>
                            <tr>
                                <th>序号.</th>
                                <th>货品编号</th>
                                <th>货品</th>
                                <th>库存总量</th>
                                <th>规格</th>
                                <th>单价</th>
                                <th>总价</th>
                            </tr>
                        </thead>
                        <tbody id="j3-stock-tbody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
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

    <script src="js/j3stocklist.js?v=<?php echo time(); ?>"></script>
</body>
</html>
