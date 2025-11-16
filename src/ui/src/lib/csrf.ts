import { BFF_BASE } from './config';
import { setCsrfToken } from './csrfStore';

/**
 * Ensure a valid CSRF token is available by fetching it from the server.
 */
export async function ensureCsrf(): Promise<string> {
  const res = await fetch(`${BFF_BASE}/auth/csrf`, {
    credentials: 'include',
  });
  if (!res.ok) throw new Error(`CSRF fetch failed: ${res.status}`);
  const data = await res.json();
  if (data?.csrfToken) setCsrfToken(data.csrfToken);
  return data?.csrfToken;
}
