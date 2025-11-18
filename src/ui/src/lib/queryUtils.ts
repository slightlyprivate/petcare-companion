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

// Invalidate a list of query keys
export function invalidateMany(qc: QueryClient, keys: readonly unknown[][]) {
  for (const key of keys) {
    qc.invalidateQueries({ queryKey: key });
  }
}

// Common list/detail invalidation pattern
export function invalidateListAndDetail(
  qc: QueryClient,
  listKey: readonly unknown[],
  detailKey: readonly unknown[] | null,
) {
  qc.invalidateQueries({ queryKey: listKey });
  if (detailKey) qc.invalidateQueries({ queryKey: detailKey });
}
