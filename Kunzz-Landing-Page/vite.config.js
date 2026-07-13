import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');

  return {
    base: env.VITE_BASE || '/',
    plugins: [react()],
    server: {
      proxy: {
        '/media': {
          target: 'http://localhost/kunzzgroup',
          changeOrigin: true,
        },
        '/kunzzgroup/media': {
          target: 'http://localhost',
          changeOrigin: true,
        },
        '/api': {
          target: 'http://localhost/kunzzgroup',
          changeOrigin: true,
          rewrite: (path) => path.replace(/^\/api/, ''),
        },
        '/frontend': {
          target: 'http://localhost/kunzzgroup',
          changeOrigin: true,
        },
        '/backend': {
          target: 'http://localhost/kunzzgroup',
          changeOrigin: true,
          bypass: (req) => {
            const url = req.url || '';
            if (
              url.startsWith('/backend/kpi-v2') ||
              url.startsWith('/backend/cost-v2') ||
              url.startsWith('/backend/kpiedit-v2') ||
              url.startsWith('/backend/costedit-v2') ||
              url.startsWith('/backend/generatecode-v2') ||
              url.startsWith('/backend/add-employee-v2') ||
              url.startsWith('/backend/stocklistall-v2') ||
              url.startsWith('/backend/stockeditall-v2') ||
              url.startsWith('/backend/stockremark-v2') ||
              url.startsWith('/backend/stockproductname-v2') ||
              url.startsWith('/backend/stocksot-v2') ||
              url.startsWith('/backend/stockminimum-v2') ||
              url.startsWith('/backend/dishware_stock-v2') ||
              url.startsWith('/backend/price-v2') ||
              url.startsWith('/backend/supply-v2') ||
              url.startsWith('/backend/bgmusicupload-v2') ||
              url.startsWith('/backend/homepage1upload-v2') ||
              url.startsWith('/backend/aboutpage1upload-v2') ||
              url.startsWith('/backend/aboutpage4upload-v2') ||
              url.startsWith('/backend/joinpage1upload-v2') ||
              url.startsWith('/backend/joinpage2upload-v2') ||
              url.startsWith('/backend/joinpage3upload-v2') ||
              url.startsWith('/backend/corporate_blueprint-v2')
            ) {
              return '/index.html';
            }
            return undefined;
          },
        },
        '/kunzzgroup/backend': {
          target: 'http://localhost',
          changeOrigin: true,
        },
      },
    },
  };
});
