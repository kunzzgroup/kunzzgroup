import { useEffect, useRef, useState } from 'react';

import { BRANCH_OPTIONS } from '../../config/generatecodeConstants.js';
import { getBranchMultiSelectLabel } from '../../utils/generatecodeCalculations.js';

export default function BranchMultiSelect({ selected, onChange, id = 'add-branch-select' }) {
  const [open, setOpen] = useState(false);
  const wrapRef = useRef(null);
  const label = getBranchMultiSelectLabel(selected);

  useEffect(() => {
    const handlePointerDown = (event) => {
      if (wrapRef.current && !wrapRef.current.contains(event.target)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handlePointerDown);
    return () => document.removeEventListener('mousedown', handlePointerDown);
  }, []);

  const toggleOpen = (event) => {
    event.stopPropagation();
    setOpen((prev) => !prev);
  };

  const toggleBranch = (value) => {
    if (selected.includes(value)) {
      onChange(selected.filter((item) => item !== value));
    } else {
      onChange([...selected, value]);
    }
  };

  return (
    <div
      className={`custom-multi-select${open ? ' active' : ''}`}
      id={id}
      ref={wrapRef}
    >
      <div className="select-header" onClick={toggleOpen}>
        <span className="selected-text" style={{ color: label.muted ? '#999' : '#111827' }}>
          {label.text}
        </span>
        <i className="fas fa-chevron-down" />
      </div>
      <div className="select-options" onMouseDown={(event) => event.stopPropagation()}>
        {BRANCH_OPTIONS.map((option) => (
          <label className="checkbox-item" key={option.value}>
            <input
              type="checkbox"
              checked={selected.includes(option.value)}
              onChange={() => toggleBranch(option.value)}
            />
            {option.label}
          </label>
        ))}
      </div>
    </div>
  );
}
