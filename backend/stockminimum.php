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
    <title>最低库存设置 - 库存管理系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/backend/css/toast.css">
    <link rel="stylesheet" href="/backend/css/smartSearch.css">
    <link rel="stylesheet" href="/backend/css/stockminimum.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>
                    最低库存设置 - <?php echo $display_name; ?>
                </h1>
            </div>
            <div class="header-right-group">
                <div class="header-search">
                    <div class="smartSearchWrapper">
                        <i class="fas fa-search smartSearch-icon"></i>
                        <input type="text" id="unified-filter" class="smartSearch-input" placeholder="搜索货品名称或编号...">
                    </div>
                </div>
                <button class="btn btn-warning" onclick="saveAllSettings()" id="saveAllBtn">
                    <i class="fas fa-save"></i> 批量保存
                </button>
                <button class="btn btn-secondary" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i> 返回库存管理
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container"></div>

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

    <script src="/backend/js/toast.js"></script>
    <script src="js/stockminimum.js?v=<?php echo time(); ?>"></script>
</body>
</html>
