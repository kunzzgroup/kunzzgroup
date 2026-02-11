<link rel="stylesheet" href="/core/css/sidebar.css">
<script src="/core/js/sidebar.js" defer></script>

<!-- 侧边菜单 -->
<div class="informationmenu">
    <div class="informationmenu-header">
        <div class="user-avatar-dropdown">
            <div id="user-avatar" class="user-avatar"><?php echo $avatarLetter; ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo $username; ?></div>
                <div class="user-position"><?php echo $position; ?></div>
            </div>
        </div>

        <div class="sidebar-menu-hamburger" id="sidebarToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="informationmenu-content">
        <?php if ($canSeeBrand): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="brand-items">
                <img src="/images/images/网页照片上传.svg" alt="" class="section-icon">
                集团架构
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="brand-items">
                <?php if (!empty($submenuVisibility['brand']['kunzz_holdings'])): ?>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        KUNZZ HOLDINGS SDN BHD
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">KUNZZ HOLDINGS SDN BHD</div>
                        </div>
                        <div class="submenu-content">
                            <a href="/pages/corporate_blueprint.php" class="submenu-item">企业蓝图</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Tokyo Japanese Cuisine Sdn Bhd -->
                <?php if (!empty($submenuVisibility['brand']['tokyo_cuisine'])): ?>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        TOKYO JAPANESE CUISINE SDN BHD
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">TOKYO JAPANESE CUISINE SDN BHD</div>
                        </div>
                        <div class="submenu-content">
                            <?php if (!empty($submenuVisibility['brand']['j1'])): ?>
                            <a href="#" class="submenu-item expandable" data-target="j1-options">
                                J1 (MIDVALLEY)
                                <span class="expand-arrow">›</span>
                            </a>
                            <div class="sub-options" id="j1-options">
                                <?php if (!empty($submenuVisibility['brand']['j1_schedule'])): ?>
                                <a href="/modules/schedule_manager.php?restaurant=J1" class="sub-option">员工排班表</a>
                                <a href="/modules/phone_manage.php?restaurant=J1" class="sub-option">员工手机记录</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($submenuVisibility['brand']['j2'])): ?>
                            <a href="#" class="submenu-item expandable" data-target="j2-options">
                                J2 (PARADIGM MALL)
                                <span class="expand-arrow">›</span>
                            </a>
                            <div class="sub-options" id="j2-options">
                                <?php if (!empty($submenuVisibility['brand']['j2_schedule'])): ?>
                                <a href="/modules/schedule_manager.php?restaurant=J2" class="sub-option">员工排班表</a>
                                <a href="/modules/phone_manage.php?restaurant=J2" class="sub-option">员工手机记录</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Tokyo Izakaya Sdn Bhd -->
                <?php if (!empty($submenuVisibility['brand']['tokyo_izakaya'])): ?>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        TOKYO IZAKAYA SDN BHD
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">TOKYO IZAKAYA SDN BHD</div>
                        </div>
                        <div class="submenu-content">
                            <?php if (!empty($submenuVisibility['brand']['j3'])): ?>
                            <a href="#" class="submenu-item expandable" data-target="j3-options">
                                J3 (DESA TEBRAU)
                                <span class="expand-arrow">›</span>
                            </a>
                            <div class="sub-options" id="j3-options">
                                <?php if (!empty($submenuVisibility['brand']['j3_schedule'])): ?>
                                <a href="/modules/schedule_manager.php?restaurant=J3" class="sub-option">员工排班表</a>
                                <a href="/modules/phone_manage.php?restaurant=J3" class="sub-option">员工手机记录</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeAnalytics): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="analytics-items">
                <img src="/images/images/运营分析与报表.svg" alt="" class="section-icon">
                营收数据
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="analytics-items">
                <?php if (!empty($submenuVisibility['analytics']['kpi_report'])): ?>
                <div class="menu-item-wrapper">
                    <a href="/modules/kpi.php" class="informationmenu-item">
                        KPI报表
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($submenuVisibility['analytics']['kpi_upload'])): ?>
                <div class="menu-item-wrapper">
                    <a href="/modules/<?php echo $kpiUploadDefaultPage; ?>" class="informationmenu-item">
                        数据上传
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeHR): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="hr-items">
                <img src="/images/images/人事与资源管理.svg" alt="" class="section-icon">
                人事管理
            </div>
            <div class="dropdown-menu-items" id="hr-items">               
                <?php if (!empty($submenuVisibility['hr']['staff_management'])): ?>
                <div class="menu-item-wrapper">
                    <a href="/modules/generatecode.php" class="informationmenu-item">
                        职员管理
                    </a>
                </div>
                <div class="menu-item-wrapper">
                    <a href="/modules/qna.php" class="informationmenu-item">
                        问卷回答
                    </a>
                </div>
                <div class="menu-item-wrapper">
                    <a href="/modules/evaluation_form.php" class="informationmenu-item">
                        考核表单
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeResource): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="resource-items">
                <img src="/images/images/资源库管理.svg" alt="" class="section-icon">
                资源总库
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="resource-items">               
                <?php if (!empty($submenuVisibility['resource']['stock_inventory'])): ?>
                <div class="menu-item-wrapper">
                    <a href="/modules/stocklistall.php" class="informationmenu-item" id="stock-link" onclick="redirectToAllowedStockPage(event)">
                        库存
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($submenuVisibility['resource']['dishware'])): ?>
                <div class="menu-item-wrapper">
                    <a href="/core/templates/dishware_stock.php" class="informationmenu-item">
                        碗碟
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($submenuVisibility['resource']['price_comparison'])): ?>
                <div class="menu-item-wrapper">
                    <a href="/modules/price.php" class="informationmenu-item">
                        价格对比
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canSeeVisual): ?>
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="photoupload-items">
                <img src="/images/images/网页照片上传.svg" alt="" class="section-icon">
                视觉管理
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="photoupload-items">
                <div class="menu-item-wrapper">
                    <a href="/cms/bgmusicupload.php" class="informationmenu-item">
                        背景音乐
                    </a>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        首页
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">首页</div>
                        </div>
                        <div class="submenu-content">
                            <a href="/cms/homepage1upload.php" class="submenu-item">第一页</a>
                        </div>
                    </div>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        关于我们
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">关于我们</div>
                        </div>
                        <div class="submenu-content">
                            <a href="/cms/aboutpage1upload.php" class="submenu-item">第一页</a>
                            <a href="/cms/aboutpage4upload.php" class="submenu-item">第四页</a>
                        </div>
                    </div>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        旗下品牌
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">旗下品牌</div>
                        </div>
                        <div class="submenu-content">
                            <a href="/cms/tokyopage1upload.php" class="submenu-item">第一页</a>
                            <a href="/cms/tokyopage5upload.php" class="submenu-item">第五页</a>
                        </div>
                    </div>
                </div>
                <div class="menu-item-wrapper">
                    <a href="#" class="informationmenu-item">
                        加入我们
                        <span class="informationmenu-arrow">›</span>
                    </a>
                    <div class="submenu">
                        <div class="submenu-header">
                            <div class="submenu-title">加入我们</div>
                        </div>
                        <div class="submenu-content">
                            <a href="/core/templates/joinpage1upload.php" class="submenu-item">第一页</a>
                            <a href="/core/templates/joinpage2upload.php" class="submenu-item">第二页</a>
                            <a href="/core/templates/joinpage3upload.php" class="submenu-item">第三页</a>
                        </div>
                    </div>
                </div>
                <div class="menu-item-wrapper">
                    <a href="/cms/corporate_blueprint_edit.php" class="informationmenu-item">
                        企业蓝图管理
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="informationmenu-footer">
            <button class="logout-btn" onclick="location.href='/core/logout.php'">
                登出
            </button>
        </div>
    </div>
</div>
