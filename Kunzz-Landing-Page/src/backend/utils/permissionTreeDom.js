export const sidebarSubOptions = {
    analytics: ['kpi_report', 'kpi_upload'],
    hr: ['staff_management'],
    resource: ['stock_inventory', 'dishware', 'price_comparison'],
    brand: ['kunzz_holdings', 'tokyo_cuisine', 'tokyo_izakaya']
};

// 初始化权限树事件监听器
export function initPermissionTreeEvents(container) {
    if (!container) return;

    // 如果已经绑定过，直接返回
    if (container.dataset.permValidationInit === 'true') return;
    container.dataset.permValidationInit = 'true';

    // 绑定所有的 checkbox 的 change 事件来触发验证
    const allCheckboxes = container.querySelectorAll('.perm-l1-check, .perm-l2-check, .perm-stock-system, .perm-stock-view, .perm-stock-shipper, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint');
    allCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            updatePermissionValidationState(container);
        });
    });

    // 初始验证状态
    updatePermissionValidationState(container);

    // 阻止label的默认行为，防止点击label时触发checkbox
    container.querySelectorAll('.perm-checkbox-label').forEach(label => {
        label.addEventListener('click', function (e) {
            // 如果点击的是checkbox，允许默认行为
            if (e.target.tagName === 'INPUT') {
                return;
            }
            // 点击其他部分（文字、箭头等），只阻止默认行为，不阻止冒泡
            e.preventDefault();
        });
    });

    // 额外权限区域的label也需要阻止
    container.querySelectorAll('.extra-perm-section label').forEach(label => {
        label.addEventListener('click', function (e) {
            if (e.target.tagName !== 'INPUT') {
                e.preventDefault();
            }
        });
    });

    // 三级面板的label也需要阻止
    container.querySelectorAll('.perm-detail-content label').forEach(label => {
        label.addEventListener('click', function (e) {
            if (e.target.tagName !== 'INPUT') {
                e.preventDefault();
            }
        });
    });

    // 四级分类点击展开/折叠
    container.querySelectorAll('.perm-level-4-item').forEach(item => {
        item.addEventListener('click', function (e) {
            // 如果点击的是复选框，不处理展开
            if (e.target.tagName === 'INPUT') {
                e.stopPropagation();
                return;
            }

            const subContainer = item.querySelector('.perm-level-4-container');
            if (!subContainer) return;

            const isCurrentlyExpanded = item.classList.contains('expanded');

            // 如果当前项未展开，先关闭所有其他四级分类
            if (!isCurrentlyExpanded) {
                container.querySelectorAll('.perm-level-4-item.expanded').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('expanded');
                        const otherContainer = otherItem.querySelector('.perm-level-4-container');
                        if (otherContainer) {
                            otherContainer.classList.remove('expanded');
                        }
                    }
                });
            }

            // 切换当前项的展开状态
            item.classList.toggle('expanded');
            subContainer.classList.toggle('expanded');
        });
    });

    // 一级分类点击展开/折叠
    container.querySelectorAll('.perm-level-1-item').forEach(item => {
        item.addEventListener('click', function (e) {
            // 如果点击的是复选框，不处理展开
            if (e.target.tagName === 'INPUT') {
                e.stopPropagation();
                return;
            }

            const parent = item.getAttribute('data-perm');
            const subContainer = container.querySelector(`.perm-level-2-container[data-parent="${parent}"]`);
            const isCurrentlyExpanded = item.classList.contains('expanded');
            const detailContent = container.querySelector('.perm-detail-content');
            const placeholder = container.querySelector('.perm-detail-placeholder');
            const hasLevel3 = item.classList.contains('has-level-3');
            const sub = item.getAttribute('data-sub');

            // 如果是一级分类有三级配置
            if (hasLevel3 && sub) {
                // 先尝试寻找当前项内部的内联面板
                let panel = item.querySelector('.perm-level-3-panel-inline');

                // 如果没找到内联面板，则去外部详细卡片找
                if (!panel) {
                    panel = container.querySelector(`.perm-detail-content .perm-level-3-panel[data-for="${sub}"]`);
                }

                // 如果当前项未展开，先关闭所有其他一级分类和三级面板
                if (!isCurrentlyExpanded) {
                    container.querySelectorAll('.perm-level-1-item.expanded').forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('expanded');
                            const otherParent = otherItem.getAttribute('data-perm');
                            const otherContainer = container.querySelector(`.perm-level-2-container[data-parent="${otherParent}"]`);
                            if (otherContainer) {
                                otherContainer.classList.remove('expanded');
                            }
                        }
                    });

                    // 关闭所有有三级配置的二级项和右侧详细配置卡片
                    container.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(level2Item => {
                        level2Item.classList.remove('expanded');
                    });
                    container.querySelectorAll('.perm-level-1-item.has-level-3.expanded').forEach(level1Item => {
                        if (level1Item !== item) {
                            level1Item.classList.remove('expanded');
                        }
                    });

                    // 统一处理面板隐藏
                    container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => {
                        if (p !== panel) p.classList.remove('show');
                    });
                }

                // 切换当前项的展开状态
                item.classList.toggle('expanded');

                // 切换三级面板显示
                if (panel) {
                    const isPanelShowing = panel.classList.contains('show');
                    if (!isPanelShowing) {
                        // 显示面板
                        container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => p.classList.remove('show'));
                        panel.classList.add('show');
                        if (detailContent) detailContent.classList.add('active');
                        if (placeholder) placeholder.classList.add('hidden');
                    } else {
                        // 隐藏面板
                        panel.classList.remove('show');
                        if (detailContent) detailContent.classList.remove('active');
                        if (placeholder) placeholder.classList.remove('hidden');
                    }
                }
                return;
            }

            // 普通一级分类（有二级容器）
            if (!subContainer) return;

            // 如果当前项未展开，先关闭所有其他一级分类
            if (!isCurrentlyExpanded) {
                container.querySelectorAll('.perm-level-1-item.expanded').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('expanded');
                        const otherParent = otherItem.getAttribute('data-perm');
                        const otherContainer = container.querySelector(`.perm-level-2-container[data-parent="${otherParent}"]`);
                        if (otherContainer) {
                            otherContainer.classList.remove('expanded');
                        }
                    }
                });

                // 关闭所有有三级配置的二级项和右侧详细配置卡片
                container.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(level2Item => {
                    level2Item.classList.remove('expanded');
                });
                container.querySelectorAll('.perm-level-1-item.has-level-3.expanded').forEach(level1Item => {
                    level1Item.classList.remove('expanded');
                });
                container.querySelectorAll('.perm-level-3-panel').forEach(p => {
                    p.classList.remove('show');
                });
                if (detailContent) detailContent.classList.remove('active');
                if (placeholder) placeholder.classList.remove('hidden');
            }

            // 切换当前项的展开状态
            item.classList.toggle('expanded');
            subContainer.classList.toggle('expanded');
        });
    });

    // 一级复选框变化 - 同步二级权限状态
    container.querySelectorAll('.perm-l1-check').forEach(checkbox => {
        if (!checkbox.dataset.fromChild) {
            checkbox.dataset.fromChild = 'false';
        }

        checkbox.addEventListener('change', function () {
            const parentValue = this.value;
            const isChecked = this.checked;
            const isFromChild = this.dataset.fromChild === 'true';

            // 重置标记
            this.dataset.fromChild = 'false';

            if (!isFromChild) {
                syncLevel2Permissions(container, parentValue, isChecked);
            }
        });
    });

    // 二级复选框变化 - 检查父级状态并同步三级权限
    container.querySelectorAll('.perm-l2-check').forEach(checkbox => {
        if (!checkbox.dataset.fromChild) {
            checkbox.dataset.fromChild = 'false';
        }

        checkbox.addEventListener('change', function () {
            const level2Value = this.value;
            const isChecked = this.checked;
            const parent = this.dataset.parent;
            const isFromChild = this.dataset.fromChild === 'true';

            // 重置标记
            this.dataset.fromChild = 'false';

            // 检查父级状态
            const parentCheckbox = container.querySelector(`.perm-l1-check[value="${parent}"]`);
            if (parentCheckbox && !parentCheckbox.checked && isChecked) {
                parentCheckbox.dataset.fromChild = 'true';
                parentCheckbox.checked = true;
                parentCheckbox.dispatchEvent(new Event('change'));
            }

            // 同步三级权限（仅在不是从子级触发时，才向下联动）
            if (!isFromChild) {
                syncLevel3Permissions(container, level2Value, isChecked);
            }

            // 取消勾选时，若无其他同级，则向上取消父级
            if (!isChecked) {
                const otherChildren = container.querySelectorAll(`.perm-l2-check[data-parent="${parent}"]:checked`);
                if (otherChildren.length === 0 && parentCheckbox) {
                    parentCheckbox.dataset.fromChild = 'true';
                    parentCheckbox.checked = false;
                    parentCheckbox.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    // 二级有三级的项目 - 在右侧卡片显示三级面板
    container.querySelectorAll('.perm-level-2-item.has-level-3').forEach(item => {
        item.addEventListener('click', function (e) {
            // 如果点击的是复选框，不处理展开
            if (e.target.tagName === 'INPUT') {
                e.stopPropagation();
                return;
            }

            const sub = item.getAttribute('data-sub');
            const detailContent = container.querySelector('.perm-detail-content');
            const placeholder = container.querySelector('.perm-detail-placeholder');
            const isCurrentlyExpanded = item.classList.contains('expanded');

            // 先尝试寻找当前项内部的内联面板
            let panel = item.querySelector('.perm-level-3-panel-inline');

            // 如果没找到内联面板，则去外部详细卡片找
            if (!panel) {
                panel = container.querySelector(`.perm-detail-content .perm-level-3-panel[data-for="${sub}"]`);
            }

            // 如果当前项未展开，先关闭所有其他有三级配置的二级项
            if (!isCurrentlyExpanded) {
                container.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('expanded');
                        container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => {
                            p.classList.remove('show');
                        });
                    }
                });
            }

            // 关闭所有三级面板（除了当前要显示的）
            container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => {
                if (p !== panel) p.classList.remove('show');
            });

            // 切换当前面板
            item.classList.toggle('expanded');

            if (!isCurrentlyExpanded) {
                // 展开：显示内容
                if (panel) panel.classList.add('show');
                if (detailContent) detailContent.classList.add('active');
                if (placeholder) placeholder.classList.add('hidden');
            } else {
                // 折叠：关闭面板
                if (panel) panel.classList.remove('show');
                if (detailContent) detailContent.classList.remove('active');
                if (placeholder) placeholder.classList.remove('hidden');
            }
        });
    });

    // 店面项展开/收缩功能
    container.querySelectorAll('.perm-store-item').forEach(item => {
        const label = item.querySelector('.perm-checkbox-label');
        if (label) {
            label.addEventListener('click', function (e) {
                // 如果点击的是checkbox，不处理展开
                if (e.target.tagName === 'INPUT') {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                item.classList.toggle('expanded');
            });
        }
    });

    // 三级页面权限和库存/上传权限的向上联动
    container.querySelectorAll('.perm-stock-system, .perm-stock-view, .perm-stock-shipper, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            let level2Value = '';
            if (this.classList.contains('perm-stock-system') || this.classList.contains('perm-stock-view') || this.classList.contains('perm-stock-shipper')) {
                level2Value = 'stock_inventory';
            } else if (this.classList.contains('perm-upload-system') || this.classList.contains('perm-upload-type')) {
                level2Value = 'kpi_upload';
            } else if (this.classList.contains('perm-page-schedule')) {
                level2Value = this.dataset.brand || '';
            } else if (this.classList.contains('perm-page-blueprint')) {
                level2Value = this.dataset.brand || '';
            }

            if (!level2Value) return;

            const level2Checkbox = container.querySelector(`.perm-l2-check[value="${level2Value}"]`);
            if (!level2Checkbox) return;

            if (this.checked) {
                if (!level2Checkbox.checked) {
                    level2Checkbox.dataset.fromChild = 'true';
                    level2Checkbox.checked = true;
                    level2Checkbox.dispatchEvent(new Event('change'));
                }
            } else {
                let otherChecked = 0;
                if (level2Value === 'stock_inventory') {
                    otherChecked = container.querySelectorAll('.perm-stock-system:checked, .perm-stock-view:checked, .perm-stock-shipper:checked').length;
                } else if (level2Value === 'kpi_upload') {
                    otherChecked = container.querySelectorAll('.perm-upload-system:checked, .perm-upload-type:checked').length;
                } else if (level2Value === 'kunzz_holdings') {
                    otherChecked = container.querySelectorAll(`.perm-page-blueprint[data-brand="${level2Value}"]:checked`).length;
                } else {
                    otherChecked = container.querySelectorAll(`.perm-page-schedule[data-brand="${level2Value}"]:checked`).length;
                }

                if (otherChecked === 0) {
                    level2Checkbox.dataset.fromChild = 'true';
                    level2Checkbox.checked = false;
                    level2Checkbox.dispatchEvent(new Event('change'));
                }
            }
        });
    });
}

// 设置默认全选所有权限
export function setDefaultAllPermissions(container) {
    if (!container) return;

    // 先确保所有checkbox都是active的（不设置disabled）
    container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.disabled = false;
    });

    // 全选所有一级权限
    container.querySelectorAll('.perm-l1-check').forEach(cb => {
        cb.checked = true;
    });

    // 全选所有二级权限
    container.querySelectorAll('.perm-l2-check').forEach(cb => {
        cb.checked = true;
    });

    // 全选所有三级权限
    container.querySelectorAll('.perm-stock-system, .perm-stock-view, .perm-stock-shipper, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint').forEach(cb => {
        cb.checked = true;
    });
}

// 权限校验系统
function updatePermissionValidationState(container) {
    if (!container) return;

    const submitBtn = container.querySelector('button[type="submit"]');
    const warningDiv = container.querySelector('.perm-warning');

    const checkedBoxes = Array.from(container.querySelectorAll('.perm-l1-check, .perm-l2-check, .perm-stock-system, .perm-stock-view, .perm-stock-shipper, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint'))
        .filter(cb => cb.checked && !cb.disabled);

    const hasSelection = checkedBoxes.length > 0;

    if (container.id === 'addUserModal' || container.id === 'editUserModal' || container.id === 'permissionsModal') {
        if (warningDiv) {
            warningDiv.style.display = hasSelection ? 'none' : 'block';
        }
        if (submitBtn) {
            submitBtn.disabled = !hasSelection;
            submitBtn.style.opacity = hasSelection ? '1' : '0.5';
            submitBtn.style.cursor = hasSelection ? 'pointer' : 'not-allowed';
        }
    }
}


// 清空并折叠权限树
export function resetPermissionTree(container) {
    if (!container) return;

    container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        cb.dataset.fromChild = 'false';
    });

    container.querySelectorAll('.expanded').forEach(item => item.classList.remove('expanded'));
    container.querySelectorAll('.show').forEach(item => item.classList.remove('show'));

    const detailContent = container.querySelector('.perm-detail-content');
    const placeholder = container.querySelector('.perm-detail-placeholder');
    if (detailContent) detailContent.classList.remove('active');
    if (placeholder) placeholder.classList.remove('hidden');

    updatePermissionValidationState(container);
}

// 同步二级权限状态
function syncLevel2Permissions(container, parentValue, parentChecked) {
    container.querySelectorAll(`.perm-l2-check[data-parent="${parentValue}"]`).forEach(cb => {
        if (parentChecked) {
            if (!cb.checked) {
                cb.checked = true;
            }
            syncLevel3Permissions(container, cb.value, true);
        } else {
            cb.checked = false;
            syncLevel3Permissions(container, cb.value, false);
        }
    });
}

// 同步三级权限状态
function syncLevel3Permissions(container, level2Value, level2Checked) {
    if (level2Value === 'stock_inventory') {
        container.querySelectorAll('.perm-stock-system, .perm-stock-view, .perm-stock-shipper').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'kpi_upload') {
        container.querySelectorAll('.perm-upload-system, .perm-upload-type').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'kunzz_holdings') {
        container.querySelectorAll('.perm-page-blueprint[data-brand="kunzz_holdings"]').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'tokyo_cuisine') {
        container.querySelectorAll('.perm-page-schedule[data-store="j1"], .perm-page-schedule[data-store="j2"]').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'tokyo_izakaya') {
        container.querySelectorAll('.perm-page-schedule[data-store="j3"]').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
}

export function setPermCheckboxes(container, perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms) {
    if (!container) return;
    const values = new Set(Array.isArray(perms) ? perms : []);

    container.querySelectorAll('.perm-l1-check').forEach((cb) => {
        cb.checked = values.has(cb.value);
    });

    const submenuData = submenuPerms && typeof submenuPerms === 'object' ? submenuPerms : {};
    container.querySelectorAll('.perm-l2-check').forEach((cb) => {
        const parent = cb.dataset.parent;
        const parentEnabled = values.has(parent);
        const source = submenuData[parent];
        const allowed = Array.isArray(source) ? source : sidebarSubOptions[parent] || [];
        cb.checked = parentEnabled && allowed.includes(cb.value);
    });

    const stockPagePerms = pagePerms && typeof pagePerms === 'object' ? pagePerms.stock_inventory || {} : {};
    const stockSystems = Array.isArray(stockPagePerms.system) ? stockPagePerms.system : [];
    const stockViews = Array.isArray(stockPagePerms.views)
        ? stockPagePerms.views
        : Array.isArray(stockPagePerms.view)
          ? stockPagePerms.view
          : [];
    const systemSet = new Set(stockSystems);
    const viewSet = new Set(stockViews);

    container.querySelectorAll('.perm-stock-system').forEach((cb) => {
        cb.checked = systemSet.has(cb.value);
    });
    container.querySelectorAll('.perm-stock-view').forEach((cb) => {
        cb.checked = viewSet.has(cb.value);
    });
    container.querySelectorAll('.perm-stock-shipper').forEach((cb) => {
        cb.checked = stockPagePerms.is_shipper === true;
    });

    const uploadPagePerms = pagePerms && typeof pagePerms === 'object' ? pagePerms.kpi_upload || {} : {};
    const uploadSystems = Array.isArray(uploadPagePerms.system) ? uploadPagePerms.system : [];
    const uploadTypes = Array.isArray(uploadPagePerms.type) ? uploadPagePerms.type : [];
    const uploadSysSet = new Set(uploadSystems);
    const uploadTypeSet = new Set(uploadTypes);

    container.querySelectorAll('.perm-upload-system').forEach((cb) => {
        cb.checked = uploadSysSet.has(cb.value);
    });
    container.querySelectorAll('.perm-upload-type').forEach((cb) => {
        cb.checked = uploadTypeSet.has(cb.value);
    });

    const brandData = brandPerms && typeof brandPerms === 'object' ? brandPerms : {};

    if (brandData.kunzz_holdings && brandData.kunzz_holdings.blueprint) {
        container.querySelectorAll('.perm-page-blueprint[data-brand="kunzz_holdings"]').forEach((cb) => {
            cb.checked = brandData.kunzz_holdings.blueprint.includes('blueprint');
        });
    }

    const cuisinePerms = brandData.tokyo_cuisine || {};
    container.querySelectorAll('.perm-page-schedule[data-store="j1"]').forEach((cb) => {
        cb.checked = cuisinePerms.j1 && cuisinePerms.j1.includes('schedule');
    });
    container.querySelectorAll('.perm-page-schedule[data-store="j2"]').forEach((cb) => {
        cb.checked = cuisinePerms.j2 && cuisinePerms.j2.includes('schedule');
    });

    const izakayaPerms = brandData.tokyo_izakaya || {};
    container.querySelectorAll('.perm-page-schedule[data-store="j3"]').forEach((cb) => {
        cb.checked = izakayaPerms.j3 && izakayaPerms.j3.includes('schedule');
    });

    const rSet = new Set(Array.isArray(reportPerms) ? reportPerms : []);
    container.querySelectorAll('.perm-report').forEach((cb) => {
        cb.checked = rSet.has(cb.value);
    });

    const resSet = new Set(Array.isArray(restaurantPerms) ? restaurantPerms : []);
    container.querySelectorAll('.perm-restaurant').forEach((cb) => {
        cb.checked = resSet.has(cb.value);
    });

    container.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
        cb.disabled = false;
    });
}

export function extractPermissionsData(container) {
    if (!container) return {};

    const perms = Array.from(container.querySelectorAll('.perm-l1-check:checked')).map(cb => cb.value);

    // Submenu permissions (L2 checkboxes)
    const submenuPermissions = {};
    container.querySelectorAll('.perm-l1-check').forEach(l1 => {
        const parent = l1.value;
        const selectedSubs = Array.from(container.querySelectorAll(`.perm-l2-check[data-parent="${parent}"]:checked`)).map(cb => cb.value);
        submenuPermissions[parent] = selectedSubs;
    });

    // Page permissions (Detail panel checkboxes)
    const pagePermissions = {
        kpi_upload: {
            system: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="kpi_upload"] .perm-upload-system:checked')).map(cb => cb.value),
            type: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="kpi_upload"] .perm-upload-type:checked')).map(cb => cb.value)
        },
        stock_inventory: {
            system: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="stock_inventory"] .perm-stock-system:checked')).map(cb => cb.value),
            views: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="stock_inventory"] .perm-stock-view:checked')).map(cb => cb.value),
            is_shipper: container.querySelector('.perm-level-3-panel[data-for="stock_inventory"] .perm-stock-shipper')?.checked === true
        },
        annual_summary: {
            system: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="annual_summary"] .perm-annual-system:checked')).map(cb => cb.value)
        },
        branch_comparison: {
            views: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="branch_comparison"] .perm-comp-view:checked')).map(cb => cb.value)
        }
    };

    // Store/Brand permissions
    const brandPermissions = {
        kunzz_holdings: container.querySelector('.perm-page-blueprint[data-brand="kunzz_holdings"]')?.checked ? { blueprint: ['blueprint'] } : {},
        tokyo_cuisine: {
            j1: container.querySelector('.perm-page-schedule[data-store="j1"][data-brand="tokyo_cuisine"]')?.checked ? ['schedule'] : [],
            j2: container.querySelector('.perm-page-schedule[data-store="j2"][data-brand="tokyo_cuisine"]')?.checked ? ['schedule'] : []
        },
        tokyo_izakaya: {
            j3: container.querySelector('.perm-page-schedule[data-store="j3"][data-brand="tokyo_izakaya"]')?.checked ? ['schedule'] : []
        }
    };

    return {
        perms,
        submenuPermissions,
        pagePermissions,
        brandPermissions,
        reportPermissions: Array.from(container.querySelectorAll('.perm-report:checked')).map(cb => cb.value),
        restaurantPermissions: Array.from(container.querySelectorAll('.perm-restaurant:checked')).map(cb => cb.value)
    };
}


export function closeDetailPanel(container) {
  if (!container) return;
  container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach((p) => p.classList.remove('show'));
  const detailContent = container.querySelector('.perm-detail-content');
  const placeholder = container.querySelector('.perm-detail-placeholder');
  if (detailContent) detailContent.classList.remove('active');
  if (placeholder) placeholder.classList.remove('hidden');
}

export function clearPermissionTreeInit(container) {
  if (container) delete container.dataset.permValidationInit;
}
