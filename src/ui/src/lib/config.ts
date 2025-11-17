const warn = (msg: string) => {
  if (import.meta.env.DEV) {
    // eslint-disable-next-line no-console
    console.warn(`[config] ${msg}`);
  }
};

const normalizeBase = (value: string | undefined, fallback: string) => {
  const v = (value ?? fallback).trim();
  if (!v) return '';
  const out = v.endsWith('/') ? v.slice(0, -1) : v;
  if (!/^https?:\/\//.test(out) && !out.startsWith('/')) {
    warn(`Base URL '${out}' should be absolute or start with '/'.`);
  }
  return out;
};

export const API_BASE = normalizeBase(import.meta.env.VITE_API_BASE, '/api');
// Prefer VITE_PROXY_BASE
export const PROXY_BASE = normalizeBase((import.meta.env as any).VITE_PROXY_BASE, '');
export const isDev = import.meta.env.DEV;

// Basic runtime guardrails
if (!API_BASE && import.meta.env.PROD) {
  throw new Error('VITE_API_BASE is required in production.');
}

// Warn when PROXY_BASE is empty in production; relative proxy calls may be fragile cross-origin
if (!PROXY_BASE && import.meta.env.PROD) {
  // eslint-disable-next-line no-console
  console.warn(
    "[config] VITE_PROXY_BASE is empty in production; BFF 'proxy()' requests will use relative URLs, which may break in multi-origin deployments.",
  );
}
