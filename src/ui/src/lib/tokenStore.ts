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
