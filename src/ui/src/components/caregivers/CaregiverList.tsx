import { Icon } from '../icons';
import { useState } from 'react';
import {
  usePetCaregivers,
  useCaregiverInvitations,
  useRemovePetCaregiver,
  useRevokeCaregiverInvitation,
} from '../../api/caregivers/hooks';
import Button from '../Button';
import ErrorMessage from '../ErrorMessage';
import { Skeleton } from '../ui/Loader';
import EmptyState from '../EmptyState';
import ConfirmDialog from '../modals/ConfirmDialog';
import InviteCaregiverModal from './InviteCaregiverModal';
import CaregiverCard from './CaregiverCard';
import PendingInvitationCard from './PendingInvitationCard';
import { useToast } from '../../lib/notifications';
import { copyInvitationLink } from '../../utils/invitationHelpers';
import type { CaregiverInvitation } from '../../api/caregivers/types';

interface CaregiverListProps {
  petId: string | number;
  isOwner?: boolean;
}

/**
 * Component displaying the list of caregivers for a pet with the ability to invite new caregivers.
 */
export default function CaregiverList({ petId, isOwner = false }: CaregiverListProps) {
  const [showInviteModal, setShowInviteModal] = useState(false);
  const [confirmRemove, setConfirmRemove] = useState<string | number | null>(null);
  const [confirmRevoke, setConfirmRevoke] = useState<number | null>(null);
  const [copiedId, setCopiedId] = useState<string | null>(null);

  const toast = useToast();
  const { data: caregivers, isLoading, error } = usePetCaregivers(petId);
  const { data: invitations } = useCaregiverInvitations();
  const removeCaregiver = useRemovePetCaregiver();
  const revokeInvitation = useRevokeCaregiverInvitation();

  // Filter pending invitations for this pet
  const pendingInvitations = (invitations?.sent || []).filter(
    (inv: CaregiverInvitation) => inv.pet_id === String(petId) && inv.status === 'pending',
  );

  const handleRemoveCaregiver = async (userId: string | number) => {
    try {
      await removeCaregiver.mutateAsync({ petId, userId });
      toast.success('Caregiver removed');
      setConfirmRemove(null);
    } catch (err) {
      toast.error((err as Error).message || 'Failed to remove caregiver');
    }
  };

  const handleRevokeInvitation = async (invitationId: number) => {
    try {
      await revokeInvitation.mutateAsync(invitationId);
      toast.success('Invitation revoked');
      setConfirmRevoke(null);
    } catch (err) {
      toast.error((err as Error).message || 'Failed to revoke invitation');
    }
  };

  const handleCopyInvitationLink = async (token: string, invitationId: string) => {
    try {
      await copyInvitationLink(token);
      setCopiedId(invitationId);
      toast.success('Invitation link copied to clipboard');
      setTimeout(() => setCopiedId(null), 2000);
    } catch (err) {
      toast.error('Failed to copy link');
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-3" aria-label="Caregivers loading">
        <div className="flex items-center justify-between">
          <Skeleton className="h-6 w-40" />
          <Skeleton className="h-8 w-28" />
        </div>
        <Skeleton className="h-16 w-full" />
        <Skeleton className="h-16 w-full" />
      </div>
    );
  }

  if (error) {
    return <ErrorMessage message="Failed to load caregivers" />;
  }

  const hasCaregivers = caregivers && caregivers.length > 0;
  const hasPendingInvitations = pendingInvitations.length > 0;

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold text-brand-fg">Caregivers</h2>
        {isOwner && (
          <Button variant="secondary" size="sm" onClick={() => setShowInviteModal(true)}>
            Invite Caregiver
          </Button>
        )}
      </div>

      {/* Caregivers List */}
      <div className="space-y-2">
        {!hasCaregivers ? (
          <EmptyState
            title="No caregivers yet"
            description={isOwner ? 'Invite someone to help care for your pet' : undefined}
          />
        ) : (
          caregivers.map((caregiver) => (
            <CaregiverCard
              key={caregiver.id}
              caregiver={caregiver}
              isOwner={isOwner}
              onRemove={setConfirmRemove}
              isRemoving={removeCaregiver.isPending}
            />
          ))
        )}
      </div>

      {/* Pending Invitations */}
      {isOwner && hasPendingInvitations && (
        <div className="mt-6 space-y-2">
          <h3 className="text-sm font-medium text-brand-fg">Pending Invitations</h3>
          {pendingInvitations.map((inv) => (
            <PendingInvitationCard
              key={inv.id}
              invitation={inv}
              onCopyLink={handleCopyInvitationLink}
              onRevoke={setConfirmRevoke}
              isCopied={copiedId === inv.id}
              isRevoking={revokeInvitation.isPending}
            />
          ))}
        </div>
      )}

      {/* Empty state for owners with no caregivers and no invitations */}
      {isOwner && !hasCaregivers && !hasPendingInvitations && (
        <EmptyState
          variant="dashed"
          icon={<Icon name="users" size={48} className="text-brand-fg/40" />}
          title="No caregivers or invitations"
          description="Invite friends or family to help care for your pet"
        />
      )}

      {/* Modals */}
      <InviteCaregiverModal
        isOpen={showInviteModal}
        onClose={() => setShowInviteModal(false)}
        petId={petId}
      />

      <ConfirmDialog
        isOpen={confirmRemove !== null}
        onClose={() => setConfirmRemove(null)}
        onConfirm={() => confirmRemove && handleRemoveCaregiver(confirmRemove)}
        title="Remove Caregiver?"
        message="Are you sure you want to remove this caregiver? They will no longer have access to this pet."
        confirmText="Remove"
        variant="danger"
        isLoading={removeCaregiver.isPending}
      />

      <ConfirmDialog
        isOpen={confirmRevoke !== null}
        onClose={() => setConfirmRevoke(null)}
        onConfirm={() => confirmRevoke && handleRevokeInvitation(confirmRevoke)}
        title="Revoke Invitation?"
        message="This will cancel the pending invitation. The recipient will no longer be able to accept it."
        confirmText="Revoke"
        variant="danger"
        isLoading={revokeInvitation.isPending}
      />
    </div>
  );
}
