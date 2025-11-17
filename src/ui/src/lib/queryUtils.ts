import type { QueryClient } from '@tanstack/react-query';
import { clearCsrfToken } from './csrfStore';

// Clears auth-derived caches and tokens on logout to prevent stale data.
export function resetOnLogout(qc: QueryClient) {
  try {
    qc.clear();
  } catch {}
  try {
    clearCsrfToken();
  } catch {}
}
