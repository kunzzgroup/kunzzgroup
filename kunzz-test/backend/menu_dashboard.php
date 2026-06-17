<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

/**
 * TOKYO JAPANESE CUISINE — Menu Management Dashboard
 * Redesigned to match the current backend style and integrate sidebar.php
 */
session_start();

// Authentication guard
if (!isset($_SESSION['user_id'])) {
    header("Location: /frontend/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>菜单管理 · Tokyo Japanese</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/menu_dashboard.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="../images/logo.png">
</head>
<body class="has-sidebar">

    <!-- Sidebar Integration -->
    <?php include 'sidebar.php'; ?>

    <div class="workspace">
        
        <!-- 1. TOPBAR -->
        <header class="topbar">
                <div class="tb-left">
                    <span class="tb-title">菜单管理</span>
                    <span style="opacity:0.2;margin:0 4px">|</span>
                    <div class="tb-crumb">
                        视觉管理 › <span id="bc-tab">Grand Menu</span> › <b id="bc-cat">—</b>
                    </div>
                </div>
                <div class="tb-right">
                    <div class="type-tabs">
                        <button class="type-tab active" id="tab-grand" onclick="switchType('grand')">GRAND</button>
                        <button class="type-tab"        id="tab-sushi" onclick="switchType('sushi')">SUSHI</button>
                    </div>
                    <span class="tb-badge" id="tb-stats">0 项目</span>
                </div>
        </header>

        <!-- 2. BODY LAYOUT -->
        <main class="body-layout">
            
            <!-- LEFT: Category Navigation -->
            <aside class="cat-col">
                <div class="cat-head">
                    <span class="cat-head-label">分类目录</span>
                    <button class="btn-new-cat" onclick="toggleCatAdd()" title="新增分类">＋</button>
                </div>
                
                <!-- Inline Add Row -->
                <div class="cat-add-row" id="cat-add-row">
                    <input type="text" id="new-cat-inp" placeholder="分类名称..." onkeyup="if(event.key==='Enter')doAddCat()">
                    <button class="btn-ok" onclick="doAddCat()">OK</button>
                </div>



                <div class="cat-scroll" id="cat-scroll">
                    <!-- Loaded by JS -->
                </div>
            </aside>

            <!-- MIDDLE: Item List -->
            <section class="list-col">
                <div class="list-toolbar">
                    <div class="search-box">
                        <span class="si">🔍</span>
                        <input type="text" id="search-inp" placeholder="搜索名称或编号..." oninput="onSearch(this.value)">
                    </div>
                    <div class="toolbar-info">
                        <div class="info-chip"><span class="dot-green"></span> <span id="info-pub">0 已发布</span></div>
                        <div class="info-chip"><span class="dot-gray"></span>  <span id="info-dft">0 草稿</span></div>
                    </div>
                </div>

                <div class="list-head">
                    <div>缩略图</div>
                    <div>菜品详情</div>
                    <div>价格</div>
                    <div>状态</div>
                    <div>操作</div>
                </div>

                <div class="list-scroll" id="list-scroll">
                    <!-- Items loaded by JS -->
                </div>
            </section>

            <!-- RIGHT: Add Panel -->
            <aside class="add-panel">
                <div class="add-panel-head">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div id="add-panel-mode" style="font-size:10px; font-weight:700; color:var(--gold); text-transform:uppercase; margin-bottom:4px; display:none;">编辑模式</div>
                            <h3 id="add-panel-title">＋ 新增菜单项目</h3>
                            <p id="add-panel-sub">请先选择分类</p>
                        </div>
                        <button class="btn-close-panel" id="btn-close-panel" onclick="cancelEdit()" style="display:none;">✕</button>
                    </div>
                </div>
                
                <div class="add-scroll">
                    <div class="field">
                        <label>菜品编号</label>
                        <input type="text" id="f-code" placeholder="例: H01, S05">
                    </div>
                    <div class="field">
                        <label>英文名称 *</label>
                        <input type="text" id="f-name" placeholder="例: SALMON NIGIRI">
                    </div>
                    <div class="field">
                        <label>中文名称</label>
                        <input type="text" id="f-cn" placeholder="例: 三文鱼握寿司">
                    </div>
                    <div class="field">
                        <label>食材/描述</label>
                        <input type="text" id="f-desc" placeholder="简短描述...">
                    </div>
                    
                    <div class="field-row">
                        <div class="field">
                            <label>价格 (RM)</label>
                            <input type="text" id="f-price" placeholder="12.90">
                        </div>
                        <div class="field">
                            <label>展示状态</label>
                            <select id="f-status">
                                <option value="published">✓ 已发布</option>
                                <option value="draft">◷ 草稿</option>
                            </select>
                        </div>
                    </div>

                    <div class="divider"></div>
                    
                    <div class="field">
                        <label>展示图片</label>
                        <div class="img-zone" id="img-zone" 
                             ondragover="event.preventDefault();this.classList.add('dragover')" 
                             ondragleave="this.classList.remove('dragover')"
                             ondrop="onImgDrop(event)">
                            <input type="file" id="f-img" accept="image/*" onchange="onImgPick(this)">
                            
                            <div class="img-zone-inner" id="img-zone-inner">
                                <span class="iz-icon">📸</span>
                                <span class="iz-text">拖拽图片或 <b>点击上传</b></span>
                                <span class="iz-hint">建议 800x600, < 2MB</span>
                            </div>

                            <div class="img-preview-wrap" id="img-preview-wrap">
                                <img id="img-preview" src="">
                                <div class="img-preview-info">
                                    <div class="img-preview-name" id="img-preview-name">file.jpg</div>
                                    <div class="img-preview-size" id="img-preview-size">0 KB</div>
                                </div>
                                <button class="img-clear" onclick="clearImg(event)">✕</button>
                            </div>
                        </div>
                    </div>

                    <button class="btn-submit" id="btn-submit" onclick="doAdd()">
                        <span id="btn-submit-text">＋ 添加到菜单</span>
                    </button>
                    <button class="btn-reset" onclick="resetForm()">重置表单</button>
                </div>
            </aside>
        </main>
    </div>

    <!-- 3. TOAST & MODALS -->
    <div class="toast" id="toast">操作成功</div>

    <!-- Confirm Dialog -->
    <div class="confirm-bg" id="confirm-bg">
        <div class="confirm-box">
            <h4 id="confirm-title">确认操作</h4>
            <p id="confirm-body">确定要执行此操作吗？</p>
            <div class="confirm-btns">
                <button class="cbtn cbtn-no"  onclick="closeConfirm()">取消</button>
                <button class="cbtn cbtn-yes" id="confirm-yes">确定执行</button>
            </div>
        </div>
    </div>

    <!-- 4. PHOTO VIEWER -->
    <div class="photo-viewer" id="photo-viewer" onclick="closePhoto()">
        <button class="pv-close">✕</button>
        <img src="" id="pv-img" onclick="event.stopPropagation()">
    </div>

    <!-- Custom JS -->
    <script src="js/menu_dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>

