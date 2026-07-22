
const sidebar = document.querySelector('.informationmenu');
// 移除遮罩层逻辑
const overlay = null;
const userAvatar = document.getElementById('user-avatar');
const closeBtn = document.querySelector('.informationmenu-close-btn');

// 点击用户头像显示菜单
userAvatar?.addEventListener('click', function () {
    sidebar.classList.add('show');
});

// 关闭菜单
function closeSidebar() {
    sidebar.classList.remove('show');
    // 无遮罩层
    // 关闭所有下拉菜单
    document.querySelectorAll('.dropdown-menu-items').forEach(dropdown => {
        dropdown.classList.remove('show');
    });
    document.querySelectorAll('.informationmenu-section-title').forEach(title => {
        title.classList.remove('active');
    });
}

closeBtn?.addEventListener('click', closeSidebar);
// 无遮罩层点击事件

// ESC键关闭菜单
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeSidebar();
    }
});

// Section标题点击事件
document.querySelectorAll('.informationmenu-section-title').forEach(title => {
    title.addEventListener('click', function (e) {
        const targetId = this.getAttribute('data-target');
        const targetDropdown = document.getElementById(targetId);

        // 收起状态：显示 flyout，不展开整栏
        if (sidebarMenu.classList.contains('collapsed')) {
            e.preventDefault();
            e.stopPropagation();

            if (activeFlyoutTitle === this && collapsedFlyoutEl?.classList.contains('is-visible')) {
                closeCollapsedFlyout();
            } else {
                openCollapsedFlyout(this);
            }

            return false;
        }

        // 侧边栏已展开时的正常切换逻辑
        // 关闭其他section的下拉菜单
        document.querySelectorAll('.dropdown-menu-items').forEach(dropdown => {
            if (dropdown.id !== targetId) {
                dropdown.classList.remove('show');
            }
        });

        // 移除其他section title的active状态
        document.querySelectorAll('.informationmenu-section-title').forEach(t => {
            if (t !== this) {
                t.classList.remove('active');
            }
        });

        // 切换当前section
        this.classList.toggle('active');
        targetDropdown?.classList.toggle('show');
    });
});

// 菜单项点击效果
document.querySelectorAll('.informationmenu-item').forEach(item => {
    item.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        // 检查是否有真实的链接
        if (href && href !== '#' && !href.startsWith('javascript:')) {
            // 有真实链接，允许正常跳转
            window.location.href = href;
            return;
        }

        // 没有真实链接的项目，阻止默认行为
        e.preventDefault();

        // 移除其他active状态
        document.querySelectorAll('.informationmenu-item').forEach(i => i.classList.remove('active'));

        // 添加active状态到当前项
        this.classList.add('active');
    });
});

// 修复后的子菜单项点击效果
document.querySelectorAll('.submenu-item:not(.expandable)').forEach(item => {
    item.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        // 检查是否有真实的链接
        if (href && href !== '#' && !href.startsWith('javascript:')) {
            // 有真实链接，允许正常跳转
            console.log('跳转到: ' + href);
            // 移除 e.preventDefault()，让链接正常工作
            window.location.href = href; // 手动跳转
            return;
        }

        // 没有真实链接的项目，阻止默认行为并显示提示
        e.preventDefault();
        const itemText = this.textContent.replace('→', '').trim();
        // showToast('点击了子菜单项: ' + itemText, 'info');
    });
});

// 多级展开功能
document.querySelectorAll('.submenu-item.expandable').forEach(item => {
    item.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const targetId = this.getAttribute('data-target');
        const targetOptions = document.getElementById(targetId);

        // 收起所有其他的子选项
        document.querySelectorAll('.sub-options').forEach(options => {
            if (options.id !== targetId) {
                options.classList.remove('expanded');
            }
        });

        // 移除所有其他expandable项的expanded类
        document.querySelectorAll('.submenu-item.expandable').forEach(expandableItem => {
            if (expandableItem !== this) {
                expandableItem.classList.remove('expanded');
            }
        });

        // 切换当前项的展开状态
        this.classList.toggle('expanded');
        targetOptions?.classList.toggle('expanded');
    });
});

// 子选项点击效果
document.querySelectorAll('.sub-option').forEach(option => {
    option.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        // 检查是否有真实的链接
        if (href && href !== '#' && !href.startsWith('javascript:')) {
            // 有真实链接，允许正常跳转
            console.log('跳转到: ' + href);
            return; // 不阻止默认行为
        }

        // 没有真实链接的项目
        e.preventDefault();
        const optionText = this.textContent.replace('·', '').trim();
        // showToast('点击了子选项: ' + optionText, 'info');
    });
});

// 增强子菜单hover效果
document.querySelectorAll('.menu-item-wrapper').forEach(wrapper => {
    const submenu = wrapper.querySelector('.submenu');
    if (submenu) {
        // 鼠标进入菜单项区域
        wrapper.addEventListener('mouseenter', function () {
            submenu.style.opacity = '1';
            submenu.style.visibility = 'visible';
            submenu.style.transform = 'translateX(0)';
            submenu.style.pointerEvents = 'auto';
        });

        // 鼠标离开整个区域时隐藏
        wrapper.addEventListener('mouseleave', function (e) {
            // 检查鼠标是否移向子菜单
            setTimeout(() => {
                if (!submenu.matches(':hover') && !wrapper.matches(':hover')) {
                    submenu.style.opacity = '0';
                    submenu.style.visibility = 'hidden';
                    submenu.style.transform = 'translateX(-50px)';
                    submenu.style.pointerEvents = 'none';
                }
            }, 100);
        });

        // 鼠标在子菜单上时保持显示
        submenu.addEventListener('mouseenter', function () {
            this.style.opacity = '1';
            this.style.visibility = 'visible';
            this.style.transform = 'translateX(0)';
            this.style.pointerEvents = 'auto';
        });

        submenu.addEventListener('mouseleave', function () {
            this.style.opacity = '0';
            this.style.visibility = 'hidden';
            this.style.transform = 'translateX(-50px)';
            this.style.pointerEvents = 'none';
        });
    }
});

console.log('点击Section + 悬停Submenu系统已加载完成');

// 根据权限跳转到允许的库存页面
async function redirectToAllowedStockPage(event) {
    event.preventDefault();
    try {
        // 获取当前 URL 中的 system 参数
        const urlParams = new URLSearchParams(window.location.search);
        const currentSystemParam = urlParams.get('system');

        const res = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_page_permissions' })
        });
        const data = await res.json();

        let targetSystem = currentSystemParam || 'central'; // 优先使用当前参数，否则默认中央
        let targetView = 'list';
        const viewOrder = ['list', 'records', 'remark', 'product', 'sot'];
        const viewRedirectMap = {
            list: 'stocklistall',
            records: 'stockeditall',
            remark: 'stockremark',
            product: 'stockproductname',
            sot: 'stocksot'
        };

        if (data.success && data.page_permissions && data.page_permissions.stock_inventory) {
            const allowedSystems = data.page_permissions.stock_inventory.system || [];
            const allowedViews = data.page_permissions.stock_inventory.view || [];

            // 如果指定了系统参数，验证权限
            if (currentSystemParam) {
                if (allowedSystems.length > 0 && !allowedSystems.includes(currentSystemParam)) {
                    // 如果当前参数不在允许列表中，且有权限限制，则使用第一个允许的
                    targetSystem = allowedSystems[0];
                }
            } else if (allowedSystems.length > 0) {
                // 如果没指定参数且有权限限制，使用第一个允许的系统
                targetSystem = allowedSystems[0];
            }

            if (allowedViews.length > 0) {
                const firstAllowedView = viewOrder.find(view => allowedViews.includes(view));
                if (firstAllowedView) {
                    targetView = firstAllowedView;
                }
            }
        }
        const redirectBase = viewRedirectMap[targetView] || viewRedirectMap.list;

        // 跳转到目标页面，并添加系统参数
        window.location.href = `${redirectBase}?system=${targetSystem}`;
    } catch (e) {
        // 出错时尝试保留当前参数，作为最后的兜底
        const urlParams = new URLSearchParams(window.location.search);
        const fallbackSystem = urlParams.get('system') || 'central';
        window.location.href = `stocklistall?system=${fallbackSystem}`;
    }
}

// 侧边栏收起/展开功能
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarMenu = document.querySelector('.informationmenu'); // 改名避免冲突

// 侧边栏图标悬停提示
const sidebarTooltipDescriptions = {
    brand: '集团架构 — 品牌与企业蓝图',
    analytics: '营收数据 — KPI 报表与上传',
    hr: '人事管理 — 职员、招聘与考核',
    resource: '资源总库 — 库存、碗碟与价格',
    photoupload: '视觉管理 — 网页素材与菜单',
};

function initSidebarTooltips() {
    document.querySelectorAll('.informationmenu-section-title').forEach((title) => {
        if (title.dataset.tooltip) return;

        const targetId = title.getAttribute('data-target') || '';
        const sectionKey = targetId.replace(/-items$/, '');
        if (sidebarTooltipDescriptions[sectionKey]) {
            title.dataset.tooltip = sidebarTooltipDescriptions[sectionKey];
            title.setAttribute('aria-label', sidebarTooltipDescriptions[sectionKey]);
            return;
        }

        const clone = title.cloneNode(true);
        clone.querySelectorAll('.section-icon, .section-arrow').forEach((el) => el.remove());
        const label = clone.textContent.replace(/\s+/g, ' ').trim();
        if (label) {
            title.dataset.tooltip = label;
            title.setAttribute('aria-label', label);
        }
    });

    updateSidebarToggleTooltip();
}

function updateSidebarToggleTooltip() {
    if (!sidebarToggle) return;
    const isCollapsed = sidebarMenu?.classList.contains('collapsed');
    const label = isCollapsed ? '展开侧边栏' : '收起侧边栏';
    sidebarToggle.dataset.tooltip = label;
    sidebarToggle.setAttribute('aria-label', label);
}

sidebarToggle?.addEventListener('click', function (e) {
    e.stopPropagation(); // 防止事件冒泡

    closeCollapsedFlyout();

    // 如果正在收起侧边栏，清除所有激活状态
    if (!sidebarMenu.classList.contains('collapsed')) {
        // 关闭所有下拉菜单
        document.querySelectorAll('.dropdown-menu-items').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        // 移除所有section title的active状态
        document.querySelectorAll('.informationmenu-section-title').forEach(title => {
            title.classList.remove('active');
        });
        // 移除所有菜单项的active状态
        document.querySelectorAll('.informationmenu-item').forEach(item => {
            item.classList.remove('active');
        });
    }

    sidebarMenu.classList.toggle('collapsed');
    sidebarToggle.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');

    updateSidebarToggleTooltip();

    // 保存侧边栏状态到localStorage
    const isCollapsed = sidebarMenu.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);

    // 确保过渡动画已启用
    if (!document.body.classList.contains('sidebar-transition')) {
        document.body.classList.add('sidebar-transition');
    }
});

let collapsedFlyoutEl = null;
let flyoutCloseTimer = null;
let activeFlyoutTitle = null;

function getSectionLabel(titleEl) {
    const clone = titleEl.cloneNode(true);
    clone.querySelectorAll('.section-icon, .section-arrow').forEach((el) => el.remove());
    return clone.textContent.replace(/\s+/g, ' ').trim();
}

function getLinkLabel(el) {
    const clone = el.cloneNode(true);
    clone.querySelectorAll('.informationmenu-arrow, .expand-arrow').forEach((node) => node.remove());
    return clone.textContent.replace(/[›→·]/g, '').replace(/\s+/g, ' ').trim();
}

function collectFlyoutLinks(dropdownEl) {
    const items = [];
    if (!dropdownEl) return items;

    dropdownEl.querySelectorAll(':scope > .menu-item-wrapper').forEach((wrapper) => {
        const mainLink = wrapper.querySelector(':scope > a.informationmenu-item');
        const submenu = wrapper.querySelector(':scope > .submenu');
        if (!mainLink) return;

        const href = mainLink.getAttribute('href');
        const onclick = mainLink.getAttribute('onclick');
        const isRealLink = href && href !== '#' && !href.startsWith('javascript:');

        if (isRealLink && !submenu) {
            items.push({
                type: 'link',
                label: getLinkLabel(mainLink),
                href,
                onclick,
                html: mainLink.innerHTML,
            });
            return;
        }

        if (submenu) {
            const groupTitle = submenu.querySelector('.submenu-title')?.textContent.trim();
            if (groupTitle) {
                items.push({ type: 'heading', label: groupTitle });
            }

            submenu.querySelectorAll('.submenu-content > .submenu-item:not(.expandable)').forEach((link) => {
                const linkHref = link.getAttribute('href');
                if (linkHref && linkHref !== '#') {
                    items.push({ type: 'link', label: getLinkLabel(link), href: linkHref });
                }
            });

            submenu.querySelectorAll('.submenu-item.expandable').forEach((exp) => {
                const targetId = exp.getAttribute('data-target');
                items.push({ type: 'subheading', label: getLinkLabel(exp) });
                if (targetId) {
                    document.getElementById(targetId)?.querySelectorAll('.sub-option').forEach((opt) => {
                        const optHref = opt.getAttribute('href');
                        if (optHref && optHref !== '#') {
                            items.push({
                                type: 'link',
                                label: getLinkLabel(opt),
                                href: optHref,
                                indent: true,
                            });
                        }
                    });
                }
            });
        }
    });

    return items;
}

function ensureCollapsedFlyout() {
    if (collapsedFlyoutEl) return collapsedFlyoutEl;

    collapsedFlyoutEl = document.createElement('div');
    collapsedFlyoutEl.id = 'sidebarCollapsedFlyout';
    collapsedFlyoutEl.className = 'sidebar-collapsed-flyout';
    collapsedFlyoutEl.hidden = true;
    collapsedFlyoutEl.innerHTML =
        '<div class="sidebar-collapsed-flyout__title"></div>' +
        '<div class="sidebar-collapsed-flyout__items"></div>';

    collapsedFlyoutEl.addEventListener('mouseenter', cancelFlyoutClose);
    collapsedFlyoutEl.addEventListener('mouseleave', scheduleFlyoutClose);
    document.body.appendChild(collapsedFlyoutEl);

    return collapsedFlyoutEl;
}

function positionCollapsedFlyout(titleEl) {
    if (!collapsedFlyoutEl || !titleEl) return;

    const rect = titleEl.getBoundingClientRect();
    collapsedFlyoutEl.style.left = `${rect.right + 8}px`;
    collapsedFlyoutEl.style.top = `${rect.top}px`;

    requestAnimationFrame(() => {
        const flyoutRect = collapsedFlyoutEl.getBoundingClientRect();
        if (flyoutRect.bottom > window.innerHeight - 8) {
            collapsedFlyoutEl.style.top = `${Math.max(8, window.innerHeight - flyoutRect.height - 8)}px`;
        }
    });
}

function openCollapsedFlyout(titleEl) {
    if (!sidebarMenu?.classList.contains('collapsed')) return;

    const targetId = titleEl.getAttribute('data-target');
    const dropdown = document.getElementById(targetId);
    const links = collectFlyoutLinks(dropdown);
    if (links.length === 0) return;

    const flyout = ensureCollapsedFlyout();
    const titleText = getSectionLabel(titleEl);

    document.querySelectorAll('.informationmenu-section-title.flyout-active').forEach((title) => {
        title.classList.remove('flyout-active');
    });
    titleEl.classList.add('flyout-active');
    activeFlyoutTitle = titleEl;

    flyout.querySelector('.sidebar-collapsed-flyout__title').textContent = titleText;

    const itemsEl = flyout.querySelector('.sidebar-collapsed-flyout__items');
    itemsEl.innerHTML = '';

    links.forEach((item) => {
        if (item.type === 'heading' || item.type === 'subheading') {
            const heading = document.createElement('div');
            heading.className =
                item.type === 'heading'
                    ? 'sidebar-collapsed-flyout__heading'
                    : 'sidebar-collapsed-flyout__subheading';
            heading.textContent = item.label;
            itemsEl.appendChild(heading);
            return;
        }

        const link = document.createElement('a');
        link.className = 'sidebar-collapsed-flyout__link';
        if (item.indent) link.classList.add('is-indented');
        link.href = item.href;
        if (item.html) {
            link.innerHTML = item.html;
        } else {
            link.textContent = item.label;
        }
        if (item.onclick) {
            link.setAttribute('onclick', item.onclick);
        }
        itemsEl.appendChild(link);
    });

    flyout.hidden = false;
    flyout.classList.add('is-visible');
    positionCollapsedFlyout(titleEl);
}

function closeCollapsedFlyout() {
    cancelFlyoutClose();
    if (!collapsedFlyoutEl) return;

    collapsedFlyoutEl.hidden = true;
    collapsedFlyoutEl.classList.remove('is-visible');
    document.querySelectorAll('.informationmenu-section-title.flyout-active').forEach((title) => {
        title.classList.remove('flyout-active');
    });
    activeFlyoutTitle = null;
}

function cancelFlyoutClose() {
    if (flyoutCloseTimer) {
        clearTimeout(flyoutCloseTimer);
        flyoutCloseTimer = null;
    }
}

function scheduleFlyoutClose() {
    cancelFlyoutClose();
    flyoutCloseTimer = setTimeout(closeCollapsedFlyout, 160);
}

function initCollapsedFlyout() {
    if (initCollapsedFlyout.initialized) return;
    initCollapsedFlyout.initialized = true;

    document.querySelectorAll('.informationmenu-section-title').forEach((title) => {
        title.addEventListener('mouseenter', function () {
            if (!sidebarMenu?.classList.contains('collapsed')) return;
            cancelFlyoutClose();
            openCollapsedFlyout(this);
        });

        title.addEventListener('mouseleave', function () {
            if (!sidebarMenu?.classList.contains('collapsed')) return;
            scheduleFlyoutClose();
        });
    });

    document.addEventListener('click', function (e) {
        if (!sidebarMenu?.classList.contains('collapsed')) return;
        if (collapsedFlyoutEl?.contains(e.target)) return;
        if (e.target.closest('.informationmenu-section-title')) return;
        closeCollapsedFlyout();
    });

    window.addEventListener('resize', function () {
        if (activeFlyoutTitle && collapsedFlyoutEl?.classList.contains('is-visible')) {
            positionCollapsedFlyout(activeFlyoutTitle);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeCollapsedFlyout();
    });
}

function bootSidebarUi() {
    initSidebarTooltips();
    initCollapsedFlyout();

    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        document.body.classList.add('sidebar-collapsed');
        sidebarMenu?.classList.add('collapsed');
        sidebarToggle?.classList.add('collapsed');
    }

    updateSidebarToggleTooltip();

    setTimeout(function () {
        document.body.classList.add('sidebar-transition');
    }, 100);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSidebarUi);
} else {
    bootSidebarUi();
}