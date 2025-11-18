import { useQuery as rqUseQuery, useMutation as rqUseMutation } from '@tanstack/react-query';
import type {
  UseQueryOptions,
  UseQueryResult,
  UseMutationOptions,
  UseMutationResult,
  DefaultError,
} from '@tanstack/react-query';
import type { QueryKey } from '@tanstack/query-core';

// Thin wrappers to encourage consistent defaults across domains.

/**
 * Custom useQuery hook with app-wide defaults
 * @param options
 * @returns UseQueryResult
 */
export function useAppQuery<
  TQueryFnData = unknown,
  TError = DefaultError,
  TData = TQueryFnData,
  TQueryKey extends QueryKey = QueryKey,
>(options: UseQueryOptions<TQueryFnData, TError, TData, TQueryKey>): UseQueryResult<TData, TError> {
  const withDefaults = {
    staleTime: options.staleTime ?? 60_000,
    gcTime: options.gcTime ?? 5 * 60_000,
    // Let global defaults handle error boundaries; override per-query if needed
    ...options,
  } as UseQueryOptions<TQueryFnData, TError, TData, TQueryKey>;
  return rqUseQuery(withDefaults);
}

/**
 * Custom useMutation hook with app-wide defaults
 * @param options
 * @returns UseMutationResult
 */
export function useAppMutation<
  TData = unknown,
  TError = DefaultError,
  TVariables = void,
  TContext = unknown,
>(
  options: UseMutationOptions<TData, TError, TVariables, TContext>,
): UseMutationResult<TData, TError, TVariables, TContext> {
  const withDefaults = {
    retry: options.retry ?? 1,
    ...options,
  } as UseMutationOptions<TData, TError, TVariables, TContext>;
  return rqUseMutation(withDefaults);
}

/**
 * Paginated query helper with stable UX defaults (keep previous page data)
 */
export function usePaginatedQuery<
  TQueryFnData = unknown,
  TError = DefaultError,
  TData = TQueryFnData,
  TQueryKey extends QueryKey = QueryKey,
>(options: UseQueryOptions<TQueryFnData, TError, TData, TQueryKey>): UseQueryResult<TData, TError> {
  const withDefaults = {
    placeholderData:
      options.placeholderData ??
      ((prev: TData | TQueryFnData | undefined) => prev as unknown as TData),
    ...options,
  } as UseQueryOptions<TQueryFnData, TError, TData, TQueryKey>;
  return rqUseQuery(withDefaults);
}

/**
 * Convenience alias for simple page-based lists.
 * Equivalent to usePaginatedQuery with placeholderData for keeping previous data.
 */
export const usePageQuery = usePaginatedQuery;
