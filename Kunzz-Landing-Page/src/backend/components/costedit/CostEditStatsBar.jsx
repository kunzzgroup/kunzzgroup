import { formatStatNumber } from '../../utils/costEditCalculations.js';

export default function CostEditStatsBar({
  stats,
  currentStock,
  saving,
  onStockChange,
  onSaveAll,
}) {
  return (
    <div className="action-buttons">
      <div className="stats-info" id="month-stats">
        <div className="stat-item">
          <i className="fas fa-calendar-day" />
          <span>
            已填写: <span className="stat-value" id="filled-days">{stats.filledDays}</span> 天
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-dollar-sign" />
          <span>
            月总销售额: RM <span className="stat-value" id="total-sales">{formatStatNumber(stats.totalSales)}</span>
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-chart-pie" />
          <span>
            月总成本: RM <span className="stat-value" id="total-cost">{formatStatNumber(stats.totalCost)}</span>
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-money-bill-wave" />
          <span>
            月总毛利润: RM <span className="stat-value" id="total-profit">{formatStatNumber(stats.totalProfit)}</span>
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-percentage" />
          <span>
            平均成本率: <span className="stat-value" id="avg-cost-percent">{formatStatNumber(stats.avgCostPercent)}</span>%
          </span>
        </div>
      </div>

      <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
        <div className="stock-input-container">
          <label htmlFor="current-stock-input">
            <i className="fas fa-warehouse" />
            当前库存 (RM):
          </label>
          <input
            type="number"
            id="current-stock-input"
            min="0"
            step="0.01"
            placeholder="0.00"
            value={currentStock}
            onChange={(event) => onStockChange(event.target.value)}
          />
        </div>
        <button type="button" className="btn btn-primary" onClick={onSaveAll} disabled={saving}>
          <i className="fas fa-save" />
          {saving ? '保存中...' : '保存本月数据'}
        </button>
      </div>
    </div>
  );
}
