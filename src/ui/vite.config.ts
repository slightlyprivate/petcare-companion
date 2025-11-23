import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(() => {
  const target = process.env.VITE_API_PROXY_TARGET || 'http://web';

  return {
    plugins: [react()],
    server: {
      host: true,
      port: 5174,
      proxy: {
        '/api': { target, changeOrigin: true },
        '/sanctum': { target, changeOrigin: true },
        '/storage': { target, changeOrigin: true },
      },
    },
    preview: {
      host: true,
      port: 4174,
    },
  };
});
