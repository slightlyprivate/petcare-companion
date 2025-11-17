import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch the list of credit purchases for the authenticated user.
 */
export const useCreditPurchases = () =>
  useAppQuery({ queryKey: qk.credits.purchases, queryFn: client.listPurchases });

/**
 * Hook to purchase credits for the authenticated user.
 */
export function usePurchaseCredits() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.purchase,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.credits.purchases }),
  });
}
