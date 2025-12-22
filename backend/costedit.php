<?php
// 包含会话验证
require_once 'session_check.php';

// 防止浏览器缓存旧版 JS/HTML，避免修复已上线但用户端仍加载旧代码导致持续报错
// 注意：必须在任何输出之前设置 header
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$reportPermissions = ['kpi', 'cost'];
$restaurantPermissions = ['j1', 'j2', 'j3'];
$reportLabelMap = [
    'kpi' => 'KPI 报表',
    'cost' => '成本报表',
];
$restaurantConfigPhp = [
    'j1' => ['name' => 'J1', 'number' => 1],
    'j2' => ['name' => 'J2', 'number' => 2],
    'j3' => ['name' => 'J3', 'number' => 3],
];

if (!isset($_SESSION)) {
    @session_start();
}

if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->prepare("SELECT report_permissions_json, restaurant_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if ($row = $stmt->fetch()) {
            if (!empty($row['report_permissions_json'])) {
                $decoded = json_decode($row['report_permissions_json'], true);
                if (is_array($decoded) && !empty($decoded)) {
                    $filtered = array_values(array_intersect($decoded, ['kpi', 'cost']));
                    if (!empty($filtered)) {
                        $reportPermissions = $filtered;
                    }
                }
            }
            if (!empty($row['restaurant_permissions_json'])) {
                $decoded = json_decode($row['restaurant_permissions_json'], true);
                if (is_array($decoded) && !empty($decoded)) {
                    $filtered = array_values(array_intersect($decoded, ['j1', 'j2', 'j3']));
                    if (!empty($filtered)) {
                        $restaurantPermissions = $filtered;
                    }
                }
            }
        }
    } catch (Throwable $e) {
        // 忽略数据库错误，使用默认权限
    }
}

$reportPermissions = array_values(array_intersect(['kpi', 'cost'], $reportPermissions));
if (empty($reportPermissions)) {
    $reportPermissions = ['kpi', 'cost'];
}

$restaurantPermissions = array_values(array_intersect(['j1', 'j2', 'j3'], $restaurantPermissions));
if (empty($restaurantPermissions)) {
    $restaurantPermissions = ['j1', 'j2', 'j3'];
}

if (!in_array('cost', $reportPermissions, true)) {
    if (in_array('kpi', $reportPermissions, true)) {
        header('Location: kpiedit.php');
        exit();
    }
    $reportPermissions[] = 'cost';
}
$reportPermissions = array_values(array_intersect(['kpi', 'cost'], $reportPermissions));

$restaurantConfigAllowed = array_intersect_key($restaurantConfigPhp, array_flip($restaurantPermissions));
if (empty($restaurantConfigAllowed)) {
    $restaurantPermissions = ['j1', 'j2', 'j3'];
    $restaurantConfigAllowed = $restaurantConfigPhp;
}

$defaultRestaurant = $restaurantPermissions[0];
$showReportDropdown = count($reportPermissions) > 1;
$showRestaurantDropdown = count($restaurantPermissions) > 1;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅成本管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            font-size: clamp(8px, 0.74vw, 14px);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            color: #111827;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(22px, 1.67vw, 32px);
        }
        
        .header h1 {
            font-size: clamp(20px, 2.6vw, 50px);
            font-weight: bold;
            color: #000000ff;
        }
        
        .header .controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* 餐厅选择器样式 */
        .restaurant-selector {
            background: white;
            border-radius: clamp(6px, 0.63vw, 12px);
            padding: 6px;
            display: flex;
            gap: 0;
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            align-items: center;
        }

        .restaurant-prefix {
            background: #f99e00;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.42vw, 8px) 0 0 clamp(4px, 0.42vw, 8px);
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 700;
            width: clamp(26px, 2.08vw, 40px);
            text-align: center;
        }

        .number-dropdown {
            position: relative;
            display: inline-block;
        }

        .number-btn {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: 0 8px 8px 0;
            border: none;
            cursor: pointer;
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
            color: #000000ff;
            width: clamp(30px, 3.13vw, 60px);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .number-btn:hover {
            background: rgba(88, 62, 4, 0.1);
        }

        .number-dropdown-menu {
            display: none;
            position: absolute;
            top: clamp(29px, 2.29vw, 44px);
            right: clamp(-24px, -1.25vw, -15px);
            background: white;
            border: 2px solid #000000ff;
            border-radius: clamp(8px, 0.63vw, 12px);
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.15);
            z-index: 1000;
            padding: clamp(6px, 0.42vw, 8px);
            min-width: 90px;
        }

        .number-dropdown-menu.show {
            display: block;
        }

        .number-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
        }

        .number-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: clamp(20px, 2.08vw, 40px);
            height: clamp(20px, 2.08vw, 40px);
            border: 2px solid #e5e7eb;
            background: white;
            color: #000000ff;
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border-radius: clamp(4px, 0.42vw, 8px);
            transition: all 0.2s ease;
        }

        .number-item:hover {
            background-color: #f3f4f6;
            color: black;
            border-color: #d1d5db;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .number-item.selected {
            background: #f99e00;
            color: white;
            border-color: #f99e00;
        }

        .back-button {
            background-color: #583e04;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .back-button:hover {
            background-color: #462d03;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        /* 月份选择器 */
        .month-selector {
            background: white;
            border-radius: 12px;
            padding: clamp(8px, 0.83vw, 16px) clamp(16px, 1.25vw, 24px);
            margin-bottom: clamp(16px, 1.25vw, 24px);
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
        }

        .month-selector select {
            padding: clamp(4px, 0.42vw, 8px) clamp(8px, 0.83vw, 16px);
            border: 1px solid #d1d5db;
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 500;
            background: white;
            color: #000000ff;
            cursor: pointer;
        }

        .month-selector select:focus {
            outline: none;
            border-color: #000000ff;
            box-shadow: 0 0 0 3px rgba(20, 14, 0, 0.1);
        }

        .month-selector label {
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 600;
            color: #000000ff;
        }

        /* Excel样式表格 */
        .excel-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.1);
            overflow: hidden;
            border: 2px solid #000000ff;
            height: 70vh;
            display: flex;
            flex-direction: column;
        }

        .table-scroll-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed;
        }

        .excel-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .excel-table th {
            font-size: clamp(8px, 0.74vw, 14px);
            background: #636363;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) 0;
            text-align: center;
            font-weight: 600;
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 100;
            white-space: nowrap;
        }

        .excel-table td {
            font-size: clamp(8px, 0.74vw, 14px);
            padding: 0;
            border: 1px solid #d1d5db;
            text-align: center;
            position: relative;
            height: clamp(20px, 3.3vw, 40px);
        }

        .excel-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .excel-table tbody tr:hover {
            background-color: #fff9f1 !important;
        }

        .excel-table tbody tr:hover td {
            background-color: #fff9f1 !important;
        }

        .excel-table tbody tr:hover .date-cell,
        .excel-table tbody tr:hover .calculated-cell,
        .excel-table tbody tr:hover .weekend,
        .excel-table tbody tr:hover .excel-input.has-data,
        .excel-table tbody tr:hover .excel-input.no-data,
        .excel-table tbody tr:hover .input-container {
            background-color: #fff9f1 !important;
        }

        /* 销售额字段在hover时保持计算列颜色，不受行hover影响 */
        .excel-table tbody tr:hover .excel-input.auto-filled {
            background: #f0f9ff !important;
            color: #0369a1;
        }

        .excel-table tbody tr.editing-row:hover td {
            background-color: #fff9f1 !important;
        }

        /* 日期列样式 */
        .date-cell {
            background: #f8f5eb !important;
            font-weight: 600;
            color: #583e04;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            min-width: 100px;
        }

        .weekend {
            background: #fef2f2 !important;
            color: #dc2626;
        }

        /* 输入框容器样式 */
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            height: clamp(20px, 3.3vw, 40px);
        }

        .currency-prefix {
            position: absolute;
            left: clamp(2px, 0.42vw, 8px);
            color: #6b7280;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            pointer-events: none;
            z-index: 2;
        }

        /* 销售额字段的货币前缀颜色与计算列一致 */
        .input-container.auto-filled-container .currency-prefix {
            color: #0369a1;
        }

        /* 输入框样式 */
        .excel-input {
            width: 100%;
            height: clamp(20px, 3.3vw, 40px);
            border: none;
            background: #fee2e2;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            transition: all 0.2s;
        }

        .excel-input.currency-input {
            padding-left: clamp(16px, 1.67vw, 32px);
            text-align: right;
            padding-right: clamp(2px, 0.42vw, 8px);
            background: #f0fdf4;
        }

        .excel-input:focus {
            background: #fff;
            border: 2px solid #583e04;
            outline: none;
            z-index: 5;
            position: relative;
        }

        /* 计算列样式 */
        .calculated-cell {
            background: #f0f9ff !important;
            color: #0369a1;
            font-weight: 600;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            min-width: 100px;
        }

        /* 负数样式 - 红色 */
        .calculated-cell.negative {
            color: #dc2626 !important;
            background: #fee2e2 !important;
        }

        .action-buttons {
            padding: clamp(12px, 1.25vw, 24px);
            background: #ffffffff;
            border-bottom: 2px solid #000000ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: clamp(0px, 0.83vw, 16px);
            flex-shrink: 0;
        }

        .btn {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.42vw, 8px);
            border: none;
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #f99e00;
            color: white;
            white-space: nowrap;
        }
        
        .btn-primary:hover {
            background-color: #f98500ff;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
        }

        /* 统计信息 */
        .stats-info {
            display: flex;
            gap: clamp(0px, 1.25vw, 24px);
            align-items: center;
            font-size: 12px;
            color: #6b7280;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-value {
            font-size: clamp(8px, 0.84vw, 16px);
            font-weight: bold;
            color: #000000ff;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .header .controls {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
            }
            
            .month-selector {
                flex-direction: column;
                gap: 16px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 12px;
            }
            
            .stats-info {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .stat-item {
                min-width: auto;
                width: 100%;
            }
        }

        /* 加载状态 */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #583e04;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 提示信息 */
        .alert {
            padding: 12px 16px;
            margin-bottom: 16px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        /* 隐藏类 */
        .hidden {
            display: none;
        }

        /* 库存输入框样式 */
        .stock-input-container {
            display: flex;
            align-items: center;
            gap: clamp(4px, 0.42vw, 8px);
            background: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(8px, 0.83vw, 16px);
            border-radius: clamp(4px, 0.42vw, 8px);
            border: 2px solid #10b981;
        }

        .stock-input-container label {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            color: #059669;
            white-space: nowrap;
        }

        .stock-input-container input {
            width: clamp(80px, 8.33vw, 160px);
            padding: clamp(2px, 0.21vw, 4px) clamp(4px, 0.42vw, 8px);
            border: 1px solid #d1d5db;
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(8px, 0.74vw, 14px);
            text-align: right;
        }

        .stock-input-container input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        /* 删除单日数据按钮 */
        .delete-day-btn {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: clamp(4px, 0.32vw, 6px);
            width: clamp(18px, 1.67vw, 32px);
            height: clamp(18px, 1.67vw, 32px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: clamp(6px, 0.63vw, 12px);
            margin: clamp(1px, 0.31vw, 3px);
        }

        .delete-day-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .delete-day-btn i {
            font-size: clamp(6px, 0.63vw, 12px);
        }

        .delete-day-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .delete-day-btn:disabled:hover {
            background: #9ca3af;
            transform: none;
            box-shadow: none;
        }

        /* 编辑按钮样式 */
        .edit-btn {
            background: #f59e0b;
            color: white;
            border: none;
            border-radius: clamp(4px, 0.32vw, 6px);
            width: clamp(18px, 1.67vw, 32px);
            height: clamp(18px, 1.67vw, 32px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: clamp(6px, 0.63vw, 12px);
            margin: clamp(1px, 0.21vw, 2px);
        }

        .edit-btn:hover {
            background: #d97706;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .edit-btn i {
            font-size: clamp(6px, 0.63vw, 12px);
        }

        .edit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .edit-btn.save-mode {
            background: #10b981;
        }

        .edit-btn.save-mode:hover {
            background: #059669;
        }

        /* 只读输入框样式 */
        .excel-input.readonly {
            background: #f9fafb !important;
            pointer-events: none;
            cursor: not-allowed;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .excel-input.currency-input.readonly {
            background: #f9fafb;
        }

        /* 自动填充字段样式（从KPI获取的销售额）- 与计算列样式一致 */
        .excel-input.auto-filled {
            background: #f0f9ff !important;
            color: #0369a1;
            font-weight: 600;
            cursor: not-allowed;
            pointer-events: none;
        }

        .excel-input.auto-filled:focus {
            background: #f0f9ff !important;
            color: #0369a1;
            border: 1px solid #0369a1;
            outline: none;
        }

        /* 操作列样式调整 */
        .action-cell {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: clamp(2px, 0.21vw, 4px);
            padding: clamp(2px, 0.21vw, 4px);
        }

        /* 编辑行样式 */
        .excel-table tr.editing-row {
            background-color: #d1fae5 !important;
        }

        .excel-table tr.editing-row td {
            background-color: #d1fae5 !important;
        }

        .excel-table tr.editing-row .date-cell {
            background-color: #d1fae5 !important;
        }

        .excel-table tr.editing-row .calculated-cell {
            background-color: #f0f9ff !important;
        }

        .excel-table tr.editing-row .weekend {
            background-color: #d1fae5 !important;
        }

        .excel-table tr.editing-row .excel-input {
            background-color: #d1fae5 !important;
        }

        .excel-table tr.editing-row .excel-input.currency-input {
            background-color: #d1fae5 !important;
        }

        /* 编辑模式下，销售额字段保持计算列颜色 */
        .excel-table tr.editing-row .excel-input.auto-filled {
            background: #f0f9ff !important;
            color: #0369a1;
        }

        /* 数据状态颜色 */
        .excel-input.has-data {
            background: #dbeafe !important;
        }

        .excel-input.no-data {
            background: #fee2e2 !important;
        }

        /* 销售额字段不受数据状态颜色影响，保持计算列颜色 */
        .excel-input.auto-filled.has-data,
        .excel-input.auto-filled.no-data {
            background: #f0f9ff !important;
            color: #0369a1;
        }

        .excel-input:focus {
            background: #fff !important;
            border: 2px solid #583e04;
            outline: none;
            z-index: 5;
            position: relative;
        }

        /* 销售额字段在focus状态下也保持计算列颜色 */
        .excel-input.auto-filled:focus {
            background: #f0f9ff !important;
            color: #0369a1;
            border: 1px solid #0369a1;
        }

        /* 通知容器 */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }

        /* 通知基础样式 */
        .toast {
            width: clamp(100px, 15.63vw, 300px);
            padding: clamp(2px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(6px, 0.42vw, 8px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: clamp(8px, 0.74vw, 14px);
            display: flex;
            align-items: center;
            gap: 12px;
            pointer-events: auto;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(100%);
            opacity: 0;
        }

        /* 通知类型样式 */
        .toast-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9));
            color: white;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .toast-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
            color: white;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .toast-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 0.9));
            color: white;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .toast-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(217, 119, 6, 0.9));
            color: white;
            border-color: rgba(245, 158, 11, 0.3);
        }

        /* 通知图标 */
        .toast-icon {
            font-size: clamp(14px, 0.94vw, 18px);
            flex-shrink: 0;
        }

        /* 通知内容 */
        .toast-content {
            flex: 1;
            font-weight: 500;
            line-height: 1.4;
        }

        /* 关闭按钮 */
        .toast-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            opacity: 0.8;
            transition: opacity 0.2s;
            flex-shrink: 0;
        }

        .toast-close:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
        }

        /* 进度条 */
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 0 0 8px 8px;
            transform-origin: left;
            animation: toastProgress 4s linear forwards;
        }

        @keyframes toastProgress {
            0% {
                transform: scaleX(1);
            }
            100% {
                transform: scaleX(0);
            }
        }

        /* 响应式调整 */
        @media (max-width: 480px) {
            .toast-container {
                bottom: 10px;
                right: 10px;
                left: 10px;
            }
            
            .toast {
                min-width: auto;
                max-width: none;
            }
        }

        /* 报表类型选择器样式 - 与 stockeditall.php 一致 */
        .report-type-selector {
            position: relative;
            display: inline-block;
        }

        .report-type-btn {
            background-color: #ff9e00;
            color: white;
            font-weight: 500;
            padding: clamp(6px, 0.52vw, 10px) clamp(16px, 1.04vw, 20px);
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: clamp(10px, 0.73vw, 14px);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            justify-content: space-between;
            position: relative;
        }

        .report-type-btn:hover {
            background-color: #f98500ff;
            border-radius: 8px;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .report-type-selector--disabled .report-type-btn {
            cursor: default;
        }

        .report-type-selector--disabled .report-type-btn:hover {
            background-color: #ff9e00;
            transform: none;
            box-shadow: none;
        }

        .report-dropdown-menu {
            position: absolute;
            top: 96%;
            left: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 8px;
            min-width: 150px;
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.2);
            z-index: 1000;
            display: none;
        }

        .report-dropdown-menu.show {
            display: block;
        }

        .report-dropdown-item {
            padding: 10px 16px;
            color: #583e04;
            text-decoration: none;
            display: block;
            font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
        }

        .report-dropdown-item:hover {
            background-color: #f0f9ff;
            color: #ff9e00;
        }

        .report-dropdown-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>TOKYO JAPANESE CUISINE 成本后台</h1>
            </div>
            <div class="controls">
                <!-- 报表类型选择器 -->
                <?php if ($showReportDropdown): ?>
                <div class="report-type-selector" onclick="toggleReportTypeDropdown()">
                    <button class="report-type-btn">
                        <i class="fas fa-chart-pie"></i>
                        <?php echo $reportLabelMap['cost']; ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="report-dropdown-menu" id="report-type-dropdown">
                        <?php if (in_array('kpi', $reportPermissions, true)): ?>
                        <a href="kpiedit.php" class="report-dropdown-item">
                            <i class="fas fa-chart-line"></i> <?php echo $reportLabelMap['kpi']; ?>
                        </a>
                        <?php endif; ?>
                        <?php if (in_array('cost', $reportPermissions, true)): ?>
                        <a href="costedit.php" class="report-dropdown-item">
                            <i class="fas fa-chart-pie"></i> <?php echo $reportLabelMap['cost']; ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="report-type-selector report-type-selector--disabled">
                    <button class="report-type-btn" style="cursor:default;">
                        <i class="fas fa-chart-pie"></i>
                        <?php echo $reportLabelMap['cost']; ?>
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- 餐厅选择器 -->
                <div class="restaurant-selector">
                    <div class="restaurant-prefix"><?php echo substr($restaurantConfigAllowed[$defaultRestaurant]['name'], 0, 1); ?></div>
                    <div class="number-dropdown">
                        <button class="number-btn dropdown-toggle"<?php if ($showRestaurantDropdown): ?> onclick="toggleNumberDropdown()"<?php else: ?> style="cursor:default;"<?php endif; ?>>
                            <?php echo $restaurantConfigAllowed[$defaultRestaurant]['number']; ?>
                            <?php if ($showRestaurantDropdown): ?>
                            <i class="fas fa-chevron-down"></i>
                            <?php endif; ?>
                        </button>
                        <div class="number-dropdown-menu" id="number-dropdown"<?php if (!$showRestaurantDropdown): ?> style="display:none;"<?php endif; ?>>
                            <?php if ($showRestaurantDropdown): ?>
                            <div class="number-grid">
                                <?php foreach ($restaurantPermissions as $storeKey): ?>
                                <button class="number-item" onclick="selectNumber(<?php echo $restaurantConfigAllowed[$storeKey]['number']; ?>)"><?php echo $restaurantConfigAllowed[$storeKey]['number']; ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 月份选择器 -->
        <div class="month-selector">
            <div>
                <label for="year-select">年份:</label>
                <select id="year-select" onchange="loadMonthData()">
                </select>
            </div>
            <div>
                <label for="month-select">月份:</label>
                <select id="month-select" onchange="loadMonthData()">
                    <option value="1">1月</option>
                    <option value="2">2月</option>
                    <option value="3">3月</option>
                    <option value="4">4月</option>
                    <option value="5">5月</option>
                    <option value="6">6月</option>
                    <option value="7">7月</option>
                    <option value="8">8月</option>
                    <option value="9">9月</option>
                    <option value="10">10月</option>
                    <option value="11">11月</option>
                    <option value="12">12月</option>
                </select>
            </div>
            <div id="current-restaurant-info" class="stat-item">
                <i class="fas fa-store"></i>
                <span>当前: <span class="stat-value"><?php echo $restaurantConfigAllowed[$defaultRestaurant]['name']; ?></span></span>
            </div>
        </div>
        
        <!-- Excel表格 -->
        <div class="excel-container">
            <div class="action-buttons">
                <div class="stats-info" id="month-stats">
                    <div class="stat-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>已填写: <span class="stat-value" id="filled-days">0</span> 天</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>月总销售额: RM <span class="stat-value" id="total-sales">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-chart-pie"></i>
                        <span>月总成本: RM <span class="stat-value" id="total-cost">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>月总毛利润: RM <span class="stat-value" id="total-profit">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-percentage"></i>
                        <span>平均成本率: <span class="stat-value" id="avg-cost-percent">0</span>%</span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div class="stock-input-container">
                        <label for="current-stock-input">
                            <i class="fas fa-warehouse"></i>
                            当前库存 (RM):
                        </label>
                        <input type="number" id="current-stock-input" min="0" step="0.01" 
                               placeholder="0.00" oninput="formatStockInput(this)">
                    </div>
                    <button class="btn btn-primary" onclick="saveAllData()">
                        <i class="fas fa-save"></i>
                        保存本月数据
                    </button>
                </div>
            </div>
            <div class="table-scroll-container">
            <table class="excel-table" id="excel-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">日期</th>
                        <th style="width: 12%;">销售额</th>
                        <th style="width: 12%;">饮料成本</th>
                        <th style="width: 12%;">厨房成本</th>
                        <th style="width: 12%;">总成本</th>
                        <th style="width: 12%;">毛利润</th>
                        <th style="width: 10%;">成本率 (%)</th>
                        <th style="width: 10%;">操作</th>
                    </tr>
                </thead>
                <tbody id="excel-tbody">
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    </div>

    <script>
        // API 配置
        const API_BASE_URL = 'costapi.php';

        // ===== 页面版本标识（用于确认是否加载到最新代码，排查缓存/OPcache）=====
        const COSTEDIT_BUILD_ID = '2025-12-18_02';
        (function () {
            try {
                console.log('[costedit] build:', COSTEDIT_BUILD_ID);
                const el = document.getElementById('page-build-id');
                if (el) el.textContent = COSTEDIT_BUILD_ID;
            } catch (e) {}
        })();
        
        const availableReportTypes = <?php echo json_encode($reportPermissions); ?>;
        const reportDropdownEnabled = <?php echo $showReportDropdown ? 'true' : 'false'; ?>;
        const availableRestaurants = <?php echo json_encode($restaurantPermissions); ?>;
        const restaurantDropdownEnabled = <?php echo $showRestaurantDropdown ? 'true' : 'false'; ?>;
        const restaurantConfig = <?php echo json_encode($restaurantConfigAllowed); ?>;

        // 应用状态
        let currentRestaurant = '<?php echo $defaultRestaurant; ?>';
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth() + 1;
        let monthData = {};
        let monthStockData = null;
        let isLoading = false;
        let pasteTargetDay = null;
        const editingDays = new Set();

        // 货币字段列表
        const currencyFields = ['sales', 'c_beverage', 'c_kitchen'];
        let preservedRowValues = new Map();

        if (!availableRestaurants.includes(currentRestaurant)) {
            currentRestaurant = availableRestaurants.length ? availableRestaurants[0] : 'j1';
        }
        if (!restaurantConfig[currentRestaurant]) {
            restaurantConfig[currentRestaurant] = { name: 'J1', number: 1 };
        }

        // 初始化应用
        function initApp() {
            // 启动会话自动刷新
            startSessionRefresh();
            
            initYearSelect();
            initCurrentMonth();
            refreshRestaurantDisplay();
            loadMonthData();
        }

        // 初始化年份选择器
        function initYearSelect() {
            const yearSelect = document.getElementById('year-select');
            const currentYear = new Date().getFullYear();
            
            // 生成从2022年到未来2年的选项
            for (let year = 2022; year <= currentYear + 2; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year + '年';
                if (year === currentYear) {
                    option.selected = true;
                }
                yearSelect.appendChild(option);
            }
        }

        // 初始化当前月份
        function initCurrentMonth() {
            const monthSelect = document.getElementById('month-select');
            monthSelect.value = currentMonth;
        }

        function refreshRestaurantDisplay() {
            const info = restaurantConfig[currentRestaurant];
            const numberBtn = document.querySelector('.number-btn');
            if (numberBtn && info) {
                if (restaurantDropdownEnabled) {
                    numberBtn.innerHTML = `${info.number} <i class="fas fa-chevron-down"></i>`;
                } else {
                    numberBtn.textContent = info.number;
                }
            }
            const infoElem = document.querySelector('#current-restaurant-info .stat-value');
            if (infoElem && info) {
                infoElem.textContent = info.name;
            }
            updateSelectedNumber();
        }

        // 返回上一页
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }

        // 切换餐厅
        function switchRestaurant(restaurant) {
            if (!availableRestaurants.includes(restaurant)) {
                showAlert('您没有权限查看该店铺', 'warning');
                return;
            }
            if (currentRestaurant === restaurant || isLoading) return;
            
            currentRestaurant = restaurant;
            refreshRestaurantDisplay();
            // 重新加载数据
            loadMonthData();
        }

        // 会话自动刷新机制
        let sessionRefreshInterval;
        
        function startSessionRefresh() {
            // 每5分钟刷新一次会话
            sessionRefreshInterval = setInterval(async () => {
                try {
                    const response = await fetch('session_refresh_api.php');
                    const result = await response.json();
                    
                    if (!result.success && result.code === 'SESSION_EXPIRED') {
                        clearInterval(sessionRefreshInterval);
                        showSessionExpiredMessage();
                    }
                } catch (error) {
                    console.error('会话刷新失败:', error);
                }
            }, 5 * 60 * 1000); // 5分钟
        }
        
        function stopSessionRefresh() {
            if (sessionRefreshInterval) {
                clearInterval(sessionRefreshInterval);
            }
        }

        // 显示会话过期消息
        function showSessionExpiredMessage() {
            const tableContainer = document.querySelector('.table-container');
            if (tableContainer) {
                tableContainer.innerHTML = `
                    <div style="text-align: center; padding: 50px; background: #ffebee; border: 1px solid #f44336; border-radius: 8px; margin: 20px;">
                        <h2 style="color: #C62828; margin: 0 0 15px 0;">🔒 会话已过期</h2>
                        <p style="margin: 0 0 20px 0; color: #666;">您的登录会话已过期，请重新登录以继续使用。</p>
                        <button onclick="window.location.href='../frontend/login.php'" 
                                style="background: #C62828; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px;">
                            重新登录
                        </button>
                    </div>
                `;
            }
        }

        // API 调用函数
        async function apiCall(endpoint, options = {}) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP错误: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.code === 'SESSION_EXPIRED') {
                    showSessionExpiredMessage();
                    return { success: false, code: 'SESSION_EXPIRED' };
                }
                
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                throw error;
            }
        }

        // 根据日期获取已存在的成本记录（用于“已存在但前端没有id”的兜底更新）
        async function getExistingCostRecordByDate(dateStr) {
            try {
                const query = new URLSearchParams({
                    action: 'list',
                    restaurant: currentRestaurant,
                    search_date: dateStr
                });
                const res = await apiCall(`?${query.toString()}`);
                if (res && res.success && Array.isArray(res.data) && res.data.length > 0) {
                    return res.data[0];
                }
            } catch (e) {
                console.warn('按日期查询成本记录失败:', e);
            }
            return null;
        }

        // 加载月度数据
        async function loadMonthData(preserveEditingState = false) {
            if (isLoading) return;
            
            if (!preserveEditingState) {
                editingDays.clear();
                preservedRowValues.clear();
            } else {
                captureEditingRowValues();
            }
            isLoading = true;
            currentYear = parseInt(document.getElementById('year-select').value);
            currentMonth = parseInt(document.getElementById('month-select').value);
            
            try {
                const startDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-01`;
                const lastDay = new Date(currentYear, currentMonth, 0).getDate();
                const endDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;
                
                // 同时加载成本数据和KPI数据
                // 销售额字段将从KPI的净销售额（总销售额-折扣）自动获取
                const [costResult, kpiResult] = await Promise.all([
                    apiCall(`?${new URLSearchParams({
                        action: 'list',
                        restaurant: currentRestaurant,
                        start_date: startDate,
                        end_date: endDate
                    })}`),
                    fetch(`kpiapi.php?${new URLSearchParams({
                        action: 'list',
                        restaurant: currentRestaurant,
                        start_date: startDate,
                        end_date: endDate
                    })}`).then(res => res.json()).catch(() => ({ success: false, data: [] }))
                ]);
                
                const costData = costResult.data || [];
                const kpiData = kpiResult.data || [];
                
                // 将KPI数据转换为以日期为键的对象，并计算净销售额
                // 净销售额 = 总销售额(gross_sales) - 折扣(discounts)
                const kpiDataMap = {};
                kpiData.forEach(item => {
                    const day = parseInt(item.date.split('-')[2]);
                    const grossSales = parseFloat(item.gross_sales) || 0;
                    const discounts = parseFloat(item.discounts) || 0;
                    const netSales = grossSales - discounts;
                    kpiDataMap[day] = {
                        net_sales: netSales
                    };
                });
                
                // 合并成本数据和KPI净销售额
                // 销售额字段优先使用KPI的净销售额，如果KPI中没有数据则使用成本数据中的销售额
                monthData = {};
                
                // 首先处理成本数据（这是主要数据源，必须保留）
                costData.forEach(item => {
                    const day = parseInt(item.date.split('-')[2]);
                    monthData[day] = item;
                    // 如果KPI数据中有该日的净销售额，则使用KPI的净销售额覆盖成本数据的销售额
                    if (kpiDataMap[day] && kpiDataMap[day].net_sales !== undefined) {
                        monthData[day].sales = kpiDataMap[day].net_sales;
                    }
                });
                
                // 对于成本数据中没有但KPI数据中有的日期，也添加到monthData中
                // 但只有在成本数据查询成功且确实没有该日期的记录时，才创建临时对象
                // 这样可以避免覆盖已保存但查询失败的成本数据
                if (costResult.success !== false) {
                    Object.keys(kpiDataMap).forEach(day => {
                        if (!monthData[day]) {
                            const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                            monthData[day] = {
                                date: dateStr,
                                sales: kpiDataMap[day].net_sales,
                                c_beverage: 0,
                                c_kitchen: 0
                            };
                        }
                    });
                }
                
                // 加载当月库存数据
                await loadMonthStock();
                
                generateExcelTable();
                updateMonthStats();
                setTimeout(() => {
                    updateInputColors();
                }, 200);
                
            } catch (error) {
                monthData = {};
                monthStockData = null;
                generateExcelTable();
                updateMonthStats();
            } finally {
                isLoading = false;
            }
        }

        // 加载当月库存数据
        async function loadMonthStock() {
            try {
                const yearMonth = `${currentYear}-${currentMonth.toString().padStart(2, '0')}`;
                const queryParams = new URLSearchParams({
                    action: 'get_month_stock',
                    restaurant: currentRestaurant,
                    year_month: yearMonth
                });
                
                const result = await apiCall(`?${queryParams}`);
                if (result.success && result.data) {
                    monthStockData = result.data;
                    // 更新输入框的值
                    const stockInput = document.getElementById('current-stock-input');
                    if (stockInput) {
                        stockInput.value = monthStockData.current_stock ? parseFloat(monthStockData.current_stock).toFixed(2) : '';
                    }
                } else {
                    monthStockData = null;
                    document.getElementById('current-stock-input').value = '';
                }
            } catch (error) {
                console.error('加载库存数据失败:', error);
                monthStockData = null;
                document.getElementById('current-stock-input').value = '';
            }
        }

        // 格式化货币输入值显示
        function formatCurrencyDisplay(value) {
            if (!value || value === '') return '';
            const num = parseFloat(value);
            if (isNaN(num)) return '';
            return num.toFixed(2);
        }

        // 生成Excel表格
        function generateExcelTable() {
            const tbody = document.getElementById('excel-tbody');
            tbody.innerHTML = '';
            
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth - 1, day);
                const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                
                const existingData = monthData[day] || {};
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="date-cell ${isWeekend ? 'weekend' : ''}">${currentMonth}月${day}<small> (周${['日', '一', '二', '三', '四', '五', '六'][date.getDay()]})</small></td>
                    <td>
                        <div class="input-container auto-filled-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input auto-filled" data-field="sales" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.sales)}" min="0" step="0.01" 
                                placeholder="0.00" readonly disabled onchange="updateCalculations(${day})" 
                                title="销售额自动从KPI净销售额获取，不可手动编辑">
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input" data-field="c_beverage" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.c_beverage)}" min="0" step="0.01" 
                                placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <span class="currency-prefix">RM</span>
                            <input type="number" class="excel-input currency-input" data-field="c_kitchen" data-day="${day}" 
                                value="${formatCurrencyDisplay(existingData.c_kitchen)}" min="0" step="0.01" 
                                placeholder="0.00" onchange="updateCalculations(${day})" oninput="formatCurrencyInput(this)">
                        </div>
                    </td>
                    <td class="calculated-cell" id="c-total-${day}">RM 0.00</td>
                    <td class="calculated-cell" id="gross-total-${day}">RM 0.00</td>
                    <td class="calculated-cell" id="cost-percent-${day}">0%</td>
                    <td class="action-cell">
                        <button class="edit-btn" id="edit-btn-${day}" onclick="toggleEdit(${day})" title="编辑${day}日数据">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="delete-day-btn" onclick="clearDayData(${day})" title="清空${day}日成本（保留销售额）">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);

                setTimeout(() => {
                    for (let day = 1; day <= daysInMonth; day++) {
                        setRowReadonly(day, true, true);
                    }
                    restoreEditingStates();
                }, 0);
                
                updateCalculations(day);
            }

            setTimeout(() => {
                updateInputColors();
            }, 100);
        }

        // 格式化货币输入
        function formatCurrencyInput(input) {
            const value = input.value;
            if (value && !isNaN(value)) {
                // 只在失去焦点时格式化
            }
        }

        // 格式化库存输入
        function formatStockInput(input) {
            const value = input.value;
            if (value && value.includes('.')) {
                const parts = value.split('.');
                if (parts[1] && parts[1].length > 2) {
                    input.value = parts[0] + '.' + parts[1].substring(0, 2);
                }
            }
        }

        // 显示提示信息
        function showAlert(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            let existingToasts = container.querySelectorAll('.toast');
            while (existingToasts.length >= 3) {
                closeToast(existingToasts[0].id);
                if (existingToasts[0].parentNode) {
                    existingToasts[0].parentNode.removeChild(existingToasts[0]);
                }
                existingToasts = container.querySelectorAll('.toast');
            }

            const toastId = 'toast-' + Date.now();
            const iconClass = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle', 
                'info': 'fa-info-circle',
                'warning': 'fa-exclamation-triangle'
            }[type] || 'fa-check-circle';

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.id = toastId;
            toast.innerHTML = `
                <i class="fas ${iconClass} toast-icon"></i>
                <div class="toast-content">${message}</div>
                <button class="toast-close" onclick="closeToast('${toastId}')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="toast-progress"></div>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('show');
            }, 0);

            setTimeout(() => {
                closeToast(toastId);
            }, 700);
        }

        // 关闭通知
        function closeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }

        // 关闭所有通知
        function closeAllToasts() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                closeToast(toast.id);
            });
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);
        
        // 页面卸载时停止会话刷新
        window.addEventListener('beforeunload', function() {
            stopSessionRefresh();
        });

        // 设置行的只读状态
        function setRowReadonly(day, readonly, skipTracking = false) {
            const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
            if (!inputs.length) return;
            const row = inputs[0].closest('tr');
            if (!row) return;
            
            inputs.forEach(input => {
                // 销售额字段始终只读（从KPI自动获取）
                if (input.dataset.field === 'sales') {
                    input.classList.add('readonly', 'auto-filled');
                    input.setAttribute('readonly', 'readonly');
                    input.setAttribute('disabled', 'disabled');
                    return;
                }
                
                if (readonly) {
                    input.classList.add('readonly');
                    input.setAttribute('readonly', 'readonly');
                    input.setAttribute('disabled', 'disabled');
                } else {
                    input.classList.remove('readonly');
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                }
            });
            
            if (readonly) {
                row.classList.remove('editing-row');
            } else {
                row.classList.add('editing-row');
            }

            if (!skipTracking) {
                if (readonly) {
                    editingDays.delete(day);
                } else {
                    editingDays.add(day);
                }
            }
        }

        function captureEditingRowValues() {
            preservedRowValues.clear();
            if (!editingDays.size) return;
            editingDays.forEach(day => {
                const dayInputs = document.querySelectorAll(`input[data-day="${day}"]`);
                if (!dayInputs.length) return;
                const values = {};
                dayInputs.forEach(input => {
                    const field = input.dataset.field;
                    values[field] = input.value;
                });
                preservedRowValues.set(day, values);
            });
        }

        function restoreEditingRowValues() {
            if (!preservedRowValues.size) return;
            preservedRowValues.forEach((values, day) => {
                Object.entries(values).forEach(([field, value]) => {
                    const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
                    if (input) {
                        input.value = value;
                    }
                });
                updateCalculations(day);
            });
            preservedRowValues.clear();
        }

        function restoreEditingStates() {
            if (!editingDays.size) return;
            editingDays.forEach(day => {
                const editBtn = document.getElementById(`edit-btn-${day}`);
                if (!editBtn) return;
                setRowReadonly(day, false, true);
                editBtn.classList.add('save-mode');
                editBtn.innerHTML = '<i class="fas fa-save"></i>';
                editBtn.title = `保存${day}日数据`;
            });
            restoreEditingRowValues();
        }

        // 更新计算字段
        function updateCalculations(day) {
            const sales = parseFloat(getInputValue('sales', day)) || 0;
            const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
            const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;

            // 总成本 = 饮料成本 + 厨房成本
            const cTotal = cBeverage + cKitchen;
            document.getElementById(`c-total-${day}`).textContent = `RM ${cTotal.toFixed(2)}`;

            // 毛利润 = 销售额 - 总成本
            const grossTotal = sales - cTotal;
            const grossTotalCell = document.getElementById(`gross-total-${day}`);
            grossTotalCell.textContent = `RM ${grossTotal.toFixed(2)}`;
            
            // 如果毛利润为负数，添加红色样式
            if (grossTotal < 0) {
                grossTotalCell.classList.add('negative');
            } else {
                grossTotalCell.classList.remove('negative');
            }

            // 成本率 = (总成本 / 销售额) * 100%
            const costPercent = sales > 0 ? (cTotal / sales) * 100 : 0;
            document.getElementById(`cost-percent-${day}`).textContent = `${costPercent.toFixed(2)}%`;

            updateMonthStats();
        }

        // 更新输入框颜色状态
        function updateInputColors() {
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            
            for (let day = 1; day <= daysInMonth; day++) {
                const dayInputs = document.querySelectorAll(`input[data-day="${day}"]`);
                
                const sales = getInputValue('sales', day).trim();
                const cBeverage = getInputValue('c_beverage', day).trim();
                const cKitchen = getInputValue('c_kitchen', day).trim();
                
                let filledKeyFields = 0;
                if (sales && sales !== '0' && sales !== '0.00') filledKeyFields++;
                if (cBeverage && cBeverage !== '0' && cBeverage !== '0.00') filledKeyFields++;
                if (cKitchen && cKitchen !== '0' && cKitchen !== '0.00') filledKeyFields++;
                
                const rowHasKeyData = filledKeyFields >= 1;
                
                dayInputs.forEach(input => {
                    const field = input.dataset.field;
                    const value = input.value.trim();
                    
                    const hasValue = value !== '' && value !== '0' && value !== '0.00';
                    if (hasValue) {
                        input.classList.remove('no-data');
                        input.classList.add('has-data');
                    } else {
                        input.classList.remove('has-data');
                        input.classList.add('no-data');
                    }
                });
            }
        }

        // 获取输入框值
        function getInputValue(field, day) {
            const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
            return input ? input.value : '';
        }

        // 更新月度统计
        function updateMonthStats() {
            let filledDays = 0;
            let totalSales = 0;
            let totalCost = 0;
            let totalProfit = 0;
            
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            
            for (let day = 1; day <= daysInMonth; day++) {
                const sales = parseFloat(getInputValue('sales', day)) || 0;
                const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
                const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;
                
                if (sales > 0 || cBeverage > 0 || cKitchen > 0) {
                    filledDays++;
                }
                
                const cTotal = cBeverage + cKitchen;
                const grossTotal = sales - cTotal;
                
                totalSales += sales;
                totalCost += cTotal;
                totalProfit += grossTotal;
            }
            
            const avgCostPercent = totalSales > 0 ? (totalCost / totalSales) * 100 : 0;
            
            document.getElementById('filled-days').textContent = filledDays;
            document.getElementById('total-sales').textContent = totalSales.toFixed(2);
            document.getElementById('total-cost').textContent = totalCost.toFixed(2);
            document.getElementById('total-profit').textContent = totalProfit.toFixed(2);
            document.getElementById('avg-cost-percent').textContent = avgCostPercent.toFixed(2);
        }

        // 智能分割数据，保护千位分隔符
        function splitWithNumberProtection(text) {
            const values = [];
            let current = '';
            let inNumber = false;
            
            for (let i = 0; i < text.length; i++) {
                const char = text[i];
                const nextChar = text[i + 1];
                const prevChar = text[i - 1];
                
                if (char === ',') {
                    const isThousandsSeparator = 
                        /\d/.test(prevChar) && 
                        /\d/.test(nextChar) && 
                        /^\d{1,3}($|[,\s\t])/.test(text.substring(i + 1));
                    
                    if (isThousandsSeparator) {
                        current += char;
                        inNumber = true;
                    } else {
                        if (current.trim()) {
                            values.push(current.trim());
                        }
                        current = '';
                        inNumber = false;
                    }
                } else if (/\s/.test(char)) {
                    if (current.trim()) {
                        values.push(current.trim());
                    }
                    current = '';
                    inNumber = false;
                } else {
                    current += char;
                    if (/\d/.test(char)) {
                        inNumber = true;
                    }
                }
            }
            
            if (current.trim()) {
                values.push(current.trim());
            }
            
            return values;
        }

        // 处理粘贴数据
        function handlePasteData(pasteData, targetDay, startField = null) {
            const lines = pasteData.trim().split('\n').filter(line => line.trim() !== '');
            
            // 销售额字段不可编辑，从粘贴字段列表中移除
            const pasteFields = [
                'c_beverage',
                'c_kitchen'
            ];
            
            // 如果起始字段是销售额，则从饮料成本开始
            let startIndex = 0;
            if (startField === 'sales') {
                startIndex = 0; // 从饮料成本开始
                showAlert('销售额字段不可编辑，将从饮料成本开始粘贴', 'info');
            } else if (startField && pasteFields.includes(startField)) {
                startIndex = pasteFields.indexOf(startField);
            }
            
            if (lines.length > 1) {
                const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
                
                const editingDays = [];
                for (let day = targetDay; day <= daysInMonth; day++) {
                    const row = document.querySelector(`input[data-day="${day}"]`)?.closest('tr');
                    if (row && row.classList.contains('editing-row')) {
                        editingDays.push(day);
                    }
                }
                
                if (editingDays.length === 0) {
                    showAlert('没有找到处于编辑模式的行', 'error');
                    return;
                }
                
                if (lines.length > editingDays.length) {
                    showAlert(`数据有 ${lines.length} 行，但只有 ${editingDays.length} 行在编辑模式`, 'info');
                }
                
                let totalPasteCount = 0;
                const pastedDays = [];
                
                for (let lineIndex = 0; lineIndex < Math.min(lines.length, editingDays.length); lineIndex++) {
                    const line = lines[lineIndex];
                    const day = editingDays[lineIndex];
                    
                    let values = [];
                    if (line.includes('\t')) {
                        values = line.split('\t');
                    } else if (line.includes(',')) {
                        const numberPattern = /^[\d,]+\.?\d*$/;
                        if (numberPattern.test(line.trim())) {
                            values = [line.trim()];
                        } else {
                            values = splitWithNumberProtection(line);
                        }
                    } else {
                        values = line.split(/\s+/);
                    }
                    
                    let rowPasteCount = 0;
                    const currentStartIndex = (lineIndex === 0) ? startIndex : 0;

                    for (let i = 0; i < values.length && (currentStartIndex + i) < pasteFields.length; i++) {
                        const fieldIndex = currentStartIndex + i;
                        const field = pasteFields[fieldIndex];
                        const value = values[i].trim();
                        
                        // 跳过销售额字段（不可编辑）
                        if (field === 'sales') {
                            continue;
                        }
                        
                        if (value && value !== '') {
                            const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
                            if (input && !input.classList.contains('auto-filled')) {
                                let cleanValue = value.replace(/[^\d.,-]/g, '');
                                cleanValue = cleanValue.replace(/,/g, '');
                                
                                const numValue = parseFloat(cleanValue);
                                if (!isNaN(numValue)) {
                                    input.value = cleanValue;
                                    rowPasteCount++;
                                }
                            }
                        }
                    }
                    
                    if (rowPasteCount > 0) {
                        totalPasteCount += rowPasteCount;
                        pastedDays.push(day);
                        updateCalculations(day);
                    }
                }
                
                if (totalPasteCount > 0) {
                    const fieldNames = {
                        'c_beverage': '饮料成本',
                        'c_kitchen': '厨房成本'
                    };
                    const startFieldName = startField && startField !== 'sales' ? fieldNames[startField] : '第一列';
                    showAlert(`第一行从${startFieldName}开始，后续行从第一列开始，成功粘贴 ${lines.length} 行数据，共 ${totalPasteCount} 个字段到第 ${pastedDays.join(', ')} 日（销售额字段自动从KPI获取，不可编辑）`, 'success');
                } else {
                    showAlert('未能识别有效的数据格式', 'error');
                }
                
            } else {
                const line = lines[0];
                let values = [];
                if (line.includes('\t')) {
                    values = line.split('\t');
                } else if (line.includes(',')) {
                    const numberPattern = /^[\d,]+\.?\d*$/;
                    if (numberPattern.test(line.trim())) {
                        values = [line.trim()];
                    } else {
                        values = splitWithNumberProtection(line);
                    }
                } else {
                    values = line.split(/\s+/);
                }
                
                let pasteCount = 0;
                
                for (let i = 0; i < values.length && (startIndex + i) < pasteFields.length; i++) {
                    const fieldIndex = startIndex + i;
                    const field = pasteFields[fieldIndex];
                    const value = values[i].trim();
                    
                    // 跳过销售额字段（不可编辑）
                    if (field === 'sales') {
                        continue;
                    }
                    
                    if (value && value !== '') {
                        const input = document.querySelector(`input[data-field="${field}"][data-day="${targetDay}"]`);
                        if (input && !input.classList.contains('auto-filled')) {
                            let cleanValue = value.replace(/[^\d.,-]/g, '');
                            cleanValue = cleanValue.replace(/,/g, '');
                            
                            const numValue = parseFloat(cleanValue);
                            if (!isNaN(numValue)) {
                                input.value = cleanValue;
                                pasteCount++;
                            }
                        }
                    }
                }
                
                updateCalculations(targetDay);
                
                if (pasteCount > 0) {
                    const fieldNames = {
                        'c_beverage': '饮料成本',
                        'c_kitchen': '厨房成本'
                    };
                    const startFieldName = startField && startField !== 'sales' ? fieldNames[startField] : '第一列';
                    showAlert(`从${startFieldName}开始成功粘贴 ${pasteCount} 个字段到第${targetDay}日（销售额字段自动从KPI获取，不可编辑）`, 'success');
                } else {
                    showAlert('未能识别有效的数据格式', 'error');
                }
            }
        }

        // 保存所有数据
        async function saveAllData() {
            if (isLoading) return;
            
            const evt = (typeof event !== 'undefined') ? event : null;
            const saveBtn = evt && evt.target ? (evt.target.closest('button') || evt.target) : null;
            if (!saveBtn) {
                showAlert('未能识别保存按钮事件，请刷新页面后重试', 'warning');
                return;
            }
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<div class="loading"></div> 保存中...';
            saveBtn.disabled = true;
            
            try {
                const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
                let successCount = 0;
                let skipCount = 0;
                let errorCount = 0;
                const errors = [];
                
                // 先保存库存数据
                const stockValue = document.getElementById('current-stock-input').value;
                if (stockValue && stockValue.trim() !== '') {
                    try {
                        const yearMonth = `${currentYear}-${currentMonth.toString().padStart(2, '0')}`;
                        const stockData = {
                            year_month: yearMonth,
                            current_stock: parseFloat(stockValue) || 0,
                            restaurant: currentRestaurant
                        };
                        
                        const stockResult = await apiCall('?action=save_month_stock', {
                            method: 'POST',
                            body: JSON.stringify(stockData)
                        });
                        
                        if (!stockResult.success) {
                            showAlert('库存数据保存失败：' + (stockResult.message || '未知错误'), 'warning');
                        }
                    } catch (error) {
                        console.error('保存库存失败:', error);
                        showAlert('库存数据保存失败', 'warning');
                    }
                }
                
                // 保存成本数据
                for (let day = 1; day <= daysInMonth; day++) {
                    const sales = parseFloat(getInputValue('sales', day)) || 0;
                    const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
                    const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;
                    
                    const hasData = cBeverage > 0 || cKitchen > 0;

                    if (hasData) {
                        const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                        
                        const recordData = {
                            date: dateStr,
                            c_beverage: cBeverage,
                            c_kitchen: cKitchen,
                            restaurant: currentRestaurant
                        };
                        
                        try {
                            let result;
                            // 只有存在数据库记录ID时才执行更新（PUT）
                            // 某些日期可能只有从KPI同步来的销售额（客户端临时数据），此时 monthData[day] 存在但没有 id
                            if (monthData[day] && monthData[day].id) {
                                recordData.id = monthData[day].id;
                                result = await apiCall('', {
                                    method: 'PUT',
                                    body: JSON.stringify(recordData)
                                });
                            } else {
                                result = await apiCall('', {
                                    method: 'POST',
                                    body: JSON.stringify(recordData)
                                });

                                // 如果后端提示“该日期已存在”，说明数据库已有记录但前端没有拿到 id
                                // 这常见于：KPI 触发器先插入了 cost 记录，或 cost 记录是其它流程生成的
                                if (result && result.success === false && String(result.message || '').includes('已存在')) {
                                    const existing = await getExistingCostRecordByDate(dateStr);
                                    if (existing && existing.id) {
                                        monthData[day] = { ...(monthData[day] || {}), ...existing };
                                        recordData.id = existing.id;
                                        result = await apiCall('', {
                                            method: 'PUT',
                                            body: JSON.stringify(recordData)
                                        });
                                    }
                                }
                            }
                            
                            if (result.success === true) {
                                successCount++;
                                // 更新 monthData 以包含保存后的数据（包括 id）
                                if (result.data && result.data.id) {
                                    monthData[day] = { ...(monthData[day] || {}), ...result.data };
                                }
                            } else if (result.success === false) {
                                const message = result.message || '';
                                if (message.includes('已存在') || message.includes('无变化')) {
                                    skipCount++;
                                } else {
                                    errorCount++;
                                    errors.push(`${day}日: ${message}`);
                                }
                            } else {
                                successCount++;
                                // 即使 success 字段未定义，如果返回了数据，也更新 monthData
                                if (result.data && result.data.id) {
                                    monthData[day] = { ...(monthData[day] || {}), ...result.data };
                                }
                            }
                            
                        } catch (error) {
                            errorCount++;
                            errors.push(`${day}日: ${error.message}`);
                        }
                    }
                }
                
                if (successCount > 0 || skipCount > 0) {
                    let message = '';
                    if (successCount > 0 && skipCount > 0) {
                        message = `数据处理完成！成功保存 ${successCount} 条记录，${skipCount} 条记录无需更新`;
                    } else if (successCount > 0) {
                        message = `数据保存成功！共保存 ${successCount} 条记录`;
                    } else if (skipCount > 0) {
                        message = `数据检查完成！${skipCount} 条记录已是最新，无需更新`;
                    }
                    
                    if (stockValue && stockValue.trim() !== '') {
                        message += '，库存数据已保存';
                    }
                    
                    if (errorCount > 0) {
                        message += `，${errorCount} 条记录保存失败`;
                    }
                    
                    showAlert(message, successCount > 0 ? 'success' : 'info');
                    
                    // 重新加载数据以确保显示最新的数据库值
                    await loadMonthData();
                } else if (errorCount > 0) {
                    showAlert(`保存失败：${errors.join('; ')}`, 'error');
                    // 即使保存失败，也重新加载数据以确保一致性
                    await loadMonthData();
                } else {
                    showAlert('没有需要保存的数据', 'info');
                }
                
            } catch (error) {
                showAlert('保存过程中发生错误，请检查网络连接后重试', 'error');
                console.error('保存错误:', error);
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        // 清空单日“成本”数据（只清空饮料/厨房成本，保留销售额）
        async function clearDayData(day) {
            if (!confirm(`确定要清空${day}日的饮料成本/厨房成本吗？销售额将保留（从KPI自动获取）。`)) {
                return;
            }
            
            const deleteBtn = event.target.closest('.delete-day-btn');
            const originalHTML = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<div class="loading"></div>';
            deleteBtn.disabled = true;
            
            try {
                const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

                // 只清空成本字段（销售额从 KPI 表获取，不存储在 cost 表）
                const recordData = {
                    date: dateStr,
                    c_beverage: 0,
                    c_kitchen: 0,
                    restaurant: currentRestaurant
                };

                let result = null;
                let id = monthData[day] && monthData[day].id ? monthData[day].id : null;

                // 如果前端没有 id，尝试按日期获取数据库记录（例如 KPI 触发器已提前写入 cost）
                if (!id) {
                    const existing = await getExistingCostRecordByDate(dateStr);
                    if (existing && existing.id) {
                        monthData[day] = { ...(monthData[day] || {}), ...existing };
                        id = existing.id;
                    }
                }

                if (id) {
                    // 清空成本：PUT 时不传 sales，避免任何情况下把销售额覆盖成 0
                    const payload = {
                        id,
                        date: dateStr,
                        c_beverage: 0,
                        c_kitchen: 0,
                        restaurant: currentRestaurant
                    };
                    result = await apiCall('', {
                        method: 'PUT',
                        body: JSON.stringify(payload)
                    });
                } else {
                    // 数据库确实没有记录时，插入一条 0 成本记录
                    result = await apiCall('', {
                        method: 'POST',
                        body: JSON.stringify(recordData)
                    });
                }

                if (result && result.success === false) {
                    throw new Error(result.message || '清空成本失败');
                }

                // 清空输入框（仅成本字段），销售额保持
                const costFields = ['c_beverage', 'c_kitchen'];
                costFields.forEach(field => {
                    const input = document.querySelector(`input[data-field="${field}"][data-day="${day}"]`);
                    if (input) input.value = '';
                });

                updateCalculations(day);
                updateInputColors();

                showAlert(`${day}日成本已清空（销售额保留）`, 'success');
                
            } catch (error) {
                showAlert(`清空${day}日成本失败: ${error.message}`, 'error');
                console.error('清空成本失败:', error);
            } finally {
                deleteBtn.innerHTML = originalHTML;
                deleteBtn.disabled = false;
            }
        }

        // 输入框光标定位处理
        let inputFirstClickMap = new Map();
        
        function handleInputFocus(input, isClick = false) {
            setTimeout(() => {
                if (isClick) {
                    const inputKey = `${input.dataset.field}-${input.dataset.day}`;
                    
                    if (inputFirstClickMap.has(inputKey)) {
                        return;
                    }
                    
                    inputFirstClickMap.set(inputKey, true);
                }
                
                // 注意：type="number" 的 input 在部分浏览器（如 Chrome）不支持 selection API，
                // 调用 select()/setSelectionRange() 会抛 InvalidStateError，导致后续逻辑中断。
                const inputType = (input.getAttribute('type') || input.type || '').toLowerCase();
                if (inputType === 'number') {
                    return;
                }

                try {
                    if (input.value) {
                        input.select();
                    } else if (typeof input.setSelectionRange === 'function') {
                        input.setSelectionRange(0, 0);
                    }
                } catch (err) {
                    // 忽略 selection 相关异常，确保不影响保存/计算等其它功能
                }
            }, 0);
        }

        function resetInputFirstClick(input) {
            const inputKey = `${input.dataset.field}-${input.dataset.day}`;
            inputFirstClickMap.delete(inputKey);
        }

        // 键盘快捷键支持
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveAllData();
            }
            
            if (e.key === 'Tab') {
                const inputs = Array.from(document.querySelectorAll('.excel-input'));
                const currentIndex = inputs.indexOf(document.activeElement);
                
                if (currentIndex !== -1) {
                    e.preventDefault();
                    const nextIndex = e.shiftKey ? 
                        (currentIndex - 1 + inputs.length) % inputs.length : 
                        (currentIndex + 1) % inputs.length;
                    inputs[nextIndex].focus();
                }
            }
            
            if (e.key === 'Enter' && document.activeElement.classList.contains('excel-input')) {
                e.preventDefault();
                const currentInput = document.activeElement;
                const field = currentInput.dataset.field;
                const currentDay = parseInt(currentInput.dataset.day);
                const nextDay = currentDay + 1;
                
                const nextInput = document.querySelector(`input[data-field="${field}"][data-day="${nextDay}"]`);
                if (nextInput) {
                    nextInput.focus();
                }
            }

            if (e.ctrlKey && e.key === 'v') {
                const activeElement = document.activeElement;
                if (activeElement && activeElement.classList.contains('excel-input')) {
                    const day = parseInt(activeElement.dataset.day);
                    const currentField = activeElement.dataset.field;
                    const row = activeElement.closest('tr');
                    
                    if (!row.classList.contains('editing-row')) {
                        showAlert(`请先点击编辑按钮进入${day}日的编辑模式`, 'info');
                        e.preventDefault();
                        return;
                    }
                    
                    pasteTargetDay = day;
                    e.preventDefault();
                    
                    if (navigator.clipboard && navigator.clipboard.readText) {
                        navigator.clipboard.readText().then(function(clipboardData) {
                            if (clipboardData) {
                                handlePasteData(clipboardData, pasteTargetDay, currentField);
                            } else {
                                showAlert('剪贴板为空', 'info');
                            }
                        }).catch(function(err) {
                            showAlert('无法访问剪贴板，请手动输入或使用右键粘贴', 'error');
                        });
                    } else {
                        showAlert('请使用右键菜单粘贴，或直接在输入框中按Ctrl+V', 'info');
                    }
                }
            }
        });

        // 输入框事件处理
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('excel-input')) {
                const field = e.target.dataset.field;
                const value = e.target.value;
                
                resetInputFirstClick(e.target);
                
                if (currencyFields.includes(field)) {
                    if (value.includes('.')) {
                        const parts = value.split('.');
                        if (parts[1] && parts[1].length > 2) {
                            e.target.value = parts[0] + '.' + parts[1].substring(0, 2);
                        }
                    }
                }

                updateInputColors();
            }
        });

        // 货币输入框失去焦点时格式化
        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('currency-input')) {
                const value = e.target.value;
                if (value && !isNaN(value) && value !== '') {
                    const num = parseFloat(value);
                    e.target.value = num.toFixed(2);
                }
            }
        }, true);

        // 输入框focus事件
        document.addEventListener('focus', function(e) {
            if (e.target.classList.contains('excel-input')) {
                handleInputFocus(e.target, false);
            }
        }, true);

        // 输入框click事件
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('excel-input')) {
                handleInputFocus(e.target, true);
            }
        });

        // 监听粘贴事件
        document.addEventListener('paste', function(e) {
            if (e.target.classList.contains('excel-input')) {
                const day = parseInt(e.target.dataset.day);
                const currentField = e.target.dataset.field;
                const row = e.target.closest('tr');
                
                if (!row.classList.contains('editing-row')) {
                    showAlert(`请先点击编辑按钮进入${day}日的编辑模式`, 'info');
                    e.preventDefault();
                    return;
                }
                
                const clipboardData = e.clipboardData || window.clipboardData;
                const pastedData = clipboardData.getData('text');
                
                if (pastedData && (pastedData.includes('\t') || pastedData.includes(',') || pastedData.split(/\s+/).length > 1)) {
                    e.preventDefault();
                    handlePasteData(pastedData, day, currentField);
                }
            }
        });

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);
    </script>
    <script>
        // 切换数字下拉菜单
        function toggleNumberDropdown() {
            if (!restaurantDropdownEnabled) return;
            const dropdown = document.getElementById('number-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
    
            // 更新选中状态
            updateSelectedNumber();
        }

        // 选择餐厅数字
        function selectNumber(number) {
            const restaurant = `j${number}`;
            if (!availableRestaurants.includes(restaurant)) {
                showAlert('您没有权限查看该店铺', 'warning');
                return;
            }
            const dropdown = document.getElementById('number-dropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
            switchRestaurant(restaurant);
        }

        // 更新选中的数字状态
        function updateSelectedNumber() {
            if (!restaurantDropdownEnabled) return;
            const info = restaurantConfig[currentRestaurant];
            const currentNumber = info ? String(info.number) : '';
            document.querySelectorAll('.number-item').forEach(item => {
                item.classList.remove('selected');
                if (item.textContent === currentNumber) {
                    item.classList.add('selected');
                }
            });
        }

        // 切换报表类型下拉菜单
        function toggleReportTypeDropdown() {
            if (!reportDropdownEnabled) return;
            const dropdown = document.getElementById('report-type-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        }

        // 点击外部关闭下拉菜单
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.number-dropdown')) {
                const dropdown = document.getElementById('number-dropdown');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            }

            // 关闭报表类型下拉菜单
            if (!e.target.closest('.report-type-selector')) {
                const reportDropdown = document.getElementById('report-type-dropdown');
                if (reportDropdown) {
                    reportDropdown.classList.remove('show');
                }
            }
        });

        // 切换编辑模式
        function toggleEdit(day) {
            const editBtn = document.getElementById(`edit-btn-${day}`);
            const inputs = document.querySelectorAll(`input[data-day="${day}"]`);
            const isEditing = editBtn.classList.contains('save-mode');
            
            if (isEditing) {
                saveSingleRowData(day);
            } else {
                setRowReadonly(day, false);
                
                editBtn.classList.add('save-mode');
                editBtn.innerHTML = '<i class="fas fa-save"></i>';
                editBtn.title = `保存${day}日数据`;
            }
        }

        // 保存单行数据
        async function saveSingleRowData(day) {
            const editBtn = document.getElementById(`edit-btn-${day}`);
            const originalHTML = editBtn.innerHTML;
            editBtn.innerHTML = '<div class="loading"></div>';
            editBtn.disabled = true;
            
            try {
                const cBeverage = parseFloat(getInputValue('c_beverage', day)) || 0;
                const cKitchen = parseFloat(getInputValue('c_kitchen', day)) || 0;
                
                const hasData = cBeverage > 0 || cKitchen > 0;

                if (hasData) {
                    const dateStr = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                    
                    const recordData = {
                        date: dateStr,
                        c_beverage: cBeverage,
                        c_kitchen: cKitchen,
                        restaurant: currentRestaurant
                    };
                    
                    let result;
                    // 只有存在数据库记录ID时才执行更新（PUT）
                    // monthData[day] 可能来自 KPI 同步的临时数据，没有 id，必须走 POST 插入
                    if (monthData[day] && monthData[day].id) {
                        recordData.id = monthData[day].id;
                        result = await apiCall('', {
                            method: 'PUT',
                            body: JSON.stringify(recordData)
                        });
                    } else {
                        result = await apiCall('', {
                            method: 'POST',
                            body: JSON.stringify(recordData)
                        });

                        // 如果提示“已存在”，则按日期查出 id，再改为 PUT 更新一次
                        if (result && result.success === false && String(result.message || '').includes('已存在')) {
                            const existing = await getExistingCostRecordByDate(dateStr);
                            if (existing && existing.id) {
                                monthData[day] = { ...(monthData[day] || {}), ...existing };
                                recordData.id = existing.id;
                                result = await apiCall('', {
                                    method: 'PUT',
                                    body: JSON.stringify(recordData)
                                });
                            }
                        }
                    }
                    
                    if (result.success === true || result.success !== false) {
                        showAlert(`${day}日数据保存成功`, 'success');
                        
                        // 重新加载整个月的数据以确保一致性
                        setRowReadonly(day, true);
                        editBtn.classList.remove('save-mode');
                        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                        editBtn.title = `编辑${day}日数据`;
                        
                        await loadMonthData(true);
                        return; // 提前返回，因为 loadMonthData 会刷新所有内容
                    } else {
                        const message = result.message || '';
                        if (message.includes('已存在') || message.includes('无变化')) {
                            showAlert(`${day}日数据无需更新`, 'info');
                        } else {
                            throw new Error(message);
                        }
                    }
                } else {
                    showAlert(`${day}日数据已保存（空记录）`, 'info');
                }
                
                setRowReadonly(day, true);
                
                editBtn.classList.remove('save-mode');
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.title = `编辑${day}日数据`;
                
                updateMonthStats();
                
            } catch (error) {
                showAlert(`保存${day}日数据失败: ${error.message}`, 'error');
                console.error('保存数据失败:', error);
            } finally {
                editBtn.disabled = false;
                if (!editBtn.classList.contains('save-mode')) {
                    editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                }
            }
        }
    </script>
</body>
</html>

