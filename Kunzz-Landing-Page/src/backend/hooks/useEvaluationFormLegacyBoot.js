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
    script.async = false;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.body.appendChild(script);
  });
}

export function useEvaluationFormLegacyBoot(markupReady, contentRef) {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

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
          `${backendBase}/js/evaluation_form.js?v=${version}`,
          'evaluation-form-js',
        );

        if (cancelled) return;

        if (typeof window.bootEvaluationForm === 'function') {
          window.bootEvaluationForm();
        } else if (typeof window.reinitEvaluationForm === 'function') {
          window.reinitEvaluationForm();
        }
      } catch (bootError) {
        if (!cancelled) {
          setError(bootError.message || '加载考核表单页面失败');
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

export async function fetchEvaluationFormFragment() {
  const backendBase = getBackendBase();
  return fetchBackendFragment(`${backendBase}/evaluation_form_fragment.php`);
}
