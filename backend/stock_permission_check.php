<?php
/**
 * backend/stock_permission_check.php
 * 
 * 核心后端库存权限检查脚本。
 * 验证当前登录用户是否拥有访问特定分店（system）和特定视图（view）的权限。
 * 
 * 规则：
 * 1. Boss 和 Admin 账号默认拥有全权限（跳过检查）。
 * 2. 普通职员必须在 `user_sidebar_permissions` 或 `user_page_permissions` 中被勾选相应权限。
 */

// 1. 确保 session 已启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. 检查基本登录状态
if (!isset($_SESSION['user_id'])) {
    if (strpos($_SERVER['PHP_SELF'], 'api.php') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '未登录']);
        exit;
    } else {
        header("Location: ../frontend/login.html");
        exit;
    }
}

// 3. 超级用户（Boss/Admin）直接放行
$accountType = $_SESSION['account_type'] ?? 'user';
if ($accountType === 'boss' || $accountType === 'admin') {
    return; // 直接返回，允许访问
}

// 4. 获取数据库连接 (如果尚未定义 $pdo)
if (!isset($pdo)) {
    try {
        $host = 'localhost';
        $dbname = 'u690174784_kunzz';
        $dbuser = 'u690174784_kunzz';
        $dbpass = 'Kunzz1688';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("权限系统对接失败: " . $e->getMessage());
    }
}

$userId = $_SESSION['user_id'];

// 5. 查询权限数据
$stmt = $pdo->prepare("SELECT permissions_json, page_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("<div style='color:#ef4444; font-weight:bold; padding:30px; border:1px solid #fee2e2; background:#fef2f2; border-radius:8px; margin:20px;'>
            <i class='fas fa-lock'></i> 无权访问：您的账号尚未初始化权限。请联系管理员在“职员管理”中为您分配权限。
         </div>");
}

// 6. 检查“资源总库”一级权限
$mainPerms = json_decode($row['permissions_json'] ?? '[]', true);
if (!in_array('resource', $mainPerms)) {
    die("<div style='color:#ef4444; font-weight:bold; padding:30px; border:1px solid #fee2e2; background:#fef2f2; border-radius:8px; margin:20px;'>
            <i class='fas fa-lock'></i> 无权访问：您没有“资源总库”的访问权限。
         </div>");
}

// 7. 提取库存详情权限
$pagePermsRaw = json_decode($row['page_permissions_json'] ?? '[]', true);
$stockPerms = $pagePermsRaw['stock_inventory'] ?? null;

// 尝试从 user_page_permissions 表获取更高优先级的覆盖（如果存在）
try {
    $stmt2 = $pdo->prepare("SELECT permissions_json FROM user_page_permissions WHERE user_id = ? AND page_key = 'stock_inventory'");
    $stmt2->execute([$userId]);
    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($row2) {
        $decoded2 = json_decode($row2['permissions_json'], true);
        $stockPerms = [
            'system' => $decoded2['systems'] ?? [],
            'views' => $decoded2['views'] ?? []
        ];
    }
} catch (Exception $e) {
    // 忽略表不存在等错误
}

if (!$stockPerms) {
    die("<div style='color:#ef4444; font-weight:bold; padding:30px; border:1px solid #fee2e2; background:#fef2f2; border-radius:8px; margin:20px;'>
            <i class='fas fa-lock'></i> 无权访问：您尚未被分配任何具体的库存（中央/J1/J2/J3）访问权限。
         </div>");
}

$allowedSystems = $stockPerms['system'] ?? $stockPerms['systems'] ?? [];
$allowedViews = $stockPerms['view'] ?? $stockPerms['views'] ?? [];

// 8. 验证当前请求的分店 (system)
// 获取来源（GET 或 POST/JSON）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $currentSystem = $input['system'] ?? $input['system_assign'] ?? ($_GET['system'] ?? 'central');
} else {
    $currentSystem = $_GET['system'] ?? 'central';
}

// 规范化名称 (统一下划线或大小写，根据系统实际存储调整)
$currentSystem = strtolower($currentSystem);
$allowedSystems = array_map('strtolower', $allowedSystems);

if (!in_array($currentSystem, $allowedSystems)) {
    $sysNames = ['central' => '中央', 'j1' => 'J1', 'j2' => 'J2', 'j3' => 'J3'];
    $sysDisplayName = $sysNames[$currentSystem] ?? $currentSystem;
    
    if (strpos($_SERVER['PHP_SELF'], 'api.php') !== false) {
        die(json_encode(['success' => false, 'message' => "无权访问 $sysDisplayName 分店数据"]));
    } else {
        die("<div style='color:#ef4444; font-weight:bold; padding:30px; border:1px solid #fee2e2; background:#fef2f2; border-radius:8px; margin:20px;'>
                <i class='fas fa-lock'></i> 无权访问：系统未授权您访问 <span style='color:#f97316'>$sysDisplayName</span> 的库存数据。
             </div>");
    }
}

// 9. 验证当前页面视图 (view)
$currentPage = basename($_SERVER['PHP_SELF'], ".php");
$viewMap = [
    'stocklistall' => 'list',
    'stockeditall' => 'records',
    'stockremark' => 'remark',
    'stockproductname' => 'product',
    'stocksot' => 'sot'
];
$currentView = $viewMap[$currentPage] ?? null;

if ($currentView) {
    $allowedViews = array_map('strtolower', $allowedViews);
    if (!in_array($currentView, $allowedViews)) {
        $viewNames = ['list' => '总库存', 'records' => '进出货记录', 'remark' => '货品备注', 'product' => '货品种类', 'sot' => '货品异常'];
        $viewDisplayName = $viewNames[$currentView] ?? $currentView;
        
        die("<div style='color:#ef4444; font-weight:bold; padding:30px; border:1px solid #fee2e2; background:#fef2f2; border-radius:8px; margin:20px;'>
                <i class='fas fa-lock'></i> 无权访问：系统未授权您访问 <span style='color:#f97316'>$viewDisplayName</span> 视图。
             </div>");
    }
}
