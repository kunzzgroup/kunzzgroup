import { useEffect, useRef, useState } from 'react';

import { QUICK_RANGES } from '../../config/kpiConfig.js';
import DateRangePicker from './DateRangePicker.jsx';
import MonthPicker from './MonthPicker.jsx';
import ReportTypeSelector from '../shared/ReportTypeSelector.jsx';
import RestaurantSelector from './RestaurantSelector.jsx';

export default function KpiFilters({
  restaurantLabel,
  dateRange,
  onDateRangeChange,
  onQuickRange,
  onRestaurantSelect,
}) {
  const [quickOpen, setQuickOpen] = useState(false);
  const quickRef = useRef(null);

  useEffect(() => {
    const handleClick = (event) => {
      if (!quickRef.current?.contains(event.target)) setQuickOpen(false);
    };
    document.addEventListener('click', handleClick);
    return () => document.removeEventListener('click', handleClick);
  }, []);

  return (
    <div className="card" style={{ marginBottom: 'clamp(14px, 1.67vw, 32px)' }}>
      <div className="card-body">
        <div className="date-controls">
          <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
            <label className="form-label" style={{ margin: 0 }}>
              日期范围
            </label>
            <DateRangePicker startDate={dateRange.startDate} endDate={dateRange.endDate} onChange={onDateRangeChange} />
          </div>

          <div className="divider" />

          <MonthPicker onChange={onDateRangeChange} />

          <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }} ref={quickRef}>
            <label className="form-label" style={{ margin: 0, display: 'flex', alignItems: 'center', gap: 4 }}>
              <i className="fas fa-clock" style={{ color: '#000000ff' }} />
              快速选择
            </label>
            <div className="dropdown">
              <button type="button" className="btn btn-secondary dropdown-toggle" onClick={() => setQuickOpen((v) => !v)}>
                <i className="fas fa-calendar-alt" />
                <span id="quick-select-text">选择时间段</span>
                <i className="fas fa-chevron-down" />
              </button>
              {quickOpen && (
                <div className="dropdown-menu show" id="quick-select-dropdown">
                  {QUICK_RANGES.map((range) => (
                    <button
                      key={range.id}
                      type="button"
                      className="dropdown-item"
                      onClick={() => {
                        onQuickRange(range.id);
                        setQuickOpen(false);
                      }}
                    >
                      {range.label}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>

          <ReportTypeSelector active="kpi" />

          <RestaurantSelector label={restaurantLabel} onSelect={onRestaurantSelect} />
        </div>
      </div>
    </div>
  );
}

export function KpiChartTypeButtons({ chartDataType, onChange }) {
  return (
    <div className="chart-data-buttons" style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
      <button
        type="button"
        className={`chart-data-btn${chartDataType === 'netSales' ? ' active' : ''}`}
        data-type="netSales"
        onClick={() => onChange('netSales')}
      >
        净销售额
      </button>
      <button
        type="button"
        className={`chart-data-btn${chartDataType === 'tables' ? ' active' : ''}`}
        data-type="tables"
        onClick={() => onChange('tables')}
      >
        桌子数量
      </button>
      <button
        type="button"
        className={`chart-data-btn${chartDataType === 'diners' ? ' active' : ''}`}
        data-type="diners"
        onClick={() => onChange('diners')}
      >
        人数
      </button>
      <button
        type="button"
        className={`chart-data-btn${chartDataType === 'returningRate' ? ' active' : ''}`}
        data-type="returningRate"
        onClick={() => onChange('returningRate')}
      >
        常客(%)
      </button>
    </div>
  );
}
