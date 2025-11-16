import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
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

// Authenticated pets
export function usePets() {
  return useQuery({ queryKey: ['pets', 'mine'], queryFn: client.listPets });
}

/**
 * Hook to fetch the details of a specific pet.
 */
export function useCreatePet() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.createPet,
    onSuccess: () => qc.invalidateQueries({ queryKey: ['pets', 'mine'] }),
  });
}

/**
 * Hook to update pet details.
 */
export function useUpdatePet() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.updatePet,
    onSuccess: (_d, v) => {
      qc.invalidateQueries({ queryKey: ['pets', 'mine'] });
      if (v?.id) qc.invalidateQueries({ queryKey: qk.pets.detail(v.id) });
    },
  });
}

/**
 * Hook to delete a pet.
 */
export function useDeletePet() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.deletePet,
    onSuccess: (_d, id) => {
      qc.invalidateQueries({ queryKey: ['pets', 'mine'] });
      if (id) qc.invalidateQueries({ queryKey: qk.pets.detail(id as any) });
    },
  });
}

/**
 * Hook to restore a deleted pet.
 */
export function useRestorePet() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.restorePet,
    onSuccess: (_d, id) => {
      qc.invalidateQueries({ queryKey: ['pets', 'mine'] });
      if (id) qc.invalidateQueries({ queryKey: qk.pets.detail(id as any) });
    },
  });
}
