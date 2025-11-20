import { api } from '../../lib/http';
import type { User } from '../types';

/**
 * Fetch the currently authenticated user's information.
 */
export async function getMe(): Promise<User> {
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
  return api('/auth/request', { method: 'POST', body: payload });
}

/**
 * Verify the one-time password (OTP) for authentication.
 */
export async function verifyOtp(payload: { email: string; code: string }) {
  return api('/auth/verify', { method: 'POST', body: payload });
}

/**
 * Log out the currently authenticated user.
 */
export async function logout() {
  await api('/auth/logout', { method: 'POST' });
}
