let ingredients = [];

// 页面加载
window.addEventListener('DOMContentLoaded', function () {
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

