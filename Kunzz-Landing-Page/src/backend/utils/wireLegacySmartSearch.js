import { getBackendBase } from '../../config.js';

function loadScript(src, id) {
  const existing = document.getElementById(id);
  if (existing) {
    existing.remove();
  }

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.id = id;
    script.src = src;
    script.async = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.body.appendChild(script);
  });
}

export async function ensureLegacySmartSearchScript() {
  if (typeof window.wireSmartSearchWrappers === 'function') {
    return;
  }

  const backendBase = getBackendBase();
  await loadScript(
    `${backendBase}/js/smartSearch.js?v=${Date.now()}`,
    'stockedit-smart-search-js',
  );
}

export function wireLegacySmartSearch(root = document) {
  if (typeof window.wireSmartSearchWrappers === 'function') {
    window.wireSmartSearchWrappers(root);
    return true;
  }

  return false;
}
