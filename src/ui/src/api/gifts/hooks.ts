import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch the list of available gift types.
 */
export const useGiftTypes = () =>
  useQuery({ queryKey: qk.gifts.types, queryFn: client.listGiftTypes });

/**
 * Hook to fetch gifts for a specific pet.
 */
export function useGiftsByPet(petId: number | string) {
  return useQuery({
    queryKey: qk.gifts.byPet(petId),
    queryFn: () => client.listByPet(petId),
    enabled: !!petId,
  });
}

/**
 * Hook to create a gift for a specific pet.
 */
export function useCreateGift() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.createGift,
    onSuccess: (_data, variables) => {
      // Invalidate only scoped keys related to this pet's gifts
      if (variables?.petId !== undefined) {
        qc.invalidateQueries({ queryKey: qk.gifts.byPet(variables.petId) });
      }
      // If gift types were changed server-side (unlikely), callers can refetch types explicitly
    },
  });
}
