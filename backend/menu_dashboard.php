<?php
// ============================================================
//  TOKYO JAPANESE CUISINE — Menu Management Dashboard
//  Fully wired to menu_api.php for real CRUD operations
// ============================================================
session_start();

// Simple auth guard — adapt to your existing auth system
// if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tokyo Japanese – 菜单管理</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&family=Cinzel:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

:root {
  --brown:    #6b4c2a;
  --brown-dk: #3e2a14;
  --brown-lt: #c8a97a;
  --gold:     #c9a84c;
  --gold-lt:  #e8c97a;
  --cream:    #f7f0e6;
  --white:    #ffffff;
  --page-bg:  #f2ebe0;
  --text:     #2c1a0e;
  --muted:    #9a7f65;
  --border:   #ddd0bc;
  --red:      #c0392b;
  --green:    #27ae60;
  --sidebar-w: 250px;
}

body { font-family:'Noto Sans SC',sans-serif; background:var(--page-bg); color:var(--text); display:flex; min-height:100vh; }

/* ══ SIDEBAR ══ */
.sidebar { width:var(--sidebar-w); min-height:100vh; background:var(--brown-dk); position:fixed; top:0; left:0; display:flex; flex-direction:column; z-index:200; box-shadow:4px 0 20px rgba(0,0,0,0.4); }
.sidebar-logo { padding:22px 20px; border-bottom:1px solid rgba(201,168,76,0.25); display:flex; align-items:center; gap:12px; background:rgba(0,0,0,0.25); }
.logo-circle { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--gold),var(--brown)); display:flex; align-items:center; justify-content:center; font-size:20px; box-shadow:0 2px 8px rgba(201,168,76,0.4); flex-shrink:0; }
.logo-text .t1 { font-family:'Cinzel',serif; color:var(--gold-lt); font-size:12px; letter-spacing:1px; }
.logo-text .t2 { color:rgba(255,255,255,0.5); font-size:10px; }
.sb-section { padding:16px 16px 4px; font-size:9.5px; letter-spacing:2px; color:rgba(201,168,76,0.5); text-transform:uppercase; font-weight:600; }
.sidebar nav ul { list-style:none; padding:0 10px; }
.sidebar nav ul li a { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:7px; color:rgba(255,255,255,0.6); text-decoration:none; font-size:13px; transition:all 0.2s; margin-bottom:1px; }
.sidebar nav ul li a:hover { background:rgba(255,255,255,0.07); color:#fff; }
.sidebar nav ul li a.active { background:var(--gold); color:var(--brown-dk); font-weight:600; }
.sidebar nav ul li a .ico { font-size:15px; width:18px; text-align:center; flex-shrink:0; }
.has-sub > a { justify-content:space-between; }
.has-sub > a .left { display:flex; align-items:center; gap:10px; }
.has-sub > a .arr { font-size:10px; transition:transform 0.25s; opacity:0.5; }
.has-sub.open > a .arr { transform:rotate(90deg); opacity:1; }
.sub-ul { display:none; padding:3px 0 3px 30px; }
.has-sub.open .sub-ul { display:block; }
.sub-ul li a { font-size:12.5px !important; color:rgba(255,255,255,0.5) !important; padding:7px 10px !important; border-left:2px solid rgba(255,255,255,0.08) !important; border-radius:0 6px 6px 0 !important; }
.sub-ul li a:hover { border-left-color:var(--gold) !important; color:#fff !important; background:rgba(201,168,76,0.12) !important; }
.sub-ul li a.active { border-left-color:var(--gold) !important; color:var(--gold-lt) !important; background:rgba(201,168,76,0.15) !important; }
.sidebar-footer { margin-top:auto; padding:16px; border-top:1px solid rgba(255,255,255,0.06); }
.sidebar-footer a { display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.4); font-size:12.5px; text-decoration:none; padding:8px 12px; border-radius:7px; transition:all 0.2s; }
.sidebar-footer a:hover { background:rgba(255,255,255,0.06); color:#fff; }

/* ══ MAIN ══ */
.main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
.topbar { background:var(--white); height:58px; display:flex; align-items:center; justify-content:space-between; padding:0 28px; border-bottom:1px solid var(--border); box-shadow:0 1px 6px rgba(0,0,0,0.06); position:sticky; top:0; z-index:100; }
.topbar-left { display:flex; align-items:center; gap:10px; }
.topbar-left h2 { font-size:16px; font-weight:700; color:var(--brown-dk); }
.breadcrumb { font-size:12px; color:var(--muted); }
.breadcrumb span { color:var(--gold); font-weight:500; }
.topbar-right { display:flex; align-items:center; gap:14px; }
.topbar-right .avatar { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--gold),var(--brown)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:700; cursor:pointer; }

.page { padding:26px 28px; }

/* ══ TABS ══ */
.menu-tabs { display:flex; gap:14px; margin-bottom:24px; }
.menu-tab { display:flex; align-items:center; gap:10px; padding:10px 20px 10px 14px; border-radius:10px; cursor:pointer; border:2px solid var(--border); background:var(--white); color:var(--muted); font-size:13.5px; font-weight:500; font-family:inherit; transition:all 0.2s; }
.menu-tab .tab-icon { width:36px; height:36px; border-radius:7px; background:var(--cream); display:flex; align-items:center; justify-content:center; font-size:20px; }
.menu-tab:hover { border-color:var(--gold); }
.menu-tab.active { border-color:var(--gold); background:#fffbf2; color:var(--brown-dk); }
.menu-tab.active .tab-icon { background:var(--gold); }
.tab-label .t1 { font-size:14px; font-weight:700; color:var(--brown-dk); }
.tab-label .t2 { font-size:11px; color:var(--muted); font-weight:400; }

/* ══ CONTENT GRID ══ */
.content-grid { display:grid; grid-template-columns:210px 1fr; gap:20px; align-items:start; }

/* ══ CATEGORY PANEL ══ */
.cat-panel { background:var(--white); border-radius:12px; border:1px solid var(--border); box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }
.cat-panel-header { padding:14px 16px; background:var(--brown-dk); display:flex; align-items:center; justify-content:space-between; }
.cat-panel-header span { color:var(--gold-lt); font-size:12.5px; font-weight:600; }
.btn-add-cat { background:var(--gold); color:var(--brown-dk); border:none; cursor:pointer; width:24px; height:24px; border-radius:5px; font-size:16px; font-weight:700; display:flex; align-items:center; justify-content:center; font-family:inherit; transition:background 0.2s; }
.btn-add-cat:hover { background:var(--gold-lt); }
.cat-list { padding:8px; }
.cat-item { display:flex; align-items:center; justify-content:space-between; padding:9px 10px; border-radius:7px; cursor:pointer; font-size:13px; color:var(--text); transition:all 0.15s; margin-bottom:2px; }
.cat-item:hover { background:var(--cream); }
.cat-item.active { background:var(--gold); color:var(--brown-dk); font-weight:600; }
.cat-item .cat-left { display:flex; align-items:center; gap:6px; }
.cat-item .cat-count { font-size:11px; background:rgba(0,0,0,0.08); padding:1px 7px; border-radius:10px; }
.cat-item.active .cat-count { background:rgba(0,0,0,0.15); }
.btn-del-cat { background:none; border:none; cursor:pointer; color:rgba(0,0,0,0.25); font-size:12px; padding:2px 4px; border-radius:3px; transition:all 0.15s; display:none; }
.cat-item:hover .btn-del-cat { display:inline-block; color:#c0392b; }
.cat-item.active .btn-del-cat { display:inline-block; color:rgba(0,0,0,0.3); }

/* ══ RIGHT PANEL ══ */
.right-panel { display:flex; flex-direction:column; gap:20px; }
.upload-card { background:var(--white); border-radius:12px; border:1px solid var(--border); box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }
.card-header { padding:14px 20px; background:linear-gradient(135deg,var(--brown-dk),var(--brown)); display:flex; align-items:center; justify-content:space-between; }
.card-header h3 { color:var(--gold-lt); font-size:13.5px; font-weight:600; letter-spacing:0.5px; }
.card-header .subtitle { color:rgba(255,255,255,0.45); font-size:11px; margin-top:2px; }
.card-body { padding:20px; }
.form-row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:14px; }
.form-row-2 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:14px; }
.fg label { display:block; font-size:11.5px; font-weight:600; color:var(--brown); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; }
.fg input, .fg select { width:100%; padding:9px 12px; border:1.5px solid var(--border); border-radius:7px; font-size:13.5px; font-family:inherit; color:var(--text); background:#fdfaf5; outline:none; transition:border 0.2s; }
.fg input:focus, .fg select:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(201,168,76,0.12); }
.fg select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%239a7f65'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; }
.upload-zone { border:2px dashed var(--border); border-radius:10px; padding:24px; text-align:center; cursor:pointer; background:#fdfaf5; transition:all 0.2s; position:relative; margin-bottom:16px; }
.upload-zone:hover { border-color:var(--gold); background:#fffbf0; }
.upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; }
.upload-zone .uz-icon { font-size:28px; display:block; margin-bottom:8px; }
.upload-zone p { font-size:13px; color:var(--muted); }
.upload-zone p strong { color:var(--gold); }
.upload-zone .hint { font-size:11px; color:#bbb; margin-top:4px; }
#preview-wrap { display:none; margin-bottom:14px; text-align:center; }
#preview-wrap img { max-height:140px; max-width:260px; border-radius:8px; border:2px solid var(--gold); box-shadow:0 4px 14px rgba(201,168,76,0.25); }
#preview-name { font-size:11.5px; color:var(--gold); margin-top:6px; font-weight:500; }
.btn-row { display:flex; gap:10px; align-items:center; }
.btn-primary { display:inline-flex; align-items:center; gap:7px; background:var(--gold); color:var(--brown-dk); border:none; padding:10px 22px; border-radius:7px; font-size:13.5px; font-weight:700; cursor:pointer; font-family:inherit; transition:all 0.2s; }
.btn-primary:hover { background:var(--gold-lt); transform:translateY(-1px); }
.btn-primary:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
.btn-secondary { display:inline-flex; align-items:center; gap:7px; background:var(--cream); color:var(--brown); border:1.5px solid var(--border); padding:10px 18px; border-radius:7px; font-size:13.5px; font-weight:500; cursor:pointer; font-family:inherit; transition:all 0.2s; }
.btn-secondary:hover { background:#ede4d4; }

/* ══ TABLE ══ */
.table-card { background:var(--white); border-radius:12px; border:1px solid var(--border); box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }
.table-header { padding:14px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.table-header h3 { font-size:14px; font-weight:600; color:var(--brown-dk); display:flex; align-items:center; gap:8px; }
.badge { background:#f0e6d0; color:var(--brown); font-size:11px; padding:2px 9px; border-radius:20px; font-weight:600; }
.tbl-search input { padding:6px 12px; border:1.5px solid var(--border); border-radius:7px; font-size:12.5px; font-family:inherit; color:var(--text); background:#fdfaf5; outline:none; width:200px; transition:border 0.2s; }
.tbl-search input:focus { border-color:var(--gold); }
table { width:100%; border-collapse:collapse; }
thead th { background:#f9f4ec; padding:10px 16px; text-align:left; font-size:10.5px; font-weight:700; color:var(--muted); letter-spacing:1px; text-transform:uppercase; border-bottom:1px solid var(--border); }
tbody td { padding:12px 16px; font-size:13px; border-bottom:1px solid #f5ede0; vertical-align:middle; color:var(--text); }
tbody tr:last-child td { border-bottom:none; }
tbody tr:hover td { background:#fffbf4; }
.item-thumb { width:56px; height:44px; border-radius:7px; object-fit:cover; border:1.5px solid var(--border); flex-shrink:0; }
.item-thumb-placeholder { width:56px; height:44px; border-radius:7px; background:linear-gradient(135deg,#e8c97a,#c8a97a); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; border:1.5px solid var(--border); }
.item-info { display:flex; align-items:center; gap:12px; }
.item-code { font-size:11px; color:var(--gold); font-weight:700; background:#fffbee; border:1px solid #e8d48a; padding:1px 7px; border-radius:4px; display:inline-block; margin-bottom:3px; }
.item-name { font-weight:600; font-size:13px; color:var(--brown-dk); line-height:1.3; }
.item-name-cn { font-size:11.5px; color:var(--muted); }
.price-cell { font-weight:700; color:var(--brown); font-size:13.5px; }
.status-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:500; cursor:pointer; transition:all 0.2s; }
.status-badge:hover { opacity:0.8; }
.s-pub { background:#e8f5ee; color:#1e7e50; }
.s-draft { background:#f5f0e8; color:#9a7f65; }
.s-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }
.act-btns { display:flex; gap:6px; }
.btn-sm { padding:5px 11px; border-radius:6px; font-size:11.5px; font-weight:500; cursor:pointer; border:none; font-family:inherit; transition:all 0.2s; }
.btn-edit-sm { background:#f0ece4; color:var(--brown); }
.btn-edit-sm:hover { background:#e4d8c4; }
.btn-del-sm { background:#fff0f0; color:#c0392b; }
.btn-del-sm:hover { background:#ffdede; }

/* Loading skeleton */
.skeleton { background:linear-gradient(90deg,#f0e6d0 25%,#e8d8c0 50%,#f0e6d0 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:4px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.skeleton-row td { padding:16px; }

/* Empty state */
.empty-state { text-align:center; padding:50px 20px; color:var(--muted); }
.empty-state .es-icon { font-size:40px; margin-bottom:12px; }
.empty-state p { font-size:13.5px; }

/* Loading spinner */
.spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(62,42,20,0.3); border-top-color:var(--brown-dk); border-radius:50%; animation:spin 0.7s linear infinite; }
@keyframes spin { to{transform:rotate(360deg)} }

/* Toast */
.toast { position:fixed; bottom:24px; right:24px; background:var(--brown-dk); color:#fff; padding:12px 20px; border-radius:10px; font-size:13.5px; box-shadow:0 6px 20px rgba(0,0,0,0.3); display:flex; align-items:center; gap:8px; transform:translateY(80px); opacity:0; transition:all 0.35s cubic-bezier(0.34,1.56,0.64,1); z-index:999; }
.toast.show { transform:translateY(0); opacity:1; }

/* Modals */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:500; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal { background:#fff; border-radius:14px; padding:28px; width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:popIn 0.25s ease; }
@keyframes popIn { from{transform:scale(0.92);opacity:0} to{transform:scale(1);opacity:1} }
.modal h3 { font-size:16px; font-weight:700; color:var(--brown-dk); margin-bottom:16px; }
.modal p { font-size:13.5px; color:var(--muted); margin-bottom:20px; line-height:1.6; }
.modal .modal-btns { display:flex; gap:10px; justify-content:flex-end; }
.btn-cancel { background:var(--cream); color:var(--brown); border:1.5px solid var(--border); padding:8px 18px; border-radius:7px; font-size:13px; font-weight:500; cursor:pointer; font-family:inherit; }
.btn-confirm-del { background:#c0392b; color:#fff; border:none; padding:8px 18px; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:background 0.2s; }
.btn-confirm-del:hover { background:#a93226; }

/* Add Category Modal */
#add-cat-modal .modal { width:360px; }
#add-cat-modal .fg { margin-bottom:14px; }

/* Edit Modal */
#edit-modal .modal { width:520px; max-height:90vh; overflow-y:auto; }
#edit-modal .form-row { grid-template-columns:1fr 1fr; }
#edit-modal .edit-thumb-wrap { text-align:center; margin-bottom:14px; }
#edit-modal .edit-thumb-wrap img { max-height:120px; max-width:200px; border-radius:8px; border:2px solid var(--gold); }
</style>
</head>
<body>

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-circle">🍣</div>
    <div class="logo-text">
      <div class="t1">TOKYO JAPANESE</div>
      <div class="t2">Admin Dashboard</div>
    </div>
  </div>
  <div class="sb-section">主菜单</div>
  <nav><ul>
    <li><a href="#"><span class="ico">📊</span> 仪表板</a></li>
    <li><a href="#"><span class="ico">📅</span> 预约管理</a></li>
    <li><a href="#"><span class="ico">💬</span> 评价管理</a></li>
  </ul></nav>
  <div class="sb-section">视觉管理</div>
  <nav><ul>
    <li><a href="#"><span class="ico">🎵</span> 背景音乐</a></li>
    <li><a href="#"><span class="ico">🏠</span> 首页</a></li>
    <li><a href="#"><span class="ico">ℹ️</span> 关于我们</a></li>
    <li><a href="#"><span class="ico">🏷️</span> 旗下品牌</a></li>
    <li><a href="#"><span class="ico">👥</span> 加入我们</a></li>
    <li><a href="#"><span class="ico">🗺️</span> 企业蓝图管理</a></li>
    <li class="has-sub open">
      <a href="#"><span class="left"><span class="ico">🍽️</span> 菜单管理</span><span class="arr">▶</span></a>
      <ul class="sub-ul">
        <li><a href="#" class="active" onclick="switchTab(null,'grand');return false">Grand Menu</a></li>
        <li><a href="#" onclick="switchTab(null,'sushi');return false">Sushi Menu</a></li>
      </ul>
    </li>
  </ul></nav>
  <div class="sidebar-footer">
    <a href="#"><span style="font-size:15px">⚙️</span> 系统设置</a>
    <a href="#"><span style="font-size:15px">🚪</span> 退出登录</a>
  </div>
</aside>

<!-- ══════════ MAIN ══════════ -->
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h2>🍽️ 菜单管理</h2>
      <div class="breadcrumb">视觉管理 › 菜单管理 › <span id="bc-tab">Grand Menu</span></div>
    </div>
    <div class="topbar-right">
      <span style="font-size:12.5px;color:var(--muted)">管理员 Admin</span>
      <div class="avatar">A</div>
    </div>
  </div>

  <div class="page">

    <!-- TABS -->
    <div class="menu-tabs">
      <button class="menu-tab active" id="tab-grand" onclick="switchTab(this,'grand')">
        <div class="tab-icon">🍽️</div>
        <div class="tab-label">
          <div class="t1">Grand Menu</div>
          <div class="t2" id="tab-grand-count">加载中...</div>
        </div>
      </button>
      <button class="menu-tab" id="tab-sushi" onclick="switchTab(this,'sushi')">
        <div class="tab-icon">🍣</div>
        <div class="tab-label">
          <div class="t1">Sushi Menu</div>
          <div class="t2" id="tab-sushi-count">加载中...</div>
        </div>
      </button>
    </div>

    <!-- CONTENT GRID -->
    <div class="content-grid">

      <!-- LEFT: CATEGORIES -->
      <div class="cat-panel">
        <div class="cat-panel-header">
          <span>📂 分类管理</span>
          <button class="btn-add-cat" onclick="openAddCatModal()" title="新增分类">+</button>
        </div>
        <div class="cat-list" id="cat-list">
          <div class="skeleton-row" style="padding:10px 8px">
            <div class="skeleton" style="height:32px;border-radius:7px;margin-bottom:4px"></div>
            <div class="skeleton" style="height:32px;border-radius:7px;margin-bottom:4px"></div>
            <div class="skeleton" style="height:32px;border-radius:7px"></div>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="right-panel">

        <!-- ADD FORM -->
        <div class="upload-card">
          <div class="card-header">
            <div>
              <h3>➕ 新增菜单项目</h3>
              <div class="subtitle">当前分类：<span id="cur-cat-label" style="color:var(--gold-lt)">请选择分类</span></div>
            </div>
          </div>
          <div class="card-body">
            <div class="form-row">
              <div class="fg">
                <label>菜单编号</label>
                <input type="text" id="inp-code" placeholder="H01">
              </div>
              <div class="fg">
                <label>英文名称 *</label>
                <input type="text" id="inp-name" placeholder="ATSUYAKI TAMAGO">
              </div>
              <div class="fg">
                <label>中文名称</label>
                <input type="text" id="inp-cn" placeholder="厚玉子烧">
              </div>
            </div>
            <div class="form-row-2">
              <div class="fg">
                <label>描述 / 食材</label>
                <input type="text" id="inp-desc" placeholder="Japanese Omelette">
              </div>
              <div class="fg">
                <label>价格 (RM)</label>
                <input type="text" id="inp-price" placeholder="13.90">
              </div>
              <div class="fg">
                <label>状态</label>
                <select id="inp-status">
                  <option value="published">✅ 已发布</option>
                  <option value="draft">📝 草稿</option>
                </select>
              </div>
            </div>
            <div class="upload-zone" id="drop-zone">
              <input type="file" id="file-input" accept="image/*">
              <span class="uz-icon">🖼️</span>
              <p>拖拽图片到这里，或 <strong>点击上传</strong></p>
              <p class="hint">支持 JPG · PNG · WEBP · 最大 5MB</p>
            </div>
            <div id="preview-wrap">
              <img id="preview-img" src="" alt="">
              <p id="preview-name"></p>
            </div>
            <div class="btn-row">
              <button class="btn-primary" id="btn-submit" onclick="handleAdd()">📤 上传并保存</button>
              <button class="btn-secondary" onclick="resetForm()">↩ 清除</button>
            </div>
          </div>
        </div>

        <!-- TABLE -->
        <div class="table-card">
          <div class="table-header">
            <h3>📋 <span id="table-title">—</span> 菜单项目 <span class="badge" id="item-count">0 项</span></h3>
            <div class="tbl-search">
              <input type="text" id="search-input" placeholder="🔍 搜索菜单名称..." oninput="handleSearch(this.value)">
            </div>
          </div>
          <table>
            <thead>
              <tr>
                <th>菜单项目</th>
                <th>价格</th>
                <th>图片</th>
                <th>状态</th>
                <th>上传时间</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody id="menu-tbody">
              <tr class="skeleton-row">
                <td colspan="6" style="text-align:center;padding:40px;color:var(--muted);font-size:13px">请先选择左侧分类</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">
  <span id="toast-msg">操作成功</span>
</div>

<!-- ══ DELETE MENU ITEM MODAL ══ -->
<div class="modal-overlay" id="del-modal">
  <div class="modal">
    <h3>🗑️ 确认删除</h3>
    <p>你确定要删除 <strong id="del-item-name" style="color:var(--brown-dk)"></strong> 吗？<br>此操作无法恢复，图片文件也会一并删除。</p>
    <div class="modal-btns">
      <button class="btn-cancel" onclick="closeModal('del-modal')">取消</button>
      <button class="btn-confirm-del" id="btn-confirm-del" onclick="doDelete()">确认删除</button>
    </div>
  </div>
</div>

<!-- ══ DELETE CATEGORY MODAL ══ -->
<div class="modal-overlay" id="del-cat-modal">
  <div class="modal">
    <h3>🗑️ 删除分类</h3>
    <p>删除分类 <strong id="del-cat-name" style="color:var(--brown-dk)"></strong> 将同时删除该分类下的所有菜单项目及图片。<br>此操作无法恢复。</p>
    <div class="modal-btns">
      <button class="btn-cancel" onclick="closeModal('del-cat-modal')">取消</button>
      <button class="btn-confirm-del" onclick="doDeleteCat()">确认删除</button>
    </div>
  </div>
</div>

<!-- ══ ADD CATEGORY MODAL ══ -->
<div class="modal-overlay" id="add-cat-modal">
  <div class="modal">
    <h3>📂 新增分类</h3>
    <div class="fg" style="margin-bottom:14px">
      <label>分类名称 *</label>
      <input type="text" id="new-cat-name" placeholder="例如：Yakimono">
    </div>
    <div class="fg" style="margin-bottom:20px">
      <label>排列顺序</label>
      <input type="number" id="new-cat-order" placeholder="0" value="0">
    </div>
    <div class="modal-btns">
      <button class="btn-cancel" onclick="closeModal('add-cat-modal')">取消</button>
      <button class="btn-primary" onclick="doAddCat()">➕ 新增分类</button>
    </div>
  </div>
</div>

<!-- ══ EDIT MENU ITEM MODAL ══ -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <h3>✏️ 编辑菜单项目</h3>
    <input type="hidden" id="edit-id">
    <div id="edit-thumb-wrap" class="edit-thumb-wrap"></div>
    <div class="form-row" style="margin-bottom:14px">
      <div class="fg">
        <label>菜单编号</label>
        <input type="text" id="edit-code" placeholder="H01">
      </div>
      <div class="fg">
        <label>英文名称 *</label>
        <input type="text" id="edit-name" placeholder="ATSUYAKI TAMAGO">
      </div>
    </div>
    <div class="form-row" style="margin-bottom:14px">
      <div class="fg">
        <label>中文名称</label>
        <input type="text" id="edit-cn" placeholder="厚玉子烧">
      </div>
      <div class="fg">
        <label>描述 / 食材</label>
        <input type="text" id="edit-desc" placeholder="Japanese Omelette">
      </div>
    </div>
    <div class="form-row" style="margin-bottom:14px">
      <div class="fg">
        <label>价格 (RM)</label>
        <input type="text" id="edit-price" placeholder="13.90">
      </div>
      <div class="fg">
        <label>状态</label>
        <select id="edit-status">
          <option value="published">✅ 已发布</option>
          <option value="draft">📝 草稿</option>
        </select>
      </div>
    </div>
    <div class="fg" style="margin-bottom:20px">
      <label>更换图片（留空保留原图）</label>
      <input type="file" id="edit-image" accept="image/*" style="background:#fdfaf5;padding:8px;border:1.5px solid var(--border);border-radius:7px;width:100%;font-size:13px">
    </div>
    <div class="modal-btns">
      <button class="btn-cancel" onclick="closeModal('edit-modal')">取消</button>
      <button class="btn-primary" id="btn-edit-save" onclick="doEdit()">💾 保存修改</button>
    </div>
  </div>
</div>

<script>
// ============================================================
//  CONFIG — change this to your actual API path
// ============================================================
const API = 'menu_api.php';

// ============================================================
//  STATE
// ============================================================
let currentType       = 'grand';
let currentCatId      = null;
let currentCatName    = '';
let deleteItemId      = null;
let deleteCatId       = null;
let searchTimer       = null;
let allCats           = { grand: [], sushi: [] };

// ============================================================
//  INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  loadCategories('grand');
  loadCategories('sushi');

  // Sidebar submenu toggle
  document.querySelectorAll('.has-sub > a').forEach(a => {
    a.addEventListener('click', e => { e.preventDefault(); a.parentElement.classList.toggle('open'); });
  });

  // Image preview
  document.getElementById('file-input').addEventListener('change', function() {
    previewFile(this.files[0], 'preview-img', 'preview-name', 'preview-wrap');
  });

  // Drag & drop
  const dz = document.getElementById('drop-zone');
  dz.addEventListener('dragover',  e => { e.preventDefault(); dz.style.borderColor='var(--gold)'; });
  dz.addEventListener('dragleave', ()  => { dz.style.borderColor='var(--border)'; });
  dz.addEventListener('drop',      e  => { e.preventDefault(); dz.style.borderColor='var(--border)'; const f=e.dataTransfer.files[0]; if(f){ document.getElementById('file-input').files=e.dataTransfer.files; previewFile(f,'preview-img','preview-name','preview-wrap'); } });

  // Close modals on backdrop click
  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target===o) o.classList.remove('show'); });
  });

  // Edit image preview
  document.getElementById('edit-image').addEventListener('change', function() {
    const f = this.files[0]; if(!f) return;
    const r = new FileReader();
    r.onload = e => { document.getElementById('edit-thumb-wrap').innerHTML = `<img src="${e.target.result}" style="max-height:100px;border-radius:8px;border:2px solid var(--gold);margin-bottom:10px">`; };
    r.readAsDataURL(f);
  });
});

// ============================================================
//  HELPERS
// ============================================================
function api(params) {
  const fd = new FormData();
  for (const [k, v] of Object.entries(params)) fd.append(k, v);
  return fetch(API, { method: 'POST', body: fd }).then(r => r.json());
}

function apiGet(params) {
  const qs = new URLSearchParams(params).toString();
  return fetch(`${API}?${qs}`).then(r => r.json());
}

function showToast(msg, duration=2800) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), duration);
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function previewFile(file, imgId, nameId, wrapId) {
  if (!file) return;
  const r = new FileReader();
  r.onload = e => {
    document.getElementById(imgId).src = e.target.result;
    document.getElementById(nameId).textContent = '✅ ' + file.name;
    document.getElementById(wrapId).style.display = 'block';
  };
  r.readAsDataURL(file);
}

function fmtDate(str) {
  if (!str) return '—';
  return str.slice(0, 10);
}

// ============================================================
//  LOAD CATEGORIES
// ============================================================
async function loadCategories(type) {
  const res = await apiGet({ action: 'get_categories', type });
  if (!res.success) { showToast('⚠️ 加载分类失败'); return; }

  allCats[type] = res.data.categories;

  // Update tab count
  const total = res.data.categories.reduce((s, c) => s + parseInt(c.item_count || 0), 0);
  const label = type === 'grand' ? '主菜单' : '寿司专区';
  document.getElementById(`tab-${type}-count`).textContent = `${label} · ${total} 项`;

  if (type === currentType) renderCatList(type);
}

function renderCatList(type) {
  const cats = allCats[type] || [];
  const list = document.getElementById('cat-list');

  if (cats.length === 0) {
    list.innerHTML = `<div style="text-align:center;padding:20px;color:var(--muted);font-size:13px">暂无分类<br><small>点击 + 新增</small></div>`;
    return;
  }

  list.innerHTML = cats.map(c => `
    <div class="cat-item ${c.id == currentCatId ? 'active' : ''}" 
         onclick="selectCat(${c.id}, '${escHtml(c.category_name)}', ${c.item_count})" 
         data-id="${c.id}">
      <span class="cat-left">
        <span>${escHtml(c.category_name)}</span>
        <span class="cat-count">${c.item_count}</span>
      </span>
      <button class="btn-del-cat" onclick="event.stopPropagation();confirmDelCat(${c.id},'${escHtml(c.category_name)}')" title="删除分类">✕</button>
    </div>
  `).join('');

  // Auto-select first if none selected
  if (!currentCatId && cats.length > 0) {
    selectCat(cats[0].id, cats[0].category_name, cats[0].item_count);
  }
}

// ============================================================
//  SWITCH TAB (grand / sushi)
// ============================================================
function switchTab(el, type) {
  currentType  = type;
  currentCatId = null;

  document.querySelectorAll('.menu-tab').forEach(t => t.classList.remove('active'));
  if (el) el.classList.add('active');
  else document.getElementById('tab-' + type).classList.add('active');

  // Sidebar links
  document.querySelectorAll('.sub-ul li a').forEach(a => a.classList.remove('active'));
  document.querySelectorAll('.sub-ul li a').forEach(a => {
    if ((type==='grand' && a.textContent.includes('Grand')) || (type==='sushi' && a.textContent.includes('Sushi')))
      a.classList.add('active');
  });

  document.getElementById('bc-tab').textContent = type === 'grand' ? 'Grand Menu' : 'Sushi Menu';
  document.getElementById('table-title').textContent = '—';
  document.getElementById('item-count').textContent  = '0 项';
  document.getElementById('menu-tbody').innerHTML    = `<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);font-size:13px">请先选择左侧分类</td></tr>`;
  document.getElementById('cur-cat-label').textContent = '请选择分类';

  renderCatList(type);
}

// ============================================================
//  SELECT CATEGORY → load items
// ============================================================
function selectCat(catId, catName, count) {
  currentCatId   = catId;
  currentCatName = catName;

  document.querySelectorAll('.cat-item').forEach(i => i.classList.remove('active'));
  document.querySelector(`.cat-item[data-id="${catId}"]`)?.classList.add('active');

  document.getElementById('cur-cat-label').textContent  = catName;
  document.getElementById('table-title').textContent    = catName;
  document.getElementById('item-count').textContent     = `${count} 项`;
  document.getElementById('search-input').value         = '';

  loadItems();
}

// ============================================================
//  LOAD MENU ITEMS
// ============================================================
async function loadItems(search = '') {
  const tbody = document.getElementById('menu-tbody');
  tbody.innerHTML = `<tr class="skeleton-row"><td colspan="6">
    <div class="skeleton" style="height:20px;margin:8px 16px;border-radius:4px"></div>
    <div class="skeleton" style="height:20px;margin:8px 16px;border-radius:4px"></div>
    <div class="skeleton" style="height:20px;margin:8px 16px;border-radius:4px"></div>
  </td></tr>`;

  const params = { action: 'get', type: currentType, category_id: currentCatId };
  if (search) params.search = search;

  const res = await apiGet(params);
  if (!res.success) { showToast('⚠️ 加载失败：' + res.message); tbody.innerHTML = `<tr><td colspan="6" class="empty-state">加载失败</td></tr>`; return; }

  const items = res.data.items;
  document.getElementById('item-count').textContent = `${items.length} 项`;

  if (items.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><div class="es-icon">🍽️</div><p>暂无菜单项目，请使用上方表单新增</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = items.map(item => `
    <tr>
      <td>
        <div class="item-info">
          ${item.image_url
            ? `<img class="item-thumb" src="${escHtml(item.image_url)}" alt="${escHtml(item.item_name)}" onerror="this.style.display='none'">`
            : `<div class="item-thumb-placeholder">🍽️</div>`}
          <div>
            ${item.item_code ? `<div class="item-code">${escHtml(item.item_code)}</div>` : ''}
            <div class="item-name">${escHtml(item.item_name)}</div>
            <div class="item-name-cn">${item.item_name_cn ? escHtml(item.item_name_cn) : ''}${item.item_desc ? ' · ' + escHtml(item.item_desc) : ''}</div>
          </div>
        </div>
      </td>
      <td class="price-cell">${item.price_formatted || '—'}</td>
      <td>
        ${item.image_url
          ? `<span style="font-size:11.5px;color:var(--green)">✅ 已上传</span>`
          : `<span style="font-size:11.5px;color:var(--muted)">— 无图片</span>`}
      </td>
      <td>
        <span class="status-badge ${item.status === 'published' ? 's-pub' : 's-draft'}" 
              onclick="toggleStatus(${item.id})" title="点击切换状态">
          <span class="s-dot"></span>
          ${item.status === 'published' ? '已发布' : '草稿'}
        </span>
      </td>
      <td style="font-size:12px;color:var(--muted)">${fmtDate(item.created_at)}</td>
      <td>
        <div class="act-btns">
          <button class="btn-sm btn-edit-sm" onclick='openEditModal(${JSON.stringify(item)})'>编辑</button>
          <button class="btn-sm btn-del-sm"  onclick="confirmDelete(${item.id}, '${escHtml(item.item_name)}')">删除</button>
        </div>
      </td>
    </tr>
  `).join('');
}

// ============================================================
//  SEARCH
// ============================================================
function handleSearch(val) {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => loadItems(val), 400);
}

// ============================================================
//  ADD MENU ITEM
// ============================================================
async function handleAdd() {
  const name = document.getElementById('inp-name').value.trim();
  if (!name)          { showToast('⚠️ 请填写英文菜单名称'); return; }
  if (!currentCatId)  { showToast('⚠️ 请先选择左侧分类'); return; }

  const btn = document.getElementById('btn-submit');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> 保存中...';

  const fd = new FormData();
  fd.append('action',      'add');
  fd.append('type',        currentType);
  fd.append('category_id', currentCatId);
  fd.append('item_code',   document.getElementById('inp-code').value.trim());
  fd.append('item_name',   name);
  fd.append('item_name_cn',document.getElementById('inp-cn').value.trim());
  fd.append('item_desc',   document.getElementById('inp-desc').value.trim());
  fd.append('price',       document.getElementById('inp-price').value.trim());
  fd.append('status',      document.getElementById('inp-status').value);

  const imgFile = document.getElementById('file-input').files[0];
  if (imgFile) fd.append('image', imgFile);

  try {
    const res = await fetch(API, { method: 'POST', body: fd }).then(r => r.json());
    if (res.success) {
      showToast('✅ 新增成功！' + name);
      resetForm();
      await loadCategories(currentType); // refresh counts
      await loadItems();
    } else {
      showToast('❌ 新增失败：' + res.message);
    }
  } catch(e) {
    showToast('❌ 网络错误，请重试');
  }

  btn.disabled = false;
  btn.innerHTML = '📤 上传并保存';
}

function resetForm() {
  ['inp-code','inp-name','inp-cn','inp-desc','inp-price'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('inp-status').value = 'published';
  document.getElementById('preview-wrap').style.display = 'none';
  document.getElementById('file-input').value = '';
}

// ============================================================
//  DELETE MENU ITEM
// ============================================================
function confirmDelete(id, name) {
  deleteItemId = id;
  document.getElementById('del-item-name').textContent = name;
  document.getElementById('del-modal').classList.add('show');
}

async function doDelete() {
  const btn = document.getElementById('btn-confirm-del');
  btn.textContent = '删除中...';
  btn.disabled    = true;

  const res = await api({ action: 'delete', id: deleteItemId });
  closeModal('del-modal');
  btn.textContent = '确认删除';
  btn.disabled    = false;

  if (res.success) {
    showToast('🗑️ 已删除');
    await loadCategories(currentType);
    await loadItems();
  } else {
    showToast('❌ 删除失败：' + res.message);
  }
}

// ============================================================
//  TOGGLE STATUS
// ============================================================
async function toggleStatus(id) {
  const res = await api({ action: 'toggle_status', id });
  if (res.success) {
    const label = res.data.status === 'published' ? '已发布' : '草稿';
    showToast(`🔄 状态已切换为「${label}」`);
    loadItems(document.getElementById('search-input').value);
  } else {
    showToast('❌ 操作失败：' + res.message);
  }
}

// ============================================================
//  EDIT MENU ITEM
// ============================================================
function openEditModal(item) {
  document.getElementById('edit-id').value    = item.id;
  document.getElementById('edit-code').value  = item.item_code  || '';
  document.getElementById('edit-name').value  = item.item_name  || '';
  document.getElementById('edit-cn').value    = item.item_name_cn || '';
  document.getElementById('edit-desc').value  = item.item_desc  || '';
  document.getElementById('edit-price').value = item.price      || '';
  document.getElementById('edit-status').value= item.status;
  document.getElementById('edit-image').value = '';

  const thumbWrap = document.getElementById('edit-thumb-wrap');
  thumbWrap.innerHTML = item.image_url
    ? `<img src="${escHtml(item.image_url)}" style="max-height:100px;border-radius:8px;border:2px solid var(--gold);margin-bottom:10px">`
    : '';

  document.getElementById('edit-modal').classList.add('show');
}

async function doEdit() {
  const name = document.getElementById('edit-name').value.trim();
  if (!name) { showToast('⚠️ 英文名称不能为空'); return; }

  const btn = document.getElementById('btn-edit-save');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> 保存中...';

  const fd = new FormData();
  fd.append('action',       'edit');
  fd.append('id',           document.getElementById('edit-id').value);
  fd.append('item_code',    document.getElementById('edit-code').value.trim());
  fd.append('item_name',    name);
  fd.append('item_name_cn', document.getElementById('edit-cn').value.trim());
  fd.append('item_desc',    document.getElementById('edit-desc').value.trim());
  fd.append('price',        document.getElementById('edit-price').value.trim());
  fd.append('status',       document.getElementById('edit-status').value);

  const imgFile = document.getElementById('edit-image').files[0];
  if (imgFile) fd.append('image', imgFile);

  try {
    const res = await fetch(API, { method: 'POST', body: fd }).then(r => r.json());
    if (res.success) {
      closeModal('edit-modal');
      showToast('✅ 修改已保存');
      loadItems(document.getElementById('search-input').value);
    } else {
      showToast('❌ 保存失败：' + res.message);
    }
  } catch(e) {
    showToast('❌ 网络错误，请重试');
  }

  btn.disabled = false;
  btn.innerHTML = '💾 保存修改';
}

// ============================================================
//  ADD CATEGORY
// ============================================================
function openAddCatModal() {
  document.getElementById('new-cat-name').value  = '';
  document.getElementById('new-cat-order').value = '0';
  document.getElementById('add-cat-modal').classList.add('show');
  setTimeout(() => document.getElementById('new-cat-name').focus(), 100);
}

async function doAddCat() {
  const name = document.getElementById('new-cat-name').value.trim();
  if (!name) { showToast('⚠️ 请填写分类名称'); return; }

  const res = await api({
    action:        'add_category',
    type:          currentType,
    category_name: name,
    sort_order:    document.getElementById('new-cat-order').value || 0,
  });

  closeModal('add-cat-modal');

  if (res.success) {
    showToast('✅ 分类「' + name + '」已新增');
    await loadCategories(currentType);
    // Auto-select the new category
    const newCat = allCats[currentType].find(c => c.id == res.data.id);
    if (newCat) selectCat(newCat.id, newCat.category_name, 0);
  } else {
    showToast('❌ 新增失败：' + res.message);
  }
}

// ============================================================
//  DELETE CATEGORY
// ============================================================
function confirmDelCat(id, name) {
  deleteCatId = id;
  document.getElementById('del-cat-name').textContent = name;
  document.getElementById('del-cat-modal').classList.add('show');
}

async function doDeleteCat() {
  const res = await api({ action: 'delete_category', id: deleteCatId });
  closeModal('del-cat-modal');

  if (res.success) {
    showToast('🗑️ 分类已删除');
    currentCatId = null;
    document.getElementById('table-title').textContent = '—';
    document.getElementById('item-count').textContent  = '0 项';
    document.getElementById('menu-tbody').innerHTML    = `<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);font-size:13px">请先选择左侧分类</td></tr>`;
    await loadCategories(currentType);
  } else {
    showToast('❌ 删除失败：' + res.message);
  }
}

// ============================================================
//  XSS SAFETY
// ============================================================
function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>
</body>
</html>
