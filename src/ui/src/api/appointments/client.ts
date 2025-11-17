import { api, proxy } from '../../lib/http';

/**
 * Fetch a list of appointments for a specific pet.
 */
export const listByPet = (petId: number | string) => api(`/pets/${petId}/appointments`);

export type CreateAppointmentPayload = {
  petId: number | string;
  title: string;
  scheduled_at: string; // ISO datetime in future
  notes?: string;
};

export type UpdateAppointmentPayload = {
  petId: number | string; // for cache invalidation
  apptId: number | string;
  title?: string;
  scheduled_at?: string; // ISO datetime
  notes?: string;
};

export type CancelAppointmentPayload = {
  petId: number | string;
  apptId: number | string;
};

// Skeleton write operations aligned with Laravel routes
export const create = (payload: CreateAppointmentPayload) =>
  proxy(`/pets/${payload.petId}/appointments`, {
    method: 'POST',
    body: { title: payload.title, scheduled_at: payload.scheduled_at, notes: payload.notes },
  });

export const update = (payload: UpdateAppointmentPayload) =>
  proxy(`/appointments/${payload.apptId}`, {
    method: 'PUT',
    body: {
      title: payload.title,
      scheduled_at: payload.scheduled_at,
      notes: payload.notes,
      pet_id: undefined, // not reassigning via playground
    },
  });

export const cancel = (payload: CancelAppointmentPayload) =>
  proxy(`/appointments/${payload.apptId}`, { method: 'DELETE' });
