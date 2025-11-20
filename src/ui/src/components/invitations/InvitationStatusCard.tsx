import Button from '../Button';

interface InvitationStatusCardProps {
  type: 'loading' | 'success' | 'error' | 'invalid';
  title: string;
  message: string;
  subMessage?: string;
  onPrimaryAction?: () => void;
  onSecondaryAction?: () => void;
  primaryLabel?: string;
  secondaryLabel?: string;
  showSpinner?: boolean;
}

const iconMap = {
  loading: (
    <div className="h-8 w-8">
      <div className="h-8 w-8 animate-spin rounded-full border-4 border-brand-accent border-t-transparent" />
    </div>
  ),
  success: (
    <svg className="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
    </svg>
  ),
  error: (
    <svg className="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
      />
    </svg>
  ),
  invalid: (
    <svg className="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
    </svg>
  ),
};

const backgroundMap = {
  loading: 'bg-brand-accent/10',
  success: 'bg-green-100',
  error: 'bg-red-100',
  invalid: 'bg-red-100',
};

/**
 * InvitationStatusCard Component
 *
 * A card component to display the status of an invitation acceptance process.
 *
 * @param {('loading' | 'success' | 'error' | 'invalid')} type - The type of status to display.
 * @param {string} title - The title of the status card.
 * @param {string} message - The main message of the status card.
 * @param {string} [subMessage] - An optional sub-message for additional information.
 * @param {function} [onPrimaryAction] - Optional function to call when the primary action button is clicked.
 * @param {function} [onSecondaryAction] - Optional function to call when the secondary action button is clicked.
 * @param {string} [primaryLabel='Continue'] - The label for the primary action button.
 * @param {string} [secondaryLabel='Go Back'] - The label for the secondary action button.
 * @param {boolean} [showSpinner=false] - Whether to show a loading spinner instead of the icon.
 * @returns {JSX.Element} The rendered invitation status card component.
 */
export default function InvitationStatusCard({
  type,
  title,
  message,
  subMessage,
  onPrimaryAction,
  onSecondaryAction,
  primaryLabel = 'Continue',
  secondaryLabel = 'Go Back',
  showSpinner = false,
}: InvitationStatusCardProps): JSX.Element {
  return (
    <div className="flex min-h-screen items-center justify-center bg-brand-smoke p-4">
      <div className="w-full max-w-md rounded-lg border border-brand-muted bg-white p-8 text-center shadow-lg">
        <div
          className={`mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full ${backgroundMap[type]}`}
        >
          {showSpinner ? iconMap.loading : iconMap[type]}
        </div>
        <h1 className="mb-2 text-xl font-semibold text-brand-primary">{title}</h1>
        <p className="mb-2 text-sm text-brand-fg">{message}</p>
        {subMessage && <p className="mb-6 text-xs text-brand-fg/60">{subMessage}</p>}

        {(onPrimaryAction || onSecondaryAction) && (
          <div className="flex gap-3 justify-center">
            {onSecondaryAction && (
              <Button variant="secondary" onClick={onSecondaryAction}>
                {secondaryLabel}
              </Button>
            )}
            {onPrimaryAction && <Button onClick={onPrimaryAction}>{primaryLabel}</Button>}
          </div>
        )}
      </div>
    </div>
  );
}
