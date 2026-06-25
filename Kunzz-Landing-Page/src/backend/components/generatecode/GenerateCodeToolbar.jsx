import { getBackendBase, getDeployBasePath } from '../../../config.js';
import { DOWNLOAD_COMPANIES } from '../../config/generatecodeConstants.js';

export default function GenerateCodeToolbar({
  searchTerm,
  onSearchChange,
  onClearSearch,
  onOpenDownload,
}) {
  const backendBase = getBackendBase();

  return (
    <div className="generate-form">
      <div id="messageArea" />
      <form id="generateForm" onSubmit={(event) => event.preventDefault()}>
        <div className="form-row" style={{ justifyContent: 'space-between', alignItems: 'end' }}>
          <div className="form-group" style={{ flex: '0 0 auto', display: 'flex', alignItems: 'center', gap: 12 }}>
            <a href={`${backendBase}/add_employee.php`} className="btn-generate">
              <i className="fas fa-user-plus" /> 添加新职员
            </a>
            <button type="button" className="btn-generate" onClick={onOpenDownload}>
              <i className="fas fa-download" /> 下载面试表
            </button>
          </div>

          <div
            className="form-group"
            style={{ flex: '0 0 auto', position: 'relative', display: 'flex', alignItems: 'center', gap: 10 }}
          >
            <div style={{ position: 'relative' }}>
              <input
                type="text"
                id="searchInput"
                placeholder="输入英文姓名或邮箱进行搜索..."
                value={searchTerm}
                onChange={(event) => onSearchChange(event.target.value)}
                style={{
                  padding: '10px 40px 10px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: 8,
                  fontSize: 'clamp(8px, 0.74vw, 14px)',
                }}
              />
              <button
                type="button"
                onClick={onClearSearch}
                style={{
                  position: 'absolute',
                  right: 8,
                  top: '50%',
                  transform: 'translateY(-50%)',
                  background: 'none',
                  border: 'none',
                  color: '#999',
                  cursor: 'pointer',
                  fontSize: 16,
                }}
                title="清除搜索"
              >
                ×
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  );
}

export function DownloadModal({ open, company, onCompanyChange, onClose, onConfirm }) {
  if (!open) return null;

  return (
    <div
      className="modal"
      style={{ display: 'block' }}
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="modal-content" style={{ maxWidth: 520 }}>
        <div className="modal-header" style={{ color: '#000000' }}>
          <i className="fas fa-download" /> 下载面试表
        </div>
        <div className="modal-body">
          <div className="form-group" style={{ marginBottom: 20 }}>
            <label
              htmlFor="company_select"
              style={{ fontSize: 14, fontWeight: 600, marginBottom: 10, display: 'block' }}
            >
              请选择公司/店铺
            </label>
            <select
              id="company_select"
              value={company}
              onChange={(event) => onCompanyChange(event.target.value)}
              style={{
                width: '100%',
                padding: 12,
                border: '2px solid #f99e00',
                borderRadius: 8,
                fontSize: 14,
              }}
            >
              <option value="">请选择...</option>
              {DOWNLOAD_COMPANIES.map((item) => (
                <option key={item.value} value={item.value}>
                  {item.label}
                </option>
              ))}
            </select>
          </div>
          <div className="modal-buttons">
            <button type="button" className="btn-action btn-save" onClick={onConfirm}>
              <i className="fas fa-check" /> 确认下载
            </button>
            <button type="button" className="btn-action btn-cancel" onClick={onClose}>
              <i className="fas fa-times" /> 取消
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export function DeleteConfirmModal({ staff, open, saving, onClose, onConfirm }) {
  if (!open || !staff) return null;

  return (
    <div
      className="modal"
      style={{ display: 'block' }}
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="modal-content">
        <div className="modal-header">
          <i className="fas fa-exclamation-triangle" /> 确认删除
        </div>
        <div className="modal-body">
          确定要删除职员 &quot;<strong style={{ color: '#f44336' }}>{staff.username || '未知职员'}</strong>&quot; 吗？
          <br />
          <br />
          <strong style={{ color: '#ff9800' }}>⚠️ 此操作不可撤销！</strong>
        </div>
        <div className="modal-buttons">
          <button type="button" className="btn-action btn-delete" disabled={saving} onClick={onConfirm}>
            <i className="fas fa-trash" /> {saving ? '删除中...' : '确认删除'}
          </button>
          <button type="button" className="btn-action btn-cancel" disabled={saving} onClick={onClose}>
            <i className="fas fa-times" /> 取消
          </button>
        </div>
      </div>
    </div>
  );
}

export function downloadInterviewForm(companyValue) {
  const company = DOWNLOAD_COMPANIES.find((item) => item.value === companyValue);
  if (!company) return null;

  const deployBase = getDeployBasePath();
  const pdfPath = `${deployBase}/form/${company.file}`.replace('//', '/');
  const link = document.createElement('a');
  link.href = pdfPath;
  link.download = company.file;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  return company.label;
}
