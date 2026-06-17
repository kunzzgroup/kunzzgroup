<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('analytics', 'kpi_upload');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
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

}
elseif (
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
}
else {
    // 没有 session，也没有有效 cookie
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
// 修改这行：检查position是否为空或null
$position = (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User';
$avatarLetter = strtoupper($username[0]);
?>

<!DOCTYPE html>
<html lang="zh">

<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅成本管理系统</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/backend/css/cost.css?v=<?php echo time(); ?>">
</head>

<body class="restaurant-j1">
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>成本分析仪表盘</h1>
            </div>
        </div>

        <!-- 日期信息显示 -->
        <div class="date-info" id="date-info">
            正在加载数据...
        </div>
        <div id="app">
            <!-- Date Controls -->
            <div class="card date-controls-card">
                <div class="card-body">
                    <div class="date-controls">

                        <!-- 日期范围选择器 -->
                        <div class="date-control-group">
                            <label class="form-label">日期范围</label>
                            <div class="date-range-picker" id="date-range-picker" onclick="toggleCalendar()">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="date-range-display">选择日期范围</span>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- 月份选择器 -->
                        <div class="date-control-group">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i>
                                选择年份和月份
                            </label>
                            <div class="enhanced-date-picker month-only" id="month-date-picker">
                                <div class="date-part" data-type="year" onclick="showDateDropdown('month', 'year')">
                                    <span id="month-year-display">2024</span>
                                </div>
                                <span class="date-separator">年</span>
                                <div class="date-part" data-type="month" onclick="showDateDropdown('month', 'month')">
                                    <span id="month-month-display">01</span>
                                </div>
                                <span class="date-separator">月</span>

                                <div class="date-dropdown" id="month-dropdown">
                                </div>
                            </div>
                        </div>

                        <div class="date-control-group">
                            <label class="form-label">
                                <i class="fas fa-clock"></i>
                                快速选择
                            </label>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" onclick="toggleQuickSelectDropdown()">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span id="quick-select-text">时段</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-menu" id="quick-select-dropdown">
                                    <button class="dropdown-item" onclick="selectQuickRange('today')">今天</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('yesterday')">昨天</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('thisWeek')">本周</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('lastWeek')">上周</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('thisMonth')">这个月</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('lastMonth')">上个月</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('thisYear')">今年</button>
                                    <button class="dropdown-item" onclick="selectQuickRange('lastYear')">去年</button>
                                </div>
                            </div>
                        </div>

                        <!-- 报表类型选择器 -->
                        <div class="report-type-selector" onclick="toggleReportTypeDropdown()">
                            <button class="report-type-btn">
                                <i class="fas fa-chart-pie"></i>
                                成本报表
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="report-dropdown-menu" id="report-type-dropdown">
                                <a href="kpi.php" class="report-dropdown-item">
                                    <i class="fas fa-chart-line"></i> KPI 报表
                                </a>
                                <a href="cost.php" class="report-dropdown-item">
                                    <i class="fas fa-chart-pie"></i> 成本报表
                                </a>
                            </div>
                        </div>

                        <!-- 餐厅选择器 -->
                        <div class="restaurant-selector">
                            <button class="restaurant-btn dropdown-toggle" onclick="toggleRestaurantDropdown()">
                                -- <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="restaurant-dropdown-menu" id="restaurant-dropdown">
                                <div class="letter-selection">
                                    <div class="section-title">选择州属</div>
                                    <div class="letter-grid">
                                        <button class="letter-item" onclick="selectLetter('J')">J</button>
                                    </div>
                                </div>
                                <div class="number-selection" id="number-selection">
                                    <div class="section-title">选择餐厅</div>
                                    <div class="number-grid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 成本指标卡片 - 单行6列（J1时为4x2） -->
            <div class="cost-grid" id="cost-grid">
                <!-- 销售额 -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon text-green">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div>
                                <p class="cost-label">销售额 (RM)</p>
                                <p class="cost-value" id="total-sales">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 总成本 -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon text-green">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div>
                                <p class="cost-label">总成本 (RM)</p>
                                <p class="cost-value" id="total-cost">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 毛利润 -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <p class="cost-label">毛利润 (RM)</p>
                                <p class="cost-value" id="gross-total">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 成本百分比 -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div>
                                <p class="cost-label">成本率</p>
                                <p class="cost-value" id="cost-percent">0%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 库存（最后） -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <p class="cost-label">库存（最后）</p>
                                <p class="cost-value" id="last-stock">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 库存（现在） -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <div>
                                <p class="cost-label">库存（现在）</p>
                                <p class="cost-value" id="current-stock">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 供应→J2 (仅J1餐厅时显示) -->
                <div class="card supply-card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div>
                                <p class="cost-label">供应→J2 (RM)</p>
                                <p class="cost-value" id="j2-supply">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 供应→J3 (仅J1餐厅时显示) -->
                <div class="card supply-card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div>
                                <p class="cost-label">供应→J3 (RM)</p>
                                <p class="cost-value" id="j3-supply">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grab Food 成本 -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-shuttle-van"></i>
                            </div>
                            <div>
                                <p class="cost-label">Grab Food 成本 (RM)</p>
                                <p class="cost-value" id="total-grab-cost">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Foodpanda 成本 -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <svg viewBox="0 0 24 24" role="img" xmlns="http://www.w3.org/2000/svg" style="width: 1.6em; height: 1.6em; vertical-align: middle;" fill="currentColor">
                                    <path d="M4.224 0a3.14 3.14 0 0 0-3.14 3.127 3.1 3.1 0 0 0 1.079 2.36 11.811 11.811 0 0 0-2.037 6.639C.126 18.68 5.458 24 12 24c6.542 0 11.874-5.32 11.874-11.874a11.69 11.69 0 0 0-2.025-6.614 3.136 3.136 0 0 0 1.09-2.373A3.132 3.132 0 0 0 19.8.012a3.118 3.118 0 0 0-2.636 1.438A11.792 11.792 0 0 0 12.012.264 11.78 11.78 0 0 0 6.86 1.438 3.133 3.133 0 0 0 4.224 0zM12 1.198c1.713 0 3.331.396 4.78 1.102a10.995 10.995 0 0 1 4.29 3.715 10.89 10.89 0 0 1 1.882 6.135c.011 6.039-4.901 10.951-10.94 10.951-6.04 0-10.951-4.912-10.951-10.951 0-2.277.694-4.386 1.88-6.135A11.08 11.08 0 0 1 7.232 2.3 10.773 10.773 0 0 1 12 1.198zM7.367 6.345c-.853.012-1.743.292-2.28.653-1.031.682-2.29 2.156-2.085 4.181.191 2.025 1.785 3.283 2.612 3.283.826 0 1.234-.42 1.485-1.45.252-1.018 1.115-2.192 2.217-3.45s-.024-2.469-.024-2.469c-.393-.513-1.052-.727-1.755-.747a3.952 3.952 0 0 0-.17-.001zm9.233.007-.17.001c-.702.02-1.358.233-1.746.752 0 0-1.126 1.21-.024 2.469 1.114 1.258 1.965 2.432 2.217 3.45.251 1.019.659 1.438 1.485 1.45.827 0 2.409-1.258 2.612-3.283.204-2.025-1.054-3.51-2.084-4.182-.544-.36-1.437-.643-2.29-.657zm-8.962 2c.348 0 .624.275.624.623-.012.335-.288.623-.624.623a.619.619 0 0 1-.623-.623.62.62 0 0 1 .623-.624zm8.891 0c.348 0 .623.275.623.623-.012.335-.287.623-.623.623a.619.619 0 0 1-.623-.623c0-.348.288-.624.623-.624zm-4.541 4.025c-.527 0-2.06.096-2.06.587 0 .887 1.88 1.522 2.06 1.474.18.048 2.06-.587 2.06-1.474 0-.49-1.52-.587-2.06-.587zM9.076 15.17c0 1.414 1.294 2.564 2.912 2.564 1.618 0 2.924-1.15 2.924-2.564z" />
                                </svg>
                            </div>
                            <div>
                                <p class="cost-label">Foodpanda 成本 (RM)</p>
                                <p class="cost-value" id="total-foodpanda-cost">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shopee Food 成本 -->
                <div class="card">
                    <div class="card-body">
                        <div class="cost-card-vertical">
                            <div class="icon dynamic-color">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div>
                                <p class="cost-label">Shopee Food 成本 (RM)</p>
                                <p class="cost-value" id="total-shopee-cost">0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chart - 全宽显示 -->
            <div class="main-chart-container">
                <div class="card">
                    <div class="card-body">
                        <div class="chart-header">
                            <h3 id="main-chart-title">
                                成本趋势分析</h3>

                            <!-- 数据类型切换按钮组 -->
                            <div class="chart-data-buttons">
                                <button class="chart-data-btn active" data-type="totalCost"
                                    onclick="switchChartData('totalCost')">
                                    总成本
                                </button>
                                <button class="chart-data-btn" data-type="grossTotal"
                                    onclick="switchChartData('grossTotal')">
                                    毛利润
                                </button>
                                <button class="chart-data-btn" data-type="costPercent"
                                    onclick="switchChartData('costPercent')">
                                    成本率
                                </button>
                                <button class="chart-data-btn" data-type="deliveryCost"
                                    onclick="switchChartData('deliveryCost')">
                                    外卖成本
                                </button>
                            </div>

                            <div class="date-range-display" id="chart-date-range">
                            </div>
                        </div>
                        <div class="chart-container chart-fill">
                            <button class="chart-back-button" id="cost-chart-back" onclick="exitDrillDown()">
                                <i class="fas fa-arrow-left"></i> 返回年度视图
                            </button>
                            <canvas id="cost-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="card detail-card">
                <div class="card-body">
                    <h3>
                        详细数据</h3>
                </div>
                <div class="table-scroll">
                    <table class="table" id="dashboard-table">
                        <thead>
                            <tr id="table-header">
                                <th>日期</th>
                                <th>销售额</th>
                                <th>饮料成本</th>
                                <th>厨房成本</th>
                                <th>Grab Food</th>
                                <th>Foodpanda</th>
                                <th>Shopee Food</th>
                                <th>总成本</th>
                                <th>毛利润</th>
                                <th>成本率 (%)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- 日历弹窗 -->
    <div class="calendar-popup" id="calendar-popup">
        <div class="calendar-header">
            <button class="calendar-nav-btn" onclick="event.stopPropagation(); changeMonth(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="calendar-month-year" onclick="event.stopPropagation();">
                <select id="calendar-month-select" onchange="renderCalendar()">
                    <option value="0">1月</option>
                    <option value="1">2月</option>
                    <option value="2">3月</option>
                    <option value="3">4月</option>
                    <option value="4">5月</option>
                    <option value="5">6月</option>
                    <option value="6">7月</option>
                    <option value="7">8月</option>
                    <option value="8">9月</option>
                    <option value="9">10月</option>
                    <option value="10">11月</option>
                    <option value="11">12月</option>
                </select>
                <select id="calendar-year-select" onchange="renderCalendar()">
                    <!-- 动态生成年份 -->
                </select>
            </div>
            <button class="calendar-nav-btn" onclick="event.stopPropagation(); changeMonth(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="calendar-weekdays">
            <div class="calendar-weekday">日</div>
            <div class="calendar-weekday">一</div>
            <div class="calendar-weekday">二</div>
            <div class="calendar-weekday">三</div>
            <div class="calendar-weekday">四</div>
            <div class="calendar-weekday">五</div>
            <div class="calendar-weekday">六</div>
        </div>
        <div class="calendar-days" id="calendar-days">
            <!-- 动态生成日期 -->
        </div>
    </div>
    <script src="js/cost.js?v=<?php echo time(); ?>"></script>
</body>

</html>