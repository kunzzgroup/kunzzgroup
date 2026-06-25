import { useEffect, useRef, useState } from 'react';

import { fetchUserPermissions } from '../../api/generatecodeApi.js';
import {
  clearPermissionTreeInit,
  closeDetailPanel,
  extractPermissionsData,
  initPermissionTreeEvents,
  resetPermissionTree,
  setDefaultAllPermissions,
  setPermCheckboxes,
} from '../../utils/permissionTreeDom.js';
import PermissionTreePanel from './PermissionTreePanel.jsx';

export default function PermissionsModal({ user, open, saving, onClose, onSave, onError }) {
  const containerRef = useRef(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!open || !user?.id || !containerRef.current) return undefined;

    const container = containerRef.current;
    let active = true;

    (async () => {
      setLoading(true);
      resetPermissionTree(container);
      clearPermissionTreeInit(container);
      setDefaultAllPermissions(container);
      initPermissionTreeEvents(container);

      try {
        const data = await fetchUserPermissions(user.id);
        if (!active) return;
        if (data.success) {
          setPermCheckboxes(
            container,
            data.permissions || [],
            data.page_permissions || {},
            data.submenu_permissions || {},
            data.report_permissions || [],
            data.restaurant_permissions || [],
            data.brand_permissions || {},
          );
        }
      } catch {
        if (active) onError('加载权限失败');
      } finally {
        if (active) setLoading(false);
      }
    })();

    return () => {
      active = false;
      resetPermissionTree(container);
      clearPermissionTreeInit(container);
    };
  }, [open, user?.id, onError]);

  if (!open || !user) return null;

  const handleSave = async () => {
    if (!containerRef.current || !user?.id) return;
    const payload = extractPermissionsData(containerRef.current);
    await onSave({
      user_id: user.id,
      permissions: payload.perms,
      page_permissions: payload.pagePermissions,
      submenu_permissions: payload.submenuPermissions,
      report_permissions: payload.reportPermissions,
      restaurant_permissions: payload.restaurantPermissions,
      brand_permissions: payload.brandPermissions,
    });
  };

  return (
    <div
      className="modal"
      style={{ display: 'block' }}
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="modal-content" style={{ maxWidth: '1200px', width: '85vw' }}>
        <div
          className="modal-header"
          style={{ color: '#ff5c00', fontSize: 24, marginBottom: 20, fontWeight: 600 }}
        >
          <i className="fas fa-user-shield" /> 用户权限设定 - {user.username || '未命名用户'}
        </div>

        <div className="modal-body" ref={containerRef} id="permissionsModal">
          {loading ? <p style={{ padding: 16 }}>正在加载权限...</p> : null}
          <PermissionTreePanel onCloseDetail={() => closeDetailPanel(containerRef.current)} />
        </div>

        <div className="modal-buttons">
          <button type="button" className="btn-action btn-save" disabled={saving || loading} onClick={handleSave}>
            {saving ? '保存中...' : '保存'}
          </button>
          <button type="button" className="btn-action btn-cancel" disabled={saving} onClick={onClose}>
            取消
          </button>
        </div>
      </div>
    </div>
  );
}
