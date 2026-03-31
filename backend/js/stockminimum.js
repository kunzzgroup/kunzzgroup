
// 全局变量
let allProducts = [];
let filteredProducts = [];
let isLoading = false;
let pendingChanges = new Set();

// 初始化
function initApp() {
    loadProductsAndSettings();
}

// 加载货品和设置数据
async function loadProductsAndSettings() {
    if (isLoading) return;

    isLoading = true;
    setLoadingState(true);

    try {
        // 这里需要创建对应的API接口
        const response = await fetch('stockminimumapi.php?action=list');
        const result = await response.json();

        if (result.success) {
            // 统一数据处理：去除产品名称首尾空格，确保与stocklistall.php一致
            allProducts = (result.data || []).map(item => ({
                ...item,
                product_name: (item.product_name || '').trim(),
                product_code: (item.product_code || '').trim(),
                minimum_quantity: parseFloat(item.minimum_quantity) || 0
            }));
            filteredProducts = [...allProducts];
            renderSettingsTable();
            updateStats();
        }

    } catch (error) {
        console.error('Error:', error);
        // 静默处理，不显示错误提示
    } finally {
        isLoading = false;
        setLoadingState(false);
    }
}

// 设置加载状态
function setLoadingState(loading) {
    const tbody = document.getElementById('settings-tbody');
    if (loading) {
        tbody.innerHTML = `
                    <tr>
                        <td colspan="4" style="padding: 40px; text-align: center;">
                            <div class="loading"></div>
                            <div style="margin-top: 16px; color: #6b7280;">正在加载数据...</div>
                        </td>
                    </tr>
                `;
    }
}

// 渲染设置表格
function renderSettingsTable() {
    const tbody = document.getElementById('settings-tbody');

    if (filteredProducts.length === 0) {
        tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <div>暂无货品数据</div>
                        </td>
                    </tr>
                `;
        return;
    }

    let html = '';
    filteredProducts.forEach(product => {
        html += `
                    <tr>
                        <td><strong>${product.product_name}</strong></td>
                        <td>${product.product_code || '-'}</td>
                        <td>
                            <input type="number" 
                                class="quantity-input"
                                value="${product.minimum_quantity}"
                                min="0"
                                step="0.01"
                                onchange="markAsChanged('${product.product_name}', this.value)"
                                placeholder="设置最低数量">
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" 
                                    onclick="saveIndividualSetting('${product.product_name}')"
                                    style="padding: 4px 8px; font-size: clamp(8px, 0.63vw, 12px);">
                                <i class="fas fa-save"></i>
                                保存
                            </button>
                        </td>
                    </tr>
                `;
    });

    tbody.innerHTML = html;
    document.getElementById('displayed-count').textContent = filteredProducts.length;
}

// 更新统计
function updateStats() {
    const totalProducts = allProducts.length;
    const configuredAlerts = allProducts.filter(p => p.minimum_quantity > 0).length;
    const unconfiguredAlerts = totalProducts - configuredAlerts;

    document.getElementById('total-products').textContent = totalProducts;
    document.getElementById('configured-alerts').textContent = configuredAlerts;
    document.getElementById('unconfigured-alerts').textContent = unconfiguredAlerts;
}

// 标记为已更改
function markAsChanged(productName, minQuantity) {
    // 确保产品名称去除空格，与数据处理逻辑一致
    const trimmedName = (productName || '').trim();
    const product = allProducts.find(p => p.product_name === trimmedName);
    if (product) {
        product.minimum_quantity = parseFloat(minQuantity) || 0;
        pendingChanges.add(trimmedName);

        // 重新渲染表格以更新状态
        updateStats();
    }
}

// 保存单个设置
async function saveIndividualSetting(productName) {
    // 确保产品名称去除空格，与数据处理逻辑一致
    const trimmedName = (productName || '').trim();
    const product = allProducts.find(p => p.product_name === trimmedName);
    if (!product) return;

    try {
        const response = await fetch('stockminimumapi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_single',
                product_name: trimmedName,
                minimum_quantity: product.minimum_quantity
            })
        });

        const result = await response.json();

        if (result.success) {
            pendingChanges.delete(trimmedName);
            showAlert(`${trimmedName} 设置保存成功`, 'success');
        } else {
            showAlert('保存失败: ' + (result.message || '未知错误'), 'error');
        }

    } catch (error) {
        showAlert('保存失败，请检查网络连接', 'error');
        console.error('Error:', error);
    }
}

// 批量保存所有更改
async function saveAllSettings() {
    if (pendingChanges.size === 0) {
        showAlert('没有需要保存的更改', 'info');
        return;
    }

    const changedProducts = Array.from(pendingChanges).map(productName => {
        // 确保产品名称去除空格
        const trimmedName = (productName || '').trim();
        const product = allProducts.find(p => p.product_name === trimmedName);
        return {
            product_name: trimmedName,
            minimum_quantity: product ? product.minimum_quantity : 0
        };
    });

    try {
        const response = await fetch('stockminimumapi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_batch',
                products: changedProducts
            })
        });

        const result = await response.json();

        if (result.success) {
            pendingChanges.clear();
            showAlert(`成功保存 ${changedProducts.length} 个货品的设置`, 'success');
        } else {
            showAlert('批量保存失败: ' + (result.message || '未知错误'), 'error');
        }

    } catch (error) {
        showAlert('保存失败，请检查网络连接', 'error');
        console.error('Error:', error);
    }
}

// 搜索设置
function searchSettings() {
    const productFilter = document.getElementById('product-filter').value.toLowerCase();
    const codeFilter = document.getElementById('code-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;

    filteredProducts = allProducts.filter(product => {
        const matchProduct = !productFilter || product.product_name.toLowerCase().includes(productFilter);
        const matchCode = !codeFilter || (product.product_code && product.product_code.toLowerCase().includes(codeFilter));

        let matchStatus = true;
        if (statusFilter) {
            switch (statusFilter) {
                case 'active':
                    matchStatus = product.minimum_quantity > 0;
                    break;
                case 'inactive':
                    matchStatus = product.minimum_quantity <= 0;
                    break;
                case 'warning':
                    // 这里可以根据实际库存数量来判断
                    matchStatus = product.minimum_quantity > 0;
                    break;
            }
        }

        return matchProduct && matchCode && matchStatus;
    });

    renderSettingsTable();

    if (filteredProducts.length === 0) {
        showAlert('未找到匹配的记录', 'info');
    } else {
        showAlert(`找到 ${filteredProducts.length} 条匹配记录`, 'success');
    }
}

// 重置过滤器
function resetFilters() {
    document.getElementById('product-filter').value = '';
    document.getElementById('code-filter').value = '';
    document.getElementById('status-filter').value = '';

    filteredProducts = [...allProducts];
    renderSettingsTable();

    showAlert('搜索条件已重置', 'info');
}

// 刷新数据
function refreshData() {
    if (pendingChanges.size > 0) {
        if (!confirm('有未保存的更改，刷新将丢失这些更改。确定要继续吗？')) {
            return;
        }
        pendingChanges.clear();
    }

    loadProductsAndSettings();
}

// 检查URL参数中的system
const urlParams = new URLSearchParams(window.location.search);
const currentSystem = urlParams.get('system') || 'central';

// 返回库存管理
function goBack() {
    if (pendingChanges.size > 0) {
        if (!confirm('有未保存的更改，离开将丢失这些更改。确定要离开吗？')) {
            return;
        }
    }

    const systemParam = `?system=${currentSystem}`;
    window.location.href = `stocklistall${systemParam}`;
}

// 完全替换现有的 showAlert 函数
function showAlert(message, type = 'success') {
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
    const cfg = {
        'success': { icon: '✅', title: '操作成功' },
        'error':   { icon: '❌', title: '操作失败' },
        'info':    { icon: 'ℹ️', title: '提示信息' },
        'warning': { icon: '⚠️', title: '注意' }
    }[type] || { icon: '✅', title: '操作成功' };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.id = toastId;
    toast.innerHTML = `
        <div class="toast-icon-wrap">` + '

    container.appendChild(toast);

    // 显示动画
    setTimeout(() => {
        toast.classList.add('show');
    }, 0);

    // 自动关闭
    setTimeout(() => {
        closeToast(toastId);
    }, 700);
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

// 添加关闭所有通知的函数（可选）
function closeAllToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        closeToast(toast.id);
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 键盘快捷键
document.addEventListener('keydown', function (e) {
    // Ctrl+S 保存
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllSettings();
    }

    // Ctrl+F 聚焦搜索框
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('product-filter').focus();
    }

    // 添加Ctrl+G聚焦编号搜索框
    if (e.ctrlKey && e.key === 'g') {
        e.preventDefault();
        document.getElementById('code-filter').focus();
    }
});

// 离开页面前检查未保存更改
window.addEventListener('beforeunload', function (e) {
    if (pendingChanges.size > 0) {
        e.preventDefault();
        e.returnValue = '有未保存的更改，确定要离开吗？';
    }
}); + `{cfg.icon}</div>
        <div class="toast-body">
            <div class="toast-title">` + '

    container.appendChild(toast);

    // 显示动画
    setTimeout(() => {
        toast.classList.add('show');
    }, 0);

    // 自动关闭
    setTimeout(() => {
        closeToast(toastId);
    }, 700);
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

// 添加关闭所有通知的函数（可选）
function closeAllToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        closeToast(toast.id);
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 键盘快捷键
document.addEventListener('keydown', function (e) {
    // Ctrl+S 保存
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllSettings();
    }

    // Ctrl+F 聚焦搜索框
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('product-filter').focus();
    }

    // 添加Ctrl+G聚焦编号搜索框
    if (e.ctrlKey && e.key === 'g') {
        e.preventDefault();
        document.getElementById('code-filter').focus();
    }
});

// 离开页面前检查未保存更改
window.addEventListener('beforeunload', function (e) {
    if (pendingChanges.size > 0) {
        e.preventDefault();
        e.returnValue = '有未保存的更改，确定要离开吗？';
    }
}); + `{cfg.title}</div>
            <div class="toast-msg">` + '

    container.appendChild(toast);

    // 显示动画
    setTimeout(() => {
        toast.classList.add('show');
    }, 0);

    // 自动关闭
    setTimeout(() => {
        closeToast(toastId);
    }, 700);
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

// 添加关闭所有通知的函数（可选）
function closeAllToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        closeToast(toast.id);
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 键盘快捷键
document.addEventListener('keydown', function (e) {
    // Ctrl+S 保存
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllSettings();
    }

    // Ctrl+F 聚焦搜索框
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('product-filter').focus();
    }

    // 添加Ctrl+G聚焦编号搜索框
    if (e.ctrlKey && e.key === 'g') {
        e.preventDefault();
        document.getElementById('code-filter').focus();
    }
});

// 离开页面前检查未保存更改
window.addEventListener('beforeunload', function (e) {
    if (pendingChanges.size > 0) {
        e.preventDefault();
        e.returnValue = '有未保存的更改，确定要离开吗？';
    }
}); + `{message}</div>
        </div>
        <button class="toast-close" onclick="closeToast('` + '

    container.appendChild(toast);

    // 显示动画
    setTimeout(() => {
        toast.classList.add('show');
    }, 0);

    // 自动关闭
    setTimeout(() => {
        closeToast(toastId);
    }, 700);
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

// 添加关闭所有通知的函数（可选）
function closeAllToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        closeToast(toast.id);
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 键盘快捷键
document.addEventListener('keydown', function (e) {
    // Ctrl+S 保存
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllSettings();
    }

    // Ctrl+F 聚焦搜索框
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('product-filter').focus();
    }

    // 添加Ctrl+G聚焦编号搜索框
    if (e.ctrlKey && e.key === 'g') {
        e.preventDefault();
        document.getElementById('code-filter').focus();
    }
});

// 离开页面前检查未保存更改
window.addEventListener('beforeunload', function (e) {
    if (pendingChanges.size > 0) {
        e.preventDefault();
        e.returnValue = '有未保存的更改，确定要离开吗？';
    }
}); + `{toastId}')">&times;</button>
        <div class="toast-progress"></div>
    `;

    container.appendChild(toast);

    // 显示动画
    setTimeout(() => {
        toast.classList.add('show');
    }, 0);

    // 自动关闭
    setTimeout(() => {
        closeToast(toastId);
    }, 700);
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

// 添加关闭所有通知的函数（可选）
function closeAllToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        closeToast(toast.id);
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', initApp);

// 键盘快捷键
document.addEventListener('keydown', function (e) {
    // Ctrl+S 保存
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllSettings();
    }

    // Ctrl+F 聚焦搜索框
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('product-filter').focus();
    }

    // 添加Ctrl+G聚焦编号搜索框
    if (e.ctrlKey && e.key === 'g') {
        e.preventDefault();
        document.getElementById('code-filter').focus();
    }
});

// 离开页面前检查未保存更改
window.addEventListener('beforeunload', function (e) {
    if (pendingChanges.size > 0) {
        e.preventDefault();
        e.returnValue = '有未保存的更改，确定要离开吗？';
    }
});