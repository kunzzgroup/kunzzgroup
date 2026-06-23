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
          target: 'https://kunzzgroup.com',
          changeOrigin: true,
          secure: true,
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
              url.startsWith('/backend/kpiedit-v2')
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
