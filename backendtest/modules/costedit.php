<?php
// 包含初始化文件
require_once dirname(__DIR__) . '/core/init.php';
// 包含会话验证
require_once CORE_PATH . '/session_check.php';

// 防止浏览器缓存旧版 JS/HTML，避免修复已上线但用户端仍加载旧代码导致持续报错
// 注意：必须在任何输出之前设置 header
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$reportPermissions = ['kpi', 'cost'];
$restaurantPermissions = ['j1', 'j2', 'j3'];
$reportLabelMap = [
    'kpi' => 'KPI 报表',
    'cost' => '成本报表',
];
$restaurantConfigPhp = [
    'j1' => ['name' => 'J1', 'number' => 1],
    'j2' => ['name' => 'J2', 'number' => 2],
    'j3' => ['name' => 'J3', 'number' => 3],
];

// session_start() handled by init.php

// 标记是否使用了新权限系统
$hasNewPermissions = false;

if (isset($_SESSION['user_id'])) {
    global $pdo;

    try {
        // DB Connected via init.php -> db.php

        // 优先使用新的权限系统（page_permissions.kpi_upload）
        $stmt = $pdo->prepare("SELECT page_permissions_json, report_permissions_json, restaurant_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if ($row = $stmt->fetch()) {
            // 读取新的权限系统（page_permissions.kpi_upload）
            if (!empty($row['page_permissions_json'])) {
                $pagePerms = json_decode($row['page_permissions_json'], true);
                if (is_array($pagePerms) && isset($pagePerms['kpi_upload'])) {
                    // 使用新权限系统
                    $hasNewPermissions = true;
                    $uploadSystems = array_values(array_intersect($pagePerms['kpi_upload']['system'] ?? [], ['j1', 'j2', 'j3']));
                    $uploadTypes = array_values(array_intersect($pagePerms['kpi_upload']['type'] ?? [], ['kpi', 'cost']));
                    // 即使为空数组，也使用新权限系统的值（表示用户没有任何权限）
                    $reportPermissions = $uploadTypes;
                    $restaurantPermissions = $uploadSystems;
                }
            }
            
            // 如果新权限系统没有数据，回退到旧权限系统（向后兼容）
            if (!$hasNewPermissions) {
                if (!empty($row['report_permissions_json'])) {
                    $decoded = json_decode($row['report_permissions_json'], true);
                    if (is_array($decoded) && !empty($decoded)) {
                        $filtered = array_values(array_intersect($decoded, ['kpi', 'cost']));
                        if (!empty($filtered)) {
                            $reportPermissions = $filtered;
                        }
                    }
                }
                if (!empty($row['restaurant_permissions_json'])) {
                    $decoded = json_decode($row['restaurant_permissions_json'], true);
                    if (is_array($decoded) && !empty($decoded)) {
                        $filtered = array_values(array_intersect($decoded, ['j1', 'j2', 'j3']));
                        if (!empty($filtered)) {
                            $restaurantPermissions = $filtered;
                        }
                    }
                }
            }
        }
    } catch (Throwable $e) {
        // 忽略数据库错误，使用默认权限
    }
}

// 只有在没有使用新权限系统的情况下，才使用默认值
if (!$hasNewPermissions) {
    $reportPermissions = array_values(array_intersect(['kpi', 'cost'], $reportPermissions));
    if (empty($reportPermissions)) {
        $reportPermissions = ['kpi', 'cost'];
    }

    $restaurantPermissions = array_values(array_intersect(['j1', 'j2', 'j3'], $restaurantPermissions));
    if (empty($restaurantPermissions)) {
        $restaurantPermissions = ['j1', 'j2', 'j3'];
    }
} else {
    // 使用新权限系统时，确保值是正确的格式（只做格式验证，不使用默认值）
    $reportPermissions = array_values(array_intersect(['kpi', 'cost'], $reportPermissions));
    $restaurantPermissions = array_values(array_intersect(['j1', 'j2', 'j3'], $restaurantPermissions));
    
    // 如果新权限系统返回了空数组，说明用户没有任何权限
    // 为了安全，这里不设置默认值，而是保持空数组
    // 后续代码需要处理空权限的情况
}

if (!in_array('cost', $reportPermissions, true)) {
    if (in_array('kpi', $reportPermissions, true)) {
        header('Location: kpiedit.php');
        exit();
    }
    $reportPermissions[] = 'cost';
}
$reportPermissions = array_values(array_intersect(['kpi', 'cost'], $reportPermissions));

$restaurantConfigAllowed = array_intersect_key($restaurantConfigPhp, array_flip($restaurantPermissions));
// 只有在没有使用新权限系统且配置为空时，才使用默认值
if (empty($restaurantConfigAllowed) && !$hasNewPermissions) {
    $restaurantPermissions = ['j1', 'j2', 'j3'];
    $restaurantConfigAllowed = $restaurantConfigPhp;
}

// 如果使用新权限系统但配置为空，说明用户没有任何权限，使用第一个可用的餐厅（如果存在）
if (empty($restaurantConfigAllowed) && $hasNewPermissions && !empty($restaurantPermissions)) {
    $restaurantConfigAllowed = array_intersect_key($restaurantConfigPhp, array_flip($restaurantPermissions));
}

// 如果仍然为空，使用默认值作为最后的后备方案
if (empty($restaurantConfigAllowed)) {
    $restaurantPermissions = ['j1', 'j2', 'j3'];
    $restaurantConfigAllowed = $restaurantConfigPhp;
}

$defaultRestaurant = !empty($restaurantPermissions) ? $restaurantPermissions[0] : 'j1';
$showReportDropdown = count($reportPermissions) > 1;
$showRestaurantDropdown = count($restaurantPermissions) > 1;

include __DIR__ . '/templates/costedit.php';
?>
