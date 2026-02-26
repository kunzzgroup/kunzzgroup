$content = Get-Content -Raw -Path generatecode.js

$start1 = $content.IndexOf("function initPermissionTreeEvents()")
$end1 = $content.IndexOf("function setDefaultAllPermissions()")
$block1 = $content.Substring($start1, $end1 - $start1)

$newBlock1 = $block1.Replace('function initPermissionTreeEvents() {', "function initPermissionTreeEvents(containerId) {`r`n    const containerEl = document.getElementById(containerId);`r`n    if (!containerEl) return;`r`n    if (containerEl.dataset.treeBound === 'true') return;`r`n    containerEl.dataset.treeBound = 'true';")
$newBlock1 = $newBlock1.Replace("if (permissionTreeEventsBound) return;`r`n    permissionTreeEventsBound = true;", "")
$newBlock1 = $newBlock1.Replace("document.querySelectorAll('#permissionsModal .perm-checkbox-label')", "containerEl.querySelectorAll('.perm-checkbox-label')")
$newBlock1 = $newBlock1.Replace("document.querySelectorAll('#perm-detail-content label')", "containerEl.querySelectorAll('.perm-detail-content label')")
$newBlock1 = $newBlock1.Replace("document.getElementById('perm-detail-content')", "containerEl.querySelector('.perm-detail-content')")
$newBlock1 = $newBlock1.Replace("document.querySelector(`#perm-detail-content .perm-level-3-panel", "containerEl.querySelector(`.perm-detail-content .perm-level-3-panel")
$newBlock1 = $newBlock1.Replace("document.querySelectorAll(", "containerEl.querySelectorAll(")
$newBlock1 = $newBlock1.Replace("document.querySelector(", "containerEl.querySelector(")

$content = $content.Substring(0, $start1) + $newBlock1 + $content.Substring($end1)

$start2 = $content.IndexOf("function setPermCheckboxes(")
$end2 = $content.IndexOf("function loadUserPermissions(")
$block2 = $content.Substring($start2, $end2 - $start2)

$newBlock2 = $block2.Replace('function setPermCheckboxes(perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms, uploadPerms) {', "function setPermCheckboxes(containerId, perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms, uploadPerms) {`r`n    const containerEl = document.getElementById(containerId);`r`n    if (!containerEl) return;")
$newBlock2 = $newBlock2.Replace("document.querySelectorAll(", "containerEl.querySelectorAll(")
$newBlock2 = $newBlock2.Replace("document.querySelector(", "containerEl.querySelector(")

$content = $content.Substring(0, $start2) + $newBlock2 + $content.Substring($end2)

# Insert new functions extractPermissionsData and updatePermissionValidationState before loadUserPermissions
$newFunctions = @"

function extractPermissionsData(containerId) {
    const containerEl = document.getElementById(containerId);
    if (!containerEl) return null;

    const perms = Array.from(containerEl.querySelectorAll('.perm-l1-check:checked')).map(cb => cb.value);

    const submenuPermissions = {};
    Object.keys(sidebarSubOptions).forEach(parent => {
        const mainCheckbox = containerEl.querySelector(`.perm-l1-check[value="` + parent + `"]`);
        const selectedSubs = Array.from(containerEl.querySelectorAll(`.perm-l2-check[data-parent="` + parent + `"]:checked`)).map(cb => cb.value);
        if (mainCheckbox && mainCheckbox.checked) {
            submenuPermissions[parent] = selectedSubs;
        } else {
            submenuPermissions[parent] = [];
        }
    });

    const selectedStockSystems = Array.from(containerEl.querySelectorAll('.perm-stock-system:checked')).map(cb => cb.value);
    const selectedStockViews = Array.from(containerEl.querySelectorAll('.perm-stock-view:checked')).map(cb => cb.value);

    const selectedUploadSystems = Array.from(containerEl.querySelectorAll('.perm-upload-system:checked')).map(cb => cb.value);
    const selectedUploadTypes = Array.from(containerEl.querySelectorAll('.perm-upload-type:checked')).map(cb => cb.value);

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

    const kunzzHoldingsPermissions = {};
    const blueprintChecked = containerEl.querySelector('.perm-page-blueprint[data-brand="kunzz_holdings"]')?.checked || false;
    if (blueprintChecked) {
        kunzzHoldingsPermissions['blueprint'] = ['blueprint'];
    }

    const cuisineStorePermissions = {};
    const j1ScheduleChecked = containerEl.querySelector('.perm-page-schedule[data-store="j1"]')?.checked || false;
    if (j1ScheduleChecked) {
        cuisineStorePermissions['j1'] = ['schedule'];
    }
    const j2ScheduleChecked = containerEl.querySelector('.perm-page-schedule[data-store="j2"]')?.checked || false;
    if (j2ScheduleChecked) {
        cuisineStorePermissions['j2'] = ['schedule'];
    }

    const izakayaStorePermissions = {};
    const j3ScheduleChecked = containerEl.querySelector('.perm-page-schedule[data-store="j3"]')?.checked || false;
    if (j3ScheduleChecked) {
        izakayaStorePermissions['j3'] = ['schedule'];
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
    const hasSelection = containerEl.querySelectorAll('.perm-l1-check:checked').length > 0;
    
    const warningEl = containerEl.querySelector('.perm-warning');
    if (warningEl) {
        warningEl.style.display = hasSelection ? 'none' : 'block';
    }
    
    const submitBtn = containerEl.querySelector('.btn-save');
    if (submitBtn) {
        submitBtn.disabled = !hasSelection;
        if (!hasSelection) {
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    }
    
    return hasSelection;
}

"@

$end2 = $content.IndexOf("function loadUserPermissions(")
$content = $content.Substring(0, $end2) + $newFunctions + $content.Substring($end2)

Set-Content -Path generatecode.js -Value $content
Write-Host "Success"
