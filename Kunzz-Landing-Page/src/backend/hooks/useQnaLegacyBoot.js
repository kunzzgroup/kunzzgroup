import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../config.js';

function loadScript(src, id) {
  const existing = document.getElementById(id);
  if (existing?.src === src) {
    return Promise.resolve();
  }
  if (existing) existing.remove();

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.id = id;
    script.src = src;
    script.async = false;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.body.appendChild(script);
  });
}

export function executeScripts(container) {
  if (!container) return;

  container.querySelectorAll('script').forEach((oldScript) => {
    const script = document.createElement('script');
    Array.from(oldScript.attributes).forEach((attr) => {
      script.setAttribute(attr.name, attr.value);
    });
    script.textContent = oldScript.textContent;
    oldScript.replaceWith(script);
  });
}

export function useQnaLegacyBoot(markupReady, contentRef) {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!markupReady) return undefined;

    let cancelled = false;

    async function boot() {
      setLoading(true);
      setError(null);

      try {
        executeScripts(contentRef.current);
        const backendBase = getBackendBase();
        const version = Date.now();

        await loadScript(`${backendBase}/js/qna.js?v=${version}`, 'qna-js');

        if (cancelled) return;

        if (typeof window.bootQna === 'function') {
          await window.bootQna();
        } else if (typeof window.reinitQna === 'function') {
          await window.reinitQna();
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载问卷页面失败');
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
  }, [markupReady, contentRef]);

  return { loading, error };
}

export async function fetchQnaFragment() {
  const backendBase = getBackendBase();
  const response = await fetch(`${backendBase}/qna_fragment.php`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
