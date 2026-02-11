<?php
// Ensure variables exist when included from different pages
$username = isset($username) ? $username : (isset($_SESSION['username']) ? $_SESSION['username'] : 'User');
$position = isset($position) ? $position : ((isset($_SESSION['position']) && !empty($_SESSION['position'])) ? $_SESSION['position'] : 'User');
$avatarLetter = isset($avatarLetter) ? $avatarLetter : strtoupper(substr($username, 0, 1));
$canViewAnalytics = isset($canViewAnalytics) ? $canViewAnalytics : true;
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
$kpiUploadDefaultPage = 'kpiedit.php'; // 默认跳转到kpiedit.php
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
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
    
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
            $permStmt = $pdo->prepare("SELECT permissions_json, page_permissions_json, submenu_permissions_json, brand_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
            $permStmt->execute([$userId]);
            $permRow = $permStmt->fetch(PDO::FETCH_ASSOC);
            
            // 读取kpi_upload权限，用于决定数据上传的默认跳转页面
            if ($permRow && !empty($permRow['page_permissions_json'])) {
                $pagePerms = json_decode($permRow['page_permissions_json'], true);
                if (is_array($pagePerms) && isset($pagePerms['kpi_upload']) && isset($pagePerms['kpi_upload']['type'])) {
                    $uploadTypes = array_values(array_intersect($pagePerms['kpi_upload']['type'] ?? [], ['kpi', 'cost']));
                    // 如果只有cost权限，默认跳转到costedit.php
                    if (count($uploadTypes) === 1 && $uploadTypes[0] === 'cost') {
                        $kpiUploadDefaultPage = 'costedit.php';
                    }
                    // 如果只有kpi权限，或者两者都有，默认跳转到kpiedit.php（已经是默认值）
                }
            }
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
                    foreach ($submenuVisibility as $section => &$subItems) {
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
                        foreach ($subItems as $key => $value) {
                            $subItems[$key] = in_array($key, $allowed, true);
                        }
                    }
                    unset($subItems);
                    
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
// Include the sidebar template
<?php require __DIR__ . '/templates/sidebar.php'; ?>