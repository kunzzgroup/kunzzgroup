/**
 * 🍱 TOKYO JAPANESE — Menu Dashboard
 * Maintenance-First Architecture
 */

// ── 1. CONFIG & STATE ──
const API = 'menu_api.php';
let menuType = 'grand', catId = null, catName = '', items = [];
let cats = { grand: [], sushi: [] };
let searchTmr = null, imgFile = null, editId = null;

/**
 * 🛠️ MAINTENANCE GUIDE (如何修改系统)
 * 
 * 1. 增加表单字段 (Add Form Field):
 *    - 在 menu_dashboard.php 的 <aside class="add-panel"> 中增加 HTML 输入框。
 *    - 在 openEditPanel() 函数中增加赋值逻辑 (document.getElementById('f-new').value = item.new_field)。
 *    - 在 doAdd() 函数的 p 对象中增加字段映射。
 * 
 * 2. 修改列表样式 (Edit List Styles):
 *    - 列表的 HTML 结构已“锁”在 PHP 中 (menu_api.php)。
 *    - JS 仅负责加载和切换，不控制具体 HTML 字符串。
 */

// ── 2. INITIALIZATION ──
document.addEventListener('DOMContentLoaded', () => {
    if (typeof INITIAL_CAT_ID !== 'undefined' && INITIAL_CAT_ID > 0) {
        catId = INITIAL_CAT_ID;
        catName = INITIAL_CAT_NAME;
        document.getElementById('bc-cat').textContent = catName;
        document.getElementById('add-panel-sub').textContent = `当前分类：${catName}`;
        initDrag('.cat-item', '#cat-scroll', 'cat');
        initDrag('.item-row', '#list-scroll', 'item');
        syncStats();
    } else {
        loadCats('grand');
    }
    loadCats('sushi');

    document.getElementById('confirm-bg')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeConfirm();
    });
});

// ── 3. CORE API ACTIONS ──
const apiGet = p => fetch(`${API}?${new URLSearchParams(p).toString()}`).then(r => r.json());

async function apiPost(p, files = {}) {
    const fd = new FormData();
    Object.entries(p).forEach(([k, v]) => fd.append(k, v));
    Object.entries(files).forEach(([k, v]) => { if (v) fd.append(k, v); });
    return fetch(API, { method: 'POST', body: fd }).then(r => r.json());
}

async function loadCats(t) {
    if (t !== menuType) {
        const res = await apiGet({ action: 'get_categories', type: t });
        if (res.success) cats[t] = res.data.categories;
        return;
    }
    const scroll = document.getElementById('cat-scroll');
    scroll.innerHTML = await fetch(`${API}?action=get_categories_html&type=${t}&active_id=${catId || 0}`).then(r => r.text());
    const resArr = await apiGet({ action: 'get_categories', type: t });
    if (resArr.success) cats[t] = resArr.data.categories;
    initDrag('.cat-item', '#cat-scroll', 'cat');
}

async function loadItems(search = '') {
    if (!catId) return;
    const scroll = document.getElementById('list-scroll');
    scroll.innerHTML = `<div class="skeleton" style="height:200px;margin:24;border-radius:12"></div>`;
    scroll.innerHTML = await fetch(`${API}?action=get_items_html&category_id=${catId}&search=${search}`).then(r => r.text());
    const res = await apiGet({ action: 'get', type: menuType, category_id: catId, search });
    if (res.success) { items = res.data.items; syncStats(); }
    initDrag('.item-row', '#list-scroll', 'item');
}

// ── 4. UI INTERACTIONS (Add/Edit/Delete) ──
async function doAdd() {
    const name = document.getElementById('f-name').value.trim();
    if (!name || !catId) return;
    const txt = document.getElementById('btn-submit-text');
    txt.textContent = editId ? `保存中...` : `添加中...`;

    const p = {
        action: editId ? 'edit' : 'add', id: editId, type: menuType, category_id: catId,
        item_code: document.getElementById('f-code').value.trim(),
        item_name: name,
        item_name_cn: document.getElementById('f-cn').value.trim(),
        item_desc: document.getElementById('f-desc').value.trim(),
        price: document.getElementById('f-price').value.trim(),
        status: document.getElementById('f-status').value
    };
    const res = await apiPost(p, imgFile ? { image: imgFile } : {});
    if (res.success) { if (editId) cancelEdit(); else resetForm(); loadItems(); toast(editId ? '✓ 已保存修改' : '✓ 已添加'); }
    txt.textContent = editId ? '✓ 保存修改' : '＋ 添加到菜单';
}

function openEditPanel(id) {
    const item = items.find(i => i.id == id);
    if (!item) return;
    editId = id;
    document.getElementById('add-panel-mode').textContent = '编辑模式';
    document.getElementById('add-panel-title').textContent = '编辑菜单项目';
    document.getElementById('add-panel-sub').textContent = `正在编辑：${item.item_name}`;
    document.getElementById('btn-panel-close').style.display = 'flex';
    document.getElementById('btn-submit-text').textContent = '✓ 保存修改';

    ['code', 'name', 'cn', 'desc', 'price', 'status'].forEach(k => {
        const el = document.getElementById('f-' + k);
        if (el) el.value = item['item_' + k] || item[k] || '';
    });

    if (item.image_url) {
        document.getElementById('img-zone-inner').style.display = 'none';
        const wrap = document.getElementById('img-preview-wrap');
        wrap.style.display = 'flex';
        wrap.querySelector('img').src = item.image_url;
        document.getElementById('img-preview-name').textContent = 'current_image.jpg';
    }
    document.querySelector('.add-scroll').scrollTop = 0;
}

function cancelEdit() {
    editId = null;
    document.getElementById('add-panel-mode').textContent = '新增模式';
    document.getElementById('add-panel-title').textContent = '新增菜单项目';
    const c = cats[menuType].find(x => x.id == catId);
    document.getElementById('add-panel-sub').textContent = c ? `当前分类：${c.category_name}` : '请先选择分类';
    document.getElementById('btn-panel-close').style.display = 'none';
    document.getElementById('btn-submit-text').textContent = '＋ 添加到菜单';
    resetForm();
}

// ── 5. UTILS & EFFECTS ──
const toast = (msg, ms = 2800) => {
    const t = document.getElementById('toast'); if (!t) return;
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._t); t._t = setTimeout(() => t.classList.remove('show'), ms);
};

const syncStats = () => {
    const pub = items.filter(i => i.status === 'published').length;
    document.getElementById('info-pub').textContent = `${pub} 已发布`;
    document.getElementById('info-dft').textContent = `${items.length - pub} 草稿`;
    document.getElementById('tb-stats').textContent = `${items.length} 项目`;
};

const resetForm = () => {
    ['f-code', 'f-name', 'f-cn', 'f-desc', 'f-price'].forEach(id => document.getElementById(id).value = '');
    clearImg();
};

function selectCat(id, name) {
    catId = id;
    document.getElementById('bc-cat').textContent = name;
    document.getElementById('add-panel-sub').textContent = `当前分类：${name}`;
    document.querySelectorAll('.cat-item').forEach(el => el.classList.toggle('active', el.dataset.id == id));
    loadItems();
}

function switchType(t) {
    menuType = t; catId = null; catName = '';
    ['tab-grand', 'tab-sushi'].forEach(id => document.getElementById(id).classList.toggle('active', id.includes(t)));
    document.getElementById('bc-tab').textContent = t === 'grand' ? 'Grand Menu' : 'Sushi Menu';
    document.getElementById('bc-cat').textContent = '—';
    document.getElementById('tb-stats').textContent = '0 项目';
    loadCats(t);
    document.getElementById('list-scroll').innerHTML = `<div class="empty-state"><div class="es-icon">🍽️</div><p>请选择左侧分类</p></div>`;
}

// (Rest of the functions: Drag, Deletion, Confirm, Image handling, etc. simplified and grouped...)
function initDrag(selector, parentId, type) {
    const p = document.querySelector(parentId);
    let drag = null;
    p.querySelectorAll(selector).forEach(el => {
        el.addEventListener('dragstart', () => { drag = el; setTimeout(() => el.classList.add('sortable-ghost'), 0); });
        el.addEventListener('dragend', () => el.classList.remove('sortable-ghost'));
        el.addEventListener('drop', async () => {
            if (drag && el !== drag) {
                const ns = [...p.querySelectorAll(selector)];
                ns.indexOf(drag) < ns.indexOf(el) ? p.insertBefore(drag, el.nextSibling) : p.insertBefore(drag, el);
                const res = await apiPost({ action: type === 'cat' ? 'reorder_cats' : 'reorder_items', ids: [...p.querySelectorAll(selector)].map(x => x.dataset.id).join(',') });
                if (res.success) toast('✓ 顺序已更新');
            }
        });
        el.addEventListener('dragover', e => e.preventDefault());
    });
}

function onSearch(v) { clearTimeout(searchTmr); searchTmr = setTimeout(() => loadItems(v), 300); }

async function toggleStatus(id) { const res = await apiPost({ action: 'toggle_status', id }); if (res.success) loadItems(); }

async function updateInline(id, field, val) { const res = await apiPost({ action: 'edit', id, [field]: val }); if (res.success) toast('✓ 已保存'); }

function confirmDelCat(id, name) {
    showConfirm('删除分类', `确定要删除分类 "${name}" 吗？这将会同时删除该分类下的所有菜品！`, async () => {
        if ((await apiPost({ action: 'delete_category', id })).success) { toast('分类已删除'); loadCats(menuType); if (catId == id) { catId = null; switchType(menuType); } }
    });
}
function confirmDelItem(id, name) {
    showConfirm('删除菜品', `确定要从菜单中移除 "${name}" 吗？`, async () => {
        if ((await apiPost({ action: 'delete', id })).success) { toast('菜品已删除'); loadItems(); }
    });
}
function showConfirm(title, body, cb) {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-body').textContent = body;
    document.getElementById('confirm-bg').classList.add('show');
    document.getElementById('confirm-yes').onclick = () => { closeConfirm(); cb(); };
}
function closeConfirm() { document.getElementById('confirm-bg').classList.remove('show'); }

function onImgPick(inp) {
    const f = inp.files[0]; if (!f) return;
    imgFile = f;
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('img-zone-inner').style.display = 'none';
        const wrap = document.getElementById('img-preview-wrap');
        wrap.style.display = 'flex';
        wrap.querySelector('img').src = e.target.result;
        document.getElementById('img-preview-name').textContent = f.name;
    };
    r.readAsDataURL(f);
}
function clearImg(e) { if (e) e.stopPropagation(); imgFile = null; document.getElementById('f-img').value = ''; document.getElementById('img-preview-wrap').style.display = 'none'; document.getElementById('img-zone-inner').style.display = 'flex'; }
function onImgDrop(e) {
    e.preventDefault(); document.getElementById('img-zone').classList.remove('dragover');
    if (e.dataTransfer.files[0]) { const inp = document.getElementById('f-img'); const dt = new DataTransfer(); dt.items.add(e.dataTransfer.files[0]); inp.files = dt.files; onImgPick(inp); }
}
function toggleCatAdd() { const row = document.getElementById('cat-add-row'); row.classList.toggle('active'); if (row.classList.contains('active')) document.getElementById('new-cat-inp').focus(); }
async function doAddCat() {
    const inp = document.getElementById('new-cat-inp'); const name = inp.value.trim(); if (!name) return;
    const res = await apiPost({ action: 'add_category', type: menuType, category_name: name });
    if (res.success) { inp.value = ''; document.getElementById('cat-add-row').classList.remove('active'); await loadCats(menuType); const c = cats[menuType].find(x => x.id == res.data.id); if (c) selectCat(c.id, c.category_name); }
}
