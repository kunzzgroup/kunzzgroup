/**
 * TOKYO JAPANESE — Menu Dashboard Optimized JS
 * Synchronized with the 3-column layout CSS.
 */

// ── CONFIG ──
const API = 'menu_api.php';

// ── STATE ──
let menuType = 'grand';
let catId = null;
let catName = '';
let cats = { grand: [], sushi: [] };
let items = [];
let searchTmr = null;
let imgFile = null;

// ── BOOT ──
document.addEventListener('DOMContentLoaded', () => {
    loadCats('grand');
    loadCats('sushi');

    const confirmBg = document.getElementById('confirm-bg');
    if (confirmBg) {
        confirmBg.addEventListener('click', e => {
            if (e.target === e.currentTarget) closeConfirm();
        });
    }
});

// ── UTILS ──
const esc = s => s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;') : '';
const fmtBytes = b => b < 1024 * 1024 ? (b / 1024).toFixed(0) + 'KB' : (b / 1024 / 1024).toFixed(1) + 'MB';

async function apiGet(p) {
    const qs = new URLSearchParams(p).toString();
    return fetch(`${API}?${qs}`).then(r => r.json());
}

async function apiPost(p, files = {}) {
    const fd = new FormData();
    Object.entries(p).forEach(([k, v]) => fd.append(k, v));
    Object.entries(files).forEach(([k, v]) => { if (v) fd.append(k, v); });
    return fetch(API, { method: 'POST', body: fd }).then(r => r.json());
}

function toast(msg, ms = 2800) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), ms);
}

// ── TYPE SWITCH ──
function switchType(t) {
    menuType = t;
    catId = null;
    catName = '';
    document.getElementById('tab-grand').classList.toggle('active', t === 'grand');
    document.getElementById('tab-sushi').classList.toggle('active', t === 'sushi');
    document.getElementById('bc-tab').textContent = t === 'grand' ? 'Grand Menu' : 'Sushi Menu';
    document.getElementById('bc-cat').textContent = '—';
    document.getElementById('tb-stats').textContent = '0 项目';
    renderCats(t);
    clearList();
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
        el.innerHTML = `<div style="text-align:center;padding:24px 10px;font-size:12px;color:var(--text-4)">暂无分类</div>`;
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
        const c = cats[menuType].find(x => x.id == res.data.id);
        if (c) selectCat(c.id, c.category_name);
    }
}

function selectCat(id, name) {
    catId = id;
    document.getElementById('bc-cat').textContent = name;
    document.getElementById('add-panel-sub').textContent = `当前分类：${name}`;
    document.querySelectorAll('.cat-item').forEach(el => el.classList.toggle('active', el.dataset.id == id));
    loadItems();
}

function clearList() {
    document.getElementById('list-scroll').innerHTML = `<div style="text-align:center;padding:100px 0;opacity:0.3;font-size:32px">🍽️</div>`;
}

// ── ITEMS ──
async function loadItems(search = '') {
    if (!catId) return;
    const scroll = document.getElementById('list-scroll');
    scroll.innerHTML = `<div class="skeleton" style="height:200px;margin:24px;border-radius:12px"></div>`;

    const p = { action: 'get', type: menuType, category_id: catId };
    if (search) p.search = search;
    const res = await apiGet(p);
    if (!res.success) return;

    items = res.data.items;
    const pub = items.filter(i => i.status === 'published').length;
    document.getElementById('info-pub').textContent = `${pub} 已发布`;
    document.getElementById('info-dft').textContent = `${items.length - pub} 草稿`;
    document.getElementById('tb-stats').textContent = `${items.length} 项目`;

    if (!items.length) {
        scroll.innerHTML = `<div style="text-align:center;padding:100px 0;opacity:0.4;font-size:14px">暂无项目</div>`;
        return;
    }
    scroll.innerHTML = items.map(i => buildItemHTML(i)).join('');
    initDrag('.item-row', '#list-scroll', 'item');
}

function buildItemHTML(i) {
    const isPub = i.status === 'published';
    return `
    <div class="item-row" data-id="${i.id}" draggable="true">
        <div class="item-thumb-box">
            ${i.image_url ? `<img src="${esc(i.image_url)}">` : `<span class="item-thumb-none">📸</span>`}
        </div>
        <div class="item-details">
            <span class="item-code-tag">${esc(i.item_code || 'N/A')}</span>
            <input class="inline-input item-name-en" value="${esc(i.item_name)}" onblur="updateInline(${i.id},'item_name',this.value)">
            <input class="inline-input item-name-cn" value="${esc(i.item_name_cn)}" onblur="updateInline(${i.id},'item_name_cn',this.value)">
            <input class="inline-input item-desc-row" value="${esc(i.item_desc)}" onblur="updateInline(${i.id},'item_desc',this.value)">
        </div>
        <div class="item-price">
            <div style="font-size:10px;opacity:0.5;font-weight:700">PRICE</div>
            <input class="inline-input item-price-val" value="${esc(i.price)}" onblur="updateInline(${i.id},'price',this.value)">
        </div>
        <div class="item-status">
            <div class="status-toggle ${isPub ? 'published' : 'draft'}" onclick="toggleStatus(${i.id})">
                <span class="dot-${isPub ? 'green' : 'gray'}"></span>
                ${isPub ? '已发布' : '草稿'}
            </div>
        </div>
        <div class="item-actions">
            <button class="btn-act" onclick="quickImg(${i.id})">🖼️</button>
            <button class="btn-act btn-del" onclick="confirmDelItem(${i.id},'${esc(i.item_name)}')">🗑️</button>
        </div>
    </div>`;
}

async function updateInline(id, field, val) {
    const res = await apiPost({ action: 'edit', id, [field]: val });
    if (res.success) toast('✓ 已保存');
}

async function toggleStatus(id) {
    const res = await apiPost({ action: 'toggle_status', id });
    if (res.success) loadItems();
}

function quickImg(id) {
    const f = document.createElement('input'); f.type = 'file'; f.accept = 'image/*';
    f.onchange = async () => {
        if (!f.files[0]) return;
        toast('⟳ 上传中…');
        const res = await apiPost({ action: 'edit', id }, { image: f.files[0] });
        if (res.success) loadItems();
    };
    f.click();
}

// ── DRAG ENGINE ──
function initDrag(selector, parentId, type) {
    const p = document.querySelector(parentId);
    let drag = null;
    p.querySelectorAll(selector).forEach(el => {
        el.addEventListener('dragstart', () => { drag = el; setTimeout(() => el.classList.add('sortable-ghost'), 0); });
        el.addEventListener('dragend', () => el.classList.remove('sortable-ghost'));
        el.addEventListener('dragover', e => e.preventDefault());
        el.addEventListener('drop', async () => {
            if (drag && el !== drag) {
                const ns = [...p.querySelectorAll(selector)];
                ns.indexOf(drag) < ns.indexOf(el) ? p.insertBefore(drag, el.nextSibling) : p.insertBefore(drag, el);

                // Persist to backend
                const newIds = [...p.querySelectorAll(selector)].map(x => x.dataset.id);
                const action = type === 'cat' ? 'reorder_cats' : 'reorder_items';
                const res = await apiPost({ action, ids: newIds.join(',') });
                if (res.success) toast('✓ 顺序已更新');
            }
        });
    });
}

// ── SEARCH ──
function onSearch(v) {
    clearTimeout(searchTmr);
    searchTmr = setTimeout(() => loadItems(v), 300);
}

// ── ADD FORM ──
function onImgPick(inp) {
    const f = inp.files[0]; if (!f) return;
    imgFile = f;
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('img-zone-inner').style.display = 'none';
        document.getElementById('img-preview-wrap').style.display = 'flex';
        document.getElementById('img-preview').src = e.target.result;
        document.getElementById('img-preview-name').textContent = f.name;
    };
    r.readAsDataURL(f);
}

function clearImg(e) {
    if (e) e.stopPropagation();
    imgFile = null;
    document.getElementById('f-img').value = '';
    document.getElementById('img-preview-wrap').style.display = 'none';
    document.getElementById('img-zone-inner').style.display = 'flex';
}

async function doAdd() {
    const name = document.getElementById('f-name').value.trim();
    if (!name || !catId) return;
    const txt = document.getElementById('btn-submit-text');
    txt.innerHTML = `保存中...`;
    const p = {
        action: 'add', type: menuType, category_id: catId,
        item_code: document.getElementById('f-code').value.trim(),
        item_name: name,
        item_name_cn: document.getElementById('f-cn').value.trim(),
        item_desc: document.getElementById('f-desc').value.trim(),
        price: document.getElementById('f-price').value.trim(),
        status: document.getElementById('f-status').value
    };
    const res = await apiPost(p, imgFile ? { image: imgFile } : {});
    txt.textContent = '＋ 添加到菜单';
    if (res.success) { resetForm(); loadItems(); toast('✓ 已添加'); }
}

function resetForm() {
    ['f-code', 'f-name', 'f-cn', 'f-desc', 'f-price'].forEach(id => document.getElementById(id).value = '');
    clearImg();
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
        }
    });
}

// ── CONFIRM ──
function showConfirm(title, body, cb) {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-body').textContent = body;
    document.getElementById('confirm-bg').classList.add('show');
    document.getElementById('confirm-yes').onclick = () => { closeConfirm(); cb(); };
}
function closeConfirm() { document.getElementById('confirm-bg').classList.remove('show'); }
