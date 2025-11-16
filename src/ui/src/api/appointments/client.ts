import { api } from '../../lib/http';

/**
 * Fetch a list of appointments for a specific pet.
 */
export const listByPet = (petId: number | string) => api(`/pets/${petId}/appointments`);

export type CreateAppointmentPayload = {
  petId: number | string;
  at: string; // ISO datetime
  notes?: string;
};

export type UpdateAppointmentPayload = {
  petId: number | string;
  apptId: number | string;
  at?: string; // ISO datetime
  notes?: string;
};

export type CancelAppointmentPayload = {
  petId: number | string;
  apptId: number | string;
};

// Skeleton write operations; endpoints subject to backend alignment
export const create = (payload: CreateAppointmentPayload) =>
  api(`/pets/${payload.petId}/appointments`, { method: 'POST', body: payload });

export const update = (payload: UpdateAppointmentPayload) =>
  api(`/pets/${payload.petId}/appointments/${payload.apptId}`, { method: 'PATCH', body: payload });

export const cancel = (payload: CancelAppointmentPayload) =>
  api(`/pets/${payload.petId}/appointments/${payload.apptId}`, { method: 'DELETE' });
