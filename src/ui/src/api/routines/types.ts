/**
 * Pet routine types and interfaces
 */

export interface PetRoutine {
  id: string | number;
  pet_id: string;
  name: string;
  description?: string | null;
  time_of_day: string;
  days_of_week: number[];
}

export interface PetRoutineOccurrence {
  id: string | number;
  routine_id: string | number;
  date: string;
  completed_at?: string | null;
  completed_by?: string | null;
  routine: PetRoutine;
}

export interface TodayTasksResponse {
  data: PetRoutineOccurrence[];
}

export interface CreateRoutinePayload {
  petId: number | string;
  name: string;
  description?: string | null;
  time_of_day: string;
  days_of_week: number[];
}

export interface UpdateRoutinePayload {
  id: number | string;
  name: string;
  description?: string | null;
  time_of_day: string;
  days_of_week: number[];
}
