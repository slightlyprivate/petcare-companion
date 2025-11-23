import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import {
  listPetCaregivers,
  sendCaregiverInvitation,
  acceptCaregiverInvitation,
  revokeCaregiverInvitation,
  listCaregiverInvitations,
  removePetCaregiver,
} from './client';

/**
 * Hook to fetch caregivers for a specific pet
 */
export function usePetCaregivers(petId: number | string) {
  return useAppQuery({
    queryKey: qk.caregivers.byPet(petId),
    queryFn: () => listPetCaregivers(petId),
    enabled: !!petId,
  });
}

/**
 * Hook to send a caregiver invitation
 */
export function useSendCaregiverInvitation() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: sendCaregiverInvitation,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: qk.caregivers.invitations });
      qc.invalidateQueries({ queryKey: qk.caregivers.byPet(variables.petId) });
    },
  });
}

/**
 * Hook to accept a caregiver invitation
 */
export function useAcceptCaregiverInvitation() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: acceptCaregiverInvitation,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.caregivers.invitations });
      qc.invalidateQueries({ queryKey: qk.pets.mine });
    },
  });
}

/**
 * Hook to revoke a caregiver invitation
 */
export function useRevokeCaregiverInvitation() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: revokeCaregiverInvitation,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: qk.caregivers.invitations });
    },
  });
}

/**
 * Hook to list all invitations for the current user
 */
export function useCaregiverInvitations() {
  return useAppQuery({
    queryKey: qk.caregivers.invitations,
    queryFn: listCaregiverInvitations,
  });
}

/**
 * Hook to remove a caregiver from a pet
 */
export function useRemovePetCaregiver() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: removePetCaregiver,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: qk.caregivers.byPet(variables.petId) });
    },
  });
}
