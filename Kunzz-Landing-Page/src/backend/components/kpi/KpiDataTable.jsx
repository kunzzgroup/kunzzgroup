import { formatCurrency } from '../../utils/kpiCalculations.js';

export default function KpiDataTable({ rows, restaurant, loading, error }) {
  const firstHeader = restaurant === 'total' ? '日期 (三店合计)' : '日期';

  if (loading) {
    return <div className="card-body">正在加载数据...</div>;
  }

  if (error) {
    return <div className="card-body" style={{ color: '#C62828' }}>❌ {error}</div>;
  }

  return (
    <div className="card">
      <div className="card-body" style={{ paddingBottom: 0 }}>
        <h3 style={{ fontSize: 'clamp(14px, 1.04vw, 20px)', fontWeight: 600, color: '#111827', marginBottom: 24 }}>
          详细数据
        </h3>
      </div>
      <div style={{ overflowX: 'auto' }}>
        <table className="table">
          <thead>
            <tr>
              <th>{firstHeader}</th>
              <th>总销售额</th>
              <th>净销售额</th>
              <th>人均消费</th>
              <th>桌子总数</th>
              <th>顾客总数</th>
              <th>新客人数</th>
              <th>常客人数</th>
              <th>常客百分比</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 ? (
              <tr>
                <td colSpan={9} style={{ textAlign: 'center', padding: 24 }}>
                  暂无数据
                </td>
              </tr>
            ) : (
              rows.map((item) => {
                const totalCustomers = item.returningCustomers + item.newCustomers;
                const returningRate =
                  totalCustomers > 0 ? ((item.returningCustomers / totalCustomers) * 100).toFixed(2) : '0.00';

                return (
                  <tr key={item.date}>
                    <td>{item.date}</td>
                    <td>RM {formatCurrency(item.totalSales)}</td>
                    <td>RM {formatCurrency(item.netSales)}</td>
                    <td>RM {formatCurrency(item.avgSalesPerDiner)}</td>
                    <td>{item.tablesUsed}</td>
                    <td>{item.diners}</td>
                    <td>{item.newCustomers}</td>
                    <td>{item.returningCustomers}</td>
                    <td>{returningRate}%</td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
