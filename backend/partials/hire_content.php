<?php
/** Hire page markup (shared by legacy shell + React fragment). */
?>
    <div id="drawerOverlay" class="drawer-overlay-filter" onclick="toggleDrawer(false)"></div>

    <div class="main-content" data-hire-content-root>
        <div class="layout-container">

            <header class="header">
                <h1>招聘申请列表</h1>
                <div class="flex-row gap-10">
                    <button class="btn btn-default mobile-filter-btn" onclick="toggleDrawer(true)">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        筛选条件
                    </button>
                    <button class="btn btn-primary btn-export text-14" onclick="exportToExcel()">
                        ⬇ 导出 Excel
                    </button>
                </div>
            </header>

            <div id="filterContainer" class="filter-bar-container">

                <div class="drawer-header">
                    <h3>高级筛选</h3>
                    <button class="drawer-close" onclick="toggleDrawer(false)">&times;</button>
                </div>

                <div class="filter-content">

                    <div class="filter-row">
                        <div class="filter-label">申请公司</div>
                        <div class="chip-list" id="chipListCompany">
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-label">申请职位</div>
                        <div class="chip-list" id="chipListJob">
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-label">处理状态</div>
                        <div class="chip-list" id="chipListStatus">
                        </div>
                    </div>

                    <div class="search-date-row">
                        <div style="position: relative;">
                            <div id="smartSearchWrapper" class="smart-search-wrapper">
                                <span class="smart-search-icon">
                                    <svg class="icon-18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                                <input type="text" id="smartInput" class="smart-search-input"
                                    placeholder="搜索姓名 / 邮箱 / 手机号">
                            </div>
                            <div id="searchSuggestions" class="search-suggestions">
                                <div class="suggest-header">🔍 快速建议匹配</div>
                                <div id="suggestionList"></div>
                            </div>
                        </div>

                        <div class="filter-date-wrap">
                            <div class="date-input-wrapper">
                                <svg class="date-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <input type="text" id="dateRangePicker" class="form-control border-highlight date-input"
                                    placeholder="选择提交日期" readonly>
                            </div>
                            <div class="quick-select-wrapper">
                                <button id="btnQuickSelect" class="btn btn-default btn-quick-select">
                                    时段
                                    <svg class="icon-sm icon-margin" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="quickSelectMenu" class="quick-select-menu">
                                    <a href="#" data-range="today">今天</a>
                                    <a href="#" data-range="yesterday">昨天</a>
                                    <div class="menu-divider"></div>
                                    <a href="#" data-range="thisWeek">本周</a>
                                    <a href="#" data-range="lastWeek">上周</a>
                                    <div class="menu-divider"></div>
                                    <a href="#" data-range="thisMonth">这个月</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="activeFiltersArea" class="active-filters-bar">
                    <span class="text-12 text-muted font-bold">已选条件：</span>
                    <div id="activeFiltersList" class="flex-row items-center gap-8 flex-wrap" style="flex:1;"></div>
                    <button class="btn-link-action text-12" onclick="resetAllFilters()">清空全部</button>
                </div>

            </div><!-- /#filterContainer -->

            <div class="content-card">
                <div id="filterContainer-inner"></div><!-- placeholder -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>应聘者</th>
                                <th>所属公司</th>
                                <th>申请职位</th>
                                <th>联系方式</th>
                                <th>简历附件</th>
                                <th>申请时间</th>
                                <th>状态</th>
                                <th class="text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

                <footer class="pagination-bar">
                    <span id="totalCountInfo" class="text-muted text-14">共计 0 条记录</span>
                    <div class="page-controls" id="pageControls">
                        <button class="btn-page" id="btnPrevPage">上一页</button>
                        <span class="current-page" id="currentPageNum">1</span>
                        <button class="btn-page" id="btnNextPage">下一页</button>
                    </div>
                </footer>

            </div><!-- /.content-card -->

        </div>
        <div id="applicantModal" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h2>应聘者详情档案</h2>
                    <button class="btn-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="info-section">
                        <h3>基础申请信息</h3>
                        <div class="info-grid">
                            <span class="text-muted">申请公司：</span><span class="font-bold text-main"
                                id="modalCompany"></span>
                            <span class="text-muted">申请职位：</span><span class="font-bold text-primary"
                                id="modalJob"></span>
                            <span class="text-muted">提交时间：</span><span class="font-normal text-main"
                                id="modalTime"></span>
                        </div>
                        <h3 class="mt-24">个人联系资料</h3>
                        <div class="info-grid">
                            <span class="text-muted">中文姓名：</span><span class="font-bold text-main"
                                id="modalZhName"></span>
                            <span class="text-muted">英文姓名：</span><span class="font-bold text-main"
                                id="modalEnName"></span>
                            <span class="text-muted">性别：</span><span class="font-normal text-main"
                                id="modalGender"></span>
                            <span class="text-muted">电子邮箱：</span><span><a href="#" id="modalEmailLink"
                                    class="modal-link font-bold"></a></span>
                            <span class="text-muted">电话号码：</span><span class="font-bold text-main"
                                id="modalPhone"></span>
                            <span class="text-muted items-center flex-row">简历附件：</span>
                            <span><button class="btn-link btn-resume-modal" id="modalResumeBtn">📄
                                    下载/预览简历</button></span>
                        </div>
                    </div>
                    <div class="action-section">
                        <h3>HR 处理进度跟进</h3>
                        <div class="mb-8">
                            <label class="modal-field-label">修改当前状态：</label>
                            <select id="modalStatusSelect" class="form-control modal-select">
                                <option value="0">🔴 待处理</option>
                                <option value="1">🟡 沟通中</option>
                                <option value="2">🟢 已录用</option>
                                <option value="3">⚪ 已淘汰</option>
                            </select>
                        </div>
                        <div>
                            <label class="modal-field-label mt-24">内部备注 (仅 HR 可见)：</label>
                            <textarea id="modalRemarks" class="form-control" rows="7" style="width: 400px; min-width: 400px; max-width: 400px; resize: vertical; height: auto;"
                                placeholder="在此记录面试情况、期望薪资、背景调查结果等..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" onclick="closeModal()">取消关闭</button>
                    <button class="btn btn-primary" onclick="saveModalChanges()">保存更新</button>
                </div>
            </div>
        </div>

        <template id="rowTemplate">
            <tr class="table-row">
                <td>
                    <div>
                        <div class="font-bold text-main text-14 js-name"></div>
                        <div class="text-muted text-12 mt-4 js-subname"></div>
                    </div>
                </td>
                <td>
                    <span class="company-badge js-company"></span>
                </td>
                <td class="font-medium text-primary js-job-title"></td>
                <td>
                    <div class="text-14 text-main mb-4 js-email"></div>
                    <div class="text-12 text-muted js-phone"></div>
                </td>
                <td>
                    <button class="btn-link js-resume">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        预览 PDF
                    </button>
                </td>
                <td>
                    <div class="text-14 text-main mb-4 js-date"></div>
                    <div class="text-12 text-muted js-time"></div>
                </td>
                <td class="status-cell">
                    <div class="status-wrapper">
                        <div class="badge js-status-badge" title="点击修改状态"></div>
                    </div>
                </td>
                <td class="text-center">
                    <button class="btn-link-action btn-action-detail">详情</button>
                </td>
            </tr>
        </template>
        <!-- 全局状态选择 Popover（fixed 定位，不受表格 overflow 限制） -->
        <div id="globalStatusPopover">
            <a href="#" data-val="0" class="status-option">🔴 待处理</a>
            <a href="#" data-val="1" class="status-option">🟡 沟通中</a>
            <a href="#" data-val="2" class="status-option">🟢 已录用</a>
            <a href="#" data-val="3" class="status-option">⚪ 已淘汰</a>
        </div>

    <!-- 待处理申请通知 Toast -->
    <div id="pendingToast">
        <div class="toast-icon">🔔</div>
        <div class="toast-body">
            <div class="toast-title">有待审批的招聘申请</div>
            <div class="toast-msg" id="toastMsg">正在加载...</div>
        </div>
        <button class="toast-close" onclick="dismissToast()" title="关闭">×</button>
        <div class="toast-progress" id="toastProgress"></div>
    </div>

</div><!-- /.main-content -->
