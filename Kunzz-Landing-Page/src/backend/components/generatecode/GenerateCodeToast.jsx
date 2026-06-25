export default function GenerateCodeToast({ toast }) {
  if (!toast) return null;

  const cfg = {
    success: { icon: '✅', title: '操作成功', className: 'toast-success' },
    error: { icon: '❌', title: '操作失败', className: 'toast-error' },
    info: { icon: 'ℹ️', title: '提示信息', className: 'toast-info' },
    warning: { icon: '⚠️', title: '注意', className: 'toast-warning' },
  }[toast.type] || { icon: '✅', title: '操作成功', className: 'toast-success' };

  return (
    <div className="toast-container" id="toast-container">
      <div className={`toast ${cfg.className} show`}>
        <div className="toast-icon-wrap">{cfg.icon}</div>
        <div className="toast-body">
          <div className="toast-title">{cfg.title}</div>
          <div className="toast-msg">{toast.message}</div>
        </div>
      </div>
    </div>
  );
}
