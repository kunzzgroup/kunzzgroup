import { useEffect, useRef, useState } from 'react';

import { getBackendBase } from '../../../config.js';

function loadScript(src, id) {
  if (document.getElementById(id)) return Promise.resolve();
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.id = id;
    script.src = src;
    script.onload = resolve;
    script.onerror = reject;
    document.body.appendChild(script);
  });
}

export default function PhpSidebar() {
  const [html, setHtml] = useState('');
  const containerRef = useRef(null);

  useEffect(() => {
    const backendBase = getBackendBase();

    fetch(`${backendBase}/sidebar_fragment.php`, { credentials: 'include' })
      .then((response) => response.text())
      .then(setHtml)
      .catch(() => setHtml(''));

    const stylesheets = [
      `${backendBase}/css/sidebar.css`,
      `${backendBase}/css/toast.css`,
      `${backendBase}/css/smartSearch.css`,
    ];

    const links = stylesheets.map((href, index) => {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = `${href}?v=${Date.now()}`;
      link.id = `backend-sidebar-style-${index}`;
      document.head.appendChild(link);
      return link;
    });

    return () => {
      links.forEach((link) => link.remove());
    };
  }, []);

  useEffect(() => {
    if (!html || !containerRef.current) return undefined;

    containerRef.current.innerHTML = html;

    const backendBase = getBackendBase();
    const version = Date.now();

    Promise.all([
      loadScript(`${backendBase}/js/toast.js?v=${version}`, 'backend-toast-js'),
      loadScript(`${backendBase}/js/smartSearch.js?v=${version}`, 'backend-smart-search-js'),
      loadScript(`${backendBase}/js/sidebar.js?v=${version}`, 'backend-sidebar-js'),
    ]).catch(() => {});

    return undefined;
  }, [html]);

  return <div ref={containerRef} />;
}
