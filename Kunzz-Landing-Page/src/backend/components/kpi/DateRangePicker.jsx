import { useEffect, useRef, useState } from 'react';

import { formatDateLocal, formatDisplayRange } from '../../utils/dateUtils.js';

function buildMonthGrid(year, month) {
  const firstDay = new Date(year, month, 1);
  const startWeekday = firstDay.getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const cells = [];

  for (let i = 0; i < startWeekday; i += 1) cells.push(null);
  for (let day = 1; day <= daysInMonth; day += 1) cells.push(day);
  return cells;
}

export default function DateRangePicker({ startDate, endDate, onChange }) {
  const [open, setOpen] = useState(false);
  const [viewDate, setViewDate] = useState(() => new Date(startDate));
  const [draftStart, setDraftStart] = useState(startDate);
  const [draftEnd, setDraftEnd] = useState(endDate);
  const rootRef = useRef(null);

  useEffect(() => {
    setDraftStart(startDate);
    setDraftEnd(endDate);
  }, [startDate, endDate]);

  useEffect(() => {
    const handleClick = (event) => {
      if (!rootRef.current?.contains(event.target)) setOpen(false);
    };
    document.addEventListener('click', handleClick);
    return () => document.removeEventListener('click', handleClick);
  }, []);

  const handleDayClick = (day) => {
    const selected = formatDateLocal(new Date(viewDate.getFullYear(), viewDate.getMonth(), day));
    if (!draftStart || (draftStart && draftEnd)) {
      setDraftStart(selected);
      setDraftEnd(null);
      return;
    }

    if (selected < draftStart) {
      setDraftEnd(draftStart);
      setDraftStart(selected);
    } else {
      setDraftEnd(selected);
    }
  };

  const applyRange = () => {
    if (draftStart && draftEnd) {
      onChange({ startDate: draftStart, endDate: draftEnd });
      setOpen(false);
    }
  };

  const displayText = formatDisplayRange(startDate, endDate);

  const cells = buildMonthGrid(viewDate.getFullYear(), viewDate.getMonth());

  return (
    <div ref={rootRef} style={{ position: 'relative' }}>
      <div className="date-range-picker" id="date-range-picker" onClick={() => setOpen((v) => !v)}>
        <i className="fas fa-calendar-alt" />
        <span id="date-range-display">{displayText}</span>
      </div>

      {open && (
        <div className="calendar-popup" style={{ display: 'block', position: 'absolute', zIndex: 20 }}>
          <div className="calendar-header">
            <button type="button" className="calendar-nav-btn" onClick={() => setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1))}>
              <i className="fas fa-chevron-left" />
            </button>
            <div className="calendar-month-year">
              {viewDate.getFullYear()}年{viewDate.getMonth() + 1}月
            </div>
            <button type="button" className="calendar-nav-btn" onClick={() => setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1))}>
              <i className="fas fa-chevron-right" />
            </button>
          </div>
          <div className="calendar-weekdays">
            {['日', '一', '二', '三', '四', '五', '六'].map((day) => (
              <div key={day} className="calendar-weekday">
                {day}
              </div>
            ))}
          </div>
          <div className="calendar-days">
            {cells.map((day, index) => {
              if (!day) return <div key={`empty-${index}`} className="calendar-day empty" />;
              const value = formatDateLocal(new Date(viewDate.getFullYear(), viewDate.getMonth(), day));
              const selected = value === draftStart || value === draftEnd;
              const inRange = draftStart && draftEnd && value >= draftStart && value <= draftEnd;
              return (
                <button
                  key={value}
                  type="button"
                  className={`calendar-day${selected ? ' selected' : ''}${inRange ? ' in-range' : ''}`}
                  onClick={() => handleDayClick(day)}
                >
                  {day}
                </button>
              );
            })}
          </div>
          <div style={{ padding: '12px', display: 'flex', justifyContent: 'flex-end' }}>
            <button type="button" className="btn btn-secondary" onClick={applyRange}>
              应用
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
