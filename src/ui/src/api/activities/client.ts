import { api } from '../../lib/http';
import type { PetActivitiesResponse, CreateActivityPayload, ListActivitiesParams } from './types';

/**
 * Fetch activities for a specific pet
 */
export const listPetActivities = async (
  petId: number | string,
  params?: ListActivitiesParams,
): Promise<PetActivitiesResponse> => {
  const queryParams = new URLSearchParams();
  if (params?.per_page) queryParams.set('per_page', String(params.per_page));
  if (params?.type) queryParams.set('type', params.type);
  if (params?.date_from) queryParams.set('date_from', params.date_from);
  if (params?.date_to) queryParams.set('date_to', params.date_to);

  const query = queryParams.toString();
  const url = `/pets/${petId}/activities${query ? `?${query}` : ''}`;

  const res = await api(url);
  return res as PetActivitiesResponse;
};

/**
 * Create a new activity for a pet
 */
export const createPetActivity = async (payload: CreateActivityPayload): Promise<void> => {
  await api(`/pets/${payload.petId}/activities`, {
    method: 'POST',
    body: {
      type: payload.type,
      description: payload.description,
      media_url: payload.media_url,
    },
  });
};

/**
 * Delete an activity
 */
export const deletePetActivity = async (activityId: number | string): Promise<void> => {
  await api(`/activities/${activityId}`, { method: 'DELETE' });
};
