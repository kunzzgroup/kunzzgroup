// Sidebar Logic
const BASE_PATH = document.currentScript?.getAttribute('data-base-path') || '../';

document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
});

async function initSidebar() {
    try {
        const response = await fetch(`${BASE_PATH}api/sidebar_data`);
        const data = await response.json();

        if (!data.success) {
            console.error('Sidebar load failed:', data.message);
            // Handle unauthorized - maybe redirect?
            // window.location.href = '../frontend/index.php'; // Optional
            return;
        }

        renderUserInfo(data.user);
        renderMenu(data.permissions, data.user, data.urls);
        attachSidebarEvents();

        // Restore state
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
            document.querySelector('.informationmenu')?.classList.add('collapsed');
            document.getElementById('sidebarToggle')?.classList.add('collapsed');
        }
        setTimeout(() => document.body.classList.add('sidebar-transition'), 100);

    } catch (e) {
        console.error('Error initializing sidebar:', e);
    }
}

function renderUserInfo(user) {
    const container = document.getElementById('user-info-container');
    if (!container) return;

    container.innerHTML = `
        <div class="user-avatar-dropdown">
            <div id="user-avatar" class="user-avatar">${user.avatar_letter}</div>
            <div class="user-info">
                <div class="user-name">${user.username}</div>
                <div class="user-position">${user.position}</div>
            </div>
        </div>
        <div class="sidebar-menu-hamburger" id="sidebarToggle">
            <span></span><span></span><span></span>
        </div>
    `;
}

function renderMenu(perms, user, urls) {
    const container = document.getElementById('menu-content');
    if (!container) return;

    let html = '';

    // Brand Section
    if (perms.sections.brand) {
        html += createSection('brand-items', '网页照片上传.svg', '集团架构',
            renderBrandItems(perms)
        );
    }

    // Analytics Section
    if (perms.sections.analytics) {
        html += createSection('analytics-items', '运营分析与报表.svg', '营收数据',
            renderAnalyticsItems(perms, urls)
        );
    }

    // HR Section
    if (perms.sections.hr) {
        html += createSection('hr-items', '人事与资源管理.svg', '人事管理',
            renderHRItems(perms)
        );
    }

    // Resource Section
    if (perms.sections.resource) {
        html += createSection('resource-items', '资源库管理.svg', '资源总库',
            renderResourceItems(perms)
        );
    }

    // Visual Section
    if (perms.sections.visual) {
        html += createSection('photoupload-items', '网页照片上传.svg', '视觉管理',
            renderVisualItems(perms)
        );
    }

    // Footer
    html += `
        <div class="informationmenu-footer">
            <button class="logout-btn" onclick="location.href='${BASE_PATH}includes/logout'">
                登出
            </button>
        </div>
    `;

    container.innerHTML = html;
}

function createSection(id, icon, title, contentHtml) {
    if (!contentHtml) return ''; // Don't render empty sections
    return `
        <div class="informationmenu-section">
            <div class="informationmenu-section-title" data-target="${id}">
                <img src="${BASE_PATH}images/images/${icon}" alt="" class="section-icon">
                ${title}
                <span class="section-arrow">⮞</span>
            </div>
            <div class="dropdown-menu-items" id="${id}">
                ${contentHtml}
            </div>
        </div>
    `;
}

// --- Item Renderers ---

function renderBrandItems(perms) {
    let html = '';
    const p = perms.brand;

    if (p.kunzz_holdings) {
        html += createMenuItemWithSubmenu('KUNZZ HOLDINGS SDN BHD', 'KUNZZ HOLDINGS SDN BHD',
            `<a href="${BASE_PATH}brand/corporate_blueprint" class="submenu-item">企业蓝图</a>`
        );
    }

    if (p.tokyo_cuisine) {
        let subContent = '';
        if (p.j1) {
            subContent += createExpandableSubmenuItem('j1-options', 'J1 (MIDVALLEY)',
                (p.j1_schedule ? `<a href="${BASE_PATH}hr/schedule_manager?restaurant=J1" class="sub-option">员工排班表</a>
                                  <a href="${BASE_PATH}hr/phone_manage?restaurant=J1" class="sub-option">员工手机记录</a>` : '')
            );
        }
        if (p.j2) {
            subContent += createExpandableSubmenuItem('j2-options', 'J2 (PARADIGM MALL)',
                (p.j2_schedule ? `<a href="${BASE_PATH}hr/schedule_manager?restaurant=J2" class="sub-option">员工排班表</a>
                                  <a href="${BASE_PATH}hr/phone_manage?restaurant=J2" class="sub-option">员工手机记录</a>` : '')
            );
        }
        if (subContent) {
            html += createMenuItemWithSubmenu('TOKYO JAPANESE CUISINE SDN BHD', 'TOKYO JAPANESE CUISINE SDN BHD', subContent);
        }
    }

    if (p.tokyo_izakaya) {
        let subContent = '';
        if (p.j3) {
            subContent += createExpandableSubmenuItem('j3-options', 'J3 (DESA TEBRAU)',
                (p.j3_schedule ? `<a href="${BASE_PATH}hr/schedule_manager?restaurant=J3" class="sub-option">员工排班表</a>
                                  <a href="${BASE_PATH}hr/phone_manage?restaurant=J3" class="sub-option">员工手机记录</a>` : '')
            );
        }
        if (subContent) {
            html += createMenuItemWithSubmenu('TOKYO IZAKAYA SDN BHD', 'TOKYO IZAKAYA SDN BHD', subContent);
        }
    }

    return html;
}

function renderAnalyticsItems(perms, urls) {
    let html = '';
    const p = perms.analytics;
    const kpiUploadLink = (urls && urls.kpi_upload) ? `${BASE_PATH}${urls.kpi_upload}` : '#';

    if (p.kpi_report) html += createSimpleMenuItem(`${BASE_PATH}analytics/kpi`, 'KPI报表');
    if (p.kpi_upload) html += createSimpleMenuItem(kpiUploadLink, '数据上传');
    return html;
}

function renderHRItems(perms) {
    let html = '';
    const p = perms.hr;
    if (p.staff_management) {
        html += createSimpleMenuItem(`${BASE_PATH}hr/generatecode`, '职员管理');
        html += createSimpleMenuItem(`${BASE_PATH}hr/qna`, '问卷回答');
        html += createSimpleMenuItem(`${BASE_PATH}hr/evaluation_form`, '考核表单');
    }
    return html;
}

function renderResourceItems(perms) {
    let html = '';
    const p = perms.resource;
    if (p.stock_inventory) {
        // Special onClick for inventory
        html += `<div class="menu-item-wrapper">
                    <a href="${BASE_PATH}resource/stocklistall" class="informationmenu-item" onclick="redirectToAllowedStockPage(event)">
                        库存
                    </a>
                 </div>`;
    }
    if (p.dishware) html += createSimpleMenuItem(`${BASE_PATH}resource/dishware_stock`, '碗碟');
    if (p.price_comparison) html += createSimpleMenuItem(`${BASE_PATH}resource/price`, '价格对比');
    return html;
}

function renderVisualItems(perms) {
    let html = '';
    // Visual links are hardcoded in groups for now as per original
    html += createSimpleMenuItem(`${BASE_PATH}visual/bgmusicupload`, '背景音乐');

    html += createMenuItemWithSubmenu('首页', '首页',
        `<a href="${BASE_PATH}visual/homepage1upload" class="submenu-item">第一页</a>`
    );

    html += createMenuItemWithSubmenu('关于我们', '关于我们',
        `<a href="${BASE_PATH}visual/aboutpage1upload" class="submenu-item">第一页</a>
         <a href="${BASE_PATH}visual/aboutpage4upload" class="submenu-item">第四页</a>`
    );

    html += createMenuItemWithSubmenu('旗下品牌', '旗下品牌',
        `<a href="${BASE_PATH}visual/tokyopage1upload" class="submenu-item">第一页</a>
         <a href="${BASE_PATH}visual/tokyopage5upload" class="submenu-item">第五页</a>`
    );

    html += createMenuItemWithSubmenu('加入我们', '加入我们',
        `<a href="${BASE_PATH}visual/joinpage1upload" class="submenu-item">第一页</a>
         <a href="${BASE_PATH}visual/joinpage2upload" class="submenu-item">第二页</a>
         <a href="${BASE_PATH}visual/joinpage3upload" class="submenu-item">第三页</a>`
    );

    html += createSimpleMenuItem(`${BASE_PATH}visual/corporate_blueprint_edit`, '企业蓝图管理');

    return html;
}

// --- HTML Generators ---

function createSimpleMenuItem(href, text) {
    return `<div class="menu-item-wrapper">
                <a href="${href}" class="informationmenu-item">${text}</a>
            </div>`;
}

function createMenuItemWithSubmenu(label, title, subContent) {
    return `<div class="menu-item-wrapper">
                <a href="#" class="informationmenu-item">
                    ${label}
                    <span class="informationmenu-arrow">›</span>
                </a>
                <div class="submenu">
                    <div class="submenu-header"><div class="submenu-title">${title}</div></div>
                    <div class="submenu-content">${subContent}</div>
                </div>
            </div>`;
}

function createExpandableSubmenuItem(targetId, text, subOptionContent) {
    if (!subOptionContent) return '';
    return `<a href="#" class="submenu-item expandable" data-target="${targetId}">
                ${text} <span class="expand-arrow">›</span>
            </a>
            <div class="sub-options" id="${targetId}">
                ${subOptionContent}
            </div>`;
}

// --- Event Listeners ---

function attachSidebarEvents() {
    const sidebar = document.querySelector('.informationmenu');
    const toggleBtn = document.getElementById('sidebarToggle');
    const userAvatar = document.getElementById('user-avatar');

    // Toggle
    toggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        closeAllDropdowns(true); // Close active things inside
        sidebar.classList.toggle('collapsed');
        toggleBtn.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Avatar Click (Mobile show)
    userAvatar?.addEventListener('click', () => sidebar.classList.add('show'));

    // Close (Click outside could be added here if needed, or close button)

    // Sections
    document.querySelectorAll('.informationmenu-section-title').forEach(title => {
        title.addEventListener('click', function (e) {
            const targetId = this.getAttribute('data-target');
            const targetDropdown = document.getElementById(targetId);

            if (sidebar.classList.contains('collapsed')) {
                // Expand logic
                sidebar.classList.remove('collapsed');
                document.body.classList.remove('sidebar-collapsed');
                toggleBtn?.classList.remove('collapsed');
            }

            // Accordion logic
            document.querySelectorAll('.dropdown-menu-items').forEach(d => {
                if (d.id !== targetId) d.classList.remove('show');
            });
            document.querySelectorAll('.informationmenu-section-title').forEach(t => {
                if (t !== this) t.classList.remove('active');
            });

            this.classList.toggle('active');
            targetDropdown?.classList.toggle('show');
        });
    });

    // Expandable Subitems
    document.querySelectorAll('.submenu-item.expandable').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const targetId = this.getAttribute('data-target');
            const targetOptions = document.getElementById(targetId);

            // Close others in same submenu
            const parent = this.closest('.submenu-content');
            parent.querySelectorAll('.sub-options').forEach(op => {
                if (op.id !== targetId) op.classList.remove('expanded');
            });
            parent.querySelectorAll('.submenu-item.expandable').forEach(ex => {
                if (ex !== this) ex.classList.remove('expanded');
            });

            this.classList.toggle('expanded');
            targetOptions?.classList.toggle('expanded');
        });
    });

    // Hover effects for submenus
    document.querySelectorAll('.menu-item-wrapper').forEach(wrapper => {
        const submenu = wrapper.querySelector('.submenu');
        if (submenu) {
            wrapper.addEventListener('mouseenter', () => {
                submenu.style.opacity = '1';
                submenu.style.visibility = 'visible';
                submenu.style.transform = 'translateX(0)';
                submenu.style.pointerEvents = 'auto';
            });
            wrapper.addEventListener('mouseleave', () => {
                setTimeout(() => {
                    if (!submenu.matches(':hover') && !wrapper.matches(':hover')) {
                        submenu.style.opacity = '0';
                        submenu.style.visibility = 'hidden';
                        submenu.style.transform = 'translateX(-50px)';
                        submenu.style.pointerEvents = 'none';
                    }
                }, 100);
            });
        }
    });
}

function closeAllDropdowns(includeActive = false) {
    document.querySelectorAll('.dropdown-menu-items').forEach(d => d.classList.remove('show'));
    if (includeActive) {
        document.querySelectorAll('.informationmenu-section-title').forEach(t => t.classList.remove('active'));
    }
}

async function redirectToAllowedStockPage(event) {
    event.preventDefault();
    try {
        const res = await fetch(`${BASE_PATH}hr/generatecodeapi`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_page_permissions' })
        });
        const data = await res.json();
        let targetSystem = 'central';

        if (data.success && data.page_permissions && data.page_permissions.stock_inventory) {
            const allowedSystems = data.page_permissions.stock_inventory.system || [];
            if (allowedSystems.length > 0) targetSystem = allowedSystems[0];
        }
        window.location.href = `${BASE_PATH}resource/stocklistall?system=${targetSystem}`;
    } catch (e) {
        window.location.href = `${BASE_PATH}resource/stocklistall?system=central`;
    }
}
