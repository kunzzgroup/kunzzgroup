// js/stock_recycle.js

let deletedData = [];

document.addEventListener('DOMContentLoaded', () => {
    loadDeletedData();
    
    // 搜索功能
    document.getElementById('recycle-search')?.addEventListener('input', (e) => {
        renderDeletedTable(e.target.value);
    });
});

async function loadDeletedData() {
    try {
        const response = await fetch('fetch_deleted.php');
        const result = await response.json();
        if (result.success) {
            deletedData = result.data;
            renderDeletedTable();
        } else {
            showAlert('加载失败: ' + result.message, 'error');
        }
    } catch (error) {
        showAlert('加载时发生错误', 'error');
    }
}

function renderDeletedTable(filter = '') {
    const tbody = document.getElementById('recycle-tbody');
    if (!tbody) return;

    const filtered = deletedData.filter(item => {
        const searchStr = `${item.product_name} ${item.receiver} ${item.system} ${item.deleted_by}`.toLowerCase();
        return searchStr.includes(filter.toLowerCase());
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 40px;">${filter ? '没有找到匹配的记录' : '回收站为空'}</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(item => `
        <tr>
            <td>${item.deleted_at}</td>
            <td>${item.deleted_by || 'Unknown'}</td>
            <td><span class="badge system-${item.system}">${item.system.toUpperCase()}</span></td>
            <td>${item.date}</td>
            <td>${item.product_name}</td>
            <td>
                ${item.in_quantity > 0 ? `<span style="color: #10b981;">入: ${item.in_quantity}</span>` : ''}
                ${item.out_quantity > 0 ? `<span style="color: #ef4444;">出: ${item.out_quantity}</span>` : ''}
            </td>
            <td>${item.specification}</td>
            <td>${item.receiver}</td>
            <td>
                <div class="action-btns">
                    <button class="restore-btn" onclick="restoreRecord(${item.id}, '${item.system}')">
                        <i class="fas fa-undo"></i> 恢复
                    </button>
                    <button class="perm-delete-btn" onclick="permanentDelete(${item.id}, '${item.system}')">
                        <i class="fas fa-trash-alt"></i> 彻底删除
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function restoreRecord(id, system) {
    if (!confirm('确定要恢复此记录吗？')) return;

    try {
        const response = await fetch('restore_stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: [id] })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('记录已成功恢复', 'success');
            loadDeletedData();
        } else {
            showAlert('恢复失败: ' + result.message, 'error');
        }
    } catch (error) {
        showAlert('恢复时发生错误', 'error');
    }
}

async function permanentDelete(id, system) {
    if (!confirm('警告：彻底删除后数据将无法恢复！确定要继续吗？')) return;

    try {
        const response = await fetch('delete_permanent.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: [id], system: system })
        });
        const result = await response.json();
        if (result.success) {
            showAlert('记录已彻底删除', 'success');
            loadDeletedData();
        } else {
            showAlert('删除失败: ' + result.message, 'error');
        }
    } catch (error) {
        showAlert('删除时发生错误', 'error');
    }
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('alert-container');
    if (!container) return;

    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.style.padding = '12px 16px';
    alert.style.borderRadius = '8px';
    alert.style.marginBottom = '16px';
    alert.style.display = 'flex';
    alert.style.alignItems = 'center';
    alert.style.gap = '12px';
    
    if (type === 'success') {
        alert.style.backgroundColor = '#ecfdf5';
        alert.style.color = '#065f46';
        alert.style.border = '1px solid #10b981';
        alert.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    } else {
        alert.style.backgroundColor = '#fef2f2';
        alert.style.color = '#991b1b';
        alert.style.border = '1px solid #ef4444';
        alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    }

    container.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}
