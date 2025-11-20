/**
 * Caregiver and invitation types
 */

export interface Caregiver {
  id: string;
  name: string;
  email: string;
  role: 'owner' | 'caregiver';
  joined_at?: string;
}

export interface CaregiverInvitation {
  id: string;
  pet_id: string;
  invitee_email: string;
  status: 'pending' | 'accepted' | 'declined' | 'revoked' | 'expired';
  expires_at: string;
  created_at: string;
}

export interface InvitationLists {
  sent: CaregiverInvitation[];
  received: CaregiverInvitation[];
}
