<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/permission_guard.php';
requirePermission('analytics', 'kpi_upload');
require_once __DIR__ . '/session_check.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$reportPermissions = ['kpi', 'cost'];
$restaurantPermissions = ['j1', 'j2', 'j3'];
$restaurantConfigPhp = [
    'j1' => ['name' => 'J1', 'number' => 1],
    'j2' => ['name' => 'J2', 'number' => 2],
    'j3' => ['name' => 'J3', 'number' => 3],
];

$userPosition = (!empty($_SESSION['position'])) ? strtoupper(trim($_SESSION['position'])) : '';
$isOperationManager = ($userPosition === 'OPERATION MANAGER');
$hasNewPermissions = false;

if (isset($_SESSION['user_id'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->prepare('SELECT page_permissions_json, report_permissions_json, restaurant_permissions_json FROM user_sidebar_permissions WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        if ($row = $stmt->fetch()) {
            if (!empty($row['page_permissions_json'])) {
                $pagePerms = json_decode($row['page_permissions_json'], true);
                if (is_array($pagePerms) && isset($pagePerms['kpi_upload'])) {
                    $hasNewPermissions = true;
                    $uploadSystems = array_values(array_intersect($pagePerms['kpi_upload']['system'] ?? [], ['j1', 'j2', 'j3']));
                    $uploadTypes = array_values(array_intersect($pagePerms['kpi_upload']['type'] ?? [], ['kpi', 'cost']));
                    $reportPermissions = $uploadTypes;
                    $restaurantPermissions = $uploadSystems;
                }
            }

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
    }
}

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
    $reportPermissions = array_values(array_intersect(['kpi', 'cost'], $reportPermissions));
    $restaurantPermissions = array_values(array_intersect(['j1', 'j2', 'j3'], $restaurantPermissions));
}

if (!in_array('kpi', $reportPermissions, true)) {
    if (in_array('cost', $reportPermissions, true)) {
        echo json_encode(['success' => false, 'redirect' => 'costedit']);
        exit;
    }
    $reportPermissions[] = 'kpi';
}
$reportPermissions = array_values(array_intersect(['kpi', 'cost'], $reportPermissions));

$restaurantConfigAllowed = array_intersect_key($restaurantConfigPhp, array_flip($restaurantPermissions));
if (empty($restaurantConfigAllowed) && !$hasNewPermissions) {
    $restaurantPermissions = ['j1', 'j2', 'j3'];
    $restaurantConfigAllowed = $restaurantConfigPhp;
}
if (empty($restaurantConfigAllowed) && $hasNewPermissions && !empty($restaurantPermissions)) {
    $restaurantConfigAllowed = array_intersect_key($restaurantConfigPhp, array_flip($restaurantPermissions));
}
if (empty($restaurantConfigAllowed)) {
    $restaurantPermissions = ['j1', 'j2', 'j3'];
    $restaurantConfigAllowed = $restaurantConfigPhp;
}

$defaultRestaurant = !empty($restaurantPermissions) ? $restaurantPermissions[0] : 'j1';

echo json_encode([
    'success' => true,
    'data' => [
        'availableReportTypes' => $reportPermissions,
        'reportDropdownEnabled' => count($reportPermissions) > 1,
        'availableRestaurants' => $restaurantPermissions,
        'restaurantDropdownEnabled' => count($restaurantPermissions) > 1,
        'restaurantConfig' => $restaurantConfigAllowed,
        'isOperationManager' => $isOperationManager,
        'defaultRestaurant' => $defaultRestaurant,
    ],
], JSON_UNESCAPED_UNICODE);
