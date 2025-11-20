import React from 'react';
import { cva } from 'class-variance-authority';
import { cn } from '../../lib/cn';

const badgeStyles = cva(
  'inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium',
  {
    variants: {
      variant: {
        default: 'bg-gray-100 text-gray-800 border-gray-200',
        success: 'bg-green-100 text-green-800 border-green-200',
        warning: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        danger: 'bg-red-100 text-red-800 border-red-200',
        info: 'bg-blue-100 text-blue-800 border-blue-200',
      },
    },
    defaultVariants: { variant: 'default' },
  },
);

/**
 * Badge Component
 *
 * A reusable badge component to display small status indicators with different variants.
 *
 * @param {'default' | 'success' | 'warning' | 'danger' | 'info'} [variant='default'] - The style variant of the badge.
 * @param {React.ReactNode} children - The content to be displayed inside the badge.
 * @param {string} [className] - Additional class names to apply to the badge.
 * @returns {JSX.Element} The rendered badge component.
 */
export function Badge({
  variant,
  children,
  className,
}: {
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'info';
  children: React.ReactNode;
  className?: string;
}): JSX.Element {
  return <span className={cn(badgeStyles({ variant }), className)}>{children}</span>;
}
