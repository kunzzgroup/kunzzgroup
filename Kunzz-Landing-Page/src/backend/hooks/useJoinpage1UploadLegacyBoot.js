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

export function useJoinpage1UploadLegacyBoot(markupReady) {
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

        await loadScript(`${backendBase}/js/toast.js?v=${version}`, 'joinpage1-toast-js');
        await loadScript(`${backendBase}/js/joinpage1upload.js?v=${version}`, 'joinpage1upload-js');

        if (cancelled) return;

        if (typeof window.bootJoinpage1Upload === 'function') {
          window.bootJoinpage1Upload();
          bootedRef.current = true;
        } else if (typeof window.reinitJoinpage1Upload === 'function') {
          window.reinitJoinpage1Upload();
          bootedRef.current = true;
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载加入我们媒体页面失败');
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

export async function fetchJoinpage1UploadFragment(queryString = '') {
  const backendBase = getBackendBase();
  const query = queryString
    ? (queryString.startsWith('?') ? queryString : `?${queryString}`)
    : '';
  return fetchBackendFragment(`${backendBase}/joinpage1upload_fragment.php${query}`);
}
