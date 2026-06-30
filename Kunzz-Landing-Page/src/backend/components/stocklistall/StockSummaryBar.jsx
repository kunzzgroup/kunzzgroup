import {
  SEARCH_PLACEHOLDER,
  TYPE_FILTER_OPTIONS,
} from '../../config/stockListConstants.js';
import SmartSearchWrapper from '../common/SmartSearchWrapper.jsx';

const TYPE_LABEL_IDS = {
  'Service Line': 'service-line',
  Sake: 'sake',
  Kitchen: 'kitchen',
  'Sushi Bar': 'sushi-bar',
};

const CENTRAL_SUPPLY_LABELS = {
  j1: 'J1供应',
  j2: 'J2供应',
  j3: 'J3供应',
};

function TypeStatCard({
  title,
  value,
  negative,
  filterable,
  active,
  onToggle,
  type,
}) {
  const className = [
    'type-grid-item',
    filterable ? 'is-filterable' : '',
    active ? 'is-active' : '',
  ].filter(Boolean).join(' ');

  const props = filterable
    ? {
        role: 'button',
        tabIndex: 0,
        'aria-pressed': active ? 'true' : 'false',
        onClick: onToggle,
        onKeyDown: (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            onToggle();
          }
        },
      }
    : {};

  return (
    <div className={className} data-type={type || undefined} {...props}>
      <div className="grid-title">{title}</div>
      <div className={`grid-value${negative ? ' negative' : ''}`}>{value || '0.00'}</div>
    </div>
  );
}

export default function StockSummaryBar({
  system,
  summaryData,
  supplyTotals,
  searchTerm,
  onSearchChange,
  onExport,
  onMinimumSettings,
  displayedCount,
  totalCount,
  typeFilters,
  onToggleTypeFilter,
}) {
  const totalValue = summaryData?.formatted_total_value || '0.00';
  const typeStats = summaryData?.type_stats;

  return (
    <div className="unified-header-row">
      <div className="header-summary">
        <div className="summary-title">总库存</div>
        <div className="summary-amount">
          <span className="currency-symbol">RM</span>
          <span className="value" id={`${system}-total-value`}>{totalValue}</span>
        </div>
      </div>

      <div className="type-grid-container">
        {system === 'central' ? (
          Object.entries(CENTRAL_SUPPLY_LABELS).map(([key, label]) => (
            <TypeStatCard
              key={key}
              title={label}
              value={supplyTotals[key]}
            />
          ))
        ) : (
          (TYPE_FILTER_OPTIONS[system] || []).map((type) => {
            const idKey = TYPE_LABEL_IDS[type];
            const statKey = type === 'Service Line' ? 'service_line' : type.toLowerCase().replace(' ', '_');
            const formattedKey = `formatted_${statKey}`;
            const rawKey = statKey;
            const value = typeStats?.[formattedKey] || '0.00';
            const negative = typeStats?.[rawKey] < 0;

            return (
              <TypeStatCard
                key={type}
                title={type}
                value={value}
                negative={negative}
                filterable
                active={typeFilters.has(type)}
                type={type}
                onToggle={() => onToggleTypeFilter(type)}
              />
            );
          })
        )}
      </div>

      <div className="header-right-section">
        <div className="header-search">
          <SmartSearchWrapper
            key={system}
            id={`${system}-unified-filter`}
            placeholder={SEARCH_PLACEHOLDER[system]}
            value={searchTerm}
            onChange={onSearchChange}
          />
        </div>

        <button
          type="button"
          className="btn btn-warning btn-expand"
          title="导出数据"
          onClick={onExport}
        >
          <span className="btn-expand-icon"><i className="fas fa-download" /></span>
          <span className="btn-expand-text">导出数据</span>
        </button>

        <button
          type="button"
          className="btn btn-primary btn-expand"
          title="设置最低库存"
          onClick={onMinimumSettings}
        >
          <span className="btn-expand-icon"><i className="fas fa-cog" /></span>
          <span className="btn-expand-text">设置最低库存</span>
        </button>

        <div className="header-stats">
          <span>
            显示记录: <span className="stat-value" id={`${system}-displayed-records`}>{displayedCount}</span>
          </span>
          <span>
            总记录: <span className="stat-value" id={`${system}-total-records`}>{totalCount}</span>
          </span>
        </div>
      </div>
    </div>
  );
}
