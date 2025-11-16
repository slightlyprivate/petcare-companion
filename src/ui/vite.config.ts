import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: {
    host: true,
    port: 5173,
    proxy: {
      // Proxy API calls to the Node BFF (which proxies to Laravel)
      '/api': {
        // Prefer env for containerized dev; falls back to BFF service name
        target: process.env.VITE_API_PROXY_TARGET || 'http://frontend:3000',
        changeOrigin: true,
      },
      // Proxy BFF auth/session endpoints
      '/auth': {
        target: process.env.VITE_API_PROXY_TARGET || 'http://frontend:3000',
        changeOrigin: true,
      },
    },
  },
  preview: {
    host: true,
    port: 5173,
  },
});
