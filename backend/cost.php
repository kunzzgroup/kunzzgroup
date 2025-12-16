<?php
session_start();

// 超时时间（秒）
define('SESSION_TIMEOUT', 60);

// 如果 session 存在，检查是否过期
if (isset($_SESSION['user_id'])) {

    // 如果超过 1 分钟没活动，并且没有记住我
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) &&
        (!isset($_COOKIE['remember_token']) || $_COOKIE['remember_token'] !== '1')
    ) {
        // 清除 session
        session_unset();
        session_destroy();

        // 清除 cookie（可选）
        setcookie('user_id', '', time() - 60, "/");
        setcookie('username', '', time() - 60, "/");
        setcookie('position', '', time() - 60, "/");
        setcookie('remember_token', '', time() - 60, "/");

        // 跳转登录页
        header("Location: index.php");
        exit();
    }

    // 更新活动时间戳
    $_SESSION['last_activity'] = time();

} elseif (
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    // 记住我逻辑（恢复 session）
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['last_activity'] = time();
} else {
    // 没有 session，也没有有效 cookie
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
// 修改这行：检查position是否为空或null
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';
$avatarLetter = strtoupper($username[0]);
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅成本管理系统</title>    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            color: #000000;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
        }

        /* 主内容区域样式 */
        .main-content {
            margin-left: 300px; /* 默认为侧边栏宽度 */
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            position: relative;
            overflow: visible;
        }

        /* 当侧边栏收起时，主内容区域扩展 */
        .main-content.sidebar-collapsed {
            margin-left: 60px; /* 收起后的侧边栏宽度 */
        }

        html {
            height: 100%;
            overflow-x: hidden;
            overflow-y: auto; 
        }

        /* 自定义滚动条样式 */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Firefox 滚动条样式 */
        html {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(16px, 1.67vw, 32px);
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
        
        .btn {
            padding: clamp(5px, 0.42vw, 8px) clamp(10px, 0.83vw, 16px);
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
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
        }
        
        .btn-secondary {
            background-color: #f99e00;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #f98500ff;
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
        
        .back-button:active {
            transform: translateY(0);
        }

        /* 餐厅选择器样式 */
        .restaurant-selector {
            position: relative;
            display: inline-block;
        }

        /* 报表类型选择器推到右边 */
        .report-type-selector {
            margin-left: auto;
        }

        /* 餐厅选择器紧跟在报表类型选择器后面 */
        .report-type-selector + .restaurant-selector {
            margin-left: clamp(-20px, -1vw, -10px); /* 使用负边距抵消gap，让它紧挨着报表选择器 */
        }

        .restaurant-btn {
            padding: clamp(6px, 0.52vw, 10px) clamp(14px, 1.04vw, 20px);
            border-radius: 8px;
            border: 2px solid #583e04 !important;
            cursor: pointer;
            font-weight: 600;
            background: white;
            color: #000000ff;
            position: relative;
            width: clamp(60px, 5.21vw, 100px);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border-color: #000000ff !important;
        }

        .restaurant-btn:hover {
            background: rgba(88, 62, 4, 0.1);
        }

        .restaurant-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.15);
            z-index: 1000;
            width: clamp(240px, 25vw, 320px);
            min-width: 200px;
            padding: clamp(6px, 0.63vw, 12px) clamp(8px, 1vw, 16px);
        }

        .restaurant-dropdown-menu.show {
            display: flex;
            gap: 16px;
            flex-direction: row;
        }

        .letter-selection,
        .number-selection {
            flex: 1;
        }

        .letter-selection {
            flex: 1;
            border-right: 1px solid #e5e7eb;
            padding-right: 12px;
            min-width: 110px;
        }

        .number-selection {
            flex: 1;
            padding-left: 0px;
            min-width: 90px;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .section-title {
            font-size: clamp(10px, 0.9vw, 14px);
            font-weight: 600;
            color: #000000ff;
            margin-bottom: clamp(4px, 0.6vw, 8px);
            text-align: center;
        }

        .letter-grid,
        .number-grid {
            display: grid;
            gap: 4px;
        }       

        .letter-grid {
            display: flex;
            flex-direction: row;
            gap: clamp(4px, 0.8vw, 8px);
            align-items: center;
            justify-content: center;
        }

        .letter-item,
        .number-item,
        .number-item.total-option {
            display: flex;
            align-items: center;
            justify-content: center;
            width: clamp(12px, 1.25vw, 24px);
            height: clamp(12px, 1.25vw, 24px);
            border: 1px solid #e5e7eb;
            background: white;
            color: #000000ff;
            cursor: pointer;
            font-size: clamp(7px, 0.63vw, 12px);
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.15s ease;
        }

        .letter-item:hover,
        .number-item:hover,
        .number-item.total-option:hover {
            background-color: #f3f4f6;
            color: black;
            border-color: #d1d5db;
        }

        .letter-item.selected,
        .number-item.selected,
        .number-item.total-option.selected {
            background: #f99e00;
            color: white;
            border-color: #f99e00;
            font-weight: 600;
        }

        /* 数字选择区域显示状态 */
        .number-selection.show {
            visibility: visible;
            opacity: 1;
        }

        .number-dropdown {
            position: relative;
            display: inline-block;
        }

        .number-btn {
            padding: 10px 16px;
            border-radius: 0 8px 8px 0;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
            color: #583e04;
            min-width: 60px;
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
            top: 120%;
            left: 0;
            background: white;
            border: 2px solid #583e04;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.15);
            z-index: 1000;
            padding: 4px;
            min-width: 120px;
        }

        .number-dropdown-menu.show {
            display: block;
        }

        .number-grid {
            margin-left: 10px;
            display: grid;
            grid-template-columns: repeat(4, 0.2fr);
            gap: 2px;
        }

        .number-item:hover {
            background-color: #f3f4f6;
            color: black;
            border-color: #d1d5db;
        }

        .number-item.selected {
            background: #f99e00;
            color: white;
            border-color: #f99e00;
            font-weight: 600;
        }

        .restaurant-btn.active {
            background: #583e04;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.25);
        }

        .restaurant-btn:hover:not(.active) {
            background: rgba(88, 62, 4, 0.1);
            transform: translateY(-1px);
        }

        .restaurant-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 8px;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .restaurant-btn.active::before {
            opacity: 1;
        }

        /* 总计按钮特殊样式 - 未激活时透明 */
        .restaurant-btn[data-restaurant="total"] {
            font-weight: 700;
        }

        /* 总计按钮默认状态（未激活） - 透明背景 */
        .restaurant-btn[data-restaurant="total"]:not(.active) {
            background: transparent !important;
            color: #583e04 !important;
            transform: none !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        /* 总计按钮激活状态 */
        .restaurant-btn[data-restaurant="total"].active {
            background: linear-gradient(135deg, #583e04, #805906) !important;
            color: white !important;
            box-shadow: 0 4px 16px rgba(88, 62, 4, 0.4) !important;
            transform: translateY(-2px) !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
        }

        /* 总计按钮悬停状态（仅在非激活时） */
        .restaurant-btn[data-restaurant="total"]:not(.active):hover {
            background: rgba(88, 62, 4, 0.1) !important;
            color: #583e04 !important;
            transform: translateY(-1px) !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }    
        
        /* 下拉菜单样式 */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            font-size: clamp(8px, 0.74vw, 14px);
            gap: 8px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.15);
            z-index: 1000;
            width: 100%;
            box-sizing: border-box;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: clamp(6px, 0.52vw, 10px) clamp(10px, 1.04vw, 20px);
            border: none;
            background: transparent;
            color: #000000ff;
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            text-align: left;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: rgba(88, 62, 4, 0.1);
        }

        .dropdown-item:first-child {
            border-radius: 6px 6px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 6px 6px;
        }

        .card {
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        
        .card-body {
            padding: clamp(5.5px, 0.7vw, 13.5px) clamp(14px, 1.25vw, 24px);
        }
        
        .grid {
            display: grid;
            gap: 24px;
        }
        
        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }
        
        /* 成本指标网格 - 默认6列 */
        .cost-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-bottom: clamp(14px, 1.67vw, 32px);
        }

        /* J1餐厅时显示8个卡片 - 上4下4 */
        .cost-grid.j1-mode {
            grid-template-columns: repeat(4, 1fr);
        }

        /* J2和J3供应卡片默认隐藏 */
        .supply-card {
            display: none;
        }

        /* J1模式下显示供应卡片 */
        .cost-grid.j1-mode .supply-card {
            display: block;
        }

        /* 图表容器 - 改为全宽 */
        .main-chart-container {
            display: flex;
            flex-direction: column;
            margin-bottom: clamp(16px, 1.67vw, 32px);
        }
        
        /* 下方图表网格 */
        .bottom-charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }      
        
        .cost-card {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cost-card .icon {
            width: 40px;
            height: 40px;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .cost-card-vertical {
            display: flex;
            flex-direction: column;
            align-items: left;
            text-align: left;
            gap: 0px;
        }

        .cost-card-vertical .icon {
            width: 50px;
            height: 50px;
            font-size: clamp(20px, 1.5vw, 28px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-bottom: clamp(0px, 0.21vw, 4px);
        }

        .cost-card-vertical .cost-label {
            font-size: clamp(10px, 0.84vw, 16px);
            color: #000000;
            font-weight: bold;
            margin-bottom: 0px;
        }

        .cost-card-vertical .cost-value {
            font-size: clamp(16px, 1.25vw, 24px);
            font-weight: bold;
            color: #111827;
        }

        .cost-value {
            font-size: 1.75rem;
            font-weight: bold;
            color: #111827;
            margin-bottom: 2px;
        }
        
        .cost-label {
            font-size: 15px;
            color: #000000;
            font-weight: bold;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            font-size: clamp(8px, 0.74vw, 14px);
            padding: 12px clamp(6px, 1vw, 24px);
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table th {
            background-color: rgba(255, 255, 255, 1);
            font-weight: bold;
            font-size: clamp(8px, 0.74vw, 14px);
            color: #000000ff;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .form-label {
            display: block;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s;
            font-family: "Segoe UI", sans-serif;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* 改进的日期选择器样式 */
        .enhanced-date-picker {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: clamp(2px, 0.31vw, 6px) clamp(0px, 0.21vw, 4px);
            gap: 0px;
            min-width: 100px;
            transition: all 0.2s;
            position: relative;
        }

        .enhanced-date-picker:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .enhanced-date-picker:hover {
            border-color: #9ca3af;
        }

        /* 月份选择器的特殊样式 - 更小的宽度 */
        .enhanced-date-picker.month-only {
            min-width: clamp(80px, 6.77vw, 130px);
        }

        /* 日期选择部分 */
        .date-part {
            position: relative;
            cursor: pointer;
            padding: 0px clamp(2px, 0.42vw, 8px);
            border-radius: 4px;
            transition: all 0.2s;
            text-align: center;
            user-select: none;
            background: transparent;
            border: 1px solid transparent;
            font-size: clamp(8px, 0.74vw, 14px);
            color: #374151;
        }

        .date-part:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .date-part.active {
            background-color: #f99e00;
            color: white;
            border-color: #f99e00;
        }

        .date-separator {
            color: #9ca3af;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            user-select: none;
            margin: 0 2px;
        }

        /* 下拉选择面板 */
        .date-dropdown {
            position: absolute;
            top: 120%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            margin-top: 4px;
            max-height: 220px;
            overflow-y: auto;
            display: none;
        }

        .date-dropdown.show {
            display: block;
            animation: dropdownFadeIn 0.2s ease-out;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 年份选择网格 */
        .year-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(0px, 0.21vw, 4px);
            padding: clamp(2px, 0.36vw, 8px);
        }

        /* 月份选择网格 */
        .month-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(0px, 0.21vw, 4px);
            padding: clamp(4px, 0.42vw, 8px);
        }

        /* 日期选择网格 */
        .day-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0px;
            padding: 2px;
        }

        /* 选择项通用样式 */
        .date-option {
            padding: clamp(1px, 0.1vw, 2px);
            text-align: center;
            cursor: pointer;
            border-radius: clamp(4px, 0.31vw, 6px);
            transition: all 0.2s;
            font-size: clamp(6px, 0.63vw, 12px);
            color: #374151;
            background: transparent;
            border: 1px solid transparent;
        }

        .date-option:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .date-option.selected {
            background-color: #f99e00;
            color: white;
            border-color: #f99e00;
        }

        .date-option.today.selected {
            background-color: #f99e00;
            color: white;
            border-color: #f99e00;
        }

        /* 日期网格的星期标题 */
        .day-header {
            padding: clamp(2px, 0.21vw, 4px);
            text-align: center;
            font-size: clamp(6px, 0.63vw, 12px);
            color: #6b7280;
            font-weight: 600;
        }

        /* 日期控制区域 */
        .date-controls {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(10px, 1.5vw, 30px);
            align-items: center;
        }

        /* 日期信息样式 */
        .date-info {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: bold;
            color: #6b7280;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            background: rgba(255, 255, 255, 1);
            border-radius: 6px;
        }

        /* 分隔线 */
        .divider {
            width: 1px;
            height: 24px;
            background-color: #583e04 !important;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .enhanced-date-picker {
                min-width: auto;
                width: 100%;
            }
            
            .enhanced-date-picker.month-only {
                min-width: auto;
                width: 100%;
            }
            
            .date-controls {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* 渐变背景样式 */
        .mountain-gradient {
            background: linear-gradient(180deg, 
                rgba(88, 62, 4, 0.3) 0%, 
                rgba(88, 62, 4, 0.1) 50%, 
                rgba(88, 62, 4, 0.05) 100%);
        }
        
        .text-green {
            color: #000000ff;
        }
        
        .text-blue {
            color: #583e04;
        }
        
        .text-purple {
            color: #583e04;
        }
        
        .text-red {
            color: #583e04;
        }
        
        .text-orange {
            color: #583e04;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 12px 16px;
            margin-bottom: 16px;
            border-radius: 8px;
            font-size: 14px;
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

        /* 餐厅特定的颜色主题 */
        .restaurant-j1 {
            --primary-color: #583e04;
            --secondary-color: #805906;
        }

        .restaurant-j2 {
            --primary-color: #583e04;
            --secondary-color: #805906;
        }

        .restaurant-j3 {
            --primary-color: #583e04;
            --secondary-color: #805906;
        }

        .restaurant-total {
            --primary-color: #583e04;
            --secondary-color: #805906;
        }

        /* 动态应用颜色 */
        .dynamic-color {
            color: #000000 !important;
        }

        .dynamic-bg {
            background-color: var(--primary-color) !important;
        }

        .dynamic-border {
            border-color: var(--primary-color) !important;
        }

        .chart-back-button {
            position: absolute;
            top: -50px;
            right: 250px;
            background: #583e04;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            z-index: 10;
            display: none;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-back-button:hover {
            background: #6b4a05;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .chart-back-button i {
            font-size: 10px;
        }

        .chart-container {
            position: relative;
        }

        .date-range-display {
            margin-top: 10px;
            background: #f9fafb;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .chart-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 8px;
            }
            
            .date-range-display {
                font-size: 12px;
            }
        }

        /* 2. 添加按钮样式CSS */
        .chart-data-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .chart-data-btn {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border: 1px solid #d1d5db;
            background: white;
            color: #6b7280;
            border-radius: 6px;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .chart-data-btn:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #4b5563;
        }

        .chart-data-btn.active {
            background: #f99e00;
            color: white;
            border-color: #f99e00;
        }

        .chart-data-btn.active:hover {
            background: #f98500ff;
            border-color: #f98500ff;
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

        .report-dropdown-menu {
            position: absolute;
            top: 96%;
            left: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 8px;
            width: clamp(116px, 8.3vw, 160px);
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.2);
            z-index: 1000;
            display: none;
        }

        .report-dropdown-menu.show {
            display: block;
        }

        .report-dropdown-item {
            padding: clamp(6px, 0.52vw, 10px) clamp(14px, 0.83vw, 16px);
            color: #583e04;
            text-decoration: none;
            display: block;
            font-size: clamp(10px, 0.73vw, 14px);
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
<body class="restaurant-j1">
    <?php include 'sidebar.php'; ?>
        <div class="container">
            <div class="header">
                <div>
                    <h1>成本分析仪表盘</h1>
                </div>
            </div>
            
            <!-- 日期信息显示 -->
            <div class="date-info" id="date-info" style="margin-bottom: 16px; border: 1px solid #e5e7eb;">
                正在加载数据...
            </div>
            <div id="app">         
            <!-- Date Controls -->
            <div class="card" style="margin-bottom: clamp(14px, 1.67vw, 32px);">
                <div class="card-body">
                    <div class="date-controls">
    
                        <!-- 开始日期选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0;">开始日期</label>
                            <div class="enhanced-date-picker" id="start-date-picker">
                                <div class="date-part" data-type="year" onclick="showDateDropdown('start', 'year')">
                                    <span id="start-year-display">2024</span>
                                </div>
                                <span class="date-separator">年</span>
                                <div class="date-part" data-type="month" onclick="showDateDropdown('start', 'month')">
                                    <span id="start-month-display">01</span>
                                </div>
                                <span class="date-separator">月</span>
                                <div class="date-part" data-type="day" onclick="showDateDropdown('start', 'day')">
                                    <span id="start-day-display">01</span>
                                </div>
                                <span class="date-separator">日</span>
            
                                <div class="date-dropdown" id="start-dropdown">
                                </div>
                            </div>
                        </div>
    
                        <!-- 结束日期选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0;">结束日期</label>
                            <div class="enhanced-date-picker" id="end-date-picker">
                                <div class="date-part" data-type="year" onclick="showDateDropdown('end', 'year')">
                                    <span id="end-year-display">2024</span>
                                </div>
                                <span class="date-separator">年</span>
                                <div class="date-part" data-type="month" onclick="showDateDropdown('end', 'month')">
                                    <span id="end-month-display">01</span>
                                </div>
                                <span class="date-separator">月</span>
                                <div class="date-part" data-type="day" onclick="showDateDropdown('end', 'day')">
                                    <span id="end-day-display">01</span>
                                </div>
                                <span class="date-separator">日</span>
            
                                <div class="date-dropdown" id="end-dropdown">
                                </div>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- 月份选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-calendar" style="color: #000000ff;"></i>
                                选择年份和月份
                            </label>
                            <div class="enhanced-date-picker month-only" id="month-date-picker">
                                <div class="date-part" data-type="year" onclick="showDateDropdown('month', 'year')">
                                    <span id="month-year-display">2024</span>
                                </div>
                                <span class="date-separator">年</span>
                                <div class="date-part" data-type="month" onclick="showDateDropdown('month', 'month')">
                                    <span id="month-month-display">01</span>
                                </div>
                                <span class="date-separator">月</span>
            
                                <div class="date-dropdown" id="month-dropdown">
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-clock" style="color: #000000ff;"></i>
                                快速选择
                            </label>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" onclick="toggleQuickSelectDropdown()">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span id="quick-select-text">时段</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-menu" id="quick-select-dropdown">
                                    <button class="dropdown-item" onclick="selectQuickRange('today')">今天</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('yesterday')">昨天</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('thisWeek')">本周</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('lastWeek')">上周</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('thisMonth')">这个月</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('lastMonth')">上个月</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('thisYear')">今年</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('lastYear')">去年</button>
                                </div>
                            </div>
                        </div>

                        <!-- 报表类型选择器 -->
                        <div class="report-type-selector" onclick="toggleReportTypeDropdown()">
                            <button class="report-type-btn">
                                <i class="fas fa-chart-pie"></i>
                                成本报表
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="report-dropdown-menu" id="report-type-dropdown">
                                <a href="kpi.php" class="report-dropdown-item">
                                    <i class="fas fa-chart-line"></i> KPI 报表
                                </a>
                                <a href="cost.php" class="report-dropdown-item">
                                    <i class="fas fa-chart-pie"></i> 成本报表
                                </a>
                            </div>
                        </div>

                        <!-- 餐厅选择器 -->
                        <div class="restaurant-selector">
                            <button class="restaurant-btn dropdown-toggle" onclick="toggleRestaurantDropdown()">
                                -- <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="restaurant-dropdown-menu" id="restaurant-dropdown">
                                <div class="letter-selection">
                                    <div class="section-title">选择州属</div>
                                    <div class="letter-grid">
                                        <button class="letter-item" onclick="selectLetter('J')">J</button>
                                    </div>
                                </div>
                                <div class="number-selection" id="number-selection">
                                    <div class="section-title">选择餐厅</div>
                                    <div class="number-grid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 成本指标卡片 - 单行6列（J1时为4x2） -->
                <div class="cost-grid" id="cost-grid">
                    <!-- 销售额 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon text-green">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div>
                                    <p class="cost-label">销售额 (RM)</p>
                                    <p class="cost-value" id="total-sales">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 总成本 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon text-green">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div>
                                    <p class="cost-label">总成本 (RM)</p>
                                    <p class="cost-value" id="total-cost">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 毛利润 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <p class="cost-label">毛利润 (RM)</p>
                                    <p class="cost-value" id="gross-total">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 成本百分比 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div>
                                    <p class="cost-label">成本率</p>
                                    <p class="cost-value" id="cost-percent">0%</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 库存（最后） -->
                    <div class="card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <p class="cost-label">库存（最后）</p>
                                    <p class="cost-value" id="last-stock">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 库存（现在） -->
                    <div class="card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div>
                                    <p class="cost-label">库存（现在）</p>
                                    <p class="cost-value" id="current-stock">0</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 供应→J2 (仅J1餐厅时显示) -->
                    <div class="card supply-card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div>
                                    <p class="cost-label">供应→J2 (RM)</p>
                                    <p class="cost-value" id="j2-supply">0</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 供应→J3 (仅J1餐厅时显示) -->
                    <div class="card supply-card">
                        <div class="card-body">
                            <div class="cost-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div>
                                    <p class="cost-label">供应→J3 (RM)</p>
                                    <p class="cost-value" id="j3-supply">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Chart - 全宽显示 -->
                <div class="main-chart-container">
                    <div class="card" style="height: 400px;">
                        <div class="card-body" style="height: 100%; display: flex; flex-direction: column;">
                            <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h3 id="main-chart-title" style="font-size: clamp(14px, 1.04vw, 20px); font-weight: 600; color: #111827; margin: 0;">成本趋势分析</h3>
                                
                                <!-- 数据类型切换按钮组 -->
                                <div class="chart-data-buttons" style="display: flex; gap: 8px; align-items: center;">
                                    <button class="chart-data-btn active" data-type="totalCost" onclick="switchChartData('totalCost')">
                                        总成本
                                    </button>
                                    <button class="chart-data-btn" data-type="grossTotal" onclick="switchChartData('grossTotal')">
                                        毛利润
                                    </button>
                                    <button class="chart-data-btn" data-type="costPercent" onclick="switchChartData('costPercent')">
                                        成本率
                                    </button>
                                </div>
                                
                                <div class="date-range-display" id="chart-date-range" style="font-size: clamp(8px, 0.74vw, 14px); color: #6b7280; font-weight: 500;">
                                </div>
                            </div>
                            <div class="chart-container" style="flex: 1;">
                                <button class="chart-back-button" id="cost-chart-back" onclick="exitDrillDown()">
                                    <i class="fas fa-arrow-left"></i> 返回年度视图
                                </button>
                                <canvas id="cost-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                        
            <!-- Detail Table -->
            <div class="card">
                <div class="card-body" style="padding-bottom: 0;">
                    <h3 style="font-size: clamp(14px, 1.04vw, 20px); font-weight: 600; color: #111827; margin-bottom: 24px;">详细数据</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table" id="dashboard-table">
                        <thead>
                            <tr id="table-header">
                                <th>日期</th>
                                <th>销售额</th>
                                <th>饮料成本</th>
                                <th>厨房成本</th>
                                <th>总成本</th>
                                <th>毛利润</th>
                                <th>成本率 (%)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        // API 配置
        const API_BASE_URL = 'costapi.php';
        
        // 应用状态
        let actualData = [];
        let allRestaurantsData = {};
        let currentRestaurant = null;
        let dateRange = {
            startDate: null,
            endDate: null
        };
        let currentChartDataType = 'totalCost';
        let costChart = null;
        
        // 日期选择器状态
        let currentDatePicker = null;
        let currentDateType = null;
        let startDateValue = { year: null, month: null, day: null };
        let endDateValue = { year: null, month: null, day: null };
        let monthDateValue = { year: null, month: null };

        // 钻取状态管理
        let isDrillDownMode = false;
        let originalDateRange = null;
        let drillDownMonth = null;
        
        // 餐厅配置
        const restaurantConfig = {
            j1: {
                name: 'J1',
                tableName: 'j1cost',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            j2: {
                name: 'J2',
                tableName: 'j2cost',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            j3: {
                name: 'J3',
                tableName: 'j3cost',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            total: {
                name: '总',
                tableName: 'all_restaurants',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            }
        };

        // 增强的日期选择器功能
        function initEnhancedDatePickers() {
            // 获取当前日期
            const today = new Date();
            const currentYear = today.getFullYear();
            const currentMonth = today.getMonth() + 1;

            // 计算本月第一天和最后一天
            const firstDayOfMonth = new Date(currentYear, currentMonth - 1, 1);
            const lastDayOfMonth = new Date(currentYear, currentMonth, 0);

            // 正确设置dateRange为当月第一天和最后一天
            dateRange = {
                startDate: `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`,
                endDate: `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(lastDayOfMonth.getDate()).padStart(2, '0')}`
            };
    
            // 设置开始和结束日期初始值为当月第一天和最后一天
            startDateValue = {
                year: currentYear,
                month: currentMonth,
                day: 1
            };

            endDateValue = {
                year: currentYear,
                month: currentMonth,
                day: lastDayOfMonth.getDate()
            };
    
            // 月份选择器初始值为未选择状态（显示"--"）
            monthDateValue = {
                year: null,
                month: null
            };
    
            // 更新显示
            updateDateDisplay('start');
            updateDateDisplay('end');
            updateDateDisplay('month');
    
            // 绑定全局点击事件以关闭下拉框
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.enhanced-date-picker')) {
                    hideAllDropdowns();
                }
            });
        }

        function updateDateDisplay(prefix) {
            if (prefix === 'month') {
                // 显示年份，如果未选择显示"--"
                document.getElementById('month-year-display').textContent = monthDateValue.year || '--';
                // 显示月份，如果未选择显示"--"
                document.getElementById('month-month-display').textContent = monthDateValue.month ? String(monthDateValue.month).padStart(2, '0') : '--';
            } else {
                const dateValue = prefix === 'start' ? startDateValue : endDateValue;
        
                document.getElementById(`${prefix}-year-display`).textContent = dateValue.year;
                document.getElementById(`${prefix}-month-display`).textContent = String(dateValue.month).padStart(2, '0');
                document.getElementById(`${prefix}-day-display`).textContent = String(dateValue.day).padStart(2, '0');
            }
        }

        function showDateDropdown(prefix, type) {
            hideAllDropdowns();
            
            const dropdown = document.getElementById(`${prefix}-dropdown`);
            const datePicker = document.getElementById(`${prefix}-date-picker`);
            
            currentDatePicker = prefix;
            currentDateType = type;
            
            datePicker.querySelectorAll('.date-part').forEach(part => {
                part.classList.remove('active');
            });
            
            datePicker.querySelector(`[data-type="${type}"]`).classList.add('active');
            
            generateDropdownContent(prefix, type);
            
            dropdown.classList.add('show');
        }

        function hideAllDropdowns() {
            document.querySelectorAll('.date-dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
            
            document.querySelectorAll('.date-part').forEach(part => {
                part.classList.remove('active');
            });
            
            currentDatePicker = null;
            currentDateType = null;
        }

        function generateDropdownContent(prefix, type) {
            const dropdown = document.getElementById(`${prefix}-dropdown`);
            let dateValue;
    
            if (prefix === 'month') {
                dateValue = monthDateValue;
            } else {
                dateValue = prefix === 'start' ? startDateValue : endDateValue;
            }
    
            const today = new Date();
    
            dropdown.innerHTML = '';
    
            if (type === 'year') {
                const yearGrid = document.createElement('div');
                yearGrid.className = 'year-grid';
        
                const currentYear = today.getFullYear();
                const startYear = 2022;
                const endYear = currentYear + 1;
        
                for (let year = startYear; year <= endYear; year++) {
                    const yearOption = document.createElement('div');
                    yearOption.className = 'date-option';
                    yearOption.textContent = year;
            
                    if (year === dateValue.year) {
                        yearOption.classList.add('selected');
                    }
            
                    if (year === currentYear) {
                        yearOption.classList.add('today');
                    }
            
                    yearOption.addEventListener('click', function() {
                        selectDateValue(prefix, 'year', year);
                    });
            
                    yearGrid.appendChild(yearOption);
                }
        
                dropdown.appendChild(yearGrid);
        
            } else if (type === 'month') {
                const monthGrid = document.createElement('div');
                monthGrid.className = 'month-grid';

                const noneOption = document.createElement('div');
                noneOption.className = 'date-option';
                noneOption.textContent = '无';
                noneOption.style.gridColumn = '1 / -1';

                if (!dateValue.month) {
                    noneOption.classList.add('selected');
                }

                noneOption.addEventListener('click', function() {
                    selectDateValue(prefix, 'month', null);
                });

                monthGrid.appendChild(noneOption);

                const months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];

                months.forEach((monthName, index) => {
                    const monthValue = index + 1;
                    const monthOption = document.createElement('div');
                    monthOption.className = 'date-option';
                    monthOption.textContent = monthName;

                    if (monthValue === dateValue.month) {
                        monthOption.classList.add('selected');
                    }

                    if (dateValue.year === today.getFullYear() && monthValue === today.getMonth() + 1) {
                        monthOption.classList.add('today');
                    }

                    monthOption.addEventListener('click', function() {
                        selectDateValue(prefix, 'month', monthValue);
                    });

                    monthGrid.appendChild(monthOption);
                });

                dropdown.appendChild(monthGrid);
        
            } else if (type === 'day') {
                const dayGrid = document.createElement('div');
                dayGrid.className = 'day-grid';
        
                const weekdays = ['日', '一', '二', '三', '四', '五', '六'];
                weekdays.forEach(day => {
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'day-header';
                    dayHeader.textContent = day;
                    dayGrid.appendChild(dayHeader);
                });
        
                const year = dateValue.year;
                const month = dateValue.month;
                const firstDay = new Date(year, month - 1, 1);
                const lastDay = new Date(year, month, 0);
                const daysInMonth = lastDay.getDate();
                const startDayOfWeek = firstDay.getDay();
        
                for (let i = 0; i < startDayOfWeek; i++) {
                    const emptyDay = document.createElement('div');
                    dayGrid.appendChild(emptyDay);
                }
        
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayOption = document.createElement('div');
                    dayOption.className = 'date-option';
                    dayOption.textContent = day;
            
                    if (day === dateValue.day) {
                        dayOption.classList.add('selected');
                    }
            
                    if (year === today.getFullYear() && 
                        month === today.getMonth() + 1 && 
                        day === today.getDate()) {
                        dayOption.classList.add('today');
                    }
            
                    dayOption.addEventListener('click', function() {
                        selectDateValue(prefix, 'day', day);
                    });
            
                    dayGrid.appendChild(dayOption);
                }
        
                dropdown.appendChild(dayGrid);
            }
        }

        function selectDateValue(prefix, type, value) {
            let dateValue;
    
            if (prefix === 'month') {
                dateValue = monthDateValue;
        
                dateValue[type] = value;
                updateDateDisplay('month');
                hideAllDropdowns();
                handleMonthPickerChange();
                return;
            } else {
                dateValue = prefix === 'start' ? startDateValue : endDateValue;
        
                dateValue[type] = value;
        
                if (type === 'year' || type === 'month') {
                    const daysInMonth = new Date(dateValue.year, dateValue.month, 0).getDate();
                    if (dateValue.day > daysInMonth) {
                        dateValue.day = daysInMonth;
                    }
                }
        
                updateDateDisplay(prefix);
                hideAllDropdowns();
                updateDateRangeFromPickers();
            }
        }

        async function handleMonthPickerChange() {
            const year = monthDateValue.year;
            const month = monthDateValue.month;

            if (year && month) {
                const firstDay = `${year}-${String(month).padStart(2, '0')}-01`;
                const lastDay = new Date(year, month, 0).getDate();
                const lastDayFormatted = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;

                dateRange = {
                    startDate: firstDay,
                    endDate: lastDayFormatted
                };

                startDateValue = {
                    year: year,
                    month: month,
                    day: 1
                };

                endDateValue = {
                    year: year,
                    month: month,
                    day: lastDay
                };

                updateDateDisplay('start');
                updateDateDisplay('end');
            }
            else if (year && !month) {
                const firstDay = `${year}-01-01`;
                const lastDay = `${year}-12-31`;

                dateRange = {
                    startDate: firstDay,
                    endDate: lastDay
                };

                startDateValue = {
                    year: year,
                    month: 1,
                    day: 1
                };

                endDateValue = {
                    year: year,
                    month: 12,
                    day: 31
                };

                updateDateDisplay('start');
                updateDateDisplay('end');
            }
            else {
                return;
            }

            if (isDrillDownMode) {
                isDrillDownMode = false;
                drillDownMonth = null;
                originalDateRange = null;
                hideBackButtons();
            }

            if (isRestaurantSelected) {
                await loadData({
                    start_date: dateRange.startDate,
                    end_date: dateRange.endDate
                });
                updateDashboard();
            }
            document.getElementById('quick-select-text').textContent = '选择时间段';
            updateChartDateRange();
        }

        async function updateDateRangeFromPickers() {
            const startDateStr = `${startDateValue.year}-${String(startDateValue.month).padStart(2, '0')}-${String(startDateValue.day).padStart(2, '0')}`;
            const endDateStr = `${endDateValue.year}-${String(endDateValue.month).padStart(2, '0')}-${String(endDateValue.day).padStart(2, '0')}`;
            
            if (new Date(startDateStr) > new Date(endDateStr)) {
                alert('开始日期不能晚于结束日期');
                return;
            }
            
            dateRange = {
                startDate: startDateStr,
                endDate: endDateStr
            };
            
            if (isDrillDownMode) {
                isDrillDownMode = false;
                drillDownMonth = null;
                originalDateRange = null;
                hideBackButtons();
            }
            
            if (isRestaurantSelected) {
                await loadData({
                    start_date: dateRange.startDate,
                    end_date: dateRange.endDate
                });
                updateDashboard();
            }
            document.getElementById('quick-select-text').textContent = '选择时间段';
            updateChartDateRange();
        }

        // 数据获取
        async function loadData(params = {}) {
            try {
                const startDate = params.start_date || dateRange.startDate;
                const endDate = params.end_date || dateRange.endDate;
        
                if (currentRestaurant === 'total') {
                    await loadAllRestaurantsData({ start_date: startDate, end_date: endDate });
                    actualData = mergeAllRestaurantsData();
                } else {
                    const queryParams = new URLSearchParams({
                        action: 'list',
                        restaurant: currentRestaurant,
                        start_date: startDate,
                        end_date: endDate
                    });
            
                    const result = await apiCall(`?${queryParams}`);
                    actualData = result.data || [];
                }
                return actualData;
            } catch (error) {
                console.error('加载数据失败:', error);
                actualData = [];
                return [];
            }
        }

        async function loadSummary(startDate, endDate) {
            try {
                // 对于所有餐厅（包括total），都尝试调用API获取数据
                try {
                    const queryParams = new URLSearchParams({
                        action: 'summary',
                        restaurant: currentRestaurant,
                        start_date: startDate,
                        end_date: endDate
                    });
            
                    const result = await apiCall(`?${queryParams}`);
            
                    if (result.success && result.data) {
                        // 加载库存数据（从月度库存记录获取）
                        const stockData = await loadStockData(startDate, endDate);
                        return {
                            ...result.data,
                            last_stock: stockData.last_stock,
                            current_stock: stockData.current_stock
                        };
                    }
                } catch (error) {
                    console.error('API汇总失败，使用前端计算:', error);
                }
                
                // API失败时，使用前端计算
                const filteredData = getFilteredCostData();
                const stockData = await loadStockData(startDate, endDate);
                
                if (filteredData.length > 0) {
                    const summary = {
                        total_sales: filteredData.reduce((sum, item) => sum + item.sales, 0),
                        total_cost: filteredData.reduce((sum, item) => sum + item.cTotal, 0),
                        total_profit: filteredData.reduce((sum, item) => sum + item.grossTotal, 0),
                        total_days: filteredData.length,
                        last_stock: stockData.last_stock,
                        current_stock: stockData.current_stock
                    };
                    return summary;
                }
        
                return {
                    last_stock: stockData.last_stock,
                    current_stock: stockData.current_stock
                };
            } catch (error) {
                console.error('加载汇总数据失败:', error);
                return {
                    last_stock: 0,
                    current_stock: 0
                };
            }
        }

        // 加载 J1 供应给 J2 和 J3 的数据（仅用于 J1 餐厅）
        async function loadSupplyData(startDate, endDate) {
            try {
                const queryParams = new URLSearchParams({
                    action: 'get_supply',
                    restaurant: 'j1',
                    start_date: startDate,
                    end_date: endDate
                });
                
                const result = await apiCall(`?${queryParams}`);
                
                if (result.success && result.data) {
                    return {
                        j2_supply: parseFloat(result.data.supply_to_j2 || 0),
                        j3_supply: parseFloat(result.data.supply_to_j3 || 0)
                    };
                }
                
                return {
                    j2_supply: 0,
                    j3_supply: 0
                };
            } catch (error) {
                console.error('加载供应数据失败:', error);
                return {
                    j2_supply: 0,
                    j3_supply: 0
                };
            }
        }
 
        // 加载库存数据（从月度库存记录）
        async function loadStockData(startDate, endDate) {
            try {
                // 获取日期范围内的年月
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                // 获取当前选择的结束月份的库存（作为 current_stock）
                const currentYearMonth = `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}`;
                
                // 获取上个月的年月（作为 last_stock）
                const lastMonthDate = new Date(end.getFullYear(), end.getMonth() - 1, 1);
                const lastYearMonth = `${lastMonthDate.getFullYear()}-${String(lastMonthDate.getMonth() + 1).padStart(2, '0')}`;
                
                let currentStock = 0;
                let lastStock = 0;
                
                // 对于总计模式，需要汇总所有餐厅的库存
                if (currentRestaurant === 'total') {
                    const restaurants = ['j1', 'j2', 'j3'];
                    
                    // 获取当前月库存
                    for (const restaurant of restaurants) {
                        try {
                            const queryParams = new URLSearchParams({
                                action: 'get_month_stock',
                                restaurant: restaurant,
                                year_month: currentYearMonth
                            });
                            const result = await apiCall(`?${queryParams}`);
                            if (result.success && result.data && result.data.current_stock) {
                                currentStock += parseFloat(result.data.current_stock);
                            }
                        } catch (error) {
                            console.error(`获取${restaurant}当前库存失败:`, error);
                        }
                    }
                    
                    // 获取上月库存
                    for (const restaurant of restaurants) {
                        try {
                            const queryParams = new URLSearchParams({
                                action: 'get_month_stock',
                                restaurant: restaurant,
                                year_month: lastYearMonth
                            });
                            const result = await apiCall(`?${queryParams}`);
                            if (result.success && result.data && result.data.current_stock) {
                                lastStock += parseFloat(result.data.current_stock);
                            }
                        } catch (error) {
                            console.error(`获取${restaurant}上月库存失败:`, error);
                        }
                    }
                } else {
                    // 单个餐厅模式
                    // 获取当前月库存
                    try {
                        const queryParams = new URLSearchParams({
                            action: 'get_month_stock',
                            restaurant: currentRestaurant,
                            year_month: currentYearMonth
                        });
                        const result = await apiCall(`?${queryParams}`);
                        if (result.success && result.data && result.data.current_stock) {
                            currentStock = parseFloat(result.data.current_stock);
                        }
                    } catch (error) {
                        console.error('获取当前库存失败:', error);
                    }
                    
                    // 获取上月库存
                    try {
                        const queryParams = new URLSearchParams({
                            action: 'get_month_stock',
                            restaurant: currentRestaurant,
                            year_month: lastYearMonth
                        });
                        const result = await apiCall(`?${queryParams}`);
                        if (result.success && result.data && result.data.current_stock) {
                            lastStock = parseFloat(result.data.current_stock);
                        }
                    } catch (error) {
                        console.error('获取上月库存失败:', error);
                    }
                }
                
                return {
                    current_stock: currentStock,
                    last_stock: lastStock
                };
            } catch (error) {
                console.error('加载库存数据失败:', error);
                return {
                    current_stock: 0,
                    last_stock: 0
                };
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
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                throw error;
            }
        }

        // 初始化应用
        async function initApp() {
            console.log('开始初始化应用...');

            // 初始化增强日期选择器
            initEnhancedDatePickers();
    
            // 如果餐厅未选择，不加载数据
            if (!isRestaurantSelected) {
                console.log('等待餐厅选择...');
                document.getElementById('total-sales').textContent = '--';
                document.getElementById('total-cost').textContent = '--';
                document.getElementById('gross-total').textContent = '--';
                document.getElementById('cost-percent').textContent = '--';
                document.getElementById('last-stock').textContent = '--';
                document.getElementById('current-stock').textContent = '--';
                document.getElementById('date-info').textContent = '请先选择餐厅';
                return;
            }

            console.log('初始化后的日期范围:', dateRange);
    
            // 初始化主题色
            updateThemeColors(currentRestaurant);
    
            // 根据默认日期范围加载数据
            await loadData({
                start_date: dateRange.startDate,
                end_date: dateRange.endDate
            });
            updateDashboard();
        }

        // 数据转换和过滤
        function convertToCostFormat(data) {
            return data.map(item => {
                const sales = parseFloat(item.sales) || 0;
                const cBeverage = parseFloat(item.c_beverage) || 0;
                const cKitchen = parseFloat(item.c_kitchen) || 0;
                const cTotal = cBeverage + cKitchen;
                const grossTotal = sales - cTotal;
                const costPercent = sales > 0 ? (cTotal / sales) * 100 : 0;

                return {
                    date: item.date,
                    sales: sales,
                    cBeverage: cBeverage,
                    cKitchen: cKitchen,
                    cTotal: cTotal,
                    grossTotal: grossTotal,
                    costPercent: costPercent
                };
            });
        }

        function fillMissingDates(costData) {
            if (!dateRange.startDate || !dateRange.endDate) {
                return costData;
            }

            const start = new Date(dateRange.startDate);
            const end = new Date(dateRange.endDate);

            if (start > end) {
                return [];
            }

            const costDataMap = new Map(costData.map(item => [item.date, item]));
            const filledData = [];

            for (let current = new Date(start); current <= end; current.setDate(current.getDate() + 1)) {
                const dateKey = current.toISOString().split('T')[0];
                if (costDataMap.has(dateKey)) {
                    filledData.push(costDataMap.get(dateKey));
                } else {
                    filledData.push({
                        date: dateKey,
                        sales: 0,
                        cBeverage: 0,
                        cKitchen: 0,
                        cTotal: 0,
                        grossTotal: 0,
                        costPercent: 0
                    });
                }
            }

            return filledData;
        }

        function getFilteredCostData() {
            const costData = fillMissingDates(convertToCostFormat(actualData));
            return costData.filter(item => {
                const itemDate = new Date(item.date);
                const start = new Date(dateRange.startDate);
                const end = new Date(dateRange.endDate);
                return itemDate >= start && itemDate <= end;
            }).sort((a, b) => new Date(a.date) - new Date(b.date));
        }

        // 更新仪表板
        async function updateDashboard() {
            const summary = await loadSummary(dateRange.startDate, dateRange.endDate);
            const filteredData = getFilteredCostData();
    
            let displaySummary;
            if (filteredData.length > 0) {
                displaySummary = {
                    total_sales: filteredData.reduce((sum, item) => sum + item.sales, 0),
                    data_total_cost: filteredData.reduce((sum, item) => sum + item.cTotal, 0),
                    total_profit: filteredData.reduce((sum, item) => sum + item.grossTotal, 0),
                    total_days: filteredData.length,
                    last_stock: parseFloat(summary.last_stock || 0),
                    current_stock: parseFloat(summary.current_stock || 0)
                };
            } else {
                displaySummary = {
                    total_sales: parseFloat(summary.total_sales || 0),
                    data_total_cost: parseFloat(summary.total_cost || 0),
                    total_profit: parseFloat(summary.total_profit || 0),
                    total_days: parseInt(summary.total_days || 0),
                    last_stock: parseFloat(summary.last_stock || 0),
                    current_stock: parseFloat(summary.current_stock || 0)
                };
            }

            // 计算实际总成本
            let actualTotalCost;
            let j2Supply = 0;
            let j3Supply = 0;

            if (currentRestaurant === 'j1') {
                // J1: 库存（最后）- 库存（现在）+ 详细数据的总成本 - J2供应 - J3供应
                const supplyData = await loadSupplyData(dateRange.startDate, dateRange.endDate);
                j2Supply = parseFloat(supplyData.j2_supply || 0);
                j3Supply = parseFloat(supplyData.j3_supply || 0);
                
                actualTotalCost = displaySummary.last_stock - displaySummary.current_stock + displaySummary.data_total_cost - j2Supply - j3Supply;
                
                // 显示供应数据
                document.getElementById('j2-supply').textContent = `${j2Supply.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                document.getElementById('j3-supply').textContent = `${j3Supply.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            } else {
                // J2/J3/总计: 库存（最后）- 库存（现在）+ 详细数据的总成本
                actualTotalCost = displaySummary.last_stock - displaySummary.current_stock + displaySummary.data_total_cost;
            }

            // 重新计算成本率和毛利润（基于实际总成本）
            displaySummary.total_cost = actualTotalCost;
            displaySummary.avg_cost_percent = displaySummary.total_sales > 0 ? 
                (actualTotalCost / displaySummary.total_sales) * 100 : 0;
            displaySummary.total_profit = displaySummary.total_sales - actualTotalCost;
    
            // 更新显示
            document.getElementById('total-sales').textContent = `${parseFloat(displaySummary.total_sales || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('total-cost').textContent = `${parseFloat(actualTotalCost || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('gross-total').textContent = `${parseFloat(displaySummary.total_profit || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('cost-percent').textContent = `${parseFloat(displaySummary.avg_cost_percent || 0).toFixed(2)}%`;
            document.getElementById('last-stock').textContent = `${parseFloat(displaySummary.last_stock || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('current-stock').textContent = `${parseFloat(displaySummary.current_stock || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    
            document.getElementById('date-info').textContent = `已选择 ${displaySummary.total_days || 0} 天的数据 - ${restaurantConfig[currentRestaurant].name}`;
    
            const chartTitle = document.getElementById('main-chart-title');
            const titles = {
                costPercent: '成本率趋势',
                grossTotal: '毛利润趋势',
                totalCost: '总成本趋势'
            };
            
            let titleText = titles[currentChartDataType] || '总成本趋势';
            if (currentRestaurant === 'total') {
                titleText += ' (三店合计)';
            }
            chartTitle.textContent = titleText;
    
            updateCharts(filteredData);
            updateDashboardTable(filteredData);
            updateChartDateRange();
        }

        function updateCharts(data) {
            const ctx1 = document.getElementById('cost-chart').getContext('2d');
            const config = restaurantConfig[currentRestaurant];

            const aggregatedData = aggregateDataByPeriod(data, dateRange);
            const isMonthlyView = aggregatedData !== data;

            // 餐厅颜色配置
            const restaurantColors = {
                j1: { 
                    primary: '#583e04', 
                    secondary: '#805906'
                },
                j2: { 
                    primary: '#d97706', 
                    secondary: '#f59e0b'
                },
                j3: { 
                    primary: '#dc2626', 
                    secondary: '#f87171'
                }
            };

            if (costChart) {
                costChart.destroy();
            }

            if (currentRestaurant === 'total') {
                // 总计模式：显示三间餐厅的对比数据
                const comparisonData = prepareMonthlyComparisonData();

                const chartLabels = comparisonData.isMonthly ? 
                    comparisonData.dates : 
                    comparisonData.dates.map(date => new Date(date).getDate().toString());

                // 获取数据标签
                const dataLabels = {
                    totalCost: ['J1 总成本', 'J2 总成本', 'J3 总成本'],
                    grossTotal: ['J1 毛利润', 'J2 毛利润', 'J3 毛利润'],
                    costPercent: ['J1 成本率', 'J2 成本率', 'J3 成本率']
                };

                costChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: dataLabels[currentChartDataType][0],
                                data: comparisonData.restaurants.j1.map(item => getChartDataByType(item, currentChartDataType)),
                                borderColor: restaurantColors.j1.primary,
                                backgroundColor: function(context) {
                                    const chart = context.chart;
                                    const {ctx, chartArea} = chart;

                                    if (!chartArea) {
                                        return null;
                                    }

                                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                    gradient.addColorStop(0, 'rgba(88, 62, 4, 0.3)');
                                    gradient.addColorStop(1, 'rgba(88, 62, 4, 0.05)');

                                    return gradient;
                                },
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 6
                            },
                            {
                                label: dataLabels[currentChartDataType][1],
                                data: comparisonData.restaurants.j2.map(item => getChartDataByType(item, currentChartDataType)),
                                borderColor: restaurantColors.j2.primary,
                                backgroundColor: function(context) {
                                    const chart = context.chart;
                                    const {ctx, chartArea} = chart;

                                    if (!chartArea) {
                                        return null;
                                    }

                                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                    gradient.addColorStop(0, 'rgba(217, 119, 6, 0.3)');
                                    gradient.addColorStop(1, 'rgba(217, 119, 6, 0.05)');

                                    return gradient;
                                },
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 6
                            },
                            {
                                label: dataLabels[currentChartDataType][2],
                                data: comparisonData.restaurants.j3.map(item => getChartDataByType(item, currentChartDataType)),
                                borderColor: restaurantColors.j3.primary,
                                backgroundColor: function(context) {
                                    const chart = context.chart;
                                    const {ctx, chartArea} = chart;

                                    if (!chartArea) {
                                        return null;
                                    }

                                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                    gradient.addColorStop(0, 'rgba(220, 38, 38, 0.3)');
                                    gradient.addColorStop(1, 'rgba(220, 38, 38, 0.05)');

                                    return gradient;
                                },
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: getYAxisFormatter(currentChartDataType)
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        if (context.length > 0) {
                                            const dataIndex = context[0].dataIndex;
                                            if (comparisonData.isMonthly) {
                                                return comparisonData.dates[dataIndex];
                                            } else {
                                                const originalDates = Object.values(allRestaurantsData).flat()
                                                    .map(item => item.date)
                                                    .filter((date, index, self) => self.indexOf(date) === index)
                                                    .sort();
                                                const filteredOriginalDates = originalDates.filter(date => {
                                                    const itemDate = new Date(date);
                                                    const start = new Date(dateRange.startDate);
                                                    const end = new Date(dateRange.endDate);
                                                    return itemDate >= start && itemDate <= end;
                                                });
                                                const date = filteredOriginalDates[dataIndex];
                                                return `${date} (${new Date(date).getDate()}号)`;
                                            }
                                        }
                                        return '';
                                    },
                                    label: getTooltipFormatter(currentChartDataType),
                                    afterBody: function(context) {
                                        if (context.length > 0) {
                                            const dataIndex = context[0].dataIndex;
                                            const j1Data = comparisonData.restaurants.j1[dataIndex];
                                            const j2Data = comparisonData.restaurants.j2[dataIndex];
                                            const j3Data = comparisonData.restaurants.j3[dataIndex];

                                            const periodText = comparisonData.isMonthly ? '当月汇总' : '当日汇总';

                                            // 根据当前选择的数据类型显示对应的汇总
                                            let summaryText = '';
                                            switch(currentChartDataType) {
                                                case 'totalCost':
                                                    const totalCost = j1Data.cTotal + j2Data.cTotal + j3Data.cTotal;
                                                    summaryText = `总成本: RM ${totalCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                                    break;
                                                case 'grossTotal':
                                                    const totalGross = j1Data.grossTotal + j2Data.grossTotal + j3Data.grossTotal;
                                                    summaryText = `总毛利润: RM ${totalGross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                                    break;
                                                case 'costPercent':
                                                    const totalSales = j1Data.sales + j2Data.sales + j3Data.sales;
                                                    const totalCostSum = j1Data.cTotal + j2Data.cTotal + j3Data.cTotal;
                                                    const avgCostPercent = totalSales > 0 ? ((totalCostSum / totalSales) * 100).toFixed(2) : '0.00';
                                                    summaryText = `平均成本率: ${avgCostPercent}%`;
                                                    break;
                                            }

                                            return [
                                                '',
                                                `--- ${periodText} ---`,
                                                summaryText
                                            ];
                                        }
                                        return [];
                                    }
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });
            } else {
                // 单店模式
                const chartLabels = isMonthlyView ? 
                    aggregatedData.map(item => item.displayDate) :
                    aggregatedData.map(item => new Date(item.date).getDate().toString());

                const dataLabel = {
                    costPercent: '成本率',
                    grossTotal: '毛利润',
                    totalCost: '总成本'
                };

                costChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: dataLabel[currentChartDataType],
                            data: aggregatedData.map(item => getChartDataByType(item, currentChartDataType)),
                            borderColor: config.colors.primary,
                            backgroundColor: function(context) {
                                const chart = context.chart;
                                const {ctx, chartArea} = chart;

                                if (!chartArea) {
                                    return null;
                                }

                                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                gradient.addColorStop(0, 'rgba(88, 62, 4, 0.4)');
                                gradient.addColorStop(0.3, 'rgba(88, 62, 4, 0.2)');
                                gradient.addColorStop(0.7, 'rgba(88, 62, 4, 0.1)');
                                gradient.addColorStop(1, 'rgba(88, 62, 4, 0.02)');

                                return gradient;
                            },
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: getYAxisFormatter(currentChartDataType)
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: getTooltipFormatter(currentChartDataType)
                                }
                            }
                        }
                    }
                });
            }
        }

        function updateDashboardTable(data) {
            const tbody = document.querySelector('#dashboard-table tbody');
            tbody.innerHTML = '';
            
            const tableHeader = document.getElementById('table-header');
            const firstHeader = tableHeader.querySelector('th');
            if (currentRestaurant === 'total') {
                firstHeader.textContent = '日期 (三店合计)';
            } else {
                firstHeader.textContent = '日期';
            }
            
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.date}</td>
                    <td>RM ${item.sales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>RM ${item.cBeverage.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>RM ${item.cKitchen.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>RM ${item.cTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>RM ${item.grossTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${item.costPercent.toFixed(2)}%</td>
                `;
                tbody.appendChild(row);
            });
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);

        // 添加数据聚合函数
        function aggregateDataByPeriod(data, dateRange) {
            const startDate = new Date(dateRange.startDate);
            const endDate = new Date(dateRange.endDate);
            const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
    
            if (daysDiff > 60) {
                return aggregateByMonth(data);
            } else {
                return data;
            }
        }

        // 按月聚合数据
        function aggregateByMonth(data) {
            const monthMap = new Map();
    
            data.forEach(item => {
                const date = new Date(item.date);
                const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        
                if (!monthMap.has(monthKey)) {
                    monthMap.set(monthKey, {
                        date: monthKey,
                        displayDate: `${date.getFullYear()}年${date.getMonth() + 1}月`,
                        sales: 0,
                        cBeverage: 0,
                        cKitchen: 0,
                        cTotal: 0,
                        grossTotal: 0,
                        daysCount: 0
                    });
                }
        
                const monthData = monthMap.get(monthKey);
                monthData.sales += item.sales;
                monthData.cBeverage += item.cBeverage;
                monthData.cKitchen += item.cKitchen;
                monthData.cTotal += item.cTotal;
                monthData.grossTotal += item.grossTotal;
                monthData.daysCount += 1;
            });
    
            return Array.from(monthMap.values()).map(item => ({
                ...item,
                costPercent: item.sales > 0 ? (item.cTotal / item.sales) * 100 : 0
            })).sort((a, b) => a.date.localeCompare(b.date));
        }

        // 更新主题颜色
        function updateThemeColors(restaurant) {
            const config = restaurantConfig[restaurant];
            const root = document.documentElement;
            
            root.style.setProperty('--primary-color', config.colors.primary);
            root.style.setProperty('--secondary-color', config.colors.secondary);
        }

        // 格式化日期显示
        function formatDateForDisplay(dateString) {
            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            const day = date.getDate();
            return `${year}年${month}月${day}日`;
        }

        // 更新图表日期范围显示
        function updateChartDateRange() {
            const chartDateRange = document.getElementById('chart-date-range');
            if (!chartDateRange) return;
            
            const startDateFormatted = formatDateForDisplay(dateRange.startDate);
            const endDateFormatted = formatDateForDisplay(dateRange.endDate);
            
            if (dateRange.startDate === dateRange.endDate) {
                chartDateRange.textContent = startDateFormatted;
            } else {
                chartDateRange.textContent = `${startDateFormatted} 至 ${endDateFormatted}`;
            }
        }

        function switchChartData(dataType) {
            currentChartDataType = dataType;
            
            document.querySelectorAll('.chart-data-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-type="${dataType}"]`).classList.add('active');
            
            const chartTitle = document.getElementById('main-chart-title');
            const titles = {
                costPercent: '成本率趋势',
                grossTotal: '毛利润趋势',
                totalCost: '总成本趋势'
            };
            
            let titleText = titles[dataType];
            if (currentRestaurant === 'total') {
                titleText += ' (三店合计)';
            }
            chartTitle.textContent = titleText;
            
            const filteredData = getFilteredCostData();
            updateCharts(filteredData);
        }

        function getChartDataByType(item, dataType) {
            switch(dataType) {
                case 'costPercent':
                    return item.costPercent;
                case 'grossTotal':
                    return item.grossTotal;
                case 'totalCost':
                    return item.cTotal;
                default:
                    return item.costPercent;
            }
        }

        function getYAxisFormatter(dataType) {
            switch(dataType) {
                case 'costPercent':
                    return function(value) {
                        return value.toFixed(2) + '%';
                    };
                case 'grossTotal':
                case 'totalCost':
                    return function(value) {
                        return 'RM ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    };
                default:
                    return function(value) {
                        return value.toString();
                    };
            }
        }

        function getTooltipFormatter(dataType) {
            switch(dataType) {
                case 'costPercent':
                    return function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                    };
                case 'grossTotal':
                case 'totalCost':
                    return function(context) {
                        return context.dataset.label + ': RM ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    };
                default:
                    return function(context) {
                        return context.dataset.label + ': ' + context.parsed.y;
                    };
            }
        }

        // 加载所有餐厅数据
        async function loadAllRestaurantsData(params = {}) {
            try {
                // 根据当前选择的字母确定要加载的餐厅
                const restaurants = [`${currentLetter.toLowerCase()}1`, `${currentLetter.toLowerCase()}2`, `${currentLetter.toLowerCase()}3`];
        
                // 确保有有效的日期参数
                const startDate = params.start_date || dateRange.startDate;
                const endDate = params.end_date || dateRange.endDate;

                const promises = restaurants.map(async (restaurant) => {
                    const queryParams = new URLSearchParams({
                        action: 'list',
                        restaurant: restaurant,
                        start_date: startDate,
                        end_date: endDate
                    });
    
                    try {
                        const result = await apiCall(`?${queryParams}`);
                        return { restaurant, data: result.data || [] };
                    } catch (error) {
                        console.error(`加载${restaurant}数据失败:`, error);
                        return { restaurant, data: [] };
                    }
                });

                const results = await Promise.all(promises);

                // 存储各餐厅数据
                allRestaurantsData = {};
                results.forEach(({ restaurant, data }) => {
                    allRestaurantsData[restaurant] = data;
                });

                return allRestaurantsData;
            } catch (error) {
                console.error('加载所有餐厅数据失败:', error);
                allRestaurantsData = {};
                return {};
            }
        }

        // 合并所有餐厅数据
        function mergeAllRestaurantsData() {
            const dateMap = new Map();

            // 遍历所有餐厅数据
            Object.values(allRestaurantsData).forEach(restaurantData => {
                restaurantData.forEach(item => {
                    const date = item.date;
                    if (!dateMap.has(date)) {
                        dateMap.set(date, {
                            date: date,
                            sales: 0,
                            c_beverage: 0,
                            c_kitchen: 0
                        });
                    }

                    const existing = dateMap.get(date);
                    existing.sales += parseFloat(item.sales) || 0;
                    existing.c_beverage += parseFloat(item.c_beverage) || 0;
                    existing.c_kitchen += parseFloat(item.c_kitchen) || 0;
                });
            });

            // 转换为数组并排序
            return Array.from(dateMap.values()).sort((a, b) => new Date(a.date) - new Date(b.date));
        }

        // 为总计模式准备对比数据
        function prepareMonthlyComparisonData() {
            if (currentRestaurant !== 'total' || !allRestaurantsData) {
                return null;
            }
    
            const restaurants = ['j1', 'j2', 'j3'];
            const restaurantDataConverted = {};
    
            // 先转换每个餐厅的数据格式
            restaurants.forEach(restaurant => {
                const restaurantData = allRestaurantsData[restaurant] || [];
                restaurantDataConverted[restaurant] = fillMissingDates(convertToCostFormat(restaurantData));
            });
    
            // 获取所有日期并过滤
            const dateSet = new Set();
            Object.values(restaurantDataConverted).forEach(data => {
                data.forEach(item => dateSet.add(item.date));
            });
    
            const sortedDates = Array.from(dateSet).sort();
            const filteredDates = sortedDates.filter(date => {
                const itemDate = new Date(date);
                const start = new Date(dateRange.startDate);
                const end = new Date(dateRange.endDate);
                return itemDate >= start && itemDate <= end;
            });
    
            // 为每个餐厅创建过滤后的数据
            const filteredRestaurantData = {};
            restaurants.forEach(restaurant => {
                filteredRestaurantData[restaurant] = restaurantDataConverted[restaurant].filter(item => {
                    const itemDate = new Date(item.date);
                    const start = new Date(dateRange.startDate);
                    const end = new Date(dateRange.endDate);
                    return itemDate >= start && itemDate <= end;
                }).sort((a, b) => new Date(a.date) - new Date(b.date));
            });
    
            // 判断是否需要按月聚合
            const startDate = new Date(dateRange.startDate);
            const endDate = new Date(dateRange.endDate);
            const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
    
            if (daysDiff > 60) {
                // 按月聚合
                const aggregatedData = {};
                restaurants.forEach(restaurant => {
                    aggregatedData[restaurant] = aggregateByMonth(filteredRestaurantData[restaurant]);
                });
        
                // 获取所有月份
                const monthSet = new Set();
                Object.values(aggregatedData).forEach(data => {
                    data.forEach(item => monthSet.add(item.date));
                });
                const months = Array.from(monthSet).sort();
        
                return {
                    dates: months.map(monthKey => {
                        const [year, month] = monthKey.split('-');
                        return `${year}年${parseInt(month)}月`;
                    }),
                    restaurants: {
                        j1: months.map(monthKey => aggregatedData.j1.find(item => item.date === monthKey) || createEmptyCostDataPoint()),
                        j2: months.map(monthKey => aggregatedData.j2.find(item => item.date === monthKey) || createEmptyCostDataPoint()),
                        j3: months.map(monthKey => aggregatedData.j3.find(item => item.date === monthKey) || createEmptyCostDataPoint())
                    },
                    isMonthly: true
                };
            } else {
                // 按天显示
                return {
                    dates: filteredDates,
                    restaurants: {
                        j1: filteredDates.map(date => filteredRestaurantData.j1.find(item => item.date === date) || createEmptyCostDataPoint()),
                        j2: filteredDates.map(date => filteredRestaurantData.j2.find(item => item.date === date) || createEmptyCostDataPoint()),
                        j3: filteredDates.map(date => filteredRestaurantData.j3.find(item => item.date === date) || createEmptyCostDataPoint())
                    },
                    isMonthly: false
                };
            }
        }

        // 创建空成本数据点的辅助函数
        function createEmptyCostDataPoint() {
            return {
                sales: 0,
                cBeverage: 0,
                cKitchen: 0,
                cTotal: 0,
                grossTotal: 0,
                costPercent: 0
            };
        }

        function hideBackButtons() {}
        function exitDrillDown() {}
    </script>
    <script>
        // 快速选择下拉菜单控制
        function toggleQuickSelectDropdown() {
            const dropdown = document.getElementById('quick-select-dropdown');
            hideAllDropdowns();
            dropdown.classList.toggle('show');
        }

        async function selectQuickRange(range) {
            const today = new Date();
            let startDate, endDate;

            switch(range) {
                case 'today':
                    startDate = new Date(today);
                    endDate = new Date(today);
                    break;
                
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    startDate = yesterday;
                    endDate = yesterday;
                    break;

                case 'thisWeek':
                    const thisWeekStart = new Date(today);
                    const dayOfWeek = thisWeekStart.getDay();
                    const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
                    thisWeekStart.setDate(thisWeekStart.getDate() - daysToMonday);
                    startDate = thisWeekStart;
                    endDate = new Date(today);
                    break;
            
                case 'lastWeek':
                    const lastWeekEnd = new Date(today);
                    const lastWeekDayOfWeek = lastWeekEnd.getDay();
                    const daysToLastSunday = lastWeekDayOfWeek === 0 ? 0 : lastWeekDayOfWeek;
                    lastWeekEnd.setDate(lastWeekEnd.getDate() - daysToLastSunday - 1);
                    const lastWeekStart = new Date(lastWeekEnd);
                    lastWeekStart.setDate(lastWeekStart.getDate() - 6);
                    startDate = lastWeekStart;
                    endDate = lastWeekEnd;
                    break;
            
                case 'thisMonth':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today);
                    break;
            
                case 'lastMonth':
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                    startDate = lastMonth;
                    endDate = lastMonthEnd;
                    break;
            
                case 'thisYear':
                    startDate = new Date(today.getFullYear(), 0, 1);
                    endDate = new Date(today);
                    break;
            
                case 'lastYear':
                    startDate = new Date(today.getFullYear() - 1, 0, 1);
                    endDate = new Date(today.getFullYear() - 1, 11, 31);
                    break;
            
                default:
                    return;
            }

            const formatDate = (date) => {
                return date.getFullYear() + '-' + 
                    String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(date.getDate()).padStart(2, '0');
            };

            dateRange = {
                startDate: formatDate(startDate),
                endDate: formatDate(endDate)
            };

            startDateValue = {
                year: startDate.getFullYear(),
                month: startDate.getMonth() + 1,
                day: startDate.getDate()
            };

            endDateValue = {
                year: endDate.getFullYear(),
                month: endDate.getMonth() + 1,
                day: endDate.getDate()
            };

            monthDateValue = {
                year: null,
                month: null
            };

            updateDateDisplay('start');
            updateDateDisplay('end');
            updateDateDisplay('month');

            const quickSelectText = document.getElementById('quick-select-text');
            const rangeTexts = {
                'today': '今天',
                'yesterday': '昨天',
                'thisWeek': '本周',
                'lastWeek': '上周',
                'thisMonth': '这个月',
                'lastMonth': '上个月',
                'thisYear': '今年',
                'lastYear': '去年'
            };
            quickSelectText.textContent = rangeTexts[range] || '选择时间段';

            document.getElementById('quick-select-dropdown').classList.remove('show');

            if (isDrillDownMode) {
                isDrillDownMode = false;
                drillDownMonth = null;
                originalDateRange = null;
                hideBackButtons();
            }

            if (isRestaurantSelected) {
                await loadData({
                    start_date: dateRange.startDate,
                    end_date: dateRange.endDate
                });
                updateDashboard();
            }
            updateChartDateRange();
        }

        // 切换报表类型下拉菜单
        function toggleReportTypeDropdown() {
            const dropdown = document.getElementById('report-type-dropdown');
            dropdown.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.enhanced-date-picker')) {
                hideAllDropdowns();
            }
    
            if (!e.target.closest('.number-dropdown')) {
                document.getElementById('number-dropdown').classList.remove('show');
            }
    
            // 关闭快速选择下拉菜单
            if (!e.target.closest('.dropdown')) {
                document.getElementById('quick-select-dropdown').classList.remove('show');
            }

            // 关闭报表类型下拉菜单
            if (!e.target.closest('.report-type-selector')) {
                const reportDropdown = document.getElementById('report-type-dropdown');
                if (reportDropdown) {
                    reportDropdown.classList.remove('show');
                }
            }
        });
    </script>
    <script>
        // 切换下拉菜单
        function toggleRestaurantDropdown() {
            const dropdown = document.getElementById('restaurant-dropdown');
            dropdown.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.restaurant-selector')) {
                const dropdown = document.getElementById('restaurant-dropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                    hideNumberOptions();
                }
            }
        });

        function switchRestaurant(restaurant) {
            if (currentRestaurant === restaurant) return;
            
            currentRestaurant = restaurant;
            
            document.querySelectorAll('.restaurant-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.restaurant === restaurant) {
                    btn.classList.add('active');
                }
            });
            
            document.body.className = `restaurant-${restaurant}`;
            updateThemeColors(restaurant);
            
            loadData().then(() => {
                updateDashboard();
            });
        }
    </script>
    <script>
        // 当前选择的字母和数字
        let currentLetter = null;
        let currentNumber = null;
        let isRestaurantSelected = false;

        function showNumberOptions(letter) {
            currentLetter = letter;
    
            document.querySelectorAll('.letter-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.querySelector(`[onclick*="'${letter}'"]`).classList.add('selected');
    
            const numberSelection = document.getElementById('number-selection');
            const sectionTitle = numberSelection.querySelector('.section-title');
            const numberGrid = numberSelection.querySelector('.number-grid');

            sectionTitle.textContent = `选择${letter}分店`;

            numberGrid.innerHTML = '';

            if (letter === 'J') {
                numberGrid.innerHTML = `
                    <button class="number-item" onclick="selectRestaurant('1')">1</button>
                    <button class="number-item" onclick="selectRestaurant('2')">2</button>
                    <button class="number-item" onclick="selectRestaurant('3')">3</button>
                    <button class="number-item total-option" onclick="selectRestaurant('total')">总</button>
                `;
            }

            numberSelection.style.visibility = 'visible';
            numberSelection.style.opacity = '1';
        }

        async function selectRestaurant(number) {
            currentNumber = number;
            isRestaurantSelected = true;
    
            if (number === 'total') {
                currentRestaurant = 'total';
                updateRestaurantButton(`${currentLetter}总`);
            } else {
                currentRestaurant = `${currentLetter.toLowerCase()}${number}`;
                updateRestaurantButton(`${currentLetter}${number}`);
            }
    
            document.getElementById('restaurant-dropdown').classList.remove('show');
            updateThemeColors(currentRestaurant);

            // 控制 J1 模式显示
            const costGrid = document.getElementById('cost-grid');
            if (currentRestaurant === 'j1') {
                costGrid.classList.add('j1-mode');
            } else {
                costGrid.classList.remove('j1-mode');
            }
    
            await loadData();
            updateDashboard();
        }

        function selectLetter(letter) {
            showNumberOptions(letter);
        }

        function hideNumberOptions() {
            const numberSelection = document.getElementById('number-selection');
            const sectionTitle = numberSelection.querySelector('.section-title');
            const numberGrid = numberSelection.querySelector('.number-grid');
    
            numberSelection.style.visibility = 'hidden';
            numberSelection.style.opacity = '0';
    
            sectionTitle.textContent = '选择餐厅';
            numberGrid.innerHTML = '';
    
            document.querySelectorAll('.letter-item').forEach(item => {
                item.classList.remove('selected');
            });
    
            currentLetter = null;
        }

        function updateRestaurantButton(text) {
            const restaurantBtn = document.querySelector('.restaurant-btn');
            restaurantBtn.innerHTML = `${text} <i class="fas fa-chevron-down"></i>`;
        }
    </script>
</body>
</html>

