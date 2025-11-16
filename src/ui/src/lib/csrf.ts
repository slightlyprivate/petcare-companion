import { proxy } from './http';
import { setCsrfToken } from './csrfStore';

/**
 * Ensure a valid CSRF token is available by fetching it from the server.
 */
export async function ensureCsrf(): Promise<string> {
  const data = await proxy<{ csrfToken: string }>('/auth/csrf');
  if (data?.csrfToken) setCsrfToken(data.csrfToken);
  return data?.csrfToken;
}
