/**
 * Token Storage Module
 *
 * SECURITY CONSIDERATIONS:
 * ========================
 * This module stores authentication tokens in localStorage for persistence across
 * browser sessions. This approach has specific security trade-offs:
 *
 * XSS Vulnerability:
 * - Tokens stored in localStorage are accessible via JavaScript
 * - If the application is vulnerable to XSS, attackers can steal tokens
 * - Mitigations in place:
 *   1. React's built-in XSS protection (JSX auto-escapes values)
 *   2. Content Security Policy (CSP) headers configured on the server
 *   3. All user input is sanitized before rendering
 *   4. No use of dangerouslySetInnerHTML with untrusted content
 *
 * Alternative Approaches Considered:
 * - Memory-only storage: More secure but tokens lost on page refresh (poor UX)
 * - HttpOnly cookies: Most secure but this PR explicitly moves to token-based auth
 *   to support mobile apps and third-party integrations
 *
 * Additional Security Measures:
 * - Tokens should have reasonable expiry times (configured server-side)
 * - Token refresh mechanisms can be implemented for long-lived sessions
 * - Logout invalidates tokens server-side (not just client-side)
 *
 * For production deployments, ensure:
 * - HTTPS is enforced
 * - CSP headers are properly configured
 * - Regular security audits are performed
 */

const STORE_KEY = 'petcare.auth.token.v1';

let token: string | null = null;
let storageAvailable = true;
let warnedStorage = false;

function loadFromStorage() {
  try {
    const t = localStorage.getItem(STORE_KEY);
    if (t) {
      token = t;
    } else {
      token = null;
    }
  } catch {
    // Storage not available (SSR or privacy mode); fall back to memory only
    storageAvailable = false;
    if (!warnedStorage) {
      console.warn('[auth] Local storage unavailable; auth token kept in memory only.');
      warnedStorage = true;
    }
  }
}

// Attempt to hydrate on load
loadFromStorage();

/**
 * Get the current auth token.
 */
export function getAuthToken(): string | null {
  return token;
}

/**
 * Set the auth token.
 */
export function setAuthToken(t: string) {
  token = t;
  try {
    localStorage.setItem(STORE_KEY, t);
  } catch {
    storageAvailable = false;
    if (!warnedStorage) {
      console.warn('[auth] Failed to persist auth token; storage unavailable.');
      warnedStorage = true;
    }
  }
}

/**
 * Clear the auth token (logout).
 */
export function clearAuthToken() {
  token = null;
  try {
    localStorage.removeItem(STORE_KEY);
  } catch {
    // Ignore storage errors
  }
}

/**
 * Check if storage is available.
 */
export function isTokenStorageAvailable() {
  return storageAvailable;
}
