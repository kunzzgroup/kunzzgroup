export default function StockListAlert({ toast }) {
  if (!toast) return <div id="alert-container" />;

  const iconMap = {
    success: 'fa-check-circle',
    error: 'fa-times-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle',
  };

  return (
    <div id="alert-container">
      <div className={`alert alert-${toast.type}`}>
        <i className={`fas ${iconMap[toast.type] || iconMap.info}`} />
        <span>{toast.message}</span>
      </div>
    </div>
  );
}
