const fs = require('fs');
const path = require('path');

const srcPath = path.join(__dirname, '../../backend/js/generatecode.js');
const outPath = path.join(__dirname, '../src/backend/utils/permissionTreeDom.js');
const src = fs.readFileSync(srcPath, 'utf8');
const start = src.indexOf('const sidebarSubOptions');
const end = src.indexOf('function openDownloadModal');
let chunk = src.slice(start, end);

chunk = chunk.replace(/^function /gm, 'function _');
chunk = chunk.replace(/function _initPermissionTreeEvents/g, 'export function initPermissionTreeEvents');
chunk = chunk.replace(/function _setDefaultAllPermissions/g, 'export function setDefaultAllPermissions');
chunk = chunk.replace(/function _updatePermissionValidationState/g, 'function updatePermissionValidationState');
chunk = chunk.replace(/function _resetPermissionTree/g, 'export function resetPermissionTree');
chunk = chunk.replace(/function _syncLevel2Permissions/g, 'function syncLevel2Permissions');
chunk = chunk.replace(/function _syncLevel3Permissions/g, 'function syncLevel3Permissions');
chunk = chunk.replace(/function _setPermCheckboxes/g, 'export function setPermCheckboxes');
chunk = chunk.replace(/function _extractPermissionsData/g, 'export function extractPermissionsData');

chunk = chunk.replace(/document\.querySelectorAll\('\.perm-/g, 'container.querySelectorAll(".perm-');
chunk = chunk.replace(/document\.querySelector\('\.perm-/g, 'container.querySelector(".perm-');
chunk = chunk.replace(
  /document\.querySelectorAll\('#permissionsModal input\[type="checkbox"\]'\)/g,
  'container.querySelectorAll(\'input[type="checkbox"]\')',
);

chunk = chunk.replace(
  'export function setPermCheckboxes(perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms) {',
  'export function setPermCheckboxes(container, perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms) {\n    if (!container) return;',
);

chunk = chunk.replace(
  'export function extractPermissionsData(container) {',
  'export function extractPermissionsData(container) {',
);

chunk += `
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
`;

const header = 'export const sidebarSubOptions = {\n    analytics: [\'kpi_report\', \'kpi_upload\'],\n    hr: [\'staff_management\'],\n    resource: [\'stock_inventory\', \'dishware\', \'price_comparison\'],\n    brand: [\'kunzz_holdings\', \'tokyo_cuisine\', \'tokyo_izakaya\']\n};\n\n';
const body = chunk.slice(chunk.indexOf('// 初始化权限树'));
fs.writeFileSync(outPath, header + body);
console.log('written', outPath, fs.statSync(outPath).size);
