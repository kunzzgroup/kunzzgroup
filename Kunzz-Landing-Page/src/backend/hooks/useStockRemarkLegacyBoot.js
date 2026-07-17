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

export function useStockRemarkLegacyBoot(markupReady) {
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
          `${backendBase}/js/stockremark.js?v=${version}`,
          'stockremark-js',
        );

        if (cancelled) return;

        if (typeof window.bootStockRemark === 'function') {
          await window.bootStockRemark();
        } else if (typeof window.reinitStockRemark === 'function') {
          await window.reinitStockRemark();
        }

        bootedRef.current = true;
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载货品备注页面失败');
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

export async function fetchStockRemarkFragment(system) {
  const backendBase = getBackendBase();
  const query = system ? `?system=${encodeURIComponent(system)}` : '';
  return fetchBackendFragment(`${backendBase}/stockremark_fragment.php${query}`);
}
