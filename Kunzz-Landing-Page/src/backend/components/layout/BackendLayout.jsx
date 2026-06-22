import { useEffect } from 'react';

import { getBackendBase } from '../../../config.js';
import PhpSidebar from './PhpSidebar.jsx';
import '../../styles/backend-layout.css';

export default function BackendLayout({ children, className = '' }) {
  useEffect(() => {
    document.body.classList.add('has-sidebar');

    const backendBase = getBackendBase();
    const version = Date.now();

    const kpiStylesheet = document.createElement('link');
    kpiStylesheet.rel = 'stylesheet';
    kpiStylesheet.href = `${backendBase}/css/kpi.css?v=${version}`;
    kpiStylesheet.id = 'backend-kpi-css';
    document.head.appendChild(kpiStylesheet);

    const icons = document.createElement('link');
    icons.rel = 'stylesheet';
    icons.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
    icons.id = 'backend-fa-css';
    if (!document.getElementById('backend-fa-css')) {
      document.head.appendChild(icons);
    }

    return () => {
      document.body.classList.remove('has-sidebar');
      document.getElementById('backend-kpi-css')?.remove();
    };
  }, []);

  return (
    <>
      <PhpSidebar />
      <div className={className}>{children}</div>
    </>
  );
}
