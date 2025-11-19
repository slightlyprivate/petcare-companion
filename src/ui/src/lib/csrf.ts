import { request } from './fetch';
import { setCsrfToken } from './csrfStore';

/**
 * Ensure a valid CSRF token is available by fetching it from Laravel Sanctum.
 * Laravel Sanctum's /sanctum/csrf-cookie endpoint sets the XSRF-TOKEN cookie.
 */
export async function ensureCsrf(): Promise<string> {
  try {
    // Call Laravel Sanctum's csrf-cookie endpoint
    // This sets the XSRF-TOKEN cookie that Laravel expects
    // Use the API base URL to ensure correct domain/path in all environments
    await request('/sanctum/csrf-cookie');

    // Laravel sets the token in a cookie named XSRF-TOKEN
    // We need to read it from the cookie
    const token = getCsrfTokenFromCookie();
    if (token) {
      // Store it in our local storage for use in axios interceptor
      setCsrfToken(token);
    }
    return token ?? '';
  } catch (err) {
    if (import.meta.env.DEV) {
      console.warn('[csrf] Failed to fetch CSRF token', err);
    }
    const e = new Error('Failed to acquire CSRF token') as Error & {
      code?: string;
      cause?: unknown;
    };
    e.cause = err;
    e.code = 'CSRF_FETCH_FAILED';
    throw e;
  }
}

/**
 * Read the XSRF-TOKEN cookie that Laravel Sanctum sets
 */
function getCsrfTokenFromCookie(): string | null {
  if (typeof document === 'undefined') return null;
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (!match) return null;
  // Decode the cookie value (Laravel URL-encodes it)
  let token: string;
  try {
    token = decodeURIComponent(match[1]);
  } catch (err) {
    if (import.meta.env.DEV) {
      console.warn('[csrf] Failed to decode CSRF token from cookie', err);
    }
    return null;
  }
  // Validate token format: Laravel Sanctum tokens are typically 40+ characters
  // and contain alphanumeric characters plus common base64 chars
  if (!/^[A-Za-z0-9+/=_-]{40,}$/.test(token)) {
    if (import.meta.env.DEV) {
      console.warn('[csrf] Invalid CSRF token format detected');
    }
    return null;
  }
  return token;
}
