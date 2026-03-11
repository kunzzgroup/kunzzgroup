<?php
// TOKYO JAPANESE CUISINE - Dynamic Haidilao-style Menu Page
?>
<!DOCTYPE html>
<html lang="zh-CN" class="menu-html">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="image/tokyologo.png">
    <title>Menu | TOKYO JAPANESE CUISINE</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+SC:wght@300;400;500;600&display=swap" rel="stylesheet" />
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="menu.css" />
    
    <style>
        /* Fallback adjustments if needed */
        .loading-text { padding: 40px; text-align: center; color: #7c776b; font-size: 1.1rem; }
    </style>
</head>

<body class="haidilao-menu-body">

    <!-- Navigation -->
    <nav id="nav" class="haidilao-nav">
        <div class="nav-container">
            <a href="tokyo" class="nav-logo-link">
                <div class="haidilao-logo-bg">
                    <img src="image/tokyologo.png" alt="Tokyo" class="nav-logo">
                </div>
                <div class="nav-text-group">
                    <span class="nav-text primary">TOKYO JAPANESE</span>
                    <span class="nav-text secondary">CUISINE</span>
                </div>
            </a>
            <div class="nav-links">
                <a href="tokyo#about-culture">关于我们</a>
                <a href="tokyo#mission-vision">文化+服务</a>
                <a href="tokyo#featured" style="color: #a68a64; font-weight: 500;">特色推荐</a>
                <a href="tokyo#location">我们在这</a>
            </div>
            <div class="nav-actions">
                <a href="tokyo#location" class="btn-primary nav-btn"
                    style="background-color: #a68a64; border-color: #a68a64; box-shadow: 0 4px 10px rgba(166, 138, 100, 0.3);">联系我们</a>
                <a href="tokyo" class="btn-secondary nav-btn"
                    style="color: #33322f; border-color: #33322f;">返回首页</a>
                <!-- Mobile-only hamburger button -->
                <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Minimalist Right Sidebar Mobile Menu -->
    <div class="mobile-sidebar-overlay" id="mobile-sidebar-overlay"></div>
    <aside class="mobile-sidebar" id="mobile-sidebar">
        <button class="mobile-sidebar-close" id="mobile-sidebar-close" aria-label="Close menu">×</button>
        <div class="mobile-sidebar-content">
            <a href="tokyo#about-culture" class="sidebar-link">关于我们</a>
            <a href="tokyo#mission-vision" class="sidebar-link">文化+服务</a>
            <a href="tokyo#featured" class="sidebar-link" style="color: #a68a64; font-weight: 600;">特色推荐</a>
            <a href="tokyo#location" class="sidebar-link">我们在这</a>
            <a href="tokyo" class="sidebar-link">返回首页</a>
        </div>
    </aside>

    <!-- Top Category Navigation (Fixed Types) -->
    <div class="haidilao-top-nav-container">
        <div class="haidilao-top-nav">
            <button class="haidilao-top-tab active" data-menu="grand" id="tab-grand">
                <div class="top-tab-icon">
                    <img src="image/menu2.png" alt="Grand Menu" />
                </div>
                <span>Grand Menu</span>
            </button>
            <button class="haidilao-top-tab" data-menu="sushi" id="tab-sushi">
                <div class="top-tab-icon">
                    <img src="image/menu1.png" alt="Sushi Menu" />
                </div>
                <span>Sushi Menu</span>
            </button>
        </div>
    </div>

    <!-- Main Dynamic Layout -->
    <div class="haidilao-layout">
        <!-- Dynamic Sidebar for Categories -->
        <aside class="haidilao-sidebar">
            <div class="sidebar-category" id="dynamic-sidebar" style="display:flex;">
                <!-- Buttons injected via JS -->
            </div>
        </aside>

        <!-- Dynamic Content Panels for Food Items -->
        <main class="haidilao-content">
            <div class="haidilao-panel active" id="dynamic-panel">
                <div class="haidilao-grid" id="dynamic-grid">
                    <div class="loading-text">载入中 Loading...</div>
                    <!-- Cards injected via JS -->
                </div>
            </div>
        </main>
    </div>

    <!-- Zoom Modal -->
    <div id="zoom-modal" class="zoom-modal hidden">
        <div class="zoom-modal-content">
            <button class="zoom-modal-close" aria-label="Close" style="font-size:30px;">&times;</button>
            <img id="zoom-modal-image" src="" alt="Zoomed image" class="zoom-modal-image" style="max-height:90vh; border-radius:12px;"/>
        </div>
    </div>

    <!-- Footer Section -->
    <section id="footer" class="footer-section">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Logo & Brand -->
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="image/tokyologo.png" alt="Tokyo Japanese Cuisine" style="height:40px; border-radius:50%; border:2px solid #c9a96e;">
                        <div><div class="footer-logo-name">TOKYO JAPANESE</div><div class="footer-logo-sub">CUISINE</div></div>
                    </div>
                    <p class="footer-tagline">正宗日式料理，匠心传递每一道风味</p>
                </div>
                <!-- Quick Links -->
                <div class="footer-links">
                    <h4>快速导航</h4>
                    <ul>
                        <li><a href="tokyo#about-culture">文化 &amp; 服务</a></li>
                        <li><a href="tokyo#mission-vision">使命愿景</a></li>
                        <li><a href="menu">特色推荐</a></li>
                        <li><a href="tokyo#location">我们在这</a></li>
                    </ul>
                </div>
                <!-- Contact -->
                <div class="footer-contact">
                    <h4>联系我们</h4>
                    <p>📍 总店：Mid Valley Southkey, JB</p>
                    <p>📞 <a href="tel:+60197108090">+60 19-710 8090</a></p>
                    <p>📍 分店：Paradigm Mall, JB</p>
                    <p>📞 <a href="tel:+60187738090">+60 18-773 8090</a></p>
                </div>
                <!-- Hours -->
                <div class="footer-hours">
                    <h4>营业时间</h4>
                    <p>周一 – 周五：11:00 – 22:00</p>
                    <p>周六 – 周日：10:00 – 22:30</p>
                    <p>节假日照常营业</p>
                </div>
            </div>
            <div class="footer-bottom"><p>🌸 © 2026 Tokyo Japanese Cuisine. All Rights Reserved.</p></div>
        </div>
    </section>

    <!-- Dynamic Fetch Logic Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Dynamic API URL for Local Testing & Production
            const isLocalhost = window.location.hostname.includes('localhost') || window.location.hostname.includes('127.0.0.1');
            const API_URL = isLocalhost 
                ? 'http://localhost/kunzzgroup/backend/menu_api.php'
                : 'https://kunzzgroup.com/backend/menu_api.php';
            let currentType = 'grand'; // 'grand' or 'sushi'
            let categoriesData = [];
            let itemsData = [];
            
            const dynamicSidebar = document.getElementById('dynamic-sidebar');
            const dynamicGrid = document.getElementById('dynamic-grid');
            const topTabs = document.querySelectorAll('.haidilao-top-tab');

            // --- Window and Zoom Modal Logic ---
            const zoomModal = document.getElementById('zoom-modal');
            const zoomModalImage = document.getElementById('zoom-modal-image');
            const closeBtn = document.querySelector('.zoom-modal-close');

            window.openZoomModal = function(src) {
                if (!src || src.includes('📸')) return; // ignore missing pics
                zoomModalImage.src = src;
                zoomModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };

            const closeZoomModal = () => {
                zoomModal.classList.add('hidden');
                zoomModalImage.src = '';
                document.body.style.overflow = '';
            };

            if (closeBtn) closeBtn.onclick = closeZoomModal;
            if (zoomModal) zoomModal.onclick = (e) => { if (e.target === zoomModal) closeZoomModal(); }
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !zoomModal.classList.contains('hidden')) closeZoomModal(); });

            // --- Top Tab Switcher Logic ---
            topTabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    topTabs.forEach(t => t.classList.remove('active'));
                    const clickedTab = e.currentTarget;
                    clickedTab.classList.add('active');
                    
                    currentType = clickedTab.getAttribute('data-menu');
                    history.replaceState(null, null, '#' + currentType);
                    
                    loadMenuData(); // Fetch completely fresh category and item data based on type
                });
            });

            // Read URL Hash Check (#grand or #sushi)
            function initHash() {
                const hash = window.location.hash.replace('#', '');
                if (hash === 'sushi' || hash === 'grand') {
                    const btn = document.querySelector(`.haidilao-top-tab[data-menu="${hash}"]`);
                    if (btn && !btn.classList.contains('active')) {
                        topTabs.forEach(t => t.classList.remove('active'));
                        btn.classList.add('active');
                        currentType = hash;
                    }
                }
            }

            // --- Fetch Both Category and Item Data ---
            async function loadMenuData() {
                dynamicSidebar.innerHTML = '';
                dynamicGrid.innerHTML = '<div class="loading-text">载入中 Loading...</div>';

                try {
                    // Fetch categories
                    const catParams = new URLSearchParams({ action: 'get_categories', type: currentType });
                    const catRes = await fetch(`${API_URL}?${catParams.toString()}`);
                    const catJson = await catRes.json();
                    categoriesData = catJson.success ? catJson.data.categories : [];

                    // Fetch items
                    const itemParams = new URLSearchParams({ action: 'get', type: currentType, status: 'published' });
                    const itemRes = await fetch(`${API_URL}?${itemParams.toString()}`);
                    const itemJson = await itemRes.json();
                    itemsData = itemJson.success ? itemJson.data.items : [];

                    renderSidebar();
                } catch (error) {
                    console.error('Error fetching menu data:', error);
                    dynamicGrid.innerHTML = '<div class="loading-text">无法加载菜单数据，请检查网络<br><br>Failed to fetch menu data.</div>';
                }
            }

            // --- Render Left Sidebar Dynamically ---
            function renderSidebar() {
                dynamicSidebar.innerHTML = '';

                if (categoriesData.length === 0) {
                    dynamicSidebar.innerHTML = '<div style="padding:20px; color:#999; text-align:center;">暂无分类<br>No Categories</div>';
                    dynamicGrid.innerHTML = '<div class="loading-text">该菜单目前没有上架的商品</div>';
                    return;
                }

                categoriesData.forEach((cat, index) => {
                    const btn = document.createElement('button');
                    btn.className = `haidilao-sidebar-tab ${index === 0 ? 'active' : ''}`; // auto-select first
                    btn.textContent = cat.category_name;
                    btn.setAttribute('data-id', cat.id);
                    
                    btn.addEventListener('click', () => {
                        // Update active state in sidebar
                        const allSidebarBtns = dynamicSidebar.querySelectorAll('.haidilao-sidebar-tab');
                        allSidebarBtns.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        
                        // Render filtered grid
                        renderFoodGrid(cat.id);
                    });

                    dynamicSidebar.appendChild(btn);
                });

                // Render the first category's items by default
                if (categoriesData.length > 0) {
                    renderFoodGrid(categoriesData[0].id);
                }
            }

            // --- Render Food Grid Iteration ---
            function renderFoodGrid(categoryId) {
                dynamicGrid.innerHTML = '';

                // Filter items by category
                const filteredItems = itemsData.filter(item => item.category_id == categoryId);

                if (filteredItems.length === 0) {
                    dynamicGrid.innerHTML = '<div class="loading-text">该分类下暂无发布的内容<br><br>No items in this category.</div>';
                    return;
                }

                filteredItems.forEach((item) => {
                    const card = document.createElement('div');
                    card.className = 'haidilao-card';
                    
                    const imageUrl = item.image_url ? item.image_url : 'image/sushi-dish-asian-restaurant.jpg';
                    const nameEn = item.item_name || 'N/A';
                    const nameCn = item.item_name_cn ? `<div style="font-size:0.8rem; color:#777; margin-top:2px;">${item.item_name_cn}</div>` : '';
                    const priceForm = item.price_formatted ? `<div style="color:#a68a64; font-weight: 500; font-family: 'DM Serif Display', serif; margin-top:6px; font-size: 1.1rem;">${item.price_formatted}</div>` : '';

                    card.innerHTML = `
                        <div class="img-wrap" onclick="openZoomModal('${imageUrl}')">
                            <img src="${imageUrl}" alt="${nameEn}" loading="lazy" onerror="this.src='image/sushi-dish-asian-restaurant.jpg'">
                        </div>
                        <div class="info-wrap" style="flex-direction: column; align-items: flex-start; padding-bottom: 15px;">
                            <span class="title" style="margin-bottom:0;">${nameEn}</span>
                            ${nameCn}
                            ${priceForm}
                        </div>
                    `;
                    dynamicGrid.appendChild(card);
                });
            }

            // --- Minimalist Mobile Sidebar Navigation Logic ---
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileSidebarNav = document.getElementById('mobile-sidebar');
            const mobileSidebarOverlay = document.getElementById('mobile-sidebar-overlay');
            const mobileSidebarClose = document.getElementById('mobile-sidebar-close');

            if (mobileMenuBtn && mobileSidebarNav) {
                mobileMenuBtn.addEventListener('click', () => {
                    mobileSidebarNav.classList.add('active');
                    mobileSidebarOverlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });

                const closeMobileNav = () => {
                    mobileSidebarNav.classList.remove('active');
                    mobileSidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                };

                mobileSidebarClose.addEventListener('click', closeMobileNav);
                mobileSidebarOverlay.addEventListener('click', closeMobileNav);
            }

            // Init Process
            initHash();
            loadMenuData();
        });
    </script>
</body>
</html>
