import { API_BASE } from './config';
import { getCsrfToken } from './csrfStore';

type Method = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

/**
 * Options for the HTTP request.
 */
export interface HttpOptions<TBody = any> {
  method?: Method;
  body?: TBody;
  headers?: Record<string, string>;
  signal?: AbortSignal;
  json?: boolean; // default true for body
}

/**
 * Generic HTTP request function to interact with the API.
 */
export async function http<T = any>(path: string, opts: HttpOptions = {}): Promise<T> {
  const url = path.startsWith('http') ? path : `${API_BASE}${path}`;
  const method = (opts.method || 'GET').toUpperCase() as Method;
  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(opts.headers || {}),
  };

  const init: RequestInit = {
    method,
    headers,
    credentials: 'include',
    signal: opts.signal,
  };

  const needsCsrf = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);
  if (needsCsrf) {
    const token = getCsrfToken();
    if (token) headers['X-CSRF-Token'] = token;
  }

  // JSON body by default
  if (opts.body !== undefined) {
    if (opts.json !== false) {
      headers['Content-Type'] = 'application/json';
      init.body = JSON.stringify(opts.body);
    } else {
      init.body = opts.body as any;
    }
  }

  const res = await fetch(url, init);
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
    const msg = (data && (data.error?.message || data.message)) || `Request failed: ${res.status}`;
    const err: any = new Error(msg);
    err.status = res.status;
    err.data = data;
    throw err;
  }
  if (ct.includes('application/json')) return res.json() as Promise<T>;
  // @ts-expect-error caller must handle non-JSON
  return res;
}
