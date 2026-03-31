<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 登录检查（在输出任何 HTML 之前）────────────────────────────────────────
define('SESSION_TIMEOUT_HIRE', 60);
$hasRemember = (
    isset($_COOKIE['user_id'], $_COOKIE['username'], $_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
);

if (isset($_SESSION['user_id'])) {
    // session 超时检查
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_HIRE) &&
        !$hasRemember
    ) {
        session_unset();
        session_destroy();
        header('Location: /frontend/login.html');
        exit;
    }
    $_SESSION['last_activity'] = time();
} elseif ($hasRemember) {
    // 恢复 remember-me session
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = $_COOKIE['position'] ?? null;
    $_SESSION['last_activity'] = time();
} else {
    // 未登录 → 跳转登录页，登录后返回 hire
    header('Location: /frontend/login.html?redirect=hire');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 招聘列表</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            /* 品牌橙灰主题 */
            --primary-color: #ff7b00;
            --primary-hover: #e66e00;
            --primary-light: #fff7ed;

            --bg-body: #faf7f2;
            --bg-white: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --highlight: #ff7b00;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            padding: 0;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* 匹配 sidebar 的主内容区 */
        .main-content {
            flex: 1;
            padding: 0 32px 32px;
            min-width: 0;
            overflow-x: auto;
        }

        /* ================= 布局与复用工具类 ================= */
        .layout-container {
            width: 100%;
            min-width: 0;
        }

        /* 内容卡片：筛选 + 表格 + 分页 包裹在一起 */
        .content-card {
            background: #fff;
            border-radius: 12px;
            border: 2px solid #000;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.10);
            margin-top: 20px;
            overflow: hidden;
        }

        @media (max-width: 1024px) {
            .layout-container {
                min-width: auto;
                width: 100%;
            }
        }

        .flex-row {
            display: flex;
            flex-direction: row;
        }

        .items-center {
            align-items: center;
        }

        .items-end {
            align-items: flex-end;
        }

        .justify-between {
            justify-content: space-between;
        }

        .justify-center {
            justify-content: center;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .gap-4 {
            gap: 4px;
        }

        .gap-8 {
            gap: 8px;
        }

        .gap-10 {
            gap: 10px;
        }

        .gap-12 {
            gap: 12px;
        }

        .gap-16 {
            gap: 16px;
        }

        .ml-auto {
            margin-left: auto;
        }

        .mb-4 {
            margin-bottom: 4px;
        }

        .mb-8 {
            margin-bottom: 8px;
        }

        .mt-4 {
            margin-top: 4px;
        }

        .mt-24 {
            margin-top: 24px;
        }

        .text-center {
            text-align: center;
        }

        .font-normal {
            font-weight: normal;
        }

        .font-medium {
            font-weight: 500;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-12 {
            font-size: 12px;
        }

        .text-14 {
            font-size: 14px;
        }

        .text-primary {
            color: var(--primary-color);
        }

        .text-muted {
            color: var(--text-muted);
        }

        .text-main {
            color: var(--text-main);
        }

        .pointer-events-none {
            pointer-events: none;
        }

        /* ================= 头部区域 ================= */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 28px 0 20px;
        }

        .header h1 {
            font-size: clamp(24px, 2.6vw, 40px);
            font-weight: bold;
            color: #000;
            margin: 0;
            flex: 1;
        }

        .header h1::after {
            content: "";
            display: block;
            height: 3px;
            width: 100%;
            margin-top: 12px;
            background: linear-gradient(90deg, rgba(255, 92, 0, 0) 0%, rgba(0, 0, 0, 1) 25%, rgba(0, 0, 0, 1) 75%, rgba(255, 92, 0, 0) 100%);
        }

        /* 按钮与链接 */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: 1px solid transparent;
            background: transparent;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 158, 0, 0.35);
        }

        .btn-default {
            border-color: var(--border-color);
            color: var(--text-muted);
            background: white;
        }

        .btn-default:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-export {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: normal;
        }

        .icon-sm {
            width: 14px;
            height: 14px;
        }

        .icon-18 {
            width: 18px;
            height: 18px;
        }

        .icon-margin {
            margin-left: 4px;
        }

        .btn-link-action {
            color: var(--primary-color);
            background: transparent;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            padding: 0;
        }

        .btn-link-action:hover {
            text-decoration: underline;
        }

        .btn-link {
            background: #f3f4f6;
            color: #4b5563;
            font-size: 12px;
            padding: 6px 13px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-link:hover {
            background: #e5e7eb;
            color: var(--text-main);
        }

        /* ================= 标签 (Chip) 筛选系统 ================= */
        .filter-bar-container {
            background-color: var(--bg-white);
            display: flex;
            flex-direction: column;
        }

        .filter-content {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 20px 24px;
        }

        .filter-row {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .filter-label {
            font-size: 12px;
            color: var(--primary-color);
            font-weight: 700;
            width: 70px;
            flex-shrink: 0;
            padding-top: 8px;
            letter-spacing: 0.5px;
        }

        .chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            flex: 1;
            overflow: visible;
            min-height: 34px;
        }

        .chip {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-main);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            user-select: none;
            outline: none;
        }

        .chip:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .chip.active {
            background: var(--primary-light);
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: bold;
        }

        .chip-count {
            background: #f3f4f6;
            color: #6b7280;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: normal;
            transition: all 0.2s;
        }

        .chip.active .chip-count {
            background: var(--primary-color);
            color: white;
        }

        /* 智能搜索栏和日期单独处理 */
        .search-date-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-top: 8px;
            border-top: 1px dashed var(--border-color);
        }

        .smart-search-wrapper {
            width: 40px;
            height: 38px;
            border-radius: 20px;
            background: white;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .smart-search-wrapper.expanded {
            width: 250px;
            border-color: var(--primary-color);
            border-radius: 6px;
            cursor: default;
        }

        .smart-search-icon {
            width: 40px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            flex-shrink: 0;
            transition: color 0.3s;
        }

        .smart-search-wrapper.expanded .smart-search-icon {
            color: var(--primary-color);
        }

        .smart-search-input {
            width: 100%;
            border: none;
            background: transparent;
            height: 100%;
            padding-right: 12px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            display: none;
        }

        .smart-search-wrapper.expanded .smart-search-input {
            display: block;
        }

        .search-suggestions {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            left: 0;
            top: 100%;
            width: 250px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-top: 8px;
            z-index: 100;
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: opacity 0.2s, visibility 0.2s;
            text-align: left;
        }

        .search-suggestions.show {
            visibility: visible;
            opacity: 1;
        }

        .suggest-header {
            font-size: 11px;
            color: #9ca3af;
            padding: 8px 12px 4px;
            font-weight: bold;
            background: #f9fafb;
            border-bottom: 1px solid #f3f4f6;
        }

        .suggest-item {
            display: block;
            padding: 8px 12px;
            font-size: 14px;
            color: var(--text-main);
            text-decoration: none;
            transition: background 0.15s;
        }

        .suggest-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        .filter-date-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-input-wrapper {
            position: relative;
            height: 38px;
            display: flex;
            align-items: center;
            width: 220px;
        }

        .date-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #9ca3af;
            pointer-events: none;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            width: 100%;
            transition: border-color 0.2s;
            height: 38px;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(255, 123, 0, 0.1);
        }

        .date-input {
            padding-left: 34px;
            cursor: pointer;
        }

        .quick-select-wrapper {
            position: relative;
            height: 38px;
        }

        .btn-quick-select {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            box-sizing: border-box;
            white-space: nowrap;
        }

        .menu-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 4px 0;
        }

        .quick-select-menu {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 8px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 50;
            width: 120px;
            padding: 8px 0;
            text-align: left;
            transition: opacity 0.15s, visibility 0.15s;
        }

        .quick-select-menu.show {
            visibility: visible;
            opacity: 1;
        }

        .quick-select-menu a {
            display: block;
            padding: 8px 16px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 14px;
        }

        .quick-select-menu a:hover {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        /* ================= 已选条件展示区 ================= */
        .active-filters-bar {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            background: #f9fafb;
            border-top: 1px dashed var(--border-color);
            flex-wrap: wrap;
        }

        .active-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: white;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .active-tag-close {
            cursor: pointer;
            font-size: 14px;
            color: var(--primary-color);
            opacity: 0.6;
            line-height: 1;
        }

        .active-tag-close:hover {
            opacity: 1;
        }

        /* ================= 移动端 Drawer (抽屉) ================= */
        .mobile-filter-btn {
            display: none;
        }

        .drawer-header {
            display: none;
        }

        .drawer-overlay-filter {
            display: none;
        }

        @media (max-width: 1024px) {
            .mobile-filter-btn {
                display: inline-flex;
            }

            .filter-bar-container {
                position: fixed;
                top: 0;
                right: -100%;
                width: 340px;
                height: 100vh;
                background: white;
                z-index: 2000;
                flex-direction: column;
                box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                overflow-y: auto;
                padding: 0;
            }

            .filter-bar-container.drawer-open {
                right: 0;
            }

            .drawer-overlay-filter {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                z-index: 1999;
                display: block;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s;
            }

            .drawer-overlay-filter.show {
                opacity: 1;
                visibility: visible;
            }

            .drawer-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 24px;
                border-bottom: 1px solid var(--border-color);
                background: var(--bg-white);
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .drawer-header h3 {
                font-size: 16px;
                margin: 0;
                color: var(--text-main);
            }

            .drawer-close {
                background: none;
                border: none;
                font-size: 24px;
                color: var(--text-muted);
                cursor: pointer;
                line-height: 1;
            }

            .filter-content {
                padding: 20px;
                gap: 24px;
            }

            .filter-row {
                flex-direction: column;
                gap: 12px;
            }

            .search-date-row {
                flex-direction: column;
                align-items: stretch;
                border-top: none;
                padding-top: 0;
                gap: 24px;
            }

            .filter-date-wrap {
                flex-direction: column;
                align-items: stretch;
            }

            .date-input-wrapper {
                width: 100%;
            }
        }

        /* ==================== 表格区域 ==================== */
        .table-container {
            overflow-x: auto;
            border-top: 2px solid #000;
            min-height: 300px;
            overflow-y: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            min-width: 1000px;
            margin: 0;
        }

        .data-table th {
            padding: 14px 16px;
            background-color: #636363;
            font-size: clamp(8px, 0.74vw, 14px);
            color: #fff;
            font-weight: bold;
            letter-spacing: 0.5px;
            border-bottom: none;
            white-space: nowrap;
            border: 1px solid #d1d5db;
        }

        .data-table th:first-child,
        .data-table td:first-child {
            padding-left: 28px;
        }

        .data-table td {
            padding: clamp(0px, 0.52vw, 10px) 16px;
            font-size: clamp(8px, 0.74vw, 14px);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            border: 1px solid #d1d5db;
        }

        .table-row {
            transition: background-color 0.2s;
        }

        .table-row:hover {
            background-color: #fff9f1;
        }

        .empty-state {
            padding: 48px;
            text-align: center;
            color: var(--text-muted);
        }

        .company-badge {
            background-color: #f3f4f6;
            color: #4b5563;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        /* 状态徽章与菜单 */
        .status-wrapper {
            position: relative;
            display: inline-block;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
            white-space: nowrap;
        }

        .badge:hover {
            filter: brightness(0.95);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .badge-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-yellow {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-green {
            background: #d1fae5;
            color: #059669;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* 全局 status popover （portal 挂在 body，避免被表格 overflow 截断） */
        #globalStatusPopover {
            display: none;
            position: fixed;
            z-index: 9999;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            min-width: 110px;
            padding: 4px;
        }

        #globalStatusPopover.show {
            display: block;
        }

        .status-option {
            display: block;
            padding: 6px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--text-main);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .status-option:hover {
            background: #f3f4f6;
        }

        .status-option.active {
            background: var(--primary-light);
            color: var(--primary-color);
            font-weight: bold;
        }

        /* 分页 */
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: var(--bg-white);
            border-top: 1px solid var(--border-color);
        }

        .btn-page {
            padding: 4px 12px;
            background: white;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-page:hover {
            border-color: var(--text-main);
            color: var(--text-main);
        }

        .current-page {
            padding: 4px 12px;
            background: var(--primary-light);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        /* ==================== 弹窗区域 ==================== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-box {
            background: white;
            border-radius: 8px;
            width: 900px;
            max-width: 95%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: translateY(20px);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        .modal-overlay.active .modal-box {
            transform: translateY(0);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 18px;
            color: var(--text-main);
            font-weight: bold;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }

        .info-section h3 {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 12px;
            font-size: 14px;
            margin-bottom: 16px;
            text-align: left;
        }

        .action-section {
            background: #f9fafb;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            text-align: left;
        }

        .action-section h3 {
            font-size: 14px;
            color: var(--text-main);
            margin-bottom: 16px;
            font-weight: bold;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f9fafb;
            border-radius: 0 0 8px 8px;
        }

        .modal-field-label {
            display: block;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-weight: bold;
        }

        .modal-select {
            border-color: var(--primary-color);
            background-color: #fff;
        }

        .modal-link {
            color: var(--primary-color);
            text-decoration: none;
        }

        .btn-resume-modal {
            border: 1px solid var(--border-color);
        }

        /* ==================== 待处理通知 Toast ==================== */
        #pendingToast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            background: #fff;
            border-radius: 12px;
            border-left: 4px solid #ef4444;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 16px 20px;
            max-width: 360px;
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
        }

        #pendingToast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-icon {
            width: 36px;
            height: 36px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .toast-body {
            flex: 1;
        }

        .toast-title {
            font-size: 14px;
            font-weight: 700;
            color: #111;
            margin-bottom: 4px;
        }

        .toast-msg {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            color: #9ca3af;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            flex-shrink: 0;
        }

        .toast-close:hover {
            color: #374151;
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: #ef4444;
            border-radius: 0 0 0 12px;
            animation: toast-progress-bar 6s linear forwards;
        }

        /* flatpickr 日历：强制 fixed 定位，脱离所有容器约束 */
        .flatpickr-calendar {
            position: fixed !important;
            z-index: 99999 !important;
            margin: 0 !important;
        }

        @keyframes toast-progress-bar {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div id="drawerOverlay" class="drawer-overlay-filter" onclick="toggleDrawer(false)"></div>

    <div class="main-content">
        <div class="layout-container">

            <header class="header">
                <h1>招聘申请列表</h1>
                <div class="flex-row gap-10">
                    <button class="btn btn-default mobile-filter-btn" onclick="toggleDrawer(true)">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        筛选条件
                    </button>
                    <button class="btn btn-primary btn-export text-14" onclick="exportToExcel()">
                        ⬇ 导出 Excel
                    </button>
                </div>
            </header>

            <div id="filterContainer" class="filter-bar-container">

                <div class="drawer-header">
                    <h3>高级筛选</h3>
                    <button class="drawer-close" onclick="toggleDrawer(false)">&times;</button>
                </div>

                <div class="filter-content">

                    <div class="filter-row">
                        <div class="filter-label">申请公司</div>
                        <div class="chip-list" id="chipListCompany">
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-label">申请职位</div>
                        <div class="chip-list" id="chipListJob">
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-label">处理状态</div>
                        <div class="chip-list" id="chipListStatus">
                        </div>
                    </div>

                    <div class="search-date-row">
                        <div style="position: relative;">
                            <div id="smartSearchWrapper" class="smart-search-wrapper">
                                <span class="smart-search-icon">
                                    <svg class="icon-18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                                <input type="text" id="smartInput" class="smart-search-input"
                                    placeholder="搜索姓名 / 邮箱 / 手机号">
                            </div>
                            <div id="searchSuggestions" class="search-suggestions">
                                <div class="suggest-header">🔍 快速建议匹配</div>
                                <div id="suggestionList"></div>
                            </div>
                        </div>

                        <div class="filter-date-wrap">
                            <div class="date-input-wrapper">
                                <svg class="date-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <input type="text" id="dateRangePicker" class="form-control border-highlight date-input"
                                    placeholder="选择提交日期" readonly>
                            </div>
                            <div class="quick-select-wrapper">
                                <button id="btnQuickSelect" class="btn btn-default btn-quick-select">
                                    时段
                                    <svg class="icon-sm icon-margin" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="quickSelectMenu" class="quick-select-menu">
                                    <a href="#" data-range="today">今天</a>
                                    <a href="#" data-range="yesterday">昨天</a>
                                    <div class="menu-divider"></div>
                                    <a href="#" data-range="thisWeek">本周</a>
                                    <a href="#" data-range="lastWeek">上周</a>
                                    <div class="menu-divider"></div>
                                    <a href="#" data-range="thisMonth">这个月</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="activeFiltersArea" class="active-filters-bar">
                    <span class="text-12 text-muted font-bold">已选条件：</span>
                    <div id="activeFiltersList" class="flex-row items-center gap-8 flex-wrap" style="flex:1;"></div>
                    <button class="btn-link-action text-12" onclick="resetAllFilters()">清空全部</button>
                </div>

            </div><!-- /#filterContainer -->

            <div class="content-card">
                <div id="filterContainer-inner"></div><!-- placeholder -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>应聘者</th>
                                <th>所属公司</th>
                                <th>申请职位</th>
                                <th>联系方式</th>
                                <th>简历附件</th>
                                <th>申请时间</th>
                                <th>状态</th>
                                <th class="text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

                <footer class="pagination-bar">
                    <span id="totalCountInfo" class="text-muted text-14">共计 0 条记录</span>
                    <div class="page-controls" id="pageControls">
                        <button class="btn-page" id="btnPrevPage">上一页</button>
                        <span class="current-page" id="currentPageNum">1</span>
                        <button class="btn-page" id="btnNextPage">下一页</button>
                    </div>
                </footer>

            </div><!-- /.content-card -->

        </div>
        <div id="applicantModal" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h2>应聘者详情档案</h2>
                    <button class="btn-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="info-section">
                        <h3>基础申请信息</h3>
                        <div class="info-grid">
                            <span class="text-muted">申请公司：</span><span class="font-bold text-main"
                                id="modalCompany"></span>
                            <span class="text-muted">申请职位：</span><span class="font-bold text-primary"
                                id="modalJob"></span>
                            <span class="text-muted">提交时间：</span><span class="font-normal text-main"
                                id="modalTime"></span>
                        </div>
                        <h3 class="mt-24">个人联系资料</h3>
                        <div class="info-grid">
                            <span class="text-muted">中文姓名：</span><span class="font-bold text-main"
                                id="modalZhName"></span>
                            <span class="text-muted">英文姓名：</span><span class="font-bold text-main"
                                id="modalEnName"></span>
                            <span class="text-muted">性别：</span><span class="font-normal text-main"
                                id="modalGender"></span>
                            <span class="text-muted">电子邮箱：</span><span><a href="#" id="modalEmailLink"
                                    class="modal-link font-bold"></a></span>
                            <span class="text-muted">电话号码：</span><span class="font-bold text-main"
                                id="modalPhone"></span>
                            <span class="text-muted items-center flex-row">简历附件：</span>
                            <span><button class="btn-link btn-resume-modal" id="modalResumeBtn">📄
                                    下载/预览简历</button></span>
                        </div>
                    </div>
                    <div class="action-section">
                        <h3>HR 处理进度跟进</h3>
                        <div class="mb-8">
                            <label class="modal-field-label">修改当前状态：</label>
                            <select id="modalStatusSelect" class="form-control modal-select">
                                <option value="0">🔴 待处理</option>
                                <option value="1">🟡 沟通中</option>
                                <option value="2">🟢 已录用</option>
                                <option value="3">⚪ 已淘汰</option>
                            </select>
                        </div>
                        <div>
                            <label class="modal-field-label mt-24">内部备注 (仅 HR 可见)：</label>
                            <textarea id="modalRemarks" class="form-control" rows="7" style="width: 400px; min-width: 400px; max-width: 400px; resize: vertical; height: auto;"
                                placeholder="在此记录面试情况、期望薪资、背景调查结果等..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" onclick="closeModal()">取消关闭</button>
                    <button class="btn btn-primary" onclick="saveModalChanges()">保存更新</button>
                </div>
            </div>
        </div>

        <template id="rowTemplate">
            <tr class="table-row">
                <td>
                    <div>
                        <div class="font-bold text-main text-14 js-name"></div>
                        <div class="text-muted text-12 mt-4 js-subname"></div>
                    </div>
                </td>
                <td>
                    <span class="company-badge js-company"></span>
                </td>
                <td class="font-medium text-primary js-job-title"></td>
                <td>
                    <div class="text-14 text-main mb-4 js-email"></div>
                    <div class="text-12 text-muted js-phone"></div>
                </td>
                <td>
                    <button class="btn-link js-resume">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        预览 PDF
                    </button>
                </td>
                <td>
                    <div class="text-14 text-main mb-4 js-date"></div>
                    <div class="text-12 text-muted js-time"></div>
                </td>
                <td class="status-cell">
                    <div class="status-wrapper">
                        <div class="badge js-status-badge" title="点击修改状态"></div>
                    </div>
                </td>
                <td class="text-center">
                    <button class="btn-link-action btn-action-detail">详情</button>
                </td>
            </tr>
        </template>
        <!-- 全局状态选择 Popover（fixed 定位，不受表格 overflow 限制） -->
        <div id="globalStatusPopover">
            <a href="#" data-val="0" class="status-option">🔴 待处理</a>
            <a href="#" data-val="1" class="status-option">🟡 沟通中</a>
            <a href="#" data-val="2" class="status-option">🟢 已录用</a>
            <a href="#" data-val="3" class="status-option">⚪ 已淘汰</a>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://npmcdn.com/flatpickr/dist/l10n/zh.js"></script>
        </script>

        <script>
            // ── 静态配置 ───────────────────────────────────────────────────────────
            const companyJobsMap = {
                'KUNZZ HOLDINGS': ['人事部', '技术部', '销售部', '设计部'],
                'TOKYO JAPANESE CUISINE': ['服务员', '厨师', '寿司师傅', '店长'],
                'TOKYO IZAKAYA': ['店长', '服务员', '厨师', '寿司师傅']
            };
            const statusConfig = [
                { val: '0', label: '待处理', icon: '🔴' },
                { val: '1', label: '沟通中', icon: '🟡' },
                { val: '2', label: '已录用', icon: '🟢' },
                { val: '3', label: '已淘汰', icon: '⚪' }
            ];

            // ── 全局数据 ───────────────────────────────────────────────────────────
            let allData = [];   // 当前过滤后的申请记录
            let rawData = [];   // 全量未过滤数据（用于公司/职位 chip 计数）
            let statsData = { total: 0, '0': 0, '1': 0, '2': 0, '3': 0 };  // 统计数据

            // 核心：全局状态管理
            let state = {
                keyword: '',
                company: '',
                jobTitle: '',
                status: '',
                dateStart: '',
                dateEnd: '',
                dateLabel: '',
                page: 1,
                pageSize: 20
            };

            let pagination = { total: 0, totalPages: 1 };  // 服务端分页信息

            let fpInstance = null;
            let currentEditingId = null; let isSearchExpanded = false;

            const els = {
                smartWrapper: document.getElementById('smartSearchWrapper'),
                smartInput: document.getElementById('smartInput'),
                suggestions: document.getElementById('searchSuggestions'),
                suggestionList: document.getElementById('suggestionList'),

                chipListCompany: document.getElementById('chipListCompany'),
                chipListJob: document.getElementById('chipListJob'),
                chipListStatus: document.getElementById('chipListStatus'),

                activeArea: document.getElementById('activeFiltersArea'),
                activeList: document.getElementById('activeFiltersList'),

                datePicker: document.getElementById('dateRangePicker'),
                btnQuick: document.getElementById('btnQuickSelect'),
                quickMenu: document.getElementById('quickSelectMenu'),

                tableBody: document.getElementById('tableBody'),
                rowTemplate: document.getElementById('rowTemplate'),
                totalCountInfo: document.getElementById('totalCountInfo'),
                pageControls: document.getElementById('pageControls'),
                btnPrev: document.getElementById('btnPrevPage'),
                btnNext: document.getElementById('btnNextPage'),
                currentPageNum: document.getElementById('currentPageNum'),

                drawer: document.getElementById('filterContainer'),
                drawerOverlay: document.getElementById('drawerOverlay'),

                modal: document.getElementById('applicantModal'),
                modalStatusSelect: document.getElementById('modalStatusSelect'),
                modalRemarks: document.getElementById('modalRemarks')
            };

            document.addEventListener('DOMContentLoaded', () => {
                initDatePicker();
                // 加载全量数据（用于 chip 计数）+ 芯片化 + 拉取当前过滤结果
                fetchStats().then(async () => {
                    await loadRawData();
                    renderChips();
                    fetchData();
                });

                // 分页按钮
                els.btnPrev.addEventListener('click', () => {
                    if (state.page > 1) { state.page--; fetchData(); }
                });
                els.btnNext.addEventListener('click', () => {
                    const totalPages = Math.ceil(pagination.total / state.pageSize);
                    if (state.page < totalPages) { state.page++; fetchData(); }
                });

                // 搜索框事件
                els.smartWrapper.addEventListener('click', (e) => {
                    if (!isSearchExpanded) { e.stopPropagation(); expandSearch(); }
                });
                els.smartInput.addEventListener('input', () => {
                    const val = els.smartInput.value.trim();
                    if (val.length > 0) {
                        generateSuggestions(val);
                        els.suggestions.classList.add('show');
                    } else {
                        els.suggestions.classList.remove('show');
                    }
                    state.keyword = val;
                    // 搜索关键词变化时重新拉取
                    fetchData();
                });

                // 快捷日期事件
                els.btnQuick.addEventListener('click', (e) => {
                    e.stopPropagation(); els.quickMenu.classList.toggle('show');
                });
                els.quickMenu.addEventListener('click', (e) => {
                    if (e.target.tagName === 'A' && e.target.getAttribute('data-range')) {
                        e.preventDefault(); setQuickDate(e.target.dataset.range, e.target.textContent);
                    }
                });

                // 全局点击事件处理
                document.addEventListener('click', (e) => {
                    if (!els.quickMenu.contains(e.target) && e.target !== els.btnQuick) els.quickMenu.classList.remove('show');
                    if (!els.smartWrapper.contains(e.target) && !els.suggestions.contains(e.target)) {
                        els.suggestions.classList.remove('show');
                        if (els.smartInput.value.trim() === '') collapseSearch();
                    }
                });
            });

            // ── 全局 Status Popover（在主 script 里，与 fetchData 同作用域）────────────
            const _gPop = document.getElementById('globalStatusPopover');
            let _gAppId = null, _gBadgeEl = null;

            function showGlobalPopover(badge, appId, currentStatus) {
                if (_gBadgeEl === badge && _gPop.classList.contains('show')) {
                    closeGlobalPopover(); return;
                }
                _gAppId = appId; _gBadgeEl = badge;
                _gPop.querySelectorAll('.status-option').forEach(opt => {
                    opt.classList.toggle('active', parseInt(opt.dataset.val) === parseInt(currentStatus));
                });
                const rect = badge.getBoundingClientRect();
                _gPop.style.top = (rect.bottom + 4) + 'px';
                _gPop.style.left = (rect.left + rect.width / 2 - 55) + 'px';
                _gPop.classList.add('show');
            }
            function closeGlobalPopover() {
                _gPop.classList.remove('show');
                _gBadgeEl = null; _gAppId = null;
            }

            // 全局 popover 选项点击
            _gPop.addEventListener('click', async (e) => {
                const opt = e.target.closest('.status-option');
                if (!opt || _gAppId === null) return;
                e.preventDefault(); e.stopPropagation();
                const id = _gAppId, newStatus = parseInt(opt.dataset.val);
                closeGlobalPopover();
                try {
                    const res = await fetch('hireapi.php', {
                        method: 'PUT', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, status: newStatus })
                    });
                    const json = await res.json();
                    if (json.code === 200) {
                        // 同步更新 rawData，让 chip 计数立即反映新状态
                        const r = rawData.find(x => x.id == id);
                        if (r) r.status = String(newStatus);
                        state.page = 1;        // 状态变更后回到第 1 页
                        applyState();          // 重建 chips + 刷新数据
                    }
                } catch (err) { console.warn('状态更新失败', err); }
            });
            document.addEventListener('click', (e) => { if (!_gPop.contains(e.target)) closeGlobalPopover(); });
            document.addEventListener('scroll', closeGlobalPopover, true);

            // ── API 拉取函数 ─────────────────────────────────────────────────────────
            async function loadRawData() {
                try {
                    const res = await fetch('hireapi.php?action=list&page_size=2000&allow_large=1');
                    const json = await res.json();
                    if (json.code === 200) rawData = json.data.list;
                } catch (e) { console.warn('loadRawData failed', e); }
            }

            async function fetchStats() {
                try {
                    const res = await fetch('hireapi.php?action=stats');
                    const json = await res.json();
                    if (json.code === 200) { statsData = json.data; }
                } catch (e) { console.warn('统计加载失败', e); }
            }

            async function fetchData() {
                els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">\u6570\u636e\u52a0\u8f7d\u4e2d\u2026</td></tr>`;
                const params = new URLSearchParams({ action: 'list', page: state.page, page_size: state.pageSize });
                if (state.company) params.set('company', state.company);
                if (state.jobTitle) params.set('job_title', state.jobTitle);
                if (state.status !== '') params.set('status', state.status);
                if (state.keyword) params.set('keyword', state.keyword);
                if (state.dateStart) params.set('date_start', state.dateStart);
                if (state.dateEnd) params.set('date_end', state.dateEnd);
                try {
                    const res = await fetch('hireapi.php?' + params.toString());
                    const json = await res.json();
                    if (json.code === 200) {
                        const isInitialLoad = !window._firstLoadDone;
                        window._firstLoadDone = true;
                        allData = json.data.list;

                        // \u66f4\u65b0\u5206\u9875\u4fe1\u606f
                        const total = json.data.total ?? allData.length;
                        const totalPages = json.data.total_pages ?? 1;
                        pagination.total = total;
                        pagination.totalPages = totalPages;

                        renderTable(allData);
                        updatePaginationUI();
                        await fetchStats();
                        renderChips();
                        updateChipCounts();
                        renderActiveTags();
                        // \u9996\u6b21\u52a0\u8f7d\u65f6\u89e6\u53d1 toast \u901a\u77e5
                        if (isInitialLoad) {
                            const pendingCount = rawData.filter(r => String(r.status) === '0').length;
                            document.dispatchEvent(new CustomEvent('hireDataLoaded', { detail: { pendingCount } }));
                        }
                    } else {
                        els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">\u52a0\u8f7d\u5931\u8d25\uff1a${json.msg}</td></tr>`;
                    }
                } catch (e) {
                    els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">\u7f51\u7edc\u9519\u8bef\uff0c\u8bf7\u5237\u65b0\u9875\u9762\u91cd\u8bd5</td></tr>`;
                }
            }

            function updatePaginationUI() {
                const { total, totalPages } = pagination;
                // 只有 1 页时隐藏整个分页栏
                els.pageControls.style.display = totalPages <= 1 ? 'none' : 'flex';
                els.currentPageNum.textContent = state.page;
                // 显示当前页条数（不是总数）
                const pageCount = allData.length;
                els.totalCountInfo.textContent = `共计 ${pageCount} 条记录`;
                // 第 1 页时禁用上一页
                els.btnPrev.disabled = state.page <= 1;
                els.btnPrev.style.opacity = state.page <= 1 ? '0.4' : '1';
                // 最后一页时禁用下一页
                els.btnNext.disabled = state.page >= totalPages;
                els.btnNext.style.opacity = state.page >= totalPages ? '0.4' : '1';
            }


            function applyState() {
                state.page = 1;         // 切换过滤时回到第 1 页
                renderChips();
                renderActiveTags();
                fetchData();        // 拉数据，完成后只更新计数，不重建 chips
            }

            // 仅更新状态 chip 的计数数字，不重建 DOM
            function updateChipCounts() {
                // 状态计数基数：rawData 按当前公司/职位过滤（不受已选状态影响）
                const statusBase = rawData.filter(r => {
                    if (state.company && r.company_name !== state.company) return false;
                    if (state.jobTitle && r.job_title !== state.jobTitle) return false;
                    return true;
                });
                document.querySelectorAll('.chip[data-chip-status]').forEach(btn => {
                    const span = btn.querySelector('.chip-count');
                    if (!span) return;
                    const v = btn.getAttribute('data-chip-status');
                    span.textContent = v === ''
                        ? statusBase.length
                        : statusBase.filter(r => String(r.status) === v).length;
                });
            }

            function renderChips() {
                // 1. 公司
                els.chipListCompany.innerHTML = '';
                els.chipListCompany.appendChild(createChip('company', '', '全部', '🏢'));
                Object.keys(companyJobsMap).forEach(c => {
                    els.chipListCompany.appendChild(createChip('company', c, c, ''));
                });
                // 2. 职位（联动公司）
                els.chipListJob.innerHTML = '';
                const jobs = state.company
                    ? companyJobsMap[state.company]
                    : [...new Set(Object.values(companyJobsMap).flat())];
                jobs.forEach(j => {
                    els.chipListJob.appendChild(createChip('jobTitle', j, j, ''));
                });
                // 3. 状态
                els.chipListStatus.innerHTML = '';
                els.chipListStatus.appendChild(createChip('status', '', '全部', '📊'));
                statusConfig.forEach(cfg => {
                    els.chipListStatus.appendChild(createChip('status', cfg.val, cfg.label, cfg.icon));
                });
            }

            // 创建单个 Chip（所有类型都显示计数）
            function createChip(type, value, label, icon) {
                const btn = document.createElement('button');
                const isActive = state[type] !== '' && state[type].toString() === value.toString();
                const isAllButton = value === '';
                const active = isAllButton ? state[type] === '' : isActive;

                btn.className = `chip ${active ? 'active' : ''}`;

                // 计算每个 chip 的数量
                let cnt = 0;
                if (type === 'status') {
                    // 状态计数基数：rawData 按当前公司/职位过滤
                    // 不受已选状态影响，移除后不会整行崩零
                    const statusBase = rawData.filter(r => {
                        if (state.company && r.company_name !== state.company) return false;
                        if (state.jobTitle && r.job_title !== state.jobTitle) return false;
                        return true;
                    });
                    cnt = isAllButton
                        ? statusBase.length
                        : statusBase.filter(r => String(r.status) === String(value)).length;
                    btn.setAttribute('data-chip-status', String(value));
                } else if (type === 'company') {
                    // 公司：用 rawData（全量）统计 → 无论当前选检哪个公司，其他公司计数不变
                    cnt = isAllButton
                        ? rawData.length
                        : rawData.filter(r => r.company_name === value).length;
                } else if (type === 'jobTitle') {
                    // 职位：用 rawData，但若有公司过滤则局限在该公司的 rawData
                    const base = state.company
                        ? rawData.filter(r => r.company_name === state.company)
                        : rawData;
                    cnt = base.filter(r => r.job_title === value).length;
                }

                const countHtml = `<span class="chip-count">${cnt}</span>`;
                btn.innerHTML = `${icon ? `<span style="margin-right:2px">${icon}</span>` : ''}${label}${countHtml}`;

                btn.onclick = () => {
                    state[type] = (active && !isAllButton) ? '' : value;
                    if (type === 'company') state.jobTitle = '';
                    applyState();
                };
                return btn;
            }

            // ── Export Excel (UTF-8 BOM CSV) ─────────────────────────────────────────
            window.exportToExcel = function () {
                if (!allData || allData.length === 0) { showToast('没有可导出的数据', 'warning'); return; }
                var statusLabel = { 0: '待处理', 1: '沟通中', 2: '已录用', 3: '已淘汰' };
                var headers = ['序号', '中文姓名', '英文姓名', '性别', '申请公司', '申请职位',
                    '邮箱', '电话区号', '电话号码', '简历链接', '状态', 'HR备注', '申请时间'];
                var rows = allData.map(function (r, i) {
                    return [
                        i + 1, r.chinese_name || '', r.english_name || '', r.gender || '',
                        r.company_name || '', r.job_title || '', r.email || '',
                        r.phone_code || '', r.phone_number || '',
                        r.resume_file_url ? (location.origin + '/backend/' + r.resume_file_url) : '',
                        statusLabel[r.status] !== undefined ? statusLabel[r.status] : String(r.status),
                        r.hr_remarks || '', (r.created_at || '').replace('T', ' ')
                    ];
                });
                function esc(v) {
                    var s = String(v).replace(/"/g, '""');
                    return (/[",\n\r]/.test(s)) ? ('"' + s + '"') : s;
                }
                var csv = [headers].concat(rows).map(function (row) { return row.map(esc).join(','); }).join('\r\n');
                var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                var now = new Date();
                var ts = '' + now.getFullYear() + String(now.getMonth() + 1).padStart(2, '0') + String(now.getDate()).padStart(2, '0');
                a.href = url; a.download = '招聘申请列表_' + ts + '.csv';
                document.body.appendChild(a); a.click();
                document.body.removeChild(a); URL.revokeObjectURL(url);
            };

            function renderActiveTags() {
                els.activeList.innerHTML = '';
                let hasActive = false;

                const addTag = (label, type) => {
                    hasActive = true;
                    const span = document.createElement('span');
                    span.className = 'active-tag';
                    span.innerHTML = `${label} <span class="active-tag-close" onclick="removeFilter('${type}')">&times;</span>`;
                    els.activeList.appendChild(span);
                };

                if (state.keyword) addTag(`关键词: ${state.keyword}`, 'keyword');
                if (state.company) addTag(`公司: ${state.company}`, 'company');
                if (state.jobTitle) addTag(`职位: ${state.jobTitle}`, 'jobTitle');
                if (state.status !== '') {
                    const sLabel = statusConfig.find(c => c.val == state.status).label;
                    addTag(`状态: ${sLabel}`, 'status');
                }
                if (state.dateStart) addTag(`日期: ${state.dateLabel}`, 'date');

                els.activeArea.style.display = hasActive ? 'flex' : 'none';
            }

            window.removeFilter = function (type) {
                if (type === 'keyword') {
                    state.keyword = ''; els.smartInput.value = ''; collapseSearch();
                } else if (type === 'date') {
                    state.dateStart = ''; state.dateEnd = ''; state.dateLabel = ''; fpInstance.clear();
                } else {
                    state[type] = '';
                    if (type === 'company') state.jobTitle = '';
                }
                applyState();
            }

            window.resetAllFilters = function () {
                state = { keyword: '', company: '', jobTitle: '', status: '', dateStart: '', dateEnd: '', dateLabel: '', page: 1, pageSize: 20 };
                els.smartInput.value = ''; collapseSearch(); fpInstance.clear();
                applyState();
            }

            function expandSearch() {
                els.smartWrapper.classList.add('expanded'); setTimeout(() => els.smartInput.focus(), 150);
                isSearchExpanded = true;
            }
            function collapseSearch() {
                els.smartWrapper.classList.remove('expanded'); isSearchExpanded = false;
            }

            function generateSuggestions(keyword) {
                els.suggestionList.innerHTML = '';
                // 从已加载的 allData 里做客户端匹配建议
                const matches = allData.filter(app =>
                    (app.chinese_name || '').toLowerCase().includes(keyword.toLowerCase()) ||
                    (app.email || '').toLowerCase().includes(keyword.toLowerCase()) ||
                    (app.phone_number || '').includes(keyword)
                ).slice(0, 3);

                if (matches.length === 0) {
                    els.suggestionList.innerHTML = `<div style="padding: 10px 12px; font-size: 12px; color: #9ca3af;">无精准匹配，按 Enter 直接搜索</div>`;
                    return;
                }

                matches.forEach(match => {
                    const phone = match.phone_code ? `${match.phone_code} ${match.phone_number}` : match.phone_number;
                    const a = document.createElement('a'); a.href = '#'; a.className = 'suggest-item';
                    a.innerHTML = `<span style="font-weight:bold;">${match.chinese_name}</span> <span style="font-size:12px;color:#9ca3af;">(${phone})</span>`;
                    a.addEventListener('click', (e) => {
                        e.preventDefault(); els.smartInput.value = match.chinese_name; els.suggestions.classList.remove('show');
                        state.keyword = match.chinese_name; fetchData();
                    });
                    els.suggestionList.appendChild(a);
                });
            }

            function initDatePicker() {
                fpInstance = flatpickr(els.datePicker, {
                    mode: "range", locale: "zh", dateFormat: "Y年m月d日",
                    appendTo: document.body,
                    static: false,
                    onOpen: function (_, __, instance) {
                        // 下一帧等 flatpickr 渲染完后再定位
                        requestAnimationFrame(() => positionCalendar(instance));
                    },
                    onChange: function (selectedDates) {
                        if (selectedDates.length === 2) {
                            state.dateStart = formatDate(selectedDates[0]);
                            state.dateEnd = formatDate(selectedDates[1]);
                            state.dateLabel = `${state.dateStart} 至 ${state.dateEnd}`;
                            applyState();
                        } else if (selectedDates.length === 0) {
                            state.dateStart = ''; state.dateEnd = ''; state.dateLabel = '';
                            applyState();
                        }
                    }
                });

                // 滚动 / 缩放时重新定位
                const reposition = () => {
                    if (fpInstance && fpInstance.isOpen) positionCalendar(fpInstance);
                };
                window.addEventListener('scroll', reposition, true);
                window.addEventListener('resize', reposition);
            }

            function positionCalendar(instance) {
                const cal = instance.calendarContainer;
                const input = els.datePicker;
                const rect = input.getBoundingClientRect();
                // position: fixed 相对于 viewport，直接用 getBoundingClientRect 的坐标
                cal.style.position = 'fixed';
                cal.style.top = (rect.bottom + 4) + 'px';
                cal.style.left = rect.left + 'px';
                cal.style.width = rect.width + 'px';
                cal.style.zIndex = '99999';
                cal.style.margin = '0';
            }

            function formatDate(date) {
                const y = date.getFullYear(); const m = String(date.getMonth() + 1).padStart(2, '0'); const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function setQuickDate(type, label) {
                if (type === 'all') { removeFilter('date'); return; }
                const now = new Date(); let start, end; now.setHours(0, 0, 0, 0);
                switch (type) {
                    case 'today': start = new Date(now); end = new Date(now); break;
                    case 'yesterday': start = new Date(now); start.setDate(now.getDate() - 1); end = new Date(start); break;
                    case 'thisWeek': const day = now.getDay() || 7; start = new Date(now); start.setDate(now.getDate() - day + 1); end = new Date(now); break;
                    case 'lastWeek': const lastWeekDay = now.getDay() || 7; end = new Date(now); end.setDate(now.getDate() - lastWeekDay); start = new Date(end); start.setDate(end.getDate() - 6); break;
                    case 'thisMonth': start = new Date(now.getFullYear(), now.getMonth(), 1); end = new Date(now); break;
                }
                fpInstance.setDate([start, end]);
                state.dateStart = formatDate(start); state.dateEnd = formatDate(end); state.dateLabel = label;
                els.quickMenu.classList.remove('show'); applyState();
            }

            // 这两个函数不再需要，保留占位避免変量未定义截断表达式错误
            function getFilteredData() { return allData; }
            function getFilteredCount(s) { return 0; }   // chip 数量已由 statsData 提供

            function renderTable(list) {
                els.tableBody.innerHTML = '';
                if (list.length === 0) {
                    els.tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">没有找到匹配的记录</td></tr>`;
                    els.totalCountInfo.textContent = `共计加载 0 条数据`;
                    return;
                }

                list.forEach(app => {
                    const clone = els.rowTemplate.content.cloneNode(true);
                    const createdAt = app.created_at || '';
                    const [datePart, timePart] = createdAt.split('T').length > 1
                        ? createdAt.replace('T', ' ').split(' ')   // ISO 格式
                        : createdAt.split(' ');                     // 普通格式

                    // 应聘者姓名
                    clone.querySelector('.js-name').textContent = `${app.chinese_name || ''} (${app.english_name || ''})`;
                    clone.querySelector('.js-subname').textContent = app.gender || '';
                    clone.querySelector('.js-company').textContent = app.company_name || '';
                    clone.querySelector('.js-job-title').textContent = app.job_title || '';

                    // 联系方式
                    clone.querySelector('.js-email').textContent = `✉️ ${app.email || ''}`;
                    const phone = app.phone_code ? `${app.phone_code} ${app.phone_number}` : (app.phone_number || '');
                    clone.querySelector('.js-phone').textContent = `📞 ${phone}`;

                    // 时间
                    clone.querySelector('.js-date').textContent = datePart || '';
                    clone.querySelector('.js-time').textContent = timePart || '';

                    // 简历预览按鈕
                    const resumeBtn = clone.querySelector('.js-resume');
                    if (app.resume_file_url) {
                        resumeBtn.onclick = () => window.open('/backend/resume.php?id=' + app.id, '_blank');
                    } else {
                        resumeBtn.textContent = '无附件';
                        resumeBtn.style.opacity = '0.4';
                        resumeBtn.style.pointerEvents = 'none';
                    }

                    // 状态徽章 — 用全局 popover portal
                    const badge = clone.querySelector('.js-status-badge');
                    updateBadgeUI(badge, app.status);

                    badge.addEventListener('click', (e) => {
                        e.stopPropagation();
                        showGlobalPopover(badge, app.id, app.status);
                    });

                    clone.querySelector('.btn-action-detail').addEventListener('click', () => openModal(app.id));
                    els.tableBody.appendChild(clone);
                });
                // totalCountInfo 由 updatePaginationUI 统一更新
            }

            function updateBadgeUI(badgeElement, statusVal, popoverElement) {
                badgeElement.className = 'badge js-status-badge';
                switch (Number(statusVal)) {
                    case 0: badgeElement.textContent = '待处理'; badgeElement.classList.add('badge-red'); break;
                    case 1: badgeElement.textContent = '沟通中'; badgeElement.classList.add('badge-yellow'); break;
                    case 2: badgeElement.textContent = '已录用'; badgeElement.classList.add('badge-green'); break;
                    default: badgeElement.textContent = '已淘汰'; badgeElement.classList.add('badge-gray'); break;
                }

                if (popoverElement) {
                    popoverElement.querySelectorAll('.status-option').forEach(opt => {
                        if (parseInt(opt.dataset.val) === Number(statusVal)) {
                            opt.classList.add('active');
                        } else {
                            opt.classList.remove('active');
                        }
                    });
                }
            }

            window.toggleDrawer = function (show) {
                if (show) {
                    els.drawer.classList.add('drawer-open');
                    els.drawerOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                } else {
                    els.drawer.classList.remove('drawer-open');
                    els.drawerOverlay.classList.remove('show');
                    document.body.style.overflow = '';
                }
            }

            function openModal(id) {
                currentEditingId = id;
                const app = allData.find(item => item.id === id);
                if (!app) return;

                document.getElementById('modalCompany').textContent = app.company_name || '';
                document.getElementById('modalJob').textContent = app.job_title || '';
                document.getElementById('modalTime').textContent = (app.created_at || '').replace('T', ' ');
                document.getElementById('modalZhName').textContent = app.chinese_name || '';
                document.getElementById('modalEnName').textContent = app.english_name || '';
                document.getElementById('modalGender').textContent = app.gender || '';

                const emailLink = document.getElementById('modalEmailLink');
                emailLink.textContent = app.email || '';
                emailLink.href = `mailto:${app.email}`;

                const phone = app.phone_code ? `${app.phone_code} ${app.phone_number}` : (app.phone_number || '');
                document.getElementById('modalPhone').textContent = phone;

                // 简历按鈕
                const resumeBtn = document.getElementById('modalResumeBtn');
                if (app.resume_file_url) {
                    resumeBtn.onclick = () => window.open('/backend/resume.php?id=' + app.id, '_blank');
                    resumeBtn.style.opacity = '1';
                } else {
                    resumeBtn.textContent = '无简历附件';
                    resumeBtn.style.opacity = '0.4';
                    resumeBtn.onclick = null;
                }

                els.modalStatusSelect.value = app.status ?? 0;
                els.modalRemarks.value = app.hr_remarks || '';
                els.modal.classList.add('active');
            }

            window.closeModal = function () {
                els.modal.classList.remove('active'); currentEditingId = null;
            }

            window.saveModalChanges = async function () {
                if (currentEditingId === null) return;
                const status = parseInt(els.modalStatusSelect.value);
                const hr_remarks = els.modalRemarks.value;

                try {
                    const res = await fetch('hireapi.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: currentEditingId, status, hr_remarks })
                    });
                    const json = await res.json();
                    if (json.code === 200) {
                        // 同步内存
                        const idx = allData.findIndex(item => item.id === currentEditingId);
                        if (idx > -1) { allData[idx].status = status; allData[idx].hr_remarks = hr_remarks; }
                        renderTable(allData);
                        await fetchStats();
                        renderChips();
                        closeModal();
                    } else {
                        showToast('保存失败：' + json.msg, 'error');
                    }
                } catch (err) {
                    showToast('网络错误，保存失败', 'error');
                }
            }
        </script>

        <!-- 待处理申请通知 Toast -->
        <div id="pendingToast">
            <div class="toast-icon">🔔</div>
            <div class="toast-body">
                <div class="toast-title">有待审批的招聘申请</div>
                <div class="toast-msg" id="toastMsg">正在加载...</div>
            </div>
            <button class="toast-close" onclick="dismissToast()" title="关闭">×</button>
            <div class="toast-progress" id="toastProgress"></div>
        </div>

        <script>
            let toastTimer = null;
            function showPendingToast(count) {
                if (count <= 0) return;
                const toast = document.getElementById('pendingToast');
                const msg = document.getElementById('toastMsg');
                const prog = document.getElementById('toastProgress');
                msg.textContent = `共有 ${count} 位申请人待处理，请及时审批。`;
                // reset progress bar
                prog.style.animation = 'none';
                prog.offsetHeight;  // reflow
                prog.style.animation = '';
                toast.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(dismissToast, 6000);
            }
            function dismissToast() {
                clearTimeout(toastTimer);
                const toast = document.getElementById('pendingToast');
                toast.classList.remove('show');
            }
            // 在数据加载完毕后触发 toast（利用 fetchData 事件）
            document.addEventListener('hireDataLoaded', (e) => {
                showPendingToast(e.detail.pendingCount);
            });
        </script>
    </div><!-- /.main-content -->
</body>

</html>