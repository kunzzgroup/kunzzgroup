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
                    <label for="freezer-category" class="sr-only">冰箱分类</label>
                    <div class="select-wrapper">
                        <select id="freezer-category" name="freezer-category">
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
                                <th scope="col">名字</th>
                                <th scope="col">数量</th>
                                <th scope="col" class="actions-column" aria-label="操作"></th>
                            </tr>
                        </thead>
                        <tbody id="stock-tbody">
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 40px; color: #6b7280;">加载中...</td>
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
        let selectedFreezerCategory = '';
        let editingRowIds = new Set();
        
        // API配置
        const API_BASE_URL = '../../stockapi.php';
        const SYSTEM_TYPE = 'J3';
        const STOCK_EDIT_API = '../../j3/j3stockeditmobile_api.php';
        
        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 冰箱分类变化事件
            document.getElementById('freezer-category').addEventListener('change', handleCategoryChange);
            
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
                
                const freezerCategorySelect = document.getElementById('freezer-category');
                const currentFreezerCategory = freezerCategorySelect ? freezerCategorySelect.value : (selectedFreezerCategory || '');
                
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
                    }
                    
                    stockData = productList.map(item => ({
                        id: item.id,
                        product_code: item.product_code || '',
                        product_name: item.product_name || '',
                        freezer_category: item.freezer_category || '',
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
                    
                    generateTable();
                } else {
                    throw new Error(result.message || '加载失败');
                }
                
            } catch (error) {
                console.error('加载产品列表失败:', error);
                document.getElementById('stock-tbody').innerHTML = `
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: #ef4444;">
                            加载失败: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }
        
        // 处理冰箱分类变化
        function handleCategoryChange() {
            const selectElement = document.getElementById('freezer-category');
            if (selectElement) {
                selectedFreezerCategory = selectElement.value;
                console.log('冰箱分类已更改:', selectedFreezerCategory);
                loadProductList();
            }
        }
        
        // 生成表格
        function generateTable() {
            const tbody = document.getElementById('stock-tbody');
            
            if (stockData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: #6b7280;">
                            没有找到产品
                        </td>
                    </tr>
                `;
                return;
            }
            
            // 根据搜索条件过滤数据
            const searchTerm = document.getElementById('search').value.toLowerCase().trim();
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
                    <td class="qty">
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
                            style="border: none; background: transparent; font-size: 15px; padding: 4px 8px; width: 80px; text-align: right; pointer-events: ${isEditing ? 'auto' : 'none'}; ${isEditing ? 'background: #fff; border: 1px solid #583e04; border-radius: 4px;' : ''}"
                        >
                    </td>
                    <td class="actions">
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
        
        // 重新加载库存总数并更新stockData
        async function reloadStockTotals() {
            try {
                const totalsResp = await fetch(`${STOCK_EDIT_API}?action=stocklist_total`);
                const totalsJson = await totalsResp.json();
                if (totalsJson.success && totalsJson.data) {
                    const items = totalsJson.data.items || [];
                    const keyOf = (name, code) => `${(name||'').trim()}|${(code||'').trim()}`;
                    const totalMap = new Map(items.map(it => [keyOf(it.product_name, it.code_number), parseFloat(it.total_qty || 0).toFixed(3)]));
                    
                    // 更新stockData中的库存数量
                    stockData = stockData.map(it => {
                        const key = keyOf(it.product_name, it.product_code);
                        const qty = totalMap.get(key) || '0.00';
                        return { ...it, qty, original_qty: qty };
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
                    // 重新加载库存总数以获取最新数据
                    await reloadStockTotals();
                    
                    // 更新当前记录的显示数量
                    const updatedRecord = stockData.find(r => r.id === id);
                    if (updatedRecord) {
                        // 确保显示的数量与数据库同步
                        updatedRecord.qty = updatedRecord.original_qty;
                    }
                    
                    editingRowIds.delete(id);
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

