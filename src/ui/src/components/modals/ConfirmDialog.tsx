import Modal from './Modal';
import Button from '../Button';

interface ConfirmDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  variant?: 'danger' | 'primary';
  isLoading?: boolean;
}

/**
 * ConfirmDialog Component
 *
 * A reusable confirmation dialog modal that prompts the user to confirm or cancel an action.
 *
 * @param {boolean} isOpen - Whether the dialog is open or closed.
 * @param {function} onClose - Function to call when the dialog is requested to be closed.
 * @param {function} onConfirm - Function to call when the user confirms the action.
 * @param {string} title - The title of the dialog.
 * @param {string} message - The message displayed in the dialog.
 * @param {string} [confirmText='Confirm'] - The text for the confirm button.
 * @param {string} [cancelText='Cancel'] - The text for the cancel button.
 * @param {'danger' | 'primary'} [variant='primary'] - The variant style for the confirm button.
 * @param {boolean} [isLoading=false] - Whether to show a loading state on the confirm button.
 * @returns {JSX.Element} The rendered confirm dialog component.
 */
export default function ConfirmDialog({
  isOpen,
  onClose,
  onConfirm,
  title,
  message,
  confirmText = 'Confirm',
  cancelText = 'Cancel',
  variant = 'primary',
  isLoading = false,
}: ConfirmDialogProps): JSX.Element {
  const handleConfirm = () => {
    onConfirm();
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title={title} size="sm">
      <div className="space-y-4">
        <p className="text-sm text-brand-fg">{message}</p>

        <div className="flex gap-3 justify-end">
          <Button variant="ghost" onClick={onClose} disabled={isLoading}>
            {cancelText}
          </Button>
          <Button
            variant={variant}
            onClick={handleConfirm}
            isLoading={isLoading}
            disabled={isLoading}
          >
            {confirmText}
          </Button>
        </div>
      </div>
    </Modal>
  );
}
