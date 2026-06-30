import { useCallback, useEffect, useRef, useState } from 'react';

function useDebouncedCallback(callback, delay) {
  const timerRef = useRef(null);
  const callbackRef = useRef(callback);

  useEffect(() => {
    callbackRef.current = callback;
  }, [callback]);

  return useCallback((value) => {
    window.clearTimeout(timerRef.current);
    timerRef.current = window.setTimeout(() => {
      callbackRef.current(value);
    }, delay);
  }, [delay]);
}

export default function SmartSearchWrapper({
  id,
  placeholder,
  value,
  onChange,
  debounceMs = 300,
}) {
  const [expanded, setExpanded] = useState(false);
  const [inputValue, setInputValue] = useState(value);
  const wrapperRef = useRef(null);
  const inputRef = useRef(null);
  const debouncedChange = useDebouncedCallback(onChange, debounceMs);

  useEffect(() => {
    setInputValue(value);
  }, [value]);

  useEffect(() => {
    setExpanded(false);
    setInputValue(value);
  }, [id]);

  useEffect(() => {
    const onDocumentClick = (event) => {
      if (!wrapperRef.current?.contains(event.target)) {
        if (!inputValue.trim()) {
          setExpanded(false);
        }
      }
    };

    document.addEventListener('click', onDocumentClick);
    return () => document.removeEventListener('click', onDocumentClick);
  }, [inputValue]);

  const handleWrapperClick = () => {
    if (!expanded) {
      setExpanded(true);
      window.setTimeout(() => inputRef.current?.focus(), 50);
    }
  };

  const handleClear = (event) => {
    event.stopPropagation();
    setInputValue('');
    onChange('');
    debouncedChange('');
    inputRef.current?.focus();
  };

  const className = [
    'smartSearchWrapper',
    expanded ? 'expanded' : '',
    inputValue ? 'has-value' : '',
  ].filter(Boolean).join(' ');

  return (
    <div
      ref={wrapperRef}
      className={className}
      data-expanded={expanded ? '1' : ''}
      onClick={handleWrapperClick}
    >
      <span className="smartSearch-icon" aria-hidden="true">
        <i className="fas fa-search" />
      </span>
      <input
        ref={inputRef}
        type="text"
        id={id}
        className="smartSearch-input"
        placeholder={placeholder}
        value={inputValue}
        onClick={(event) => event.stopPropagation()}
        onChange={(event) => {
          const nextValue = event.target.value;
          setInputValue(nextValue);
          debouncedChange(nextValue);
        }}
      />
      <button
        type="button"
        className="smartSearch-clear"
        aria-label="清除搜索"
        onClick={handleClear}
      >
        <i className="fas fa-times" />
      </button>
    </div>
  );
}
