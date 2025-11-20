import { useEffect, type ReactNode } from 'react';
import { cn } from '../../lib/cn';

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title: string;
  children: ReactNode;
  size?: 'sm' | 'md' | 'lg';
}

/**
 * Modal Component
 *
 * A reusable modal dialog component that can display content in a centered overlay.
 *
 * @param {boolean} isOpen - Whether the modal is open or closed.
 * @param {function} onClose - Function to call when the modal is requested to be closed.
 * @param {string} title - The title of the modal.
 * @param {ReactNode} children - The content to display inside the modal.
 * @param {'sm' | 'md' | 'lg'} [size='md'] - The size of the modal.
 * @returns {JSX.Element | null} The rendered modal component or null if closed.
 */
export default function Modal({
  isOpen,
  onClose,
  title,
  children,
  size = 'md',
}: ModalProps): JSX.Element | null {
  // Handle escape key
  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && isOpen) {
        onClose();
      }
    };

    if (isOpen) {
      document.addEventListener('keydown', handleEscape);
      // Prevent body scroll when modal is open
      document.body.style.overflow = 'hidden';
    }

    return () => {
      document.removeEventListener('keydown', handleEscape);
      document.body.style.overflow = 'unset';
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
  };

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      onClick={onClose}
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-title"
    >
      <div
        className={cn('w-full rounded-lg bg-white shadow-xl', sizeClasses[size])}
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="flex items-center justify-between border-b border-brand-muted px-6 py-4">
          <h2 id="modal-title" className="text-lg font-semibold text-brand-primary">
            {title}
          </h2>
          <button
            onClick={onClose}
            className="text-brand-fg transition-colors hover:text-brand-primary"
            aria-label="Close modal"
          >
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        {/* Body */}
        <div className="px-6 py-4">{children}</div>
      </div>
    </div>
  );
}
