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

export function useAboutpage4UploadLegacyBoot(markupReady) {
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

        await loadScript(`${backendBase}/js/toast.js?v=${version}`, 'aboutpage4-toast-js');
        await loadScript(`${backendBase}/js/aboutpage4upload.js?v=${version}`, 'aboutpage4upload-js');

        if (cancelled) return;

        if (typeof window.bootAboutpage4Upload === 'function') {
          window.bootAboutpage4Upload();
          bootedRef.current = true;
        } else if (typeof window.reinitAboutpage4Upload === 'function') {
          window.reinitAboutpage4Upload();
          bootedRef.current = true;
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载发展历史管理页面失败');
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

export async function fetchAboutpage4UploadFragment(queryString = '') {
  const backendBase = getBackendBase();
  const query = queryString
    ? (queryString.startsWith('?') ? queryString : `?${queryString}`)
    : '';
  const response = await fetch(`${backendBase}/aboutpage4upload_fragment.php${query}`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
