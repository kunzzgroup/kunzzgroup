<?php
if (!headers_sent()) {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>菜单成本管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menucost.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>菜单成本管理</h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                返回
            </a>
        </div>

        <div id="alert-container"></div>

        <!-- 添加菜单项表单 -->
        <div class="add-form-section">
            <div class="add-form-title">添加新菜单项</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>菜单编号 *</label>
                    <input type="text" id="new-menu-code" class="form-input" placeholder="如：A1" required>
                </div>
                <div class="form-group">
                    <label>菜单名称 *</label>
                    <input type="text" id="new-menu-name" class="form-input" placeholder="如：SHAKE SASHIMI 或 鲑鱼刺身" required>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" onclick="addMenuItem()">
                        <i class="fas fa-plus"></i>
                        添加
                    </button>
                </div>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="table-container">
            <div class="table-scroll-container">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>菜单编号</th>
                            <th>菜单名称</th>
                            <th>序号</th>
                            <th>原材料</th>
                            <th>单价 (RM)</th>
                            <th>单位</th>
                            <th>用量</th>
                            <th>成本</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="data-table-body">
                        <tr>
                            <td colspan="9" class="empty-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <div>加载中...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/menucost.js?v=<?php echo time(); ?>"></script>
</body>
</html>

