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
            overflow-y: hidden;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            height: 100vh;
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

        /* 系统选择器样式 */
        .system-selector {
            position: relative;
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
            transition: all 0.2s;
            width: clamp(80px, 6.77vw, 130px);
            justify-content: space-between;
        }
        
        .selector-button:hover {
            background-color: #f98500ff;
            border-radius: 8px;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .selector-dropdown {
            position: absolute;
            top: 96%;
            right: 0;
            background: white;
            border: 2px solid #000000ff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.2);
            width: 100%;
            z-index: 1000;
            display: none;
            margin-top: 4px;
        }

        .selector-dropdown.show {
            display: block;
        }

        .dropdown-item {
            padding: clamp(6px, 0.42vw, 8px) clamp(10px, 0.83vw, 16px);
            cursor: pointer;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.2s;
            color: #000000ff;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f8f5eb;
            border-radius: 8px;
        }

        .dropdown-item.active {
            background-color: #f99e00;
            color: white;
            border-radius: 4px;
        }

        .back-button {
            background-color: #6b7280;
            color: white;
            font-weight: 500;
            padding: 13px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            margin-left: 16px;
        }
        
        .back-button:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.2);
        }

        /* Alert Messages */
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

        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* 搜索和过滤区域 */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 24px 40px;
            margin-bottom: 24px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            height: 130px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #583e04;
        }

        .filter-input, .filter-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #583e04;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 3px rgba(88, 62, 4, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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
            background-color: #f99300;
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

        .btn-warning {
            background-color: #f99e00;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            margin-top: 0px;
            border-radius: clamp(4px, 0.42vw, 8px);
            border: none;
            cursor: pointer;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            flex-shrink: 0;
            white-space: nowrap;
        }
        
        .btn-warning:hover {
            background-color: #f98500ff;
            transform: translateY(-1px);
        }

        .summary-card {
            background: white;
            padding: 12px 24px;
            border-radius: 12px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            transition: transform 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.15);
        }

        .summary-card h3 {
            color: #583e04;
            font-size: 30px;
            font-weight: 600;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #583e04;
        }

        .summary-card.total-value .value {
            color: #583e04;
        }

        /* 货币显示容器 */
        .currency-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            box-sizing: border-box;
            font-size: 14px;
            width: 100%;
        }

        .currency-display .currency-symbol {
            color: #6b7280;
            font-weight: 500;
            text-align: left;
            flex-shrink: 0;
        }

        .currency-display .currency-amount {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            color: #000000ff;
            text-align: right;
            flex-shrink: 0;
        }

        .currency-symbol {
            font-size: clamp(8px, 0.74vw, 14px);
            color: #6b7280;
        }

        .stock-table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .stock-table th {
            background: #636363;
            color: white;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 100;
            white-space: nowrap;
            min-width: 80px;
        }

        /* 确保表格头部完全遮盖滚动的数据 */
        .table-scroll-container {
            position: relative;
        }

        .stock-table thead {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #000000ff;
        }

        .stock-table thead tr {
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* 确保表格在容器内正确显示 */
        .stock-table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin: 0;
        }

        /* 确保表格容器高度计算正确 */
        .main-content-row + .table-container {
            height: calc(102vh - 300px); /* 根据页面布局调整 */
            min-height: 400px;
        }

        .stock-table td {
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            font-size: clamp(8px, 0.74vw, 14px);
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: middle;
        }

        .stock-table tr:nth-child(even) {
            background-color: white;
        }

        .stock-table tr:hover {
            background-color: #e5ebf8ff;
        }

        /* 响应式表格列宽 */
        .stock-table th:nth-child(1), .stock-table td:nth-child(1) { width: 8%; }   /* No. */
        .stock-table th:nth-child(2), .stock-table td:nth-child(2) { width: 12%; }  /* Code Number */
        .stock-table th:nth-child(3), .stock-table td:nth-child(3) { width: 25%; }  /* Product Name */
        .stock-table th:nth-child(4), .stock-table td:nth-child(4) { width: 15%; }  /* Total Stock */
        .stock-table th:nth-child(5), .stock-table td:nth-child(5) { width: 12%; }  /* Specification */
        .stock-table th:nth-child(6), .stock-table td:nth-child(6) { width: 14%; }  /* Unit Price */
        .stock-table th:nth-child(7), .stock-table td:nth-child(7) { width: 14%; }  /* Total Price */

        .table-container {
            background: white;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.1);
            border: 2px solid #000000ff;
            overflow: visible;
            display: flex;
            flex-direction: column;
            max-height: 69vh; /* 设置最大高度 */
        }

        .table-scroll-container {
            overflow-x: hidden;
            overflow-y: auto;
            flex: 1;
            position: relative;
        }

        /* 操作按钮 */
        .action-buttons {
            padding: 14px 24px;
            background: #f8f5eb;
            border-top: 2px solid #583e04;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        /* 统计信息 */
        .stats-info {
            display: flex;
            gap: 4px;
            align-items: center;
            font-size: 14px;
            color: #6b7280;
            flex-wrap: wrap;
            margin-right: -50px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 150px;
        }

        .stat-value {
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: bold;
            color: #583e04;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
        }

        .positive-value {
            color: #10b981;
            font-weight: 600;
        }

        .zero-value {
            color: #6b7280;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }

        .no-data i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

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

        .total-row {
            background: #f8f5eb !important;
            border-top: 2px solid #000000ff;
            font-weight: 600;
            color: #000000ff;
        }

        /* 总库存价值专用的货币显示样式 */
        .summary-currency-display {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .summary-currency-display .currency-symbol {
            font-size: 32px;
            font-weight: bold;
            color: #583e04;
        }

        .summary-currency-display .value {
            font-size: 32px;
            font-weight: 700;
            color: #10b981;
        }

        /* 主要内容行布局 */
        .main-content-row {
            display: flex;
            gap: 24px;
            margin-bottom: 14px;
            align-items: stretch;
        }

        /* 左侧总库存区域 */
        .summary-section {
            flex: 0 0 400px;
            min-width: 400px;
            display: flex;
            flex-direction: column;
        }

        /* 右侧搜索过滤区域 */
        .filter-section {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* 总库存卡片样式调整 */
        .summary-section .summary-card {
            width: 100%;
            margin-bottom: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* 价格单元格样式 */
        .stock-table td.price-cell {
            padding: 0;
            text-align: left;
        }

        .stock-table td.stock-cell {
            padding: 0;
            text-align: center;
        }

        .stock-cell .currency-display {
            justify-content: center;
        }

        /* 确保价格单元格内容填满 */
        .price-cell .currency-display {
            width: 100%;
            margin: 0;
        }

        /* 价格分析专用样式 */
        .product-group {
            background: white;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            overflow: hidden;
        }

        .product-header {
            background: #583e04;
            color: white;
            padding: 16px 24px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-header .price-count {
            font-size: 14px;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .price-variants-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed;
        }

        .price-variants-table th {
            background: #f8f5eb;
            color: #583e04;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #583e04;
        }

        .price-variants-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }

        .price-variants-table th:nth-child(1) { width: 10%; }
        .price-variants-table th:nth-child(2) { width: 30%; }
        .price-variants-table th:nth-child(3) { width: 30%; }
        .price-variants-table th:nth-child(4) { width: 30%; }

        .price-variants-table td:nth-child(1) { width: 10%; }
        .price-variants-table td:nth-child(2) { width: 30%; }
        .price-variants-table td:nth-child(3) { width: 30%; }
        .price-variants-table td:nth-child(4) { width: 30%; }

        .price-variants-table tr:hover {
            background-color: #f9fafb;
        }

        .highest-price {
            background-color: #fef3c7 !important;
            font-weight: 600;
        }

        .highest-price .currency-amount {
            color: #dc2626;
            font-weight: 700;
        }

        /* 页面切换 */
        .page-section {
            display: none;
        }

        .page-section.active {
            display: block;
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

        /* 视图选择器样式 */
        .view-selector {
            position: relative;
            margin-right: 16px;
        }

        /* .view-selector .selector-button {
            background-color: #583e04;
            min-width: 80px;
        } */

        .view-selector .selector-button:hover {
            background-color: #f98500ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        .view-selector .selector-dropdown {
            width: 100%;
        }

        .low-stock-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease-out;
        }

        .low-stock-modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border: none;
            border-radius: 12px;
            width: clamp(500px, 41.67vw, 800px);
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease-out;
        }

        .low-stock-modal-header {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            padding: clamp(10px, 1.04vw, 20px) clamp(18px, 1.25vw, 24px);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .low-stock-modal-header h2 {
            font-size: clamp(14px, 1.25vw, 24px);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: clamp(18px, 1.25vw, 24px);
            cursor: pointer;
            padding: 0;
            width: clamp(20px, 1.56vw, 30px);
            height: clamp(20px, 1.56vw, 30px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .close-modal:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .low-stock-modal-body {
            padding: clamp(10px, 1.15vw, 22px) clamp(18px, 1.25vw, 24px);
            height: clamp(58vh, 30vw, 60vh);
            overflow-y: auto;
        }

        .low-stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .low-stock-table th,
        .low-stock-table td {
            padding: clamp(8px, 0.63vw, 12px);
            font-size: clamp(8px, 0.84vw, 16px);
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .low-stock-table th {
            background-color: #f9fafb;
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 600;
            color: #374151;
        }

        .low-stock-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .stock-critical {
            color: #dc2626;
            font-weight: 700;
        }

        .stock-warning {
            color: #d97706;
            font-weight: 700;
        }

        .modal-footer {
            padding: clamp(10px, 1.04vw, 20px) 24px;
            background-color: #f9fafb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
        }

        .alert-summary {
            color: #6b7280;
            font-size: clamp(8px, 0.74vw, 14px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .low-stock-row {
            background-color: #fab9b9ff !important;
            color: #991b1b !important;
        }

        .low-stock-row:hover {
            background-color: #fa9999ff !important;
        }

        .low-stock-row td {
            color: #991b1b !important;
            font-weight: 500;
        }

        .low-stock-row .currency-amount {
            color: #991b1b !important;
            font-weight: 600;
        }

        .low-stock-row .currency-symbol {
            color: #991b1b !important;
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

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .main-content-row {
                flex-direction: column;
                gap: 16px;
            }
            
            .summary-section {
                flex: none;
                width: 100%;
                min-width: auto;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
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

            .selector-dropdown {
                right: auto;
                left: 0;
            }

            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 16px;
            }
        }

        .search-row {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 24px;
        }

        .search-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .search-group label {
            font-size: 12px;
            font-weight: 600;
            color: #583e04;
            white-space: nowrap;
            margin-bottom: 5px;
        }

        .unified-search-input {
            padding: clamp(4px, 0.42vw, 8px) 12px;
            border: 1px solid #d1d5db;
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(8px, 0.74vw, 14px);
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .unified-search-input:focus {
            outline: none;
            border-color: #000000ff;
            box-shadow: 0 0 10px rgba(31, 14, 0, 0.8);
        }

        .unified-search-input::placeholder {
            color: #9ca3af;
        }

        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }
        }

        /* 统一顶部行样式 */
        .unified-header-row {
            display: flex;
            align-items: center;
            gap: 0px;
            padding: clamp(8px, 0.83vw, 16px) clamp(18px, 1.25vw, 24px);
            background: white;
            border-radius: 12px;
            margin-bottom: clamp(14px, 1.25vw, 24px);
            border: 2px solid #000000ff;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
            flex-wrap: nowrap;
            justify-content: space-between; /* 添加这行，让内容两端对齐 */
        }

        .header-summary {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex-shrink: 0;
            margin-right: 0px;
        }

        .summary-title {
            font-size: clamp(14px, 1.5vw, 28px);
            font-weight: 600;
            color: #000000ff;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .summary-amount {
            display: flex;
            align-items: baseline;
            gap: clamp(0px, 0.31vw, 6px);
        }

        .summary-amount .currency-symbol {
            font-size: clamp(18px, 1.88vw, 36px);
            font-weight: bold;
            color: #000000ff;
        }

        .summary-amount .value {
            font-size: clamp(18px, 1.88vw, 36px);
            margin-left: 0px;
            font-weight: 700;
            color: #000000ff;
        }

        .header-search {
            flex: 1;
            min-width: 50px;
            display: flex;
            align-items: center; /* 改为水平对齐 */
            gap: clamp(6px, 0.63vw, 12px); /* 添加间距 */
        }

        .header-stats {
            margin-top: 0px;
            display: flex;
            gap: clamp(8px, 0.83vw, 16px);
            font-size: clamp(6px, 0.63vw, 12px);
            color: #6b7280;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .header-right-section {
            margin-top: clamp(18px, 1.83vw, 35px);
            display: flex;
            align-items: center;
            gap: clamp(8px, 1.04vw, 20px);
            margin-left: auto;
        }

        .header-stats .stat-value {
            font-weight: bold;
            color: #000000ff;
        }

        /* 田字格容器 */
        .type-grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: 1fr;
            gap: clamp(8px, 0.83vw, 16px);
            padding: clamp(8px, 0.83vw, 16px);
            background: white;
            border-radius: 12px;
            width: clamp(350px, 36.46vw, 700px);
        }

        .type-grid-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4px clamp(8px, 0.83vw, 16px);
            background: #ffffffff;
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }

        .type-grid-item .grid-title {
            font-size: clamp(10px, 1vw, 18px);
            font-weight: 600;
            color: #000000ff;
            margin-bottom: 0px;
        }

        .type-grid-item .grid-value {
            font-size: clamp(12px, 1.25vw, 24px);
            font-weight: 700;
            color: #000000ff;
        }

        .type-grid-item .grid-value.negative {
            color: #dc2626;
        }
    </style>
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
                        <div class="grid-title">Drinks</div>
                        <div class="grid-value" id="j3-drinks-value">0.00</div>
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
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #583e04; white-space: nowrap;">搜索</span>
                    <input type="text" id="j3-unified-filter" class="unified-search-input" 
                        placeholder="搜索货品名称、编号或规格单位...">
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

    <script>
        // 全局状态（仅保留 J3）
        let currentSystem = 'j3';
        let stockData = { j3: [] };
        let filteredData = { j3: [] };
        let isLoading = { j3: false };

        // API配置（仅 J3）
        const API_CONFIG = { j3: 'j3stocklistapi.php' };

        // 初始化应用
        function initApp() {
            // 启动会话自动刷新
            startSessionRefresh();
            // 仅加载 J3 数据
            loadData('j3');
            // 实时搜索监听器
            setupRealTimeSearch();
        }

        // 设置实时搜索
        function setupRealTimeSearch() {
            const searchInput = document.getElementById('j3-unified-filter');
            if (searchInput) {
                let debounceTimer;
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        searchData('j3');
                    }, 300);
                });
            }
        }

        // 已移除系统/视图切换逻辑，页面仅保留 J3
        function toggleViewSelector() {
            const dd = document.getElementById('view-selector-dropdown');
            if (dd) dd.classList.toggle('show');
        }

        function switchView(view, e) {
            if (e) e.stopPropagation();
            if (view === 'records') {
                window.location.href = 'j3stockinoutpage.php';
                return;
            }
            if (view === 'product') {
                window.location.href = 'stockproductname.php';
                return;
            }
            // list = 当前页
            document.getElementById('current-view').textContent = '总库存';
            document.querySelectorAll('#view-selector-dropdown .dropdown-item').forEach(i => i.classList.remove('active'));
            const dd = document.getElementById('view-selector-dropdown');
            if (dd) dd.classList.remove('show');
        }

        // 点击外部关闭下拉（仅视图选择器）
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.view-selector')) {
                const vd = document.getElementById('view-selector-dropdown');
                if (vd) vd.classList.remove('show');
            }
        });

        // 返回仪表盘
        function goBack() {
            window.location.href = 'dashboard.php';
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
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.innerHTML = `
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

        // API调用函数
        async function apiCall(system, endpoint, options = {}) {
            try {
                const baseUrl = API_CONFIG[system];
                const response = await fetch(`${baseUrl}${endpoint}`, {
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
                
                // 检查是否是会话过期
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

        // 修改 loadData 函数
        async function loadData(system) {
            if (system !== 'j3') system = 'j3';
            if (isLoading.j3) return;
            isLoading.j3 = true;
            setLoadingState('j3', true);
            try {
                const result = await apiCall('j3', '?action=summary');
                if (result.success) {
                    stockData.j3 = result.data.summary || [];
                    updateSummaryCards('j3', result.data);
                    filteredData.j3 = [...stockData.j3];
                    renderStockTable('j3');
                    updateStats('j3');
                    if (stockData.j3.length === 0) {
                        showAlert('当前没有J3数据', 'info');
                    }
                } else {
                    stockData.j3 = [];
                    filteredData.j3 = [];
                    showAlert('获取数据失败: ' + (result.message || '未知错误'), 'error');
                    renderStockTable('j3');
                }
            } catch (error) {
                stockData.j3 = [];
                filteredData.j3 = [];
                console.error('Error:', error);
                renderStockTable('j3');
            } finally {
                isLoading.j3 = false;
                setLoadingState('j3', false);
            }
        }

        // 已移除低库存设置与检查逻辑

        // 实时搜索数据
        function searchData(system) {
            const searchTerm = document.getElementById(`${system}-unified-filter`).value.toLowerCase();

            filteredData[system] = stockData[system].filter(item => {
                // 搜索所有字段，包括序号、货品编号、货品名称、库存数量、规格、单价、总价
                return (
                    (item.no && item.no.toString().includes(searchTerm)) ||
                    (item.product_name && item.product_name.toLowerCase().includes(searchTerm)) ||
                    (item.code_number && item.code_number.toLowerCase().includes(searchTerm)) ||
                    (item.total_stock && item.total_stock.toString().includes(searchTerm)) ||
                    (item.specification && item.specification.toLowerCase().includes(searchTerm)) ||
                    (item.price && item.price.toString().includes(searchTerm)) ||
                    (item.total_price && item.total_price.toString().includes(searchTerm)) ||
                    (item.formatted_total_price && item.formatted_total_price.includes(searchTerm))
                );
            });

            renderStockTable(system);
            updateStats(system);
        }

        // 已移除价格分析（remark）相关逻辑

        // 重置搜索过滤器
        function resetFilters(system) {
            if (system !== 'j3') system = 'j3';
            const input = document.getElementById('j3-unified-filter');
            if (input) input.value = '';
            filteredData.j3 = [...stockData.j3];
            renderStockTable('j3');
            updateStats('j3');
            showAlert('搜索条件已重置', 'info');
        }

        // 设置加载状态
        function setLoadingState(system, loading) {
            const tbody = document.getElementById(`${system}-stock-tbody`);
            if (loading && tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center;">
                            <div class="loading"></div>
                            <div style="margin-top: 16px; color: #6b7280;">正在加载数据...</div>
                        </td>
                    </tr>
                `;
            }
        }

        // 更新汇总卡片
        function updateSummaryCards(system, data) {
            document.getElementById(`${system}-total-value`).textContent = data.formatted_total_value || '0.00';
            
            // 更新 J3 类型统计（若返回）
            if (data.type_stats) {
                const drinksEl = document.getElementById(`${system}-drinks-value`);
                const sakeEl = document.getElementById(`${system}-sake-value`);
                const kitchenEl = document.getElementById(`${system}-kitchen-value`);
                const sushiBarEl = document.getElementById(`${system}-sushi-bar-value`);
                
                // 更新数值并检查是否为负数
                if (drinksEl) {
                    drinksEl.textContent = data.type_stats.formatted_drinks || '0.00';
                    drinksEl.classList.toggle('negative', data.type_stats.drinks < 0);
                }
                if (sakeEl) {
                    sakeEl.textContent = data.type_stats.formatted_sake || '0.00';
                    sakeEl.classList.toggle('negative', data.type_stats.sake < 0);
                }
                if (kitchenEl) {
                    kitchenEl.textContent = data.type_stats.formatted_kitchen || '0.00';
                    kitchenEl.classList.toggle('negative', data.type_stats.kitchen < 0);
                }
                if (sushiBarEl) {
                    sushiBarEl.textContent = data.type_stats.formatted_sushi_bar || '0.00';
                    sushiBarEl.classList.toggle('negative', data.type_stats.sushi_bar < 0);
                }
            }
        }

        // 更新统计信息
        function updateStats(system) {
            const displayedRecords = filteredData[system].length;
            const totalRecords = stockData[system].length;
            
            document.getElementById(`${system}-displayed-records`).textContent = displayedRecords;
            document.getElementById(`${system}-total-records`).textContent = totalRecords;
        }

        // 替换现有的 renderStockTable 函数（仅 J3）
        function renderStockTable(system) {
            const tbody = document.getElementById(`${system}-stock-tbody`);
            
            if (filteredData[system].length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无数据</div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            let totalValue = 0;
            let tableRows = '';
            
            filteredData[system].forEach((item, index) => {
                const stockValue = parseFloat(item.total_stock) || 0;
                const priceValue = parseFloat(item.total_price) || 0;
                const stockClass = stockValue > 0 ? 'positive-value' : 'zero-value';
                const priceClass = priceValue > 0 ? 'positive-value' : 'zero-value';
                
                let rowClass = '';
                
                tableRows += `
                    <tr class="${rowClass}">
                        <td class="text-center">${item.no}</td>
                        <td class="text-center">${item.code_number || '-'}</td>
                        <td><strong>${item.product_name}</strong></td>
                        <td class="stock-cell">
                            <div class="currency-display ${stockClass}">
                                <span class="currency-symbol">&nbsp;</span>
                                <span class="currency-amount">${item.formatted_stock}</span>
                            </div>
                        </td>
                        <td class="text-center">${item.specification || '-'}</td>
                        <td class="price-cell">
                            <div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${item.formatted_price}</span>
                            </div>
                        </td>
                        <td class="price-cell">
                            <div class="currency-display ${priceClass}">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${item.formatted_total_price}</span>
                            </div>
                        </td>
                    </tr>
                `;
                totalValue += priceValue;
            });
            
            // 添加总计行
            tableRows += `
                <tr class="total-row">
                    <td colspan="6" class="text-right" style="font-size: clamp(10px, 0.84vw, 16px); padding-right: 15px; text-align: right;">总计:</td>
                    <td class="price-cell positive-value" style="font-size: 16px;">
                        <div class="currency-display">
                            <span class="currency-symbol">RM</span>
                            <span class="currency-amount">${formatCurrency(totalValue)}</span>
                        </div>
                    </td>
                </tr>
            `;
            
            tbody.innerHTML = tableRows;
        }

        // 已移除 remark 渲染相关函数

        // 格式化货币 - 统一显示两位小数
        function formatCurrency(value) {
            if (!value || value === '' || value === '0') return '0.00';
            const num = parseFloat(value);
            if (isNaN(num)) return '0.00';
            
            // 直接格式化为两位小数显示
            return num.toFixed(2);
        }

        // 刷新数据
        function refreshData(system) {
            loadData(system);
        }

        // 导出数据
        function exportData(system) {
            if (system !== 'j3') system = 'j3';
            if (filteredData[system].length === 0) {
                showAlert('没有数据可导出', 'error');
                return;
            }
            
            try {
                let csvContent, fileName;
                // 库存汇总导出（仅 J3）
                const headers = ['No.', 'Product Name', 'Code Number', 'Total Stock', 'Specification', 'Unit Price', 'Total Price'];
                csvContent = headers.join(',') + '\n';
                filteredData[system].forEach(item => {
                    const row = [
                        item.no,
                        `"${item.product_name}"`,
                        item.code_number || '',
                        item.formatted_stock,
                        item.specification || '',
                        item.formatted_price,
                        item.formatted_total_price
                    ];
                    csvContent += row.join(',') + '\n';
                });
                fileName = `${system}_stock_summary_${new Date().toISOString().split('T')[0]}.csv`;
                
                // 创建下载链接
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', fileName);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('数据导出成功', 'success');
            } catch (error) {
                showAlert('导出失败', 'error');
            }
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
            }, 70000);
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

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);
        
        // 页面卸载时停止会话刷新
        window.addEventListener('beforeunload', function() {
            stopSessionRefresh();
        });

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

        // 键盘快捷键支持（仅 J3）
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const filter = document.getElementById('j3-unified-filter');
                if (filter) filter.focus();
            }
            if (e.key === 'Escape') {
                resetFilters('j3');
            }
            if (e.ctrlKey && e.key === 'Home') {
                e.preventDefault();
                scrollToTop();
            }
        });

        // 定时刷新数据（每5分钟）
        setInterval(() => {
            if (!document.hidden) {
                loadData('j3');
            }
        }, 300000);

        // 已移除低库存预警弹窗相关逻辑
    </script>
</body>
</html>