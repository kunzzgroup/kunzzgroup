<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>菜单成本管理</title>
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
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
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

        /* 添加菜单项表单 */
        .add-form-section {
            background: white;
            border-radius: 12px;
            padding: 24px 40px;
            margin-bottom: 24px;
            border: 2px solid #583e04;
            box-shadow: 0 2px 8px rgba(88, 62, 4, 0.1);
        }

        .add-form-title {
            font-size: clamp(14px, 1.25vw, 18px);
            font-weight: 600;
            color: #583e04;
            margin-bottom: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            color: #583e04;
        }

        .form-input, .form-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #583e04;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #583e04;
            box-shadow: 0 0 0 3px rgba(88, 62, 4, 0.1);
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

        /* 表格容器 */
        .table-container {
            background: white;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(88, 62, 4, 0.1);
            border: 2px solid #000000ff;
            overflow: visible;
            display: flex;
            flex-direction: column;
            max-height: 69vh;
        }

        .table-scroll-container {
            overflow-x: hidden;
            overflow-y: auto;
            flex: 1;
            position: relative;
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
            padding: clamp(4px, 0.42vw, 8px) 0;
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

        .stock-table td {
            font-size: clamp(8px, 0.74vw, 14px);
            padding: 0;
            border: 1px solid #d1d5db;
            text-align: center;
            position: relative;
        }

        /* 确保表格头部完全遮盖滚动的数据 */
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

        .stock-table tr:nth-child(even) {
            background-color: white;
        }

        .stock-table tr:hover {
            background-color: #e5ebf8ff;
        }

        /* 菜单项行样式 */
        .stock-table tr.menu-item-row {
            background-color: #e5ebf8ff !important;
            font-weight: 600;
        }

        .stock-table tr.menu-item-row:hover {
            background-color: #d1d9e6 !important;
        }

        /* 配料行样式 */
        .stock-table tr.ingredient-row {
            background-color: white;
        }

        /* 确保显示文本和编辑输入框对齐 */
        .stock-table td span {
            display: inline-block;
            width: 100%;
            line-height: clamp(14px, 1.25vw, 24px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            box-sizing: border-box;
            vertical-align: middle;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
        }

        .stock-table tbody tr.editing {
            background-color: #e0f2fe;
        }

        /* 响应式表格列宽 */
        .stock-table th:nth-child(1), .stock-table td:nth-child(1) { width: 10%; } /* 菜单编号 */
        .stock-table th:nth-child(2), .stock-table td:nth-child(2) { width: 20%; } /* 菜单名称 */
        .stock-table th:nth-child(3), .stock-table td:nth-child(3) { width: 5%; } /* 序号 */
        .stock-table th:nth-child(4), .stock-table td:nth-child(4) { width: 20%; } /* 原材料 */
        .stock-table th:nth-child(5), .stock-table td:nth-child(5) { width: 10%; } /* 单价(RM) */
        .stock-table th:nth-child(6), .stock-table td:nth-child(6) { width: 10%; } /* 单位 */
        .stock-table th:nth-child(7), .stock-table td:nth-child(7) { width: 10%; } /* 用量 */
        .stock-table th:nth-child(8), .stock-table td:nth-child(8) { width: 10%; } /* 成本 */
        .stock-table th:nth-child(9), .stock-table td:nth-child(9) { width: 5%; } /* 操作 */

        .table-input, .table-select {
            width: 100%;
            height: 40px;
            border: none;
            background: transparent;
            text-align: center;
            font-size: clamp(8px, 0.74vw, 14px);
            padding: 8px 4px;
            transition: all 0.2s;
            box-sizing: border-box;
            vertical-align: middle;
            line-height: 24px;
        }

        .table-input:focus, .table-select:focus {
            background: #fff;
            border: 2px solid #583e04;
            outline: none;
            z-index: 5;
            position: relative;
        }

        /* 添加配料行样式 */
        .add-ingredient-row {
            background-color: #f8f5eb !important;
        }

        .text-right {
            text-align: right;
        }

        .action-cell {
            display: flex;
            gap: 4px;
            justify-content: center;
        }

        .action-btn {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: clamp(4px, 0.32vw, 6px);
            width: clamp(18px, 1.67vw, 32px);
            height: clamp(18px, 1.67vw, 32px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: clamp(6px, 0.63vw, 12px);
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .action-btn.edit-btn {
            background: #f59e0b;
        }

        .action-btn.delete-btn {
            background: #ef4444;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>菜单成本管理</h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                返回
            </a>
        </div>

        <div id="alert-container"></div>

        <!-- 添加菜单项表单 -->
        <div class="add-form-section">
            <div class="add-form-title">添加新菜单项</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>菜单编号 *</label>
                    <input type="text" id="new-menu-code" class="form-input" placeholder="如：A1" required>
                </div>
                <div class="form-group">
                    <label>菜单名称 *</label>
                    <input type="text" id="new-menu-name" class="form-input" placeholder="如：SHAKE SASHIMI 或 鲑鱼刺身" required>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" onclick="addMenuItem()">
                        <i class="fas fa-plus"></i>
                        添加
                    </button>
                </div>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="table-container">
            <div class="table-scroll-container">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>菜单编号</th>
                            <th>菜单名称</th>
                            <th>序号</th>
                            <th>原材料</th>
                            <th>单价 (RM)</th>
                            <th>单位</th>
                            <th>用量</th>
                            <th>成本</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="data-table-body">
                        <tr>
                            <td colspan="9" class="empty-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <div>加载中...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let menuItems = [];
        let ingredients = [];

        // 页面加载
        window.addEventListener('DOMContentLoaded', function() {
            loadData();
            loadIngredients();
        });

        // 显示提示消息
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // 加载原材料列表
        async function loadIngredients() {
            try {
                const response = await fetch('menucost_api.php?action=ingredients');
                const result = await response.json();
                if (result.success) {
                    ingredients = result.data || [];
                }
            } catch (error) {
                console.error('加载原材料失败:', error);
            }
        }

        // 加载数据
        async function loadData() {
            try {
                const response = await fetch('menucost_api.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    menuItems = result.data || [];
                    renderMenuItems();
                } else {
                    showAlert(result.message || '加载数据失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // 渲染表格
        function renderMenuItems() {
            const tbody = document.getElementById('data-table-body');
            
            if (menuItems.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <div>暂无数据</div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            menuItems.forEach(item => {
                const totalCost = item.ingredients?.reduce((sum, ing) => sum + parseFloat(ing.cost || 0), 0) || 0;
                const ingredientCount = item.ingredients?.length || 0;
                // rowspan = 配料数量 + 1行添加配料行
                const rowspan = ingredientCount + 1;
                
                // 菜单项行（合并前两列显示菜单信息）
                html += `
                    <tr class="menu-item-row">
                        <td rowspan="${rowspan}"><span>${escapeHtml(item.menu_code)}</span></td>
                        <td rowspan="${rowspan}"><span>${escapeHtml(item.menu_name)}</span></td>
                        <td colspan="6"><span>总成本: RM ${totalCost.toFixed(2)}</span></td>
                        <td>
                            <span class="action-cell">
                                <button class="action-btn delete-btn" onclick="deleteMenuItem(${item.id})" title="删除菜单项">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </span>
                        </td>
                    </tr>
                `;
                
                // 配料行
                if (item.ingredients && item.ingredients.length > 0) {
                    item.ingredients.forEach((ing, index) => {
                        html += `
                            <tr class="ingredient-row">
                                <td><span>${index + 1}</span></td>
                                <td><span>${escapeHtml(ing.ingredient_name)}</span></td>
                                <td class="text-right"><span>${parseFloat(ing.rm_price || 0).toFixed(2)}</span></td>
                                <td><span>${parseFloat(ing.unit || 0).toFixed(2)}</span></td>
                                <td class="text-right"><span>${parseFloat(ing.measurement || 0).toFixed(2)}</span></td>
                                <td class="text-right"><span>${parseFloat(ing.cost || 0).toFixed(2)}</span></td>
                                <td>
                                    <span class="action-cell">
                                        <button class="action-btn delete-btn" onclick="deleteIngredient(${ing.id}, ${item.id})" title="删除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                }
                
                // 添加配料行
                html += `
                    <tr class="add-ingredient-row" data-menu-item-id="${item.id}">
                        <td><span style="color: #9ca3af;">+</span></td>
                        <td>
                            <select class="table-select" id="new-ingredient-${item.id}" onchange="onIngredientSelect(${item.id}, this.value)">
                                <option value="">请选择原材料</option>
                                ${ingredients.map(ing => `
                                    <option value="${ing.id}" data-price="${ing.price}" data-unit="${ing.unit}">
                                        ${escapeHtml(ing.product_name)}
                                    </option>
                                `).join('')}
                            </select>
                        </td>
                        <td>
                            <input type="number" class="table-input" id="new-rm-price-${item.id}" step="0.01" min="0" readonly>
                        </td>
                        <td>
                            <input type="number" class="table-input" id="new-unit-${item.id}" step="0.01" min="0" readonly>
                        </td>
                        <td>
                            <input type="number" class="table-input" id="new-measurement-${item.id}" step="0.01" min="0" onchange="calculateCost(${item.id})">
                        </td>
                        <td>
                            <input type="number" class="table-input" id="new-cost-${item.id}" step="0.01" readonly>
                        </td>
                        <td>
                            <span class="action-cell">
                                <button class="action-btn edit-btn" onclick="addIngredient(${item.id})" title="添加">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </span>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        // 原材料选择
        function onIngredientSelect(menuItemId, ingredientId) {
            const ingredient = ingredients.find(ing => ing.id == ingredientId);
            if (ingredient) {
                document.getElementById(`new-rm-price-${menuItemId}`).value = parseFloat(ingredient.price || 0).toFixed(2);
                document.getElementById(`new-unit-${menuItemId}`).value = parseFloat(ingredient.unit || 0).toFixed(2);
                calculateCost(menuItemId);
            }
        }

        // 计算成本
        function calculateCost(menuItemId) {
            const rmPrice = parseFloat(document.getElementById(`new-rm-price-${menuItemId}`).value || 0);
            const unit = parseFloat(document.getElementById(`new-unit-${menuItemId}`).value || 0);
            const measurement = parseFloat(document.getElementById(`new-measurement-${menuItemId}`).value || 0);
            
            let cost = 0;
            if (unit > 0 && measurement > 0) {
                cost = (rmPrice / unit) * measurement;
            }
            
            document.getElementById(`new-cost-${menuItemId}`).value = cost.toFixed(2);
        }

        // 删除菜单项
        async function deleteMenuItem(menuItemId) {
            if (!confirm('确定要删除这个菜单项吗？这将删除所有关联的配料。')) {
                return;
            }

            try {
                const response = await fetch(`menucost_api.php?id=${menuItemId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('菜单项删除成功', 'success');
                    await loadData();
                } else {
                    showAlert(result.message || '删除失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // HTML转义
        function escapeHtml(text) {
            if (text == null) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 添加菜单项
        async function addMenuItem() {
            const menuCode = document.getElementById('new-menu-code').value.trim();
            const menuName = document.getElementById('new-menu-name').value.trim();

            if (!menuCode || !menuName) {
                showAlert('菜单编号和名称不能为空', 'error');
                return;
            }

            try {
                const response = await fetch('menucost_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        menu_code: menuCode,
                        menu_name: menuName
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('菜单项添加成功', 'success');
                    document.getElementById('new-menu-code').value = '';
                    document.getElementById('new-menu-name').value = '';
                    await loadData();
                } else {
                    showAlert(result.message || '添加失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // 添加配料
        async function addIngredient(menuItemId) {
            const ingredientId = document.getElementById(`new-ingredient-${menuItemId}`).value;
            const rmPrice = document.getElementById(`new-rm-price-${menuItemId}`).value;
            const unit = document.getElementById(`new-unit-${menuItemId}`).value;
            const measurement = document.getElementById(`new-measurement-${menuItemId}`).value;

            if (!ingredientId) {
                showAlert('请选择原材料', 'error');
                return;
            }

            if (!measurement || parseFloat(measurement) <= 0) {
                showAlert('请输入有效的用量', 'error');
                return;
            }

            try {
                const response = await fetch('menucost_api.php?action=ingredient', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        menu_item_id: menuItemId,
                        ingredient_id: ingredientId,
                        rm_price: parseFloat(rmPrice || 0),
                        unit: parseFloat(unit || 0),
                        measurement: parseFloat(measurement || 0)
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('配料添加成功', 'success');
                    // 清空表单
                    document.getElementById(`new-ingredient-${menuItemId}`).value = '';
                    document.getElementById(`new-rm-price-${menuItemId}`).value = '';
                    document.getElementById(`new-unit-${menuItemId}`).value = '';
                    document.getElementById(`new-measurement-${menuItemId}`).value = '';
                    document.getElementById(`new-cost-${menuItemId}`).value = '';
                    await loadData();
                } else {
                    showAlert(result.message || '添加失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // 删除配料
        async function deleteIngredient(ingredientId, menuItemId) {
            if (!confirm('确定要删除这个配料吗？')) {
                return;
            }

            try {
                const response = await fetch(`menucost_api.php?action=ingredient&id=${ingredientId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('配料删除成功', 'success');
                    await loadData();
                } else {
                    showAlert(result.message || '删除失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }
    </script>
</body>
</html>

