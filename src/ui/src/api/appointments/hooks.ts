import { useQuery } from '@tanstack/react-query';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch appointments for a specific pet.
 */
export function useAppointmentsByPet(petId: number | string) {
  return useQuery({
    queryKey: qk.appts.byPet(petId),
    queryFn: () => client.listByPet(petId),
    enabled: !!petId,
  });
}
