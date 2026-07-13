import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../config.js';

function loadStylesheet(href, id) {
  const existing = document.getElementById(id);
  if (existing?.href === href) {
    return Promise.resolve();
  }
  if (existing) existing.remove();

  return new Promise((resolve, reject) => {
    const link = document.createElement('link');
    link.id = id;
    link.rel = 'stylesheet';
    link.href = href;
    link.onload = () => resolve();
    link.onerror = () => reject(new Error(`Failed to load ${href}`));
    document.head.appendChild(link);
  });
}

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

export function useCorporateBlueprintLegacyBoot(markupReady, contentRef) {
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
        executeScripts(contentRef.current);
        const backendBase = getBackendBase();
        const version = Date.now();

        await loadStylesheet(
          'https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/css/jquery.orgchart.min.css',
          'corporate-blueprint-orgchart-css',
        );
        await loadScript('https://code.jquery.com/jquery-3.6.0.min.js', 'corporate-blueprint-jquery');
        await loadScript(
          'https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/js/jquery.orgchart.min.js',
          'corporate-blueprint-orgchart-js',
        );
        await loadScript(
          `${backendBase}/js/corporate_blueprint.js?v=${version}`,
          'corporate-blueprint-js',
        );

        if (cancelled) return;

        if (typeof window.bootCorporateBlueprint === 'function') {
          window.bootCorporateBlueprint();
          bootedRef.current = true;
        } else if (typeof window.reinitCorporateBlueprint === 'function') {
          window.reinitCorporateBlueprint();
          bootedRef.current = true;
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载企业蓝图页面失败');
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

export async function fetchCorporateBlueprintFragment(queryString = '') {
  const backendBase = getBackendBase();
  const query = queryString
    ? (queryString.startsWith('?') ? queryString : `?${queryString}`)
    : '';
  const response = await fetch(`${backendBase}/corporate_blueprint_fragment.php${query}`, {
    credentials: 'include',
    cache: 'no-store',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  return response.text();
}
