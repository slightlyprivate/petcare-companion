import { api } from '../../lib/http';
import type { Paginated as PaginatedType } from '../../lib/fetch';
import { normalizePaginated, unwrapResource } from '../../lib/fetch';
import type { Pet } from '../types';

/**
 * Fetch the list of public pets.
 */
export const listPublicPets = async (): Promise<PaginatedType<Pet>> => {
  const res = await api('/public/pets');
  return normalizePaginated<Pet>(res);
};

/**
 * Fetch the details of a specific public pet.
 */
export const getPublicPet = async (id: number | string): Promise<Pet | null> => {
  const res = await api(`/public/pets/${id}`);
  return unwrapResource<Pet>(res);
};

/**
 * Fetch the details of a specific pet.
 */
export const getPet = async (id: number | string): Promise<Pet | null> => {
  const res = await api(`/pets/${id}`);
  return unwrapResource<Pet>(res);
};

/**
 * Authenticated pets endpoints
 */
export const listPets = async (): Promise<PaginatedType<Pet>> => {
  const res = await api('/pets');
  return normalizePaginated<Pet>(res);
};

/**
 * Create, update, delete, and restore pet endpoints
 */
export const createPet = (payload: {
  name: string;
  species: string;
  owner_name: string;
  breed?: string | null;
  birth_date?: string | null; // YYYY-MM-DD
}) => api('/pets', { method: 'POST', body: payload });

/**
 * Update pet details
 */
export const updatePet = (payload: {
  id: number | string;
  name: string;
  species: string;
  owner_name: string;
  breed?: string | null;
  birth_date?: string | null;
}) => api(`/pets/${payload.id}`, { method: 'PUT', body: payload });

/**
 * Upload pet avatar image
 */
export const updatePetAvatar = async (
  petId: number | string,
  file: File,
): Promise<{ message: string; avatar_url: string }> => {
  const formData = new FormData();
  formData.append('avatar', file);

  const res = await api(`/pets/${petId}/avatar`, {
    method: 'POST',
    body: formData,
  });

  return res as { message: string; avatar_url: string };
};

/**
 * Delete a pet
 */
export const deletePet = (id: number | string) => api(`/pets/${id}`, { method: 'DELETE' });

/**
 * Restore a deleted pet
 */
export const restorePet = (id: number | string) => api(`/pets/${id}/restore`, { method: 'POST' });

/**
 * Public pet report
 */
export const getPublicPetReport = (petId: number | string) => api(`/public/pet-reports/${petId}`);
