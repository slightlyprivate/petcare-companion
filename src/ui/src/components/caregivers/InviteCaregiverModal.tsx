import { useState } from 'react';
import Modal from '../modals/Modal';
import Button from '../Button';
import { useSendCaregiverInvitation } from '../../api/caregivers/hooks';
import { useToast } from '../../lib/notifications';
import { cn } from '../../lib/cn';

interface InviteCaregiverModalProps {
  isOpen: boolean;
  onClose: () => void;
  petId: string | number;
}

/**
 * InviteCaregiverModal Component
 *
 * A modal dialog that allows users to invite a caregiver by entering their email address.
 *
 * @param {boolean} isOpen - Whether the modal is open or closed.
 * @param {function} onClose - Function to call when the modal is requested to be closed.
 * @param {string | number} petId - The ID of the pet for which the caregiver is being invited.
 * @returns {JSX.Element} The rendered invite caregiver modal component.
 */
export default function InviteCaregiverModal({
  isOpen,
  onClose,
  petId,
}: InviteCaregiverModalProps): JSX.Element {
  const [inviteEmail, setInviteEmail] = useState('');
  const [emailError, setEmailError] = useState('');
  const toast = useToast();
  const sendInvitation = useSendCaregiverInvitation();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setEmailError('');

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(inviteEmail)) {
      setEmailError('Please enter a valid email address');
      return;
    }

    try {
      await sendInvitation.mutateAsync({ petId, invitee_email: inviteEmail });
      toast.success('Invitation sent successfully!');
      setInviteEmail('');
      onClose();
    } catch (err) {
      const errorMessage = (err as Error).message || 'Failed to send invitation';
      setEmailError(errorMessage);
      toast.error(errorMessage);
    }
  };

  const handleClose = () => {
    setInviteEmail('');
    setEmailError('');
    onClose();
  };

  return (
    <Modal isOpen={isOpen} onClose={handleClose} title="Invite Caregiver">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="invitee-email" className="mb-2 block text-sm font-medium text-brand-fg">
            Email Address
          </label>
          <input
            id="invitee-email"
            type="email"
            value={inviteEmail}
            onChange={(e) => setInviteEmail(e.target.value)}
            placeholder="caregiver@example.com"
            className={cn(
              'w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-2',
              emailError
                ? 'border-red-500 focus:ring-red-500'
                : 'border-brand-muted focus:ring-brand-accent',
            )}
            disabled={sendInvitation.isPending}
            required
            autoFocus
          />
          {emailError && <p className="mt-1 text-sm text-red-600">{emailError}</p>}
          <p className="mt-1 text-xs text-brand-fg/60">
            They will receive an email with an invitation link
          </p>
        </div>

        <div className="flex justify-end gap-3">
          <Button
            type="button"
            variant="ghost"
            onClick={handleClose}
            disabled={sendInvitation.isPending}
          >
            Cancel
          </Button>
          <Button
            type="submit"
            isLoading={sendInvitation.isPending}
            disabled={sendInvitation.isPending}
          >
            Send Invitation
          </Button>
        </div>
      </form>
    </Modal>
  );
}
