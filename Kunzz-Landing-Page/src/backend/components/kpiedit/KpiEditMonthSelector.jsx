import { getYearOptions } from '../../config/kpiEditConfig.js';

export default function KpiEditMonthSelector({ year, month, restaurantName, onYearChange, onMonthChange }) {
  return (
    <div className="month-selector">
      <div>
        <label htmlFor="year-select">年份:</label>
        <select id="year-select" value={year} onChange={(event) => onYearChange(Number(event.target.value))}>
          {getYearOptions().map((optionYear) => (
            <option key={optionYear} value={optionYear}>
              {optionYear}年
            </option>
          ))}
        </select>
      </div>
      <div>
        <label htmlFor="month-select">月份:</label>
        <select id="month-select" value={month} onChange={(event) => onMonthChange(Number(event.target.value))}>
          {Array.from({ length: 12 }, (_, index) => index + 1).map((optionMonth) => (
            <option key={optionMonth} value={optionMonth}>
              {optionMonth}月
            </option>
          ))}
        </select>
      </div>
      <div id="current-restaurant-info" className="stat-item">
        <i className="fas fa-store" />
        <span>
          当前: <span className="stat-value">{restaurantName}</span>
        </span>
      </div>
    </div>
  );
}
