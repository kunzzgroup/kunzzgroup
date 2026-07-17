import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../config.js';
import { fetchBackendFragment } from '../utils/fetchBackendFragment.js';

function loadScript(src, id) {
  const existing = document.getElementById(id);
  if (existing) {
    if (existing.src === src) {
      return Promise.resolve();
    }
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

export function useStockSotLegacyBoot(markupReady) {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const bootedRef = useRef(false);

  useEffect(() => {
    if (!markupReady) return undefined;

    let cancelled = false;

    async function boot() {
      setLoading(true);
      setError(null);

      try {
        const backendBase = getBackendBase();
        const version = Date.now();

        await loadScript(
          `${backendBase}/js/stocksot.js?v=${version}`,
          'stocksot-js',
        );

        if (cancelled) return;

        if (typeof window.bootStockSot === 'function') {
          await window.bootStockSot();
        } else if (typeof window.reinitStockSot === 'function') {
          await window.reinitStockSot();
        }

        bootedRef.current = true;
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载货品异常页面失败');
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

export async function fetchStockSotFragment(system) {
  const backendBase = getBackendBase();
  const query = system ? `?system=${encodeURIComponent(system)}` : '';
  return fetchBackendFragment(`${backendBase}/stocksot_fragment.php${query}`);
}
