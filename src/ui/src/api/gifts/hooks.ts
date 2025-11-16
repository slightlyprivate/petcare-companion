import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch the list of available gift types.
 */
export const useGiftTypes = () =>
  useQuery({ queryKey: qk.gifts.types, queryFn: client.listGiftTypes });

/**
 * Hook to create a gift for a specific pet.
 */
export function useCreateGift() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: client.createGift, onSuccess: () => qc.invalidateQueries() });
}
