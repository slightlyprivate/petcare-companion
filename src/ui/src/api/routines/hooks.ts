import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import {
  getTodayTasks,
  completeRoutineOccurrence,
  createPetRoutine,
  updatePetRoutine,
  deletePetRoutine,
} from './client';
import type { CreateRoutinePayload, UpdateRoutinePayload } from './types';

/**
 * Hook to fetch today's routine tasks for a specific pet
 */
export function useTodayTasks(petId: number | string) {
  return useAppQuery({
    queryKey: qk.routines.today(petId),
    queryFn: () => getTodayTasks(petId),
    enabled: !!petId,
    staleTime: 30_000, // Refresh more frequently for today's tasks
  });
}

/**
 * Hook to complete a routine occurrence
 */
export function useCompleteRoutineOccurrence() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: completeRoutineOccurrence,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.routines.all });
    },
  });
}

/**
 * Hook to create a new routine
 */
export function useCreatePetRoutine() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: createPetRoutine,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: qk.routines.byPet(variables.petId) });
      qc.invalidateQueries({ queryKey: qk.routines.today(variables.petId) });
    },
  });
}

/**
 * Hook to update a routine
 */
export function useUpdatePetRoutine() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: updatePetRoutine,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.routines.all });
    },
  });
}

/**
 * Hook to delete a routine
 */
export function useDeletePetRoutine() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: deletePetRoutine,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.routines.all });
    },
  });
}
