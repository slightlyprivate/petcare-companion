import { API_BASE, PROXY_BASE } from './config';
import { getCsrfToken } from './csrfStore';
import { ensureCsrf } from './csrf';

type Method = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

export interface RequestOptions<TBody = any> {
  method?: Method;
  body?: TBody;
  headers?: Record<string, string>;
  signal?: AbortSignal;
  json?: boolean; // default true when body is present
  base?: 'api' | 'proxy' | string; // default 'api'
  retries?: number; // network/5xx retries, default 0
  timeoutMs?: number; // optional timeout for the request
}

const joinUrl = (base: string, path: string) => {
  if (!path) return base;
  if (/^https?:\/\//.test(path)) return path;
  const b = base.endsWith('/') ? base.slice(0, -1) : base;
  const p = path.startsWith('/') ? path : `/${path}`;
  return `${b}${p}`;
};

async function doFetch(url: string, init: RequestInit, retries: number): Promise<Response> {
  try {
    const res = await fetch(url, init);
    if (retries > 0 && (res.status >= 500 || res.status === 429)) {
      await new Promise((r) =>
        setTimeout(r, 200 * Math.pow(2, Math.min(5, (init as any).__retryCount || 0))),
      );
      (init as any).__retryCount = ((init as any).__retryCount || 0) + 1;
      return doFetch(url, init, retries - 1);
    }
    return res;
  } catch (err) {
    if (retries > 0) {
      await new Promise((r) =>
        setTimeout(r, 200 * Math.pow(2, Math.min(5, (init as any).__retryCount || 0))),
      );
      (init as any).__retryCount = ((init as any).__retryCount || 0) + 1;
      return doFetch(url, init, retries - 1);
    }
    throw err;
  }
}

export async function request<T = any>(path: string, opts: RequestOptions = {}): Promise<T> {
  const baseOpt = opts.base ?? 'api';
  const baseUrl = baseOpt === 'api' ? API_BASE : baseOpt === 'proxy' ? PROXY_BASE : baseOpt;
  const url = joinUrl(baseUrl, path);

  const method = (opts.method || 'GET').toUpperCase() as Method;
  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(opts.headers || {}),
  };

  const init: RequestInit & { __retryCount?: number } = {
    method,
    headers,
    credentials: 'include',
    signal: opts.signal,
  };

  const needsCsrf = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);
  if (needsCsrf) {
    if (baseOpt === 'proxy' && !getCsrfToken()) {
      try {
        await ensureCsrf();
      } catch {
        // Allow request to proceed; server will enforce as needed
      }
    }
    const token = getCsrfToken();
    if (token) headers['X-CSRF-Token'] = token;
  }

  if (opts.body !== undefined) {
    if (opts.json !== false) {
      headers['Content-Type'] = 'application/json';
      init.body = JSON.stringify(opts.body);
    } else {
      init.body = opts.body as any;
    }
  }

  // Optional timeout handling
  let timeoutId: any;
  let controller: AbortController | undefined;
  if (opts.timeoutMs && !init.signal) {
    controller = new AbortController();
    init.signal = controller.signal;
    timeoutId = setTimeout(() => controller?.abort(), opts.timeoutMs);
  }

  try {
    const res = await doFetch(url, init, opts.retries ?? 0);
    const ct = res.headers.get('content-type') || '';

    if (!res.ok) {
      let data: any = undefined;
      if (ct.includes('application/json')) {
        try {
          data = await res.json();
        } catch {}
      } else {
        try {
          data = await res.text();
        } catch {}
      }
      const msg =
        (data && (data.error?.message || data.message)) || `Request failed: ${res.status}`;
      const err: any = new Error(msg);
      err.status = res.status;
      err.data = data;
      throw err;
    }

    if (ct.includes('application/json')) return res.json() as unknown as T;
    return res as unknown as T;
  } finally {
    if (timeoutId) clearTimeout(timeoutId);
  }
}

export function api<T = any>(path: string, opts: Omit<RequestOptions, 'base'> = {}) {
  return request<T>(path, { ...opts, base: 'api' });
}

export function proxy<T = any>(path: string, opts: Omit<RequestOptions, 'base'> = {}) {
  return request<T>(path, { ...opts, base: 'proxy' });
}
