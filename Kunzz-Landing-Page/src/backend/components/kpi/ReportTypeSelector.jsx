import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../../config.js';

export default function ReportTypeSelector() {
  const [open, setOpen] = useState(false);
  const rootRef = useRef(null);
  const backendBase = getBackendBase();

  useEffect(() => {
    const handleClick = (event) => {
      if (!rootRef.current?.contains(event.target)) setOpen(false);
    };
    document.addEventListener('click', handleClick);
    return () => document.removeEventListener('click', handleClick);
  }, []);

  return (
    <div className="report-type-selector" ref={rootRef} onClick={() => setOpen((value) => !value)}>
      <button type="button" className="report-type-btn">
        <i className="fas fa-chart-bar" />
        KPI 报表
        <i className="fas fa-chevron-down" />
      </button>
      {open && (
        <div className="report-dropdown-menu show" id="report-type-dropdown">
          <a href={`${backendBase}/kpi-v2`} className="report-dropdown-item">
            <i className="fas fa-chart-line" /> KPI 报表
          </a>
          <a href={`${backendBase}/cost`} className="report-dropdown-item">
            <i className="fas fa-chart-pie" /> 成本报表
          </a>
        </div>
      )}
    </div>
  );
}
