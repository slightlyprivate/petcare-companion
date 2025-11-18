import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(() => {
  // Proxy target should point to the Laravel backend (web service in docker-compose)
  const target = process.env.VITE_API_PROXY_TARGET || 'http://web';
  
  return {
    plugins: [react(), tailwindcss()],
    server: {
      host: true,
      port: 5173,
      proxy: {
        // Proxy all /api requests to Laravel backend
        '/api': {
          target,
          changeOrigin: true,
        },
        // Proxy /sanctum requests for CSRF cookie and auth
        '/sanctum': {
          target,
          changeOrigin: true,
        },
        // Proxy /storage requests for uploaded files
        '/storage': {
          target,
          changeOrigin: true,
        },
      },
    },
    preview: {
      host: true,
      port: 5173,
    },
  };
});
