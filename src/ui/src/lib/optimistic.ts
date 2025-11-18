import type { QueryClient, QueryKey } from '@tanstack/react-query';

/**
 * Utility to build optimistic mutation handlers for list updates.
 * Usage: spread the returned handlers into useAppMutation({...}).
 */
export function optimisticListUpdate<TItem = unknown, TVariables = unknown>(args: {
  qc: QueryClient;
  listKey: QueryKey;
  // Given variables, returns a provisional item to insert/update
  makeItem: (vars: TVariables) => TItem;
  // How to merge the provisional/new item into the existing list
  merge?: (current: TItem[], next: TItem) => TItem[];
}) {
  const { qc, listKey, makeItem } = args;
  const merge = args.merge || ((cur, next) => [next, ...cur]);

  return {
    onMutate: async (variables: TVariables) => {
      await qc.cancelQueries({ queryKey: listKey });
      const prev = qc.getQueryData(listKey) as
        | { data?: TItem[]; [k: string]: unknown }
        | TItem[]
        | undefined;
      let currentList: TItem[] = [];
      if (Array.isArray(prev)) {
        currentList = prev;
      } else if (prev && typeof prev === 'object' && 'data' in prev && Array.isArray(prev.data)) {
        currentList = prev.data;
      }
      const optimistic = makeItem(variables);
      const nextList = merge(currentList, optimistic);
      if (prev && typeof prev === 'object' && !Array.isArray(prev) && 'data' in prev) {
        qc.setQueryData(listKey, { ...prev, data: nextList });
      } else {
        qc.setQueryData(listKey, nextList);
      }
      return { prev };
    },
    onError: (_err: unknown, _vars: TVariables, ctx: { prev?: unknown } | undefined) => {
      if (ctx && 'prev' in ctx) qc.setQueryData(listKey, ctx.prev);
    },
    onSettled: () => {
      qc.invalidateQueries({ queryKey: listKey });
    },
  } as const;
}
