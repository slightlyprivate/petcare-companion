import { api } from '../../lib/http';

/**
 * Fetch the list of public pets.
 */
export const listPublicPets = () => api('/public/pets');

/**
 * Fetch the details of a specific public pet.
 */
export const getPublicPet = (id: number | string) => api(`/public/pets/${id}`);

/**
 * Fetch the details of a specific pet.
 */
export const getPet = (id: number | string) => api(`/pets/${id}`);
