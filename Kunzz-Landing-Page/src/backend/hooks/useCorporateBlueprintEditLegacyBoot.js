import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../config.js';
import { fetchBackendFragment } from '../utils/fetchBackendFragment.js';

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
    script.async = true;
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

export function useCorporateBlueprintEditLegacyBoot(markupReady, contentRef) {
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

        await loadScript(
          `${backendBase}/js/corporate_blueprint_edit.js?v=${version}`,
          'corporate-blueprint-edit-js',
        );

        if (cancelled) return;

        if (typeof window.bootCorporateBlueprintEdit === 'function') {
          window.bootCorporateBlueprintEdit();
        } else if (typeof window.reinitCorporateBlueprintEdit === 'function') {
          window.reinitCorporateBlueprintEdit();
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载企业蓝图管理页面失败');
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

export async function fetchCorporateBlueprintEditFragment(queryString = '') {
  const backendBase = getBackendBase();
  const query = queryString
    ? (queryString.startsWith('?') ? queryString : `?${queryString}`)
    : '';
  return fetchBackendFragment(`${backendBase}/corporate_blueprint_edit_fragment.php${query}`);
}
