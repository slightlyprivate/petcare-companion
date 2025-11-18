import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import {
  listPublicPets,
  getPublicPet,
  listPets,
  createPet,
  updatePet,
  deletePet,
  restorePet,
  getPet,
} from './client';

type UpdatePetVariables = {
  id: number | string;
  name: string;
  species: string;
  owner_name: string;
  breed?: string | null;
  birth_date?: string | null;
};

/**
 * Hook to fetch the list of public pets.
 */
export function usePublicPets() {
  return useAppQuery({ queryKey: qk.pets.all, queryFn: listPublicPets });
}

/**
 * Hook to fetch the details of a specific public pet.
 */
export function usePublicPet(id: number | string) {
  return useAppQuery({
    queryKey: qk.pets.detail(id),
    queryFn: () => getPublicPet(id),
    enabled: !!id,
  });
}

// Authenticated pets
export function usePets() {
  return useAppQuery({ queryKey: qk.pets.mine, queryFn: listPets });
}

/**
 * Hook to fetch the details of a specific pet.
 */
export function useCreatePet() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: createPet,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.pets.mine }),
  });
}

/**
 * Hook to update pet details.
 */
export function useUpdatePet() {
  const qc = useQueryClient();
  return useAppMutation<unknown, unknown, UpdatePetVariables>({
    mutationFn: updatePet,
    onSuccess: (_d, v) => {
      qc.invalidateQueries({ queryKey: qk.pets.mine });
      if (v?.id) qc.invalidateQueries({ queryKey: qk.pets.detail(v.id) });
    },
  });
}

/**
 * Hook to delete a pet.
 */
export function useDeletePet() {
  const qc = useQueryClient();
  return useAppMutation<unknown, unknown, number | string>({
    mutationFn: deletePet,
    onSuccess: (_d, id) => {
      qc.invalidateQueries({ queryKey: qk.pets.mine });
      if (id) qc.invalidateQueries({ queryKey: qk.pets.detail(id) });
    },
  });
}

/**
 * Hook to restore a deleted pet.
 */
export function useRestorePet() {
  const qc = useQueryClient();
  return useAppMutation<unknown, unknown, number | string>({
    mutationFn: restorePet,
    onSuccess: (_d, id) => {
      qc.invalidateQueries({ queryKey: qk.pets.mine });
      if (id) qc.invalidateQueries({ queryKey: qk.pets.detail(id) });
    },
  });
}
/**
 * Hook to fetch a pet by id (authenticated route).
 */
export function usePet(id: number | string) {
  return useAppQuery({
    queryKey: qk.pets.detail(id),
    queryFn: () => getPet(id),
    enabled: !!id,
  });
}
