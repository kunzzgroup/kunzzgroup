
// 全局变量
let restaurants = [];
let currentRestaurant = 'overview';
let priceData = [];
let editingRestaurantId = null;

// 所有类型的固定数据（按定义的顺序排列）
const allTypes = [
    '刺身',
    '寿司', '细卷', '一品寿司', '手卷', '寿司卷', '饭团',
    '沙律', '前菜', '蒸煮物', '扬物', '烤物', '一品料理', '披萨', '面食', '咖喱饭', '石锅', '炒饭', '炒面', '附加',
    '盖饭', '套餐',
    '甜点', '饮料'
];

// 页面加载时初始化
document.addEventListener('DOMContentLoaded', function () {
    // 默认总览模式，隐藏新增记录按钮和批量删除按钮
    document.getElementById('add-record-btn').style.display = 'none';
    document.getElementById('batch-delete-btn').style.display = 'none';
    // 初始化类型过滤选择框
    updateTypeOptions('', 'type-filter');
    loadRestaurants();
});

// 加载餐厅列表
async function loadRestaurants() {
    try {
        const response = await fetch('price_api.php?action=restaurants');

        // 检查响应状态
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            restaurants = result.data || [];
            renderRestaurantDropdown();
            renderAddModalRestaurantPrices();
            renderRestaurantList();
            loadPriceData();
        } else {
            // 即使餐厅列表加载失败，也尝试加载价格数据
            restaurants = [];
            renderRestaurantList();
            showToast('加载餐厅列表失败: ' + (result.message || '未知错误'), 'error');
            loadPriceData();
        }
    } catch (error) {
        console.error('加载餐厅列表错误:', error);
        // 即使餐厅列表加载失败，也尝试加载价格数据
        restaurants = [];
        renderRestaurantList();
        showToast('加载餐厅列表失败: ' + error.message, 'error');
        loadPriceData();
    }
}

// 渲染餐厅下拉菜单（显示中文名，全部大写）
function renderRestaurantDropdown() {
    const dropdown = document.getElementById('restaurant-dropdown');
    let html = '<div class="dropdown-item active" data-restaurant="overview" onclick="switchRestaurant(\'overview\')">总览</div>';

    restaurants.forEach(restaurant => {
        // 优先显示中文名，如果没有则显示英文名，全部转换为大写
        const displayName = (restaurant.name_cn || restaurant.name_en || '未命名').toUpperCase();
        html += `<div class="dropdown-item" data-restaurant="${restaurant.id}" onclick="switchRestaurant(${restaurant.id})">${displayName}</div>`;
    });

    dropdown.innerHTML = html;
}

// 加载价格数据
async function loadPriceData() {
    try {
        const search = document.getElementById('search-input')?.value || '';
        const type = document.getElementById('type-filter')?.value || '';

        let url = 'price_api.php?action=list';
        if (search) url += '&search=' + encodeURIComponent(search);
        if (type) url += '&type=' + encodeURIComponent(type);

        const response = await fetch(url);

        // 检查响应状态
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            priceData = result.data || [];
            restaurants = result.restaurants || restaurants; // 更新餐厅列表

            // 排序：先按类型（按定义的顺序），然后按时间从旧到新
            priceData.sort((a, b) => {
                // 首先按类型排序
                const typeA = a.food_type || '';
                const typeB = b.food_type || '';
                const indexA = allTypes.indexOf(typeA);
                const indexB = allTypes.indexOf(typeB);

                // 如果类型不在列表中，放在最后
                const typeOrderA = indexA === -1 ? 9999 : indexA;
                const typeOrderB = indexB === -1 ? 9999 : indexB;

                if (typeOrderA !== typeOrderB) {
                    return typeOrderA - typeOrderB;
                }

                // 类型相同，按时间从旧到新排序
                // 使用所有价格记录中最早的 id（id 是自增的，可以作为时间顺序参考）
                const getEarliestId = (item) => {
                    if (item.prices && Object.keys(item.prices).length > 0) {
                        let earliestId = Infinity;
                        Object.values(item.prices).forEach(price => {
                            if (price && price.id && price.id < earliestId) {
                                earliestId = price.id;
                            }
                        });
                        return earliestId === Infinity ? 0 : earliestId;
                    }
                    return 0;
                };

                const idA = getEarliestId(a);
                const idB = getEarliestId(b);

                return idA - idB; // 从旧到新（id 小的更早）
            });

            renderTable();
        } else {
            // 加载失败时也要渲染表格，显示错误信息
            priceData = [];
            const tbody = document.getElementById('price-tbody');
            if (tbody) {
                const colCount = 4 + (currentRestaurant === 'overview' ? restaurants.length : 1) + (currentRestaurant === 'overview' ? 0 : 1);
                tbody.innerHTML = '<tr><td colspan="' + colCount + '" style="padding: 20px; color: #ef4444; text-align: center;">加载失败: ' + (result.message || '未知错误') + '</td></tr>';
            }
            showToast('加载价格数据失败: ' + (result.message || '未知错误'), 'error');
        }
    } catch (error) {
        console.error('加载价格数据错误:', error);
        // 网络错误或其他错误时也要渲染表格
        priceData = [];
        const tbody = document.getElementById('price-tbody');
        if (tbody) {
            const colCount = 4 + (currentRestaurant === 'overview' ? restaurants.length : 1) + (currentRestaurant === 'overview' ? 0 : 1);
            tbody.innerHTML = '<tr><td colspan="' + colCount + '" style="padding: 20px; color: #ef4444; text-align: center;">加载失败: ' + error.message + '</td></tr>';
        }
        showToast('加载价格数据失败: ' + error.message, 'error');
    }
}

// 获取所有类型选项
function getAllTypes() {
    return allTypes;
}

// 更新类型选项（显示所有类型）
function updateTypeOptions(selectedCategory, targetSelectId) {
    const typeSelect = document.getElementById(targetSelectId);
    if (!typeSelect) return;

    typeSelect.innerHTML = '';

    if (targetSelectId === 'type-filter') {
        typeSelect.innerHTML = '<option value="">全部类型</option>';
    } else {
        typeSelect.innerHTML = '<option value="">请选择类型</option>';
    }

    // 显示所有类型选项
    allTypes.forEach(type => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type;
        typeSelect.appendChild(option);
    });
}

// 类型过滤变化事件
document.getElementById('type-filter')?.addEventListener('change', function () {
    loadPriceData();
});

// 渲染新增记录模态框的餐厅价格输入框
function renderAddModalRestaurantPrices() {
    const container = document.getElementById('add-prices-container');
    if (!container || restaurants.length === 0) return;

    let html = '';
    restaurants.forEach(restaurant => {
        const displayName = (restaurant.name_cn || restaurant.name_en || '未命名').toUpperCase();
        html += `
                    <div class="form-group">
                        <label for="add-price-${restaurant.id}">${displayName}价格</label>
                        <input type="number" id="add-price-${restaurant.id}" class="form-input" min="0" step="0.01" placeholder="0.00">
                    </div>
                `;
    });

    container.innerHTML = html;
}

// 渲染餐馆列表（用于删除）
function renderRestaurantList() {
    const container = document.getElementById('restaurant-list');
    if (!container) return;

    if (!restaurants || restaurants.length === 0) {
        container.innerHTML = '<div class="empty-placeholder">暂无餐馆</div>';
        return;
    }

    container.innerHTML = restaurants.map(restaurant => {
        const nameCn = (restaurant.name_cn || '未命名').toUpperCase();
        const nameEn = (restaurant.name_en || '').toUpperCase();
        return `
                    <div class="restaurant-item">
                        <div class="restaurant-names">
                            <div class="restaurant-name-cn">${nameCn}</div>
                            <div class="restaurant-name-en">${nameEn || '&nbsp;'}</div>
                        </div>
                        <div class="restaurant-actions">
                            <button class="restaurant-edit-btn" onclick="openEditRestaurantModal(${restaurant.id})">
                                <i class="fas fa-edit"></i>
                                编辑
                            </button>
                            <button class="restaurant-delete-btn" onclick="deleteRestaurant(${restaurant.id})">
                                <i class="fas fa-trash"></i>
                                删除
                            </button>
                        </div>
                    </div>
                `;
    }).join('');
}

function setRestaurantModalMode(mode = 'add') {
    const titleEl = document.getElementById('restaurant-modal-title');
    const submitTextEl = document.getElementById('restaurant-modal-submit-text');
    if (mode === 'edit') {
        if (titleEl) titleEl.textContent = '编辑餐馆';
        if (submitTextEl) submitTextEl.textContent = '更新';
    } else {
        if (titleEl) titleEl.textContent = '新增餐馆';
        if (submitTextEl) submitTextEl.textContent = '保存';
    }
}

// 打开新增记录模态框
function openAddModal() {
    const modal = document.getElementById('add-modal');
    modal.classList.add('show');

    // 清空表单
    document.getElementById('add-product-name').value = '';
    document.getElementById('add-type').value = '';

    // 重置类型选择框
    updateTypeOptions('', 'add-type');

    // 清空所有价格输入框
    restaurants.forEach(restaurant => {
        const priceInput = document.getElementById(`add-price-${restaurant.id}`);
        if (priceInput) {
            priceInput.value = '';
        }
    });

    // 渲染餐厅价格输入框
    renderAddModalRestaurantPrices();
}

// 关闭新增记录模态框
function closeAddModal() {
    const modal = document.getElementById('add-modal');
    modal.classList.remove('show');
}

// 保存食品记录
async function saveFoodRecord() {
    const foodNameInput = document.getElementById('add-product-name');
    const foodName = foodNameInput.value.trim().toUpperCase();
    foodNameInput.value = foodName;
    const foodType = document.getElementById('add-type').value.trim() || null;

    if (!foodName) {
        showToast('请输入货品名称', 'error');
        return;
    }

    // 收集所有餐厅的价格
    const prices = [];
    let hasPrice = false;

    restaurants.forEach(restaurant => {
        const priceInput = document.getElementById(`add-price-${restaurant.id}`);
        if (priceInput) {
            const priceValue = parseFloat(priceInput.value);
            if (!Number.isNaN(priceValue) && priceValue >= 0) {
                prices.push({
                    restaurant_id: restaurant.id,
                    price: priceValue
                });
                hasPrice = true;
            }
        }
    });

    if (!hasPrice) {
        showToast('请至少输入一个餐厅的价格', 'error');
        return;
    }

    try {
        // 为每个有价格的餐厅创建记录
        const promises = prices.map(priceData => {
            return fetch('price_api.php?action=food', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    restaurant_id: priceData.restaurant_id,
                    food_name: foodName,
                    food_type: foodType,
                    price: priceData.price
                })
            });
        });

        const responses = await Promise.all(promises);
        const results = await Promise.all(responses.map(r => r.json()));

        // 检查是否有错误
        const errors = results.filter(r => !r.success);
        if (errors.length > 0) {
            showToast('部分记录保存失败: ' + errors[0].message, 'error');
            return;
        }

        showToast(`成功添加 ${results.length} 条记录`, 'success');
        closeAddModal();
        loadPriceData();
    } catch (error) {
        console.error('保存记录错误:', error);
        showToast('保存失败', 'error');
    }
}

// 渲染表格
function renderTable() {
    const thead = document.getElementById('price-thead');
    const tbody = document.getElementById('price-tbody');

    // 在函数开始处声明一次 isBatchDeleteMode，避免重复声明
    const isBatchDeleteMode = document.body.classList.contains('batch-delete-mode');

    // 渲染表头
    let headerHtml = '<tr>';

    // 单个餐厅模式下，只在批量删除模式时显示复选框列
    if (currentRestaurant !== 'overview') {
        if (isBatchDeleteMode) {
            headerHtml += `<th style="min-width: 50px; width: 3%;">
                        <input type="checkbox" class="batch-select-checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)">
                    </th>`;
        }
    }

    headerHtml += '<th style="min-width: 60px;">序号</th>';
    headerHtml += '<th style="min-width: 150px;">货品名称</th>';
    headerHtml += '<th style="min-width: 100px;">类型</th>';

    // 根据当前选择的餐厅显示列（显示中文名，全部大写）
    // 计算餐厅列宽度：总览模式剩余69%，单个餐厅模式剩余63%
    const fixedColumnsWidth = 4 + 20 + 7; // 序号+货品名称+类型
    const checkboxColumnWidth = (currentRestaurant !== 'overview' && isBatchDeleteMode) ? 3 : 0;
    const operationColumnWidth = currentRestaurant === 'overview' ? 0 : 6;
    const restaurantCount = currentRestaurant === 'overview' ? restaurants.length : 1;
    const availableWidth = 100 - fixedColumnsWidth - checkboxColumnWidth - operationColumnWidth;
    const restaurantColumnWidth = restaurantCount > 0 ? (availableWidth / restaurantCount).toFixed(2) : 0;

    if (currentRestaurant === 'overview') {
        // 总览模式：显示所有餐厅，不显示操作列，所有餐厅列等宽
        restaurants.forEach(restaurant => {
            const displayName = (restaurant.name_cn || restaurant.name_en || '未命名').toUpperCase();
            headerHtml += `<th class="restaurant-column" style="width: ${restaurantColumnWidth}%; min-width: 80px;">${displayName}</th>`;
        });
    } else {
        // 单个餐厅模式：只显示当前餐厅，显示操作列
        const selectedRestaurant = restaurants.find(r => r.id == currentRestaurant);
        if (selectedRestaurant) {
            const displayName = (selectedRestaurant.name_cn || selectedRestaurant.name_en || '未命名').toUpperCase();
            headerHtml += `<th class="restaurant-column" style="width: ${restaurantColumnWidth}%; min-width: 80px;">${displayName}</th>`;
        }
        if (!isBatchDeleteMode) {
            headerHtml += '<th style="min-width: 100px;">操作</th>';
        }
    }

    headerHtml += '</tr>';
    thead.innerHTML = headerHtml;

    // 渲染表体
    const checkboxColumn = (currentRestaurant !== 'overview' && isBatchDeleteMode) ? 1 : 0;
    const colCount = checkboxColumn + 3 + (currentRestaurant === 'overview' ? restaurants.length : 1) + (currentRestaurant === 'overview' ? 0 : (isBatchDeleteMode ? 0 : 1));

    if (priceData.length === 0) {
        if (currentRestaurant === 'overview') {
            tbody.innerHTML = '<tr><td colspan="' + colCount + '" style="padding: 20px; color: #6b7280; text-align: center;">暂无数据</td></tr>';
        } else {
            // 单个餐厅模式：显示空数据提示
            tbody.innerHTML = '<tr><td colspan="' + colCount + '" style="padding: 20px; color: #6b7280; text-align: center;">暂无数据 - 点击"新增记录"按钮添加</td></tr>';
        }
        // 如果是单个餐厅模式，添加新增行容器
        if (currentRestaurant !== 'overview') {
            let container = document.getElementById('new-rows-container');
            if (!container) {
                container = document.createElement('tbody');
                container.id = 'new-rows-container';
                tbody.parentNode.appendChild(container);
            }
        }
        return;
    }

    let bodyHtml = '';
    let rowIndex = 0;

    // 总览模式下：找到东京/TOKYO餐厅
    let tokyoRestaurant = null;
    if (currentRestaurant === 'overview') {
        tokyoRestaurant = restaurants.find(r => {
            const nameCn = (r.name_cn || '').toUpperCase();
            const nameEn = (r.name_en || '').toUpperCase();
            return nameCn.includes('东京') || nameCn.includes('TOKYO') ||
                nameEn.includes('TOKYO') || nameEn.includes('东京');
        });
    }

    priceData.forEach((item, index) => {
        // 总览模式下：只显示东京/TOKYO餐厅有的货品
        if (currentRestaurant === 'overview') {
            if (tokyoRestaurant) {
                const tokyoPriceData = item.prices && item.prices[tokyoRestaurant.id] ? item.prices[tokyoRestaurant.id] : null;
                // 如果东京餐厅没有这个食品的价格记录，跳过这一行
                if (!tokyoPriceData || tokyoPriceData.price === null) {
                    return;
                }
            } else {
                // 如果找不到东京餐厅，跳过所有记录
                return;
            }
        }

        // 单个餐厅模式下：如果该餐厅没有这个食品的价格，则跳过不显示
        if (currentRestaurant !== 'overview') {
            const selectedRestaurant = restaurants.find(r => r.id == currentRestaurant);
            if (selectedRestaurant) {
                const priceData_item = item.prices && item.prices[selectedRestaurant.id] ? item.prices[selectedRestaurant.id] : null;
                // 如果该餐厅没有这个食品的价格记录，跳过这一行
                if (!priceData_item || priceData_item.price === null) {
                    return;
                }
            } else {
                return;
            }
        }

        rowIndex++;
        // 存储原始索引到data属性，用于编辑和删除功能
        bodyHtml += `<tr data-original-index="${index}">`;

        // 单个餐厅模式下，只在批量删除模式时显示复选框列
        if (currentRestaurant !== 'overview' && isBatchDeleteMode) {
            const selectedRestaurant = restaurants.find(r => r.id == currentRestaurant);
            const priceData_item = selectedRestaurant && item.prices && item.prices[selectedRestaurant.id] ? item.prices[selectedRestaurant.id] : null;
            const recordId = priceData_item ? priceData_item.id : null;
            if (recordId) {
                bodyHtml += `<td><input type="checkbox" class="batch-select-checkbox row-checkbox" data-record-id="${recordId}" onchange="updateBatchDeleteButton()"></td>`;
            } else {
                bodyHtml += '<td></td>';
            }
        }

        const displayFoodName = (item.food_name || '').toUpperCase();
        bodyHtml += `<td><span>${rowIndex}</span></td>`;
        bodyHtml += `<td><span>${displayFoodName}</span></td>`;
        bodyHtml += `<td><span>${item.food_type || ''}</span></td>`;

        // 根据当前选择的餐厅显示价格列（所有餐厅列等宽）
        const fixedColumnsWidth = 4 + 20 + 7;
        const checkboxColumnWidth = (currentRestaurant !== 'overview' && isBatchDeleteMode) ? 3 : 0;
        const operationColumnWidth = currentRestaurant === 'overview' ? 0 : 6;
        const restaurantCount = currentRestaurant === 'overview' ? restaurants.length : 1;
        const availableWidth = 100 - fixedColumnsWidth - checkboxColumnWidth - operationColumnWidth;
        const restaurantColumnWidth = restaurantCount > 0 ? (availableWidth / restaurantCount).toFixed(2) : 0;

        if (currentRestaurant === 'overview') {
            // 收集所有有价格的餐厅价格，用于计算最贵和最便宜
            const priceMap = new Map();
            restaurants.forEach(restaurant => {
                const priceData = item.prices && item.prices[restaurant.id] ? item.prices[restaurant.id] : null;
                if (priceData && priceData.price !== null) {
                    priceMap.set(restaurant.id, parseFloat(priceData.price));
                }
            });

            // 计算最贵和最便宜的价格
            let minPrice = Infinity;
            let maxPrice = -Infinity;
            priceMap.forEach(price => {
                if (price < minPrice) minPrice = price;
                if (price > maxPrice) maxPrice = price;
            });

            // 判断是否为独家商品（只有一个餐厅有价格）
            const isExclusive = priceMap.size === 1;

            restaurants.forEach(restaurant => {
                const priceData = item.prices && item.prices[restaurant.id] ? item.prices[restaurant.id] : null;
                let cellClass = 'price-cell restaurant-column';

                if (priceData && priceData.price !== null) {
                    const price = parseFloat(priceData.price);

                    // 独家商品显示蓝色
                    if (isExclusive) {
                        cellClass += ' restaurant-exclusive';
                    } else {
                        // 最便宜显示黄色
                        if (price === minPrice && minPrice !== maxPrice) {
                            cellClass += ' lowest-price';
                        }
                        // 最贵显示青色
                        if (price === maxPrice && minPrice !== maxPrice) {
                            cellClass += ' highest-price';
                        }
                    }
                }

                bodyHtml += `<td class="${cellClass}" style="width: ${restaurantColumnWidth}%; min-width: 80px;">`;
                if (priceData && priceData.price !== null) {
                    bodyHtml += `<div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${parseFloat(priceData.price).toFixed(2)}</span>
                            </div>`;
                } else {
                    bodyHtml += '<span>&nbsp;</span>';
                }
                bodyHtml += '</td>';
            });
        } else {
            const selectedRestaurant = restaurants.find(r => r.id == currentRestaurant);
            if (selectedRestaurant) {
                const priceData = item.prices && item.prices[selectedRestaurant.id] ? item.prices[selectedRestaurant.id] : null;
                bodyHtml += `<td class="price-cell restaurant-column" style="width: ${restaurantColumnWidth}%; min-width: 80px;">`;
                if (priceData && priceData.price !== null) {
                    bodyHtml += `<div class="currency-display">
                                <span class="currency-symbol">RM</span>
                                <span class="currency-amount">${parseFloat(priceData.price).toFixed(2)}</span>
                            </div>`;
                } else {
                    bodyHtml += '<span>&nbsp;</span>';
                }
                bodyHtml += '</td>';
            }
            // 存储记录ID到data属性中
            const priceData = selectedRestaurant && item.prices && item.prices[selectedRestaurant.id] ? item.prices[selectedRestaurant.id] : null;
            const recordId = priceData ? priceData.id : null;
            if (!isBatchDeleteMode) {
                bodyHtml += `<td class="action-cell" data-record-id="${recordId || ''}" data-food-name="${displayFoodName}" data-food-type="${item.food_type || ''}">`;
                bodyHtml += `<button class="action-btn edit-btn" onclick="editRowByIndex(${index})" title="编辑">`;
                bodyHtml += '<i class="fas fa-edit"></i>';
                bodyHtml += '</button>';
                bodyHtml += `<button class="action-btn delete-btn" onclick="deleteRowByIndex(${index})" title="删除">`;
                bodyHtml += '<i class="fas fa-trash"></i>';
                bodyHtml += '</button>';
                bodyHtml += '</td>';
            }
        }
        bodyHtml += '</tr>';
    });

    tbody.innerHTML = bodyHtml;

    // 如果是单个餐厅模式，确保新增行容器存在
    if (currentRestaurant !== 'overview') {
        let container = document.getElementById('new-rows-container');
        if (!container) {
            container = document.createElement('tbody');
            container.id = 'new-rows-container';
            const table = document.getElementById('price-table');
            if (table) {
                table.appendChild(container);
            }
        }
    }

}

// 切换餐厅选择器下拉菜单
function toggleRestaurantSelector() {
    const dropdown = document.getElementById('restaurant-dropdown');
    dropdown.classList.toggle('show');
}

// 切换餐厅
function switchRestaurant(restaurant) {
    currentRestaurant = restaurant;

    // 退出批量删除模式
    if (document.body.classList.contains('batch-delete-mode')) {
        cancelBatchDelete();
    }

    // 更新按钮显示（显示中文名，全部大写）
    if (restaurant === 'overview') {
        document.getElementById('current-restaurant').textContent = '总览';
        // 总览模式：隐藏新增记录按钮和批量删除按钮
        document.getElementById('add-record-btn').style.display = 'none';
        document.getElementById('batch-delete-btn').style.display = 'none';
    } else {
        const selectedRestaurant = restaurants.find(r => r.id == restaurant);
        if (selectedRestaurant) {
            const displayName = (selectedRestaurant.name_cn || selectedRestaurant.name_en || '未命名').toUpperCase();
            document.getElementById('current-restaurant').textContent = displayName;
        }
        // 单个餐厅模式：显示新增记录按钮和批量删除按钮
        document.getElementById('add-record-btn').style.display = 'inline-flex';
        document.getElementById('batch-delete-btn').style.display = 'inline-flex';
    }

    // 更新active状态
    document.querySelectorAll('#restaurant-dropdown .dropdown-item').forEach(item => {
        item.classList.remove('active');
    });
    const activeItem = document.querySelector(`#restaurant-dropdown .dropdown-item[data-restaurant="${restaurant}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }

    // 隐藏下拉菜单
    document.getElementById('restaurant-dropdown').classList.remove('show');

    // 清空新增行
    const newRowsContainer = document.getElementById('new-rows-container');
    if (newRowsContainer) {
        newRowsContainer.innerHTML = '';
    }
    updateBatchSaveButton();

    // 重新渲染表格
    renderTable();
}

// 新增行计数器
let newRowCounter = 0;

// 处理粘贴数据
async function handlePasteData(e, currentRow, defaultType = '') {
    try {
        // 获取剪贴板数据
        const clipboardData = e.clipboardData || window.clipboardData;
        const pastedText = clipboardData.getData('text');

        if (!pastedText || !pastedText.trim()) {
            return;
        }

        // 解析粘贴的数据（支持制表符、逗号或空格分隔）
        const lines = pastedText.split(/\r?\n/).filter(line => line.trim());

        if (lines.length === 0) {
            return;
        }

        // 获取所有已存在的新行
        const container = document.getElementById('new-rows-container');
        if (!container) {
            return;
        }

        const existingRows = Array.from(container.querySelectorAll('.new-row'));

        // 找到当前行在已存在行中的索引
        let currentRowIndex = existingRows.findIndex(row => row === currentRow);
        if (currentRowIndex === -1) {
            currentRowIndex = 0;
        }

        // 先填充已存在的行
        let filledCount = 0;
        for (let i = 0; i < lines.length && (currentRowIndex + i) < existingRows.length; i++) {
            const lineData = parsePasteLine(lines[i].trim());
            const targetRow = existingRows[currentRowIndex + i];

            if (targetRow && (lineData.name || lineData.price !== null)) {
                const foodNameInput = targetRow.querySelector('.new-food-name');
                const priceInput = targetRow.querySelector('.new-price');

                if (foodNameInput && lineData.name) {
                    foodNameInput.value = lineData.name.toUpperCase();
                }
                if (priceInput && lineData.price !== null) {
                    priceInput.value = lineData.price;
                }
                filledCount++;
            }
        }

        // 如果还有剩余的数据行，且已存在的行不够，才创建新行
        const remainingLines = lines.length - filledCount;
        if (remainingLines > 0) {
            const startIndex = filledCount;
            for (let i = startIndex; i < lines.length; i++) {
                const lineData = parsePasteLine(lines[i].trim());
                if (lineData.name || lineData.price !== null) {
                    // 创建新行
                    addNewRow(defaultType);

                    // 等待新行创建完成
                    await new Promise(resolve => setTimeout(resolve, 10));

                    // 获取最后创建的行并填充数据
                    const newRows = container.querySelectorAll('.new-row');
                    const lastRow = newRows[newRows.length - 1];
                    if (lastRow) {
                        const newFoodNameInput = lastRow.querySelector('.new-food-name');
                        const newPriceInput = lastRow.querySelector('.new-price');

                        if (newFoodNameInput && lineData.name) {
                            newFoodNameInput.value = lineData.name.toUpperCase();
                        }
                        if (newPriceInput && lineData.price !== null) {
                            newPriceInput.value = lineData.price;
                        }
                    }
                }
            }
        }

        // 滚动到最后填充的行
        setTimeout(() => {
            const allRows = container.querySelectorAll('.new-row');
            const lastFilledRow = allRows[Math.min(currentRowIndex + lines.length - 1, allRows.length - 1)];
            if (lastFilledRow) {
                lastFilledRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 100);

        showToast(`成功粘贴 ${lines.length} 行数据`, 'success');
    } catch (error) {
        console.error('粘贴数据处理错误:', error);
        showToast('粘贴失败，请检查数据格式', 'error');
    }
}

// 处理价格输入框的粘贴（支持多行批量粘贴）
async function handlePricePasteData(e, currentRow, defaultType = '') {
    try {
        // 获取剪贴板数据
        const clipboardData = e.clipboardData || window.clipboardData;
        const pastedText = clipboardData.getData('text');

        if (!pastedText || !pastedText.trim()) {
            return;
        }

        // 解析粘贴的数据（每行一个价格）
        const lines = pastedText.split(/\r?\n/).filter(line => line.trim());

        if (lines.length === 0) {
            return;
        }

        // 获取所有已存在的新行
        const container = document.getElementById('new-rows-container');
        if (!container) {
            return;
        }

        const existingRows = Array.from(container.querySelectorAll('.new-row'));

        // 找到当前行在已存在行中的索引
        let currentRowIndex = existingRows.findIndex(row => row === currentRow);
        if (currentRowIndex === -1) {
            currentRowIndex = 0;
        }

        // 解析价格数据的辅助函数
        function parsePrice(priceText) {
            if (!priceText || !priceText.trim()) {
                return null;
            }
            // 尝试提取价格数字
            const priceStr = priceText.replace(/[^\d.-]/g, ''); // 移除非数字字符（保留小数点和负号）
            const priceNum = parseFloat(priceStr);
            if (!isNaN(priceNum) && priceNum >= 0) {
                return priceNum;
            }
            return null;
        }

        // 先填充已存在的行的价格
        let filledCount = 0;
        for (let i = 0; i < lines.length && (currentRowIndex + i) < existingRows.length; i++) {
            const priceValue = parsePrice(lines[i].trim());
            if (priceValue !== null) {
                const targetRow = existingRows[currentRowIndex + i];
                if (targetRow) {
                    const priceInput = targetRow.querySelector('.new-price');
                    if (priceInput) {
                        priceInput.value = priceValue;
                    }
                    filledCount++;
                }
            }
        }

        // 如果还有剩余的价格数据，且已存在的行不够，才创建新行
        const remainingLines = lines.length - filledCount;
        if (remainingLines > 0) {
            const startIndex = filledCount;
            for (let i = startIndex; i < lines.length; i++) {
                const priceValue = parsePrice(lines[i].trim());
                if (priceValue !== null) {
                    // 创建新行
                    addNewRow(defaultType);

                    // 等待新行创建完成
                    await new Promise(resolve => setTimeout(resolve, 10));

                    // 获取最后创建的行并填充价格
                    const newRows = container.querySelectorAll('.new-row');
                    const lastRow = newRows[newRows.length - 1];
                    if (lastRow) {
                        const newPriceInput = lastRow.querySelector('.new-price');
                        if (newPriceInput) {
                            newPriceInput.value = priceValue;
                        }
                    }
                }
            }
        }

        // 滚动到最后填充的行
        setTimeout(() => {
            const allRows = container.querySelectorAll('.new-row');
            const lastFilledRow = allRows[Math.min(currentRowIndex + lines.length - 1, allRows.length - 1)];
            if (lastFilledRow) {
                lastFilledRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 100);

        showToast(`成功粘贴 ${lines.length} 个价格`, 'success');
    } catch (error) {
        console.error('价格粘贴处理错误:', error);
        showToast('粘贴失败，请检查数据格式', 'error');
    }
}

// 解析粘贴的一行数据
function parsePasteLine(line) {
    if (!line || !line.trim()) {
        return { name: '', price: null };
    }

    // 尝试多种分隔符：制表符、逗号、多个空格
    let parts = [];

    // 优先使用制表符（Excel常用）
    if (line.includes('\t')) {
        parts = line.split('\t').map(p => p.trim());
    }
    // 其次使用逗号
    else if (line.includes(',')) {
        parts = line.split(',').map(p => p.trim());
    }
    // 最后使用多个空格
    else {
        parts = line.split(/\s{2,}/).map(p => p.trim());
    }

    const name = parts[0] || '';
    let price = null;

    // 尝试解析价格（可能是第二个或最后一个部分）
    if (parts.length > 1) {
        // 尝试从第二个部分开始找数字
        for (let i = 1; i < parts.length; i++) {
            const priceStr = parts[i].replace(/[^\d.-]/g, ''); // 移除非数字字符（保留小数点和负号）
            const priceNum = parseFloat(priceStr);
            if (!isNaN(priceNum) && priceNum >= 0) {
                price = priceNum;
                break;
            }
        }
    }

    return { name, price };
}

// 添加新行
function addNewRow(defaultType = '') {
    if (currentRestaurant === 'overview') {
        showToast('总览模式下不能新增记录', 'error');
        return;
    }

    const container = document.getElementById('new-rows-container');
    if (!container) return;

    newRowCounter++;
    const rowId = `new-row-${newRowCounter}`;

    // 计算餐厅列宽度
    const fixedColumnsWidth = 4 + 20 + 7;
    const operationColumnWidth = 6;
    const restaurantCount = 1;
    const availableWidth = 100 - fixedColumnsWidth - operationColumnWidth;
    const restaurantColumnWidth = (availableWidth / restaurantCount).toFixed(2);

    const selectedRestaurant = restaurants.find(r => r.id == currentRestaurant);
    if (!selectedRestaurant) return;

    const rowHtml = `
                <tr id="${rowId}" class="new-row">
                    <td><span>-</span></td>
                    <td><input type="text" class="table-input new-food-name" placeholder="货品名称" style="text-align: left;"></td>
                    <td>
                        <select class="table-select new-type-select" id="new-type-${newRowCounter}" style="text-align: left;">
                            <option value="">请选择类型</option>
                        </select>
                    </td>
                    <td class="price-cell restaurant-column" style="width: ${restaurantColumnWidth}%; min-width: 80px;">
                        <input type="number" class="table-input new-price" min="0" step="0.01" placeholder="0.00" style="text-align: right;">
                    </td>
                    <td class="action-cell">
                        <button class="action-btn delete-btn" onclick="removeNewRow('${rowId}')" title="删除此行">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

    container.insertAdjacentHTML('beforeend', rowHtml);

    // 初始化类型选择框
    const row = document.getElementById(rowId);
    const typeSelect = row.querySelector('.new-type-select');
    const foodNameInput = row.querySelector('.new-food-name');
    const priceInput = row.querySelector('.new-price');

    if (typeSelect) {
        // 初始化类型选择框，显示所有类型选项
        updateTypeOptions('', typeSelect.id);
        // 如果有默认类型，设置默认类型值
        if (defaultType) {
            typeSelect.value = defaultType;
        }
    }

    // 添加粘贴事件处理（在货品名称输入框上，支持多行粘贴）
    if (foodNameInput) {
        foodNameInput.addEventListener('paste', function (e) {
            e.preventDefault();
            handlePasteData(e, row, defaultType);
        });
    }

    // 添加粘贴事件处理（在价格输入框上，支持多行批量粘贴）
    if (priceInput) {
        priceInput.addEventListener('paste', function (e) {
            e.preventDefault();
            handlePricePasteData(e, row, defaultType);
        });
    }

    // 显示批量保存按钮
    updateBatchSaveButton();

    // 滚动到新行
    row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// 删除新增行
function removeNewRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        updateBatchSaveButton();
    }
}

// 更新批量保存按钮显示状态
function updateBatchSaveButton() {
    const container = document.getElementById('new-rows-container');
    const batchSaveBtn = document.getElementById('batch-save-btn');
    if (container && batchSaveBtn) {
        const newRows = container.querySelectorAll('.new-row');
        // 检查是否有编辑中的行
        const editingRows = document.querySelectorAll('.editing-row');
        // 只有在有新行且没有编辑中的行时才显示批量保存按钮
        if (newRows.length > 0 && editingRows.length === 0) {
            batchSaveBtn.style.display = 'inline-flex';
        } else {
            batchSaveBtn.style.display = 'none';
        }
    }
}

// 批量保存新增行
async function batchSaveNewRows() {
    if (currentRestaurant === 'overview') {
        showToast('总览模式下不能新增记录', 'error');
        return;
    }

    const container = document.getElementById('new-rows-container');
    if (!container) return;

    const newRows = container.querySelectorAll('.new-row');
    if (newRows.length === 0) {
        showToast('没有需要保存的记录', 'warning');
        return;
    }

    const records = [];
    let hasError = false;

    newRows.forEach((row, index) => {
        const foodNameInput = row.querySelector('.new-food-name');
        const foodName = foodNameInput ? foodNameInput.value.trim().toUpperCase() : '';
        if (foodNameInput) {
            foodNameInput.value = foodName;
        }
        const foodType = row.querySelector('.new-type-select')?.value.trim() || null;
        const priceInputVal = row.querySelector('.new-price')?.value;
        const priceValue = priceInputVal === '' ? 0 : parseFloat(priceInputVal);

        if (!foodName) {
            showToast(`第 ${index + 1} 行：请输入货品名称`, 'error');
            hasError = true;
            return;
        }

        if (Number.isNaN(priceValue) || priceValue < 0) {
            showToast(`第 ${index + 1} 行：请输入有效的价格（可为0）`, 'error');
            hasError = true;
            return;
        }

        records.push({
            restaurant_id: currentRestaurant,
            food_name: foodName,
            food_type: foodType,
            price: priceValue
        });
    });

    if (hasError || records.length === 0) {
        return;
    }

    try {
        const response = await fetch('price_api.php?action=batch-save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ records: records })
        });

        const result = await response.json();

        if (result.success) {
            showToast(`成功保存 ${result.saved_count} 条记录`, 'success');
            // 清空新增行
            container.innerHTML = '';
            updateBatchSaveButton();
            loadPriceData();
        } else {
            showToast('保存失败: ' + result.message, 'error');
            if (result.errors && result.errors.length > 0) {
                result.errors.forEach(error => {
                    showToast(error, 'error');
                });
            }
        }
    } catch (error) {
        console.error('批量保存错误:', error);
        showToast('保存失败', 'error');
    }
}

// 打开新增餐馆模态框
function openAddRestaurantModal() {
    editingRestaurantId = null;
    setRestaurantModalMode('add');
    document.getElementById('restaurant-name-cn').value = '';
    document.getElementById('restaurant-name-en').value = '';
    document.getElementById('add-restaurant-modal').classList.add('show');
}

function openEditRestaurantModal(id) {
    const targetRestaurant = restaurants.find(r => r.id == id);
    if (!targetRestaurant) {
        showToast('未找到该餐馆信息', 'error');
        return;
    }
    editingRestaurantId = id;
    setRestaurantModalMode('edit');
    document.getElementById('restaurant-name-cn').value = (targetRestaurant.name_cn || '').toUpperCase();
    document.getElementById('restaurant-name-en').value = (targetRestaurant.name_en || '').toUpperCase();
    document.getElementById('add-restaurant-modal').classList.add('show');
}

// 关闭新增餐馆模态框
function closeAddRestaurantModal() {
    editingRestaurantId = null;
    setRestaurantModalMode('add');
    document.getElementById('add-restaurant-modal').classList.remove('show');
}

// 保存餐馆
async function saveRestaurant() {
    // 获取输入值并转换为大写
    const nameCn = document.getElementById('restaurant-name-cn').value.trim().toUpperCase();
    const nameEn = document.getElementById('restaurant-name-en').value.trim().toUpperCase();

    if (!nameCn) {
        showToast('请输入餐馆中文名称', 'error');
        return;
    }

    if (!nameEn) {
        showToast('请输入餐馆英文名称', 'error');
        return;
    }

    try {
        const isEdit = !!editingRestaurantId;
        const url = isEdit
            ? `price_api.php?action=restaurant&id=${editingRestaurantId}`
            : 'price_api.php?action=restaurant';
        const method = isEdit ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name_cn: nameCn,
                name_en: nameEn
            })
        });

        const result = await response.json();

        if (result.success) {
            showToast(isEdit ? '餐馆更新成功' : '餐馆添加成功', 'success');
            closeAddRestaurantModal();
            loadRestaurants();
        } else {
            showToast((isEdit ? '更新失败: ' : '添加失败: ') + result.message, 'error');
        }
    } catch (error) {
        console.error('保存餐馆错误:', error);
        showToast('保存失败', 'error');
    }
}

// 删除餐馆
async function deleteRestaurant(id) {
    const targetRestaurant = restaurants.find(r => r.id == id);
    const restaurantName = targetRestaurant ? (targetRestaurant.name_cn || targetRestaurant.name_en || '该餐馆') : '该餐馆';

    if (!confirm(`确定要删除 "${restaurantName}" 吗？此操作会同时删除该餐馆的所有记录。`)) {
        return;
    }

    if (editingRestaurantId == id) {
        closeAddRestaurantModal();
    }

    try {
        const response = await fetch(`price_api.php?action=restaurant&id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showToast('餐馆删除成功', 'success');
            const needSwitch = currentRestaurant == id;
            await loadRestaurants();
            if (needSwitch) {
                switchRestaurant('overview');
            } else {
                loadPriceData();
            }
        } else {
            showToast('删除失败: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('删除餐馆错误:', error);
        showToast('删除失败', 'error');
    }
}

// 编辑行（通过原始索引）
function editRowByIndex(originalIndex) {
    if (currentRestaurant === 'overview') {
        showToast('总览模式下不能编辑记录', 'error');
        return;
    }

    const item = priceData[originalIndex];
    if (!item) return;

    const selectedRestaurant = restaurants.find(r => r.id == currentRestaurant);
    if (!selectedRestaurant) return;

    // 通过data属性找到对应的表格行
    const tbody = document.getElementById('price-tbody');
    const row = tbody.querySelector(`tr[data-original-index="${originalIndex}"]`);
    if (!row) return;

    // 检查是否已经在编辑状态
    if (row.classList.contains('editing-row')) {
        // 保存编辑
        const priceData_item = item.prices && item.prices[selectedRestaurant.id] ? item.prices[selectedRestaurant.id] : null;
        const recordId = priceData_item ? priceData_item.id : null;
        saveEditRow(row, recordId, item);
    } else {
        // 进入编辑状态
        const priceData_item = item.prices && item.prices[selectedRestaurant.id] ? item.prices[selectedRestaurant.id] : null;
        const recordId = priceData_item ? priceData_item.id : null;
        enterEditMode(row, item, recordId);
    }
}

// 编辑行（兼容旧代码）
function editRow(index) {
    editRowByIndex(index);
}

// 进入编辑模式
function enterEditMode(row, item, recordId) {
    row.classList.add('editing-row');

    // 获取单元格
    const cells = row.querySelectorAll('td');
    if (cells.length < 5) return;

    // 货品名称
    const foodNameCell = cells[1];
    const foodName = (item.food_name || '').toUpperCase();
    foodNameCell.innerHTML = `<input type="text" class="table-input edit-food-name" value="${foodName}" style="text-align: left;">`;

    // 类型
    const typeCell = cells[2];
    const foodType = item.food_type || '';
    let typeOptions = '<option value="">请选择类型</option>';
    allTypes.forEach(type => {
        typeOptions += `<option value="${type}" ${type === foodType ? 'selected' : ''}>${type}</option>`;
    });
    const typeSelectId = `edit-type-${recordId}`;
    typeCell.innerHTML = `<select class="table-select edit-type-select" id="${typeSelectId}" style="text-align: left;">${typeOptions}</select>`;

    // 价格
    const priceCell = cells[3];
    const priceData_item = item.prices && item.prices[currentRestaurant] ? item.prices[currentRestaurant] : null;
    const price = priceData_item && priceData_item.price ? priceData_item.price : '';
    priceCell.innerHTML = `<input type="number" class="table-input edit-price" value="${(parseFloat(price) || 0) === 0 ? '' : price}" min="0" step="0.01" placeholder="0.00" style="text-align: right;">`;

    // 操作按钮
    const actionCell = cells[4];
    const originalIndex = row.getAttribute('data-original-index');
    actionCell.innerHTML = `
                <button class="action-btn edit-btn save-mode" onclick="saveEditRow(this.closest('tr'), ${recordId}, null)" title="保存">
                    <i class="fas fa-save"></i>
                </button>
                <button class="action-btn delete-btn" onclick="cancelEditRow(this.closest('tr'), ${originalIndex})" title="取消">
                    <i class="fas fa-times"></i>
                </button>
            `;

    // 更新批量保存按钮状态（如果有编辑中的行，隐藏批量保存按钮）
    updateBatchSaveButton();
}

// 保存编辑
async function saveEditRow(row, recordId, originalItem) {
    const foodNameInput = row.querySelector('.edit-food-name');
    const foodName = foodNameInput ? foodNameInput.value.trim().toUpperCase() : '';
    if (foodNameInput) {
        foodNameInput.value = foodName;
    }
    const foodType = row.querySelector('.edit-type-select')?.value.trim() || null;
    const priceInputVal = row.querySelector('.edit-price')?.value;
    const priceValue = priceInputVal === '' ? 0 : parseFloat(priceInputVal);

    if (!foodName) {
        showToast('请输入货品名称', 'error');
        return;
    }

    if (Number.isNaN(priceValue) || priceValue < 0) {
        showToast('请输入有效的价格（可为0）', 'error');
        return;
    }

    // 获取原始索引，用于更新 priceData
    const originalIndex = row.getAttribute('data-original-index');
    const item = originalIndex !== null ? priceData[parseInt(originalIndex)] : null;

    try {
        // 如果存在旧记录，先删除
        if (recordId) {
            const deleteResponse = await fetch(`price_api.php?action=food&id=${recordId}`, {
                method: 'DELETE'
            });
            const deleteResult = await deleteResponse.json();

            if (!deleteResult.success) {
                showToast('更新失败: ' + deleteResult.message, 'error');
                return;
            }
        }

        // 创建新记录（可能类型已改变）
        const createResponse = await fetch('price_api.php?action=food', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                restaurant_id: currentRestaurant,
                food_name: foodName,
                food_type: foodType,
                price: priceValue
            })
        });

        const createResult = await createResponse.json();

        if (createResult.success) {
            showToast(recordId ? '记录更新成功' : '记录添加成功', 'success');

            // 更新 priceData 数组中的数据
            if (item) {
                item.food_name = foodName;
                item.food_type = foodType;
                // 更新价格数据
                if (!item.prices) {
                    item.prices = {};
                }
                if (!item.prices[currentRestaurant]) {
                    item.prices[currentRestaurant] = {};
                }
                item.prices[currentRestaurant].id = createResult.data.id;
                item.prices[currentRestaurant].price = priceValue;
            }

            // 只更新当前行的显示，不影响其他正在编辑的行
            updateSingleRowDisplay(row, foodName, foodType, priceValue, createResult.data.id);

            // 更新批量保存按钮状态
            updateBatchSaveButton();
        } else {
            showToast('保存失败: ' + createResult.message, 'error');
            // 保存失败时，重新加载以恢复原状态
            loadPriceData();
        }
    } catch (error) {
        console.error('保存编辑错误:', error);
        showToast('保存失败', 'error');
        // 保存失败时，重新加载以恢复原状态
        loadPriceData();
    }
}

// 更新单行显示（不重新加载整个表格）
function updateSingleRowDisplay(row, foodName, foodType, price, recordId) {
    // 移除编辑状态
    row.classList.remove('editing-row');

    // 获取单元格
    const cells = row.querySelectorAll('td');
    if (cells.length < 5) return;

    // 更新货品名称
    const foodNameCell = cells[1];
    foodNameCell.innerHTML = `<span>${foodName}</span>`;

    // 更新类型
    const typeCell = cells[2];
    typeCell.innerHTML = `<span>${foodType || ''}</span>`;

    // 更新价格
    const priceCell = cells[3];
    // 确保价格单元格有正确的 class
    priceCell.className = 'price-cell restaurant-column';
    priceCell.innerHTML = `<div class="currency-display">
                <span class="currency-symbol">RM</span>
                <span class="currency-amount">${parseFloat(price).toFixed(2)}</span>
            </div>`;

    // 更新操作按钮
    const actionCell = cells[4];
    // 确保操作单元格有正确的 class
    actionCell.className = 'action-cell';
    const originalIndex = row.getAttribute('data-original-index');
    actionCell.innerHTML = `
                <button class="action-btn edit-btn" onclick="editRowByIndex(${originalIndex})" title="编辑">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn delete-btn" onclick="deleteRowByIndex(${originalIndex})" title="删除">
                    <i class="fas fa-trash"></i>
                </button>
            `;
}

// 取消编辑
function cancelEditRow(row, index) {
    loadPriceData(); // 重新加载数据以恢复原状态
}

// 删除行（通过原始索引）
async function deleteRowByIndex(originalIndex) {
    if (currentRestaurant === 'overview') {
        showToast('总览模式下不能删除记录', 'error');
        return;
    }

    const item = priceData[originalIndex];
    if (!item) return;

    const selectedRestaurant = restaurants.find(r => r.id == currentRestaurant);
    if (!selectedRestaurant) return;

    const priceData_item = item.prices && item.prices[selectedRestaurant.id] ? item.prices[selectedRestaurant.id] : null;
    const recordId = priceData_item ? priceData_item.id : null;

    if (!recordId) {
        showToast('无法找到记录ID', 'error');
        return;
    }

    const foodName = (item.food_name || '').toUpperCase();
    if (!confirm(`确定要删除 "${foodName}" 这条记录吗？`)) {
        return;
    }

    try {
        const response = await fetch(`price_api.php?action=food&id=${recordId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showToast('记录删除成功', 'success');
            loadPriceData();
        } else {
            showToast('删除失败: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('删除记录错误:', error);
        showToast('删除失败', 'error');
    }
}

// 删除行（兼容旧代码）
async function deleteRow(index) {
    deleteRowByIndex(index);
}

// 进入批量删除模式
function enterBatchDeleteMode() {
    if (currentRestaurant === 'overview') {
        showToast('总览模式下不能批量删除', 'error');
        return;
    }

    document.body.classList.add('batch-delete-mode');
    document.getElementById('batch-delete-btn').style.display = 'none';
    document.getElementById('confirm-batch-delete-btn').style.display = 'inline-flex';
    document.getElementById('cancel-batch-delete-btn').style.display = 'inline-flex';
    document.getElementById('add-record-btn').style.display = 'none';
    document.getElementById('batch-save-btn').style.display = 'none';

    // 重新渲染表格以显示复选框
    renderTable();
    updateBatchDeleteButton();
}

// 取消批量删除模式
function cancelBatchDelete() {
    document.body.classList.remove('batch-delete-mode');
    document.getElementById('batch-delete-btn').style.display = 'inline-flex';
    document.getElementById('confirm-batch-delete-btn').style.display = 'none';
    document.getElementById('cancel-batch-delete-btn').style.display = 'none';
    document.getElementById('add-record-btn').style.display = currentRestaurant === 'overview' ? 'none' : 'inline-flex';

    // 取消所有选择
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }

    // 重新渲染表格
    renderTable();
}

// 全选/取消全选
function toggleSelectAll(checkbox) {
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    rowCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBatchDeleteButton();
}

// 更新批量删除按钮状态
function updateBatchDeleteButton() {
    const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    const confirmBtn = document.getElementById('confirm-batch-delete-btn');
    if (confirmBtn) {
        confirmBtn.disabled = selectedCheckboxes.length === 0;
    }
}

// 确认批量删除
async function confirmBatchDelete() {
    const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        showToast('请至少选择一条记录', 'warning');
        return;
    }

    const recordIds = Array.from(selectedCheckboxes).map(cb => cb.getAttribute('data-record-id'));
    const count = recordIds.length;

    if (!confirm(`确定要删除选中的 ${count} 条记录吗？此操作不可恢复。`)) {
        return;
    }

    try {
        // 批量删除
        const promises = recordIds.map(id =>
            fetch(`price_api.php?action=food&id=${id}`, {
                method: 'DELETE'
            })
        );

        const responses = await Promise.all(promises);
        const results = await Promise.all(responses.map(r => r.json()));

        // 检查结果
        const successCount = results.filter(r => r.success).length;
        const failCount = results.length - successCount;

        if (failCount === 0) {
            showToast(`成功删除 ${successCount} 条记录`, 'success');
            cancelBatchDelete();
            loadPriceData();
        } else {
            showToast(`成功删除 ${successCount} 条记录，${failCount} 条删除失败`, 'warning');
            loadPriceData();
        }
    } catch (error) {
        console.error('批量删除错误:', error);
        showToast('批量删除失败', 'error');
    }
}

// Toast通知
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    toast.innerHTML = `
                <i class="fas ${icons[type] || icons.info} toast-icon"></i>
                <span class="toast-content">${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            `;

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// 搜索和过滤事件
document.getElementById('search-input')?.addEventListener('input', function () {
    loadPriceData();
});

// 类型过滤变化事件
document.getElementById('type-filter')?.addEventListener('change', function () {
    loadPriceData();
});

// 切换对比模式选择器下拉菜单
function toggleComparisonModeSelector() {
    const dropdown = document.getElementById('comparison-mode-dropdown');
    dropdown.classList.toggle('show');
}

// 切换对比模式
function switchComparisonMode(mode) {
    const comparisonModeNames = {
        'restaurant': '餐厅对比',
        'supplier': '批发商对比'
    };

    // 更新按钮显示
    document.getElementById('current-comparison-mode').textContent = comparisonModeNames[mode] || mode;

    // 更新active状态
    document.querySelectorAll('#comparison-mode-dropdown .dropdown-item').forEach(item => {
        item.classList.remove('active');
    });
    const activeItem = document.querySelector(`#comparison-mode-dropdown .dropdown-item[data-mode="${mode}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }

    // 隐藏下拉菜单
    document.getElementById('comparison-mode-dropdown').classList.remove('show');

    // 如果选择批发商对比，直接跳转到 supplier.php
    if (mode === 'supplier') {
        window.location.href = 'supply';
        return;
    }
}

// 打开新增行数模态框
function openAddRowsModal() {
    if (currentRestaurant === 'overview') {
        showToast('总览模式下不能新增记录', 'error');
        return;
    }
    const modal = document.getElementById('add-rows-modal');
    const rowsCountInput = document.getElementById('rows-count');
    if (rowsCountInput) {
        rowsCountInput.value = '1';
    }
    // 清空默认类型
    const defaultType = document.getElementById('default-type');
    if (defaultType) {
        defaultType.value = '';
    }
    // 初始化类型选择框，显示所有类型
    updateTypeOptions('', 'default-type');
    modal.classList.add('show');
}

// 关闭新增行数模态框
function closeAddRowsModal() {
    const modal = document.getElementById('add-rows-modal');
    modal.classList.remove('show');
}

// 创建多行记录
function createMultipleRows() {
    const rowsCountInput = document.getElementById('rows-count');
    if (!rowsCountInput) return;

    const rowsCount = parseInt(rowsCountInput.value, 10);
    if (isNaN(rowsCount) || rowsCount < 1 || rowsCount > 100) {
        showToast('请输入1-100之间的有效数字', 'error');
        return;
    }

    // 获取默认类型
    const defaultTypeEl = document.getElementById('default-type');
    const defaultType = defaultTypeEl ? defaultTypeEl.value.trim() : '';

    // 关闭模态框
    closeAddRowsModal();

    // 创建指定数量的行，应用默认类型
    for (let i = 0; i < rowsCount; i++) {
        addNewRow(defaultType);
    }

    // 滚动到表格底部
    setTimeout(() => {
        const container = document.getElementById('new-rows-container');
        if (container) {
            const lastRow = container.querySelector('.new-row:last-child');
            if (lastRow) {
                lastRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }, 100);

    showToast(`成功创建 ${rowsCount} 行记录`, 'success');
}

function closeAddModal() {
    const modal = document.getElementById('add-modal');
    modal.classList.remove('show');
}

// 点击其他地方关闭选择器和模态框
document.addEventListener('click', function (event) {
    // 关闭选择器
    const selector = event.target.closest('.restaurant-selector');
    if (!selector) {
        const restaurantDropdown = document.getElementById('restaurant-dropdown');
        if (restaurantDropdown) {
            restaurantDropdown.classList.remove('show');
        }
        const comparisonDropdown = document.getElementById('comparison-mode-dropdown');
        if (comparisonDropdown) {
            comparisonDropdown.classList.remove('show');
        }
    }

    // 关闭模态框
    const addModal = document.getElementById('add-modal');
    if (event.target === addModal) {
        closeAddModal();
    }

    const addRowsModal = document.getElementById('add-rows-modal');
    if (event.target === addRowsModal) {
        closeAddRowsModal();
    }

    const addRestaurantModal = document.getElementById('add-restaurant-modal');
    if (event.target === addRestaurantModal) {
        closeAddRestaurantModal();
    }
});