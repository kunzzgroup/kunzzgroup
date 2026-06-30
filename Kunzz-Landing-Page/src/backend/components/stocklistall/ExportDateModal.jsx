const QUICK_BUTTONS = [
  { type: 'today', label: '今天' },
  { type: 'this_month', label: '本月' },
  { type: 'last_month', label: '上月' },
  { type: 'all', label: '全部' },
];

export default function ExportDateModal({
  open,
  exportDates,
  onClose,
  onChangeDates,
  onQuickRange,
  onConfirm,
  exporting,
}) {
  if (!open) return null;

  const todayStr = new Date().toISOString().slice(0, 10);

  return (
    <div
      id="export-date-modal"
      className="export-date-modal"
      style={{ display: 'block' }}
      onClick={(e) => {
        if (e.target.id === 'export-date-modal') onClose();
      }}
    >
      <div className="export-date-modal-content">
        <div className="export-date-modal-header">
          <h2>
            <i className="fas fa-calendar-alt" />
            选择导出日期范围
          </h2>
          <button type="button" className="close-modal" onClick={onClose}>
            <i className="fas fa-times" />
          </button>
        </div>
        <div className="export-date-modal-body">
          <div className="date-range-row">
            <div className="date-selector-group">
              <label htmlFor="export-start-date">开始日期</label>
              <input
                type="date"
                id="export-start-date"
                max={todayStr}
                value={exportDates.startDate}
                onChange={(e) => onChangeDates({ ...exportDates, startDate: e.target.value })}
              />
            </div>
            <div className="date-range-separator">
              <i className="fas fa-arrow-right" />
            </div>
            <div className="date-selector-group">
              <label htmlFor="export-end-date">结束日期</label>
              <input
                type="date"
                id="export-end-date"
                required
                max={todayStr}
                value={exportDates.endDate}
                onChange={(e) => onChangeDates({ ...exportDates, endDate: e.target.value })}
              />
            </div>
          </div>
          <div className="date-quick-buttons">
            {QUICK_BUTTONS.map((btn) => (
              <button
                key={btn.type}
                type="button"
                className={`btn btn-quick-date${exportDates.quickType === btn.type ? ' active' : ''}`}
                onClick={() => onQuickRange(btn.type)}
              >
                {btn.label}
              </button>
            ))}
          </div>
        </div>
        <div className="modal-footer">
          <button type="button" className="btn btn-secondary" onClick={onClose}>
            <i className="fas fa-times" />
            取消
          </button>
          <button
            type="button"
            className="btn btn-primary"
            onClick={onConfirm}
            disabled={exporting}
          >
            <i className="fas fa-download" />
            {exporting ? '导出中...' : '确认导出'}
          </button>
        </div>
      </div>
    </div>
  );
}
