import { http } from '../../lib/http';

/**
 * Fetch a list of appointments for a specific pet.
 */
export const listByPet = (petId: number | string) => http(`/pets/${petId}/appointments`);
