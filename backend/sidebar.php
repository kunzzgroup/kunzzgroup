<?php
// Ensure variables exist when included from different pages
$username = isset($username) ? $username : (isset($_SESSION['username']) ? $_SESSION['username'] : 'User');
$position = isset($position) ? $position : ((isset($_SESSION['position']) && !empty($_SESSION['position'])) ? $_SESSION['position'] : 'User');
$avatarLetter = isset($avatarLetter) ? $avatarLetter : strtoupper(substr($username, 0, 1));
$canViewAnalytics = isset($canViewAnalytics) ? $canViewAnalytics : true;
?>

<?php
session_start();

// 超时时间（秒）
define('SESSION_TIMEOUT', 60);

// 如果 session 存在，检查是否过期
if (isset($_SESSION['user_id'])) {

    // 如果超过 1 分钟没活动，并且没有记住我
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) &&
        (!isset($_COOKIE['remember_token']) || $_COOKIE['remember_token'] !== '1')
    ) {
        // 清除 session
        session_unset();
        session_destroy();

        // 清除 cookie（可选）
        setcookie('user_id', '', time() - 60, "/");
        setcookie('username', '', time() - 60, "/");
        setcookie('position', '', time() - 60, "/");
        setcookie('remember_token', '', time() - 60, "/");

        // 跳转登录页
        header("Location: index.php");
        exit();
    }

    // 更新活动时间戳
    $_SESSION['last_activity'] = time();

} elseif (
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    // 记住我逻辑（恢复 session）
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['last_activity'] = time();
} else {
    // 没有 session，也没有有效 cookie
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
// 修改这行：检查position是否为空或null
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';
$avatarLetter = strtoupper($username[0]);
// 添加权限检查 - 检查用户注册码
$canViewAnalytics = true; // 默认可以查看
// 新增：细粒度侧边栏权限，默认全部可见
$canSeeAnalytics = true;
$canSeeHR = true;
$canSeeResource = true;
$canSeeVisual = true;
$canSeeBrand = true;
$submenuVisibility = [
    'analytics' => [
        'kpi_report' => true,
        'kpi_upload' => true,
    ],
    'hr' => [
        'staff_management' => true,
        'schedule' => true,
    ],
    'resource' => [
        'stock_inventory' => true,
        'dishware' => true,
        'price_comparison' => true,
    ],
    'visual' => [
        'bgmusic' => true,
        'homepage1' => true,
        'about1' => true,
        'about4' => true,
        'tokyo1' => true,
        'tokyo5' => true,
        'join1' => true,
        'join2' => true,
        'join3' => true,
    ],
    'brand' => [
        'kunzz_holdings' => true,
        'tokyo_cuisine' => true,
        'tokyo_izakaya' => true,
        'j1' => true,
        'j2' => true,
        'j3' => true,
    ],
];
if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'u857194726_kunzzgroup';
    $dbuser = 'u857194726_kunzzgroup';
    $dbpass = 'Kholdings1688@';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $restrictedCodes = ['SUPPORT88','PHOTO001','AZGQOY','NR7FNW']; // 限制访问的注册码
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT registration_code FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userCode = $stmt->fetchColumn();
        
        $canViewAnalytics = !($userCode && in_array($userCode, $restrictedCodes));

        // 加载基于用户的侧边栏权限（如不存在则默认全开）
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_sidebar_permissions (
                user_id INT PRIMARY KEY,
                permissions_json TEXT NULL,
                page_permissions_json TEXT NULL,
                submenu_permissions_json TEXT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN page_permissions_json TEXT NULL"); } catch (Throwable $e) { /* ignore */ }
            try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN submenu_permissions_json TEXT NULL"); } catch (Throwable $e) { /* ignore */ }
            try { $pdo->exec("ALTER TABLE user_sidebar_permissions ADD COLUMN brand_permissions_json TEXT NULL"); } catch (Throwable $e) { /* ignore */ }
            $permStmt = $pdo->prepare("SELECT permissions_json, submenu_permissions_json, brand_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
            $permStmt->execute([$userId]);
            $permRow = $permStmt->fetch(PDO::FETCH_ASSOC);
            // 如果没有权限记录，默认全部开启
            if (!$permRow || empty($permRow['permissions_json'])) {
                // 保持默认值（全部为true）
            } else {
                $list = json_decode($permRow['permissions_json'], true);
                if (is_array($list) && !empty($list)) {
                    $map = array_flip($list);
                    $canSeeAnalytics = isset($map['analytics']);
                    $canSeeHR = isset($map['hr']);
                    $canSeeResource = isset($map['resource']);
                    $canSeeVisual = isset($map['visual']);
                    $canSeeBrand = isset($map['brand']);
                }
                // 如果权限数组为空，保持默认全部开启
            }
            $subList = [];
            // 如果没有submenu_permissions_json，默认全部开启（保持初始值）
            if ($permRow && isset($permRow['submenu_permissions_json']) && !empty($permRow['submenu_permissions_json'])) {
                $subList = json_decode($permRow['submenu_permissions_json'], true);
                if (is_array($subList) && !empty($subList)) {
                    foreach ($submenuVisibility as $section => &$items) {
                        // 对于brand，需要特殊处理，因为它的结构不同
                        if ($section === 'brand') {
                            continue; // 稍后单独处理
                        }
                        // 对于visual，如果主模块可见，则所有子选项都可见
                        if ($section === 'visual') {
                            continue; // 稍后单独处理
                        }
                        $allowed = isset($subList[$section]) && is_array($subList[$section]) ? $subList[$section] : [];
                        // 如果该section的权限数组为空，保持默认全部开启
                        if (empty($allowed)) {
                            continue;
                        }
                        foreach ($items as $key => $value) {
                            $items[$key] = in_array($key, $allowed, true);
                        }
                    }
                    unset($items);
                    
                    // 处理visual权限：如果主模块可见，则所有子选项都可见
                    if ($canSeeVisual) {
                        foreach ($submenuVisibility['visual'] as $key => $value) {
                            $submenuVisibility['visual'][$key] = true;
                        }
                    }
                }
                // 如果权限数组为空或无效，保持默认全部开启
            }
            // 如果没有submenu_permissions_json，但主模块可见，则所有visual子选项都可见
            if ($canSeeVisual) {
                foreach ($submenuVisibility['visual'] as $key => $value) {
                    $submenuVisibility['visual'][$key] = true;
                }
            }
            
            // 处理brand权限（三级和四级）
            // 首先从submenu_permissions_json读取二级分类权限
            $brandSubmenu = isset($subList['brand']) && is_array($subList['brand']) && !empty($subList['brand']) ? $subList['brand'] : [];
            // 如果brand权限数组为空，保持默认全部开启
            if (!empty($brandSubmenu)) {
                $submenuVisibility['brand']['kunzz_holdings'] = in_array('kunzz_holdings', $brandSubmenu, true);
                $submenuVisibility['brand']['tokyo_cuisine'] = in_array('tokyo_cuisine', $brandSubmenu, true);
                $submenuVisibility['brand']['tokyo_izakaya'] = in_array('tokyo_izakaya', $brandSubmenu, true);
            }
            // 如果brandSubmenu为空，保持默认值（全部为true）
            
            // 然后从brand_permissions_json读取三级和四级权限（店面权限）
            // 初始化店面的schedule权限标志（默认为true，如果有权限数据才会被设置为false）
            $submenuVisibility['brand']['j1_schedule'] = true;
            $submenuVisibility['brand']['j2_schedule'] = true;
            $submenuVisibility['brand']['j3_schedule'] = true;
            
            if ($permRow && isset($permRow['brand_permissions_json']) && !empty($permRow['brand_permissions_json'])) {
                $brandPerms = json_decode($permRow['brand_permissions_json'], true);
                if (is_array($brandPerms)) {
                    
                    // 检查三级分类（店面）- 兼容旧格式（数组）和新格式（对象）
                    if (isset($brandPerms['tokyo_cuisine'])) {
                        if (is_array($brandPerms['tokyo_cuisine']) && isset($brandPerms['tokyo_cuisine'][0])) {
                            // 旧格式：索引数组
                            $submenuVisibility['brand']['j1'] = in_array('j1', $brandPerms['tokyo_cuisine'], true);
                            $submenuVisibility['brand']['j2'] = in_array('j2', $brandPerms['tokyo_cuisine'], true);
                            // 旧格式没有schedule权限，保持默认开启
                        } else if (is_array($brandPerms['tokyo_cuisine'])) {
                            // 新格式：关联数组（对象），检查是否有权限
                            // 如果对象为空，保持默认开启（全部为true）
                            if (empty($brandPerms['tokyo_cuisine'])) {
                                // 对象为空，保持默认值（全部为true）
                            } else {
                                // 对象不为空，检查具体权限
                                $submenuVisibility['brand']['j1'] = isset($brandPerms['tokyo_cuisine']['j1']);
                                $submenuVisibility['brand']['j2'] = isset($brandPerms['tokyo_cuisine']['j2']);
                                
                                // 检查四级权限（schedule）- J1
                                if (isset($brandPerms['tokyo_cuisine']['j1']) && is_array($brandPerms['tokyo_cuisine']['j1'])) {
                                    // 如果数组不为空，检查是否包含schedule
                                    if (!empty($brandPerms['tokyo_cuisine']['j1'])) {
                                        $submenuVisibility['brand']['j1_schedule'] = in_array('schedule', $brandPerms['tokyo_cuisine']['j1'], true);
                                    }
                                    // 如果数组为空，保持默认开启（true）
                                }
                                
                                // 检查四级权限（schedule）- J2
                                if (isset($brandPerms['tokyo_cuisine']['j2']) && is_array($brandPerms['tokyo_cuisine']['j2'])) {
                                    // 如果数组不为空，检查是否包含schedule
                                    if (!empty($brandPerms['tokyo_cuisine']['j2'])) {
                                        $submenuVisibility['brand']['j2_schedule'] = in_array('schedule', $brandPerms['tokyo_cuisine']['j2'], true);
                                    }
                                    // 如果数组为空，保持默认开启（true）
                                }
                            }
                        }
                        // 如果没有tokyo_cuisine数据，保持默认值（全部为true）
                    }
                    // 如果没有tokyo_cuisine，保持默认值（j1和j2为true）
                    
                    if (isset($brandPerms['tokyo_izakaya'])) {
                        if (is_array($brandPerms['tokyo_izakaya']) && isset($brandPerms['tokyo_izakaya'][0])) {
                            // 旧格式：索引数组
                            $submenuVisibility['brand']['j3'] = in_array('j3', $brandPerms['tokyo_izakaya'], true);
                            // 旧格式没有schedule权限，保持默认开启
                        } else if (is_array($brandPerms['tokyo_izakaya'])) {
                            // 新格式：关联数组（对象）
                            // 如果对象为空，保持默认开启（全部为true）
                            if (empty($brandPerms['tokyo_izakaya'])) {
                                // 对象为空，保持默认值（全部为true）
                            } else {
                                // 对象不为空，检查具体权限
                                $submenuVisibility['brand']['j3'] = isset($brandPerms['tokyo_izakaya']['j3']);
                                
                                // 检查四级权限（schedule）- J3
                                if (isset($brandPerms['tokyo_izakaya']['j3']) && is_array($brandPerms['tokyo_izakaya']['j3'])) {
                                    // 如果数组不为空，检查是否包含schedule
                                    if (!empty($brandPerms['tokyo_izakaya']['j3'])) {
                                        $submenuVisibility['brand']['j3_schedule'] = in_array('schedule', $brandPerms['tokyo_izakaya']['j3'], true);
                                    }
                                    // 如果数组为空，保持默认开启（true）
                                }
                            }
                        }
                        // 如果没有tokyo_izakaya数据，保持默认值（全部为true）
                    }
                    // 如果没有tokyo_izakaya，保持默认值（j3为true）
                }
            }
            
            // 如果没有brand_permissions_json，保持默认全部开启（不需要额外处理，初始值已经是true）
        } catch (Exception $e) {
            $canSeeAnalytics = $canSeeHR = $canSeeResource = $canSeeVisual = $canSeeBrand = true;
        }
    } catch (PDOException $e) {
        $canViewAnalytics = true; // 出错时默认允许访问
        $canSeeAnalytics = $canSeeHR = $canSeeResource = $canSeeVisual = $canSeeBrand = true;
    }
}
// 如果主模块不可见，则对应子选项全部关闭
if (!$canSeeAnalytics) {
    foreach ($submenuVisibility['analytics'] as &$flag) { $flag = false; }
    unset($flag);
}
if (!$canSeeHR) {
    foreach ($submenuVisibility['hr'] as &$flag) { $flag = false; }
    unset($flag);
}
if (!$canSeeResource) {
    foreach ($submenuVisibility['resource'] as &$flag) { $flag = false; }
    unset($flag);
}
if (!$canSeeVisual) {
    foreach ($submenuVisibility['visual'] as &$flag) { $flag = false; }
    unset($flag);
}
if (!$canSeeBrand) {
    foreach ($submenuVisibility['brand'] as &$flag) { $flag = false; }
    unset($flag);
}
?>


<style>
/*左边的选项bar*/
.informationmenu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(255, 255, 255, 1);
    z-index: 999;
    /* 修改：默认显示遮罩层 */
    opacity: 1;
    visibility: visible;
    transition: all 0.3s ease;
    pointer-events: none; /* 添加这行，让遮罩层不阻止点击 */
}

.informationmenu-overlay.show {
    opacity: 1;
    visibility: visible;
}

/* 如果你想要隐藏遮罩层，可以添加这个类 */
.informationmenu-overlay.hide {
    opacity: 0;
    visibility: hidden;
}

.informationmenu {
    width: clamp(140px, 13.02vw, 250px);
    height: 100vh;
    background-color: white;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
    position: fixed;
    left: 0;
    top: 0;
    overflow: visible;
    z-index: 1000;
    /* 修改：默认显示菜单（移除负的transform） */
    transform: translateX(0);
    transition: transform 0.3s ease;
    /* 添加 flexbox 布局 */
    display: flex;
    flex-direction: column;
}

.informationmenu.show {
    transform: translateX(0);
}

/* 如果你想要隐藏菜单，可以添加这个类 */
.informationmenu.hide {
    transform: translateX(-100%);
}

/* 其余样式保持不变 */
.informationmenu-header {
    padding: clamp(10px, 1.25vw, 24px) clamp(12px, 1.25vw, 24px);
    border-bottom: 1px solid #d5d5d5;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.informationmenu-logo {
    width: 30px;
    height: 30px;
    background-color: #333;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.informationmenu-close-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #ddd;
    background-color: white;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #666;
    transition: all 0.2s ease;
}

.informationmenu-close-btn:hover {
    background-color: #f5f5f5;
}

.informationmenu-content {
    /* 移除固定高度，让它自动调整 */
    overflow-y: auto;
    /* 添加 flex-grow 让内容区域占据剩余空间 */
    flex: 1;
    display: flex;
    flex-direction: column;
}

.informationmenu-section {
    padding: clamp(4px, 0.42vw, 8px) 0px;
    /* 让所有section占据剩余空间，但footer会被推到底部 */
}

.informationmenu-section-title {
    padding: clamp(8px, 0.63vw, 12px) clamp(14px, 1.04vw, 20px);
    font-size: clamp(8px, 0.84vw, 16px);
    font-weight: bold;
    color: #000000ff;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
}

.informationmenu-section-title:hover:not(.active) {
    background-color: #ffffffff;
    color: #ff5c00;
    margin: 0;
}

/* 禁用active状态下的hover效果 */
.informationmenu-section-title.active:hover {
    background-color: #ff5c00;
    color: #ffffff;
}

.informationmenu-section-title.active:hover .section-icon {
    filter: brightness(0) invert(1);
}

.informationmenu-section-title.active {
    background-color: #ff5c00;
    color: #ffffff;
    margin: 0px 4px;
    border-radius: 10px;
    box-shadow: 0px 2px 6px #ff7a2e;
}

.section-arrow {
    font-size: 12px;
    transition: transform 0.3s ease;
}

.informationmenu-section-title.active .section-arrow {
    transform: rotate(90deg);
}

/* 下拉显示的菜单项区域 */
.dropdown-menu-items {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.1s ease;
    background: transparent;
    border-radius: clamp(4px, 0.42vw, 8px);
    margin: 0 10px;
}

.dropdown-menu-items.show {
    max-height: 500px;
    border-radius: clamp(4px, 0.42vw, 8px);
    transition: max-height 0.3s ease;
    margin: 4px 10px;
}

.menu-item-wrapper {
    position: relative;
}

.informationmenu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: clamp(4px, 0.52vw, 10px) clamp(20px, 1.56vw, 30px);
    color: #000000ff;
    text-decoration: none;
    font-size: clamp(6px, 0.73vw, 14px);
    font-weight: bold;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    border-left: 2px solid #ff5c00;
}

.informationmenu-item:hover {
    background-color: #e3f2fd;
    color: #1976d2;
}

.informationmenu-arrow {
    font-size: 12px;
    color: #999;
    transition: transform 0.2s ease;
}

.informationmenu-item:hover .informationmenu-arrow {
    transform: translateX(3px);
}

/* 子菜单 - 固定定位覆盖屏幕 */
.submenu {
    position: fixed;
    left: clamp(140px, 13.02vw, 250px);
    top: 0;
    width: clamp(170px, 15.63vw, 300px);
    height: 100vh;
    background: linear-gradient(135deg, #ff8019 0%, #ffb342 100%);
    color: white;
    border-radius: 0 12px 12px 0;
    box-shadow: 8px 0 40px rgba(0,0,0,0.4);
    z-index: 3000;
    opacity: 0;
    visibility: hidden;
    transform: translateX(-50px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
    overflow-y: auto;
}

/* HOVER时显示 - 关键样式，包括子菜单hover */
.menu-item-wrapper:hover .submenu,
.submenu:hover {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
    pointer-events: auto;
}

/* 保持菜单项高亮当子菜单被hover时 */
.menu-item-wrapper:hover .informationmenu-item,
.submenu:hover ~ .informationmenu-item {
    background-color: transparent;
    color: #ff5c00;
    text-shadow: 0 1px 1px rgba(255, 92, 0, 0.5);
}

.submenu-header {
    padding: clamp(25px, 1.98vw, 38px) clamp(20px, 1.67vw, 32px) clamp(17px, 1.67vw, 32px);
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.2);
    position: sticky;
    top: 0;
    z-index: 1;
}

.submenu-title {
    font-size: clamp(10px, 0.84vw, 16px);
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: white;
}

.submenu-content {
    padding: clamp(8px, 1.04vw, 20px) 0;
}

.submenu-item {
    display: flex;
    align-items: center;
    padding: clamp(10px, 0.94vw, 18px) clamp(15px, 1.3vw, 25px);
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    font-size: clamp(8px, 0.84vw, 16px);
    transition: all 0.3s ease;
    cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    margin: 0 clamp(10px, 0.78vw, 15px);
    border-radius: 8px;
    margin-bottom: clamp(0px, 0.26vw, 5px);
}

.submenu-item:last-child {
    border-bottom: none;
}

.submenu-item:hover {
    background: rgba(255,255,255,0.15);
    color: white;
    padding-left: clamp(10px, 1.56vw, 30px);
    transform: translateX(5px);
}

.submenu-item::before {
    content: '→';
    margin-right: 10px;
    font-weight: bold;
    transition: transform 0.3s ease;
}

.submenu-item:hover::before {
    transform: translateX(5px);
}

/* 展开箭头样式 */
.expand-arrow {
    font-size: 12px;
    transition: transform 0.3s ease;
    margin-left: auto;
}

.submenu-item.expandable.expanded .expand-arrow {
    transform: rotate(90deg);
}

/* 子选项容器 */
.sub-options {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: rgba(255,255,255,0.1);
    margin: 0 15px 5px;
    border-radius: 8px;
}

.sub-options.expanded {
    max-height: 500px;
}

.sub-option {
    display: flex;
    align-items: center;
    padding: 12px 25px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
    cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.sub-option:last-child {
    border-bottom: none;
}

.sub-option:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    padding-left: 35px;
}

.sub-option::before {
    content: '·';
    margin-right: 10px;
    font-size: 20px;
    font-weight: bold;
}

.logout-btn {
    width: clamp(60px, 6.25vw, 120px);
    background: #ff5c00;
    border: none;
    border-radius: 20px;
    padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
    color: white;
    font-size: clamp(8px, 0.74vw, 14px);
    font-weight: 500;
    cursor: pointer;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #ff7700;
    transform: translateY(-1px);
}

.logout-btn:active {
    transform: translateY(0);
}

.informationmenu-footer {
    display: flex;
    justify-content: center;
    padding: clamp(8px, 0.94vw, 18px);
    border-top: 1px solid #d5d5d5;
    background-color: white;
    /* 确保footer固定在底部 */
    margin-top: auto;
    flex-shrink: 0;
    /* 强制定位到最底部 */
    position: sticky;
    bottom: 0;
}

/* 侧边栏收起按钮样式 */
.sidebar-menu-hamburger {
    width: 30px;
    height: 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    padding: 0px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.sidebar-menu-hamburger:hover {
    background-color: #f0f0f0;
}

.sidebar-menu-hamburger span {
    width: clamp(10px, 0.94vw, 18px);
    height: clamp(1px, 0.1vw, 2px);
    background-color: #000000ff;
    margin: clamp(1px, 0.1vw, 2px) 0;
    transition: all 0.3s ease;
    border-radius: 1px;
}

/* 侧边菜单 section 图标样式 */
.section-icon {
    width: clamp(12px, 1.04vw, 20px);
    height: clamp(12px, 1.04vw, 20px);
    margin-right: clamp(18px, 1.3vw, 25px);
    vertical-align: middle;
    flex-shrink: 0;
    object-fit: contain;
    transition: filter 0.3s ease; /* 给icon加动画 */
}

.informationmenu-section-title.active .section-icon {
    filter: brightness(0) invert(1);
}

.informationmenu-section-title:hover:not(.active) .section-icon {
    filter: brightness(0) saturate(100%) invert(45%) sepia(98%) saturate(2000%) hue-rotate(0deg) brightness(102%) contrast(105%);
}

/* 更新 section-title 的 flexbox 布局 */
.informationmenu-section-title {
    display: flex;
    align-items: center;
    justify-content: flex-start; /* 改为靠左对齐 */
    text-align: left;
    /* 保持你现有的其他样式 */
}

/* 确保箭头在最右边 */
.informationmenu-section-title .section-arrow {
    margin-left: auto; /* 箭头自动推到最右边 */
}

/* 侧边栏收起状态下的图标样式 */
.informationmenu.collapsed .section-icon {
    margin-right: 0 !important;
    width: clamp(10px, 1.15vw, 22px) !important;
    height: clamp(10px, 1.15vw, 22px) !important;
    display: block !important;
}

/* 收起状态下隐藏文字，只显示图标 */
.informationmenu.collapsed .informationmenu-section-title {
    justify-content: center !important;
    padding: 15px 10px !important;
    color: transparent !important;
}

.informationmenu.collapsed .informationmenu-section-title .section-arrow {
    display: none !important;
}

/* 收起状态保持三条横线 */
.sidebar-menu-hamburger.collapsed span {
    /* 保持原始状态，不做任何变换 */
    transform: none;
    opacity: 1;
}

/* 提高选择器权重 */
.informationmenu.collapsed {
    width: clamp(50px, 3.65vw, 70px) !important;
    overflow: visible;
}

.informationmenu.collapsed .logout-btn {
    opacity: 0 !important;
    visibility: hidden !important;
    transition: opacity 0.3s ease, visibility 0.3s ease !important;
}

.informationmenu.collapsed .informationmenu-section-title {
    padding: clamp(2px, 0.63vw, 12px) 10px !important;
    text-align: center;
    font-size: 0; /* 隐藏文字 */
    /* 确保图标仍然显示 */
    line-height: normal;
    height: auto !important;
}

.informationmenu.collapsed .informationmenu-section-title::before {
    display: none !important;
}

.informationmenu.collapsed .dropdown-menu-items {
    display: none !important;
}

.informationmenu.collapsed .informationmenu-footer {
    padding: 10px !important;
}

.informationmenu.collapsed .submenu {
    display: none !important;
}

/* 确保过渡效果 */
.informationmenu {
    transition: width 0.3s ease !important;
}

.informationmenu.collapsed .user-avatar-dropdown {
    display: none !important;
}

.informationmenu.collapsed .informationmenu-header {
    padding: clamp(15px, 1.51vw, 29px) clamp(10px, 1.04vw, 20px) !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 0 !important;
}

.informationmenu.collapsed .user-info {
    display: none !important;
}

.informationmenu.collapsed .user-position {
    display: none !important;
}

.informationmenu.collapsed .user-avatar {
    display: none !important;
}

/* 登录后头像和下拉菜单样式 */
.user-avatar-dropdown {
    position: relative;
    display: flex;
    align-items: center;
    gap: clamp(4px, 0.63vw, 12px);
    cursor: pointer;
}

.user-avatar {
    width: clamp(22px, 2.4vw, 46px);
    height: clamp(22px, 2.4vw, 46px);
    background-color: #FF5C00;
    color: white;
    font-weight: bold;
    font-size: clamp(7px, 1.04vw, 20px);
    line-height: clamp(24px, 2.34vw, 45px);
    text-align: center;
    border-radius: 50%;
    user-select: none;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    margin: 0;
    font-size: clamp(6px, 0.63vw, 12px);
    font-weight: bold;
    color: #000000ff;
}

.user-position {
    margin: 2px 0 0 0;
    font-size: clamp(7px, 0.63vw, 12px);
    font-weight: 500;
    color: #000000ff;
}

/* 取消整页白色覆盖，但保留侧栏本体为白色卡片 */
.informationmenu {
    background: transparent !important; /* 容器透明，不再铺一层白底 */
    pointer-events: none;              /* 容器非交互，避免遮挡右侧内容点击 */
}
.informationmenu.show { background: transparent !important; }

/* 仅让侧栏内部结构保持白色背景并可交互 */
.informationmenu .informationmenu-header,
.informationmenu .informationmenu-content,
.informationmenu .informationmenu-footer {
    background: #ffffffff;               /* 侧栏本体白底 */
    pointer-events: auto;              /* 本体可交互 */
}

/* 若有使用伪元素做遮罩，强制移除 */
.informationmenu::before,
.informationmenu::after { display: none !important; }

/* 页面内容右移，避免被侧栏覆盖 */
body {
    margin-left: clamp(140px, 13.02vw, 250px); /* 默认就给右边距，避免闪烁 */
}
body.sidebar-collapsed {
    margin-left: clamp(50px, 3.65vw, 70px);
}

/* 确保主内容区域能够自适应剩余空间 */
.main-content {
    width: calc(100vw - clamp(140px, 13.02vw, 250px));
    max-width: none;
    box-sizing: border-box;
    transition: width 0.3s ease;
}

body.sidebar-collapsed .main-content {
    width: calc(100vw - clamp(50px, 3.65vw, 70px));
}

/* 响应式调整 */

body.sidebar-transition {
    transition: margin-left 0.3s ease;
}
@media (max-width: 768px) {
    body.has-sidebar { margin-left: 0; }
    /* 移动端采用抽屉式覆盖显示 */
    .informationmenu.hide { transform: translateX(-100%); }
    .informationmenu.show { transform: translateX(0); }
}

/* 修复箭头图标被样式覆盖为横线的问题：强制使用文本箭头并移除背景/伪元素 */
.informationmenu .section-arrow,
.informationmenu .informationmenu-arrow {
    background: none !important;
    width: auto !important;
    height: auto !important;
    border: none !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-mask: none !important;
    mask: none !important;
}
.informationmenu .section-arrow::before,
.informationmenu .informationmenu-arrow::before,
.informationmenu .section-arrow::after,
.informationmenu .informationmenu-arrow::after {
    content: none !important;
}
.informationmenu .section-arrow { font-size: 16px; line-height: 1; display: inline-block; }
.informationmenu .informationmenu-arrow { font-size: clamp(12px, 0.94vw, 18px); line-height: 1; display: inline-block; }

/* 按用户要求，隐藏“分组标题”的箭头，保留子项箭头 */
.informationmenu .section-arrow { display: none !important; }
</style>

<!-- 侧边菜单 -->
<div class="informationmenu">
    <div class="informationmenu-header">
        <div class="user-avatar-dropdown">
            <div id="user-avatar" class="user-avatar"><?php echo $avatarLetter; ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo $username; ?></div>
                <div class="user-position"><?php echo $position; ?></div>
            </div>
        </div>

        <div class="sidebar-menu-hamburger" id="sidebarToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="informationmenu-content">
        <?php if ($canSeeBrand): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="brand-items">
                <img src="../images/images/网页照片上传.svg" alt="" class="section-icon">
                集团架构
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="brand-items">
                <?php if (!empty($submenuVisibility['brand']['kunzz_holdings'])): ?>
                <!-- <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        KUNZZ HOLDINGS SDN BHD
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">KUNZZ HOLDINGS SDN BHD</div>
                        </div>
                        <div class="submenu-content">
                            <a href="#" class="submenu-item">集团总部</a>
                        </div>
                    </div>
                </div> -->
                <?php endif; ?>
                
                <!-- Tokyo Japanese Cuisine Sdn Bhd -->
                <?php if (!empty($submenuVisibility['brand']['tokyo_cuisine'])): ?>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        TOKYO JAPANESE CUISINE SDN BHD
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">TOKYO JAPANESE CUISINE SDN BHD</div>
                        </div>
                        <div class="submenu-content">
                            <?php if (!empty($submenuVisibility['brand']['j1'])): ?>
                            <a href="#" class="submenu-item expandable" data-target="j1-options">
                                J1 (MIDVALLEY)
                                <span class="expand-arrow">›</span>
                            </a>
                            <div class="sub-options" id="j1-options">
                                <?php if (!empty($submenuVisibility['brand']['j1_schedule'])): ?>
                                <a href="schedule_manager.php?restaurant=J1" class="sub-option">员工排班表</a>
                                <a href="phone_manage.php?restaurant=J1" class="sub-option">员工手机记录</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($submenuVisibility['brand']['j2'])): ?>
                            <a href="#" class="submenu-item expandable" data-target="j2-options">
                                J2 (PARADIGM MALL)
                                <span class="expand-arrow">›</span>
                            </a>
                            <div class="sub-options" id="j2-options">
                                <?php if (!empty($submenuVisibility['brand']['j2_schedule'])): ?>
                                <a href="schedule_manager.php?restaurant=J2" class="sub-option">员工排班表</a>
                                <a href="phone_manage.php?restaurant=J2" class="sub-option">员工手机记录</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Tokyo Izakaya Sdn Bhd -->
                <?php if (!empty($submenuVisibility['brand']['tokyo_izakaya'])): ?>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        TOKYO IZAKAYA SDN BHD
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">TOKYO IZAKAYA SDN BHD</div>
                        </div>
                        <div class="submenu-content">
                            <?php if (!empty($submenuVisibility['brand']['j3'])): ?>
                            <a href="#" class="submenu-item expandable" data-target="j3-options">
                                J3 (DESA TEBRAU)
                                <span class="expand-arrow">›</span>
                            </a>
                            <div class="sub-options" id="j3-options">
                                <?php if (!empty($submenuVisibility['brand']['j3_schedule'])): ?>
                                <a href="schedule_manager.php?restaurant=J3" class="sub-option">员工排班表</a>
                                <a href="phone_manage.php?restaurant=J3" class="sub-option">员工手机记录</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeAnalytics): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="analytics-items">
                <img src="../images/images/运营分析与报表.svg" alt="" class="section-icon">
                营收数据
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="analytics-items">
                <?php if (!empty($submenuVisibility['analytics']['kpi_report'])): ?>
                <div class="menu-item-wrapper">
                    <a href="kpi.php" class="informationmenu-item">
                        KPI报表
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($submenuVisibility['analytics']['kpi_upload'])): ?>
                <div class="menu-item-wrapper">
                    <a href="kpiedit.php" class="informationmenu-item">
                        数据上传
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeHR): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="hr-items">
                <img src="../images/images/人事与资源管理.svg" alt="" class="section-icon">
                人事管理
            </div>
            <div class="dropdown-menu-items" id="hr-items">               
                <?php if (!empty($submenuVisibility['hr']['staff_management'])): ?>
                <div class="menu-item-wrapper">
                    <a href="generatecode.php" class="informationmenu-item">
                        职员管理
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeResource): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="resource-items">
                <img src="../images/images/资源库管理.svg" alt="" class="section-icon">
                资源总库
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="resource-items">               
                <?php if (!empty($submenuVisibility['resource']['stock_inventory'])): ?>
                <div class="menu-item-wrapper">
                    <a href="stocklistall.php" class="informationmenu-item" id="stock-link" onclick="redirectToAllowedStockPage(event)">
                        库存
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($submenuVisibility['resource']['dishware'])): ?>
                <div class="menu-item-wrapper">
                    <a href="dishware_stock.php" class="informationmenu-item">
                        碗碟
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($submenuVisibility['resource']['price_comparison'])): ?>
                <div class="menu-item-wrapper">
                    <a href="price.php" class="informationmenu-item">
                        价格对比
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeVisual): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="photoupload-items">
                <img src="../images/images/网页照片上传.svg" alt="" class="section-icon">
                视觉管理
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="photoupload-items">
                <div class="menu-item-wrapper">
                    <a href="bgmusicupload.php" class="informationmenu-item">
                        背景音乐
                    </a>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        首页
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">首页</div>
                        </div>
                        <div class="submenu-content">
                            <a href="homepage1upload.php" class="submenu-item">第一页</a>
                        </div>
                    </div>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        关于我们
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">关于我们</div>
                        </div>
                        <div class="submenu-content">
                            <a href="aboutpage1upload.php" class="submenu-item">第一页</a>
                            <a href="aboutpage4upload.php" class="submenu-item">第四页</a>
                        </div>
                    </div>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        旗下品牌
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">旗下品牌</div>
                        </div>
                        <div class="submenu-content">
                            <a href="tokyopage1upload.php" class="submenu-item">第一页</a>
                            <a href="tokyopage5upload.php" class="submenu-item">第五页</a>
                        </div>
                    </div>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        加入我们
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">加入我们</div>
                        </div>
                        <div class="submenu-content">
                            <a href="joinpage1upload.php" class="submenu-item">第一页</a>
                            <a href="joinpage2upload.php" class="submenu-item">第二页</a>
                            <a href="joinpage3upload.php" class="submenu-item">第三页</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="informationmenu-footer">
            <button class="logout-btn" onclick="location.href='logout.php'">
                登出
            </button>
        </div>
    </div>
</div>

<script>
    const sidebar = document.querySelector('.informationmenu');
    // 移除遮罩层逻辑
    const overlay = null;
    const userAvatar = document.getElementById('user-avatar');
    const closeBtn = document.querySelector('.informationmenu-close-btn');

    // 点击用户头像显示菜单
    userAvatar?.addEventListener('click', function() {
        sidebar.classList.add('show');
    });

    // 关闭菜单
    function closeSidebar() {
        sidebar.classList.remove('show');
        // 无遮罩层
        // 关闭所有下拉菜单
        document.querySelectorAll('.dropdown-menu-items').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        document.querySelectorAll('.informationmenu-section-title').forEach(title => {
            title.classList.remove('active');
        });
    }

    closeBtn?.addEventListener('click', closeSidebar);
    // 无遮罩层点击事件

    // ESC键关闭菜单
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Section标题点击事件
    document.querySelectorAll('.informationmenu-section-title').forEach(title => {
        title.addEventListener('click', function(e) {
            const targetId = this.getAttribute('data-target');
            const targetDropdown = document.getElementById(targetId);

            // 检查侧边栏是否处于收起状态
            if (sidebarMenu.classList.contains('collapsed')) {
                e.preventDefault();
                e.stopPropagation();
        
                // 展开侧边栏
                sidebarMenu.classList.remove('collapsed');
                sidebarToggle.classList.remove('collapsed');
                // ⭐ 添加这行：移除 body 的 sidebar-collapsed 类
                document.body.classList.remove('sidebar-collapsed');
        
                // 同时展开点击的选项
                // 关闭其他section的下拉菜单
                document.querySelectorAll('.dropdown-menu-items').forEach(dropdown => {
                    if (dropdown.id !== targetId) {
                        dropdown.classList.remove('show');
                    }
                });
        
                // 移除其他section title的active状态
                document.querySelectorAll('.informationmenu-section-title').forEach(t => {
                    if (t !== this) {
                        t.classList.remove('active');
                    }
                });
        
                // 激活当前section
                this.classList.add('active');
                targetDropdown?.classList.add('show');
        
                return false;
            }

            // 侧边栏已展开时的正常切换逻辑
            // 关闭其他section的下拉菜单
            document.querySelectorAll('.dropdown-menu-items').forEach(dropdown => {
                if (dropdown.id !== targetId) {
                    dropdown.classList.remove('show');
                }
            });

            // 移除其他section title的active状态
            document.querySelectorAll('.informationmenu-section-title').forEach(t => {
                if (t !== this) {
                    t.classList.remove('active');
                }
            });

            // 切换当前section
            this.classList.toggle('active');
            targetDropdown?.classList.toggle('show');
        });
    });

    // 菜单项点击效果
    document.querySelectorAll('.informationmenu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
    
            // 检查是否有真实的链接
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                // 有真实链接，允许正常跳转
                window.location.href = href;
                return;
            }
    
            // 没有真实链接的项目，阻止默认行为
            e.preventDefault();
    
            // 移除其他active状态
            document.querySelectorAll('.informationmenu-item').forEach(i => i.classList.remove('active'));
    
            // 添加active状态到当前项
            this.classList.add('active');
        });
    });

    // 修复后的子菜单项点击效果
    document.querySelectorAll('.submenu-item:not(.expandable)').forEach(item => {
        item.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
    
            // 检查是否有真实的链接
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                // 有真实链接，允许正常跳转
                console.log('跳转到: ' + href);
                // 移除 e.preventDefault()，让链接正常工作
                window.location.href = href; // 手动跳转
                return;
            }
    
            // 没有真实链接的项目，阻止默认行为并显示提示
            e.preventDefault();
            const itemText = this.textContent.replace('→', '').trim();
            alert('点击了子菜单项: ' + itemText);
        });
    });

    // 多级展开功能
    document.querySelectorAll('.submenu-item.expandable').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const targetId = this.getAttribute('data-target');
            const targetOptions = document.getElementById(targetId);
            
            // 收起所有其他的子选项
            document.querySelectorAll('.sub-options').forEach(options => {
                if (options.id !== targetId) {
                    options.classList.remove('expanded');
                }
            });
            
            // 移除所有其他expandable项的expanded类
            document.querySelectorAll('.submenu-item.expandable').forEach(expandableItem => {
                if (expandableItem !== this) {
                    expandableItem.classList.remove('expanded');
                }
            });
            
            // 切换当前项的展开状态
            this.classList.toggle('expanded');
            targetOptions?.classList.toggle('expanded');
        });
    });

    // 子选项点击效果
    document.querySelectorAll('.sub-option').forEach(option => {
        option.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // 检查是否有真实的链接
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                // 有真实链接，允许正常跳转
                console.log('跳转到: ' + href);
                return; // 不阻止默认行为
            }
            
            // 没有真实链接的项目
            e.preventDefault();
            const optionText = this.textContent.replace('·', '').trim();
            alert('点击了子选项: ' + optionText);
        });
    });

    // 增强子菜单hover效果
    document.querySelectorAll('.menu-item-wrapper').forEach(wrapper => {
        const submenu = wrapper.querySelector('.submenu');
        if (submenu) {
            // 鼠标进入菜单项区域
            wrapper.addEventListener('mouseenter', function() {
                submenu.style.opacity = '1';
                submenu.style.visibility = 'visible';
                submenu.style.transform = 'translateX(0)';
                submenu.style.pointerEvents = 'auto';
            });

            // 鼠标离开整个区域时隐藏
            wrapper.addEventListener('mouseleave', function(e) {
                // 检查鼠标是否移向子菜单
                setTimeout(() => {
                    if (!submenu.matches(':hover') && !wrapper.matches(':hover')) {
                        submenu.style.opacity = '0';
                        submenu.style.visibility = 'hidden';
                        submenu.style.transform = 'translateX(-50px)';
                        submenu.style.pointerEvents = 'none';
                    }
                }, 100);
            });

            // 鼠标在子菜单上时保持显示
            submenu.addEventListener('mouseenter', function() {
                this.style.opacity = '1';
                this.style.visibility = 'visible';
                this.style.transform = 'translateX(0)';
                this.style.pointerEvents = 'auto';
            });

            submenu.addEventListener('mouseleave', function() {
                this.style.opacity = '0';
                this.style.visibility = 'hidden';
                this.style.transform = 'translateX(-50px)';
                this.style.pointerEvents = 'none';
            });
        }
    });

    console.log('点击Section + 悬停Submenu系统已加载完成');

    // 根据权限跳转到允许的库存页面
    async function redirectToAllowedStockPage(event) {
        event.preventDefault();
        try {
            const res = await fetch('generatecodeapi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_page_permissions' })
            });
            const data = await res.json();
            let targetSystem = 'central'; // 默认中央
            let targetView = 'list';
            const viewOrder = ['list', 'records', 'remark', 'product', 'sot'];
            const viewRedirectMap = {
                list: 'stocklistall.php',
                records: 'stockeditall.php',
                remark: 'stockremark.php',
                product: 'stockproductname.php',
                sot: 'stocksot.php'
            };
            
            if (data.success && data.page_permissions && data.page_permissions.stock_inventory) {
                const allowedSystems = data.page_permissions.stock_inventory.system || [];
                const allowedViews = data.page_permissions.stock_inventory.view || [];
                if (allowedSystems.length > 0) {
                    // 使用第一个允许的系统
                    targetSystem = allowedSystems[0];
                }
                if (allowedViews.length > 0) {
                    const firstAllowedView = viewOrder.find(view => allowedViews.includes(view));
                    if (firstAllowedView) {
                        targetView = firstAllowedView;
                    }
                }
            }
            const redirectBase = viewRedirectMap[targetView] || viewRedirectMap.list;
            
            // 跳转到stocklistall.php，并添加系统参数或使用URL hash
            window.location.href = `${redirectBase}?system=${targetSystem}`;
        } catch (e) {
            // 出错时默认跳转到中央
            window.location.href = 'stocklistall.php?system=central';
        }
    }
</script>
<script>
    // 侧边栏收起/展开功能
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarMenu = document.querySelector('.informationmenu'); // 改名避免冲突

    sidebarToggle?.addEventListener('click', function(e) {
        e.stopPropagation(); // 防止事件冒泡

        // 如果正在收起侧边栏，清除所有激活状态
        if (!sidebarMenu.classList.contains('collapsed')) {
            // 关闭所有下拉菜单
            document.querySelectorAll('.dropdown-menu-items').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
            // 移除所有section title的active状态
            document.querySelectorAll('.informationmenu-section-title').forEach(title => {
                title.classList.remove('active');
            });
            // 移除所有菜单项的active状态
            document.querySelectorAll('.informationmenu-item').forEach(item => {
                item.classList.remove('active');
            });
        }

        sidebarMenu.classList.toggle('collapsed');
        sidebarToggle.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');

        // 保存侧边栏状态到localStorage
        const isCollapsed = sidebarMenu.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);

        // 确保过渡动画已启用
        if (!document.body.classList.contains('sidebar-transition')) {
            document.body.classList.add('sidebar-transition');
        }
    });

    // 页面加载完成后启用过渡动画
    document.addEventListener('DOMContentLoaded', function() {
        // 立即检查是否需要应用collapsed状态（从localStorage读取）
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
            sidebarMenu?.classList.add('collapsed');
            sidebarToggle?.classList.add('collapsed');
        }
        
        // 页面加载后短暂延迟再启用过渡效果
        setTimeout(function() {
            document.body.classList.add('sidebar-transition');
        }, 100);
    });
</script>