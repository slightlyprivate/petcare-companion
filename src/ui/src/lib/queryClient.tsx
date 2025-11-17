import { QueryClient, QueryCache, MutationCache } from '@tanstack/react-query';
import type { ApiError } from './fetch';
import { isAuthError } from './fetch';
import { handleAuthError } from './authErrors';

let client: QueryClient | null = null;

export function getQueryClient() {
  if (client) return client;

  // Global error behavior: avoid retries for auth/404, backoff for transient failures
  const queriesDefault = {
    staleTime: 60 * 1000, // 1 minute caching by default
    gcTime: 5 * 60 * 1000, // 5 minutes
    retry: (failureCount: number, error: unknown) => {
      const err = error as ApiError;
      if (isAuthError(err)) return false;
      const status = (err as any)?.status;
      if (status === 404) return false;
      return failureCount < 2; // up to 2 retries for network/5xx
    },
    retryDelay: (attempt: number) => Math.min(1000 * 2 ** attempt, 8000),
    useErrorBoundary: (error: unknown) => {
      const status = (error as any)?.status;
      return typeof status === 'number' && status >= 500; // bubble 5xx to route error boundaries
    },
    onError: (error: unknown) => {
      const err = error as ApiError;
      if (isAuthError(err)) handleAuthError(err);
    },
  } as const;

  const mutationsDefault = {
    retry: (failureCount: number, error: unknown) => {
      const err = error as ApiError;
      if (isAuthError(err)) return false;
      const status = (err as any)?.status;
      if (status === 422) return false; // validation shouldn't retry
      return failureCount < 1; // single retry for transient failures
    },
    retryDelay: (attempt: number) => Math.min(1000 * 2 ** attempt, 4000),
    onError: (error: unknown) => {
      const err = error as ApiError;
      if (isAuthError(err)) handleAuthError(err);
    },
  } as const;

  client = new QueryClient({
    defaultOptions: {
      queries: queriesDefault,
      mutations: mutationsDefault,
    },
    queryCache: new QueryCache({
      onError: (error) => {
        const err = error as ApiError;
        if (isAuthError(err)) handleAuthError(err);
      },
    }),
    mutationCache: new MutationCache({
      onError: (error) => {
        const err = error as ApiError;
        if (isAuthError(err)) handleAuthError(err);
      },
    }),
  });

  return client;
}
