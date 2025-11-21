import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import { listPetActivities, createPetActivity, deletePetActivity } from './client';
import type { ListActivitiesParams } from './types';

/**
 * Hook to fetch activities for a specific pet
 */
export function usePetActivities(petId: number | string, params?: ListActivitiesParams) {
  return useAppQuery({
    queryKey: qk.activities.byPet(petId, params as Record<string, unknown>),
    queryFn: () => listPetActivities(petId, params),
    enabled: !!petId,
  });
}

/**
 * Hook to create a new activity for a pet
 */
export function useCreatePetActivity() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: createPetActivity,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: qk.activities.byPet(variables.petId) });
    },
  });
}

/**
 * Hook to delete an activity
 */
export function useDeletePetActivity() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: deletePetActivity,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.activities.all });
    },
  });
}
