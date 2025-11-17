import { ButtonHTMLAttributes } from 'react';
import Spinner from './Spinner';
import { cn } from '../lib/cn';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
}

/**
 * Accessible, styled button with variants and loading state.
 */
export default function Button({
  className,
  variant = 'primary',
  size = 'md',
  isLoading = false,
  disabled,
  children,
  ...props
}: ButtonProps) {
  const base =
    'inline-flex items-center justify-center rounded focus:outline-none focus:ring-2 focus:ring-brand-accent disabled:opacity-50';
  const variantCls =
    variant === 'primary'
      ? 'bg-brand-accent text-white hover:bg-brand-accent-700'
      : variant === 'secondary'
        ? 'bg-brand-secondary text-brand-primary hover:bg-brand-secondary-200'
        : variant === 'danger'
          ? 'bg-red-600 text-white hover:bg-red-700'
          : 'bg-transparent text-brand-fg hover:bg-brand-muted/40';
  const sizeCls =
    size === 'sm'
      ? 'px-2.5 py-1 text-xs'
      : size === 'lg'
        ? 'px-4 py-2 text-base'
        : 'px-3 py-1.5 text-sm';

  return (
    <button
      className={cn(base, variantCls, sizeCls, className)}
      aria-busy={isLoading || undefined}
      disabled={disabled || isLoading}
      {...props}
    >
      {isLoading ? <Spinner /> : null}
      <span className={cn(isLoading && 'ml-2')}>{children}</span>
    </button>
  );
}
