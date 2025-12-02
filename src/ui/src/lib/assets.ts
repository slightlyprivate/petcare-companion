import { ASSET_BASE } from './config';

const isAbsolute = (value: string) =>
  /^https?:\/\//.test(value) || value.startsWith('//') || value.startsWith('data:');

/**
 * Build a browser-safe asset URL from a storage path or absolute URL.
 */
export function resolveAssetUrl(path?: string | null): string {
  if (!path) return '';
  if (isAbsolute(path)) return path;

  const normalized = path.startsWith('/storage')
    ? path
    : `${ASSET_BASE}/${path.replace(/^\/+/, '')}`;

  if (normalized.startsWith('http') || normalized.startsWith('//')) {
    return normalized;
  }

  return normalized.startsWith('/') ? normalized : `/${normalized}`;
}
