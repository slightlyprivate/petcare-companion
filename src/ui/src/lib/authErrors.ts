import { isDev } from './config';
import { PATHS } from '../routes/paths';
import type { ApiError } from './fetch';
import { getQueryClient } from './queryClient';
import { resetOnLogout } from './queryUtils';

let redirecting = false;

/**
 * Handle authentication errors by redirecting to the login page.
 * @param err The API error object.
 * @returns void
 */
export function handleAuthError(err: ApiError) {
  if (redirecting) return;
  const status = err?.status;
  // Only force logout + redirect on 401 (Unauthenticated)
  if (status !== 401) return;

  // Clear client caches/tokens to ensure a clean state
  try {
    const qc = getQueryClient();
    resetOnLogout(qc);
  } catch {}

  redirecting = true;
  if (isDev) {
    // eslint-disable-next-line no-console
    console.warn('[auth] 401 received; forcing logout and redirecting to home');
  }
  try {
    window.history.pushState({}, '', PATHS.ROOT);
    window.dispatchEvent(new PopStateEvent('popstate'));
  } catch {
    window.location.href = PATHS.ROOT;
  }
}
