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
    onMutate: async (occurrenceId: number | string) => {
      // Optimistically mark occurrence as completed in today's tasks cache
      const keys = qc
        .getQueryCache()
        .findAll({ predicate: (q) => Array.isArray(q.queryKey) && q.queryKey[0] === 'routines' });
      const previousSnapshots: any[] = [];
      for (const entry of keys) {
        const qKey = entry.queryKey;
        const prev = qc.getQueryData(qKey);
        previousSnapshots.push([qKey, prev]);
        if (prev && (prev as any).data) {
          const cloned = { ...(prev as any), data: [...(prev as any).data] };
          cloned.data = cloned.data.map((t: any) => {
            if (t.id === occurrenceId || t.occurrence_id === occurrenceId) {
              return { ...t, completed_at: new Date().toISOString() };
            }
            return t;
          });
          qc.setQueryData(qKey, cloned);
        }
      }
      return { previousSnapshots };
    },
    onError: (_err, _vars, context) => {
      // Rollback optimistic updates
      context?.previousSnapshots?.forEach(([qKey, data]: any) => qc.setQueryData(qKey, data));
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.routines.all });
    },
    onSettled: (_data, _error, _vars) => {
      // Revalidate today's tasks explicitly to ensure accuracy
      // Could narrow to specific pet if variable shape known
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
    onMutate: async (routineId: number | string) => {
      const keys = qc
        .getQueryCache()
        .findAll({ predicate: (q) => Array.isArray(q.queryKey) && q.queryKey[0] === 'routines' });
      const previousSnapshots: any[] = [];
      for (const entry of keys) {
        const qKey = entry.queryKey;
        const prev = qc.getQueryData(qKey);
        previousSnapshots.push([qKey, prev]);
        if (prev && (prev as any).data) {
          const cloned = { ...(prev as any), data: [...(prev as any).data] };
          cloned.data = cloned.data.filter(
            (t: any) => t.routine_id !== routineId && t.id !== routineId,
          );
          qc.setQueryData(qKey, cloned);
        }
      }
      return { previousSnapshots };
    },
    onError: (_err, _vars, context) => {
      context?.previousSnapshots?.forEach(([qKey, data]: any) => qc.setQueryData(qKey, data));
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.routines.all });
    },
  });
}
