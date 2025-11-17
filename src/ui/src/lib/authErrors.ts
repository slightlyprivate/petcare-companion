import { isDev } from './config';
import type { ApiError } from './fetch';

let redirecting = false;

/**
 * Handle authentication errors by redirecting to the login page.
 * @param err The API error object.
 * @returns void
 */
export function handleAuthError(err: ApiError) {
  if (redirecting) return;
  const status = err?.status;
  if (status !== 401 && status !== 419) return;

  // Avoid redirect loops on the login route
  const { pathname, search, hash } = window.location;
  if (pathname.startsWith('/login')) return;

  redirecting = true;
  const redirectTo = `${pathname}${search}${hash}`;
  const qs = new URLSearchParams({ redirectTo }).toString();
  if (isDev) {
    // eslint-disable-next-line no-console
    console.warn('[auth] redirecting to /login due to auth error', status);
  }
  window.location.assign(`/login?${qs}`);
}
