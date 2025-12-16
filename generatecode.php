<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>生成申请码管理系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            min-height: 100vh;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: clamp(10px, 1.56vw, 30px);
            position: relative;
        }

        .header h1 {
            color: #000000ff;
            font-size: clamp(20px, 2.6vw, 50px);
            margin-bottom: 10px;
            text-align: left;
        }

        .header h1::after {
            content: "";
            display: block;
            height: 3px;
            width: 100%;
            margin-top: 16px;
            background: linear-gradient(90deg, rgba(255,92,0,0) 0%, rgba(0, 0, 0, 1) 25%, rgba(0, 0, 0, 1) 75%, rgba(255,92,0,0) 100%);
        }

        .header p {
            color: #ff5c00;
            font-size: 17px;
        }

        .back-button {
            background-color: #6b7280;
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
            position: absolute;
            top: 135px;
            right: 0;
        }

        .back-button:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.2);
        }

        /* 生成代码表单样式 */
        .generate-form {
            background: transparent;
            border-radius: 15px;
            justify-items: normal;
        }

        .form-title {
            color: #E65100;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #ff5c00;
            padding-bottom: 12px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            align-items: end;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #BF360C;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ff5c00;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff5c00;
            box-shadow: 0 0 10px rgba(255, 115, 0, 0.8);
        }

        /* 修改后的模态框样式 - 匹配图片设计 */
        #addUserModal .modal-content,
        #editUserModal .modal-content {
            max-width: 800px !important;
            width: clamp(400px, 41.67vw, 800px) !important;
            height: 90vh;
            overflow-y: auto;
            padding: clamp(18px, 1.56vw, 30px) clamp(18px, 1.56vw, 30px) clamp(0px, 1.56vw, 30px);
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin: 3% auto !important;
        }

        #addUserModal .modal-header,
        #editUserModal .modal-header {
            font-size: clamp(12px, 1.04vw, 20px);
            font-weight: 600;
            margin-bottom: clamp(14px, 1.56vw, 30px);
            text-align: center;
            padding-bottom: 0;
            border-bottom: none;
        }

        /* 表单区块样式（绿主题，卡片化） */
        .form-section {
            margin-bottom: clamp(10px, 1.04vw, 20px);
            border: 1px solid #000000ff;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .form-section-header {
            padding: clamp(6px, 0.52vw, 10px) clamp(10px, 0.73vw, 14px);
            border-bottom: 1px solid #ffddaa;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 700;
            text-align: left;
            color: white;
            background: #f99e00;
            border-radius: 8px 8px 0 0;
        }

        .form-section-content {
            padding: clamp(2px, 0.21vw, 4px) clamp(10px, 0.73vw, 14px) clamp(12px, 0.94vw, 18px);
        }

        /* 表单组基础样式 - 重新设计 */
        #addUserModal .form-group,
        #editUserModal .form-group {
            margin-bottom: 0px;
            display: flex;
            flex-direction: column;
        }

        /* 标签样式 - 更清晰对齐 */
        #addUserModal .form-group label,
        #editUserModal .form-group label {
            display: block;
            margin-bottom: clamp(0px, 0.26vw, 5px);
            color: #000000ff;
            font-weight: 600;
            font-size: clamp(8px, 0.74vw, 14px);
            text-align: left;
        }

        /* 必填字段标签样式 */
        #addUserModal .form-group label[for*="username"]:first-child::after,
        #addUserModal .form-group label[for*="email"]::after,
        #addUserModal .form-group label[for*="account_type"]::after,
        #editUserModal .form-group label[for*="username"]:first-child::after,
        #editUserModal .form-group label[for*="email"]::after,
        #editUserModal .form-group label[for*="account_type"]::after {
            content: " *";
            color: #ef4444;
            font-weight: bold;
        }

        /* 输入框和选择框基础样式 - 绿主题 */
        #addUserModal .form-group input,
        #addUserModal .form-group select,
        #addUserModal .form-group textarea,
        #editUserModal .form-group input,
        #editUserModal .form-group select,
        #editUserModal .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #bfbfbf;
            border-radius: 6px;
            font-size: clamp(8px, 0.74vw, 14px);
            font-family: inherit;
            background-color: #ffffff;
            color: black;
            transition: all 0.2s ease;
            min-height: 0px;
        }

        /* 输入框聚焦样式 - 绿主题 */
        #addUserModal .form-group input:focus,
        #addUserModal .form-group select:focus,
        #addUserModal .form-group textarea:focus,
        #editUserModal .form-group input:focus,
        #editUserModal .form-group select:focus,
        #editUserModal .form-group textarea:focus {
            outline: none;
            border-color: #d16500ff;
            box-shadow: 0 0 0 3px rgba(253, 206, 135, 0.15);
            background-color: #ffffff;
        }

        /* 局部覆盖全局 style.css，统一本页输入控件尺寸 */
        .generate-form input[type="text"],
        .generate-form input[type="email"],
        .generate-form input[type="tel"],
        .generate-form input[type="date"],
        #addUserModal .form-group input[type="text"],
        #addUserModal .form-group input[type="email"],
        #addUserModal .form-group input[type="tel"],
        #addUserModal .form-group input[type="date"],
        #addUserModal .form-group select,
        #editUserModal .form-group input[type="text"],
        #editUserModal .form-group input[type="email"],
        #editUserModal .form-group input[type="tel"],
        #editUserModal .form-group input[type="date"],
        #editUserModal .form-group select,
        #searchInput {
            height: clamp(20px, 1.87vw, 36px) !important;
            padding: clamp(0px, 0.42vw, 8px) clamp(8px, 0.63vw, 12px) !important;
            box-sizing: border-box !important;
            line-height: 1.2 !important;
        }

        /* 保持文本域独立高度 */
        #addUserModal .form-group textarea,
        #editUserModal .form-group textarea {
            min-height: 30px !important;
            height: auto !important;
        }

        /* 文本域特殊样式 */
        #addUserModal .form-group textarea,
        #editUserModal .form-group textarea {
            min-height: 60px;
            resize: vertical;
            font-family: inherit;
        }

        /* 单列布局 */
        .form-row-1col {
            display: block;
        }

        /* 双列布局样式 */
        .form-row-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* 三列布局样式 */
        .form-row-3col {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
        }

        /* 搜索框特殊样式 */
        #searchInput {
            transition: all 0.3s ease;
            width: clamp(160px, 13vw, 260px);
        }

        #searchInput:focus {
            outline: none;
            border-color: #000000ff !important;
            box-shadow: 0 0 10px rgba(31, 14, 0, 0.8) !important;
        }

        #searchInput::placeholder {
            color: #999;
            font-style: italic;
        }

        /* 高亮搜索结果 */
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        /* 隐藏不匹配的行 */
        .hidden-row {
            display: none !important;
        }

        .btn-generate {
            background: #f99e00;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-generate:hover {
            background: #f98500ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        /* 消息提示样式 */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .message.success {
            background: #C8E6C9;
            color: #2E7D32;
            border: 2px solid #4CAF50;
        }

        .message.error {
            background: #FFCDD2;
            color: #C62828;
            border: 2px solid #F44336;
        }

        /* 表格样式 */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 2px solid #000000ff;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .table-title {
            background: #ffffffff;
            color: #000000ff;
            padding: clamp(12px, 1.04vw, 20px);
            font-size: clamp(12px, 1.04vw, 20px);
            font-weight: bold;
            text-align: center;
        }

        .table-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d1d5db;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            background: #636363;
            color: white;
            padding: clamp(8px, 0.8vw, 15px) 0px;
            text-align: center;
            font-weight: bold;
            font-size: clamp(8px, 0.74vw, 14px);
            border: 1px solid #d1d5db;
            position: sticky;
            top: 0;
        }

        th:first-child {
            text-align: center;
        }

        th:last-child {
            text-align: center;
        }

        /* 设置各列的宽度 */
        th:nth-child(1), td:nth-child(1) { width: 60px; }      /* 序号 */
        th:nth-child(2), td:nth-child(2) { width: 120px; }     /* 职位 */
        th:nth-child(3), td:nth-child(3) { width: 220px; }     /* 英文姓名 */
        th:nth-child(4), td:nth-child(4) { width: 410px; }     /* 邮箱 */
        th:nth-child(5), td:nth-child(5) { width: 120px; }     /* 联络号码 */
        th:nth-child(6), td:nth-child(6) { width: 80px; }     /* 操作 */

        /* 当地址列显示"-"时居中对齐 */
        td:nth-child(13) em {
            display: block;
            text-align: center;
            width: 100%;
        }

        td {
            padding: clamp(0px, 0.31vw, 6px) 6px;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            border: 1px solid #d1d5db;
            vertical-align: middle;
            text-align: center;
        }

        /* 地址列编辑状态下的样式 */
        td:nth-child(13) .edit-input {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box;
            word-wrap: break-word;
            white-space: pre-wrap;
            resize: vertical;
            min-height: 60px;
            font-family: inherit;
            font-size: 12px;
        }

        /* 表格行悬停效果 - 保持边框 */
        tr:hover {
            background: #fff9f1;
            transition: all 0.2s ease;
        }

        /* 状态标签样式 */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14.4px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }

        .status-used {
            background: #C8E6C9;
            color: #2E7D32;
        }

        .status-unused {
            background: #FFE0B2;
            color: #E65100;
        }

        .account-type-badge {
            padding: 4px 0px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .header h1 {
                font-size: 40px;
            }

            .generate-form {
                padding: 20px;
            }

            th, td {
                padding: 8px 6px;
                font-size: 14.4px;
            }

            /* 响应式模态框 - 移动端 */
            #addUserModal .modal-content,
            #editUserModal .modal-content {
                max-width: 95% !important;
                width: 95% !important;
                margin: 5% auto;
                padding: 20px;
            }

            /* 移动端所有多列布局都变为单列 */
            .form-row-2col,
            .form-row-3col {
                grid-template-columns: 1fr !important;
                gap: 15px;
            }
        }

        /* 加载动画 */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #FFE0B2;
            border-radius: 50%;
            border-top-color: #FF9800;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* 操作按钮样式 */
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: center;
            align-items: center;
            flex-wrap: nowrap;
            width: 100%;
        }

        .btn-action {
            padding: 0;
            border: none;
            border-radius: 6px;
            font-size: clamp(8px, 0.63vw, 12px);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: clamp(24px, 1.7vw, 32px);
            height: clamp(24px, 1.7vw, 32px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: none;
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
        }

        .btn-edit:hover {
            background: #d97706;
            transform: translateY(-1px);
        }

        .btn-save {
            background: #10b981;
            color: white;
        }

        .btn-save:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
        }

        .btn-cancel:hover {
            background: #6b7280;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* 编辑模式下的输入框 */
        .edit-input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #2196F3;
            border-radius: 4px;
            font-size: 12px;
            font-family: inherit;
            background: #f8f9fa;
            box-sizing: border-box;
        }

        .edit-select {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #2196F3;
            border-radius: 4px;
            font-size: 14px;
            background: #f8f9fa;
            box-sizing: border-box;
        }

        .edit-input:focus,
        .edit-select:focus {
            outline: none;
            border-color: #1976D2;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
        }

        /* 确认删除模态框 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-body {
            margin-bottom: 25px;
            color: #333;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .modal-buttons .btn-action {
            flex: 0 0 auto;
            width: clamp(70px, 5.73vw, 110px);
            padding: 8px;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        /* 编辑状态下的行高亮 */
        .editing-row {
            background: #e3f2fd !important;
            box-shadow: 0 0 10px rgba(33, 150, 243, 0.2);
        }

        /* 回到顶部按钮 */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #eb8e02ff;
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
            background-color: #d16003ff;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(88, 62, 4, 0.4);
        }

        .back-to-top:active {
            transform: translateY(-1px);
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
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <!-- 页面标题 -->
        <div class="header">
            <h1>职员管理系统</h1>
        </div>

        <!-- 生成代码表单 -->
        <div class="generate-form">
            
            <div id="messageArea"></div>

            <form id="generateForm">
                <form id="generateForm">
                    <div class="form-row" style="justify-content: space-between; align-items: end;">
                        <!-- 添加新职员按钮 -->
                        <div class="form-group" style="flex: 0 0 auto; margin-right: 20px;">
                            <button type="button" class="btn-generate" onclick="openAddUserModal()" >
                                <i class="fas fa-user-plus"></i> 添加新职员
                            </button>
                        </div>
                        
                        <div class="form-group" style="flex: 0 0 auto; position: relative; display: flex; align-items: center; gap: 10px;">
                            <div style="position: relative;">
                                <input type="text" id="searchInput" placeholder="输入英文姓名或邮箱进行搜索..."
                                    style="padding: 10px 40px 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: clamp(8px, 0.74vw, 14px);">
                                <button type="button" onclick="clearSearch()" 
                                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; font-size: 16px;"
                                        title="清除搜索">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </form>
        </div>

        <!-- 代码和职员列表 -->
        <div class="table-container">
            <div class="table-title">
                职员列表
            </div>
            
            <div class="table-wrapper">
                <table id="codesTable">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>职位</th>
                            <th>英文姓名</th>
                            <th>邮箱</th>
                            <th>联络号码</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">
                                <div class="loading"></div>
                                正在加载数据...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 添加职员模态框 -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="color: #000000ff;">
                <i class="fas fa-user-plus"></i> 添加新职员
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <!-- 基本信息区块 -->
                    <div class="form-section">
                        <div class="form-section-header">基本信息</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="add_username">英文姓名 *</label>
                                    <input type="text" id="add_username" name="username" required maxlength="50">
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_username_cn">中文姓名</label>
                                    <input type="text" id="add_username_cn" name="username_cn" maxlength="100">
                                </div>
                            </div>
                            
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="add_nickname">昵称</label>
                                    <input type="text" id="add_nickname" name="nickname" maxlength="50">
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_email">邮箱 *</label>
                                    <input type="email" id="add_email" name="email" required maxlength="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 个人资料区块 -->
                    <div class="form-section">
                        <div class="form-section-header">个人资料</div>
                        <div class="form-section-content">
                            <div class="form-row-3col">
                                <div class="form-group">
                                    <label for="add_ic_number">身份证号码</label>
                                    <input type="text" id="add_ic_number" name="ic_number" maxlength="20">
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_phone_number">联络号码</label>
                                    <input type="tel" id="add_phone_number" name="phone_number" maxlength="20">
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_date_of_birth">出生日期</label>
                                    <input type="date" id="add_date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                            
                            <div class="form-row-3col">
                                <div class="form-group">
                                    <label for="add_gender">性别</label>
                                    <select id="add_gender" name="gender">
                                        <option value="">请选择</option>
                                        <option value="male">男</option>
                                        <option value="female">女</option>
                                        <option value="other">其他</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_nationality">国籍</label>
                                    <select id="add_nationality" name="nationality">
                                        <option value="">请选择国籍</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Brunei">Brunei</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="China">China</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="East Timor (Timor-Leste)">East Timor (Timor-Leste)</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran">Iran</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Laos">Laos</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Myanmar (Burma)">Myanmar (Burma)</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="North Korea">North Korea</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palestine">Palestine</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="South Korea">South Korea</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Syria">Syria</option>
                                        <option value="Taiwan">Taiwan</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vietnam">Vietnam</option>
                                        <option value="Yemen">Yemen</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_race">种族</label>
                                    <select id="add_race" name="race">
                                        <option value="">请选择种族</option>
                                        <option value="Malay">Malay</option>
                                        <option value="Chinese">Chinese</option>
                                        <option value="Indian">Indian</option>
                                        <option value="Bumiputera (Sabah/Sarawak)">Bumiputera (Sabah/Sarawak)</option>
                                        <option value="Indonesian">Indonesian</option>
                                        <option value="Bangladeshi">Bangladeshi</option>
                                        <option value="Nepali">Nepali</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Filipino">Filipino</option>
                                        <option value="Indian (Foreign)">Indian (Foreign)</option>
                                        <option value="Pakistani">Pakistani</option>
                                        <option value="Vietnamese">Vietnamese</option>
                                        <option value="Cambodian">Cambodian</option>
                                        <option value="Others (Foreign)">Others (Foreign)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row-1col">
                                <div class="form-group">
                                    <label for="add_home_address">住址</label>
                                    <textarea id="add_home_address" name="home_address" rows="2" maxlength="255"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 银行信息区块 -->
                    <div class="form-section">
                        <div class="form-section-header">银行信息</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="add_bank_account_holder_en">银行账户持有人</label>
                                    <input type="text" id="add_bank_account_holder_en" name="bank_account_holder_en" maxlength="50">
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_bank_account">银行账号</label>
                                    <input type="text" id="add_bank_account" name="bank_account" maxlength="30">
                                </div>
                            </div>
                            
                            <div class="form-row-1col">
                                <div class="form-group">
                                    <label for="add_bank_name">银行名称</label>
                                    <select id="add_bank_name" name="bank_name">
                                        <option value="">请选择银行</option>
                                        <option value="Maybank (Malayan Banking Berhad)">Maybank (Malayan Banking Berhad)</option>
                                        <option value="CIMB Bank">CIMB Bank</option>
                                        <option value="Public Bank">Public Bank</option>
                                        <option value="RHB Bank">RHB Bank</option>
                                        <option value="Hong Leong Bank">Hong Leong Bank</option>
                                        <option value="AmBank">AmBank</option>
                                        <option value="Alliance Bank">Alliance Bank</option>
                                        <option value="Affin Bank">Affin Bank</option>
                                        <option value="Bank Islam Malaysia">Bank Islam Malaysia</option>
                                        <option value="Agrobank">Agrobank</option>
                                        <option value="Bank Simpanan Nasional (BSN)">Bank Simpanan Nasional (BSN)</option>
                                        <option value="HSBC Bank Malaysia">HSBC Bank Malaysia</option>
                                        <option value="OCBC Bank (Malaysia)">OCBC Bank (Malaysia)</option>
                                        <option value="Standard Chartered Bank Malaysia">Standard Chartered Bank Malaysia</option>
                                        <option value="United Overseas Bank (UOB Malaysia)">United Overseas Bank (UOB Malaysia)</option>
                                        <option value="Bank of China (Malaysia)">Bank of China (Malaysia)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 紧急联络人区块 -->
                    <div class="form-section">
                        <div class="form-section-header">紧急联络人</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="add_emergency_contact_name">紧急联系人</label>
                                    <input type="text" id="add_emergency_contact_name" name="emergency_contact_name" maxlength="100">
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_emergency_phone_number">紧急联系人电话</label>
                                    <input type="tel" id="add_emergency_phone_number" name="emergency_phone_number" maxlength="20">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 账号设置区块 -->
                    <div class="form-section">
                        <div class="form-section-header">账号设置</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="add_position">职位</label>
                                    <input type="text" id="add_position" name="position" maxlength="100">
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_account_type">账号类型 *</label>
                                    <select id="add_account_type" name="account_type" required>
                                        <option value="">请选择账号类型</option>
                                        <option value="boss">老板 (Boss)</option>
                                        <option value="admin">管理员 (Admin)</option>
                                        <option value="hr">人事部 (HR)</option>
                                        <option value="design">设计部 (Design)</option>
                                        <option value="support">支援部 (Support)</option>
                                        <option value="IT">技术部 (IT)</option>
                                        <option value="photograph">摄影部 (Photography)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="submit" class="btn-action btn-save">
                            添加职员
                        </button>
                        <button type="button" class="btn-action btn-cancel" onclick="closeAddUserModal()">
                            取消
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 编辑职员模态框 -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="color: #f59e0b;">
                <i class="fas fa-user-edit"></i> 编辑职员信息
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <!-- 基本信息区块 -->
                    <div class="form-section">
                        <div class="form-section-header">基本信息</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="edit_username">英文姓名 *</label>
                                    <input type="text" id="edit_username" name="username" required maxlength="50">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_username_cn">中文姓名</label>
                                    <input type="text" id="edit_username_cn" name="username_cn" maxlength="100">
                                </div>
                            </div>
                            
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="edit_nickname">昵称</label>
                                    <input type="text" id="edit_nickname" name="nickname" maxlength="50">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_email">邮箱 *</label>
                                    <input type="email" id="edit_email" name="email" required maxlength="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 个人资料区块 -->
                    <div class="form-section">
                        <div class="form-section-header">个人资料</div>
                        <div class="form-section-content">
                            <div class="form-row-3col">
                                <div class="form-group">
                                    <label for="edit_ic_number">身份证号码</label>
                                    <input type="text" id="edit_ic_number" name="ic_number" maxlength="20">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_phone_number">联络号码</label>
                                    <input type="tel" id="edit_phone_number" name="phone_number" maxlength="20">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_date_of_birth">出生日期</label>
                                    <input type="date" id="edit_date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                            
                            <div class="form-row-3col">
                                <div class="form-group">
                                    <label for="edit_gender">性别</label>
                                    <select id="edit_gender" name="gender">
                                        <option value="">请选择</option>
                                        <option value="male">男</option>
                                        <option value="female">女</option>
                                        <option value="other">其他</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_nationality">国籍</label>
                                    <select id="edit_nationality" name="nationality">
                                        <option value="">请选择国籍</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Brunei">Brunei</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="China">China</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="East Timor (Timor-Leste)">East Timor (Timor-Leste)</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran">Iran</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Laos">Laos</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Myanmar (Burma)">Myanmar (Burma)</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="North Korea">North Korea</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palestine">Palestine</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="South Korea">South Korea</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Syria">Syria</option>
                                        <option value="Taiwan">Taiwan</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vietnam">Vietnam</option>
                                        <option value="Yemen">Yemen</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_race">种族</label>
                                    <select id="edit_race" name="race">
                                        <option value="">请选择种族</option>
                                        <option value="Malay">Malay</option>
                                        <option value="Chinese">Chinese</option>
                                        <option value="Indian">Indian</option>
                                        <option value="Bumiputera (Sabah/Sarawak)">Bumiputera (Sabah/Sarawak)</option>
                                        <option value="Indonesian">Indonesian</option>
                                        <option value="Bangladeshi">Bangladeshi</option>
                                        <option value="Nepali">Nepali</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Filipino">Filipino</option>
                                        <option value="Indian (Foreign)">Indian (Foreign)</option>
                                        <option value="Pakistani">Pakistani</option>
                                        <option value="Vietnamese">Vietnamese</option>
                                        <option value="Cambodian">Cambodian</option>
                                        <option value="Others (Foreign)">Others (Foreign)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row-1col">
                                <div class="form-group">
                                    <label for="edit_home_address">住址</label>
                                    <textarea id="edit_home_address" name="home_address" rows="2" maxlength="255"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 银行信息区块 -->
                    <div class="form-section">
                        <div class="form-section-header">银行信息</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="edit_bank_account_holder_en">银行账户持有人</label>
                                    <input type="text" id="edit_bank_account_holder_en" name="bank_account_holder_en" maxlength="50">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_bank_account">银行账号</label>
                                    <input type="text" id="edit_bank_account" name="bank_account" maxlength="30">
                                </div>
                            </div>
                            
                            <div class="form-row-1col">
                                <div class="form-group">
                                    <label for="edit_bank_name">银行名称</label>
                                    <select id="edit_bank_name" name="bank_name">
                                        <option value="">请选择银行</option>
                                        <option value="Maybank (Malayan Banking Berhad)">Maybank (Malayan Banking Berhad)</option>
                                        <option value="CIMB Bank">CIMB Bank</option>
                                        <option value="Public Bank">Public Bank</option>
                                        <option value="RHB Bank">RHB Bank</option>
                                        <option value="Hong Leong Bank">Hong Leong Bank</option>
                                        <option value="AmBank">AmBank</option>
                                        <option value="Alliance Bank">Alliance Bank</option>
                                        <option value="Affin Bank">Affin Bank</option>
                                        <option value="Bank Islam Malaysia">Bank Islam Malaysia</option>
                                        <option value="Agrobank">Agrobank</option>
                                        <option value="Bank Simpanan Nasional (BSN)">Bank Simpanan Nasional (BSN)</option>
                                        <option value="HSBC Bank Malaysia">HSBC Bank Malaysia</option>
                                        <option value="OCBC Bank (Malaysia)">OCBC Bank (Malaysia)</option>
                                        <option value="Standard Chartered Bank Malaysia">Standard Chartered Bank Malaysia</option>
                                        <option value="United Overseas Bank (UOB Malaysia)">United Overseas Bank (UOB Malaysia)</option>
                                        <option value="Bank of China (Malaysia)">Bank of China (Malaysia)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 紧急联络人区块 -->
                    <div class="form-section">
                        <div class="form-section-header">紧急联络人</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="edit_emergency_contact_name">紧急联系人</label>
                                    <input type="text" id="edit_emergency_contact_name" name="emergency_contact_name" maxlength="100">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_emergency_phone_number">紧急联系人电话</label>
                                    <input type="tel" id="edit_emergency_phone_number" name="emergency_phone_number" maxlength="20">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 账号设置区块 -->
                    <div class="form-section">
                        <div class="form-section-header">账号设置</div>
                        <div class="form-section-content">
                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="edit_position">职位</label>
                                    <input type="text" id="edit_position" name="position" maxlength="100">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_account_type">账号类型 *</label>
                                    <select id="edit_account_type" name="account_type" required>
                                        <option value="">请选择账号类型</option>
                                        <option value="boss">老板 (Boss)</option>
                                        <option value="admin">管理员 (Admin)</option>
                                        <option value="hr">人事部 (HR)</option>
                                        <option value="design">设计部 (Design)</option>
                                        <option value="support">支援部 (Support)</option>
                                        <option value="IT">技术部 (IT)</option>
                                        <option value="photograph">摄影部 (Photography)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="submit" class="btn-action btn-save">
                            <i class="fas fa-save"></i> 保存修改
                        </button>
                        <button type="button" class="btn-action btn-cancel" onclick="closeEditUserModal()">
                            <i class="fas fa-times"></i> 取消
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 回到顶部按钮 -->
    <button class="back-to-top" id="back-to-top-btn" onclick="scrollToTop()" title="回到顶部">
        <i class="fas fa-chevron-up"></i>
    </button>

    <div class="toast-container" id="toast-container">
        <!-- 动态通知内容 -->
    </div>

    <script>
        // 输入格式化和过滤函数
        function formatAndFilterInput(input, field) {
            let value = input.value;
            
            switch(field) {
                case 'username':
                case 'emergency_contact_name':
                case 'bank_account_holder_en':
                case 'position':  // 添加这一行
                    // 只允许大写字母和空格，自动转换为大写
                    value = value.toUpperCase().replace(/[^A-Z\s]/g, '');
                    break;
                    
                case 'username_cn':
                    // 只允许中文字符
                    value = value.replace(/[^\u4e00-\u9fff]/g, '');
                    break;
                    
                case 'email':
                    // 只允许小写字母、数字、@和点号，自动转换为小写
                    value = value.toLowerCase().replace(/[^a-z0-9@.]/g, '');
                    break;
                    
                case 'ic_number':
                case 'phone_number':
                case 'emergency_phone_number':
                case 'bank_account':
                    // 只允许数字
                    value = value.replace(/[^\d]/g, '');
                    break;
                    
                case 'home_address':
                    // 只允许大写字母、数字、空格和常见符号，自动转换为大写
                    value = value.toUpperCase().replace(/[^A-Z0-9\s\.,\-\#\/\(\)]/g, '');
                    break;
            }
            
            input.value = value;
        }

        // 添加实时格式化
        function addInputFormatting(input, field) {
            // 输入时格式化
            input.addEventListener('input', function() {
                formatAndFilterInput(this, field);
            });
            
            // 粘贴时格式化
            input.addEventListener('paste', function(e) {
                setTimeout(() => {
                    formatAndFilterInput(this, field);
                }, 0);
            });
        }

        // 简单验证函数（用于最终提交验证）
        function validateField(field, value) {
            if (!value) return true; // 空值通过验证
            
            switch(field) {
                case 'username':
                case 'emergency_contact_name':
                case 'bank_account_holder_en':
                    // 至少两个单词
                    return /^[A-Z]+(\s[A-Z]+)+$/.test(value);
                    
                case 'username_cn':
                    // 至少两个中文字符
                    return /^[\u4e00-\u9fff]{2,}$/.test(value);
                    
                case 'email':
                    // 必须包含@
                    return /^[a-z0-9]+@[a-z0-9]+\.[a-z0-9]+$/.test(value);
                    
                default:
                    return true;
            }
        }

        // 页面加载时获取数据
        document.addEventListener('DOMContentLoaded', function() {
            loadCodesAndUsers();
            
            // 添加实时搜索功能
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function(e) {
                filterTable(e.target.value);
            });

            // 初始化事件监听器
            rebindEventListeners();
        });

        // 加载代码和职员数据
        async function loadCodesAndUsers() {
            const tableBody = document.getElementById('tableBody');
            
            try {
                const response = await fetch('generatecodeapi.php?action=list');
                const result = await response.json();

                if (result.success) {
                    displayData(result.data);
                } else {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #C62828;">
                                ❌ 加载失败: ${result.message}
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #C62828;">
                            ❌ 网络错误，请检查连接
                        </td>
                    </tr>
                `;
            }
            
            // 添加这段代码来重新绑定事件监听器
            rebindEventListeners();
        }

        // 重新绑定事件监听器
        function rebindEventListeners() {
            // 重新绑定添加职员表单提交事件
            const addUserForm = document.getElementById('addUserForm');
            if (addUserForm) {
                addUserForm.removeEventListener('submit', handleAddUserSubmit);
                addUserForm.addEventListener('submit', handleAddUserSubmit);
            }
            
            // 重新绑定编辑职员表单提交事件
            const editUserForm = document.getElementById('editUserForm');
            if (editUserForm) {
                editUserForm.removeEventListener('submit', handleEditUserSubmit);
                editUserForm.addEventListener('submit', handleEditUserSubmit);
            }
            
            // 重新绑定模态框外部点击关闭事件
            const addUserModal = document.getElementById('addUserModal');
            if (addUserModal) {
                addUserModal.onclick = function(event) {
                    if (event.target === this) {
                        closeAddUserModal();
                    }
                };
            }
            
            // 绑定编辑模态框外部点击关闭事件
            const editUserModal = document.getElementById('editUserModal');
            if (editUserModal) {
                editUserModal.onclick = function(event) {
                    if (event.target === this) {
                        closeEditUserModal();
                    }
                };
            }
        }

        // 提取表单提交处理函数
        function handleAddUserSubmit(e) {
            e.preventDefault();
            addNewUser();
        }

        // 生成6位随机代码（数字字母结合）
        function generateRandomCode() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 6; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        // 返回仪表盘
        function goBack() {
            window.location.href = 'dashboard.php';
        }

        // 显示数据
        function displayData(data) {
            const tableBody = document.getElementById('tableBody');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #666;">
                            📝 暂无数据
                        </td>
                    </tr>
                `;
                return;
            }

            // 定义账号类型的排序顺序
            const typeOrder = {
                'boss': 1,
                'admin': 2,
                'hr': 3, 
                'design': 4,
                'support': 5,
                'IT': 6,
                'photograph': 7
            };
            
            // 按照指定顺序排序数据
            const sortedData = [...data].sort((a, b) => {
                const orderA = typeOrder[a.account_type] || 999;
                const orderB = typeOrder[b.account_type] || 999;
                return orderA - orderB;
            });

            const rows = sortedData.map((item, index) => `
                <tr id="row-${item.id}" data-id="${item.id}" data-user='${JSON.stringify(item).replace(/'/g, "&apos;")}'>
                    <td style="text-align: center; font-weight: bold; color: black;">${index + 1}</td>
                    <td>${item.position || '<em style="color: #999;">-</em>'}</td>
                    <td>${item.username || '<em style="color: #999;">-</em>'}</td>
                    <td>${item.email || '<em style="color: #999;">-</em>'}</td>
                    <td>${item.phone_number || '<em style="color: #999;">-</em>'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" onclick="openEditModal(${item.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="confirmDelete(${item.id}, '${item.username || '未知职员'}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            tableBody.innerHTML = rows;

            // 保存原始数据用于搜索
            originalTableData = sortedData;

            // 如果有搜索词，重新应用过滤
            const searchInput = document.getElementById('searchInput');
            if (searchInput && searchInput.value.trim()) {
                filterTable(searchInput.value);
            }
        }

        // 格式化账号类型
        function formatAccountType(type) {
            const types = {
                'boss': '老板',
                'admin': '管理员',
                'hr': '人事部',
                'design': '设计部',
                'support': '支援部',
                'IT': '技术部',
                'photograph': '摄影部'
            };
            return types[type] || type;
        }

        // 格式化性别
        function formatGender(gender) {
            const genders = {
                'male': '男',
                'female': '女',
                'other': '其他'
            };
            return genders[gender] || gender;
        }

        // 完全替换现有的 showMessage 函数
        function showMessage(message, type = 'success') {
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
            }, 100);

            // 自动关闭
            setTimeout(() => {
                closeToast(toastId);
            }, 800);
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

        // 刷新表格
        function refreshTable() {
            loadCodesAndUsers();
        }

        // 全局变量存储原始数据
        let originalTableData = [];

        // 实时过滤表格（搜索英文姓名和邮箱列）
        function filterTable(searchTerm) {
            const tableBody = document.getElementById('tableBody');
            const rows = tableBody.getElementsByTagName('tr');
            
            // 如果没有搜索词，显示所有行
            if (!searchTerm.trim()) {
                for (let row of rows) {
                    row.classList.remove('hidden-row');
                }
                return;
            }
            
            const searchLower = searchTerm.toLowerCase();
            
            // 遍历每一行进行过滤
            for (let row of rows) {
                // 跳过加载中或无数据的行
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                    continue;
                }
                
                // 检查英文姓名列（第3列，索引为2）和邮箱列（第4列，索引为3）
                const usernameCell = row.cells[2]; // 英文姓名列
                const emailCell = row.cells[3]; // 邮箱列
                
                let isMatch = false;
                
                // 检查英文姓名
                if (usernameCell) {
                    const usernameText = usernameCell.textContent.toLowerCase();
                    if (usernameText.includes(searchLower)) {
                        isMatch = true;
                    }
                }
                
                // 检查邮箱
                if (!isMatch && emailCell) {
                    const emailText = emailCell.textContent.toLowerCase();
                    if (emailText.includes(searchLower)) {
                        isMatch = true;
                    }
                }
                
                // 显示或隐藏行
                if (isMatch) {
                    row.classList.remove('hidden-row');
                } else {
                    row.classList.add('hidden-row');
                }
            }
        }

        // 清除搜索
        function clearSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.value = '';
            filterTable('');
        }

        // 回到顶部功能
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // 打开编辑模态框
        function openEditModal(id) {
            const row = document.getElementById(`row-${id}`);
            const userData = JSON.parse(row.getAttribute('data-user').replace(/&apos;/g, "'"));
            
            // 填充表单数据
            document.getElementById('edit_user_id').value = userData.id;
            document.getElementById('edit_username').value = userData.username || '';
            document.getElementById('edit_username_cn').value = userData.username_cn || '';
            document.getElementById('edit_nickname').value = userData.nickname || '';
            document.getElementById('edit_email').value = userData.email || '';
            document.getElementById('edit_ic_number').value = userData.ic_number || '';
            document.getElementById('edit_date_of_birth').value = userData.date_of_birth || '';
            document.getElementById('edit_nationality').value = userData.nationality || '';
            document.getElementById('edit_gender').value = userData.gender || '';
            document.getElementById('edit_race').value = userData.race || '';
            document.getElementById('edit_phone_number').value = userData.phone_number || '';
            document.getElementById('edit_home_address').value = userData.home_address || '';
            document.getElementById('edit_bank_account_holder_en').value = userData.bank_account_holder_en || '';
            document.getElementById('edit_bank_account').value = userData.bank_account || '';
            document.getElementById('edit_bank_name').value = userData.bank_name || '';
            document.getElementById('edit_position').value = userData.position || '';
            document.getElementById('edit_emergency_contact_name').value = userData.emergency_contact_name || '';
            document.getElementById('edit_emergency_phone_number').value = userData.emergency_phone_number || '';
            document.getElementById('edit_account_type').value = userData.account_type || '';
            
            // 显示模态框
            document.getElementById('editUserModal').style.display = 'block';
            
            // 添加输入格式化
            const fieldsToFormat = [
                'username', 'username_cn', 'email', 'ic_number', 
                'phone_number', 'emergency_phone_number', 'bank_account',
                'bank_account_holder_en', 'emergency_contact_name', 'home_address', 'position'
            ];
            
            fieldsToFormat.forEach(field => {
                const input = document.getElementById(`edit_${field}`);
                if (input) {
                    addInputFormatting(input, field);
                }
            });
        }

        // 关闭编辑模态框
        function closeEditUserModal() {
            document.getElementById('editUserModal').style.display = 'none';
            document.getElementById('editUserForm').reset();
        }

        // 处理编辑表单提交
        async function handleEditUserSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(document.getElementById('editUserForm'));
            const userData = {};
            
            // 收集表单数据
            for (let [key, value] of formData.entries()) {
                userData[key] = value.trim();
            }
            
            // 验证必填字段
            if (!userData.username || !userData.email || !userData.account_type) {
                showMessage('请填写所有必填字段（英文姓名、邮箱、账号类型）！', 'error');
                return;
            }
            
            // 验证字段格式
            const fieldsToValidate = ['username', 'username_cn', 'email'];
            
            for (let field of fieldsToValidate) {
                if (userData[field] && !validateField(field, userData[field])) {
                    const fieldNames = {
                        'username': '英文姓名需要至少两个单词',
                        'username_cn': '中文姓名需要至少两个字',
                        'email': '邮箱格式不正确'
                    };
                    showMessage(fieldNames[field], 'error');
                    return;
                }
            }
            
            // 显示加载状态
            const submitBtn = document.querySelector('#editUserForm .btn-save');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loading"></div>保存中...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update',
                        id: userData.user_id,
                        ...userData
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('修改成功！', 'success');
                    closeEditUserModal();
                    loadCodesAndUsers(); // 刷新表格
                } else {
                    showMessage(result.message || '修改失败！', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('网络错误，请检查连接！', 'error');
            } finally {
                // 恢复按钮状态
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }

        // 确认删除
        function confirmDelete(id, username) {
            // 先关闭已存在的模态框
            closeModal();
            
            // 创建模态框
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.id = `deleteModal_${id}`; // 添加唯一ID
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <i class="fas fa-exclamation-triangle"></i> 确认删除
                    </div>
                    <div class="modal-body">
                        确定要删除职员 "<strong style="color: #f44336;">${username}</strong>" 吗？<br><br>
                        <strong style="color: #ff9800;">⚠️ 此操作不可撤销！</strong>
                    </div>
                    <div class="modal-buttons">
                        <button class="btn-action btn-delete" onclick="deleteRowAndClose(${id})">
                            <i class="fas fa-trash"></i> 确认删除
                        </button>
                        <button class="btn-action btn-cancel" onclick="closeModal()">
                            <i class="fas fa-times"></i> 取消
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            modal.style.display = 'block';
            
            // 点击模态框外部关闭
            modal.onclick = function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            };
            
            // ESC 键关闭
            const escHandler = function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
        }

        // 获取字段最大长度
        function getFieldMaxLength(field) {
            const maxLengths = {
                'username': 50,
                'username_cn': 100,
                'nickname': 50,
                'nationality': 50,
                'position': 100,
                'emergency_contact_name': 100,
                'bank_name': 100,
                'bank_account_holder_en': 50,
                'race': 50
            };
            return maxLengths[field] || 100;
        }

        // 获取字段占位符文本
        function getFieldPlaceholder(field) {
            const placeholders = {
                'username': '全名（英）',
                'username_cn': '全名（中）',
                'nickname': '小名',
                'nationality': '国籍',
                'position': '职位',
                'emergency_contact_name': '紧急联络人',
                'bank_name': '银行名称',
                'bank_account_holder_en': '银行持有人',
                'race': '种族'
            };
            return placeholders[field] || field;
        }

        // 关闭模态框
        function closeModal() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
                // 延迟移除，确保动画完成
                setTimeout(() => {
                    if (modal.parentNode) {
                        modal.parentNode.removeChild(modal);
                    }
                }, 100);
            });
        }

        // 删除行数据
        async function deleteRow(id) {
            try {
                const response = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id: id
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('删除成功！', 'success');
                    loadCodesAndUsers(); // 重新加载数据
                } else {
                    showMessage(result.message || '删除失败！', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('网络错误，请检查连接！', 'error');
            }
        }

        // 获取账号类型的键值（用于取消编辑时）
        function getAccountTypeKey(displayName) {
            const typeMap = {
                '老板': 'boss',
                '管理员': 'admin',
                '人事部': 'hr',
                '设计部': 'design',
                '支援部': 'support',
                '技术部': 'IT',
                '摄影部': 'photograph'
            };
            return typeMap[displayName] || displayName;
        }

        // 打开添加职员模态框
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
            
            // 添加输入格式化
            const fieldsToFormat = [
                'username', 'username_cn', 'email', 'ic_number', 
                'phone_number', 'emergency_phone_number', 'bank_account',
                'bank_account_holder_en', 'emergency_contact_name', 'home_address', 'position'
            ];
            
            fieldsToFormat.forEach(field => {
                const input = document.getElementById(`add_${field}`);
                if (input) {
                    addInputFormatting(input, field);
                }
            });
        }

        // 关闭添加职员模态框
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
            document.getElementById('addUserForm').reset();
        }

        // 修改 addNewUser 函数，添加更多调试信息
        async function addNewUser() {
            const formData = new FormData(document.getElementById('addUserForm'));
            const userData = {};
            
            // 收集表单数据
            for (let [key, value] of formData.entries()) {
                userData[key] = value.trim();
            }
            
            console.log('发送的数据:', userData); // 调试信息
            
            // 验证必填字段
            if (!userData.username || !userData.email || !userData.account_type) {
                showMessage('请填写所有必填字段（英文姓名、邮箱、账号类型）！', 'error');
                return;
            }

            // 验证所有字段格式
            const fieldsToValidate = ['username', 'username_cn', 'email'];

            for (let field of fieldsToValidate) {
                if (userData[field] && !validateField(field, userData[field])) {
                    const fieldNames = {
                        'username': '英文姓名需要至少两个单词',
                        'username_cn': '中文姓名需要至少两个字',
                        'email': '邮箱格式不正确'
                    };
                    showMessage(fieldNames[field], 'error');
                    return;
                }
            }
            
            // 显示加载状态
            const submitBtn = document.querySelector('#addUserForm .btn-save');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loading"></div>添加中...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_user',
                        ...userData
                    })
                });
                
                console.log('响应状态:', response.status); // 调试信息
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('服务器响应:', result); // 调试信息
                
                if (result.success) {
                    let message = `职员 "${result.data.username}" 添加成功！`;
                    if (result.data.email_sent) {
                        message += ` 登录信息已发送到 ${result.data.email}`;
                    } else {
                        message += ` 申请码：${result.data.code}，临时密码：${result.data.default_password}`;
                    }
                    showMessage(message, 'success');
                    closeAddUserModal();
                    loadCodesAndUsers(); // 刷新表格
                } else {
                    showMessage(result.message || '添加失败，请重试！', 'error');
                }
            } catch (error) {
                console.error('详细错误信息:', error);
                showMessage(`网络错误：${error.message}`, 'error');
            } finally {
                // 恢复按钮状态
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }

        // 删除行数据并关闭模态框
        async function deleteRowAndClose(id) {
            // 显示删除中状态
            const modal = document.querySelector('.modal');
            const deleteBtn = modal.querySelector('.btn-delete');
            const cancelBtn = modal.querySelector('.btn-cancel');
            
            // 禁用按钮并显示加载状态
            deleteBtn.innerHTML = '<div class="loading"></div>删除中...';
            deleteBtn.disabled = true;
            cancelBtn.disabled = true;
            
            try {
                const response = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id: id
                    })
                });
                
                const result = await response.json();
                
                // 确保关闭模态框
                closeModal();
                
                if (result.success) {
                    showMessage('删除成功！', 'success');
                    loadCodesAndUsers(); // 重新加载数据
                } else {
                    showMessage(result.message || '删除失败！', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                closeModal(); // 确保出错时也关闭模态框
                showMessage('网络错误，请检查连接！', 'error');
            }
        }

        // 点击模态框外部关闭（为添加职员模态框）
        document.getElementById('addUserModal').onclick = function(event) {
            if (event.target === this) {
                closeAddUserModal();
            }
        };

        // 监听滚动事件，控制回到顶部按钮显示
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            // 使用防抖优化性能
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                const backToTopBtn = document.getElementById('back-to-top-btn');
                const scrollThreshold = 150; // 滚动超过150px后显示按钮
                
                if (window.pageYOffset > scrollThreshold) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            }, 10);
        });
    </script>
</body>
</html>