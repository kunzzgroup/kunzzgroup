import { formatCurrency, formatPercent } from '../../utils/costCalculations.js';

export default function CostDataTable({ rows, restaurant, loading, error }) {
  const dateHeader = restaurant === 'total' ? '日期 (三店合计)' : '日期';

  if (!restaurant) {
    return (
      <div className="card detail-card">
        <div className="card-body">
          <h3>详细数据</h3>
          <p style={{ color: '#6b7280' }}>请先选择餐厅</p>
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="card detail-card">
        <div className="card-body">
          <h3>详细数据</h3>
          <p>正在加载...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="card detail-card">
        <div className="card-body">
          <h3>详细数据</h3>
          <p style={{ color: '#dc2626' }}>{error}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="card detail-card">
      <div className="card-body">
        <h3>详细数据</h3>
      </div>
      <div className="table-scroll">
        <table className="table" id="dashboard-table">
          <thead>
            <tr id="table-header">
              <th>{dateHeader}</th>
              <th>销售额</th>
              <th>饮料成本</th>
              <th>厨房成本</th>
              <th>Grab Food</th>
              <th>Foodpanda</th>
              <th>Shopee Food</th>
              <th>总成本</th>
              <th>毛利润</th>
              <th>成本率 (%)</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 ? (
              <tr>
                <td colSpan={10} style={{ textAlign: 'center', color: '#6b7280' }}>
                  暂无数据
                </td>
              </tr>
            ) : (
              rows.map((item) => (
                <tr key={item.date}>
                  <td>{item.displayDate || item.date}</td>
                  <td>RM {formatCurrency(item.sales)}</td>
                  <td>RM {formatCurrency(item.cBeverage)}</td>
                  <td>RM {formatCurrency(item.cKitchen)}</td>
                  <td>RM {formatCurrency(item.cGrab)}</td>
                  <td>RM {formatCurrency(item.cFoodpanda)}</td>
                  <td>RM {formatCurrency(item.cShopee)}</td>
                  <td>RM {formatCurrency(item.cTotal)}</td>
                  <td>RM {formatCurrency(item.grossTotal)}</td>
                  <td>{formatPercent(item.costPercent)}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
