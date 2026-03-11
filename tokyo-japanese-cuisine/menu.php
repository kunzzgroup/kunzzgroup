<?php
// TOKYO JAPANESE CUISINE - Premium Menu Page
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="logo/tokyologo.png">
    <title>Menu | TOKYO JAPANESE CUISINE</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+SC:wght@300;400;500;600&display=swap" rel="stylesheet" />
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="menu.css" />
    
    <style>
        /* Fallback CSS Variables just in case styles.css misses them */
        :root {
            --color-bg: #f9f8f4;
            --color-bg-alt: #f0ede6;
            --color-text: #33322f;
            --color-text-light: #7c776b;
            --color-primary: #a68a64;
            --color-primary-dark: #8b7353;
        }

        /* Adjust footer margin since we don't use swiper here */
        .footer-section {
            margin-top: 60px;
            background-color: var(--color-bg-alt, #f0ede6);
        }
    </style>
</head>

<body class="luxury-menu-body">

    <!-- Luxury Header -->
    <header class="luxury-header">
        <div class="luxury-header-inner">
            <a href="tokyo.html" class="lux-logo-link">
                <img src="logo/tokyologo.png" alt="Tokyo Logo" class="lux-nav-logo">
                <div class="lux-logo-text">
                    <span class="lux-logo-primary">TOKYO JAPANESE</span>
                    <span class="lux-logo-secondary">CUISINE</span>
                </div>
            </a>
            <div class="lux-nav-links">
                <a href="tokyo.html#about-culture">关于我们</a>
                <a href="tokyo.html#mission-vision">文化+服务</a>
                <a href="menu.php" style="color: var(--color-primary); font-weight: 500;">精选菜单</a>
                <a href="tokyo.html#location">我们在这</a>
            </div>
            <div class="lux-nav-actions">
                <a href="tokyo.html#location" class="btn-lux-primary">联系我们</a>
            </div>
        </div>
    </header>

    <!-- Page Hero -->
    <div class="lux-page-hero">
        <div class="lux-page-label">FINE DINING MENU</div>
        <h1 class="lux-page-title">匠心料理 · 品越菜单</h1>
        <p class="lux-page-subtext">Authentic Japanese Culinary Experience</p>
        
        <div class="lux-menu-switcher">
            <button class="lux-switcher-btn active" data-type="grand">Grand Menu (热食菜单)</button>
            <span class="lux-switcher-divider">|</span>
            <button class="lux-switcher-btn" data-type="sushi">Sushi & Sashimi (寿司·刺身)</button>
        </div>
    </div>

    <!-- Category Bar -->
    <div class="lux-category-bar" id="category-bar">
        <!-- Categories injected via JS -->
    </div>

    <!-- Main Container -->
    <div class="lux-container" id="menu-container">
        <div class="lux-section-header">
            <h2 class="lux-section-title" id="current-category-title">All Items 全部</h2>
        </div>
        <div class="lux-food-grid" id="food-grid">
            <!-- Food cards injected via JS -->
        </div>
    </div>

    <!-- Zoom Modal -->
    <div id="zoom-modal" class="zoom-modal hidden">
        <button class="zoom-modal-close" aria-label="Close" style="position:absolute; top:30px; right:30px; background:none; border:none; color:white; font-size:40px; cursor:pointer;">&times;</button>
        <img id="zoom-modal-image" src="" alt="Zoomed Menu Item" style="max-width:90%; max-height:90%; border-radius:12px; object-fit:contain; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
    </div>

    <!-- Footer Section -->
    <section id="footer" class="footer-section">
        <div class="footer-container">
            <div class="footer-content">

                <!-- Logo & Brand -->
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="logo/tokyologo.png" alt="Tokyo Japanese Cuisine" style="height:40px; border-radius:50%; border:2px solid #c9a96e;">
                        <div>
                            <div class="footer-logo-name">TOKYO JAPANESE</div>
                            <div class="footer-logo-sub">CUISINE</div>
                        </div>
                    </div>
                    <p class="footer-tagline">正宗日式料理，匠心传递每一道风味</p>
                </div>

                <!-- Quick Links -->
                <div class="footer-links">
                    <h4>快速导航</h4>
                    <ul>
                        <li><a href="tokyo.html#about-culture">文化 &amp; 服务</a></li>
                        <li><a href="tokyo.html#mission-vision">使命愿景</a></li>
                        <li><a href="menu.php">特色推荐</a></li>
                        <li><a href="tokyo.html#location">我们在这</a></li>
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

            <div class="footer-bottom">
                <p>🌸 © 2026 Tokyo Japanese Cuisine. All Rights Reserved.</p>
            </div>
        </div>
    </section>

    <!-- Initialization Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Note: Update API_URL if backend folder is located elsewhere
            const API_URL = '../backend/menu_api.php';
            let currentType = 'grand';
            let currentCategoryId = null; // null means 'All'
            let categoriesData = [];
            let itemsData = [];

            const typeSwitchers = document.querySelectorAll('.lux-switcher-btn');
            const categoryBar = document.getElementById('category-bar');
            const foodGrid = document.getElementById('food-grid');
            const categoryTitle = document.getElementById('current-category-title');
            
            // Zoom Modal logic
            const zoomModal = document.getElementById('zoom-modal');
            const zoomModalImage = document.getElementById('zoom-modal-image');
            const zoomModalClose = document.querySelector('.zoom-modal-close');

            // --- Window and Zoom Modal Logic ---
            window.openZoomModal = function(src) {
                if (!src || src.includes('📸')) return; // ignore invalid images
                zoomModalImage.src = src;
                zoomModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };

            const closeZoomModal = () => {
                zoomModal.classList.add('hidden');
                zoomModalImage.src = '';
                document.body.style.overflow = '';
            };

            zoomModalClose.addEventListener('click', closeZoomModal);
            zoomModal.addEventListener('click', (e) => {
                if (e.target === zoomModal) closeZoomModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !zoomModal.classList.contains('hidden')) {
                    closeZoomModal();
                }
            });

            // --- Type Switcher Click Event (Grand vs Sushi) ---
            typeSwitchers.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    typeSwitchers.forEach(b => b.classList.remove('active'));
                    e.currentTarget.classList.add('active');
                    currentType = e.currentTarget.getAttribute('data-type');
                    // Update URL Hash automatically without redirect
                    history.replaceState(null, null, '#' + currentType);
                    currentCategoryId = null;
                    loadMenuData();
                });
            });

            // --- Read URL Hash Check (#grand or #sushi) ---
            function parseHashOnInit() {
                const hash = window.location.hash.replace('#', '');
                if (hash === 'sushi' || hash === 'grand') {
                    const btn = document.querySelector(`.lux-switcher-btn[data-type="${hash}"]`);
                    if (btn && !btn.classList.contains('active')) {
                        typeSwitchers.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        currentType = hash;
                    }
                }
            }

            // --- Fetch Data ---
            async function loadMenuData() {
                foodGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #9b8f7e; font-size: 1.1rem; padding: 60px 0; letter-spacing: 0.05em;">载入中 Loading...</div>';
                
                try {
                    // Fetch categories
                    const catParams = new URLSearchParams({ action: 'get_categories', type: currentType });
                    const catRes = await fetch(`${API_URL}?${catParams.toString()}`);
                    const catJson = await catRes.json();
                    if (catJson.success) {
                        categoriesData = catJson.data.categories;
                    } else {
                        categoriesData = [];
                    }

                    // Fetch items 
                    const itemParams = new URLSearchParams({ action: 'get', type: currentType, status: 'published' });
                    const itemRes = await fetch(`${API_URL}?${itemParams.toString()}`);
                    const itemJson = await itemRes.json();
                    if (itemJson.success) {
                        itemsData = itemJson.data.items;
                    } else {
                        itemsData = [];
                    }

                    renderCategoryTabs();
                    renderFoodGrid();

                } catch (error) {
                    console.error('Error fetching menu data:', error);
                    foodGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #9b8f7e; font-size: 1.1rem; padding: 60px 0;">暂时无法加载菜单数据，请检查网络或刷新页面以重试。<br><br>Failed to fetch menu data.</div>';
                }
            }

            // --- Render Categories (Tabs) ---
            function renderCategoryTabs() {
                categoryBar.innerHTML = '';
                
                // Add "All" Category Tab
                const btnAll = document.createElement('button');
                btnAll.className = `lux-cat-item ${currentCategoryId === null ? 'active' : ''}`;
                btnAll.textContent = 'All 全部';
                btnAll.addEventListener('click', () => {
                    currentCategoryId = null;
                    updateCategorySelection();
                    renderFoodGrid();
                });
                categoryBar.appendChild(btnAll);

                // Add Category Tabs from API
                categoriesData.forEach(cat => {
                    const btn = document.createElement('button');
                    btn.className = `lux-cat-item ${currentCategoryId == cat.id ? 'active' : ''}`;
                    btn.textContent = cat.category_name;
                    btn.addEventListener('click', () => {
                        currentCategoryId = cat.id;
                        updateCategorySelection();
                        renderFoodGrid();
                    });
                    categoryBar.appendChild(btn);
                });
            }

            // --- Update UI Tab Selection ---
            function updateCategorySelection() {
                const btns = categoryBar.querySelectorAll('.lux-cat-item');
                btns.forEach((btn, idx) => {
                    if (currentCategoryId === null && idx === 0) {
                        btn.classList.add('active');
                    } else if (currentCategoryId !== null && idx > 0 && categoriesData[idx-1].id == currentCategoryId) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            // --- Render Food Grid ---
            function renderFoodGrid() {
                // Determine Category Title
                if (currentCategoryId === null) {
                    categoryTitle.textContent = 'All Items 全部';
                } else {
                    const activeCat = categoriesData.find(c => c.id == currentCategoryId);
                    categoryTitle.textContent = activeCat ? activeCat.category_name : 'Menu Items';
                }

                // Apply Filters
                const filteredItems = currentCategoryId === null 
                    ? itemsData 
                    : itemsData.filter(item => item.category_id == currentCategoryId);

                foodGrid.innerHTML = '';

                if (filteredItems.length === 0) {
                    foodGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #9b8f7e; font-size: 1.1rem; padding: 60px 0;">该分类下暂无发布的内容。<br><br>No items in this category yet.</div>';
                    return;
                }

                // Render Item Cards
                filteredItems.forEach((item, index) => {
                    const card = document.createElement('div');
                    card.className = 'lux-food-card';
                    card.style.animationDelay = `${index * 0.05}s`;

                    // Handle missing fields gracefully
                    const imageUrl = item.image_url ? item.image_url : 'image/sushi-dish-asian-restaurant.jpg'; // fallback
                    
                    const pForm = item.price_formatted ? item.price_formatted : '';
                    const priceHtml = pForm ? `<div style="color:var(--color-primary); font-family: 'Playfair Display', serif; font-size:1.15rem; margin-top:12px; letter-spacing:0.04em;">${pForm}</div>` : '';
                    
                    const nameEn = item.item_name || 'N/A';
                    const nameCn = item.item_name_cn ? `<div style="font-size:0.85rem; color:#9b8f7e; margin-top:6px; letter-spacing:0.04em;">${item.item_name_cn}</div>` : '';
                    const desc   = item.item_desc ? `<div style="font-size:0.82rem; color:#888; margin-top:10px; line-height:1.6; letter-spacing:0.02em;">${item.item_desc}</div>` : '';

                    card.innerHTML = `
                        <div class="lux-card-img" onclick="openZoomModal('${imageUrl}')">
                            <img src="${imageUrl}" alt="${nameEn}" loading="lazy" onerror="this.src='image/sushi-dish-asian-restaurant.jpg'">
                        </div>
                        <div class="lux-card-info">
                            <h3 class="lux-food-title">${nameEn}</h3>
                            ${nameCn}
                            ${desc}
                            ${priceHtml}
                        </div>
                    `;
                    foodGrid.appendChild(card);
                });
            }

            // Init call
            parseHashOnInit();
            loadMenuData();
        });
    </script>
</body>
</html>
