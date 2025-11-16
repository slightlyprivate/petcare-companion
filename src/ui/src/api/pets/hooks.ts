import { useQuery } from '@tanstack/react-query';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch the list of public pets.
 */
export function usePublicPets() {
  return useQuery({ queryKey: qk.pets.all, queryFn: client.listPublicPets });
}

/**
 * Hook to fetch the details of a specific public pet.
 */
export function usePublicPet(id: number | string) {
  return useQuery({
    queryKey: qk.pets.detail(id),
    queryFn: () => client.getPublicPet(id),
    enabled: !!id,
  });
}
