import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../config.js';
import { fetchBackendFragment } from '../utils/fetchBackendFragment.js';

function loadScript(src, id) {
  const existing = document.getElementById(id);
  if (existing?.src === src) {
    return Promise.resolve();
  }
  if (existing) existing.remove();

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.id = id;
    script.src = src;
    script.async = false;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.body.appendChild(script);
  });
}

function loadStylesheet(href, id) {
  if (document.getElementById(id)) {
    return;
  }
  const link = document.createElement('link');
  link.id = id;
  link.rel = 'stylesheet';
  link.href = href;
  document.head.appendChild(link);
}

export function useHireLegacyBoot(markupReady) {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!markupReady) return undefined;

    let cancelled = false;

    async function boot() {
      setLoading(true);
      setError(null);

      try {
        const backendBase = getBackendBase();
        const version = Date.now();

        loadStylesheet(
          `https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css`,
          'hire-flatpickr-css',
        );

        await loadScript(
          'https://cdn.jsdelivr.net/npm/flatpickr',
          'hire-flatpickr-js',
        );
        await loadScript(
          'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/zh.js',
          'hire-flatpickr-zh-js',
        );
        await loadScript(`${backendBase}/js/hire.js?v=${version}`, 'hire-js');

        if (cancelled) return;

        if (typeof window.bootHire === 'function') {
          window.bootHire();
        } else if (typeof window.reinitHire === 'function') {
          window.reinitHire();
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载招聘列表页面失败');
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    boot();

    return () => {
      cancelled = true;
    };
  }, [markupReady]);

  return { loading, error };
}

export async function fetchHireFragment() {
  const backendBase = getBackendBase();
  return fetchBackendFragment(`${backendBase}/hire_fragment.php`);
}
