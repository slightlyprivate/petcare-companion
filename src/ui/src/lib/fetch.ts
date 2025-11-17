import type { AxiosError } from 'axios';
import axiosClient, { type AxiosExtendedConfig } from './axiosClient';
import { API_BASE, PROXY_BASE } from './config';

type Method = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

/**
 * Request options interface
 */
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

// Using centralized axios client with interceptors

/**
 * Generic request helper
 * @param path The API endpoint path.
 * @param opts The request options.
 * @returns The API response.
 */
export async function request<T = any>(path: string, opts: RequestOptions = {}): Promise<T> {
  const baseOpt = opts.base ?? 'api';
  const baseUrl = baseOpt === 'api' ? API_BASE : baseOpt === 'proxy' ? PROXY_BASE : baseOpt;
  const url = joinUrl(baseUrl, path);

  const method = (opts.method || 'GET').toUpperCase() as Method;
  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(opts.headers || {}),
  };

  const axConfig: AxiosExtendedConfig = {
    method,
    headers,
    signal: opts.signal,
    timeout: opts.timeoutMs,
    __maxRetries: opts.retries ?? 0,
  };

  if (opts.body !== undefined) {
    if (opts.json !== false) {
      headers['Content-Type'] = 'application/json';
      axConfig.data = opts.body;
    } else {
      axConfig.data = opts.body as any;
    }
  }

  try {
    const res = await axiosClient.request<T>({ url, ...axConfig });
    return res.data as T;
  } catch (error) {
    const axErr = error as AxiosError;
    const status = axErr.response?.status;
    const data = axErr.response?.data;

    const msg =
      (data as any)?.error?.message ||
      (data as any)?.message ||
      `Request failed: ${status ?? 'network'}`;
    const e: any = new Error(msg);
    e.status = status;
    e.data = data;
    throw e;
  }
}

/**
 * API request helper
 * @param path The API endpoint path.
 * @param opts The request options.
 * @returns The API response.
 */
export function api<T = any>(path: string, opts: Omit<RequestOptions, 'base'> = {}) {
  return request<T>(path, { ...opts, base: 'api' });
}

/**
 * Proxy request helper
 * @param path The API endpoint path.
 * @param opts The request options.
 * @returns The API response.
 */
export function proxy<T = any>(path: string, opts: Omit<RequestOptions, 'base'> = {}) {
  return request<T>(path, { ...opts, base: 'proxy' });
}

// Cross-cutting helpers
export type ApiError = Error & { status?: number; data?: any };

/**
 * Determines if an error is an authentication error (401, 403, 419).
 * @param err The error object to check.
 * @returns True if the error is an authentication error, false otherwise.
 */
export function isAuthError(err: unknown): err is ApiError {
  const e = err as any;
  return (
    !!e &&
    typeof e === 'object' &&
    typeof e.status === 'number' &&
    [401, 403, 419].includes(e.status)
  );
}

/**
 * Paginated API response structure.
 */
export interface Paginated<T> {
  data: T[];
  meta?: { total?: number; page?: number; per_page?: number };
}

/**
 * Normalizes a paginated API response.
 * @param res The API response object.
 * @returns The normalized paginated response.
 */
export function normalizePaginated<T>(res: any): Paginated<T> {
  if (Array.isArray(res)) return { data: res };
  if (res && Array.isArray(res.data)) return { data: res.data, meta: res.meta };
  return { data: [] };
}

/**
 * Unwraps a resource from the API response.
 * @param res The API response object.
 * @returns The unwrapped resource or null if not found.
 */
export function unwrapResource<T>(res: any): T | null {
  if (
    res &&
    typeof res === 'object' &&
    !Array.isArray(res) &&
    'data' in res &&
    !Array.isArray(res.data)
  ) {
    return res.data as T;
  }
  return (res as T) ?? null;
}
