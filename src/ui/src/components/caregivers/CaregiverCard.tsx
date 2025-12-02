import Button from '../Button';
import { cn } from '../../lib/cn';
import type { Caregiver } from '../../api/caregivers/types';

interface CaregiverCardProps {
  caregiver: Caregiver;
  isOwner: boolean;
  onRemove: (id: string | number) => void;
  isRemoving: boolean;
}

/**
 * CaregiverCard Component
 *
 * A card component that displays information about a caregiver, including their name,
 * email, role, and joined date. If the current user is the owner, they can also remove
 * the caregiver.
 *
 * @param {Caregiver} caregiver - The caregiver data to display.
 * @param {boolean} isOwner - Whether the current user is the owner of the pet.
 * @param {function} onRemove - Function to call when the remove button is clicked.
 * @param {boolean} isRemoving - Whether the caregiver is currently being removed.
 * @returns {JSX.Element} The rendered caregiver card component.
 */
export default function CaregiverCard({
  caregiver,
  isOwner,
  onRemove,
  isRemoving,
}: CaregiverCardProps): JSX.Element {
  return (
    <div className="flex items-center justify-between rounded-lg border border-brand-muted bg-white p-3 shadow-sm">
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
          onClick={() => onRemove(caregiver.id)}
          disabled={isRemoving}
          className="text-red-600 hover:bg-red-50"
        >
          Remove
        </Button>
      )}
    </div>
  );
}
