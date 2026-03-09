/**
 * TOKYO JAPANESE — Menu Dashboard Optimized JS
 */

// ── CONFIG ──
const API = 'menu_api.php';

// ── STATE ──
let menuType = 'grand';
let catId = null;
let catName = '';
let cats = { grand: [], sushi: [] };
let items = [];
let editOpen = null;  // item id currently being edited
let searchTmr = null;
let imgFile = null;

// ── BOOT ──
document.addEventListener('DOMContentLoaded', () => {
    loadCats('grand');
    loadCats('sushi');

    // Close confirm modal on backdrop click
    const confirmBg = document.getElementById('confirm-bg');
    if (confirmBg) {
        confirmBg.addEventListener('click', e => {
            if (e.target === e.currentTarget) closeConfirm();
        });
    }
});

// ── UTILS ──
const esc = s => s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;') : '';
const fmtDate = s => s ? s.slice(0, 10) : '—';
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

    const bcTab = document.getElementById('bc-tab');
    if (bcTab) bcTab.textContent = t === 'grand' ? 'Grand Menu' : 'Sushi Menu';

    const bcCat = document.getElementById('bc-cat');
    if (bcCat) bcCat.textContent = '—';

    const stats = document.getElementById('tb-stats');
    if (stats) stats.textContent = '0 项目';

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
        el.innerHTML = `<div style="text-align:center;padding:20px 10px;font-size:12px;color:var(--text-4)">暂无分类<br>点击 + 新增</div>`;
        return;
    }
    el.innerHTML = data.map(c => `
    <div class="cat-item ${c.id == catId ? 'active' : ''}" data-id="${c.id}"
         draggable="true" onclick="selectCat(${c.id},'${esc(c.category_name)}')">
      <span class="cat-drag" title="拖拽排序">⠿</span>
      <span class="cat-label">${esc(c.category_name)}</span>
      <span class="cat-n">${c.item_count}</span>
      <button class="cat-rm" onclick="event.stopPropagation();confirmDelCat(${c.id},'${esc(c.category_name)}')" title="删除">✕</button>
    </div>
  `).join('');
    initCatDrag();
}

function toggleCatAdd() {
    const row = document.getElementById('cat-add-row');
    if (!row) return;
    row.classList.toggle('open');
    if (row.classList.contains('open')) document.getElementById('new-cat-inp').focus();
    else document.getElementById('new-cat-inp').value = '';
}

async function doAddCat() {
    const inp = document.getElementById('new-cat-inp');
    const name = inp.value.trim();
    if (!name) { toast('⚠ 请输入分类名称'); return; }
    const res = await apiPost({ action: 'add_category', type: menuType, category_name: name });
    if (res.success) {
        toast(`✓ 分类「${name}」已新增`);
        inp.value = '';
        document.getElementById('cat-add-row').classList.remove('open');
        await loadCats(menuType);
        const c = cats[menuType].find(x => x.id == res.data.id);
        if (c) selectCat(c.id, c.category_name);
    } else { toast('✕ ' + res.message); }
}

function confirmDelCat(id, name) {
    showConfirm(`删除分类「${name}」`, `该分类下所有菜单项目及图片将被一并删除，无法恢复。`, async () => {
        const res = await apiPost({ action: 'delete_category', id });
        if (res.success) {
            toast('✓ 分类已删除');
            if (catId == id) { catId = null; clearList(); }
            await loadCats(menuType);
        } else { toast('✕ ' + res.message); }
    });
}

// Category drag-to-reorder
function initCatDrag() {
    let drag = null;
    document.querySelectorAll('.cat-item').forEach(el => {
        el.addEventListener('dragstart', e => { drag = el; setTimeout(() => el.classList.add('dragging'), 0); e.dataTransfer.effectAllowed = 'move'; });
        el.addEventListener('dragend', () => { el.classList.remove('dragging'); document.querySelectorAll('.cat-item').forEach(i => i.classList.remove('over')); });
        el.addEventListener('dragover', e => { e.preventDefault(); if (el !== drag) { document.querySelectorAll('.cat-item').forEach(i => i.classList.remove('over')); el.classList.add('over'); } });
        el.addEventListener('drop', e => {
            e.preventDefault();
            if (drag && el !== drag) {
                const p = drag.parentNode, ns = [...p.children], di = ns.indexOf(drag), ti = ns.indexOf(el);
                di < ti ? p.insertBefore(drag, el.nextSibling) : p.insertBefore(drag, el);
                // Sync cats array order
                const ids = [...p.querySelectorAll('.cat-item')].map(x => parseInt(x.dataset.id));
                cats[menuType] = ids.map(id => cats[menuType].find(c => c.id == id)).filter(Boolean);
                toast('✓ 分类顺序已更新');
            }
        });
    });
}

// ── SELECT CATEGORY ──
function selectCat(id, name) {
    catId = id;
    catName = name;

    const bcCat = document.getElementById('bc-cat');
    if (bcCat) bcCat.textContent = name;

    const addSub = document.getElementById('add-panel-sub');
    if (addSub) addSub.textContent = `当前：${name}`;

    document.querySelectorAll('.cat-item').forEach(el => el.classList.toggle('active', el.dataset.id == id));
    loadItems();
}

function clearList() {
    const scroll = document.getElementById('list-scroll');
    if (scroll) scroll.innerHTML = `<div class="empty-state"><div class="ei">🍽️</div><p>请选择左侧分类</p></div>`;

    const pub = document.getElementById('info-pub');
    if (pub) pub.textContent = '0 已发布';

    const dft = document.getElementById('info-dft');
    if (dft) dft.textContent = '0 草稿';

    const stats = document.getElementById('tb-stats');
    if (stats) stats.textContent = '0 项目';
}

// ── LOAD ITEMS ──
async function loadItems(search = '') {
    if (!catId) return;
    const scroll = document.getElementById('list-scroll');
    if (!scroll) return;
    scroll.innerHTML = [1, 2, 3, 4].map(() => `<div class="skel" style="height:56px"></div>`).join('');

    const p = { action: 'get', type: menuType, category_id: catId };
    if (search) p.search = search;
    const res = await apiGet(p);
    if (!res.success) { toast('✕ 加载失败'); return; }

    items = res.data.items;
    const pubCount = items.filter(i => i.status === 'published').length;
    const dftCount = items.length - pubCount;

    const pubEl = document.getElementById('info-pub');
    if (pubEl) pubEl.textContent = `${pubCount} 已发布`;

    const dftEl = document.getElementById('info-dft');
    if (dftEl) dftEl.textContent = `${dftCount} 草稿`;

    const statsEl = document.getElementById('tb-stats');
    if (statsEl) statsEl.textContent = `${items.length} 项目`;

    // Update cat count in sidebar
    const catEl = document.querySelector(`.cat-item[data-id="${catId}"] .cat-n`);
    if (catEl) catEl.textContent = items.length;

    if (!items.length) {
        scroll.innerHTML = `<div class="empty-state"><div class="ei">🍽️</div><p>暂无项目<br><small style="opacity:0.6">在右侧面板新增</small></p></div>`;
        return;
    }

    scroll.innerHTML = items.map(item => buildItemHTML(item)).join('');
    initItemDrag();
}

function buildItemHTML(item) {
    return `
  <div class="item-wrap" data-id="${item.id}">
    <div class="item-row ${editOpen == item.id ? 'editing' : ''}" id="row-${item.id}" draggable="true" data-id="${item.id}">

      <!-- Thumb — click to quick-upload image -->
      <div class="thumb-cell">
        ${item.image_url
            ? `<img class="item-thumb" src="${esc(item.image_url)}" onerror="this.parentNode.innerHTML=thumbPh(${item.id})">`
            : thumbPh(item.id)}
      </div>

      <!-- Info -->
      <div class="info-cell">
        <div class="item-en">
          ${item.item_code ? `<span class="item-tag">${esc(item.item_code)}</span>` : ''}
          ${esc(item.item_name)}
        </div>
        <div class="item-sub">
          ${item.item_name_cn ? esc(item.item_name_cn) : ''}
          ${item.item_name_cn && item.item_desc ? ' · ' : ''}
          ${item.item_desc ? esc(item.item_desc) : ''}
        </div>
      </div>

      <!-- Price -->
      <div class="price-cell">${item.price_formatted || '—'}</div>

      <!-- Status toggle -->
      <div class="status-cell">
        <button class="status-btn ${item.status === 'published' ? 's-pub' : 's-draft'}" onclick="toggleStatus(${item.id})">
          <span class="sdot"></span>
          ${item.status === 'published' ? '已发布' : '草稿'}
        </button>
      </div>

      <!-- Actions -->
      <div class="act-cell">
        <button class="abtn edit" onclick="toggleEdit(${item.id})">编辑</button>
        <button class="abtn del"  onclick="confirmDelItem(${item.id},'${esc(item.item_name)}')">删除</button>
      </div>
    </div>

    <!-- Inline edit panel -->
    <div class="edit-panel ${editOpen == item.id ? 'open' : ''}" id="ep-${item.id}">
      <div class="ep-grid">
        <div><div class="ep-label">编号</div><input class="ep-input" id="ep-code-${item.id}"  value="${esc(item.item_code || '')}"></div>
        <div><div class="ep-label">英文名称</div><input class="ep-input" id="ep-name-${item.id}"  value="${esc(item.item_name || '')}"></div>
        <div><div class="ep-label">中文名称</div><input class="ep-input" id="ep-cn-${item.id}"    value="${esc(item.item_name_cn || '')}"></div>
        <div><div class="ep-label">描述</div><input class="ep-input" id="ep-desc-${item.id}" value="${esc(item.item_desc || '')}"></div>
        <div><div class="ep-label">价格</div><input class="ep-input" id="ep-price-${item.id}" value="${esc(item.price || '')}"></div>
      </div>
      <div class="ep-grid2">
        <div>
          <div class="ep-label">图片</div>
          <label class="ep-img-zone" id="ep-iz-${item.id}">
            <input type="file" accept="image/*" onchange="onEpImg(${item.id},this)">
            <img class="ep-img-preview" id="ep-prev-${item.id}" ${item.image_url ? `src="${esc(item.image_url)}" style="display:block"` : ''}>
            <span class="ep-img-text" id="ep-it-${item.id}">${item.image_url ? '点击换图' : '点击上传'}</span>
          </label>
        </div>
        <div>
          <div class="ep-label">状态</div>
          <select class="ep-input ep-select" id="ep-status-${item.id}">
            <option value="published" ${item.status === 'published' ? 'selected' : ''}>✓ 已发布</option>
            <option value="draft"     ${item.status === 'draft' ? 'selected' : ''}>◷ 草稿</option>
          </select>
        </div>
      </div>
      <div class="ep-actions">
        <button class="btn-save" id="ep-save-${item.id}" onclick="doEdit(${item.id})">保存修改</button>
        <button class="btn-discard" onclick="toggleEdit(${item.id})">取消</button>
        <span style="font-size:11px;color:var(--text-4);margin-left:auto">${fmtDate(item.updated_at)}</span>
      </div>
    </div>
  </div>`;
}

function thumbPh(id) {
    return `<label class="thumb-ph" title="点击上传图片">
    <input type="file" accept="image/*" onchange="quickUploadImg(${id},this)">
    <span>🍽</span>
    <span class="upload-hint">上传</span>
  </label>`;
}

// ── SEARCH ──
function onSearch(v) {
    clearTimeout(searchTmr);
    searchTmr = setTimeout(() => loadItems(v), 350);
}

// ── TOGGLE EDIT ──
function toggleEdit(id) {
    if (editOpen === id) {
        document.getElementById(`ep-${id}`)?.classList.remove('open');
        document.getElementById(`row-${id}`)?.classList.remove('editing');
        editOpen = null;
        return;
    }
    // Close previous
    if (editOpen) {
        document.getElementById(`ep-${editOpen}`)?.classList.remove('open');
        document.getElementById(`row-${editOpen}`)?.classList.remove('editing');
    }
    editOpen = id;
    const ep = document.getElementById(`ep-${id}`);
    const row = document.getElementById(`row-${id}`);
    if (ep) ep.classList.add('open');
    if (row) row.classList.add('editing');

    const nameInp = document.getElementById(`ep-name-${id}`);
    if (nameInp) nameInp.focus();

    // Scroll into view
    setTimeout(() => ep?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 80);
}

// ── EDIT IMG PREVIEW ──
function onEpImg(id, inp) {
    const f = inp.files[0]; if (!f) return;
    const prev = document.getElementById(`ep-prev-${id}`);
    const text = document.getElementById(`ep-it-${id}`);
    const zone = document.getElementById(`ep-iz-${id}`);
    const r = new FileReader();
    r.onload = e => {
        if (prev) { prev.src = e.target.result; prev.style.display = 'block'; }
        if (text) text.textContent = '✓ ' + f.name.slice(0, 16);
        if (zone) zone.classList.add('has-file');
    };
    r.readAsDataURL(f);
}

// ── SAVE EDIT ──
async function doEdit(id) {
    const nameInp = document.getElementById(`ep-name-${id}`);
    const name = nameInp ? nameInp.value.trim() : '';
    if (!name) { toast('⚠ 英文名称不能为空'); return; }

    const btn = document.getElementById(`ep-save-${id}`);
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spin"></span>'; }

    const p = {
        action: 'edit', id,
        item_code: document.getElementById(`ep-code-${id}`).value.trim(),
        item_name: name,
        item_name_cn: document.getElementById(`ep-cn-${id}`).value.trim(),
        item_desc: document.getElementById(`ep-desc-${id}`).value.trim(),
        price: document.getElementById(`ep-price-${id}`).value.trim(),
        status: document.getElementById(`ep-status-${id}`).value,
    };

    const imgInp = document.querySelector(`#ep-${id} input[type=file]`);
    const res = await apiPost(p, (imgInp && imgInp.files[0]) ? { image: imgInp.files[0] } : {});

    if (btn) { btn.disabled = false; btn.innerHTML = '保存修改'; }

    if (res.success) {
        toast('✓ 修改已保存');
        editOpen = null;
        const searchInp = document.getElementById('search-inp');
        loadItems(searchInp ? searchInp.value : '');
    } else { toast('✕ ' + res.message); }
}

// ── TOGGLE STATUS ──
async function toggleStatus(id) {
    const res = await apiPost({ action: 'toggle_status', id });
    if (res.success) {
        const lbl = res.data.status === 'published' ? '✓ 已发布' : '◷ 草稿';
        toast(lbl);
        const searchInp = document.getElementById('search-inp');
        loadItems(searchInp ? searchInp.value : '');
    } else { toast('✕ ' + res.message); }
}

// ── QUICK UPLOAD (click thumb) ──
async function quickUploadImg(id, inp) {
    const f = inp.files[0]; if (!f) return;
    toast('⟳ 上传中…');
    const res = await apiPost({ action: 'edit', id }, { image: f });
    if (res.success) {
        toast('✓ 图片已更新');
        const searchInp = document.getElementById('search-inp');
        loadItems(searchInp ? searchInp.value : '');
    } else { toast('✕ ' + res.message); }
}

// ── DELETE ITEM ──
function confirmDelItem(id, name) {
    showConfirm(`删除「${name}」`, '此操作无法恢复，图片也会一并删除。', async () => {
        const res = await apiPost({ action: 'delete', id });
        if (res.success) {
            toast('✓ 已删除');
            await loadCats(menuType);
            const searchInp = document.getElementById('search-inp');
            await loadItems(searchInp ? searchInp.value : '');
        } else { toast('✕ ' + res.message); }
    });
}

// ── ITEM DRAG-REORDER ──
function initItemDrag() {
    const scroll = document.getElementById('list-scroll');
    if (!scroll) return;
    let drag = null;
    scroll.querySelectorAll('.item-row').forEach(el => {
        el.addEventListener('dragstart', e => { drag = el; setTimeout(() => el.classList.add('dragging'), 0); e.dataTransfer.effectAllowed = 'move'; });
        el.addEventListener('dragend', () => { el.classList.remove('dragging'); scroll.querySelectorAll('.item-row').forEach(i => i.classList.remove('over')); });
        el.addEventListener('dragover', e => { e.preventDefault(); if (el !== drag) { scroll.querySelectorAll('.item-row').forEach(i => i.classList.remove('over')); el.classList.add('over'); } });
        el.addEventListener('drop', e => {
            e.preventDefault();
            if (!drag || el === drag) return;
            const dWrap = drag.closest('.item-wrap');
            const tWrap = el.closest('.item-wrap');
            if (!dWrap || !tWrap) return;
            const p = dWrap.parentNode;
            const ns = [...p.children];
            if (ns.indexOf(dWrap) < ns.indexOf(tWrap)) p.insertBefore(dWrap, tWrap.nextSibling);
            else p.insertBefore(dWrap, tWrap);
            toast('✓ 顺序已调整');
        });
    });
}

// ── ADD FORM ──
function onImgPick(inp) {
    const f = inp.files[0]; if (!f) return;
    imgFile = f;
    showImgPreview(f);
}

function onImgDrop(e) {
    e.preventDefault();
    const zone = document.getElementById('img-zone');
    if (zone) zone.classList.remove('dragover');
    const f = e.dataTransfer.files[0];
    if (f && f.type.startsWith('image/')) {
        imgFile = f;
        showImgPreview(f);
    }
}

function showImgPreview(f) {
    const r = new FileReader();
    r.onload = e => {
        const inner = document.getElementById('img-zone-inner');
        if (inner) inner.style.display = 'none';

        const wrap = document.getElementById('img-preview-wrap');
        if (wrap) wrap.classList.add('show');

        const prev = document.getElementById('img-preview');
        if (prev) prev.src = e.target.result;

        const name = document.getElementById('img-preview-name');
        if (name) name.textContent = '✓ ' + f.name;

        const size = document.getElementById('img-preview-size');
        if (size) size.textContent = fmtBytes(f.size);
    };
    r.readAsDataURL(f);
}

function clearImg(e) {
    if (e && e.stopPropagation) e.stopPropagation();
    imgFile = null;
    const inp = document.getElementById('f-img');
    if (inp) inp.value = '';

    const wrap = document.getElementById('img-preview-wrap');
    if (wrap) wrap.classList.remove('show');

    const inner = document.getElementById('img-zone-inner');
    if (inner) inner.style.display = '';
}

async function doAdd() {
    const nameInp = document.getElementById('f-name');
    const name = nameInp ? nameInp.value.trim() : '';
    if (!name) { toast('⚠ 请填写英文名称'); nameInp?.focus(); return; }
    if (!catId) { toast('⚠ 请先选择左侧分类'); return; }

    const btn = document.getElementById('btn-submit');
    const txt = document.getElementById('btn-submit-text');
    if (btn) btn.disabled = true;
    if (txt) txt.innerHTML = '<span class="spin"></span> 保存中…';

    const p = {
        action: 'add', type: menuType, category_id: catId,
        item_code: document.getElementById('f-code').value.trim(),
        item_name: name,
        item_name_cn: document.getElementById('f-cn').value.trim(),
        item_desc: document.getElementById('f-desc').value.trim(),
        price: document.getElementById('f-price').value.trim(),
        status: document.getElementById('f-status').value,
    };

    const res = await apiPost(p, imgFile ? { image: imgFile } : {});

    if (btn) btn.disabled = false;
    if (txt) txt.textContent = '＋ 添加到菜单';

    if (res.success) {
        toast(`✓ 「${name}」已添加`);
        resetForm();
        await loadCats(menuType);
        const searchInp = document.getElementById('search-inp');
        loadItems(searchInp ? searchInp.value : '');
    } else { toast('✕ ' + res.message); }
}

function resetForm() {
    ['f-code', 'f-name', 'f-cn', 'f-desc', 'f-price'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const status = document.getElementById('f-status');
    if (status) status.value = 'published';
    clearImg();
    imgFile = null;
}

// ── CONFIRM ──
function showConfirm(title, body, cb) {
    const t = document.getElementById('confirm-title');
    const b = document.getElementById('confirm-body');
    const bg = document.getElementById('confirm-bg');
    const yes = document.getElementById('confirm-yes');

    if (t) t.textContent = title;
    if (b) b.textContent = body;
    if (bg) bg.classList.add('show');
    if (yes) yes.onclick = () => { closeConfirm(); cb(); };
}

function closeConfirm() {
    const bg = document.getElementById('confirm-bg');
    if (bg) bg.classList.remove('show');
}
