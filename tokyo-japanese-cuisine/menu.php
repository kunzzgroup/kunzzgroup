<?php
define('MENU_API_PATH', __DIR__ . '/../menu_api.php');
define('IMG_URL_PREFIX', '/uploads/');

if (file_exists(MENU_API_PATH)) {
    require_once MENU_API_PATH;
} else {
    die('找不到 menu_api.php，请检查路径配置。');
}

function fetchMenuData(string $type): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, category_name FROM menu_categories WHERE menu_type = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$type]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("SELECT item_name, item_name_cn, item_desc, price, image_path FROM menus WHERE category_id = ? AND status = 'published' ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$cat['id']]);
        $result[] = ['id' => $cat['id'], 'category_name' => $cat['category_name'], 'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
    return $result;
}

$grandCategories = fetchMenuData('grand');
$sushiCategories = fetchMenuData('sushi');

function panelId(string $type, int $catId): string { return 'panel-' . $type . '-' . $catId; }
function imgUrl(?string $path): string { if (!$path) return ''; if (str_starts_with($path, 'http')) return $path; return IMG_URL_PREFIX . ltrim($path, '/'); }
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="zh-CN" class="menu-html">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="logo/tokyologo.png">
    <title>Menu | TOKYO JAPANESE CUISINE</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+SC:wght@300;400;500;600&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
</head>

<body class="haidilao-menu-body">

    <!-- Navigation -->
    <nav id="nav" class="haidilao-nav">
        <div class="nav-container">
            <a href="tokyo.html" class="nav-logo-link">
                <div class="haidilao-logo-bg">
                    <img src="logo/tokyologo.png" alt="Tokyo" class="nav-logo">
                </div>
                <div class="nav-text-group">
                    <span class="nav-text primary">TOKYO JAPANESE</span>
                    <span class="nav-text secondary">CUISINE</span>
                </div>
            </a>
            <div class="nav-links">
                <a href="tokyo.html#about">关于我们</a>
                <a href="tokyo.html#culture">使命愿景</a>
                <a href="tokyo.html#featured">特色推荐</a>
                <a href="tokyo.html#location">我们在这</a>
            </div>
            <div class="nav-actions">
                <a href="tokyo.html#reserve" class="btn-primary nav-btn"
                    style="background-color: #a68a64; border-color: #a68a64; box-shadow: 0 4px 10px rgba(166, 138, 100, 0.3);">预订餐桌</a>
                <a href="tokyo.html" class="btn-secondary nav-btn"
                    style="color: #33322f; border-color: #33322f;">返回首页</a>
                <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar-overlay" id="mobile-sidebar-overlay"></div>
    <aside class="mobile-sidebar" id="mobile-sidebar">
        <button class="mobile-sidebar-close" id="mobile-sidebar-close" aria-label="Close menu">×</button>
        <div class="mobile-sidebar-content">
            <a href="tokyo.html#about" class="sidebar-link">关于我们</a>
            <a href="tokyo.html#culture" class="sidebar-link">使命愿景</a>
            <a href="tokyo.html#featured" class="sidebar-link">特色推荐</a>
            <a href="tokyo.html#location" class="sidebar-link">我们在这</a>
            <a href="tokyo.html#reserve" class="sidebar-link" style="color:#a68a64;font-weight:600;">预订餐桌</a>
            <a href="tokyo.html" class="sidebar-link" style="color:#a68a64;">返回首页</a>
        </div>
    </aside>

    <!-- Top Category Navigation (Grand Menu / Sushi Menu) -->
    <div class="haidilao-top-nav-container">
        <div class="haidilao-top-nav">
            <button class="haidilao-top-tab active" data-menu="grand" id="tab-grand">
                <div class="top-tab-icon">
                    <img src="grandmenu/menu2.png" alt="Grand Menu" />
                </div>
                <span>Grand Menu</span>
            </button>
            <button class="haidilao-top-tab" data-menu="sushi" id="tab-sushi">
                <div class="top-tab-icon">
                    <img src="sushimenu/menu1.png" alt="Sushi Menu" />
                </div>
                <span>Sushi Menu</span>
            </button>
        </div>
    </div>

    <!-- Main Layout -->
    <div class="haidilao-layout">

        <!-- Sidebar（动态生成分类按钮）-->
        <aside class="haidilao-sidebar">

            <!-- Grand Menu Sidebar -->
            <div class="sidebar-category" id="sidebar-grand">
                <?php foreach ($grandCategories as $idx => $cat) :
                    $pid = panelId('grand', $cat['id']);
                    $activeCls = $idx === 0 ? 'active' : '';
                ?>
                    <button class="haidilao-sidebar-tab <?= $activeCls ?>"
                        data-target="<?= e($pid) ?>">
                        <?= e($cat['category_name']) ?>
                    </button>
                <?php endforeach; ?>
                <?php if (empty($grandCategories)) : ?>
                    <p style="padding:16px;opacity:.5;font-size:13px;">暂无分类</p>
                <?php endif; ?>
            </div>

            <!-- Sushi Menu Sidebar (hidden by default) -->
            <div class="sidebar-category" id="sidebar-sushi" style="display:none;">
                <?php foreach ($sushiCategories as $idx => $cat) :
                    $pid = panelId('sushi', $cat['id']);
                    $activeCls = $idx === 0 ? 'active' : '';
                ?>
                    <button class="haidilao-sidebar-tab <?= $activeCls ?>"
                        data-target="<?= e($pid) ?>">
                        <?= e($cat['category_name']) ?>
                    </button>
                <?php endforeach; ?>
                <?php if (empty($sushiCategories)) : ?>
                    <p style="padding:16px;opacity:.5;font-size:13px;">暂无分类</p>
                <?php endif; ?>
            </div>

        </aside>

        <!-- Content Panels（动态生成菜品卡片）-->
        <main class="haidilao-content">

            <!-- ── Grand Menu Panels ── -->
            <?php foreach ($grandCategories as $idx => $cat) :
                $pid = panelId('grand', $cat['id']);
                $isActive = $idx === 0;
            ?>
                <div class="haidilao-panel <?= $isActive ? 'active' : '' ?>"
                    id="<?= e($pid) ?>"
                    <?= $isActive ? '' : 'hidden' ?>>

                    <?php if (empty($cat['items'])) : ?>
                        <div style="padding:48px;text-align:center;opacity:.4;">
                            <div style="font-size:40px;">🍱</div>
                            <p>此分类暂无已发布菜品</p>
                        </div>
                    <?php else : ?>
                        <div class="haidilao-grid">
                            <?php foreach ($cat['items'] as $item) :
                                $img = imgUrl($item['image_path']);
                                $name = e($item['item_name']);
                                $nameCn = e($item['item_name_cn'] ?? '');
                                $desc = e($item['item_desc'] ?? '');
                                $price = $item['price'] !== null ? 'RM ' . number_format((float)$item['price'], 2) : '';
                            ?>
                                <div class="haidilao-card">
                                    <div class="img-wrap">
                                        <?php if ($img) : ?>
                                            <img src="<?= e($img) ?>" alt="<?= $name ?>" loading="lazy" />
                                        <?php else : ?>
                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f5f0e8;color:#bbb;font-size:32px;">🍽️</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="info-wrap">
                                        <span class="title"><?= $name ?></span>
                                        <?php if ($nameCn) : ?>
                                            <span class="subtitle"><?= $nameCn ?></span>
                                        <?php endif; ?>
                                        <?php if ($desc) : ?>
                                            <p class="desc"><?= $desc ?></p>
                                        <?php endif; ?>
                                        <?php if ($price) : ?>
                                            <span class="price"><?= e($price) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- ── Sushi Menu Panels ── -->
            <?php foreach ($sushiCategories as $idx => $cat) :
                $pid = panelId('sushi', $cat['id']);
            ?>
                <div class="haidilao-panel" id="<?= e($pid) ?>" hidden>

                    <?php if (empty($cat['items'])) : ?>
                        <div style="padding:48px;text-align:center;opacity:.4;">
                            <div style="font-size:40px;">🍣</div>
                            <p>此分类暂无已发布菜品</p>
                        </div>
                    <?php else : ?>
                        <div class="haidilao-grid">
                            <?php foreach ($cat['items'] as $item) :
                                $img = imgUrl($item['image_path']);
                                $name = e($item['item_name']);
                                $nameCn = e($item['item_name_cn'] ?? '');
                                $desc = e($item['item_desc'] ?? '');
                                $price = $item['price'] !== null ? 'RM ' . number_format((float)$item['price'], 2) : '';
                            ?>
                                <div class="haidilao-card">
                                    <div class="img-wrap">
                                        <?php if ($img) : ?>
                                            <img src="<?= e($img) ?>" alt="<?= $name ?>" loading="lazy" />
                                        <?php else : ?>
                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f5f0e8;color:#bbb;font-size:32px;">🍽️</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="info-wrap">
                                        <span class="title"><?= $name ?></span>
                                        <?php if ($nameCn) : ?>
                                            <span class="subtitle"><?= $nameCn ?></span>
                                        <?php endif; ?>
                                        <?php if ($desc) : ?>
                                            <p class="desc"><?= $desc ?></p>
                                        <?php endif; ?>
                                        <?php if ($price) : ?>
                                            <span class="price"><?= e($price) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </main>
    </div>

    <!-- Zoom Modal -->
    <div id="zoom-modal" class="zoom-modal hidden">
        <div class="zoom-modal-content">
            <button class="zoom-modal-close" aria-label="Close">&times;</button>
            <img id="zoom-modal-image" src="" alt="Zoomed image" class="zoom-modal-image" />
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>