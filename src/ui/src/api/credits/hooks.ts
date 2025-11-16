import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch the list of credit purchases for the authenticated user.
 */
export const useCreditPurchases = () =>
  useQuery({ queryKey: qk.credits.purchases, queryFn: client.listPurchases });

/**
 * Hook to purchase credits for the authenticated user.
 */
export function usePurchaseCredits() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.purchase,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.credits.purchases }),
  });
}
