import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { BFF_REWRITE_PREFIXES } from '../shared/bffPaths.js';

export default defineConfig(() => {
  const target = process.env.VITE_API_PROXY_TARGET || 'http://frontend:3000';
  const proxyEntries: Record<string, { target: string; changeOrigin: boolean }> = {
    '/api': { target, changeOrigin: true },
    '/auth': { target, changeOrigin: true },
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
