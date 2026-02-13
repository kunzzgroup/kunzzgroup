<?php
// 包含会话验证
require_once 'session_check.php';

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

if (!isset($_SESSION)) {
    @session_start();
}

// 标记是否使用了新权限系统
$hasNewPermissions = false;

if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅成本管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅成本管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/costedit.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>TOKYO JAPANESE CUISINE 成本后台</h1>
            </div>
            <div class="controls">
                <!-- 报表类型选择器 -->
                <?php if ($showReportDropdown): ?>
                <div class="report-type-selector" onclick="toggleReportTypeDropdown()">
                    <button class="report-type-btn">
                        <i class="fas fa-chart-pie"></i>
                        <?php echo $reportLabelMap['cost']; ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="report-dropdown-menu" id="report-type-dropdown">
                        <?php if (in_array('kpi', $reportPermissions, true)): ?>
                        <a href="kpiedit.php" class="report-dropdown-item">
                            <i class="fas fa-chart-line"></i> <?php echo $reportLabelMap['kpi']; ?>
                        </a>
                        <?php endif; ?>
                        <?php if (in_array('cost', $reportPermissions, true)): ?>
                        <a href="costedit.php" class="report-dropdown-item">
                            <i class="fas fa-chart-pie"></i> <?php echo $reportLabelMap['cost']; ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="report-type-selector report-type-selector--disabled">
                    <button class="report-type-btn" style="cursor:default;">
                        <i class="fas fa-chart-pie"></i>
                        <?php echo $reportLabelMap['cost']; ?>
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- 餐厅选择器 -->
                <div class="restaurant-selector">
                    <div class="restaurant-prefix"><?php echo substr($restaurantConfigAllowed[$defaultRestaurant]['name'], 0, 1); ?></div>
                    <div class="number-dropdown">
                        <button class="number-btn dropdown-toggle"<?php if ($showRestaurantDropdown): ?> onclick="toggleNumberDropdown()"<?php else: ?> style="cursor:default;"<?php endif; ?>>
                            <?php echo $restaurantConfigAllowed[$defaultRestaurant]['number']; ?>
                            <?php if ($showRestaurantDropdown): ?>
                            <i class="fas fa-chevron-down"></i>
                            <?php endif; ?>
                        </button>
                        <div class="number-dropdown-menu" id="number-dropdown"<?php if (!$showRestaurantDropdown): ?> style="display:none;"<?php endif; ?>>
                            <?php if ($showRestaurantDropdown): ?>
                            <div class="number-grid">
                                <?php foreach ($restaurantPermissions as $storeKey): ?>
                                <button class="number-item" onclick="selectNumber(<?php echo $restaurantConfigAllowed[$storeKey]['number']; ?>)"><?php echo $restaurantConfigAllowed[$storeKey]['number']; ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 月份选择器 -->
        <div class="month-selector">
            <div>
                <label for="year-select">年份:</label>
                <select id="year-select" onchange="loadMonthData()">
                </select>
            </div>
            <div>
                <label for="month-select">月份:</label>
                <select id="month-select" onchange="loadMonthData()">
                    <option value="1">1月</option>
                    <option value="2">2月</option>
                    <option value="3">3月</option>
                    <option value="4">4月</option>
                    <option value="5">5月</option>
                    <option value="6">6月</option>
                    <option value="7">7月</option>
                    <option value="8">8月</option>
                    <option value="9">9月</option>
                    <option value="10">10月</option>
                    <option value="11">11月</option>
                    <option value="12">12月</option>
                </select>
            </div>
            <div id="current-restaurant-info" class="stat-item">
                <i class="fas fa-store"></i>
                <span>当前: <span class="stat-value"><?php echo $restaurantConfigAllowed[$defaultRestaurant]['name']; ?></span></span>
            </div>
        </div>
        
        <!-- Excel表格 -->
        <div class="excel-container">
            <div class="action-buttons">
                <div class="stats-info" id="month-stats">
                    <div class="stat-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>已填写: <span class="stat-value" id="filled-days">0</span> 天</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>月总销售额: RM <span class="stat-value" id="total-sales">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-chart-pie"></i>
                        <span>月总成本: RM <span class="stat-value" id="total-cost">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>月总毛利润: RM <span class="stat-value" id="total-profit">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-percentage"></i>
                        <span>平均成本率: <span class="stat-value" id="avg-cost-percent">0</span>%</span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div class="stock-input-container">
                        <label for="current-stock-input">
                            <i class="fas fa-warehouse"></i>
                            当前库存 (RM):
                        </label>
                        <input type="number" id="current-stock-input" min="0" step="0.01" 
                               placeholder="0.00" oninput="formatStockInput(this)">
                    </div>
                    <button class="btn btn-primary" onclick="saveAllData()">
                        <i class="fas fa-save"></i>
                        保存本月数据
                    </button>
                </div>
            </div>
            <div class="table-scroll-container">
            <table class="excel-table" id="excel-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">日期</th>
                        <th style="width: 12%;">销售额</th>
                        <th style="width: 12%;">饮料成本</th>
                        <th style="width: 12%;">厨房成本</th>
                        <th style="width: 12%;">总成本</th>
                        <th style="width: 12%;">毛利润</th>
                        <th style="width: 10%;">成本率 (%)</th>
                        <th style="width: 10%;">操作</th>
                    </tr>
                </thead>
                <tbody id="excel-tbody">
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    </div>

    <script>
        const PAGE_CONFIG = {
            availableReportTypes: <?php echo json_encode($reportPermissions); ?>,
            reportDropdownEnabled: <?php echo $showReportDropdown ? 'true' : 'false'; ?>,
            availableRestaurants: <?php echo json_encode($restaurantPermissions); ?>,
            restaurantDropdownEnabled: <?php echo $showRestaurantDropdown ? 'true' : 'false'; ?>,
            restaurantConfig: <?php echo json_encode($restaurantConfigAllowed); ?>,
            defaultRestaurant: '<?php echo $defaultRestaurant; ?>'
        };
    </script>
    <script src="js/costedit.js?v=<?php echo time(); ?>"></script>
</body>
</html>
</body>
</html>

