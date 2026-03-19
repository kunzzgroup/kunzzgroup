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

// 如果 session 中没有 user_id，尝试从 cookie 恢复（和 session_check.php 同逻辑）
if (empty($_SESSION['user_id']) &&
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = $_COOKIE['position'] ?? null;
    $_SESSION['last_activity'] = time();
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
    $username = $_SESSION['username'] ?? '未知用户';
    echo '<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 - 无权限访问</title>
<link rel="icon" type="image/png" href="../images/images/logo.png">
<style>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f4ef;
    font-family: "Inter", "PingFang SC", "Microsoft YaHei", sans-serif;
    color: #333;
}
.card {
    text-align: center;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 48px 40px 40px;
    max-width: 440px;
    width: 90%;
    animation: fadeUp 0.5s ease;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.icon-circle {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: #fff0ed;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}
.icon-circle svg {
    width: 36px; height: 36px;
    color: #e8654a;
}
h1 {
    font-size: 20px;
    font-weight: 700;
    color: #222;
    margin-bottom: 6px;
}
.subtitle {
    font-size: 14px;
    color: #999;
    margin-bottom: 28px;
}
.info-box {
    background: #fafafa;
    border: 1px solid #f0f0f0;
    border-radius: 10px;
    padding: 0;
    margin-bottom: 28px;
    overflow: hidden;
}
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    font-size: 14px;
}
.info-row + .info-row {
    border-top: 1px solid #f0f0f0;
}
.info-label {
    color: #888;
}
.info-value {
    background: #f5f5f5;
    color: #555;
    padding: 4px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
}
.btn {
    display: block;
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #f5a623, #e8961e);
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    letter-spacing: 0.5px;
}
.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245,166,35,0.35);
    background: linear-gradient(135deg, #f7b13d, #e8961e);
}
</style>
</head>
<body>
<div class="card">
    <div class="icon-circle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </div>
    <h1>抱歉，您无权访问此页面</h1>
    <p class="subtitle">Access Denied</p>
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">当前账户</span>
            <span class="info-value">' . htmlspecialchars($username) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">请求页面</span>
            <span class="info-value">' . htmlspecialchars(basename($_SERVER['REQUEST_URI'] ?? '')) . '</span>
        </div>
    </div>
    <a href="javascript:history.back()" class="btn">返回上一页 / Go Back</a>
</div>
</body>
</html>';
    exit;
}
