import type { QueryClient, QueryKey } from '@tanstack/react-query';

/**
 * Utility to build optimistic mutation handlers for list updates.
 * Usage: spread the returned handlers into useAppMutation({...}).
 */
export function optimisticListUpdate<TItem = any, TVariables = any>(args: {
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
      const prev = qc.getQueryData<any>(listKey);
      const currentList: TItem[] = Array.isArray(prev?.data)
        ? prev.data
        : Array.isArray(prev)
          ? prev
          : [];
      const optimistic = makeItem(variables);
      const nextList = merge(currentList, optimistic);
      if (prev && 'data' in (prev as any)) {
        qc.setQueryData(listKey, { ...prev, data: nextList });
      } else {
        qc.setQueryData(listKey, nextList);
      }
      return { prev };
    },
    onError: (_err: any, _vars: TVariables, ctx: any) => {
      if (ctx?.prev !== undefined) qc.setQueryData(listKey, ctx.prev);
    },
    onSettled: () => {
      qc.invalidateQueries({ queryKey: listKey });
    },
  } as const;
}
