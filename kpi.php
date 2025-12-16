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
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅KPI管理系统</title>    
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
            margin-left: auto;
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
            width: clamp(240px, 25vw, 320px); /* 调整响应式宽度 */
            min-width: 200px;
            padding: clamp(6px, 0.63vw, 12px) clamp(8px, 1vw, 16px); /* 响应式内边距 */
        }

        .restaurant-dropdown-menu.show {
            display: flex;
            gap: 16px;
            flex-direction: row; /* 强制为行布局 */
        }

        .letter-selection,
        .number-selection {
            flex: 1;
        }

        .letter-selection {
            flex: 1;
            border-right: 1px solid #e5e7eb;
            padding-right: 12px;
            min-width: 110px; /* 稍微增加宽度以容纳横向布局 */
        }

        .number-selection {
            flex: 1;
            padding-left: 0px;
            min-width: 90px; /* 固定最小宽度 */
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .section-title {
            font-size: clamp(10px, 0.9vw, 14px); /* 增加最小值 */
            font-weight: 600;
            color: #000000ff;
            margin-bottom: clamp(4px, 0.6vw, 8px); /* 响应式边距 */
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
            gap: clamp(4px, 0.8vw, 8px); /* 响应式间距 */
            align-items: center;
            justify-content: center;
        }

        .letter-item,
        .number-item,
        .number-item.total-option {
            display: flex;
            align-items: center;
            justify-content: center;
            width: clamp(12px, 1.25vw, 24px); /* 增加最小值 */
            height: clamp(12px, 1.25vw, 24px); /* 增加最小值 */
            border: 1px solid #e5e7eb;
            background: white;
            color: #000000ff;
            cursor: pointer;
            font-size: clamp(7px, 0.63vw, 12px); /* 增加最小值 */
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
            width: 100%; /* 改为固定 100% 宽度 */
            box-sizing: border-box; /* 确保边框不会增加宽度 */
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
        
        /* KPI 网格 - 改为单行5列 */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: clamp(14px, 1.67vw, 32px);
        }

        /* 图表容器 - 改为全宽 */
        .main-chart-container {
            display: flex;
            flex-direction: column;
            margin-bottom: clamp(16px, 1.67vw, 32px);
        }
        
        /* 下方图表网格 - 现在只有一个图表 */
        .bottom-charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }      
        
        .kpi-card {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .kpi-card .icon {
            width: 40px;
            height: 40px;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .kpi-card-vertical {
            display: flex;
            flex-direction: column;
            align-items: left;
            text-align: left;
            gap: 0px;
        }

        .kpi-card-vertical .icon {
            width: 50px;
            height: 50px;
            font-size: clamp(20px, 1.5vw, 28px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-bottom: clamp(0px, 0.21vw, 4px);
        }

        .kpi-card-vertical .kpi-label {
            font-size: clamp(10px, 0.84vw, 16px);
            color: #000000;
            font-weight: bold;
            margin-bottom: 0px;
        }

        .kpi-card-vertical .kpi-value {
            font-size: clamp(16px, 1.25vw, 24px);
            font-weight: bold;
            color: #111827;
        }

        .kpi-value {
            font-size: 1.75rem;
            font-weight: bold;
            color: #111827;
            margin-bottom: 2px;
        }
        
        .kpi-label {
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
    </style>
</head>
<body class="restaurant-j1">
    <?php include 'sidebar.php'; ?>
        <div class="container">
            <div class="header">
                <div>
                    <h1>KPI 仪表盘</h1>
                </div>
                <div class="controls">
                    <!-- 控件区域 -->
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
                                <!-- 保持原有的日期选择器内容不变 -->
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
            
                                <!-- 下拉选择面板 -->
                                <div class="date-dropdown" id="start-dropdown">
                                    <!-- 动态内容将在这里生成 -->
                                </div>
                            </div>
                        </div>
    
                        <!-- 结束日期选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0;">结束日期</label>
                            <div class="enhanced-date-picker" id="end-date-picker">
                                <!-- 保持原有的日期选择器内容不变 -->
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
            
                                <!-- 下拉选择面板 -->
                                <div class="date-dropdown" id="end-dropdown">
                                    <!-- 动态内容将在这里生成 -->
                                </div>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- 月份选择器 - 改为增强型选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-calendar" style="color: #000000ff;"></i>
                                选择年份和月份
                            </label>
                            <div class="enhanced-date-picker month-only" id="month-date-picker">
                                <!-- 保持原有的月份选择器内容不变 -->
                                <div class="date-part" data-type="year" onclick="showDateDropdown('month', 'year')">
                                    <span id="month-year-display">2024</span>
                                </div>
                                <span class="date-separator">年</span>
                                <div class="date-part" data-type="month" onclick="showDateDropdown('month', 'month')">
                                    <span id="month-month-display">01</span>
                                </div>
                                <span class="date-separator">月</span>
            
                                <!-- 下拉选择面板 -->
                                <div class="date-dropdown" id="month-dropdown">
                                    <!-- 动态内容将在这里生成 -->
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
                                        <!-- 默认为空 -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- KPI Cards - 单行5列 -->
                <div class="kpi-grid">
                    <!-- 总销售额 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="kpi-card-vertical">
                                <div class="icon text-green">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div>
                                    <p class="kpi-label">总销售额 (RM)</p>
                                    <p class="kpi-value" id="total-sales">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 净销售额 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="kpi-card-vertical">
                                <div class="icon text-green">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <p class="kpi-label">净销售额 (RM)</p>
                                    <p class="kpi-value" id="net-sales">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 桌子总数 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="kpi-card-vertical">
                                <div class="icon dynamic-color">
                                    <img src="images/images/table.svg" alt="桌子图标" style="width: clamp(30px, 2.1vw, 40px); height: clamp(28px, 1.98vw, 38px); filter: brightness(0);">
                                </div>
                                <div>
                                    <p class="kpi-label">桌子总数</p>
                                    <p class="kpi-value" id="total-tables">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 顾客总数 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="kpi-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <p class="kpi-label">顾客总数</p>
                                    <p class="kpi-value" id="total-diners">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- 人均消费 -->
                    <div class="card">
                        <div class="card-body">
                            <div class="kpi-card-vertical">
                                <div class="icon dynamic-color">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div>
                                    <p class="kpi-label">人均消费 (RM)</p>
                                    <p class="kpi-value" id="avg-per-diner">0</p>
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
                                <h3 id="main-chart-title" style="font-size: clamp(14px, 1.04vw, 20px); font-weight: 600; color: #111827; margin: 0;">净销售额趋势</h3>
                                
                                <!-- 新增：数据类型切换按钮组 -->
                                <div class="chart-data-buttons" style="display: flex; gap: 8px; align-items: center;">
                                    <button class="chart-data-btn active" data-type="netSales" onclick="switchChartData('netSales')">
                                        净销售额
                                    </button>
                                    <button class="chart-data-btn" data-type="tables" onclick="switchChartData('tables')">
                                        桌子数量
                                    </button>
                                    <button class="chart-data-btn" data-type="returningRate" onclick="switchChartData('returningRate')">
                                        常客(%)
                                    </button>
                                    <button class="chart-data-btn" data-type="diners" onclick="switchChartData('diners')">
                                        人数
                                    </button>
                                </div>
                                
                                <div class="date-range-display" id="chart-date-range" style="font-size: clamp(8px, 0.74vw, 14px); color: #6b7280; font-weight: 500;">
                                    <!-- 日期范围将在这里显示 -->
                                </div>
                            </div>
                            <div class="chart-container" style="flex: 1;">
                                <button class="chart-back-button" id="sales-chart-back" onclick="exitDrillDown()">
                                    <i class="fas fa-arrow-left"></i> 返回年度视图
                                </button>
                                <canvas id="sales-chart"></canvas>
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
                                <th>总销售额</th>
                                <th>净销售额</th>
                                <th>人均消费</th>
                                <th>桌子总数</th>
                                <th>顾客总数</th>
                                <th>新客人数</th>
                                <th>常客人数</th>
                                <th>常客百分比</th>
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
        const API_BASE_URL = 'kpiapi.php';
        
        // 应用状态
        let actualData = [];
        let allRestaurantsData = {}; // 存储所有餐厅的数据
        let currentRestaurant = null;
        let dateRange = {
            startDate: null,
            endDate: null
        };
        let currentChartDataType = 'netSales';
        let salesChart = null;
        
        // 日期选择器状态
        let currentDatePicker = null;
        let currentDateType = null;
        let startDateValue = { year: null, month: null, day: null };
        let endDateValue = { year: null, month: null, day: null };
        let monthDateValue = { year: null, month: null }; // 新增月份选择器状态

        // 钻取状态管理
        let isDrillDownMode = false;
        let originalDateRange = null;
        let drillDownMonth = null;
        
        // 餐厅配置
        const restaurantConfig = {
            j1: {
                name: 'J1',
                tableName: 'j1data_view',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            j2: {
                name: 'J2',
                tableName: 'j2data_view',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            j3: {
                name: 'J3',
                tableName: 'j3data_view',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            k1: {
                name: 'K1',
                tableName: 'k1data_view',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            k2: {
                name: 'K2',
                tableName: 'k2data_view',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            k3: {
                name: 'K3',
                tableName: 'k3data_view',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            },
            total: {
                name: '总计',
                tableName: 'all_restaurants',
                colors: {
                    primary: '#583e04',
                    secondary: '#805906'
                }
            }
        };

        // 工具函数
        function getToday() {
            return new Date().toISOString().split('T')[0];
        }

        function getTodayMinusMonth() {
            const date = new Date();
            date.setMonth(date.getMonth() - 1);
            return date.toISOString().split('T')[0];
        }

        function getCurrentMonth() {
            return new Date().toISOString().slice(0, 7);
        }

        // 新增：获取当前月份的第一天
        function getCurrentMonthFirstDay() {
            const date = new Date();
            return new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
        }

        // 新增：获取当前月份的最后一天
        function getCurrentMonthLastDay() {
            const date = new Date();
            // 获取下个月的第0天，即本月最后一天
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
            return lastDay.toISOString().split('T')[0];
        }

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
            // 隐藏其他下拉框
            hideAllDropdowns();
            
            const dropdown = document.getElementById(`${prefix}-dropdown`);
            const datePicker = document.getElementById(`${prefix}-date-picker`);
            
            // 设置当前状态
            currentDatePicker = prefix;
            currentDateType = type;
            
            // 移除所有active状态
            datePicker.querySelectorAll('.date-part').forEach(part => {
                part.classList.remove('active');
            });
            
            // 添加当前选中的active状态
            datePicker.querySelector(`[data-type="${type}"]`).classList.add('active');
            
            // 生成下拉内容
            generateDropdownContent(prefix, type);
            
            // 显示下拉框
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
                // 生成年份选择
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
                // 生成月份选择
                const monthGrid = document.createElement('div');
                monthGrid.className = 'month-grid';

                // 添加"无"选项
                const noneOption = document.createElement('div');
                noneOption.className = 'date-option';
                noneOption.textContent = '无';
                noneOption.style.gridColumn = '1 / -1'; // 让"无"选项占满整行

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
                // 日期选择逻辑保持不变
                const dayGrid = document.createElement('div');
                dayGrid.className = 'day-grid';
        
                // 添加星期标题
                const weekdays = ['日', '一', '二', '三', '四', '五', '六'];
                weekdays.forEach(day => {
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'day-header';
                    dayHeader.textContent = day;
                    dayGrid.appendChild(dayHeader);
                });
        
                // 计算当月信息
                const year = dateValue.year;
                const month = dateValue.month;
                const firstDay = new Date(year, month - 1, 1);
                const lastDay = new Date(year, month, 0);
                const daysInMonth = lastDay.getDate();
                const startDayOfWeek = firstDay.getDay();
        
                // 添加空白日期（上个月的）
                for (let i = 0; i < startDayOfWeek; i++) {
                    const emptyDay = document.createElement('div');
                    dayGrid.appendChild(emptyDay);
                }
        
                // 添加当月日期
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
        
                // 更新值
                dateValue[type] = value;
        
                // 更新显示
                updateDateDisplay('month');
        
                // 隐藏下拉框
                hideAllDropdowns();
        
                // 处理月份选择器的数据加载逻辑
                handleMonthPickerChange();
        
                return; // 提前返回，不执行后面的日期选择器逻辑
            } else {
                dateValue = prefix === 'start' ? startDateValue : endDateValue;
        
                // 更新值
                dateValue[type] = value;
        
                // 如果选择了年份或月份，需要验证日期的有效性
                if (type === 'year' || type === 'month') {
                    const daysInMonth = new Date(dateValue.year, dateValue.month, 0).getDate();
                    if (dateValue.day > daysInMonth) {
                        dateValue.day = daysInMonth;
                    }
                }
        
                // 更新显示
                updateDateDisplay(prefix);
        
                // 隐藏下拉框
                hideAllDropdowns();
        
                // 更新日期范围
                updateDateRangeFromPickers();
            }
        }

        // 处理月份选择器变化
        async function handleMonthPickerChange() {
            const year = monthDateValue.year;
            const month = monthDateValue.month;

            // 如果年份和月份都选择了，显示整个月的数据
            if (year && month) {
                const firstDay = `${year}-${String(month).padStart(2, '0')}-01`;
                const lastDay = new Date(year, month, 0).getDate();
                const lastDayFormatted = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;

                dateRange = {
                    startDate: firstDay,
                    endDate: lastDayFormatted
                };

                // 更新开始和结束日期选择器的值
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
            // 如果只选择了年份，显示整年的数据
            else if (year && !month) {
                const firstDay = `${year}-01-01`;
                const lastDay = `${year}-12-31`;

                dateRange = {
                    startDate: firstDay,
                    endDate: lastDay
                };

                // 更新开始和结束日期选择器的值为整年
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
            // 如果都没选择，不做任何操作
            else {
                return;
            }

            // 新增：退出钻取模式
            if (isDrillDownMode) {
                isDrillDownMode = false;
                drillDownMonth = null;
                originalDateRange = null;
                hideBackButtons();
            }

            // 只有选择了餐厅才加载数据
            if (isRestaurantSelected) {
                await loadData({
                    start_date: dateRange.startDate,
                    end_date: dateRange.endDate
                });
                updateDashboard();
            }
            document.getElementById('quick-select-text').textContent = '选择时间段';
            
            // 更新图表日期范围显示
            updateChartDateRange();
        }

        async function updateDateRangeFromPickers() {
            const startDateStr = `${startDateValue.year}-${String(startDateValue.month).padStart(2, '0')}-${String(startDateValue.day).padStart(2, '0')}`;
            const endDateStr = `${endDateValue.year}-${String(endDateValue.month).padStart(2, '0')}-${String(endDateValue.day).padStart(2, '0')}`;
            
            // 验证日期有效性
            if (new Date(startDateStr) > new Date(endDateStr)) {
                alert('开始日期不能晚于结束日期');
                return;
            }
            
            dateRange = {
                startDate: startDateStr,
                endDate: endDateStr
            };
            
            // 新增：退出钻取模式
            if (isDrillDownMode) {
                isDrillDownMode = false;
                drillDownMonth = null;
                originalDateRange = null;
                hideBackButtons();
            }
            
            // 只有选择了餐厅才加载数据
            if (isRestaurantSelected) {
                await loadData({
                    start_date: dateRange.startDate,
                    end_date: dateRange.endDate
                });
                updateDashboard();
            }
            document.getElementById('quick-select-text').textContent = '选择时间段';
            
            // 更新图表日期范围显示
            updateChartDateRange();
        }

        // 切换餐厅
        function switchRestaurant(restaurant) {
            if (currentRestaurant === restaurant) return;
            
            currentRestaurant = restaurant;
            
            // 更新按钮状态
            document.querySelectorAll('.restaurant-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.restaurant === restaurant) {
                    btn.classList.add('active');
                }
            });
            
            // 更新页面主题色
            document.body.className = `restaurant-${restaurant}`;
            updateThemeColors(restaurant);
            
            // 重新加载数据
            loadData().then(() => {
                updateDashboard();
            });
        }

        // 更新主题颜色
        function updateThemeColors(restaurant) {
            const config = restaurantConfig[restaurant];
            const root = document.documentElement;
            
            root.style.setProperty('--primary-color', config.colors.primary);
            root.style.setProperty('--secondary-color', config.colors.secondary);
            
            // 更新餐厅选择器边框色
            const selector = document.querySelector('.restaurant-selector');
            selector.style.borderColor = config.colors.primary;
        }

        // 返回上一页功能
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
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
        
                // 先检查HTTP状态码
                if (!response.ok) {
                    throw new Error(`HTTP错误: ${response.status}`);
                }
        
                const data = await response.json();
        
                // 返回完整的响应数据，让调用者处理success字段
                return data;
            } catch (error) {
                console.error('API调用失败:', error);
                throw error;
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
                        // 即使 success 为 false，也可能有数据
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
                            gross_sales: 0,
                            net_sales: 0,
                            tender_amount: 0,
                            discounts: 0,
                            tax: 0,
                            service_fee: 0,
                            adj_amount: 0,
                            diners: 0,
                            tables_used: 0,
                            returning_customers: 0,
                            new_customers: 0
                        });
                    }

                    const existing = dateMap.get(date);
                    const grossSales = parseFloat(item.gross_sales) || 0;
                    const tenderAmount = parseFloat(item.tender_amount) || 0;
                    const discounts = parseFloat(item.discounts) || 0;
                    const netSales = item.net_sales ? parseFloat(item.net_sales) : (grossSales - discounts);

                    existing.gross_sales += grossSales;
                    existing.tender_amount += tenderAmount; // 这里是关键修改
                    existing.net_sales += netSales;
                    existing.discounts += discounts;
                    existing.tax += parseFloat(item.tax) || 0;
                    existing.service_fee += parseFloat(item.service_fee) || 0;
                    existing.adj_amount += parseFloat(item.adj_amount) || 0;
                    existing.diners += parseInt(item.diners) || 0;
                    existing.tables_used += parseInt(item.tables_used) || 0;
                    existing.returning_customers += parseInt(item.returning_customers) || 0;
                    existing.new_customers += parseInt(item.new_customers) || 0;
                });
            });

            // 转换为数组并排序
            return Array.from(dateMap.values()).sort((a, b) => new Date(a.date) - new Date(b.date));
        }

        // 数据获取
        async function loadData(params = {}) {
            try {
                // 确保有有效的日期范围
                const startDate = params.start_date || dateRange.startDate;
                const endDate = params.end_date || dateRange.endDate;
        
                if (currentRestaurant === 'total') {
                    // 加载所有餐厅数据
                    await loadAllRestaurantsData({ start_date: startDate, end_date: endDate });
                    actualData = mergeAllRestaurantsData();
                } else {
                    // 加载单个餐厅数据
                    const queryParams = new URLSearchParams({
                        action: 'list',
                        restaurant: currentRestaurant,
                        start_date: startDate,
                        end_date: endDate
                    });
            
                    const result = await apiCall(`?${queryParams}`);
            
                    // 即使API返回success: false，也可能有数据
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
                if (currentRestaurant === 'total') {
                    // 为总计模式计算汇总数据
                    const filteredData = getFilteredKPIData();
                    if (filteredData.length > 0) {
                        return {
                            total_gross_sales: filteredData.reduce((sum, item) => sum + item.totalSales, 0),
                            total_net_sales: filteredData.reduce((sum, item) => sum + item.netSales, 0),
                            total_tables: filteredData.reduce((sum, item) => sum + item.tablesUsed, 0),
                            total_diners: filteredData.reduce((sum, item) => sum + item.diners, 0),
                            total_returning_customers: filteredData.reduce((sum, item) => sum + item.returningCustomers, 0),
                            total_new_customers: filteredData.reduce((sum, item) => sum + item.newCustomers, 0),
                            total_days: filteredData.length
                        };
                    }
                    return {};
                } else {
                    // 单店模式：尝试从API获取汇总，如果失败则前端计算
                    try {
                        const queryParams = new URLSearchParams({
                            action: 'summary',
                            restaurant: currentRestaurant,
                            start_date: startDate,
                            end_date: endDate
                        });
                
                        const result = await apiCall(`?${queryParams}`);
                
                        // 如果API有汇总数据就用API的，否则前端计算
                        if (result.success && result.data) {
                            return result.data;
                        } else {
                            // 前端计算汇总
                            const filteredData = getFilteredKPIData();
                            if (filteredData.length > 0) {
                                return {
                                    total_gross_sales: filteredData.reduce((sum, item) => sum + item.totalSales, 0),
                                    total_net_sales: filteredData.reduce((sum, item) => sum + item.netSales, 0),
                                    total_tables: filteredData.reduce((sum, item) => sum + item.tablesUsed, 0),
                                    total_diners: filteredData.reduce((sum, item) => sum + item.diners, 0),
                                    total_returning_customers: filteredData.reduce((sum, item) => sum + item.returningCustomers, 0),
                                    total_new_customers: filteredData.reduce((sum, item) => sum + item.newCustomers, 0),
                                    total_days: filteredData.length
                                };
                            }
                        }
                    } catch (error) {
                        console.error('API汇总失败，使用前端计算:', error);
                        // API失败时使用前端计算
                        const filteredData = getFilteredKPIData();
                        if (filteredData.length > 0) {
                            return {
                                total_gross_sales: filteredData.reduce((sum, item) => sum + item.totalSales, 0),
                                total_net_sales: filteredData.reduce((sum, item) => sum + item.netSales, 0),
                                total_tables: filteredData.reduce((sum, item) => sum + item.tablesUsed, 0),
                                total_diners: filteredData.reduce((sum, item) => sum + item.diners, 0),
                                total_returning_customers: filteredData.reduce((sum, item) => sum + item.returningCustomers, 0),
                                total_new_customers: filteredData.reduce((sum, item) => sum + item.newCustomers, 0),
                                total_days: filteredData.length
                            };
                        }
                    }
            
                    return {};
                }
            } catch (error) {
                console.error('加载汇总数据失败:', error);
                return {};
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
                // 清空显示
                document.getElementById('total-sales').textContent = '--';
                document.getElementById('net-sales').textContent = '--';
                document.getElementById('total-tables').textContent = '--';
                document.getElementById('total-diners').textContent = '--';
                document.getElementById('avg-per-diner').textContent = '--';
                document.getElementById('date-info').textContent = '请先选择餐厅';
                return;
            }

            console.log('初始化后的日期范围:', dateRange);
    
            // 初始化主题色
            updateThemeColors(currentRestaurant);
    
            await loadData();
            updateDashboard();
        }

        // 数据转换和过滤
        function convertToKPIFormat(data) {
            return data.map(item => {
                const diners = parseInt(item.diners) || 0;
                const returningCustomers = parseInt(item.returning_customers) || 0;
                const newCustomers = parseInt(item.new_customers) || 0;
                const totalCustomers = returningCustomers + newCustomers;

                // 计算净销售额：总销售额 - 折扣 (匹配 edit 页面的计算逻辑)
                const grossSales = parseFloat(item.gross_sales) || 0;
                const discounts = parseFloat(item.discounts) || 0;
                const netSales = item.net_sales ? parseFloat(item.net_sales) : (grossSales - discounts);

                return {
                    date: item.date,
                    totalSales: parseFloat(item.tender_amount) || 0, // 使用 tender_amount 作为总销售额
                    netSales: netSales,
                    diners: diners,
                    tablesUsed: parseInt(item.tables_used) || 0,
                    returningCustomers: returningCustomers,
                    newCustomers: newCustomers,
                    // 人均消费基于净销售额计算
                    avgSalesPerDiner: diners > 0 ? netSales / diners : 0,
                    returningRate: totalCustomers > 0 ? (returningCustomers / totalCustomers) * 100 : 0,
                    newCustomersRate: totalCustomers > 0 ? (newCustomers / totalCustomers) * 100 : 0
                };
            });
        }

        function getFilteredKPIData() {
            const kpiData = convertToKPIFormat(actualData);
            return kpiData.filter(item => {
                const itemDate = new Date(item.date);
                const start = new Date(dateRange.startDate);
                const end = new Date(dateRange.endDate);
                return itemDate >= start && itemDate <= end;
            }).sort((a, b) => new Date(a.date) - new Date(b.date));
        }

        // 更新仪表板
        async function updateDashboard() {
            const summary = await loadSummary(dateRange.startDate, dateRange.endDate);
            const filteredData = getFilteredKPIData();
    
            // 使用前端数据重新计算精确的汇总统计 (匹配 edit 页面的计算逻辑)
            let displaySummary;
            if (filteredData.length > 0) {
                displaySummary = {
                    total_gross_sales: filteredData.reduce((sum, item) => sum + item.totalSales, 0), // totalSales 现在正确对应 tender_amount
                    total_net_sales: filteredData.reduce((sum, item) => sum + item.netSales, 0),
                    total_tables: filteredData.reduce((sum, item) => sum + item.tablesUsed, 0),
                    total_diners: filteredData.reduce((sum, item) => sum + item.diners, 0),
                    total_returning_customers: filteredData.reduce((sum, item) => sum + item.returningCustomers, 0),
                    total_new_customers: filteredData.reduce((sum, item) => sum + item.newCustomers, 0),
                    total_days: filteredData.length
                };
                // 重新计算真正的平均每人消费 (基于净销售额，匹配 edit 页面逻辑)
                displaySummary.avg_per_diner = displaySummary.total_diners > 0 ? 
                    displaySummary.total_net_sales / displaySummary.total_diners : 0;
            } else {
                // 如果没有过滤数据，使用API数据但重新计算平均值
                displaySummary = {
                    total_gross_sales: parseFloat(summary.total_gross_sales || 0),
                    total_net_sales: parseFloat(summary.total_net_sales || 0),
                    total_tables: parseInt(summary.total_tables || 0),
                    total_diners: parseInt(summary.total_diners || 0),
                    total_returning_customers: parseInt(summary.total_returning_customers || 0),
                    total_new_customers: parseInt(summary.total_new_customers || 0),
                    total_days: parseInt(summary.total_days || 0)
                };
                // 重新计算平均每人消费，基于净销售额而不是总销售额
                displaySummary.avg_per_diner = displaySummary.total_diners > 0 ? 
                    displaySummary.total_net_sales / displaySummary.total_diners : 0;
            }
    
            // 更新KPI卡片 (显示格式与 edit 页面保持一致)
            document.getElementById('total-sales').textContent = `${parseFloat(displaySummary.total_gross_sales || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('net-sales').textContent = `${parseFloat(displaySummary.total_net_sales || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('total-tables').textContent = (displaySummary.total_tables || 0).toLocaleString();
            document.getElementById('total-diners').textContent = (displaySummary.total_diners || 0).toLocaleString();
            document.getElementById('avg-per-diner').textContent = `${parseFloat(displaySummary.avg_per_diner || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    
            // 更新日期信息
            document.getElementById('date-info').textContent = `已选择 ${displaySummary.total_days || 0} 天的数据 - ${restaurantConfig[currentRestaurant].name}`;
    
            // 更新图表标题
            const chartTitle = document.getElementById('main-chart-title');
            if (isDrillDownMode) {
                if (currentRestaurant === 'total') {
                    chartTitle.textContent = `净销售额趋势 - (三店合计)`;
                } else {
                    chartTitle.textContent = `净销售额趋势`;
                }
            } else {
                if (currentRestaurant === 'total') {
                    chartTitle.textContent = '净销售额趋势 (三店合计)';
                } else {
                    chartTitle.textContent = '净销售额趋势';
                }
            }
    
            // 更新图表
            updateCharts(filteredData);
    
            // 更新详细表格
            updateDashboardTable(filteredData);

            // 新增：更新图表日期范围显示
            updateChartDateRange();
        }

        // 准备分餐厅对比数据
        function prepareComparisonData() {
            if (currentRestaurant !== 'total' || !allRestaurantsData) {
                return null;
            }
            
            const dateSet = new Set();
            Object.values(allRestaurantsData).forEach(data => {
                data.forEach(item => dateSet.add(item.date));
            });
            
            const sortedDates = Array.from(dateSet).sort();
            const filteredDates = sortedDates.filter(date => {
                const itemDate = new Date(date);
                const start = new Date(dateRange.startDate);
                const end = new Date(dateRange.endDate);
                return itemDate >= start && itemDate <= end;
            });
            
            const restaurants = ['j1', 'j2', 'j3'];
            const comparisonData = {
                dates: filteredDates,
                restaurants: {}
            };
            
            restaurants.forEach(restaurant => {
                const restaurantData = allRestaurantsData[restaurant] || [];
                comparisonData.restaurants[restaurant] = filteredDates.map(date => {
                    const dayData = restaurantData.find(item => item.date === date);
                    if (dayData) {
                        const grossSales = parseFloat(dayData.gross_sales) || 0;
                        const discounts = parseFloat(dayData.discounts) || 0;
                        const netSales = dayData.net_sales ? parseFloat(dayData.net_sales) : (grossSales - discounts);
                        const tenderAmount = parseFloat(dayData.tender_amount) || 0;
    
                        return {
                            totalSales: grossSales,
                            netSales: netSales,
                            diners: parseInt(dayData.diners) || 0,
                            tablesUsed: parseInt(dayData.tables_used) || 0,
                            returningCustomers: parseInt(dayData.returning_customers) || 0,
                            newCustomers: parseInt(dayData.new_customers) || 0
                        };
                    } else {
                        return {
                            totalSales: 0,
                            netSales: 0,
                            diners: 0,
                            tablesUsed: 0,
                            returningCustomers: 0,
                            newCustomers: 0
                        };
                    }
                });
            });
            
            return comparisonData;
        }

        // 8. 修改updateCharts函数
        function updateCharts(data) {
            const ctx1 = document.getElementById('sales-chart').getContext('2d');
            const config = restaurantConfig[currentRestaurant];

            // 根据日期范围决定数据聚合方式
            const aggregatedData = aggregateDataByPeriod(data, dateRange);
            const isMonthlyView = aggregatedData !== data;

            // 餐厅颜色配置
            const restaurantColors = {
                j1: { 
                    primary: '#583e04', 
                    secondary: '#805906',
                    returning: '#583e04',
                    new: '#805906'
                },
                j2: { 
                    primary: '#d97706', 
                    secondary: '#f59e0b',
                    returning: '#d97706',
                    new: '#f59e0b'
                },
                j3: { 
                    primary: '#dc2626', 
                    secondary: '#f87171',
                    returning: '#dc2626',
                    new: '#f87171'
                }
            };

            // 销售趋势图
            if (salesChart) {
                salesChart.destroy();
            }

            if (currentRestaurant === 'total') {
                // 总计模式：显示三间餐厅的对比数据
                const comparisonData = prepareMonthlyComparisonData();

                const chartLabels = comparisonData.isMonthly ? 
                    comparisonData.dates : 
                    comparisonData.dates.map(date => new Date(date).getDate().toString());

                // 获取数据标签
                const dataLabels = {
                    netSales: ['J1 净销售额', 'J2 净销售额', 'J3 净销售额'],
                    tables: ['J1 桌子数量', 'J2 桌子数量', 'J3 桌子数量'],
                    returningRate: ['J1 常客', 'J2 常客', 'J3 常客'],
                    diners: ['J1 人数', 'J2 人数', 'J3 人数']
                };

                salesChart = new Chart(ctx1, {
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
                        onClick: function(event, elements) {
                            // 钻取逻辑保持不变
                            if (!isDrillDownMode && isMonthlyView && elements.length > 0) {
                                const elementIndex = elements[0].index;

                                if (currentRestaurant === 'total') {
                                    const comparisonData = prepareMonthlyComparisonData();
                                    if (comparisonData && comparisonData.isMonthly) {
                                        const monthDisplay = comparisonData.dates[elementIndex];
                                        const match = monthDisplay.match(/(\d{4})年(\d+)月/);
                                        if (match) {
                                            const year = match[1];
                                            const month = String(match[2]).padStart(2, '0');
                                            const monthKey = `${year}-${month}`;
                                            enterDrillDownMode(monthKey, monthDisplay);
                                        }
                                    }
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
                                                case 'netSales':
                                                    const totalSales = j1Data.netSales + j2Data.netSales + j3Data.netSales;
                                                    summaryText = `总净销售额: RM ${totalSales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                                    break;
                                                case 'tables':
                                                    const j1Tables = j1Data.returningCustomers + j1Data.newCustomers;
                                                    const j2Tables = j2Data.returningCustomers + j2Data.newCustomers;
                                                    const j3Tables = j3Data.returningCustomers + j3Data.newCustomers;
                                                    const totalTables = j1Tables + j2Tables + j3Tables;
                                                    summaryText = `桌子数量: ${totalTables}桌`;
                                                    break;
                                                case 'returningRate':
                                                    const totalReturningCustomers = j1Data.returningCustomers + j2Data.returningCustomers + j3Data.returningCustomers;
                                                    const totalAllCustomers = (j1Data.returningCustomers + j1Data.newCustomers) + 
                                                                            (j2Data.returningCustomers + j2Data.newCustomers) + 
                                                                            (j3Data.returningCustomers + j3Data.newCustomers);
                                                    const totalReturningRate = totalAllCustomers > 0 ? ((totalReturningCustomers / totalAllCustomers) * 100).toFixed(2) : '0.0';
                                                    summaryText = `常客：${totalReturningCustomers} (${totalReturningRate}%)`;
                                                    break;
                                                case 'diners':
                                                    const totalDiners = j1Data.diners + j2Data.diners + j3Data.diners;
                                                    summaryText = `人数: ${totalDiners}人`;
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
                    netSales: '净销售额',
                    tables: '桌子数量',
                    returningRate: '常客百分比',
                    diners: '人数'
                };

                salesChart = new Chart(ctx1, {
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
                        onClick: function(event, elements) {
                            // 钻取逻辑保持不变
                            if (!isDrillDownMode && isMonthlyView && elements.length > 0) {
                                const elementIndex = elements[0].index;
                                const item = aggregatedData[elementIndex];
                                if (item.date.includes('-')) {
                                    const monthKey = item.date;
                                    const monthDisplay = item.displayDate;
                                    enterDrillDownMode(monthKey, monthDisplay);
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        if (context.length > 0) {
                                            const dataIndex = context[0].dataIndex;
                                            const item = aggregatedData[dataIndex];
                                            if (isMonthlyView) {
                                                return item.displayDate;
                                            } else {
                                                return `${item.date} (${new Date(item.date).getDate()}号)`;
                                            }
                                        }
                                        return '';
                                    },
                                    label: getTooltipFormatter(currentChartDataType),
                                    afterBody: function(context) {
                                        // 单店模式不显示汇总信息
                                        return [];
                                    }
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
            
            // 更新表头（总计模式下添加标识）
            const tableHeader = document.getElementById('table-header');
            const firstHeader = tableHeader.querySelector('th');
            if (currentRestaurant === 'total') {
                firstHeader.textContent = '日期 (三店合计)';
            } else {
                firstHeader.textContent = '日期';
            }
            
            // 显示所有选择的数据，而不是限制为10条
            data.forEach(item => {
                const totalCustomers = item.returningCustomers + item.newCustomers;
                const returningRate = totalCustomers > 0 ? ((item.returningCustomers / totalCustomers) * 100).toFixed(2) : 0;
                const newCustomersRate = totalCustomers > 0 ? ((item.newCustomers / totalCustomers) * 100).toFixed(2) : 0;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.date}</td>
                    <td>RM ${item.totalSales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>RM ${item.netSales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>RM ${item.avgSalesPerDiner.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${item.tablesUsed}</td>
                    <td>${item.diners}</td>
                    <td>${item.newCustomers}</td>
                    <td>${item.returningCustomers}</td>
                    <td>${returningRate}%</td>
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
    
            // 如果数据跨度超过60天，按月聚合；否则按天显示
            if (daysDiff > 60) {
                return aggregateByMonth(data);
            } else {
                return data; // 按天显示
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
                        totalSales: 0,
                        netSales: 0,
                        diners: 0,
                        tablesUsed: 0,
                        returningCustomers: 0,
                        newCustomers: 0,
                        daysCount: 0
                    });
                }
        
                const monthData = monthMap.get(monthKey);
                monthData.totalSales += item.totalSales;
                monthData.netSales += item.netSales;
                monthData.diners += item.diners;
                monthData.tablesUsed += item.tablesUsed;
                monthData.returningCustomers += item.returningCustomers;
                monthData.newCustomers += item.newCustomers;
                monthData.daysCount += 1;
            });
    
            // 转换为数组并计算平均值
            return Array.from(monthMap.values()).map(item => ({
                ...item,
                avgSalesPerDiner: item.diners > 0 ? item.netSales / item.diners : 0,
                returningRate: (item.returningCustomers + item.newCustomers) > 0 ? 
                    (item.returningCustomers / (item.returningCustomers + item.newCustomers)) * 100 : 0,
                newCustomersRate: (item.returningCustomers + item.newCustomers) > 0 ? 
                    (item.newCustomers / (item.returningCustomers + item.newCustomers)) * 100 : 0
            })).sort((a, b) => a.date.localeCompare(b.date));
        }

        // 为总计模式准备对比数据（与单店模式一致的聚合逻辑）
        function prepareMonthlyComparisonData() {
            if (currentRestaurant !== 'total' || !allRestaurantsData) {
                return null;
            }
    
            const restaurants = ['j1', 'j2', 'j3'];
            const restaurantDataConverted = {};
    
            // 先转换每个餐厅的数据格式
            restaurants.forEach(restaurant => {
                const restaurantData = allRestaurantsData[restaurant] || [];
                restaurantDataConverted[restaurant] = convertToKPIFormat(restaurantData);
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
    
            // 判断是否需要按月聚合（与单店模式相同逻辑）
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
                        j1: months.map(monthKey => aggregatedData.j1.find(item => item.date === monthKey) || createEmptyDataPoint()),
                        j2: months.map(monthKey => aggregatedData.j2.find(item => item.date === monthKey) || createEmptyDataPoint()),
                        j3: months.map(monthKey => aggregatedData.j3.find(item => item.date === monthKey) || createEmptyDataPoint())
                    },
                    isMonthly: true
                };
            } else {
                // 按天显示
                return {
                    dates: filteredDates,
                    restaurants: {
                        j1: filteredDates.map(date => filteredRestaurantData.j1.find(item => item.date === date) || createEmptyDataPoint()),
                        j2: filteredDates.map(date => filteredRestaurantData.j2.find(item => item.date === date) || createEmptyDataPoint()),
                        j3: filteredDates.map(date => filteredRestaurantData.j3.find(item => item.date === date) || createEmptyDataPoint())
                    },
                    isMonthly: false
                };
            }
        }

        // 创建空数据点的辅助函数
        function createEmptyDataPoint() {
            return {
                totalSales: 0,
                netSales: 0,
                diners: 0,
                tablesUsed: 0,
                returningCustomers: 0,
                newCustomers: 0,
                avgSalesPerDiner: 0
            };
        }
    </script>
    <script>
        // 快速选择下拉菜单控制
        function toggleQuickSelectDropdown() {
            const dropdown = document.getElementById('quick-select-dropdown');
    
            // 关闭其他所有下拉菜单
            hideAllDropdowns();
    
            // 切换当前下拉菜单
            dropdown.classList.toggle('show');
        }

        // 快速选择时间范围
        async function selectQuickRange(range) {
            const today = new Date();
            let startDate, endDate;

            // ... 现有的 switch 语句保持不变 ...
            switch(range) {
                case 'today':
                    // 今天
                    startDate = new Date(today);
                    endDate = new Date(today);
                    break;
                
                case 'yesterday':
                    // 昨天
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    
                    startDate = yesterday;
                    endDate = yesterday;
                    break;

                case 'thisWeek':
                    // 本周（周一到今天）
                    const thisWeekStart = new Date(today);
                    const dayOfWeek = thisWeekStart.getDay();
                    const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
                    thisWeekStart.setDate(thisWeekStart.getDate() - daysToMonday);
            
                    startDate = thisWeekStart;
                    endDate = new Date(today);
                    break;
            
                case 'lastWeek':
                    // 上周（上周一到上周日）
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
                    // 这个月（本月1号到今天）
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today);
                    break;
            
                case 'lastMonth':
                    // 上个月
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            
                    startDate = lastMonth;
                    endDate = lastMonthEnd;
                    break;
            
                case 'thisYear':
                    // 今年
                    startDate = new Date(today.getFullYear(), 0, 1);
                    endDate = new Date(today);
                    break;
            
                case 'lastYear':
                    // 去年
                    startDate = new Date(today.getFullYear() - 1, 0, 1);
                    endDate = new Date(today.getFullYear() - 1, 11, 31);
                    break;
            
                default:
                    return;
            }

            // 格式化日期为 YYYY-MM-DD 格式
            const formatDate = (date) => {
                return date.getFullYear() + '-' + 
                    String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(date.getDate()).padStart(2, '0');
            };

            // 更新日期范围
            dateRange = {
                startDate: formatDate(startDate),
                endDate: formatDate(endDate)
            };

            // 更新开始和结束日期选择器的值
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

            // 重置月份选择器（因为我们现在使用的是自定义范围）
            monthDateValue = {
                year: null,
                month: null
            };

            // 更新所有日期选择器的显示
            updateDateDisplay('start');
            updateDateDisplay('end');
            updateDateDisplay('month');

            // 更新按钮显示文本
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

            // 关闭下拉菜单
            document.getElementById('quick-select-dropdown').classList.remove('show');

            // 新增：退出钻取模式
            if (isDrillDownMode) {
                isDrillDownMode = false;
                drillDownMonth = null;
                originalDateRange = null;
                hideBackButtons();
            }

            // 只有选择了餐厅才加载数据
            if (isRestaurantSelected) {
                await loadData({
                    start_date: dateRange.startDate,
                    end_date: dateRange.endDate
                });
                updateDashboard();
            }
            
            // 更新图表日期范围显示
            updateChartDateRange();
        }

        // 修改现有的document.addEventListener，添加快速选择下拉菜单的关闭逻辑
        document.addEventListener('click', function(e) {
            // 关闭日期选择器下拉菜单
            if (!e.target.closest('.enhanced-date-picker')) {
                hideAllDropdowns();
            }
    
            // 关闭餐厅数字选择下拉菜单
            if (!e.target.closest('.number-dropdown')) {
                document.getElementById('number-dropdown').classList.remove('show');
            }
    
            // 关闭快速选择下拉菜单
            if (!e.target.closest('.dropdown')) {
                document.getElementById('quick-select-dropdown').classList.remove('show');
            }
        });

        // 获取范围描述文本的辅助函数
        function getRangeDescription(range) {
            const descriptions = {
                'today': '今天',
                'yesterday': '昨天',
                'thisWeek': '本周',
                'lastWeek': '上周', 
                'thisMonth': '这个月',
                'lastMonth': '上个月',
                'thisYear': '今年',
                'lastYear': '去年'
            };
            return descriptions[range] || '自定义范围';
        }
    </script>
    <script>
        // 切换下拉菜单
        function toggleRestaurantDropdown() {
            const dropdown = document.getElementById('restaurant-dropdown');
            dropdown.classList.toggle('show');
        }

        // 修改餐厅下拉菜单关闭事件
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.restaurant-selector')) {
                const dropdown = document.getElementById('restaurant-dropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                    hideNumberOptions(); // 关闭下拉菜单时隐藏数字选项
                }
            }
        });

        // 选择餐厅数字或总计
        function selectNumber(value) {
            const numberBtn = document.querySelector('.number-btn');
    
            if (value === 'total') {
                numberBtn.innerHTML = `总计 <i class="fas fa-chevron-down"></i>`;
                // 切换到总计
                switchRestaurant('total');
            } else {
                numberBtn.innerHTML = `${value} <i class="fas fa-chevron-down"></i>`;
                // 切换餐厅
                const restaurant = `j${value}`;
                switchRestaurant(restaurant);
            }
    
            // 关闭下拉菜单
            document.getElementById('number-dropdown').classList.remove('show');
        }

        // 更新选中的数字状态
        function updateSelectedNumber() {
            const currentNumber = currentRestaurant === 'total' ? 'total' : currentRestaurant.replace('j', '');
            document.querySelectorAll('.number-item').forEach(item => {
                item.classList.remove('selected');
                if (item.textContent === currentNumber || (currentNumber === 'total' && item.textContent === '总计')) {
                    item.classList.add('selected');
                }
            });
        }

        // 点击外部关闭下拉菜单
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.number-dropdown')) {
                document.getElementById('number-dropdown').classList.remove('show');
            }
        });
    </script>
    <script>
        // 当前选择的字母和数字
        let currentLetter = null;
        let currentNumber = null;
        let isRestaurantSelected = false;

        // 显示数字选项
        function showNumberOptions(letter) {
            currentLetter = letter;
    
            // 更新字母选择状态
            document.querySelectorAll('.letter-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.querySelector(`[onclick*="'${letter}'"]`).classList.add('selected');
    
            const numberSelection = document.getElementById('number-selection');
            const sectionTitle = numberSelection.querySelector('.section-title');
            const numberGrid = numberSelection.querySelector('.number-grid');

            // 更新标题
            sectionTitle.textContent = `选择${letter}分店`;

            // 清空现有选项
            numberGrid.innerHTML = '';

            if (letter === 'J') {
                // J有1、2、3和总计选项
                numberGrid.innerHTML = `
                    <button class="number-item" onclick="selectRestaurant('1')">1</button>
                    <button class="number-item" onclick="selectRestaurant('2')">2</button>
                    <button class="number-item" onclick="selectRestaurant('3')">3</button>
                    <button class="number-item total-option" onclick="selectRestaurant('total')">总</button>
                `;
            } else if (letter === 'K') {
                // K只有1、2和总计选项
                numberGrid.innerHTML = `
                    <button class="number-item" onclick="selectRestaurant('1')">1</button>
                    <button class="number-item" onclick="selectRestaurant('2')">2</button>
                    <button class="number-item total-option" onclick="selectRestaurant('total')">总</button>
                `;
            }

            // 显示数字选择区域
            numberSelection.style.visibility = 'visible';
            numberSelection.style.opacity = '1';
        }

        // 选择具体餐厅
        async function selectRestaurant(number) {
            currentNumber = number;
            isRestaurantSelected = true;
    
            if (number === 'total') {
                currentRestaurant = 'total';
                updateRestaurantButton(`${currentLetter}总计`);
            } else {
                currentRestaurant = `${currentLetter.toLowerCase()}${number}`;
                updateRestaurantButton(`${currentLetter}${number}`);
            }
    
            // 关闭下拉菜单
            document.getElementById('restaurant-dropdown').classList.remove('show');
    
            // 更新主题颜色
            updateThemeColors(currentRestaurant);
    
            // 现在加载数据
            await loadData();
            updateDashboard();
        }

        // 选择字母
        function selectLetter(letter) {
            showNumberOptions(letter);
        }

        // 隐藏数字选项
        function hideNumberOptions() {
            const numberSelection = document.getElementById('number-selection');
            const sectionTitle = numberSelection.querySelector('.section-title');
            const numberGrid = numberSelection.querySelector('.number-grid');
    
            // 隐藏数字选择区域
            numberSelection.style.visibility = 'hidden';
            numberSelection.style.opacity = '0';
    
            // 重置标题和内容
            sectionTitle.textContent = '选择餐厅';
            numberGrid.innerHTML = '';
    
            // 移除字母选择状态
            document.querySelectorAll('.letter-item').forEach(item => {
                item.classList.remove('selected');
            });
    
            currentLetter = null;
        }

        // 修改现有的selectNumber函数
        function selectNumber(value) {
            currentNumber = value;

            // 更新数字选择状态
            document.querySelectorAll('.number-item').forEach(item => {
                item.classList.remove('selected');
                if (!item.classList.contains('total-option') && parseInt(item.textContent) === value) {
                    item.classList.add('selected');
                }
            });

            // 更新按钮显示
            updateRestaurantButton();

            // 切换餐厅
            const restaurant = `${currentLetter.toLowerCase()}${value}`;
            switchRestaurant(restaurant);

            // 关闭下拉菜单
            document.getElementById('restaurant-dropdown').classList.remove('show');
        }

        function selectTotal() {
            currentNumber = 'total';

            // 更新数字选择状态
            document.querySelectorAll('.number-item').forEach(item => {
                item.classList.remove('selected');
                if (item.textContent === '总计') {
                    item.classList.add('selected');
                }
            });

            // 更新按钮显示
            updateRestaurantButton();

            // 切换到总计
            switchRestaurant('total');

            // 关闭下拉菜单
            document.getElementById('restaurant-dropdown').classList.remove('show');
        }

        // 更新餐厅按钮显示
        function updateRestaurantButton(text) {
            const restaurantBtn = document.querySelector('.restaurant-btn');
            restaurantBtn.innerHTML = `${text} <i class="fas fa-chevron-down"></i>`;
        }

        // 进入钻取模式
        async function enterDrillDownMode(monthKey, monthDisplay) {
            console.log('进入钻取模式:', monthKey, monthDisplay);
    
            // 保存原始日期范围
            originalDateRange = { ...dateRange };
    
            // 设置钻取状态
            isDrillDownMode = true;
            drillDownMonth = monthDisplay;
    
            // 计算该月的日期范围
            const [year, month] = monthKey.split('-');
            const firstDay = `${year}-${month}-01`;
            const lastDay = new Date(parseInt(year), parseInt(month), 0).getDate();
            const lastDayFormatted = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
    
            // 更新日期范围为该月
            dateRange = {
                startDate: firstDay,
                endDate: lastDayFormatted
            };
    
            // 更新日期选择器显示
            startDateValue = {
                year: parseInt(year),
                month: parseInt(month),
                day: 1
            };
    
            endDateValue = {
                year: parseInt(year),
                month: parseInt(month),
                day: lastDay
            };
    
            updateDateDisplay('start');
            updateDateDisplay('end');
    
            // 重新加载数据
            await loadData({
                start_date: dateRange.startDate,
                end_date: dateRange.endDate
            });
    
            // 更新仪表板
            updateDashboard();
    
            // 显示返回按钮
            showBackButtons();

            // 新增：更新图表日期范围显示
            updateChartDateRange();
        }

        // 退出钻取模式
        async function exitDrillDown() {
            console.log('退出钻取模式');
    
            // 恢复原始状态
            isDrillDownMode = false;
            drillDownMonth = null;
    
            // 恢复原始日期范围
            if (originalDateRange) {
                dateRange = { ...originalDateRange };
        
                // 恢复日期选择器
                const startDate = new Date(dateRange.startDate);
                const endDate = new Date(dateRange.endDate);
        
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
        
                updateDateDisplay('start');
                updateDateDisplay('end');
        
                originalDateRange = null;
            }
    
            // 重新加载数据
            await loadData({
                start_date: dateRange.startDate,
                end_date: dateRange.endDate
            });
    
            // 更新仪表板
            updateDashboard();
    
            // 隐藏返回按钮
            hideBackButtons();

            // 新增：更新图表日期范围显示
            updateChartDateRange();
        }

        // 显示返回按钮
        function showBackButtons() {
            document.querySelectorAll('.chart-back-button').forEach(button => {
                if (button.id === 'sales-chart-back') {  // 只显示主图表的返回按钮
                    button.style.display = 'flex';
                    button.textContent = `返回年度视图`;
                    button.innerHTML = '<i class="fas fa-arrow-left"></i> 返回年度视图';
                }
            });
        }

        function hideBackButtons() {
            document.querySelectorAll('.chart-back-button').forEach(button => {
                if (button.id === 'sales-chart-back') {  // 只隐藏主图表的返回按钮
                    button.style.display = 'none';
                }
            });
        }
    </script>
    <script>
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
            
            // 如果是同一天，只显示一个日期
            if (dateRange.startDate === dateRange.endDate) {
                chartDateRange.textContent = startDateFormatted;
            } else {
                chartDateRange.textContent = `${startDateFormatted} 至 ${endDateFormatted}`;
            }
        }
    </script>
    <script>
        function switchChartData(dataType) {
            currentChartDataType = dataType;
            
            // 更新按钮状态
            document.querySelectorAll('.chart-data-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-type="${dataType}"]`).classList.add('active');
            
            // 更新图表标题
            const chartTitle = document.getElementById('main-chart-title');
            const titles = {
                netSales: '净销售额趋势',
                tables: '桌子数量趋势',
                returningRate: '常客百分比趋势',
                diners: '人数趋势'
            };
            
            let titleText = titles[dataType];
            if (currentRestaurant === 'total') {
                titleText += ' (三店合计)';
            }
            chartTitle.textContent = titleText;
            
            // 重新绘制图表
            const filteredData = getFilteredKPIData();
            updateCharts(filteredData);
        }

        // 5. 获取图表数据的辅助函数
        function getChartDataByType(item, dataType) {
            switch(dataType) {
                case 'netSales':
                    return item.netSales;
                case 'tables':
                    return item.returningCustomers + item.newCustomers; // 桌子总数
                case 'returningRate':
                    const totalCustomers = item.returningCustomers + item.newCustomers;
                    return totalCustomers > 0 ? (item.returningCustomers / totalCustomers) * 100 : 0;
                case 'diners':
                    return item.diners;
                default:
                    return item.netSales;
            }
        }

        // 6. 获取Y轴标签格式化函数
        function getYAxisFormatter(dataType) {
            switch(dataType) {
                case 'netSales':
                    return function(value) {
                        return 'RM ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    };
                case 'tables':
                    return function(value) {
                        return value + '桌';
                    };
                case 'returningRate':
                    return function(value) {
                        return value.toFixed(2) + '%';
                    };
                case 'diners':
                    return function(value) {
                        return value + '人';
                    };
                default:
                    return function(value) {
                        return value.toString();
                    };
            }
        }

        function getTooltipFormatter(dataType) {
            switch(dataType) {
                case 'netSales':
                    return function(context) {
                        return context.dataset.label + ': RM ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    };
                case 'tables':
                    return function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + '桌';
                    };
                case 'returningRate':
                    return function(context) {
                        if (currentRestaurant === 'total') {
                            // 总计模式下需要显示具体常客人数
                            const dataIndex = context.dataIndex;
                            const comparisonData = prepareMonthlyComparisonData();
                            
                            let restaurantData;
                            if (context.dataset.label.includes('J1')) {
                                restaurantData = comparisonData.restaurants.j1[dataIndex];
                            } else if (context.dataset.label.includes('J2')) {
                                restaurantData = comparisonData.restaurants.j2[dataIndex];
                            } else if (context.dataset.label.includes('J3')) {
                                restaurantData = comparisonData.restaurants.j3[dataIndex];
                            }
                            
                            if (restaurantData) {
                                const returningCustomers = restaurantData.returningCustomers;
                                const percentage = context.parsed.y.toFixed(2);
                                return context.dataset.label + ': ' + returningCustomers + ' (' + percentage + '%)';
                            }
                        } else {
                            // 单店模式下也显示具体常客人数
                            const dataIndex = context.dataIndex;
                            const filteredData = getFilteredKPIData();
                            const aggregatedData = aggregateDataByPeriod(filteredData, dateRange);
                            const item = aggregatedData[dataIndex];
                            
                            if (item) {
                                const returningCustomers = item.returningCustomers;
                                const percentage = context.parsed.y.toFixed(2);
                                return '常客：' + returningCustomers + ' (' + percentage + '%)';
                            }
                        }
                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                    };
                case 'diners':
                    return function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + '人';
                    };
                default:
                    return function(context) {
                        return context.dataset.label + ': ' + context.parsed.y;
                    };
            }
        }
    </script>           
</body>
</html>