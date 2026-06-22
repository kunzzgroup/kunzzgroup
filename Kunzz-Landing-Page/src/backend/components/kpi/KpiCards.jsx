import { getDeployBasePath } from '../../../config.js';
import { KPI_CARDS } from '../../config/kpiConfig.js';
import { formatCurrency, formatNumber } from '../../utils/kpiCalculations.js';

const PLACEHOLDER = '--';

function getCardValue(cardId, summary, hasRestaurant) {
  if (!hasRestaurant) return PLACEHOLDER;

  switch (cardId) {
    case 'totalSales':
      return formatCurrency(summary.totalSales);
    case 'netSales':
      return formatCurrency(summary.netSales);
    case 'totalTables':
      return formatNumber(summary.totalTables);
    case 'totalDiners':
      return formatNumber(summary.totalDiners);
    case 'returningRate':
      return `${formatCurrency(summary.returningRate)}%`;
    case 'avgPerDiner':
      return formatCurrency(summary.avgPerDiner);
    default:
      return PLACEHOLDER;
  }
}

export default function KpiCards({ summary, hasRestaurant }) {
  const tableIcon = `${getDeployBasePath()}/images/images/table.svg`;

  return (
    <div className="kpi-grid">
      {KPI_CARDS.map((card) => (
        <div key={card.id} className="card">
          <div className="card-body">
            <div className="kpi-card-vertical">
              <div className={`icon ${card.colorClass}`}>
                {card.icon === 'table' ? (
                  <img
                    src={tableIcon}
                    alt="桌子图标"
                    style={{ width: 'clamp(30px, 2.1vw, 40px)', height: 'clamp(28px, 1.98vw, 38px)', filter: 'brightness(0)' }}
                  />
                ) : (
                  <i className={`fas ${card.icon}`} />
                )}
              </div>
              <div>
                <p className="kpi-label">{card.label}</p>
                <p
                  className="kpi-value"
                  id={
                    card.id === 'totalSales'
                      ? 'total-sales'
                      : card.id === 'netSales'
                        ? 'net-sales'
                        : card.id === 'totalTables'
                          ? 'total-tables'
                          : card.id === 'totalDiners'
                            ? 'total-diners'
                            : card.id === 'returningRate'
                              ? 'returning-rate'
                              : 'avg-per-diner'
                  }
                >
                  {getCardValue(card.id, summary, hasRestaurant)}
                </p>
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
