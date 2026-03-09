/**
 * TOKYO JAPANESE CUISINE — Menu Management Dashboard JS
 * Extracted from menu_dashboard.php for cleaner architecture.
 */

const API = 'menu_api.php';

// LOCAL STATE
let currentType = 'grand';
let currentCatId = null;
let currentCatName = '';
let deleteItemId = null;
let deleteCatId = null;
let searchTimer = null;
let allCats = { grand: [], sushi: [] };

document.addEventListener('DOMContentLoaded', () => {
    // Initial Loads
    loadCategories('grand');
    loadCategories('sushi');

    // Image preview for add form
    const fileInput = document.getElementById('file-input');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            previewFile(this.files[0], 'preview-img', 'preview-name', 'preview-wrap');
        });
    }

    // Drag & drop for add form
    const dz = document.getElementById('drop-zone');
    if (dz) {
        dz.addEventListener('dragover', (e) => {
            e.preventDefault();
            dz.style.borderColor = 'var(--gold)';
            dz.style.background = '#fffbf5';
        });
        dz.addEventListener('dragleave', () => {
            dz.style.borderColor = 'var(--border)';
            dz.style.background = '#fdfdfd';
        });
        dz.addEventListener('drop', (e) => {
            e.preventDefault();
            dz.style.borderColor = 'var(--border)';
            dz.style.background = '#fdfdfd';
            const f = e.dataTransfer.files[0];
            if (f) {
                document.getElementById('file-input').files = e.dataTransfer.files;
                previewFile(f, 'preview-img', 'preview-name', 'preview-wrap');
            }
        });
    }

    // Close modals on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(o => {
        o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); });
    });

    // Edit image preview handler
    const editImage = document.getElementById('edit-image');
    if (editImage) {
        editImage.addEventListener('change', function () {
            const f = this.files[0]; if (!f) return;
            const r = new FileReader();
            r.onload = e => {
                document.getElementById('edit-thumb-wrap').innerHTML = `<img src="${e.target.result}" style="max-height:100px;border-radius:12px;border:3px solid var(--gold);margin-top:10px">`;
            };
            r.readAsDataURL(f);
        });
    }
});

// CORE API HELPERS
async function api(params) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(params)) fd.append(k, v);
    try {
        const r = await fetch(API, { method: 'POST', body: fd });
        return await r.json();
    } catch (e) {
        console.error('API Error:', e);
        return { success: false, message: 'Network or server error.' };
    }
}

async function apiGet(params) {
    const qs = new URLSearchParams(params).toString();
    try {
        const r = await fetch(`${API}?${qs}`);
        return await r.json();
    } catch (e) {
        console.error('API GET Error:', e);
        return { success: false, message: 'Network or server error.' };
    }
}

// UI HELPERS
function showToast(msg, duration = 3000) {
    const t = document.getElementById('toast');
    const m = document.getElementById('toast-msg');
    if (!t || !m) return;
    m.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), duration);
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove('show');
}

function openModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add('show');
}

function previewFile(file, imgId, nameId, wrapId) {
    if (!file) return;
    const r = new FileReader();
    r.onload = e => {
        const img = document.getElementById(imgId);
        const name = document.getElementById(nameId);
        const wrap = document.getElementById(wrapId);
        if (img) img.src = e.target.result;
        if (name) name.textContent = '✅ ' + file.name;
        if (wrap) wrap.style.display = 'block';
    };
    r.readAsDataURL(file);
}

function fmtDate(str) {
    if (!str) return '—';
    return str.slice(0, 10);
}

// CATEGORY LOGIC
async function loadCategories(type) {
    const res = await apiGet({ action: 'get_categories', type });
    if (!res.success) { showToast('⚠️ 无法加载分类'); return; }

    allCats[type] = res.data.categories;

    // Update the tab subtitle counter
    const total = res.data.categories.reduce((s, c) => s + parseInt(c.item_count || 0), 0);
    const counterEl = document.getElementById(`tab-${type}-count`);
    if (counterEl) counterEl.textContent = `${total} 项记录`;

    if (type === currentType) renderCatList(type);
}

function renderCatList(type) {
    const cats = allCats[type] || [];
    const list = document.getElementById('cat-list');
    if (!list) return;

    if (cats.length === 0) {
        list.innerHTML = `<div style="text-align:center;padding:30px;color:var(--muted);font-size:13px">暂无分类<br><small>点击右上角 + 开始</small></div>`;
        return;
    }

    list.innerHTML = cats.map(c => `
        <div class="cat-item ${c.id == currentCatId ? 'active' : ''}" 
             onclick="selectCat(${c.id}, '${escHtml(c.category_name)}', ${c.item_count})" 
             data-id="${c.id}">
            <div style="display:flex;align-items:center;gap:8px;flex:1;overflow:hidden">
                <span class="cat-count" title="项目数量">${c.item_count}</span>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(c.category_name)}</span>
                <span style="font-size:10px;opacity:0.4;font-weight:400" title="排序权重">#${c.sort_order || 0}</span>
            </div>
            <div class="cat-item-actions">
                <button class="btn-edit-cat" onclick="event.stopPropagation();openEditCatModal(${c.id})" title="编辑分类">✎</button>
                <button class="btn-del-cat" onclick="event.stopPropagation();confirmDelCat(${c.id},'${escHtml(c.category_name)}')" title="删除分类">✕</button>
            </div>
        </div>
    `).join('');

    // If no category selected yet, pick first
    if (!currentCatId && cats.length > 0) {
        selectCat(cats[0].id, cats[0].category_name, cats[0].item_count);
    }
}

// TAB SWITCHING
function switchTab(el, type) {
    currentType = type;
    currentCatId = null;

    // UI Updates
    document.querySelectorAll('.menu-tab').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');
    else {
        const target = document.getElementById('tab-' + type);
        if (target) target.classList.add('active');
    }

    const bc = document.getElementById('bc-tab');
    if (bc) bc.textContent = type === 'grand' ? 'Grand Menu' : 'Sushi Menu';

    document.getElementById('table-title').textContent = '—';
    document.getElementById('item-count').textContent = '0 项';
    document.getElementById('menu-tbody').innerHTML = `<tr><td colspan="6" style="text-align:center;padding:60px;color:var(--muted);font-size:14px">请先在左侧选择一个分类</td></tr>`;
    document.getElementById('cur-cat-label').textContent = '请选择分类';

    renderCatList(type);
}

function selectCat(catId, catName, count) {
    currentCatId = catId;
    currentCatName = catName;

    document.querySelectorAll('.cat-item').forEach(i => i.classList.remove('active'));
    const targetCat = document.querySelector(`.cat-item[data-id="${catId}"]`);
    if (targetCat) targetCat.classList.add('active');

    document.getElementById('cur-cat-label').textContent = catName;
    document.getElementById('table-title').textContent = catName;
    document.getElementById('item-count').textContent = `${count} 项`;
    document.getElementById('search-input').value = '';

    loadItems();
}

// ITEM LOGIC
async function loadItems(search = '') {
    const tbody = document.getElementById('menu-tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6">
        <div class="skeleton" style="height:35px;margin:15px;border-radius:10px"></div>
        <div class="skeleton" style="height:35px;margin:15px;border-radius:10px"></div>
        <div class="skeleton" style="height:35px;margin:15px;border-radius:10px"></div>
    </td></tr>`;

    const params = { action: 'get', type: currentType, category_id: currentCatId };
    if (search) params.search = search;

    const res = await apiGet(params);

    // Smooth transition effect
    tbody.classList.remove('fade-in');
    void tbody.offsetWidth; // Trigger reflow
    tbody.classList.add('fade-in');

    if (!res.success) {
        showToast('⚠️ 加载失败：' + res.message);
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:40px">加载出错</td></tr>`;
        return;
    }

    const items = res.data.items;
    document.getElementById('item-count').textContent = `${items.length} 项`;

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:80px;color:var(--muted)">
            <div style="font-size:40px;margin-bottom:15px">🍽️</div>
            <p>该分类下暂无项目</p>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = items.map(item => `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:15px">
                    ${item.image_url
            ? `<img class="item-thumb" src="${escHtml(item.image_url)}" onerror="this.src='../images/placeholder.png'">`
            : `<div class="item-thumb" style="background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:18px">🍱</div>`
        }
                    <div>
                        <div style="display:flex;align-items:center">
                            ${item.item_code ? `<span class="item-code">${escHtml(item.item_code)}</span>` : ''}
                            <span class="item-name">${escHtml(item.item_name)}</span>
                        </div>
                        <div style="font-size:12px;color:var(--muted);margin-top:4px">
                            ${item.item_name_cn ? escHtml(item.item_name_cn) : ''} 
                            ${item.item_desc ? `<span style="opacity:0.5;margin:0 4px">|</span>` + escHtml(item.item_desc) : ''}
                        </div>
                    </div>
                </div>
            </td>
            <td><strong style="color:var(--brown)">${item.price_formatted || '—'}</strong></td>
            <td>
                ${item.image_url
            ? `<span style="color:var(--green);font-size:12px">● 已同步</span>`
            : `<span style="color:var(--muted);font-size:12px;opacity:0.5">无图片</span>`
        }
            </td>
            <td>
                <div class="status-badge ${item.status === 'published' ? 's-pub' : 's-draft'}" 
                     onclick="toggleStatus(${item.id})" style="cursor:pointer">
                    <span class="s-dot"></span>
                    ${item.status === 'published' ? '已发布' : '草稿'}
                </div>
            </td>
            <td style="color:var(--muted);font-size:12px">${fmtDate(item.created_at)}</td>
            <td>
                <div style="display:flex;gap:8px">
                    <button class="btn btn-secondary btn-small" style="padding:4px 10px;border-radius:8px" onclick='openEditModal(${JSON.stringify(item)})'>编辑</button>
                    <button class="btn btn-danger btn-small" style="padding:4px 10px;border-radius:8px" onclick="confirmDelete(${item.id}, '${escHtml(item.item_name)}')">删除</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ACTIONS
function handleSearch(val) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadItems(val), 400);
}

async function handleAdd() {
    const name = document.getElementById('inp-name').value.trim();
    if (!name) { showToast('⚠️ 请填写菜品名称'); return; }
    if (!currentCatId) { showToast('⚠️ 请先选择分类'); return; }

    const btn = document.getElementById('btn-submit');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '保存中...';

    const fd = new FormData();
    fd.append('action', 'add');
    fd.append('type', currentType);
    fd.append('category_id', currentCatId);
    fd.append('item_code', document.getElementById('inp-code').value.trim());
    fd.append('item_name', name);
    fd.append('item_name_cn', document.getElementById('inp-cn').value.trim());
    fd.append('item_desc', document.getElementById('inp-desc').value.trim());
    fd.append('price', document.getElementById('inp-price').value.trim());
    fd.append('status', document.getElementById('inp-status').value);

    const imgFile = document.getElementById('file-input').files[0];
    if (imgFile) fd.append('image', imgFile);

    try {
        const resp = await fetch(API, { method: 'POST', body: fd });
        const result = await resp.json();
        if (result.success) {
            showToast('✅ 项目已成功添加');
            resetForm();
            await loadCategories(currentType);
            await loadItems();
        } else {
            showToast('❌ 错误：' + result.message);
        }
    } catch (e) {
        showToast('❌ 网络提交失败');
    }

    btn.disabled = false;
    btn.innerHTML = originalText;
}

function resetForm() {
    ['inp-code', 'inp-name', 'inp-cn', 'inp-desc', 'inp-price'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const s = document.getElementById('inp-status');
    if (s) s.value = 'published';
    const pw = document.getElementById('preview-wrap');
    if (pw) pw.style.display = 'none';
    const fi = document.getElementById('file-input');
    if (fi) fi.value = '';
}

async function toggleStatus(id) {
    const res = await api({ action: 'toggle_status', id });
    if (res.success) {
        showToast(`🔄 状态已更新为「${res.data.status === 'published' ? '已发布' : '草稿'}」`);
        loadItems(document.getElementById('search-input').value);
    } else {
        showToast('❌ 切换失败');
    }
}

// MODAL CONTROLS
function confirmDelete(id, name) {
    deleteItemId = id;
    document.getElementById('del-item-name').textContent = name;
    openModal('del-modal');
}

async function doDelete() {
    const btn = document.getElementById('btn-confirm-del');
    btn.textContent = '提交中...';
    btn.disabled = true;

    const res = await api({ action: 'delete', id: deleteItemId });
    closeModal('del-modal');
    btn.textContent = '确认删除';
    btn.disabled = false;

    if (res.success) {
        showToast('🗑️ 项目已移除');
        await loadCategories(currentType);
        await loadItems();
    } else {
        showToast('❌ 删除失败');
    }
}

function openEditModal(item) {
    document.getElementById('edit-id').value = item.id;
    document.getElementById('edit-code').value = item.item_code || '';
    document.getElementById('edit-name').value = item.item_name || '';
    document.getElementById('edit-cn').value = item.item_name_cn || '';
    document.getElementById('edit-desc').value = item.item_desc || '';
    document.getElementById('edit-price').value = item.price || '';
    document.getElementById('edit-status').value = item.status;
    document.getElementById('edit-image').value = '';

    const thumbWrap = document.getElementById('edit-thumb-wrap');
    thumbWrap.innerHTML = item.image_url
        ? `<img src="${escHtml(item.image_url)}" style="max-height:100px;border-radius:12px;border:3px solid var(--gold);margin-top:10px">`
        : '';

    openModal('edit-modal');
}

async function doEdit() {
    const name = document.getElementById('edit-name').value.trim();
    if (!name) { showToast('⚠️ 名称不能为空'); return; }

    const btn = document.getElementById('btn-edit-save');
    btn.disabled = true;
    btn.innerHTML = '正在保存...';

    const fd = new FormData();
    fd.append('action', 'edit');
    fd.append('id', document.getElementById('edit-id').value);
    fd.append('item_code', document.getElementById('edit-code').value.trim());
    fd.append('item_name', name);
    fd.append('item_name_cn', document.getElementById('edit-cn').value.trim());
    fd.append('item_desc', document.getElementById('edit-desc').value.trim());
    fd.append('price', document.getElementById('edit-price').value.trim());
    fd.append('status', document.getElementById('edit-status').value);

    const imgFile = document.getElementById('edit-image').files[0];
    if (imgFile) fd.append('image', imgFile);

    try {
        const r = await fetch(API, { method: 'POST', body: fd });
        const res = await r.json();
        if (res.success) {
            closeModal('edit-modal');
            showToast('✅ 修改已生效');
            loadItems(document.getElementById('search-input').value);
        } else {
            showToast('❌ 错误：' + res.message);
        }
    } catch (e) {
        showToast('❌ 提交过程中出现问题');
    }

    btn.disabled = false;
    btn.innerHTML = '💾 保存修改';
}

function openAddCatModal() {
    document.getElementById('new-cat-name').value = '';
    document.getElementById('new-cat-order').value = '0';
    openModal('add-cat-modal');
}

async function doAddCat() {
    const name = document.getElementById('new-cat-name').value.trim();
    if (!name) { showToast('⚠️ 分类名不能为空'); return; }

    const res = await api({
        action: 'add_category',
        type: currentType,
        category_name: name,
        sort_order: document.getElementById('new-cat-order').value || 0,
    });

    closeModal('add-cat-modal');

    if (res.success) {
        showToast('✅ 已成功创建分类');
        await loadCategories(currentType);
        const newCat = allCats[currentType].find(c => c.id == res.data.id);
        if (newCat) selectCat(newCat.id, newCat.category_name, 0);
    } else {
        showToast('❌ 失败：' + res.message);
    }
}

function confirmDelCat(id, name) {
    deleteCatId = id;
    document.getElementById('del-cat-name').textContent = name;
    openModal('del-cat-modal');
}

async function doDeleteCat() {
    const res = await api({ action: 'delete_category', id: deleteCatId });
    closeModal('del-cat-modal');

    if (res.success) {
        showToast('🗑️ 分类及关联项目已删除');
        currentCatId = null;
        await loadCategories(currentType);
        document.getElementById('table-title').textContent = '—';
        document.getElementById('menu-tbody').innerHTML = `<tr><td colspan="6" style="text-align:center;padding:60px;color:var(--muted)">请在左侧选择分类</td></tr>`;
    } else {
        showToast('❌ 加载失败');
    }
}

function openEditCatModal(id) {
    // Look up category in local state
    const cat = allCats[currentType].find(c => c.id == id);
    if (!cat) return;

    document.getElementById('edit-cat-id').value = cat.id;
    document.getElementById('edit-cat-name').value = cat.category_name;
    document.getElementById('edit-cat-order').value = cat.sort_order || 0;
    openModal('edit-cat-modal');
}

async function doEditCat() {
    const id = document.getElementById('edit-cat-id').value;
    const name = document.getElementById('edit-cat-name').value.trim();
    const order = document.getElementById('edit-cat-order').value;

    if (!name) { showToast('⚠️ 分类名称不能为空'); return; }

    const res = await api({
        action: 'edit_category',
        id,
        category_name: name,
        sort_order: order
    });

    closeModal('edit-cat-modal');

    if (res.success) {
        showToast('✅ 分类已更新');
        await loadCategories(currentType);
    } else {
        showToast('❌ 更新失败：' + res.message);
    }
}

function toggleAddCard() {
    document.getElementById('add-card').classList.toggle('collapsed');
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
