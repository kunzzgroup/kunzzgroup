<body class="restaurant-j1">
   <?php include CORE_PATH . '/sidebar.php'; ?>
        <div class="container">
            <div class="header">
                <div>
                    <h1>成本分析仪表盘</h1>
                </div>
            </div>
            
            <!-- 日期信息显示 -->
            <div class="date-info" id="date-info" style="margin-bottom: 16px; border: 1px solid #e5e7eb;">
                正在加载数据...
            </div>
            <div id="app">         
            <!-- Date Controls -->
            <div class="card" style="margin-bottom: clamp(14px, 1.67vw, 32px);">
                <div class="card-body">
                    <div class="date-controls">
    
                        <!-- 日期范围选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0;">日期范围</label>
                            <div class="date-range-picker" id="date-range-picker" onclick="toggleCalendar()">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="date-range-display">选择日期范围</span>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- 月份选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-calendar" style="color: #000000ff;"></i>
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

                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-clock" style="color: #000000ff;"></i>
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
                </div>

                <!-- Main Chart - 全宽显示 -->
                <div class="main-chart-container">
                    <div class="card" style="height: 400px;">
                        <div class="card-body" style="height: 100%; display: flex; flex-direction: column;">
                            <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h3 id="main-chart-title" style="font-size: clamp(14px, 1.04vw, 20px); font-weight: 600; color: #111827; margin: 0;">成本趋势分析</h3>
                                
                                <!-- 数据类型切换按钮组 -->
                                <div class="chart-data-buttons" style="display: flex; gap: 8px; align-items: center;">
                                    <button class="chart-data-btn active" data-type="totalCost" onclick="switchChartData('totalCost')">
                                        总成本
                                    </button>
                                    <button class="chart-data-btn" data-type="grossTotal" onclick="switchChartData('grossTotal')">
                                        毛利润
                                    </button>
                                    <button class="chart-data-btn" data-type="costPercent" onclick="switchChartData('costPercent')">
                                        成本率
                                    </button>
                                </div>
                                
                                <div class="date-range-display" id="chart-date-range" style="font-size: clamp(8px, 0.74vw, 14px); color: #6b7280; font-weight: 500;">
                                </div>
                            </div>
                            <div class="chart-container" style="flex: 1;">
                                <button class="chart-back-button" id="cost-chart-back" onclick="exitDrillDown()">
                                    <i class="fas fa-arrow-left"></i> 返回年度视图
                                </button>
                                <canvas id="cost-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                        
            <!-- Detail Table -->
            <div class="card">
                <div class="card-body" style="padding-bottom: 0;">
                    <h3 style="font-size: clamp(14px, 1.04vw, 20px); font-weight: 600; color: #111827; margin-bottom: 24px;">详细数据</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table" id="dashboard-table">
                        <thead>
                            <tr id="table-header">
                                <th>日期</th>
                                <th>销售额</th>
                                <th>饮料成本</th>
                                <th>厨房成本</th>
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
    <div class="calendar-popup" id="calendar-popup" style="display: none;">
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