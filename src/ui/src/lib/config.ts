const warn = (msg: string) => {
  if (import.meta.env.DEV) {
    // eslint-disable-next-line no-console
    console.warn(`[config] ${msg}`);
  }
};

const normalizeBase = (value: string | undefined, fallback: string) => {
  const v = (value ?? fallback).trim();
  if (!v) return '';
  if (v === '/') return '/';
  const out = v.endsWith('/') ? v.slice(0, -1) : v;
  if (!/^https?:\/\//.test(out) && !out.startsWith('/')) {
    warn(`Base URL '${out}' should be absolute or start with '/'.`);
  }
  return out;
};

export const API_BASE = normalizeBase(import.meta.env.VITE_API_BASE, '/api');
// Prefer VITE_PROXY_BASE
export const PROXY_BASE = normalizeBase(import.meta.env.VITE_PROXY_BASE, '');
export const isDev = import.meta.env.DEV;

// Basic runtime guardrails
if (!API_BASE && import.meta.env.PROD) {
  throw new Error('VITE_API_BASE is required in production.');
}
// Enforce PROXY_BASE in production; relative proxy URLs are fragile across origins
if (!PROXY_BASE && import.meta.env.PROD) {
  throw new Error('VITE_PROXY_BASE is required in production for stable BFF routing.');
}
