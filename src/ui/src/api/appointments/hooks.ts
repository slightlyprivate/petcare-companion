import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch appointments for a specific pet.
 */
export function useAppointmentsByPet(petId: number | string) {
  return useAppQuery({
    queryKey: qk.appts.byPet(petId),
    queryFn: () => client.listByPet(petId),
    enabled: !!petId,
  });
}

/**
 * Skeleton mutation: create appointment and invalidate byPet cache.
 */
export function useCreateAppointment() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.create,
    onSuccess: (_data, variables) => {
      if (variables?.petId !== undefined) {
        qc.invalidateQueries({ queryKey: qk.appts.byPet(variables.petId) });
      }
    },
  });
}

/**
 * Skeleton mutation: update appointment and invalidate byPet cache.
 */
export function useUpdateAppointment() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.update,
    onSuccess: (_data, variables) => {
      if (variables?.petId !== undefined) {
        qc.invalidateQueries({ queryKey: qk.appts.byPet(variables.petId) });
      }
    },
  });
}

/**
 * Skeleton mutation: cancel appointment and invalidate byPet cache.
 */
export function useCancelAppointment() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.cancel,
    onSuccess: (_data, variables) => {
      if (variables?.petId !== undefined) {
        qc.invalidateQueries({ queryKey: qk.appts.byPet(variables.petId) });
      }
    },
  });
}
