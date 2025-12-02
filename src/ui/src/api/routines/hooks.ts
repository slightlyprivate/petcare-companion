import { useQueryClient, type QueryKey } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import {
  getTodayTasks,
  completeRoutineOccurrence,
  createPetRoutine,
  updatePetRoutine,
  deletePetRoutine,
} from './client';
import type { CreateRoutinePayload, PetRoutineOccurrence, UpdateRoutinePayload } from './types';

type RoutineTask = PetRoutineOccurrence & { occurrence_id?: number | string };
type RoutineQueryData = { data: RoutineTask[] };
type RoutineSnapshot = { queryKey: QueryKey; data: RoutineQueryData | undefined };
type RoutineMutationContext = { previousSnapshots: RoutineSnapshot[] };

const isRoutineQueryKey = (queryKey: QueryKey) =>
  Array.isArray(queryKey) && queryKey[0] === 'routines';

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
  return useAppMutation<PetRoutineOccurrence, unknown, number | string, RoutineMutationContext>({
    mutationFn: completeRoutineOccurrence,
    onMutate: async (occurrenceId: number | string) => {
      // Optimistically mark occurrence as completed in today's tasks cache
      const routineQueries = qc
        .getQueryCache()
        .findAll({ predicate: (q) => isRoutineQueryKey(q.queryKey) });
      const previousSnapshots: RoutineSnapshot[] = [];

      routineQueries.forEach(({ queryKey }) => {
        const prev = qc.getQueryData<RoutineQueryData>(queryKey);
        previousSnapshots.push({ queryKey, data: prev });
        if (prev?.data) {
          const updatedData = prev.data.map((task) =>
            task.id === occurrenceId || task.occurrence_id === occurrenceId
              ? { ...task, completed_at: new Date().toISOString() }
              : task,
          );
          qc.setQueryData<RoutineQueryData>(queryKey, { ...prev, data: updatedData });
        }
      });

      return { previousSnapshots };
    },
    onError: (_err, _vars, context) => {
      // Rollback optimistic updates
      context?.previousSnapshots.forEach(({ queryKey, data }) => qc.setQueryData(queryKey, data));
    },
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
  return useAppMutation<void, unknown, CreateRoutinePayload>({
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
  return useAppMutation<void, unknown, UpdateRoutinePayload>({
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
  return useAppMutation<void, unknown, number | string, RoutineMutationContext>({
    mutationFn: deletePetRoutine,
    onMutate: async (routineId: number | string) => {
      const keys = qc.getQueryCache().findAll({ predicate: (q) => isRoutineQueryKey(q.queryKey) });
      const previousSnapshots: RoutineSnapshot[] = [];
      for (const entry of keys) {
        const qKey = entry.queryKey;
        const prev = qc.getQueryData<RoutineQueryData>(qKey);
        previousSnapshots.push({ queryKey: qKey, data: prev });
        if (prev?.data) {
          const updatedData = prev.data.filter(
            (task) => task.routine_id !== routineId && task.id !== routineId,
          );
          qc.setQueryData<RoutineQueryData>(qKey, { ...prev, data: updatedData });
        }
      }
      return { previousSnapshots };
    },
    onError: (_err, _vars, context) => {
      context?.previousSnapshots.forEach(({ queryKey, data }) => qc.setQueryData(queryKey, data));
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.routines.all });
    },
  });
}
