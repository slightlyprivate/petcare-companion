import { api } from '../../lib/http';
import type { Caregiver, CaregiverInvitation, InvitationLists } from './types';

/**
 * Fetch caregivers for a specific pet
 */
export const listPetCaregivers = async (petId: number | string): Promise<Caregiver[]> => {
  const res = await api(`/pets/${petId}/caregivers`);
  // Assuming the API returns { data: [...] }
  return (res as { data?: Caregiver[] })?.data || [];
};

/**
 * Send a caregiver invitation
 */
export const sendCaregiverInvitation = async (payload: {
  petId: number | string;
  invitee_email: string;
}): Promise<CaregiverInvitation> => {
  const res = await api(`/pets/${payload.petId}/caregiver-invitations`, {
    method: 'POST',
    body: { invitee_email: payload.invitee_email },
  });
  return (res as { data: CaregiverInvitation }).data;
};

/**
 * Accept a caregiver invitation
 */
export const acceptCaregiverInvitation = async (token: string): Promise<void> => {
  await api(`/caregiver-invitations/${token}/accept`, { method: 'POST' });
};

/**
 * Revoke a caregiver invitation
 */
export const revokeCaregiverInvitation = async (invitationId: number | string): Promise<void> => {
  await api(`/caregiver-invitations/${invitationId}`, { method: 'DELETE' });
};

/**
 * List all invitations for the current user
 */
export const listCaregiverInvitations = async (): Promise<InvitationLists> => {
  const res = await api('/caregiver-invitations');
  return (res as { data: InvitationLists }).data;
};

/**
 * Remove a caregiver from a pet
 */
export const removePetCaregiver = async (payload: {
  petId: number | string;
  userId: number | string;
}): Promise<void> => {
  await api(`/pets/${payload.petId}/caregivers/${payload.userId}`, { method: 'DELETE' });
};
