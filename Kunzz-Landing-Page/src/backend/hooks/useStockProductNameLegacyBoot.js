import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../config.js';

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

export function useStockProductNameLegacyBoot(markupReady) {
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
          `${backendBase}/js/stockproductname.js?v=${version}`,
          'stockproductname-js',
        );

        if (cancelled) return;

        if (typeof window.bootStockProductName === 'function') {
          await window.bootStockProductName();
        } else if (typeof window.reinitStockProductName === 'function') {
          await window.reinitStockProductName();
        }

        bootedRef.current = true;
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载货品种类页面失败');
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

export async function fetchStockProductNameFragment(system) {
  const backendBase = getBackendBase();
  const query = system ? `?system=${encodeURIComponent(system)}` : '';
  const response = await fetch(`${backendBase}/stockproductname_fragment.php${query}`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
