import { createRoot } from 'react-dom/client';

import SmartSearchWrapper from '../components/common/SmartSearchWrapper.jsx';

const roots = new WeakMap();

function resolveHost(hostOrSelector) {
  if (!hostOrSelector) {
    return null;
  }

  if (typeof hostOrSelector === 'string') {
    return document.querySelector(hostOrSelector);
  }

  return hostOrSelector;
}

function renderSearch(host, options) {
  const root = roots.get(host);
  const props = {
    id: options.id || 'unified-filter',
    placeholder: options.placeholder || '输入关键字搜索...',
    value: options.value ?? '',
    onChange: options.onChange ?? (() => {}),
    debounceMs: options.debounceMs ?? 300,
  };

  if (root) {
    root.render(<SmartSearchWrapper {...props} />);
    return;
  }

  const nextRoot = createRoot(host);
  roots.set(host, nextRoot);
  nextRoot.render(<SmartSearchWrapper {...props} />);
}

export function mountLegacySmartSearch(hostOrSelector, options = {}) {
  const host = resolveHost(hostOrSelector);
  if (!host) {
    return () => {};
  }

  const legacyWrapper = host.querySelector('.smartSearchWrapper');
  const placeholder =
    options.placeholder
    || legacyWrapper?.querySelector('.smartSearch-input')?.placeholder
    || '输入关键字搜索...';

  host.querySelector('.smartSearchWrapper')?.remove();
  host.dataset.smartSearchMounted = '1';

  renderSearch(host, { ...options, placeholder });

  return () => unmountLegacySmartSearch(host);
}

export function unmountLegacySmartSearch(hostOrSelector) {
  const host = resolveHost(hostOrSelector);
  if (!host) {
    return;
  }

  const root = roots.get(host);
  root?.unmount();
  roots.delete(host);
  delete host.dataset.smartSearchMounted;
  host.innerHTML = '';
}
