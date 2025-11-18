import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { BFF_REWRITE_PREFIXES } from '../shared/bffPaths.js';

export default defineConfig(() => {
  const target = process.env.VITE_API_PROXY_TARGET || 'http://frontend:3000';
  const proxyEntries: Record<string, { target: string; changeOrigin: boolean }> = {
    '/api': { target, changeOrigin: true },
    // Proxy only the BFF auth endpoints, not SPA routes like /auth/signin
    '/auth/csrf': { target, changeOrigin: true },
    '/auth/request': { target, changeOrigin: true },
    '/auth/verify': { target, changeOrigin: true },
    '/auth/logout': { target, changeOrigin: true },
    '/brand': { target: 'http://localhost:8080', changeOrigin: true },
  };
  for (const prefix of BFF_REWRITE_PREFIXES) {
    proxyEntries[prefix] = { target, changeOrigin: true };
  }
  return {
    plugins: [react(), tailwindcss()],
    server: {
      host: true,
      port: 5173,
      proxy: proxyEntries,
    },
    preview: {
      host: true,
      port: 5173,
    },
  };
});
