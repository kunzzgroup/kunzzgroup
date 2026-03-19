<?php
/**
 * ═══════════════════════════════════════════
 *  权限守卫 (Permission Guard)
 *  - 后端页面/API 权限拦截
 *  - 没权限 = 403 拒绝
 * ═══════════════════════════════════════════
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 获取当前用户的权限数据（带缓存）
 */
function _loadUserPermissions() {
    static $cached = null;
    if ($cached !== null) return $cached;

    $cached = [
        'modules' => [],          // L1: 可用模块 ['analytics','hr','resource','visual','brand']
        'submenus' => [],         // L2: 子菜单 {'resource': ['stock_inventory','dishware','price_comparison']}
        'stock_views' => [],      // L3: 库存视图 ['list','records','remark','product','sot']
        'stock_systems' => [],    // L3: 库存系统 ['central','j1','j2','j3']
        'has_record' => false,    // 是否有权限记录
    ];

    if (empty($_SESSION['user_id'])) return $cached;

    try {
        $host = 'localhost';
        $dbname = 'u690174784_kunzz';
        $dbuser = 'u690174784_kunzz';
        $dbpass = 'Kunzz1688';

        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $userId = intval($_SESSION['user_id']);

        // 读取 sidebar 权限
        $stmt = $pdo->prepare("SELECT permissions_json, submenu_permissions_json, page_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // 没有权限记录 = 默认全部开启（管理员 / 旧用户）
            return $cached;
        }

        $cached['has_record'] = true;

        // L1: 模块权限
        $modules = json_decode($row['permissions_json'] ?? '[]', true);
        if (is_array($modules) && !empty($modules)) {
            $cached['modules'] = $modules;
        }

        // L2: 子菜单权限
        $submenus = json_decode($row['submenu_permissions_json'] ?? '{}', true);
        if (is_array($submenus) && !empty($submenus)) {
            $cached['submenus'] = $submenus;
        }

        // L3: 页面级权限（库存视图/系统）
        $pagePerms = json_decode($row['page_permissions_json'] ?? '{}', true);
        if (is_array($pagePerms) && isset($pagePerms['stock_inventory'])) {
            $stockPerms = $pagePerms['stock_inventory'];
            $cached['stock_views'] = $stockPerms['views'] ?? ($stockPerms['view'] ?? []);
            $cached['stock_systems'] = $stockPerms['system'] ?? ($stockPerms['systems'] ?? []);
        }

        // 也查 user_page_permissions 表（新表结构）
        try {
            $stmt2 = $pdo->prepare("SELECT permissions_json FROM user_page_permissions WHERE user_id = ? AND page_key = 'stock_inventory'");
            $stmt2->execute([$userId]);
            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($row2) {
                $decoded = json_decode($row2['permissions_json'], true);
                if (is_array($decoded)) {
                    if (!empty($decoded['system']) || !empty($decoded['systems'])) {
                        $cached['stock_systems'] = $decoded['system'] ?? ($decoded['systems'] ?? []);
                    }
                    if (!empty($decoded['views']) || !empty($decoded['view'])) {
                        $cached['stock_views'] = $decoded['views'] ?? ($decoded['view'] ?? []);
                    }
                }
            }
        } catch (Throwable $e) {
            // 新表可能不存在
        }

    } catch (Throwable $e) {
        // 数据库错误时默认放行
    }

    return $cached;
}

/**
 * 检查模块 + 子菜单权限
 * @param string $module   模块名: analytics, hr, resource, visual, brand
 * @param string|null $submenu 子菜单名 (可选): stock_inventory, kpi_report 等
 * @return bool
 */
function hasPermission($module, $submenu = null) {
    $perms = _loadUserPermissions();

    // 没有权限记录 = 默认全部开启（向后兼容）
    if (!$perms['has_record']) return true;

    // 模块权限为空 = 默认全部开启
    if (empty($perms['modules'])) return true;

    // 检查 L1: 模块
    if (!in_array($module, $perms['modules'], true)) {
        return false;
    }

    // 如果不需要检查子菜单
    if ($submenu === null) return true;

    // 检查 L2: 子菜单
    $allowedSubs = $perms['submenus'][$module] ?? [];
    // 子菜单为空 = 默认全部开启
    if (empty($allowedSubs)) return true;

    return in_array($submenu, $allowedSubs, true);
}

/**
 * 检查库存视图权限
 * @param string $view 视图名: list, records, remark, product, sot
 * @return bool
 */
function hasStockViewPermission($view) {
    // 先检查模块+子菜单
    if (!hasPermission('resource', 'stock_inventory')) return false;

    $perms = _loadUserPermissions();
    // 没有视图权限数据 = 默认全部开启
    if (empty($perms['stock_views'])) return true;

    return in_array($view, $perms['stock_views'], true);
}

/**
 * 检查库存系统权限
 * @param string $system 系统名: central, j1, j2, j3
 * @return bool
 */
function hasStockSystemPermission($system) {
    if (!hasPermission('resource', 'stock_inventory')) return false;

    $perms = _loadUserPermissions();
    if (empty($perms['stock_systems'])) return true;

    return in_array($system, $perms['stock_systems'], true);
}

// ============================================================
//  页面拦截函数（调用即拦截）
// ============================================================

/**
 * 要求模块+子菜单权限，否则 403 拒绝
 */
function requirePermission($module, $submenu = null) {
    if (!hasPermission($module, $submenu)) {
        _denyAccess();
    }
}

/**
 * 要求库存视图权限，否则 403 拒绝
 */
function requireStockView($view) {
    if (!hasStockViewPermission($view)) {
        _denyAccess();
    }
}

/**
 * API 拦截：返回 JSON 错误
 */
function requirePermissionApi($module, $submenu = null) {
    if (!hasPermission($module, $submenu)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => '无权限访问此功能',
            'code' => 'FORBIDDEN'
        ]);
        exit;
    }
}

/**
 * API 拦截：库存视图
 */
function requireStockViewApi($view) {
    if (!hasStockViewPermission($view)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => '无权限访问此视图',
            'code' => 'FORBIDDEN'
        ]);
        exit;
    }
}

/**
 * 输出 403 页面并 exit
 */
function _denyAccess() {
    http_response_code(403);
    echo '<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 - 无权限</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    font-family: "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
    color: #fff;
}
.container {
    text-align: center;
    padding: 60px 40px;
    background: rgba(255,255,255,0.05);
    border-radius: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    max-width: 500px;
}
.icon { font-size: 80px; margin-bottom: 20px; }
h1 { font-size: 48px; font-weight: 700; color: #e94560; margin-bottom: 10px; }
h2 { font-size: 22px; font-weight: 400; color: #ccc; margin-bottom: 30px; }
p { color: #888; line-height: 1.6; margin-bottom: 30px; }
.btn {
    display: inline-block;
    padding: 12px 36px;
    background: linear-gradient(135deg, #e94560, #c23152);
    color: #fff;
    text-decoration: none;
    border-radius: 30px;
    font-size: 16px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(233,69,96,0.3);
}
</style>
</head>
<body>
<div class="container">
    <div class="icon">🔒</div>
    <h1>403</h1>
    <h2>无权限访问</h2>
    <p>您没有访问此页面的权限。<br>请联系管理员获取授权。</p>
    <a href="javascript:history.back()" class="btn">← 返回上一页</a>
</div>
</body>
</html>';
    exit;
}
