import { isDev } from './config';
import { PATHS } from '../routes/paths';
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
  // Avoid redirect loops on any auth routes
  if (pathname.startsWith(PATHS.AUTH.ROOT)) return;

  redirecting = true;
  const redirectTo = `${pathname}${search}${hash}`;
  const qs = new URLSearchParams({ redirectTo }).toString();
  if (isDev) {
    // eslint-disable-next-line no-console
    console.warn('[auth] redirecting to signin due to auth error', status);
  }
  const url = `${PATHS.AUTH.SIGNIN}?${qs}`;
  try {
    // Client-side navigate to avoid dev proxy intercepting /auth/*
    window.history.pushState({}, '', url);
    window.dispatchEvent(new PopStateEvent('popstate'));
  } catch {
    // Fallback
    window.location.href = url;
  }
}
