import { useEffect, useRef, useState } from 'react';

const MONTHS = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];

function getMonthRange(year, month) {
  const lastDay = new Date(year, month, 0).getDate();
  return {
    startDate: `${year}-${String(month).padStart(2, '0')}-01`,
    endDate: `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`,
  };
}

function getYearRange(year) {
  return {
    startDate: `${year}-01-01`,
    endDate: `${year}-12-31`,
  };
}

export default function MonthPicker({ onChange }) {
  const [monthValue, setMonthValue] = useState({ year: null, month: null });
  const [activeType, setActiveType] = useState(null);
  const rootRef = useRef(null);

  useEffect(() => {
    const handleClick = (event) => {
      if (!rootRef.current?.contains(event.target)) {
        setActiveType(null);
      }
    };
    document.addEventListener('click', handleClick);
    return () => document.removeEventListener('click', handleClick);
  }, []);

  const applySelection = (nextValue) => {
    setMonthValue(nextValue);
    setActiveType(null);

    if (nextValue.year && nextValue.month) {
      onChange(getMonthRange(nextValue.year, nextValue.month));
      return;
    }

    if (nextValue.year && !nextValue.month) {
      onChange(getYearRange(nextValue.year));
    }
  };

  const currentYear = new Date().getFullYear();
  const years = Array.from({ length: currentYear - 2022 + 2 }, (_, index) => 2022 + index);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
      <label className="form-label" style={{ margin: 0, display: 'flex', alignItems: 'center', gap: 4 }}>
        <i className="fas fa-calendar" style={{ color: '#000000ff' }} />
        选择年份和月份
      </label>
      <div className="enhanced-date-picker month-only" id="month-date-picker" ref={rootRef}>
        <div
          className={`date-part${activeType === 'year' ? ' active' : ''}`}
          data-type="year"
          onClick={() => setActiveType('year')}
        >
          <span id="month-year-display">{monthValue.year || '--'}</span>
        </div>
        <span className="date-separator">年</span>
        <div
          className={`date-part${activeType === 'month' ? ' active' : ''}`}
          data-type="month"
          onClick={() => setActiveType('month')}
        >
          <span id="month-month-display">
            {monthValue.month ? String(monthValue.month).padStart(2, '0') : '--'}
          </span>
        </div>
        <span className="date-separator">月</span>

        {activeType === 'year' && (
          <div className="date-dropdown show" id="month-dropdown">
            <div className="year-grid">
              {years.map((year) => (
                <div
                  key={year}
                  className={`date-option${monthValue.year === year ? ' selected' : ''}${year === currentYear ? ' today' : ''}`}
                  onClick={() => applySelection({ ...monthValue, year })}
                >
                  {year}
                </div>
              ))}
            </div>
          </div>
        )}

        {activeType === 'month' && (
          <div className="date-dropdown show" id="month-dropdown">
            <div className="month-grid">
              <div
                className={`date-option${!monthValue.month ? ' selected' : ''}`}
                style={{ gridColumn: '1 / -1' }}
                onClick={() => applySelection({ ...monthValue, month: null })}
              >
                无
              </div>
              {MONTHS.map((label, index) => {
                const month = index + 1;
                return (
                  <div
                    key={label}
                    className={`date-option${monthValue.month === month ? ' selected' : ''}`}
                    onClick={() => monthValue.year && applySelection({ ...monthValue, month })}
                  >
                    {label}
                  </div>
                );
              })}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
