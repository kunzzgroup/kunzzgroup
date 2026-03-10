/**
 * TOKYO JAPANESE — Menu Dashboard Premium JS
 * Handles dynamic side-panel, drag-and-drop persistence, and CRUD.
 */

// ── CONFIG ──
const API = 'menu_api.php';

// ── STATE ──
let menuType = 'grand';
let catId = null;
let cats = { grand: [], sushi: [] };
let items = [];
let searchTmr = null;
let imgFile = null;
let currentItemId = null; // Currently editing if not null

// ── BOOT ──
document.addEventListener('DOMContentLoaded', () => {
    loadCats('grand');
    loadCats('sushi');

    // Close modals on click outside
    document.getElementById('confirm-bg')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeConfirm();
    });
});

// ── UTILS ──
const esc = s => s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;') : '';

async function apiGet(p) {
    const qs = new URLSearchParams(p).toString();
    const r = await fetch(`${API}?${qs}`);
    return r.json();
}

async function apiPost(p, files = {}) {
    const fd = new FormData();
    Object.entries(p).forEach(([k, v]) => fd.append(k, (v === null || v === undefined) ? '' : v));
    Object.entries(files).forEach(([k, v]) => { if (v) fd.append(k, v); });
    const r = await fetch(API, { method: 'POST', body: fd });
    return r.json();
}

function toast(msg, type = 'success') {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    t.style.background = type === 'error' ? 'var(--red)' : 'var(--brown)';
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 3000);
}

// ── TYPE SWITCH ──
function switchType(t) {
    menuType = t;
    catId = null;
    document.getElementById('tab-grand').classList.toggle('active', t === 'grand');
    document.getElementById('tab-sushi').classList.toggle('active', t === 'sushi');
    document.getElementById('bc-tab').textContent = t === 'grand' ? 'Grand Menu' : 'Sushi Menu';
    document.getElementById('bc-cat').textContent = '—';
    renderCats(t);
    clearList();
    resetForm();
}

// ── CATEGORIES ──
async function loadCats(t) {
    const res = await apiGet({ action: 'get_categories', type: t });
    if (!res.success) return;
    cats[t] = res.data.categories;
    if (t === menuType) {
        renderCats(t);
        if (!catId && cats[t].length) {
            selectCat(cats[t][0].id, cats[t][0].category_name);
        }
    }
}

function renderCats(t) {
    const el = document.getElementById('cat-scroll');
    if (!el) return;
    const data = cats[t] || [];
    if (!data.length) {
        el.innerHTML = `<div style="text-align:center;padding:40px 20px;font-size:12px;color:var(--text-dim)">暂无分类</div>`;
        return;
    }
    el.innerHTML = data.map(c => `
        <div class="cat-item ${c.id == catId ? 'active' : ''}" data-id="${c.id}"
             draggable="true" onclick="selectCat(${c.id},'${esc(c.category_name)}')">
            <div class="cat-item-left">
                <span class="cat-drag-handle">⠿</span>
                <span class="cat-name-box">${esc(c.category_name)}</span>
            </div>
            <div class="cat-item-right">
                <span class="cat-badge">${c.item_count}</span>
                <div class="cat-actions">
                   <button class="btn-cat-act" onclick="event.stopPropagation();confirmDelCat(${c.id},'${esc(c.category_name)}')" title="删除">✕</button>
                </div>
            </div>
        </div>
    `).join('');
    initDrag('.cat-item', '#cat-scroll', 'cat');
}

function toggleCatAdd() {
    const row = document.getElementById('cat-add-row');
    row.classList.toggle('active');
    if (row.classList.contains('active')) document.getElementById('new-cat-inp').focus();
}

async function doAddCat() {
    const inp = document.getElementById('new-cat-inp');
    const name = inp.value.trim();
    if (!name) return;
    const res = await apiPost({ action: 'add_category', type: menuType, category_name: name });
    if (res.success) {
        inp.value = '';
        document.getElementById('cat-add-row').classList.remove('active');
        await loadCats(menuType);
        const newCat = cats[menuType].find(x => x.id == res.data.id);
        if (newCat) selectCat(newCat.id, newCat.category_name);
        toast('分类已新增');
    }
}

function selectCat(id, name) {
    catId = id;
    document.getElementById('bc-cat').textContent = name;
    document.getElementById('add-panel-sub').textContent = `当前分类：${name}`;
    document.querySelectorAll('.cat-item').forEach(el => el.classList.toggle('active', el.dataset.id == id));
    loadItems();
}

// ── ITEMS ──
async function loadItems(search = '') {
    if (!catId) return;
    const scroll = document.getElementById('list-scroll');
    scroll.innerHTML = `<div class="skeleton" style="height:100px;margin:24px;border-radius:14px"></div>`.repeat(5);

    const p = { action: 'get', type: menuType, category_id: catId };
    if (search) p.search = search;
    const res = await apiGet(p);
    if (!res.success) return;

    items = res.data.items;
    updateStats();

    if (!items.length) {
        scroll.innerHTML = `
            <div style="text-align:center;padding:100px 0;animation:fadeIn 1s ease">
                <div style="font-size:48px;margin-bottom:16px;opacity:0.2">🍽️</div>
                <div style="font-size:14px;color:var(--text-dim)">当前分类下暂无菜品</div>
            </div>`;
        return;
    }
    scroll.innerHTML = items.map(i => buildItemHTML(i)).join('');
    initDrag('.item-row', '#list-scroll', 'item');
}

function updateStats() {
    const pub = items.filter(i => i.status === 'published').length;
    document.getElementById('info-pub').textContent = `${pub} 已发布`;
    document.getElementById('info-dft').textContent = `${items.length - pub} 草稿`;
    document.getElementById('tb-stats').textContent = `${items.length} 项目`;
}

function buildItemHTML(i) {
    const isPub = i.status === 'published';
    return `
    <div class="item-row ${i.id == currentItemId ? 'active' : ''}" data-id="${i.id}" draggable="true" onclick="selectItem(${i.id})">
        <div class="item-thumb-box">
            ${i.image_url ? `<img src="${esc(i.image_url)}">` : `<span class="item-thumb-none">📸</span>`}
        </div>
        <div class="item-details">
            <span class="item-code-tag">${esc(i.item_code || 'UNTITLED')}</span>
            <div class="item-name-en">${esc(i.item_name)}</div>
            <div class="item-name-cn">${esc(i.item_name_cn || '—')}</div>
            <div class="item-desc-row">${esc(i.item_desc || '')}</div>
        </div>
        <div class="item-price">
            <div class="item-price-label">PRICE</div>
            <div class="item-price-val">RM ${parseFloat(i.price || 0).toFixed(2)}</div>
        </div>
        <div class="item-status">
            <div class="status-toggle ${isPub ? 'published' : 'draft'}" onclick="event.stopPropagation();toggleStatus(${i.id})">
                <span class="dot-${isPub ? 'green' : 'gray'}"></span>
                ${isPub ? '已发布' : '草稿'}
            </div>
        </div>
        <div class="item-actions">
            <button class="btn-act" onclick="event.stopPropagation();quickImg(${i.id})" title="快速传图">🖼️</button>
            <button class="btn-act btn-del" onclick="event.stopPropagation();confirmDelItem(${i.id},'${esc(i.item_name)}')" title="删除">🗑️</button>
        </div>
    </div>`;
}

// ── ITEM DETAIL / EDIT ──
async function selectItem(id) {
    currentItemId = id;
    document.querySelectorAll('.item-row').forEach(el => el.classList.toggle('active', el.dataset.id == id));

    // UI Feedback
    const panel = document.getElementById('detail-panel');
    const badge = document.getElementById('panel-mode-badge');
    const title = document.getElementById('panel-title');
    const btnDel = document.getElementById('btn-delete-item');
    const btnSub = document.getElementById('btn-submit-text');

    badge.textContent = '编辑模式';
    badge.style.background = 'var(--sky)';
    title.textContent = '编辑菜品详情';
    btnDel.style.display = 'block';
    btnSub.textContent = '保存修改';

    // Fetch full data
    const res = await apiGet({ action: 'get_item', id });
    if (!res.success) return;
    const i = res.data;

    document.getElementById('f-id').value = i.id;
    document.getElementById('f-code').value = i.item_code || '';
    document.getElementById('f-name').value = i.item_name || '';
    document.getElementById('f-cn').value = i.item_name_cn || '';
    document.getElementById('f-desc').value = i.item_desc || '';
    document.getElementById('f-price').value = i.price || '';
    document.getElementById('f-status').value = i.status;

    if (i.image_url) {
        showPreview(i.image_url, '已上传图片', '');
    } else {
        clearImgUI();
    }
}

function closePanel() {
    currentItemId = null;
    resetForm();
    document.querySelectorAll('.item-row').forEach(el => el.classList.remove('active'));
}

async function updateInline(id, field, val) {
    const res = await apiPost({ action: 'edit', id, [field]: val });
    if (res.success) toast('✓ 已自动保存');
}

async function toggleStatus(id) {
    const res = await apiPost({ action: 'toggle_status', id });
    if (res.success) {
        items = items.map(it => it.id == id ? { ...it, status: res.data.status } : it);
        loadItems();
        toast('状态已切换');
    }
}

function quickImg(id) {
    const f = document.createElement('input'); f.type = 'file'; f.accept = 'image/*';
    f.onchange = async () => {
        if (!f.files[0]) return;
        toast('⟳ 正在上传图片...');
        const res = await apiPost({ action: 'edit', id }, { image: f.files[0] });
        if (res.success) {
            loadItems();
            if (currentItemId == id) selectItem(id);
            toast('✓ 图片更新成功');
        }
    };
    f.click();
}

// ── DRAG & DROP ──
function initDrag(selector, parentId, type) {
    const p = document.querySelector(parentId);
    if (!p) return;
    let drag = null;
    p.querySelectorAll(selector).forEach(el => {
        el.addEventListener('dragstart', (e) => {
            drag = el;
            e.dataTransfer.setData('text/plain', el.dataset.id);
            setTimeout(() => el.classList.add('sortable-ghost'), 0);
        });
        el.addEventListener('dragend', () => el.classList.remove('sortable-ghost'));
        el.addEventListener('dragover', e => e.preventDefault());
        el.addEventListener('drop', async (e) => {
            e.preventDefault();
            if (drag && el !== drag) {
                const ns = [...p.querySelectorAll(selector)];
                if (ns.indexOf(drag) < ns.indexOf(el)) {
                    p.insertBefore(drag, el.nextSibling);
                } else {
                    p.insertBefore(drag, el);
                }

                // Persist to backend
                const newIds = [...p.querySelectorAll(selector)].map(x => x.dataset.id);
                const action = type === 'cat' ? 'reorder_cats' : 'reorder_items';
                const res = await apiPost({ action, ids: newIds.join(',') });
                if (res.success) toast('✓ 排序已同步至云端');
            }
        });
    });
}

// ── SEARCH ──
function onSearch(v) {
    clearTimeout(searchTmr);
    searchTmr = setTimeout(() => loadItems(v), 300);
}

// ── ADD/EDIT FORM ──
function onImgPick(inp) {
    const f = inp.files[0]; if (!f) return;
    imgFile = f;
    const r = new FileReader();
    r.onload = e => showPreview(e.target.result, f.name, (f.size / 1024).toFixed(0) + ' KB');
    r.readAsDataURL(f);
}

function showPreview(src, name, size) {
    document.getElementById('img-zone-inner').style.display = 'none';
    document.getElementById('img-preview-wrap').style.display = 'flex';
    document.getElementById('img-preview').src = src;
    document.getElementById('img-preview-name').textContent = name;
    document.getElementById('img-preview-size').textContent = size;
}

function clearImgUI() {
    imgFile = null;
    const inp = document.getElementById('f-img');
    if (inp) inp.value = '';
    document.getElementById('img-preview-wrap').style.display = 'none';
    document.getElementById('img-zone-inner').style.display = 'flex';
}

function clearImg(e) {
    if (e) e.stopPropagation();
    clearImgUI();
}

async function doSave() {
    const name = document.getElementById('f-name').value.trim();
    if (!name) { toast('请填写英文名称', 'error'); return; }
    if (!catId) { toast('请先选择一个分类', 'error'); return; }

    const id = document.getElementById('f-id').value;
    const isEdit = !!id;
    const txt = document.getElementById('btn-submit-text');
    const oldTxt = txt.textContent;

    txt.innerHTML = `<span style="opacity:0.6">正在保存...</span>`;

    const p = {
        action: isEdit ? 'edit' : 'add',
        id: id || undefined,
        type: menuType,
        category_id: catId,
        item_code: document.getElementById('f-code').value.trim(),
        item_name: name,
        item_name_cn: document.getElementById('f-cn').value.trim(),
        item_desc: document.getElementById('f-desc').value.trim(),
        price: document.getElementById('f-price').value.trim(),
        status: document.getElementById('f-status').value
    };

    try {
        const res = await apiPost(p, imgFile ? { image: imgFile } : {});
        txt.textContent = oldTxt;
        if (res.success) {
            toast(isEdit ? '✓ 已保存修改' : '✓ 已添加到菜单');
            if (!isEdit) resetForm();
            loadItems();
            if (isEdit) selectItem(id);
        } else {
            toast(res.message || '保存失败', 'error');
        }
    } catch (e) {
        txt.textContent = oldTxt;
        toast('网络错误，请稍后重试', 'error');
    }
}

function resetForm() {
    const ids = ['f-id', 'f-code', 'f-name', 'f-cn', 'f-desc', 'f-price'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    document.getElementById('f-status').value = 'published';

    // Reset Panel UI
    const badge = document.getElementById('panel-mode-badge');
    badge.textContent = '新增模式';
    badge.style.background = 'var(--gold)';
    document.getElementById('panel-title').textContent = '新增菜单项目';
    document.getElementById('btn-delete-item').style.display = 'none';
    document.getElementById('btn-submit-text').textContent = '＋ 添加到菜单';

    clearImgUI();
}

function onImgDrop(e) {
    e.preventDefault();
    document.getElementById('img-zone').classList.remove('dragover');
    if (e.dataTransfer.files[0]) {
        const inp = document.getElementById('f-img');
        const dt = new DataTransfer(); dt.items.add(e.dataTransfer.files[0]);
        inp.files = dt.files; onImgPick(inp);
    }
}

// ── DELETION ──
function confirmDelCat(id, name) {
    showConfirm('删除分类', `确定要删除分类 "${name}" 吗？这将会同时删除该分类下的所有菜品，且无法恢复！`, async () => {
        const res = await apiPost({ action: 'delete_category', id });
        if (res.success) {
            toast('分类及其项目已删除');
            loadCats(menuType);
            if (catId == id) { catId = null; clearList(); }
        }
    });
}

function confirmDelItem(id, name) {
    showConfirm('删除菜品', `确定要从菜单中移除 "${name}" 吗？`, async () => {
        const res = await apiPost({ action: 'delete', id });
        if (res.success) {
            toast('菜品已删除');
            loadItems();
            if (currentItemId == id) closePanel();
        }
    });
}

function confirmDelCurrent() {
    const id = document.getElementById('f-id').value;
    const name = document.getElementById('f-name').value;
    if (id) confirmDelItem(id, name);
}

function clearList() {
    document.getElementById('list-scroll').innerHTML = `<div style="text-align:center;padding:100px 0;opacity:0.2;font-size:32px">🍽️</div>`;
}

// ── CONFIRM MODAL ──
function showConfirm(title, body, cb) {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-body').textContent = body;
    document.getElementById('confirm-bg').classList.add('show');
    document.getElementById('confirm-yes').onclick = () => { closeConfirm(); cb(); };
}
function closeConfirm() { document.getElementById('confirm-bg').classList.remove('show'); }
