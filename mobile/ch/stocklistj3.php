<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    header('Location: login.html?redirect=' . urlencode($current_page));
    exit;
}

// 获取当前登录用户的用户名（优先昵称，其次中文名，最后英文名）
$currentUsername = '';
if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT nickname, username_cn, username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userRow) {
            $nickname = trim((string)($userRow['nickname'] ?? ''));
            $usernameCn = trim((string)($userRow['username_cn'] ?? ''));
            $username = trim((string)($userRow['username'] ?? ''));
            $currentUsername = $nickname !== '' ? $nickname : ($usernameCn !== '' ? $usernameCn : $username);
        }
    } catch (PDOException $e) {
        $currentUsername = '';
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存列表 J3 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/stocklist.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="stocklist-page">
        <header class="page-header">
            <a class="logout-button" href="login.html?redirect=stocklistj3.php" aria-label="退出登录">
                <img src="../images/icons/logout.svg" alt="" aria-hidden="true">
            </a>
            <h1>库存列表 (J3)</h1>
            <div class="calendar-header-right">
                <span id="calendar-selected-date-display" class="calendar-date-display" aria-live="polite"></span>
                <button class="calendar-button" type="button" aria-label="日历">
                    <img src="../images/icons/calendar.svg" alt="" aria-hidden="true">
                </button>
            </div>
        </header>

        <main class="page-content">
            <section class="form-section">
                <div class="selects-row">
                    <div class="select-group">
                        <label for="product-category" class="sr-only">货品类型</label>
                        <div class="select-wrapper">
                            <select id="product-category" name="product-category">
                                <option value="" disabled selected>部门</option>
                                <option value="">全部</option>
                            </select>
                            <span class="select-icon" aria-hidden="true"></span>
                        </div>
                    </div>

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

    <div class="calendar-modal-overlay" id="calendar-modal-overlay" aria-hidden="true">
        <div class="calendar-modal" role="dialog" aria-labelledby="calendar-modal-title" aria-modal="true">
            <h3 id="calendar-modal-title">选择日期</h3>
            <input type="date" id="calendar-date-picker" class="date-input-bar" aria-label="选择日期">
            <div class="calendar-modal-actions">
                <button type="button" class="btn-cancel" id="calendar-modal-cancel">取消</button>
                <button type="button" class="btn-confirm" id="calendar-modal-confirm">确定</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // 全局变量
        let productList = [];
        let allProductList = []; // 保存完整的原始产品列表（未过滤的）
        let stockData = [];
        let selectedFreezerCategory = '';
        let selectedProductCategory = '';
        let editingRowIds = new Set();
        let currentWorkDate = null; // 本页本次选择的日期（不刷新时保留，刷新后清空为今天）
        
        // 当前登录用户名（从PHP传递）
        const CURRENT_USERNAME = <?php echo json_encode($currentUsername, JSON_UNESCAPED_UNICODE); ?>;
        
        // API配置 - J3 数据库（指向backend目录下的stockapi.php）
        const API_BASE_URL = '../../backend/stockapi.php';
        const SYSTEM_TYPE = 'J3';
        const STOCK_EDIT_API = '../../j3/j3stockeditmobile_api.php';
        
        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 冰箱分类变化事件
            document.getElementById('freezer-category').addEventListener('change', handleCategoryChange);
            
            // 货品类型变化事件
            document.getElementById('product-category').addEventListener('change', handleProductCategoryChange);
            
            // 搜索按钮点击事件
            document.querySelector('.btn-search').addEventListener('click', handleSearch);
            
            // 搜索框回车键事件
            document.getElementById('search').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    handleSearch();
                }
            });
            
            // 日历按钮：打开日期选择弹窗
            document.querySelector('.calendar-button').addEventListener('click', openCalendarModal);
            document.getElementById('calendar-modal-overlay').addEventListener('click', function(e) {
                if (e.target === this) closeCalendarModal();
            });
            document.getElementById('calendar-modal-cancel').addEventListener('click', closeCalendarModal);
            document.getElementById('calendar-modal-confirm').addEventListener('click', function() {
                const date = document.getElementById('calendar-date-picker').value;
                if (date) {
                    setDefaultWorkDate(date);
                    updateCalendarDateDisplay();
                }
                closeCalendarModal();
            });
            
            updateCalendarDateDisplay();
            // 加载产品列表
            loadProductList();
        });
        
        function openCalendarModal() {
            const overlay = document.getElementById('calendar-modal-overlay');
            const picker = document.getElementById('calendar-date-picker');
            picker.value = getDefaultWorkDate();
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            picker.focus();
        }
        
        function formatWorkDateDisplay(dateStr) {
            if (!dateStr || !/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return '';
            const p = dateStr.split('-');
            return p[1].replace(/^0/, '') + '月' + p[2].replace(/^0/, '') + '日';
        }
        
        function updateCalendarDateDisplay() {
            const el = document.getElementById('calendar-selected-date-display');
            if (!el) return;
            const dateStr = getDefaultWorkDate();
            el.textContent = dateStr ? formatWorkDateDisplay(dateStr) : '';
        }
        
        function closeCalendarModal() {
            const overlay = document.getElementById('calendar-modal-overlay');
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
        }
        
        // 获取今天的日期字符串 (YYYY-MM-DD)
        function getTodayDateString() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        }
        
        // 出货记录使用的日期：本次会话内选的日期（不刷新则保留），刷新页面则默认今天
        function getDefaultWorkDate() {
            if (currentWorkDate && /^\d{4}-\d{2}-\d{2}$/.test(currentWorkDate)) return currentWorkDate;
            return getTodayDateString();
        }
        // 选择日期确定时：记入内存并写入 storage，供编辑页记录使用
        function setDefaultWorkDate(dateStr) {
            if (!dateStr || !/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return;
            currentWorkDate = dateStr;
            const key = 'j3_stock_edit_date';
            try {
                localStorage.setItem(key, dateStr);
                sessionStorage.setItem(key, dateStr);
            } catch (e) {}
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
                    // 保存完整的原始产品列表（用于提取所有类型选项）
                    allProductList = result.data || [];
                    // 统一将 Drinks 显示为 Service Line（与后台一致）
                    allProductList = allProductList.map(item => {
                        if (item.category === 'Drinks' || item.category === 'drinks') {
                            item.category = 'Service Line';
                        }
                        return item;
                    });
                    productList = [...allProductList]; // 复制完整列表
                    console.log('接收到产品数据数量:', productList.length);
                    
                    // 应用冰箱分类过滤
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
                    
                    // 应用货品类型过滤（如果选择了）
                    const productCategorySelect = document.getElementById('product-category');
                    const currentProductCategory = productCategorySelect ? productCategorySelect.value : (selectedProductCategory || '');
                    if (currentProductCategory && currentProductCategory !== '') {
                        productList = productList.filter(p => p.category === currentProductCategory);
                        console.log('应用货品类型过滤后数量:', productList.length);
                    }
                    
                    // 创建产品查找表，用于快速获取主档信息（如分类、区域）
                    const productMap = new Map();
                    allProductList.forEach(p => {
                        productMap.set(`${(p.product_name||'').trim()}|${(p.product_code||'').trim()}`, p);
                    });

                    // 读取库存总数并合并到列表
                    try {
                        const totalsResp = await fetch(`${STOCK_EDIT_API}?action=stocklist_total`);
                        const totalsJson = await totalsResp.json();
                        if (totalsJson.success && totalsJson.data) {
                            const items = totalsJson.data.items || [];
                            const processedProducts = new Set();
                            stockData = [];

                            // 首先根据库存总数中的记录创建行（支持一个产品多个规格）
                            items.forEach(it => {
                                const prodKey = `${(it.product_name||'').trim()}|${(it.code_number||'').trim()}`;
                                const prodInfo = productMap.get(prodKey) || {};
                                processedProducts.add(prodKey);
                                
                                const qty = parseFloat(it.total_qty || 0).toFixed(3);
                                stockData.push({
                                    id: `${prodInfo.id || 'new'}-${it.specification || 'none'}`,
                                    product_code: it.code_number || '',
                                    product_name: it.product_name || '',
                                    specification: it.specification || '',
                                    freezer_category: prodInfo.freezer_category || '',
                                    category: prodInfo.category || it.type || '',
                                    qty: qty,
                                    original_qty: qty
                                });
                            });

                            // 然后添加剩下的主档产品（无库存记录的产品）
                            productList.forEach(p => {
                                const prodKey = `${(p.product_name||'').trim()}|${(p.product_code||'').trim()}`;
                                if (!processedProducts.has(prodKey)) {
                                    stockData.push({
                                        id: p.id,
                                        product_code: p.product_code || '',
                                        product_name: p.product_name || '',
                                        specification: '',
                                        freezer_category: p.freezer_category || '',
                                        category: p.category || '',
                                        qty: '0.000',
                                        original_qty: '0.000'
                                    });
                                }
                            });
                        }
                    } catch (e) {
                        console.warn('合并库存总数失败:', e);
                        // 回退到基础列表
                        stockData = productList.map(item => ({
                            id: item.id,
                            product_code: item.product_code || '',
                            product_name: item.product_name || '',
                            specification: '',
                            freezer_category: item.freezer_category || '',
                            category: item.category || '',
                            qty: '0.00',
                            original_qty: '0.00'
                        }));
                    }
                    
                    // 更新货品类型下拉选项（在合并库存总数之后）
                    updateProductCategoryOptions();
                    
                    // 更新冰箱分类选项（根据当前选择的货品类型）
                    updateFreezerCategoryOptions();
                    
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
        
        // 处理冰箱分类变化
        function handleCategoryChange() {
            const selectElement = document.getElementById('freezer-category');
            if (selectElement) {
                selectedFreezerCategory = selectElement.value;
                console.log('冰箱分类已更改:', selectedFreezerCategory);
                
                // 不再自动重置货品类型，允许同时使用两个筛选条件
                loadProductList();
            }
        }
        
        // 处理货品类型变化
        function handleProductCategoryChange() {
            const selectElement = document.getElementById('product-category');
            if (selectElement) {
                selectedProductCategory = selectElement.value;
                console.log('货品类型已更改:', selectedProductCategory);
                
                // 根据选择的货品类型更新冰箱分类选项
                updateFreezerCategoryOptions();
                
                // 检查当前选中的冰箱分类是否仍然有效
                const freezerCategorySelect = document.getElementById('freezer-category');
                if (freezerCategorySelect) {
                    const currentFreezerValue = freezerCategorySelect.value;
                    // 检查当前值是否还在选项中
                    const optionExists = Array.from(freezerCategorySelect.options).some(opt => opt.value === currentFreezerValue);
                    if (!optionExists && currentFreezerValue !== '') {
                        // 如果当前选中的冰箱分类不在新列表中，重置为"全部"
                        freezerCategorySelect.value = '';
                        selectedFreezerCategory = '';
                    }
                }
                
                // 如果选择了货品类型，需要重新加载产品列表以应用过滤
                if (selectedProductCategory && selectedProductCategory !== '') {
                    loadProductList();
                } else {
                    generateTable();
                }
            }
        }
        
        // 更新货品类型下拉选项
        function updateProductCategoryOptions() {
            const categorySelect = document.getElementById('product-category');
            if (!categorySelect) return;
            
            // 从完整的原始产品列表中获取所有唯一的货品类型（不受冰箱区过滤影响）
            const categories = [...new Set(allProductList.map(item => item.category).filter(cat => cat && cat.trim() !== ''))].sort();
            
            // 保存当前选中的值
            const currentValue = categorySelect.value;
            
            // 清空选项（保留"全部"选项）
            categorySelect.innerHTML = '<option value="" disabled selected>部门</option><option value="">全部</option>';
            
            // 添加所有货品类型选项
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
            
            // 恢复之前选中的值（如果还存在）
            if (currentValue && categories.includes(currentValue)) {
                categorySelect.value = currentValue;
                selectedProductCategory = currentValue;
            } else {
                categorySelect.value = '';
                selectedProductCategory = '';
            }
            
            // 更新冰箱分类选项（根据当前选择的货品类型）
            updateFreezerCategoryOptions();
        }
        
        // 更新冰箱分类下拉选项（根据选中的货品类型过滤）
        function updateFreezerCategoryOptions() {
            const freezerCategorySelect = document.getElementById('freezer-category');
            if (!freezerCategorySelect) return;
            
            // 所有可用的冰箱分类选项
            const allFreezerCategories = [
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
            
            // 保存当前选中的值
            const currentValue = freezerCategorySelect.value;
            
            // 如果没有选择货品类型，显示所有冰箱分类
            if (!selectedProductCategory || selectedProductCategory === '') {
                freezerCategorySelect.innerHTML = '<option value="" disabled selected>区域</option><option value="">全部</option>';
                allFreezerCategories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.value;
                    option.textContent = cat.text;
                    freezerCategorySelect.appendChild(option);
                });
            } else {
                // 如果选择了货品类型，只显示包含该类型产品的冰箱分类
                // 从完整产品列表中找出包含该类型的冰箱分类
                const validFreezerCategories = new Set();
                
                allProductList.forEach(item => {
                    if (item.category === selectedProductCategory && item.freezer_category) {
                        // 支持逗号分隔的多个冰箱分类
                        const freezerCats = String(item.freezer_category).split(',').map(c => c.trim()).filter(Boolean);
                        freezerCats.forEach(cat => validFreezerCategories.add(cat));
                    }
                });
                
                // 清空选项（保留"全部"选项）
                freezerCategorySelect.innerHTML = '<option value="" disabled selected>区域</option><option value="">全部</option>';
                
                // 只添加有效的冰箱分类选项
                allFreezerCategories.forEach(cat => {
                    if (validFreezerCategories.has(cat.value)) {
                        const option = document.createElement('option');
                        option.value = cat.value;
                        option.textContent = cat.text;
                        freezerCategorySelect.appendChild(option);
                    }
                });
            }
            
            // 恢复之前选中的值（如果还存在）
            const optionExists = Array.from(freezerCategorySelect.options).some(opt => opt.value === currentValue);
            if (currentValue && optionExists) {
                freezerCategorySelect.value = currentValue;
                selectedFreezerCategory = currentValue;
            } else {
                if (currentValue !== '') {
                    // 如果之前的值不在新列表中，重置为"全部"
                    freezerCategorySelect.value = '';
                    selectedFreezerCategory = '';
                }
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
            const productCategorySelect = document.getElementById('product-category');
            const selectedCategory = productCategorySelect ? productCategorySelect.value : '';
            
            const filteredData = stockData.filter(item => {
                // 零库存过滤：排除数量为 0 或负数的行
                if (parseFloat(item.qty) <= 0) return false;

                // 搜索过滤
                if (searchTerm) {
                    const code = (item.product_code || '').toLowerCase();
                    const name = (item.product_name || '').toLowerCase();
                    if (!code.includes(searchTerm) && !name.includes(searchTerm)) {
                        return false;
                    }
                }
                
                // 货品类型过滤
                if (selectedCategory && selectedCategory !== '') {
                    if (item.category !== selectedCategory) {
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
                    <td style="width: 130px !important; min-width: 130px !important; max-width: 130px !important; padding: 8px 6px !important; word-wrap: break-word; overflow-wrap: break-word;">
                        ${escapeHtml(item.product_name || '')}
                        ${item.specification ? `<br><small style="color: #666; font-size: 11px;">(${escapeHtml(item.specification)})</small>` : ''}
                    </td>
                    <td class="qty" style="width: 50px !important; min-width: 50px !important; max-width: 50px !important; padding: 8px 4px !important; text-align: right !important;">
                        <input 
                            type="number" 
                            class="qty-input ${isEditing ? 'editing' : ''}" 
                            value="${item.qty}" 
                            min="0"
                            step="0.01"
                            data-id="${item.id}"
                            data-original="${item.original_qty}"
                            onchange="updateQty('${item.id}', this.value)"
                            onfocus="this.select()"
                            ${isEditing ? '' : 'readonly'}
                            style="border: none; background: transparent; font-size: 13px; padding: 2px 4px; width: 100%; max-width: 42px; text-align: right; pointer-events: ${isEditing ? 'auto' : 'none'}; ${isEditing ? 'background: #fff; border: 1px solid #583e04; border-radius: 4px;' : ''}"
                        >
                    </td>
                    <td class="actions" style="width: 35px !important; min-width: 35px !important; max-width: 35px !important; padding: 8px 2px !important;">
                        ${isEditing ? 
                            `<button class="edit-button" onclick="saveRecord('${item.id}')" title="保存" style="background: #2aa745;">
                                <img src="../images/icons/edit.svg" alt="" aria-hidden="true" style="filter: brightness(0) invert(1);">
                            </button>` :
                            `<button class="edit-button" onclick="editRecord('${item.id}')" title="编辑">
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
        
        
        // 更新数量（不允许负数）
        function updateQty(id, newQty) {
            const item = stockData.find(i => i.id == id);
            if (item) {
                let num = parseFloat(newQty);
                if (isNaN(num) || num < 0) {
                    num = 0;
                }
                item.qty = num;
                
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
                    const keyOf = (name, code, spec) => `${(name||'').trim()}|${(code||'').trim()}|${(spec||'').trim()}`;
                    const totalMap = new Map(items.map(it => [keyOf(it.product_name, it.code_number, it.specification), parseFloat(it.total_qty || 0).toFixed(3)]));
                    
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
                        const key = keyOf(it.product_name, it.product_code, it.specification);
                        const qty = totalMap.get(key) || '0.000';
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
        
        // 保存单个记录（按价格从高到低依次扣除，原子化提交）
        async function saveRecord(id) {
            const record = stockData.find(r => r.id === id);
            if (!record) return;

            try {
                const originalQty = parseFloat(record.original_qty) || 0;
                let currentQty = parseFloat(record.qty) || 0;
                if (currentQty < 0) {
                    alert('数量不能为负数！');
                    record.qty = 0;
                    generateTable();
                    return;
                }
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
                
                // 获取该产品的所有不同价格的库存记录（指定规格）
                const stockByPriceUrl = `${STOCK_EDIT_API}?action=product_stock_by_price&product_name=${encodeURIComponent(record.product_name)}${record.product_code ? '&code_number=' + encodeURIComponent(record.product_code) : ''}&specification=${encodeURIComponent(record.specification || '')}`;
                const stockResp = await fetch(stockByPriceUrl);
                const stockResult = await stockResp.json();
                
                const workDate = getDefaultWorkDate();
                const now = new Date();
                const baseTimeStr = now.toTimeString().slice(0, 8);
                const outboundRows = [];
                
                if (!stockResult.success || !stockResult.data || stockResult.data.length === 0) {
                    // 向后兼容处理
                    outboundRows.push({
                        time: baseTimeStr,
                        product_name: record.product_name,
                        code_number: record.product_code || null,
                        specification: record.specification || null,
                        type: record.category || null,
                        in_quantity: 0,
                        out_quantity: soldQty,
                        receiver: CURRENT_USERNAME || 'Mobile'
                    });
                } else {
                    // 按价格分层逻辑
                    const priceStocks = stockResult.data;
                    let remainingQty = soldQty;
                    
                    for (let i = 0; i < priceStocks.length && remainingQty > 0.001; i++) {
                        const priceStock = priceStocks[i];
                        const availableStock = parseFloat(priceStock.available_stock) || 0;
                        if (availableStock <= 0) continue;
                        
                        const deductQty = Math.min(remainingQty, availableStock);
                        if (deductQty > 0.001) {
                            const timeStr = i === 0 ? baseTimeStr : new Date(now.getTime() + i * 1000).toTimeString().slice(0, 8);
                            outboundRows.push({
                                time: timeStr,
                                product_name: record.product_name,
                                code_number: record.product_code || null,
                                specification: priceStock.specification || record.specification || null,
                                type: priceStock.type || record.category || null,
                                in_quantity: 0,
                                out_quantity: deductQty,
                                price: priceStock.price,
                                receiver: CURRENT_USERNAME || 'Mobile'
                            });
                            remainingQty -= deductQty;
                        }
                    }
                    
                    if (remainingQty > 0.001) {
                        alert(`警告：库存不足！\n产品: ${record.product_name}\n需要扣除: ${soldQty.toFixed(3)}\n实际可扣除: ${(soldQty - remainingQty).toFixed(3)}`);
                        return;
                    }
                }
                
                // 发送原子化的批量保存请求
                const batchPayload = {
                    action: 'batch_save',
                    document_date: workDate,
                    rows: outboundRows
                };
                
                const response = await fetch(STOCK_EDIT_API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(batchPayload)
                });
                
                const result = await response.json();
                if (result.success) {
                    editingRowIds.delete(id);
                    await reloadStockTotals(editingRowIds);
                    const updatedRecord = stockData.find(r => r.id === id);
                    if (updatedRecord) updatedRecord.qty = updatedRecord.original_qty;
                    generateTable();
                    
                    const details = outboundRows.map(r => `RM ${(r.price||0).toFixed(2)}: ${r.out_quantity.toFixed(3)}`).join(', ');
                    alert(`记录已保存 (J3)\n产品: ${record.product_name}\n总出货: ${soldQty.toFixed(3)}\n详情: ${details}`);
                } else {
                    throw new Error(result.message || '批量保存失败');
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
