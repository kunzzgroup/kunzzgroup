import { useEffect, useRef, useState } from 'react';

import { BRANCH_L1_OPTIONS, BRANCH_L2_OPTIONS } from '../../config/generatecodeConstants.js';

export default function BranchFilter({ branchL1, branchL2, onChangeL1, onChangeL2 }) {
  const [openL1, setOpenL1] = useState(false);
  const [openL2, setOpenL2] = useState(false);
  const wrapRef = useRef(null);

  useEffect(() => {
    const handleClick = (event) => {
      if (wrapRef.current && !wrapRef.current.contains(event.target)) {
        setOpenL1(false);
        setOpenL2(false);
      }
    };
    document.addEventListener('click', handleClick);
    return () => document.removeEventListener('click', handleClick);
  }, []);

  const l1Label = BRANCH_L1_OPTIONS.find((item) => item.value === branchL1)?.label || '全部';
  const l2Label = BRANCH_L2_OPTIONS.find((item) => item.value === branchL2)?.label || '-';
  const showL2 = branchL1 === 'branch';

  return (
    <div className="branch-filter-wrap" ref={wrapRef}>
      <div style={{ position: 'relative' }}>
        <button type="button" className="branch-filter-btn" onClick={() => setOpenL1((prev) => !prev)}>
          <span>{l1Label}</span>
          <i className="fas fa-chevron-down" style={{ fontSize: 10, color: '#9ca3af' }} />
        </button>
        {openL1 ? (
          <div className="branch-filter-dropdown" style={{ display: 'block' }}>
            {BRANCH_L1_OPTIONS.map((option) => (
              <div
                key={option.value}
                className={`bl1-item ${branchL1 === option.value ? 'active' : ''}`}
                onClick={() => {
                  onChangeL1(option.value);
                  setOpenL1(false);
                  setOpenL2(false);
                }}
              >
                {option.label}
              </div>
            ))}
          </div>
        ) : null}
      </div>

      {showL2 ? (
        <div style={{ position: 'relative' }}>
          <button type="button" className="branch-filter-btn" onClick={() => setOpenL2((prev) => !prev)}>
            <span>{l2Label}</span>
            <i className="fas fa-chevron-down" style={{ fontSize: 10, color: '#9ca3af' }} />
          </button>
          {openL2 ? (
            <div className="branch-filter-dropdown" style={{ display: 'block' }}>
              {BRANCH_L2_OPTIONS.map((option) => (
                <div
                  key={option.value}
                  className={`bl1-item ${branchL2 === option.value ? 'active' : ''}`}
                  onClick={() => {
                    onChangeL2(option.value);
                    setOpenL2(false);
                  }}
                >
                  {option.label}
                </div>
              ))}
            </div>
          ) : null}
        </div>
      ) : null}
    </div>
  );
}
