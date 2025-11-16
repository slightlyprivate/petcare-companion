import { http } from '../../lib/http';

/**
 * Fetch the list of public pets.
 */
export const listPublicPets = () => http('/public/pets');

/**
 * Fetch the details of a specific public pet.
 */
export const getPublicPet = (id: number | string) => http(`/public/pets/${id}`);

/**
 * Fetch the details of a specific pet.
 */
export const getPet = (id: number | string) => http(`/pets/${id}`);
