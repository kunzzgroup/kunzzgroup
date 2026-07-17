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

export function usePriceLegacyBoot(markupReady) {
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

        await loadScript(`${backendBase}/js/price.js?v=${version}`, 'price-js');

        if (cancelled) return;

        if (bootedRef.current && typeof window.reinitPrice === 'function') {
          await window.reinitPrice();
        } else if (typeof window.bootPrice === 'function') {
          await window.bootPrice();
          bootedRef.current = true;
        } else if (typeof window.reinitPrice === 'function') {
          await window.reinitPrice();
          bootedRef.current = true;
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载价格对比页面失败');
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

export async function fetchPriceFragment() {
  const backendBase = getBackendBase();
  return fetchBackendFragment(`${backendBase}/price_fragment.php`);
}
