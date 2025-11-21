import React from 'react';
import { cva } from 'class-variance-authority';
import { cn } from '../../lib/cn';

const buttonStyles = cva(
  'inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:opacity-50 disabled:pointer-events-none',
  {
    variants: {
      variant: {
        primary: 'bg-blue-600 text-white hover:bg-blue-700',
        secondary: 'bg-gray-100 text-gray-900 hover:bg-gray-200',
        outline: 'border border-gray-300 bg-white text-gray-900 hover:bg-gray-50',
        danger: 'bg-red-600 text-white hover:bg-red-700',
        ghost: 'text-gray-700 hover:bg-gray-100',
      },
      size: {
        sm: 'h-8 px-3',
        md: 'h-10 px-4',
        lg: 'h-11 px-5',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
    },
  },
);

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
}

/**
 * Button Component
 *
 * A reusable button component with variant and size options.
 *
 * @param {('primary' | 'secondary' | 'outline' | 'danger' | 'ghost')} [variant='primary'] - The style variant of the button.
 * @param {('sm' | 'md' | 'lg')} [size='md'] - The size of the button.
 * @param {string} [className] - Additional class names to apply to the button.
 * @param {React.ButtonHTMLAttributes<HTMLButtonElement>} rest - Other button attributes.
 * @returns {JSX.Element} The rendered button component.
 */
export function Button({ className, variant, size, ...rest }: ButtonProps): JSX.Element {
  return <button className={cn(buttonStyles({ variant, size }), className)} {...rest} />;
}
