<?php
$file = 'js/generatecode.js';
$content = file_get_contents($file);

// Replace initPermissionTreeEvents
$targetInit = 'function initPermissionTreeEvents() {
    // 如果已经绑定过，直接返回
    if (permissionTreeEventsBound) return;
    permissionTreeEventsBound = true;

    // 阻止label的默认行为，防止点击label时触发checkbox
    document.querySelectorAll(\'#permissionsModal .perm-checkbox-label\').forEach(label => {';

$replInit = 'function initPermissionTreeEvents(containerId) {
    const containerEl = document.getElementById(containerId);
    if (!containerEl) return;
    if (containerEl.dataset.treeBound === "true") return;
    containerEl.dataset.treeBound = "true";

    // 阻止label的默认行为，防止点击label时触发checkbox
    containerEl.querySelectorAll(\'.perm-checkbox-label\').forEach(label => {';

$content = str_replace($targetInit, $replInit, $content);

// In initPermissionTreeEvents, replace other document.querySelectorAll with containerEl.querySelectorAll
// This is bounded by the function body. We can use regex to replace within the function.
$startPos = strpos($content, 'function initPermissionTreeEvents(containerId)');
$endPos = strpos($content, 'function setDefaultAllPermissions()');

if ($startPos !== false && $endPos !== false) {
    $funcBody = substr($content, $startPos, $endPos - $startPos);
    
    $funcBody = str_replace('document.querySelectorAll(', 'containerEl.querySelectorAll(', $funcBody);
    $funcBody = str_replace('document.querySelector(', 'containerEl.querySelector(', $funcBody);
    $funcBody = str_replace('document.getElementById(\'perm-detail-content\')', 'containerEl.querySelector(\'.perm-detail-content\')', $funcBody);
    $funcBody = str_replace('#perm-detail-content', '.perm-detail-content', $funcBody);
    
    $content = substr_replace($content, $funcBody, $startPos, $endPos - $startPos);
}

// Update setPermCheckboxes to take containerId
$targetSetPerm = 'function setPermCheckboxes(perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms, uploadPerms) {';
$replSetPerm = 'function setPermCheckboxes(containerId, perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms, uploadPerms) {
    const containerEl = document.getElementById(containerId);
    if (!containerEl) return;';
$content = str_replace($targetSetPerm, $replSetPerm, $content);

// Within setPermCheckboxes, replace document.querySelectorAll
$startPos2 = strpos($content, 'function setPermCheckboxes(containerId,');
$endPos2 = strpos($content, 'function loadUserPermissions(');

if ($startPos2 !== false && $endPos2 !== false) {
    $funcBody2 = substr($content, $startPos2, $endPos2 - $startPos2);
    $funcBody2 = str_replace('document.querySelectorAll(', 'containerEl.querySelectorAll(', $funcBody2);
    $funcBody2 = str_replace('document.querySelector(', 'containerEl.querySelector(', $funcBody2);
    $content = substr_replace($content, $funcBody2, $startPos2, $endPos2 - $startPos2);
}

// Provide a new extractPermissionsData function
$extractFunc = '
function extractPermissionsData(containerId) {
    const containerEl = document.getElementById(containerId);
    if (!containerEl) return null;

    // 获取一级权限
    const perms = Array.from(containerEl.querySelectorAll(\'.perm-l1-check:checked\')).map(cb => cb.value);

    // 获取二级权限（按父级分组）
    const submenuPermissions = {};
    Object.keys(sidebarSubOptions).forEach(parent => {
        const mainCheckbox = containerEl.querySelector(`.perm-l1-check[value="${parent}"]`);
        const selectedSubs = Array.from(containerEl.querySelectorAll(`.perm-l2-check[data-parent="${parent}"]:checked`)).map(cb => cb.value);
        if (mainCheckbox && mainCheckbox.checked) {
            submenuPermissions[parent] = selectedSubs;
        } else {
            submenuPermissions[parent] = [];
        }
    });

    // 获取库存三级权限
    const selectedStockSystems = Array.from(containerEl.querySelectorAll(\'.perm-stock-system:checked\')).map(cb => cb.value);
    const selectedStockViews = Array.from(containerEl.querySelectorAll(\'.perm-stock-view:checked\')).map(cb => cb.value);

    // 获取数据上传三级权限
    const selectedUploadSystems = Array.from(containerEl.querySelectorAll(\'.perm-upload-system:checked\')).map(cb => cb.value);
    const selectedUploadTypes = Array.from(containerEl.querySelectorAll(\'.perm-upload-type:checked\')).map(cb => cb.value);

    const pagePermissions = {
        stock_inventory: {
            system: selectedStockSystems,
            view: selectedStockViews
        },
        kpi_upload: {
            system: selectedUploadSystems,
            type: selectedUploadTypes
        }
    };

    // 获取集团架构三级页面权限，每个店面独立保存
    const kunzzHoldingsPermissions = {};
    const blueprintChecked = containerEl.querySelector(\'.perm-page-blueprint[data-brand="kunzz_holdings"]\')?.checked || false;
    if (blueprintChecked) {
        kunzzHoldingsPermissions[\'blueprint\'] = [\'blueprint\'];
    }

    const cuisineStorePermissions = {};
    const j1ScheduleChecked = containerEl.querySelector(\'.perm-page-schedule[data-store="j1"]\')?.checked || false;
    if (j1ScheduleChecked) {
        cuisineStorePermissions[\'j1\'] = [\'schedule\'];
    }
    const j2ScheduleChecked = containerEl.querySelector(\'.perm-page-schedule[data-store="j2"]\')?.checked || false;
    if (j2ScheduleChecked) {
        cuisineStorePermissions[\'j2\'] = [\'schedule\'];
    }

    const izakayaStorePermissions = {};
    const j3ScheduleChecked = containerEl.querySelector(\'.perm-page-schedule[data-store="j3"]\')?.checked || false;
    if (j3ScheduleChecked) {
        izakayaStorePermissions[\'j3\'] = [\'schedule\'];
    }

    const brandPermissions = {
        kunzz_holdings: kunzzHoldingsPermissions,
        tokyo_cuisine: cuisineStorePermissions,
        tokyo_izakaya: izakayaStorePermissions
    };

    return {
        permissions: perms,
        page_permissions: pagePermissions,
        submenu_permissions: submenuPermissions,
        brand_permissions: brandPermissions
    };
}

function updatePermissionValidationState(containerId) {
    const containerEl = document.getElementById(containerId);
    if (!containerEl) return false;
    
    // Check if at least one L1 checkbox is selected
    const hasSelection = containerEl.querySelectorAll(\'.perm-l1-check:checked\').length > 0;
    
    const warningEl = containerEl.querySelector(\'.perm-warning\');
    if (warningEl) {
        warningEl.style.display = hasSelection ? \'none\' : \'block\';
    }
    
    const submitBtn = containerEl.querySelector(\'.btn-save\');
    if (submitBtn) {
        submitBtn.disabled = !hasSelection;
        if (!hasSelection) {
            submitBtn.style.opacity = \'0.5\';
            submitBtn.style.cursor = \'not-allowed\';
        } else {
            submitBtn.style.opacity = \'1\';
            submitBtn.style.cursor = \'pointer\';
        }
    }
    
    return hasSelection;
}
';

$content = str_replace('// 初始化权限树事件监听器', $extractFunc . '

// 初始化权限树事件监听器', $content);

file_put_contents($file, $content);
echo "generatecode.js updated successfully.\\n";
?>
