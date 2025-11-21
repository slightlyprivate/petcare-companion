import Button from '../Button';
import { cn } from '../../lib/cn';
import { Icon } from '../icons';
import { getRelativeTime, getExpiryStatus } from '../../utils/invitationHelpers';
import type { CaregiverInvitation } from '../../api/caregivers/types';

interface PendingInvitationCardProps {
  invitation: CaregiverInvitation;
  onCopyLink: (token: string, invitationId: string) => void;
  onRevoke: (invitationId: number) => void;
  isCopied: boolean;
  isRevoking: boolean;
}

/**
 * PendingInvitationCard Component
 *
 * A card component that displays information about a pending caregiver invitation,
 * including the invitee's email, status, expiry information, and actions to copy
 * the invitation link or revoke the invitation.
 *
 * @param {CaregiverInvitation} invitation - The invitation data to display.
 * @param {function} onCopyLink - Function to call when the copy link button is clicked.
 * @param {function} onRevoke - Function to call when the revoke button is clicked.
 * @param {boolean} isCopied - Whether the invitation link has been copied.
 * @param {boolean} isRevoking - Whether the invitation is currently being revoked.
 * @returns {JSX.Element} The rendered pending invitation card component.
 */
export default function PendingInvitationCard({
  invitation,
  onCopyLink,
  onRevoke,
  isCopied,
  isRevoking,
}: PendingInvitationCardProps): JSX.Element {
  const expiryStatus = getExpiryStatus(invitation.expires_at);

  return (
    <div className="flex items-center justify-between rounded-lg border border-yellow-200 bg-yellow-50/50 p-3">
      <div className="flex-1">
        <p className="font-medium text-brand-fg">{invitation.invitee_email}</p>
        <div className="mt-1 flex items-center gap-2">
          <span className="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700">
            {invitation.status}
          </span>
          <span className={cn('text-xs', expiryStatus.color)}>{expiryStatus.text}</span>
        </div>
        <p className="mt-1 text-xs text-brand-fg/40">
          Sent {getRelativeTime(invitation.created_at)}
        </p>
      </div>
      <div className="flex gap-2">
        <Button
          variant="ghost"
          size="sm"
          onClick={() => onCopyLink(invitation.token || '', invitation.id)}
          disabled={!invitation.token}
          className="text-brand-accent hover:bg-brand-accent/10"
          title="Copy invitation link"
        >
          {isCopied ? <Icon name="check" size={16} /> : <Icon name="copy" size={16} />}
        </Button>
        <Button
          variant="ghost"
          size="sm"
          onClick={() => onRevoke(Number(invitation.id))}
          disabled={isRevoking}
          className="text-red-600 hover:bg-red-50"
        >
          Revoke
        </Button>
      </div>
    </div>
  );
}
