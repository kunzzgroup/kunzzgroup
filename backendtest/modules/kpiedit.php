<?php
// 包含初始化文件
require_once dirname(__DIR__) . '/core/init.php';
// 包含会话验证
require_once CORE_PATH . '/session_check.php';

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

// session_check.php 已经开启了 session

// 获取用户职位信息
$userPosition = (!empty($_SESSION['position'])) ? strtoupper(trim($_SESSION['position'])) : '';
$isOperationManager = ($userPosition === 'OPERATION MANAGER');

// 标记是否使用了新权限系统
$hasNewPermissions = false;

if (isset($_SESSION['user_id'])) {
    // 使用全局 $pdo 对象
    try {
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

if (!in_array('kpi', $reportPermissions, true)) {
    if (in_array('cost', $reportPermissions, true)) {
        header('Location: costedit.php');
        exit();
    }
    $reportPermissions[] = 'kpi';
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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅数据管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../modules/css/kpiedit.css">
</head>
<?php include __DIR__ . '/templates/kpiedit.php'; ?>
<!-- 引入外部JS文件 -->
<script src="js/kpiedit.js"></script>
</body>
</html>