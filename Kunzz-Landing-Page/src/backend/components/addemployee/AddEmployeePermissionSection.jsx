import PermissionTreePanel from '../generatecode/PermissionTreePanel.jsx';
import { closeDetailPanel } from '../../utils/permissionTreeDom.js';

export default function AddEmployeePermissionSection({ showWarning }) {
  const handleCloseDetail = () => {
    const container = document.querySelector('.editUserPermLayout');
    closeDetailPanel(container);
  };

  return (
    <div className="form-section editUserPermLayout">
      <div className="form-section-header" style={{ textTransform: 'uppercase' }}>
        权限管理 PERMISSION MANAGEMENT
      </div>
      <div className="form-section-content">
        <PermissionTreePanel onCloseDetail={handleCloseDetail} />
        <div
          className="perm-warning"
          style={{
            display: showWarning ? 'block' : 'none',
            color: '#dc2626',
            fontSize: 13,
            fontWeight: 'bold',
            marginTop: 10,
            textAlign: 'center',
          }}
        >
          <i className="fas fa-exclamation-triangle" /> 请至少选择一项权限
        </div>
      </div>
    </div>
  );
}
