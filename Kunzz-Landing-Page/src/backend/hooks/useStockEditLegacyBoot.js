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

export function useStockEditLegacyBoot(markupReady) {
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

        if (!window.PDFLib) {
          await loadScript(
            'https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js',
            'stockedit-pdf-lib',
          );
        }

        await loadScript(
          `${backendBase}/js/stockeditall.js?v=${version}`,
          'stockeditall-js',
        );

        if (cancelled) return;

        if (typeof window.bootStockEditAll === 'function') {
          await window.bootStockEditAll();
        } else if (typeof window.reinitStockEditAll === 'function') {
          await window.reinitStockEditAll();
        }

        bootedRef.current = true;
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载进出货页面失败');
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

export async function fetchStockEditFragment(system) {
  const backendBase = getBackendBase();
  const query = system ? `?system=${encodeURIComponent(system)}` : '';
  const response = await fetch(`${backendBase}/stockeditall_fragment.php${query}`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
