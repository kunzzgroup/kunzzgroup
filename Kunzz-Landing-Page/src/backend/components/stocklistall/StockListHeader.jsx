import { useEffect, useRef, useState } from 'react';

import { PAGE_TITLES, SYSTEM_NAMES, VIEW_NAMES } from '../../config/stockListConstants.js';

export default function StockListHeader({
  system,
  systemOptions,
  viewOptions,
  onSwitchSystem,
  onSwitchView,
}) {
  const [systemOpen, setSystemOpen] = useState(false);
  const [viewOpen, setViewOpen] = useState(false);
  const controlsRef = useRef(null);

  useEffect(() => {
    const onClickOutside = (event) => {
      if (!controlsRef.current?.contains(event.target)) {
        setSystemOpen(false);
        setViewOpen(false);
      }
    };

    document.addEventListener('click', onClickOutside);
    return () => document.removeEventListener('click', onClickOutside);
  }, []);

  return (
    <div className="header">
      <div>
        <h1 id="page-title">{PAGE_TITLES[system] || `总库存 - ${system.toUpperCase()}`}</h1>
      </div>
      <div className="controls" ref={controlsRef}>
        <div className="view-selector">
          <button
            type="button"
            className="selector-button"
            onClick={() => {
              setViewOpen((open) => !open);
              setSystemOpen(false);
            }}
          >
            <span id="current-view">{VIEW_NAMES.list}</span>
            <i className="fas fa-chevron-down" />
          </button>
          <div className={`selector-dropdown${viewOpen ? ' show' : ''}`} id="view-selector-dropdown">
            {viewOptions.map((opt) => (
              <div
                key={opt.value}
                className={`dropdown-item${opt.value === 'list' ? ' active' : ''}`}
                role="button"
                tabIndex={0}
                onClick={() => {
                  setViewOpen(false);
                  onSwitchView(opt.value);
                }}
                onKeyDown={(e) => {
                  if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setViewOpen(false);
                    onSwitchView(opt.value);
                  }
                }}
              >
                {opt.label}
              </div>
            ))}
          </div>
        </div>

        <div className="system-selector">
          <button
            type="button"
            className="selector-button"
            onClick={() => {
              setSystemOpen((open) => !open);
              setViewOpen(false);
            }}
          >
            <span id="current-system">{SYSTEM_NAMES[system] || system.toUpperCase()}</span>
            <i className="fas fa-chevron-down" />
          </button>
          <div className={`selector-dropdown${systemOpen ? ' show' : ''}`} id="selector-dropdown">
            {systemOptions.map((opt) => (
              <div
                key={opt.value}
                className={`dropdown-item${opt.value === system ? ' active' : ''}`}
                role="button"
                tabIndex={0}
                data-system-value={opt.value}
                onClick={() => {
                  setSystemOpen(false);
                  onSwitchSystem(opt.value);
                }}
                onKeyDown={(e) => {
                  if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setSystemOpen(false);
                    onSwitchSystem(opt.value);
                  }
                }}
              >
                {opt.label}
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
