<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐厅数据管理后台 - Excel模式</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/kpiedit.css">
</head>
<body>
    <?php $basePath = '../'; include __DIR__ . '/../sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>TOKYO JAPANESE CUISINE 数据后台</h1>
            </div>
            <div class="controls">
                <!-- 报表类型选择器 -->
                <?php if ($showReportDropdown): ?>
                <div class="report-type-selector" onclick="toggleReportTypeDropdown()">
                    <button class="report-type-btn">
                        <i class="fas fa-chart-bar"></i>
                        <?php echo $reportLabelMap['kpi']; ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="report-dropdown-menu" id="report-type-dropdown">
                        <?php if (in_array('kpi', $reportPermissions, true)): ?>
                        <a href="kpiedit" class="report-dropdown-item">
                            <i class="fas fa-chart-line"></i> KPI数据录入
                        </a>
                        <a href="costedit" class="report-dropdown-item">
                            <i class="fas fa-wallet"></i> 支出数据录入
                        </a>
                        <?php endif; ?>
                        <?php if (in_array('cost', $reportPermissions, true)): ?>
                        <a href="costedit" class="report-dropdown-item">
                            <i class="fas fa-chart-pie"></i> <?php echo $reportLabelMap['cost']; ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="report-type-selector report-type-selector--disabled">
                    <button class="report-type-btn" style="cursor:default;">
                        <i class="fas fa-chart-bar"></i>
                        <?php echo $reportLabelMap['kpi']; ?>
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
                    <!-- 动态生成年份选项 -->
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
                        <i class="fas fa-chart-line"></i>
                        <span>月总净利润额: RM <span class="stat-value" id="total-sales">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>月总利润额: RM <span class="stat-value" id="total-tender">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span>月总顾客人数: <span class="stat-value" id="total-diners">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-table"></i>
                        <span>月总桌数: <span class="stat-value" id="total-tables">0</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calculator"></i>
                        <span>月总人均消费: RM <span class="stat-value" id="avg-per-customer">0</span></span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
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
                        <th style="width: 7%;">日期</th>
                        <th style="width: 8%;">总销售额</th>
                        <th style="width: 6%;">折扣</th>
                        <th style="width: 8%;">净销售额</th>
                        <th style="width: 7%;">税</th>
                        <th style="width: 7%;">服务费</th>
                        <th style="width: 7%;">调整金额</th>
                        <th style="width: 8%;">投标金额</th>
                        <th style="width: 5%;">桌数总数</th>
                        <th style="width: 5%;">顾客总数</th>
                        <th style="width: 8%;">人均消费</th>
                        <th style="width: 5%;">新客人数</th>
                        <th style="width: 5%;">常客人数</th>
                        <th style="width: 7%;">常客人率 (%)</th>
                        <th style="width: 9%;">操作</th>
                    </tr>
                </thead>
                <tbody id="excel-tbody">
                    <!-- 动态生成行 -->
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    <!-- 动态通知内容 -->
    </div>

    <script>
        window.kpiConfig = {
            reportPermissions: <?php echo json_encode($reportPermissions); ?>,
            showReportDropdown: <?php echo $showReportDropdown ? 'true' : 'false'; ?>,
            restaurantPermissions: <?php echo json_encode($restaurantPermissions); ?>,
            showRestaurantDropdown: <?php echo $showRestaurantDropdown ? 'true' : 'false'; ?>,
            restaurantConfigAllowed: <?php echo json_encode($restaurantConfigAllowed); ?>,
            isOperationManager: <?php echo $isOperationManager ? 'true' : 'false'; ?>,
            defaultRestaurant: '<?php echo $defaultRestaurant; ?>'
        };
    </script>
    <script src="../js/kpiedit.js"></script>
</body>
</html>
