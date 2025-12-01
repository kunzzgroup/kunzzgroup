<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // 重定向到登录页面
    exit;
}

// 获取用户权限 - 直接检查注册码
$canApprove = false;
if (isset($_SESSION['user_id'])) {
    // 这里需要连接数据库检查用户的注册码
    $host = 'localhost';
    $dbname = 'u857194726_kunzzgroup';
    $dbuser = 'u857194726_kunzzgroup';
    $dbpass = 'Kholdings1688@';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $allowedCodes = ['SUPPORT88', 'IT4567', 'QX0EQP', 'HR2025','AZGQOY'];
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userCode = $stmt->fetchColumn();
        
        $canApprove = $userCode && in_array($userCode, $allowedCodes);
    } catch (PDOException $e) {
        $canApprove = false;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存货品管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
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
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(18px, 1.67vw, 32px);
        }
        
        .header h1 {
            font-size: clamp(20px, 2.6vw, 50px);
            font-weight: bold;
            color: #000000ff;
        }
        
        .header .controls {
            display: flex;
            align-items: center;
            gap: 0px;
        }

        .selector-button {
            background-color: #f99e00;
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
            transition: all 0.2s ease;
            width: clamp(90px, 6.77vw, 130px);
            justify-content: space-between;
            position: relative;
        }
        
        .selector-button:hover {
            background-color: #f98500ff;
            border-radius: 8px;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .selector-dropdown {
            position: absolute;
            top: 110%;
            right: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.2);
            width: 100%;
            z-index: 10000;
            display: none;
        }

        .selector-dropdown.show {
            display: block;
        }

        .selector-dropdown .dropdown-item {
            padding: clamp(6px, 0.42vw, 8px) clamp(10px, 0.83vw, 16px);
            cursor: pointer;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.2s;
            color: #000000ff;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            text-decoration: none;
            display: block;
        }

        .selector-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }

        .selector-dropdown .dropdown-item:hover {
            background-color: #f8f5eb;
            border-radius: 8px;
        }

        .selector-dropdown .dropdown-item.active {
            background-color: #f99e00 !important;
            border-radius: 4px !important;
            color: white !important;
        }

        /* 视图选择器样式 */
        .view-selector {
            position: relative;
            margin-right: 16px;
        }

        /* .view-selector .selector-button {
            background-color: #583e04;
            min-width: 120px;
        } */

        .view-selector .selector-button:hover {
            background-color: #f99e00;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .view-selector .selector-dropdown {
            width: 100%;
        }

        /* 系统选择器样式 */
        .system-selector {
            position: relative;
        }

        .system-selector .selector-button {
            width: clamp(80px, 6.77vw, 130px);
        }

        .system-selector .selector-dropdown {
            width: 100%;
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

        /* 响应式表格列宽 - 11列设置 */
        .excel-table th:nth-child(1), .excel-table td:nth-child(1) { width: 4%; }   /* 第1列 - 序号 */
        .excel-table th:nth-child(2), .excel-table td:nth-child(2) { width: 10%; }  /* 第2列 - 货品编号 */
        .excel-table th:nth-child(3), .excel-table td:nth-child(3) { width: 20%; }  /* 第3列 - 货品名字 */
        .excel-table th:nth-child(4), .excel-table td:nth-child(4) { width: 8%; }   /* 第4列 - 规格 */
        .excel-table th:nth-child(5), .excel-table td:nth-child(5) { width: 8%; }   /* 第5列 - 货品类型 */
        .excel-table th:nth-child(6), .excel-table td:nth-child(6) { width: 14%; }  /* 第6列 - 供应商 */
        .excel-table th:nth-child(7), .excel-table td:nth-child(7) { width: 7%; }   /* 第7列 - 申请人 */
        .excel-table th:nth-child(8), .excel-table td:nth-child(8) { width: 8%; }   /* 第8列 - 系统分配 */
        .excel-table th:nth-child(9), .excel-table td:nth-child(9) { width: 10%; }  /* 第9列 - 冰箱分类 */
        .excel-table th:nth-child(10), .excel-table td:nth-child(10) { width: 12%; }  /* 第10列 - 批准状态 */
        .excel-table th:nth-child(11), .excel-table td:nth-child(11) { width: 90px; }   /* 第11列 - 操作按钮 */

        /* Excel样式表格 */
        .excel-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.1);
            border: 2px solid #000000ff;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 300px);
            min-height: 100px;
            flex: 1;
            overflow: hidden;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed;  /* 添加这行确保列宽控制 */
        }

        .excel-table th {
            background: #636363;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .excel-table td {
            font-size: clamp(8px, 0.74vw, 14px);
            padding: 0;
            border: 1px solid #d1d5db;
            text-align: center;
            position: relative;
        }

        .excel-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .excel-table tr:hover {
            background-color: #f3f4f6;
        }

        /* 输入框容器样式 */
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            height: clamp(30px, 2.08vw, 40px);
        }

        /* 输入框样式 */
        .excel-input {
            width: 100%;
            height: clamp(30px, 2.08vw, 40px);
            border: none;
            background: #f0fdf4;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            transition: all 0.2s;
        }

        .excel-input.text-input {
            text-align: left;
            padding-left: 8px;
        }

        .excel-input.datetime-input {
            padding: clamp(0px, 0.42vw, 8px);
            text-align: center;
        }

        .excel-input:focus {
            background: #fff;
            border: 2px solid #583e04;
            outline: none;
            z-index: 5;
            position: relative;
        }

        .excel-input:not(:placeholder-shown) {
            background: #f0fdf4;
        }

        /* 下拉选择框样式 */
        .excel-select {
            width: 100%;
            height: clamp(30px, 2.08vw, 40px);
            border: none;
            background: #f0fdf4;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            transition: all 0.2s;
            cursor: pointer;
        }

        .excel-select:focus {
            background: #fff;
            border: 2px solid #583e04;
            outline: none;
            z-index: 5;
            position: relative;
        }

        .excel-select[disabled] {
            background: #f9fafb !important;
            pointer-events: none;
            cursor: not-allowed;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .excel-select.readonly {
            background: #f9fafb !important;
            pointer-events: none;
            cursor: not-allowed;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .excel-input[readonly] {
            background-color: #f0fdf4 !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: bold;
            color: #000000ff;
            cursor: not-allowed;
        }

        .excel-input[readonly]:focus {
            background-color: #f0fdf4 !important;
            border: 1px solid #f0fdf4 !important;
        }

        /* 数字字体样式 - 与第一个代码保持一致 */
        .excel-input[type="number"] {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        .table-scroll-container {
            overflow-x: hidden;
            overflow-y: auto;
            flex: 1;
            position: relative;
        }

        .excel-table thead {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #000000ff;
        }

        .excel-table thead tr {
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .excel-table th {
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .action-buttons {
            flex-shrink: 0;
        }

        .filter-bar {
            flex-shrink: 0;
        }

        /* 操作按钮 */
        .action-buttons {
            padding: clamp(16px, 1.25vw, 24px);
            background: #f8f5eb;
            border-top: 2px solid #583e04;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0px;
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

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }

        /* 统计信息 */
        .stats-info {
            display: flex;
            gap: 0px;
            align-items: center;
            font-size: clamp(8px, 0.74vw, 14px);
            color: #6b7280;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: clamp(90px, 7.82vw, 150px);
        }

        .stat-value {
            font-size: clamp(12px, 0.94vw, 18px);
            font-weight: bold;
            color: #000000ff;
        }

        /* 删除按钮 */
        .delete-row-btn {
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
            margin: 4px;
        }

        .delete-row-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .delete-row-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        /* 状态指示 */
        .status-approved {
            background-color: #d1fae5 !important;
            color: #065f46;
        }

        .status-pending {
            background-color: #fef3c7 !important;
            color: #92400e;
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

        /* 新行样式 */
        .new-row {
            background-color: #f0fdf4 !important;
        }

        /* 序号列样式 */
        .serial-number-cell {
            padding: 8px;
            text-align: center;
            font-weight: 600;
            color: #6b7280;
            background-color: #f9fafb;
        }

        /* 搜索过滤栏 */
        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: clamp(10px, 1.15vw, 22px) clamp(18px, 1.25vw, 24px);
            margin-bottom: clamp(14px, 1.25vw, 24px);
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        /* 确保统计信息在中间正确显示 */
        .filter-bar .stats-info {
            display: flex;
            gap: 20px;
            align-items: center;
            font-size: clamp(8px, 0.74vw, 14px);
            color: #6b7280;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-bar .stats-info .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: clamp(25px, 2.61vw, 50px);
        }

        .filter-bar .stats-info .stat-value {
            font-size: clamp(10px, 0.94vw, 18px);
            font-weight: bold;
            color: #000000ff;
        }

        @media (max-width: 768px) {
            .filter-bar .stats-info {
                flex-direction: column;
                gap: 8px;
                align-items: center;
            }
            
            .filter-bar .stats-info .stat-item {
                min-width: auto;
                width: 100%;
                justify-content: center;
            }
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: clamp(10px, 0.83vw, 16px);
            flex-wrap: wrap;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-item label {
            font-size: clamp(6px, 0.63vw, 12px);
            font-weight: 600;
            color: #000000ff;
        }

        .filter-input {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: clamp(8px, 0.74vw, 14px);
            background: white;
            color: #000000ff;
            min-width: 70px;
        }

        .filter-input:focus {
            outline: none;
            border-color: #000000ff;
            box-shadow: 0 0 0 2px rgba(32, 22, 0, 0.1);
        }

        /* 批准按钮样式 */
        .approve-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            padding: clamp(4px, 0.36vw, 8px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            margin: -6px auto;
        }

        .approve-btn:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .approve-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .approved-status {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            flex-direction: column;
        }

        .no-permission {
            color: #9ca3af;
            font-style: italic;
            text-align: center;
        }

        .approve-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
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
            margin: 2px;
        }

        .edit-btn:hover {
            background: #d97706;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
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

        /* 多选系统分配样式 */
        .multiselect-wrapper {
            position: relative;
            width: 100%;
            height: clamp(30px, 2.08vw, 40px);
        }

        .multiselect-trigger {
            width: 100%;
            height: 100%;
            border: none;
            background: #f0fdf4;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .multiselect-trigger:focus {
            background: #fff;
            border: 2px solid #583e04;
            outline: none;
        }

        .multiselect-trigger.readonly {
            background: #f9fafb !important;
            pointer-events: none;
            cursor: not-allowed;
        }

        .multiselect-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #583e04;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .multiselect-dropdown.show {
            display: block;
        }

        .multiselect-option {
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .multiselect-option:hover {
            background: #f3f4f6;
        }

        .multiselect-option input[type="checkbox"] {
            cursor: pointer;
        }

        .multiselect-option label {
            cursor: pointer;
            user-select: none;
            flex: 1;
            font-size: clamp(8px, 0.74vw, 14px);
        }

        .multiselect-selected {
            flex: 1;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        .excel-input.datetime-input.readonly {
            background: #f9fafb;
        }

        .excel-input.text-input.readonly {
            background: #f9fafb;
        }

        .action-cell {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 2px;
            padding: 4px;
            width: 100%;
            height: clamp(30px, 2.08vw, 40px);
            box-sizing: border-box;
        }

        /* 编辑行样式 */
        .excel-table tr.editing-row {
            background-color: #cde3ff !important;
        }

        .excel-table tr.editing-row td {
            background-color: #cde3ff !important;
        }

        .excel-table tr.editing-row .status-approved {
            background-color: #cde3ff !important;
        }

        .excel-table tr.editing-row .status-pending {
            background-color: #cde3ff !important;
        }

        /* 序号列在编辑模式下保持灰色背景 */
        .excel-table tr.editing-row .serial-number-cell {
            background-color: #f9fafb !important;
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

        /* 回到顶部按钮 */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #583e04;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.3);
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            z-index: 1000;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background-color: #462d03;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(88, 62, 4, 0.4);
        }

        .back-to-top:active {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>库存货品管理后台</h1>
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
                        <div class="dropdown-item" onclick="switchView('sot')">货品异常</div>
                    </div>
                </div>
                <div class="system-selector">
                    <button class="selector-button" onclick="toggleSystemSelector()">
                        <span id="current-system">总览</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="system-selector-dropdown">
                        <div class="dropdown-item active" onclick="switchSystem('overview')">总览</div>
                        <div class="dropdown-item" onclick="switchSystem('central')">中央</div>
                        <div class="dropdown-item" onclick="switchSystem('j1')">J1</div>
                        <div class="dropdown-item" onclick="switchSystem('j2')">J2</div>
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
                    <label>搜索货品</label>
                    <input type="text" class="filter-input" id="product-search-filter" placeholder="输入关键字搜索...">
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
                        <th style="min-width: 120px;">货品编号</th>
                        <th style="min-width: 200px;">货品名字</th>
                        <th style="min-width: 150px;">规格</th>
                        <th style="min-width: 120px;">货品类型</th>
                        <th style="min-width: 150px;">供应商</th>
                        <th style="min-width: 120px;">申请人</th>
                        <th style="min-width: 100px;">系统分配</th>
                        <th style="min-width: 120px;">冰箱分类</th>
                        <th style="min-width: 120px;">批准状态</th>
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

    <script>
    
        // 检查用户权限的函数
        async function checkUserPermissions() {
            try {
                const response = await fetch('check_permissions.php');
                const result = await response.json();
                return result.canApprove || false;
            } catch (error) {
                console.error('检查权限失败:', error);
                return false;
            }
        }

        // 全局变量存储用户权限
        let userCanApprove = false;

        // 初始化权限检查
        async function initPermissions() {
            userCanApprove = await checkUserPermissions();
        }

        const API_BASE_URL = 'stockapi.php';  // 如果在同一目录
        
        // 应用状态
        let stockData = [];
        let isLoading = false;
        let nextRowId = 1;
        let currentSystem = 'overview'; // 当前选择的系统：overview, central, j1, j2, j3
        const PRODUCT_SYSTEM_OPTIONS = [
            { value: 'overview', label: '总览' },
            { value: 'central', label: '中央' },
            { value: 'j1', label: 'J1' },
            { value: 'j2', label: 'J2' },
            { value: 'j3', label: 'J3' }
        ];
        const PRODUCT_VIEW_OPTIONS = [
            { value: 'list', label: '总库存' },
            { value: 'records', label: '进出货' },
            { value: 'remark', label: '货品备注' },
            { value: 'product', label: '货品种类' },
            { value: 'sot', label: '货品异常' }
        ];
        let cachedProductAllowedSystems = new Set();
        let cachedProductAllowedViews = new Set();

        // 输入框光标定位处理
        let inputFirstClickMap = new Map(); // 记录每个输入框是否已经被点击过

        function handleInputFocus(input, isClick = false) {
            // 延迟执行以确保focus事件完成后再设置光标位置
            setTimeout(() => {
                if (isClick) {
                    const inputKey = `${input.dataset.field}-${input.dataset.row}`;
                    
                    // 如果这个输入框已经被点击过，不处理光标位置
                    if (inputFirstClickMap.has(inputKey)) {
                        return; // 让浏览器处理正常的点击定位
                    }
                    
                    // 标记这个输入框已经被点击过
                    inputFirstClickMap.set(inputKey, true);
                }
                
                if (input.value) {
                    // 如果有值，选择所有内容（便于快速替换）
                    input.select();
                } else {
                    // 如果没有值，将光标设置到开头
                    input.setSelectionRange(0, 0);
                }
            }, 0);
        }

        // 重置输入框的首次点击状态（当输入框值发生变化时）
        function resetInputFirstClick(input) {
            const inputKey = `${input.dataset.field}-${input.dataset.row}`;
            inputFirstClickMap.delete(inputKey);
        }

        // 货币字段列表
        const currencyFields = [];

        // 格式化货币输入值显示
        function formatCurrencyDisplay(value) {
            if (!value || value === '') return '';
            const num = parseFloat(value);
            if (isNaN(num)) return 0.00;
            return num.toFixed(2);
        }

        function rebuildProductSystemDropdown(allowedSet) {
            const dropdown = document.getElementById('system-selector-dropdown');
            if (!dropdown) return;
            const filtered = (allowedSet && allowedSet.size > 0)
                ? PRODUCT_SYSTEM_OPTIONS.filter(opt => opt.value === 'overview' || allowedSet.has(opt.value))
                : PRODUCT_SYSTEM_OPTIONS.slice();
            if (!filtered.some(opt => opt.value === 'overview')) {
                filtered.unshift(PRODUCT_SYSTEM_OPTIONS[0]);
            }
            if (filtered.length === 0) {
                filtered.push(PRODUCT_SYSTEM_OPTIONS[0]);
            }
            let systemChanged = false;
            if (!filtered.some(opt => opt.value === currentSystem)) {
                currentSystem = filtered[0].value;
                systemChanged = true;
            }
            dropdown.innerHTML = '';
            filtered.forEach(opt => {
                const item = document.createElement('div');
                item.className = 'dropdown-item' + (opt.value === currentSystem ? ' active' : '');
                item.dataset.systemValue = opt.value;
                item.textContent = opt.label;
                item.onclick = function() { switchSystem(opt.value); };
                dropdown.appendChild(item);
            });
            if (systemChanged) {
                switchSystem(currentSystem);
            }
        }

        function rebuildProductViewDropdown(allowedSet) {
            const dropdown = document.getElementById('view-selector-dropdown');
            if (!dropdown) return;
            const filtered = (allowedSet && allowedSet.size > 0)
                ? PRODUCT_VIEW_OPTIONS.filter(opt => allowedSet.has(opt.value))
                : PRODUCT_VIEW_OPTIONS.slice();
            if (filtered.length === 0) {
                filtered.push(PRODUCT_VIEW_OPTIONS[0]);
            }
            dropdown.innerHTML = '';
            filtered.forEach(opt => {
                const item = document.createElement('div');
                item.className = 'dropdown-item' + (opt.value === 'product' ? ' active' : '');
                item.dataset.viewValue = opt.value;
                item.textContent = opt.label;
                item.onclick = function() { switchView(opt.value); };
                dropdown.appendChild(item);
            });
        }

        // 应用页面权限，隐藏不允许的下拉选项，并自动切换到允许的系统
        async function applyPagePermissions() {
            try {
                const res = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_page_permissions' })
                });
                const data = await res.json();
                if (!data.success) return false; // 返回false表示没有切换系统
                const perms = data.page_permissions || {};
                const current = perms.stock_inventory || {};
                const allowedSystems = new Set(current.system || []);
                const allowedViews = new Set(current.view || []); // Use stockproductname views
                cachedProductAllowedSystems = new Set(allowedSystems);
                cachedProductAllowedViews = new Set(allowedViews);
                rebuildProductSystemDropdown(allowedSystems);
                rebuildProductViewDropdown(allowedViews);

                // 如果有权限限制，检查当前系统是否被允许
                if (allowedSystems.size > 0) {
                    // 检查URL参数
                    const urlParams = new URLSearchParams(window.location.search);
                    const urlSystem = urlParams.get('system');
                    
                    // 如果当前系统不在允许列表中（但overview总是允许的），或者没有设置系统（首次进入），切换到第一个允许的系统
                    // 注意：overview 不在权限列表中，所以如果用户有权限限制，应该切换到第一个允许的系统
                    if ((currentSystem !== 'overview' && !allowedSystems.has(currentSystem)) || (!urlSystem && allowedSystems.size > 0 && currentSystem === 'overview')) {
                        const firstAllowed = Array.from(allowedSystems)[0];
                        if (firstAllowed) {
                            // 直接调用 switchSystem 来切换系统（这会更新UI和加载数据）
                            switchSystem(firstAllowed);
                            // 更新URL参数
                            const newUrl = new URL(window.location);
                            newUrl.searchParams.set('system', firstAllowed);
                            window.history.replaceState({}, '', newUrl);
                            return true; // 返回true表示已切换系统
                        }
                    }
                }
                
                if (allowedViews.size > 0 && !allowedViews.has('product')) {
                    const viewOrder = ['product', 'records', 'remark', 'sot', 'list'];
                    const viewRedirectMap = {
                        list: 'stocklistall.php',
                        records: 'stockeditall.php',
                        remark: 'stockremark.php',
                        product: 'stockproductname.php',
                        sot: 'stocksot.php'
                    };
                    const viewToOpen = viewOrder.find(view => allowedViews.has(view));
                    if (viewToOpen) {
                        const base = viewRedirectMap[viewToOpen] || 'stocklistall.php';
                        const param = currentSystem && currentSystem !== 'overview' ? `?system=${currentSystem}` : '';
                        window.location.href = `${base}${param}`;
                        return true;
                    }
                }
                
                return false; // 返回false表示没有切换系统
            } catch (e) {
                return false;
            }
        }

        // 初始化应用
        async function initApp() {
            await initPermissions();
            
            // 应用页面权限，自动切换到第一个允许的系统
            const systemSwitched = await applyPagePermissions();
            
            // 如果系统未切换，才加载数据（如果已切换，switchSystem 会处理数据加载）
            if (!systemSwitched) {
                loadStockData();
            }
            initRealTimeSearch(); // 添加这行
        }

        // 切换视图选择器下拉菜单
        function toggleViewSelector() {
            const dropdown = document.getElementById('view-selector-dropdown');
            dropdown.classList.toggle('show');
        }

        function switchView(viewType) {
            if (viewType === 'list') {
                // 跳转到总库存页面
                window.location.href = 'stocklistall.php';
            } else if (viewType === 'records') {
                // 跳转到进出货页面
                window.location.href = 'stockeditall.php';
            } else if (viewType === 'remark') {
                // 跳转到货品备注页面
                window.location.href = 'stockremark.php';
            } else if (viewType === 'sot') {
                // 跳转到货品异常页面
                window.location.href = 'stocksot.php';
            } else {
                // 保持在当前页面（货品种类）
                hideViewDropdown();
            }
        }

        // 隐藏视图选择器下拉菜单
        function hideViewDropdown() {
            const dropdown = document.getElementById('view-selector-dropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }

        // 切换系统选择器下拉菜单
        function toggleSystemSelector() {
            const dropdown = document.getElementById('system-selector-dropdown');
            dropdown.classList.toggle('show');
        }

        // 切换系统
        function switchSystem(system) {
            if (cachedProductAllowedSystems && cachedProductAllowedSystems.size > 0 && system !== 'overview' && !cachedProductAllowedSystems.has(system)) {
                const firstAllowed = Array.from(cachedProductAllowedSystems)[0];
                system = firstAllowed || 'overview';
            }
            if (currentSystem === system) {
                // 如果点击的是当前系统，只关闭下拉菜单
                document.getElementById('system-selector-dropdown').classList.remove('show');
                return;
            }
            
            currentSystem = system;
            
            // 更新系统选择器显示文本
            const systemNames = {
                'overview': '总览',
                'central': '中央',
                'j1': 'J1',
                'j2': 'J2',
                'j3': 'J3'
            };
            document.getElementById('current-system').textContent = systemNames[system];
            
            // 更新页面标题
            const titleSuffix = system === 'overview' ? '' : ` - ${systemNames[system]}`;
            document.querySelector('.header h1').textContent = `库存货品管理后台${titleSuffix}`;
            
            // 更新下拉菜单的active状态
            document.querySelectorAll('#system-selector-dropdown .dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            const activeItem = document.querySelector(`#system-selector-dropdown .dropdown-item[data-system-value="${system}"]`);
            if (activeItem) {
                activeItem.classList.add('active');
            }
            
            // 关闭下拉菜单
            document.getElementById('system-selector-dropdown').classList.remove('show');
            
            // 更新表头
            updateTableHeader();
            
            // 重新加载数据
            loadStockData();
        }

        // 更新表头
        function updateTableHeader() {
            const thead = document.querySelector('#excel-table thead tr');
            
            if (currentSystem === 'overview') {
                // 总览页面的表头
                thead.innerHTML = `
                    <th style="min-width: 60px;">序号</th>
                    <th style="min-width: 120px;">货品编号</th>
                    <th style="min-width: 200px;">货品名字</th>
                    <th style="min-width: 150px;">规格</th>
                    <th style="min-width: 120px;">货品类型</th>
                    <th style="min-width: 150px;">供应商</th>
                    <th style="min-width: 120px;">申请人</th>
                    <th style="min-width: 100px;">系统分配</th>
                    <th style="min-width: 120px;">冰箱分类</th>
                    <th style="min-width: 120px;">批准状态</th>
                    <th style="min-width: 100px;">操作</th>
                `;
            } else {
                // 系统页面的表头
                thead.innerHTML = `
                    <th style="min-width: 60px;">序号</th>
                    <th style="min-width: 120px;">货品编号</th>
                    <th style="min-width: 200px;">货品名字</th>
                    <th style="min-width: 150px;">规格</th>
                    <th style="min-width: 120px;">货品类型</th>
                    <th style="min-width: 150px;">供应商</th>
                    <th style="min-width: 120px;">申请人</th>
                    <th style="min-width: 100px;">系统分配</th>
                    <th style="min-width: 120px;">冰箱分类</th>
                    <th style="min-width: 120px;">状态</th>
                    <th style="min-width: 100px;">操作</th>
                `;
            }
        }
        
        // 返回上一页
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);

        // 回到顶部功能
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // 监听滚动事件，控制回到顶部按钮显示
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            // 使用防抖优化性能
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                const backToTopBtn = document.getElementById('back-to-top-btn');
                const scrollThreshold = 150; // 滚动超过300px后显示按钮
                
                if (window.pageYOffset > scrollThreshold) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            }, 10);
        });

        // API 调用函数
        async function apiCall(endpoint, options = {}) {
            try {
                console.log('API调用:', `${API_BASE_URL}${endpoint}`, options);
                
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                const responseText = await response.text();
                console.log('API响应:', responseText);
                
                if (!response.ok) {
                    throw new Error(`HTTP错误: ${response.status} - ${responseText}`);
                }
                
                const data = JSON.parse(responseText);
                console.log('解析后的数据:', data);
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                throw error;
            }
        }

        // 加载库存数据
        async function loadStockData() {
            if (isLoading) return;
            
            isLoading = true;
            
            try {
                // 获取搜索参数
                const productSearch = document.getElementById('product-search-filter').value.trim();
                const approvalStatus = document.getElementById('approval-status-filter').value.trim();

                // 构建URL参数
                const params = new URLSearchParams();
                params.append('action', 'list');

                // 如果不是总览页面，根据系统类型过滤数据
                if (currentSystem !== 'overview') {
                    const systemTypes = {
                        'central': 'Central',
                        'j1': 'J1',
                        'j2': 'J2',
                        'j3': 'J3'
                    };
                    params.append('system_assign', systemTypes[currentSystem]);
                }

                if (productSearch) params.append('product_search', productSearch);
                if (approvalStatus) params.append('approval_status', approvalStatus);
                
                const url = `${API_BASE_URL}?${params.toString()}`;
                console.log('请求URL:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const responseText = await response.text();
                console.log('API响应文本:', responseText);
                
                if (!response.ok) {
                    throw new Error(`HTTP错误: ${response.status} - ${responseText}`);
                }
                
                const result = JSON.parse(responseText);
                console.log('解析后的数据:', result);
                
                if (result.success) {
                    stockData = result.data || [];
                    generateStockTable();
                    updateStats();
                    showAlert(`库存数据加载成功，共找到 ${stockData.length} 条记录`, 'success');
                } else {
                    throw new Error(result.message || '加载失败');
                }
                
            } catch (error) {
                console.error('加载数据失败:', error);
                stockData = [];
                generateStockTable();
                updateStats();
                showAlert('数据加载失败: ' + error.message, 'error');
            } finally {
                isLoading = false;
            }
        }

        // 实时搜索功能
        function initRealTimeSearch() {
            const productSearchInput = document.getElementById('product-search-filter');
            const approvalStatusSelect = document.getElementById('approval-status-filter');
            
            // 防抖函数
            function debounce(func, delay) {
                let timeoutId;
                return function (...args) {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => func.apply(this, args), delay);
                };
            }
            
            // 创建防抖版本的搜索函数
            const debouncedSearch = debounce(loadStockData, 300);
            
            // 为货品搜索输入框添加实时搜索
            if (productSearchInput) {
                productSearchInput.addEventListener('input', debouncedSearch);
            }
            
            // 为批准状态选择框添加实时搜索
            if (approvalStatusSelect) {
                approvalStatusSelect.addEventListener('change', loadStockData);
            }
        }

        // 清空过滤器函数（保留但简化）
        function clearFilters() {
            document.getElementById('product-search-filter').value = '';
            document.getElementById('approval-status-filter').value = '';
            
            showAlert('过滤器已清空，重新加载所有数据', 'info');
            loadStockData();
        }

        // 创建多选系统分配组件
        function createMultiSelectSystemAssign(selectedValues, rowId, isReadonly) {
            const systemOptions = [
                { value: 'Central', label: '中央' },
                { value: 'J1', label: 'J1' },
                { value: 'J2', label: 'J2' },
                { value: 'J3', label: 'J3' }
            ];
            
            // 将逗号分隔的字符串转换为数组
            const selectedArray = selectedValues ? selectedValues.split(',').map(v => v.trim()).filter(v => v) : [];
            
            // 生成显示文本
            const displayText = selectedArray.length > 0 
                ? selectedArray.map(val => {
                    const opt = systemOptions.find(o => o.value === val);
                    return opt ? opt.label : val;
                }).join(', ')
                : '选择系统';
            
            const readonlyClass = isReadonly ? 'readonly' : '';
            const disabled = isReadonly ? 'disabled' : '';
            
            return `
                <div class="multiselect-wrapper" data-field="system_assign" data-row="${rowId}">
                    <div class="multiselect-trigger ${readonlyClass}" onclick="${!isReadonly ? `toggleMultiSelect('${rowId}', event)` : ''}" ${disabled}>
                        <span class="multiselect-selected">${displayText}</span>
                        <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                    </div>
                    <div class="multiselect-dropdown" id="multiselect-${rowId}">
                        ${systemOptions.map(opt => `
                            <div class="multiselect-option" onclick="event.stopPropagation()">
                                <input type="checkbox" 
                                    id="system-${rowId}-${opt.value}" 
                                    value="${opt.value}"
                                    ${selectedArray.includes(opt.value) ? 'checked' : ''}
                                    ${disabled}
                                    onchange="updateMultiSelectDisplay('${rowId}')">
                                <label for="system-${rowId}-${opt.value}">${opt.label}</label>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // 创建多选冰箱分类组件
        function createMultiSelectFreezerCategory(selectedValues, rowId, isReadonly) {
            const freezerOptions = [
                { value: 'K1-1', label: 'K1-1' },
                { value: 'K1-2', label: 'K1-2' },
                { value: 'K1-3', label: 'K1-3' },
                { value: 'K1-4', label: 'K1-4' },
                { value: 'K1-5', label: 'K1-5' },
                { value: 'K1-6', label: 'K1-6' },
                { value: 'K1-7', label: 'K1-7' },
                { value: 'C-1', label: 'C-1' },
                { value: 'KDI-1', label: 'KDI-1' },
                { value: 'KDI-2', label: 'KDI-2' },
                { value: 'KDI-3', label: 'KDI-3' },
                { value: 'KDI-4', label: 'KDI-4' },
                { value: 'S1-1', label: 'S1-1' },
                { value: 'S1-2', label: 'S1-2' },
                { value: 'S1-3', label: 'S1-3' },
                { value: 'S1-4', label: 'S1-4' },
                { value: 'SBS-1', label: 'SBS-1' },
                { value: 'SBS-2', label: 'SBS-2' },
                { value: 'SBDI-1', label: 'SBDI-1' },
                { value: 'SBDI-2', label: 'SBDI-2' }
            ];

            const selectedArray = selectedValues 
                ? selectedValues.split(',').map(v => v.trim()).filter(v => v) 
                : [];

            const displayText = selectedArray.length > 0 
                ? selectedArray.join(', ')
                : '选择分类';

            const readonlyClass = isReadonly ? 'readonly' : '';
            const disabled = isReadonly ? 'disabled' : '';

            return `
                <div class="multiselect-wrapper" data-field="freezer_category" data-row="${rowId}">
                    <div class="multiselect-trigger ${readonlyClass}" onclick="${!isReadonly ? `toggleMultiSelectFreezer('${rowId}', event)` : ''}" ${disabled}>
                        <span class="multiselect-selected">${displayText}</span>
                        <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                    </div>
                    <div class="multiselect-dropdown" id="multiselect-freezer-${rowId}">
                        ${freezerOptions.map(opt => `
                            <div class="multiselect-option" onclick="event.stopPropagation()">
                                <input type="checkbox"
                                    id="freezer-${rowId}-${opt.value}"
                                    value="${opt.value}"
                                    ${selectedArray.includes(opt.value) ? 'checked' : ''}
                                    ${disabled}
                                    onchange="updateMultiSelectFreezerDisplay('${rowId}')">
                                <label for="freezer-${rowId}-${opt.value}">${opt.label}</label>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // 切换多选下拉框
        function toggleMultiSelect(rowId, event) {
            event.stopPropagation();
            const dropdown = document.getElementById(`multiselect-${rowId}`);
            
            // 关闭其他所有多选下拉框
            document.querySelectorAll('.multiselect-dropdown.show').forEach(dd => {
                if (dd.id !== `multiselect-${rowId}`) {
                    dd.classList.remove('show');
                }
            });
            
            dropdown.classList.toggle('show');
        }

        // 专用于冰箱分类的多选下拉框切换
        function toggleMultiSelectFreezer(rowId, event) {
            event.stopPropagation();
            const dropdown = document.getElementById(`multiselect-freezer-${rowId}`);
            document.querySelectorAll('.multiselect-dropdown.show').forEach(dd => {
                if (dd.id !== `multiselect-freezer-${rowId}`) {
                    dd.classList.remove('show');
                }
            });
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        }

        // 更新多选显示文本
        function updateMultiSelectDisplay(rowId) {
            const systemOptions = [
                { value: 'Central', label: '中央' },
                { value: 'J1', label: 'J1' },
                { value: 'J2', label: 'J2' },
                { value: 'J3', label: 'J3' }
            ];
            
            const checkboxes = document.querySelectorAll(`#multiselect-${rowId} input[type="checkbox"]:checked`);
            const selectedValues = Array.from(checkboxes).map(cb => cb.value);
            
            const displayText = selectedValues.length > 0 
                ? selectedValues.map(val => {
                    const opt = systemOptions.find(o => o.value === val);
                    return opt ? opt.label : val;
                }).join(', ')
                : '选择系统';
            
            const wrapper = document.querySelector(`div[data-field="system_assign"][data-row="${rowId}"]`);
            if (wrapper) {
                const trigger = wrapper.querySelector('.multiselect-selected');
                if (trigger) {
                    trigger.textContent = displayText;
                }
            }
        }

        // 更新冰箱分类多选显示文本
        function updateMultiSelectFreezerDisplay(rowId) {
            const checkboxes = document.querySelectorAll(`#multiselect-freezer-${rowId} input[type="checkbox"]:checked`);
            const selectedValues = Array.from(checkboxes).map(cb => cb.value);
            const displayText = selectedValues.length > 0 ? selectedValues.join(', ') : '选择分类';
            const wrapper = document.querySelector(`div[data-field="freezer_category"][data-row="${rowId}"]`);
            if (wrapper) {
                const trigger = wrapper.querySelector('.multiselect-selected');
                if (trigger) {
                    trigger.textContent = displayText;
                }
            }
        }

        // 生成库存表格
        function generateStockTable() {
            const tbody = document.getElementById('excel-tbody');
            tbody.innerHTML = '';
            
            // 按货品名称的第一个字母排序
            const sortedData = [...stockData].sort((a, b) => {
                const nameA = (a.product_name || '').trim().toLowerCase();
                const nameB = (b.product_name || '').trim().toLowerCase();
                
                if (nameA < nameB) return -1;
                if (nameA > nameB) return 1;
                return 0;
            });
            
            sortedData.forEach((item, index) => {
                const row = createStockRow(item, index);
                tbody.appendChild(row);
            });
        }

        // 创建库存行
        function createStockRow(data = {}, index = -1) {
            const row = document.createElement('tr');
            const isNewRow = index === -1;
            const rowId = isNewRow ? `new-${nextRowId++}` : data.id || index;
            
            if (isNewRow) {
                row.classList.add('new-row');
            }
            
            // 根据批准状态设置行样式
            if (data.approver) {
                row.classList.add('status-approved');
            } else if (!isNewRow) {
                row.classList.add('status-pending');
            }
            
            // 根据当前系统类型生成不同的行内容
            if (currentSystem === 'overview') {
                // 总览页面的行
                row.innerHTML = `
                    <td class="serial-number-cell">
                        ${isNewRow ? '-' : (index + 1)}
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="product_code" data-row="${rowId}" 
                            value="${data.product_code || ''}" placeholder="货品编号" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="product_name" data-row="${rowId}" 
                            value="${data.product_name || ''}" placeholder="货品名称" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        <select class="excel-select ${!isNewRow ? 'readonly' : ''}" data-field="specification" data-row="${rowId}" 
                            required ${!isNewRow ? 'disabled' : ''}>
                            <option value="">选择规格</option>
                            <option value="Tub" ${data.specification === 'Tub' ? 'selected' : ''}>Tub</option>
                            <option value="Kilo" ${data.specification === 'Kilo' ? 'selected' : ''}>Kilo</option>
                            <option value="Piece" ${data.specification === 'Piece' ? 'selected' : ''}>Piece</option>
                            <option value="Bottle" ${data.specification === 'Bottle' ? 'selected' : ''}>Bottle</option>
                            <option value="Box" ${data.specification === 'Box' ? 'selected' : ''}>Box</option>
                            <option value="Packet" ${data.specification === 'Packet' ? 'selected' : ''}>Packet</option>
                            <option value="Carton" ${data.specification === 'Carton' ? 'selected' : ''}>Carton</option>
                            <option value="Tin" ${data.specification === 'Tin' ? 'selected' : ''}>Tin</option>
                            <option value="Roll" ${data.specification === 'Roll' ? 'selected' : ''}>Roll</option>
                            <option value="Nos" ${data.specification === 'Nos' ? 'selected' : ''}>Nos</option>
                            <option value="mL" ${data.specification === 'mL' ? 'selected' : ''}>mL</option>
                            <option value="Glass" ${data.specification === 'Glass' ? 'selected' : ''}>Glass</option>
                        </select>
                    </td>
                    <td>
                        <select class="excel-select ${!isNewRow ? 'readonly' : ''}" data-field="category" data-row="${rowId}" 
                            required ${!isNewRow ? 'disabled' : ''}>
                            <option value="">选择类型</option>
                            <option value="Drinks" ${data.category === 'Drinks' ? 'selected' : ''}>Drinks</option>
                            <option value="Sake" ${data.category === 'Sake' ? 'selected' : ''}>Sake</option>
                            <option value="Kitchen" ${data.category === 'Kitchen' ? 'selected' : ''}>Kitchen</option>
                            <option value="Sushi Bar" ${data.category === 'Sushi Bar' ? 'selected' : ''}>Sushi Bar</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="supplier" data-row="${rowId}" 
                            value="${data.supplier || ''}" placeholder="供应商名称" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="applicant" data-row="${rowId}" 
                            value="${data.applicant || ''}" placeholder="申请人" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        ${createMultiSelectSystemAssign(data.system_assign || '', rowId, !isNewRow)}
                    </td>
                    <td>
                        ${createMultiSelectFreezerCategory(data.freezer_category || '', rowId, !isNewRow)}
                    </td>
                    <td style="padding: 8px;">
                        ${data.approver ? 
                            `<span style="color: #065f46; font-weight: 600;">已批准</span>` : 
                            (userCanApprove && !isNewRow ? 
                                `<button class="approve-btn" onclick="approveRecord('${rowId}')">
                                    <i class="fas fa-check"></i>
                                    批准
                                </button>` : 
                                `<span style="color: #92400e; font-weight: 600;">待批准</span>`
                            )
                        }
                    </td>
                    <td class="action-cell">
                        <button class="edit-btn ${isNewRow ? 'save-mode' : ''}" id="edit-btn-${rowId}" onclick="toggleEdit('${rowId}')" title="${isNewRow ? '保存记录' : '编辑记录'}">
                            <i class="fas ${isNewRow ? 'fa-save' : 'fa-edit'}"></i>
                        </button>
                        <button class="delete-row-btn" onclick="deleteRow('${rowId}')" title="删除此行">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
            } else {
                // 系统页面的行
                const systemNames = {
                    'central': '中央',
                    'j1': 'J1',
                    'j2': 'J2',
                    'j3': 'J3'
                };
                const systemValues = {
                    'central': 'Central',
                    'j1': 'J1',
                    'j2': 'J2',
                    'j3': 'J3'
                };
                
                row.innerHTML = `
                    <td class="serial-number-cell">
                        ${isNewRow ? '-' : (index + 1)}
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="product_code" data-row="${rowId}" 
                            value="${data.product_code || ''}" placeholder="货品编号" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="product_name" data-row="${rowId}" 
                            value="${data.product_name || ''}" placeholder="货品名称" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        <select class="excel-select ${!isNewRow ? 'readonly' : ''}" data-field="specification" data-row="${rowId}" 
                            required ${!isNewRow ? 'disabled' : ''}>
                            <option value="">选择规格</option>
                            <option value="Tub" ${data.specification === 'Tub' ? 'selected' : ''}>Tub</option>
                            <option value="Kilo" ${data.specification === 'Kilo' ? 'selected' : ''}>Kilo</option>
                            <option value="Piece" ${data.specification === 'Piece' ? 'selected' : ''}>Piece</option>
                            <option value="Bottle" ${data.specification === 'Bottle' ? 'selected' : ''}>Bottle</option>
                            <option value="Box" ${data.specification === 'Box' ? 'selected' : ''}>Box</option>
                            <option value="Packet" ${data.specification === 'Packet' ? 'selected' : ''}>Packet</option>
                            <option value="Carton" ${data.specification === 'Carton' ? 'selected' : ''}>Carton</option>
                            <option value="Tin" ${data.specification === 'Tin' ? 'selected' : ''}>Tin</option>
                            <option value="Roll" ${data.specification === 'Roll' ? 'selected' : ''}>Roll</option>
                            <option value="Nos" ${data.specification === 'Nos' ? 'selected' : ''}>Nos</option>
                            <option value="mL" ${data.specification === 'mL' ? 'selected' : ''}>mL</option>
                            <option value="Glass" ${data.specification === 'Glass' ? 'selected' : ''}>Glass</option>
                        </select>
                    </td>
                    <td>
                        <select class="excel-select ${!isNewRow ? 'readonly' : ''}" data-field="category" data-row="${rowId}" 
                            required ${!isNewRow ? 'disabled' : ''}>
                            <option value="">选择类型</option>
                            <option value="Drinks" ${data.category === 'Drinks' ? 'selected' : ''}>Drinks</option>
                            <option value="Sake" ${data.category === 'Sake' ? 'selected' : ''}>Sake</option>
                            <option value="Kitchen" ${data.category === 'Kitchen' ? 'selected' : ''}>Kitchen</option>
                            <option value="Sushi Bar" ${data.category === 'Sushi Bar' ? 'selected' : ''}>Sushi Bar</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="supplier" data-row="${rowId}" 
                            value="${data.supplier || ''}" placeholder="供应商名称" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" class="excel-input text-input ${!isNewRow ? 'readonly' : ''}" data-field="applicant" data-row="${rowId}" 
                            value="${data.applicant || ''}" placeholder="申请人" required ${!isNewRow ? 'readonly disabled' : ''}>
                    </td>
                    <td>
                        <select class="excel-select readonly" data-field="system_assign" data-row="${rowId}" disabled>
                            <option value="${systemValues[currentSystem]}" selected>${systemNames[currentSystem]}</option>
                        </select>
                    </td>
                    <td>
                        ${createMultiSelectFreezerCategory(data.freezer_category || '', rowId, !isNewRow)}
                    </td>
                    <td style="padding: 8px;">
                        ${data.approver ? 
                            '<span style="color: #065f46; font-weight: 600;">已批准</span>' : 
                            '<span style="color: #92400e; font-weight: 600;">待批准</span>'
                        }
                    </td>
                    <td class="action-cell">
                        <button class="edit-btn ${isNewRow ? 'save-mode' : ''}" id="edit-btn-${rowId}" onclick="toggleEdit('${rowId}')" title="${isNewRow ? '保存记录' : '编辑记录'}">
                            <i class="fas ${isNewRow ? 'fa-save' : 'fa-edit'}"></i>
                        </button>
                        <button class="delete-row-btn" onclick="deleteRow('${rowId}')" title="删除此行">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
            }
            
            return row;
        }

        // 添加新行
        function addNewRow() {
            const tbody = document.getElementById('excel-tbody');
            
            // 根据当前系统类型设置系统分配的默认值
            let defaultSystemAssign = '';
            if (currentSystem !== 'overview') {
                const systemValues = {
                    'central': 'Central',
                    'j1': 'J1',
                    'j2': 'J2',
                    'j3': 'J3'
                };
                defaultSystemAssign = systemValues[currentSystem];
            }
            
            const newData = {
                product_code: '',
                product_name: '',
                specification: '',
                category: '',
                supplier: '',
                applicant: '',
                system_assign: defaultSystemAssign,  // 根据当前系统设置默认值
                approver: ''
            };
            
            const newRow = createStockRow(newData);
            tbody.appendChild(newRow);
            
            // 聚焦到货品编号输入框
            const productCodeInput = newRow.querySelector('input[data-field="product_code"]');
            if (productCodeInput) {
                productCodeInput.focus();
            }
            
            // 设置新行为编辑模式（非只读状态）
            setTimeout(() => {
                const rowId = `new-${nextRowId - 1}`; // 获取刚创建的行ID
                const newRowElement = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr');
                if (newRowElement) {
                    newRowElement.classList.add('editing-row');
                }
            }, 0);
            
            updateStats();
        }

        // 删除行
        function deleteRow(rowId) {
            if (!confirm('确定要删除这行数据吗？此操作不可恢复！')) {
                return;
            }
            
            const row = document.querySelector(`tr:has(input[data-row="${rowId}"])`);
            if (row) {
                // 如果是数据库中的记录，需要调用API删除
                if (!rowId.toString().startsWith('new-')) {
                    deleteFromDatabase(rowId);
                }
                
                row.remove();
                updateStats();
                showAlert('行已删除', 'success');
            }
        }

        // 从数据库删除记录
        async function deleteFromDatabase(id) {
            try {
                const response = await fetch(`${API_BASE_URL}?id=${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const responseText = await response.text();
                console.log('DELETE响应:', responseText);
                const result = JSON.parse(responseText);
                
                if (!result.success) {
                    throw new Error(result.message || '删除失败');
                }
            } catch (error) {
                showAlert('删除记录失败: ' + error.message, 'error');
            }
        }

        // 保存所有数据
        async function saveAllData() {
            if (isLoading) return;
            
            const saveBtn = event.target;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<div class="loading"></div> 保存中...';
            saveBtn.disabled = true;
            
            try {
                const rows = document.querySelectorAll('#excel-tbody tr');
                let successCount = 0;
                let errorCount = 0;
                const errors = [];
                
                for (const row of rows) {
                    const rowData = extractRowData(row);
                    
                    // 验证必填字段
                    if (!rowData.product_code || !rowData.product_name || !rowData.specification || 
                        !rowData.category || !rowData.supplier || !rowData.applicant) {
                        continue; // 跳过不完整的行
                    }
                    
                    try {
                        const rowId = row.querySelector('input').dataset.row;
                        let result;
                        
                        if (rowId.toString().startsWith('new-')) {
                            // 新记录
                            const response = await fetch(API_BASE_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(rowData)
                            });
                            const responseText = await response.text();
                            console.log('POST响应:', responseText);
                            result = JSON.parse(responseText);
                        } else {
                            // 更新现有记录
                            rowData.id = rowId;
                            const response = await fetch(API_BASE_URL, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(rowData)
                            });
                            const responseText = await response.text();
                            console.log('PUT响应:', responseText);
                            result = JSON.parse(responseText);
                        }
                        
                        if (result.success) {
                            successCount++;
                            // 更新行ID（针对新记录）
                            if (rowId.toString().startsWith('new-') && result.data && result.data.id) {
                                updateRowId(row, rowId, result.data.id);
                            }
                        } else {
                            throw new Error(result.message || '保存失败');
                        }
                        
                    } catch (error) {
                        errorCount++;
                        errors.push(`第${Array.from(rows).indexOf(row) + 1}行: ${error.message}`);
                    }
                }
                
                if (successCount > 0) {
                    showAlert(`成功保存 ${successCount} 条记录${errorCount > 0 ? `，${errorCount} 条失败` : ''}`, 'success');
                    // 重新加载数据以确保同步
                    await loadStockData();
                } else if (errorCount > 0) {
                    showAlert(`保存失败：${errors.join('; ')}`, 'error');
                } else {
                    showAlert('没有需要保存的完整数据', 'info');
                }
                
            } catch (error) {
                showAlert('保存过程中发生错误', 'error');
                console.error('保存错误:', error);
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        // 提取行数据
        function extractRowData(row) {
            const data = {};
            const inputs = row.querySelectorAll('input[data-field]');
            const selects = row.querySelectorAll('select[data-field]');
            
            inputs.forEach(input => {
                const field = input.dataset.field;
                let value = input.value.trim();
                
                data[field] = value;
            });
            
            selects.forEach(select => {
                const field = select.dataset.field;
                let value = select.value.trim();
                
                data[field] = value;
            });
            
            // 提取多选系统分配的值
            const systemAssignWrapper = row.querySelector('div[data-field="system_assign"]');
            if (systemAssignWrapper) {
                const rowId = systemAssignWrapper.dataset.row;
                const checkboxes = systemAssignWrapper.querySelectorAll('input[type="checkbox"]:checked');
                const selectedValues = Array.from(checkboxes).map(cb => cb.value);
                data.system_assign = selectedValues.join(',');
            }

            // 提取多选冰箱分类的值
            const freezerWrapper = row.querySelector('div[data-field="freezer_category"]');
            if (freezerWrapper) {
                const checkboxes = freezerWrapper.querySelectorAll('input[type="checkbox"]:checked');
                const selectedValues = Array.from(checkboxes).map(cb => cb.value);
                data.freezer_category = selectedValues.join(',');
            }

            // 在系统页面保存现有记录时，保持原始的系统分配设置
            if (currentSystem !== 'overview') {
                const rowIdentifier = row.querySelector('input[data-field]')?.dataset.row;
                if (rowIdentifier && !rowIdentifier.toString().startsWith('new-')) {
                    const originalRecord = stockData.find(item => item.id == rowIdentifier);
                    if (originalRecord && originalRecord.system_assign) {
                        data.system_assign = originalRecord.system_assign;
                    }
                }
            }
            
            // 检查是否已批准（通过查看批准状态列的内容）
            const approvalStatusCell = row.querySelector('td:nth-child(10)'); // 批准状态列（因为加了冰箱分类列，序号+1）
            const isApproved = approvalStatusCell && approvalStatusCell.textContent.includes('已批准');
            
            // 如果已批准，从stockData中获取实际的批准人
            if (isApproved) {
                const rowId = row.querySelector('input[data-field]')?.dataset.row;
                if (rowId) {
                    const originalRecord = stockData.find(item => item.id == rowId);
                    if (originalRecord && originalRecord.approver) {
                        data.approver = originalRecord.approver;
                    }
                }
            }
            
            return data;
        }

        // 更新行ID
        function updateRowId(row, oldId, newId) {
            const inputs = row.querySelectorAll('input');
            const selects = row.querySelectorAll('select');
            
            inputs.forEach(input => {
                if (input.dataset.row === oldId) {
                    input.dataset.row = newId;
                }
            });
            
            selects.forEach(select => {
                if (select.dataset.row === oldId) {
                    select.dataset.row = newId;
                }
            });
            
            const deleteBtn = row.querySelector('.delete-row-btn');
            if (deleteBtn) {
                deleteBtn.setAttribute('onclick', `deleteRow('${newId}')`);
            }
            
            // 移除新行样式
            row.classList.remove('new-row');
        }

        // 更新统计信息
        function updateStats() {
            const rows = document.querySelectorAll('#excel-tbody tr');
            let totalRecords = rows.length;
            let approvedCount = 0;
            let pendingCount = 0;
            
            rows.forEach(row => {
                // 检查是否已批准（通过查看批准状态列的内容）
                const approvalStatusCell = row.querySelector('td:nth-child(10)'); // 批准状态列（因为加了冰箱分类列，序号+1）
                const isApproved = approvalStatusCell && approvalStatusCell.textContent.includes('已批准');
                
                if (isApproved) {
                    approvedCount++;
                } else {
                    pendingCount++;
                }
            });
            
            document.getElementById('total-records').textContent = totalRecords;
            document.getElementById('approved-count').textContent = approvedCount;
            document.getElementById('pending-count').textContent = pendingCount;
        }

        // 清空过滤器
        function clearFilters() {
            document.getElementById('product-code-filter').value = '';
            document.getElementById('product-name-filter').value = '';
            document.getElementById('approval-status-filter').value = '';
            
            showAlert('过滤器已清空，重新加载所有数据', 'info');
            loadStockData();
        }

        // 完全替换现有的 showAlert 函数
        function showAlert(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            // 先检查并限制通知数量（在添加新通知之前）
            const existingToasts = container.querySelectorAll('.toast');
            while (existingToasts.length >= 3) {
                closeToast(existingToasts[0].id);
                // 立即从DOM移除，不等待动画
                if (existingToasts[0].parentNode) {
                    existingToasts[0].parentNode.removeChild(existingToasts[0]);
                }
                // 重新获取当前通知列表
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

            // 显示动画
            setTimeout(() => {
                toast.classList.add('show');
            }, 0);

            // 自动关闭
            setTimeout(() => {
                closeToast(toastId);
            }, 700);
        }

        // 添加关闭通知的函数
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

        // 添加关闭所有通知的函数（可选）
        function closeAllToasts() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                closeToast(toast.id);
            });
        }

        // 输入框和下拉选择框事件处理
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('excel-input')) {
                const field = e.target.dataset.field;
                const value = e.target.value;
                const row = e.target.closest('tr');

                resetInputFirstClick(e.target);
                updateStats();
            }
        });

        // 下拉选择框事件处理
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('excel-select')) {
                const field = e.target.dataset.field;
                const value = e.target.value;
                const row = e.target.closest('tr');

                updateStats();
            }
        });


        // 键盘快捷键支持
        document.addEventListener('keydown', function(e) {
            // Ctrl+S 保存数据
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveAllData();
            }
            
            // Ctrl+N 添加新行
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                addNewRow();
            }
            
            // Tab键在输入框和下拉选择框间移动
            if (e.key === 'Tab') {
                const inputs = Array.from(document.querySelectorAll('.excel-input:not([readonly])'));
                const selects = Array.from(document.querySelectorAll('.excel-select:not([disabled])'));
                const allElements = [...inputs, ...selects].sort((a, b) => {
                    const aRow = a.closest('tr');
                    const bRow = b.closest('tr');
                    if (aRow === bRow) {
                        return Array.from(aRow.children).indexOf(a.closest('td')) - Array.from(bRow.children).indexOf(b.closest('td'));
                    }
                    return Array.from(document.querySelectorAll('#excel-tbody tr')).indexOf(aRow) - Array.from(document.querySelectorAll('#excel-tbody tr')).indexOf(bRow);
                });
                
                const currentIndex = allElements.indexOf(document.activeElement);
                
                if (currentIndex !== -1) {
                    e.preventDefault();
                    const nextIndex = e.shiftKey ? 
                        (currentIndex - 1 + allElements.length) % allElements.length : 
                        (currentIndex + 1) % allElements.length;
                    allElements[nextIndex].focus();
                }
            }
            
            // Enter键移动到下一行同一列  
            if (e.key === 'Enter' && (document.activeElement.classList.contains('excel-input') || document.activeElement.classList.contains('excel-select')) && !document.activeElement.readOnly && !document.activeElement.disabled) {
                e.preventDefault();
                const currentElement = document.activeElement;
                const field = currentElement.dataset.field;
                
                const currentRow = currentElement.closest('tr');
                const nextRow = currentRow.nextElementSibling;
                
                if (nextRow) {
                    const nextElement = nextRow.querySelector(`input[data-field="${field}"]:not([readonly]), select[data-field="${field}"]:not([disabled])`);
                    if (nextElement) {
                        nextElement.focus();
                    }
                } else {
                    // 如果是最后一行，添加新行并聚焦
                    addNewRow();
                    setTimeout(() => {
                        const newRow = document.querySelector('#excel-tbody tr:last-child');
                        const newElement = newRow.querySelector(`input[data-field="${field}"]:not([readonly]), select[data-field="${field}"]:not([disabled])`);
                        if (newElement) {
                            newElement.focus();
                        }
                    }, 100);
                }
            }
        });

        // 为所有输入框添加focus事件监听
        document.addEventListener('focus', function(e) {
            if (e.target.classList.contains('excel-input')) {
                handleInputFocus(e.target, false);
            }
        }, true);

        // 为所有输入框添加click事件监听
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('excel-input')) {
                handleInputFocus(e.target, true);
            }
        });

        // 点击其他地方关闭下拉菜单
        document.addEventListener('click', function(event) {
            const selector = event.target.closest('.selector-button');
            const dropdown = event.target.closest('.selector-dropdown');
            const dropdownItem = event.target.closest('.dropdown-item');
            
            // 如果点击的是下拉选项，立即隐藏对应的下拉菜单
            if (dropdownItem) {
                const parentDropdown = dropdownItem.closest('.selector-dropdown');
                if (parentDropdown) {
                    parentDropdown.classList.remove('show');
                }
                return;
            }
            
            // 如果点击的不是选择器按钮或下拉菜单，隐藏所有下拉菜单
            if (!selector && !dropdown) {
                document.getElementById('view-selector-dropdown')?.classList.remove('show');
                document.getElementById('system-selector-dropdown')?.classList.remove('show');
            }
            
            // 关闭多选下拉框（如果点击的不是多选组件内部）
            const multiSelectTrigger = event.target.closest('.multiselect-trigger');
            const multiSelectDropdown = event.target.closest('.multiselect-dropdown');
            
            if (!multiSelectTrigger && !multiSelectDropdown) {
                document.querySelectorAll('.multiselect-dropdown.show').forEach(dd => {
                    dd.classList.remove('show');
                });
            }
        });

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);
    </script>
    <script>
        // 批准记录
        async function approveRecord(rowId) {
            if (!userCanApprove) {
                showAlert('您没有权限执行此操作', 'error');
                return;
            }

            if (!confirm('确定要批准这条记录吗？')) {
                return;
            }
            
            const approveBtn = document.querySelector(`button[onclick="approveRecord('${rowId}')"]`);
            const originalText = approveBtn.innerHTML;
            approveBtn.innerHTML = '<div class="loading"></div> 批准中...';
            approveBtn.disabled = true;
            
            try {
                const response = await fetch(`${API_BASE_URL}?action=approve`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: rowId
                    })
                });
                
                const responseText = await response.text();
                console.log('批准响应:', responseText);
                const result = JSON.parse(responseText);
                
                if (result.success) {
                    // 更新界面
                    const row = approveBtn.closest('tr');
                    const approverCell = approveBtn.closest('td');
                    
                    // 更新批准状态列
                    approverCell.innerHTML = `
                        <span style="color: #065f46; font-weight: 600;">已批准</span>
                    `;
                    
                    // 更新行样式
                    row.classList.remove('status-pending');
                    row.classList.add('status-approved');
                    
                    updateStats();
                    showAlert('记录已批准', 'success');
                    
                } else {
                    throw new Error(result.message || '批准失败');
                }
                
            } catch (error) {
                console.error('批准失败:', error);
                showAlert('批准失败: ' + error.message, 'error');
                approveBtn.innerHTML = originalText;
                approveBtn.disabled = false;
            }
        }
    </script>
    <script>
        // 切换编辑模式
        function toggleEdit(rowId) {
            const editBtn = document.getElementById(`edit-btn-${rowId}`);
            if (!editBtn) {
                console.error(`找不到编辑按钮: edit-btn-${rowId}`);
                return;
            }
            
            const isEditing = editBtn.classList.contains('save-mode');
            
            if (isEditing) {
                // 保存模式 - 保存这一行
                saveSingleRowData(rowId);
            } else {
                // 切换到编辑模式
                setRowReadonly(rowId, false);
                
                editBtn.classList.add('save-mode');
                editBtn.innerHTML = '<i class="fas fa-save"></i>';
                editBtn.title = '保存记录';
            }
        }

        // 设置行的只读状态
        function setRowReadonly(rowId, readonly) {
            const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr') || 
                        document.querySelector(`div[data-row="${rowId}"]`)?.closest('tr');
            
            if (!row) {
                console.error(`找不到行: ${rowId}`);
                return;
            }
            
            if (currentSystem === 'overview') {
                // 总览页面的编辑规则
                // 处理输入框 - 在总览页面，所有输入框始终保持只读
                const inputs = row.querySelectorAll(`input[data-row="${rowId}"]`);
                inputs.forEach(input => {
                    // 跳过多选框中的checkbox
                    if (input.type === 'checkbox') return;
                    
                    // 在总览页面，所有输入框始终保持只读（不能编辑）
                    input.classList.add('readonly');
                    input.setAttribute('readonly', 'readonly');
                    input.setAttribute('disabled', 'disabled');
                });
                
                // 处理下拉选择框（保持其他select为只读）
                const selects = row.querySelectorAll(`select[data-row="${rowId}"]`);
                selects.forEach(select => {
                    select.classList.add('readonly');
                    select.setAttribute('disabled', 'disabled');
                });

                // 处理多选系统分配组件 - 在总览页面可编辑
                const multiSelectWrapper = row.querySelector(`div[data-field="system_assign"][data-row="${rowId}"]`);
                if (multiSelectWrapper) {
                    const trigger = multiSelectWrapper.querySelector('.multiselect-trigger');
                    const checkboxes = multiSelectWrapper.querySelectorAll('input[type="checkbox"]');
                    
                    if (readonly) {
                        // 只读模式：禁用系统分配
                        trigger.classList.add('readonly');
                        trigger.removeAttribute('onclick');
                        checkboxes.forEach(cb => cb.setAttribute('disabled', 'disabled'));
                    } else {
                        // 编辑模式：只有系统分配可以编辑
                        trigger.classList.remove('readonly');
                        trigger.setAttribute('onclick', `toggleMultiSelect('${rowId}', event)`);
                        checkboxes.forEach(cb => cb.removeAttribute('disabled'));
                    }
                }

                // 处理多选冰箱分类组件 - 在总览页面可编辑
                const freezerWrapper = row.querySelector(`div[data-field="freezer_category"][data-row="${rowId}"]`);
                if (freezerWrapper) {
                    const trigger = freezerWrapper.querySelector('.multiselect-trigger');
                    const checkboxes = freezerWrapper.querySelectorAll('input[type="checkbox"]');
                    if (readonly) {
                        trigger.classList.add('readonly');
                        trigger.removeAttribute('onclick');
                        checkboxes.forEach(cb => cb.setAttribute('disabled', 'disabled'));
                    } else {
                        trigger.classList.remove('readonly');
                        trigger.setAttribute('onclick', `toggleMultiSelectFreezer('${rowId}', event)`);
                        checkboxes.forEach(cb => cb.removeAttribute('disabled'));
                    }
                }
            } else {
                // 系统页面的编辑规则
                // 处理输入框
                const inputs = row.querySelectorAll(`input[data-row="${rowId}"]`);
                inputs.forEach(input => {
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
                
                // 处理下拉选择框
                const selects = row.querySelectorAll(`select[data-row="${rowId}"]`);
                selects.forEach(select => {
                    // 系统分配字段始终保持只读，不允许编辑
                    if (select.dataset.field === 'system_assign') {
                        select.classList.add('readonly');
                        select.setAttribute('disabled', 'disabled');
                        return; // 跳过系统分配字段的处理
                    }
                    
                    if (readonly) {
                        select.classList.add('readonly');
                        select.setAttribute('disabled', 'disabled');
                    } else {
                        select.classList.remove('readonly');
                        select.removeAttribute('disabled');
                    }
                });

                // 处理多选冰箱分类组件（系统页面可编辑）
                const freezerWrapper = row.querySelector(`div[data-field="freezer_category"][data-row="${rowId}"]`);
                if (freezerWrapper) {
                    const trigger = freezerWrapper.querySelector('.multiselect-trigger');
                    const checkboxes = freezerWrapper.querySelectorAll('input[type="checkbox"]');
                    if (readonly) {
                        trigger.classList.add('readonly');
                        trigger.removeAttribute('onclick');
                        checkboxes.forEach(cb => cb.setAttribute('disabled', 'disabled'));
                    } else {
                        trigger.classList.remove('readonly');
                        trigger.setAttribute('onclick', `toggleMultiSelectFreezer('${rowId}', event)`);
                        checkboxes.forEach(cb => cb.removeAttribute('disabled'));
                    }
                }
            }
            
            // 切换行的编辑样式
            if (readonly) {
                row.classList.remove('editing-row');
            } else {
                row.classList.add('editing-row');
            }
        }

        // 保存单行数据
        async function saveSingleRowData(rowId) {
            const editBtn = document.getElementById(`edit-btn-${rowId}`);
            if (!editBtn) {
                console.error(`找不到编辑按钮: edit-btn-${rowId}`);
                return;
            }
            
            const originalHTML = editBtn.innerHTML;
            editBtn.innerHTML = '<div class="loading"></div>';
            editBtn.disabled = true;
            
            try {
                const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr');
                if (!row) {
                    throw new Error('找不到对应的行');
                }
                
                const rowData = extractRowData(row);
                console.log('提取的行数据:', rowData);

                // 验证必填字段（只对新记录进行严格验证）
                const isNewRecord = rowId.toString().startsWith('new-');

                if (isNewRecord) {
                    // 新记录必须填写所有必填字段
                    if (!rowData.product_code || !rowData.product_name || !rowData.specification || 
                        !rowData.category || !rowData.supplier || !rowData.applicant) {
                        throw new Error('请填写所有必填字段');
                    }
                } else {
                    // 现有记录允许部分字段为空，但至少要有货品编号或货品名称
                    if (!rowData.product_code && !rowData.product_name) {
                        throw new Error('货品编号和货品名称至少需要填写一个');
                    }
                }

                let result;

                // 如果是编辑现有记录，根据页面类型处理批准状态
                if (!isNewRecord) {
                    if (currentSystem === 'overview') {
                        // 总览页面：保持原有的批准状态
                        const originalRecord = stockData.find(item => item.id == rowId);
                        if (originalRecord && originalRecord.approver) {
                            rowData.approver = originalRecord.approver;
                        }
                    } else {
                        // 系统页面：清除批准状态，需要重新批准
                        rowData.approver = '';
                    }
                }
                
                if (isNewRecord) {
                    // 新记录
                    const response = await fetch(API_BASE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(rowData)
                    });
                    const responseText = await response.text();
                    console.log('POST响应:', responseText);
                    result = JSON.parse(responseText);
                    
                    if (result.success && result.data && result.data.id) {
                        // 更新行ID和相关元素
                        const newId = result.data.id;
                        updateRowIdComplete(row, rowId, newId);
                        // 更新当前使用的rowId变量
                        rowId = newId;
                    }
                } else {
                    // 更新现有记录
                    rowData.id = rowId;
                    const response = await fetch(API_BASE_URL, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(rowData)
                    });
                    const responseText = await response.text();
                    console.log('PUT响应:', responseText);
                    result = JSON.parse(responseText);
                }
                
                if (result.success) {
                    // 根据页面类型显示不同的提示信息
                    if (currentSystem === 'overview') {
                        showAlert('记录保存成功', 'success');
                    } else if (!isNewRecord) {
                        showAlert('记录保存成功，需要在总览页面重新批准', 'success');
                        
                        // 系统页面：如果是编辑现有记录，更新状态列显示为"待批准"
                        const row = document.querySelector(`input[data-row="${rowId}"]`)?.closest('tr');
                        if (row) {
                            const statusCell = row.querySelector('td:nth-child(9)');
                            if (statusCell) {
                                statusCell.innerHTML = '<span style="color: #92400e; font-weight: 600;">待批准</span>';
                            }
                            // 更新行样式
                            row.classList.remove('status-approved');
                            row.classList.add('status-pending');
                        }
                    } else {
                        showAlert('记录保存成功', 'success');
                    }
                    
                    // 切换回只读模式
                    setRowReadonly(rowId, true);
                    
                    // 更新编辑按钮状态
                    const currentEditBtn = document.getElementById(`edit-btn-${rowId}`);
                    if (currentEditBtn) {
                        currentEditBtn.classList.remove('save-mode');
                        currentEditBtn.innerHTML = '<i class="fas fa-edit"></i>';
                        currentEditBtn.title = '编辑记录';
                        currentEditBtn.disabled = false;
                    }
                    
                    updateStats();
                } else {
                    throw new Error(result.message || '保存失败');
                }
                
            } catch (error) {
                console.error('保存数据失败:', error);
                showAlert('保存失败: ' + error.message, 'error');
                
                // 恢复按钮状态
                editBtn.innerHTML = originalHTML;
                editBtn.disabled = false;
            }
        }

        // 完整更新行ID（修复版本）
        function updateRowIdComplete(row, oldId, newId) {
            console.log(`更新行ID: ${oldId} -> ${newId}`);
            
            // 更新所有input的data-row属性
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.dataset.row === oldId) {
                    input.dataset.row = newId;
                }
            });
            
            // 更新所有select的data-row属性
            const selects = row.querySelectorAll('select');
            selects.forEach(select => {
                if (select.dataset.row === oldId) {
                    select.dataset.row = newId;
                }
            });
            
            // 更新编辑按钮的ID和事件
            const editBtn = row.querySelector(`#edit-btn-${oldId}`);
            if (editBtn) {
                editBtn.id = `edit-btn-${newId}`;
                editBtn.setAttribute('onclick', `toggleEdit('${newId}')`);
            }
            
            // 更新删除按钮的事件
            const deleteBtn = row.querySelector('.delete-row-btn');
            if (deleteBtn) {
                deleteBtn.setAttribute('onclick', `deleteRow('${newId}')`);
            }
            
            // 更新批准按钮的事件（如果存在）
            const approveBtn = row.querySelector(`button[onclick*="approveRecord('${oldId}')"]`);
            if (approveBtn) {
                approveBtn.setAttribute('onclick', `approveRecord('${newId}')`);
            }
            
            // 移除新行样式
            row.classList.remove('new-row');
            
            const recordIndex = stockData.findIndex(item => item.id == oldId || (typeof item.id === 'undefined' && oldId.toString().startsWith('new-')));
            if (recordIndex === -1) {
                // 如果是新记录，添加到stockData中
                const rowData = extractRowData(row);
                rowData.id = newId;
                stockData.push(rowData);
            } else {
                // 更新现有记录的ID
                stockData[recordIndex].id = newId;
            }
            
            console.log(`行ID更新完成: ${oldId} -> ${newId}`);
        }
</script>
</body>
<script>
// 页面权限检查已在 applyPagePermissions 函数中处理，这里不需要重复代码
</script>
</html>