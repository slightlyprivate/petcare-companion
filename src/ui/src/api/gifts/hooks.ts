import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch the list of available gift types.
 */
export const useGiftTypes = () =>
  useAppQuery({ queryKey: qk.gifts.types, queryFn: client.listGiftTypes });

/**
 * Hook to fetch gifts for a specific pet.
 */
// No useGiftsByPet hook as API lacks a read endpoint for gifts by pet

/**
 * Hook to create a gift for a specific pet.
 */
export function useCreateGift() {
  return useAppMutation({
    mutationFn: client.createGift,
    onSuccess: () => {
      // No dedicated gifts listing cache yet; caller can refetch types or pet details if needed
    },
  });
}
