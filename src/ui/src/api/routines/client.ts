import { api } from '../../lib/http';
import type {
  TodayTasksResponse,
  PetRoutineOccurrence,
  CreateRoutinePayload,
  UpdateRoutinePayload,
} from './types';

/**
 * Fetch today's routine tasks for a specific pet
 */
export const getTodayTasks = async (petId: number | string): Promise<TodayTasksResponse> => {
  const res = await api(`/pets/${petId}/routines/today`);
  return res as TodayTasksResponse;
};

/**
 * Complete a routine occurrence
 */
export const completeRoutineOccurrence = async (
  occurrenceId: number | string,
): Promise<PetRoutineOccurrence> => {
  const res = await api(`/routine-occurrences/${occurrenceId}/complete`, {
    method: 'POST',
  });
  return (res as { data: PetRoutineOccurrence }).data;
};

/**
 * Create a new routine for a pet
 */
export const createPetRoutine = async (payload: CreateRoutinePayload): Promise<void> => {
  await api(`/pets/${payload.petId}/routines`, {
    method: 'POST',
    body: {
      name: payload.name,
      description: payload.description,
      time_of_day: payload.time_of_day,
      days_of_week: payload.days_of_week,
    },
  });
};

/**
 * Update a routine
 */
export const updatePetRoutine = async (payload: UpdateRoutinePayload): Promise<void> => {
  await api(`/routines/${payload.id}`, {
    method: 'PATCH',
    body: {
      name: payload.name,
      description: payload.description,
      time_of_day: payload.time_of_day,
      days_of_week: payload.days_of_week,
    },
  });
};

/**
 * Delete a routine
 */
export const deletePetRoutine = async (routineId: number | string): Promise<void> => {
  await api(`/routines/${routineId}`, { method: 'DELETE' });
};
