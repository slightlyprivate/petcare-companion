import { useState } from 'react';
import {
  usePetCaregivers,
  useSendCaregiverInvitation,
  useRemovePetCaregiver,
} from '../../api/caregivers/hooks';
import Button from '../Button';
import ErrorMessage from '../ErrorMessage';
import Spinner from '../Spinner';
import { cn } from '../../lib/cn';

interface CaregiverListProps {
  petId: string | number;
  isOwner?: boolean;
}

/**
 * Component displaying the list of caregivers for a pet with the ability to invite new caregivers.
 */
export default function CaregiverList({ petId, isOwner = false }: CaregiverListProps) {
  const [showInviteForm, setShowInviteForm] = useState(false);
  const [inviteEmail, setInviteEmail] = useState('');
  const [emailError, setEmailError] = useState('');

  const { data: caregivers, isLoading, error } = usePetCaregivers(petId);
  const sendInvitation = useSendCaregiverInvitation();
  const removeCaregiver = useRemovePetCaregiver();

  const handleInviteSubmit = async (e: React.FormEvent) => {
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
      setInviteEmail('');
      setShowInviteForm(false);
    } catch (err) {
      setEmailError((err as Error).message || 'Failed to send invitation');
    }
  };

  const handleRemoveCaregiver = async (userId: string | number) => {
    if (!window.confirm('Are you sure you want to remove this caregiver?')) {
      return;
    }

    try {
      await removeCaregiver.mutateAsync({ petId, userId });
    } catch (err) {
      console.error('Failed to remove caregiver:', err);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-8">
        <Spinner />
      </div>
    );
  }

  if (error) {
    return <ErrorMessage message="Failed to load caregivers" />;
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold text-brand-fg">Caregivers</h2>
        {isOwner && (
          <Button
            variant="secondary"
            size="sm"
            onClick={() => setShowInviteForm(!showInviteForm)}
            disabled={sendInvitation.isPending}
          >
            {showInviteForm ? 'Cancel' : 'Invite Caregiver'}
          </Button>
        )}
      </div>

      {showInviteForm && (
        <form
          onSubmit={handleInviteSubmit}
          className="rounded-lg border border-brand-muted bg-brand-secondary/30 p-4"
        >
          <label htmlFor="invitee-email" className="mb-2 block text-sm font-medium text-brand-fg">
            Email Address
          </label>
          <div className="flex gap-2">
            <input
              id="invitee-email"
              type="email"
              value={inviteEmail}
              onChange={(e) => setInviteEmail(e.target.value)}
              placeholder="caregiver@example.com"
              className={cn(
                'flex-1 rounded border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                emailError
                  ? 'border-red-500 focus:ring-red-500'
                  : 'border-brand-muted focus:ring-brand-accent',
              )}
              disabled={sendInvitation.isPending}
              required
            />
            <Button type="submit" size="sm" isLoading={sendInvitation.isPending}>
              Send Invite
            </Button>
          </div>
          {emailError && <p className="mt-1 text-sm text-red-600">{emailError}</p>}
          {sendInvitation.isSuccess && (
            <p className="mt-2 text-sm text-green-600">Invitation sent successfully!</p>
          )}
        </form>
      )}

      <div className="space-y-2">
        {!caregivers || caregivers.length === 0 ? (
          <div className="rounded-lg border border-brand-muted bg-brand-secondary/20 p-6 text-center">
            <p className="text-sm text-brand-fg/60">No caregivers yet</p>
            {isOwner && (
              <p className="mt-1 text-xs text-brand-fg/40">
                Invite someone to help care for your pet
              </p>
            )}
          </div>
        ) : (
          caregivers.map((caregiver) => (
            <div
              key={caregiver.id}
              className="flex items-center justify-between rounded-lg border border-brand-muted bg-white p-3 shadow-sm"
            >
              <div className="flex-1">
                <div className="flex items-center gap-2">
                  <p className="font-medium text-brand-fg">{caregiver.name || caregiver.email}</p>
                  <span
                    className={cn(
                      'rounded-full px-2 py-0.5 text-xs font-medium',
                      caregiver.role === 'owner'
                        ? 'bg-brand-accent/10 text-brand-accent'
                        : 'bg-blue-50 text-blue-700',
                    )}
                  >
                    {caregiver.role}
                  </span>
                </div>
                {caregiver.email && caregiver.name && (
                  <p className="text-sm text-brand-fg/60">{caregiver.email}</p>
                )}
                {caregiver.joined_at && (
                  <p className="text-xs text-brand-fg/40">
                    Joined {new Date(caregiver.joined_at).toLocaleDateString()}
                  </p>
                )}
              </div>
              {isOwner && caregiver.role === 'caregiver' && (
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => handleRemoveCaregiver(caregiver.id)}
                  disabled={removeCaregiver.isPending}
                  className="text-red-600 hover:bg-red-50"
                >
                  Remove
                </Button>
              )}
            </div>
          ))
        )}
      </div>
    </div>
  );
}
