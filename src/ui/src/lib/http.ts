// `api()` targets the Laravel API
export { request, api, type RequestOptions } from './fetch';

import { isDev } from './config';
import { handleAuthError } from './authErrors';
import { request as coreRequest, type RequestOptions as CoreOptions } from './fetch';

export type HttpBase = 'api' | string;

export type HttpError = Error & {
  status?: number;
  code?: string | number;
  details?: unknown;
  data?: unknown;
};

export function toHttpError(err: unknown): HttpError {
  const e = err as
    | { message?: string; status?: number; code?: string | number; data?: unknown }
    | undefined;
  const out: HttpError = new Error(e?.message || 'Request failed');
  out.status = typeof e?.status === 'number' ? e.status : undefined;
  const dataObj = (e?.data || {}) as Record<string, unknown>;
  out.code = e?.code ?? (dataObj?.code as string | number | undefined);
  out.details = dataObj?.errors || dataObj?.detail || e?.data;
  out.data = e?.data;
  return out;
}

export interface HttpClientOptions {
  base?: HttpBase;
  headers?: Record<string, string>;
  enableLogging?: boolean;
  onRequest?: (info: { url: string; options: CoreOptions }) => void;
  onResponse?: (info: { url: string; status: number; data: unknown }) => void;
  onError?: (info: { url: string; error: HttpError }) => void;
}

export interface HttpClient {
  get<T = unknown>(path: string, opts?: Omit<CoreOptions, 'method' | 'base'>): Promise<T>;
  post<T = unknown>(
    path: string,
    body?: unknown,
    opts?: Omit<CoreOptions, 'method' | 'base' | 'body'>,
  ): Promise<T>;
  put<T = unknown>(
    path: string,
    body?: unknown,
    opts?: Omit<CoreOptions, 'method' | 'base' | 'body'>,
  ): Promise<T>;
  patch<T = unknown>(
    path: string,
    body?: unknown,
    opts?: Omit<CoreOptions, 'method' | 'base' | 'body'>,
  ): Promise<T>;
  delete<T = unknown>(path: string, opts?: Omit<CoreOptions, 'method' | 'base'>): Promise<T>;
}

export function createHttpClient(options: HttpClientOptions = {}): HttpClient {
  const base = options.base ?? 'api';
  const headers = options.headers ?? {};
  const logging = options.enableLogging ?? isDev;
  const log = (...args: unknown[]) => {
    try {
      console.debug('[http]', ...args);
    } catch {
      // Ignore console errors
    }
  };

  const doReq = async <T>(
    method: CoreOptions['method'],
    path: string,
    opts: CoreOptions = {},
  ): Promise<T> => {
    const mergedOpts: CoreOptions = {
      ...opts,
      method,
      headers: { ...headers, ...(opts.headers || {}) },
      base,
    };
    if (logging) {
      options.onRequest?.({ url: path, options: mergedOpts });
      if (!options.onRequest) log('request', method, path, { ...mergedOpts, headers: undefined });
    }
    try {
      const data = await coreRequest<T>(path, mergedOpts);
      if (logging) {
        options.onResponse?.({ url: path, status: 200, data });
        if (!options.onResponse) log('response', path, 200);
      }
      return data;
    } catch (err) {
      const hErr = toHttpError(err);
      if (logging) {
        options.onError?.({ url: path, error: hErr });
        if (!options.onError) log('error', path, hErr.status, hErr.message);
      }
      if (hErr.status === 401 || hErr.status === 419) {
        // Global auth handling to complement RequireAuth for background queries
        handleAuthError(hErr);
      }
      throw hErr;
    }
  };

  return {
    get: (path, opts) => doReq('GET', path, opts),
    post: (path, body, opts) => doReq('POST', path, { ...(opts || {}), body }),
    put: (path, body, opts) => doReq('PUT', path, { ...(opts || {}), body }),
    patch: (path, body, opts) => doReq('PATCH', path, { ...(opts || {}), body }),
    delete: (path, opts) => doReq('DELETE', path, opts),
  };
}

// Prebuilt client for convenience
export const http = {
  api: createHttpClient({ base: 'api' }),
};
