import { useEffect, useRef, useState } from 'react';

import { CHART_DATA_TYPES, QUICK_RANGES } from '../../config/costConfig.js';
import ReportTypeSelector from '../shared/ReportTypeSelector.jsx';
import DateRangePicker from '../kpi/DateRangePicker.jsx';
import MonthPicker from '../kpi/MonthPicker.jsx';
import RestaurantSelector from '../kpi/RestaurantSelector.jsx';

export default function CostFilters({
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
    <div className="card date-controls-card">
      <div className="card-body">
        <div className="date-controls">
          <div className="date-control-group">
            <label className="form-label">日期范围</label>
            <DateRangePicker
              startDate={dateRange.startDate}
              endDate={dateRange.endDate}
              onChange={onDateRangeChange}
            />
          </div>

          <div className="divider" />

          <MonthPicker onChange={onDateRangeChange} />

          <div className="date-control-group" ref={quickRef}>
            <label className="form-label">
              <i className="fas fa-clock" />
              快速选择
            </label>
            <div className="dropdown">
              <button
                type="button"
                className="btn btn-secondary dropdown-toggle"
                onClick={() => setQuickOpen((v) => !v)}
              >
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

          <ReportTypeSelector active="cost" />

          <RestaurantSelector label={restaurantLabel} onSelect={onRestaurantSelect} />
        </div>
      </div>
    </div>
  );
}

export function CostChartTypeButtons({ chartDataType, onChange }) {
  return (
    <div className="chart-data-buttons">
      {CHART_DATA_TYPES.map((type) => (
        <button
          key={type.id}
          type="button"
          className={`chart-data-btn${chartDataType === type.id ? ' active' : ''}`}
          data-type={type.id}
          onClick={() => onChange(type.id)}
        >
          {type.label}
        </button>
      ))}
    </div>
  );
}
