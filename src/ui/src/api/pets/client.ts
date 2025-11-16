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
