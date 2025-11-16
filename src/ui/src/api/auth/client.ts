import { http } from '../../lib/http';

/**
 * Fetch the currently authenticated user's information.
 */
export async function getMe() {
  return http('/auth/me');
}

/**
 * Request a one-time password (OTP) to be sent to the user's email.
 */
export async function requestOtp(payload: { email: string }) {
  // BFF auth route (not under /api base): call absolute path
  const res = await fetch('/auth/request', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error(`OTP request failed: ${res.status}`);
  return res.json();
}

/**
 * Verify the one-time password (OTP) for authentication.
 */
export async function verifyOtp(payload: { email: string; code: string }) {
  const res = await fetch('/auth/verify', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error(`OTP verify failed: ${res.status}`);
  return res.json();
}

/**
 * Log out the currently authenticated user.
 */
export async function logout() {
  const res = await fetch('/auth/logout', { method: 'POST', credentials: 'include' });
  if (!res.ok && res.status !== 204) throw new Error(`Logout failed: ${res.status}`);
}
