const warn = (msg: string) => {
  if (import.meta.env.DEV) {
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
export const isDev = import.meta.env.DEV;
export const ASSET_BASE = normalizeBase(import.meta.env.VITE_ASSET_BASE, '/storage');

if (!ASSET_BASE && import.meta.env.PROD) {
  throw new Error('VITE_ASSET_BASE is required in production.');
}
