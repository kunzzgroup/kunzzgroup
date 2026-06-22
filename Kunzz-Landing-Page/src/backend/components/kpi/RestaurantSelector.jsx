import { useEffect, useRef, useState } from 'react';

export default function RestaurantSelector({ label, onSelect }) {
  const [open, setOpen] = useState(false);
  const [letter, setLetter] = useState(null);
  const rootRef = useRef(null);

  useEffect(() => {
    const handleClick = (event) => {
      if (!rootRef.current?.contains(event.target)) {
        setOpen(false);
      }
    };
    document.addEventListener('click', handleClick);
    return () => document.removeEventListener('click', handleClick);
  }, []);

  const handleLetterSelect = (nextLetter) => {
    setLetter(nextLetter);
  };

  const handleRestaurantSelect = (number) => {
    onSelect(letter, number);
    setOpen(false);
  };

  return (
    <div className="restaurant-selector" ref={rootRef}>
      <button type="button" className="restaurant-btn dropdown-toggle" onClick={() => setOpen((v) => !v)}>
        {label} <i className="fas fa-chevron-down" />
      </button>
      {open && (
        <div className="restaurant-dropdown-menu show">
          <div className="letter-selection">
            <div className="section-title">选择州属</div>
            <div className="letter-grid">
              <button type="button" className={`letter-item${letter === 'J' ? ' selected' : ''}`} onClick={() => handleLetterSelect('J')}>
                J
              </button>
            </div>
          </div>
          {letter && (
            <div className="number-selection" style={{ visibility: 'visible', opacity: 1 }}>
              <div className="section-title">选择{letter}分店</div>
              <div className="number-grid">
                <button type="button" className="number-item" onClick={() => handleRestaurantSelect('1')}>
                  1
                </button>
                <button type="button" className="number-item" onClick={() => handleRestaurantSelect('2')}>
                  2
                </button>
                <button type="button" className="number-item" onClick={() => handleRestaurantSelect('3')}>
                  3
                </button>
                <button type="button" className="number-item total-option" onClick={() => handleRestaurantSelect('total')}>
                  总
                </button>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
