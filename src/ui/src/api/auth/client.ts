import { api } from '../../lib/http';
import type { User } from '../types';
import { setAuthToken, clearAuthToken } from '../../lib/tokenStore';

/** Response from the verify OTP endpoint */
interface VerifyOtpResponse {
  token: string;
  token_type: string;
  user: User;
}

/**
 * Fetch the currently authenticated user's information.
 */
export async function getMe(): Promise<User> {
  return api('/auth/me');
}

/**
 * Check authentication status (200 if authenticated; 401 if not).
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
 * Stores the returned token for subsequent authenticated requests.
 */
export async function verifyOtp(payload: {
  email: string;
  code: string;
  device_name?: string;
}): Promise<VerifyOtpResponse> {
  const response = await api<VerifyOtpResponse>('/auth/verify', { method: 'POST', body: payload });
  // Store the token for future requests
  if (response.token) {
    setAuthToken(response.token);
  }
  return response;
}

/**
 * Log out the currently authenticated user.
 * Clears the stored auth token.
 */
export async function logout() {
  try {
    await api('/auth/logout', { method: 'POST' });
  } finally {
    clearAuthToken();
  }
}
