import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      // CMS media (video/images) — use production files in local dev when not on disk
      '/media': {
        target: 'https://kunzzgroup.com',
        changeOrigin: true,
        secure: true,
      },
      '/api': {
        target: 'http://localhost/kunzzgroup-1',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, ''),
      },
      '/frontend': {
        target: 'http://localhost/kunzzgroup-1',
        changeOrigin: true,
      },
    },
  },
});