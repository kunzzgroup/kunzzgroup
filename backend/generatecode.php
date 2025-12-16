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
            font-size: clamp(10px, 0.83vw, 16px);
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: clamp(4px, 0.625vw, 12px);
            border: 2px solid #ff5c00;
            border-radius: 8px;
            font-size: clamp(10px, 0.83vw, 16px);
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
        th:nth-child(6), td:nth-child(6) { width: 120px; }     /* 操作 */

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

            .permissions-layout {
                flex-direction: column;
            }

            .permissions-column {
                flex: 1 1 100%;
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
            margin: 3% auto;
            padding: clamp(18px, 1.3vw, 25px);
            border-radius: 10px;
            width: clamp(750px, 62.5vw, 1200px);
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-body {
            margin-bottom: 0px;
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
            width: clamp(60px, 5.73vw, 110px);
            height: auto;
            padding: clamp(4px, 0.31vw, 6px) 12px;
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
        /* 权限布局容器 */
        .perm-layout-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            transform: translateZ(0); /* 启用GPU加速 */
            -webkit-font-smoothing: antialiased;
        }
        
        /* 权限树形结构样式 */
        .perm-tree-container {
            flex: 1;
            max-height: 60vh;
            overflow-y: auto;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: #f9fafb;
            transform: translateZ(0); /* 启用GPU加速 */
            -webkit-overflow-scrolling: touch; /* iOS平滑滚动 */
        }

        /* 一级分类 */
        .perm-level-1 {
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
            overflow: hidden;
        }

        .perm-level-1-item {
            padding: 12px 15px;
            background: linear-gradient(135deg, #ff8019 0%, #ffb342 100%);
            cursor: pointer; /* 一级分类可以点击展开 */
            transition: background 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: background;
        }
        
        .perm-level-1-item .perm-checkbox-label {
            cursor: pointer; /* 一级分类的label也可以点击展开 */
        }

        .perm-level-1-item:hover {
            background: linear-gradient(135deg, #ff5c00 0%, #ff8019 100%);
        }

        .perm-level-1-item label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer; /* 一级分类label保持pointer以提示可点击展开 */
            margin: 0;
            color: white;
            font-size: 16px;
        }
        
        .perm-level-1-item label span:not(.perm-arrow) {
            cursor: pointer; /* 文字也显示可点击 */
        }
        
        /* 一级分类有三级配置的样式 */
        .perm-level-1-item.has-level-3 {
            position: relative;
        }
        
        .perm-level-1-item.has-level-3::after {
            content: '详细配置 ▶';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            opacity: 0.8;
        }
        
        .perm-level-1-item.has-level-3.expanded {
            background: linear-gradient(135deg, #ff5c00 0%, #ff8019 100%);
        }
        
        .perm-level-1-item .perm-arrow {
            cursor: pointer;
            user-select: none;
        }

        .perm-level-1-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: white;
        }

        .perm-level-1-item strong {
            flex: 1;
        }

        /* 箭头图标 */
        .perm-arrow {
            display: inline-block;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14px;
            margin-right: 8px;
            cursor: pointer;
            user-select: none;
            will-change: transform;
            transform-origin: center;
        }

        .perm-level-1-item.expanded .perm-arrow {
            transform: rotate(90deg);
        }

        /* 二级容器 */
        .perm-level-2-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.3s ease,
                        padding 0.3s ease;
            background: #f8f9fa;
            opacity: 0;
            will-change: max-height, opacity;
        }

        .perm-level-2-container.expanded {
            max-height: 2000px;
            padding: 10px;
            opacity: 1;
        }

        /* 二级项目 */
        .perm-level-2-item {
            padding: 10px 15px;
            margin: 5px 0;
            background: white;
            border-left: 3px solid #ff8019;
            border-radius: 4px;
            position: relative;
        }

        .perm-level-2-item:hover {
            background: #fff9f1;
        }

        .perm-level-2-item label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: default; /* 普通二级项的label点击不会勾选 */
            margin: 0;
            font-size: 14px;
            color: #374151;
        }

        .perm-level-2-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #ff8019;
        }

        /* 有三级菜单的二级项 */
        .perm-level-2-item.has-level-3 {
            cursor: pointer;
        }
        
        .perm-level-2-item.has-level-3 .perm-checkbox-label {
            cursor: pointer; /* 有三级的二级项的label也可以点击展开 */
        }
        
        .perm-level-2-item.has-level-3 .perm-checkbox-label span {
            cursor: pointer; /* 文字也显示可点击 */
        }

        .perm-arrow-sub {
            display: inline-block;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 12px;
            margin-right: 5px;
            color: #ff8019;
            cursor: pointer; /* 箭头可点击 */
            user-select: none;
            will-change: transform;
            transform-origin: center;
        }

        .perm-level-2-item.has-level-3.expanded .perm-arrow-sub {
            transform: rotate(90deg);
        }
        
        .perm-level-2-item.has-level-3.expanded {
            background: #fff4e6;
            border-left-color: #ff5c00;
            border-left-width: 5px;
            box-shadow: 0 2px 8px rgba(255, 128, 25, 0.2);
            transition: background-color 0.3s ease,
                        border-left-color 0.3s ease,
                        border-left-width 0.3s ease,
                        box-shadow 0.3s ease;
        }
        
        .perm-level-2-item.has-level-3::after {
            content: '详细配置 ▶';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: #ff8019;
            font-weight: 600;
            opacity: 0.7;
        }

        /* 右侧详细配置卡片 */
        .perm-detail-card {
            flex: 0 0 400px;
            max-height: 60vh;
            border: 2px solid #ff8019;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 12px rgba(255, 128, 25, 0.15);
            position: relative;
            overflow: hidden;
            transform: translateZ(0); /* 启用GPU加速 */
            -webkit-overflow-scrolling: touch; /* iOS平滑滚动 */
        }

        .perm-detail-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 300px;
            padding: 40px 20px;
            text-align: center;
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        visibility 0.3s;
            will-change: opacity, transform;
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
            opacity: 1;
            transform: translateY(0);
            visibility: visible;
        }

        .perm-detail-placeholder.hidden {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            visibility: hidden;
        }

        #perm-detail-content {
            opacity: 0;
            transform: translateY(10px);
            height: 100%;
            overflow-y: auto;
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        visibility 0.3s;
            will-change: opacity, transform;
            pointer-events: none;
            visibility: hidden;
            position: relative;
            z-index: 1;
        }

        #perm-detail-content.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
            visibility: visible;
        }

        /* 三级面板（在右侧卡片中显示） */
        .perm-level-3-panel {
            display: none;
            padding: 20px;
            opacity: 0;
            transform: translateY(5px);
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: opacity, transform;
            position: relative;
            width: 100%;
            box-sizing: border-box;
        }

        .perm-level-3-panel.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        .perm-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: linear-gradient(135deg, #ff8019 0%, #ffb342 100%);
            color: white;
            font-size: 16px;
            font-weight: 600;
            margin: -20px -20px 20px -20px;
            border-radius: 6px 6px 0 0;
        }
        
        .perm-detail-header strong {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .perm-detail-header strong::before {
            content: '⚙';
            font-size: 20px;
        }
        
        .perm-close-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        
        .perm-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .perm-level-3-section {
            margin-bottom: 15px;
        }

        .perm-level-3-section:last-child {
            margin-bottom: 0;
        }

        .perm-section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #ff8019;
        }

        .perm-level-3-section label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            cursor: default; /* 点击label不会勾选 */
            font-size: 13px;
            color: #4b5563;
        }
        
        .perm-store-item {
            margin: 8px 0;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            background-color: #ffffff;
        }
        
        .perm-store-item .perm-checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            cursor: pointer;
            user-select: none;
            font-size: 13px;
            color: #4b5563;
            font-weight: 500;
        }
        
        .perm-store-item .perm-checkbox-label:hover {
            background-color: #f9fafb;
        }
        
        .perm-store-item .perm-arrow-store {
            display: inline-block;
            transition: transform 0.2s;
            color: #6b7280;
            font-size: 12px;
        }
        
        .perm-store-item.expanded .perm-arrow-store {
            transform: rotate(90deg);
        }
        
        .perm-store-content {
            display: none;
            padding: 10px 12px 10px 30px;
            border-top: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        
        .perm-store-item.expanded .perm-store-content {
            display: block;
        }
        
        .perm-store-content .perm-section-title {
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .perm-store-content label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            cursor: default;
            font-size: 13px;
            color: #4b5563;
        }

        .perm-level-3-section input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #ff8019;
        }
        
        .perm-level-3-section label input[type="checkbox"] {
            cursor: pointer; /* 确保三级checkbox可点击 */
        }

        /* 四级分类样式 */
        .perm-level-4-item {
            margin: 8px 0;
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 3px solid #ffb342;
            border-radius: 4px;
            position: relative;
        }

        .perm-level-4-item .perm-checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin: 0;
        }

        .perm-level-4-item .perm-checkbox-label span:not(.perm-arrow-level4) {
            cursor: pointer;
        }

        .perm-arrow-level4 {
            display: inline-block;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 11px;
            margin-right: 5px;
            color: #ff8019;
            cursor: pointer;
            user-select: none;
            will-change: transform;
            transform-origin: center;
        }

        .perm-level-4-item.expanded .perm-arrow-level4 {
            transform: rotate(90deg);
        }

        .perm-level-4-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.3s ease,
                        padding 0.3s ease;
            opacity: 0;
            will-change: max-height, opacity;
            margin-top: 8px;
            padding-left: 20px;
        }

        .perm-level-4-container.expanded {
            max-height: 500px;
            padding: 10px;
            opacity: 1;
        }

        .perm-level-4-section {
            margin-bottom: 10px;
        }

        .perm-level-4-section:last-child {
            margin-bottom: 0;
        }

        .perm-level-4-section label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            cursor: default;
            font-size: 12px;
            color: #4b5563;
        }

        .perm-level-4-section input[type="checkbox"] {
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        /* 额外权限区域 */
        .extra-perms-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #fff9f1;
            border: 1px solid #ffd4a3;
            border-radius: 8px;
        }

        .extra-perm-section {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .extra-perm-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ff8019;
        }

        .extra-perm-section label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            cursor: default; /* 点击label不会勾选 */
            font-size: 13px;
            color: #4b5563;
        }

        .extra-perm-section input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #ff8019;
        }
        
        .extra-perm-section label input[type="checkbox"] {
            cursor: pointer; /* 确保额外权限checkbox可点击 */
        }

        /* 复选框标签通用样式 */
        .perm-checkbox-label {
            display: flex;
            align-items: center;
            user-select: none;
            cursor: default; /* 设置为默认指针，因为点击label不会勾选 */
        }

        .perm-checkbox-label input[type="checkbox"] {
            cursor: pointer; /* checkbox本身保持手型指针 */
        }


        /* 响应式 */
        @media (max-width: 768px) {
            .extra-perms-container {
                grid-template-columns: 1fr;
            }

            .perm-layout-container {
                flex-direction: column;
            }
            
            .perm-detail-card {
                flex: 1;
                max-height: 40vh;
            }
            
            #permissionsModal .modal-content {
                width: 95vw !important;
            }
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

        /* 权限弹窗内“全选/清空”按钮为长方形 */
        #permissionsModal .perm-actions .btn-action {
            width: clamp(60px, 5.73vw, 110px);
            height: auto;
            padding: clamp(4px, 0.31vw, 6px) 12px;
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
                        <!-- 添加新职员 + 下载申请表 按钮组 -->
                        <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: center; gap: 12px;">
                            <button type="button" class="btn-generate" onclick="openAddUserModal()" >
                                <i class="fas fa-user-plus"></i> 添加新职员
                            </button>
                            <button type="button" class="btn-generate" onclick="openDownloadModal()">
                                <i class="fas fa-download"></i> 下载面试表
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

    <!-- 用户权限模态框 -->
    <div id="permissionsModal" class="modal">
        <div class="modal-content" style="max-width: 1200px !important; width: 85vw !important;">
            <div class="modal-header" style="color: #ff5c00; font-size: 24px; margin-bottom: 20px; font-weight: 600;">
                <i class="fas fa-user-shield"></i> <span id="perm_modal_title">用户权限设定</span>
            </div>
            <input type="hidden" id="perm_current_user_id">
            <div class="modal-body">
                
                <!-- 权限配置布局 -->
                <div class="perm-layout-container">
                    <!-- 左侧：权限树形结构 -->
                    <div class="perm-tree-container">
                    <!-- 一级：集团架构 -->
                    <div class="perm-level-1">
                        <div class="perm-level-1-item" data-perm="brand">
                            <label class="perm-checkbox-label">
                                <input type="checkbox" class="perm-l1-check" value="brand">
                                <span class="perm-arrow">▶</span>
                                <strong>集团架构</strong>
                            </label>
                        </div>
                        <div class="perm-level-2-container" data-parent="brand">
                            <!-- KUNZZ HOLDINGS SDN BHD -->
                            <div class="perm-level-2-item">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="brand" value="kunzz_holdings">
                                    <span>KUNZZ HOLDINGS SDN BHD</span>
                                </label>
                            </div>
                            
                            <!-- TOKYO JAPANESE CUISINE SDN BHD -->
                            <div class="perm-level-2-item has-level-3" data-sub="tokyo_cuisine">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_cuisine">
                                    <span class="perm-arrow-sub">▶</span>
                                    <span>TOKYO JAPANESE CUISINE SDN BHD</span>
                                </label>
                            </div>
                            
                            <!-- TOKYO IZAKAYA SDN BHD -->
                            <div class="perm-level-2-item has-level-3" data-sub="tokyo_izakaya">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_izakaya">
                                    <span class="perm-arrow-sub">▶</span>
                                    <span>TOKYO IZAKAYA SDN BHD</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 一级：营收数据 -->
                    <div class="perm-level-1">
                        <div class="perm-level-1-item" data-perm="analytics">
                            <label class="perm-checkbox-label">
                                <input type="checkbox" class="perm-l1-check" value="analytics">
                                <span class="perm-arrow">▶</span>
                                <strong>营收数据</strong>
                            </label>
                        </div>
                        <div class="perm-level-2-container" data-parent="analytics">
                            <div class="perm-level-2-item">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="analytics" value="kpi_report">
                                    <span>KPI报表</span>
                                </label>
                            </div>
                            <div class="perm-level-2-item has-level-3" data-sub="kpi_upload">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="analytics" value="kpi_upload">
                                    <span class="perm-arrow-sub">▶</span>
                                    <span>数据上传</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 一级：人事管理 -->
                    <div class="perm-level-1">
                        <div class="perm-level-1-item" data-perm="hr">
                            <label class="perm-checkbox-label">
                                <input type="checkbox" class="perm-l1-check" value="hr">
                                <span class="perm-arrow">▶</span>
                                <strong>人事管理</strong>
                            </label>
                        </div>
                        <div class="perm-level-2-container" data-parent="hr">
                            <div class="perm-level-2-item">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="hr" value="staff_management">
                                    <span>职员管理</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 一级：资源总库 -->
                    <div class="perm-level-1">
                        <div class="perm-level-1-item" data-perm="resource">
                            <label class="perm-checkbox-label">
                                <input type="checkbox" class="perm-l1-check" value="resource">
                                <span class="perm-arrow">▶</span>
                                <strong>资源总库</strong>
                            </label>
                        </div>
                        <div class="perm-level-2-container" data-parent="resource">
                            <div class="perm-level-2-item has-level-3" data-sub="stock_inventory">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="resource" value="stock_inventory">
                                    <span class="perm-arrow-sub">▶</span>
                                    <span>库存</span>
                                </label>
                            </div>
                            <div class="perm-level-2-item">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="resource" value="dishware">
                                    <span>碗碟</span>
                                </label>
                            </div>
                            <div class="perm-level-2-item">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="resource" value="price_comparison">
                                    <span>价格对比</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 一级：视觉管理 -->
                    <div class="perm-level-1">
                        <div class="perm-level-1-item" data-perm="visual">
                            <label class="perm-checkbox-label">
                                <input type="checkbox" class="perm-l1-check" value="visual">
                                <strong>视觉管理</strong>
                            </label>
                        </div>
                    </div>
                </div>
                    
                    <!-- 右侧：三级详细配置卡片 -->
                    <div class="perm-detail-card">
                        <div class="perm-detail-placeholder">
                            <i class="fas fa-hand-pointer" style="font-size: 48px; color: #d1d5db; margin-bottom: 15px;"></i>
                            <p style="color: #9ca3af; font-size: 14px;">点击左侧带有箭头的选项<br>查看详细配置</p>
                        </div>
                        
                        <!-- 所有三级面板移到这里 -->
                        <div id="perm-detail-content">
                            <!-- 集团架构 - TOKYO CUISINE -->
                            <div class="perm-level-3-panel" data-for="tokyo_cuisine">
                                <div class="perm-detail-header">
                                    <strong>TOKYO JAPANESE CUISINE SDN BHD</strong>
                                    <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                </div>
                                <div class="perm-level-3-section">
                                    <div class="perm-section-title">店面</div>
                                    <!-- J1 店面 - 可展开/收缩 -->
                                    <div class="perm-store-item" data-store="j1">
                                        <label class="perm-checkbox-label">
                                            <span class="perm-arrow-store">▶</span>
                                            <span>J1 (Midvalley Southkey)</span>
                                        </label>
                                        <div class="perm-store-content">
                                            <div class="perm-section-title">页面权限</div>
                                            <label><input type="checkbox" class="perm-page-schedule" data-store="j1" data-brand="tokyo_cuisine" value="schedule"> 员工排班表</label>
                                        </div>
                                    </div>
                                    <!-- J2 店面 - 可展开/收缩 -->
                                    <div class="perm-store-item" data-store="j2">
                                        <label class="perm-checkbox-label">
                                            <span class="perm-arrow-store">▶</span>
                                            <span>J2 (Paradigm Mall)</span>
                                        </label>
                                        <div class="perm-store-content">
                                            <div class="perm-section-title">页面权限</div>
                                            <label><input type="checkbox" class="perm-page-schedule" data-store="j2" data-brand="tokyo_cuisine" value="schedule"> 员工排班表</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 集团架构 - TOKYO IZAKAYA -->
                            <div class="perm-level-3-panel" data-for="tokyo_izakaya">
                                <div class="perm-detail-header">
                                    <strong>TOKYO IZAKAYA SDN BHD</strong>
                                    <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                </div>
                                <div class="perm-level-3-section">
                                    <div class="perm-section-title">店面</div>
                                    <!-- J3 店面 - 可展开/收缩 -->
                                    <div class="perm-store-item" data-store="j3">
                                        <label class="perm-checkbox-label">
                                            <span class="perm-arrow-store">▶</span>
                                            <span>J3 (Desa Tebrau)</span>
                                        </label>
                                        <div class="perm-store-content">
                                            <div class="perm-section-title">页面权限</div>
                                            <label><input type="checkbox" class="perm-page-schedule" data-store="j3" data-brand="tokyo_izakaya" value="schedule"> 员工排班表</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 营收数据 - 数据上传 -->
                            <div class="perm-level-3-panel" data-for="kpi_upload">
                                <div class="perm-detail-header">
                                    <strong>数据上传</strong>
                                    <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                </div>
                                <div class="perm-level-3-section">
                                    <div class="perm-section-title">数据上传类型</div>
                                    <label><input type="checkbox" class="perm-upload-type" value="kpi"> KPI</label>
                                    <label><input type="checkbox" class="perm-upload-type" value="cost"> 成本</label>
                                </div>
                            </div>
                            
                            <!-- 资源总库 - 库存 -->
                            <div class="perm-level-3-panel" data-for="stock_inventory">
                                <div class="perm-detail-header">
                                    <strong>库存</strong>
                                    <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                </div>
                                <div class="perm-level-3-section">
                                    <div class="perm-section-title">系统选项</div>
                                    <label><input type="checkbox" class="perm-stock-system" value="central"> 中央</label>
                                    <label><input type="checkbox" class="perm-stock-system" value="j1"> J1</label>
                                    <label><input type="checkbox" class="perm-stock-system" value="j2"> J2</label>
                                    <label><input type="checkbox" class="perm-stock-system" value="j3"> J3</label>
                                </div>
                                <div class="perm-level-3-section">
                                    <div class="perm-section-title">视图选项</div>
                                    <label><input type="checkbox" class="perm-stock-view" value="list"> 总库存</label>
                                    <label><input type="checkbox" class="perm-stock-view" value="records"> 进出货</label>
                                    <label><input type="checkbox" class="perm-stock-view" value="remark"> 货品备注</label>
                                    <label><input type="checkbox" class="perm-stock-view" value="product"> 货品种类</label>
                                    <label><input type="checkbox" class="perm-stock-view" value="sot"> 货品异常</label>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-action btn-save" onclick="savePermissions()">保存</button>
                    <button type="button" class="btn-action btn-cancel" onclick="closePermissionsModal()">取消</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 下载申请表模态框 -->
    <div id="downloadModal" class="modal">
        <div class="modal-content" style="max-width: 520px;">
            <div class="modal-header" style="color:#000000ff;">
                <i class="fas fa-download"></i> 下载面试表
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="company_select" style="font-size: 14px; font-weight: 600; margin-bottom: 10px; display: block;">请选择公司/店铺</label>
                    <select id="company_select" style="width: 100%; padding: 12px; border: 2px solid #f99e00; border-radius: 8px; font-size: 14px;">
                        <option value="">请选择...</option>
                        <option value="KUNZZHOLDINGS">KUNZZHOLDINGS</option>
                        <option value="TOKYO_J1">TOKYO (J1)</option>
                        <option value="TOKYO_J2">TOKYO (J2)</option>
                        <option value="TOKYO_J3">TOKYO (J3)</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-action btn-save" onclick="confirmDownload()">
                        <i class="fas fa-check"></i> 确认下载
                    </button>
                    <button type="button" class="btn-action btn-cancel" onclick="closeDownloadModal()">
                        <i class="fas fa-times"></i> 取消
                    </button>
                </div>
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
                                    <label for="add_account_type">账号类型 *</label>
                                    <select id="add_account_type" name="account_type" required>
                                        <option value="">请选择账号类型</option>
                                        <option value="special">特殊 (Special)</option>
                                        <option value="hr">人事部 (HR)</option>
                                        <option value="account">会计部 (Accountant)</option>
                                        <option value="media">媒体制作部 (Media Production)</option>
                                        <option value="marketing">推广部 (Marketing)</option>
                                        <option value="support">支援部 (Support)</option>
                                        <option value="production">生产部 (Production)</option>
                                        <option value="r&d">研发部 (R&D)</option>
                                        <option value="technical">科技部 (Technical)</option>
                                        <option value="design">设计部 (Design)</option>
                                        <option value="operation">Operation</option>
                                        <option value="service">前台 (Service)</option>
                                        <option value="sushi">Sushi Bar</option>
                                        <option value="kitchen">厨房 (Kitchen)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_position">职位</label>
                                    <select id="add_position" name="position">
                                        <option value="">请先选择账号类型</option>
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
                                    <label for="edit_account_type">账号类型 *</label>
                                    <select id="edit_account_type" name="account_type" required>
                                        <option value="">请选择账号类型</option>
                                        <option value="special">特殊 (Special)</option>
                                        <option value="hr">人事部 (HR)</option>
                                        <option value="account">会计部 (Accountant)</option>
                                        <option value="media">媒体制作部 (Media Production)</option>
                                        <option value="marketing">推广部 (Marketing)</option>
                                        <option value="support">支援部 (Support)</option>
                                        <option value="production">生产部 (Production)</option>
                                        <option value="r&d">研发部 (R&D)</option>
                                        <option value="technical">科技部 (Technical)</option>
                                        <option value="design">设计部 (Design)</option>
                                        <option value="operation">Operation</option>
                                        <option value="service">前台 (Service)</option>
                                        <option value="sushi">Sushi Bar</option>
                                        <option value="kitchen">厨房 (Kitchen)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_position">职位</label>
                                    <select id="edit_position" name="position">
                                        <option value="">请先选择账号类型</option>
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
        // 职位配置 - 根据账号类型显示不同职位选项
        const positionsByAccountType = {
            'special': [
                'BOSS',
                'PA',
                'CAO',
                'CSO',
                'COO'
            ],
            'hr': [
                'CHO',
                'VP OF HR',
                'HR DIRECTOR',
                'SENIOR HR MANAGER',
                'HR MANAGER',
                'HR SUPERVISOR',
                'SENIOR HR EXECUTIVE',
                'HR EXECUTIVE',
                'JUNIOR HR EXECUTIVE',
                'HR INTERN'
            ],
            'account': [
                'CFO',
                'FINANCE MANAGER',
                'ACCOUNT SUPERVISOR',
                'ACCOUNT EXECUTIVE',
                'ACCOUNT INTERN'
            ],
            'media': [
                'CVO',
                'VP VISUAL',
                'VISUAL DIRECTOR',
                'SR.MEDIA MANAGER',
                'MEDIA MANAGER',
                'MEDIA LEAD',
                'SR.VIDEO CREATOR',
                'VIDEO CREATOR',
                'JR.VIDEO CREATOR',
                'MEDIA INTERN'
            ],
            'marketing': [
                'CMO',
                'VP OF MARKETING',
                'MARKETING DIRECTOR',
                'SR.MARKETING MANAGER',
                'MARKETING MANAGER',
                'ASST.MARKETING MANAGER',
                'SR.MARKETING EXEC',
                'MARKETING EXEC',
                'JR.MARKETING EXEC',
                'MARKETING INTERN'
            ],
            'support': [
                'VO OF KS',
                'KITCHEN.SUP DIRECTOR',
                'SENIOR KITCHEN SUP MANAGER',
                'KITCHEN SUP MANAGER',
                'KITCHEN SUPPORT LEAD',
                'SENIOR KITCHEN SUPPORT',
                'KITCHEN SUPPORT',
                'JUNIOR KITCHEN SUPPORT',
                'KITCHEN SUPPORT INTERN'
            ],
            'production': [
                'VP OF OPERATIONS',
                'OPERATIONS DIRECTOR',
                'SNR.OPERATIONS MANAGER',
                'PRODUCTION MANAGER',
                'TEAM LEAD',
                'SENIOR PRODUCTION',
                'OPERATOR',
                'JUNIOR OPERATOR',
                'OPERATOR INTERN'
            ],
            'r&d': [
                'VP PF R&D',
                'R&D DIRECTOR',
                'SENIOR R&D MANAGER',
                'R&D MANAGER',
                'LEAD R&D',
                'SENIOR R&D',
                'R&D',
                'JUNIOR R&D',
                'R&D INTERN'
            ],
            'technical': [
                'CTO',
                'VP OF TECH',
                'TECH DIRECTOR',
                'SR.ENGN.MANAGER',
                'ENG.MANAGER',
                'TECH LEAD',
                'SR.TECH ENGINEER',
                'TECH ENGINEER',
                'JR.TECH ENGINEER',
                'ENGINEER INTERN'
            ],
            'design': [
                'CBO',
                'VP OF DESIGN',
                'DESIGN DIRECTOR',
                'SENIOR DESIGN MANAGER',
                'DESIGN MANAGER',
                'DESGIN SUPERVISOR',
                'GRAPHIC DESIGNER',
                'JUNIOR GRAPHIC DESIGNER',
                'DESIGN ASSISTANT',
                'DESIGNER INTERN'
            ],
            'operation': [
                'OPERATION MANAGER'
            ],
            'service': [
                'MANAGER',
                'ASST.MANAGER',
                'SUPERVISOR',
                'SENIOR CAPTAIN',
                'CAPTAIN',
                'SENIOR WAITER',
                'WAITER'
            ],
            'sushi': [
                'HEAD CHEF',
                'OUTLET CHEF',
                'ASST.CHEF',
                'COMIS 1',
                'COMIS 2',
                'COMIS 3',
                'SUSHI HELPER'
            ],
            'kitchen': [
                'HEAD CHEF',
                'OUTLET CHEF',
                'ASST.CHEF',
                'COMIS 1',
                'COMIS 2',
                'COMIS 3',
                'KITCHEN HELPER'
            ]
        };

        // 更新职位下拉选项
        function updatePositionOptions(accountType, positionSelectId) {
            const positionSelect = document.getElementById(positionSelectId);
            
            // 清空现有选项
            positionSelect.innerHTML = '';
            
            if (!accountType || !positionsByAccountType[accountType]) {
                positionSelect.innerHTML = '<option value="">请先选择账号类型</option>';
                positionSelect.disabled = true;
                return;
            }
            
            // 启用职位选择框
            positionSelect.disabled = false;
            
            // 添加默认选项
            positionSelect.innerHTML = '<option value="">请选择职位</option>';
            
            // 添加对应账号类型的职位选项
            const positions = positionsByAccountType[accountType];
            positions.forEach(position => {
                const option = document.createElement('option');
                option.value = position;
                option.textContent = position;
                positionSelect.appendChild(option);
            });
        }

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
                    // 只允许中文字符，但保留当前输入状态
                    // 不进行实时过滤，只在失去焦点时验证
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
            if (field === 'username_cn') {
                // 中文名字特殊处理：只在失去焦点时验证
                input.addEventListener('blur', function() {
                    // 失去焦点时过滤非中文字符
                    let value = this.value;
                    value = value.replace(/[^\u4e00-\u9fff]/g, '');
                    this.value = value;
                });
                
                // 粘贴时格式化
                input.addEventListener('paste', function(e) {
                    setTimeout(() => {
                        let value = this.value;
                        value = value.replace(/[^\u4e00-\u9fff]/g, '');
                        this.value = value;
                    }, 0);
                });
            } else {
                // 其他字段的实时格式化
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
            // 启动会话自动刷新
            startSessionRefresh();
            
            loadCodesAndUsers();
            
            // 添加实时搜索功能
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function(e) {
                filterTable(e.target.value);
            });

            // 初始化事件监听器
            rebindEventListeners();
        });
        
        // 页面卸载时停止会话刷新
        window.addEventListener('beforeunload', function() {
            stopSessionRefresh();
        });

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
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #C62828;">
                        <div style="background: #ffebee; border: 1px solid #f44336; border-radius: 8px; padding: 20px; margin: 10px;">
                            <h3 style="color: #C62828; margin: 0 0 10px 0;">🔒 会话已过期</h3>
                            <p style="margin: 0 0 15px 0;">您的登录会话已过期，请重新登录以继续使用。</p>
                            <button onclick="window.location.href='../frontend/login.php'" 
                                    style="background: #C62828; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                                重新登录
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        // 加载代码和职员数据
        async function loadCodesAndUsers() {
            const tableBody = document.getElementById('tableBody');
            
            try {
                const response = await fetch('generatecodeapi.php?action=list');
                const result = await response.json();

                if (result.success) {
                    displayData(result.data);
                } else {
                    // 检查是否是会话过期
                    if (result.code === 'SESSION_EXPIRED') {
                        showSessionExpiredMessage();
                        return;
                    }
                    
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
                'special': 1,
                'hr': 2,
                'account': 3,
                'media': 4,
                'marketing': 5,
                'support': 6,
                'production': 7,
                'r&d': 8,
                'technical': 9,
                'design': 10,
                'operation': 11,
                'service': 12,
                'sushi': 13,
                'kitchen': 14
            };
            
            // 按照指定顺序排序数据（先按账号类型，相同类型内按职位顺序）
            const sortedData = [...data].sort((a, b) => {
                const orderA = typeOrder[a.account_type] || 999;
                const orderB = typeOrder[b.account_type] || 999;
                
                // 如果账号类型不同，按类型顺序排序
                if (orderA !== orderB) {
                    return orderA - orderB;
                }
                
                // 相同账号类型内，按职位顺序排序
                const accountType = a.account_type;
                const positions = positionsByAccountType[accountType] || [];
                
                // 获取职位在配置数组中的索引
                const positionA = (a.position || '').trim();
                const positionB = (b.position || '').trim();
                
                const indexA = positions.indexOf(positionA);
                const indexB = positions.indexOf(positionB);
                
                // 如果职位在配置中，按索引排序（索引小的在前）
                if (indexA !== -1 && indexB !== -1) {
                    if (indexA !== indexB) {
                        return indexA - indexB;
                    }
                } else if (indexA !== -1) {
                    // A在配置中，B不在，A排在前面
                    return -1;
                } else if (indexB !== -1) {
                    // B在配置中，A不在，B排在前面
                    return 1;
                } else {
                    // 都不在配置中，按职位名称字母顺序排序
                    if (positionA && positionB) {
                        const compare = positionA.localeCompare(positionB);
                        if (compare !== 0) {
                            return compare;
                        }
                    } else if (positionA) {
                        return -1; // A有职位，B没有，A排在前面
                    } else if (positionB) {
                        return 1; // B有职位，A没有，B排在前面
                    }
                }
                
                // 如果职位也相同或都为空，按创建时间正序（旧的在前）
                const timeA = new Date(a.created_at || 0).getTime();
                const timeB = new Date(b.created_at || 0).getTime();
                if (timeA !== timeB) {
                    return timeA - timeB;
                }
                
                // 如果创建时间也相同，按ID正序
                return (a.id || 0) - (b.id || 0);
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
                            <button class="btn-action btn-edit" onclick="openEditModal(${item.id})" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-save" onclick="openPermissionsModal(${item.id})" title="权限设定" style="background: #ff8019;">
                                <i class="fas fa-user-shield"></i>
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
                'special': '特殊',
                'hr': '人事部',
                'account': '会计部',
                'media': '媒体制作部',
                'marketing': '推广部',
                'support': '支援部',
                'production': '生产部',
                'r&d': '研发部',
                'technical': '科技部',
                'design': '设计部',
                'operation': 'Operation',
                'service': '前台',
                'sushi': 'Sushi Bar',
                'kitchen': '厨房'
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
            document.getElementById('edit_emergency_contact_name').value = userData.emergency_contact_name || '';
            document.getElementById('edit_emergency_phone_number').value = userData.emergency_phone_number || '';
            document.getElementById('edit_account_type').value = userData.account_type || '';
            
            // 先设置账号类型，然后更新职位选项
            if (userData.account_type) {
                updatePositionOptions(userData.account_type, 'edit_position');
                // 在职位选项加载后设置职位值
                setTimeout(() => {
                    document.getElementById('edit_position').value = userData.position || '';
                }, 50);
            }
            
            // 添加账号类型变化监听器
            const accountTypeSelect = document.getElementById('edit_account_type');
            accountTypeSelect.addEventListener('change', function() {
                updatePositionOptions(this.value, 'edit_position');
            });
            
            // 显示模态框
            document.getElementById('editUserModal').style.display = 'block';
            
            // 添加输入格式化
            const fieldsToFormat = [
                'username', 'username_cn', 'email', 'ic_number', 
                'phone_number', 'emergency_phone_number', 'bank_account',
                'bank_account_holder_en', 'emergency_contact_name', 'home_address'
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
                '摄影部': 'photograph',
                '研发部': 'r&d',
                '生产部': 'production'
            };
            return typeMap[displayName] || displayName;
        }

        // 打开添加职员模态框
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
            
            // 重置表单
            document.getElementById('addUserForm').reset();
            
            // 重置职位选择框
            const positionSelect = document.getElementById('add_position');
            positionSelect.innerHTML = '<option value="">请先选择账号类型</option>';
            positionSelect.disabled = true;
            
            // 添加账号类型变化监听器
            const accountTypeSelect = document.getElementById('add_account_type');
            accountTypeSelect.addEventListener('change', function() {
                updatePositionOptions(this.value, 'add_position');
            });
            
            // 添加输入格式化
            const fieldsToFormat = [
                'username', 'username_cn', 'email', 'ic_number', 
                'phone_number', 'emergency_phone_number', 'bank_account',
                'bank_account_holder_en', 'emergency_contact_name', 'home_address'
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

        // 点击模态框外部关闭（用户权限）
        document.getElementById('permissionsModal').onclick = function(event) {
            if (event.target === this) {
                closePermissionsModal();
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
    <script>
        // 用户权限逻辑 - 重构版
        const sidebarSubOptions = {
            analytics: ['kpi_report', 'kpi_upload'],
            hr: ['staff_management'],
            resource: ['stock_inventory', 'dishware', 'price_comparison'],
            brand: ['kunzz_holdings', 'tokyo_cuisine', 'tokyo_izakaya']
        };
        const STOCK_SYSTEM_KEYS = ['central', 'j1', 'j2', 'j3'];
        const STOCK_VIEW_KEYS = ['list', 'records', 'remark', 'product', 'sot'];
        const reportTypeOptions = ['kpi', 'cost'];
        const restaurantOptions = ['j1', 'j2', 'j3'];

        // 全局标记，避免重复绑定
        let permissionTreeEventsBound = false;
        
        // 初始化权限树事件监听器
        function initPermissionTreeEvents() {
            // 如果已经绑定过，直接返回
            if (permissionTreeEventsBound) return;
            permissionTreeEventsBound = true;
            
            // 阻止label的默认行为，防止点击label时触发checkbox
            document.querySelectorAll('#permissionsModal .perm-checkbox-label').forEach(label => {
                label.addEventListener('click', function(e) {
                    // 如果点击的是checkbox，允许默认行为
                    if (e.target.tagName === 'INPUT') {
                        return;
                    }
                    // 点击其他部分（文字、箭头等），只阻止默认行为，不阻止冒泡
                    e.preventDefault();
                });
            });
            
            // 额外权限区域的label也需要阻止
            document.querySelectorAll('.extra-perm-section label').forEach(label => {
                label.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'INPUT') {
                        e.preventDefault();
                    }
                });
            });
            
            // 三级面板的label也需要阻止
            document.querySelectorAll('#perm-detail-content label').forEach(label => {
                label.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'INPUT') {
                        e.preventDefault();
                    }
                });
            });
            
            // 四级分类点击展开/折叠
            document.querySelectorAll('.perm-level-4-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    // 如果点击的是复选框，不处理展开
                    if (e.target.tagName === 'INPUT') {
                        e.stopPropagation();
                        return;
                    }
                    
                    const container = item.querySelector('.perm-level-4-container');
                    if (!container) return;
                    
                    const isCurrentlyExpanded = item.classList.contains('expanded');
                    
                    // 如果当前项未展开，先关闭所有其他四级分类
                    if (!isCurrentlyExpanded) {
                        document.querySelectorAll('.perm-level-4-item.expanded').forEach(otherItem => {
                            if (otherItem !== item) {
                                otherItem.classList.remove('expanded');
                                const otherContainer = otherItem.querySelector('.perm-level-4-container');
                                if (otherContainer) {
                                    otherContainer.classList.remove('expanded');
                                }
                            }
                        });
                    }
                    
                    // 切换当前项的展开状态
                    item.classList.toggle('expanded');
                    container.classList.toggle('expanded');
                });
            });
            
            // 一级分类点击展开/折叠
            document.querySelectorAll('.perm-level-1-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    // 如果点击的是复选框，不处理展开
                    if (e.target.tagName === 'INPUT') {
                        e.stopPropagation();
                        return;
                    }
                    
                    const parent = item.getAttribute('data-perm');
                    const container = document.querySelector(`.perm-level-2-container[data-parent="${parent}"]`);
                    const isCurrentlyExpanded = item.classList.contains('expanded');
                    const detailContent = document.getElementById('perm-detail-content');
                    const placeholder = document.querySelector('.perm-detail-placeholder');
                    const hasLevel3 = item.classList.contains('has-level-3');
                    const sub = item.getAttribute('data-sub');
                    
                    // 如果是一级分类有三级配置（如视觉管理）
                    if (hasLevel3 && sub) {
                        const panel = document.querySelector(`#perm-detail-content .perm-level-3-panel[data-for="${sub}"]`);
                        
                        // 如果当前项未展开，先关闭所有其他一级分类和三级面板
                        if (!isCurrentlyExpanded) {
                            document.querySelectorAll('.perm-level-1-item.expanded').forEach(otherItem => {
                                if (otherItem !== item) {
                                    otherItem.classList.remove('expanded');
                                    const otherParent = otherItem.getAttribute('data-perm');
                                    const otherContainer = document.querySelector(`.perm-level-2-container[data-parent="${otherParent}"]`);
                                    if (otherContainer) {
                                        otherContainer.classList.remove('expanded');
                                    }
                                }
                            });
                            
                            // 关闭所有有三级配置的二级项和右侧详细配置卡片
                            document.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(level2Item => {
                                level2Item.classList.remove('expanded');
                            });
                            document.querySelectorAll('.perm-level-1-item.has-level-3.expanded').forEach(level1Item => {
                                if (level1Item !== item) {
                                    level1Item.classList.remove('expanded');
                                }
                            });
                            document.querySelectorAll('.perm-level-3-panel').forEach(p => {
                                if (p !== panel) p.classList.remove('show');
                            });
                        }
                        
                        // 切换当前项的展开状态
                        item.classList.toggle('expanded');
                        
                        // 切换三级面板显示
                        if (panel) {
                            const isPanelShowing = panel.classList.contains('show');
                            if (!isPanelShowing) {
                                // 显示面板
                                document.querySelectorAll('.perm-level-3-panel').forEach(p => p.classList.remove('show'));
                                panel.classList.add('show');
                                if (detailContent) detailContent.classList.add('active');
                                if (placeholder) placeholder.classList.add('hidden');
                            } else {
                                // 隐藏面板
                                panel.classList.remove('show');
                                if (detailContent) detailContent.classList.remove('active');
                                if (placeholder) placeholder.classList.remove('hidden');
                            }
                        }
                        return;
                    }
                    
                    // 普通一级分类（有二级容器）
                    if (!container) return;
                    
                    // 如果当前项未展开，先关闭所有其他一级分类
                    if (!isCurrentlyExpanded) {
                        document.querySelectorAll('.perm-level-1-item.expanded').forEach(otherItem => {
                            if (otherItem !== item) {
                                otherItem.classList.remove('expanded');
                                const otherParent = otherItem.getAttribute('data-perm');
                                const otherContainer = document.querySelector(`.perm-level-2-container[data-parent="${otherParent}"]`);
                                if (otherContainer) {
                                    otherContainer.classList.remove('expanded');
                                }
                            }
                        });
                        
                        // 关闭所有有三级配置的二级项和右侧详细配置卡片
                        document.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(level2Item => {
                            level2Item.classList.remove('expanded');
                        });
                        document.querySelectorAll('.perm-level-1-item.has-level-3.expanded').forEach(level1Item => {
                            level1Item.classList.remove('expanded');
                        });
                        document.querySelectorAll('.perm-level-3-panel').forEach(panel => {
                            panel.classList.remove('show');
                        });
                        if (detailContent) detailContent.classList.remove('active');
                        if (placeholder) placeholder.classList.remove('hidden');
                    }
                    
                    // 切换当前项的展开状态
                    item.classList.toggle('expanded');
                    container.classList.toggle('expanded');
                });
            });

            // 一级复选框变化 - 同步二级权限状态
            document.querySelectorAll('.perm-l1-check').forEach(checkbox => {
                if (!checkbox.dataset.fromChild) {
                    checkbox.dataset.fromChild = 'false';
                }
                
                checkbox.addEventListener('change', function() {
                    const parentValue = this.value;
                    const isChecked = this.checked;
                    const isFromChild = this.dataset.fromChild === 'true';
                    
                    // 重置标记
                    this.dataset.fromChild = 'false';
                    
                    if (!isFromChild) {
                        syncLevel2Permissions(parentValue, isChecked);
                    }
                });
            });
            
            // 二级复选框变化 - 检查父级状态并同步三级权限
            document.querySelectorAll('.perm-l2-check').forEach(checkbox => {
                checkbox.dataset.fromChild = checkbox.dataset.fromChild || 'false';
                
                checkbox.addEventListener('change', function() {
                    const level2Value = this.value;
                    const isChecked = this.checked;
                    const parent = this.dataset.parent;
                    const isFromChild = this.dataset.fromChild === 'true';
                    
                    // 重置标记
                    this.dataset.fromChild = 'false';
                    
                    // 检查父级状态
                    const parentCheckbox = document.querySelector(`.perm-l1-check[value="${parent}"]`);
                    if (parentCheckbox && !parentCheckbox.checked) {
                        parentCheckbox.dataset.fromChild = 'true';
                        parentCheckbox.checked = true;
                        parentCheckbox.dispatchEvent(new Event('change'));
                        this.checked = true;
                    }
                    
                    // 同步三级权限（仅在不是从子级触发时，才向下联动）
                    if (!isFromChild) {
                        syncLevel3Permissions(level2Value, isChecked);
                    }
                    
                    // 取消勾选时，若无其他同级，则向上取消父级
                    if (!isChecked) {
                        const otherChildren = document.querySelectorAll(`.perm-l2-check[data-parent="${parent}"]:checked`);
                        if (otherChildren.length === 0 && parentCheckbox) {
                            parentCheckbox.dataset.fromChild = 'true';
                            parentCheckbox.checked = false;
                            parentCheckbox.dispatchEvent(new Event('change'));
                        }
                    }
                });
            });
            
            // 二级有三级的项目 - 在右侧卡片显示三级面板
            document.querySelectorAll('.perm-level-2-item.has-level-3').forEach(item => {
                item.addEventListener('click', function(e) {
                    // 如果点击的是复选框，不处理展开
                    if (e.target.tagName === 'INPUT') {
                        e.stopPropagation();
                        return;
                    }
                    
                    const sub = item.getAttribute('data-sub');
                    const panel = document.querySelector(`#perm-detail-content .perm-level-3-panel[data-for="${sub}"]`);
                    const detailContent = document.getElementById('perm-detail-content');
                    const placeholder = document.querySelector('.perm-detail-placeholder');
                    const isCurrentlyExpanded = item.classList.contains('expanded');
                    
                    // 如果当前项未展开，先关闭所有其他有三级配置的二级项
                    if (!isCurrentlyExpanded) {
                        document.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(otherItem => {
                            if (otherItem !== item) {
                                otherItem.classList.remove('expanded');
                                const otherSub = otherItem.getAttribute('data-sub');
                                const otherPanel = document.querySelector(`#perm-detail-content .perm-level-3-panel[data-for="${otherSub}"]`);
                                if (otherPanel) {
                                    otherPanel.classList.remove('show');
                                }
                            }
                        });
                    }
                    
                    // 关闭所有三级面板（除了当前要显示的）
                    document.querySelectorAll('.perm-level-3-panel').forEach(p => {
                        if (p !== panel) p.classList.remove('show');
                    });
                    
                    // 切换当前面板
                    item.classList.toggle('expanded');
                    
                    if (!isCurrentlyExpanded) {
                        // 展开：显示右侧卡片内容，隐藏占位符
                        detailContent.classList.add('active');
                        placeholder.classList.add('hidden');
                        panel.classList.add('show');
                    } else {
                        // 折叠：关闭面板，显示占位符
                        panel.classList.remove('show');
                        detailContent.classList.remove('active');
                        placeholder.classList.remove('hidden');
                    }
                });
            });
            
            // 店面项展开/收缩功能
            document.querySelectorAll('.perm-store-item').forEach(item => {
                const label = item.querySelector('.perm-checkbox-label');
                if (label) {
                    label.addEventListener('click', function(e) {
                        // 如果点击的是checkbox，不处理展开
                        if (e.target.tagName === 'INPUT') {
                            return;
                        }
                        e.preventDefault();
                        e.stopPropagation();
                        item.classList.toggle('expanded');
                    });
                }
            });
            
            // 三级页面权限和库存/上传权限的向上联动
            document.querySelectorAll('.perm-stock-system, .perm-stock-view, .perm-upload-type, .perm-page-schedule').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    let level2Value = '';
                    if (this.classList.contains('perm-stock-system') || this.classList.contains('perm-stock-view')) {
                        level2Value = 'stock_inventory';
                    } else if (this.classList.contains('perm-upload-type')) {
                        level2Value = 'kpi_upload';
                    } else if (this.classList.contains('perm-page-schedule')) {
                        level2Value = this.dataset.brand || '';
                    }
                    
                    if (!level2Value) return;
                    
                    const level2Checkbox = document.querySelector(`.perm-l2-check[value="${level2Value}"]`);
                    if (!level2Checkbox) return;
                    
                    if (this.checked) {
                        if (!level2Checkbox.checked) {
                            level2Checkbox.dataset.fromChild = 'true';
                            level2Checkbox.checked = true;
                            level2Checkbox.dispatchEvent(new Event('change'));
                        }
                    } else {
                        let otherChecked = 0;
                        if (level2Value === 'stock_inventory') {
                            otherChecked = document.querySelectorAll('.perm-stock-system:checked, .perm-stock-view:checked').length;
                        } else if (level2Value === 'kpi_upload') {
                            otherChecked = document.querySelectorAll('.perm-upload-type:checked').length;
                        } else {
                            otherChecked = document.querySelectorAll(`.perm-page-schedule[data-brand="${level2Value}"]:checked`).length;
                        }
                        
                        if (otherChecked === 0) {
                            level2Checkbox.dataset.fromChild = 'true';
                            level2Checkbox.checked = false;
                            level2Checkbox.dispatchEvent(new Event('change'));
                        }
                    }
                });
            });
            
            // 二级复选框变化 - 不自动勾选三级
            // 移除自动勾选逻辑，用户需要手动勾选三级选项

        }
        
        // 设置默认全选所有权限
        function setDefaultAllPermissions() {
            // 先确保所有checkbox都是active的（不设置disabled）
            document.querySelectorAll('#permissionsModal input[type="checkbox"]').forEach(cb => {
                cb.disabled = false;
            });
            
            // 全选所有一级权限
            document.querySelectorAll('.perm-l1-check').forEach(cb => {
                cb.checked = true;
            });
            
            // 全选所有二级权限
            document.querySelectorAll('.perm-l2-check').forEach(cb => {
                cb.checked = true;
                cb.disabled = false;
            });
            
            // 全选所有三级权限（库存、数据上传、集团架构页面权限）
            document.querySelectorAll('.perm-stock-system, .perm-stock-view, .perm-upload-type, .perm-page-schedule').forEach(cb => {
                cb.checked = true;
                cb.disabled = false;
            });
            
            // 确保所有checkbox都是active的（不设置disabled）
            document.querySelectorAll('#permissionsModal input[type="checkbox"]').forEach(cb => {
                cb.disabled = false;
            });
        }
        
        // 同步二级权限状态
        function syncLevel2Permissions(parentValue, parentChecked) {
            // 获取该父级下的所有二级权限
            document.querySelectorAll(`.perm-l2-check[data-parent="${parentValue}"]`).forEach(cb => {
                if (parentChecked) {
                    if (!cb.checked) {
                        cb.checked = true;
                    }
                    syncLevel3Permissions(cb.value, true);
                } else {
                    cb.checked = false;
                    syncLevel3Permissions(cb.value, false);
                }
            });
        }
        
        // 同步三级权限状态
        function syncLevel3Permissions(level2Value, level2Checked) {
            // 库存权限
            if (level2Value === 'stock_inventory') {
                document.querySelectorAll('.perm-stock-system, .perm-stock-view').forEach(cb => {
                    cb.checked = level2Checked ? true : false;
                });
            }
            
            // 数据上传权限
            if (level2Value === 'kpi_upload') {
                document.querySelectorAll('.perm-upload-type').forEach(cb => {
                    cb.checked = level2Checked ? true : false;
                });
            }
            
            // 集团架构权限 - TOKYO CUISINE
            if (level2Value === 'tokyo_cuisine') {
                document.querySelectorAll('.perm-page-schedule[data-store="j1"], .perm-page-schedule[data-store="j2"]').forEach(cb => {
                    cb.checked = level2Checked ? true : false;
                });
            }
            
            // 集团架构权限 - TOKYO IZAKAYA
            if (level2Value === 'tokyo_izakaya') {
                document.querySelectorAll('.perm-page-schedule[data-store="j3"]').forEach(cb => {
                    cb.checked = level2Checked ? true : false;
                });
            }
        }
        
        // 关闭详细配置面板
        function closeDetailPanel() {
            document.querySelectorAll('.perm-level-3-panel').forEach(p => p.classList.remove('show'));
            document.querySelectorAll('.perm-level-2-item.has-level-3').forEach(i => i.classList.remove('expanded'));
            document.querySelectorAll('.perm-level-1-item.has-level-3').forEach(i => i.classList.remove('expanded'));
            document.querySelectorAll('.perm-level-4-item.expanded').forEach(item => {
                item.classList.remove('expanded');
                const container = item.querySelector('.perm-level-4-container');
                if (container) container.classList.remove('expanded');
            });
            document.getElementById('perm-detail-content').classList.remove('active');
            document.querySelector('.perm-detail-placeholder').classList.remove('hidden');
        }

        // 更新库存权限（三级）
        function updateStockInventoryPerms(checked) {
            document.querySelectorAll('.perm-stock-system').forEach(cb => cb.checked = checked);
            document.querySelectorAll('.perm-stock-view').forEach(cb => cb.checked = checked);
        }
        
        // 更新数据上传权限（三级）
        function updateKpiUploadPerms(checked) {
            document.querySelectorAll('.perm-upload-type').forEach(cb => cb.checked = checked);
        }

        function hasCustomPermissions(data) {
            if (!data) return false;
            const { permissions, submenu_permissions, page_permissions, report_permissions, restaurant_permissions, brand_permissions, upload_permissions } = data;
            if (Array.isArray(permissions) && permissions.length) return true;
            if (Array.isArray(report_permissions) && report_permissions.length) return true;
            if (Array.isArray(restaurant_permissions) && restaurant_permissions.length) return true;
            if (Array.isArray(upload_permissions) && upload_permissions.length) return true;

            if (submenu_permissions && typeof submenu_permissions === 'object') {
                if (Object.values(submenu_permissions).some(arr => Array.isArray(arr) && arr.length)) {
                    return true;
                }
            }

            if (page_permissions && typeof page_permissions === 'object') {
                const stock = page_permissions.stock_inventory || {};
                if ((Array.isArray(stock.system) && stock.system.length) ||
                    (Array.isArray(stock.view) && stock.view.length)) {
                    return true;
                }
            }
            
            if (brand_permissions && typeof brand_permissions === 'object') {
                if (Object.values(brand_permissions).some(arr => Array.isArray(arr) && arr.length)) {
                    return true;
                }
            }

            return false;
        }

        function openPermissionsModal(userId) {
            if (!userId) {
                showMessage('无效的用户ID', 'error');
                return;
            }
            
            const modal = document.getElementById('permissionsModal');
            
            // 保存当前用户ID
            document.getElementById('perm_current_user_id').value = userId;
            
            // 查找用户信息并更新标题
            const user = originalTableData.find(u => u.id == userId);
            if (user) {
                document.getElementById('perm_modal_title').textContent = `用户权限设定 - ${user.username || '未命名用户'}`;
            } else {
                document.getElementById('perm_modal_title').textContent = '用户权限设定';
            }
            
            // 加载该用户的权限
            loadUserPermissions(userId);
            
            // 初始化权限树事件
            initPermissionTreeEvents();
            
            modal.style.display = 'block';
        }

        function closePermissionsModal() {
            // 关闭所有展开的项
            document.querySelectorAll('.perm-level-1-item.expanded').forEach(item => {
                item.classList.remove('expanded');
            });
            document.querySelectorAll('.perm-level-2-container.expanded').forEach(container => {
                container.classList.remove('expanded');
            });
            document.querySelectorAll('.perm-level-3-panel.show').forEach(panel => {
                panel.classList.remove('show');
            });
            document.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(item => {
                item.classList.remove('expanded');
            });
            document.querySelectorAll('.perm-level-1-item.has-level-3.expanded').forEach(item => {
                item.classList.remove('expanded');
            });
            document.querySelectorAll('.perm-level-4-item.expanded').forEach(item => {
                item.classList.remove('expanded');
                const container = item.querySelector('.perm-level-4-container');
                if (container) container.classList.remove('expanded');
            });
            
            // 重置右侧详细配置卡片
            const detailContent = document.getElementById('perm-detail-content');
            const placeholder = document.querySelector('.perm-detail-placeholder');
            if (detailContent) detailContent.classList.remove('active');
            if (placeholder) placeholder.classList.remove('hidden');
            
            // 重置标题和用户ID
            document.getElementById('perm_modal_title').textContent = '用户权限设定';
            document.getElementById('perm_current_user_id').value = '';
            
            document.getElementById('permissionsModal').style.display = 'none';
        }

        // 设置权限复选框状态 - 重构版
        function setPermCheckboxes(perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms, uploadPerms) {
            const mainList = Array.isArray(perms) ? perms : [];
            const values = new Set(mainList);
            
            // 设置一级分类
            document.querySelectorAll('.perm-l1-check').forEach(cb => {
                cb.checked = values.has(cb.value);
            });
            
            // 设置二级分类
            const submenuData = (submenuPerms && typeof submenuPerms === 'object') ? submenuPerms : {};
            document.querySelectorAll('.perm-l2-check').forEach(cb => {
                const parent = cb.dataset.parent;
                const parentEnabled = values.has(parent);
                const hasCustom = Object.prototype.hasOwnProperty.call(submenuData, parent);
                const source = hasCustom ? submenuData[parent] : undefined;
                const allowed = Array.isArray(source) ? source : (sidebarSubOptions[parent] || []);
                cb.checked = parentEnabled && allowed.includes(cb.value);
            });
            
            // 设置库存三级权限
            const stockPagePerms = (pagePerms && typeof pagePerms === 'object') ? (pagePerms.stock_inventory || {}) : {};
            const stockSystems = Array.isArray(stockPagePerms.system) ? stockPagePerms.system : [];
            const stockViews = Array.isArray(stockPagePerms.view) ? stockPagePerms.view : [];
            const systemSet = new Set(stockSystems);
            const viewSet = new Set(stockViews);
            
            // 检查stock_inventory二级权限是否选中
            const stockInventoryChecked = document.querySelector('.perm-l2-check[value="stock_inventory"]')?.checked || false;
            
            document.querySelectorAll('.perm-stock-system').forEach(cb => {
                cb.checked = systemSet.has(cb.value);
            });
            document.querySelectorAll('.perm-stock-view').forEach(cb => {
                cb.checked = viewSet.has(cb.value);
            });
            
            // 设置集团架构三级和四级权限
            const brandData = (brandPerms && typeof brandPerms === 'object') ? brandPerms : {};
            
            // 兼容旧格式（数组）和新格式（对象）
            let cuisineStores = [];
            let izakayaStores = [];
            let cuisineStorePerms = {};
            let izakayaStorePerms = {};
            
            if (Array.isArray(brandData.tokyo_cuisine)) {
                // 旧格式：数组
                cuisineStores = brandData.tokyo_cuisine;
            } else if (brandData.tokyo_cuisine && typeof brandData.tokyo_cuisine === 'object') {
                // 新格式：对象，包含第四级权限
                cuisineStorePerms = brandData.tokyo_cuisine;
                cuisineStores = Object.keys(cuisineStorePerms);
            }
            
            if (Array.isArray(brandData.tokyo_izakaya)) {
                // 旧格式：数组
                izakayaStores = brandData.tokyo_izakaya;
            } else if (brandData.tokyo_izakaya && typeof brandData.tokyo_izakaya === 'object') {
                // 新格式：对象，包含第四级权限
                izakayaStorePerms = brandData.tokyo_izakaya;
                izakayaStores = Object.keys(izakayaStorePerms);
            }
            
            // 检查相关二级权限是否选中
            const tokyoCuisineChecked = document.querySelector('.perm-l2-check[value="tokyo_cuisine"]')?.checked || false;
            const tokyoIzakayaChecked = document.querySelector('.perm-l2-check[value="tokyo_izakaya"]')?.checked || false;
            
            // 设置三级页面权限（员工排班表）- 每个店面独立设置
            // 设置J1的页面权限
            if (cuisineStorePerms && typeof cuisineStorePerms === 'object') {
                const j1Perms = Array.isArray(cuisineStorePerms['j1']) ? cuisineStorePerms['j1'] : [];
                document.querySelectorAll('.perm-page-schedule[data-store="j1"]').forEach(cb => {
                    cb.checked = j1Perms.includes('schedule');
                });
            }
            
            // 设置J2的页面权限
            if (cuisineStorePerms && typeof cuisineStorePerms === 'object') {
                const j2Perms = Array.isArray(cuisineStorePerms['j2']) ? cuisineStorePerms['j2'] : [];
                document.querySelectorAll('.perm-page-schedule[data-store="j2"]').forEach(cb => {
                    cb.checked = j2Perms.includes('schedule');
                });
            }
            
            // 设置J3的页面权限
            if (izakayaStorePerms && typeof izakayaStorePerms === 'object') {
                const j3Perms = Array.isArray(izakayaStorePerms['j3']) ? izakayaStorePerms['j3'] : [];
                document.querySelectorAll('.perm-page-schedule[data-store="j3"]').forEach(cb => {
                    cb.checked = j3Perms.includes('schedule');
                });
            }
            
            // 设置数据上传三级权限（新增）
            const uploadData = Array.isArray(uploadPerms) && uploadPerms.length ? uploadPerms : [];
            const uploadSet = new Set(uploadData);
            document.querySelectorAll('.perm-upload-type').forEach(cb => {
                cb.checked = uploadSet.has(cb.value);
            });
            
            // 设置额外权限
            const reportSetSource = Array.isArray(reportPerms) && reportPerms.length ? reportPerms : [];
            const reportSet = new Set(reportSetSource);
            document.querySelectorAll('.perm-report').forEach(cb => {
                cb.checked = reportSet.has(cb.value);
            });
            
            const restaurantSetSource = Array.isArray(restaurantPerms) && restaurantPerms.length ? restaurantPerms : [];
            const restaurantSet = new Set(restaurantSetSource);
            document.querySelectorAll('.perm-restaurant').forEach(cb => {
                cb.checked = restaurantSet.has(cb.value);
            });
            
            // 确保所有checkbox都是active的（不设置disabled）
            // 因为用户要求所有checkbox都应该是active的，不管父级是否选中
            document.querySelectorAll('#permissionsModal input[type="checkbox"]').forEach(cb => {
                cb.disabled = false;
            });
        }

        async function loadUserPermissions(userId) {
            try {
                // 先设置默认全选（所有checkbox都是active且全选）
                setDefaultAllPermissions();
                
                const res = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_permissions', user_id: userId })
                });
                const data = await res.json();
                if (data.success) {
                    const permsPayload = {
                        permissions: data.permissions || [],
                        submenu_permissions: data.submenu_permissions || {},
                        page_permissions: data.page_permissions || {},
                        report_permissions: data.report_permissions || [],
                        restaurant_permissions: data.restaurant_permissions || [],
                        brand_permissions: data.brand_permissions || {},
                        upload_permissions: data.upload_permissions || []
                    };
                    
                    // 检查是否有权限数据（即使brand_permissions的值为空数组，只要键存在就表示有权限数据）
                    const hasAnyPermissions = 
                        permsPayload.permissions.length > 0 ||
                        Object.keys(permsPayload.submenu_permissions).length > 0 ||
                        Object.keys(permsPayload.page_permissions).length > 0 ||
                        Object.keys(permsPayload.brand_permissions).length > 0 ||
                        (permsPayload.report_permissions && permsPayload.report_permissions.length > 0) ||
                        (permsPayload.restaurant_permissions && permsPayload.restaurant_permissions.length > 0) ||
                        (permsPayload.upload_permissions && permsPayload.upload_permissions.length > 0);
                    
                    if (hasAnyPermissions) {
                        // 有权限数据，根据实际数据更新（覆盖默认全选）
                        // 但确保所有checkbox都是active的
                        setPermCheckboxes(
                            permsPayload.permissions,
                            permsPayload.page_permissions,
                            permsPayload.submenu_permissions,
                            permsPayload.report_permissions,
                            permsPayload.restaurant_permissions,
                            permsPayload.brand_permissions,
                            permsPayload.upload_permissions
                        );
                        // 确保所有checkbox都是active的（不设置disabled）
                        document.querySelectorAll('#permissionsModal input[type="checkbox"]').forEach(cb => {
                            cb.disabled = false;
                        });
                    }
                    // 如果没有权限数据，保持默认全选状态
                }
                // 如果加载失败，保持默认全选状态
            } catch (e) {
                // 出错时，保持默认全选状态
                console.error('加载权限失败:', e);
            }
        }

        async function savePermissions() {
            const userId = document.getElementById('perm_current_user_id').value;
            
            // 获取一级权限
            const perms = Array.from(document.querySelectorAll('.perm-l1-check:checked')).map(cb => cb.value);
            
            // 获取二级权限（按父级分组）
            const submenuPermissions = {};
            Object.keys(sidebarSubOptions).forEach(parent => {
                const mainCheckbox = document.querySelector(`.perm-l1-check[value="${parent}"]`);
                const selectedSubs = Array.from(document.querySelectorAll(`.perm-l2-check[data-parent="${parent}"]:checked`)).map(cb => cb.value);
                if (mainCheckbox && mainCheckbox.checked) {
                    submenuPermissions[parent] = selectedSubs;
                } else {
                    submenuPermissions[parent] = [];
                }
            });
            
            // 获取库存三级权限
            const selectedStockSystems = Array.from(document.querySelectorAll('.perm-stock-system:checked')).map(cb => cb.value);
            const selectedStockViews = Array.from(document.querySelectorAll('.perm-stock-view:checked')).map(cb => cb.value);
            const pagePermissions = {
                stock_inventory: {
                    system: selectedStockSystems,
                    view: selectedStockViews
                }
            };
            
            // 获取集团架构三级页面权限，每个店面独立保存
            const cuisineStorePermissions = {};
            // 获取J1的页面权限
            const j1ScheduleChecked = document.querySelector('.perm-page-schedule[data-store="j1"]')?.checked || false;
            if (j1ScheduleChecked) {
                cuisineStorePermissions['j1'] = ['schedule'];
            }
            // 获取J2的页面权限
            const j2ScheduleChecked = document.querySelector('.perm-page-schedule[data-store="j2"]')?.checked || false;
            if (j2ScheduleChecked) {
                cuisineStorePermissions['j2'] = ['schedule'];
            }
            
            const izakayaStorePermissions = {};
            // 获取J3的页面权限
            const j3ScheduleChecked = document.querySelector('.perm-page-schedule[data-store="j3"]')?.checked || false;
            if (j3ScheduleChecked) {
                izakayaStorePermissions['j3'] = ['schedule'];
            }
            
            const brandPermissions = {
                tokyo_cuisine: cuisineStorePermissions,
                tokyo_izakaya: izakayaStorePermissions
            };
            
            // 获取数据上传三级权限（新增）
            const uploadPermissions = Array.from(document.querySelectorAll('.perm-upload-type:checked')).map(cb => cb.value);
            
            // 获取额外权限
            const reportPermissions = Array.from(document.querySelectorAll('.perm-report:checked')).map(cb => cb.value);
            const restaurantPermissions = Array.from(document.querySelectorAll('.perm-restaurant:checked')).map(cb => cb.value);
            
            const btn = document.querySelector('#permissionsModal .btn-save');
            const old = btn.innerHTML;
            btn.innerHTML = '<div class="loading"></div>保存中...';
            btn.disabled = true;
            
            try {
                const res = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_permissions',
                        user_id: userId,
                        permissions: perms,
                        page_permissions: pagePermissions,
                        submenu_permissions: submenuPermissions,
                        report_permissions: reportPermissions,
                        restaurant_permissions: restaurantPermissions,
                        brand_permissions: brandPermissions,
                        upload_permissions: uploadPermissions
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showMessage('权限已保存', 'success');
                    closePermissionsModal();
                } else {
                    showMessage(data.message || '保存失败', 'error');
                }
            } catch (e) {
                showMessage('网络错误，稍后重试', 'error');
            } finally {
                btn.innerHTML = old;
                btn.disabled = false;
            }
        }

        // 下载申请表相关函数
        function openDownloadModal() {
            const modal = document.getElementById('downloadModal');
            document.getElementById('company_select').value = ''; // 重置选择
            modal.style.display = 'block';
        }

        function closeDownloadModal() {
            document.getElementById('downloadModal').style.display = 'none';
        }

        function confirmDownload() {
            const select = document.getElementById('company_select');
            const company = select.value;
            
            if (!company) {
                showMessage('请选择一个公司/店铺', 'warning');
                return;
            }
            
            // PDF文件路径映射
            const pdfFiles = {
                'KUNZZHOLDINGS': '../form/kh.pdf',
                'TOKYO_J1': '../form/j1.pdf',
                'TOKYO_J2': '../form/j2.pdf',
                'TOKYO_J3': '../form/j3.pdf'
            };
            
            const pdfPath = pdfFiles[company];
            
            if (pdfPath) {
                // 创建一个隐藏的a标签来触发下载
                const link = document.createElement('a');
                link.href = pdfPath;
                link.download = pdfPath.split('/').pop(); // 使用文件名作为下载名
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showMessage(`正在下载 ${select.options[select.selectedIndex].text} 的申请表...`, 'success');
                closeDownloadModal();
            } else {
                showMessage('下载失败，文件不存在', 'error');
            }
        }

        // 点击模态框外部关闭（下载申请表）
        document.getElementById('downloadModal').onclick = function(event) {
            if (event.target === this) {
                closeDownloadModal();
            }
        };
    </script>
</body>
</html>