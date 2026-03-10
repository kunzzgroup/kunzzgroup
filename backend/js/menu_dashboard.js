/**
 * TOKYO JAPANESE — Menu Dashboard (HTML-in-PHP Version)
 * JS remains lean and only handles events/transport.
 */

// ── 1. CONFIG & STATE ──
const API = 'menu_api.php';
let menuType = 'grand', catId = null, catName = '', items = [], cats = { grand: [], sushi: [] }, imgFile = null, editId = null, searchTmr = null;

// ── 2. INITIALIZATION ──
document.addEventListener('DOMContentLoaded', () => {
    loadCats('grand');
    loadCats('sushi');
    document.getElementById('confirm-bg')?.addEventListener('click', e => e.target === e.currentTarget && closeConfirm());
});

// ── 3. CORE API ACTIONS ──
const apiGet = p => fetch(`${API}?${new URLSearchParams(p).toString()}`).then(r => r.json());
async function apiPost(p, f = {}) {
    const fd = new FormData(); Object.entries(p).forEach(([k, v]) => fd.append(k, v));
    Object.entries(f).forEach(([k, v]) => v && fd.append(k, v));
    return fetch(API, { method: 'POST', body: fd }).then(r => r.json());
}

async function loadCats(t) {
    if (t === menuType) {
        document.getElementById('cat-scroll').innerHTML = await fetch(`${API}?action=get_categories_html&type=${t}&active_id=${catId || 0}`).then(r => r.text());
        initDrag('.cat-item', '#cat-scroll', 'cat');
    }
    const res = await apiGet({ action: 'get_categories', type: t });
    if (res.success) {
        cats[t] = res.data.categories;
        if (t === menuType && !catId && cats[t].length) selectCat(cats[t][0].id, cats[t][0].category_name);
    }
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

// ── 4. UI LOGIC (Add / Edit / Delete) ──
async function doAdd() {
    const name = document.getElementById('f-name').value.trim();
    if (!name || !catId) return;
    const p = {
        action: editId ? 'edit' : 'add', id: editId, type: menuType, category_id: catId,
        item_code: document.getElementById('f-code').value, item_name: name,
        item_name_cn: document.getElementById('f-cn').value, item_desc: document.getElementById('f-desc').value,
        price: document.getElementById('f-price').value, status: document.getElementById('f-status').value
    };
    const res = await apiPost(p, imgFile ? { image: imgFile } : {});
    if (res.success) { cancelEdit(); loadItems(); toast(editId ? '✓ 已保存修改' : '✓ 已添加'); }
}

function openEditPanel(id) {
    const item = items.find(i => i.id == id); if (!item) return;
    editId = id;
    document.getElementById('add-panel-mode').style.display = 'block';
    document.getElementById('add-panel-title').textContent = '编辑菜单项目';
    document.getElementById('add-panel-sub').textContent = `正在编辑：${item.item_name}`;
    document.getElementById('btn-close-panel').style.display = 'flex';
    document.getElementById('btn-submit-text').textContent = '✓ 保存修改';
    ['code', 'name', 'cn', 'desc', 'price', 'status'].forEach(k => {
        const el = document.getElementById('f-' + k); if (el) el.value = item['item_' + k] || item[k] || '';
    });
    if (item.image_url) {
        document.getElementById('img-zone-inner').style.display = 'none';
        const wrap = document.getElementById('img-preview-wrap'); wrap.style.display = 'flex';
        wrap.querySelector('img').src = item.image_url;
    }
}

function cancelEdit() {
    editId = null; resetForm();
    document.getElementById('add-panel-mode').style.display = 'none';
    document.getElementById('add-panel-title').textContent = '＋ 新增菜单项目';
    document.getElementById('btn-submit-text').textContent = '＋ 添加到菜单';
    document.getElementById('add-panel-sub').textContent = `当前分类：${catName}`;
    document.getElementById('btn-close-panel').style.display = 'none';
}

// ── 5. HELPER FUNCTIONS ──
const syncStats = () => {
    const pub = items.filter(i => i.status === 'published').length;
    document.getElementById('info-pub').textContent = `${pub} 已发布`;
    document.getElementById('info-dft').textContent = `${items.length - pub} 草稿`;
    document.getElementById('tb-stats').textContent = `${items.length} 项目`;
};
const toast = (m, d = 2800) => { const t = document.getElementById('toast'); t.textContent = m; t.classList.add('show'); setTimeout(() => t.classList.remove('show'), d); };
const resetForm = () => { ['f-code', 'f-name', 'f-cn', 'f-desc', 'f-price'].forEach(id => document.getElementById(id).value = ''); clearImg(); };
function selectCat(id, name) { catId = id; catName = name; document.getElementById('bc-cat').textContent = name; document.getElementById('add-panel-sub').textContent = `当前分类：${name}`; document.querySelectorAll('.cat-item').forEach(el => el.classList.toggle('active', el.dataset.id == id)); loadItems(); }
function switchType(t) { menuType = t; catId = null;['tab-grand', 'tab-sushi'].forEach(id => document.getElementById(id).classList.toggle('active', id.includes(t))); loadCats(t); }

// (Dragging & Files - Simplified)
function initDrag(sel, pid, type) {
    const p = document.querySelector(pid); let drag = null;
    p.querySelectorAll(sel).forEach(el => {
        el.addEventListener('dragstart', () => { drag = el; setTimeout(() => el.classList.add('sortable-ghost'), 0); });
        el.addEventListener('dragend', () => el.classList.remove('sortable-ghost'));
        el.addEventListener('dragover', e => e.preventDefault());
        el.addEventListener('drop', async () => {
            if (drag && el !== drag) {
                const ns = [...p.querySelectorAll(sel)]; ns.indexOf(drag) < ns.indexOf(el) ? p.insertBefore(drag, el.nextSibling) : p.insertBefore(drag, el);
                const res = await apiPost({ action: type === 'cat' ? 'reorder_cats' : 'reorder_items', ids: [...p.querySelectorAll(sel)].map(x => x.dataset.id).join(',') });
                if (res.success) toast('✓ 顺序已更新');
            }
        });
    });
}
function onSearch(v) { clearTimeout(searchTmr); searchTmr = setTimeout(() => loadItems(v), 300); }
async function toggleStatus(id) { if ((await apiPost({ action: 'toggle_status', id })).success) loadItems(); }
async function updateInline(id, f, v) { if ((await apiPost({ action: 'edit', id, [f]: v })).success) toast('✓ 已保存'); }
function openConfirm(title, body, onYes) {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-body').textContent = body;
    const btn = document.getElementById('confirm-yes');
    btn.onclick = () => { onYes(); closeConfirm(); };
    document.getElementById('confirm-bg').classList.add('show');
}
function closeConfirm() { document.getElementById('confirm-bg').classList.remove('show'); }

function confirmDelCat(id, name) {
    openConfirm('确认删除分类？', `确定要删除 "${name}" 吗？该分类下的菜单项也将被永久移除。`, async () => {
        if ((await apiPost({ action: 'delete_category', id })).success) {
            toast('✓ 分类已删除');
            loadCats(menuType);
        }
    });
}
function confirmDelItem(id, name) {
    openConfirm('确认移除菜品？', `确定要移除 "${name}" 吗？`, async () => {
        if ((await apiPost({ action: 'delete', id })).success) {
            toast('✓ 菜品已移除');
            loadItems();
        }
    });
}

function openImgPick(inp) { const f = inp.files[0]; if (!f) return; imgFile = f; const r = new FileReader(); r.onload = e => { document.getElementById('img-zone-inner').style.display = 'none'; const w = document.getElementById('img-preview-wrap'); w.style.display = 'flex'; w.querySelector('img').src = e.target.result; }; r.readAsDataURL(f); }
function clearImg() { imgFile = null; document.getElementById('f-img').value = ''; document.getElementById('img-preview-wrap').style.display = 'none'; document.getElementById('img-zone-inner').style.display = 'flex'; }
function toggleCatAdd() { const r = document.getElementById('cat-add-row'); r.classList.toggle('active'); if (r.classList.contains('active')) document.getElementById('new-cat-inp').focus(); }
async function doAddCat() { const i = document.getElementById('new-cat-inp'); if (!i.value) return; if ((await apiPost({ action: 'add_category', type: menuType, category_name: i.value })).success) { i.value = ''; loadCats(menuType); } }

// ── 6. PHOTO VIEWER ──
function openPhoto(url) {
    if (!url) return;
    const v = document.getElementById('photo-viewer');
    const img = document.getElementById('pv-img');
    img.src = url;
    v.style.display = 'flex';
    setTimeout(() => v.classList.add('show'), 10);
}
function closePhoto() {
    const v = document.getElementById('photo-viewer');
    v.classList.remove('show');
    setTimeout(() => v.style.display = 'none', 300);
}
