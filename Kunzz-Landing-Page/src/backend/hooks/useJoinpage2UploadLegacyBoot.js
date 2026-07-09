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

export function useJoinpage2UploadLegacyBoot(markupReady) {
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

        await loadScript(`${backendBase}/js/joinpage2upload.js?v=${version}`, 'joinpage2upload-js');

        if (cancelled) return;

        if (typeof window.bootJoinpage2Upload === 'function') {
          window.bootJoinpage2Upload();
          bootedRef.current = true;
        } else if (typeof window.reinitJoinpage2Upload === 'function') {
          window.reinitJoinpage2Upload();
          bootedRef.current = true;
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载足迹照片管理页面失败');
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

export async function fetchJoinpage2UploadFragment(queryString = '') {
  const backendBase = getBackendBase();
  const query = queryString
    ? (queryString.startsWith('?') ? queryString : `?${queryString}`)
    : '';
  const response = await fetch(`${backendBase}/joinpage2upload_fragment.php${query}`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
