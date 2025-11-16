import { proxy } from './http';
import { setCsrfToken } from './csrfStore';

/**
 * Ensure a valid CSRF token is available by fetching it from the server.
 */
export async function ensureCsrf(): Promise<string> {
  type CsrfResponse = { csrfToken: string; ttlMs?: number; expiresAt?: string };
  const data = await proxy<CsrfResponse>('/auth/csrf');
  if (data?.csrfToken) {
    const exp = data.expiresAt ? Date.parse(data.expiresAt) : undefined;
    setCsrfToken(data.csrfToken, {
      ttlMs: data.ttlMs,
      expiresAt: typeof exp === 'number' && !Number.isNaN(exp) ? exp : undefined,
    });
  }
  return (data as any)?.csrfToken;
}
