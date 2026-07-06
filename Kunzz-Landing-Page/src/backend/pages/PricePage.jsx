import { useEffect, useLayoutEffect, useRef, useState } from 'react';

import BackendLayout from '../components/layout/BackendLayout.jsx';
import { getBackendBase } from '../../config.js';
import {
  fetchPriceFragment,
  usePriceLegacyBoot,
} from '../hooks/usePriceLegacyBoot.js';
import { mountLegacySmartSearch, unmountLegacySmartSearch } from '../utils/mountLegacySmartSearch.jsx';

export default function PricePage() {
  const [markup, setMarkup] = useState('');
  const [markupError, setMarkupError] = useState(null);
  const [searchReady, setSearchReady] = useState(false);
  const contentRef = useRef(null);
  const mountedMarkupRef = useRef('');
  const { loading: bootLoading, error: bootError } = usePriceLegacyBoot(searchReady);

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
      setSearchReady(false);
      try {
        const html = await fetchPriceFragment();
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
  }, []);

  useLayoutEffect(() => {
    const container = contentRef.current;
    if (!container || !markup) {
      setSearchReady(false);
      return undefined;
    }

    if (mountedMarkupRef.current !== markup) {
      container.innerHTML = markup;
      mountedMarkupRef.current = markup;
    }

    const host = container.querySelector('.header-search');
    if (!host) {
      setSearchReady(false);
      return undefined;
    }

    mountLegacySmartSearch(host, {
      id: 'search-input',
      placeholder: '输入货品名称',
      onChange: () => {
        if (typeof window.refreshPriceSearch === 'function') {
          window.refreshPriceSearch();
        }
      },
    });

    setSearchReady(true);

    return () => {
      unmountLegacySmartSearch(host);
      setSearchReady(false);
    };
  }, [markup]);

  const showLoading = !markup && !markupError;
  const showBootSpinner = markup && bootLoading;

  return (
    <BackendLayout
      stylesheet="price.css"
      extraStylesheets={['smartSearch.css']}
    >
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
              <p style={{ padding: '0 24px 24px', color: '#6b7280' }}>正在初始化价格对比数据...</p>
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
