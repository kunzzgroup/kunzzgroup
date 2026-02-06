<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>员工排班管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="../css/schedule_manager.css">
</head>
<body>
    <?php $basePath = '../'; include __DIR__ . '/../sidebar.php'; ?>
    <div class="container">
        <!-- 页面标题 -->
        <div class="header">
            <h1 id="page-title">员工排班管理系统 - J1</h1>
            <div class="restaurant-selector">
                <button class="selector-button" onclick="toggleRestaurantSelector()">
                    <span id="current-restaurant">J1</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="selector-dropdown" id="restaurant-dropdown">
                    <div class="dropdown-item active" data-restaurant="J1" onclick="switchRestaurant('J1')">J1</div>
                    <div class="dropdown-item" data-restaurant="J2" onclick="switchRestaurant('J2')">J2</div>
                </div>
            </div>
        </div>

        <!-- 控制栏 -->
        <div class="card">
            <div class="card-body">
                <div class="schedule-controls">
                    <div class="controls-left">
                        <!-- 年月选择器 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-calendar" style="color: #ff5c00;"></i>
                                选择年份和月份
                            </label>
                            <div class="enhanced-date-picker" id="schedule-date-picker">
                                <div class="date-part" data-type="year" onclick="showScheduleDateDropdown('year')">
                                    <span id="schedule-year-display">2024</span>
                                </div>
                                <span class="date-separator">年</span>
                                <div class="date-part" data-type="month" onclick="showScheduleDateDropdown('month')">
                                    <span id="schedule-month-display">01</span>
                                </div>
                                <span class="date-separator">月</span>
            
                                <div class="date-dropdown" id="schedule-dropdown"></div>
                            </div>
                        </div>
                        
                        <!-- 复制到下月按钮 -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label class="form-label" style="margin: 0; visibility: hidden;">占位</label>
                            <button class="btn-control btn-copy" onclick="copyToNextMonth()" title="将当前月的排班复制到下一个月">
                                <i class="fas fa-copy"></i> 复制到下月
                            </button>
                        </div>
                    </div>
                    <div class="controls-right">
                        <button id="saveAllBtn" class="btn-control" onclick="saveAllChanges()" style="background: #3b82f6; color: white; border-color: #3b82f6;">
                            <i class="fas fa-save"></i> 保存所有更改
                        </button>
                        <button class="btn-generate" onclick="showManagementPanel('shifts')">
                            <i class="fas fa-clock"></i> 班次管理
                        </button>
                        <button class="btn-generate" onclick="showManagementPanel('employees')">
                            <i class="fas fa-users"></i> 员工管理
                        </button>
                        <button class="btn-generate" onclick="showManagementPanel('legend')">
                            <i class="fas fa-info-circle"></i> 图例说明
                        </button>
                        <button class="btn-control" onclick="downloadPDFDirect()">
                            <i class="fas fa-file-pdf"></i> 下载PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 排班表 -->
        <div class="table-container">
            <div class="table-wrapper">
                <div id="scheduleContainer">
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <div class="loading" style="margin: 0 auto 10px;"></div>
                        <div>正在加载排班表...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 打印图例（仅在打印时显示） -->
        <div class="print-legend">
            <h3 style="text-align: center; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #000; border-top: 2px solid #000; padding-top: 20px;">班次与假期图例</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 900px; margin: 0 auto;">
                <div>
                    <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">班次 (Shifts)</h4>
                    <div id="printShiftLegend"></div>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">请假 (Leave)</h4>
                    <div id="printLeaveLegend"></div>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px;">假期 (Holiday)</h4>
                    <div id="printHolidayLegend"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 设置整列假期模态框 -->
    <div id="columnHolidayModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeColumnHolidayModal()">&times;</span>
                <h3 style="margin-top: 8px;"><i class="fas fa-calendar-day"></i> 设置公共假期</h3>
                <p id="columnDateInfo" style="color: #6b7280; font-size: 13px; margin-top: 4px;"></p>
            </div>
            <div class="form-group">
                <label>选择公共假期类型（将应用到所有员工）:</label>
                <div id="columnHolidayOptions" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;"></div>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-delete" onclick="clearColumnSchedule()">
                    <i class="fas fa-eraser"></i> 清除整列
                </button>
                <button class="btn-action btn-cancel" onclick="closeColumnHolidayModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
            </div>
        </div>
    </div>

    <!-- 原批量日期模态已移除 -->

    <!-- 设置排班模态框 -->
    <div id="scheduleModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeScheduleModal()">&times;</span>
                <h3 style="margin-top: 8px;">设置排班</h3>
                <p id="modalEmployeeInfo" style="color: #6b7280; font-size: 13px; margin-top: 4px;"></p>
            </div>
            <div class="form-group">
                <label>选择类型:</label>
                <select id="scheduleType" onchange="updateScheduleOptions()">
                    <option value="">-- 选择 --</option>
                    <option value="shift">班次</option>
                    <option value="leave">请假</option>
                </select>
            </div>
            <div class="form-group">
                <label>选择值:</label>
                <div id="scheduleOptions" style="min-height: 100px;"></div>
            </div>
            <div class="form-group">
                <label>备注:</label>
                <textarea id="scheduleNotes" rows="3" placeholder="可选的备注信息"></textarea>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-delete" onclick="deleteCurrentSchedule()">
                    <i class="fas fa-trash"></i> 删除
                </button>
                <button class="btn-action btn-cancel" onclick="closeScheduleModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
                <button class="btn-action btn-save" onclick="saveSchedule()">
                    <i class="fas fa-check"></i> 保存
                </button>
            </div>
        </div>
    </div>

    <!-- 添加/编辑员工模态框 -->
    <div id="employeeModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeEmployeeModal()">&times;</span>
                <h3 id="employeeModalTitle" style="margin-top: 8px;"><i class="fas fa-user-plus"></i> 添加员工</h3>
            </div>
            <input type="hidden" id="employeeId" value="">
            <div class="form-group">
                <label>姓名:</label>
                <input type="text" id="employeeName" required>
            </div>
            <div class="form-group">
                <label>手机号码:</label>
                <input type="tel" id="employeePhone" required>
            </div>
            <div class="form-group">
                <label>工作区域:</label>
                <select id="employeeWorkArea" required onchange="updatePositionOptions()">
                    <option value="service_line">Service Line</option>
                    <option value="sushi_bar">Sushi Bar</option>
                    <option value="kitchen">Kitchen</option>
                </select>
            </div>
            <div class="form-group">
                <label>职位:</label>
                <select id="employeePosition" required>
                    <option value="">-- 请选择职位 --</option>
                </select>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-cancel" onclick="closeEmployeeModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
                <button class="btn-action btn-save" onclick="saveEmployee()">
                    <i class="fas fa-check"></i> 保存
                </button>
            </div>
        </div>
    </div>

    <!-- 添加/编辑班次模态框 -->
    <div id="shiftModal" class="modal" style="z-index: 10001;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeShiftModal()">&times;</span>
                <h3 id="shiftModalTitle" style="margin-top: 8px;"><i class="fas fa-clock"></i> 添加班次</h3>
            </div>
            <input type="hidden" id="shiftId" value="">
            <div class="form-group">
                <label>班次代码 (如 A, B, C):</label>
                <input type="text" id="shiftCode" maxlength="10" required style="text-transform: uppercase;">
            </div>
            <div class="form-group">
                <label>开始时间:</label>
                <input type="time" id="shiftStartTime" required>
            </div>
            <div class="form-group">
                <label>结束时间:</label>
                <input type="time" id="shiftEndTime" required>
            </div>
            <div class="form-actions">
                <button class="btn-action btn-cancel" onclick="closeShiftModal()">
                    <i class="fas fa-times"></i> 取消
                </button>
                <button class="btn-action btn-save" onclick="saveShift()">
                    <i class="fas fa-check"></i> 保存
                </button>
            </div>
        </div>
    </div>

    <script>
    <script src="../js/schedule_manager.js"></script>
</body>
</html>
