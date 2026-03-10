<?php
define('API_BASE', 'menu_api.php');

function fetchCategories(string $type): array {
    $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST']
          . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/' . API_BASE;
    $raw  = @file_get_contents($host . '?action=get_categories&type=' . $type,
                false, stream_context_create(['http' => ['timeout' => 8]]));
    if (!$raw) return [];
    $json = json_decode($raw, true);
    return ($json['success'] ?? false) ? ($json['data']['categories'] ?? []) : [];
}

$grandCats = fetchCategories('grand');
$sushiCats = fetchCategories('sushi');
?><!DOCTYPE html>
<html lang="zh-CN" class="menu-html">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="logo/tokyologo.png" />
    <title>Menu | TOKYO JAPANESE CUISINE</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+SC:wght@300;400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="menu.css" />
    <style>
        .nav-btn--dark    { color: #33322f; border-color: #33322f; }
        .sidebar-link--accent { color: #a68a64; }
        .sidebar-empty    { padding: 12px 16px; font-size: 13px; color: #9b8f7e; }

        .item-info        { display: flex; flex-direction: column; gap: 2px; }
        .title-cn         { font-size: 13px; color: #9b8f7e; }
        .item-price       { font-size: 13px; font-weight: 600; color: #a68a64; margin-left: auto; white-space: nowrap; }

        .img-placeholder  { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f0ece6; color: #c4b89e; font-size: 2rem; }

        .state-loading,
        .state-empty      { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 0; gap: 12px; color: #9b8f7e; font-size: 0.88rem; letter-spacing: 0.04em; }
        .state-loading::before { content: ''; width: 22px; height: 22px; border: 2px solid #e8e0d4; border-top-color: #a68a64; border-radius: 50%; animation: spin 0.75s linear infinite; }
        .state-empty-icon { font-size: 2rem; }
        @keyframes spin   { to { transform: rotate(360deg); } }
    </style>
</head>

<body class="luxury-menu-body haidilao-menu-body">

<nav id="nav">
    <div class="nav-container">
        <a href="tokyo.html" class="nav-logo-link">
            <div class="haidilao-logo-bg">
                <img src="logo/tokyologo.png" alt="Tokyo" class="nav-logo" />
            </div>
            <div class="nav-text-group">
                <span class="nav-text primary">TOKYO JAPANESE</span>
                <span class="nav-text secondary">CUISINE</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="tokyo.html#about-culture">关于我们</a>
            <a href="tokyo.html#mission-vision">使命愿景</a>
            <a href="tokyo.html#featured">特色推荐</a>
            <a href="tokyo.html#location">我们在这</a>
        </div>
        <div class="nav-actions">
            <a href="tokyo.html#location" class="btn-primary nav-btn">联系我们</a>
            <a href="tokyo.html" class="btn-secondary nav-btn nav-btn--dark">返回首页</a>
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<div class="mobile-sidebar-overlay" id="mobile-sidebar-overlay"></div>
<aside class="mobile-sidebar" id="mobile-sidebar">
    <button class="mobile-sidebar-close" id="mobile-sidebar-close" aria-label="Close">×</button>
    <div class="mobile-sidebar-content">
        <a href="tokyo.html#about-culture" class="sidebar-link">关于我们</a>
        <a href="tokyo.html#mission-vision" class="sidebar-link">使命愿景</a>
        <a href="tokyo.html#featured" class="sidebar-link">特色推荐</a>
        <a href="tokyo.html#location" class="sidebar-link">我们在这</a>
        <a href="tokyo.html" class="sidebar-link sidebar-link--accent">返回首页</a>
    </div>
</aside>

<div class="haidilao-top-nav-container">
    <div class="haidilao-top-nav">
        <button class="haidilao-top-tab active" data-menu="grand">
            <div class="top-tab-icon">
                <img src="grandmenu/menu2.png" alt="Grand Menu" />
            </div>
            <span>Grand Menu</span>
        </button>
        <button class="haidilao-top-tab" data-menu="sushi">
            <div class="top-tab-icon">
                <img src="sushimenu/menu1.png" alt="Sushi Menu" />
            </div>
            <span>Sushi Menu</span>
        </button>
    </div>
</div>

<div class="haidilao-layout">
    <aside class="haidilao-sidebar">
        <div class="sidebar-category" id="sidebar-grand">
            <?php foreach ($grandCats as $i => $cat): ?>
                <button class="haidilao-sidebar-tab<?= $i === 0 ? ' active' : '' ?>"
                        data-cat-id="<?= (int)$cat['id'] ?>">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </button>
            <?php endforeach; ?>
            <?php if (empty($grandCats)): ?>
                <p class="sidebar-empty">暂无分类</p>
            <?php endif; ?>
        </div>

        <div class="sidebar-category" id="sidebar-sushi" hidden>
            <?php foreach ($sushiCats as $i => $cat): ?>
                <button class="haidilao-sidebar-tab<?= $i === 0 ? ' active' : '' ?>"
                        data-cat-id="<?= (int)$cat['id'] ?>">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </button>
            <?php endforeach; ?>
            <?php if (empty($sushiCats)): ?>
                <p class="sidebar-empty">暂无分类</p>
            <?php endif; ?>
        </div>
    </aside>

    <main class="haidilao-content" id="menu-content">
        <div class="state-loading"></div>
    </main>
</div>

<div id="zoom-modal" class="zoom-modal hidden">
    <div class="zoom-modal-content">
        <button class="zoom-modal-close" aria-label="Close">&times;</button>
        <img id="zoom-modal-image" src="" alt="" class="zoom-modal-image" />
    </div>
</div>

<script>
(() => {
    const API       = '<?= API_BASE ?>';
    const content   = document.getElementById('menu-content');
    const zoomModal = document.getElementById('zoom-modal');
    const zoomImg   = document.getElementById('zoom-modal-image');
    const topTabs   = document.querySelectorAll('.haidilao-top-tab');
    const sidebars  = {
        grand: document.getElementById('sidebar-grand'),
        sushi: document.getElementById('sidebar-sushi'),
    };

    let activeType  = 'grand';
    let activeCatId = null;

    // Zoom modal
    const openZoom  = src => { zoomImg.src = src; zoomModal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; };
    const closeZoom = ()  => { zoomModal.classList.add('hidden'); document.body.style.overflow = ''; };

    document.querySelector('.zoom-modal-close').onclick = closeZoom;
    zoomModal.onclick = e => { if (e.target === zoomModal) closeZoom(); };
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeZoom(); });

    // HTML escaping
    const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

    // Render card grid
    function renderGrid(items) {
        const published = items.filter(i => i.status === 'published');

        if (!published.length) {
            return `<div class="state-empty"><span class="state-empty-icon">🍱</span><span>该分类下暂无菜品</span></div>`;
        }

        const cards = published.map(i => `
            <div class="haidilao-card">
                <div class="img-wrap">
                    ${i.image_url
                        ? `<img src="${esc(i.image_url)}" alt="${esc(i.item_name)}" loading="lazy" />`
                        : `<div class="img-placeholder">🍱</div>`}
                </div>
                <div class="info-wrap">
                    <div class="item-info">
                        <span class="title">${esc(i.item_name)}</span>
                        ${i.item_name_cn ? `<span class="title-cn">${esc(i.item_name_cn)}</span>` : ''}
                    </div>
                    ${i.price_formatted ? `<span class="item-price">${esc(i.price_formatted)}</span>` : ''}
                </div>
            </div>`).join('');

        return `<div class="haidilao-grid">${cards}</div>`;
    }

    // Fetch and render a category
    function loadCategory(catId) {
        if (activeCatId === catId) return;
        activeCatId = catId;
        content.innerHTML = '<div class="state-loading"></div>';

        fetch(`${API}?action=get&type=${activeType}&category_id=${catId}&status=published`)
            .then(r => r.json())
            .then(json => {
                if (!json.success) throw new Error(json.message);
                content.innerHTML = renderGrid(json.data?.items ?? []);
                content.querySelectorAll('.img-wrap img').forEach(img => {
                    img.style.cursor = 'pointer';
                    img.onclick = () => openZoom(img.src);
                });
            })
            .catch(err => {
                content.innerHTML = `<div class="state-empty"><span class="state-empty-icon">⚠️</span><span>${err.message}</span></div>`;
            });
    }

    // Bind sidebar tab clicks
    function bindSidebar(sidebar) {
        sidebar?.querySelectorAll('.haidilao-sidebar-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                sidebar.querySelectorAll('.haidilao-sidebar-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                loadCategory(parseInt(tab.dataset.catId));
            });
        });
    }

    Object.values(sidebars).forEach(bindSidebar);

    // Switch between Grand / Sushi
    function switchMenu(type) {
        activeType  = type;
        activeCatId = null;

        Object.entries(sidebars).forEach(([k, s]) => { if (s) s.hidden = k !== type; });

        const sidebar  = sidebars[type];
        const firstTab = sidebar?.querySelector('.haidilao-sidebar-tab');

        if (firstTab) {
            sidebar.querySelectorAll('.haidilao-sidebar-tab').forEach(t => t.classList.remove('active'));
            firstTab.classList.add('active');
            loadCategory(parseInt(firstTab.dataset.catId));
        } else {
            content.innerHTML = '<div class="state-empty"><span class="state-empty-icon">📁</span><span>暂无分类</span></div>';
        }
    }

    topTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            topTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            switchMenu(tab.dataset.menu);
        });
    });

    // Hash routing: menu.php#sushi or menu.php#grand
    const hash    = location.hash.replace('#', '');
    const initTab = (hash === 'sushi' || hash === 'grand')
        ? document.querySelector(`.haidilao-top-tab[data-menu="${hash}"]`)
        : document.querySelector('.haidilao-top-tab.active');
    setTimeout(() => initTab?.click(), 50);

    // Mobile sidebar
    const mobileMenu    = document.getElementById('mobile-sidebar');
    const mobileOverlay = document.getElementById('mobile-sidebar-overlay');
    const openMobile    = () => { mobileMenu?.classList.add('active'); mobileOverlay?.classList.add('active'); document.body.style.overflow = 'hidden'; };
    const closeMobile   = () => { mobileMenu?.classList.remove('active'); mobileOverlay?.classList.remove('active'); document.body.style.overflow = ''; };

    document.getElementById('mobile-menu-btn')?.addEventListener('click', openMobile);
    document.getElementById('mobile-sidebar-close')?.addEventListener('click', closeMobile);
    mobileOverlay?.addEventListener('click', closeMobile);
    document.querySelectorAll('.sidebar-link').forEach(l => l.addEventListener('click', closeMobile));
})();
</script>

</body>
</html>