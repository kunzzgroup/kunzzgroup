import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { useSearchParams } from 'react-router-dom';

import BackendLayout from '../components/layout/BackendLayout.jsx';
import { getBackendBase } from '../../config.js';
import {
  fetchCorporateBlueprintEditFragment,
  useCorporateBlueprintEditLegacyBoot,
} from '../hooks/useCorporateBlueprintEditLegacyBoot.js';

export default function CorporateBlueprintEditPage() {
  const [searchParams] = useSearchParams();
  const [markup, setMarkup] = useState('');
  const [markupError, setMarkupError] = useState(null);
  const [markupReady, setMarkupReady] = useState(false);
  const contentRef = useRef(null);
  const mountedMarkupRef = useRef('');
  const { loading: bootLoading, error: bootError } = useCorporateBlueprintEditLegacyBoot(
    markupReady,
    contentRef,
  );

  const fragmentQuery = searchParams.toString();

  useEffect(() => {
    const backendBase = getBackendBase();
    window.__KUNZZ_BACKEND_BASE__ = backendBase;

    const base = document.createElement('base');
    base.href = `${backendBase}/`;
    document.head.prepend(base);

    return () => {
      delete window.__KUNZZ_BACKEND_BASE__;
      base.remove();
    };
  }, []);

  useEffect(() => {
    let cancelled = false;

    async function loadMarkup() {
      setMarkupError(null);
      setMarkupReady(false);
      try {
        const html = await fetchCorporateBlueprintEditFragment(fragmentQuery);
        if (!cancelled) {
          setMarkup(html);
        }
      } catch (error) {
        if (!cancelled) {
          setMarkup('');
          setMarkupError(error.message || '加载页面内容失败');
        }
      }
    }

    loadMarkup();

    return () => {
      cancelled = true;
    };
  }, [fragmentQuery]);

  useLayoutEffect(() => {
    const container = contentRef.current;
    if (!container || !markup) {
      setMarkupReady(false);
      return undefined;
    }

    if (mountedMarkupRef.current !== markup) {
      container.innerHTML = markup;
      mountedMarkupRef.current = markup;
    }

    setMarkupReady(true);

    return () => {
      setMarkupReady(false);
    };
  }, [markup]);

  const showLoading = !markup && !markupError;
  const showBootSpinner = markup && bootLoading;

  return (
    <BackendLayout stylesheet="corporate_blueprint_edit.css">
      {showLoading && (
        <div className="container">
          <p style={{ padding: 24 }}>正在加载...</p>
        </div>
      )}

      {markupError && (
        <div className="container">
          <p style={{ padding: 24, color: '#b91c1c' }}>{markupError}</p>
        </div>
      )}

      {markup && (
        <>
          <div ref={contentRef} />
          {showBootSpinner && (
            <div className="container">
              <p style={{ padding: '0 24px 24px', color: '#6b7280' }}>正在初始化企业蓝图管理...</p>
            </div>
          )}
          {bootError && (
            <div className="container">
              <p style={{ padding: '0 24px 24px', color: '#b91c1c' }}>{bootError}</p>
            </div>
          )}
        </>
      )}
    </BackendLayout>
  );
}
