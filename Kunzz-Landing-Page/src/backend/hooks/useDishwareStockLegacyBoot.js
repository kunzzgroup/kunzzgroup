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

export function useDishwareStockLegacyBoot(markupReady) {
  const [loading, setLoading] = useState(true);
  const [ready, setReady] = useState(false);
  const [error, setError] = useState(null);
  const bootedRef = useRef(false);

  useEffect(() => {
    if (!markupReady) {
      setReady(false);
      return undefined;
    }

    let cancelled = false;

    async function boot() {
      setLoading(true);
      setReady(false);
      setError(null);

      try {
        const backendBase = getBackendBase();
        const version = Date.now();

        await loadScript(`${backendBase}/js/toast.js?v=${version}`, 'dishware-toast-js');
        await loadScript(
          `${backendBase}/js/dishware_stock.js?v=${version}`,
          'dishware-stock-js',
        );

        if (cancelled) return;

        const contentRoot = document.querySelector('[data-dishware-content-root]');

        if (typeof window.bootDishwareStock === 'function') {
          await window.bootDishwareStock(contentRoot);
        } else if (typeof window.reinitDishwareStock === 'function') {
          await window.reinitDishwareStock(contentRoot);
        }

        bootedRef.current = true;
        if (!cancelled) {
          setReady(true);
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载碗碟库存页面失败');
          setReady(false);
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
      setReady(false);
    };
  }, [markupReady]);

  return { loading, ready, error };
}

export async function fetchDishwareStockFragment() {
  const backendBase = getBackendBase();
  const response = await fetch(`${backendBase}/dishware_stock_fragment.php`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
