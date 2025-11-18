import { api, proxy } from '../../lib/http';

/**
 * Fetch the currently authenticated user's information.
 */
export async function getMe() {
  return api('/auth/me');
}

/**
 * Check authentication status (204 if authenticated; 401 if not).
 */
export async function getStatus(): Promise<boolean> {
  try {
    await api('/auth/status');
    return true;
  } catch (e: unknown) {
    const status = (e as { status?: number } | undefined)?.status;
    if (status === 401) return false;
    throw e;
  }
}

/**
 * Request a one-time password (OTP) to be sent to the user's email.
 */
export async function requestOtp(payload: { email: string }) {
  return proxy('/auth/request', { method: 'POST', body: payload });
}

/**
 * Verify the one-time password (OTP) for authentication.
 */
export async function verifyOtp(payload: { email: string; code: string }) {
  return proxy('/auth/verify', { method: 'POST', body: payload });
}

/**
 * Log out the currently authenticated user.
 */
export async function logout() {
  await proxy('/auth/logout', { method: 'POST' });
}
