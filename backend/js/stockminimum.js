
// 全局变量
let allProducts = [];
let filteredProducts = [];
let isLoading = false;
let pendingChanges = new Set();

// 初始化
function initApp() {
    loadProductsAndSettings();
    setupRealTimeSearch();
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
                                    onclick="saveIndividualSetting('${product.product_name}', this)"
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
async function saveIndividualSetting(productName, btn) {
    // 确保产品名称去除空格，与数据处理逻辑一致
    const trimmedName = (productName || '').trim();
    const product = allProducts.find(p => p.product_name === trimmedName);
    if (!product) return;

    if (btn) {
        btn.disabled = true;
        btn.dataset.originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中';
    }

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
            showToast(`${trimmedName} 设置保存成功`, 'success');
        } else {
            showToast('保存失败: ' + (result.message || '未知错误'), 'error');
        }

    } catch (error) {
        showToast('保存失败，请检查网络连接', 'error');
        console.error('Error:', error);
    } finally {
        if (btn && btn.dataset.originalHTML) {
            btn.innerHTML = btn.dataset.originalHTML;
            btn.disabled = false;
        }
    }
}

// 批量保存所有更改
async function saveAllSettings() {
    if (pendingChanges.size === 0) {
        showToast('没有需要保存的更改', 'info');
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

    const btn = document.getElementById('saveAllBtn');
    if (btn) {
        btn.disabled = true;
        btn.dataset.originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';
    }

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
            showToast(`成功保存 ${changedProducts.length} 个货品的设置`, 'success');
        } else {
            showToast('批量保存失败: ' + (result.message || '未知错误'), 'error');
        }

    } catch (error) {
        showToast('保存失败，请检查网络连接', 'error');
        console.error('Error:', error);
    } finally {
        if (btn && btn.dataset.originalHTML) {
            btn.innerHTML = btn.dataset.originalHTML;
            btn.disabled = false;
        }
    }
}

// 实时搜索功能
function setupRealTimeSearch() {
    const searchInput = document.getElementById('unified-filter');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (!searchTerm) {
                filteredProducts = [...allProducts];
            } else {
                filteredProducts = allProducts.filter(product => {
                    const matchProduct = product.product_name && product.product_name.toLowerCase().includes(searchTerm);
                    const matchCode = product.product_code && product.product_code.toLowerCase().includes(searchTerm);
                    return matchProduct || matchCode;
                });
            }
            
            renderSettingsTable();
        });
    }
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





// 添加关闭所有通知的函数（可选）

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
        const searchInput = document.getElementById('unified-filter');
        if(searchInput) searchInput.focus();
    }
});

// 离开页面前检查未保存更改
window.addEventListener('beforeunload', function (e) {
    if (pendingChanges.size > 0) {
        e.preventDefault();
        e.returnValue = '有未保存的更改，确定要离开吗？';
    }
});