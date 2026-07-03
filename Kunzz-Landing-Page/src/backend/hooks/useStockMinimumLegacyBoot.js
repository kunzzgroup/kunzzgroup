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

export function useStockMinimumLegacyBoot(markupReady) {
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

        await loadScript(`${backendBase}/js/toast.js?v=${version}`, 'stockminimum-toast-js');
        await loadScript(`${backendBase}/js/stockminimum.js?v=${version}`, 'stockminimum-js');

        if (cancelled) return;

        if (typeof window.bootStockMinimum === 'function') {
          await window.bootStockMinimum();
        } else if (typeof window.reinitStockMinimum === 'function') {
          await window.reinitStockMinimum();
        }

        bootedRef.current = true;
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载最低库存页面失败');
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

export async function fetchStockMinimumFragment(system) {
  const backendBase = getBackendBase();
  const query = system ? `?system=${encodeURIComponent(system)}` : '';
  const response = await fetch(`${backendBase}/stockminimum_fragment.php${query}`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
