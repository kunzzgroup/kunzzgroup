// ─── 全局状态 ────────────────────────────────────────────────────────────────
let currentSystem = (typeof INITIAL_SYSTEM !== 'undefined') ? INITIAL_SYSTEM : 'central';
let allProducts = [];        // 当前系统的货品完整列表
let filteredProducts = [];   // 搜索过滤后的列表
let pendingChanges = new Set();
let isLoading = false;

const SYSTEM_NAMES = {
    central: '中央',
    j1: 'J1',
    j2: 'J2',
    j3: 'J3'
};

// ─── 初始化 ──────────────────────────────────────────────────────────────────
function initApp() {
    loadProductsAndSettings(currentSystem);
}

// ─── 切换系统 (Tab) ──────────────────────────────────────────────────────────
function switchSystem(system) {
    if (system === currentSystem) return;

    currentSystem = system;

    // 更新 URL 不跳转
    const url = new URL(window.location);
    url.searchParams.set('system', system);
    window.history.replaceState({}, '', url);

    // 更新 Tab 高亮
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeTab = document.querySelector(`.tab-btn[data-system="${system}"]`);
    if (activeTab) activeTab.classList.add('active');

    // 更新标题
    const systemLabel = SYSTEM_NAMES[system] || system.toUpperCase();
    const pageTitle = document.getElementById('page-title');
    const tableTitle = document.getElementById('table-title');
    if (pageTitle) pageTitle.textContent = `最低库存设置 — ${systemLabel}`;
    if (tableTitle) tableTitle.textContent = `最低库存设置 — ${systemLabel}`;

    // 清空搜索框
    const filterInput = document.getElementById('unified-filter');
    if (filterInput) filterInput.value = '';

    // 清空未保存的变更
    pendingChanges.clear();

    // 重新加载该系统的货品数据
    loadProductsAndSettings(system);
}

// ─── 加载货品和设置数据 ──────────────────────────────────────────────────────
async function loadProductsAndSettings(system) {
    if (isLoading) return;
    isLoading = true;
    setLoadingState(true);

    try {
        const res = await fetch(`stockminimumapi.php?action=list&system=${system}&_t=${Date.now()}`, {
            cache: 'no-cache',
            headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
        });
        const result = await res.json();

        if (result.success) {
            allProducts = (result.data || []).map(item => ({
                ...item,
                product_name: (item.product_name || '').trim(),
                product_code: (item.product_code || '').trim(),
                minimum_quantity: parseFloat(item.minimum_quantity) || 0
            }));
            filteredProducts = [...allProducts];
            renderSettingsTable();
            updateDisplayedCount();
        } else {
            showToast('加载数据失败: ' + (result.message || '未知错误'), 'error');
        }

    } catch (error) {
        console.error('Error:', error);
        showToast('网络错误，请检查连接', 'error');
    } finally {
        isLoading = false;
        setLoadingState(false);
    }
}

// ─── 加载状态 ────────────────────────────────────────────────────────────────
function setLoadingState(loading) {
    const tbody = document.getElementById('settings-tbody');
    if (!tbody) return;
    if (loading) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" style="padding: 40px; text-align: center;">
                    <div class="loading"></div>
                    <div style="margin-top: 16px; color: #6b7280;">正在加载 ${SYSTEM_NAMES[currentSystem]} 货品数据...</div>
                </td>
            </tr>`;
    }
}

// ─── 渲染表格 ────────────────────────────────────────────────────────────────
function renderSettingsTable() {
    const tbody = document.getElementById('settings-tbody');
    if (!tbody) return;

    if (filteredProducts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="no-data">
                    <i class="fas fa-inbox"></i>
                    <div>暂无货品数据</div>
                </td>
            </tr>`;
        updateDisplayedCount();
        return;
    }

    let html = '';
    filteredProducts.forEach(product => {
        // Escape single quotes in product name for inline onclick
        const safeName = (product.product_name || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");

        html += `
            <tr>
                <td class="product-name-cell" style="text-align: left; padding-left: 15px;">${escapeHtml(product.product_name)}</td>
                <td class="code-cell">${escapeHtml(product.product_code || '-')}</td>
                <td>
                    <input type="number"
                        class="quantity-input"
                        value="${product.minimum_quantity}"
                        min="0"
                        step="0.01"
                        data-product="${safeName}"
                        onchange="markAsChanged('${safeName}', this.value)"
                        placeholder="0">
                </td>
                <td>
                    <button class="btn btn-primary btn-sm"
                            onclick="saveIndividualSetting('${safeName}', this)">
                        <i class="fas fa-save"></i> 保存
                    </button>
                </td>
            </tr>`;
    });

    tbody.innerHTML = html;
    updateDisplayedCount();
}

// ─── HTML 转义 ───────────────────────────────────────────────────────────────
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ─── 连接预留（搜索功能已移除）――――――――――――――――――――――――――――――
// (如需自行在此添加)

// ─── 更新显示计数 ─────────────────────────────────────────────────────────────
function updateDisplayedCount() {
    const el = document.getElementById('displayed-count');
    if (el) el.textContent = filteredProducts.length;
}

// ─── 标记已更改 ───────────────────────────────────────────────────────────────
function markAsChanged(productName, value) {
    const trimmedName = (productName || '').trim();
    const product = allProducts.find(p => p.product_name === trimmedName);
    if (product) {
        product.minimum_quantity = parseFloat(value) || 0;
        pendingChanges.add(trimmedName);
    }
}

// ─── 保存单个设置 ─────────────────────────────────────────────────────────────
async function saveIndividualSetting(productName, btn) {
    const trimmedName = (productName || '').trim();
    const product = allProducts.find(p => p.product_name === trimmedName);
    if (!product) return;

    // 按钮加载动画
    if (btn) {
        btn.disabled = true;
        btn.dataset.originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    try {
        const res = await fetch('stockminimumapi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save_single',
                product_name: trimmedName,
                minimum_quantity: product.minimum_quantity
            })
        });
        const result = await res.json();

        if (result.success) {
            pendingChanges.delete(trimmedName);
            showToast(`已保存：${trimmedName}`, 'success');
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

// ─── 批量保存 ─────────────────────────────────────────────────────────────────
async function saveAllSettings() {
    if (pendingChanges.size === 0) {
        showToast('没有未保存的更改', 'info');
        return;
    }

    const changedProducts = Array.from(pendingChanges).map(name => {
        const product = allProducts.find(p => p.product_name === name.trim());
        return {
            product_name: name.trim(),
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
        const res = await fetch('stockminimumapi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'save_batch', products: changedProducts })
        });
        const result = await res.json();

        if (result.success) {
            pendingChanges.clear();
            showToast(`成功保存 ${changedProducts.length} 个货品设置`, 'success');
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

// ─── 返回库存管理 ──────────────────────────────────────────────────────────────
function goBack() {
    if (pendingChanges.size > 0) {
        if (!confirm('有未保存的更改，离开将丢失这些更改。确定要离开吗？')) return;
    }
    window.location.href = `stocklistall?system=${currentSystem}`;
}

// ─── 键盘快捷键 ───────────────────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllSettings();
    }
});

// ─── 离开前提醒 ───────────────────────────────────────────────────────────────
window.addEventListener('beforeunload', function (e) {
    if (pendingChanges.size > 0) {
        e.preventDefault();
        e.returnValue = '有未保存的更改，确定要离开吗？';
    }
});

// ─── 启动 ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', initApp);