import { proxy } from './http';
import { setCsrfToken } from './csrfStore';

/**
 * Ensure a valid CSRF token is available by fetching it from the server.
 */
export async function ensureCsrf(): Promise<string> {
  type CsrfResponse = { csrfToken: string; ttlMs?: number; expiresAt?: string };
  try {
    const data = await proxy<CsrfResponse>('/auth/csrf');
    if (data?.csrfToken) {
      const exp = data.expiresAt ? Date.parse(data.expiresAt) : undefined;
      setCsrfToken(data.csrfToken, {
        ttlMs: data.ttlMs,
        expiresAt: typeof exp === 'number' && !Number.isNaN(exp) ? exp : undefined,
      });
    }
    return (data as any)?.csrfToken;
  } catch (err) {
    if (import.meta.env.DEV) {
      // eslint-disable-next-line no-console
      console.warn('[csrf] Failed to fetch CSRF token', err);
    }
    const e: any = new Error('Failed to acquire CSRF token');
    e.cause = err;
    e.code = 'CSRF_FETCH_FAILED';
    throw e;
  }
}
