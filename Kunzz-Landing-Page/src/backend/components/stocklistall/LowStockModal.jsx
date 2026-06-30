export default function LowStockModal({ alerts, onClose }) {
  if (!alerts?.length) return null;

  const sortedAlerts = [...alerts].sort((a, b) => {
    const nameA = (a.product_name || '').toUpperCase();
    const nameB = (b.product_name || '').toUpperCase();
    return nameA.localeCompare(nameB);
  });

  return (
    <div
      id="low-stock-modal"
      className="low-stock-modal"
      style={{ display: 'block' }}
      onClick={(e) => {
        if (e.target.id === 'low-stock-modal') onClose();
      }}
    >
      <div className="low-stock-modal-content">
        <div className="low-stock-modal-header">
          <h2>
            <i className="fas fa-exclamation-triangle" />
            库存不足提醒
          </h2>
          <button type="button" className="close-modal" onClick={onClose}>
            <i className="fas fa-times" />
          </button>
        </div>
        <div className="low-stock-modal-body">
          <div id="low-stock-content">
            <div
              style={{
                fontSize: 'clamp(8px, 0.84vw, 16px)',
                padding: 'clamp(6px, 0.63vw, 12px)',
                backgroundColor: '#fef2f2',
                border: '1px solid #fecaca',
                borderRadius: 6,
                color: '#b91c1c',
              }}
            >
              <i className="fas fa-exclamation-triangle" style={{ marginRight: 8 }} />
              发现 {sortedAlerts.length} 个货品库存不足，请及时补货！
            </div>
            <table className="low-stock-table">
              <thead>
                <tr>
                  <th>货品名称</th>
                  <th>货品编号</th>
                  <th>规格</th>
                  <th>当前库存</th>
                  <th>最低库存</th>
                </tr>
              </thead>
              <tbody>
                {sortedAlerts.map((alert) => {
                  const currentStock = parseFloat(alert.current_stock);
                  const minimumStock = parseFloat(alert.minimum_quantity);
                  let statusClass = 'stock-warning';
                  if (currentStock <= 0 || currentStock <= minimumStock * 0.5) {
                    statusClass = 'stock-critical';
                  }

                  return (
                    <tr key={`${alert.product_name}-${alert.code_number}-${alert.specification}`}>
                      <td><strong>{(alert.product_name || '').trim()}</strong></td>
                      <td>{alert.code_number || '-'}</td>
                      <td>{alert.specification || '-'}</td>
                      <td className={statusClass}>{alert.formatted_stock}</td>
                      <td>{minimumStock > 0 ? minimumStock.toFixed(2) : '-'}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
        <div className="modal-footer">
          <div className="alert-summary" id="alert-summary">
            共 {sortedAlerts.length} 项需要关注
          </div>
        </div>
      </div>
    </div>
  );
}
