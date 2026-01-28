<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存列表 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/stocklist.css">
</head>
<body>
    <div class="stocklist-page">
        <header class="page-header">
            <a class="logout-button" href="login.html" aria-label="退出登录">
                <img src="../images/icons/logout.svg" alt="" aria-hidden="true">
            </a>
            <h1>库存列表</h1>
        </header>

        <main class="page-content">
            <section class="form-section">
                <div class="select-group">
                    <label for="filter-type" class="sr-only">筛选类型</label>
                    <div class="select-wrapper">
                        <select id="filter-type" name="filter-type">
                            <option value="">选择筛选类型</option>
                            <option value="freezer">冰箱区</option>
                            <option value="category">货品类型</option>
                        </select>
                        <span class="select-icon" aria-hidden="true"></span>
                    </div>
                </div>

                <div class="select-group" id="filter-options-wrapper">
                    <label for="filter-options" class="sr-only">筛选选项</label>
                    <div class="select-wrapper">
                        <select id="filter-options" name="filter-options">
                            <option value="">全部</option>
                        </select>
                        <span class="select-icon" aria-hidden="true"></span>
                    </div>
                </div>

                <div class="input-group search-group">
                    <label for="search" class="sr-only">按商品名称搜索</label>
                    <input id="search" type="text" placeholder="按商品名称搜索">
                    <button class="btn btn-search" type="button" aria-label="搜索">
                        <img src="../images/icons/search.svg" alt="" aria-hidden="true">
                    </button>
                </div>
            </section>

            <section class="table-section" aria-labelledby="stock-table-title">
                <h2 id="stock-table-title" class="sr-only">库存明细</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col" class="product-code-column" style="width: 60px !important; min-width: 60px !important; max-width: 60px !important; text-align: left !important; padding: 10px 10px !important;">货品编号</th>
                                <th scope="col" style="text-align: left !important; width: 130px !important; min-width: 130px !important; max-width: 130px !important; padding: 10px 12px !important;">名字</th>
                                <th scope="col" style="width: 50px !important; min-width: 50px !important; max-width: 50px !important; padding: 10px 10px !important; text-align: right !important;">数量</th>
                                <th scope="col" class="actions-column" style="width: 35px !important; min-width: 35px !important; max-width: 35px !important; padding: 10px 4px !important;" aria-label="操作"></th>
                            </tr>
                        </thead>
                        <tbody id="stock-tbody">
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: #6b7280;">加载中...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // 全局变量
        let productList = [];
        let stockData = [];
        let currentFilterType = ''; // 'freezer' 或 'category'
        let selectedFilterValue = '';
        let editingRowIds = new Set();
        
        // API配置
        const API_BASE_URL = '../../stockapi.php';
        const SYSTEM_TYPE = 'J1';
        const STOCK_EDIT_API = '../../j1/j1stockeditmobile_api.php';
        
        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 筛选类型变化事件
            document.getElementById('filter-type').addEventListener('change', handleFilterTypeChange);
            
            // 筛选选项变化事件
            document.getElementById('filter-options').addEventListener('change', handleFilterOptionsChange);
            
            // 搜索按钮点击事件
            document.querySelector('.btn-search').addEventListener('click', handleSearch);
            
            // 搜索框回车键事件
            document.getElementById('search').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    handleSearch();
                }
            });
            
            // 加载产品列表
            loadProductList();
        });
        
        // 获取今天的日期字符串 (YYYY-MM-DD)
        function getTodayDateString() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        }
        
        // 从API获取产品列表
        async function loadProductList() {
            try {
                const params = new URLSearchParams();
                params.append('action', 'list');
                params.append('system_assign', SYSTEM_TYPE);
                
                const apiUrl = `${API_BASE_URL}?${params.toString()}`;
                console.log('完整的API请求URL:', apiUrl);
                
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
                    console.log('接收到产品数据数量:', productList.length);
                    
                    // 如果当前选择了冰箱区过滤，应用过滤
                    if (currentFilterType === 'freezer' && selectedFilterValue) {
                        const selected = selectedFilterValue.trim();
                        const matchesCategory = (val) => {
                            if (!val) return false;
                            const parts = String(val).split(',').map(v => v.trim()).filter(Boolean);
                            return parts.includes(selected);
                        };
                        productList = productList.filter(p => matchesCategory(p.freezer_category));
                        console.log('应用客户端分类过滤后数量:', productList.length);
                    }
                    
                    stockData = productList.map(item => ({
                        id: item.id,
                        product_code: item.product_code || '',
                        product_name: item.product_name || '',
                        freezer_category: item.freezer_category || '',
                        category: item.category || '',
                        qty: '0.00',
                        original_qty: '0.00'
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
                    
                    // 如果已经选择了筛选类型，更新选项列表
                    if (currentFilterType) {
                        updateFilterOptions();
                    }
                    
                    generateTable();
                } else {
                    throw new Error(result.message || '加载失败');
                }
                
            } catch (error) {
                console.error('加载产品列表失败:', error);
                document.getElementById('stock-tbody').innerHTML = `
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #ef4444;">
                            加载失败: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }
        
        // 处理筛选类型变化（冰箱区或类型）
        function handleFilterTypeChange() {
            const filterTypeSelect = document.getElementById('filter-type');
            const optionsWrapper = document.getElementById('filter-options-wrapper');
            const optionsSelect = document.getElementById('filter-options');
            
            if (!filterTypeSelect || !optionsWrapper || !optionsSelect) return;
            
            currentFilterType = filterTypeSelect.value;
            selectedFilterValue = '';
            
            if (currentFilterType === '') {
                // 没有选择筛选类型，隐藏选项列表
                optionsWrapper.classList.remove('show');
                optionsSelect.innerHTML = '<option value="">全部</option>';
            } else {
                // 显示选项列表并更新选项
                optionsWrapper.classList.add('show');
                updateFilterOptions();
            }
            
            // 重新加载产品列表（如果是冰箱区）或重新生成表格（如果是类型）
            if (currentFilterType === 'freezer') {
                loadProductList();
            } else if (currentFilterType === 'category') {
                generateTable();
            }
        }
        
        // 处理筛选选项变化
        function handleFilterOptionsChange() {
            const optionsSelect = document.getElementById('filter-options');
            if (!optionsSelect) return;
            
            selectedFilterValue = optionsSelect.value;
            
            if (currentFilterType === 'freezer') {
                // 冰箱区过滤需要重新加载产品列表
                loadProductList();
            } else if (currentFilterType === 'category') {
                // 货品类型过滤只需要重新生成表格
                generateTable();
            }
        }
        
        // 更新筛选选项列表
        function updateFilterOptions() {
            const optionsSelect = document.getElementById('filter-options');
            if (!optionsSelect || !currentFilterType) return;
            
            let options = [];
            
            if (currentFilterType === 'freezer') {
                // 冰箱区选项
                options = [
                    { value: 'K1-1', text: 'K1-1' },
                    { value: 'K1-2', text: 'K1-2' },
                    { value: 'K1-3', text: 'K1-3' },
                    { value: 'K1-4', text: 'K1-4' },
                    { value: 'K1-5', text: 'K1-5' },
                    { value: 'K1-6', text: 'K1-6' },
                    { value: 'K1-7', text: 'K1-7' },
                    { value: 'C-1', text: 'C-1' },
                    { value: 'KDI-1', text: 'KDI-1' },
                    { value: 'KDI-2', text: 'KDI-2' },
                    { value: 'KDI-3', text: 'KDI-3' },
                    { value: 'KDI-4', text: 'KDI-4' },
                    { value: 'S1-1', text: 'S1-1' },
                    { value: 'S1-2', text: 'S1-2' },
                    { value: 'S1-3', text: 'S1-3' },
                    { value: 'S1-4', text: 'S1-4' },
                    { value: 'SBS-1', text: 'SBS-1' },
                    { value: 'SBS-2', text: 'SBS-2' },
                    { value: 'SBDI-1', text: 'SBDI-1' },
                    { value: 'SBDI-2', text: 'SBDI-2' }
                ];
            } else if (currentFilterType === 'category') {
                // 货品类型选项 - 从stockData中提取
                options = [...new Set(stockData.map(item => item.category).filter(cat => cat && cat.trim() !== ''))].sort().map(cat => ({
                    value: cat,
                    text: cat
                }));
            }
            
            // 保存当前选中的值
            const currentValue = optionsSelect.value;
            
            // 清空选项（保留"全部"选项）
            optionsSelect.innerHTML = '<option value="">全部</option>';
            
            // 添加所有选项
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                optionsSelect.appendChild(optionElement);
            });
            
            // 恢复之前选中的值（如果还存在）
            if (currentValue && options.some(opt => opt.value === currentValue)) {
                optionsSelect.value = currentValue;
                selectedFilterValue = currentValue;
            } else {
                optionsSelect.value = '';
                selectedFilterValue = '';
            }
        }
        
        // 生成表格
        function generateTable() {
            const tbody = document.getElementById('stock-tbody');
            
            if (stockData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #6b7280;">
                            没有找到产品
                        </td>
                    </tr>
                `;
                return;
            }
            
            // 根据搜索条件和过滤条件过滤数据
            const searchTerm = document.getElementById('search').value.toLowerCase().trim();
            
            const filteredData = stockData.filter(item => {
                // 搜索过滤
                if (searchTerm) {
                    const code = (item.product_code || '').toLowerCase();
                    const name = (item.product_name || '').toLowerCase();
                    if (!code.includes(searchTerm) && !name.includes(searchTerm)) {
                        return false;
                    }
                }
                
                // 根据当前筛选类型和值进行过滤
                if (currentFilterType === 'category' && selectedFilterValue && selectedFilterValue !== '') {
                    if (item.category !== selectedFilterValue) {
                        return false;
                    }
                }
                
                return true;
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
                    <td class="product-code-cell" style="width: 60px !important; min-width: 60px !important; max-width: 60px !important; padding: 8px 4px !important;">${escapeHtml(item.product_code || '')}</td>
                    <td style="width: 130px !important; min-width: 130px !important; max-width: 130px !important; padding: 8px 6px !important; word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(item.product_name || '')}</td>
                    <td class="qty" style="width: 50px !important; min-width: 50px !important; max-width: 50px !important; padding: 8px 4px !important; text-align: right !important;">
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
                            style="border: none; background: transparent; font-size: 13px; padding: 2px 4px; width: 100%; max-width: 42px; text-align: right; pointer-events: ${isEditing ? 'auto' : 'none'}; ${isEditing ? 'background: #fff; border: 1px solid #583e04; border-radius: 4px;' : ''}"
                        >
                    </td>
                    <td class="actions" style="width: 35px !important; min-width: 35px !important; max-width: 35px !important; padding: 8px 2px !important;">
                        ${isEditing ? 
                            `<button class="edit-button" onclick="saveRecord(${item.id})" title="保存" style="background: #2aa745;">
                                <img src="../images/icons/edit.svg" alt="" aria-hidden="true" style="filter: brightness(0) invert(1);">
                            </button>` :
                            `<button class="edit-button" onclick="editRecord(${item.id})" title="编辑">
                                <img src="../images/icons/edit.svg" alt="" aria-hidden="true">
                            </button>`
                        }
                    </td>
                </tr>
            `;
            }).join('');
        }
        
        // 编辑记录
        function editRecord(id) {
            if (editingRowIds.has(id)) {
                return;
            }
            
            editingRowIds.add(id);
            generateTable();
        }
        
        
        // 更新数量
        function updateQty(id, newQty) {
            const item = stockData.find(i => i.id == id);
            if (item) {
                item.qty = parseFloat(newQty) || 0;
                
                const originalQty = parseFloat(item.original_qty) || 0;
                const soldQty = originalQty - item.qty;
                
                console.log(`产品ID ${id}: 原始数量=${originalQty}, 当前数量=${item.qty}, 出货量=${soldQty}`);
            }
        }
        
        // 重新加载库存总数并更新stockData（排除正在编辑的记录）
        async function reloadStockTotals(excludeEditingIds = new Set()) {
            try {
                const totalsResp = await fetch(`${STOCK_EDIT_API}?action=stocklist_total`);
                const totalsJson = await totalsResp.json();
                if (totalsJson.success && totalsJson.data) {
                    const items = totalsJson.data.items || [];
                    const keyOf = (name, code) => `${(name||'').trim()}|${(code||'').trim()}`;
                    const totalMap = new Map(items.map(it => [keyOf(it.product_name, it.code_number), parseFloat(it.total_qty || 0).toFixed(3)]));
                    
                    // 保存正在编辑的记录的值（避免被覆盖）
                    const editingValues = new Map();
                    excludeEditingIds.forEach(editId => {
                        const record = stockData.find(r => r.id === editId);
                        if (record) {
                            editingValues.set(editId, {
                                qty: record.qty,
                                original_qty: record.original_qty
                            });
                        }
                    });
                    
                    // 更新stockData中的库存数量
                    stockData = stockData.map(it => {
                        // 如果正在编辑，保留编辑中的值
                        if (excludeEditingIds.has(it.id) && editingValues.has(it.id)) {
                            const saved = editingValues.get(it.id);
                            return { ...it, original_qty: saved.original_qty };
                        }
                        
                        // 否则更新为最新的库存总数
                        const key = keyOf(it.product_name, it.product_code);
                        const qty = totalMap.get(key) || '0.00';
                        return { ...it, qty, original_qty: qty };
                    });
                    
                    // 恢复正在编辑的记录的数量值（保持用户正在编辑的值）
                    editingValues.forEach((values, editId) => {
                        const record = stockData.find(r => r.id === editId);
                        if (record) {
                            record.qty = values.qty; // 保持用户正在编辑的值
                        }
                    });
                }
            } catch (e) {
                console.warn('重新加载库存总数失败:', e);
            }
        }
        
        // 保存单个记录
        async function saveRecord(id) {
            const record = stockData.find(r => r.id === id);
            if (!record) return;

            try {
                const originalQty = parseFloat(record.original_qty) || 0;
                const currentQty = parseFloat(record.qty) || 0;
                const soldQty = originalQty - currentQty;
                
                if (currentQty > originalQty) {
                    alert('数量不能增加，只能减少！\n原始数量: ' + originalQty.toFixed(3) + '\n当前数量: ' + currentQty.toFixed(3));
                    record.qty = originalQty;
                    generateTable();
                    return;
                }
                
                if (soldQty === 0) {
                    record.original_qty = currentQty;
                    editingRowIds.delete(id);
                    generateTable();
                    alert(`数量未变化，已更新\n产品: ${record.product_name}`);
                    return;
                }
                
                // 使用今天的日期
                const todayDate = getTodayDateString();
                const now = new Date();
                const timeStr = now.toTimeString().slice(0, 8);
                
                const outboundData = {
                    date: todayDate,
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
                    // 从编辑列表中移除当前保存的记录
                    editingRowIds.delete(id);
                    
                    // 重新加载库存总数以获取最新数据（排除其他正在编辑的记录）
                    await reloadStockTotals(editingRowIds);
                    
                    // 更新当前保存的记录：使用最新的库存总数
                    const updatedRecord = stockData.find(r => r.id === id);
                    if (updatedRecord) {
                        // 确保显示的数量与数据库同步（使用重新加载后的 original_qty）
                        updatedRecord.qty = updatedRecord.original_qty;
                    }
                    
                    generateTable();
                    alert(`记录已保存\n产品: ${record.product_name}\n出货量: ${soldQty.toFixed(3)}`);
                } else {
                    throw new Error(result.message || '保存失败');
                }
                
            } catch (error) {
                console.error('保存失败:', error);
                alert('保存失败: ' + error.message);
            }
        }
        
        // 搜索处理
        function handleSearch() {
            generateTable();
        }
        
        // HTML转义
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>

