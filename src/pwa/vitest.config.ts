import { defineConfig } from 'vitest/config';

export default defineConfig({
  // React plugin removed to avoid version type mismatch between vitest's internal vite and project vite.
  // TSX transforms handled via esbuild + TypeScript settings; add plugin if needed for advanced features.
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.ts'],
    globals: true,
    coverage: {
      reporter: ['text', 'json', 'html'],
    },
  },
});
