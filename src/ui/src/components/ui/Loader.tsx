import { cn } from '../../lib/cn';
import Icon from '../icons/Icon';

/**
 * Loader Component
 *
 * A reusable loader/spinner component to indicate loading state.
 *
 * @param {string} [className] - Additional class names to apply to the loader.
 * @param {number} [size=24] - The size of the loader icon.
 * @returns {JSX.Element} The rendered loader component.
 */
export function Loader({
  className,
  size = 24,
}: {
  className?: string;
  size?: number;
}): JSX.Element {
  return (
    <Icon
      name="spinner"
      className={cn('animate-spin text-gray-500', className)}
      size={size}
      aria-label="Loading"
    />
  );
}

/**
 * Skeleton Component
 *
 * A reusable skeleton loader component to indicate loading state for various UI elements.
 *
 * @param {string} [className] - Additional class names to apply to the skeleton.
 * @returns {JSX.Element} The rendered skeleton component.
 */
export function Skeleton({ className }: { className?: string }): JSX.Element {
  return <div className={cn('animate-pulse rounded bg-gray-200', className)} />;
}
