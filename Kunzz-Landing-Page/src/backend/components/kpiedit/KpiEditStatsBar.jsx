import { formatStatNumber } from '../../utils/kpiEditCalculations.js';

export default function KpiEditStatsBar({ stats, saving, onSaveAll }) {
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
          <i className="fas fa-chart-line" />
          <span>
            月总净利润额: RM <span className="stat-value" id="total-sales">{formatStatNumber(stats.totalNetSales)}</span>
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-money-bill-wave" />
          <span>
            月总利润额: RM <span className="stat-value" id="total-tender">{formatStatNumber(stats.totalTenderAmount)}</span>
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-users" />
          <span>
            月总顾客人数: <span className="stat-value" id="total-diners">{formatStatNumber(stats.totalDiners, 0)}</span>
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-table" />
          <span>
            月总桌数: <span className="stat-value" id="total-tables">{formatStatNumber(stats.totalTables, 0)}</span>
          </span>
        </div>
        <div className="stat-item">
          <i className="fas fa-calculator" />
          <span>
            月总人均消费: RM <span className="stat-value" id="avg-per-customer">{formatStatNumber(stats.avgPerCustomer)}</span>
          </span>
        </div>
      </div>

      <div style={{ display: 'flex', gap: 12 }}>
        <button type="button" className="btn btn-primary" onClick={onSaveAll} disabled={saving}>
          <i className="fas fa-save" />
          {saving ? '保存中...' : '保存本月数据'}
        </button>
      </div>
    </div>
  );
}
