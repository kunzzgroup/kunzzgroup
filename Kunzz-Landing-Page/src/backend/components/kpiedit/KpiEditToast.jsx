export default function KpiEditToast({ toast }) {
  if (!toast) return null;

  const typeClass = {
    success: 'toast-success',
    error: 'toast-error',
    info: 'toast-info',
    warning: 'toast-warning',
  }[toast.type] || 'toast-info';

  return (
    <div className="toast-container" id="toast-container">
      <div className={`toast ${typeClass} show`}>{toast.message}</div>
    </div>
  );
}
