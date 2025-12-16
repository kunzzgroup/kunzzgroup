<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List - J3 Mobile</title>
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
            padding: 0;
            margin: 0;
        }
        
        .header-section {
            background-color: #faf7f2;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            color: #111827;
            margin-bottom: 12px;
        }
        
        .header-section h1 {
            font-size: 28px;
            font-weight: bold;
            color: #000000;
            margin: 0;
        }
        
        .filter-section {
            background-color: #faf7f2;
            padding: 16px;
        }
        
        .filter-row {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .filter-item label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
        }
        
        .filter-select,
        .filter-input {
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            color: #111827;
            width: 100%;
        }
        
        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 2px rgba(88, 62, 4, 0.1);
        }
        
        .search-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        
        .search-input-container {
            flex: 1;
        }
        
        .search-btn {
            background-color: #f99e00;
            color: #000000;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        
        .search-btn:hover {
            background-color: #f98500;
            transform: translateY(-1px);
        }
        
        .date-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        
        .date-input-container {
            flex: 1;
        }
        
        .confirm-btn {
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        
        .confirm-btn:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }
        
        .table-container {
            background: white;
            margin: 0;
            overflow-x: auto;
        }
        
        .stock-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .stock-table thead {
            background-color: #636363;
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .stock-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #d1d5db;
        }
        
        .stock-table td {
            padding: 12px;
            border: 1px solid #d1d5db;
        }
        
        .stock-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .stock-table tbody tr:hover {
            background-color: #f3f4f6;
        }
        
        .qty-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .qty-input {
            border: none;
            background: transparent;
            font-size: 14px;
            padding: 4px 8px;
            width: 80px;
            text-align: right;
            pointer-events: none;
        }
        
        .qty-input.editing {
            pointer-events: auto;
            background: #fff;
            border: 1px solid #583e04;
            border-radius: 4px;
        }
        
        .qty-input.editing:focus {
            outline: 2px solid #583e04;
            outline-offset: -2px;
        }
        
        .action-buttons {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        
        .action-btn {
            width: 28px;
            height: 28px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s;
        }
        
        .edit-btn {
            background-color: #f59e0b;
            color: white;
        }
        
        .edit-btn:hover {
            background-color: #d97706;
            transform: scale(1.1);
        }
        
        .edit-btn.save-mode {
            background-color: #10b981;
        }
        
        .edit-btn.save-mode:hover {
            background-color: #059669;
        }
        
        .cancel-btn {
            background-color: #6b7280;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #4b5563;
            transform: scale(1.1);
        }
        
        .delete-btn {
            background-color: #ef4444;
            color: white;
        }
        
        .delete-btn:hover {
            background-color: #dc2626;
            transform: scale(1.1);
        }
        
        .save-all-btn {
            background-color: #583e04;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
            margin-top: 8px;
        }
        
        .save-all-btn:hover {
            background-color: #462d03;
            transform: translateY(-1px);
        }
        
        .save-all-btn:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        /* 响应式设计 */
        @media (min-width: 768px) {
            .header-section h1 {
                font-size: 32px;
            }
            
            .filter-row {
                flex-direction: row;
                align-items: flex-end;
            }
            
            .search-row,
            .date-row {
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="menu-icon" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>
        <h1>Stock List</h1>
        <div id="stocklist-total" style="font-size: 14px; color: #6b7280; margin-top: 8px;">总数: 加载中...</div>
    </div>
    
    <div class="filter-section">
        <div class="filter-row">
            <div class="filter-item">
                <label>冰箱分类</label>
                <select class="filter-select" id="freezer-category-select" onchange="handleCategoryChange()">
                    <option value="">全部</option>
                    <option value="K1-1">K1-1</option>
                    <option value="K1-2">K1-2</option>
                    <option value="K1-3">K1-3</option>
                    <option value="K1-4">K1-4</option>
                    <option value="K1-5">K1-5</option>
                    <option value="K1-6">K1-6</option>
                    <option value="K1-7">K1-7</option>
                    <option value="C-1">C-1</option>
                    <option value="KDI-1">KDI-1</option>
                    <option value="KDI-2">KDI-2</option>
                    <option value="KDI-3">KDI-3</option>
                    <option value="KDI-4">KDI-4</option>
                    <option value="S1-1">S1-1</option>
                    <option value="S1-2">S1-2</option>
                    <option value="S1-3">S1-3</option>
                    <option value="S1-4">S1-4</option>
                    <option value="SBS-1">SBS-1</option>
                    <option value="SBS-2">SBS-2</option>
                    <option value="SBDI-1">SBDI-1</option>
                    <option value="SBDI-2">SBDI-2</option>
                </select>
            </div>
        </div>
        
        <div class="filter-row">
            <div class="search-row">
                <div class="search-input-container filter-item">
                    <label>Search</label>
                    <input type="text" class="filter-input" id="search-input" placeholder="Search by Item's Code/Name">
                </div>
                <button class="search-btn" onclick="handleSearch()">Search</button>
            </div>
        </div>
        
        <div class="filter-row">
            <div class="date-row">
                <div class="date-input-container filter-item">
                    <label>Date</label>
                    <input type="text" class="filter-input" id="date-input" placeholder="DD/MM/YYYY" readonly>
                    <input type="date" id="date-picker" style="display: none;" onchange="updateDateDisplay(this.value)">
                </div>
                <button class="confirm-btn" onclick="handleConfirm()">Confirm</button>
            </div>
        </div>
        
        <div class="filter-row" style="justify-content: center;">
            <button class="save-all-btn" id="save-all-btn" onclick="handleSaveAll()" disabled>Save All</button>
        </div>
    </div>
    
    <div class="table-container">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Qty</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody id="stock-tbody">
                <tr>
                    <td class="loading" colspan="3">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        // 全局变量
        let productList = [];
        let stockData = [];
        let selectedDate = '';
        let selectedK1 = 'K1';
        let selectedFreezerCategory = ''; // 选中的冰箱分类
        let editingRowIds = new Set(); // 跟踪正在编辑的行
        let originalEditData = new Map(); // 保存原始数据用于取消编辑
        
        // API配置 - 连接j3stockproductname.php使用的API
        const API_BASE_URL = '../stockapi.php';
        const SYSTEM_TYPE = 'J3';
        const STOCK_EDIT_API = 'j3stockeditmobile_api.php';
        const STOCK_LIST_API = '../backend/j3stocklistapi.php';
        
        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 设置默认日期为今天
            const today = new Date();
            const dateStr = today.toISOString().split('T')[0];
            document.getElementById('date-picker').value = dateStr;
            updateDateDisplay(dateStr);
            
            // 点击日期输入框打开日期选择器
            document.getElementById('date-input').addEventListener('click', function() {
                document.getElementById('date-picker').showPicker();
            });
            
            // 加载产品列表
            loadProductList();
        });
        
        // 更新日期显示
        function updateDateDisplay(dateStr) {
            selectedDate = formatDate(dateStr);
            document.getElementById('date-input').value = selectedDate;
        }
        
        // 切换菜单（占位函数）
        function toggleMenu() {
            // 这里可以添加侧边栏菜单的逻辑
            console.log('Menu toggled');
        }
        
        // 格式化日期显示
        function formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
        
        // 从j3stockproductname.php获取产品列表
        async function loadProductList() {
            try {
                // 构建API请求 - 使用与j3stockproductname.php相同的方式获取产品列表
                const params = new URLSearchParams();
                params.append('action', 'list');
                            params.append('system_assign', SYSTEM_TYPE);
            
            // 读取当前冰箱分类选择（客户端过滤，不传到API）
            const freezerCategorySelect = document.getElementById('freezer-category-select');
            const currentFreezerCategory = freezerCategorySelect ? freezerCategorySelect.value : (selectedFreezerCategory || '');
                
                const apiUrl = `${API_BASE_URL}?${params.toString()}`;
                console.log('完整的API请求URL:', apiUrl); // 调试日志
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP错误: ${response.status}`);
                }
                
                const responseText = await response.text();
                const result = JSON.parse(responseText);
                
                if (result.success) {
                    productList = result.data || [];
                    console.log('接收到产品数据数量:', productList.length); // 调试日志
                    if (currentFreezerCategory) {
                        // 客户端按多分类过滤：支持逗号分隔
                        const selected = currentFreezerCategory.trim();
                        const matchesCategory = (val) => {
                            if (!val) return false;
                            const parts = String(val).split(',').map(v => v.trim()).filter(Boolean);
                            return parts.includes(selected);
                        };
                        productList = productList.filter(p => matchesCategory(p.freezer_category));
                        console.log('应用客户端分类过滤后数量:', productList.length);
                    } else {
                        // 显示所有记录的 freezer_category 值（用于调试）
                        const allCategories = [...new Set(productList.map(p => p.freezer_category).filter(c => c))];
                        console.log('所有产品中的 freezer_category 值:', allCategories);
                    }
                    stockData = productList.map(item => ({
                        id: item.id,
                        product_code: item.product_code || '',
                        product_name: item.product_name || '',
                        freezer_category: item.freezer_category || '',
                        qty: '0.00',
                        original_qty: '0.00' // 稍后用库存总数覆盖
                    }));
                    
                    // 读取库存总数并合并到列表
                    try {
                        const totalsResp = await fetch(`${STOCK_EDIT_API}?action=stocklist_total`);
                        const totalsJson = await totalsResp.json();
                        if (totalsJson.success && totalsJson.data) {
                            const items = totalsJson.data.items || [];
                            const keyOf = (name, code) => `${(name||'').trim()}|${(code||'').trim()}`;
                            const totalMap = new Map(items.map(it => [keyOf(it.product_name, it.code_number), parseFloat(it.total_qty || 0).toFixed(3)]));
                            stockData = stockData.map(it => {
                                const key = keyOf(it.product_name, it.product_code);
                                const qty = totalMap.get(key) || '0.00';
                                return { ...it, qty, original_qty: qty };
                            });
                        }
                    } catch (e) {
                        console.warn('合并库存总数失败:', e);
                    }
                    generateTable();
                    
                    // 加载库存总数
                    await loadStocklistTotal();
                } else {
                    throw new Error(result.message || '加载失败');
                }
                
            } catch (error) {
                console.error('加载产品列表失败:', error);
                document.getElementById('stock-tbody').innerHTML = `
                    <tr>
                        <td class="empty-state" colspan="3">加载失败: ${error.message}</td>
                    </tr>
                `;
            }
        }
        
        // 处理冰箱分类变化
function handleCategoryChange() {
    const selectElement = document.getElementById('freezer-category-select');
    if (selectElement) {
        selectedFreezerCategory = selectElement.value;
        console.log('冰箱分类已更改:', selectedFreezerCategory); // 调试日志
        loadProductList();
    }
}
        
        // 生成表格
        function generateTable() {
            const tbody = document.getElementById('stock-tbody');
            
            if (stockData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td class="empty-state" colspan="3">没有找到产品</td>
                    </tr>
                `;
                return;
            }
            
            // 根据搜索条件过滤数据
            const searchTerm = document.getElementById('search-input').value.toLowerCase().trim();
            const filteredData = stockData.filter(item => {
                if (!searchTerm) return true;
                const code = (item.product_code || '').toLowerCase();
                const name = (item.product_name || '').toLowerCase();
                return code.includes(searchTerm) || name.includes(searchTerm);
            });
            
            // 按产品名称排序
            filteredData.sort((a, b) => {
                const nameA = (a.product_name || '').toLowerCase();
                const nameB = (b.product_name || '').toLowerCase();
                return nameA.localeCompare(nameB);
            });
            
            tbody.innerHTML = filteredData.map((item, index) => {
                const isEditing = editingRowIds.has(item.id);
                return `
                <tr>
                    <td>${escapeHtml(item.product_name || '')}</td>
                    <td class="qty-cell">
                        <input 
                            type="number" 
                            class="qty-input ${isEditing ? 'editing' : ''}" 
                            value="${item.qty}" 
                            step="0.01"
                            data-id="${item.id}"
                            data-original="${item.original_qty}"
                            onchange="updateQty(${item.id}, this.value)"
                            onfocus="this.select()"
                            ${isEditing ? '' : 'readonly'}
                        >
                    </td>
                    <td>
                        <div class="action-buttons">
                            ${isEditing ? 
                                `<button class="action-btn edit-btn save-mode" onclick="saveRecord(${item.id})" title="保存">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button class="action-btn cancel-btn" onclick="cancelEdit(${item.id})" title="取消">
                                    <i class="fas fa-times"></i>
                                </button>` :
                                `<button class="action-btn edit-btn" onclick="editRecord(${item.id})" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteRecord(${item.id})" title="删除">
                                    <i class="fas fa-trash"></i>
                                </button>`
                            }
                        </div>
                    </td>
                </tr>
            `;
            }).join('');
            
            // 更新保存按钮状态
            updateSaveAllButton();
        }
        
        // 编辑记录
        function editRecord(id) {
            // 如果已经在编辑中，直接返回
            if (editingRowIds.has(id)) {
                return;
            }
            
            editingRowIds.add(id);
            
            // 保存原始数据的深拷贝
            const record = stockData.find(r => r.id == id);
            if (record) {
                originalEditData.set(id, JSON.parse(JSON.stringify(record)));
            }
            
            generateTable();
        }
        
        // 取消编辑
        function cancelEdit(id) {
            if (id !== null) {
                // 恢复原始数据
                if (originalEditData && originalEditData.has(id)) {
                    const recordIndex = stockData.findIndex(r => r.id === id);
                    if (recordIndex !== -1) {
                        stockData[recordIndex] = JSON.parse(JSON.stringify(originalEditData.get(id)));
                    }
                    originalEditData.delete(id);
                }
                editingRowIds.delete(id);
            } else {
                // 取消所有编辑
                if (originalEditData) {
                    editingRowIds.forEach(editId => {
                        if (originalEditData.has(editId)) {
                            const recordIndex = stockData.findIndex(r => r.id === editId);
                            if (recordIndex !== -1) {
                                stockData[recordIndex] = JSON.parse(JSON.stringify(originalEditData.get(editId)));
                            }
                        }
                    });
                    originalEditData.clear();
                }
                editingRowIds.clear();
            }
            
            generateTable();
        }
        
        // 更新数量
        function updateQty(id, newQty) {
            const item = stockData.find(i => i.id == id);
            if (item) {
                item.qty = parseFloat(newQty) || 0;
                
                // 计算出货量 = 原始数量 - 当前剩余数量
                const originalQty = parseFloat(item.original_qty) || 0;
                const soldQty = originalQty - item.qty;
                
                console.log(`产品ID ${id}: 原始数量=${originalQty}, 当前数量=${item.qty}, 出货量=${soldQty}`);
            }
            
            // 更新保存按钮状态
            updateSaveAllButton();
        }
        
        // 保存单个记录
        async function saveRecord(id) {
            const record = stockData.find(r => r.id === id);
            if (!record) return;

            try {
                // 计算出货量
                const originalQty = parseFloat(record.original_qty) || 0;
                const currentQty = parseFloat(record.qty) || 0;
                const soldQty = originalQty - currentQty;
                
                // 检查数量不能增加
                if (currentQty > originalQty) {
                    alert('数量不能增加，只能减少！\n原始数量: ' + originalQty.toFixed(3) + '\n当前数量: ' + currentQty.toFixed(3));
                    // 恢复原始数量
                    record.qty = originalQty;
                    generateTable();
                    return;
                }
                
                // 如果数量没有变化，只更新原始数量，不创建记录
                if (soldQty === 0) {
                    record.original_qty = currentQty;
                    
                    // 退出编辑模式
                    editingRowIds.delete(id);
                    if (originalEditData) {
                        originalEditData.delete(id);
                    }
                    
                    generateTable();
                    alert(`数量未变化，已更新\n产品: ${record.product_name}`);
                    return;
                }
                
                // 数量减少，创建出库记录
                // 获取当前日期和时间
                const datePicker = document.getElementById('date-picker').value;
                if (!datePicker) {
                    alert('请先选择日期');
                    return;
                }
                
                const now = new Date();
                const timeStr = now.toTimeString().slice(0, 8); // HH:MM:SS
                
                // 创建出库记录
                const outboundData = {
                    date: datePicker,
                    time: timeStr,
                    product_name: record.product_name,
                    code_number: record.product_code || null,
                    in_quantity: 0,
                    out_quantity: soldQty
                };
                
                // 保存到数据库
                const response = await fetch(STOCK_EDIT_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(outboundData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // 更新原始数量为当前数量（用于下次计算）
                    record.original_qty = currentQty;
                    
                    // 更新本地库存总数
                    await updateStocklistTotal(record.product_name, record.product_code, 0, soldQty);
                    
                    // 退出编辑模式
                    editingRowIds.delete(id);
                    if (originalEditData) {
                        originalEditData.delete(id);
                    }
                    
                    generateTable();
                    
                    // 显示成功消息
                    alert(`记录已保存\n产品: ${record.product_name}\n出货量: ${soldQty.toFixed(3)}`);
                } else {
                    throw new Error(result.message || '保存失败');
                }
                
            } catch (error) {
                console.error('保存失败:', error);
                alert('保存失败: ' + error.message);
            }
        }
        
        // 删除记录
        async function deleteRecord(id) {
            if (!confirm('确定要删除这条记录吗？')) {
                return;
            }
            
            try {
                // TODO: 这里将来会从数据库删除
                // 目前只从内存中删除
                const recordIndex = stockData.findIndex(r => r.id === id);
                if (recordIndex !== -1) {
                    stockData.splice(recordIndex, 1);
                }
                
                // 如果正在编辑，也要清除编辑状态
                editingRowIds.delete(id);
                if (originalEditData) {
                    originalEditData.delete(id);
                }
                
                generateTable();
                alert('记录已删除');
                
            } catch (error) {
                console.error('删除失败:', error);
                alert('删除失败: ' + error.message);
            }
        }
        
        // 更新保存全部按钮状态
        function updateSaveAllButton() {
            const saveAllBtn = document.getElementById('save-all-btn');
            if (saveAllBtn) {
                saveAllBtn.disabled = editingRowIds.size === 0;
            }
        }
        
        // 保存所有更改
        async function handleSaveAll() {
            if (editingRowIds.size === 0) {
                alert('没有需要保存的更改');
                return;
            }
            
            if (!confirm(`确定要保存所有 ${editingRowIds.size} 条记录的更改吗？`)) {
                return;
            }
            
            try {
                const datePicker = document.getElementById('date-picker').value;
                
                if (!datePicker) {
                    alert('请先选择日期');
                    return;
                }
                
                const now = new Date();
                const timeStr = now.toTimeString().slice(0, 8);
                let successCount = 0;
                let failCount = 0;
                
                // 保存每条记录
                for (const id of editingRowIds) {
                    const record = stockData.find(r => r.id === id);
                    if (!record) continue;
                    
                    const originalQty = parseFloat(record.original_qty) || 0;
                    const currentQty = parseFloat(record.qty) || 0;
                    const soldQty = originalQty - currentQty;
                    
                    // 检查数量不能增加
                    if (currentQty > originalQty) {
                        failCount++;
                        // 恢复原始数量
                        record.qty = originalQty;
                        continue;
                    }
                    
                    // 如果数量没有变化，只更新原始数量，不创建记录
                    if (soldQty === 0) {
                        record.original_qty = currentQty;
                        successCount++;
                        continue;
                    }
                    
                    // 数量减少，创建出库记录
                    try {
                        const outboundData = {
                            date: datePicker,
                            time: timeStr,
                            product_name: record.product_name,
                            code_number: record.product_code || null,
                            in_quantity: 0,
                            out_quantity: soldQty
                        };
                        
                        const response = await fetch(STOCK_EDIT_API, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(outboundData)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            record.original_qty = currentQty;
                            await updateStocklistTotal(record.product_name, record.product_code, 0, soldQty);
                            successCount++;
                        } else {
                            failCount++;
                        }
                    } catch (error) {
                        console.error(`保存记录 ${record.product_name} 失败:`, error);
                        failCount++;
                    }
                }
                
                // 清除所有编辑状态
                editingRowIds.clear();
                if (originalEditData) {
                    originalEditData.clear();
                }
                
                generateTable();
                
                // 显示结果
                let message = `保存完成！\n日期: ${selectedDate}\n成功: ${successCount} 条`;
                if (failCount > 0) {
                    message += `\n失败: ${failCount} 条（数量不能增加）`;
                }
                alert(message);
                
            } catch (error) {
                console.error('保存失败:', error);
                alert('保存失败: ' + error.message);
            }
        }
        
        // 更新库存总数
        async function updateStocklistTotal(productName, codeNumber, inQty, outQty) {
            // 这个函数会在API端自动更新，这里只做本地UI更新
            await loadStocklistTotal();
        }
        
        // 加载库存总数
        async function loadStocklistTotal() {
            try {
                const response = await fetch(`${STOCK_EDIT_API}?action=stocklist_total`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    const totalQty = result.data.total_qty || '0.000';
                    const totalRecords = result.data.total_records || 0;
                    
                    // 更新显示（如果有显示总数的元素）
                    const totalDisplay = document.getElementById('stocklist-total');
                    if (totalDisplay) {
                        totalDisplay.textContent = `总数: ${totalQty} (${totalRecords} 项)`;
                    }
                }
            } catch (error) {
                console.error('加载库存总数失败:', error);
            }
        }
        
        // 搜索处理
        function handleSearch() {
            generateTable();
        }
        
        // 确认按钮处理
        function handleConfirm() {
            const datePicker = document.getElementById('date-picker').value;
            if (datePicker) {
                updateDateDisplay(datePicker);
                console.log('选择的日期:', selectedDate);
                
                // 计算出货量并准备保存数据
                const saveData = stockData.map(item => {
                    const originalQty = parseFloat(item.original_qty) || 0;
                    const currentQty = parseFloat(item.qty) || 0;
                    const soldQty = originalQty - currentQty;
                    
                    return {
                        id: item.id,
                        product_code: item.product_code,
                        product_name: item.product_name,
                        original_qty: originalQty,
                        current_qty: currentQty,
                        sold_qty: soldQty,
                        date: selectedDate
                    };
                });
                
                console.log('准备保存的数据:', saveData);
                
                // TODO: 这里将来会保存所有数据到数据库
                // 目前只记录数据
                alert(`日期已确认: ${selectedDate}\n共 ${saveData.length} 条记录\n（数据保存功能待实现）`);
            } else {
                alert('请选择日期');
            }
        }
        
        // HTML转义
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // 搜索框回车键支持
        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSearch();
            }
        });
        
        // 日期选择变化
        document.getElementById('date-picker').addEventListener('change', function(e) {
            updateDateDisplay(e.target.value);
        });
        
        // 冰箱分类选择变化（已在HTML中通过onchange处理）
    </script>
</body>
</html>

