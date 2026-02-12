<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>菜单成本数据管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menucostdata.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>菜单成本数据管理</h1>
        </div>

        <div id="alert-container"></div>

        <!-- 行数选择模态框 -->
        <div id="rows-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">新增记录</h3>
                    <button class="modal-close" onclick="closeRowsModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rows-count">要创建的行数 *</label>
                        <input type="number" id="rows-count" class="form-input" min="1" max="50" value="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-modal btn-modal-secondary" onclick="closeRowsModal()">取消</button>
                    <button class="btn-modal btn-modal-primary" onclick="createMultipleRows()">
                        <i class="fas fa-plus"></i>
                        创建记录
                    </button>
                </div>
            </div>
        </div>

        <!-- 添加表单 -->
        <div class="add-form-section">
            <div class="add-form-title">添加新记录</div>
            <div class="form-actions-row">
                <div class="search-in-form">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">搜索</span>
                    <input type="text" id="unified-filter" class="unified-search-input" 
                        placeholder="输入关键字搜索...">
                </div>
                <button class="btn btn-primary" onclick="showRowsModal()">
                    <i class="fas fa-plus"></i>
                    新增
                </button>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="table-container">
            <div class="table-scroll-container">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>原材料名称</th>
                            <th>价格</th>
                            <th>单位</th>
                            <th>规格</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody id="data-table-body">
                        <tr>
                            <td colspan="6" class="empty-state">
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
        let dataList = [];
        let filteredDataList = [];
        let editingId = null;
        let newRowCounter = 0;

        // 页面加载时获取数据
        window.addEventListener('DOMContentLoaded', function() {
            loadData();
            setupRealTimeSearch();
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
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 3000);
        }

        // 加载数据
        async function loadData() {
            try {
                const response = await fetch('/backendtest/api/menucostdata_api.php');
                const result = await response.json();
                
                if (result.success) {
                    dataList = result.data || [];
                    filteredDataList = [...dataList];
                    applySearchFilter();
                } else {
                    showAlert(result.message || '加载数据失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // 应用搜索过滤
        function applySearchFilter() {
            const searchTerm = document.getElementById('unified-filter').value.trim().toLowerCase();
            
            if (!searchTerm) {
                filteredDataList = [...dataList];
            } else {
                filteredDataList = dataList.filter(item => {
                    return (
                        (item.product_name && item.product_name.toLowerCase().includes(searchTerm)) ||
                        (item.price && item.price.toString().includes(searchTerm)) ||
                        (item.unit && item.unit.toString().includes(searchTerm)) ||
                        (item.specification && item.specification.toLowerCase().includes(searchTerm))
                    );
                });
            }
            
            renderTable();
        }

        // 设置实时搜索
        function setupRealTimeSearch() {
            const searchInput = document.getElementById('unified-filter');
            
            // 防抖处理，避免频繁搜索
            let debounceTimer;
            
            function handleSearch() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    applySearchFilter();
                }, 300); // 300ms延迟
            }
            
            if (searchInput) {
                searchInput.addEventListener('input', handleSearch);
            }
        }

        // 渲染表格
        function renderTable() {
            const tbody = document.getElementById('data-table-body');
            
            // 保存新增行
            const newRows = Array.from(tbody.querySelectorAll('tr.new-row')).map(row => {
                return {
                    rowId: row.dataset.rowId,
                    html: row.outerHTML
                };
            });
            
            if (filteredDataList.length === 0 && newRows.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <div>暂无数据</div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = filteredDataList.map((item, index) => {
                const isEditing = editingId === item.id;
                
                return `
                    <tr class="${isEditing ? 'editing' : ''}">
                        <td class="text-center"><span>${index + 1}</span></td>
                        <td>
                            ${isEditing ? 
                                `<input type="text" class="table-input" id="edit-product-name-${item.id}" value="${escapeHtml(item.product_name)}">` :
                                `<span>${escapeHtml(item.product_name)}</span>`
                            }
                        </td>
                        <td class="text-right">
                            ${isEditing ? 
                                `<input type="number" class="table-input" id="edit-price-${item.id}" value="${item.price}" step="0.01" min="0">` :
                                `<span>${parseFloat(item.price).toFixed(2)}</span>`
                            }
                        </td>
                        <td>
                            ${isEditing ? 
                                `<input type="number" class="table-input" id="edit-unit-${item.id}" value="${item.unit}" min="0" step="1">` :
                                `<span>${escapeHtml(item.unit)}</span>`
                            }
                        </td>
                        <td>
                            ${isEditing ? 
                                `<input type="text" class="table-input" id="edit-specification-${item.id}" value="${escapeHtml(item.specification || '')}">` :
                                `<span>${escapeHtml(item.specification || '')}</span>`
                            }
                        </td>
                        <td class="text-center">
                            <span class="action-cell">
                                ${isEditing ? 
                                    `<button class="action-btn edit-btn save-mode" onclick="saveRecord(${item.id})" title="保存">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button class="action-btn" onclick="cancelEdit(${item.id})" title="取消" style="background: #6b7280;">
                                        <i class="fas fa-times"></i>
                                    </button>` :
                                    `<button class="action-btn edit-btn" onclick="editRecord(${item.id})" title="编辑">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteRecord(${item.id})" title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>`
                                }
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
            
            // 恢复新增行
            newRows.forEach(newRow => {
                html += newRow.html;
            });
            
            tbody.innerHTML = html;
            
            // 更新新增行的序号
            updateNewRowNumbers();
        }

        // 更新新增行的序号
        function updateNewRowNumbers() {
            const tbody = document.getElementById('data-table-body');
            const allRows = Array.from(tbody.querySelectorAll('tr:not(.empty-state)'));
            const newRows = Array.from(tbody.querySelectorAll('tr.new-row'));
            
            allRows.forEach((row, index) => {
                const firstCell = row.querySelector('td:first-child span');
                if (firstCell) {
                    firstCell.textContent = index + 1;
                }
            });
        }

        // HTML转义
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 显示行数选择模态框
        function showRowsModal() {
            const modal = document.getElementById('rows-modal');
            const rowsCountInput = document.getElementById('rows-count');
            
            // 重置行数输入框为1
            rowsCountInput.value = 1;
            
            // 显示弹窗
            modal.classList.add('show');
            
            // 聚焦到行数输入框
            setTimeout(() => {
                rowsCountInput.focus();
                rowsCountInput.select();
            }, 100);
        }

        // 关闭行数选择模态框
        function closeRowsModal() {
            const modal = document.getElementById('rows-modal');
            modal.classList.remove('show');
        }

        // 点击弹窗外部关闭弹窗
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('rows-modal');
            if (event.target === modal) {
                closeRowsModal();
            }
        });

        // 创建多行记录
        function createMultipleRows() {
            const rowsCount = parseInt(document.getElementById('rows-count').value);
            
            // 验证输入
            if (!rowsCount || rowsCount < 1 || rowsCount > 50) {
                showAlert('请输入有效的行数（1-50）', 'error');
                return;
            }
            
            // 关闭弹窗
            closeRowsModal();
            
            // 创建指定数量的行
            for (let i = 0; i < rowsCount; i++) {
                addNewRow();
            }
            
            // 滚动到表格底部
            setTimeout(() => {
                const tableContainer = document.querySelector('.table-scroll-container');
                if (tableContainer) {
                    tableContainer.scrollTop = tableContainer.scrollHeight;
                }
            }, 100);
            
            showAlert(`成功创建 ${rowsCount} 行记录`, 'success');
        }

        // 添加新行到表格
        function addNewRow() {
            const tbody = document.getElementById('data-table-body');
            const rowId = 'new-' + Date.now() + '-' + (newRowCounter++);
            const row = document.createElement('tr');
            row.className = 'new-row';
            row.dataset.rowId = rowId;
            
            // 计算序号：数据行数 + 已有新增行数 + 1
            const dataRows = tbody.querySelectorAll('tr:not(.new-row):not(.empty-state)').length;
            const existingNewRows = tbody.querySelectorAll('tr.new-row').length;
            const rowIndex = dataRows + existingNewRows;
            
            row.innerHTML = `
                <td class="text-center"><span>${rowIndex + 1}</span></td>
                <td>
                    <input type="text" class="table-input" id="${rowId}-product-name" placeholder="请输入原材料名称">
                </td>
                <td class="text-right">
                    <input type="number" class="table-input" id="${rowId}-price" placeholder="0.00" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" class="table-input" id="${rowId}-unit" placeholder="0" min="0" step="1">
                </td>
                <td>
                    <input type="text" class="table-input" id="${rowId}-specification" placeholder="请输入规格">
                </td>
                <td class="text-center">
                    <span class="action-cell">
                        <button class="action-btn edit-btn save-mode" onclick="saveNewRow('${rowId}')" title="保存">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="action-btn delete-btn" onclick="cancelNewRow('${rowId}')" title="取消">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                </td>
            `;
            
            // 添加到表格底部
            tbody.appendChild(row);
            
            // 自动聚焦到第一个输入框
            setTimeout(() => {
                const firstInput = document.getElementById(`${rowId}-product-name`);
                if (firstInput) {
                    firstInput.focus();
                }
            }, 100);
        }

        // 保存新行
        async function saveNewRow(rowId) {
            const productName = document.getElementById(`${rowId}-product-name`).value.trim();
            const price = document.getElementById(`${rowId}-price`).value;
            const unit = document.getElementById(`${rowId}-unit`).value;
            const specification = document.getElementById(`${rowId}-specification`).value.trim();

            if (!productName) {
                showAlert('请输入原材料名称', 'error');
                return;
            }

            if (!price || parseFloat(price) < 0) {
                showAlert('请输入有效的价格', 'error');
                return;
            }

            if (!unit || parseFloat(unit) < 0 || isNaN(unit)) {
                showAlert('请输入有效的单位数字', 'error');
                return;
            }

            try {
                const response = await fetch('/backendtest/api/menucostdata_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_name: productName,
                        price: parseFloat(price),
                        unit: parseFloat(unit),
                        specification: specification
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('添加成功', 'success');
                    // 重新加载数据
                    await loadData();
                    // 清空搜索框
                    document.getElementById('unified-filter').value = '';
                } else {
                    showAlert(result.message || '添加失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // 取消新行
        function cancelNewRow(rowId) {
            const row = document.querySelector(`tr[data-row-id="${rowId}"]`);
            if (row) {
                row.remove();
                // 重新渲染表格以更新序号
                renderTable();
            }
        }

        // 编辑记录
        function editRecord(id) {
            editingId = id;
            renderTable();
        }

        // 取消编辑
        function cancelEdit(id) {
            editingId = null;
            renderTable();
        }

        // 保存记录
        async function saveRecord(id) {
            const productName = document.getElementById(`edit-product-name-${id}`).value.trim();
            const price = document.getElementById(`edit-price-${id}`).value;
            const unit = document.getElementById(`edit-unit-${id}`).value;
            const specification = document.getElementById(`edit-specification-${id}`).value.trim();

            if (!productName) {
                showAlert('请输入原材料名称', 'error');
                return;
            }

            if (!price || parseFloat(price) < 0) {
                showAlert('请输入有效的价格', 'error');
                return;
            }

            if (!unit || parseFloat(unit) < 0 || isNaN(unit)) {
                showAlert('请输入有效的单位数字', 'error');
                return;
            }

            try {
                const response = await fetch('/backendtest/api/menucostdata_api.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        product_name: productName,
                        price: parseFloat(price),
                        unit: unit,
                        specification: specification
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('更新成功', 'success');
                    editingId = null;
                    await loadData();
                    // 保持搜索过滤
                    applySearchFilter();
                } else {
                    showAlert(result.message || '更新失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // 删除记录
        async function deleteRecord(id) {
            if (!confirm('确定要删除这条记录吗？')) {
                return;
            }

            try {
                const response = await fetch(`/backendtest/api/menucostdata_api.php?id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('删除成功', 'success');
                    await loadData();
                    // 保持搜索过滤
                    applySearchFilter();
                } else {
                    showAlert(result.message || '删除失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            }
        }

        // 支持回车键添加记录（保留原有功能，但已移除表单）
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.classList.contains('table-input')) {
                const rowId = e.target.id.split('-').slice(0, 2).join('-');
                if (rowId.startsWith('new-')) {
                    // 如果是新行的输入框，找到保存按钮并点击
                    const saveBtn = document.querySelector(`tr[data-row-id="${rowId}"] .save-mode`);
                    if (saveBtn) {
                        saveBtn.click();
                    }
                }
            }
        });
    </script>
</body>
</html>

