<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'stock_inventory');

// stock_recycle.php
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>回收站 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="/backend/css/stockeditall.css?v=<?php echo time(); ?>" />
    <style>
        .recycle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .recycle-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .recycle-title h1 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }
        .back-btn {
            background: #f3f4f6;
            color: #4b5563;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .back-btn:hover {
            background: #e5e7eb;
        }
        .action-btns {
            display: flex;
            gap: 10px;
        }
        .restore-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .perm-delete-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="recycle-header">
            <div class="recycle-title">
                <button class="back-btn" onclick="window.location.href='stockeditall'">
                    <i class="fas fa-arrow-left"></i> 返回库存
                </button>
                <h1><i class="fas fa-trash-alt" style="color: #6b7280;"></i> 回收站</h1>
            </div>
            <div class="controls">
                <div class="header-search">
                    <input type="text" id="recycle-search" class="unified-search-input" placeholder="在已删除记录中搜索...">
                </div>
            </div>
        </div>

        <div id="alert-container"></div>

        <div class="table-container">
            <div class="table-scroll-container">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>删除时间</th>
                            <th>删除人</th>
                            <th>系统</th>
                            <th>日期</th>
                            <th>货品</th>
                            <th>进/出</th>
                            <th>规格</th>
                            <th>收货人</th>
                            <th style="width: 150px;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="recycle-tbody">
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">正在加载已删除记录...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/stock_recycle.js?v=<?php echo time(); ?>"></script>
</body>
</html>
