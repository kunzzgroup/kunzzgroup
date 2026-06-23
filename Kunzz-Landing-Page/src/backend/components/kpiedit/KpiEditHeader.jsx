import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../../config.js';
import { REPORT_LABELS } from '../../config/kpiEditConfig.js';

export default function KpiEditHeader({ config, restaurant, onRestaurantChange }) {
  const [reportOpen, setReportOpen] = useState(false);
  const [numberOpen, setNumberOpen] = useState(false);
  const rootRef = useRef(null);
  const backendBase = getBackendBase();

  useEffect(() => {
    const handleClick = (event) => {
      if (!rootRef.current?.contains(event.target)) {
        setReportOpen(false);
        setNumberOpen(false);
      }
    };
    document.addEventListener('click', handleClick);
    return () => document.removeEventListener('click', handleClick);
  }, []);

  if (!config) return null;

  const restaurantInfo = config.restaurantConfig[restaurant] || { name: 'J1', number: 1 };
  const prefix = restaurantInfo.name.charAt(0);

  return (
    <div className="header">
      <div>
        <h1>TOKYO JAPANESE CUISINE 数据后台</h1>
      </div>
      <div className="controls" ref={rootRef}>
        {config.reportDropdownEnabled ? (
          <div className="report-type-selector">
            <button type="button" className="report-type-btn" onClick={() => setReportOpen((v) => !v)}>
              <i className="fas fa-chart-bar" />
              {REPORT_LABELS.kpi}
              <i className="fas fa-chevron-down" />
            </button>
            {reportOpen && (
              <div className="report-dropdown-menu show" id="report-type-dropdown">
                {config.availableReportTypes.includes('kpi') && (
                  <a href={`${backendBase}/kpiedit-v2`} className="report-dropdown-item">
                    <i className="fas fa-chart-line" /> {REPORT_LABELS.kpi}
                  </a>
                )}
                {config.availableReportTypes.includes('cost') && (
                  <a href={`${backendBase}/costedit`} className="report-dropdown-item">
                    <i className="fas fa-chart-pie" /> {REPORT_LABELS.cost}
                  </a>
                )}
              </div>
            )}
          </div>
        ) : (
          <div className="report-type-selector report-type-selector--disabled">
            <button type="button" className="report-type-btn" style={{ cursor: 'default' }}>
              <i className="fas fa-chart-bar" />
              {REPORT_LABELS.kpi}
            </button>
          </div>
        )}

        <div className="restaurant-selector">
          <div className="restaurant-prefix">{prefix}</div>
          <div className="number-dropdown">
            <button
              type="button"
              className="number-btn dropdown-toggle"
              style={config.restaurantDropdownEnabled ? undefined : { cursor: 'default' }}
              onClick={() => config.restaurantDropdownEnabled && setNumberOpen((v) => !v)}
            >
              {restaurantInfo.number}
              {config.restaurantDropdownEnabled && <i className="fas fa-chevron-down" />}
            </button>
            {numberOpen && config.restaurantDropdownEnabled && (
              <div className="number-dropdown-menu show" id="number-dropdown">
                <div className="number-grid">
                  {config.availableRestaurants.map((key) => (
                    <button
                      key={key}
                      type="button"
                      className={`number-item${restaurant === key ? ' selected' : ''}`}
                      onClick={() => {
                        onRestaurantChange(key);
                        setNumberOpen(false);
                      }}
                    >
                      {config.restaurantConfig[key]?.number}
                    </button>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
