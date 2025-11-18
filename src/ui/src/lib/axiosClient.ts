import axios, {
  AxiosError,
  AxiosInstance,
  AxiosRequestConfig,
  InternalAxiosRequestConfig,
  AxiosHeaders,
} from 'axios';
import { isDev } from './config';
import { getCsrfToken, isStorageAvailable } from './csrfStore';
import { ensureCsrf } from './csrf';
import { handleAuthError } from './authErrors';

type UnsafeMethod = 'POST' | 'PUT' | 'PATCH' | 'DELETE';
const isUnsafe = (m?: string) =>
  !!m && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(m.toUpperCase());

export type AxiosExtendedConfig = AxiosRequestConfig & {
  __csrfRetried?: boolean;
  __retryCount?: number;
  __maxRetries?: number;
};
type InterceptConfig = InternalAxiosRequestConfig & {
  __csrfRetried?: boolean;
  __retryCount?: number;
  __maxRetries?: number;
};

function delay(ms: number) {
  return new Promise((r) => setTimeout(r, ms));
}

const client: AxiosInstance = axios.create({
  withCredentials: true,
  headers: { Accept: 'application/json' },
});

// Request interceptor: attach CSRF for unsafe methods; light logging
client.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  const cfg = config as InterceptConfig;
  if (cfg.withCredentials === undefined) cfg.withCredentials = true;

  const method = (cfg.method || 'GET').toUpperCase();
  if (isUnsafe(method)) {
    let token = getCsrfToken();
    if (!token) {
      try {
        await ensureCsrf();
      } catch {
        // ignore; server will enforce CSRF
      }
      token = getCsrfToken() || token;
    }
    if (token) {
      cfg.headers.set('X-CSRF-Token', token);
    }
  }

  if (isDev) {
    try {
      // eslint-disable-next-line no-console
      console.debug('[http][req]', method, cfg.url);
    } catch {}
  }

  return cfg as InternalAxiosRequestConfig;
});

// Response interceptor: handle CSRF 419 retry, auth redirect, and transient retries
client.interceptors.response.use(
  (response) => {
    if (isDev) {
      try {
        // eslint-disable-next-line no-console
        console.debug('[http][res]', response.status, response.config.url);
      } catch {}
    }
    return response;
  },
  async (error: AxiosError) => {
    const cfg = (error.config || {}) as InterceptConfig;
    const status = error.response?.status;
    const method = (cfg.method || 'GET').toUpperCase();

    // Attempt CSRF refresh once on 419 for unsafe methods
    if (status === 419 && isUnsafe(method) && !cfg.__csrfRetried) {
      try {
        await ensureCsrf();
        const token = getCsrfToken();
        cfg.__csrfRetried = true;
        if (token) {
          cfg.headers.set('X-CSRF-Token', token);
        }
        return client.request(cfg as AxiosRequestConfig);
      } catch (e) {
        if (isDev) {
          // eslint-disable-next-line no-console
          console.warn('[csrf] Retry after 419 failed; continuing with original error', e);
        }
      }
    }

    // Global auth redirect support
    if (status === 401 || status === 419) {
      // Best-effort notify; guarded against loops internally
      handleAuthError({ name: 'AuthError', message: error.message, status } as unknown as Error & {
        status?: number;
      });
    }

    // Transient retry: network, 5xx, 429
    const max = cfg.__maxRetries ?? 0;
    const count = cfg.__retryCount ?? 0;
    const isTransient = !status || status >= 500 || status === 429;
    if (count < max && isTransient) {
      const backoff = Math.min(200 * 2 ** Math.min(5, count), 4000);
      cfg.__retryCount = count + 1;
      await delay(backoff);
      return client.request(cfg as AxiosRequestConfig);
    }

    if (isDev) {
      try {
        // eslint-disable-next-line no-console
        console.debug('[http][err]', status, cfg.url, isStorageAvailable() ? '' : '(no storage)');
      } catch {}
    }

    return Promise.reject(error);
  },
);

export default client;
