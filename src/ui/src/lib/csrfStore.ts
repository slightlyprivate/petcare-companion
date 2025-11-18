const STORE_KEY = 'petcare.csrf.token.v1';
const STORE_EXP_KEY = 'petcare.csrf.token.exp.v1';
const DEFAULT_TTL_MS = 60 * 60 * 1000; // 1 hour

let token: string | null = null;
let expiresAt: number | null = null;
let storageAvailable = true;
let warnedStorage = false;

function loadFromStorage() {
  try {
    const t = localStorage.getItem(STORE_KEY);
    const expRaw = localStorage.getItem(STORE_EXP_KEY);
    const exp = expRaw ? Number(expRaw) : 0;
    if (t && exp && Date.now() < exp) {
      token = t;
      expiresAt = exp;
    } else {
      // stale or missing
      localStorage.removeItem(STORE_KEY);
      localStorage.removeItem(STORE_EXP_KEY);
      token = null;
      expiresAt = null;
    }
  } catch {
    // storage not available (SSR or privacy mode); fall back to memory only
    storageAvailable = false;
    if (!warnedStorage) {
      // eslint-disable-next-line no-console
      console.warn('[csrf] Local storage unavailable; CSRF token kept in memory only.');
      warnedStorage = true;
    }
  }
}

// attempt to hydrate on load
loadFromStorage();

/**
 * Get the current CSRF token.
 */
export function getCsrfToken(): string | null {
  if (expiresAt && Date.now() >= expiresAt) {
    // expired; clear
    try {
      localStorage.removeItem(STORE_KEY);
      localStorage.removeItem(STORE_EXP_KEY);
    } catch {}
    token = null;
    expiresAt = null;
    return null;
  }
  return token;
}

/**
 * Set the CSRF token.
 */
export function setCsrfToken(t: string, opts?: { ttlMs?: number; expiresAt?: number | Date }) {
  token = t;
  let expMs: number = DEFAULT_TTL_MS;
  if (opts?.ttlMs && opts.ttlMs > 0) expMs = opts.ttlMs;
  if (opts?.expiresAt instanceof Date) {
    expiresAt = opts.expiresAt.getTime();
  } else if (typeof opts?.expiresAt === 'number') {
    expiresAt = opts.expiresAt;
  } else {
    expiresAt = Date.now() + expMs;
  }
  try {
    localStorage.setItem(STORE_KEY, t);
    if (expiresAt) localStorage.setItem(STORE_EXP_KEY, String(expiresAt));
  } catch {
    storageAvailable = false;
    if (!warnedStorage) {
      // eslint-disable-next-line no-console
      console.warn('[csrf] Failed to persist CSRF token; storage unavailable.');
      warnedStorage = true;
    }
  }
}

export function clearCsrfToken() {
  token = null;
  expiresAt = null;
  try {
    localStorage.removeItem(STORE_KEY);
    localStorage.removeItem(STORE_EXP_KEY);
  } catch {}
}

export function isStorageAvailable() {
  return storageAvailable;
}
