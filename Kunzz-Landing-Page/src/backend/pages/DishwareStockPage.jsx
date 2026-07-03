import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { useSearchParams } from 'react-router-dom';

import BackendLayout from '../components/layout/BackendLayout.jsx';
import { getBackendBase } from '../../config.js';
import {
  fetchDishwareStockFragment,
  normalizeDishwareTab,
  useDishwareStockLegacyBoot,
} from '../hooks/useDishwareStockLegacyBoot.js';
import { mountLegacySmartSearch, unmountLegacySmartSearch } from '../utils/mountLegacySmartSearch.jsx';

const SEARCH_PLACEHOLDERS = {
  stock: '搜索碗碟名称、编号或分类...',
  j1: '搜索破损记录...',
  j2: '搜索破损记录...',
  j3: '搜索破损记录...',
  transfer: '搜索转卖记录...',
};

export default function DishwareStockPage() {
  const [searchParams] = useSearchParams();
  const tab = normalizeDishwareTab(searchParams.get('tab'));
  const [markup, setMarkup] = useState('');
  const [markupError, setMarkupError] = useState(null);
  const [markupReady, setMarkupReady] = useState(false);
  const [searchReady, setSearchReady] = useState(false);
  const contentRef = useRef(null);
  const mountedMarkupRef = useRef('');
  const { loading: bootLoading, ready: bootReady, error: bootError } = useDishwareStockLegacyBoot(
    markupReady,
    tab,
  );

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
      setSearchReady(false);
      try {
        const html = await fetchDishwareStockFragment();
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

  useLayoutEffect(() => {
    if (!bootReady || !markup) {
      setSearchReady(false);
      return undefined;
    }

    const container = contentRef.current;
    if (!container) {
      setSearchReady(false);
      return undefined;
    }

    const host = container.querySelector('.header-search');
    if (!host) {
      setSearchReady(false);
      return undefined;
    }

    mountLegacySmartSearch(host, {
      id: 'unified-filter',
      placeholder: SEARCH_PLACEHOLDERS[tab] || SEARCH_PLACEHOLDERS.stock,
      onChange: (value) => {
        if (typeof window.refreshDishwareSearch === 'function') {
          window.refreshDishwareSearch(value);
        }
      },
    });

    if (typeof window.finalizeDishwareStockDom === 'function') {
      window.finalizeDishwareStockDom(container);
    }

    setSearchReady(true);

    return () => {
      unmountLegacySmartSearch(host);
      setSearchReady(false);
    };
  }, [markup, bootReady, tab]);

  useEffect(() => {
    return () => {
      if (typeof window.cleanupDishwareModalsFromBody === 'function') {
        window.cleanupDishwareModalsFromBody();
      }
    };
  }, []);

  const showLoading = !markup && !markupError;
  const showBootSpinner = markup && bootLoading;

  return (
    <BackendLayout
      stylesheet="dishware_stock.css"
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
          <div ref={contentRef} data-dishware-content-root />
          {showBootSpinner && (
            <div className="container">
              <p style={{ padding: '0 24px 24px', color: '#6b7280' }}>正在初始化碗碟库存数据...</p>
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
